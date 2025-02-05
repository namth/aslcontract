<?php
/* 
    Template Name: Edit Child Datasource
*/
global $wpdb;

# get childID from query string, if not have, then redirect to datasource page
$childID = isset($_GET['childID']) ? $_GET['childID'] : '';
if (!$childID) {
    wp_redirect(home_url('/datasource'));
    exit;
}
# get child datasource by childID
$table_name = $wpdb->prefix . 'aslchilddatasource';
$childdatasource = $wpdb->get_row("SELECT * FROM $table_name WHERE childID = $childID");

# process form data
if (isset($_POST['post_datasource_field']) && wp_verify_nonce($_POST['post_datasource_field'], 'post_datasource')) {
    $sourceName = $_POST['sourceName'];
    $sourceID = $_POST['sourceID'];
    $api = $_POST['api'];
    $header = $_POST['header'];
    $searchfield = $_POST['searchfield'];
    $childDescription = $_POST['childDescription'];
    $childModified = date('Y-m-d H:i:s');

    $table_name = $wpdb->prefix . 'aslchilddatasource';
    $wpdb->update(
        $table_name,
        array(
            'sourceID' => $sourceID,
            'childName' => $sourceName,
            'api' => $api,
            'header' => $header,
            'searchfield' => $searchfield,
            'childDescription' => $childDescription,
            'childModified' => $childModified
        ),
        array('childID' => $childID)
    );
    $notification = 'Sửa API thành công';

    # redirect to detail child datasource page
    wp_redirect(home_url('/child-data?childID=' . $childID));
    exit;
}

get_header();
?>
<div class="content-wrapper">
    <div class="col-sm-12 mb-3">
        <a href="javascript:history.back()" class="btn btn-icon-text border-none ps-0 align-items-center d-flex"><i class="ph ph-arrow-left me-2"></i> Quay lại</a>
    </div>
    <div class="card card-rounded">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="display-2">Sửa API cho datasource</h2>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="wrapper d-flex justify-content-center align-items-center flex-column py-2">
                            <?php 
                            if (isset($notification)) {
                                echo '<div class="alert alert-success" role="alert">' . $notification . '</div>';
                            } else {
                            ?>
                            
                            <form
                                class="forms-sample col-md-6 col-lg-4 d-flex justify-content-center flex-column text-center"
                                action="" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="sourceName">Tên API</label>
                                    <input type="text" class="form-control text-center" id="sourceName" name="sourceName"
                                        value="<?php echo $childdatasource->childName; ?>">
                                </div>
                                <div class="form-group d-flex flex-column">
                                    <label for="type">Data Source</label>
                                    <select class="js-example-basic-single" id="type" name="sourceID">
                                        <option value="">-- Chọn datasource --</option>
                                        <?php 
                                            # Lấy danh sách datasource từ database
                                            $datasources = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}asldatasource");
                                            foreach ($datasources as $datasource) {
                                                # if sourceID from query string is equal to sourceID in database, then add selected attribute to option
                                                if ($childdatasource->sourceID == $datasource->sourceID) {
                                                    echo '<option value="' . $datasource->sourceID . '" selected>' . $datasource->sourceName . '</option>';
                                                } else {
                                                    echo '<option value="' . $datasource->sourceID . '">' . $datasource->sourceName . '</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="api">API</label>
                                    <input type="text" class="form-control text-center" id="api" name="api"
                                        value="<?php echo $childdatasource->api; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="header">Trường dữ liệu</label>
                                    <input type="text" class="form-control text-center" id="header" name="header" value="<?php echo $childdatasource->header; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="searchfield">Dữ liệu tìm kiếm</label>
                                    <input type="text" class="form-control text-center" id="searchfield" name="searchfield" value="<?php echo $childdatasource->searchfield; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="childDescription">Mô tả</label>
                                    <textarea class="form-control text-center" id="childDescription" name="childDescription"><?php echo $childdatasource->childDescription; ?></textarea>
                                </div>
                                <?php
                                wp_nonce_field('post_datasource', 'post_datasource_field');
                                ?>
                                <div class="form-group d-flex justify-content-center gap-3">
                                    <a href="javascript:history.back()" class="btn btn-light btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-arrow-arc-left me-2"></span> Quay lại
                                    </a>
                                    <button type="submit" class="btn btn-info btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-magic-wand btn-icon-prepend fa-150p"></span> Sửa API
                                    </button>
                                </div>
                            </form>
                            <?php 
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();