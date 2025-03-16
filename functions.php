<?php
/* import vendor autoload form root directory */
require_once ABSPATH .'vendor/autoload.php';
require_once __DIR__ .'/functions/google_api.php';
require_once __DIR__ .'/functions/onedrive_api.php';
require_once __DIR__ .'/ajax_function.php';

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
    wp_enqueue_style('jquery.steps', get_template_directory_uri() . '/assets/css/jquery.steps.css');
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
    wp_enqueue_script('jquery.steps', get_template_directory_uri() . '/assets/js/jquery.steps.min.js', array(), '1.0', true);
    wp_enqueue_script('phosphor-icon', 'https://unpkg.com/@phosphor-icons/web@2.1.1', array(), '2.1.1', true);
    wp_enqueue_script('main', get_template_directory_uri() . '/assets/js/main.js', array(), '1.0', true);

    wp_localize_script('main', 'AJAX', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_js_css');

function createDatabase(){
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    # create tags table
    $asltags = $wpdb->prefix . 'asltags';
    $sql = "CREATE TABLE $asltags (
        `tagID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
        `tagName` varchar(255) NOT NULL,
        `tagDescription` varchar(255) NOT NULL,
        `tagModified` datetime NOT NULL,
        PRIMARY KEY  (tagID)
    ) $charset_collate;";
    dbDelta( $sql );

    # Create datasource table
    $asldatasource = $wpdb->prefix . 'asldatasource';
    $sql = "CREATE TABLE $asldatasource (
        `sourceID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
        `sourceName` varchar(255) NOT NULL,
        `api` varchar(255) NOT NULL,
        `username` varchar(255) NULL,
        `password` varchar(255) NULL,
        `token` varchar(255) NULL,
        `type` varchar(255) NULL,
        `sourceModified` datetime NOT NULL,
        PRIMARY KEY  (sourceID)
    ) $charset_collate;";
    dbDelta( $sql );

    # create child datasource table
    $aslchilddatasource = $wpdb->prefix . 'aslchilddatasource';
    $sql = "CREATE TABLE $aslchilddatasource (
        `childID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
        `sourceID` mediumint(9) UNSIGNED NOT NULL REFERENCES `{$asldatasource}`(`sourceID`),
        `childName` varchar(255) NOT NULL,
        `childDescription` varchar(255) NULL,
        `api` varchar(255) NOT NULL,
        `header` text NULL,
        `searchfield` text NULL,
        `childModified` datetime NOT NULL,
        PRIMARY KEY  (childID)
    ) $charset_collate;";
    dbDelta( $sql );

    # template table
    $asltemplate = $wpdb->prefix . 'asltemplate';
    $sql = "CREATE TABLE $asltemplate (
        `templateID` mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
        `tagID` mediumint(9) UNSIGNED NULL REFERENCES `{$asltags}`(`tagID`),
        `templateName` varchar(255) NOT NULL,
        `gFileID` varchar(255) NOT NULL,
        `gDestinationFolderID` varchar(255) NULL,
        `gDestinationFilename` varchar(255) NULL,
        `userID` mediumint(9) UNSIGNED NULL,
        `templateModified` datetime NOT NULL,
        PRIMARY KEY  (templateID)
    ) $charset_collate;";
    dbDelta( $sql );

    # replacement table
    $aslreplacement = $wpdb->prefix . 'aslreplacement';
    $sql = "CREATE TABLE $aslreplacement (
        `templateID` mediumint(9) UNSIGNED NOT NULL REFERENCES `{$asltemplate}`(`templateID`),
        `childID` mediumint(9) UNSIGNED NOT NULL REFERENCES `{$aslchilddatasource}`(`childID`),
        `dataReplace` text NOT NULL,
        PRIMARY KEY  (templateID, childID)
    ) $charset_collate;";
    dbDelta( $sql );

    # create document table
    $asldocument = $wpdb->prefix . 'asldocument';
    $sql = "CREATE TABLE $asldocument (
        `documentID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `templateID` mediumint(9) UNSIGNED NULL REFERENCES `{$asltemplate}`(`templateID`),
        `tagID` mediumint(9) UNSIGNED NULL REFERENCES `{$asltags}`(`tagID`),
        `userID` mediumint(9) UNSIGNED NULL,
        `documentName` varchar(255) NOT NULL,
        `gFileID` varchar(255) NOT NULL,
        `gDestinationFolderID` varchar(255) NULL,
        `documentModified` datetime NOT NULL,
        PRIMARY KEY  (documentID)
    ) $charset_collate;";
    dbDelta( $sql );

    # access control table
    $aslusermanager = $wpdb->prefix . 'aslusermanager';
    $sql = "CREATE TABLE $aslusermanager (
        `userID` mediumint(9) UNSIGNED NOT NULL,
        `managerID` mediumint(9) UNSIGNED NOT NULL,
        PRIMARY KEY  (userID, managerID)
    ) $charset_collate;";
    dbDelta( $sql );
}
add_action('after_switch_theme', 'createDatabase');

