<?php
/* 
* File: addnew_template.php, main.js
* Function: get_datasource
*/
use Google\Client as Google_Client;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Drive\DriveFile as Google_Service_Drive_File;
use Google\Service\Drive\Permission as Google_Service_Drive_Permission;
use Google\Service\Docs as Google_Service_Docs;
use Google\Service\Docs\SubstringMatchCriteria as Google_Service_SubstringMatchCriteria;
use Google\Service\Docs\Request as Google_Service_Docs_Request;
use Google\Service\Docs\BatchUpdateDocumentRequest as Google_Service_Docs_BatchUpdateDocumentRequest;
use PHPViet\NumberToWords\Transformer;


add_action('wp_ajax_get_datasource', 'get_datasource');
function get_datasource(){
    global $wpdb;
    $childID = $_POST['childID'];

    # get child datasource by childID
    $table_name = $wpdb->prefix . 'aslchilddatasource';
    $datasource = $wpdb->get_row("SELECT * FROM $table_name WHERE childID = $childID");

    if($datasource->header){
        ?>
        <div class="data_replace_box d-flex align-items-center justify-content-center gap-4 w-100" id="child-<?php echo $childID; ?>">
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
                    <span class="w110">Loại dữ liệu</span>
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
                                <select class="js-example-basic-single w110" id="type" name="datatype-' . $childID . '#' . $value . '">
                                    <option value="text">Text</option>
                                    <option value="img">Image URL</option>
                                    <option value="number">Number</option>
                                </select>
                                <input type="text" class="form-control w268" name="data-' . $childID . '#' . $value . '" value="{' . $value . '}">
                                <a class="remove_field nav-link text-danger" href="#">
                                    <i class="ph ph-trash"></i>
                                </a>
                            </div>';
                    }
                ?>
            </div>
            <a id="remove_datasource" class="remove_datasource nav-link" href="#" data-childid="<?php echo $childID; ?>">
                <i class="ph ph-x icon-md"></i>
            </a>
        </div>
        <?php
    }
    exit;
}

