<?php
/**
 * The template for displaying 404 pages (not found)
 */

get_header();
?>

<style>
    .full-screen-wrapper {
        display: flex;
        justify-content: center;
        padding: 20px;
    }
    
    .big-alert {
        font-size: 1.2rem;
        padding: 2rem;
    }
    
    .error-title {
        font-size: 2rem;
        font-weight: bold;
    }
</style>

<div class="full-screen-wrapper">
    <div class="content-wrapper">
        <div class="card card-rounded">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <div class="mb-4">
                            <h1 class="error-title">404 - Không Tìm Thấy Trang</h1>
                        </div>
                        <div class="alert alert-warning big-alert">
                            <h5 class="mb-3">Rất tiếc! Trang bạn đang tìm kiếm không tồn tại.</h5>
                            <p>Có vẻ như trang bạn đang cố truy cập không còn tồn tại nữa, hoặc có thể đã được chuyển đến một URL khác.</p>
                            <div class="d-flex justify-content-center mt-4">
                                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-info d-flex align-items-center">
                                    <i class="ph ph-house me-2"></i> Về trang chủ
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
?>
