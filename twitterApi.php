<?php
/**
 * Twitter API for podlove publisher
 *
 * @author Kaspar Janßen <mail@kjanssen.net>
 * @version rc1
 */

// oauth credentials are mandatory!
if (empty($_POST['oauth_token']) || empty($_POST['oauth_token_secret'])) {
    exit('please authenticate');
}

// retrieve necessary files
require_once('TwitterAPIExchange.php');
$config = parse_ini_file("config.ini", true);

// setup basic oauth parameters
$settings = array(
    'oauth_access_token' => getPOST('oauth_token'),
    'oauth_access_token_secret' => getPOST('oauth_token_secret'),
    'consumer_key' => $config['twitter']['consumer_key'],
    'consumer_secret' => $config['twitter']['consumer_secret']
);

// action to verify credentials by podlove
if ($_POST["action"] == 'verify_credentials') {

    $url = 'https://api.twitter.com/1.1/account/verify_credentials.json';
    $getfield = '?include_entities=false';
    $requestMethod = 'GET';

    $twitter = new TwitterAPIExchange($settings);
    $response = $twitter->setGetfield($getfield)
        ->buildOauth($url, $requestMethod)
        ->performRequest();

// action to update status (post tweet)
} elseif ($_POST["action"] == 'statuses_update') {

    $url = 'https://api.twitter.com/1.1/statuses/update.json';
    $requestMethod = 'POST';

    $postfields = array(
        'status' => getPOST('status')
    );

    $twitter = new TwitterAPIExchange($settings);
    $response = $twitter->setPostfields($postfields)
        ->buildOauth($url, $requestMethod)
        ->performRequest();

// fallback if action missing or not implemented
} else {
    $response = 'auth.podlove.org error: something went wrong';
}

// response output
echo $response;

// helper class to "secure" posted data
// Todo: please review!!!
function getPOST($name) {
    return htmlspecialchars($_POST[$name]);
}