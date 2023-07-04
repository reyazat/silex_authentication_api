#!/bin/bash

cd ..;

composer require silex/silex symfony/config symfony/yaml symfony/stopwatch symfony/var-dumper symfony/translation symfony/validator symfony/filesystem symfony/finder
RESULT=$?

if [ $RESULT -eq 0 ]; then
    composer require knplabs/console-service-provider
	RESULT=$?
	if [ $RESULT -eq 0 ]; then
		composer require monolog/monolog
		RESULT=$?
		if [ $RESULT -eq 0 ]; then
			composer require illuminate/database illuminate/pagination illuminate/events
			RESULT=$?
			if [ $RESULT -eq 0 ]; then
				composer require moust/silex-cache symfony/security-csrf
				RESULT=$?
				if [ $RESULT -eq 0 ]; then
					composer require rmccue/requests					
					RESULT=$?
					if [ $RESULT -eq 0 ]; then
						composer require erusev/parsedown erusev/parsedown-extra
						RESULT=$?
						if [ $RESULT -eq 0 ]; then
							composer require layershifter/tld-database
							RESULT=$?
							if [ $RESULT -eq 0 ]; then
								composer require gumlet/php-image-resize
								RESULT=$?
								if [ $RESULT -eq 0 ]; then
									composer require ramsey/uuid
									RESULT=$?
									if [ $RESULT -eq 0 ]; then
										composer require mailgun/mailgun-php kriswallsmith/buzz php-http/guzzle6-adapter nyholm/psr7 php-http/message
										RESULT=$?
										if [ $RESULT -eq 0 ]; then
											composer require firebase/php-jwt
											RESULT=$?
											if [ $RESULT -eq 0 ]; then
												sed -i '$s/}/,\n"autoload":{\n"psr-4":{\n"Controllers\\\\\": "Src\/Controllers",\n"Providers\\\\": "Src\/Providers",\n"Helper\\\\": "Src\/Helpers",\n"Component\\\\": "Src\/Component",\n"Models\\\\": "Src\/Models",\n"Console\\\\": "Src\/Console"}\n}\n}/' composer.json
												echo 'Composer installation completed'
												composer update && composer dump-autoload --optimize
												echo 'Composer update is finished'
											fi
										fi
									fi
								fi
							fi
						fi
					fi
				fi
			fi
		fi
	fi
fi
													