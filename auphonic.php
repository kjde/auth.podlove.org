<?php

$config = parse_ini_file("config.ini", true);

if($_POST["code"] == "") {
	print_r("Error: missing code token");
} else {
	$ch = curl_init('https://auphonic.com/oauth2/token/');                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");       
	curl_setopt($ch, CURLOPT_USERAGENT, 'Podlove Publisher (http://podlove.org/)');                                                              
	curl_setopt($ch, CURLOPT_POSTFIELDS, array(                                                                          
		  "client_id" => $config['auphonic']['client_id'],
		  "client_secret" => $config['auphonic']['client_secret'],
		  "redirect_uri" => $_POST["redirect_uri"],
		  "grant_type" => "authorization_code",
		  "code" => $_POST["code"]));                                                              
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
	
	$result = curl_exec($ch);
	$parsed_result = json_decode($result);
	
	if(!isset($parsed_result->error) AND $parsed_result->access_token !== "") {
		echo $parsed_result->access_token;
	} else {
		echo "Error: " . $parsed_result->error;
	}			
	
}