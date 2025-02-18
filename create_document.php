<?php
/* 
    Template Name: Create Document
*/
get_header();

# get template id
$templateID = $_GET['templateID'];
global $wpdb;

# if have not template id, then redirect to list folder page
if (!$templateID) {
    wp_redirect(home_url('/list-folder'));
    exit;
} else {
    # get row data of template
    $table_name = $wpdb->prefix . 'asltemplate';
    $template = $wpdb->get_row("SELECT * FROM $table_name WHERE templateID = $templateID");
}

/* 
validate form data and create new contract
*/
// if (isset($_POST['post_contract_field']) && wp_verify_nonce($_POST['post_contract_field'], 'post_contract')) {
//     # get post data
//     $newfilename = $_POST['contract_name'];
//     $ls_dataid = explode(',', $_POST['ls_dataid']);
//     $sourceFileId = $template->gFileID;
//     $folderId = $template->gDestinationFolderID;

//     # if !empty $ls_dataid, get replace data from aslreplacements table
//     if (!empty($ls_dataid)) {
//         $replacements = [];
//         foreach ($ls_dataid as $childID) {
//             # get data_replace from post data
//             $data_replace = json_decode(asl_encrypt($_POST['selectdata_' . $childID], 'd'));

//             // print_r($data_replace->name);
//             # get replacement data from aslreplacement table by childID and templateID
//             $replacement = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}aslreplacement WHERE templateID = $templateID AND childID = $childID");

//             # if have replacement data, decode it and replace value in $data_replace
//             if ($replacement) {
//                 $replacement = json_decode($replacement->dataReplace, true);
//                 foreach ($replacement as $key => $value) {
//                     $replacement[$key] = $data_replace->$value;
//                 }
//                 $replacements = array_merge($replacements, $replacement);
//             }
//         }
//     }

//     // Load credentials from file
//     // $client = new Google_Client();
//     // $client->setApplicationName("ASL Contract");
//     // $client->addScope(Google_Service_Drive::DRIVE);
//     // $client->setAccessType('offline');
//     // $client->setAuthConfig(__DIR__ . '/asl-contract-01fd683a00f9.json');

//     # replace newfilename with all data in $replacements
//     $newfilename = str_replace(array_keys($replacements), array_values($replacements), $newfilename);

//     /* Clone docs file from source template file */
//     $new_file = new Google_Service_Drive_File();
//     $optParams = array(
//         'folderId' => $folderId,
//         'newfilename' => $newfilename,
//     );
//     $copyfileID = google_clone_file($sourceFileId, $new_file, $optParams);
    
//     # if clone file success, add result to database and replace text in file with $replacements
//     if ($copyfileID) {
//         # add result to database: asldocument table
//         $table_name = $wpdb->prefix . 'asldocument';
//         $wpdb->insert($table_name, [
//             'templateID' => $templateID,
//             'userID' => get_current_user_id(),
//             'documentName' => $newfilename,
//             'gFileID' => $copyfileID,
//             'gDestinationFolderID' => $folderId,
//             'documentModified' => current_time('mysql'),
//         ]);

//         $notification = 'Tạo file thành công. File ID: ' . $copyfileID;
    
//         $result = google_docs_replaceText($copyfileID, $replacements);    
//     } else {
//         $notification =  "Clone thất bại";
//     }
// }

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
                                echo '<div class="alert alert-success" role="alert">' . $notification . '</div>';
                            } else {
                            ?>
                            <div class="d-flex justify-content-center mb-3">
                                <i class="fa fa-file-text-o fa-150p"></i>
                                <div class="wrapper ms-3">
                                    <p class="ms-1 mb-1 fw-bold"><?php echo $template->templateName; ?></p>
                                </div>
                            </div>
                            <form id="create_document" class="forms-sample col-md-6 col-lg-4 d-flex justify-content-center flex-column text-center align-items-center gap-4"
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
                                            <?php echo $template->gDestinationFolderID; ?>
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
                                    foreach ($childs as $child) {
                                        # get childName from aslchilddatasource table by childID
                                        $childData = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}aslchilddatasource WHERE childID = $child->childID");
                                        
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
                            <div id="create_loading">
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