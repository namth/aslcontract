<?php
/* 
*   Template Name: List Document
*/
get_header();
global $wpdb;

$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <?php
                $table_name = $wpdb->prefix . 'asldocument';
                # get all documents where userID = current user id, and order by documentModified DESC
                $documents = $wpdb->get_results("SELECT * FROM $table_name WHERE userID = $current_user_id ORDER BY documentModified DESC");
 
                # if have tags, then show list of tags
                echo '<div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h4 class="display-4">Danh sách file đã tạo</h4>
                        </div>
                    </div>
                    <div class="d-flex gap-3 flex-column">';

                if ($documents) {
                    foreach ($documents as $document) {
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
                                    <small><?php echo $document->documentModified; ?></small>
                                </div>
                                <div class="p-2 d-flex align-items-center card-subtitle">
                                    <i class="ph ph-user me-1"></i>
                                    <small><?php echo $current_user->display_name; ?></small>
                                </div>
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
                    echo '<i>Chưa có file nào được tạo</i>';
                }
                echo '</div>';
            ?>
        </div>
    </div>
</div>
<?php
get_footer();


