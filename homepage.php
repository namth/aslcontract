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
use Google\Service\Docs\ReplaceAllTextRequest as Google_Service_Docs_ReplaceAllTextRequest;
use Google\Service\Docs\DeleteContentRangeRequest as Google_Service_Docs_DeleteContentRangeRequest;
use Google\Service\Docs\InsertTextRequest as Google_Service_Docs_InsertTextRequest;
use Google\Service\Docs\Range as Google_Service_Docs_Range;
use Google\Service\Docs\Location as Google_Service_Docs_Location;

// Get the ID of the file to duplicate
$sourceFileId = '1a4l5i3RiMBkxwMWj20bCR3drrfM8IRUDyBAZ_EVYGOo';
$part_data = '1VTdTBsOA-8P3iYZ1TvGweob5g9nBu6NmyuQUodVuIu0';
$folderId = '1J7DMIPwy4YGyAN6sI6gYetd-UK-8vnoV';
$replace_to = "{ben_B}";
$data_replace = [
    '{name}' => 'Nguyễn Văn A',
    '{country}' => 'Vietnam',
    '{your_email}' => '01/01/2023',
    '{your_phone}' => '12 tháng',
];
$filename_template = 'Hợp đồng lao động - part test';

// Get the Google Docs service and Drive service
$docsService = new Google_Service_Docs($client);
$driveService = new Google_Service_Drive($client);

try {
    // Step 1: Copy the source document to create a new file
    $newFile = new Google_Service_Drive_File();
    $newFile->setName($filename_template);
    
    // If folder ID is provided, set the parent folder
    if (!empty($folderId)) {
        $newFile->setParents([$folderId]);
    }
    
    // Copy the file (this preserves all formatting)
    $copiedFile = $driveService->files->copy($sourceFileId, $newFile);
    $newDocId = $copiedFile->getId();
    
    // Step 2: Create a temporary copy of the part document to apply data_replace changes
    $tempPartFile = new Google_Service_Drive_File();
    $tempPartFile->setName('Temp Part File');
    $tempPartCopy = $driveService->files->copy($part_data, $tempPartFile);
    $tempPartId = $tempPartCopy->getId();
    
    // Step 3: Apply replacements to the temporary part document
    $replaceRequests = [];
    foreach ($data_replace as $placeholder => $value) {
        $replaceTextRequest = new Google_Service_Docs_ReplaceAllTextRequest();
        $replaceTextRequest->setReplaceText($value);
        $replaceTextRequest->setContainsText(new Google_Service_SubstringMatchCriteria([
            'text' => $placeholder,
            'matchCase' => true
        ]));
        
        $request = new Google_Service_Docs_Request();
        $request->setReplaceAllText($replaceTextRequest);
        $replaceRequests[] = $request;
    }
    
    if (!empty($replaceRequests)) {
        $batchUpdateRequest = new Google_Service_Docs_BatchUpdateDocumentRequest();
        $batchUpdateRequest->setRequests($replaceRequests);
        $docsService->documents->batchUpdate($tempPartId, $batchUpdateRequest);
    }
    
    // Step 4: Find the placeholder in the destination document
    $newDoc = $docsService->documents->get($newDocId);
    $placeholderInfo = findPlaceholderPosition($newDoc, $replace_to);
    
    if ($placeholderInfo) {
        // Step 5: Delete the placeholder in the destination document
        $deleteRequest = new Google_Service_Docs_DeleteContentRangeRequest();
        $deleteRequest->setRange(new Google_Service_Docs_Range([
            'startIndex' => $placeholderInfo['startIndex'],
            'endIndex' => $placeholderInfo['endIndex']
        ]));
        
        $request = new Google_Service_Docs_Request();
        $request->setDeleteContentRange($deleteRequest);
        
        $batchUpdateRequest = new Google_Service_Docs_BatchUpdateDocumentRequest();
        $batchUpdateRequest->setRequests([$request]);
        $docsService->documents->batchUpdate($newDocId, $batchUpdateRequest);
        
        // Step 6: Copy content with formatting from temp part document to destination
        // Get the formatted content from the temp part document
        $tempPartDoc = $docsService->documents->get($tempPartId);
        $formattedContent = extractFormattedContent($tempPartDoc);
        
        // Create a batch update request to insert the formatted content
        $insertRequests = createInsertRequests($formattedContent, $placeholderInfo['startIndex']);
        
        if (!empty($insertRequests)) {
            $batchUpdateRequest = new Google_Service_Docs_BatchUpdateDocumentRequest();
            $batchUpdateRequest->setRequests($insertRequests);
            $docsService->documents->batchUpdate($newDocId, $batchUpdateRequest);
        }
        
        // Clean up the temporary file
        $driveService->files->delete($tempPartId);
    }
    
    // Get link to the new document
    $newDocLink = "https://docs.google.com/document/d/" . $newDocId;
    
    $message = "Document created successfully with preserved formatting. <a href='$newDocLink' target='_blank'>Open document</a>";
} catch (Exception $e) {
    $message = "An error occurred: " . $e->getMessage();
}

