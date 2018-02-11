<?php
include_once(dirname(__FILE__).'/../config/config.php');
include_once(dirname(__FILE__).'/../user/user.inc');
include_once(dirname(__FILE__).'/../user/access.php');
include_once(dirname(__FILE__).'/../plugins/phpmailer/PHPMailerAutoload.php');
#Connect to openstack API
$openstack_cli="openstack --os-auth-url ".OS_AUTH_URL." --os-project-id ".OS_PROJECT_ID." --os-project-name ".OS_PROJECT_NAME." --os-user-domain-name ".OS_USER_DOMAIN_NAME." --os-username ".OS_USERNAME." --os-password ".OS_PASSWORD." --os-region-name ".OS_REGION_NAME." --os-interface ".OS_INTERFACE." --os-identity-api-version ".OS_IDENTITY_API_VERSION;
$vsphere_cli="/usr/bin/perl ".dirname(__FILE__)."/../perl/controlvm.pl --url ".VMW_SERVER."/sdk/webService --username ".VMW_USERNAME." --password '".VMW_PASSWORD."'";
$shortopts  = "";
$shortopts .= "v::"; // Необязательное значение

$longopts  = array(
    "action:",     // Обязательное значение
    "optional::",    // Необязательное значение

);
$options = getopt($shortopts, $longopts);
switch ($options['action']){
    case "notify":
        list_notifications();
        break;
    case "disable":
        disable_sites();
        break;
    case "delete":
        delete_sites();
        break;
    case "shutdown_vm":
        shutdown_vm();
        break;
    case "terminate_vm":
        terminate_vm();
        break;
	case "vmupdate":
		vmupdate();
		break;
}

function update_info($task,$user)
{
	$cli="/usr/bin/perl ".dirname(__FILE__)."/../perl/createvm.pl --url ".VMW_SERVER."/sdk/webService --username ".VMW_USERNAME." --password '".VMW_PASSWORD."' --resourcepool '".VMW_RESOURCE_POOL."' --vmtemplate none --vmname ".$task." --user ".$user." --folder '".VMW_VM_FOLDER."' --datastore '".VMW_DATASTORE."' --action updateinfo";
	$result=shell_exec($cli);
	if ($result!=0)
	{
		if ($result==1)
		{
			return 1;
		}
		elseif (preg_match('/\s/',$result))
		{
			#$query="DELETE FROM vms where vm_id='".$task."'";
			#$query="UPDATE `vms` SET `vm_id`='FAILURE_".$task."' where vm_id='".$task."'";
			return $result;
		}
		else
		{
			$query="UPDATE vms SET vm_id='".$result."' where vm_id='".$task."'";
			$toreturn=$result;
		}
		sql_query($query);

		return $toreturn;
	}
	return -1;
}

function vmupdate()
{
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
	$query="SELECT `vm_id`,`username`,`exp_date`,`vms`.`user_id`,`email`,`title` FROM `vms`,`users` WHERE `vms`.`user_id`=`users`.`user_id` and `vm_id` like '%task%'";
	if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    } else {
          $vm_in_db=mysqli_query($conn,$query) or die("MySQL error: " . mysqli_error($conn) . "<hr>\nQuery: $query");
        }
	$success=true;
    $conn->close();
	foreach ($vm_in_db as $item) {
				$error = update_info($item['vm_id'],$item['username']);
				if ($error==1 || preg_match('/\s/',$error))
				{
					$returneddebug=vmdebug($item['vm_id'],$item['title'],$item['username']);
					if ($returneddebug==0) send_notification ($item['email'],'Hi! Your VM called "'.$item['title'].'" is ready.<br><hr>Sincerely yours, SelfPortal. In case of any errors - please, contact your system administrators via '.MAIL_ADMIN);
					else {send_notification (MAIL_ADMIN,'User with id '.$item['user_id'].' have tried to create VM (VSphere provider, i guess) with name '.$item['title'].', but error occured: '.$error);
					send_notification ($item['email'],'Hi! There was something strange, when we\'ve to create VM called "'.$item['title'].'" for you. Unfortunately, an error occured: '.$error.'. If you know how to fix it - good, otherwise - please, contact your system administrators via '.MAIL_ADMIN); $success=false;}	
				}
				elseif ($error!=-1)
				{
					send_notification ($item['email'],'Hi! Your VM called "'.$item['title'].'" is ready.<br><hr>Sincerely yours, SelfPortal. In case of any errors - please, contact your system administrators via '.MAIL_ADMIN);
				}
				else $success=false;
	}
	if ($success) shell_exec("sudo crontab -l -u root | grep -v '/modules/tasks.php --action vmupdate' | sudo crontab -u root -");
}

