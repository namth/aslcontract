<?php
use Google\Client as Google_Client;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Drive\DriveFile as Google_Service_Drive_File;
use Google\Service\Docs as Google_Service_Docs;
use Google\Service\Docs\SubstringMatchCriteria as Google_Service_SubstringMatchCriteria;
use Google\Service\Docs\Request as Google_Service_Docs_Request;
use Google\Service\Docs\BatchUpdateDocumentRequest as Google_Service_Docs_BatchUpdateDocumentRequest;

function google_docs_get_content($fileId) {
    global $client; // Giả sử bạn đã khai báo biến $client để kết nối Google API
  
    $service = new Google_Service_Docs($client);
  
    // Lấy nội dung file
    $response = $service->documents->get($fileId);
  
    // Trích xuất nội dung từ response
    $allText = [];
    foreach ($response->getBody()->getContent() as $structuralElement) {
        if ($structuralElement->paragraph) {
            foreach ($structuralElement->paragraph->elements as $paragraphElement) {
                if ($paragraphElement->textRun) {
                    $allText[] = $paragraphElement->textRun->content;
                }
            }
        }
    }
  
    return $allText;
}

function google_docs_edit_content($fileId, $newContent) {
    global $client; // Giả sử bạn đã khai báo biến $client để kết nối Google API
  
    $service = new Google_Service_Docs($client);
  
    // Chuẩn bị nội dung mới
    $requests = [
      [
        'insertText' => [
          'location' => [
            'index' => 1, // Chèn từ đầu file
          ],
          'text' => $newContent, // Nội dung mới
        ],
      ],
    ];
  
    // Gửi yêu cầu sửa đổi
    $result = $service->documents->batchUpdate($fileId, [
      'requests' => $requests,
    ]);
  
    return $result;
}

function google_clone_file($sourceFileId, Google_Service_Drive_File $new_file, $optParams = []) {
    global $client;

    $service = new Google_Service_Drive($client);

    try {
        // Duplicate the file
        $new_file->setName($optParams['newfilename']);
        // Move the new file to specific folder
        $new_file->setParents(array($optParams['folderId']));
        $copiedFile = $service->files->copy($sourceFileId, $new_file);

        return $copiedFile->id;
    } catch (Exception $e) {
        return false;
    }
}


function google_docs_replaceText($documentId, $replacements) {
    global $client;

    $service = new Google_Service_Docs($client);

    // Duyệt qua các cặp khóa-giá trị
    foreach ($replacements as $key => $value) {
        $e = new Google_Service_SubstringMatchCriteria();
        $e->text =  $key ;
        $e->setMatchCase(false);


        $requests[] = new Google_Service_Docs_Request(array(
            'replaceAllText' => array(
                'replaceText' => $value,
                'containsText' => $e
            ),
        ));
    }

    $batchUpdateRequest = new Google_Service_Docs_BatchUpdateDocumentRequest(array(
        'requests' => $requests
    ));

    $response = $service->documents->batchUpdate($documentId, $batchUpdateRequest);
    return $response->id;
}