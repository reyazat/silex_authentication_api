<?php
namespace Component\SocialMedia\Assets;

class ConstantSocial{
	
	////////////////////////////////////////////////////////////////////////// facebook connect details
	
	const facebook_app_id = '1433089837000480';
	const facebook_app_secret = '3d3e40a0a975681b60c07e74a2668b80';
	const facebook_default_graph_version = 'v2.2';
	const facebook_scope = ['email','public_profile','user_birthday','user_location','user_hometown'];
	const facebook_fields = 'first_name,last_name,email,hometown,location,birthday,currency,gender,languages,locale,timezone,website,work';
	
	
	////////////////////////////////////////////////////////////////////////// google connect details
	
	const google_client_id = '811989301073-huou9c3olr9ne92au7orcru5udh51fgb.apps.googleusercontent.com'; 
	//const google_client_id = '989884990306-mo8nkgsmomv4ilckr9vp51mpaupia93v.apps.googleusercontent.com'; 
	const google_client_secret = '3VtPLZkZDv96h-aZfGR0A_LR';
	//const google_client_secret = 'jfXl1jUsphmkHrDhmtAdJmxR';
	const google_scope = ['email','profile'];
	
	
	////////////////////////////////////////////////////////////////////////// linkedin connect details
	
	//const linkedin_client_id = '86i8bdvpr6b0wb'; 
	const linkedin_client_id = '77z5a72a4csw1c'; 
	//const linkedin_client_secret = 's1B3AdQZURkuelyO';
	const linkedin_client_secret = 'COdxf7SbTo0sBl9R'; 
	//const linkedin_scope = ['r_basicprofile','r_emailaddress'];
	const linkedin_scope = ['r_liteprofile','r_emailaddress','w_member_social']; 
	const linkedin_authorization = 'https://www.linkedin.com/oauth/v2/authorization';
	const linkedin_hash = '$smartysoftware@linkedin_login@!#';
	const linkedin_accesstoken_url = 'https://www.linkedin.com/oauth/v2/accessToken';
	//const linkedin_profile_url = 'https://www.linkedin.com/v1/people/~:(firstName,last-name,headline,industry,location:(name),positions,email-address,picture-url)?format=json';
	//const linkedin_profile_url = 'https://api.linkedin.com/v2/people/~:(firstName,last-name,headline,industry,location:(name),positions,email-address,picture-url)?format=json';
	const linkedin_profile_url = 'https://api.linkedin.com/v2/me';
	const linkedin_email_url = 'https://api.linkedin.com/v2/clientAwareMemberHandles?q=members&projection=(elements*(primary,type,handle~))';
	
	
	public function linkedin_state(){
		
		return password_hash(self::linkedin_hash, PASSWORD_BCRYPT,['cost' => 15]);
		
	}

}
