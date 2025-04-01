<?php
/* 
    Template Name: Authentication
*/
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Abstractions\ApiException;
use Microsoft\Kiota\Authentication\Oauth\AuthorizationCodeContext;
use Microsoft\Graph\Generated\Drives\Item\Items\Item\Copy\CopyPostRequestBody;
use Microsoft\Graph\Generated\Models\ItemReference;


($config = include __DIR__ . '/config.php') or die('Configuration file not found');
$tenantId = $config['TENANT_ID'];
$clientId = $config['ONEDRIVE_CLIENT_ID'];
$clientSecret = $config['ONEDRIVE_CLIENT_SECRET'];
$redirectUri = $config['ONEDRIVE_REDIRECT_URI'];
$scopes = ['Files.ReadWrite.All', 'User.Read.All'];

$authcode = $_GET['code']; // Lấy authorization code

echo $authcode;
echo '<br><br>';

$tokenrequest_url = "https://login.microsoftonline.com/" . $config['TENANT_ID'] . "/oauth2/v2.0/token";

$tokenRequestContext = new AuthorizationCodeContext(
    $tenantId,
    $clientId,
    $clientSecret,
    $authcode,
    $redirectUri
);
$graphServiceClient = new GraphServiceClient($tokenRequestContext, $scopes);

$fileID     = '594473535F203348!1357';
$folderID   = '594473535F203348!5204';

try {
    $user = $graphServiceClient->me()->get()->wait();
    echo "Hello, I am {$user->getGivenName()}";
    
    $drive = $graphServiceClient->drives()->get()->wait();
    
    echo '<br><br>User ID: ';
    print_r($drive->getId());
    echo '<br><br>User Principal Name: ';
    print_r($user->getUserPrincipalName());

    $copyRequestBody = new CopyPostRequestBody();
    $itemReference = new ItemReference();
    $itemReference->setId($folderID); // Destination folder ID
    $copyRequestBody->setParentReference($itemReference);
    $copyRequestBody->setName('CopiedFileName'); // New file name

    // $graphServiceClient->drives()->byDriveId()
    //     ->items()->byDriveItemId()
    //     ->copy($copyRequestBody)
    //     ->post()
    //     ->wait();

    // $result = $graphServiceClient->drives()->byDriveId($user->getDrive()->getId())
    //             ->items()->byDriveItemId($fileID)
    //             ->copy()
    //             ->post($copyRequestBody)
    //             ->wait();

    echo 'File copied successfully!';
} catch (ApiException $ex) {
    echo $ex->getError()->getMessage();
}