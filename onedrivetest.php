<?php
/* 
    Template Name: OneDrive Test
*/
($config = include __DIR__ . '/config.php') or die('Configuration file not found');
$scopes = ['Files.ReadWrite.All', 'User.Read.All'];
$appId = $config['ONEDRIVE_CLIENT_ID'];
$redirectUri = $config['ONEDRIVE_REDIRECT_URI'];
$tenantId = $config['TENANT_ID'];

use Microsoft\Kiota\Authentication\Oauth\ClientCredentialContext;
use Microsoft\Graph\Core\Authentication\GraphPhpLeagueAuthenticationProvider;



get_header();
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <div class="home-tab">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link ps-0" id="home-tab" data-bs-toggle="tab" href="#overview" role="tab"
                                aria-controls="overview" aria-selected="false">Overview</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#audiences"
                                role="tab" aria-selected="true">Audiences</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#demographics" role="tab"
                                aria-selected="false">Demographics</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link border-0" id="more-tab" data-bs-toggle="tab" href="#more" role="tab"
                                aria-selected="false">More</a>
                        </li>
                    </ul>
                    <div>
                        <div class="btn-wrapper">
                            <a href="#" class="btn btn-otline-dark align-items-center"><i class="icon-share"></i>
                                Share</a>
                            <a href="#" class="btn btn-otline-dark"><i class="icon-printer"></i> Print</a>
                            <a href="#" class="btn btn-primary text-white me-0"><i class="icon-download"></i> Export</a>
                        </div>
                    </div>
                </div>
                <div class="tab-content tab-content-basic">
                    <div class="tab-pane fade" id="overview" role="tabpanel" aria-labelledby="overview">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="statistics-details align-items-center justify-content-between">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show active" id="audiences" role="tabpanel" aria-labelledby="overview">
                        Audiences


                        <a href="<?php echo get_authorize_url(); ?>" id="connectToOneDrive">Kết nối với OneDrive</a>
                        <?php

                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();