function vmdebug($task,$vmname,$user)
{
	$cli="/usr/bin/perl ".dirname(__FILE__)."/../perl/listvms.pl --url ".VMW_SERVER."/sdk/webService --username ".VMW_USERNAME." --password '".VMW_PASSWORD."' --vmalias ".$vmname." --folder '".VMW_VM_FOLDER."' --datacenter '".VMW_DATACENTER."'";
	$result=shell_exec($cli);
	if (!empty($result))
	{
		$query="UPDATE `vms` SET `vm_id`='".$result."' where `vm_id`='".$task."'";
		sql_query($query);
		$cli="/usr/bin/perl ".dirname(__FILE__)."/../perl/createvm.pl --url ".VMW_SERVER."/sdk/webService --username ".VMW_USERNAME." --password '".VMW_PASSWORD."' --resourcepool '".VMW_RESOURCE_POOL."' --vmtemplate none --vmname ".$result." --user ".$user." --folder '".VMW_VM_FOLDER."' --datastore '".VMW_DATASTORE."' --action rename";
		$result = shell_exec($cli);
		if (!empty($result)) { 
			send_notification(MAIL_ADMIN,"Hello, Administrator! Something went wrong when user named ".$user." tried to create VM '".$vmname."' in VSphere. I was not able to rename a VM, so i've deleted it. Please, check it.");
			$query="UPDATE `vms` set `vm_id`='FAILURE_".$task."' where `vm_id`='".$task."'";
			sql_query($query);
			$cli="/usr/bin/perl ".dirname(__FILE__)."/../perl/controlvm.pl --url ".VMW_SERVER."/sdk/webService --username ".VMW_USERNAME." --password '".VMW_PASSWORD."' --vmname ".$result." --action Destroy";
			$result = shell_exec($cli);
			return 0;
		}
		else return 1;
	}
	else {
		send_notification(MAIL_ADMIN,"Hello, Administrator! Something went wrong when user named ".$user." tried to create VM '".$vmname."' in VSphere. I was not able to find task '".$task."' or a vm by it's name. Please, check it.");
		$query="UPDATE `vms` set `vm_id`='FAILURE_".$task."' where `vm_id`='".$task."'";
		sql_query($query);
		return 1;
	}
	return 0;
}

