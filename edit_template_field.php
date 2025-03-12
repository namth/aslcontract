<?php 
/* 
Template Name: Edit Template Field
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
    $templateName = $_POST['templateName'];
    $tagID = $_POST['tagID'];
    $google_fileID = $_POST['google_fileID'];
    $googleFolderID = $_POST['googleFolderID'];
    $gDestinationFilename = $_POST['gDestinationFilename'];

    if (!$templateName) {
        $error = true;
        $notification = 'Tên template không được để trống';
    }

    if (!$google_fileID) {
        $error = true;
        $notification = 'Google File ID không được để trống';
    }

    if (!$googleFolderID) {
        $error = true;
        $notification = 'Google Folder ID không được để trống';
    }

    if (!$gDestinationFilename) {
        $error = true;
        $notification = 'Tên file sau khi tạo tự động không được để trống';
    }

    if (!$error) {
        $table_name = $wpdb->prefix . 'asltemplate';
        $wpdb->update(
            $table_name,
            array(
                'templateName' => $templateName,
                'tagID' => $tagID,
                'gFileID' => $google_fileID,
                'gDestinationFolderID' => $googleFolderID,
                'gDestinationFilename' => $gDestinationFilename,
                'templateModified' => date('Y-m-d H:i:s')
            ),
            array('templateID' => $templateID)
        );
        $notification = 'Sửa template thành công';
    
        # redirect to detail template page
        wp_redirect(home_url('/template/?templateID=' . $templateID));
        exit;
    } else {
        $notification = 'Có lỗi xảy ra, vui lòng kiểm tra lại thông tin';
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
                            <h2 class="display-2">Sửa template</h2>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="wrapper d-flex justify-content-center align-items-center flex-column py-2">
                            <?php 
                            if (isset($notification)) {
                                echo '<div class="alert alert-success" role="alert">' . $notification . '</div>';
                            } else {
                            ?>
                            
                            <form
                                class="forms-sample col-md-6 col-lg-4 d-flex justify-content-center flex-column text-center"
                                action="" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="templateName">Tên template</label>
                                    <input type="text" class="form-control text-center" id="templateName" name="templateName"
                                        value="<?php echo $template->templateName; ?>">
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
                                <div class="form-group">
                                    <label for="googleFolderID">Google Folder ID (Thư mục đích)</label>
                                    <input type="text" class="form-control text-center" id="googleFolderID" name="googleFolderID" value="<?php echo $template->gDestinationFolderID; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="gDestinationFilename">Tên file sau khi tạo tự động</label>
                                    <input type="text" class="form-control text-center" id="gDestinationFilename" name="gDestinationFilename" value="<?php echo $template->gDestinationFilename; ?>">
                                </div>
                                <?php
                                wp_nonce_field('post_template', 'post_template_field');
                                ?>
                                <div class="form-group d-flex justify-content-center gap-3">
                                    <a href="javascript:history.back()" class="btn btn-light btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-arrow-arc-left me-2"></span> Quay lại
                                    </a>
                                    <button type="submit" class="btn btn-info btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-magic-wand btn-icon-prepend fa-150p"></span> Sửa template
                                    </button>
                                </div>
                            </form>
                            <?php 
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();