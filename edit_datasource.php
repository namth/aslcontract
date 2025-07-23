<?php
/* 
    Template Name: Edit Datasource
*/
global $wpdb;

# access permission
if (!current_user_can('administrator')) {
    echo '<div class="alert alert-danger" role="alert">Bạn không có quyền truy cập</div>';
    get_footer();
    exit;
}

# get sourceID from query string, if not have, then redirect to datasource page
$sourceID = isset($_GET['sourceID']) ? $_GET['sourceID'] : '';
if (!$sourceID) {
    wp_redirect(home_url('/datasource'));
    exit;
}

# get datasource by sourceID
$table_name = $wpdb->prefix . 'asldatasource';
$datasource = $wpdb->get_row("SELECT * FROM $table_name WHERE sourceID = $sourceID");

# If datasource doesn't exist, redirect to datasource page
if (!$datasource) {
    wp_redirect(home_url('/datasource'));
    exit;
}

# Decrypt sensitive data
$username = isset($datasource->username) ? asl_encrypt($datasource->username, 'd') : '';
$password = isset($datasource->password) ? asl_encrypt($datasource->password, 'd') : '';

# process form data
if (isset($_POST['post_datasource_field']) && wp_verify_nonce($_POST['post_datasource_field'], 'post_datasource')) {
    $sourceName = $_POST['sourceName'];
    $type = $_POST['type'];
    $api = $_POST['api'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $token = $_POST['token'];
    $sourceModified = current_time('mysql');

    $table_name = $wpdb->prefix . 'asldatasource';
    $wpdb->update(
        $table_name,
        array(
            'sourceName' => $sourceName,
            'type' => $type,
            'api' => $api,
            'username' => asl_encrypt($username),
            'password' => asl_encrypt($password),
            'token' => $token,
            'sourceModified' => $sourceModified
        ),
        array('sourceID' => $sourceID)
    );
    $notification = 'Cập nhật datasource thành công';

    # if success, then redirect to list datasource page
    if (!$wpdb->last_error) {
        wp_redirect(home_url('/datasource'));
        exit;
    }
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
                            <h2 class="display-2">Sửa datasource</h2>
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
                                    <label for="sourceName">Tên datasource</label>
                                    <input type="text" class="form-control text-center" id="sourceName" name="sourceName"
                                        placeholder="Tên datasource" value="<?php echo $datasource->sourceName; ?>">
                                </div>
                                <div class="form-group d-flex flex-column">
                                    <label for="type">Loại</label>
                                    <select class="js-example-basic-single" id="type" name="type">
                                        <option value="aslapi" <?php echo ($datasource->type == 'aslapi') ? 'selected' : ''; ?>>ASL API Custom</option>
                                        <option value="aslsql" <?php echo ($datasource->type == 'aslsql') ? 'selected' : ''; ?>>ASL SQL Data</option>
                                        <option value="googlesheet" <?php echo ($datasource->type == 'googlesheet') ? 'selected' : ''; ?>>Google Sheets</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="api">API</label>
                                    <input type="text" class="form-control text-center" id="api" name="api"
                                        placeholder="API" value="<?php echo $datasource->api; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control text-center" id="username" name="username"
                                        placeholder="Username" value="<?php echo $username; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="text" class="form-control text-center" id="password" name="password"
                                        placeholder="Password" value="<?php echo $password; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="token">Token</label>
                                    <input type="text" class="form-control text-center" id="token" name="token"
                                        placeholder="Token" value="<?php echo $datasource->token; ?>">
                                </div>
                                <?php
                                wp_nonce_field('post_datasource', 'post_datasource_field');
                                ?>
                                <div class="form-group d-flex justify-content-center gap-3">
                                    <a href="javascript:history.back()" class="btn btn-light btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-arrow-arc-left me-2"></span> Quay lại
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-icon-text d-flex align-items-center">
                                        <span class="ph ph-pencil-line btn-icon-prepend fa-150p"></span> Cập nhật datasource
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
