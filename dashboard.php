<?php
/* 
    Template Name: Dashboard
*/
get_header();

// Get current user ID
$current_user_id = get_current_user_id();
global $wpdb;

// Get 8 most recent documents created by the user
$recent_docs = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}asldocument 
        WHERE userID = %d 
        ORDER BY documentModified DESC 
        LIMIT 8",
        $current_user_id
    )
);

// Get 6 most recently used templates by the user with tag information
$recent_templates = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT t.*, tg.tagName FROM {$wpdb->prefix}asltemplate t
        LEFT JOIN {$wpdb->prefix}asltags tg ON t.tagID = tg.tagID
        INNER JOIN {$wpdb->prefix}asldocument d ON t.templateID = d.templateID
        WHERE d.userID = %d
        GROUP BY t.templateID
        ORDER BY MAX(d.documentModified) DESC
        LIMIT 6",
        $current_user_id
    )
);

?>

<div class="content-wrapper">
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-6">
            <!-- Quick Links Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="<?php echo home_url('/list-folder'); ?>" class="text-decoration-none">
                                <div class="card card-rounded text-white text-center p-4" style="background-color: #0075b1;">
                                    <i class="ph ph-folder-open icon-lg mb-2"></i>
                                    <h5>Mẫu tài liệu</h5>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?php echo home_url('/list-document'); ?>" class="text-decoration-none">
                                <div class="card card-rounded text-white text-center p-4" style="background-color: #0075b1;">
                                    <i class="ph ph-files icon-lg mb-2"></i>
                                    <h5>Tài liệu của tôi</h5>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?php echo home_url('/personnel'); ?>" class="text-decoration-none">
                                <div class="card card-rounded text-white text-center p-4" style="background-color: #0075b1;">
                                    <i class="ph ph-users icon-lg mb-2"></i>
                                    <h5>Nhân sự</h5>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recently Used Templates -->
            <div class="row">
                <div class="col-md-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Mẫu tài liệu đã dùng gần đây</h4>
                            <?php if ($recent_templates): ?>
                                <div class="statistics-details d-flex flex-row gap-3 flex-wrap">
                                    <?php foreach ($recent_templates as $template): ?>
                                        <div class="card card-rounded p-3 w32p asl-template gap-3">
                                            <a href="<?php echo home_url('/template/?templateID=') . $template->templateID; ?>" class="d-flex justify-content-center flex-column text-center nav-link">
                                                <i class="ph ph-file-text icon-lg p-3"></i>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold">
                                                        <?php echo $template->templateName; ?>
                                                    </span>
                                                    <small class="text-muted">
                                                        <?php echo $template->tagName ? $template->tagName : 'Không phân loại'; ?>
                                                    </small>
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
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="<?php echo home_url('/list-folder'); ?>" class="btn btn-info">Xem tất cả mẫu tài liệu</a>
                                </div>
                            <?php else: ?>
                                <div class="text-center">
                                    <p>Chưa có mẫu tài liệu nào được sử dụng</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Recent Documents -->
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Tài liệu đã tạo gần đây</h4>
                    <div class="d-flex gap-3 flex-column">
                        <?php if ($recent_docs): ?>
                            <?php foreach ($recent_docs as $doc): ?>
                                <div class="card card-rounded p-2 d-flex align-items-center justify-content-between flex-row gap-3">
                                    <span class="d-flex align-items-center justify-content-left nav-link ps-2 w-100">
                                        <i class="ph ph-file-text fa-150p"></i>
                                        <div class="p-2 d-flex">
                                            <span class="fw-bold">
                                                <?php echo $doc->documentName; ?>
                                            </span>
                                        </div>
                                    </span>
                                    <div class="d-flex align-items-center gap-3 w-100 justify-content-between">
                                        <div class="p-2 d-flex align-items-center card-subtitle">
                                            <i class="ph ph-calendar-blank me-1"></i>
                                            <small><?php echo date('d/m/Y', strtotime($doc->documentModified)); ?></small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center gap-2">
                                            <a href="https://docs.google.com/document/d/<?php echo $doc->gFileID; ?>/edit" class="nav-link fa-150p" target="_blank">
                                                <i class="ph ph-eye"></i>
                                            </a>
                                            <a href="<?php echo home_url('/googledrive/?action=download&documentID=' . $doc->documentID); ?>" class="nav-link fa-150p" target="_blank">
                                                <i class="ph ph-cloud-arrow-down"></i>
                                            </a>
                                            <a href="<?php echo home_url('/googledrive/?action=download&type=pdf&documentID=' . $doc->documentID); ?>" class="nav-link fa-150p" target="_blank">
                                                <i class="ph ph-file-pdf"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-3">
                                <a href="<?php echo home_url('/list-document'); ?>" class="btn text-white" style="background-color: #0075b1;">Xem tất cả tài liệu</a>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <i>Chưa có tài liệu nào được tạo</i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .w32p {
        width: 32%;
    }
    .asl-template {
        position: relative;
        transition: all 0.3s ease;
    }
    .asl-template:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .asl-template-action {
        display: flex;
        justify-content: center;
        gap: 10px;
    }
    .asl-round-btn {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }
    .asl-round-btn:hover {
        background-color: #e9ecef;
    }
</style>

<?php get_footer(); ?>
