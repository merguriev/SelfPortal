<?php
#Connect to openstack API
if ($_POST['provider']=="openstack" || !isset($_POST['provider'])) $cli="openstack --os-auth-url ".OS_AUTH_URL." --os-project-id ".OS_PROJECT_ID." --os-project-name ".OS_PROJECT_NAME." --os-user-domain-name ".OS_USER_DOMAIN_NAME." --os-username ".OS_USERNAME." --os-password ".OS_PASSWORD." --os-region-name ".OS_REGION_NAME." --os-interface ".OS_INTERFACE." --os-identity-api-version ".OS_IDENTITY_API_VERSION;
else if ($_POST['provider']=="vsphere") $cli="/usr/bin/perl ".$_SERVER["DOCUMENT_ROOT"]."/perl/";
$cli_flag=false;
#Openstack CLI
function get_vms($cli,$panel,$provider) {
    $vm_user_list=[];
    $vms=json_decode(shell_exec($cli));
    switch ($panel) {
        case "user" :$query="SELECT `title`,`vm_id`,`username`,`exp_date`,`vms`.`user_id`,`email` FROM `vms`,`users` WHERE `vms`.`user_id`='$_SESSION[user_id]' AND `vms`.`user_id`=`users`.`user_id`";break;
        case "admin":$query="SELECT `title`,`vm_id`,`username`,`exp_date`,`vms`.`user_id`,`email` FROM `vms`,`users` WHERE `vms`.`user_id`=`users`.`user_id`";break;
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
          $vm_in_db=mysqli_query($conn,$query) or die("MySQL error: " . mysqli_error($conn) . "<hr>\nQuery: $query");
    }
	$conn->close();
	switch ($provider)
	{
		case "vsphere":
			foreach ($vm_in_db as $item) {
				if (strpos($item['vm_id'], "TERMINATED_VSPHERE")!==false)
				{
					$vm=new stdClass();
					$vm->ID = $item['vm_id'];
					$vm->date = "N/A";
					$vm->owner = $item['username'];
					$vm->extendlimit=0;
					$vm->Status="TERMINATED";
					$vm->Image="TERMINATED";
					$vm->Name=$item['title'];
					$vm_user_list[]=$vm;
				}
				elseif (strpos($item['vm_id'], "FAILURE_VSPHERE")!==false)
				{
					$vm=new stdClass();
					$vm->ID = $item['vm_id'];
					$vm->date = "N/A";
					$vm->owner = $item['username'];
					$vm->extendlimit=0;
					$vm->Status="FAILURE";
					$vm->Image="FAILURE";
					$vm->Name=$item['title'];
					$vm_user_list[]=$vm;
				}
				elseif (strpos($item['vm_id'], "task-")!==false) {
					$vm=new stdClass();
					$vm->ID = -1;
					$vm->date = $item['exp_date'];
					$vm->owner = $item['username'];
					$vm->extendlimit=DAYS_USER_CAN_EXTEND_VM;
					$vm->Status="Building";
					$vm->Image="Deploying";
					$vm->Name=$item['title'];
					$vm_user_list[]=$vm;
				}
    		}		
		break;
		case "openstack":
			foreach ($vm_in_db as $item) {
				if (strpos($item['vm_id'], "TERMINATED_OPENSTACK")!==false)
				{
					$vm=new stdClass();
					$vm->ID = $item['vm_id'];
					$vm->date = "N/A";
					$vm->owner = $item['username'];
					$vm->extendlimit=0;
					$vm->Status="TERMINATED";
					$vm->Image="TERMINATED";
					$vm->Name=$item['title'];
					$vm_user_list[]=$vm;
				}
			}
		break;
	}
    foreach ($vms as $vm){
        $exist=false;
        foreach ($vm_in_db as $item) {
			if ($vm->{'ID'} == $item['vm_id']) {
            	$exist=true;
				$vm->date = $item['exp_date'];
				$vm->owner = $item['username'];
				$vm->extendlimit=DAYS_USER_CAN_EXTEND_VM;
				$vm_user_list[]=$vm;
        	}
			if (strpos($item['vm_id'], "task-")) $exist=true;
    	}
        if ($panel == "admin" and !$exist) $vm_user_list[]=$vm;
    }
	echo json_encode($vm_user_list);
}

