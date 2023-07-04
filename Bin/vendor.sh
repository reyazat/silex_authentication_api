#!/bin/bash
cd .. ;

composer require silex/silex
RESULT=$?
if [ $RESULT -eq 0 ]; then
    composer require wanfeiyy/dd
	RESULT=$?
	if [ $RESULT -eq 0 ]; then
		composer require symfony/var-dumper
		RESULT=$?
		if [ $RESULT -eq 0 ]; then
			composer require monolog/monolog
			RESULT=$?
			if [ $RESULT -eq 0 ]; then
				composer require symfony/translation
				RESULT=$?
				if [ $RESULT -eq 0 ]; then
					composer require symfony/config
					RESULT=$?
					if [ $RESULT -eq 0 ]; then
						composer require symfony/yaml
						RESULT=$?
						if [ $RESULT -eq 0 ]; then
							composer require kint-php/kint
							RESULT=$?
							if [ $RESULT -eq 0 ]; then
								composer require symfony/stopwatch
								RESULT=$?
								if [ $RESULT -eq 0 ]; then
									composer require predis/service-provider
									RESULT=$?
									if [ $RESULT -eq 0 ]; then
										composer require jguyomard/silex-capsule-eloquent
										RESULT=$?
										if [ $RESULT -eq 0 ]; then
											composer require symfony/psr-http-message-bridge
											RESULT=$?
											if [ $RESULT -eq 0 ]; then
												composer require zendframework/zend-diactoros
												RESULT=$?
												if [ $RESULT -eq 0 ]; then
													composer require rmccue/requests
													RESULT=$?
													if [ $RESULT -eq 0 ]; then
														composer require twig/twig
														RESULT=$?
														if [ $RESULT -eq 0 ]; then
															composer require symfony/twig-bridge
															RESULT=$?
															if [ $RESULT -eq 0 ]; then
																composer require twig/extensions
																RESULT=$?
																if [ $RESULT -eq 0 ]; then
																	composer require nochso/html-compress-twig
																	RESULT=$?
																	if [ $RESULT -eq 0 ]; then
																		composer require erusev/parsedown
																		RESULT=$?
																		if [ $RESULT -eq 0 ]; then
																			composer require erusev/parsedown-extra
																			RESULT=$?
																			if [ $RESULT -eq 0 ]; then
																				composer require symfony/filesystem
																				RESULT=$?
																				if [ $RESULT -eq 0 ]; then
																					composer require symfony/asset
																					RESULT=$?
																					if [ $RESULT -eq 0 ]; then
																						composer require symfony/finder
																						RESULT=$?
																						if [ $RESULT -eq 0 ]; then
																							composer require kriswallsmith/assetic
																							RESULT=$?
																							if [ $RESULT -eq 0 ]; then
																								composer require packagist/yuicompressor-bin
																								RESULT=$?
																								if [ $RESULT -eq 0 ]; then
																									composer require bandwidth-throttle/token-bucket
																									RESULT=$?
																									if [ $RESULT -eq 0 ]; then
																									
																										composer require illuminate/pagination
																										RESULT=$?
																										if [ $RESULT -eq 0 ]; then
																										
																											composer require ramsey/uuid
																											RESULT=$?
																											if [ $RESULT -eq 0 ]; then
																											
																												echo OK
																												echo '';
																												echo '';
																												echo '';
																												echo '********************************************';
																												echo '* Please add below section in composer.json*';
																												echo '* file                                     *';
																												echo '*                                          *';
																												echo '* ,                                        *';
																												echo '* "autoload": {                            *';
																												echo '*   "psr-4": {                             *';
																												echo '*     "Controllers\\": "Src/Controllers",  *';
																												echo '*     "Providers\\": "Src/Providers",      *';
																												echo '*     "Helper\\": "Src/Helpers",           *';
																												echo '*     "Component\\": "Src/Component",      *';
																												echo '*     "Models\\": "Src/Models"             *';
																												echo '*   }                                      *';
																												echo '* }                                        *';
																												echo '*                                          *';
																												echo '* and then run the following commands:     *';
																												echo '*  composer update                         *';
																												echo '*  composer dump-autoload --optimize       *';
																												echo '*                                          *';
																												echo '********************************************';


																											else
																												echo failed ramsey/uuid
																											fi
																										else
																											echo failed illuminate/pagination
																										fi
																									else
																										echo failed bandwidth-throttle/token-bucket
																									fi
																								else
																									echo failed packagist/yuicompressor-bin
																								fi
																							else
																								echo failed kriswallsmith/assetic
																							fi
																						else
																							echo failed symfony/finder
																						fi
																					else
																						echo failed symfony/asset
																					fi
																				else
																					echo failed symfony/filesystem
																				fi
																			else
																				echo failed erusev/parsedown-extra
																			fi
																		else
																			echo failed erusev/parsedown
																		fi
																	else
																		echo failed nochso/html-compress-twig
																	fi
																else
																	echo failed twig/extensions
																fi
															else
																echo failed symfony/twig-bridge
															fi
														else
															echo failed twig/twig
														fi
													else
														echo failed rmccue/requests
													fi
												else
													echo failed zendframework/zend-diactoros
												fi
											else
												echo failed symfony/psr-http-message-bridge
											fi
										else
											echo failed jguyomard/silex-capsule-eloquent
										fi
									else
										echo failed predis/service-provider
									fi
								else
									echo failed symfony/stopwatch
								fi
							else
								echo failed kint-php/kint
							fi
						else
							echo failed symfony/yaml
						fi
					else
						echo failed symfony/config
					fi
				else 
					echo failed symfony/translation
				fi
			else 
				echo failed monolog
			fi
		else 
			echo failed symfony/var-dumper
		fi
	else 
		echo failed wanfeiyy/dd
	fi
else
  echo failed silex
fi
