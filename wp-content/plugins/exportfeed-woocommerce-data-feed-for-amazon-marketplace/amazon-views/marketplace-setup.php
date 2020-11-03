<style type="text/css">
    .amazon-setup {
        background-color: #fff;
        padding: 15px 0;
    }

    table tr th {
        text-align: left;
        width: 135px;
        font-size:15px;
    }

    tr td input, tr td select {
        width: 300px;
        height: 50px !important;
        font-size: 17px !important;
    }
    tr td span{
       font-size:14px;
        color:#23282d;
    }
    .message_box_for_marketplace {
        height: 81px;
        border: solid 1px yellow;
        margin-top: 20px;
        background-color : rgb(255, 252, 227);
    }

    .message_box_for_marketplace p {
        padding: 0 0 0 0.5em;
    }
    #amwscpf_btn_signin{
        width: 306px;
        text-align: center;
        font-size: 22px;
        font-weight: bold;
    }
</style>
<div style="display: none;" id="ajax-loader-cat-import"><span id="gif-message-span"></span></div>
<div style="" id="skip-popup-modal" class="modal postbox">
            <div class="skip-modal-content">
                <span class="skip-close">×</span>
                <label id="skip-label">You need to select marketplace you want to sell on, for creating feeds .</label>
                <table class="marketplace-table">
                <tr>
                    <th><span><?php echo __('Marketplace *', 'amwscpf'); ?></span></th>
                    <td>
                        <select id="amwscpf-amazon_market_id_withoutAccount" name="amwscpf_amazon_market_id" title="Site"
                                class="required-entry select" onclick="return doFetchAmazonMarket(this.value);" >
                            <option value="">-- <?php echo __('Please select', 'amwscpf'); ?> --</option>
                            <?php foreach ($cpf_marketplace as $key => $market) : ?>
                                <option
                                    value="<?php echo $market['code'] ?>"
                                    data-marketurl="<?php echo $market['url'] ?>"
                                    data-marketcode="<?php echo $market['code'] ?>">
                                    <?php echo $market['title'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" id="amwscpf_amazon_market_code_no_account"/>
                    </td>
                    <td style="padding-left:10px">
                        <span class="marketplace_msg_box">Select the marketplace before proceeding any steps.</span>
                    </td>
                </tr>
                </table>
                <div class="Submit-button">
                    <a href="#" id="submit_after_skip" onclick="return amwscp_AddMarketplaceCodeOption();" class="button button-primary">Submit</a>
                </div>
            </div>
        </div>
<div class="amazon-setup">
        <div class="skip-option">
        <span class="skip-description">You can skip Amazon Connection if you don't have developer access for Amazon marketplace. Check more about registering <a href="https://docs.developer.amazonservices.com/en_US/dev_guide/DG_Registering.html" target ="_blank">Amazon MWS developer account</a>. Amazon requires at least 30 days to approve developer account. In the meantime you can create feed and upload to Amazon Seller Central manually. Skipping connnection and submitting product feed will disable the inventory and order sync functionality untill you connect with Amazon using the MWS developer keys.</span>
                <a href="#" id="amwscpf_btn_skip" onclick="ShowSkipPopup();">Skip and continue</a>
        </div>
    <table  style="width:100%;">
        <tr>
            <th><span><?php echo __('Seller Email *', 'amwscpf'); ?></span></th>
            <td><input type="text" name="amwscpf_account_title" value="" placeholder="Enter your amazon mws seller email"
                       class="text_input"/><span class="acc_title"></span></td>
            <td style="padding-left:10px">
                <span class="title_message_box"> We need this when you sync order.</span>
            </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr>
            <th><span><?php echo __('Marketplace *', 'amwscpf'); ?></span></th>
            <td>
                <select disabled id="amwscpf-amazon_market_id" name="amwscpf_amazon_market_id" title="Site"
                        class=" required-entry select" onclick="return doFetchAmazonMarket(this.value);" >
                    <option value="">-- <?php echo __('Please select', 'amwscpf'); ?> --</option>
                    <?php foreach ($cpf_marketplace as $key => $market) : ?>
                        <option
                            value="<?php echo $market['code'] ?>"
                            data-marketurl="<?php echo $market['url'] ?>"
                            data-marketcode="<?php echo $market['code'] ?>">
                            <?php echo $market['title'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" id="amwscpf_amazon_market_code"/>
            </td>
            <td style="padding-left:10px">
                <span class="marketplace_msg_box">Select the marketplace and then click submit.</span>
            </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr>
            <th></th>
            <td>
                <a href="#" id="amwscpf_btn_signin" data-target="false" class="button button-primary button-hero">Connect
                    to
                    Amazon</a>
            </td>
            <td></td>
        </tr>
    </table>
    
    <div class="message_box_for_marketplace">
        <p>
            <span class="dashicons dashicons-warning"></span>
            Connect with Amazon to get the credentials.
        </p>
        <p>
            <span class="dashicons dashicons-warning"></span>
            <strong>Choose</strong> I want to access my own Amazon seller account with MWS and Click next.
        </p>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(
        function () {
            // amazon site selector during install: submit form on selection
            jQuery('#amwscpf-amazon_market_id').change(function (event, a, b) {
                t = jQuery(this);
                var market_id = event.target.value;
                var market_url = jQuery(this).find('option:selected').attr('data-marketurl');
                var market_code = jQuery(this).find('option:selected').attr('data-marketcode');
                if (market_id) {
                    jQuery('#account_setup_2').show();
                    var sign_in = "https://sellercentral." + market_url + "/gp/mws/registration/register.html?ie=UTF8&*Version*=1&*entries*=0";
                    //cpf_load_market_details( market_id );
                    jQuery("#amwscpf_btn_signin").attr({
                        href: sign_in,
                        target: '_blank'
                    });
                    jQuery('#amwscpf_btn_signin').attr('data-target', 'true');
                    jQuery("#amwscpf_amazon_market_code").val(market_code);
                    jQuery('#setup_marketplace').attr('class', '').addClass('dashicons dashicons-thumbs-up').css('color', 'green');
                    jQuery('span.marketplace_msg_box').html('Ready to connect with Amazon').css('color', 'green');

                    /*jQuery.ajax({
                        url : ajaxurl,
                        type :'post',
                        data : {
                            feedpath:amwscpf_object.cmdTemplatesList,
                            security:amwscpf_object.security,
                            action: amwscpf_object.action,
                            marketplace : market_id
                        },
                        success : function (res){
                            jQuery('#template-lists').html(res);
                        }
                    });*/
                }
            });

            jQuery('#amwscpf-amazon_market_id_withoutAccount').change(function (event, a, b) {
                t = jQuery(this);
                    var market_code = jQuery(this).find('option:selected').attr('data-marketcode');
                    jQuery("#amwscpf_amazon_market_code_no_account").val(market_code);
                    jQuery('#setup_marketplace').attr('class', '').addClass('dashicons dashicons-thumbs-up').css('color', 'green');
                    jQuery('span.marketplace_msg_box').html('Click Submit to save marketplace').css('color', 'green');
                    return;
            });

            jQuery('#amwscpf_btn_signin').click(function (e) {
                var text = jQuery('input[name=amwscpf_account_title]').val();
                var targetted = jQuery('#amwscpf_btn_signin').attr('data-target');

                if (targetted == 'false') {
                    jQuery('span.marketplace_msg_box').html('You need to select the Marketplace first.').css('color', 'red');
                    jQuery('span.title_message_box').html('Need a title for account.').css('color', 'red');
                }

                if (targetted == 'true'){
                    autotrigger[amwscpf_object.step].click();
                    amwscpf_object.step = 1;
                    autotrigger[amwscpf_object.step].click();
                }

            });

            jQuery('input[name=amwscpf_account_title]').on('keyup', function () {
                var text = jQuery(this).val();
                if (text.length > 0) {
                    jQuery('span.title_message_box').html('Seller Email added.').css('color', 'green');
                    jQuery('#amwscpf-amazon_market_id').removeAttr('disabled');

                }
                if (text.length < 1) {
                    jQuery('#amwscpf-amazon_market_id').attr('disabled', 'diabled');
                    jQuery('span.title_message_box').html('Seller Email is needed.').css('color', 'red');
                }
            });
        }
    );

</script>