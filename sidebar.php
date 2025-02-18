<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <?php 
            $current_permalink = get_permalink();
            $current_user_id = get_current_user_id();
            
            if (current_user_can('administrator')) {
                $menu_sidebar = [
                    'dashboard' => [
                        'title' => 'Dashboard',
                        'menu'  => [
                            [
                                'title' => 'Dashboard',
                                'url'   => get_bloginfo('url'),
                                'icon'  => 'mdi mdi-grid-large',
                            ],
                        ],
                    ],
                    'file' => [
                        'title' => 'Quản lý file',
                        'menu'  => [
                            [
                                'title' => 'File đã tạo',
                                'url'   => home_url('/list-document'),
                                'icon'  => 'mdi mdi-file-document-outline',
                            ],
                            [
                                'title' => 'Mẫu tài liệu',
                                'icon'  => 'mdi mdi-folder-table-outline',
                                'id'    => 'template',
                                'submenu' => [
                                    [
                                        'title' => 'Thư mục mẫu tài liệu',
                                        'url'   => home_url('/list-folder'),
                                    ],
                                    [
                                        'title' => 'Tạo thư mục mới',
                                        'url'   => home_url('/add-new-folder'),
                                    ],
                                    [
                                        'title' => 'Tạo tài liệu mới',
                                        'url'   => home_url('/add-new-template'),
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Nhân sự',
                                'icon'  => 'mdi mdi-account-supervisor-outline',
                                'id'    => 'users',
                                'submenu' => [
                                    [
                                        'title' => 'Tất cả nhân sự',
                                        'url'   => home_url('/list-all-user'),
                                    ],
                                    [
                                        'title' => 'Nhân sự liên quan',
                                        'url'   => home_url('/list-user'),
                                    ],
                                    [
                                        'title' => 'Thêm mới nhân sự',
                                        'url'   => home_url('/add-new-user'),
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Data Source',
                                'icon'  => 'mdi mdi-layers-outline',
                                'id'    => 'datasource',
                                'submenu' => [
                                    [
                                        'title' => 'Danh sách Data Source',
                                        'url'   => home_url('/datasource/'),
                                    ],
                                    [
                                        'title' => 'Thêm mới Data Source',
                                        'url'   => home_url('/them-datasource/'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'setting' => [
                        'title' => 'Cài đặt',
                        'menu'  => [
                            [
                                'title' => 'Tài khoản',
                                'icon'  => 'mdi mdi-account-circle-outline',
                                'url'   => get_author_posts_url($current_user_id),
                            ],
                            [
                                'title' => 'WP Admin',
                                'icon'  => 'mdi mdi-wordpress',
                                'url'   => home_url('/wp-admin'),
                            ],
                            [
                                'title' => 'Đăng xuất',
                                'url'   => wp_logout_url(home_url()),
                                'icon'  => 'mdi mdi-logout',
                            ],
                        ],
                    ],
                ];
            } else {
                # setup menu for user
                $menu_sidebar = [
                    'dashboard' => [
                        'title' => 'Dashboard',
                        'menu'  => [
                            [
                                'title' => 'Dashboard',
                                'url'   => get_bloginfo('url'),
                                'icon'  => 'mdi mdi-grid-large',
                            ],
                        ],
                    ],
                    'file' => [
                        'title' => 'Quản lý file',
                        'menu'  => [
                            [
                                'title' => 'File đã tạo',
                                'url'   => home_url('/list-document'),
                                'icon'  => 'mdi mdi-file-document-outline',
                            ],
                            [
                                'title' => 'Mẫu tài liệu',
                                'icon'  => 'mdi mdi-folder-table-outline',
                                'url'    => home_url('/list-folder'),
                            ],
                            [
                                'title' => 'Nhân sự',
                                'icon'  => 'mdi mdi-account-supervisor-outline',
                                'url'   => home_url('/list-user'),
                            ],
                        ],
                    ],
                    'setting' => [
                        'title' => 'Cài đặt',
                        'menu'  => [
                            [
                                'title' => 'Tài khoản',
                                'icon'  => 'mdi mdi-account-circle-outline',
                                'url'   => get_author_posts_url($current_user_id),
                            ],
                            [
                                'title' => 'Đăng xuất',
                                'url'   => wp_logout_url(home_url()),
                                'icon'  => 'mdi mdi-logout',
                            ],
                        ],
                    ],
                ];
            }

            # print menu for administrator
            foreach ($menu_sidebar as $key => $value) {
                # if title is not empty, then print title
                if ($value['title']) {
                    echo '<li class="nav-item nav-category">' . $value['title'] . '</li>';
                }
                foreach ($value['menu'] as $menu) {
                    # if id isset, then set link href to #id, else set link href to url
                    if (isset($menu['id'])) {
                        $href = '#' . $menu['id'];
                        $id = $menu['id'];
                        $arrow = '<i class="menu-arrow"></i>';
                        $data_attr = 'data-bs-toggle="collapse"  aria-expanded="false" aria-controls="' . $id . '"';
                    } else {
                        $href = $menu['url'];
                        $id = $arrow = $data_attr = '';
                    }

                    $url = isset($menu['url']) ? trailingslashit($menu['url']) : '#';

                    # if current url is equal to menu url, then add active class
                    if ($current_permalink == $url) {
                        $active = 'active';
                    } else {
                        $active = '';
                        $submenu_active = '';

                        # if have submenu, then check submenu url
                        if (isset($menu['submenu'])) {
                            foreach ($menu['submenu'] as $submenu) {
                                if ($current_permalink == trailingslashit($submenu['url'])) {
                                    $active = 'active';
                                    $submenu_active = 'show';
                                }
                            }
                        }
                    }

                    echo '<li class="nav-item ' . $active . '">
                            <a class="nav-link" href="' . $href . '" ' . $data_attr . '>
                                <i class="menu-icon ' . $menu['icon'] . '"></i>
                                <span class="menu-title">' . $menu['title'] . '</span>
                                ' . $arrow . '
                            </a>';

                    # if have submenu, then print submenu
                    if (isset($menu['submenu'])) {
                        echo '<div class="collapse ' . $submenu_active . '" id="' . $id . '">
                                <ul class="nav flex-column sub-menu">';

                        foreach ($menu['submenu'] as $submenu) {
                            # if current url is equal to submenu url, then add active class
                            if ($current_permalink == trailingslashit($submenu['url'])) {
                                $active = 'active';
                            } else {
                                $active = '';
                            }
                            echo '<li class="nav-item">
                                    <a class="nav-link ' . $active . '" href="' . $submenu['url'] . '">' . $submenu['title'] . '</a>';
                        }

                        echo '</ul>
                        </div>';
                    
                    }

                    echo '</li>';
                }
            }
        ?>
    </ul>
</nav>