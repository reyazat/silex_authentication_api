#!/bin/bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RESULT=$?
if [ $RESULT -eq 0 ]; then
	php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
	RESULT=$?
	if [ $RESULT -eq 0 ]; then
		php composer-setup.php
	else
		echo failed get 'https://getcomposer.org/installer'
	fi
else
  echo failed get 'https://getcomposer.org/installer'
fi

mv composer.phar /usr/local/bin/composer
php -r "unlink('composer-setup.php');"

version=$( php -v|grep --only-matching --perl-regexp "PHP \\d+\.\\d+" )
versionnumber=${version/PHP / }
b=7.0
if [ $(echo $versionnumber'<'$b |bc -l) -eq 1 ]; then

	apt-get install php$versionnumber-bcmath
	RESULT=$?
	if [ $RESULT -eq 0 ]; then
		echo Success Install
	else
		echo failed php$versionnumber-bcmath
	fi
fi
sudo apt-get -y update && sudo apt-get -y upgrade && sudo apt-get -y dist-upgrade
RESULT=$?
if [ $RESULT -eq 0 ]; then
	curl -sL https://deb.nodesource.com/setup_6.x | sudo -E bash -
	RESULT=$?
	if [ $RESULT -eq 0 ]; then
		apt-get purge -y nodejs
		apt-get install -y nodejs
	else
		echo failed nodejs
	fi
else
	echo failed update and upgrade
fi

npm install -g uglify-js
RESULT=$?
if [ $RESULT -eq 0 ]; then
	echo OK
else
	echo failed uglify-js
fi

npm install -g uglifycss
RESULT=$?
if [ $RESULT -eq 0 ]; then
	echo OK
else
	echo failed uglifycss
fi



apt-get autoremove -y

apt-get install -y optipng

apt-get install -y libjpeg-turbo-progs
RESULT=$?
if [ $RESULT -eq 0 ]; then
echo '*******************FINISH*************************';
else
echo failed libjpeg-turbo-progs
fi
