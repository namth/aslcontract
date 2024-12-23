<?php
/* 
    Template Name: Google Auth
*/
use Google\Client as Google_Client;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Drive\DriveFile as Google_Service_Drive_File;
use Google\Service\Docs\SubstringMatchCriteria as Google_Service_SubstringMatchCriteria;
use Google\Service\Docs\Request as Google_Service_Docs_Request;
use Google\Service\Docs\BatchUpdateDocumentRequest as Google_Service_Docs_BatchUpdateDocumentRequest;


// Load credentials from file
// $client = new Google_Client();
// $client->setApplicationName("ASL Contract");
// $client->addScope(Google_Service_Drive::DRIVE);
// $client->setAccessType('offline');
// $client->setAuthConfig(__DIR__ . '/asl-contract-01fd683a00f9.json');

// // Get the ID of the file to duplicate
// $sourceFileId = '1a4l5i3RiMBkxwMWj20bCR3drrfM8IRUDyBAZ_EVYGOo';
// $folderId = '1J7DMIPwy4YGyAN6sI6gYetd-UK-8vnoV';
// $newfilename = 'Hợp đồng lao động - Nguyễn Duy Sơn';


get_header();
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <div class="home-tab">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    <?php
                    if (isset($_GET["code"]) && $_GET["code"]) {
                        // Load credentials from file
                        $client = new Google_Client();
                        $client->setAuthConfig(__DIR__ . '/asl-contract-client-oauth.json');
                        $client->addScope(Google_Service_Drive::DRIVE);

                        // Get authorization code
                        $code = $_GET['code'];

                        if ($code) {
                            $client->authenticate($code);
                            $accessToken = $client->getAccessToken();
                            $service = new Google_Service_Drive($client);

                            // Chuyển hướng người dùng đến trang chọn file (dùng JavaScript)
                            echo '<script>
                                    window.open("' . admin_url('admin-ajax.php') . '?action=select_google_drive_file&token=' . urlencode(json_encode($accessToken)) . '", "_blank");
                                  </script>';
                        } else {
                            echo "Lỗi xác thực!";
                        }
                        // Get access token
                        // $client->authenticate($code);
                        // $accessToken = $client->getAccessToken();
                    
                        // // Create Google Drive service
                        // $service = new Google_Service_Drive($client);
                    
                        // // Get file information
                        // $fileId = $_GET['fileId']; // File ID from Google Drive
                        // $file = $service->files->get($fileId);
                    
                        // // Display file information
                        // echo '<h1>Thông tin File</h1>';
                        // echo '<p>Tên File: ' . $file->getName() . '</p>';
                        // echo '<p>ID File: ' . $file->getId() . '</p>';
                        // echo '<p>Loại File: ' . $file->getMimeType() . '</p>';
                        // echo '<p>Kích thước File: ' . $file->getSize() . ' bytes</p>';
                    }

                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();

