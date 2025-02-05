<?php
/* 
* File: addnew_template.php, main.js
* Function: get_datasource
*/
use Google\Client as Google_Client;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Drive\DriveFile as Google_Service_Drive_File;


add_action('wp_ajax_get_datasource', 'get_datasource');
function get_datasource(){
    global $wpdb;
    $childID = $_POST['childID'];

    # get child datasource by childID
    $table_name = $wpdb->prefix . 'aslchilddatasource';
    $datasource = $wpdb->get_row("SELECT * FROM $table_name WHERE childID = $childID");

    if($datasource->header){
        ?>
        <div class="data_replace_box d-flex align-items-center justify-content-center gap-4 w-100">
            <div class="d-flex justify-content-center flex-column text-center">
                <i class="ph ph-database icon-lg p-2"></i>
                <div class="d-flex flex-column">
                    <span class="fw-bold">
                        <?php echo $datasource->childName; ?>
                    </span>
                </div>
            </div>
            <div class="replace_area d-flex align-items-center flex-column justify-content-center gap-3">
                <div class="replace_header d-flex justify-content-center align-items-center p-2 gap-3">
                    <i class="ph ph-puzzle-piece icon-md"></i>
                    <span class="w165">Trường dữ liệu</span>
                    <i class="ph ph-arrow-circle-right icon-md"></i>
                    <span class="w300">Từ khóa thay thế</span>
                </div>
                <?php 
                    $datareplace = explode(',', $datasource->header);
                    
                    foreach($datareplace as $value){
                        $value = trim($value);
                        echo '<div class="replace_field d-flex justify-content-center align-items-center gap-3">
                                <i class="ph ph-puzzle-piece icon-md"></i>
                                <span class="w165">' . $value . '</span>
                                <i class="ph ph-arrow-circle-right icon-md"></i>
                                <input type="text" class="form-control w300" name="data-' . $childID . '#' . $value . '" value="{' . $value . '}">
                            </div>';
                    }
                ?>
            </div>
        </div>
        <?php
    }
    exit;
}


/* 
* File: main.js, create_document.php
*/
add_action('wp_ajax_search_data', 'search_data');

function search_data(){
    global $wpdb;

    # get childID from data-childID attribute
    $childID = $_POST['childID'];
    $search = $_POST['search'];

    # get child datasource by childID
    $table_name = $wpdb->prefix . 'aslchilddatasource';
    $childdatasource = $wpdb->get_row("SELECT * FROM $table_name WHERE childID = $childID");

    # get datasource from sourceID
    $table_name = $wpdb->prefix . 'asldatasource';
    $datasource = $wpdb->get_row("SELECT * FROM $table_name WHERE sourceID = $childdatasource->sourceID");

    # switch case to get data from different type of datasource
    switch($datasource->type){
        case 'aslapi':
            # get data from api
            $get_header_api = $datasource->api . '/wp-json/qlcv/v1/asldata/' . $childdatasource->api . '?field=' . $childdatasource->header;

            # create search query string
            $field_array = explode(',', $childdatasource->searchfield);
            $search_query_arr = [];
            foreach($field_array as $field){
                $search_query_arr[] = $field . ' LIKE "%' . $search . '%"';
            }
            $search_query = "&where=(" . implode(' OR ', $search_query_arr) . ")&limit=5";

            # get token and call api
            $token = $datasource->token;
            $api = $datasource->api . '/wp-json/qlcv/v1/gettoken';
            $username = asl_encrypt($datasource->username, 'd');
            $password = asl_encrypt($datasource->password, 'd');
                
            # check token is valid or not, if not valid, call gettoken api to get new token
            if (!check_token($datasource->api, $token)) {
                $token = refresh_token($api, $username, $password);
                # update token to database
                $wpdb->update("{$wpdb->prefix}asldatasource", ['token' => $token], ['sourceID' => $datasource->sourceID]);
            }
            $search_result = asl_api($get_header_api . $search_query, $token, 'GET');

            $results = json_decode($search_result);
            # show search result
            if(!empty($results)){
                echo '<div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th> # </th>';
                foreach($field_array as $field){
                    echo '<th>' . $field . '</th>';
                }
                echo '              <th></th>
                                </tr>
                                </thead>
                                <tbody>';

                foreach($results as $result){
                    $data = (array) $result;
                    echo '          <tr class="select_data">
                                        <td class="py-1">
                                            <i class="ph ph-file-magnifying-glass icon-md"></i>
                                        </td>';
                    foreach($field_array as $field){
                        echo '          <td>' . $data[$field] . '</td>';
                    }
                    echo '              <td>
                                            <a href="#" class="nav-link accept_select" data-asldata=\'' . asl_encrypt(json_encode($data)) . '\' data-childid="' . $childID . '">
                                                <i class="ph ph-check fa-150p"></i>
                                            </a>
                                        </td>
                                    </tr>';
                }

                echo '          </tbody>
                            </table>
                        </div>';
            } else {
                echo '<div class="alert alert-danger d-flex align-items-center" role="alert"><i class="ph ph-cloud-x me-2 fa-150p"></i> Không tìm thấy dữ liệu</div>';
            }
            break;
            
        case 'aslsql':
            # get data from sql
            $table_name = $wpdb->prefix . $childdatasource->api;
            $field_array = explode(',', $childdatasource->searchfield);
            $search_query_arr = [];
            foreach($field_array as $field){
                $search_query_arr[] = $field . ' LIKE "%' . $search . '%"';
            }
            $search_query = "WHERE " . implode(' OR ', $search_query_arr) . " LIMIT 5";
            $search_result = $wpdb->get_results("SELECT $childdatasource->header FROM $table_name $search_query");

            // echo "SELECT $childdatasource->header FROM $table_name $search_query";
            # show search result
            if(!empty($search_result)){
                echo '<div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th> # </th>';
                foreach($field_array as $field){
                    echo '<th>' . $field . '</th>';
                }
                echo '              <th></th>
                                </tr>
                                </thead>
                                <tbody>';

                foreach($search_result as $result){
                    $data = (array) $result;
                    echo '          <tr class="select_data">
                                        <td class="py-1">
                                            <i class="ph ph-file-magnifying-glass icon-md"></i>
                                        </td>';
                    foreach($field_array as $field){
                        echo '          <td>' . $data[$field] . '</td>';
                    }
                    echo '              <td>
                                            <a href="#" class="nav-link accept_select" data-asldata=\'' . asl_encrypt(json_encode($data)) . '\' data-childid="' . $childID . '">
                                                <i class="ph ph-check fa-150p"></i>
                                            </a>
                                        </td>
                                    </tr>';
                }

                echo '          </tbody>
                            </table>
                        </div>';
            } else {
                echo '<div class="alert alert-danger d-flex align-items-center" role="alert"><i class="ph ph-funnel-x me-2 fa-150p"></i> Không tìm thấy dữ liệu</div>';
            }

            
            break;
    }

    // echo json_encode($search_result);
    exit;
}

