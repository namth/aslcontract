<?php 
/* 
Template Name: Add New Template
*/
get_header();
global $wpdb;

$current_user_id = get_current_user_id();

# process form data
if (isset($_POST['post_template_field']) && wp_verify_nonce($_POST['post_template_field'], 'post_template')) {
    $error = false;
    $templateName = $_POST['templateName'];
    $google_fileID = $_POST['google_fileID'];
    $googleFolderID = $_POST['googleFolderID'];
    $gDestinationFilename = $_POST['gDestinationFilename'];
    $tagID = $_POST['tagID'];

    # templateName is required, if not have, then show error message
    if (empty($templateName)) {
        $notification = 'Tên mẫu file tài liệu không được để trống';
        $error = true;
    }

    # google_fileID is required, if not have, then show error message
    if (empty($google_fileID)) {
        $notification = 'Google File ID không được để trống';
        $error = true;
    }

    # googleFolderID is required, if not have, then show error message
    if (empty($googleFolderID)) {
        $notification = 'Google Folder ID không được để trống';
        $error = true;
    }

    # gDestinationFilename is required, if not have, then show error message
    if (empty($gDestinationFilename)) {
        $notification = 'Tên file không được để trống';
        $error = true;
    }

    # Loop through $_POST to get all data replace
    # If prefix of key is 'data-', then get value of key and add to $data_replace array, switch key and value, key is trim 'data-' prefix
    $data_replace = array();
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'data-') !== false) {
            $temp_data = explode('#', substr($key, 5));
            $data_replace[$temp_data[0]][$value] = $temp_data[1];
        }
    }

    # if not error, then insert data to database
    if (!$error) {
        $table_name = $wpdb->prefix . 'asltemplate';
        $wpdb->insert(
            $table_name,
            array(
                'templateName' => $templateName,
                'tagID' => $tagID,
                'gFileID' => $google_fileID,
                'gDestinationFolderID' => $googleFolderID,
                'gDestinationFilename' => $gDestinationFilename,
                'userID' => $current_user_id,
                'templateModified' => current_time('mysql'),
            )
        );

        # if $data_replace is not empty, then insert data to database
        if ($data_replace) {
            $templateID = $wpdb->insert_id;
            $table_name = $wpdb->prefix . 'aslreplacement';
            foreach ($data_replace as $key => $value) {
                $wpdb->insert(
                    $table_name,
                    array(
                        'templateID' => $templateID,
                        'childID' => $key,
                        'dataReplace' => json_encode($value),
                    )
                );
            }
        }

        # if not success, then show error message
        if ($wpdb->last_error) {
            $notification = 'Thêm template thất bại';
        } else {
            $notification = 'Thêm template thành công';
            # redirect to list tag page
            wp_redirect(home_url('/list-template/?tagid=') . $tagID);
            exit;
        }
    } 
}

