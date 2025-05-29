<?php
/* 
    Template Name: List Datasource
*/

# access permission
if (!current_user_can('administrator')) {
    echo '<div class="alert alert-danger" role="alert">Bạn không có quyền truy cập</div>';
    get_footer();
    exit;
}

# process delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['sourceID'])) {
    global $wpdb;
    $sourceID = $_GET['sourceID'];
    
    # Check if there are any child datasources related to this sourceID
    $child_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aslchilddatasource WHERE sourceID = $sourceID");
    
    if ($child_count > 0) {
        $notification = 'Không thể xóa datasource này vì vẫn còn API con. Vui lòng xóa các API con trước.';
        $notification_type = 'danger';
    } else {
        # Delete the datasource
        $table_name = $wpdb->prefix . 'asldatasource';
        $wpdb->delete($table_name, array('sourceID' => $sourceID));
        
        if ($wpdb->last_error) {
            $notification = 'Có lỗi xảy ra khi xóa datasource: ' . $wpdb->last_error;
            $notification_type = 'danger';
        } else {
            $notification = 'Đã xóa datasource thành công';
            $notification_type = 'success';
        }
    }
}

get_header();
?>
<style>
</style>
<div class="content-wrapper"><?php
if (isset($notification)) {
    echo '<div class="alert alert-' . $notification_type . ' alert-dismissible fade show" role="alert">
            ' . $notification . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}
?>
    <div class="row">
        <div class="col-sm-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <h4 class="display-4">Danh sách dữ liệu</h4>
                    <a href="<?php echo home_url('/them-datasource'); ?>" class="btn btn-info btn-icon-text d-flex align-items-center db_addnew p-2 px-3">
                        <i class="ph ph-squares-four me-2 fa-150p"></i> Thêm mới nhóm dữ liệu
                    </a>
                </div>
            </div>
            <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'asldatasource';
                # get all datasources
                $datasources = $wpdb->get_results("SELECT * FROM $table_name");
 
                # if have datasources, then show list of datasources
                if ($datasources) {
                    echo '<div class="d-flex gap-3 flex-wrap">';
                    
                    foreach ($datasources as $datasource) {
                        echo '<div class="card card-rounded p-3 d-flex justify-content-center align-items-center fit-content">';
                        echo '<div class="d-flex hover_display justify-content-center align-items-center gap-2">';
                        echo '<h4 class="m-1">' . $datasource->sourceName . '</h4>';
                        echo '<div class="d-none gap-2">';
                        echo '<a href="' . home_url('/sua-datasource?sourceID=') . $datasource->sourceID . '" class="nav-link edit-btn" title="Sửa datasource"><i class="ph ph-pencil-simple"></i></a>';
                        echo '<a href="' . home_url('/datasource?action=delete&sourceID=') . $datasource->sourceID . '" class="nav-link delete-btn" title="Xóa datasource" onclick="return confirm(\'Bạn có chắc chắn muốn xóa datasource này?\');"><i class="ph ph-trash text-danger"></i></a>';
                        echo '</div>';
                        echo '</div>';
                        
                        # get all child datasource by sourceID
                        $table_name = $wpdb->prefix . 'aslchilddatasource';
                        $childdatasources = $wpdb->get_results("SELECT * FROM $table_name WHERE sourceID = $datasource->sourceID");

                        echo '<div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">';
                        if ($childdatasources) {
                            foreach ($childdatasources as $childdatasource) {
                                echo '<div class="d-flex flex-column align-items-center gap-1">';
                                echo '<a href="' . home_url('/child-data?childID=') . $childdatasource->childID . '" class="d-flex justify-content-center flex-column text-center nav-link mt-2 fit-content mxw150">
                                        <i class="ph ph-database icon-lg p-2"></i>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">
                                                ' . $childdatasource->childName . '
                                            </span>
                                        </div>
                                    </a>';
                                
                                // Add view data button if datasource type is aslsql
                                if ($datasource->type === 'aslsql') {
                                    echo '<a href="' . home_url('/view-local-table?childID=') . $childdatasource->childID . '" class="d-flex align-items-center btn btn-sm btn-inverse-success" title="Xem dữ liệu bảng">
                                            <i class="ph ph-table me-2"></i> Xem dữ liệu
                                          </a>';
                                }
                                
                                echo '</div>';
                            }
                        }

                        echo '<a href="' . home_url('/them-moi-api-cho-datasource?sourceid=') . $datasource->sourceID . '" class="d-flex justify-content-center flex-column text-center nav-link mt-2 db_addnew text-warning">
                                <i class="ph ph-selection-plus icon-lg p-2"></i>
                                <div class="d-flex flex-column">
                                    <small class="">
                                        Thêm mới
                                    </small>
                                </div>
                            </a>';
                        
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
            ?>
        </div>
    </div>
</div>
<?php
get_footer();

