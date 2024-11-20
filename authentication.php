<?php
/* 
    Template Name: Authentication
*/
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Abstractions\ApiException;
use Microsoft\Kiota\Authentication\Oauth\AuthorizationCodeContext;

($config = include __DIR__ . '/config.php') or die('Configuration file not found');
$tenantId = $config['TENANT_ID'];
$clientId = $config['ONEDRIVE_CLIENT_ID'];
$clientSecret = $config['ONEDRIVE_CLIENT_SECRET'];
$redirectUri = $config['ONEDRIVE_REDIRECT_URI'];
$scopes = ['Files.ReadWrite.All', 'User.Read'];

$authcode = $_GET['code']; // Lấy authorization code
$state = $_GET['state']; // Lấy authorization code

$tokenrequest_url = "https://login.microsoftonline.com/" . $config['TENANT_ID'] . "/oauth2/v2.0/token";

$tokenRequestContext = new AuthorizationCodeContext(
    $tenantId,
    $clientId,
    $clientSecret,
    $authcode,
    $redirectUri
);
$graphServiceClient = new GraphServiceClient($tokenRequestContext, $scopes);

try {
    $user = $graphServiceClient->me()->get()->wait();
    echo "Hello, I am {$user->getGivenName()}";
} catch (ApiException $ex) {
    echo $ex->getError()->getMessage();
}