?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="display-3">Thêm mẫu tài liệu mới</h2>
                </div>
            </div>
            <?php 
                if (isset($notification)) echo '<div class="alert alert-danger">' . $notification . '</div>';
            ?>
            <form id="addnew_template_form" action="" method="post" enctype="multipart/form-data">
                <div class="asl-tab-wizard">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                        <ul class="nav nav-tabs d-sm-flex align-items-center justify-content-center w-100" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link asl_tab active p-3" id="tab1" data-bs-toggle="tab" href="#choosefile"
                                    role="tab" aria-controls="choosefile" aria-selected="true">Tên mẫu</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link asl_tab p-3" id="tab2" data-bs-toggle="tab" href="#choosefolder" role="tab"
                                    aria-selected="false">Chọn file mẫu</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link asl_tab p-3" id="tab4" data-bs-toggle="tab" href="#datareplace" role="tab"
                                    aria-selected="false">Dữ liệu thay thế</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link asl_tab p-3" id="tab3" data-bs-toggle="tab" href="#filename" role="tab"
                                    aria-selected="false">Tên và vị trí file được tạo</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content tab-content-basic">
                        <div class="tab-pane fade show active" id="choosefile" role="tabpanel" aria-labelledby="choosefile">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="statistics-details d-flex flex-row gap-3 flex-wrap justify-content-center">
                                        
                                        <div class="d-flex justify-content-center align-items-center w-100 gap-3">
                                            <label for="templateName" class="w165 text-right">Tên mẫu file tài liệu</label>
                                            <input type="text" class="form-control mw300" id="templateName" name="templateName">
                                        </div>
                                        
                                        <div class="d-flex justify-content-center w-100">
                                            <a href="#choosefolder" class="btn btn-info btn-icon-text d-flex align-items-center" >
                                                <i class="ph ph-arrow-bend-up-right btn-icon-prepend fa-150p"></i> Tiếp tục
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="choosefolder" role="tabpanel" aria-labelledby="choosefolder">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="statistics-details d-flex flex-row gap-3 flex-wrap">

                                        <div class="d-flex justify-content-center align-items-center w-100 gap-3">
                                            <label for="google_fileID" class="w165 text-right">Google File ID</label>
                                            <input type="text" class="form-control mw300" id="google_fileID" name="google_fileID">
                                        </div>

                                        <div class="d-flex justify-content-center align-items-center w-100 gap-3">
                                            <label for="tagID" class="w165 text-right">Phân loại</label>
                                            <select class="form-control js-example-basic-single w300" id="tagID" name="tagID">
                                                <option value="">-- Chọn phân loại --</option>
                                                <?php 
                                                    # get all tag from database and show here
                                                    $tags = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}asltags");
                                                    if ($tags) {
                                                        foreach ($tags as $tag) {
                                                            echo '<option value="' . $tag->tagID . '">' . $tag->tagName . '</option>';
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group d-flex justify-content-center w-100">
                                            <a href="#choosefile" class="btn btn-inverse-info btn-icon-text me-2 d-flex align-items-center"><i class="ph ph-arrow-arc-left btn-icon-prepend"></i> Quay lại</a>
                                            <a href="#datareplace" class="btn btn-info btn-icon-text d-flex align-items-center">
                                                <i class="ph ph-arrow-bend-up-right btn-icon-prepend fa-150p"></i> Tiếp tục
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="datareplace" role="tabpanel" aria-labelledby="datareplace">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="statistics-details d-flex flex-row gap-3 flex-wrap">

                                        <div id="replaceArea" class="d-flex justify-content-center w-100 flex-column gap-3 align-items-center">

                                        </div>
                                        
                                        <div class="d-flex justify-content-center w-100 flex-column gap-3 align-items-center">
                                            <button id="add_datasource" class="asl-dash-btn btn-inverse-info w-100"><i class="ph ph-plugs icon-md"></i></button>
                                            <div id="list_datasource" class="justify-content-center w-100 gap-3">
                                                <?php 
                                                    # get all datasource from database and show here
                                                    $datasources = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}asldatasource");

                                                    if ($datasources) {
                                                        foreach ($datasources as $datasource) {
                                                            echo '<div class="datasource d-flex align-items-center flex-column justify-content-center gap-3">';
                                                            echo '<h4>' . $datasource->sourceName . '</h4>';

                                                            # get child datasource from parent and show here
                                                            $child_datasources = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aslchilddatasource WHERE sourceID = {$datasource->sourceID}");
                                                            # if have child datasource then show here
                                                            if ($child_datasources) {
                                                                echo '<div class="d-flex align-items-center flex-wrap gap-3">';
                                                                foreach ($child_datasources as $child_datasource) {
                                                                    echo '<a href="#" data-childid="' . $child_datasource->childID . '" 
                                                                            class="child_datasource d-flex align-items-center flex-column justify-content-center gap-2">
                                                                            <i class="ph ph-database icon-md"></i><span>' . $child_datasource->childName . '</span></a>';
                                                                }
                                                                echo '</div>';
                                                            }

                                                            echo '</div>';
                                                        }
                                                    }
                                                ?>
                                            </div>
                                        </div>
                                    
                                        <div class="form-group d-flex justify-content-center w-100">
                                            <a href="#choosefolder" class="btn btn-inverse-info btn-icon-text me-2 d-flex align-items-center"><i class="ph ph-arrow-arc-left btn-icon-prepend"></i> Quay lại</a>
                                            <a href="#filename" class="btn btn-info btn-icon-text d-flex align-items-center"><i class="ph ph-arrow-arc-right btn-icon-prepend"></i> Tiếp tục</a>
                                            
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="filename" role="tabpanel" aria-labelledby="filename">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="statistics-details d-flex flex-row gap-3 flex-wrap">
                                        
                                        <div class="d-flex justify-content-center align-items-center w-100 gap-3">
                                            <label for="googleFolderID" class="w165 text-right">Google Folder ID</label>
                                            <input type="text" class="form-control mw300" id="googleFolderID" name="googleFolderID">
                                        </div>
                                        <div class="d-flex justify-content-center align-items-center w-100 gap-3">
                                            <label for="gDestinationFilename" class="w165 text-right">Mẫu tên file</label>
                                            <input type="text" class="form-control mw300" id="gDestinationFilename" name="gDestinationFilename">
                                        </div>

                                        <?php
                                        wp_nonce_field('post_template', 'post_template_field');
                                        ?>
                                    
                                        <div class="form-group d-flex justify-content-center w-100">
                                            <a href="#datareplace" class="btn btn-inverse-info btn-icon-text me-2 d-flex align-items-center">
                                                <i class="ph ph-arrow-bend-up-left btn-icon-prepend fa-150p"></i> Quay lại
                                            </a>
                                            <button type="submit" class="btn btn-info btn-icon-text d-flex align-items-center">
                                                <span class="mdi mdi-creation-outline btn-icon-prepend fa-150p"></span> Tạo template mới
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
get_footer();
