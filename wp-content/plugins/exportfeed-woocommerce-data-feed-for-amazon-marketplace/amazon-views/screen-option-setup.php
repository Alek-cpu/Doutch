<?php
define('IMAGE_PATH', plugins_url('/', __FILE__) . '');
?>
<div class="wrap">
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-3" class="postbox-container">
                <div class="container" style="margin:0">
                    <!-- <h1 class="heading-primary">Welcome To Amazon Feed Setup</h1> -->
                    <div class="amazon-market-place-select-container">
                        <div id="amazon-marketplace-select" class="amazon-marketplace-common amazon-marketplace_select"
                             style="">
                            <div class="">
                                <h1>Amazon Account Setup</h1>
                                <div class="table-block">
                                    <table class="select-marketplace-tbl">
                                        <tbody>
                                        <tr>
                                            <th><span>
                                                        <?php echo __('Select Marketplace *', 'amwscpf'); ?></span></th>
                                            <td>
                                                <select id="amwscpf-amazon_market_id_withoutAccount"
                                                        onchange="doFetchAmazonMarket(this.value);"
                                                        name="amwscpf_amazon_market_id"
                                                        title="Site" class="required-entry select">
                                                    <option value="null">Select Merchant</option>
                                                    <option value="US">US</option>
                                                    <option value="CA">Canada</option>
                                                    <option value="MX">Mexico</option>
                                                    <option value="UK">UK</option>
                                                    <option value="FR">France</option>
                                                    <option value="ES">Spain</option>
                                                    <option value="DE">Germany</option>
                                                    <option value="IT">Italy</option>
                                                    <option value="AU">Australia</option>
                                                    <option value="IN">India</option>
                                                    <?php //endforeach; ?>
                                                </select>
                                                <input type="hidden" id="amwscpf_amazon_market_code_no_account"/>
                                                <input type="hidden" id="amwscp_hidden_marketplace_id">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <div class="select-us-marketplace" style="display:none;">
                                        <!-- <p style="font-size: 16px;margin:0px;"> Developer ID: <span
                                                    id="copyDeveloperID">349543377271</span>
                                            <input type="hidden" value="3495-4337-7271"
                                                   id="amazon_seller_central_developer_id">
                                            <span class="small cpf_small_link copy_developer_id">
                                                <a id="copyDeveloperID" name="copy_pre"><span style="cursor: pointer;">
                                                        Copy Developer ID
                                                    </span></a>
                                            </span>
                                        </p> -->
                                        <p>Go to your Amazon seller account and fill the required fields below.</p>
                                        <table class="amazon-details">
                                            <tbody>
                                            <tr>
                                                <th>
                                                    <?php echo __('Account Title *', 'amwscpf'); ?>
                                                </th>
                                                <td>
                                                    <input type="text" name="amwscpf_title" id="amwscpf_title"
                                                           value="" class="text_input" placeholder="Enter your email"/>

                                                    <span class="acc_title"></span>
                                                </td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <th>
                                                    <?php echo __('Seller ID *', 'amwscpf'); ?>
                                                </th>
                                                <td>
                                                    <input type="text" name="amwscpf_merchant_id"
                                                           id="amwscpf_merchant_id"
                                                           value="" class="text_input"
                                                           placeholder="Enter your seller id"/>

                                                    <span class="acc_merchant"></span>
                                                </td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <th>
                                                    <?php echo __('MWS Auth Token *', 'amwscpf'); ?>
                                                </th>
                                                <td>
                                                    <input type="text" name="amwscpf_auth_id" id="amwscpf_auth_id"
                                                           value="" class="text_input"
                                                           placeholder="Enter your MWS auth token"/>
                                                    <span class="acc_auth_id"></span>
                                                </td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>&nbsp;</td>
                                            <tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="select-canada-marketplace" style="display:none;">
                                        <p style="font-size: 16px;margin-top: 15px;">
                                        </p>
                                    </div>
                                    <div class="select-eu-marketplace" style="display:none;">
                                        <p style="font-size: 16px;margin-top: 15px;">
                                            </span>
                                        </p>
                                    </div>
                                    <div class="userguide-small-button next-button text-right clearfix">
                                        <div class="cpf-userguide-nextprev" style="display:none">
                                            <p style="font-size: 18px;" id="error_msg_div"></p>
                                            <a class="amc-contact-link button button-primary button-hero"
                                               href="#connect_amazon"
                                               data-toggle="tab" onclick="SubmitMarketplace();">Save</a>
                                        </div>
                                    </div>
                                    <a id="submit_after_skip" onclick="return amwscp_AddMarketplaceCodeOption();" class="skip-btn-KTG button button-primary button-hero" style="display: none;"
                                       href="#connect_amazon"
                                       data-toggle="tab" onclick="javascript:void(0);">Save</a>
                                </div>
                                <div id="welcome_slider_modal" class="welcome_slider_wrapper fade" role="dialog"
                                     style="display:none;">
                                    <div id="myCarousel" class="carousel slide" data-ride="carousel">
                                        <div class="carousel-inner">
                                            <div class="item">
                                                <div id="message_box_for_default_account"
                                                     class="message-box-amazon-setup">
                                                    <h2>Why do you need to select a specific marketplace?</h2>
                                                    <ul>
                                                        <li>While sending your product information to Amazon, the
                                                            Marketplace ID needs
                                                            to be included.
                                                        </li>
                                                        <li> The product categories and product listing requirements
                                                            are different for
                                                            different marketplaces.
                                                        </li>
                                                    </ul>
                                                    <h2>Which marketplace should you select?</h2>
                                                    <ul>
                                                        <li>
                                                            You need to select the marketplace on which you have your
                                                            professional
                                                            seller account.
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="item">
                                                <div id="message_box_for_default_account"
                                                     class="message-box-amazon-setup page2">
                                                    <p> We’re still working on <span id=merchant_name>Australia</span> Connection for direct products upload and sync features.
                                                     Please click Save button to  create your product feed for <span id=merchant_name>Australia</span> and upload the product feed to your Seller Central.
                                                     We’ll notify you once we release the Direct Upload feature for <span id=merchant_name>Australia</span>
                                                    </p>    
                                                </div>
                                            </div>
                                            <div class="item active">
                                                <div id="message_box_for_default_account"
                                                     class="message-box-amazon-setup page3">
                                                    <h2>Why authorize?</h2>
                                                    <p>You need to authorize ExportFeed developer account to access
                                                        your Amazon MWS selling account in order to enable products
                                                        upload and sync features.</p>

                                                    <h2>How to authorize?</h2>
                                                    <ul>
                                                        <li>Go to the <a target="_blank"
                                                                         href="https://sellercentral.amazon.com.mx/gp/mws/registration/register.html?signInPageDisplayed=1&devAuth=1"
                                                                         class="user_permission_page"> User Permissions
                                                                page </a>
                                                            in seller central account
                                                        </li>
                                                        <li>Click on <b>Authorise a developer</b> button.</li>
                                                        <li>Enter <b>ExportFeed</b> in the Developer’s Name text box.</li>
                                                        <li class="developerID">Enter <div class="tooltip" id="copyDeveloperID" onclick="copy(this);">349543377271</div><span class="tooltiptext">Click to copy</span> in the Developer’s ID box.</li>
                                                        <li>Now, click Next to authorize ExportFeed and to copy your
                                                            Seller Id and Auth Token.
                                                        </li>
                                                    </ul>
                                                    <input type="hidden" value="0" id="user_permission_clicked">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function () {
            var d = document,
                accordionToggles = d.querySelectorAll('.js-accordionTrigger'),
                setAria,
                setAccordionAria,
                switchAccordion,
                touchSupported = ('ontouchstart' in window),
                pointerSupported = ('pointerdown' in window);
            autotrigger = accordionToggles;
            skipClickDelay = function (e) {
                e.preventDefault();
                e.target.click();
            };
            setAriaAttr = function (el, ariaType, newProperty) {
                el.setAttribute(ariaType, newProperty);
            };
        })();
    </script>