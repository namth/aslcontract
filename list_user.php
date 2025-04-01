<?php
/* 
    Template Name: List All User
*/
get_header();

$current_user = wp_get_current_user();

?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <?php
                # get all users id
                $users = get_users();

                if ($users) {
                    echo '<div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4 class="display-4">Tất cả nhân sự</h4>
                                <a href="' . home_url("/add-new-user") . '" class="btn btn-info btn-icon-text d-flex align-items-center p-2 px-3">
                                    <i class="ph ph-user-plus me-2 fa-150p"></i> Thêm nhân sự
                                </a>
                            </div>
                        </div>
                        <div class="statistics-details d-flex flex-row gap-3 flex-wrap">';

                    foreach ($users as $user) {
                        $staff = $user->ID;
                        # get user page link
                        $user_link = get_author_posts_url($staff);
                        # get user meta
                        $position = get_user_meta($staff, 'position', true);
                        # if user is administrator, then set $position = "Admin"
                        if (in_array('administrator', $user->roles)) {
                            $position = "Administrator";
                        }
                        $label = [
                            "Nhân viên" => "btn-inverse-warning",
                            "Quản lý" => "btn-inverse-success",
                            "Trưởng phòng" => "btn-inverse-danger",
                            "Phó giám đốc" => "btn-inverse-info",
                            "Giám đốc" => "btn-inverse-primary",
                            "Administrator" => "btn-dark"
                        ];
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
                }

                
            ?>
        </div>
    </div>
</div>
<?php
get_footer();

