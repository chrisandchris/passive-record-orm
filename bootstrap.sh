#!/usr/bin/env bash

echo "--- Let's get to work. Installing now. ---"

echo "--- Updating packages list ---"
sudo apt-get update

echo "--- MySQL time ---"
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
sudo sed -i 's/#max_connections/max_connections/' /etc/mysql/my.cnf
sudo sed -i 's/max_connections[ ]*= 100/max_connections = 1000' /etc/mysql/my.cnf

echo "--- Installing base packages ---"
sudo apt-get install -y vim curl python-software-properties

echo "--- Updating packages list ---"
sudo apt-get update

# echo "--- We want the bleeding edge of PHP ---"
# sudo add-apt-repository -y ppa:ondrej/php5

echo "--- Updating packages list ---"
sudo apt-get update

echo "--- Installing PHP-specific packages ---"
sudo apt-get install -y php5 apache2 libapache2-mod-php5 php5-curl php5-gd php5-mcrypt \
    mysql-server-5.5 php5-mysql git-core php5-sqlite

echo "--- Install PHPUnit ---"
sudo wget -q https://phar.phpunit.de/phpunit.phar
sudo chmod +x phpunit.phar
sudo mv phpunit.phar /usr/local/bin/phpunit
phpunit --version

echo "--- Installing and configuring Xdebug ---"
sudo apt-get install -y php5-xdebug

cat << EOF | sudo tee -a /etc/php5/apache2/conf.d/xdebug.ini
xdebug.scream=1
xdebug.cli_color=1
xdebug.show_local_vars=1
EOF

cat << EOF | sudo tee -a /etc/php5/cli/conf.d/xdebug.ini
xdebug.scream=1
xdebug.cli_color=1
xdebug.show_local_vars=1
EOF

echo "--- Enabling mod-rewrite ---"
sudo a2enmod rewrite

echo "--- Setting up web directory ---"
sudo rm -rf /var/www/html
sudo ln -fs /vagrant /var/www/html
sudo mkdir -p /var/www/uploads
sudo chmod 0777 /var/www/uploads

echo "--- Modify apache user ---"
sed -i "s/export APACHE_RUN_USER=www-data/export APACHE_RUN_USER=vagrant/" /etc/apache2/envvars
sed -i "s/export APACHE_RUN_GROUP=www-data/export APACHE_RUN_GROUP=vagrant/" /etc/apache2/envvars

echo "--- Turn on errors ---"
sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php5/apache2/php.ini
sed -i "s/display_errors = .*/display_errors = On/" /etc/php5/apache2/php.ini

echo "-- Modify apache configuration --"
sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/sites-enabled/000-default.conf
sudo echo "Listen 8000" >> /etc/apache2/ports.conf
sudo cp /etc/apache2/sites-enabled/000-default.conf /etc/apache2/sites-enabled/000-default-8000.conf
sed -i 's/<VirtualHost *:80>/<VirtualHost *:8000>/' /etc/apache2/sites-enabled/000-default-8000.conf

echo "--- Restarting Apache ---"
sudo service apache2 restart

echo "--- Install Composer (PHP package manager) ---"
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

#
# Project specific packages
#

echo "-- Configure xdebug --"
sudo echo "xdebug.remote_enable=1
xdebug.profiler_enable=1
xdebug.remote_connect_back=1
xdebug.remote_port=9000
xdebug.remote_log=/tmp/php5-xdebug.log" >> /etc/php5/apache2/conf.d/xdebug.ini
sudo echo "xdebug.remote_enable=1
xdebug.profiler_enable=1
xdebug.remote_connect_back=1
xdebug.remote_port=9000
xdebug.remote_log=/tmp/php5-xdebug.log" >> /etc/php5/cli/conf.d/xdebug.ini

echo "alias phpx=\"php -dxdebug.remote_host=10.211.55.2 -dxdebug.remote_autostart=1\"" >> /home/vagrant/.bash_profile
echo "alias phpunitx=\"./vendor/phpunit/phpunit/phpunit  -dxdebug.remote_host=10.211.55.2 -dxdebug.remote_autostart=1\"" >> /home/vagrant/.bash_profile
echo "alias ll=\"ls -al\"" >> /home/vagrant/.bash_profile

echo "--- All done, enjoy! :) ---"
