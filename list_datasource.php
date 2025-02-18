<?php
/* 
    Template Name: List Datasource
*/
get_header();

?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <h4 class="display-4">Danh sách dữ liệu</h4>
                    <a href="<?php echo home_url('/them-datasource'); ?>" class="btn btn-info btn-icon-text d-flex align-items-center db_addnew p-2 px-3">
                        <i class="ph ph-squares-four me-2 fa-150p"></i> Thêm mới nhóm dữ liệu
                    </a>
                </div>
            </div>
            <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'asldatasource';
                # get all datasources
                $datasources = $wpdb->get_results("SELECT * FROM $table_name");
 
                # if have datasources, then show list of datasources
                if ($datasources) {
                    echo '<div class="d-flex gap-3 flex-wrap">';
                    
                    foreach ($datasources as $datasource) {
                        echo '<div class="card card-rounded p-3 d-flex justify-content-center align-items-center fit-content">';
                        echo '<h4>' . $datasource->sourceName . '</h4>';
                        
                        # get all child datasource by sourceID
                        $table_name = $wpdb->prefix . 'aslchilddatasource';
                        $childdatasources = $wpdb->get_results("SELECT * FROM $table_name WHERE sourceID = $datasource->sourceID");

                        echo '<div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">';
                        if ($childdatasources) {
                            foreach ($childdatasources as $childdatasource) {
                                echo '<a href="' . home_url('/child-data?childID=') . $childdatasource->childID . '" class="d-flex justify-content-center flex-column text-center nav-link mt-2 fit-content mw150">
                                        <i class="ph ph-database icon-lg p-2"></i>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">
                                                ' . $childdatasource->childName . '
                                            </span>
                                        </div>
                                    </a>';
                            }
                        }

                        echo '<a href="' . home_url('/them-moi-api-cho-datasource?sourceid=') . $datasource->sourceID . '" class="d-flex justify-content-center flex-column text-center nav-link mt-2 db_addnew text-warning">
                                <i class="ph ph-selection-plus icon-lg p-2"></i>
                                <div class="d-flex flex-column">
                                    <small class="">
                                        Thêm mới
                                    </small>
                                </div>
                            </a>';
                        
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
            ?>
        </div>
    </div>
</div>
<?php
get_footer();

