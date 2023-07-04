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
