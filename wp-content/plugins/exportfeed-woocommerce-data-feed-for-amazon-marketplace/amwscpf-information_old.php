<?php
//Checks cart-product-feed version
function amwscpf_version_validation()
{
    //taken from /include/update.php line 270
    $plugin_info = get_site_transient( 'update_plugins' );
    //we want to always display 'up to date', therefore we don't need the below check
    if ( !isset( $plugin_info->response[ AMWSCPF_BASENAME ] ) )
        return ' | You are up to date';
    $CPF_WP_version = $plugin_info->response[ AMWSCPF_BASENAME ]->new_version; //wordpress repository version

    $doUpdate = version_compare( $CPF_WP_version, AMWSCPF_VERSION );
    //if current version is older than wordpress repo version
    if ( $doUpdate == 1 ) return ' | <a href=\'plugins.php\'>Out of date - please update</a>';
    //else, up to date
    return ' | You are up to date';
}

function amwscpf_print_info()
{
    $lic = new AMWSCPF_License();
    $lic_validation = $lic->results['status'];
    $time = date('Y-m-d',filemtime(AMWSCPF_PATH.'/amazon-feed.php'));
    $today = date('Y-m-d');
    $datetime = new DateTime($time);
    $datetime->modify('+2 day');
    $day2 = $datetime->format('Y-m-d');


    if (($day2 == $today) && ($lic_validation == 'Invalid') ){
        echo '<div class="update-nag" id="report_msg" style="display: block"><p>Start a free trail to create the feed. <a href="http://www.exportfeed.com/" target="_blank">Contact us</a> for more details.</p></div>';
    }

    $datetime->modify('+4 day');
    $day4 = $datetime->format('Y-m-d');
    if ($day4 == $today ){
        echo '<div class="update-nag" id="report_msg" style="display: block"><p>Are you stuck on creating feeds? We create a complimentary feed according to your needs. <a href="http://www.exportfeed.com/" target="_blank">Contact us</a> for more details.</p></div>';
    }
    require_once AMWSCPF_PATH.'/core/classes/amazon_main.php';
    $amazon = new CPF_Amazon_Main();
    global $amwcore;

    $steps = ['Connect to Amazon Marketplace','Set a Default Account','Import Amazon Category Templates','Create Amazon Product Feed','Upload to Amazon Marketplace'];
    $s1 = false; $s2 = false; $s3 = false;
    $html = "";
    $finalize = false;
    $amazon_account = $amazon->check_if_any_account_is_created() > 0 ? 'dashicons dashicons-yes' : 'dashicons dashicons-no-alt';
    $default_account = $amazon->check_if_any_account_is_created(true) ? 'dashicons dashicons-yes' : 'dashicons dashicons-no-alt';
    $no_of_template = $amazon->check_if_any_template_is_imported();

    if ($amazon->check_if_any_account_is_created() <= 0 ){
        echo '<script>window.location.href = "'.admin_url('admin.php?page=exportfeed-amazon-amwscpf-admin&help=true').';</script>';
    }

    foreach ($steps as $key => $step){
        $class = 'visited';
        $link = "#";
        $span = '';
        if ($key == 0){
            if (!$amazon->check_if_any_account_is_created() > 0){
                $class = "current";
                $link = admin_url('admin.php?page=amwscpf-feed-account');
                $tip = 'Your Account Table must be empty. Goto Account page and addng a new account.';
            } else {
                $tip = 'Account Setup is completed. You can now import `Templates` varied by your account region.';
                $step1 = true;
                $span = '<span class="dashicons dashicons-yes"></span>';
            }
        }
        if ($key == 1){
            if (!$amazon->check_if_any_account_is_created(true) > 0){
                $class = "current";
                $link = admin_url('admin.php?page=amwscpf-feed-account');
                $tip = 'You have created an Account but forgot to make Default. Its one click action in the account listing table page.';
            } else {
                $tip = 'Safe to proceed next step.';
                $span = '<span class="dashicons dashicons-yes"></span>';
                $s1 = true;
            }
        }
        if ($key == 2 ){
            if (! $no_of_template > 0){
                $class = "current";
                $link = admin_url('admin.php?page=amwscpf-feed-template');
                $tip = 'You need to import the templates. To do this, Goto Template submenu and start importing the templates';
            } else {
                $tip = 'Safe to proceed next step.';
                $span = '<span class="dashicons dashicons-yes"></span>';
                $s2 = true;
            }
        }

        if ($key == 3 ){
            $class = '';
            $link = admin_url('admin.php?page=exportfeed-amazon-amwscpf-admin');
            if ($s1 && $s2) {
                $class = 'current';
                $span = '<span class="dashicons dashicons-yes"></span>';
                $step = 'Ready to Create Feed';
            }
        }
        $html .= "<li class='$class help_tip' data-tip='".$tip."'><a href='$link'>".$span.$step."</a></li>";
        }

        // shipping
        $iconurl = plugins_url( '/', __FILE__ ) . '/images/exf-sm-logo.png';
        echo
            '<div class="exf-logo-header">
            <section>
                <ol class="cd-multi-steps text-center" style="float:left">'.$html.'</ol>
            </section>
            <div class="exf-logo-link" style="float:right">
                <a target="_blank" href="http://www.exportfeed.com"><img class="exf-logo-style" src=' . $iconurl . ' alt="shopping cart logo"></a>
            </div>
            <div class=\'version-style\' style="float:right">
                <a target="_blank" href="http://www.exportfeed.com/woocommerce-product-feed/">Product Site</a> | 
                <a target="_blank" href="http://www.exportfeed.com/faq/">FAQ/Help</a> 
                '//| <a target="_blank" href="http://www.exportfeed.com/?s=">SEARCH</a> <br>
            .'<br>Version: ' . AMWSCPF_VERSION . amwscpf_version_validation() .'<br>
            </div>
         </div>
         <div style="clear:both"></div>';
}
