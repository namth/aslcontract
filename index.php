<?php

use Google\Client as Google_Client;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Drive\DriveFile as Google_Service_Drive_File;
use Google\Service\Docs\SubstringMatchCriteria as Google_Service_SubstringMatchCriteria;
use Google\Service\Docs\Request as Google_Service_Docs_Request;
use Google\Service\Docs\BatchUpdateDocumentRequest as Google_Service_Docs_BatchUpdateDocumentRequest;


// // Load credentials from file
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
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link ps-0" id="home-tab" data-bs-toggle="tab" href="#overview"
                                role="tab" aria-controls="overview" aria-selected="false">Overview</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#audiences" role="tab"
                                aria-selected="true">Audiences</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#demographics" role="tab"
                                aria-selected="false">Demographics</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link border-0" id="more-tab" data-bs-toggle="tab" href="#more" role="tab"
                                aria-selected="false">More</a>
                        </li>
                    </ul>
                    <div>
                        <div class="btn-wrapper">
                            <a href="#" class="btn btn-otline-dark align-items-center"><i class="icon-share"></i>
                                Share</a>
                            <a href="#" class="btn btn-otline-dark"><i class="icon-printer"></i> Print</a>
                            <a href="#" class="btn btn-primary text-white me-0"><i class="icon-download"></i> Export</a>
                        </div>
                    </div>
                </div>
                <div class="tab-content tab-content-basic">
                    <div class="tab-pane fade" id="overview" role="tabpanel" aria-labelledby="overview">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="statistics-details align-items-center justify-content-between">
                                    <?php
                                    /* Clone docs file from source template file */
                                    // $new_file = new Google_Service_Drive_File();
                                    // $optParams = array(
                                    //     'folderId' => $folderId,
                                    //     'newfilename' => $newfilename,
                                    // );
                                    // $copyfileID = google_clone_file($sourceFileId, $new_file, $optParams);
                                    
                                    // if ($copyfileID) {
                                    //     echo 'Clone thành công: '. $copyfileID;
                                    
                                    //     /* Replace text in docs file */
                                    //     $replacements = array(
                                    //         '{your_email}' => 'duyson126@gmail.com',
                                    //         '{your_phone}' => '0367.542.037',
                                    //     );
                                    

                                    //     $result = google_docs_replaceText($copyfileID, $replacements);
                                    
                                    // } else {
                                    //     echo "Clone thất bại";
                                    // }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show active" id="audiences" role="tabpanel" aria-labelledby="overview">
                        Audiences

                        <?php
                        if (isset($_GET["code"]) && $_GET["code"]) {
                            // Load credentials from file
                            $client = new Google_Client();
                            $client->setAuthConfig(__DIR__ . '/asl-contract-client-oauth.json');
                            $client->addScope(Google_Service_Drive::DRIVE_READONLY);

                            // Get authorization code
                            $code = $_GET['code'];

                            // Get access token
                            $client->authenticate($code);
                            $accessToken = $client->getAccessToken();

                            // Create Google Drive service
                            $service = new Google_Service_Drive($client);

                            // Get file information
                            $fileId = $_GET['fileId']; // File ID from Google Drive
                            $file = $service->files->get($fileId);

                            // Display file information
                            echo '<h1>Thông tin File</h1>';
                            echo '<p>Tên File: ' . $file->getName() . '</p>';
                            echo '<p>ID File: ' . $file->getId() . '</p>';
                            echo '<p>Loại File: ' . $file->getMimeType() . '</p>';
                            echo '<p>Kích thước File: ' . $file->getSize() . ' bytes</p>';
                        } else {
                        ?>

                        <button id="pickFile">Chọn File</button>
                        <div id="fileInformation"></div>

                        <script>
                            jQuery(document).ready(function () {
                                jQuery('#pickFile').click(function () {
                                    // Mở popup chọn file của Google Drive
                                    window.open('https://accounts.google.com/o/oauth2/auth?client_id=912936701906-6moube1rdg93dnht69q2r59dij8onura.apps.googleusercontent.com&redirect_uri=http://aslcontract.local&response_type=code&scope=https://www.googleapis.com/auth/drive.readonly', 'GoogleDriveFilePicker', 'width=800,height=600');
                                });
                            });
                        </script>
                        
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();

