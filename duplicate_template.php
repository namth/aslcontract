<?php 
/* 
Template Name: Duplicate Template
*/
global $wpdb;

$current_user_id = get_current_user_id();

# get templateID from query string
$templateID = $_GET['templateID'];
if (!$templateID) {
    wp_redirect(home_url('/list-template'));
    exit;
}

# get template data
$table_name = $wpdb->prefix . 'asltemplate';
$template = $wpdb->get_row("SELECT * FROM $table_name WHERE templateID = $templateID");

# process form data for duplication
if (isset($_POST['duplicate_template_field']) && wp_verify_nonce($_POST['duplicate_template_field'], 'duplicate_template')) {
    $error = false;
    $templateName = $_POST['templateName'];
    $tagID = $_POST['tagID'];
    $google_fileID = $_POST['google_fileID'];
    $googleTagID = $_POST['googleTagID'];
    $gDestinationFilename = $_POST['gDestinationFilename'];
    
    # validate input fields
    if (empty($templateName)) {
        $notification = 'Tên mẫu file tài liệu không được để trống';
        $error = true;
    }

    if (empty($google_fileID)) {
        $notification = 'Google File ID không được để trống';
        $error = true;
    }

    if (empty($googleTagID)) {
        $notification = 'Google Tag không được để trống';
        $error = true;
    }

    if (empty($gDestinationFilename)) {
        $notification = 'Tên file không được để trống';
        $error = true;
    }
    
    # if not error, then create new template
    if (!$error) {
        # insert new template
        $wpdb->insert(
            $table_name,
            array(
                'templateName' => $templateName,
                'tagID' => $tagID,
                'gFileID' => $google_fileID,
                'googleTagID' => $googleTagID,
                'gDestinationFilename' => $gDestinationFilename,
                'userID' => $current_user_id,
                'templateModified' => current_time('mysql'),
            )
        );
        
        # get the new template ID
        $new_templateID = $wpdb->insert_id;
        
        # get replacements from original template
        $replacement_table = $wpdb->prefix . 'aslreplacement';
        $replacements = $wpdb->get_results("SELECT * FROM $replacement_table WHERE templateID = $templateID");
        
        # copy replacements to new template
        foreach ($replacements as $replacement) {
            $wpdb->insert(
                $replacement_table,
                array(
                    'templateID' => $new_templateID,
                    'childID' => $replacement->childID,
                    'dataReplace' => $replacement->dataReplace,
                )
            );
        }
        
        # if not success, then show error message
        if ($wpdb->last_error) {
            $notification = 'Nhân bản template thất bại';
        } else {
            $notification = 'Nhân bản template thành công';
            # redirect to the new template detail page
            wp_redirect(home_url('/template/?templateID=' . $new_templateID));
            exit;
        }
    }
}

get_header();
?>

<div class="content-wrapper">
    <div class="col-sm-12 mb-3">
        <a href="javascript:history.back()" class="btn btn-icon-text border-none ps-0 align-items-center d-flex"><i class="ph ph-arrow-left me-2"></i> Quay lại</a>
    </div>
    <div class="card card-rounded">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="display-2">Nhân bản template</h2>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="wrapper d-flex justify-content-center align-items-center flex-column py-2">
                            <?php 
                            if (isset($notification)) {
                                echo '<div class="alert alert-info" role="alert">' . $notification . '</div>';
                            }
                            ?>
                            
                            <form
                                class="forms-sample col-md-6 col-lg-4 d-flex justify-content-center flex-column text-center"
                                action="" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="templateName">Tên template mới</label>
                                    <input type="text" class="form-control text-center" id="templateName" name="templateName"
                                        value="<?php echo 'Bản sao của ' . $template->templateName; ?>">
                                </div>
                                <div class="form-group d-flex flex-column justify-content-center align-items-center">
                                    <label for="header">Phân loại</label>
                                    <select class="form-control js-example-basic-single" id="tagID" name="tagID">
                                        <option value="">-- Chọn phân loại --</option>
                                        <?php 
                                            # get all tag from database and show here
                                            $tags = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}asltags");
                                            if ($tags) {
                                                foreach ($tags as $tag) {
                                                    if ($template->tagID == $tag->tagID) {
                                                        echo '<option value="' . $tag->tagID . '" selected>' . $tag->tagName . '</option>';
                                                    } else {
                                                        echo '<option value="' . $tag->tagID . '">' . $tag->tagName . '</option>';
                                                    }
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="google_fileID">Google File ID</label>
                                    <input type="text" class="form-control text-center" id="google_fileID" name="google_fileID"
                                        value="<?php echo $template->gFileID; ?>">
                                </div>
                                <div class="form-group d-flex flex-column justify-content-center align-items-center">
                                    <label for="googleTagID">Google Tag (Thư mục đích)</label>
                                    <select class="form-control js-example-basic-single" id="googleTagID" name="googleTagID">
                                        <option value="">-- Chọn Google Tag --</option>
                                        <?php 
                                            # get all Google tags from database
                                            $google_tags = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}asltags WHERE tagType = 'google'");
                                            if ($google_tags) {
                                                foreach ($google_tags as $gtag) {
                                                    if ($template->googleTagID == $gtag->tagID) {
                                                        echo '<option value="' . $gtag->tagID . '" selected>' . $gtag->tagName . '</option>';
                                                    } else {
                                                        echo '<option value="' . $gtag->tagID . '">' . $gtag->tagName . '</option>';
                                                    }
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="gDestinationFilename">Tên file sau khi tạo tự động</label>
                                    <input type="text" class="form-control text-center" id="gDestinationFilename" name="gDestinationFilename" value="<?php echo $template->gDestinationFilename; ?>">
                                </div>
                                <?php
                                wp_nonce_field('duplicate_template', 'duplicate_template_field');
                                ?>
                                <div class="form-group d-flex justify-content-center gap-3 mt-4">
                                    <a href="javascript:history.back()" class="btn btn-light btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-arrow-arc-left me-2"></span> Quay lại
                                    </a>
                                    <button type="submit" class="btn btn-info btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-files btn-icon-prepend fa-150p"></span> Nhân bản template
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();
