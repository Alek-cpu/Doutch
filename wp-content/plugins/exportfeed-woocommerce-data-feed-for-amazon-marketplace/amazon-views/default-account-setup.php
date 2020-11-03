<?php
global $amwcore;
?>
<style type="text/css">
    table#default-account{
        display: inline-block;
        vertical-align: top;
    }
    .loadingoverloay{
        background: #fff;
        position: absolute;
        opacity: 0.45;
        display: none;
        right:0;
        top:0;
        left : 0;
        bottom:0;
        margin:auto;
    }
</style>
<div class="amazon-setup ">
    <div class="loadingoverloay" style="">
        <?php $amwcore->amwscpf_loader_big('account',['position'=>'absolute','left'=>'0','top'=>'0','bottom'=>'0','right'=>'0','margin'=>'auto']); ?>
    </div>
    <table id="default-account">
        <tr>
            <th>
                <input type="hidden" name="is_help" value="1" />
                <?php echo __('Seller ID *', 'amwscpf'); ?>
                <span class="acc_merchant"></span>
            </th>
            <td>
                <input type="text" name="amwscpf_merchant_id" id="amwscpf_merchant_id" value="" class="text_input"/>
            </td>
            <td></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr>
            <th>
                <?php echo __('Marketplace ID *', 'amwscpf'); ?>
                <span class="acc_marketplace_id"></span>
            </th>
            <td>
                <input type="text" name="amwscpf_marketplace_id" id="amwscpf_marketplace_id" value=""
                       class="text_input"/>
            </td>
            <td></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr>
            <th>
                <?php echo __('AWS Access Key ID *', 'amwscpf'); ?>
                <span class="acc_aws_key"></span>
            </th>
            <td>
                <input type="text" name="amwscpf_access_key_id" id="amwscpf_access_key_id" value="" class="text_input"/>
            </td>
            <td></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr>
            <th>
                <?php echo __('Secret Key *', 'amwscpf'); ?>
                <span class="acc_sectret_key"></span>
            </th>
            <td>
                <input type="text" name="amwscpf_secret_key" id="amwscpf_secret_key" value="" class="text_input"/>
            </td>
            <td></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr>
            <th></th>
            <td>
                <a href="#" id="amwscpf_btn_add_account" onclick="amwscp_add_amazon_account();" class="button button-primary button-hero"
                   style="float:left;">Save</a>
            </td>
            <td>
            </td>
        </tr>
    </table>
    <div id="message_box_for_default_account">
        <ul>
            <li>Name the title for account. eg. <i>amazon account</i></li>
            <li>Next, choose your marketplace from dropdown. eg. <i>United States</i></li>
            <li>In order to complete the form to the left, you need to login to your amazon marketplace. So, before
                filling any fields from left form, click on <i>Connect to Amazon</i> button
            </li>
            <li>Login with your Amazon credentials and <strong>choose</strong><i> I want to access my own Amazon seller
                    account with MWS.</i> Now you have all the infos needed to fill the rest of fields in the form.
            </li>
            <li>Fill all fields in the form and click on <i>Save </i>button</li>
        </ul>
    </div>
    <div class="clear"></div>
</div>