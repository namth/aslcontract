<?php
/* 
    Template Name: Add New Tag
*/

# process form data
if (isset($_POST['post_tag_field']) && wp_verify_nonce($_POST['post_tag_field'], 'post_tag')) {
    global $wpdb;
    $error = false;

    $tagName = $_POST['tagName'];
    $tagDescription = $_POST['tagDescription'];
    $tagModified = date('Y-m-d H:i:s');

    # tagName is required, if not have, then show error message
    if (empty($tagName)) {
        $notification = 'Tên thư mục không được để trống';
        $error = true;
    }

    # if not error, then insert data to database
    if (!$error) {
        $table_name = $wpdb->prefix . 'asltags';
        $wpdb->insert(
            $table_name,
            array(
                'tagName' => $tagName,
                'tagDescription' => $tagDescription,
                'tagModified' => $tagModified
            )
        );
        # if not success, then show error message
        if ($wpdb->last_error) {
            $notification = 'Thêm thư mục thất bại';
        } else {
            # redirect to list tag page
            wp_redirect(home_url('/list-folder'));
            exit;
        }
    } 
}

get_header();
?>
<div class="content-wrapper">
    <div class="card card-rounded">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="display-2">Thêm thư mục mới</h2>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="wrapper d-flex justify-content-center align-items-center flex-column py-2">
                            <?php 
                            if (isset($notification)) {
                                echo '<div class="alert alert-danger d-flex justify-content-center align-items-center" role="alert"><i class="ph ph-warning me-2"></i>' . $notification . '</div>';
                            } 
                            ?>
                            
                            <form
                                class="forms-sample col-md-6 col-lg-4 d-flex justify-content-center flex-column text-center"
                                action="" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="tagName">Tên thư mục</label>
                                    <input type="text" class="form-control text-center" id="tagName" name="tagName">
                                </div>
                                <div class="form-group">
                                    <label for="tagDescription">Mô tả ngắn</label>
                                    <input type="text" class="form-control text-center" id="tagDescription" name="tagDescription">
                                </div>
                                <?php
                                wp_nonce_field('post_tag', 'post_tag_field');
                                ?>
                                <div class="form-group d-flex justify-content-center">
                                    <a href="javascript:history.back()" class="btn btn-inverse-info btn-icon-text me-2 d-flex align-items-center"><i class="ph ph-arrow-arc-left btn-icon-prepend"></i> Quay lại</a>
                                    <button type="submit" class="btn btn-info btn-icon-text d-flex align-items-center">
                                        <span class="mdi mdi-creation-outline btn-icon-prepend fa-150p"></span> Tạo thư mục
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