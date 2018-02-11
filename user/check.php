<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 'on');*/
ini_set('session.cookie_httponly', '1');
session_start();
if (!isset($_SESSION['user_id'])) die (http_response_code(401));
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';
include_once ("user.inc");
include_once ("providers.php");
include_once ("access.php");
    if (!@access_level($_POST['type'], $_POST['action'],$_POST['id'])) die (http_response_code(401));
    $flag=false;
    $multiquery=false;
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
    switch ($_POST['type']) {
        case "vm":
        case "vms":
        $cli_flag=false;
        switch ($_POST['provider']) {
            case "openstack":
                switch ($_POST['action']) {
                    case "createserver":
                        $server_id=create_server($_POST['name']['image'],$_POST['name']['flavor'],$_POST['name']['keypair'],$_POST['name']['name'],$_SESSION['user_id'],$_SESSION['user']);
                        if (isset($server_id)) {
                            if (!preg_match('/\s/',$server_id)){
                            $query="INSERT INTO `vms` VALUES ('$server_id','".$_SESSION['user_id']."','".$_POST['name']['name']."','".$_POST['name']['date']."','Enabled')";
                            }
                            else {
                                ob_start();
                                var_dump($_POST);
                                $postdump = ob_get_clean();
                                include_once $_SERVER['DOCUMENT_ROOT'].'/modules/tasks.php';
                                ob_start();
                                write_log(date('Y-m-d H:i:s')." [OPENSTACK][ERROR] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to create VM in openstack. POST: ".$postdump.". OpenStack message: ".$server_id);
                                send_notification(MAIL_ADMIN,"Hello, Administrator! Something went wrong when user with id ".$_SESSION['user_id']." tried to create VM in OpenStack. Please, check it. Here is the details of his POST query:<pre> ".$postdump."</pre> And also here is the error, returned by OpenStack: ".$server_id);
                                ob_get_clean();
                                echo preg_replace("(\(.*\))","",$server_id);
                                return;
                            }
                        }
                        else {
							ob_start();
                            var_dump($_POST);
                            $postdump = ob_get_clean();
                            include_once $_SERVER['DOCUMENT_ROOT'].'/modules/tasks.php';
                            write_log(date('Y-m-d H:i:s')." [OPENSTACK][ERROR] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to create VM in openstack. POST: ".$postdump);
                            send_notification(MAIL_ADMIN,"Hello, Administrator! Something went wrong when user with id ".$_SESSION['user_id']." tried to create VM in openstack. Please, check it. Here is the details of his POST query: ".$postdump);
                        }
                        break;
                    case "list": $cli .=" server list -f json";
                        if (($_POST['panel']) == "user" || ($_POST['panel']) == "admin") get_vms($cli,$_POST['panel'],$_POST['provider']);
                        break;
                    case "info": $cli .=" server show '$_POST[id]' -f json";
                        $cli_flag=true;
                        break;
                    case "stopvm": $cli .=" server stop '$_POST[id]'";
                        $cli_flag=true;
                        $query="UPDATE `vms` SET `status`='Disabled' where `vm_id`='$_POST[id]'";
                        break;
                    case "startvm": $cli .=" server start '$_POST[id]'";
                        $query="UPDATE `vms` SET `status`='Enabled' where `vm_id`='$_POST[id]'";
                        $cli_flag=true;
                        break;
                    case "terminatevm": $cli .=" server delete '$_POST[id]'";
                        $cli_flag=true;
                        $query="UPDATE `vms` set `vm_id`='TERMINATED_OPENSTACK".$_POST['id']."' WHERE `vm_id`= '$_POST[id]'";
                        break;
					case "clearvm":
						$query="DELETE FROM `vms` WHERE `vm_id`= '".$_POST['id']."'";
                        break;
                    case "rebootvm": $cli .=" server reboot '$_POST[id]'";
                        $query="UPDATE `vms` SET `status`='Enabled' where `vm_id`='$_POST[id]'";
                        $cli_flag=true;
                        break;
                    case "vnc": $cli .=" console url show '$_POST[id]' -f json";
                        $cli_flag=true;
                        break;
                    case "images": $cli .=" image list -f json";
                        $cli_flag=true;
                        break;
                    case "imagedetails": $cli .=" image show '$_POST[id]' -f json";
                        $cli_flag=true;
                        break;
                    case "flavor": $cli .=" flavor list -f json";
                        $cli_flag=true;
                        break;
                    case "flavordetails": $cli .=" flavor show '$_POST[id]' -f json";
                        $cli_flag=true;
                        break;
                    case "extend":
                        $query="UPDATE vms set exp_date=DATE_ADD(vms.exp_date, INTERVAL '$_POST[days]' DAY) WHERE vm_id='$_POST[id]'";
                        break;
                    case "count":
                        $query = "SELECT COUNT(vm_id) FROM `vms` WHERE user_id='".$_SESSION['user_id']."'";
                        $result=server_db($query);
                        break;
                    case "assignip":
                        add_ip_to_server($_POST['id'],get_free_ip());
                        break;
                }
				break;
			case "vsphere":
				switch ($_POST['action']) {
                    case "createserver":
                        $server_id=create_vsphere_vm($_POST['name']['image'],$_POST['name']['name'],$_SESSION['user']);
                        if (isset($server_id)) {
                            if (!preg_match('/\s/',$server_id)){
                            $query="INSERT INTO `vms` VALUES ('$server_id','".$_SESSION['user_id']."','".$_POST['name']['name']."','".$_POST['name']['date']."','Enabled')";
							shell_exec ('(sudo crontab -l -u root | grep -v "/modules/tasks.php --action vmupdate"; echo "* * * * * /usr/bin/php '.$_SERVER['DOCUMENT_ROOT'].'/modules/tasks.php --action vmupdate") | sudo crontab -u root -');
                            }
                            else {
                                ob_start();
                                var_dump($_POST);
                                $postdump = ob_get_clean();
                                include_once $_SERVER['DOCUMENT_ROOT'].'/modules/tasks.php';
                                ob_start();
                                write_log(date('Y-m-d H:i:s')." [VSphere][ERROR] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to create VM in openstack. POST: ".$postdump.". VSphere message: ".$server_id);
                                send_notification(MAIL_ADMIN,"Hello, Administrator! Something went wrong when user with id ".$_SESSION['user_id']." tried to create VM in VSphere. Please, check it. Here is the details of his POST query:<pre> ".$postdump."</pre> And also here is the error, returned by VSphere: ".$server_id);
                                ob_get_clean();
								echo "VM cannot be created. Reason unknown. Admin team is already notified about your problem. Keep calm and wait for help.";
                                return;
                            }
                        }
                        else {
							ob_start();
                            var_dump($_POST);
                            $postdump = ob_get_clean();
                            include_once $_SERVER['DOCUMENT_ROOT'].'/modules/tasks.php';
                            write_log(date('Y-m-d H:i:s')." [VSphere][ERROR] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to create VM in VSphere. POST: ".$postdump);
                            send_notification(MAIL_ADMIN,"Hello, Administrator! Something went wrong when user with id ".$_SESSION['user_id']." tried to create VM in VSphere. Please, check it. Here is the details of his POST query: ".$postdump.". Most possible reason: name collision.");
							echo "VM cannot be created. Check if there is no VM with the same name created.";
                        }
                        break;
                    case "list": $cli .="listvms.pl --url ".VMW_SERVER."/sdk/webService --folder '".VMW_VM_FOLDER."' --username ".VMW_USERNAME." --password '".VMW_PASSWORD."' --datacenter '".VMW_DATACENTER."'";;
                        if (($_POST['panel']) == "user" || ($_POST['panel']) == "admin") get_vms($cli,$_POST['panel'],$_POST['provider']);
                        break;
                    case "info": $cli .="listvms.pl --vmname '$_POST[id]' --datacenter '".VMW_DATACENTER."'";
                        $cli_flag=true;
                        break;
                    case "stopvm": $cli .="controlvm.pl --vmname '$_POST[id]' --action Stop";
                        $cli_flag=true;
                        $query="UPDATE `vms` SET `status`='Disabled' where `vm_id`='$_POST[id]'";
                        break;
                    case "startvm": $cli .="controlvm.pl --vmname '$_POST[id]' --action Start";
                        $query="UPDATE `vms` SET `status`='Enabled' where `vm_id`='$_POST[id]'";
                        $cli_flag=true;
                        break;
                    case "terminatevm": $cli .="controlvm.pl --vmname '$_POST[id]' --action Destroy";
                        $cli_flag=true;
                        $query="UPDATE `vms` set `vm_id`='TERMINATED_VSPHERE_".$_POST['id']."' WHERE `vm_id`= '$_POST[id]'";
                        break;
					case "clearvm":
						$query="DELETE FROM `vms` WHERE `vm_id`= '".$_POST['id']."'";
                        break;	
                    case "rebootvm": $cli .="controlvm.pl --vmname '$_POST[id]' --action Restart";
                        $query="UPDATE `vms` SET `status`='Enabled' where `vm_id`='$_POST[id]'";
                        $cli_flag=true;
                        break;
                    case "vnc": $cli .="vnc.pl -vmname ".$_POST[id];
                        $cli_flag=true;
                        break;
                    case "images": $cli .="listvms.pl --folder '".VMW_TEMPLATE_FOLDER."' --datacenter '".VMW_DATACENTER."'";
                        $cli_flag=true;
                        break;
                    case "flavor": echo json_encode(array(), JSON_FORCE_OBJECT); return;
                        break;
                    case "extend":
                        $query="UPDATE vms set exp_date=DATE_ADD(vms.exp_date, INTERVAL '$_POST[days]' DAY) WHERE vm_id='$_POST[id]'";
                        break;
                    case "count":
                        $query = "SELECT COUNT(vm_id) FROM `vms` WHERE user_id='".$_SESSION['user_id']."'";
                        $result=server_db($query);
                        break;
                }
				$cli .=" --url ".VMW_SERVER."/sdk/webService --username ".VMW_USERNAME." --password '".VMW_PASSWORD."'";
        }
        break;
        case "keys":
            switch ($_POST['action']){
                case "list":
                    $query="SELECT `key_id`,`title`,`user_id` from `public_keys` ";
                    $query .= "WHERE `user_id`='".$_SESSION['user_id']."'";
                    break;
                case "add":
                    $query="INSERT INTO `public_keys` VALUES (NULL,'".$_SESSION['user_id']."','".$_POST['name']['title']."','".$_POST['name']['key']."')";
                    $openstack_query_result=add_key_to_openstack($_SESSION['user_id'],$_POST['name']['title'],$_POST['name']['key']);
                    if (isset($openstack_query_result)) {
                        echo preg_replace("(\(.*\))","",$openstack_query_result);
                        write_log(date('Y-m-d H:i:s')." [KEYS][ERROR] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to add SSH key to his profile, but failed. Openstack message: ".$openstack_query_result);
                        return;
                            }
                    break;
                case "delete":
                    $query="DELETE FROM `public_keys` WHERE `key_id`='".$_POST['id']."'";
                    remove_key_from_openstack($_SESSION['user_id'],$_POST['title']);
                    break;
            }
            break;
        case "notifications":
            switch ($_POST['action']){
                case "list":
                    $query="SELECT `title`,`exp_date`,`days`,`status`,'VM' FROM `vms`,`users`, (SELECT `vm_id`,DATEDIFF(exp_date,CURDATE()) as days FROM `vms`) as days WHERE (days BETWEEN ".DAYS_BEFORE_DELETE." AND ".DAYS_BEFORE_DISABLE.") AND `vms`.`user_id`=`users`.`user_id` AND `days`.`vm_id`= `vms`.`vm_id` AND `users`.`user_id`=".$_SESSION['user_id']." AND `vms`.`vm_id` not like 'TERMINATED%' AND `vms`.`vm_id` not like 'FAILURE%' UNION SELECT concat(`site_name`,'.',`domain`),`stop_date`,`days`,`status`,'site' FROM `users`,`proxysites`,`domains`, (SELECT `site_id`, DATEDIFF(stop_date,CURDATE()) as days FROM `proxysites`) as days WHERE (days BETWEEN ".DAYS_BEFORE_DELETE." AND ".DAYS_BEFORE_DISABLE.") AND `proxysites`.`domain_id`=`domains`.`domain_id` AND `proxysites`.`user_id`=`users`.`user_id` AND `days`.`site_id`= `proxysites`.`site_id` AND `users`.`user_id`=".$_SESSION['user_id']." ORDER by `exp_date`";
                    break;
            }
            break;
	    case "domains":
		switch ($_POST['action']) {
			case "delete":
                $query = "DELETE FROM `domains` WHERE `domain_id`= '".$_POST['id']."'; DELETE from `proxysites` where `domain_id`='".$_POST['id']."'";
                $flag=true;
                $multiquery=true;
                break;
            case "add":
                if($_POST['id']=="true") $check=1;
                    else $check=0;
                $query = "INSERT INTO `domains` VALUES (NULL,'$_POST[name]',$check)";
				break;
            case "get":
                $query = "SELECT `domain`,`shared` FROM `domains` WHERE `domain_id`= '$_POST[id]' ";
                break;
            case "edit":
                if (isset($_POST['publish'])) {
                    if($_POST['publish']=="true") $check=1;
                        else $check=0;
                }
                else $check=0;
                $query = "UPDATE `domains` set `domain`='".$_POST['name']['name']."',`shared`='$check' WHERE `domain_id`= '$_POST[id]' ";
                $flag=true;
				break;
            case "list":
                $query = "SELECT * FROM `domains`";
                if ($_POST['id']=="shared") $query.=" WHERE `shared`='1'";
                break;
		}
          break;
      case "users":
            switch ($_POST['action']){
            case "list":
                $query = "SELECT `user_id`,`username`,`email`,`department` FROM `users`";
                break;
                }
      break;
      case "blacklist":
            switch ($_POST['action']){
            case "list":
                $query = "SELECT `ip_id`,INET_NTOA(`IP`),`Mask` FROM `blacklist`";
                break;
            case "add":
                if (explode("/",$_POST['name'])[1]) $mask=explode("/",$_POST['name'])[1];
                else $mask=32;
                $query = "INSERT INTO `blacklist` VALUES (NULL,INET_ATON('".explode("/",$_POST['name'])[0]."'),'".$mask."')";
                break;
            case "delete":
                $query = "DELETE FROM `blacklist` WHERE `ip_id`= '$_POST[id]' ";
                break;
            case "check":
                $query="CALL `checkip`('".$_POST['proxy']."')";
                break;
            }
      break;
      case "site":
          switch ($_POST['action']){
              case "add":
                  $query ="CALL `addsite`('".$_POST['name']['name']."','".$_POST['name']['host']."','".$_POST['name']['port']."',".$_SESSION['user_id'].",".$_POST['name']['proxy'].",'".$_POST['name']['date']."')";
                  $flag=true;
                  break;
              case "list":
                  $query = "SELECT `site_id`, `site_name`, `domain`,`rhost`,`rport`,`stop_date`, `status`, `username`, `proxysites`.`domain_id` FROM `proxysites`, `domains`,`users` WHERE `proxysites`.`domain_id`=`domains`.`domain_id` AND `proxysites`.`user_id`=`users`.`user_id` ";
                  if ($_POST['id']!=null and $_POST['id'] == $_SESSION['user_id']) $query .= "AND `proxysites`.`user_id`='".$_POST['id']."'";
                  break;
              case "delete":
                  $query = "DELETE FROM `proxysites` WHERE `site_id`= '$_POST[id]'";
                  $flag=true;
                  break;
              case "get":
                  $query = "SELECT `site_id`, `site_name`, `domain`,`rhost`,`rport`,`stop_date`, `status`, `username`,  `proxysites`.`domain_id`  FROM `proxysites`, `domains`,`users` WHERE `proxysites`.`domain_id`=`domains`.`domain_id` AND `proxysites`.`user_id`=`users`.`user_id` AND `proxysites`.`site_id`=".$_POST['id'];
                  break;
              case "switch":
                  $query = "SET @A= (SELECT status from proxysites where site_id=" . $_POST['id'] . ");";
                  $query .= "UPDATE proxysites set status= IF (@A='Enabled','Disabled','Enabled') where site_id=" . $_POST['id'] . ";";
                  $flag=true;
                  $multiquery=true;
                  break;
              case "edit":
                  $query ="CALL `updatesite`('".$_POST['name']['name']."','".$_POST['name']['host']."','".$_POST['name']['port']."',".$_POST['name']['proxy'].",'".$_POST['name']['date']."','" . $_POST['id'] . "')";
                  $flag=true;
                  break;
              case "check":
                  $query = "SELECT `site_name` FROM `proxysites` WHERE `site_name` = '".$_POST['name']."' AND `domain_id`='".$_POST['proxy']."'";
                  break;
              case "count":
                  $query = "SELECT COUNT(site_id) FROM `proxysites` WHERE status='Enabled' AND user_id='".$_SESSION['user_id']."'";
                  break;
                       }
      break;
	}
	if (isset($query)) {
        write_log(date('Y-m-d H:i:s')." [DB][INFO] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." is trying to query DB: '".$query."'.");
        if ($conn->connect_error) {
            write_log(date('Y-m-d H:i:s')." [DB][ERROR] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to query DB: '".$query."', but DB connection error occured: ".$conn->connection_error);
            die("Connection failed: " . $conn->connect_error);
        } else {
        if ($multiquery)
        {
	    $result=mysqli_multi_query($conn,$query);
	    if (!$result)
            {
                echo "false";
                $conn->close();
                write_log(date('Y-m-d H:i:s')." [DB][ERROR] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to query DB: '".$query."', but DB connection error occured: ".mysqli_error($conn));
                die("MySQL error: " . mysqli_error($conn) . "<hr>\nmultiquery: $query");
            }
            else echo "true"; 
            usleep(1000);
        }
        else {
		$result=mysqli_query($conn,$query);
        if (!$result) {
            write_log(date('Y-m-d H:i:s')." [DB][ERROR] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to query DB: '".$query."', but DB connection error occured: ".mysqli_error($conn));
            die("MySQL error: " . mysqli_error($conn) . "<hr>\nQuery: $query");
        }
        if ($result!="FALSE") echo json_encode(mysqli_fetch_all($result,MYSQLI_ASSOC));
	     }
        }
        write_log(date('Y-m-d H:i:s')." [DB][INFO] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to query DB: '".$query."' and suceeded.");
        $conn->close();
        if ($flag) $out=update_nginx_config();
    }
    if ($cli_flag) {
       $cli_result=shell_exec($cli);
        if (is_null(json_decode($cli_result)) && isset($cli_result)) write_log(date('Y-m-d H:i:s')." ["+$_POST['provider']+"][ERROR] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to query "+$_POST['provider']+": '".$cli."', but error occured: ".$cli_result);
        else write_log(date('Y-m-d H:i:s')." ["+$_POST['provider']+"][INFO] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to query "+$_POST['provider']+": '".$cli."' and suceeded.");
        echo $cli_result;
    }

?>
