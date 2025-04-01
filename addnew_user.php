<?php
/* 
    Template Name: Add New User
*/
global $wpdb;

# process post data
if (isset($_POST['post_user_field'])) {
    if (!wp_verify_nonce($_POST['post_user_field'], 'post_user')) {
        die('Security check');
    }

    $username = $_POST['username'];
    $password = $_POST['password'];
    $displayName = $_POST['displayName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];
    $staff = $_POST['staff'];

    # check if username is existed
    $user = get_user_by('login', $username);
    if ($user) {
        $notification = 'Tên đăng nhập đã tồn tại';
    } else {
        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            $notification = 'Có lỗi xảy ra, vui lòng thử lại';
        } else {
            # update user meta
            update_user_meta($user_id, 'display_name', $displayName);
            update_user_meta($user_id, 'phone', $phone);
            update_user_meta($user_id, 'position', $position);

            # add user to staff
            if ($staff) {
                foreach ($staff as $staff_id) {
                    # add user to aslusermanager table
                    $table_name = $wpdb->prefix . 'aslusermanager';
                    $wpdb->insert($table_name, array(
                        'managerID' => $user_id,
                        'userID' => $staff_id
                    ));
                }
            }

            $notification = 'Tạo user mới thành công';
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
                            <h2 class="display-2">Thêm mới user</h2>
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
                                    <label class="fw-bold" for="username">Tên đăng nhập</label>
                                    <input type="text" class="form-control text-center" id="username" name="username"
                                        placeholder="Tên đăng nhập" value="">
                                </div>
                                <div class="form-group">
                                    <label class="fw-bold" for="password">Mật khẩu</label>
                                    <input type="password" class="form-control text-center" id="password" name="password"
                                        placeholder="Mật khẩu" value="">
                                </div>
                                <div class="form-group">
                                    <label class="fw-bold" for="displayName">Họ và tên</label>
                                    <input type="text" class="form-control text-center" id="displayName" name="displayName" value="">
                                </div>
                                <div class="form-group">
                                    <label class="fw-bold" for="email">Email</label>
                                    <textarea class="form-control text-center" id="email" name="email"
                                        placeholder="Email"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="fw-bold" for="phone">Số điện thoại</label>
                                    <textarea class="form-control text-center" id="phone" name="phone"
                                        placeholder="Số điện thoại"></textarea>
                                </div>
                                <div class="form-group d-flex flex-column">
                                    <label class="fw-bold" for="position">Chức vụ</label>
                                    <select class="js-example-basic-single" id="position" name="position">
                                        <?php 
                                        $positions = array('Nhân viên', 'Quản lý', 'Trưởng phòng', 'Phó giám đốc', 'Giám đốc');
                                        foreach ($positions as $position) {
                                            echo '<option value="' . $position . '">' . $position . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group flex-column add_staff">
                                    <label class="fw-bold" for="staff">Nhân viên cấp dưới trực tiếp</label>
                                    <select class="js-example-basic-multiple" id="staff" name="staff[]" multiple="multiple">
                                        <?php 
                                            # get all user of wordpress here, and list out all here to show in select option with display name and email
                                            $users = get_users();
                                            foreach ($users as $user) {
                                                # do not show current user in the list
                                                if ($user->ID == get_current_user_id()) {
                                                    continue;
                                                }
                                                echo '<option value="' . $user->ID . '">' . $user->display_name . ' (' . $user->user_email . ')</option>';
                                            }

                                        ?>
                                    </select>
                                </div>

                                <?php
                                wp_nonce_field('post_user', 'post_user_field');
                                ?>
                                <div class="form-group d-flex justify-content-center gap-3">
                                    <a href="javascript:history.back()" class="btn btn-light btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-arrow-arc-left me-2"></span> Quay lại
                                    </a>
                                    <button type="submit" class="btn btn-info btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-magic-wand btn-icon-prepend fa-150p"></span> Tạo mới user
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