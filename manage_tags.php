<?php
/* 
*   Template Name: Manage Tags
*/
use Google\Service\Drive;

global $wpdb;
global $client;

# Get tag type from URL parameter, default to 'normal'
$tagType = isset($_GET['tagType']) ? sanitize_text_field($_GET['tagType']) : 'normal';

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
    
    # Redirect back with tagType parameter preserved
    $redirectUrl = add_query_arg('tagType', $tagType, home_url('/manage-tags'));
    wp_redirect($redirectUrl);
    exit;
}

get_header();
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <?php
                $table_name = $wpdb->prefix . 'asltags';
                # get tags based on tagType parameter, and order by tagModified DESC
                $tags = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE tagType = %s ORDER BY tagModified DESC", $tagType));
 
                # Set dynamic content based on tagType
                $pageTitle = ($tagType === 'google') ? 'Quản lý tài liệu Google' : 'Danh sách thư mục đã tạo';
                $addButtonText = ($tagType === 'google') ? 'Thêm mới tài liệu Google' : 'Thêm mới thư mục';
                $addButtonIcon = ($tagType === 'google') ? 'ph-google-drive-logo' : 'ph-folder-simple-plus';
                $addButtonUrl = ($tagType === 'google') ? home_url("/add-new-folder?tagType=google") : home_url("/add-new-folder");
                $itemIcon = ($tagType === 'google') ? 'ph-google-drive-logo' : 'ph-file-text';
                $emptyMessage = ($tagType === 'google') ? 'Chưa có tài liệu Google nào được tạo' : 'Chưa có thư mục nào được tạo';

                # if have tags, then show list of tags
                echo '<div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h4 class="display-4">' . $pageTitle . '</h4>';
                            
                # only show add button to administrators
                if (current_user_can('administrator')) {
                    echo '<a href="' . $addButtonUrl . '" class="btn btn-info btn-icon-text d-flex align-items-center p-2 px-3">
                            <i class="' . $addButtonIcon . ' me-2 fa-150p"></i> ' . $addButtonText . '
                          </a>';
                }

                echo '</div>
                    </div>
                    <div class="d-flex gap-3 flex-column">';

                if (!empty($tags)) {
                    // Initialize Drive service once for all tags
                    $driveService = null;
                    if ($tagType === 'google') {
                        try {
                            $driveService = new Drive($client);
                        } catch (Exception $e) {
                            // Drive service initialization failed
                        }
                    }

                    foreach ($tags as $tag) {
                        # Check connection status for Google tags
                        $connectionStatus = '';
                        $connectionTitle = '';
                        if ($tagType === 'google' && !empty($tag->googleFileID)) {
                            $systemEmail = GG_APP_EMAIL ?? '';
                            if ($systemEmail) {
                                $shareStatus = isItemSharedWithEmail($tag->googleFileID, $systemEmail);
                                if ($shareStatus['shared']) {
                                    $connectionStatus = '<span class="connection-dot connected me-2" title="Đã kết nối với hệ thống">●</span>';
                                    $connectionTitle = 'Đã kết nối với hệ thống';
                                } else {
                                    $connectionStatus = '<span class="connection-dot disconnected me-2" title="Chưa kết nối với hệ thống">●</span>';
                                    $connectionTitle = 'Chưa kết nối với hệ thống';
                                }
                            }
                        }
                        ?>
                        <div class="card card-rounded p-2 d-flex align-items-center justify-content-between flex-row gap-3">
                            <span class="d-flex align-items-center justify-content-left nav-link ps-2 w-100">
                                <?php echo $connectionStatus; ?>
                                <i class="<?php echo $itemIcon; ?> fa-150p"></i>
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
                                    <small><?php echo wp_date('d/m/Y H:i', strtotime($tag->tagModified)); ?></small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <?php if ($tagType === 'google' && !empty($tag->googleFileID)): ?>
                                        <?php
                                        // Get Google Drive webViewLink using API
                                        $viewLink = '';
                                        if ($driveService) {
                                            try {
                                                $file = $driveService->files->get($tag->googleFileID, array(
                                                    'fields' => 'webViewLink'
                                                ));
                                                $viewLink = $file->webViewLink ?? '';
                                            } catch (Exception $e) {
                                                // Fallback to constructed link if API fails
                                                $viewLink = 'https://docs.google.com/document/d/' . $tag->googleFileID . '/edit';
                                            }
                                        } else {
                                            // Fallback when service is not available
                                            $viewLink = 'https://docs.google.com/document/d/' . $tag->googleFileID . '/edit';
                                        }
                                        ?>
                                        <?php if ($viewLink): ?>
                                            <a href="<?php echo esc_url($viewLink); ?>" target="_blank" class="nav-link fa-150p" title="Mở tài liệu Google">
                                                <i class="ph ph-arrow-square-out me-2"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <a href="<?php echo home_url('/edit-tag/?tagID=' . $tag->tagID); ?>" class="nav-link fa-150p">
                                        <i class="ph ph-pencil-simple-line me-2"></i>
                                    </a>
                                    <a href="?action=delete&tagID=<?php echo $tag->tagID; ?>&tagType=<?php echo urlencode($tagType); ?>" class="nav-link fa-150p">
                                        <i class="ph ph-trash me-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<i>' . $emptyMessage . '</i>';
                }
                echo '</div>';
            ?>
        </div>
    </div>
</div>
<?php
get_footer();