/* 
* File: main.js, addnew_template.php
* Function: add_formula
*/
add_action('wp_ajax_add_formula', 'add_formula');
function add_formula() {
    global $wpdb;

    $formula_count = $_POST['formula_count'] + 1;
    $custom_type = $_POST['custom'];
    switch($custom_type){
        case 'formula':
            $formula_div = '
                <div class="data_replace_box d-flex align-items-center justify-content-center gap-4 w-100" id="formula-' . $formula_count . '">
                    <div class="replace_area d-flex align-items-center flex-column justify-content-center gap-3">
                        <div class="replace_field d-flex justify-content-center align-items-center p-2 gap-3">
                            <i class="ph ph-math-operations icon-md"></i>
                            <input type="text" class="form-control w198" name="formula_key-' . $formula_count . '" placeholder="Nhập từ khóa sẽ thay thế trong file" value="{formula_' . $formula_count . '}">
                            <i class="ph ph-equals icon-md"></i>
                            <input type="text" class="form-control w315" name="formula_value-' . $formula_count . '" placeholder="Nhập công thức">
                        </div>
                    </div>
                    <a id="remove_formula" class="remove_datasource nav-link" href="#">
                        <i class="ph ph-x icon-md"></i>
                    </a>
                </div>
            ';
            break;

        case 'date':
            $formula_div = '
                <div class="data_replace_box d-flex align-items-center justify-content-center gap-4 w-100" id="formula-' . $formula_count . '">
                    <div class="replace_area d-flex align-items-center flex-column justify-content-center gap-3">
                        <div class="replace_field d-flex justify-content-center align-items-center p-2 gap-3">
                            <i class="ph ph-calendar icon-md"></i>
                            <input type="text" class="form-control w198" name="date_key-' . $formula_count . '" placeholder="Nhập từ khóa sẽ thay thế trong file" value="{date_' . $formula_count . '}">
                            <i class="ph ph-sun-horizon icon-md"></i>
                            <input type="text" class="form-control w315" name="date_format-' . $formula_count . '" placeholder="Nhập định dạng ngày tháng" value="d/m/Y">
                        </div>
                    </div>
                    <a id="remove_formula" class="remove_datasource nav-link" href="#">
                        <i class="ph ph-x icon-md"></i>
                    </a>
                </div>';
            break;

        case 'blank':
            $formula_div = '
                <div class="data_replace_box d-flex align-items-center justify-content-center gap-4 w-100" id="formula-' . $formula_count . '">
                    <div class="replace_area d-flex align-items-center flex-column justify-content-center gap-3">
                        <div class="replace_field d-flex justify-content-center align-items-center p-2 gap-3">
                            <i class="ph ph-brackets-curly icon-md"></i>
                            <input type="text" class="form-control w198" name="blank_key-' . $formula_count . '" placeholder="Nhập từ khóa sẽ thay thế trong file" value="{blank_' . $formula_count . '}">
                            <i class="ph ph-article-ny-times icon-md"></i>
                            <select class="js-example-basic-single w110" id="type" name="blank_type-' . $formula_count . '">
                                <option value="text">Text</option>
                                <option value="img">Image URL</option>
                                <option value="number">Number</option>
                            </select>
                            <input type="text" class="form-control w315" name="blank_default-' . $formula_count . '" placeholder="Nhập nội dung mặc định">
                        </div>
                    </div>
                    <a id="remove_formula" class="remove_datasource nav-link" href="#">
                        <i class="ph ph-x icon-md"></i>
                    </a>
                </div>';
            break;

        case 'multiblock':
            # get all datasource from database and show here
            $datasources = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}asldatasource");

            if ($datasources) {
                foreach ($datasources as $datasource) {
                    $datasource_div .= '<div class="datasource d-flex align-items-center flex-column justify-content-center gap-3">';
                    $datasource_div .= '<h4>' . $datasource->sourceName . '</h4>';

                    # get child datasource from parent and show here
                    $child_datasources = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aslchilddatasource WHERE sourceID = {$datasource->sourceID}");
                    # if have child datasource then show here
                    if ($child_datasources) {
                        $datasource_div .= '<div class="d-flex align-items-center flex-wrap gap-3">';
                        foreach ($child_datasources as $child_datasource) {
                            $datasource_div .= '<a href="#" data-childid="' . $child_datasource->childID . '" 
                                    class="multiblock nav-link d-flex align-items-center flex-column justify-content-center gap-2 blockid-' . $child_datasource->childID . '">
                                    <i class="ph ph-database icon-md"></i><span>' . $child_datasource->childName . '</span></a>';
                        }
                        $datasource_div .= '</div>';
                    }

                    $datasource_div .= '</div>';
                }
            }
            $formula_div = '
                <div class="data_replace_box d-flex align-items-center justify-content-center flex-column gap-4 w-100" id="formula-' . $formula_count . '">
                    <div class="replace_area d-flex align-items-center flex-column justify-content-center gap-3">
                        <div class="replace_field d-flex justify-content-center align-items-center p-2 gap-3">
                            <i class="ph ph-diamonds-four icon-md"></i>
                            <input type="text" class="form-control w300" name="multi_key-' . $formula_count . '" placeholder="Nhập từ khóa sẽ thay thế trong file" value="{linkdata_' . $formula_count . '}">
                        </div>
                        <div id="list_datasource" class="flex-column align-items-center gap-3">
                            <div class="d-flex justify-content-center flex-wrap gap-3">
                            ' . $datasource_div . '
                            </div>
                            <a href="#" class="select_multiblock btn btn-info btn-icon-text fit-content d-flex align-items-center justify-content-center px-4 py-2" data-formula="' . $formula_count . '">
                                <i class="ph ph-git-fork btn-icon-prepend fa-150p"></i> Chọn
                            </a>
                        </div>
                    </div>
                    <a id="remove_formula" class="remove_datasource nav-link" href="#">
                        <i class="ph ph-x icon-md"></i>
                    </a>
                </div>
            ';
            break;
    }

    $result = ['formula_count' => $formula_count, 'formula_div' => $formula_div];
    echo json_encode($result);
    exit;
}

