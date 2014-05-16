<?php
/**
 * Twitter authentication module for podlove publisher
 *
 * @author Kaspar Janßen <mail@kjanssen.net>
 * @version rc1
 */

// content is what we show the visitor
$content = null;

// retrieve necessary files
$config = parse_ini_file("config.ini", true);
require_once('TwitterAPIExchange.php');

// No step? WTF?
if ($_GET["step"] == '') {
    print_r("Something went wrong.");
} else {

    /*
     * Step 1: Where the journey begins
     *
     * We display a text with a link to our twitter auth redirection in step 2.
     */
    if ($_GET["step"] == "1") {

        $content = "<h1>Podlove Publisher: Twitter Authorization Step 1</h1>";
        $content .= "<div>";
        $content .= "<p>To authorize <em>Podlove</em>, to announce every new episode you need to authenticate it on Twitter.
		This can be done as explained right below. Read the intructions carefully before you start the authentication process!</p>";
        $content .= "<ol>
			<li>Make sure you are logged in at App.net. If you are logged in click on \"continue\".</li>
			<li>On the App.net authorization page click the \"AUTHORIZE\" button, if you would like to allow the <em>Podlove Publisher</em> to announce new episodes for you.</li>
			<li>Copy the resulting authentication code into the \"App.net authentication code\" field in the Modules section.</li>
		</ol>";

        $content .= "<p><a href='twitter.php?step=2'>Continue</a></p>";
    }

    /*
     * Step 2: We travel to twitter
     *
     * We request a token from twitter to perform the authentication where we redirect the visitor to.
     */
    if ($_GET["step"] == "2") {

        // We only have consumer (server) but not user (oauth) credentials yet, so we leave them blank.
        $settings = array(
            'oauth_access_token' => "",
            'oauth_access_token_secret' => "",
            'consumer_key' => $config['twitter']['consumer_key'],
            'consumer_secret' => $config['twitter']['consumer_secret']
        );

        $url = 'https://api.twitter.com/oauth/request_token';
        $requestMethod = 'GET';

        $getfields = '';

        // announce the upcoming authentication
        $twitter = new TwitterAPIExchange($settings);
        $return = $twitter->buildOauth($url, $requestMethod)
            ->setGetfield($getfields)
            ->performRequest();

        // we cut the reponse into a more comfortable array
        $exploded = array();
        parse_str($return, $exploded);

        // redirection to twitter. They will redirect to step 3
        header(
            'Location: https://api.twitter.com/oauth/authorize?oauth_token=' . $exploded['oauth_token'] . '&force_login=true',
            true,
            302
        );


    }

    /*
     * Step 3: We go home and show what we got
     *
     * We will be redirected to here by twitter and we will get the oauth credentials. We display some text and
     * the credentials to the visitor because he has to store them in podlove publisher. (we don't want them TM)
     */
    if ($_GET["step"] == "3") {

        // After a successfully authentication, twitter serves us the token but not the token_secret. Because we need
        // it we have to request it.
        $settings = array(
            'oauth_access_token' => htmlspecialchars($_GET['oauth_token']),
            'oauth_access_token_secret' => "",
            'consumer_key' => $config['twitter']['consumer_key'],
            'consumer_secret' => $config['twitter']['consumer_secret']
        );

        $url = 'https://api.twitter.com/oauth/access_token';
        $requestMethod = 'POST';

        $postfields = array(
            'oauth_verifier' => htmlspecialchars($_GET['oauth_verifier'])
        );

        $twitter = new TwitterAPIExchange($settings);
        $return = $twitter->buildOauth($url, $requestMethod)
            ->setPostfields($postfields)
            ->performRequest();


        $content .= "<h1>Podlove Publisher: Twitter Authorization Step 3</h1>";
        $content .= "<div>";

        if (strpos($return, 'oauth_token') !== false) {

            $exploded = array();
            parse_str($return, $exploded);


            $content .= "<p>Finally, you need to copy the authentication code right below in the \"Twitter authentication code\" field in the modules section and save the changes.
		Make sure you copy the whole code. You can close this windows afterwards. Thanks for trusting <em>Podlove Publisher</em>.</p>";
            $content .= "<textarea id='feld'>";
            $content .= 'oauth_token=' . $exploded['oauth_token'] . '&oauth_token_secret=' . $exploded['oauth_token_secret'];
            $content .= "</textarea>";
        } else {

            $content .= "<p>Something went wrong. Please <a href='twitter.php?step=1'>restart the process</a></p>";
        }
    }
}

/*
 * Website theme
 *
 * You can change the look of step 1 and 3 in here:
 */
if ($content != null) {
    ?>
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" class="wp-toolbar" lang="en-US">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <style type="text/css">
            * {
                margin: 0px;
                padding: 0px;
            }

            body {
                font: 14px/1.5 Arial, Tahoma, sans;
                color: #4f4f4f;
            }

            h1 {
                font-weight: normal;
                background-color: #eee;
                padding: 10px 30px 10px 30px;
                border-bottom: 1px solid #dbdbdb;
            }

            body div {
                padding: 10px 30px 10px 30px;
                width: 500px;
            }

            ol {
                margin: 1em 0px 1em 2em;
            }

            p {
                margin: 1em 0px 1em 0px;
            }

            a, a:visited {
                font-weight: bold;
                color: #2580a5;
            }

            a:hover {
                text-decoration: none;
                color: #000;
            }

            textarea {
                width: 380px;
                height: 80px;
                background-color: #f3f1f1;
                border: 1px solid #dbdbdb;
                padding: 20px;
            }

        </style>

    </head>
    <body>
    <?php echo $content ?>
    </div>

    </body>
    </html>
<?php

}