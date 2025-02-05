<?php
/* 
    Template Name: Add New Datasource
*/
get_header();

# process form data
if (isset($_POST['post_datasource_field']) && wp_verify_nonce($_POST['post_datasource_field'], 'post_datasource')) {
    global $wpdb;
    $sourceName = $_POST['sourceName'];
    $type = $_POST['type'];
    $api = $_POST['api'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $token = $_POST['token'];
    $sourceModified = date('Y-m-d H:i:s');

    $table_name = $wpdb->prefix . 'asldatasource';
    $wpdb->insert(
        $table_name,
        array(
            'sourceName' => $sourceName,
            'type' => $type,
            'api' => $api,
            'username' => asl_encrypt($username),
            'password' => asl_encrypt($password),
            'token' => $token,
            'sourceModified' => $sourceModified
        )
    );
    $notification = 'Thêm datasource thành công';
}

?>
<div class="content-wrapper">
    <div class="card card-rounded">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="display-2">Thêm datasource</h2>
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
                                        placeholder="Tên datasource" value="">
                                </div>
                                <div class="form-group d-flex flex-column">
                                    <label for="type">Loại</label>
                                    <select class="js-example-basic-single" id="type" name="type">
                                        <option value="aslapi">ASL API Custom</option>
                                        <option value="aslsql">ASL SQL Data</option>
                                        <option value="oauth2">Oauth 2.0</option>
                                        <option value="refresh_token">Refresh Token</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="api">API</label>
                                    <input type="text" class="form-control text-center" id="api" name="api"
                                        placeholder="API" value="">
                                </div>
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control text-center" id="username" name="username"
                                        placeholder="Username" value="">
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="text" class="form-control text-center" id="password" name="password"
                                        placeholder="Password" value="">
                                </div>
                                <div class="form-group">
                                    <label for="token">Token</label>
                                    <input type="text" class="form-control text-center" id="token" name="token"
                                        placeholder="Token" value="">
                                </div>
                                <?php
                                wp_nonce_field('post_datasource', 'post_datasource_field');
                                ?>
                                <div class="form-group d-flex justify-content-center">
                                    <a href="javascript:history.back()" class="btn btn-light btn-icon-text me-2 d-flex align-items-center"><span class="mdi mdi-close"></span> Quay lại</a>
                                    <button type="submit" class="btn btn-primary btn-icon-text d-flex align-items-center">
                                        <span class="mdi mdi-creation-outline btn-icon-prepend fa-150p"></span> Tạo datasource
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