<?php

use Google\Client as Google_Client;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Drive\DriveFile as Google_Service_Drive_File;
use Google\Service\Docs\SubstringMatchCriteria as Google_Service_SubstringMatchCriteria;
use Google\Service\Docs\Request as Google_Service_Docs_Request;
use Google\Service\Docs\BatchUpdateDocumentRequest as Google_Service_Docs_BatchUpdateDocumentRequest;


// Load credentials from file
$client = new Google_Client();
$client->setApplicationName("ASL Contract");
$client->addScope(Google_Service_Drive::DRIVE);
$client->setAccessType('offline');
$client->setAuthConfig(__DIR__ . '/asl-contract-client-oauth.json');
// $client->setAuthConfig(__DIR__ . '/asl-contract-01fd683a00f9.json');

// Get the ID of the file to duplicate
// $sourceFileId = '1a4l5i3RiMBkxwMWj20bCR3drrfM8IRUDyBAZ_EVYGOo';
// $folderId = '1J7DMIPwy4YGyAN6sI6gYetd-UK-8vnoV';
// $filename_template = 'Hợp đồng lao động';


get_header();
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <div class="home-tab">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link ps-0" id="home-tab" data-bs-toggle="tab" href="#overview" role="tab"
                                aria-controls="overview" aria-selected="false">Overview</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#audiences"
                                role="tab" aria-selected="true">Audiences</a>
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
                                    /* Replace text in docs file */
                                    // $replacements = array(
                                    //     '{your_name}' => 'Trần Hải Nam',
                                    //     '{job}' => 'Nhân viên',
                                    //     '{country}' => 'Việt Nam',
                                    //     '{CCCD}' => '123456789',
                                    //     '{date_of_issue}' => '21/03/2021',
                                    //     '{your_email}' => 'namth.pass@gmail.com',
                                    //     '{your_phone}' => '098.689.6800',
                                    //     '{your_address}' => 'Tổ 12 phường Sài Đồng, Long Biên, Hà Nội',
                                    // );
                                    // $replacements = array(
                                    //     '{your_name}' => 'Nguyễn Duy Sơn',
                                    //     '{job}' => 'Nhân viên',
                                    //     '{country}' => 'Việt Nam',
                                    //     '{CCCD}' => '1234567890',
                                    //     '{date_of_issue}' => '28/05/2021',
                                    //     '{your_email}' => 'duyson126@gmail.com',
                                    //     '{your_phone}' => '0367.542.037',
                                    //     '{your_address}' => 'Xóm Yên Phú, xã Văn Thành, huyện Yên Thành, tỉnh Nghệ An',
                                    // );
                                    
                                    // $newfilename = $filename_template . ' - ' . $replacements['{your_name}'];
                                    
                                    // /* Clone docs file from source template file */
                                    // $new_file = new Google_Service_Drive_File();
                                    // $optParams = array(
                                    //     'folderId' => $folderId,
                                    //     'newfilename' => $newfilename,
                                    // );
                                    // $copyfileID = google_clone_file($sourceFileId, $new_file, $optParams);
                                    
                                    // if ($copyfileID) {
                                    //     echo 'Clone thành công: '. $copyfileID;
                                    
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
                        <?php
                        // $client->setRedirectUri('https://iv.io.vn/google-auth');
                        // $authUrl = $client->createAuthUrl();

                        ?>

                        <button id="chooseGoogleDriveFile">Chọn File từ Google Drive</button>
                        <div id="fileID"></div>

                        <script>
                            document.getElementById('chooseGoogleDriveFile').addEventListener('click', () => {
                                window.location.href = `<?php echo $authUrl; ?>`; // Gọi URL xác thực từ WordPress
                            });
                        </script>

                        <?php
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();

