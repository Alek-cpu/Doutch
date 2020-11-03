<?php
$cpf_class->prepare_items();
?>
<div style="display: none;" id="ajax-loader-cat-import" ></div>
<div class="wrap">
    <div id ='amwscpf-update-nag' style="display: none;" class="update-nag">Processing feed Result...</div>
    <div class="manual-update"><label class="upd-txt">Want to update reports manually ?</label><input style="margin-left: 25px;" class="button-primary" type="submit" value="Update Now" id="submit" name="submit" onclick="amwscp_doUpdateFeedResults()">
        <div id="update-message">&nbsp;</div>
    </div>

    <div class="postbox report">
    <h3 class="hndle">
    	<span>Submitted Feed Report</span>
    </h3>
    <div class="inside">
    	<span>This section allows you to download reports based on title, submitted date, status and type. 
			You can take help of the data from these reports to see progress of your product listing. Please <a href="https://www.exportfeed.com/contact" target="_blank">contact</a> our support if you have trouble regarding any feed report. </span>
    </div>
    </div>
    <form id="amwscpf-feed-report-filter" method="get">
        <?php wp_nonce_field( 'amwscpf-ajax-feed-update-nonce', '_ajax_amwscpf_feed_update_nonce' ); ?>
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <input type="hidden" name="tab" value="feedreports"/>
        <?php $cpf_class->display() ?>
    </form>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('.tablenav').show();
    });
</script>