<?php
use Google\Client as Google_Client;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Drive\DriveFile as Google_Service_Drive_File;
use Google\Service\Drive\Permission as Google_Service_Drive_Permission;
use Google\Service\Docs as Google_Service_Docs;
use Google\Service\Docs\SubstringMatchCriteria as Google_Service_SubstringMatchCriteria;
use Google\Service\Docs\Request as Google_Service_Docs_Request;
use Google\Service\Docs\BatchUpdateDocumentRequest as Google_Service_Docs_BatchUpdateDocumentRequest;

function google_docs_get_content($fileId)
{
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

function google_docs_edit_content($fileId, $newContent)
{
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

function google_clone_file($sourceFileId, Google_Service_Drive_File $new_file, $optParams = [])
{
    global $client;

    $service = new Google_Service_Drive($client);

    try {
        // Duplicate the file
        $new_file->setName($optParams['newfilename']);
        // Move the new file to specific folder
        $new_file->setParents(array($optParams['folderId']));
        $copiedFile = $service->files->copy($sourceFileId, $new_file);

        // set permission for the new file, share with anyone can view with link
        $permission = new Google_Service_Drive_Permission();
        $permission->setRole('reader');
        $permission->setType('anyone'); // anyoneWithLink

        $result = $service->permissions->create($copiedFile->id, $permission);

        // set permission for the new file, share with specific email
        if (isset($optParams['email'])) {
            $permission = new Google_Service_Drive_Permission();
            $permission->setRole('writer'); // Hoặc 'reader' nếu chỉ muốn cho phép xem
            $permission->setType('user');
            $permission->setEmailAddress($optParams['email']);

            $result = $service->permissions->create($copiedFile->id, $permission);
        }

        return $copiedFile->id;
    } catch (Exception $e) {
        return false;
    }
}


function google_docs_replaceText($documentId, $replacements)
{
    if (empty($replacements)) {
        return false;
    }

    global $client;

    $service = new Google_Service_Docs($client);

    // Duyệt qua các cặp khóa-giá trị
    foreach ($replacements as $key => $value) {
        $e = new Google_Service_SubstringMatchCriteria();
        $e->text = $key;
        $e->setMatchCase(false);


        $requests[] = new Google_Service_Docs_Request(array(
            'replaceAllText' => array(
                'replaceText' => $value,
                'containsText' => $e
            ),
        ));
    }

    // return $requests;

    $batchUpdateRequest = new Google_Service_Docs_BatchUpdateDocumentRequest(array(
        'requests' => $requests
    ));

    $response = $service->documents->batchUpdate($documentId, $batchUpdateRequest);
    return $response->id;
}

/* 
 * Hàm chèn hình ảnh vào Google Docs
 */
function insertImageIntoGoogleDoc($fileId, $img_replacements)
{
    global $client; // Biến $client được định nghĩa ở bước xác thực
    $found = false;
    $service = new Google_Service_Docs($client);

    // 1. Lấy nội dung tài liệu
    $document = $service->documents->get($fileId);

    // 2. Tìm vị trí của văn bản cần thay thế
    $startIndex = null;
    foreach ($document->getBody()->getContent() as $structuralElement) {
        if ($structuralElement->paragraph) {
            foreach ($structuralElement->paragraph->elements as $paragraphElement) {
                if ($paragraphElement->textRun) {
                    $text = $paragraphElement->textRun->content;
                    foreach ($img_replacements as $textToReplace => $imageUrl) {
                        if (strpos($text, $textToReplace) !== false) {
                            $startIndex = $paragraphElement->startIndex;
                            $found = true;
    
                            // 4. Xóa văn bản cần thay thế ( nếu tìm thấy)
                            $requests[] = new Google_Service_Docs_Request(array(
                                'deleteContentRange' => [
                                    'range' => [
                                        'startIndex' => $startIndex,
                                        'endIndex' => $startIndex + strlen($textToReplace),
                                    ],
                                ],
                            ));
    
                            // 5. Chèn hình ảnh
                            $requests[] = new Google_Service_Docs_Request(array(
                                'insertInlineImage' => array(
                                    'uri' => $imageUrl,
                                    'location' => array(
                                        'index' => $startIndex,
                                    )
                                )
                            ));
                        }
                    }
                }
            }
        } else if ($structuralElement->table) {
            foreach ($structuralElement->table->tableRows as $row) {
                foreach ($row->tableCells as $cell) {
                    foreach ($cell->content as $content) {
                        if ($content->paragraph) {
                            foreach ($content->paragraph->elements as $paragraphElement) {
                                if ($paragraphElement->textRun) {
                                    $text = $paragraphElement->textRun->content;
                                    foreach ($img_replacements as $textToReplace => $imageUrl) {
                                        if (strpos($text, $textToReplace) !== false) {
                                            $startIndex = $paragraphElement->startIndex;
                                            $found = true;

                                            // 4. Xóa văn bản cần thay thế ( nếu tìm thấy)
                                            $requests[] = new Google_Service_Docs_Request(array(
                                                'deleteContentRange' => [
                                                    'range' => [
                                                        'startIndex' => $startIndex,
                                                        'endIndex' => $startIndex + strlen($textToReplace),
                                                    ],
                                                ],
                                            ));

                                            // 5. Chèn hình ảnh
                                            $requests[] = new Google_Service_Docs_Request(array(
                                                'insertInlineImage' => array(
                                                    'uri' => $imageUrl,
                                                    'location' => array(
                                                        'index' => $startIndex,
                                                    )
                                                )
                                            ));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // return $requests;

    // 3. Xử lý trường hợp không tìm thấy văn bản
    if ($startIndex === null) {
        return ['success' => false, 'message' => 'Văn bản cần thay thế không được tìm thấy'];
    }

    if ($found) {
        // 6. Gửi yêu cầu cập nhật
        $batchUpdateRequest = new Google_Service_Docs_BatchUpdateDocumentRequest(array(
            'requests' => $requests
        ));

        $result = $service->documents->batchUpdate($fileId, $batchUpdateRequest);
    }

    return ['success' => true, 'message' => 'Hình ảnh đã được chèn'];
}

/* 
* set permission for file, share with anyone can view with link
*/
function shareFileWithLink( $fileId ) {
    global $client; // Giả sử bạn đã có đối tượng Google_Client đã xác thực
    
    $service = new Google_Service_Drive($client);

    try {
        $permission = new Google_Service_Drive_Permission();
        $permission->setRole('reader');
        $permission->setType('anyone'); // anyoneWithLink

        $result = $service->permissions->create($fileId, $permission);

        if ($result) {
            return ['success' => true, 'message' => 'File đã được chia sẻ với bất cứ ai có link.', 'link' => $result->getLink()];
        } else {
            return ['success' => false, 'message' => 'Lỗi khi chia sẻ file.'];
        }

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}


function shareFileWithEmail($fileId, $emailAddress) {
    global $client; // Giả sử bạn đã có đối tượng Google_Client đã được xác thực
    
    $service = new Google_Service_Drive($client);

    try {
        $permission = new Google_Service_Drive_Permission();
        $permission->setRole('writer'); // Hoặc 'reader' nếu chỉ muốn cho phép xem
        $permission->setType('user');
        $permission->setEmailAddress($emailAddress);

        $result = $service->permissions->create($fileId, $permission);

        if ($result) {
            return ['success' => true, 'message' => 'File đã được chia sẻ với ' . $emailAddress . '.'];
        } else {
            return ['success' => false, 'message' => 'Lỗi khi chia sẻ file.'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

/* function shareFileWithMultipleEmails($fileId, $emailAddresses) {
    global $client; // Giả sử bạn đã có đối tượng Google_Client đã được xác thực
    
    $service = new Google_Service_Drive($client);

    try {
        $permissions = [];
        foreach ($emailAddresses as $emailAddress) {
            $permission = new Google_Service_Drive_Permission();
            $permission->setRole('writer'); // Hoặc 'reader'
            $permission->setType('user');
            $permission->setEmailAddress($emailAddress);
            $permissions[] = $permission;
        }


        $batch = new Google_Service_Drive_BatchRequest();
        foreach ($permissions as $permission){
          $batch->add($service->permissions->create($fileId, $permission, ['fields' => 'id']));
        }

        $results = $batch->execute();

        $success = true;
        $message = '';
        foreach ($results as $result){
          if (!$result->success){
            $success = false;
            $message .= "Error sharing with: ". $result->error->message . "\n";
          }
        }

        return ['success' => $success, 'message' => $success ? 'Files have been shared successfully' : $message];


    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
} */