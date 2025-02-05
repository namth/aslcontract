<?php
/* 
*   Template Name: Detail Template
*/
get_header();
global $wpdb;

$templateID = $_GET['templateID'];
$template = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}asltemplate WHERE templateID = $templateID");

# get user by id from wp_users table
$create_user = get_userdata($template->userID);
?>
<div class="content-wrapper">
    <div class="col-sm-12 mb-3">
        <a href="<?php echo home_url('/list-template/?tagid=') . $template->tagID; ?>" class="btn btn-icon-text border-none ps-0 align-items-center d-flex"><i class="ph ph-arrow-left me-2"></i> Quay lại</a>
    </div>
    <div class="card card-rounded">
        <div class="card-body">
            <div class="row">

                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="display-4"><?php echo $template->templateName; ?></h2>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="wrapper d-flex justify-content-center flex-column py-2">
                            <?php 
                            if (isset($notification)) {
                                echo $notification;
                            }

                            echo "<span class='d-flex align-items-center mb-2 gap-2'><b><i class='ph ph-file-text me-2'></i>Template Name: </b> " . $template->templateName . "</span>";
                            echo "<span class='d-flex align-items-center mb-2 gap-2'><b><i class='ph ph-file-cloud me-2'></i>Google File ID:</b> " . $template->gFileID . "</span>";
                            echo "<span class='d-flex align-items-center mb-2 gap-2'><b><i class='ph ph-cloud-arrow-up me-2'></i>Google ID thư mục đích:</b> " . $template->gDestinationFolderID . "</span>";
                            echo "<span class='d-flex align-items-center mb-2 gap-2'><b><i class='ph ph-chat-teardrop-text me-2'></i>Tên file mẫu:</b> " . $template->gDestinationFilename . "</span>";
                            echo "<span class='d-flex align-items-center mb-2 gap-2'><b><i class='ph ph-user me-2'></i>Người tạo:</b> " . $create_user->display_name . "</span>";

                            # get child data source and replacement data from aslreplacement table
                            $childs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aslreplacement WHERE templateID = $templateID");
                            if ($childs) {
                                foreach ($childs as $child) {
                                    # get childName from aslchilddatasource table by childID
                                    $childData = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}aslchilddatasource WHERE childID = $child->childID");
                                    echo "<span class='d-flex align-items-center gap-2 mt-2'>
                                            <b><i class='ph ph-database me-2'></i>Data Source:</b> " . $childData->childName . "
                                        </span>";

                                    # print replacement data
                                    ?>
                                    <div class="data_replace_box d-flex align-items-center gap-4 fit-content mb-2">
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
                                                <span class="w300">Từ khóa thay thế</span>
                                                <i class="ph ph-arrow-circle-right icon-md"></i>
                                                <span class="w300">Dữ liệu</span>
                                            </div>
                                            <?php 
                                                $datareplace = json_decode($child->dataReplace);
                                                
                                                foreach($datareplace as $key => $value){
                                                    echo '<div class="replace_field d-flex justify-content-center align-items-center gap-3">
                                                            <i class="ph ph-puzzle-piece icon-md"></i>
                                                            <span class="w300">' . $key . '</span>
                                                            <i class="ph ph-arrow-circle-right icon-md"></i>
                                                            <span class="w300">' . $value . '</span>
                                                        </div>';
                                                }
                                            ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            <!-- Button pull data from $source to header (json format) -->
                            <div class="form-group d-flex justify-content-left mt-3">
                                <a href="<?php echo home_url('/create-document?templateID=') . $templateID; ?>" class="btn btn-info btn-icon-text me-2 d-flex align-items-center">
                                    <span class="mdi mdi-creation-outline btn-icon-prepend fa-150p"></span> Tạo tài liệu từ mẫu này
                                </a>
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