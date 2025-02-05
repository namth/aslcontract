<?php
/* 
    Template Name: List User
*/
get_header();

$current_user = wp_get_current_user();

?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <?php
                $staffs = get_staff_ids($current_user->ID);
 
                if ($staffs) {
                    echo '<div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4 class="display-4">Nhân sự bạn quản lý</h4>
                            </div>
                        </div>
                        <div class="statistics-details d-flex flex-row gap-3 flex-wrap">';

                    foreach ($staffs as $staff) {
                        # get user info
                        $user = get_userdata($staff);
                        # get user page link
                        $user_link = get_author_posts_url($staff);
                        # get user meta
                        $position = get_user_meta($staff, 'position', true);
                        $label = [
                            "Nhân viên" => "btn-inverse-warning",
                            "Quản lý" => "btn-inverse-success",
                            "Trưởng phòng" => "btn-inverse-danger",
                            "Phó giám đốc" => "btn-inverse-info",
                            "Giám đốc" => "btn-inverse-primary"
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

                $managers = get_manager_ids($current_user->ID);

                if ($managers) {
                    echo '<div class="d-flex justify-content-between align-items-center mb-3 mt-5">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4 class="display-4">Quản lý của bạn</h4>
                            </div>
                        </div>
                        <div class="statistics-details d-flex flex-row gap-3 flex-wrap">';
                    foreach ($managers as $manager) {
                        # get user info
                        $user = get_userdata($manager);
                        # get user page link
                        $user_link = get_author_posts_url($manager);
                        # get user meta
                        $position = get_user_meta($manager, 'position', true);
                        $label = [
                            "Nhân viên" => "btn-inverse-warning",
                            "Quản lý" => "btn-inverse-success",
                            "Trưởng phòng" => "btn-inverse-danger",
                            "Phó giám đốc" => "btn-inverse-info",
                            "Giám đốc" => "btn-inverse-primary"
                        ];
                        # get user avatar
                        $avatar = get_avatar_url($manager);
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

