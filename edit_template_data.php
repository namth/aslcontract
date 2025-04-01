<?php 
/* 
Template Name: Edit Template Data
*/
global $wpdb;

$current_user_id = get_current_user_id();

# get templateID from query string
$templateID = $_GET['templateID'];
if (!$templateID) {
    wp_redirect(home_url('/list-folder'));
    exit;
}
$table_name = $wpdb->prefix . 'asltemplate';
$template = $wpdb->get_row("SELECT * FROM $table_name WHERE templateID = $templateID");

# process form data
if (isset($_POST['post_template_field']) && wp_verify_nonce($_POST['post_template_field'], 'post_template')) {
    $error = false;

    # Loop through $_POST to get all data replace
    # If prefix of key is 'data-', then get value of key and add to $data_replace array, switch key and value, key is trim 'data-' prefix
    $data_replace = array();
    foreach ($_POST as $key => $value) {
        $value = convert_vi_to_en($value);
        if (strpos($key, 'data-') !== false) {
            $suffix     = substr($key, 5);
            $datatype   = $_POST['datatype-' . $suffix];
            $temp_data  = explode('#', $suffix);

            $data_replace[$temp_data[0]][$value] = [
                'field' => $temp_data[1],
                'type'  => $datatype,
            ];
        } else if (strpos($key, 'formula_key') !== false) {
            $formulaid      = substr($key, 12);
            // $tmp_field      = $_POST['formula_key-' . $formulaid];
            $tmp_formula    = convert_vi_to_en($_POST['formula_value-' . $formulaid]);

            $data_replace[0][$value] = [
                'field' => $tmp_formula,
                'type' => 'formula',
            ];
        } else if (strpos($key, 'date_key') !== false) {
            $dateid             = substr($key, 9);
            // $tmp_field          = $_POST['date_key-' . $dateid];
            $tmp_date_format    = $_POST['date_format-' . $dateid];

            $data_replace[0][$value] = [
                'field' => $tmp_date_format,
                'type' => 'date',
            ];
        } else if (strpos($key, 'blank_key') !== false) {
            $blankid    = substr($key, 10);
            $type       = $_POST['blank_type-' . $blankid];
            $default    = $_POST['blank_default-' . $blankid];

            $data_replace[0][$value] = [
                'type' => $type,
                'default' => $default,
            ];

        } else if (strpos($key, 'multi_key') !== false) {
            $multiblockid       = substr($key, 10);
            $replace_field      = $_POST['multi_key-' . $multiblockid];
            $first_datasource   = $_POST['first_datasource-' . $multiblockid];
            $first_field        = $_POST['first_field-' . $multiblockid];
            $first_seperator    = $_POST['first_seperator-' . $multiblockid];

            $data_replace[0][$replace_field] = [
                'first' => [
                    'dataID'    => $first_datasource,
                    'field'     => $first_field,
                    'seperator' => $first_seperator,
                ],
                'type' => 'multitext',
            ];

            $second_datasource  = $_POST['second_datasource-' . $multiblockid];
            if ($second_datasource) {
                $second_field       = $_POST['second_field-' . $multiblockid];
                $second_seperator   = $_POST['second_seperator-' . $multiblockid];
                $link               = $_POST['link-' . $multiblockid];
                $seperator          = $_POST['seperator-' . $multiblockid];

                $data_replace[0][$replace_field]['second'] = [
                    'dataID'    => $second_datasource,
                    'field'     => $second_field,
                    'seperator' => $second_seperator
                ];
                $data_replace[0][$replace_field]['link'] = $link;
                $data_replace[0][$replace_field]['seperator'] = $seperator;
            }
        }
    }

    $table_name = $wpdb->prefix . 'aslreplacement';
    # remove all data replace from aslreplacement table by templateID
    $wpdb->delete($table_name, ['templateID' => $templateID]);

    # insert new data replace to aslreplacement table
    # if $data_replace is not empty, then insert data to database
    if ($data_replace) {
        foreach ($data_replace as $key => $value) {
            $wpdb->insert(
                $table_name,
                array(
                    'templateID' => $templateID,
                    'childID' => $key,
                    'dataReplace' => json_encode($value),
                )
            );
        }
    }
    # redirect to detail template page
    wp_redirect(home_url('/template/?templateID=' . $templateID));
}

get_header();

