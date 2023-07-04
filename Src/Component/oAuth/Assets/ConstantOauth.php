<?php
namespace Component\oAuth\Assets;

class ConstantOauth{
	
	const refreshTokenExpire = 'P1M';
	const accessTokenExpire = 'PT1H';
	const encryptionKey = '2JX1A4KC16G9ZDQ9HH7HG80O081SJV5772MOOFGYBHR06UZM5ZN6YDT8SYW2BNFF';
	const privateKey = 'file://' . __DIR__ . '/Keys/private.key';
	const publicKey = 'file://' . __DIR__ . '/Keys/public.key';
	
	//const googleCaptchaSecret = '6LdkpTUUAAAAAIH0LeWAr-SH3Ldig_NNPB8VnT4r';
	const googleCaptchaSecret = '6LdGYBoTAAAAAPfZZJR-bZeEQ8oIPKzhVZnE3TQt';
	const googleCaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
	
}
