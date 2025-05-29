<?php
/* 
    Template Name: View Local Table Data
*/
get_header();
global $wpdb;

# access permission
if (!current_user_can('administrator')) {
    echo '<div class="alert alert-danger" role="alert">Bạn không có quyền truy cập</div>';
    get_footer();
    exit;
}

// Get childID from GET parameter
$childID = isset($_GET['childID']) ? intval($_GET['childID']) : 0;

if (!$childID) {
    echo '<div class="alert alert-danger" role="alert">Thiếu thông tin childID</div>';
    get_footer();
    exit;
}

// Get childdatasource information
$childdatasource = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM {$wpdb->prefix}aslchilddatasource WHERE childID = %d", $childID)
);

if (!$childdatasource) {
    echo '<div class="alert alert-danger" role="alert">Không tìm thấy thông tin childdatasource</div>';
    get_footer();
    exit;
}

// Get parent datasource to check its type
$datasource = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM {$wpdb->prefix}asldatasource WHERE sourceID = %d", $childdatasource->sourceID)
);

if (!$datasource) {
    echo '<div class="alert alert-danger" role="alert">Không tìm thấy thông tin datasource</div>';
    get_footer();
    exit;
}

// Check if datasource type is aslsql
$is_sql_datasource = ($datasource->type === 'aslsql');

// Get table name from the api field
$table_name = $wpdb->prefix . $childdatasource->api;

// Get column names from the header field (comma-separated)
$columns = explode(',', $childdatasource->header);

// Process DELETE request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['record_id']) && isset($_GET['id_field']) && $is_sql_datasource) {
    $record_id = $_GET['record_id'];
    $id_field = $_GET['id_field'];
    
    // Only proceed if the ID field is valid
    if (in_array($id_field, $columns)) {
        $delete_result = $wpdb->delete(
            $table_name,
            [$id_field => $record_id]
        );
        
        if ($delete_result !== false) {
            $notification = 'Đã xóa bản ghi thành công.';
            $notification_type = 'success';
        } else {
            $notification = 'Lỗi khi xóa bản ghi: ' . $wpdb->last_error;
            $notification_type = 'danger';
        }
    } else {
        $notification = 'Trường ID không hợp lệ.';
        $notification_type = 'danger';
    }
}

// Process ADD request
if (isset($_POST['action']) && $_POST['action'] == 'add_record' && $is_sql_datasource) {
    $data = [];
    $valid = true;
    
    // Get values for each column
    foreach ($columns as $column) {
        $column = trim($column);
        if (isset($_POST[$column])) {
            $data[$column] = $_POST[$column];
        } else {
            $valid = false;
            $notification = 'Thiếu thông tin cho trường: ' . $column;
            $notification_type = 'danger';
            break;
        }
    }
    
    if ($valid) {
        $insert_result = $wpdb->insert(
            $table_name,
            $data
        );
        
        if ($insert_result !== false) {
            $notification = 'Đã thêm bản ghi mới thành công.';
            $notification_type = 'success';
        } else {
            $notification = 'Lỗi khi thêm bản ghi: ' . $wpdb->last_error;
            $notification_type = 'danger';
        }
    }
}

// Check if there are search parameters
$search_query = '';
$search_value = isset($_GET['search']) ? $_GET['search'] : '';
$search_field = isset($_GET['search_field']) ? $_GET['search_field'] : '';

if ($search_value && $search_field && in_array($search_field, $columns)) {
    $search_query = $wpdb->prepare(" WHERE $search_field LIKE %s", '%' . $wpdb->esc_like($search_value) . '%');
}

// Prepare the query to get the data (with limit and pagination)
$limit = 20;
$current_page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$offset = ($current_page - 1) * $limit;

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM " . $table_name . $search_query;
$total_count = 0;
try {
    $count_result = $wpdb->get_row($count_query);
    if ($count_result) {
        $total_count = $count_result->total;
    }
} catch (Exception $e) {
    // Error handling for count query
}
$total_pages = ceil($total_count / $limit);
?>

