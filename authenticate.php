<?php
include ("login.php");
// Initialize session
ini_set('session.cookie_httponly', '1');
session_start();
function SIDtoString($ADsid)
{
    $sid = "S-";
    //$ADguid = $info[0]['objectguid'][0];
    $sidinhex = str_split(bin2hex($ADsid), 2);
    // Byte 0 = Revision Level
    $sid = $sid.hexdec($sidinhex[0])."-";
    // Byte 1-7 = 48 Bit Authority
    $sid = $sid.hexdec($sidinhex[6].$sidinhex[5].$sidinhex[4].$sidinhex[3].$sidinhex[2].$sidinhex[1]);
    // Byte 8 count of sub authorities - Get number of sub-authorities
    $subauths = hexdec($sidinhex[7]);
    //Loop through Sub Authorities
    for($i = 0; $i < $subauths; $i++) {
        $start = 8 + (4 * $i);
        // X amount of 32Bit (4 Byte) Sub Authorities
        $sid = $sid."-".hexdec($sidinhex[$start+3].$sidinhex[$start+2].$sidinhex[$start+1].$sidinhex[$start]);
    }
    return $sid;
}

function authenticate($user, $password) {
    if(empty($user) || empty($password)) return false;

    // Active Directory server
    $ldap_host = LDAP_HOST;

    // Active Directory DN
    $ldap_dn_Users = LDAP_DN_Users;
    $ldap_dn_Admins = LDAP_DN_Admins;

    // Active Directory user group
    $ldap_user_group = LDAP_USER_GROUP;

    // Active Directory manager group
    $ldap_manager_groups = unserialize(LDAP_MANAGER_GROUPS);

    // Domain, for purposes of constructing $user
    $ldap_usr_dom = LDAP_USR_DOM;

    // connect to active directory
    $ldap = ldap_connect($ldap_host);

    // verify user and password
    if($bind = @ldap_bind($ldap, $user.$ldap_usr_dom, $password)) {
        // valid
        // check presence in groups
        $filter = "(sAMAccountName=".$user.")";
        $attr = array("memberof","mail","displayName","department","objectSid","pwdlastset");
        $resultusers = ldap_search($ldap, $ldap_dn_Users, $filter, $attr) or exit("Unable to search LDAP server");
        $resultadmins = ldap_search($ldap, $ldap_dn_Admins, $filter, $attr) or exit("Unable to search LDAP server");
        $entries = ldap_get_entries($ldap, $resultusers);
     //   var_dump ($entries[0]);

        $entries=array_merge($entries,ldap_get_entries($ldap, $resultadmins));
        ldap_unbind($ldap);


        // check groups
        foreach($entries[0]['memberof'] as $grps) {

            // is manager, break loop
			foreach ($ldap_manager_groups as $ldap_manager_group)
            	if(strpos($grps, $ldap_manager_group)) { $access = 2; break; }

            // is user
            if(strpos($grps, $ldap_user_group)) $access = 1;
        }
        if($access != 0) {
            $department="empty";
            $mail="empty";
            // establish session variables
            $_SESSION['user'] = $user;
            $_SESSION['access'] = $access;
            $_SESSION['displayname']=$entries[0]['displayname'][0];
            $changedate=date("d-m-Y H:i:s",$entries[0]['pwdlastset'][0]/10000000-11644473600);
            $_SESSION['pwdlastset']=date_diff(new DateTime(), new DateTime($changedate))->days;
            if (isset($entries[0]['department'][0])) $department=$entries[0]['department'][0];
            if (isset($entries[0]['mail'][0])) $mail=$entries[0]['mail'][0];

            $_SESSION['user_id']=check_user($user,$mail,$department,SIDtoString($entries[0]['objectsid'][0]));
            return true;
        } else {
            // user has no rights
            return false;
        }

    } else {
        // invalid name or password
        return false;
    }
}
?>
