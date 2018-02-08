#!/bin/bash

if [ "$EUID" -ne 0 ]
  then echo "Installation should be run as root. Are you root?"
  exit
fi

apt install nginx php php-curl php-json php-ldap php-mysqli php-xml mysql-server make gcc libssl-dev lib32z1 build-essential gcc uuid uuid-dev perl libssl-dev perl-doc liburi-perl libxml-libxml-perl libcrypt-ssleay-perl -y
cpan install CPAN
cpan reload cpan
cpan install JSON
cpan install YAML
cpan install LWP::Protocol::https
cpan install IO::Socket::SSL
cpan install Switch
cpan install Socket6

mkdir -p /var/log/selfportal
chmod 750 /var/log/selfportal

cat >/etc/logrotate.d/selfportal <<EOL
/var/log/selfportal/*log {
        weekly
        missingok
        rotate 12
        compress
        delaycompress
        notifempty
        create 0600 www-data www-data
} 
EOL

touch /var/www/selfportal/config/config.php
cat >/var/www/selfportal/config/config.php <<EOL
<?php
#Config logs
define('LOG_FILE',"/var/log/selfportal/selfportal.log");
EOL

mkdir -p /var/www/selfportal
cp * /var/www/selfportal -R

echo "Please, provide me with a credentials for your mysql database (root/empty by default)."
while [[ -z $mysqlname ]]
do
  read -p "Enter username: " mysqlname
done
read -s -p "Enter password: " mysqlpasswd

if [ -z "$mysqlpasswd" ]
then
	mysql -u $mysqlname < my_backup.sql
else
	mysql -u $mysqlname -p$mysqlpasswd < /var/www/selfportal/db/portal.sql
fi

apt install python-pip
pip install python-openstackclient

cat >/var/www/selfportal/config/config.php <<EOL
<?php
#Config DB
define ('DB_USER', "$mysqlname");
define ('DB_PASSWORD', "$mysqlpasswd");
define ('DB_DATABASE', "portal");
define ('DB_HOST', "localhost");
#Config LDAP
EOL

echo "Now you can connect to SelfPortal using LDAP Auth only. So we need to configure it now: "

#TODO: CONFIG LDAP
#TODO: CONFIG MAIL
#Config OpenStack

read -p "Do you want to configure SelfPortal to work with OpenStack?" servername
if [[ $REPLY =~ ^[Yy]$ ]]
then
	echo "Well then. Now we will need you to provide us with some information about your installation:"
	
	while [[ -z "$openstackserver" ]]
	do
		read -p "Enter OpenStack endpoint (like https://openstack.example.com:5000/v3): " openstackserver
	done
    cat >/var/www/selfportal/config/config.php <<EOL
	define('OS_AUTH_URL',"$openstackserver");
	EOL
	
	while [[ -z "$openstackprojectid" ]]
	do
		read -p "Enter OpenStack project id" openstackprojectid
	done
	cat >/var/www/selfportal/config/config.php <<EOL
    define('OS_PROJECT_ID',"$openstackprojectid");
	EOL
	
	while [[ -z "$openstackprojectname" ]]
	do
		read -p "Enter OpenStack project name" openstackprojectname
	done
	cat >/var/www/selfportal/config/config.php <<EOL
    define('OS_PROJECT_NAME',"$openstackprojectname");
	EOL

	while [[ -z "$openstackuserdomain" ]]
	do
		read -p "Enter OpenStack user domain name (like Default): " openstackuserdomain
	done
	cat >/var/www/selfportal/config/config.php <<EOL
    define('OS_USER_DOMAIN_NAME',"$openstackuserdomain");
	EOL
	
	while [[ -z "$openstackuser" ]]
	do
		read -p "Enter OpenStack username we can use to deploy VMs: " openstackuser
	done
	cat >/var/www/selfportal/config/config.php <<EOL
    define('OS_USERNAME',"$openstackuser");
	EOL
 	
	while [[ -z "$openstackpassword" ]]
	do
		read -s -p "Enter password" openstackpassword
	done
	cat >/var/www/selfportal/config/config.php <<EOL
    define('OS_PASSWORD',"$openstackpassword");
	EOL
	
	while [[ -z "$openstackregion" ]]
	do
		read -p "Enter OpenStack region name (like RegionOne): " openstackregion
	done
	cat >/var/www/selfportal/config/config.php <<EOL
    define('OS_REGION_NAME',"$openstackpassword");
	EOL
	
	while [[ -z "$openstackinterface" ]]
	do
		read -p "Enter interface (like public): " openstackinterface
	done
	cat >/var/www/selfportal/config/config.php <<EOL
    define('OS_INTERFACE',"$openstackinterface");
	EOL

	while [[ -z "$openstackidentity" ]]
	do
		read -p "Enter OpenStack identity ip version (like 3) " openstackidentity
	done
	cat >/var/www/selfportal/config/config.php <<EOL
    define('OS_IDENTITY_API_VERSION',"$openstackidentity");
	EOL
	
	while [[ -z "$openstacknetworkid" ]]
	do
		read -p "Enter OpenStack network id: " openstacknetworkid
	done
	cat >/var/www/selfportal/config/config.php <<EOL
    define('OS_NET_ID',"$openstacknetworkid");
	EOL
	
	while [[ -z "$openstacksecuritygroup" ]]
	do
		read -p "Enter OpenStack endpoint (like https://openstack.example.com:5000/v3): " openstacksecuritygroup
	done
	cat >/var/www/selfportal/config/config.php <<EOL
    define('OS_SEC_GRP',"$openstacksecuritygroup");
	EOL
	
else echo "Okay, it doesn't seems to be Y or y symbol, so no connections to OpenStack were made."
fi

read -p "Do you want to configure SelfPortal to work with vSphere?" servername
if [[ $REPLY =~ ^[Yy]$ ]]
then
    #TODO CONFIG VSPHERE
else echo "Okay, it doesn't seems to be Y or y symbol, so no connections to OpenStack were made."
fi

while [[ -z "$servername" ]]
do
  read -p "Enter full address of your future SelfPortal installation (like selfportal.altoros.com): " servername
done

cp /var/www/selfportal/config/sites-enabled/proxy.conf /etc/nginx/sites-enabled/proxy.conf
cat >/etc/nginx/sites-enabled/selfportal <<EOL
server {
        listen 80 default_server;

        root /var/www/selfportal/;
        index index.html index.htm index.php ;

        # Make site accessible from http://localhost/
        server_name $servername;
}
EOL

echo 'www-data ALL=NOPASSWD: /usr/sbin/nginx, /usr/bin/crontab, /bin/grep' >> /etc/sudoers

read -p "Now we can configure scheduled VM cleaning. Would you like us to add appropriate jobs to crontab? [Y/n] " -n 1 -r
echo    # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    (crontab -l; echo "0 8 */1 * * /usr/bin/php /var/www/selfportal/modules/tasks.php --action notify" ) | crontab -
	(crontab -l; echo "1 0 */1 * * /usr/bin/php /var/www/selfportal/modules/tasks.php --action disable" ) | crontab -
	(crontab -l; echo "5 0 */1 * * /usr/bin/php /var/www/selfportal/modules/tasks.php --action delete" ) | crontab -
	(crontab -l; echo "10 0 */1 * * /usr/bin/php /var/www/selfportal/modules/tasks.php --action shutdown_vm" ) | crontab -
	(crontab -l; echo "15 0 */1 * * /usr/bin/php /var/www/selfportal/modules/tasks.php --action terminate_vm" ) | crontab -
	#TODO: CONFIG TASKS
else 
	echo "Okay, it doesn't seems to be Y or y symbol, so no actions will be done."
fi

echo "Please, do not forget to install Perl SDK according to your vSphere version (for 6.0 it is https://code.vmware.com/web/sdk/60/vsphere-perl)"

