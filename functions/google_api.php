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

/*
 * Hàm chia sẻ một mục trên Google Drive với một email cụ thể
 *
 * @param string $itemId ID của mục cần chia sẻ
 * @param string $email Email của người dùng mà bạn muốn chia sẻ
 * @param string $role Vai trò của người dùng (ví dụ: 'reader', 'writer', 'commenter', 'organizer')
 * @return array Kết quả của việc chia sẻ, bao gồm thành công hay không và thông báo
*/
function shareGoogleDriveItem($itemId, $email, $role = 'writer') {
    global $client; // Giả sử $client đã được khởi tạo và xác thực

    $service = new Google_Service_Drive($client);
    $isShared = isItemSharedWithEmail($itemId, $email); // Kiểm tra xem email đã được chia sẻ chưa

    if ($isShared['shared']) {
        return ['success' => true, 'message' => 'Email đã được chia sẻ trước đó.']; // Email đã được chia sẻ
    } else {
        try {
            $permission = new Google_Service_Drive_Permission([
                'emailAddress' => $email,
                'role' => $role,
                'type' => 'user',
            ]);

            $service->permissions->create($itemId, $permission);
            return ['success' => true, 'message' => 'Đã chia sẻ thành công.'];

        } catch (Google_Service_Exception $e) {
            return ['success' => false, 'message' => 'Lỗi khi chia sẻ: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi không xác định: ' . $e->getMessage()];
        }
    }
}

//Hàm kiểm tra quyền chia sẻ cho cả file và folder
function isItemSharedWithEmail($itemId, $email) {
    global $client;
    $service = new Google_Service_Drive($client);
    
    try {
        // Bước 1: Kiểm tra quyền truy cập cơ bản trước
        if (!canAccessItem($service, $itemId)) {
            return [
                'accessible' => false,
                'shared' => false,
                'role' => null,
                'error' => 'No access to this item (may be private or not shared with current user)'
            ];
        }
        
        // Bước 2: Lấy thông tin item
        $item = $service->files->get($itemId, array('fields' => 'mimeType, name, owners'));
        $isFolder = ($item->getMimeType() === 'application/vnd.google-apps.folder');
        
        $result = [
            'accessible' => true,
            'shared' => false,
            'role' => null,
            'type' => $isFolder ? 'folder' : 'file',
            'name' => $item->getName(),
            'owners' => []
        ];
        
        // Lấy thông tin owners
        foreach ($item->getOwners() as $owner) {
            $result['owners'][] = $owner->getEmailAddress();
        }
        
        // Bước 3: Kiểm tra permissions (chỉ khi có quyền truy cập)
        try {
            $permissions = $service->permissions->listPermissions($itemId, array(
                'fields' => 'permissions(emailAddress, role, type)'
            ));
            
            foreach ($permissions->getPermissions() as $permission) {
                // Kiểm tra email trực tiếp
                if ($permission->getEmailAddress() == $email) {
                    $result['shared'] = true;
                    $result['role'] = $permission->getRole();
                    $result['permission_type'] = $permission->getType();
                    break;
                }
            }
            
        } catch (Google_Service_Exception $permE) {
            // Nếu không thể lấy permissions, có thể do quyền hạn chế
            if ($permE->getCode() == 403) {
                $result['permission_check_error'] = 'Cannot access permission list - insufficient rights';
                // Kiểm tra xem email có phải là owner không
                if (in_array($email, $result['owners'])) {
                    $result['shared'] = true;
                    $result['role'] = 'owner';
                    $result['permission_type'] = 'user';
                }
            } else {
                throw $permE; // Re-throw nếu không phải lỗi 403
            }
        }
        
        // Bước 4: Nếu là folder và chưa có quyền trực tiếp, kiểm tra quyền kế thừa
        // if (!$result['shared'] && $isFolder) {
        //     $result['inherited_permissions'] = checkInheritedPermissions($service, $itemId, $email);
        // }
        
        return $result;
        
    } catch (Google_Service_Exception $e) {
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        
        // Xử lý các lỗi phổ biến
        switch ($errorCode) {
            case 403:
                return [
                    'accessible' => false,
                    'shared' => false,
                    'role' => null,
                    'error' => 'Access denied - file/folder may be private or not shared with current user',
                    'code' => 403
                ];
            case 404:
                return [
                    'accessible' => false,
                    'shared' => false,
                    'role' => null,
                    'error' => 'File/folder not found or deleted',
                    'code' => 404
                ];
            default:
                return [
                    'accessible' => false,
                    'shared' => false,
                    'role' => null,
                    'error' => 'Google Drive API Error: ' . $errorMessage,
                    'code' => $errorCode
                ];
        }
    } catch (Exception $e) {
        return [
            'accessible' => false,
            'shared' => false,
            'role' => null,
            'error' => 'General Error: ' . $e->getMessage()
        ];
    }
}

