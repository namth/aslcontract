<?php
/* 
    Template Name: Create Contract
*/
get_header();

use Google\Client as Google_Client;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Drive\DriveFile as Google_Service_Drive_File;
/* 
validate form data and create new contract
*/
if (isset($_POST['post_contract_field']) && wp_verify_nonce($_POST['post_contract_field'], 'post_contract')) {
    // get form data
    $contract_name = $_POST['contract_name'];
    $employee_id = $_POST['employee_id'];
    $sourceFileId = $_POST['sourceFileId'];
    $folderId = $_POST['folderId'];

    // Load credentials from file
    $client = new Google_Client();
    $client->setApplicationName("ASL Contract");
    $client->addScope(Google_Service_Drive::DRIVE);
    $client->setAccessType('offline');
    $client->setAuthConfig(__DIR__ . '/asl-contract-01fd683a00f9.json');

    // If $employee_id is 1, use the first $replacements, otherwise use the second $replacements
    if ($employee_id == 1) {
        $replacements = array(
            '{your_name}' => 'Trần Hải Nam',
            '{job}' => 'Chuyên viên IT',
            '{country}' => 'Việt Nam',
            '{CCCD}' => '123456789',
            '{your_birthday}' => '',
            '{date_of_issue}' => '21/03/2021',
            '{your_email}' => 'namth.pass@gmail.com',
            '{your_phone}' => '098.689.6800',
            '{your_address}' => 'Tổ 12 phường Sài Đồng, Long Biên, Hà Nội',
        );
        $image_replace = array(
            '{your_image}' => 'https://danviet.mediacdn.vn/2021/5/5/1-16201893641271008335156.jpg',
        );
    } else {
        $replacements = array(
            '{your_name}' => 'Nguyễn Duy Sơn',
            '{job}' => 'Nhân viên',
            '{country}' => 'Việt Nam',
            '{CCCD}' => '1234567890',
            '{date_of_issue}' => '28/05/2021',
            '{your_email}' => 'duyson126@gmail.com',
            '{your_phone}' => '0367.542.037',
            '{your_address}' => 'Xóm Yên Phú, xã Văn Thành, huyện Yên Thành, tỉnh Nghệ An',
        );
    }

    $newfilename = 'Hợp đồng lao động - ' . $replacements['{your_name}'];

    /* Clone docs file from source template file */
    $new_file = new Google_Service_Drive_File();
    $optParams = array(
        'folderId' => $folderId,
        'newfilename' => $newfilename,
    );
    $copyfileID = google_clone_file($sourceFileId, $new_file, $optParams);
    
    if ($copyfileID) {
        $notification = 'Tạo file thành công: ' . $copyfileID;
    
        $result = google_docs_replaceText($copyfileID, $replacements);
        insertImageIntoGoogleDoc($copyfileID, 'https://danviet.mediacdn.vn/2021/5/5/1-16201893641271008335156.jpg', '{your_image}');
    
    } else {
        $notification =  "Clone thất bại";
    }
}

?>
<div class="content-wrapper">
    <div class="card card-rounded">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="display-2">Tạo tài liệu mới</h2>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="wrapper d-flex justify-content-center align-items-center flex-column py-2">
                            <?php 
                            if (isset($notification)) {
                                echo '<div class="alert alert-success" role="alert">' . $notification . '</div>';
                            } else {
                            ?>
                            <div class="d-flex justify-content-center mb-3">
                                <i class="fa fa-file-text-o fa-150p"></i>
                                <div class="wrapper ms-3">
                                    <p class="ms-1 mb-1 fw-bold">Mẫu hợp đồng lao động 2024</p>
                                </div>
                            </div>
                            <form
                                class="forms-sample col-md-6 col-lg-4 d-flex justify-content-center flex-column text-center"
                                action="" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="exampleInputUsername1">Tên hợp đồng mới</label>
                                    <input type="text" class="form-control text-center" id="exampleInputUsername1" name="contract_name"
                                        placeholder="Tên file tài liệu mới" value="Hợp đồng lao động - {your_name}">
                                </div>
                                <div class="d-flex mb-3 justify-content-center">
                                    <i class="fa fa-folder-open-o fa-150p"></i>
                                    <div class="wrapper ms-3">
                                        <p class="ms-1 mb-1 fw-bold">Thư mục đích:
                                            https://drive.google.com/drive/u/3/folders/1J7DMIPwy4YGyAN6sI6gYetd-UK-8vnoV
                                        </p>
                                    </div>
                                </div>
                                <div class="form-group mt-5 mb-4">
                                    <h4 class="card-title">Dữ liệu thay thế</h4>
                                </div>
                                <div class="form-group d-flex flex-column">
                                    <label>Chọn nhân sự</label>
                                    <select class="js-example-basic-single" name="employee_id">
                                        <option value="1">Trần Hải Nam</option>
                                        <option value="2">Nguyễn Duy Sơn</option>
                                    </select>
                                </div>
                                <?php
                                wp_nonce_field('post_contract', 'post_contract_field');
                                ?>
                                <input type="hidden" name="sourceFileId" value="1a4l5i3RiMBkxwMWj20bCR3drrfM8IRUDyBAZ_EVYGOo">
                                <input type="hidden" name="folderId" value="1J7DMIPwy4YGyAN6sI6gYetd-UK-8vnoV">
                                <div class="form-group d-flex justify-content-center">
                                    <button type="submit"
                                        class="btn btn-primary btn-icon-text me-2 d-flex align-items-center"><span
                                            class="mdi mdi-creation-outline btn-icon-prepend fa-150p"></span> Tạo tài
                                        liệu</button>
                                    <!-- <button class="btn btn-light btn-icon-text"><span class="mdi mdi-close"></span> Quay lại</button> -->
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