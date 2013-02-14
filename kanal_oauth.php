<?php
/*
 * This is the PHP you'll need to get the Meo Kanal OAuth keys
 * This tool uses the PHP-OAuth2 wrapper lib from https://github.com/adoy/PHP-OAuth2
 */

if (isset($_GET["error"]))
{
    echo("<pre>OAuth Error: " . $_GET["error"]."\n");
    echo('<a href="index.php">Retry</a></pre>');
    die;
}

$authorizeUrl = 'https://kanal.pt/api/oauth';
$accessTokenUrl = 'https://kanal.pt/api/oauth/access_token';

$clientId = '1111111111111111111111111111111111111111111111111111111';
$clientSecret = '1111111111111111111111111111111111111111111111111111111';

$redirectUrl = "http://myblog.com/kanal_oauth.php";

require("classes/oauth2/Client.php");
require("classes/oauth2/GrantType/IGrantType.php");
require("classes/oauth2/GrantType/AuthorizationCode.php");

$client = new OAuth2\Client($clientId, $clientSecret);

if (!isset($_GET["code"]))
{
    $authUrl = $client->getAuthenticationUrl($authorizeUrl, $redirectUrl, array("scope" => "channel.list,channel.create,channel.edit,schedule.create,schedule.delete,schedule.edit,schedule.list,videos.list,videos.delete,videos.create,slideshows.create,notifications.list,schedule.shuffle,devices.list,notifications.delete,devices.manage"));
    header("Location: ".$authUrl);
    die("Redirect");
}
else
{
    $params = array("code" => $_GET["code"], "redirect_uri" => $redirectUrl);
    $response = $client->getAccessToken($accessTokenUrl, "authorization_code", $params);
    file_put_contents("/tmp/response",print_r($response,true));
    $accessTokenResult = $response["result"];
    $client->setAccessToken($accessTokenResult["access_token"]);
    $client->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);
    $response = $client->fetch("https://services.sapo.pt/IPTV/MEO/Kanal/api/scopes");
    print_r($response);
}
?>

