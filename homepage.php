<?php
/* 
    Template Name: Homepage
*/
get_header();

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
// $client->setAuthConfig(__DIR__ . '/asl-contract-client-oauth.json');
$client->setAuthConfig(__DIR__ . '/asl-contract-01fd683a00f9.json');

// Get the ID of the file to duplicate
$sourceFileId = '1a4l5i3RiMBkxwMWj20bCR3drrfM8IRUDyBAZ_EVYGOo';
// $folderId = '1J7DMIPwy4YGyAN6sI6gYetd-UK-8vnoV';
// $filename_template = 'Hợp đồng lao động';

$service = new Google_Service_Docs($client);

    // 1. Lấy nội dung tài liệu
    $document = $service->documents->get($sourceFileId);

    /* 
    * find the first image in the document, and then replace it with a new image
    */
    $requests = [];
    $image_replace = array(
        '{your_image}' => 'https://hera.ai.vn/wp-content/uploads/2024/06/010-1024x684.webp',
    );
    foreach ($document->getBody()->getContent() as $structuralElement) {
        if ($structuralElement->paragraph) {
            foreach ($structuralElement->paragraph->elements as $paragraphElement) {
                if ($paragraphElement->textRun) {
                    $text = $paragraphElement->textRun->content;

                    if (strpos($text, '{your_image}') !== false) {
                        $startIndex = $paragraphElement->startIndex;
                        
                        print_r($startIndex);
                        echo '<br>';
                    }
                }
            }
        } else if ($structuralElement->table) {
            foreach ($structuralElement->table->tableRows as $tableRow) {
                foreach ($tableRow->tableCells as $tableCell) {
                    foreach ($tableCell->content as $content) {
                        if ($content->paragraph) {
                            foreach ($content->paragraph->elements as $paragraphElement) {
                                print_r($paragraphElement);
                                if ($paragraphElement->textRun) {
                                    $text = $paragraphElement->textRun->content;
                                    
                                    if (strpos($text, '{your_image}') !== false) {
                                        $startIndex = $paragraphElement->startIndex;

                                        print_r($startIndex);
                                        echo '<br>';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    // $batchUpdateRequest = new Google_Service_Docs_BatchUpdateDocumentRequest(array(
    //     'requests' => $requests
    // ));

    // $response = $service->documents->batchUpdate($sourceFileId, $batchUpdateRequest);
?>
<div class="content-wrapper">
    <div class="card card-rounded">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="card-title card-title-dash">File vừa tạo</h4>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex">
                                <i class="fa fa-file-text-o fa-150p"></i>
                                <div class="wrapper ms-3">
                                    <p class="ms-1 mb-1 fw-bold">Brandon Washington</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-right justify-content-between gap-1"> 
                                <a href="#" class="btn btn-inverse-info btn-rounded"><i class="fa fa-pencil"></i></a>
                                <a href="#" class="btn btn-inverse-warning btn-rounded"><i class="fa fa-cloud-download"></i></a>
                                <a href="#" class="btn btn-inverse-success btn-rounded"><i class="fa fa-eye"></i></a>
                                <a href="#" class="btn btn-inverse-danger btn-rounded"><i class="fa fa-trash-o"></i></a>
                            </div>
                        </div>
                        <div class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex">
                                <i class="fa fa-file-text-o fa-150p"></i>
                                <div class="wrapper ms-3">
                                    <p class="ms-1 mb-1 fw-bold">Wayne Murphy</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-right justify-content-between gap-1"> 
                                <a href="#" class="btn btn-inverse-info btn-rounded"><i class="fa fa-pencil"></i></a>
                                <a href="#" class="btn btn-inverse-warning btn-rounded"><i class="fa fa-cloud-download"></i></a>
                                <a href="#" class="btn btn-inverse-success btn-rounded"><i class="fa fa-eye"></i></a>
                                <a href="#" class="btn btn-inverse-danger btn-rounded"><i class="fa fa-trash-o"></i></a>
                            </div>
                        </div>
                        <div class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex">
                                <i class="fa fa-file-text-o fa-150p"></i>
                                <div class="wrapper ms-3">
                                    <p class="ms-1 mb-1 fw-bold">Katherine Butler</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-right justify-content-between gap-1"> 
                                <a href="#" class="btn btn-inverse-info btn-rounded"><i class="fa fa-pencil"></i></a>
                                <a href="#" class="btn btn-inverse-warning btn-rounded"><i class="fa fa-cloud-download"></i></a>
                                <a href="#" class="btn btn-inverse-success btn-rounded"><i class="fa fa-eye"></i></a>
                                <a href="#" class="btn btn-inverse-danger btn-rounded"><i class="fa fa-trash-o"></i></a>
                            </div>
                        </div>
                        <div class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex">
                                <i class="fa fa-file-text-o fa-150p"></i>
                                <div class="wrapper ms-3">
                                    <p class="ms-1 mb-1 fw-bold">Matthew Bailey</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-right justify-content-between gap-1"> 
                                <a href="#" class="btn btn-inverse-info btn-rounded"><i class="fa fa-pencil"></i></a>
                                <a href="#" class="btn btn-inverse-warning btn-rounded"><i class="fa fa-cloud-download"></i></a>
                                <a href="#" class="btn btn-inverse-success btn-rounded"><i class="fa fa-eye"></i></a>
                                <a href="#" class="btn btn-inverse-danger btn-rounded"><i class="fa fa-trash-o"></i></a>
                            </div>
                        </div>
                        <div class="wrapper d-flex align-items-center justify-content-between py-2">
                            <div class="d-flex">
                                <i class="fa fa-file-text-o fa-150p"></i>
                                <div class="wrapper ms-3">
                                    <p class="ms-1 mb-1 fw-bold">Rafell John</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-right justify-content-between gap-1"> 
                                <a href="#" class="btn btn-inverse-info btn-rounded"><i class="fa fa-pencil"></i></a>
                                <a href="#" class="btn btn-inverse-warning btn-rounded"><i class="fa fa-cloud-download"></i></a>
                                <a href="#" class="btn btn-inverse-success btn-rounded"><i class="fa fa-eye"></i></a>
                                <a href="#" class="btn btn-inverse-danger btn-rounded"><i class="fa fa-trash-o"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();