add_action('wp_ajax_show_multiblock', 'show_multiblock');
function show_multiblock(){
    global $wpdb;
    $multi_datasource = explode(',', $_POST['multi_datasource']);
    $formula_count = $_POST['formulaID'];
    echo '<div class="d-flex align-items-center justify-content-center gap-4 w-100">
            <div class="d-flex align-items-center justify-content-center gap-3">';
    # get the first item in $multi_datasource and remove it from $multi_datasource
    $childID = array_shift($multi_datasource);
    $table_name = $wpdb->prefix . 'aslchilddatasource';
    $childdatasource = $wpdb->get_row("SELECT * FROM $table_name WHERE childID = $childID");
    echo '<div id="first" class="replace_area d-flex justify-content-center align-items-center flex-column p-4 gap-3">
            <div class="d-flex justify-content-center align-items-center gap-3">
                <i class="ph ph-database icon-md"></i>
                <span class="w198">' . $childdatasource->childName . '</span>
            </div>';
    $field_array = explode(',', $childdatasource->header);
    echo '<div class="d-flex justify-content-center align-items-center gap-3">';
    foreach($field_array as $field){
        echo '<span class="fit-content badge btn-inverse-primary border-radius-9 mt-2">
                ' . $field . '
            </span>';
    }
    echo '</div>';
    echo '  <div class="d-flex justify-content-center align-items-center gap-3">
                <i class="ph ph-brackets-curly icon-md"></i>
                <input type="text" class="form-control w198" name="first_field-' . $formula_count . '" placeholder="Nhập vào trường dữ liệu sẽ lấy">
            </div>
            <div class="d-flex justify-content-center align-items-center gap-3">
                <i class="ph ph-git-commit icon-md"></i>
                <input type="text" class="form-control w198" name="first_seperator-' . $formula_count . '" placeholder="Nhập ký tự phân cách" value="PHP_EOL">
            </div>';
    echo '  <input type="hidden" name="first_datasource-' . $formula_count . '" value="' . $childID . '">
        </div>';

    # get the second item in $multi_datasource (if have) and remove it from $multi_datasource
    if(!empty($multi_datasource)){
        $childID = array_shift($multi_datasource);
        $table_name = $wpdb->prefix . 'aslchilddatasource';
        $childdatasource = $wpdb->get_row("SELECT * FROM $table_name WHERE childID = $childID");
        echo '<div id="link" class="d-flex align-items-center justify-content-center flex-column gap-3">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <i class="ph ph-flow-arrow icon-md"></i>
                    <input type="text" class="form-control w165" name="link-' . $formula_count . '" placeholder="Linked field">
                </div>
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <i class="ph ph-git-commit icon-md"></i>
                    <input type="text" class="form-control w165" name="seperator-' . $formula_count . '" placeholder="Nhập ký tự kết nối giữa 2 dataset" value=" : ">
                </div>
            </div>';
        echo '<div id="second" class="replace_area d-flex justify-content-center align-items-center flex-column p-4 gap-3">
                <div class="d-flex justify-content-center align-items-center gap-3">
                    <i class="ph ph-database icon-md"></i>
                    <span class="w198">' . $childdatasource->childName . '</span>
                </div>';
        $field_array = explode(',', $childdatasource->header);
        echo '<div class="d-flex justify-content-center align-items-center gap-3">';
        foreach($field_array as $field){
            echo '<span class="fit-content badge btn-inverse-primary border-radius-9 mt-2">
                    ' . $field . '
                </span>';
        }
        echo '</div>';
        echo '  <div class="d-flex justify-content-center align-items-center gap-3">
                    <i class="ph ph-brackets-curly icon-md"></i>
                    <input type="text" class="form-control w198" name="second_field-' . $formula_count . '" placeholder="Nhập từ khóa sẽ thay thế trong file">
                </div>
                <div class="d-flex justify-content-center align-items-center gap-3">
                    <i class="ph ph-git-commit icon-md"></i>
                    <input type="text" class="form-control w198" name="second_seperator-' . $formula_count . '" placeholder="Nhập định dạng ngày tháng" value=", ">
                </div>';
        echo '  <input type="hidden" name="second_datasource-' . $formula_count . '" value="' . $childID . '">
            </div>';
    }

    echo '</div>
        </div>';
    
    exit;
}

