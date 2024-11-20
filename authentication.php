<?php
/* 
    Template Name: Authentication
*/

($config = include __DIR__ . '/config.php') or die('Configuration file not found');
$clientId = $config['ONEDRIVE_CLIENT_ID'];
$clientSecret = $config['ONEDRIVE_CLIENT_SECRET'];
$redirectUri = $config['ONEDRIVE_REDIRECT_URI'];

$code = $_GET['code']; // Lấy authorization code
$state = $_GET['state']; // Lấy authorization code

$tokenrequest_url = "https://login.microsoftonline.com/" . $config['TENANT_ID'] . "/oauth2/v2.0/token";

echo $code;