<div class="content-wrapper">
    <div class="col-sm-12 mb-3">
        <a href="<?php echo home_url('/child-data?childID=' . $childID); ?>" class="btn btn-icon-text border-none ps-0 align-items-center d-flex">
            <i class="ph ph-arrow-left me-2"></i> Quay lại
        </a>
    </div>

    <?php if (isset($notification)): ?>
    <div class="alert alert-<?php echo $notification_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $notification; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card card-rounded">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="display-4 d-flex align-items-center">
                                <i class="ph ph-table me-2 icon-lg"></i> 
                                <?php echo esc_html($childdatasource->childName); ?> 
                                <small class="ms-2 text-muted">(<?php echo esc_html($table_name); ?>)</small>
                            </h2>
                        </div>
                    </div>

                    <?php if ($is_sql_datasource): ?>
                        
                        <div class="mb-4">
                            <form method="get" class="d-flex gap-2 align-items-end">
                                <input type="hidden" name="childID" value="<?php echo $childID; ?>">
                                <input type="hidden" name="page" value="1"> <!-- Reset to page 1 when searching -->
                                
                                <div class="form-group mb-0">
                                    <label for="search_field">Tìm kiếm theo trường</label>
                                    <select name="search_field" id="search_field" class="form-control">
                                        <?php foreach ($columns as $column): ?>
                                            <option value="<?php echo esc_attr(trim($column)); ?>" <?php selected($search_field, trim($column)); ?>>
                                                <?php echo esc_html(trim($column)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-0">
                                    <label for="search">Giá trị tìm kiếm</label>
                                    <input type="text" name="search" id="search" class="form-control" value="<?php echo esc_attr($search_value); ?>">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="ph ph-magnifying-glass"></i> Tìm kiếm
                                </button>
                                
                                <?php if ($search_value): ?>
                                    <a href="<?php echo esc_url(add_query_arg('childID', $childID, home_url('/view-local-table-data'))); ?>" class="btn btn-light">
                                        <i class="ph ph-x"></i> Xóa bộ lọc
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>

                        <!-- Add New Record Button -->
                        <div class="mb-4">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                                <i class="ph ph-plus"></i> Thêm bản ghi mới
                            </button>
                        </div>

                        <!-- Add New Record Modal -->
                        <div class="modal fade" id="addRecordModal" tabindex="-1" aria-labelledby="addRecordModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addRecordModalLabel">Thêm bản ghi mới</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="post">
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="add_record">
                                            <input type="hidden" name="childID" value="<?php echo $childID; ?>">
                                            
                                            <?php foreach ($columns as $column): ?>
                                                <?php $column = trim($column); ?>
                                                <div class="mb-3">
                                                    <label for="<?php echo esc_attr($column); ?>" class="form-label"><?php echo esc_html($column); ?></label>
                                                    <input type="text" class="form-control" id="<?php echo esc_attr($column); ?>" name="<?php echo esc_attr($column); ?>" required>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-success">Lưu bản ghi</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <?php
                            // If it's an SQL datasource, fetch and display the data
                            $sql_query = "SELECT * FROM " . $table_name . $search_query . " LIMIT " . $limit . " OFFSET " . $offset;
                            
                            try {
                                $results = $wpdb->get_results($sql_query);
                                
                                if ($results && count($results) > 0) {
                                    echo '<table class="table table-striped table-bordered">';
                                    
                                    // Table header
                                    echo '<thead><tr>';
                                    foreach ($columns as $column) {
                                        $column = trim($column);
                                        echo '<th>' . esc_html($column) . '</th>';
                                    }
                                    echo '<th>Thao tác</th>';
                                    echo '</tr></thead>';
                                    
                                    // Table body
                                    echo '<tbody>';
                                    foreach ($results as $row) {
                                        echo '<tr>';
                                        
                                        // Choose a primary key field (assuming first column as ID)
                                        $id_field = trim($columns[0]);
                                        $record_id = $row->$id_field;
                                        
                                        foreach ($columns as $column) {
                                            $column = trim($column);
                                            echo '<td class="mxw45vw">' . esc_html($row->$column ?? '') . '</td>';
                                        }
                                        
                                        // Add Delete button
                                        echo '<td>
                                            <a href="' . esc_url(add_query_arg([
                                                'childID' => $childID,
                                                'action' => 'delete',
                                                'record_id' => $record_id,
                                                'id_field' => $id_field
                                            ], home_url('/view-local-table-data'))) . '" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="return confirm(\'Bạn có chắc muốn xóa bản ghi này?\');">
                                                <i class="ph ph-trash"></i>
                                            </a>
                                        </td>';
                                        
                                        echo '</tr>';
                                    }
                                    echo '</tbody>';
                                    
                                    echo '</table>';
                                    
                                    // Pagination
                                    if ($total_pages > 1) {
                                        echo '<nav aria-label="Page navigation" class="mt-4">';
                                        echo '<ul class="pagination justify-content-center">';
                                        
                                        // Previous page link
                                        $prev_disabled = ($current_page <= 1) ? 'disabled' : '';
                                        $prev_page = max(1, $current_page - 1);
                                        echo '<li class="page-item ' . $prev_disabled . '">';
                                        echo '<a class="page-link" href="' . esc_url(add_query_arg([
                                            'childID' => $childID,
                                            'paged' => $prev_page,
                                            'search_field' => $search_field,
                                            'search' => $search_value
                                        ], home_url('/view-local-table-data'))) . '" aria-label="Previous">';
                                        echo '<span aria-hidden="true">&laquo;</span>';
                                        echo '</a>';
                                        echo '</li>';
                                        
                                        // Page number links
                                        $start_page = max(1, min($current_page - 2, $total_pages - 4));
                                        $end_page = min($total_pages, max($current_page + 2, 5));
                                        
                                        // Always show first page
                                        if ($start_page > 1) {
                                            echo '<li class="page-item">';
                                            echo '<a class="page-link" href="' . esc_url(add_query_arg([
                                                'childID' => $childID,
                                                'paged' => 1,
                                                'search_field' => $search_field,
                                                'search' => $search_value
                                            ], home_url('/view-local-table-data'))) . '">1</a>';
                                            echo '</li>';
                                            
                                            if ($start_page > 2) {
                                                echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                            }
                                        }
                                        
                                        // Page numbers
                                        for ($i = $start_page; $i <= $end_page; $i++) {
                                            $active = ($current_page == $i) ? 'active' : '';
                                            echo '<li class="page-item ' . $active . '">';
                                            echo '<a class="page-link" href="' . esc_url(add_query_arg([
                                                'childID' => $childID,
                                                'paged' => $i,
                                                'search_field' => $search_field,
                                                'search' => $search_value
                                            ], home_url('/view-local-table-data'))) . '">' . $i . '</a>';
                                            echo '</li>';
                                        }
                                        
                                        // Always show last page
                                        if ($end_page < $total_pages) {
                                            if ($end_page < $total_pages - 1) {
                                                echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                            }
                                            
                                            echo '<li class="page-item">';
                                            echo '<a class="page-link" href="' . esc_url(add_query_arg([
                                                'childID' => $childID,
                                                'paged' => $total_pages,
                                                'search_field' => $search_field,
                                                'search' => $search_value
                                            ], home_url('/view-local-table-data'))) . '">' . $total_pages . '</a>';
                                            echo '</li>';
                                        }
                                        
                                        // Next page link
                                        $next_disabled = ($current_page >= $total_pages) ? 'disabled' : '';
                                        $next_page = min($total_pages, $current_page + 1);
                                        echo '<li class="page-item ' . $next_disabled . '">';
                                        echo '<a class="page-link" href="' . esc_url(add_query_arg([
                                            'childID' => $childID,
                                            'paged' => $next_page,
                                            'search_field' => $search_field,
                                            'search' => $search_value
                                        ], home_url('/view-local-table-data'))) . '" aria-label="Next">';
                                        echo '<span aria-hidden="true">&raquo;</span>';
                                        echo '</a>';
                                        echo '</li>';
                                        
                                        echo '</ul>';
                                        echo '</nav>';
                                    }
                                    
                                    // Show pagination info
                                    $start_record = ($current_page - 1) * $limit + 1;
                                    $end_record = min($current_page * $limit, $total_count);
                                    echo '<div class="text-center text-muted mt-2">';
                                    echo 'Hiển thị ' . $start_record . ' đến ' . $end_record . ' trong tổng số ' . $total_count . ' bản ghi';
                                    echo '</div>';
                                    
                                    // Remove the limit message since we have pagination now
                                } else {
                                    echo '<div class="alert alert-warning">Không tìm thấy dữ liệu trong bảng.</div>';
                                }
                            } catch (Exception $e) {
                                echo '<div class="alert alert-danger">Lỗi khi truy vấn dữ liệu: ' . esc_html($e->getMessage()) . '</div>';
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Datasource không phải là loại SQL. Không thể hiển thị dữ liệu bảng.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($is_sql_datasource): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reset form when modal is closed
    const addRecordModal = document.getElementById('addRecordModal');
    if (addRecordModal) {
        addRecordModal.addEventListener('hidden.bs.modal', function () {
            const form = addRecordModal.querySelector('form');
            if (form) {
                form.reset();
            }
        });
    }
    
    // Auto dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });
});
</script>
<?php endif; ?>

<?php get_footer(); ?>
