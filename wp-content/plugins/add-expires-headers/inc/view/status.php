<?php

/* controlling view of cache settings*/
if ( !defined( 'ABSPATH' ) ) {
    die;
}
?>
<div class="col s12 aeh-options">
		<div class="col s12 top-mar">
      <div class="col m6 s12">
        <h5 class="left zero-mar">Plugin Status</h5>
      </div>
      <div class="col m6 s12">
        <a class="waves-effect waves-light btn right" href="#" target="_blank"><i class="material-icons left">book</i>Documentation</a>
      </div>
		</div>
    <div class="clearfix" ></div>
		<div class="divider top-mar"></div>
  <div class="row">
    <div class="col s12">
        <div class="col s12 top-mar">
          <p class="left font-p-med"><b>Check below about any issue plugin facing for working properly due to your server configuration.</b></p>
          <?php 
$error = AEH_Pro::get_instance()->main()->aeh_is_mod_not_loaded( 'mod_expires' );

if ( $error ) {
    ?>
            <p class="left"><span class="new badge white-text aeh-badge left" data-badge-caption="">1</span><b><?php 
    echo  $error ;
    ?></p>
          <?php 
} else {
    ?>
              <p class="left"><span class="new badge white-text aeh-badge-ok left" data-badge-caption="">1</span>Your Plugin have active <b>mod_expires</b> apache module which help server to cache static resources.</p>
          <?php 
}


if ( dd_aeh()->is_not_paying() ) {
    ?>
            <p class="left"><span class="new badge white-text aeh-badge left" data-badge-caption="">2</span>Free version not support adding expires headers to external resources. Please <a href="<?php 
    echo  dd_aeh()->get_upgrade_url() ;
    ?>" target="_blank"><b>Sign Up for Pro version</b></a> to get covered for adding expires headers to external resources.</p>
          <?php 
}


if ( dd_aeh()->is_not_paying() ) {
    if ( !$error ) {
        ?>
              <p class="left"><span class="new badge white-text aeh-badge left" data-badge-caption="">3</span>You are using free version of plugin hence you are missing some advance features of plugin. <a href="https://www.addexpiresheaders.com/pro-features/" target="_blank"><b>Check Pro Fetures Now.</b></a></p>
            <?php 
    }
} else {
    if ( !$error ) {
        ?>
             <p class="left"><span class="new badge white-text aeh-badge-ok left" data-badge-caption="">3</span>You have access to advance features of plugin. <a href="https://www.addexpiresheaders.com/pro-features/" target="_blank"><b>Checkout more info about same.</b></a></p>
          <?php 
    }
}

?>
        </div>
        <div class="clearfix"></div>
    		<div class="divider top-mar"></div>
    </div>
  </div>
</div>
