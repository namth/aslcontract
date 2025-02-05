<?php
/* 
    Template Name: Edit User
*/
global $wpdb;

# get user id from url
$userid = $_GET['userid'];
# if current user is not admin, $userid is current user id
if (!$userid && current_user_can('administrator')) {
    $userid = get_current_user_id();
} else {
    wp_redirect(home_url('/list-user'));
    exit;
}
# get user object
$current_user = get_user_by('ID', $userid);
# get user meta
$position = get_user_meta($userid, 'position', true);
$phone = get_user_meta($userid, 'phone', true);

# process post data
if (isset($_POST['post_user_field'])) {
    if (!wp_verify_nonce($_POST['post_user_field'], 'post_user')) {
        die('Security check');
    }

    $username = $_POST['username'];
    $displayName = $_POST['displayName'];
    $email = $_POST['email'];
    $new_phone = $_POST['phone'];
    $new_position = $_POST['position'];
    $staff = $_POST['staff'];

    # check if username is existed and email is existed
    $user = get_user_by('login', $username);
    $user_email = get_user_by('email', $email);
    if ($user && $user->ID != $userid) {
        $notification = 'Tên đăng nhập đã tồn tại';
    } else if ($user_email && $user_email->ID != $userid) {
        $notification = 'Email đã tồn tại';
    } else {
        # update user
        $user_id = wp_update_user(array(
            'ID' => $userid,
            'user_login' => $username,
            'display_name' => $displayName,
            'user_email' => $email
        ));
        if (is_wp_error($user_id)) {
            $notification = 'Có lỗi xảy ra, vui lòng thử lại';
        } else {
            # update user meta
            # if $phone is not empty and not equal to current phone, then update phone
            if ($new_phone && $new_phone != $phone) {
                update_user_meta($user_id, 'phone', $new_phone);
            }
            # if $position is not empty, then update position
            if ($new_position) {
                update_user_meta($user_id, 'position', $new_position);
            }

            # update user to staff
            $table_name = $wpdb->prefix . 'aslusermanager';
            $wpdb->delete($table_name, array('managerID' => $userid));
            # if $staff is not empty and $new_position is not equal "Nhân viên", then add user to staff
            if ($staff && $new_position != 'Nhân viên') {
                foreach ($staff as $staff_id) {
                    # add user to aslusermanager table
                    $wpdb->insert($table_name, array(
                        'managerID' => $userid,
                        'userID' => $staff_id
                    ));
                }
            }

            $notification = 'Sửa user thành công';
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
                            <h2 class="display-2">Sửa thông tin user</h2>
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
                                        placeholder="Tên đăng nhập" value="<?php echo $current_user->user_login; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="fw-bold" for="displayName">Họ và tên</label>
                                    <input type="text" class="form-control text-center" id="displayName" name="displayName" value="<?php echo $current_user->display_name; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="fw-bold" for="email">Email</label>
                                    <input type="text" class="form-control text-center" id="email" name="email" value="<?php echo $current_user->user_email; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="fw-bold" for="phone">Số điện thoại</label>
                                    <input type="text" class="form-control text-center" id="phone" name="phone" value="<?php echo $phone; ?>">
                                </div>
                                <div class="form-group d-flex flex-column">
                                    <label class="fw-bold" for="position">Chức vụ</label>
                                    <?php 
                                        # if current user is admin, then show select option to choose position, if not, then show text label with span tag
                                        if (current_user_can('administrator')) {
                                            echo '<select class="js-example-basic-single" id="position" name="position">';
                                            $positions = array('Nhân viên', 'Quản lý', 'Trưởng phòng', 'Phó giám đốc', 'Giám đốc');
                                            foreach ($positions as $pos) {
                                                if ($pos == $position) {
                                                    echo '<option value="' . $pos . '" selected>' . $pos . '</option>';
                                                } else {
                                                    echo '<option value="' . $pos . '">' . $pos . '</option>';
                                                }
                                            }
                                            echo '</select>';
                                        } else {
                                            echo '<span>' . $position . '</span>';
                                        }
                                    ?>
                                    
                                </div>
                                <?php 
                                # get all userID from aslusermanager table where managerID is current user id
                                $staffs = get_staff_ids($userid);

                                # if current user is administrator, then show select option to choose staff, 
                                echo '<div class="form-group flex-column add_staff" style="display: flex;">
                                        <label class="fw-bold" for="staff">Nhân viên cấp dưới trực tiếp</label>
                                        <select class="js-example-basic-multiple" id="staff" name="staff[]" multiple="multiple">'; 
                                            # get all user of wordpress here, and list out all here to show in select option with display name and email
                                            $users = get_users();
                                            foreach ($users as $user) {
                                                # don't show current user in select option
                                                if ($user->ID == $userid) {
                                                    continue;
                                                }
                                                if (in_array($user->ID, $staffs)) {
                                                    echo '<option value="' . $user->ID . '" selected>' . $user->display_name . ' (' . $user->user_email . ')</option>';
                                                } else {
                                                    echo '<option value="' . $user->ID . '">' . $user->display_name . ' (' . $user->user_email . ')</option>';
                                                }
                                            }

                                echo '  </select>';
                                echo '</div>';

                                wp_nonce_field('post_user', 'post_user_field');
                                ?>
                                <div class="form-group d-flex justify-content-center gap-3">
                                    <a href="javascript:history.back()" class="btn btn-light btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-arrow-arc-left me-2"></span> Quay lại
                                    </a>
                                    <button type="submit" class="btn btn-info btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-magic-wand btn-icon-prepend fa-150p"></span> Sửa user
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