function get_free_ip(){
    $cli=$GLOBALS['cli']." floating ip list -f json";
    $ips=shell_exec($cli);
    $ip_array=json_decode($ips);
    foreach ($ip_array as $ip) {
        if ($ip->{'Port'} == null) {
            return $ip->{'Floating IP Address'};
        }
    }
    return allocate_ip();
    }
function allocate_ip(){
    $cli=$GLOBALS['cli']." floating ip create admin_floating_net -f json";
    $ips=shell_exec($cli);
    $ip_array=json_decode($ips);
    return  $ip_array->{'floating_ip_address'};
    }
function add_ip_to_server($server_id,$ip){
    if(!isset($server_id) or !isset($ip)) return "Can't get server_id or ip";
    $state=false;
    $count=0;
    $cli=$GLOBALS['cli']." server add floating ip $server_id $ip";
    while ($count<10){
    usleep(3000000);
    if (get_server_state($server_id) == "ACTIVE") {$state=true; break;}
    else $count++;
    }
    if ($state) shell_exec($cli);
    return $server_id;
}
function get_server_state($id) {
    $cli=$GLOBALS['cli']." server show $id -f json";
    $server=shell_exec($cli);
    $server_info=json_decode($server);
    return $server_info->{'status'};
}
function add_key_to_openstack($user_id,$title,$key){
    $folder="/tmp/";
    $keyname=$title."_".$user_id;
    $file = fopen($folder.$keyname, "w");
    fwrite($file,$key);
    fclose($file);
    $cli =$GLOBALS['cli']." keypair create --public-key ".$folder."$keyname $keyname -f json 2>&1";
    $key=shell_exec($cli);
    $key_json=json_decode($key);
    unlink($folder.$keyname);
    if (!isset($key_json->{'fingerprint'})) return $key;
}
function remove_key_from_openstack($id,$title){
    $keyname=$title."_".$id;
    $cli=$GLOBALS['cli']." keypair delete '$keyname'";
    shell_exec($cli);
}

function create_vsphere_vm ($image_id,$name,$owner){
    $cli=$GLOBALS['cli']."createvm.pl --url ".VMW_SERVER."/sdk/webService --username ".VMW_USERNAME." --password '".VMW_PASSWORD."' --resourcepool '".VMW_RESOURCE_POOL."' --vmtemplate ".$image_id." --vmname '".$name."' --user '".$owner."' --folder '".VMW_VM_FOLDER."' --datastore '".VMW_DATASTORE."' --action createvm --datacenter '".VMW_DATACENTER."'";
	set_time_limit(0);
    $task=shell_exec($cli);
    return $task;
}

function create_server($image,$flavor,$keypair,$name,$owner,$owner_user){
	#OPENSTACK VM CREATION
    $cli=$GLOBALS['cli']." server create --image '$image' --flavor '$flavor' --security-group ".OS_SEC_GRP." --key-name '$keypair' --nic net-id=".OS_NET_ID." --property Owner_id='$owner' --property Owner_name='$owner_user' '$name' -f json 2>&1";
    $server=shell_exec($cli);
    $server_json=json_decode($server);
    if (isset($server_json->{'id'})) {
        return add_ip_to_server($server_json->{'id'},get_free_ip());
    }
    else return $server;
}
function server_db($query) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
    if ($conn->connect_error) {
        write_log(date('Y-m-d H:i:s')." [DB][ERROR] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to query DB: '".$query."', but DB connection error occured: ".$conn->connection_error);
        die("Connection failed: " . $conn->connect_error);
    } else {
        $result=mysqli_query($conn,$query);
            if (!$result)
            {
                write_log(date('Y-m-d H:i:s')." [DB][ERROR] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to query DB: '".$query."', but DB connection error occured: ".mysqli_error($conn) . ". Query: $query");
                die("MySQL error: " . mysqli_error($conn) . "<hr>\nQuery: $query");
            }
    }
    write_log(date('Y-m-d H:i:s')." [DB][INFO] User ".$_SESSION['user']." (id ".$_SESSION['user_id'].") with access level ".$_SESSION['access']." tried to query DB: '".$query."' and suceeded.");
    $conn->close();
    return $result;

}