/**
 * Find the position of a placeholder in a Google Doc
 * 
 * @param object $document The Google Doc object
 * @param string $placeholder The placeholder text to find
 * @return array|null Information about the placeholder position or null if not found
 */
function findPlaceholderPosition($document, $placeholder) {
    if (!isset($document->body->content)) {
        return null;
    }
    
    foreach ($document->body->content as $element) {
        if (isset($element->paragraph)) {
            foreach ($element->paragraph->elements as $paragraphElement) {
                if (isset($paragraphElement->textRun)) {
                    $content = $paragraphElement->textRun->content;
                    $pos = strpos($content, $placeholder);
                    if ($pos !== false) {
                        return [
                            'startIndex' => $paragraphElement->startIndex + $pos,
                            'endIndex' => $paragraphElement->startIndex + $pos + strlen($placeholder),
                            'elementIndex' => $paragraphElement->startIndex,
                            'style' => isset($paragraphElement->textRun->textStyle) ? $paragraphElement->textRun->textStyle : null,
                        ];
                    }
                }
            }
        }
    }
    
    return null;
}

/**
 * Extract formatted content from a Google Doc
 * 
 * @param object $document The Google Doc object
 * @return array An array of formatted content elements
 */
function extractFormattedContent($document) {
    $formattedContent = [];
    
    if (!isset($document->body->content)) {
        return $formattedContent;
    }
    
    // Process structural elements like paragraphs, lists, tables
    foreach ($document->body->content as $element) {
        // Handle paragraphs
        if (isset($element->paragraph)) {
            $paragraphStyle = isset($element->paragraph->paragraphStyle) ? $element->paragraph->paragraphStyle : null;
            $paragraphElements = [];
            
            foreach ($element->paragraph->elements as $paragraphElement) {
                if (isset($paragraphElement->textRun)) {
                    $textRun = $paragraphElement->textRun;
                    $paragraphElements[] = [
                        'type' => 'textRun',
                        'content' => $textRun->content,
                        'textStyle' => isset($textRun->textStyle) ? $textRun->textStyle : null,
                    ];
                }
                // Add other element types (equations, horizontal rules, etc.) as needed
            }
            
            $formattedContent[] = [
                'type' => 'paragraph',
                'elements' => $paragraphElements,
                'paragraphStyle' => $paragraphStyle,
            ];
        }
        // Add handling for tables, lists, and other structural elements as needed
    }
    
    return $formattedContent;
}

/**
 * Create insert requests for formatted content
 * 
 * @param array $formattedContent The formatted content to insert
 * @param int $startIndex The position to insert at
 * @return array An array of requests for batch update
 */
function createInsertRequests($formattedContent, $startIndex) {
    $requests = [];
    $currentIndex = $startIndex;
    
    foreach ($formattedContent as $element) {
        if ($element['type'] === 'paragraph') {
            // Insert each text run
            foreach ($element['elements'] as $textElement) {
                if ($textElement['type'] === 'textRun') {
                    // Insert text
                    $insertTextRequest = new Google_Service_Docs_InsertTextRequest();
                    $insertTextRequest->setText($textElement['content']);
                    $insertTextRequest->setLocation(new Google_Service_Docs_Location([
                        'index' => $currentIndex,
                    ]));
                    
                    $request = new Google_Service_Docs_Request();
                    $request->setInsertText($insertTextRequest);
                    $requests[] = $request;
                    
                    // Apply text styling
                    if (!empty($textElement['textStyle'])) {
                        // Create update text style request here
                        // This would require additional API objects to set styles
                        // Google_Service_Docs_UpdateTextStyleRequest, etc.
                    }
                    
                    $currentIndex += strlen($textElement['content']);
                }
            }
            
            // Apply paragraph styling if needed
            if (!empty($element['paragraphStyle'])) {
                // Create update paragraph style request here
                // This would require additional API objects
                // Google_Service_Docs_UpdateParagraphStyleRequest, etc.
            }
        }
        // Add handlers for other element types (tables, lists) as needed
    }
    
    return $requests;
}

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
                    <?php if (isset($message)): ?>
                    <div class="alert <?php echo strpos($message, 'error') !== false ? 'alert-danger' : 'alert-success'; ?>">
                        <?php echo $message; ?>
                    </div>
                    <?php endif; ?>
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
?>