# encrypt and decrypt function
function asl_encrypt($stringToHandle = "",$encryptDecrypt = 'e'){
    // Set secret keys
    $secret_key = 'ASL'; // Change this!
    $secret_iv = 'QLCV'; // Change this!
    $key = hash('sha256',$secret_key);
    $iv = substr(hash('sha256',$secret_iv),0,16);
    // Check whether encryption or decryption
    if($encryptDecrypt == 'e'){
       // We are encrypting
       $output = base64_encode(openssl_encrypt($stringToHandle,"AES-256-CBC",$key,0,$iv));
    }else if($encryptDecrypt == 'd'){
       // We are decrypting
       $output = openssl_decrypt(base64_decode($stringToHandle),"AES-256-CBC",$key,0,$iv);
    }

    return $output;
}

# refresh token everyday
function refresh_token($api_url, $username, $password)
{

    $user = array(
        'username' => $username,
        'password' => $password,
    );

    # authenticate to get token
    $jwt = wp_remote_post(
        $api_url,
        array(
            'method'        => 'POST',
            'timeout'       => '60',
            'body'          => $user,
        )
    );

    $token = json_decode(wp_remote_retrieve_body($jwt));
    // print_r($token);
    if (!$token->token) {
        return false;
    } else {
        return $token->token;
    }
};

# check token is valid or not
function check_token($api_url, $token){
    $api = $api_url . '/wp-json/qlcv/v1/checktoken';
    $check = json_decode(asl_api($api, $token, 'POST'));
    if ($check->code == 'success') {
        return true;
    } else {
        return false;
    }
}

# call any api with authentication token
function asl_api($api, $token, $method, $body='') {
    $args = array(
        'method'    => $method,
        'timeout'   => '120',
        'headers'   => array(
            'Content-Type'  => 'application/json; charset=utf-8',
            'Authorization' => $token,
        ),
        'body'      => $body,
        'sslverify' => true,
    );

    $response = wp_remote_post(
        $api,
        $args
    );

    if (is_wp_error($response)) {
        return $response;
    } else {
        // $response_body = json_decode(wp_remote_retrieve_body($response));
        return wp_remote_retrieve_body($response);
    }
}

# get Google Client and hook it to init action
function getGoogleClient(){
    global $client;
    
    $client = new Google_Client();
    $client->setApplicationName("ASL Contract");
    $client->addScope(Google_Service_Drive::DRIVE);
    $client->setAccessType('offline');
    $client->setAuthConfig(ABSPATH . GG_JSON_API_FILE);
    return $client;
}
add_action('init', 'getGoogleClient');



# get all staff id from aslusermanager table
function get_staff_ids($userID, $level = false) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aslusermanager';
    $staff = $wpdb->get_results("SELECT * FROM $table_name WHERE managerID = $userID");
    $staff_id = [];
    foreach ($staff as $s) {
        $staff_id[] = $s->userID;

        # if $level, get all staff id where manager is $s->userID, and push userID to $staff_id
        if ($level) {
            $staffs = $wpdb->get_results("SELECT * FROM $table_name WHERE managerID = $s->userID");
            foreach ($staffs as $st) {
                $staff_id[] = $st->userID;
            }
        }
    }
    # return unique staff id
    return array_unique($staff_id);
}

# get all manager id from aslusermanager table
function get_manager_ids($userID) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aslusermanager';
    $managers = $wpdb->get_results("SELECT * FROM $table_name WHERE userID = $userID");
    $manager_id = [];
    foreach ($managers as $m) {
        $manager_id[] = $m->managerID;
    }
    return $manager_id;
}

# hide admin bar for all users except admin
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
        show_admin_bar(false);
}

# replace all vietnamese character in string to english character
function convert_vi_to_en($str) {
    # list of vietnamese character and its english character
    $vietnamese = array(
        'a' => array('á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ'),
        'd' => array('đ'),
        'e' => array('é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ'),
        'i' => array('í', 'ì', 'ỉ', 'ĩ', 'ị'),
        'o' => array('ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ'),
        'u' => array('ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự'),
        'y' => array('ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ'),
        'A' => array('Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ'),
        'D' => array('Đ'),
        'E' => array('É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ'),
        'I' => array('Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị'),
        'O' => array('Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ'),
        'U' => array('Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự'),
        'Y' => array('Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ'),
        '_' => array(' ', '?', '.', ',', '/')
    );

    # replace all vietnamese character to english character
    foreach ($vietnamese as $en => $vi) {
        $str = str_replace($vi, $en, $str);
    }

    return $str;
}

function textkey($text, $addstr = '_text') {
    # we have a $text with format: "{keyname}" and we want to insert $addstr after "keyname"
    # for example: $text = "{world}", $addstr = '_text' => $text = "{world_text}"
    # so we need to replace "{world}" to "{world_text}"
    $text = preg_replace('/{([a-zA-Z0-9_]+)}/', '{${1}' . $addstr . '}', $text);
    return $text;
}

function is_valid_formula($formula) {
    // Cho phép các số, toán tử +, -, *, /, %, dấu ngoặc đơn và dấu chấm
    $pattern = '/^[\d\s\+\-\*\/\%\(\)\.]+$/';
    return preg_match($pattern, $formula);
}

function remove_seperator_in_number($number) {
    return str_replace(',', '', $number);
}