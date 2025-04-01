<?php
/* 
    Template Name: Change Password
*/
global $wpdb;
$success = false;

# get user id from url
$userid = $_GET['userid'];
# if current user is not admin, $userid is current user id
if (!$userid) {
    $userid = get_current_user_id();
} else {
    # if user is not admin, then redirect to list user page
    if (!current_user_can('administrator')) {
        wp_redirect(home_url('/list-user'));
        exit;
    }
}
# get user object
$get_user = get_user_by('ID', $userid);
# get user page link
$user_link = get_author_posts_url($userid);

# process post data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['post_user_field'])) {
        if (wp_verify_nonce($_POST['post_user_field'], 'post_user')) {
            $newpassword = $_POST['newpassword'];
            $confirmpass = $_POST['confirmpass'];
            # check if new password and confirm password is the same, and new password has at least 8 characters
            if (strlen($newpassword) < 8) {
                $notification = '<div class="alert alert-danger" role="alert"> Mật khẩu phải có ít nhất 8 ký tự</div>';
            } else {
                if ($newpassword == $confirmpass) {
                    $hash_pass = wp_hash_password($newpassword);
                    $wpdb->update($wpdb->prefix . 'users', ['user_pass' => $hash_pass], ['ID' => $userid]);
                    $notification = '<div class="alert alert-success" role="alert"> Đổi mật khẩu thành công</div>';
                    $success = true;
                } else {
                    $notification = '<div class="alert alert-danger" role="alert"> Mật khẩu không khớp</div>';
                }
            }
        }
    }
}

get_header();

?>
<div class="content-wrapper">
    <div class="col-sm-12 mb-3">
        <a href="<?php echo $user_link; ?>" class="btn btn-icon-text border-none ps-0 align-items-center d-flex"><i class="ph ph-arrow-left me-2"></i> Quay lại</a>
    </div>
    <div class="card card-rounded">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="display-2">Đổi mật khẩu</h2>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="wrapper d-flex justify-content-center align-items-center flex-column py-2">
                            <?php 
                            if (isset($notification)) {
                                echo $notification;
                            } 
                            if (!$success) {
                                
                            ?>
                            <form
                                class="forms-sample col-md-6 col-lg-4 d-flex justify-content-center flex-column text-center"
                                action="" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label class="fw-bold" for="username">Tài khoản </label>
                                    <p class="display-4"><?php echo $get_user->user_login; ?></p>
                                </div>
                                <div class="form-group">
                                    <label class="fw-bold" for="newpassword">Mật khẩu mới</label>
                                    <input type="password" class="form-control text-center" id="newpassword" name="newpassword">
                                </div>
                                <div class="form-group">
                                    <label class="fw-bold" for="confirmpass">Nhập lại mật khẩu mới</label>
                                    <input type="password" class="form-control text-center" id="confirmpass" name="confirmpass">
                                </div>
                                <?php 
                                wp_nonce_field('post_user', 'post_user_field');
                                ?>
                                <div class="form-group d-flex justify-content-center gap-3">
                                    <a href="<?php echo $user_link; ?>" class="btn btn-light btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-arrow-arc-left me-2"></span> Quay lại
                                    </a>
                                    <button type="submit" class="btn btn-info btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-magic-wand btn-icon-prepend fa-150p"></span> Đổi mật khẩu
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