/* 
* File: main.js, create_document.php
*/
/* add_action('wp_ajax_choose_date', 'choose_date');

function choose_date(){
    global $wpdb;

    $childID = $_POST['childID'];
    $selectdate = explode('/', $_POST['selectdate']);
    $templateID = $_POST['templateID'];

    # get child datasource by childID
    $table_name = $wpdb->prefix . 'aslchilddatasource';
    $childdatasource = $wpdb->get_row("SELECT * FROM $table_name WHERE childID = $childID");
    
    $replacement = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}aslreplacement WHERE templateID = $templateID AND childID = $childID");
    
    # get data from sql
    $table_name = $wpdb->prefix . $childdatasource->api;
    $field_array = explode(',', $childdatasource->header);

    $date_data = array_combine($field_array, $selectdate);
    $data_replace = json_decode($replacement->dataReplace, true);
    # replace value in $data_replace with $date_data
    foreach($data_replace as $key => $value){
        $data_replace[$key] = $date_data[$value];
    }

    if(!empty($selectdate)){
        echo '  <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th> # </th>';
        foreach($data_replace as $key => $value){
            echo '              <th>' . $key . '</th>';
        }
        echo '              </tr>
                        </thead>
                        <tbody>';

        echo '              <tr class="select_data">
                                <td class="py-1">
                                    <i class="ph ph-calendar icon-md"></i>
                                </td>';
        foreach($data_replace as $key => $value){
            echo '              <td>' . $value . '</td>';
        }
        echo '              </tr>
                        </tbody>
                    </table>
                </div>';
    } else {
        echo '<div class="alert alert-danger d-flex align-items-center" role="alert"><i class="ph ph-funnel-x me-2 fa-150p"></i> Không tìm thấy dữ liệu</div>';
    }
    exit;
} */


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
                        $field = trim($field);
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
                        $field = trim($field);
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
    global $wpdb;
    $jsondata = json_decode(asl_encrypt($_POST['jsondata'], 'd'));
    $templateID = $_POST['templateID'];
    $childid = $_POST['childid'];

    $replacement = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}aslreplacement WHERE templateID = $templateID AND childID = $childid");
    
    if(!$replacement){
        echo '<div class="alert alert-danger d-flex align-items-center" role="alert"><i class="ph ph-funnel-x me-2 fa-150p"></i> Không tìm thấy dữ liệu</div>';
        exit;
    }
    $data_replace = json_decode($replacement->dataReplace, true);
    # replace value in $data_replace with $jsondata
    foreach($data_replace as $key => $value){
        $field = $value['field'];
        $data_replace[$key]['field'] = $jsondata->$field;
    }

    $show = '<div class="table-responsive">
                <table class="table table-bordered">';
    foreach($data_replace as $key => $value){
        # if type is image url, then put it in img tag and put them all to a variable
        if($value['type'] == 'img'){
            $showvalue = '<img src="' . $value['field'] . '" alt="' . $key . '" class="img-thumbnail">';
        } else {
            $showvalue = $value['field'];
        }
        $show .= '  <tr>
                        <td class="py-1">
                            <i class="ph ph-puzzle-piece icon-md"></i>
                        </td>
                        <td>' . $key . '</td>
                        <td>
                            <i class="ph ph-arrow-circle-right icon-md"></i>
                        </td>
                        <td>' . $showvalue . '</td>
                    </tr>';
    }
    $show .= '</table>
        </div>';

    $result = ['show' => $show, 'outputdata' => asl_encrypt(json_encode($data_replace))];
    echo json_encode($result);

    exit;
}

