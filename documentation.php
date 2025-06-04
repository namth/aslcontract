<?php
/* 
    Template Name: Documentation - Hướng dẫn sử dụng
*/
get_header();
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="display-3">Hướng dẫn sử dụng hệ thống ASL Contract</h2>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="home-tab">
                        <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="overview-tab" data-bs-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">Tổng quan</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="template-tab" data-bs-toggle="tab" href="#template-guide" role="tab" aria-controls="template-guide" aria-selected="false">Quản lý Template</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="document-tab" data-bs-toggle="tab" href="#document-guide" role="tab" aria-controls="document-guide" aria-selected="false">Tạo tài liệu</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="user-tab" data-bs-toggle="tab" href="#user-guide" role="tab" aria-controls="user-guide" aria-selected="false">Quản lý nhân sự</a>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content tab-content-basic mt-4">
                            <!-- Tổng quan -->
                            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                                <div class="row">
                                    <div class="col-12">
                                        <h3 class="mb-4">Giới thiệu hệ thống</h3>
                                        <p class="lead">ASL Contract là hệ thống quản lý tài liệu và hợp đồng tự động, giúp bạn tạo ra các tài liệu chuyên nghiệp từ các mẫu có sẵn.</p>
                                        
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <div class="card card-rounded border border-primary">
                                                    <div class="card-body text-center">
                                                        <i class="ph ph-files icon-lg text-primary mb-3"></i>
                                                        <h5>Tự động hóa tài liệu</h5>
                                                        <p>Tạo tài liệu tự động từ template với dữ liệu động</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card card-rounded border border-success">
                                                    <div class="card-body text-center">
                                                        <i class="ph ph-google-drive-logo icon-lg text-success mb-3"></i>
                                                        <h5>Tích hợp Google Drive</h5>
                                                        <p>Kết nối trực tiếp với Google Drive để lưu trữ và chia sẻ</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="mt-4 mb-3">Các tính năng chính</h4>
                                        <div class="list-group">
                                            <div class="list-group-item d-flex align-items-center">
                                                <i class="ph ph-folder-open me-3 text-info"></i>
                                                <div>
                                                    <strong>Quản lý thư mục</strong> - Tổ chức template theo thư mục
                                                </div>
                                            </div>
                                            <div class="list-group-item d-flex align-items-center">
                                                <i class="ph ph-file-text me-3 text-warning"></i>
                                                <div>
                                                    <strong>Template thông minh</strong> - Hỗ trợ nhiều loại dữ liệu: text, số, ngày, công thức, hình ảnh
                                                </div>
                                            </div>
                                            <div class="list-group-item d-flex align-items-center">
                                                <i class="ph ph-database me-3 text-success"></i>
                                                <div>
                                                    <strong>Nguồn dữ liệu</strong> - Kết nối với nhiều nguồn dữ liệu khác nhau
                                                </div>
                                            </div>
                                            <div class="list-group-item d-flex align-items-center">
                                                <i class="ph ph-users me-3 text-primary"></i>
                                                <div>
                                                    <strong>Quản lý nhân sự</strong> - Phân quyền và quản lý người dùng
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hướng dẫn Template -->
                            <div class="tab-pane fade" id="template-guide" role="tabpanel" aria-labelledby="template-tab">
                                <div class="row">
                                    <div class="col-12">
                                        <h3 class="mb-4">Quản lý Template</h3>
                                        
                                        <div class="accordion" id="templateAccordion">
                                            <!-- Tạo thư mục -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="folderHeading">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#folderCollapse" aria-expanded="true" aria-controls="folderCollapse">
                                                        <i class="ph ph-folder-plus me-2"></i>
                                                        Bước 1: Tạo thư mục
                                                    </button>
                                                </h2>
                                                <div id="folderCollapse" class="accordion-collapse collapse show" aria-labelledby="folderHeading" data-bs-parent="#templateAccordion">
                                                    <div class="accordion-body">
                                                        <p>Trước khi tạo template, bạn cần tạo 2 loại thư mục khác nhau:</p>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="card border border-info">
                                                                    <div class="card-header bg-info text-white">
                                                                        <h6><i class="ph ph-folder me-2"></i>Thư mục Template (Thường)</h6>
                                                                    </div>
                                                                    <div class="card-body">
                                                                        <p><strong>Mục đích:</strong> Phân loại và tổ chức các template</p>
                                                                        <p><strong>Các bước tạo:</strong></p>
                                                                        <ol>
                                                                            <li>Vào <strong>Quản lý thư mục > Tạo thư mục mới</strong></li>
                                                                            <li>Nhập tên thư mục (ví dụ: "Hợp đồng lao động")</li>
                                                                            <li>Nhập mô tả cho thư mục</li>
                                                                            <li>Nhấn <strong>Thêm thư mục</strong></li>
                                                                        </ol>
                                                                        <p><strong>Ví dụ:</strong> "Hợp đồng nhân sự", "Tài liệu pháp lý", "Báo cáo tài chính"</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="card border border-success">
                                                                    <div class="card-header bg-success text-white">
                                                                        <h6><i class="ph ph-google-drive-logo me-2"></i>Google Tags (Thư mục đích)</h6>
                                                                    </div>
                                                                    <div class="card-body">
                                                                        <p><strong>Mục đích:</strong> Quản lý thư mục Google Drive nơi lưu trữ tài liệu được tạo</p>
                                                                        <p><strong>Các bước tạo:</strong></p>
                                                                        <ol>
                                                                            <li>Vào <strong>Quản lý thư mục Google > Tạo thư mục mới</strong></li>
                                                                            <li>Nhập tên thư mục (ví dụ: "Hợp đồng lao động")</li>
                                                                            <li>Nhập mô tả cho thư mục</li>
                                                                            <li>Nhập link google của thư mục hoặc <b>Google ID</b> của thư mục</li>
                                                                            <li>Nhấn <strong>Thêm thư mục</strong></li>
                                                                        </ol>
                                                                        <p><strong>Lưu ý:</strong> Nếu bạn nhập link google, hệ thống sẽ tự động trích xuất để lấy <b>Google ID</b> của file hoặc thư mục</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="alert alert-warning mt-3">
                                                            <i class="ph ph-warning-circle me-2"></i>
                                                            <strong>Lưu ý quan trọng:</strong>
                                                            <ul class="mb-0 mt-2">
                                                                <li><strong>Thư mục Template:</strong> Chỉ để phân loại template trong hệ thống</li>
                                                                <li><strong>Google Tags:</strong> Là địa chỉ thư mục Google Drive thực tế nơi tài liệu sẽ được lưu</li>
                                                                <li>Cần tạo cả 2 loại trước khi tạo template</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tạo template -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="createTemplateHeading">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#createTemplateCollapse" aria-expanded="false" aria-controls="createTemplateCollapse">
                                                        <i class="ph ph-file-plus me-2"></i>
                                                        Bước 2: Tạo template mới
                                                    </button>
                                                </h2>
                                                <div id="createTemplateCollapse" class="accordion-collapse collapse" aria-labelledby="createTemplateHeading" data-bs-parent="#templateAccordion">
                                                    <div class="accordion-body">
                                                        <p>Quy trình tạo template gồm 4 bước:</p>
                                                        
                                                        <h5><span class="badge bg-primary">Bước 1</span> Đặt tên mẫu</h5>
                                                        <ul>
                                                            <li>Nhập tên template rõ ràng, dễ hiểu</li>
                                                            <li>Chọn thư mục đã tạo từ dropdown</li>
                                                        </ul>

                                                        <h5><span class="badge bg-primary">Bước 2</span> Chọn file mẫu</h5>
                                                        <ul>
                                                            <li>Nhập Google File ID của tài liệu Google Docs mẫu</li>
                                                            <li>File này sẽ được sử dụng làm template gốc</li>
                                                        </ul>

                                                        <h5><span class="badge bg-primary">Bước 3</span> Cấu hình dữ liệu thay thế</h5>
                                                        <p>Đây là bước quan trọng nhất. Bạn có thể thêm các loại dữ liệu:</p>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="card border border-info">
                                                                    <div class="card-body">
                                                                        <h6><i class="ph ph-database me-2"></i>Nguồn dữ liệu</h6>
                                                                        <small>Kết nối với database để lấy thông tin nhân sự, công ty...</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="card border border-success">
                                                                    <div class="card-body">
                                                                        <h6><i class="ph ph-math-operations me-2"></i>Công thức</h6>
                                                                        <small>Tính toán tự động (ví dụ: lương * 12)</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2">
                                                            <div class="col-md-6">
                                                                <div class="card border border-warning">
                                                                    <div class="card-body">
                                                                        <h6><i class="ph ph-calendar-plus me-2"></i>Ngày tháng</h6>
                                                                        <small>Định dạng ngày theo yêu cầu</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="card border border-danger">
                                                                    <div class="card-body">
                                                                        <h6><i class="ph ph-align-left-simple me-2"></i>Text tự do</h6>
                                                                        <small>Nhập văn bản tự do khi tạo tài liệu</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <h5><span class="badge bg-primary">Bước 4</span> Cấu hình thư mục đích</h5>
                                                        <ul>
                                                            <li><strong>Chọn Google Tag:</strong> Thay vì nhập trực tiếp Google Folder ID, bạn chọn từ danh sách Google Tag có sẵn</li>
                                                            <li><strong>Quản lý Google Tag:</strong> Truy cập <em>Template > Quản lý tags</em> để tạo và quản lý các Google Tag (thư mục Google Drive)</li>
                                                            <li><strong>Tên file mặc định:</strong> Đặt tên mặc định cho file được tạo (có thể sử dụng biến như {ten_nhan_vien})</li>
                                                            <li><strong>Lưu ý:</strong> Google Tag giúp quản lý thư mục đích một cách tập trung và dễ dàng hơn</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Quản lý template -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="manageTemplateHeading">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manageTemplateCollapse" aria-expanded="false" aria-controls="manageTemplateCollapse">
                                                        <i class="ph ph-gear me-2"></i>
                                                        Bước 3: Quản lý template
                                                    </button>
                                                </h2>
                                                <div id="manageTemplateCollapse" class="accordion-collapse collapse" aria-labelledby="manageTemplateHeading" data-bs-parent="#templateAccordion">
                                                    <div class="accordion-body">
                                                        <p>Sau khi tạo template, bạn có thể:</p>
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="text-center">
                                                                    <i class="ph ph-eye icon-lg text-primary"></i>
                                                                    <h6 class="mt-2">Xem chi tiết</h6>
                                                                    <small>Xem cấu hình và thông tin template</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="text-center">
                                                                    <i class="ph ph-pencil-simple-line icon-lg text-warning"></i>
                                                                    <h6 class="mt-2">Chỉnh sửa</h6>
                                                                    <small>Sửa đổi dữ liệu thay thế</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="text-center">
                                                                    <i class="ph ph-files icon-lg text-success"></i>
                                                                    <h6 class="mt-2">Nhân bản</h6>
                                                                    <small>Tạo bản sao để tùy chỉnh</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hướng dẫn tạo tài liệu -->
                            <div class="tab-pane fade" id="document-guide" role="tabpanel" aria-labelledby="document-tab">
                                <div class="row">
                                    <div class="col-12">
                                        <h3 class="mb-4">Tạo tài liệu từ template</h3>
                                        
                                        <div class="alert alert-info">
                                            <i class="ph ph-info me-2"></i>
                                            <strong>Lưu ý:</strong> Để tạo tài liệu, bạn cần có ít nhất một template đã được cấu hình.
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5><i class="ph ph-number-circle-one me-2"></i>Chọn template</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <ol>
                                                            <li>Vào <strong>Dashboard</strong> hoặc <strong>Tạo tài liệu mới</strong></li>
                                                            <li>Chọn thư mục chứa template</li>
                                                            <li>Click vào template muốn sử dụng</li>
                                                            <li>Nhấn nút <strong>Tạo tài liệu</strong></li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5><i class="ph ph-number-circle-two me-2"></i>Nhập dữ liệu</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <p>Tùy theo cấu hình template, bạn sẽ thấy các trường:</p>
                                                        <ul>
                                                            <li><strong>Tìm kiếm dữ liệu:</strong> Nhập từ khóa để tìm trong database</li>
                                                            <li><strong>Ngày tháng:</strong> Chọn ngày từ calendar</li>
                                                            <li><strong>Text tự do:</strong> Nhập trực tiếp</li>
                                                            <li><strong>Công thức:</strong> Tự động tính toán</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mt-3">
                                            <div class="card-header">
                                                <h5><i class="ph ph-number-circle-three me-2"></i>Xử lý dữ liệu đặc biệt</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <i class="ph ph-magnifying-glass icon-lg text-primary mb-2"></i>
                                                            <h6>Tìm kiếm dữ liệu</h6>
                                                            <small>Nhập từ khóa, hệ thống sẽ hiển thị danh sách kết quả phù hợp để chọn</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <i class="ph ph-calculator icon-lg text-success mb-2"></i>
                                                            <h6>Số và công thức</h6>
                                                            <small>Số được format tự động, công thức tính toán dựa trên dữ liệu đã nhập</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <i class="ph ph-image icon-lg text-warning mb-2"></i>
                                                            <h6>Hình ảnh</h6>
                                                            <small>Paste URL hình ảnh, hệ thống sẽ chèn vào vị trí phù hợp trong tài liệu</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <i class="ph ph-diamonds-four icon-lg text-danger mb-2"></i>
                                                            <h6>Dữ liệu phức hợp</h6>
                                                            <small>Kết hợp nhiều nguồn dữ liệu thành bảng hoặc danh sách</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mt-3">
                                            <div class="card-header">
                                                <h5><i class="ph ph-check-circle me-2"></i>Hoàn thành và quản lý</h5>
                                            </div>
                                            <div class="card-body">
                                                <p>Sau khi nhấn <strong>Tạo tài liệu</strong>:</p>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="alert alert-success">
                                                            <strong>1. Tạo file</strong><br>
                                                            File được tạo tự động trên Google Drive
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="alert alert-info">
                                                            <strong>2. Thay thế dữ liệu</strong><br>
                                                            Tất cả placeholder được thay bằng dữ liệu thực
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="alert alert-warning">
                                                            <strong>3. Chia sẻ tự động</strong><br>
                                                            File được chia sẻ với email của bạn
                                                        </div>
                                                    </div>
                                                </div>

                                                <p class="mt-3">Bạn có thể:</p>
                                                <ul>
                                                    <li><i class="ph ph-eye me-2 text-primary"></i>Xem tài liệu trực tiếp trên Google Docs</li>
                                                    <li><i class="ph ph-cloud-arrow-down me-2 text-success"></i>Download file Word (.docx)</li>
                                                    <li><i class="ph ph-file-pdf me-2 text-danger"></i>Download file PDF</li>
                                                    <li><i class="ph ph-trash me-2 text-warning"></i>Xóa tài liệu (chỉ admin)</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hướng dẫn quản lý nhân sự -->
                            <div class="tab-pane fade" id="user-guide" role="tabpanel" aria-labelledby="user-tab">
                                <div class="row">
                                    <div class="col-12">
                                        <h3 class="mb-4">Quản lý nhân sự</h3>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card border border-info">
                                                    <div class="card-header bg-info text-white">
                                                        <h5><i class="ph ph-user-plus me-2"></i>Thêm nhân sự mới</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <p><strong>Quyền:</strong> Chỉ Administrator</p>
                                                        <p><strong>Các bước:</strong></p>
                                                        <ol>
                                                            <li>Vào <strong>Nhân sự > Thêm mới nhân sự</strong></li>
                                                            <li>Nhập thông tin cơ bản:
                                                                <ul>
                                                                    <li>Tên đăng nhập</li>
                                                                    <li>Mật khẩu</li>
                                                                    <li>Email</li>
                                                                    <li>Tên hiển thị</li>
                                                                </ul>
                                                            </li>
                                                            <li>Nhập thông tin bổ sung:
                                                                <ul>
                                                                    <li>Số điện thoại</li>
                                                                    <li>Chức vụ</li>
                                                                    <li>Quản lý trực tiếp</li>
                                                                </ul>
                                                            </li>
                                                            <li>Nhấn <strong>Thêm người dùng</strong></li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card border border-warning">
                                                    <div class="card-header bg-warning text-white">
                                                        <h5><i class="ph ph-pencil-simple-line me-2"></i>Chỉnh sửa thông tin</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <p><strong>Quyền:</strong> Admin hoặc chính user đó</p>
                                                        <p><strong>Thông tin có thể sửa:</strong></p>
                                                        <ul>
                                                            <li>Tên hiển thị</li>
                                                            <li>Email</li>
                                                            <li>Số điện thoại</li>
                                                            <li>Chức vụ (chỉ admin)</li>
                                                            <li>Mật khẩu</li>
                                                            <li>Nhân viên cấp dưới (chỉ admin)</li>
                                                        </ul>
                                                        <p><strong>Cách sửa:</strong></p>
                                                        <ol>
                                                            <li>Click vào tên user cần sửa</li>
                                                            <li>Chọn <strong>Chỉnh sửa thông tin</strong></li>
                                                            <li>Cập nhật thông tin</li>
                                                            <li>Nhấn <strong>Cập nhật</strong></li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mt-4">
                                            <div class="card-header">
                                                <h5><i class="ph ph-tree-structure me-2"></i>Cấu trúc tổ chức</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6>Các chức vụ trong hệ thống:</h6>
                                                        <div class="list-group">
                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                Administrator
                                                                <span class="badge bg-dark">Toàn quyền</span>
                                                            </div>
                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                Giám đốc
                                                                <span class="badge bg-primary">Cấp cao nhất</span>
                                                            </div>
                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                Phó giám đốc
                                                                <span class="badge bg-info">Cấp phó</span>
                                                            </div>
                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                Trưởng phòng
                                                                <span class="badge bg-danger">Quản lý phòng</span>
                                                            </div>
                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                Quản lý
                                                                <span class="badge bg-success">Quản lý nhóm</span>
                                                            </div>
                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                Nhân viên
                                                                <span class="badge bg-warning">Cơ bản</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Quyền truy cập:</h6>
                                                        <ul>
                                                            <li><strong>Administrator:</strong> Toàn bộ hệ thống</li>
                                                            <li><strong>Quản lý các cấp:</strong> 
                                                                <ul>
                                                                    <li>Xem tài liệu của nhân viên cấp dưới</li>
                                                                    <li>Tạo tài liệu từ template</li>
                                                                    <li>Quản lý thông tin cá nhân</li>
                                                                </ul>
                                                            </li>
                                                            <li><strong>Nhân viên:</strong>
                                                                <ul>
                                                                    <li>Tạo tài liệu cá nhân</li>
                                                                    <li>Xem tài liệu của mình</li>
                                                                    <li>Cập nhật thông tin cá nhân</li>
                                                                </ul>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mt-3">
                                            <div class="card-header">
                                                <h5><i class="ph ph-users me-2"></i>Quản lý mối quan hệ</h5>
                                            </div>
                                            <div class="card-body">
                                                <p>Hệ thống hỗ trợ thiết lập mối quan hệ quản lý giữa các nhân viên:</p>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="text-center">
                                                            <i class="ph ph-arrow-down icon-lg text-success mb-2"></i>
                                                            <h6>Nhân viên cấp dưới</h6>
                                                            <small>Danh sách người mà bạn quản lý trực tiếp</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="text-center">
                                                            <i class="ph ph-arrow-up icon-lg text-warning mb-2"></i>
                                                            <h6>Quản lý của bạn</h6>
                                                            <small>Người quản lý trực tiếp của bạn</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="text-center">
                                                            <i class="ph ph-equals icon-lg text-info mb-2"></i>
                                                            <h6>Nhân sự liên quan</h6>
                                                            <small>Tất cả người có liên quan trong công việc</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-3">
                                                    <h6>Lợi ích của việc thiết lập mối quan hệ:</h6>
                                                    <ul>
                                                        <li>Quản lý có thể xem tài liệu của nhân viên cấp dưới</li>
                                                        <li>Tự động filter dữ liệu theo quyền hạn</li>
                                                        <li>Báo cáo thống kê theo cấu trúc tổ chức</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="card card-rounded mt-4 border border-primary">
                <div class="card-body text-center">
                    <i class="ph ph-headset icon-lg text-primary mb-3"></i>
                    <h5>Cần hỗ trợ?</h5>
                    <p class="text-muted">Nếu bạn gặp khó khăn trong quá trình sử dụng, hãy liên hệ với admin hệ thống để được hỗ trợ.</p>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <i class="ph ph-envelope text-info me-2"></i>
                            <strong>Email:</strong> namth.pass@gmail.com
                        </div>
                        <div class="col-md-4">
                            <i class="ph ph-phone text-success me-2"></i>
                            <strong>Hotline:</strong> 0986896800 (Zalo Nam Trần)
                        </div>
                        <div class="col-md-4">
                            <i class="ph ph-clock text-warning me-2"></i>
                            <strong>Giờ làm việc:</strong> 8:00 - 17:30
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
