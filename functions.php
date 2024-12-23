<?php
/* import vendor autoload form root directory */
require_once ABSPATH .'vendor/autoload.php';
require_once __DIR__ .'/functions/google_api.php';
require_once __DIR__ .'/functions/onedrive_api.php';

use Google\Client as Google_Client;
use Google\Service\Drive as Google_Service_Drive;

/* enqueue js css function */
function enqueue_js_css(){
    /* 
    * Enqueue style css file
    */
    wp_enqueue_style('feather', get_template_directory_uri() . '/assets/vendors/feather/feather.css');
    wp_enqueue_style('mdi', get_template_directory_uri() . '/assets/vendors/mdi/css/materialdesignicons.min.css');
    wp_enqueue_style('themify-icons', get_template_directory_uri() . '/assets/vendors/ti-icons/css/themify-icons.css');
    wp_enqueue_style('font-awesome', get_template_directory_uri() . '/assets/vendors/font-awesome/css/font-awesome.min.css');
    wp_enqueue_style('typicons', get_template_directory_uri() . '/assets/vendors/typicons/typicons.css');
    wp_enqueue_style('simple-line-icons', get_template_directory_uri() . '/assets/vendors/simple-line-icons/css/simple-line-icons.css');
    wp_enqueue_style('vendor.bundle.base', get_template_directory_uri() . '/assets/vendors/css/vendor.bundle.base.css');
    wp_enqueue_style('bootstrap-datepicker', get_template_directory_uri() . '/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css');
    /* Plugin css for page have select2 element */
    wp_enqueue_style('select2', get_template_directory_uri() . '/assets/vendors/select2/select2.min.css');
    wp_enqueue_style('bootstrap-select2', get_template_directory_uri() . '/assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css');
    wp_enqueue_style('main-style', get_template_directory_uri() . '/assets/css/style.css');
    wp_enqueue_style('style', get_stylesheet_uri());    
    
    /*
    * Enqueue js file
    */
    wp_enqueue_script('jquery');
    wp_enqueue_script('bundle.base', get_template_directory_uri() . '/assets/vendors/js/vendor.bundle.base.js', array(), '1.0', true);
    wp_enqueue_script('bootstrap-datepicker', get_template_directory_uri() . '/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js', array(), '1.0', true);
    wp_enqueue_script('off-canvas', get_template_directory_uri() . '/assets/js/off-canvas.js', array(), '1.0', true);
    wp_enqueue_script('template', get_template_directory_uri() . '/assets/js/template.js', array(), '1.0', true);
    wp_enqueue_script('settings', get_template_directory_uri() . '/assets/js/settings.js', array(), '1.0', true);
    wp_enqueue_script('hoverable-collapse', get_template_directory_uri() . '/assets/js/hoverable-collapse.js', array(), '1.0', true);
    wp_enqueue_script('todolist', get_template_directory_uri() . '/assets/js/todolist.js', array(), '1.0', true);
    wp_enqueue_script('select2.base', get_template_directory_uri() . '/assets/vendors/select2/select2.min.js', array(), '1.0', true);
    wp_enqueue_script('select2', get_template_directory_uri() . '/assets/js/select2.js', array(), '1.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_js_css');


add_action('wp_ajax_select_google_drive_file', 'process_google_drive_file_selection');
add_action('wp_ajax_nopriv_select_google_drive_file', 'process_google_drive_file_selection');

function process_google_drive_file_selection(){
    $accessTokenJson = urldecode($_GET['token']);
    $accessToken = json_decode($accessTokenJson, true);

    $client = new Google_Client();
    $client->setAuthConfig(__DIR__ . '/asl-contract-client-oauth.json');
    $client->setAccessToken($accessToken);
    $service = new Google_Service_Drive($client);

    // Logic để cho phép người dùng chọn file (phần này cần dùng Javascript, không thể làm trực tiếp bằng PHP)
    // ... (Ví dụ: Sử dụng thư viện JavaScript của bên thứ 3) ...  Giả sử file ID được truyền từ Javascript qua AJAX
    $fileId = isset($_GET['fileId']) ? $_GET['fileId'] : null; 

    if ($fileId) {
        try {
            $file = $service->files->get($fileId, array('fields' => 'id, name, mimeType, size'));
            $fileData = [
                'success' => true,
                'fileId' => $file->getId(),
                'fileName' => $file->getName(),
                'fileType' => $file->getMimeType(),
                'fileSize' => $file->getSize(),
            ];
            header('Content-Type: application/json');
            echo json_encode($fileData);
        } catch (Exception $e) {
            $fileData = ['success' => false, 'error' => $e->getMessage()];
            header('Content-Type: application/json');
            echo json_encode($fileData);
        }
    } else {
        $fileData = ['success' => false, 'error' => 'No file selected'];
        header('Content-Type: application/json');
        echo json_encode($fileData);
    }
    wp_die();
}