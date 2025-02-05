<?php
/* 
    Template Name: Detail Child Datasource
*/
get_header();
global $wpdb;

$childID = $_GET['childID'];
$child = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}aslchilddatasource WHERE childID = $childID");

if (!$child) {
    echo '<div class="alert alert-danger" role="alert">Không tìm thấy API</div>';
    get_footer();
    exit;
} else {
    $source = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}asldatasource WHERE sourceID = $child->sourceID");

}
?>
<div class="content-wrapper">
    <div class="col-sm-12 mb-3">
        <a href="<?php echo home_url('/datasource'); ?>" class="btn btn-icon-text border-none ps-0 align-items-center d-flex"><i class="ph ph-arrow-left me-2"></i> Quay lại</a>
    </div>

    <div class="card card-rounded">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="display-4 d-flex align-items-center"><i class="ph ph-database me-2 icon-lg"></i> <?php echo $child->childName; ?></h2>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="wrapper d-flex justify-content-center flex-column py-2">
                            <?php 
                            if (isset($notification)) {
                                echo $notification;
                            }

                            // print_r($child);
                            // echo "<span>Data ID: " . $child->childID . "</span>";
                            echo '<table class="table table-bordered fit-content">';
                            echo '<tr><th>Tên data</th><td>' . $child->childName . '</td></tr>';
                            echo '<tr><th>Mô tả</th><td>' . $child->childDescription . '</td></tr>';
                            echo '<tr><th>Data API</th><td>' . $child->api . '</td></tr>';
                            echo '<tr><th>Các trường dữ liệu</th><td>' . $child->header . '</td></tr>';
                            echo '<tr><th>Trường dữ liệu tìm kiếm</th><td>' . $child->searchfield . '</td></tr>';
                            echo '</table>';
                            ?>
                            <!-- Button pull data from $source to header (json format) -->
                            <div class="form-group d-flex justify-content-left mt-3">
                                <a href="<?php echo home_url('/edit-child-data?childID=') . $child->childID; ?>" class="btn btn-info btn-icon-text me-2 d-flex align-items-center">
                                    <i class="ph ph-pencil-simple-line btn-icon-prepend fa-150p"></i> Sửa dữ liệu
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();