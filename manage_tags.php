<?php
/* 
*   Template Name: Manage Tags
*/
global $wpdb;

# access permission
if (!current_user_can('administrator')) {
    echo '<div class="alert alert-danger" role="alert">Bạn không có quyền truy cập</div>';
    get_footer();
    exit;
}

# process delete tag
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $tagID = $_GET['tagID'];
    $table_name = $wpdb->prefix . 'asltags';
    $wpdb->delete($table_name, array('tagID' => $tagID));
    wp_redirect(home_url('/manage-tags'));
    exit;
}

get_header();
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <?php
                $table_name = $wpdb->prefix . 'asltags';
                # get all tag, and order by tagModified DESC
                $tags = $wpdb->get_results("SELECT * FROM $table_name ORDER BY tagModified DESC");
 
                # if have tags, then show list of tags
                echo '<div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h4 class="display-4">Danh sách thư mục đã tạo</h4>';
                            
                # only show "Thêm mới thư mục" button to administrators
                if (current_user_can('administrator')) {
                    echo '<a href="' . home_url("/add-new-folder") . '" class="btn btn-info btn-icon-text d-flex align-items-center p-2 px-3">
                            <i class="ph ph-folder-simple-plus me-2 fa-150p"></i> Thêm mới thư mục
                          </a>';
                }

                echo '</div>
                    </div>
                    <div class="d-flex gap-3 flex-column">';

                if (!empty($tags)) {
                    foreach ($tags as $tag) {
                        ?>
                        <div class="card card-rounded p-2 d-flex align-items-center justify-content-between flex-row gap-3">
                            <span class="d-flex align-items-center justify-content-left nav-link ps-2 w-100">
                                <i class="ph ph-file-text fa-150p"></i>
                                <div class="p-2 d-flex gap-3 align-items-center">
                                    <span class="fw-bold">
                                        <?php echo $tag->tagName; ?>
                                    </span>
                                    <small class="card-subtitle"><i><?php echo $tag->tagDescription; ?></i></small>
                                </div>
                            </span>
                            <div class="d-flex align-items-center gap-3 w-100 justify-content-between">
                                <div class="p-2 d-flex align-items-center card-subtitle">
                                    <i class="ph ph-calendar-blank me-1"></i>
                                    <small><?php echo $tag->tagModified; ?></small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <a href="<?php echo home_url('/edit-tag/?tagID=' . $tag->tagID); ?>" class="nav-link fa-150p">
                                        <i class="ph ph-pencil-simple-line me-2"></i>
                                    </a>
                                    <a href="?action=delete&tagID=<?php echo $tag->tagID; ?>" class="nav-link fa-150p">
                                        <i class="ph ph-trash me-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<i>Chưa có thư mục nào được tạo</i>';
                }
                echo '</div>';
            ?>
        </div>
    </div>
</div>
<?php
get_footer();


