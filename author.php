<?php 
get_header();
$current_user_id = get_current_user_id();

# get user info
$author_id = get_the_author_meta('ID'); // Lấy ID của tác giả
$author_name = get_the_author_meta('display_name', $author_id); // Lấy tên hiển thị của tác giả
$author_email = get_the_author_meta('user_email', $author_id); // Lấy email của tác giả
?>
<div class="content-wrapper">
    <div class="col-sm-12 mb-3">
        <a href="#" onclick="javascript:history.back()" class="btn btn-icon-text border-none ps-0 align-items-center d-flex"><i class="ph ph-arrow-left me-2"></i> Quay lại</a>
    </div>

    <div class="card card-rounded">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="mt-3">
                        <div class="wrapper d-flex justify-content-center align-items-center flex-column py-2">
                            <?php 
                                # show avatar of user
                                echo get_avatar($author_id, 100, '', $author_name, ['class' => 'rounded-circle']);
                                echo '<h3 class="display-5 mb-4">' . $author_name . '</h3>';
                                echo '<p>Email: <b>' . $author_email . '</b></p>';
                                echo '<p>Số điện thoại: <b>' . get_the_author_meta('phone', $author_id) . '</b></p>';
                                echo '<p>Chức vụ: <b>' . get_the_author_meta('position', $author_id) . '</b></p>';
                            
                                if (current_user_can('administrator')) {
                                    $author_url = home_url("/edit-user?userid=" . $author_id);
                                    $change_password_url = home_url("/change-password?userid=" . $author_id);
                                } else {
                                    $author_url = home_url("/edit-user");
                                    $change_password_url = home_url("/change-password");
                                }
                                
                                if ($current_user_id == $author_id || current_user_can('administrator')) {
                                    echo '<div class="form-group d-flex justify-content-left mt-3">
                                            <a href="' . $author_url . '" class="btn btn-info btn-icon-text me-2 d-flex align-items-center">
                                                <i class="ph ph-user-gear btn-icon-prepend fa-150p"></i> Sửa thông tin
                                            </a>
                                            <a href="' . $change_password_url . '" class="btn btn-icon-text me-2 d-flex align-items-center">
                                                <i class="ph ph-password btn-icon-prepend fa-150p"></i> Đổi mật khẩu
                                            </a>
                                        </div>';
                                }
                            ?>
                            
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <?php 
        $label = [
            "Nhân viên" => "btn-inverse-warning",
            "Quản lý" => "btn-inverse-success",
            "Trưởng phòng" => "btn-inverse-danger",
            "Phó giám đốc" => "btn-inverse-info",
            "Giám đốc" => "btn-inverse-primary",
            "Administrator" => "btn-dark"
        ];

        $staffs = get_staff_ids($author_id);

        if ($staffs) {
            echo '<div class="card card-rounded mt-2">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">';
            echo '<div class="d-flex justify-content-center align-items-center mb-3">
                    <div class="d-flex justify-content-center align-items-center w-100">
                        <h4 class="display-4">Nhân sự cấp dưới</h4>
                    </div>
                </div>
                <div class="statistics-details d-flex flex-row gap-3 flex-wrap justify-content-center align-items-center">';

            foreach ($staffs as $staff) {
                # get user info
                $user = get_userdata($staff);
                # get user page link
                $user_link = get_author_posts_url($staff);
                # get user meta
                $position = get_user_meta($staff, 'position', true);
                if (in_array('administrator', $user->roles)) {
                    $position = "Administrator";
                }
                # get user avatar
                $avatar = get_avatar_url($staff);
                echo '
                <div class="card card-rounded p-3 w165">
                    <a href="' . $user_link . '" class="d-flex justify-content-center flex-column text-center nav-link">
                        <span class="fit-content badge border-radius-9 ' . $label[$position] . ' mt-2">
                            ' . $position . '
                        </span>
                        <div class="p-2 d-flex flex-column justify-content-center align-items-center gap-3">
                            <img src="' . $avatar . '" class="rounded-circle mt-3" width="100" height="100" alt="avatar">
                            <span class="fw-bold">
                                ' . $user->display_name . '
                            </span>
                        </div>
                    </a>
                </div>
                ';
            }
            echo '</div>';
            echo '          </div>
                        </div>
                    </div>
                </div>';
        } 
    ?>
</div>      
<?php
get_footer();