// Hàm kiểm tra khả năng truy cập item
function canAccessItem($service, $itemId) {
    try {
        // Thử lấy thông tin cơ bản nhất
        $service->files->get($itemId, array('fields' => 'id'));
        return true;
    } catch (Google_Service_Exception $e) {
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Hàm sao chép file trên Google Drive
 *
 * @param string $sourceFileId ID của file nguồn cần sao chép
 * @param Google_Service_Drive_File $new_file Đối tượng Google_Service_Drive_File chứa thông tin file mới
 * @param array $optParams Mảng tùy chọn, bao gồm 'newfilename' và 'folderId'
 * @return string|bool ID của file mới đã được sao chép hoặc false nếu có lỗi
 */
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

        return $copiedFile->id; // Trả về ID của file mới đã được sao chép
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
                                    ),
                                    // 'objectSize' => array(
                                    //     'height' => array(
                                    //         'magnitude' => 20,
                                    //         'unit' => 'PT',
                                    //     ),
                                    //     'width' => array(
                                    //         'magnitude' => 20,
                                    //         'unit' => 'PT',
                                    //     ),
                                    // )
                                )
                            ));
                        }
                    }
                }
            }
        } else if ($structuralElement->table) {
            $table = $structuralElement->table;
            foreach ($structuralElement->table->tableRows as $rowIndex => $row) {
                foreach ($row->tableCells as $colIndex => $cell) {
                    foreach ($cell->content as $content) {
                        if ($content->paragraph) {
                            foreach ($content->paragraph->elements as $paragraphElement) {
                                if ($paragraphElement->textRun) {
                                    $text = $paragraphElement->textRun->content;
                                    foreach ($img_replacements as $textToReplace => $imageUrl) {
                                        if (strpos($text, $textToReplace) !== false) {
                                            $startIndex = $paragraphElement->startIndex;
                                            $found = true;

                                            # Tính toán kích thước cell
                                            $tableStyle = $table->getTableStyle();
                                            
                                            if (isset($tableStyle->tableColumnProperties[$colIndex])) {
                                                $colProp = $tableStyle->tableColumnProperties[$colIndex];
                                                $colWidth = $colProp->getWidth();
                                                $padding = 6; // Padding mặc định của Google Docs
                                                
                                                // Truy cập width từ modelData
                                                if (isset($colWidth)) {
                                                    $cellWidth = $colWidth['magnitude'] - $padding * 2; // Trừ đi padding * 2 bên để ảnh căn vào giữa
                                                }
                                            }

                                            // print_r($colProp->getWidth());
                                            // print_r("<br>cellWidth: " . $cellWidth);

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
                                                    ),
                                                    'objectSize' => array(
                                                        // 'height' => array(
                                                        //     'magnitude' => 50,
                                                        //     'unit' => 'PT',
                                                        // ),
                                                        'width' => array(
                                                            'magnitude' => $cellWidth,
                                                            'unit' => 'PT',
                                                        ),
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

    // Sắp xếp các requests theo startIndex từ lớn đến nhỏ (đảo ngược)
    if (isset($requests) && count($requests) > 1) {
        usort($requests, function($a, $b) {
            // Lấy startIndex từ deleteContentRange request
            $startIndexA = null;
            $startIndexB = null;
            
            // Tìm startIndex từ deleteContentRange hoặc insertInlineImage
            if (isset($a->deleteContentRange) && isset($a->deleteContentRange->range)) {
                $startIndexA = $a->deleteContentRange->range->startIndex;
            } elseif (isset($a->insertInlineImage) && isset($a->insertInlineImage->location)) {
                $startIndexA = $a->insertInlineImage->location->index;
            }
            
            if (isset($b->deleteContentRange) && isset($b->deleteContentRange->range)) {
                $startIndexB = $b->deleteContentRange->range->startIndex;
            } elseif (isset($b->insertInlineImage) && isset($b->insertInlineImage->location)) {
                $startIndexB = $b->insertInlineImage->location->index;
            }
            
            // Sắp xếp từ lớn đến nhỏ (đảo ngược)
            return $startIndexB - $startIndexA;
        });
    }

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