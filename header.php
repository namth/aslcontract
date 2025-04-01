<?php
  /* 
  * if user not logged in, redirect to login page
  */
  if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
  }

  $current_user = wp_get_current_user();
  global $wpdb;

  // Count total documents created by user
  $doc_count = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}asldocument 
        WHERE userID = %d",
        $current_user->ID
    )
  );
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?php echo get_the_title(); ?> </title>
  <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon.png" />
  <?php wp_head(); ?>
</head>

<body class="with-welcome-text">
  <div class="container-scroller">
    <!-- partial:../../partials/_navbar.html -->
    <nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3">
          <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
            <span class="icon-menu"></span>
          </button>
        </div>
        <div>
          <a class="navbar-brand brand-logo" href="<?php echo home_url(); ?>">
            <img src="<?php echo get_template_directory_uri(); ?>/img/inova_logo_900.png" alt="logo" />
          </a>
          <a class="navbar-brand brand-logo-mini" href="<?php echo home_url(); ?>">
            <img src="<?php echo get_template_directory_uri(); ?>/img/inova_logo_icon.webp" alt="logo" />
          </a>
        </div>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-top">
        <ul class="navbar-nav">
          <li class="nav-item fw-semibold d-none d-lg-block ms-0">
            <h1 class="welcome-text">Chào mừng <span class="text-black fw-bold"><?php echo wp_get_current_user()->display_name; ?></span></h1>
            <h3 class="welcome-sub-text">Bạn đã tạo <span style="color: #003366;"><?php echo $doc_count; ?> tài liệu</span> cho đến nay!</h3>
          </li>
        </ul>
        <ul class="navbar-nav ms-auto">
          <!-- <li class="nav-item">
            <form class="search-form" action="#">
              <i class="icon-search"></i>
              <input type="search" class="form-control" placeholder="Search Here" title="Search here">
            </form>
          </li> -->
          <li class="nav-item">
            <span class="fw-semibold"><?php echo $current_user->display_name; ?></span>
          </li>
          <li class="nav-item dropdown d-none d-lg-block user-dropdown">
            <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
              <img class="img-xs rounded-circle"
                src="<?php 
                        # get user avatar and display url of avatar
                        echo get_avatar_url($current_user->ID);
                      ?>" alt="Profile image">
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
              <div class="dropdown-header text-center">
                <a class="nav-link" id="UserDropdown" href="<?php 
                                                              # display admin url
                                                              echo admin_url();
                                                              ?>">
                  <img class="img-md rounded-circle"
                    src="<?php 
                          # get user avatar and display url of avatar
                          echo get_avatar_url($current_user->ID);
                        ?>" alt="Profile image">
                </a>
                <p class="mb-1 mt-3 fw-semibold"><?php echo $current_user->display_name; ?></p>
                <p class="fw-light text-muted mb-0"><?php echo $current_user->user_email; ?></p>
              </div>
              <!-- <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> My
                Profile <span class="badge badge-pill badge-danger">1</span></a> -->
              <a class="dropdown-item" href="<?php echo wp_logout_url(home_url()); ?>"><i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Đăng xuất</a>
            </div>
          </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
          data-bs-toggle="offcanvas">
          <span class="mdi mdi-menu"></span>
        </button>
      </div>
    </nav>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:../../partials/_sidebar.html -->
      <?php 
        get_sidebar();
      ?>
      <!-- partial -->
      <div class="main-panel">