/* 
* File: main.js, create_document.php
*/
add_action('wp_ajax_create_document', 'create_document');
function create_document() {
    global $wpdb;
    global $client;

    // $gservice = new Google_Service_Docs($client);

    if (isset($_POST['post_contract_field']) && wp_verify_nonce($_POST['post_contract_field'], 'post_contract')) {
        # get post data
        $newfilename = $_POST['contract_name'];
        $ls_dataid = explode(',', $_POST['ls_dataid']);
        $templateID = $_POST['templateID'];
        $template = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}asltemplate WHERE templateID = $templateID");
        $sourceFileId = $template->gFileID;
        $folderId = $template->gDestinationFolderID;
        $current_user = wp_get_current_user();
        $transformer = new Transformer();
    
        # if !empty $ls_dataid, get replace data from aslreplacements table
        if (!empty($ls_dataid)) {
            $replacements = [];
            $img_replacements = [];

            foreach ($ls_dataid as $childID) {
                # get data_replace from post data
                $selectdata = $_POST['selectdata_' . $childID];
                if ($selectdata) {
                    $data_replace = json_decode(asl_encrypt($_POST['selectdata_' . $childID], 'd'));

                    foreach ($data_replace as $key => $value) {
                        $field = $value->field;
                        $type = $value->type;
                        
                        switch ($type) {
                            case 'img':
                                $img_replacements[$key] = $field;
                                break;

                            case 'number':
                                if (is_numeric($field)) {
                                    $replacements[$key] = number_format($field);
                                    $replacements[$key . '_text'] = $transformer->toWords($field);
                                } else {
                                    $replacements[$key] = $field;
                                }
                                break;
                            
                            default:
                                $replacements[$key] = $field;
                                break;
                        }
                    }
                }
            }
        }

        # process custom data
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'custom#') !== false) {
                $suffix     = substr($key, 7);
                $tmp_data   = explode('#', $suffix);
                $type       = $tmp_data[0];
                $newkey     = $tmp_data[1];               

                switch ($type) {
                    case 'formula':
                        $congthuc = str_replace(array_keys($replacements), array_values($replacements), $value);
                        $replacevalue = eval('return ' . $congthuc . ';');
                        $replacements[$newkey] = number_format($replacevalue);

                        $replacements[$newkey . '_text'] = $transformer->toWords($replacevalue);
                        break;

                    case 'date':
                        $format = $_POST['format_' . $newkey];
                        $date_arr = explode('/', $value);
                        $replacevalue = str_replace(array('dd', 'mm', 'YYYY'), $date_arr, $format);
                        $replacements[$newkey] = $replacevalue;
                        break;

                    case 'multidata':
                        $replacekey = $_POST['key_' . $newkey];
                        $multi_data = json_decode(asl_encrypt($value, 'd'), true);
                        $struct     = json_decode(asl_encrypt($_POST['struct_' . $newkey], 'd'));
                        $replacevalue = process_multidata($struct, $multi_data);
                        $replacements[$replacekey] = $replacevalue;
                        break;

                    case 'img':
                        $img_replacements[$newkey] = $value;
                        break;

                    case 'number':
                        if (is_numeric($value)) {
                            $replacements[$newkey] = number_format($value);
                            $replacements[$newkey . '_text'] = $transformer->toWords($value);
                        } else {
                            $replacements[$newkey] = $value;
                        }
                        break;
                    
                    default:
                        $tmp_value = str_replace(array_keys($replacements), array_values($replacements), $value);
                        $replacements[$newkey] = $tmp_value;
                        break;
                }
            }
        }
        
        # replace newfilename with all data in $data_replace
        $newfilename = str_replace(array_keys($replacements), array_values($replacements), $newfilename);
    
        /* Clone docs file from source template file */
        $new_file = new Google_Service_Drive_File();
        $optParams = array(
            'folderId' => $folderId,
            'newfilename' => $newfilename,
            'email' => $current_user->user_email,
        );
        $copyfileID = google_clone_file($sourceFileId, $new_file, $optParams);
        
        # if clone file success, add result to database and replace text in file with $data_replace
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
        
            $txt_requests = google_docs_replaceText($copyfileID, $replacements);
            $img_requests = insertImageIntoGoogleDoc($copyfileID, $img_replacements);

            // print_r($txt_requests);
            // $requests = array_merge($txt_requests, $img_requests);
            // $batchUpdateRequest = new Google_Service_Docs_BatchUpdateDocumentRequest(array('requests' => $txt_requests));
            // $gservice->documents->batchUpdate($copyfileID, $batchUpdateRequest);
        } else {
            $notification =  '<div class="alert alert-success" role="alert"> Clone thất bại' . '</div>';
        }
    }

    echo $notification;
    exit;
}

