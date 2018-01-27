<?php
function update_nginx_config() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
    $query ="SELECT `site_name`,`domain` ,`rhost`,`rport` FROM `proxysites`, `domains` WHERE `proxysites`.`domain_id`=`domains`.`domain_id` AND `domains`.`shared`=1 AND `proxysites`.`status`='Enabled'";
        if ($result = mysqli_query($conn, $query)) 
        {   
            $config="##SELFPORTAL\n ";
            while ($site=mysqli_fetch_array($result)) {
$config .="server {
           listen 80;
           server_name $site[site_name].$site[domain];

           location / {
                proxy_pass http://$site[rhost]:$site[rport]/;
                proxy_set_header   X-Real-IP \$remote_addr;
                proxy_set_header   Host \$http_host;
                proxy_set_header   X-Forwarded-For \$proxy_add_x_forwarded_for;
                }
}
";
            }
            file_put_contents(NGINX_FILE, $config, LOCK_EX);
        }
$out=shell_exec('sudo /usr/sbin/nginx -s reload');
$conn->close();
}

?>