function sql_query($query){

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
    // Check connection
    if ($conn->connect_error) {
        write_log(date('Y-m-d H:i:s')." [CRON][INFO] Query DB: '".$query."' but connection failed: ".mysqli_error($conn));
        die("Connection failed: " . $conn->connect_error);
    } else {
        $result=mysqli_query($conn,$query);
        if (!$result){
            write_log(date('Y-m-d H:i:s')." [CRON][INFO] Query DB: '".$query."' but error occured: ".mysqli_error($conn));
            die("MySQL error: " . mysqli_error($conn) . "<hr>\nQuery: $query");
        }
    }
    $conn->close();
    write_log(date('Y-m-d H:i:s')." [CRON][INFO] Query DB: '".$query."' and suceeded.");
    return $result;
}
//Check items status and send notification
function list_notifications(){
    $query="SELECT `site_name`,`domain`,`stop_date`,`email`,`days` FROM `users`,`proxysites`,`domains`, (SELECT `site_id`, DATEDIFF(stop_date,CURDATE()) as days FROM `proxysites`) as days WHERE (days BETWEEN ".DAYS_BEFORE_DELETE." AND ".DAYS_BEFORE_DISABLE.") AND  `proxysites`.`domain_id`=`domains`.`domain_id` AND `proxysites`.`user_id`=`users`.`user_id` AND `days`.`site_id`= `proxysites`.`site_id` ORDER by `stop_date`";
    $sites=sql_query($query);
    $emails=[];
    while ($site=mysqli_fetch_array($sites)){
        $siteitem="$site[site_name]".".$site[domain]";
        if(!isset($emails[$site['email']]['site_disable']) and ($site['days']>=0) ) {$emails[$site['email']]['site_disable'] ="<br>This site(s) will be disabled:<br>";}
        if(!isset($emails[$site['email']]['site_delete']) and ($site['days']<0)) {$emails[$site['email']]['site_delete']="<br>This site(s) will be deleted:<br>";}
        if($site['days']>0) $emails[$site['email']]['site_disable'] .="<li><b>$site[stop_date]</b> - <a href="."\"http://$siteitem/\">$siteitem"."</a></li>";
        if($site['days']==0) $emails[$site['email']]['site_disable'] .="<li><b>TODAY AT 23.59</b> - <a href="."\"http://$siteitem/\">$siteitem"."</a></li>";
        if($site['days']<0 && abs($site['days'])<abs(DAYS_BEFORE_DELETE)) {
            $date= new DateTime($site['stop_date']);
            $date->add(new DateInterval('P'.abs(DAYS_BEFORE_DELETE).'D'));
            $site_date=$date->format('Y-m-d');
            $emails[$site['email']]['site_delete'] .="<li><b>$site_date</b> - <a href="."\"http://$siteitem/\">$siteitem"."</a></li>";
        }
        if ($site['days']<0 && abs($site['days'])>=abs(DAYS_BEFORE_DELETE))
        {
            $emails[$site['email']]['site_delete'] .="TODAY AT 23.59 - <a href="."\"http://$siteitem/\">$siteitem"."</a></li>";
        }

    }
    $query="SELECT `title`,`email`,`days`,`exp_date` FROM `vms`,`users`, (SELECT `vm_id`,DATEDIFF(exp_date,CURDATE()) as days FROM `vms`) as days WHERE (days BETWEEN ".DAYS_BEFORE_DELETE." AND ".DAYS_BEFORE_DISABLE.") AND `vms`.`user_id`=`users`.`user_id` AND `days`.`vm_id`= `vms`.`vm_id` ORDER by `exp_date`";
    $vms=sql_query($query);
    while ($vm=mysqli_fetch_array($vms)){
        if(!isset($emails[$vm['email']]['vm_disable']) and ($vm['days']>=0)) {$emails[$vm['email']]['vm_disable'] = "<br>This Virtual machine(s) will be disabled:<br>";}
        if(!isset($emails[$vm['email']]['vm_delete']) and ($vm['days']<0)) {$emails[$vm['email']]['vm_delete'] = "<br>This Virtual machine(s) will be deleted:<br>";}
        if($vm['days']>0) $emails[$vm['email']]['vm_disable'] .="<li><b>$vm[exp_date]</b> - $vm[title]</li>";
        if($vm['days']==0) $emails[$vm['email']]['vm_disable'] .="<li><b>TODAY AT 23:59</b> - $vm[title]</li>";
        if($vm['days']<0 && abs($vm['days'])<abs(DAYS_BEFORE_DELETE))  {
            $date= new DateTime($vm['exp_date']);
            $date->add(new DateInterval('P'.abs(DAYS_BEFORE_DELETE).'D'));
            $vm_date=$date->format('Y-m-d');
            $emails[$vm['email']]['vm_delete'] .="<li><b>$vm_date </b> - $vm[title]</li>";};
        if($vm['days']<0 && abs($vm['days'])>=abs(DAYS_BEFORE_DELETE))
           {
               $emails[$vm['email']]['vm_delete'] .="<li><b>TODAY AT 23:59</b> - $vm[title] </li>";
           }
    }
    foreach ($emails as $email => $notification){
        $body="Notification from SELFPORTAL <br>".$notification['site_disable'].$notification['site_delete'].$notification['vm_disable'].$notification['vm_delete'];
        send_notification($email,$body);
    }
}