/* 
* File: main.js, create_document.php
*/
add_action('wp_ajax_search_multidata', 'search_multidata');
function search_multidata() {
    global $wpdb;
    $struct = json_decode(asl_encrypt($_POST['struct'], 'd'));
    $search = $_POST['search'];
    $key = $_POST['key'];

    $first_dataID = $struct->first->dataID;
    $second_dataID = $struct->second->dataID;
    $second_field = $struct->second->field;
    $link = $struct->link;


    $table_name = $wpdb->prefix . 'aslchilddatasource';
    $childdatasource = $wpdb->get_row("SELECT * FROM $table_name WHERE childID = $second_dataID");

    // # get data from sql
    $table_name = $wpdb->prefix . $childdatasource->api;
    $field_array = explode(',', $childdatasource->searchfield);
    $search_query_arr = [];
    foreach($field_array as $field){
        $search_query_arr[] = $field . ' LIKE "%' . $search . '%"';
    }
    $search_query = "WHERE " . implode(' OR ', $search_query_arr) . " LIMIT 15";
    $search_result = $wpdb->get_results("SELECT * FROM $table_name $search_query");

    # show search result
    // print_r($search_result);

    $lastID = 0;
    # show search result
    if(!empty($search_result)){
        echo '<div id="select_multidata_form">';
        echo '<div class="table-responsive">
                    <table class="table">
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
            $select_data = str_replace(array_keys($data), array_values($data), $second_field);
            $parentID = $data[$link];
            
            if ($parentID != $lastID) {
                $lastID = $parentID;
                
                # get data from first dataID
                $table_name = $wpdb->prefix . 'aslchilddatasource';
                $childdatasource = $wpdb->get_row("SELECT * FROM $table_name WHERE childID = $first_dataID");

                $table_name = $wpdb->prefix . $childdatasource->api;
                $first_field_array = explode(',', $childdatasource->searchfield);
                $first_data_row = $wpdb->get_row("SELECT * FROM $table_name WHERE $link = $parentID");

                echo '          <tr class="table-warning ">
                                    <td class="py-1">
                                        <i class="ph ph-folder-open icon-md"></i>
                                    </td>';
                foreach($first_field_array as $field){
                    $field = trim($field);
                    echo '          <td>' . $first_data_row->$field . '</td>';
                }
                echo '              <td></td>
                                </tr>';

            }

            echo '          <tr class="multiselect_data" data-multiselect="' . $select_data . '" data-parentid="' . $parentID . '" data-key="' . $key . '">
                                <td class="py-1">
                                    <i class="ph ph-file-magnifying-glass icon-md"></i>
                                </td>';
            foreach($field_array as $field){
                $field = trim($field);
                echo '          <td>' . $data[$field] . '</td>';
            }
            echo '              <td>
                                    <i class="ph ph-hand-pointing fa-150p"></i>
                                </td>
                            </tr>';
        }

        echo '          </tbody>
                    </table>
                </div>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger d-flex align-items-center" role="alert"><i class="ph ph-funnel-x me-2 fa-150p"></i> Không tìm thấy dữ liệu</div>';
    }

    exit;
}

add_action('wp_ajax_select_multidata', 'select_multidata');
function select_multidata() {
    global $wpdb;
    $multiselect    = $_POST['multiselect'];
    $parentid       = $_POST['parentid'];
    $currentdata    = $_POST['currentdata'];
    $struct         = json_decode(asl_encrypt($_POST['struct'], 'd'));
    
    if ($currentdata) {
        $currentdata = json_decode(asl_encrypt($currentdata, 'd'), true);
    } else {
        $currentdata = [];
    }
    $currentdata[$parentid][] = $multiselect;

    $show = process_multidata($struct, $currentdata, true);

    $result = [
        'outputdata' => asl_encrypt(json_encode($currentdata)),
        'show'  => $show
    ];
    echo json_encode($result);
    exit;
}

function process_multidata($struct, $currentdata, $html = false) {
    global $wpdb;
    if ($html) {
        $eol = "<br>";
    } else {
        $eol = "\n";
    }
    $first_separator    = $struct->first->seperator == "PHP_EOL" ? $eol: $struct->first->seperator;
    $link               = $struct->link;
    $datasourceID       = $struct->first->dataID;
    $childdatasource    = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}aslchilddatasource WHERE childID = $datasourceID");

    $show_array = [];
    foreach($currentdata as $first_data_id => $second_array){
        $first_data_row     = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}$childdatasource->api WHERE $link = $first_data_id");
        $seperator          = $struct->seperator == "PHP_EOL" ? $eol: $struct->seperator;
        $second_seperator   = $struct->second->seperator == "PHP_EOL" ? $eol: $struct->second->seperator;
        $second_data        = implode($second_seperator, $second_array);
        $first_data         = str_replace(array_keys((array) $first_data_row), array_values((array) $first_data_row), $struct->first->field);
        $show_array[]       = implode($seperator, [$first_data, $second_data]);
    }

    $result =  implode($first_separator, $show_array);
    return $result;
}