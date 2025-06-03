<?php
/* 
    Template Name: Create Document
*/
use Google\Service\Drive\DriveFile as Google_Service_Drive_File;
use PHPViet\NumberToWords\Transformer;
use Google\Service\Drive;
use Google\Service\Docs as Google_Service_Docs;
use Google\Service\Docs\SubstringMatchCriteria as Google_Service_SubstringMatchCriteria;
use Google\Service\Docs\Request as Google_Service_Docs_Request;
use Google\Service\Docs\BatchUpdateDocumentRequest as Google_Service_Docs_BatchUpdateDocumentRequest;

get_header();

# get template id
$templateID = $_GET['templateID'];
global $wpdb;
global $client;

$service = new Drive($client);

# if have not template id, then redirect to list folder page
if (!$templateID) {
    wp_redirect(home_url('/list-folder'));
    exit;
} else {
    # get row data of template
    $table_name = $wpdb->prefix . 'asltemplate';
    $template = $wpdb->get_row("SELECT * FROM $table_name WHERE templateID = $templateID");
}

# process post data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['post_contract_field'])) {
        $newfilename    = $_POST['contract_name'];
        $ls_dataid      = explode(',', $_POST['ls_dataid']);
        $templateID     = $_POST['templateID'];
        $template       = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}asltemplate WHERE templateID = $templateID");
        $sourceFileId   = $template->gFileID;
        
        # Get Google folder ID from associated Google tag
        $folderId = null;
        if ($template->googleTagID) {
            $googleTag = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}asltags WHERE tagID = $template->googleTagID");
            if ($googleTag && $googleTag->googleFileID) {
                $folderId = $googleTag->googleFileID;
            }
        }
        
        $current_user   = wp_get_current_user();
        $transformer    = new Transformer();

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
                                if (!empty($field) && filter_var($field, FILTER_VALIDATE_URL)) {
                                    // Check if the URL ends with a common image extension
                                    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
                                    $path_parts = pathinfo(parse_url($field, PHP_URL_PATH));
                                    if (isset($path_parts['extension']) && in_array(strtolower($path_parts['extension']), $image_extensions)) {
                                        $img_replacements[$key] = $field;
                                    }
                                }
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
        // Create array to store formulas for later processing
        $formulas = [];

        foreach ($_POST as $key => $value) {
            if (strpos($key, 'custom#') !== false) {
                $suffix     = substr($key, 7);
                $tmp_data   = explode('#', $suffix);
                $type       = $tmp_data[0];
                $newkey     = $tmp_data[1];               

                switch ($type) {
                    case 'formula':
                        // Store formula for processing after main loop
                        $formulas[$newkey] = str_replace(array_keys($replacements), array_values($replacements), $value);
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
                        if (!empty($value) && filter_var($value, FILTER_VALIDATE_URL)) {
                            // Check if the URL ends with a common image extension
                            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
                            $path_parts = pathinfo(parse_url($value, PHP_URL_PATH));
                            if (isset($path_parts['extension']) && in_array(strtolower($path_parts['extension']), $image_extensions)) {
                                $img_replacements[$newkey] = $value;
                            }
                        }
                        break;

                    case 'number':
                        if (is_numeric($value) && $value) {
                            # if $value greater than 1000, then ceil it up
                            if ($value > 1000) {
                                $value = ceil($value);
                            }
                            $replacements[$newkey] = number_format($value);
                            $replacements[textkey($newkey)] = ucfirst($transformer->toWords($value));
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
        
        // Process stored formulas after main loop with iterative approach
        $processed_count = 0;
        $max_iterations = 10; // Prevent infinite loops
        $iteration = 0;
        
        while (!empty($formulas) && $iteration < $max_iterations) {
            $iteration++;
            $failed_formulas = [];
            $success_count = 0;
            
            foreach ($formulas as $newkey => $formula) {
                try {
                    // Replace any keys in the formula with their current values
                    $parsed_formula = str_replace(array_keys($replacements), array_values($replacements), $formula);
                    $parsed_formula = remove_seperator_in_number($parsed_formula);
                    
                    if (is_valid_formula($parsed_formula)) {
                        $replacevalue = eval('return ' . $parsed_formula . ';');
                        $replacements[$newkey] = number_format($replacevalue);
                        
                        // If the value is numeric, convert it to words
                        if (is_numeric($replacevalue) && $replacevalue) {
                            # if $value greater than 1000, then ceil it up
                            if ($replacevalue > 1000) {
                                $replacevalue = ceil($replacevalue);
                            }
                            $replacements[textkey($newkey)] = ucfirst($transformer->toWords($replacevalue));
                        }
                        
                        $processed_count++;
                        $success_count++;
                    } else {
                        // Formula is not valid yet, keep for next iteration
                        $failed_formulas[$newkey] = $formula;
                    }
                } catch (Exception $e) {
                    // If formula evaluation fails, save for next iteration
                    $failed_formulas[$newkey] = $formula;
                }
            }
            
            // Update formulas array with failed ones for next iteration
            $formulas = $failed_formulas;
            
            // If no formulas were successfully processed in this iteration, break
            if ($success_count == 0) {
                break;
            }
        }
        
        // Add remaining unprocessed formulas as their original text if they couldn't be evaluated
        foreach ($formulas as $newkey => $formula) {
            if (!isset($replacements[$newkey])) {
                $replacements[$newkey] = $formula;
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
        # get webViewLink of the cloned file
        if ($copyfileID) {
            $new_file = $service->files->get($copyfileID, array('fields' => 'webViewLink'));
            $webViewLink = $new_file->getWebViewLink();
        } else {
            $webViewLink = '';
        }
        
        # if clone file success, add result to database and replace text in file with $data_replace
        if ($copyfileID) {
            # add result to database: asldocument table
            $table_name = $wpdb->prefix . 'asldocument';
            $wpdb->insert($table_name, [
                'templateID' => $templateID,
                'userID' => get_current_user_id(),
                'documentName' => $newfilename,
                'gFileID' => $copyfileID,
                'tagID' => $template->googleTagID,
                'documentModified' => current_time('mysql'),
            ]);
    
            $notification = '<div class="alert alert-success" role="alert"> 
                Tạo file thành công. File ID: ' . $copyfileID . '
                <div class="mt-2">
                    <a href="' . $webViewLink . '" target="_blank" class="btn btn-primary">Xem tài liệu</a>
                </div>
            </div>';
        
            $txt_requests = google_docs_replaceText($copyfileID, $replacements);
            $img_requests = insertImageIntoGoogleDoc($copyfileID, $img_replacements);
        } else {
            $notification =  '<div class="alert alert-success" role="alert"> Clone thất bại' . '</div>';
        }        
    }
}

?>
<div class="content-wrapper">
    <div class="card card-rounded">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="display-2">Tạo tài liệu mới</h2>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="wrapper d-flex justify-content-center align-items-center flex-column py-2">
                            <?php 
                            if (isset($notification)) {
                                echo $notification;
                            } else {
                            ?>
                            <div class="d-flex justify-content-center mb-3">
                                <i class="fa fa-file-text-o fa-150p"></i>
                                <div class="wrapper ms-3">
                                    <p class="ms-1 mb-1 fw-bold"><?php echo $template->templateName; ?></p>
                                </div>
                            </div>
                            <form id="create_document" class="forms-sample col-md-12 col-lg-8 d-flex justify-content-center flex-column text-center align-items-center gap-4"
                                action="" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="exampleInputUsername1">Tên hợp đồng mới</label>
                                    <input type="text" class="form-control text-center document_name" id="exampleInputUsername1" name="contract_name"
                                        placeholder="Tên file tài liệu mới" value="<?php echo $template->gDestinationFilename; ?>">
                                </div>
                                <div class="d-flex justify-content-center mt-4">
                                    <i class="fa fa-folder-open-o fa-150p"></i>
                                    <div class="wrapper ms-3">
                                        <p class="ms-1 mb-1 fw-bold">Thư mục đích:
                                            <?php 
                                                if ($template->googleTagID) {
                                                    $googleTag = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}asltags WHERE tagID = $template->googleTagID");
                                                    echo $googleTag ? $googleTag->tagName : 'Chưa thiết lập';
                                                } else {
                                                    echo 'Chưa thiết lập';
                                                }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <h4 class="card-title mb-0">Dữ liệu thay thế</h4>
                                </div>
                                <?php

                                # get child data source and replacement data from aslreplacement table
                                $childs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aslreplacement WHERE templateID = $templateID");
                                if ($childs) {
                                    foreach ($childs as $key => $child) {
                                        if ($child->childID == 0) {
                                            continue;
                                        }
                                        # get childName from aslchilddatasource table by childID
                                        $childData = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}aslchilddatasource WHERE childID = $child->childID");
                                        
                                        // $system = ['date', 'time'];
                                        # print replacement data
                                        if ($childData) {
                                        ?>
                                        <div class="data_replace_box d-flex align-items-center gap-4 fit-content">
                                            <div class="d-flex justify-content-center flex-column text-center">
                                                <i class="ph ph-database icon-lg p-2"></i>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold">
                                                        <?php echo $childData->childName; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="replace_area d-flex align-items-center flex-column justify-content-center">
                                                <div class="replace_search justify-content-center align-items-center p-2 gap-3" id="replaceSearch_<?php echo $child->childID; ?>">
                                                    <i class="ph ph-puzzle-piece icon-md"></i>
                                                    <input type="text" class="form-control" id="exampleInputUsername1" name="datareplace_<?php echo $child->childID; ?>">
                                                    <input type="hidden" name="selectdata_<?php echo $child->childID; ?>">
                                                    <a class="search_data nav-link border-none p-1" data-childid="<?php echo $child->childID; ?>">
                                                        <i class="ph ph-magnifying-glass fa-150p"></i>
                                                        <span class="loader"><img src="<?php echo get_template_directory_uri() . "/assets/images/loader.gif"; ?>" alt=""></span>
                                                    </a>
                                                </div>
                                                <div id="replaceResult_<?php echo $child->childID; ?>" class="replace_result"></div>
                                            </div>
                                        </div>
                                        <?php
                                        }

                                        # pop out data from $childs
                                        unset($childs[$key]);
                                    }

                                    # show all the custom data
                                    $data_replace = json_decode($childs[0]->dataReplace);
                                    // print_r($data_replace);
                                    foreach ($data_replace as $key => $replace_arr) {
                                        $type = $replace_arr->type;
                                        $newkey = str_replace(array('{', '}'), '', $key);
                                        if ($type == 'date') {
                                            $format = $replace_arr->field;
                                        ?>
                                        <div class="data_replace_box d-flex align-items-center gap-4 fit-content">
                                            <div class="d-flex justify-content-center flex-column text-center">
                                                <i class="ph ph-calendar icon-lg p-2"></i>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold">
                                                        <?php echo $newkey; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="replace_area d-flex align-items-center flex-column justify-content-center">
                                                <div class="replace_search justify-content-center align-items-center p-2 gap-3" id="replaceSearch_<?php echo $newkey; ?>">
                                                    <i class="ph ph-puzzle-piece icon-md"></i>
                                                    <div class="datepicker-popup input-group date datepicker navbar-date-picker">
                                                        <span class="input-group-addon input-group-prepend border-right">
                                                            <span class="icon-calendar input-group-text calendar-icon"></span>
                                                        </span>
                                                        <input type="text" class="form-control" name="custom#date#<?php echo $key; ?>">
                                                    </div>
                                                    <input type="hidden" name="format_<?php echo $key; ?>" value="<?php echo $format; ?>">
                                                </div>
                                                <div id="replaceResult_<?php echo $child->childID; ?>" class="replace_result"></div>
                                            </div>
                                        </div>
                                        <?php
                                        } else if ($type == 'multitext') {
                                            $struct = asl_encrypt(json_encode($replace_arr));
                                            $default = $replace_arr->default;
                                            ?>
                                            <div class="data_replace_box d-flex align-items-center gap-4 fit-content">
                                                <div class="d-flex justify-content-center flex-column text-center">
                                                    <i class="ph ph-diamonds-four icon-lg p-2"></i>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold">
                                                            <?php echo $newkey; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="replace_area d-flex align-items-center flex-column justify-content-center">
                                                    <div id="multiResult_<?php echo $newkey; ?>" class=""></div>
                                                    <div class="replace_search justify-content-center align-items-center p-2 gap-3" id="replaceSearch_<?php echo $newkey; ?>">
                                                        <i class="ph ph-puzzle-piece icon-md"></i>
                                                        <input type="text" class="form-control" name="search_<?php echo $newkey; ?>" value="">
                                                        <input type="hidden" name="custom#multidata#<?php echo $newkey; ?>">
                                                        <input type="hidden" name="key_<?php echo $newkey; ?>" value="<?php echo $key; ?>">
                                                        <input type="hidden" name="struct_<?php echo $newkey; ?>" value="<?php echo $struct; ?>">
                                                        <a class="search_multidata nav-link border-none p-1" data-key="<?php echo $newkey; ?>">
                                                            <i class="ph ph-magnifying-glass fa-150p"></i>
                                                            <span class="loader"><img src="<?php echo get_template_directory_uri() . "/assets/images/loader.gif"; ?>" alt="" width="24"></span>
                                                        </a>
                                                    </div>
                                                    <div id="selectResult" class="replace_result"></div>
                                                </div>
                                            </div>
                                            <?php
                                        } else if ($type == 'formula') {
                                            echo '<input type="hidden" name="custom#formula#' . $key . '" value="' . $replace_arr->field . '">';
                                        } else {
                                            $default = $replace_arr->default;
                                            ?>
                                            <div class="data_replace_box d-flex align-items-center gap-4 fit-content">
                                                <div class="d-flex justify-content-center flex-column text-center">
                                                    <i class="ph ph-database icon-lg p-2"></i>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold">
                                                            <?php echo $newkey; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="replace_area d-flex align-items-center flex-column justify-content-center">
                                                    <div class="replace_search justify-content-center align-items-center p-2 gap-3" id="replaceSearch_<?php echo $newkey; ?>">
                                                        <i class="ph ph-puzzle-piece icon-md"></i>
                                                        <input type="text" class="form-control" name="<?php echo "custom#" . $type . "#" . $key; ?>" value="<?php echo $default; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                }
                                wp_nonce_field('post_contract', 'post_contract_field');
                                ?>
                                <input type="hidden" name="ls_dataid">
                                <input type="hidden" name="templateID" value="<?php echo $templateID; ?>">
                                <div class="form-group d-flex justify-content-center">
                                    <button type="submit"
                                        class="btn btn-info btn-icon-text me-2 d-flex align-items-center"><span
                                            class="mdi mdi-creation-outline btn-icon-prepend fa-150p"></span> Tạo tài
                                        liệu</button>
                                    <!-- <button class="btn btn-light btn-icon-text"><span class="mdi mdi-close"></span> Quay lại</button> -->
                                </div>
                            </form>
                            <?php 
                            }
                            ?>
                            <div id="create_loading" class="justify-content-center flex-column">
                                <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
                                <dotlottie-player src="https://lottie.host/50abcbf0-6a0e-47cf-9432-cb11fa05f0ef/UGUCILoRSN.lottie" background="transparent" speed="1" style="width: 300px; height: 300px" loop autoplay></dotlottie-player>
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