function send_notification($email,$body){

    $mail = new PHPMailer;
    //$mail->SMTPDebug = 3;                               // Enable verbose debug output

    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = MAIL_SERVER;  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = MAIL_USER;                 // SMTP username
    $mail->Password = MAIL_PASS;                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to

    $mail->setFrom(MAIL_USER, 'SELFPORTAL');

    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = 'SELFPORTAL';

    $mail->addAddress($email);
    $mail->Body    = $body;
    if(!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
   #     echo 'Message has been sent';
    }
}
//Check expired items and change new status
function disable_sites(){
    $query= "UPDATE `proxysites`SET status='Disabled' WHERE `stop_date` < CURDATE()";
    sql_query($query);
    usleep(1000);
    update_nginx_config();
}
//Delete expired items and change new status
function delete_sites(){
    $query= "DELETE FROM `proxysites` WHERE DATEDIFF(stop_date,CURDATE()) < ".DAYS_BEFORE_DELETE;
    sql_query($query);
    usleep(1000);
    update_nginx_config();
}
function shutdown_vm(){
    $query= "UPDATE `vms` set `status`='Disabled' where `exp_date` < CURDATE()";
    $vms=sql_query($query);
    $query= "SELECT `vm_id` FROM `vms` WHERE `exp_date` < CURDATE()";
    $vms=sql_query($query);
    usleep(1000);
    foreach ($vms as $vm) {
        $cli=$GLOBALS['openstack_cli']." server stop '".$vm['vm_id']."' 2>&1";
        $cli_result=shell_exec($cli);
        if (isset($cli_result))
		{
			$cli=$GLOBALS['vsphere_cli']."--vmname ".$vm['vm_id']." --action Stop";
        	$cli_result2=shell_exec($cli);
			if (isset($cli_result2)) write_log(date('Y-m-d H:i:s')." [CRON][SHUTDOWN][ERROR] Cron tried to query both VSphere and OpenStack: '".$cli."', but error occured. Openstack: ".$cli_result.". VSphere: ".$cli_result2);
			else write_log(date('Y-m-d H:i:s')." [VSPHERE][CRON][SHUTDOWN][INFO] Cron tried to query VSphere: '".$cli."' and suceeded.");
		}
        else write_log(date('Y-m-d H:i:s')." [OPENSTACK][CRON][SHUTDOWN][INFO] Cron tried to query OpenStack: '".$cli."' and suceeded.");
    }
}
function terminate_vm(){
    $query= "SELECT `vm_id` FROM `vms` WHERE DATEDIFF(exp_date,CURDATE()) < ".DAYS_BEFORE_DELETE;
    $vms=sql_query($query);
    usleep(1000);
    foreach ($vms as $vm) {
        $cli=$GLOBALS['openstack_cli']." server delete ".$vm['vm_id']." 2>&1";
        $cli_result=shell_exec($cli);
		$query="DELETE FROM `vms` WHERE `vm_id`= '".$vm['vm_id']."'";
        if (isset($cli_result))
		{
			echo $cli_result;
			$cli=$GLOBALS['vsphere_cli']."--vmname ".$vm['vm_id']." --action Destroy";
        	$cli_result2=shell_exec($cli);
			if (isset($cli_result2)) write_log(date('Y-m-d H:i:s')." [CRON][TERMINATE][ERROR] Cron tried to query both VSphere and OpenStack: '".$cli."', but error occured. Openstack: ".$cli_result.". VSphere: ".$cli_result2);
			else { write_log(date('Y-m-d H:i:s')." [VSPHERE][CRON][TERMINATE][INFO] Cron tried to query VSphere: '".$cli."' and suceeded."); sql_query($query); }
		}
        else 
		{
			write_log(date('Y-m-d H:i:s')." [OPENSTACK][CRON][TERMINATE][INFO] Cron tried to query OpenStack: '".$cli."' and suceeded.");
        	sql_query($query);
		}
    }
}
