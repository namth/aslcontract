<?php
/* 
 * Template Name: Delete and View Document
 * Description: This template is used to delete and view document by the google fileID
 */
use Google\Service\Drive;

global $wpdb;
global $client;

# get google fileID from query string
$documentID = $_GET['documentID'];
$document = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}asldocument WHERE documentID = $documentID");
$gFileID = $document->gFileID;
$action = $_GET['action']; // action = delete or view

$service = new Drive($client);
// print_r($gFileID);

# if action = delete, then delete document
if ($action == 'view') {

    try {
        $file = $service->files->get($gFileID, array(
            'fields' => 'webViewLink' // Chỉ lấy các link cần thiết
        ));

        // print_r($file); // In ra thông tin file
        // Kiểm tra xem link có tồn tại không
        if (isset($file->webViewLink)) {
            wp_redirect($file->webViewLink); // Chuyển hướng đến link xem trực tuyến (view)
            exit;
        } else {
            wp_redirect(home_url('/list-document')); // Chuyển hướng về trang danh sách file
            exit;
        }
    } catch (Exception $e) {
        return ['error' => $e->getMessage()]; // Trả về thông báo lỗi
    }
} else if ($action == 'delete') {
    $table_name = $wpdb->prefix . 'asldocument';
    $wpdb->delete($table_name, ['documentID' => $documentID]);

    # delete file from Google Drive
    try {
        $service->files->delete($gFileID);
    } catch (Exception $e) {
        return ['error' => $e->getMessage()]; // Trả về thông báo lỗi
    }

    wp_redirect(home_url('/list-document'));
    exit;
} else if ($action == 'download') {
    $extension = $_POST['type'] ?? 'docx';
    switch ($type) {
        case 'pdf':
            $mimeType = 'application/pdf';
            break;
        default:
            $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            break;
    }

    try {
        $response = $service->files->export($gFileID, $mimeType, array(
            'alt' => 'media'));
        $content = $response->getBody()->getContents();

        $filename = $document->documentName . '.docx';
        # download file to docx
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $content;
        exit;
    } catch (Exception $e) {
        return ['error' => $e->getMessage()]; // Trả về thông báo lỗi
    }
}