/* 
* File: main.js, create_document.php
*/
add_action('wp_ajax_decrypt_data', 'decrypt_data');
function decrypt_data() {
    $jsondata = json_decode(asl_encrypt($_POST['jsondata'], 'd'));

    echo '<div class="table-responsive">
                <table class="table table-bordered">';
    foreach($jsondata as $key => $value){
        echo '<tr>
                <td class="py-1">
                    <i class="ph ph-puzzle-piece icon-md"></i>
                </td>
                <td>' . $key . '</td>
                <td>
                    <i class="ph ph-arrow-circle-right icon-md"></i>
                </td>
                <td>' . $value . '</td>
            </tr>';
    }
    echo '</table>
        </div>';

    exit;
}

/* 
* File: main.js, create_document.php
*/
add_action('wp_ajax_create_document', 'create_document');
function create_document() {
    global $wpdb;
    global $client;

    if (isset($_POST['post_contract_field']) && wp_verify_nonce($_POST['post_contract_field'], 'post_contract')) {
        # get post data
        $newfilename = $_POST['contract_name'];
        $ls_dataid = explode(',', $_POST['ls_dataid']);
        $templateID = $_POST['templateID'];
        $template = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}asltemplate WHERE templateID = $templateID");
        $sourceFileId = $template->gFileID;
        $folderId = $template->gDestinationFolderID;
        $current_user = wp_get_current_user();
    
        # if !empty $ls_dataid, get replace data from aslreplacements table
        if (!empty($ls_dataid)) {
            $replacements = [];
            foreach ($ls_dataid as $childID) {
                # get data_replace from post data
                $data_replace = json_decode(asl_encrypt($_POST['selectdata_' . $childID], 'd'));
    
                // print_r($data_replace->name);
                # get replacement data from aslreplacement table by childID and templateID
                $replacement = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}aslreplacement WHERE templateID = $templateID AND childID = $childID");
    
                # if have replacement data, decode it and replace value in $data_replace
                if ($replacement) {
                    $replacement = json_decode($replacement->dataReplace, true);
                    foreach ($replacement as $key => $value) {
                        $replacement[$key] = $data_replace->$value;
                    }
                    $replacements = array_merge($replacements, $replacement);
                }
            }
        }
        
        # replace newfilename with all data in $replacements
        $newfilename = str_replace(array_keys($replacements), array_values($replacements), $newfilename);
    
        /* Clone docs file from source template file */
        $new_file = new Google_Service_Drive_File();
        $optParams = array(
            'folderId' => $folderId,
            'newfilename' => $newfilename,
            'email' => $current_user->user_email,
        );
        $copyfileID = google_clone_file($sourceFileId, $new_file, $optParams);
        
        # if clone file success, add result to database and replace text in file with $replacements
        if ($copyfileID) {
            # add result to database: asldocument table
            $table_name = $wpdb->prefix . 'asldocument';
            $wpdb->insert($table_name, [
                'templateID' => $templateID,
                'userID' => get_current_user_id(),
                'documentName' => $newfilename,
                'gFileID' => $copyfileID,
                'gDestinationFolderID' => $folderId,
                'documentModified' => current_time('mysql'),
            ]);
    
            $notification = '<div class="alert alert-success" role="alert"> Tạo file thành công. File ID: ' . $copyfileID . '</div>';
        
            $result = google_docs_replaceText($copyfileID, $replacements);    
        } else {
            $notification =  '<div class="alert alert-success" role="alert"> Clone thất bại' . '</div>';
        }
    }

    echo $notification;
    exit;
}
