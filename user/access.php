<?php
#Check right
function access_level($resourse,$action,$resource_id) {
    if (access_level_internal($resourse,$action,$resource_id))
    {
        $log=date('Y-m-d H:i:s')." [ACCESS][INFO] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to ".$action." ".$resourse;
        if (isset($resource_id)) $log.=" (id ".$resource_id.")";
        write_log ($log.". Access granted.");
        return true;
    }
    else
    {
        $log=date('Y-m-d H:i:s')." [ACCESS][WARNING] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to ".$action." ".$resourse;
        if (isset($resource_id)) $log.=" (id ".$resource_id.")";
        write_log ($log.". Access denied!");
        return false;
    }
}

function access_level_internal($resourse,$action,$resource_id) {
    $access=false;
    if ( $_SESSION['access']==2)  return $access=true;
    switch ($resourse) {
        case "vms":
        case "vm":
            switch ($action) {
                case "createserver":
                case "count":
                case "images":
                case "imagedetails":
                case "flavor":
                case "list":
                case "flavordetails":
                    return $access=true;
                case "terminatevm":
                case "assignip":
                case "stopvm":
				case "clearvm":
                case "info":
                case "startvm":
                case "extend":
                case "rebootvm":
                case "vnc":
                    $query = "SELECT `vm_id` from `vms` WHERE `user_id`='$_SESSION[user_id]' and `vm_id`='$resource_id'";
                    break;
              }
            break;
        case "site":
            switch ($action) {
                case "edit":
                case "switch":
                case "delete":
                case "get":
                    $query = "SELECT `site_name` FROM `proxysites` WHERE  `site_id`= '$resource_id' AND `user_id`='$_SESSION[user_id]'";
                    break;
                case "list":
                    if ($resource_id == $_SESSION['user_id']) return $access=true;
                    break;
                case "add":
                case "count":
                case "check":
                    return $access=true;
                    break;
            }
            break;
        case "domains":
            switch ($action) {
                case "edit":
                    return $access = false;
                case "delete":
                    return $access = false;
                case "add":
                    return $access = false;
                case "list":
                case "get":
                case "check":
                     return $access=true;
                     break;
            }
            break;
        case "blacklist":
            switch ($action) {
                case "add":
                    return $access = false;
                case "delete":
                    return $access = false;
                case "list":
                case "check":
                    return $access = true;
            }
            break;
        case "users":
            switch ($action) {
                case "list":
                    return $access=false;
            }
            break;
        case "keys":
            switch ($action) {
                case "delete":
                        $query = "SELECT `key_id` from `public_keys` WHERE `user_id`='$_SESSION[user_id]' and `key_id`='$resource_id'";
                        break;
                case "list":
                        return $access=true;
                case "add":
                        return $access=true;
            }
            break;
        case "notifications":
            switch ($action) {
                case "list":
                    return $access=true;
            }
            break;
    }
    return is_owner($query);
}
function is_owner($query) {
    $owner=false;
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
            $result=mysqli_query($conn,$query) or die("MySQL error: " . mysqli_error($conn) . "<hr>\nQuery: $query");
            if (mysqli_num_rows($result)!=0) { $owner=true; }

        }
    $conn->close();
    return $owner;
}

function write_log($entry){
    $file = fopen(LOG_FILE, "a");
    $entry=preg_replace("/--os-username .* --os-password .* --os-region-name/","--os-username ******** --os-password ******* --os-region-name",$entry);
    fwrite($file,$entry."\n");
    fclose($file);
}
