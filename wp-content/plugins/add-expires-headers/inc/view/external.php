<?php

/* controlling view of cache external resources settings */
if ( !defined( 'ABSPATH' ) ) {
    die;
}

if ( dd_aeh()->is_not_paying() ) {
    ?>
	<div id="test-2" class="col s12 aeh-options">
		<div class="col s12" style="margin-top:15px;vertical-align:bottom">
			<h5 class="left margin-zero" style="margin:0px">Adding Expires Headers to External Resources</h5>
			<a class="waves-effect waves-light btn-small right" href="<?php 
    echo  dd_aeh()->get_upgrade_url() ;
    ?>"><i class="material-icons left">local_offer</i>Sign-up for Pro Version!</a>
		</div>
		<div class="clearfix" style="clear:both"></div>
		<div class="divider" style="margin-top:15px"></div>
		<div id="test-2" class="col s6">
	    <ul class="row">
				<li class="valign-wrapper" style="margin-bottom:15px">
				    <i class="material-icons teal-text">check_circle</i>
				    <span style="margin-left:5px">Google Tagmanager Script</span>
				</li>
				<li class="valign-wrapper" style="margin-bottom:15px">
				    <i class="material-icons teal-text">check_circle</i>
				    <span style="margin-left:5px">Google Analytics Script</span>
				</li>
				<li class="valign-wrapper" style="margin-bottom:15px">
				    <i class="material-icons teal-text">check_circle</i>
				    <span style="margin-left:5px">Google Fonts Styles</span>
				</li>
			</ul>
		</div>
		<div id="test-2" class="col s6">
	    <ul class="row">
				<li class="valign-wrapper" style="margin-bottom:15px">
				    <i class="material-icons teal-text">check_circle</i>
				    <span style="margin-left:5px">Jquery Validation Scripts</span>
				</li>
				<li class="valign-wrapper" style="margin-bottom:15px">
				    <i class="material-icons teal-text">check_circle</i>
				    <span style="margin-left:5px">Facebook Scripts</span>
				</li>
				<li class="valign-wrapper" style="margin-bottom:15px">
				    <i class="material-icons teal-text">check_circle</i>
				    <span style="margin-left:5px">Other Third Party Scripts on website</span>
				</li>
			</ul>
		</div>
		<div class="clearfix" style="clear:both"></div>
		<div class="divider"></div>
		<div class="row center-align">
			<a class="waves-effect waves-light btn-small top-mar-30" target="_blank" href="https://www.addexpiresheaders.com/pro-features/"><i class="material-icons left">list</i>Learn More About Pro Version!</a>
		</div>
	</div>
<?php 
}
