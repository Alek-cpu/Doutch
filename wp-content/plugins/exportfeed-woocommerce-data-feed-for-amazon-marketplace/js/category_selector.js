jQuery(document).ready(function(){
    function download_report(id,type){
        jQuery.ajax({
            url     : ajaxurl,
            data    : {
                action      : 'amazon_seller_ajax_handle',
                security    : amwscpf_i18n.nonce_check,
                feedpath    : amwscpf_i18n.cmdSubmissionFeedResult,
            },
            type    : 'post',
            success : function (res) {
                jQuery('div.update-nag').append(res);
            }
        });
    }
});