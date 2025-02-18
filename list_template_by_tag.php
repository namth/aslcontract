<?php
/* 
* Template Name: List Template By Tag
*/
get_header();

# get tag id
$tagid = $_GET['tagid'];
global $wpdb;

# if have not tag id, then redirect to list tag page
if (!$tagid) {
    wp_redirect(home_url('/list-folder'));
    exit;
}

# get row data of tag
$table_name = $wpdb->prefix . 'asltags';
$tag = $wpdb->get_row("SELECT * FROM $table_name WHERE tagID = $tagid");
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12 mb-3">
            <a href="<?php echo home_url('/list-folder') ?>" class="btn btn-icon-text border-none ps-0 align-items-center d-flex"><i class="ph ph-arrow-left me-2"></i> Quay lại</a>
        </div>

        <div class="col-sm-12">
            <?php
                echo '<div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4 class="display-4">' . $tag->tagName . '</h4>
                                <a href="' . home_url("/add-new-template") . '" class="btn btn-info btn-icon-text d-flex align-items-center p-2 px-3">
                                    <i class="ph ph-folder-simple-plus me-2 fa-150p"></i> Thêm mới mẫu hợp đồng
                                </a>
                            </div>
                        </div>';

                # get all templates by tag id
                $table_name = $wpdb->prefix . 'asltemplate';
                $templates = $wpdb->get_results("SELECT * FROM $table_name WHERE tagID = $tagid");

                # if have templates, then show list of templates
                if ($templates) {
                    echo '<div class="statistics-details d-flex flex-row gap-3 flex-wrap">';

                    foreach ($templates as $template) {
                        ?>
                        <div class="card card-rounded p-3 w165 asl-template gap-3">
                            <a href="<?php echo home_url('/template/?templateID=') . $template->templateID; ?>" class="d-flex justify-content-center flex-column text-center nav-link">
                                <i class="ph ph-file-text icon-lg p-4"></i>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">
                                        <?php echo $template->templateName; ?>
                                    </span>
                                </div>
                            </a>
                            <div class="asl-template-action">
                                <a href="<?php echo home_url('/template/?templateID=') . $template->templateID; ?>" class="asl-round-btn nav-link text-primary">
                                    <i class="ph ph-eye fa-150p"></i>
                                </a>
                                <a href="<?php echo home_url('/create-document?templateID=') . $template->templateID; ?>" class="asl-round-btn nav-link text-primary pr-2">
                                    <i class="ph ph-sparkle fa-150p"></i>
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                    echo '</div>';
                } else {
                    echo '<i>Chưa có mẫu hợp đồng nào.</i>';
                }
            ?>
        </div>
    </div>
</div>
<?php
get_footer();

