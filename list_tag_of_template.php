<?php
/* 
    Template Name: List Tag of Template
*/
get_header();

?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'asltags';
                # get all tags
                $tags = $wpdb->get_results("SELECT * FROM $table_name");
 
                # if have tags, then show list of tags
                if ($tags) {
                    echo '<div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4 class="display-4">Danh sách thư mục</h4>
                                <a href="' . home_url("/add-new-folder") . '" class="btn btn-info btn-icon-text d-flex align-items-center">
                                    <i class="ph ph-folder-simple-plus me-2"></i> Thêm mới thư mục
                                </a>
                            </div>
                        </div>
                        <div class="statistics-details d-flex flex-row gap-3 flex-wrap">';

                    foreach ($tags as $tag) {
                        # count number of templates which have this tag->tagID
                        $table_name = $wpdb->prefix . 'asltemplate';
                        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE tagID = $tag->tagID");

                        if ($count) {
                            ?>
                            <div class="card card-rounded p-3 w165">
                                <a href="<?php echo home_url('/list-template/?tagid=' . $tag->tagID); ?>" class="d-flex justify-content-center flex-column text-center nav-link">
                                    <i class="ph ph-folder-open icon-lg p-4"></i>
                                    <div class="p-2 d-flex flex-column">
                                        <span class="fw-bold">
                                            <?php echo $tag->tagName; ?>
                                        </span>
                                        <p class="bagde mt-2">
                                            <i><?php echo $count; ?> mẫu tài liệu</i>
                                        </p>
                                    </div>
                                </a>
                            </div>
                            <?php
                        }
                    }
                    echo '</div>';
                }
            ?>
        </div>
    </div>
</div>
<?php
get_footer();

