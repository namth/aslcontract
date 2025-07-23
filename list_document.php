<?php
/* 
*   Template Name: List Document
*/
get_header();
global $wpdb;

$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;
$your_staffs = get_staff_ids($current_user_id);
# put current user id to $your_staffs array
array_push($your_staffs, $current_user_id);

# Get search and filter parameters
$search_name = isset($_GET['search_name']) ? sanitize_text_field($_GET['search_name']) : '';
$filter_tag = isset($_GET['filter_tag']) ? intval($_GET['filter_tag']) : '';
$filter_user = isset($_GET['filter_user']) ? intval($_GET['filter_user']) : '';
$page = max(1, intval(get_query_var('paged')));
$per_page = 20;
$offset = ($page - 1) * $per_page;
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <?php
                # Build search and filter conditions
                $where_conditions = ["d.userID IN (" . implode(',', array_map('intval', $your_staffs)) . ")"];
                $query_params = [];
                
                if (!empty($search_name)) {
                    $where_conditions[] = "d.documentName LIKE %s";
                    $query_params[] = '%' . $wpdb->esc_like($search_name) . '%';
                }
                
                if (!empty($filter_user)) {
                    $where_conditions[] = "d.userID = %d";
                    $query_params[] = $filter_user;
                }
                
                if (!empty($filter_tag)) {
                    $where_conditions[] = "d.tagID = %d";
                    $query_params[] = $filter_tag;
                }
                
                $where_clause = "WHERE " . implode(' AND ', $where_conditions);
                
                # Get total count for pagination
                if (!empty($query_params)) {
                    $count_query = $wpdb->prepare("
                        SELECT COUNT(*) 
                        FROM {$wpdb->prefix}asldocument d 
                        LEFT JOIN {$wpdb->prefix}asltags t ON d.tagID = t.tagID 
                        $where_clause
                    ", $query_params);
                } else {
                    $count_query = "
                        SELECT COUNT(*) 
                        FROM {$wpdb->prefix}asldocument d 
                        LEFT JOIN {$wpdb->prefix}asltags t ON d.tagID = t.tagID 
                        $where_clause
                    ";
                }
                $total_documents = $wpdb->get_var($count_query);
                
                # Calculate pagination
                $total_pages = ceil($total_documents / $per_page);
                
                # Get documents with tags for current page
                $query_params[] = $per_page;
                $query_params[] = $offset;
                
                if (count($query_params) > 2) {
                    $documents = $wpdb->get_results($wpdb->prepare("
                        SELECT d.*, t.tagName, t.tagType 
                        FROM {$wpdb->prefix}asldocument d 
                        LEFT JOIN {$wpdb->prefix}asltags t ON d.tagID = t.tagID 
                        $where_clause 
                        ORDER BY d.documentModified DESC 
                        LIMIT %d OFFSET %d
                    ", $query_params));
                } else {
                    $documents = $wpdb->get_results($wpdb->prepare("
                        SELECT d.*, t.tagName, t.tagType 
                        FROM {$wpdb->prefix}asldocument d 
                        LEFT JOIN {$wpdb->prefix}asltags t ON d.tagID = t.tagID 
                        $where_clause 
                        ORDER BY d.documentModified DESC 
                        LIMIT %d OFFSET %d
                    ", $per_page, $offset));
                }
                
                # Get all Google tags for filter dropdown (only Google type tags)
                $all_tags = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}asltags WHERE tagType = 'google' ORDER BY tagName");
                
                # Get all staff users for filter dropdown
                $staff_users = [];
                foreach ($your_staffs as $staff_id) {
                    $user = get_userdata($staff_id);
                    if ($user) {
                        $staff_users[] = $user;
                    }
                }
 
                # Header with title, user filter, tag filter, and search
                echo '<div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h4 class="display-4">Danh sách file đã tạo</h4>
                            <div class="d-flex align-items-center gap-3">
                                <!-- User Filter -->
                                <div class="d-flex align-items-center">
                                    <i class="ph ph-user me-2 text-muted fa-150p"></i>
                                    <select class="form-control" id="filter_user" name="filter_user" onchange="filterByUser(this.value)" style="min-width: 200px;">
                                        <option value="">-- Tất cả người tạo --</option>';
                                        
                foreach ($staff_users as $user) {
                    $selected = ($filter_user == $user->ID) ? 'selected' : '';
                    echo '<option value="' . $user->ID . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
                }
                
                echo '                  </select>
                                </div>
                                <!-- Tag Filter -->
                                <div class="d-flex align-items-center">
                                    <i class="ph ph-tag me-2 text-muted fa-150p"></i>
                                    <select class="form-control" id="filter_tag" name="filter_tag" onchange="filterByTag(this.value)" style="min-width: 200px;">
                                        <option value="">-- Tất cả Google Folder --</option>';
                                        
                foreach ($all_tags as $tag) {
                    $selected = ($filter_tag == $tag->tagID) ? 'selected' : '';
                    echo '<option value="' . $tag->tagID . '" ' . $selected . '>' . esc_html($tag->tagName) . '</option>';
                }
                
                echo '                  </select>
                                </div>
                                <!-- Search -->
                                <form method="GET" class="d-flex align-items-center" id="searchForm">
                                    <input type="hidden" name="filter_user" value="' . esc_attr($filter_user) . '">
                                    <input type="hidden" name="filter_tag" value="' . esc_attr($filter_tag) . '">
                                    <div class="d-flex align-items-center" style="min-width: 300px;">
                                        <i class="ph ph-magnifying-glass fa-150p me-2"></i>
                                        <input type="text" class="form-control" name="search_name" 
                                               value="' . esc_attr($search_name) . '" 
                                               placeholder="Tìm kiếm theo tên file..."
                                               onkeypress="handleEnterKey(event)">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                    function filterByUser(userID) {
                        const searchName = document.querySelector(\'input[name="search_name"]\').value;
                        const filterTag = document.querySelector(\'select[name="filter_tag"]\').value;
                        const url = new URL(window.location.href);
                        url.searchParams.set(\'filter_user\', userID);
                        url.searchParams.set(\'filter_tag\', filterTag);
                        url.searchParams.set(\'search_name\', searchName);
                        url.searchParams.delete(\'paged\'); // Reset to first page
                        window.location.href = url.toString();
                    }
                    
                    function filterByTag(tagID) {
                        const searchName = document.querySelector(\'input[name="search_name"]\').value;
                        const filterUser = document.querySelector(\'select[name="filter_user"]\').value;
                        const url = new URL(window.location.href);
                        url.searchParams.set(\'filter_user\', filterUser);
                        url.searchParams.set(\'filter_tag\', tagID);
                        url.searchParams.set(\'search_name\', searchName);
                        url.searchParams.delete(\'paged\'); // Reset to first page
                        window.location.href = url.toString();
                    }
                    
                    function handleEnterKey(event) {
                        if (event.key === \'Enter\') {
                            event.preventDefault();
                            document.getElementById(\'searchForm\').submit();
                        }
                    }
                    </script>
                    
                    <div class="d-flex gap-3 flex-column">';

                if ($documents) {
                    foreach ($documents as $document) {
                        $document_user = get_userdata($document->userID);
                        # get user page link from $document_user->ID
                        $user_link = get_author_posts_url($document_user->ID);
                        ?>
                        <div class="card card-rounded p-2 d-flex align-items-center justify-content-between flex-row gap-3">
                            <span class="d-flex align-items-center justify-content-left nav-link ps-2 w-100">
                                <i class="ph ph-file-text fa-150p"></i>
                                <div class="p-2 d-flex">
                                    <span class="fw-bold">
                                        <?php echo $document->documentName; ?>
                                    </span>
                                </div>
                            </span>
                            <div class="d-flex align-items-center gap-3 w-100 justify-content-between">
                                <div class="p-2 d-flex align-items-center card-subtitle">
                                    <i class="ph ph-calendar-blank me-1"></i>
                                    <small><?php echo wp_date('d/m/Y H:i', strtotime($document->documentModified)); ?></small>
                                </div>
                                <div class="p-2 d-flex align-items-center card-subtitle">
                                    <i class="ph ph-user me-1"></i>
                                    <small><?php echo "<a href='$user_link' class='nav-link'>" . $document_user->display_name . "</a>"; ?></small>
                                </div>
                                <?php if ($document->tagName): ?>
                                <div class="p-2 d-flex align-items-center card-subtitle">
                                    <i class="ph ph-tag me-1"></i>
                                    <small><?php echo esc_html($document->tagName); ?></small>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <a href="<?php echo home_url('/googledrive/?action=view&documentID=' . $document->documentID); ?>" class="nav-link fa-150p" target="_blank">
                                        <i class="ph ph-eye me-2"></i>
                                    </a>
                                    <a href="<?php echo home_url('/googledrive/?action=download&documentID=' . $document->documentID); ?>" class="nav-link fa-150p" target="_blank">
                                        <i class="ph ph-cloud-arrow-down me-2"></i>
                                    </a>
                                    <a href="<?php echo home_url('/googledrive/?action=download&type=pdf&documentID=' . $document->documentID); ?>" class="nav-link fa-150p" target="_blank">
                                        <i class="ph ph-file-pdf me-2"></i>
                                    </a>
                                    <a href="<?php echo home_url('/googledrive/?action=delete&documentID=' . $document->documentID); ?>" class="nav-link fa-150p">
                                        <i class="ph ph-trash me-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="text-center p-4">
                            <i class="ph ph-file-x fa-300p text-muted mb-3"></i>
                            <p class="text-muted">Chưa có file nào được tạo</p>
                          </div>';
                }

                # Pagination
                if ($total_pages > 1) {
                    echo '<div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Document pagination">
                                <ul class="pagination">';
                    
                    # Previous button
                    if ($page > 1) {
                        $prev_url = add_query_arg(array('paged' => $page - 1, 'search_name' => $search_name, 'filter_user' => $filter_user, 'filter_tag' => $filter_tag), home_url('/list-document'));
                        echo '<li class="page-item">
                                <a class="page-link" href="' . esc_url($prev_url) . '">
                                    <i class="ph ph-caret-left"></i> Trước
                                </a>
                              </li>';
                    }
                    
                    # Page numbers
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1) {
                        $first_url = add_query_arg(array('paged' => 1, 'search_name' => $search_name, 'filter_user' => $filter_user, 'filter_tag' => $filter_tag), home_url('/list-document'));
                        echo '<li class="page-item">
                                <a class="page-link" href="' . esc_url($first_url) . '">1</a>
                              </li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $page_url = add_query_arg(array('paged' => $i, 'search_name' => $search_name, 'filter_user' => $filter_user, 'filter_tag' => $filter_tag), home_url('/list-document'));
                        $active = ($i == $page) ? 'active' : '';
                        echo '<li class="page-item ' . $active . '">
                                <a class="page-link" href="' . esc_url($page_url) . '">' . $i . '</a>
                              </li>';
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        $last_url = add_query_arg(array('paged' => $total_pages, 'search_name' => $search_name, 'filter_user' => $filter_user, 'filter_tag' => $filter_tag), home_url('/list-document'));
                        echo '<li class="page-item">
                                <a class="page-link" href="' . esc_url($last_url) . '">' . $total_pages . '</a>
                              </li>';
                    }
                    
                    # Next button
                    if ($page < $total_pages) {
                        $next_url = add_query_arg(array('paged' => $page + 1, 'search_name' => $search_name, 'filter_user' => $filter_user, 'filter_tag' => $filter_tag), home_url('/list-document'));
                        echo '<li class="page-item">
                                <a class="page-link" href="' . esc_url($next_url) . '">
                                    Tiếp <i class="ph ph-caret-right"></i>
                                </a>
                              </li>';
                    }
                    
                    echo '      </ul>
                            </nav>
                          </div>';
                }
                
                # Show pagination info at the bottom
                if ($total_documents > 0) {
                    echo '<div class="d-flex justify-content-center mt-3">
                            <div class="text-muted">
                                Hiển thị ' . (($page - 1) * $per_page + 1) . '-' . min($page * $per_page, $total_documents) . ' 
                                trong tổng số ' . $total_documents . ' file
                            </div>
                          </div>';
                }
                
                echo '</div>';
            ?>
        </div>
    </div>
</div>
<?php
get_footer();