$options = ['text'=> 'Text', 'img' => 'Image', 'number' => 'Number'];
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="display-3">Sửa data tài liệu</h2>
                </div>
            </div>
            <?php 
                if (isset($notification)) echo '<div class="alert alert-danger">' . $notification . '</div>';
            ?>
            <form id="addnew_template_form" action="" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="statistics-details d-flex flex-row gap-3 flex-wrap">

                            <div id="replaceArea" class="d-flex justify-content-center w-100 flex-column gap-3 align-items-center">
                            <?php 
                                # get replacement data from aslreplacement table by templateID
                                $childs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aslreplacement WHERE templateID = $templateID ORDER BY childID DESC");

                                foreach ($childs as $key => $child) {
                                    $childID = $child->childID;
                                    
                                    if ($childID > 0){
                                        $childData = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}aslchilddatasource WHERE childID = $childID");
                                        ?>
                                        <div class="data_replace_box d-flex align-items-center justify-content-center gap-4 w-100" id="child-<?php echo $childID; ?>">
                                            <div class="d-flex justify-content-center flex-column text-center">
                                                <i class="ph ph-database icon-lg p-2"></i>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold">
                                                        <?php echo $childData->childName; ?>
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
                                                    $datareplace = json_decode($child->dataReplace);
                                                    
                                                    foreach($datareplace as $key_replace => $obj){
                                                        $value = trim($obj->field);
                                                        $type = $obj->type;
                                                        echo '<div class="replace_field d-flex justify-content-center align-items-center gap-3">
                                                                    <i class="ph ph-puzzle-piece icon-md"></i>
                                                                    <span class="w165">' . $value . '</span>
                                                                    <i class="ph ph-arrow-circle-right icon-md"></i>
                                                                    <select class="js-example-basic-single w110" name="datatype-' . $childID . '#' . $value . '">';
                                                                        
                                                                        foreach ($options as $key_option => $option) {
                                                                            $selected = ($key_option == $type) ? 'selected' : '';
                                                                            echo '<option value="' . $key_option . '" ' . $selected . '>' . $option . '</option>';
                                                                        }
                                                        echo '      </select>
                                                                    <input type="text" class="form-control w268" name="data-' . $childID . '#' . $value . '" value="' . $key_replace . '">
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
                                    } else {
                                        // print_r($child);
                                        $datareplace = json_decode($child->dataReplace);
                                        $formula_count = 0;

                                        foreach($datareplace as $key_replace => $obj){
                                            $field = isset($obj->field) ? trim($obj->field): "";
                                            $type = $obj->type;
                                            $formula_count++;

                                            switch($type){
                                                case 'formula':
                                                    echo '
                                                        <div class="data_replace_box d-flex align-items-center justify-content-center gap-4 w-100" id="formula-' . $formula_count . '">
                                                            <div class="replace_area d-flex align-items-center flex-column justify-content-center gap-3">
                                                                <div class="replace_field d-flex justify-content-center align-items-center p-2 gap-3">
                                                                    <i class="ph ph-math-operations icon-md"></i>
                                                                    <input type="text" class="form-control w198" name="formula_key-' . $formula_count . '" placeholder="Nhập từ khóa sẽ thay thế trong file" value="' . $key_replace . '">
                                                                    <i class="ph ph-equals icon-md"></i>
                                                                    <input type="text" class="form-control w315" name="formula_value-' . $formula_count . '" placeholder="Nhập công thức" value="' . $field . '">
                                                                </div>
                                                            </div>
                                                            <a id="remove_formula" class="remove_datasource nav-link" href="#">
                                                                <i class="ph ph-x icon-md"></i>
                                                            </a>
                                                        </div>
                                                    ';
                                                    break;
                                        
                                                case 'date':
                                                    echo '
                                                        <div class="data_replace_box d-flex align-items-center justify-content-center gap-4 w-100" id="formula-' . $formula_count . '">
                                                            <div class="replace_area d-flex align-items-center flex-column justify-content-center gap-3">
                                                                <div class="replace_field d-flex justify-content-center align-items-center p-2 gap-3">
                                                                    <i class="ph ph-calendar icon-md"></i>
                                                                    <input type="text" class="form-control w198" name="date_key-' . $formula_count . '" placeholder="Nhập từ khóa sẽ thay thế trong file" value="' . $key_replace . '">
                                                                    <i class="ph ph-sun-horizon icon-md"></i>
                                                                    <input type="text" class="form-control w315" name="date_format-' . $formula_count . '" placeholder="Nhập định dạng ngày tháng" value="' . $field . '">
                                                                </div>
                                                            </div>
                                                            <a id="remove_formula" class="remove_datasource nav-link" href="#">
                                                                <i class="ph ph-x icon-md"></i>
                                                            </a>
                                                        </div>';
                                                    break;

                                                case 'multitext':
                                                    $firstID = $obj->first->dataID;
                                                    $secondID = $obj->second->dataID;
                                                    echo '<div class="data_replace_box d-flex align-items-center justify-content-center flex-column gap-4 w-100" id="formula-1">
                                                            <div class="replace_area d-flex align-items-center flex-column justify-content-center gap-3">
                                                                <div class="replace_field d-flex justify-content-center align-items-center p-2 gap-3">
                                                                    <i class="ph ph-diamonds-four icon-md"></i>
                                                                    <input type="text" class="form-control w300" name="multi_key-1" placeholder="Nhập từ khóa sẽ thay thế trong file" value="{linkdata_1}">
                                                                </div>
                                                                
                                                            </div>
                                                            <a id="remove_formula" class="remove_datasource nav-link" href="#">
                                                                <i class="ph ph-x icon-md"></i>
                                                            </a>';
                                                    echo_multiblock($formula_count, $firstID, $secondID, $obj);
                                                    echo '</div>';
                                                    break;
                                        
                                                default:
                                                    $blank_default = isset($obj->default) ? $obj->default : "";
                                                    echo '
                                                        <div class="data_replace_box d-flex align-items-center justify-content-center gap-4 w-100" id="formula-' . $formula_count . '">
                                                            <div class="replace_area d-flex align-items-center flex-column justify-content-center gap-3">
                                                                <div class="replace_field d-flex justify-content-center align-items-center p-2 gap-3">
                                                                    <i class="ph ph-brackets-curly icon-md"></i>
                                                                    <input type="text" class="form-control w198" name="blank_key-' . $formula_count . '" placeholder="Nhập từ khóa sẽ thay thế trong file" value="' . $key_replace . '">
                                                                    <i class="ph ph-article-ny-times icon-md"></i>
                                                                    <select class="js-example-basic-single w110" id="type" name="blank_type-' . $formula_count . '">';
                                                                        foreach ($options as $key_option => $option) {
                                                                            $selected = ($key_option == $type) ? 'selected' : '';
                                                                            echo '<option value="' . $key_option . '" ' . $selected . '>' . $option . '</option>';
                                                                        }
                                                    echo '          </select>
                                                                    <input type="text" class="form-control w315" name="blank_default-' . $formula_count . '" placeholder="Nhập nội dung mặc định" value="' . $blank_default . '">
                                                                </div>
                                                            </div>
                                                            <a id="remove_formula" class="remove_datasource nav-link" href="#">
                                                                <i class="ph ph-x icon-md"></i>
                                                            </a>
                                                        </div>';
                                                    break;                                                
                                            }
                                        }
                                    }
                                }
                            ?>
                            </div>
                            
                            <div class="d-flex justify-content-center w-100 flex-column gap-3 align-items-center">
                                <div id="datasource_action" class="asl-dash-btn btn-inverse-info d-flex gap-5 w-100 justify-content-center">
                                    <a href="#" id="add_datasource" class="d-flex nav-link"><i class="ph ph-plugs icon-md"></i></a>
                                    <a href="#" class="add_formula d-flex nav-link" data-custom="formula"><i class="ph ph-math-operations icon-md"></i></a>
                                    <a href="#" class="add_formula d-flex nav-link" data-custom="date"><i class="ph ph-calendar-plus icon-md"></i></a>
                                    <a href="#" class="add_formula d-flex nav-link" data-custom="blank"><i class="ph ph-align-left-simple icon-md"></i></a>
                                    <a href="#" class="add_formula d-flex nav-link" data-custom="multiblock"><i class="ph ph-diamonds-four icon-md"></i></a>
                                    <input type="hidden" name="formula_count" id="formula_count" value="0">
                                    <input type="hidden" name="multi_datasource">
                                </div>
                                <div id="list_datasource" class="justify-content-center w-100 gap-3" style="display: none;">
                                    <?php 
                                        # get all datasource from database and show here
                                        $datasources = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}asldatasource");

                                        if ($datasources) {
                                            foreach ($datasources as $datasource) {
                                                echo '<div class="datasource d-flex align-items-center flex-column justify-content-center gap-3">';
                                                echo '<h4>' . $datasource->sourceName . '</h4>';

                                                # get child datasource from parent and show here
                                                $child_datasources = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aslchilddatasource WHERE sourceID = {$datasource->sourceID}");
                                                # if have child datasource then show here
                                                if ($child_datasources) {
                                                    echo '<div class="d-flex align-items-center flex-wrap gap-3">';
                                                    foreach ($child_datasources as $child_datasource) {
                                                        echo '<a href="#" data-childid="' . $child_datasource->childID . '" 
                                                                class="child_datasource d-flex align-items-center flex-column justify-content-center gap-2">
                                                                <i class="ph ph-database icon-md"></i><span>' . $child_datasource->childName . '</span></a>';
                                                    }
                                                    echo '</div>';
                                                }

                                                echo '</div>';
                                            }
                                        }
                                    ?>
                                </div>
                            </div>
                            <?php
                                wp_nonce_field('post_template', 'post_template_field');
                            ?>
                            <div class="form-group d-flex justify-content-center w-100">
                                <a href="javascript:history.back()" class="btn btn-inverse-info btn-icon-text me-2 d-flex align-items-center">
                                    <i class="ph ph-arrow-bend-up-left btn-icon-prepend fa-150p"></i> Quay lại
                                </a>
                                <button type="submit" class="btn btn-info btn-icon-text d-flex align-items-center">
                                    <span class="mdi mdi-creation-outline btn-icon-prepend fa-150p"></span> Sửa template
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
get_footer();
