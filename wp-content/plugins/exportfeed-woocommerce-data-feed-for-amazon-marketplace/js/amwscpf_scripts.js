var ajaxhost = "";
var category_lookup_timer;

var tmpl_id;
var current_model_label = 0;
var current_label = 0;
var total_div = 0;


var feedIdentifier = 0; //A value we create and infoamwscp_doUpdateAllFeedsrm the server of that allows us to track errors during feed generation
var feed_id = 0; //A value the server gives us if we're in a feed that exists already. Will be needed when we want to set overrides specific to this feed
var feedFetchTimer = null;
var localCategories = {
    children: []
};

function amwscp_parseFetchCategoryResult(res) {
    document.getElementById("categoryList").innerHTML = res;
    if (res.length > 0) {
        document.getElementById("categoryList").style.border = "1px solid #A5ACB2";
        document.getElementById("categoryList").style.display = "inline";
    } else {
        document.getElementById("categoryList").style.border = "0px";
        document.getElementById("categoryList").style.display = "none";
        document.getElementById("remote_category").value = "";
    }
}

function amwscpf_parseFetchLocalCategories(res) {
    localCategories = jQuery.parseJSON(res);
}

function amwscp_parseGetFeedResults(res) {
    //Stop the intermediate status interval
    window.clearInterval(feedFetchTimer);
    feedFetchTimer = null;
    jQuery('#feed-status-display').html("");
    results = jQuery.parseJSON(res);
    //Show results
    if (results.url.length > 0) {
        jQuery('#feed-error-display').html('');
        window.open(results.url);
    }
    // if (results.errors.length > 0)
    //     jQuery('#feed-error-display').html(results.errors);
    if (results.errors.length > 0) {
        var errormsg = results.errors.replace(/<(.|\n)*?>/g, '');
        errormsg = errormsg.split(':');
        var type = errormsg[0].toLowerCase();
        var strtocompare = 'warning';
        if (type.localeCompare(strtocompare) == 0) {
            jQuery('#feed-status-display').html("Feed is generated. Goto <a href='" + results.submit + "'>manage feeds</a> to upload your feeds to amazon.");
            jQuery('#feed-error-display').css('color', '#FF9900');
            jQuery('#feed-error-display').html(results.errors);
        } else {
            jQuery('#feed-error-display').css('color', 'red');
            jQuery('#feed-error-display').html(results.errors);
        }
    } else {
        jQuery('#feed-status-display').css('color', 'blue');
        jQuery('#feed-status-display').html('Feed Generated Successfully! Goto <a href=' + results.submit + '>manage feeds</a> to upload your feeds to amazon.');
    }
}

function amwscp_parseUploadFeedResults(res, provider) {

    //Stop the intermediate status interval
    window.clearInterval(feedFetchTimer);
    feedFetchTimer = null;
    jQuery('#feed-error-display2').html("");
    jQuery('#feed-status-display2').html("Uploading feed...");

    var results = jQuery.parseJSON(res);

    //Show results
    if (results.url.length > 0) {
        jQuery('#feed-error-display2').html("&nbsp;");
        //window.open(results.url);
        var data = {
            feedpath: amwscpf_object.cmdUploadFeed,
            security: amwscpf_object.security,
            action: amwscpf.action,
            content: results.url,
            provider: provider
        };
        jQuery('.remember-field').each(function () {
            data[this.name] = this.value;
        });

        /** DO INVENTORY UPLOAD HERE **/
        if (provider == 'amazonsc') {
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: data,
                success: function (result) {
                    // console.log('success');
                    // console.log(result);
                    amwscp_parseUploadFeedResultStatus(data, result);
                },
                error: function (result) {
                    console.log('error');
                    console.log(result);
                }
            });
        } else if (provider == 'ebayupload') {
            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                data: data,
                success: function (result) {
                    console.log('success');
                    console.log(result);
                    amwscp_parseUploadFeedResultStatus(data, result);
                },
                error: function (result) {
                    console.log('error');
                    console.log(result);
                }
            });
        } else console.log(provider);
    }
    if (results.errors.length > 0) {
        jQuery('#feed-error-display2').html(results.errors);
        jQuery('#feed-status-display2').html("");
    }
}

function amwscp_parseUploadFeedResultStatus(data, id) {

    if (data.provider == 'amazonsc') {
        if (isNaN(result)) {
            var errors = JSON.parse(result);
            jQuery('#feed-status-display2').html("");
            jQuery('#feed-error-display2').html("ERROR: " + errors['Caught Exception']);
        } else {
            data['feedid'] = result;
            data['feedpath'] = amwscpf_object.cmdUploadFeedStatus;
            data['action'] = amwscpf_object.action;
            data['security'] = amwscpf_object.security;
            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                data: data,
                success: function (result) {
                    console.log('success');
                    console.log(result);
                    jQuery('#feed-status-display2').html(result);
                },
                error: function (result) {
                    console.log('error');
                    console.log(result);
                }
            });
        }
    }
}

function amwscp_parseGetFeedStatus(res) {
    if (feedFetchTimer != null) {
        jQuery('#gif-message-span').html(res);
        // jQuery('#feed-status-display').html(res);
    }
    // else{
    // 	jQuery('#feed-status-display').css('color','blue');
    // 	jQuery('#feed-status-display').html('');
    // }
}

function amwscp_parseUploadFeedStatus(res) {
    if (feedFetchTimer != null)
        jQuery('#feed-status-display2').html(res);
}

function amwscp_parseLicenseKeyChange(res) {
    jQuery("#tblLicenseKey").remove();
}

function amwscp_parseSelectFeedChange(res) {
    jQuery('#feedPageBody').html(res);
    amwscp_doFetchLocalCategories();
}

function amwscp_parseUpdateSetting(res) {
    jQuery('#updateSettingMessage').html(res);
}

function amwscp_doEraseMappings(service_name) {
    var r = confirm("This will clear your current Attribute Mappings including saved Maps from previous attributes. Proceed?");
    if (r == true) {
        jQuery.ajax({
            type: "post",
            url: ajaxurl,
            data: {
                service_name: service_name,
                feedpath: amwscpf_object.cmdMappingsErase,
                security: amwscpf_object.security,
                action: amwscpf_object.action,
            },
            success: function (res) {
                amwscp_showEraseConfirmation(res)
            }
        });
        //window.location.reload();
    }
}

function amwscp_doFetchCategory(service_name, partial_data) {
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            service_name: service_name,
            partial_data: partial_data,
            shop_id: shopID,
            feedpath: amwscpf_object.cmdFetchCategory,
            action: amwscpf_object.action,
            security: amwscpf_object.security
        },
        success: function (res) {
            amwscp_parseFetchCategoryResult(res)
        }
    });
}

function amwscp_doFetchCategory_timed(service_name, partial_data) {
    if (!category_lookup_timer) {
        window.clearTimeout(category_lookup_timer);
    }

    category_lookup_timer = setTimeout(function () {
        amwscp_doFetchCategory(service_name, partial_data)
    }, 100);
}

function amwscp_doFetchLocalCategories() {
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            shop_id: shopID,
            feedpath: amwscpf_object.cmdFetchLocalCategories,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        },
        success: function (res) {
            amwscpf_parseFetchLocalCategories(res)
        }
    });
}

function amwscp_doUploadFeed(provider, service, userid) {

    jQuery('#feed-error-display2').html("Uploading feed...");
    var thisDate = new Date();
    feedIdentifier = thisDate.getTime();

    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";

    var data = {
        userid: userid,
        remember: jQuery("#remember").is(":checked"),
        provider: service,
        feedpath: amwscpf_object.cmdRemember,
        security: amwscpf_object.security,
        action: amwscpf_object.action
    };

    jQuery('.remember-field').each(function () {
        data[this.name] = this.value;
    });

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: data,
        success: function () {

        }
    });

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            provider: provider,
            local_category: jQuery('#local_category').val(),
            remote_category: jQuery('#remote_category').val(),
            file_name: jQuery('#feed_filename').val(),
            feed_identifier: feedIdentifier,
            feed_id: feed_id,
            shop_id: shopID,
            feedpath: amwscpf_object.cmdGetFeed,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        },
        success: function (res) {
            amwscp_parseUploadFeedResults(res, provider)
        }
    });
    feedFetchTimer = window.setInterval(function () {
        updateUploadFeedStatus()
    }, 500);
}

function amwscp_doGetFeed(provider) {
    jQuery('#ajax-loader-cat-import').show();
    jQuery('#feed-status-display').html('');
    jQuery('#feed-error-display').html("Generating feed...");
    let shopID = jQuery("#edtRapidCartShop").val(),
        thisDate = new Date(),
        feedIdentifier = thisDate.getTime(),
        feed_product_type = jQuery('#feed_product_type').val(),
        recommended_browse_nodes = jQuery('#recommended_browse_node').val(),
        local_category = jQuery('#local_category').val(),
        remote_category = jQuery('#remote_category').val(),
        file_name = jQuery('#feed_filename').val(),
        item_type_keyword = jQuery('#item_type_keyword').val(),
        amazon_category = jQuery('#feed-type-value-input').val(),
        selectedMArket = jQuery("#categoryDisplayTextbyC").val(),
        variationTheme = jQuery('#variation-theme-select').val();

    // alert(amazon_category);return;
    if (provider == 'Amazonsc') {
        if (amwscpf_object.disabled) {
            remote_category = 'listingloader';
        }
        if (!(local_category.length > 0)) {
            jQuery('#ajax-loader-cat-import').hide();
            jQuery('#feed-error-display').css('color', '#FF9900');
            jQuery('#feed-error-display').html('Local Category must be defined.');
            amwscp_showLocalCategories('Amazonsc', true);
            return;
        }

        if (!(remote_category.length > 0)) {
            jQuery('#ajax-loader-cat-import').hide();
            jQuery('#feed-error-display').css('color', '#FF9900');
            jQuery('#feed-error-display').html('Template must be selected.');
            return;
        }

        if (!(file_name.length > 0)) {
            jQuery('#ajax-loader-cat-import').hide();

            jQuery('#feed-error-display').html('Specify the filename.');
            return;
        }

        if (isNaN(recommended_browse_nodes)) {
            jQuery('#ajax-loader-cat-import').hide();
            jQuery('#feed-error-display').html('Recommende Browse Node should be Integer i.e. from Numberphile.');
            return;
        }

    }

    if (shopID == null)
        shopID = "";
    // console.log(provider+','+local_category +','+ remote_category +','+ file_name +','+ feedIdentifier +','+ feed_id +','+ feed_product_type +','+ recommended_browse_nodes+','+item_type_keyword);
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            provider: provider,
            local_category: local_category,
            remote_category: remote_category,
            amazon_category: amazon_category,
            selectedMArket: selectedMArket,
            file_name: file_name,
            feed_identifier: feedIdentifier,
            feed_id: feed_id,
            shop_id: shopID,
            feed_product_type: feed_product_type,
            recommended_browse_nodes: recommended_browse_nodes,
            item_type_keyword: item_type_keyword,
            feedpath: amwscpf_object.cmdGetFeed,
            security: amwscpf_object.security,
            action: amwscpf_object.action,
            variationTheme: variationTheme
        },
        success: function (res) {
            jQuery('#ajax-loader-cat-import').hide();
            amwscp_parseGetFeedResults(res)
        },
        error: function (res) {
            jQuery('#ajax-loader-cat-import').hide();
            if (res.status == 500) {
                jQuery('#feed-error-display').html('');
                jQuery('#feed-error-display').css("color", "red");
                jQuery('#feed-error-display').html("Server Error Occured. Please Contact Our <a target='_blank' href='https://www.expertfeed.com/contact'>support</a>");
            }
            console.log(res);
        }
    });
    feedFetchTimer = window.setInterval(function () {
        amwscp_updateGetFeedStatus()
    }, 500);
}

function amwscp_doGetAlternateFeed(provider) {

    jQuery('#feed-error-display').html("Generating feed...");
    var thisDate = new Date();
    feedIdentifier = thisDate.getTime();

    var feeds = new Array;
    jQuery(".feedSetting:checked").each(function () {
        feeds.push(jQuery(this).val());
    });

    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";

    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            provider: provider,
            local_category: "0",
            remote_category: "0",
            file_name: jQuery('#feed_filename').val(),
            feed_identifier: feedIdentifier,
            feed_id: feed_id,
            shop_id: shopID,
            feed_ids: feeds,
            feedpath: amwscpf_object.cmdGetFeed,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        },
        success: function (res) {
            amwscp_parseGetFeedResults(res)
        }
    });
    feedFetchTimer = window.setInterval(function () {
        amwscp_updateGetFeedStatus()
    }, 500);
}

function amwscp_doSelectCategory(service_name, tpl, tmpl_id = null, country_code = null) {
    let essentials = {country: country_code, template_id: tmpl_id, tpl: tpl};
    sessionStorage.setItem('amazon-templates-essentials', JSON.stringify(essentials));
    // if(tpl.length<=0){
    // 	alert("Please select one category"); return;
    // }
    jQuery('#amazon_product_category_' + tmpl_id).parent().find(".selected").removeClass("selected").css({
        'background-color': 'white',
        'color': '#444'
    });
    jQuery('#amazon_product_category_' + tmpl_id).addClass("selected");
    jQuery('.selected').css({
        'background-color': '#0073aa',
        'color': 'white'
    });
    jQuery('#selected-category-text').html('');
    jQuery('#selected-category-text').html(jQuery('.selected li').html());
    jQuery('#final-category').parent().parent().html('');
    jQuery('#feed_product_type').parent().parent().hide();
    jQuery('#feed_product_type').html('');

    jQuery('#amazon_product_category_' + tmpl_id).parent().parent().nextAll().html('');
    jQuery('#amazon_product_category_' + tmpl_id).parent().parent().nextAll().removeAttr('style');

    var error = jQuery('#feed-error-display').html();

    if (error.length > 5) {
        jQuery('#feed-error-display').html('');
    }
    if (service_name == 'Productlistraw') {
        tpl = '';
    }
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    if (country_code !== null) {
        var template = tpl + '_' + country_code;
    } else {
        var template = tpl;
    }

    jQuery('#remote_category').val(template);
    // var template = tpl+'_'+country_code;
    //The user has just selected a template.
    //Therefore, we must reload the Optional / Required Mappings
    jQuery('#ajax-loader-cat-import').show();
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        dataType: 'json',
        data: {
            shop_id: shopID,
            template: template,
            provider: service_name,
            feedpath: amwscpf_object.cmdFetchTemplateDetails,
            security: amwscpf_object.security,
            action: amwscpf_object.action,
            fetch_feed_product_type:true
        },
        success: function (res) {
            jQuery("#attribute-mapping-div").show();
            jQuery("#attributeMappings").html('');
            jQuery("#attributeMappings").html(res.mappings);
            if (res) {
                if (tpl.length > 0 && service_name == 'Amazonsc') {
                    remote_template_id = res.template_id;
                    amwscp_doGetFeedProductType(template, tmpl_id, country_code, remote_template_id);
                } else {
                    jQuery('#ajax-loader-cat-import').hide();
                }
            }
        }
    });
}

function amwscp_doGetFeedProductType(tpl_id, tmpl_id, country_code, remote_template_id) {
    tmpl_id = tmpl_id;
    // jQuery('#ajax-loader-cat-import').show();
    jQuery.ajax({
        url: ajaxurl,
        type: "post",
        dataType: "json",
        data: {
            tpl_id: tpl_id,
            tmpl_id: tmpl_id,
            country_code: country_code,
            feedpath: amwscpf_object.cmdGetFeedProductType,
            security: amwscpf_object.security,
            action: amwscpf_object.action,
            remote_template_id: remote_template_id,
            feed_product_type: amwscpf_object.feed_product_type,
            fetch_feed_product_type:true
        },
        success: function (res) {
            jQuery('#ajax-loader-cat-import').hide();
            if (res.status == 'success') {
                // console.log(res.html);return;
                if (res.hide == false) {
                    jQuery('span#amwscp_feed_list').html('');
                    jQuery('#feed_product_type_box').show();
                    jQuery('.input-boxes').show();
                    jQuery('span#amwscp_feed_list').html(res.html);
                } else {
                    jQuery('span#amwscp_feed_list').html('');
                    jQuery('#feed_product_type_box').hide();
                    jQuery('span#amwscp_feed_list').html(res.html);

                }
                jQuery('#select-feed-type').show();

            } else {
                jQuery('#feed_product_type_box').hide();
                jQuery('#select-feed-type').show();
            }
            // console.log(res);
            if (res.nodata == true) {
                jQuery('#div-1').css({
                    'width': '580px',
                    'display': 'inline-block',
                    'overflow-y': 'scroll',
                    'height': '310px'
                });
                jQuery('#div-1').html(res.feed_type_data_html)
            } else {
                jQuery('#div-1').css({
                    'width': '290px',
                    'display': 'inline-block',
                    'overflow-y': 'scroll',
                    'height': '310px'
                });
                jQuery('#div-1').html(res.feed_type_data_html);
            }
        },
        error: function (res) {
            console.log("ERROR:");
            console.log(res);
            jQuery('ajax-loader-cat-import').hide();
        }
    });
}

function browseSubCat(id, level, node) {
    if (current_model_label - level == 1) {
        var showlevel = level - 2;
        jQuery('#div-' + showlevel).removeAttr('style');
        jQuery('#div-' + showlevel).css({
            'width': '290px',
            'display': 'inline-block',
            'overflow-y': 'scroll',
            'height': '310px'
        });
    }
    current_model_label = level;
    jQuery('#ajax-loader-cat-import').show();
    jQuery('#' + node).parent().parent().nextAll().remove();

    jQuery('#' + node).parent().find(".selected").removeClass("selected").css({
        'background-color': 'white',
        'color': 'black'
    });
    jQuery('#' + node).addClass("selected");
    jQuery('.selected').css({
        'background-color': '#0073aa',
        'color': 'white'
    });

    jQuery('#final-category').parent().parent().html('');

    var new_text_string = '';
    jQuery('.category-main-div').find('.selected').each(function () {

        var $new_text = jQuery('#' + this.id).text();

        if ($new_text.length > 0) {
            if (new_text_string.length > 0) {
                new_text_string = jQuery.trim(new_text_string);
                new_text_string = new_text_string + ' > ' + $new_text;
            } else {
                new_text_string = $new_text;
            }

        }
    });


    jQuery('#selected-category-text').html('');
    jQuery('#selected-category-text').html(new_text_string);

    var initlevel = level;
    level = parseInt(level) + 1;
    id = parseInt(id);
    hide_label = parseInt(level) - 4;
    if (parseInt(level) > 3) {
        jQuery('#div-' + hide_label).css({
            'margin-left': '200px',
            'display': 'none'
        });
    }
    jQuery.ajax({
        url: ajaxurl,
        type: "post",
        dataType: "json",
        data: {
            tpl_id: '',
            feedpath: amwscpf_object.cmdGetFeedProductType,
            security: amwscpf_object.security,
            action: amwscpf_object.action,
            feed_product_type: amwscpf_object.feed_product_type,
            type: 'servies',
            level: level,
            id: id,
            node: node,
            tmpl_id: tmpl_id,
            fetch_product_type:false
        },
        success: function (res) {
            jQuery('#ajax-loader-cat-import').hide();
            if (res.status == 'success') {
                if (res.html !== 'empty') {
                    jQuery('span#amwscp_feed_list').html(res.html);
                }
            } else {
                jQuery('#select-feed-type').show();
            }

            var identifier = jQuery('#div-' + initlevel);
            if (typeof (identifier[0]) != "undefined" && identifier.selector == "#div-" + initlevel) {

                if (res.final == true) {
                    jQuery(identifier.selector).css({
                        'width': '290px',
                        'display': 'inline-block',
                        'overflow-y': 'scroll',
                        'height': '310px'
                    });
                    jQuery(identifier.selector).html(res.feed_type_data_html);
                    last_node = res.node_id;
                    $innerhtml = jQuery('#' + last_node).children().html();

                    var parser = new DOMParser;
                    var dom = parser.parseFromString(
                        '<!doctype html><body>' + $innerhtml,
                        'text/html');
                    var decodedString = dom.body.textContent;
                    jQuery('#final-category').html(decodedString);
                    jQuery('#final-category').parent('ul').css({
                        'position': 'relative',
                        'text-align': 'center',
                        'top': '40%'
                    });
                    jQuery('#final-category').siblings();

                } else {
                    jQuery(identifier.selector).css({
                        'width': '290px',
                        'display': 'inline-block',
                        'overflow-y': 'scroll',
                        'height': '310px'
                    });
                    jQuery(identifier.selector).html(res.feed_type_data_html);
                }
            } else {
                if (res.final == true) {
                    jQuery('.category-main-div').append('<div id="div-' + initlevel + '">' + res.feed_type_data_html + '</div>');
                    jQuery('#div-' + initlevel).css({
                        'width': '290px',
                        'display': 'inline-block',
                        'overflow-y': 'scroll',
                        'height': '310px'
                    });
                    last_node = res.node_id;
                    $innerhtml = jQuery('#' + last_node).children().html();
                    jQuery('#final-category').html($innerhtml);
                    // jQuery('#final-category').parent('ul').css({'margin-top': '14px','margin-left': '136px'});
                    jQuery('#final-category').parent().css({
                        'text-align': 'center',
                        'position': 'relative',
                        'top': '40%'
                    });
                } else {
                    jQuery('.category-main-div').append('<div id="div-' + initlevel + '">' + res.feed_type_data_html + '</div>');
                    jQuery('#div-' + initlevel).css({
                        'width': '290px',
                        'display': 'inline-block',
                        'overflow-y': 'scroll',
                        'height': '310px'
                    });
                }
            }

        }
    });
}

function amwscp_doSelectLocalCategory(id, count) {

    if (count <= 0) {
        alert("You have selected your shop's category which does not have any products. Please select a category which has some products to send to the merchant.");
        jQuery('#cbLocalCategory' + id).prop('checked', false);
        return false;
        // location.reload();return;
    } else {
        //Build a list of checked boxes
        var category_string = "";
        var category_ids = "";
        jQuery(".cbLocalCategory").each(
            function (index) {
                tc = document.getElementById(jQuery(this).attr('id'));
                if (tc.checked) {
                    //if (jQuery(this).attr('checked') == 'checked') {
                    category_string += jQuery(this).val() + ", ";
                    category_ids += jQuery(this).attr('category') + ",";
                }
            }
        );

        //Trim the trailing commas
        category_ids = category_ids.substring(0, category_ids.length - 1);
        category_string = category_string.substring(0, category_string.length - 2);

        //Push the results to the form
        jQuery("#local_category").val(category_ids);
        jQuery("#local_category_display").val(category_string);

    }


}

function assignItemtypeandNode(node) {
    var item_type;
    if (node == 'nodata') {
        var browse_node = jQuery('#manual-recommended-node').val();
        var item_type = jQuery('#manual-item-type').val();

        jQuery('#recommended_browse_node').val(browse_node);
        jQuery('#recommended_browse_node').attr("value", browse_node);
        jQuery('#item_type_keyword').val(item_type);
        jQuery('#item_type_keyword').attr("value", item_type);
        var $value = jQuery('#selected-category-text').text().replace(/ /g, ' ');
        $value = jQuery.trim($value);
        jQuery('#feed-type-value-input').val($value);
    } else {
        item_type = jQuery('#item_type_' + node).val();
        jQuery('#item_type_keyword').val(item_type);
        jQuery('#item_type_keyword').attr("value", item_type);
        jQuery('#recommended_browse_node').val(node);
        jQuery('#recommended_browse_node').attr("value", node);
        $innerhtml = jQuery('#' + last_node).children().html();
        var $value = jQuery('#selected-category-text').text().replace(/ /g, ' ');
        $value = jQuery.trim($value);
        jQuery('#feed-type-value-input').val($value);
        console.log($value);
        jQuery('#feed-type-value-input').attr('value', $value);
    }

    modal = document.getElementById('myModal');
    modal.style.display = "none";
}

function amwscp_doSelectFeed() {
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            feedType: jQuery('#selectFeedType').val(),
            feedpath: amwscpf_object.cmdSelectFeed,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        },
        success: function (res) {
            amwscp_parseSelectFeedChange(res)
        }
    });
}

function amwscp_doUpdateAllFeeds() {
    jQuery('#update-message').html("Updating feeds...");
    //in Joomla, this message is hidden, so unhide
    jQuery('#update-message').css({
        "display": "block"
    });
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            feedpath: amwscpf_object.cmdUpdateAllFeeds,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        },
        success: function (res) {
            jQuery('#update-message').html(res);
            // location.reload();
        }
    });
}

function amwscp_doUpdateSetting(source, settingName) {
    //Note: Value must always come last...
    //and &amp after value will be absorbed into value
    if (jQuery("#cbUniqueOverride").attr('checked') == 'checked')
        unique_setting = '&feedid=' + feed_id;
    else
        unique_setting = '';
    jQuery('#updateSettingMessage').html('Updating Please Wait...');
    var shopID = jQuery("#edtRapidCartShop").val();
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: "feedpath=" + amwscpf_object.cmdUpdateSetting + "&security=" + amwscpf_object.security + "&action=" + amwscpf_object.action + "&setting=" + settingName + unique_setting + "&shop_id=" + shopID + "&value=" + jQuery("#" + source).val(),
        success: function (res) {
            amwscp_parseUpdateSetting(res)
        }
    });
}

function amwscp_getLocalCategoryBranch(branch, gap, chosen_categories) {
    var result = '';
    var result_checked = '';
    var result_unchecked = '';
    var span = '<span style="width: ' + gap + 'px; display: inline-block;">&nbsp;</span>';
    for (var i = 0; i < branch.length; i++) {
        if (branch[i].tally > 0) {
            if (jQuery.inArray(branch[i].id, chosen_categories) > -1) {
                checkedState = ' checked="true"';
                result_checked += '<div>' + span + '<input type="checkbox" class="cbLocalCategory" id="cbLocalCategory' + branch[i].id + '" value="' + branch[i].title +
                    '" onclick="amwscp_doSelectLocalCategory(' + branch[i].id + ',' + branch[i].tally + ')" category="' + branch[i].id + '"' + checkedState + ' />' + branch[i].title + '(' + branch[i].tally + ')</div>';
                result_checked += amwscp_getLocalCategoryBranch(branch[i].children, gap + 20, chosen_categories);
            } else {
                checkedState = '';

                result_unchecked += '<div>' + span + '<input type="checkbox" class="cbLocalCategory" id="cbLocalCategory' + branch[i].id + '" value="' + branch[i].title +
                    '" onclick="amwscp_doSelectLocalCategory(' + branch[i].id + ',' + branch[i].tally + ')" category="' + branch[i].id + '"' + checkedState + ' />' + branch[i].title + '(' + branch[i].tally + ')</div>';
                result_unchecked += amwscp_getLocalCategoryBranch(branch[i].children, gap + 20, chosen_categories);
            }
        }
    }
    result += result_checked + result_unchecked;
    return result;
}

function amwscp_getLocalCategoryList(chosen_categories) {
    return amwscp_getLocalCategoryBranch(localCategories.children, 0, chosen_categories);
}

function amwscp_searsPostByRestAPI() {
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            username: jQuery("#edtUsername").val(),
            password: jQuery("#edtPassword").val(),
            feedpath: amwscpf_object.cmdSearsPostByRestAPI,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        },
        success: function (res) {
            amwscp_searsPostByRestAPIResults(res)
        }
    });
}

function amwscp_searsPostByRestAPIResults(res) {

}

function amwscp_setAttributeOption(service_name, attribute, select_index) {
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            feedpath: amwscpf_object.cmdSetAttributeOption,
            security: amwscpf_object.security,
            action: amwscpf_object.action,
            service_name: service_name,
            attribute: attribute,
            mapto: jQuery('#attribute_select' + select_index).val()
        }
    });
}

function amwscp_setAttributeOptionV2(sender) {
    var service_name = jQuery(sender).attr('service_name');
    var attribute_name = jQuery(sender).val();
    var mapto = jQuery(sender).attr('mapto');
    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            service_name: service_name,
            attribute: attribute_name,
            mapto: mapto,
            shop_id: shopID,
            feedpath: amwscpf_object.cmdSetAttributeUserMap,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        }
    });
}

function amwscp_submitLicenseKey(keyname) {
    var r = alert("License field will disappear if key is successful. Please reload the page.");
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            setting: keyname,
            value: jQuery("#edtLicenseKey").val(),
            feedpath: amwscpf_object.cmdUpdateSetting,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        },
        success: function (res) {
            amwscp_parseLicenseKeyChange(res)
        }
    });
    //window.location.reload(1);
}

function amwscp_showEraseConfirmation(res) {
    //alert("Attribute Mappings Cleared"); //Dropped message and just reloaded instead
    if (document.getElementById("selectFeedType") == null)
        jQuery(".attribute_select").val("");
    else
        amwscp_doSelectFeed();
}

function amwscp_showLocalCategories(provider, showerror = false) {
    var error = jQuery('#feed-error-display').html();
    if (error.length > 5 && showerror == false) {
        jQuery('#feed-error-display').html('');
    }
    chosen_categories = jQuery("#local_category").val();
    chosen_categories = chosen_categories.split(",");
    jQuery.colorbox({
        html: "<div class='categoryListLocalFrame'><div class='categoryListLocal'><h1>Categories</h1>" + amwscp_getLocalCategoryList(chosen_categories) + "</div></div>",
        closeButton: true,
        overlayClose: true
    });
}

function amwscp_toggleAdvancedDialog() {
    toggleButton = document.getElementById("toggleAdvancedSettingsButton");

    if (toggleButton.innerHTML.indexOf("O") > 0) {
        //Open the dialog
        toggleButton.innerHTML = "[ Close Advanced Commands ] ";
        document.getElementById("feed-advanced").style.display = "inline";
    } else {
        //Close the dialog
        toggleButton.innerHTML = "[ Open Advanced Commands ] ";
        document.getElementById("feed-advanced").style.display = "none";
    }
}

function amwscp_toggleOptionalAttributes() {
    toggleButton = document.getElementById("amwscp_toggleOptionalAttributes");

    if (toggleButton.innerHTML.indexOf("h") > 0) {
        //Open the dialog
        toggleButton.innerHTML = "[Show] Additional Attributes";
        document.getElementById("optional-attributes").style.display = "inline-block";

    } else {
        //Close the dialog
        toggleButton.innerHTML = "[Hide] Additional Attributes";
        document.getElementById("optional-attributes").style.display = "none";
    }
} //amwscp_toggleOptionalAttributes

function amwscp_toggleRequiredAttributes() {
    toggleButton = document.getElementById("required-attributes");

    if (toggleButton.style.display == "none") {
        //Open the dialog
        document.getElementById("required-attributes").style.display = "inline-block";
    } else {
        //Close the dialog
        document.getElementById("required-attributes").style.display = "none";
    }
} //amwscp_toggleRequiredAttributes
function amwscp_updateGetFeedStatus() {
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {
            feed_identifier: feedIdentifier,
            feedpath: amwscpf_object.cmdGetFeedStatus,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        },
        success: function (res) {
            amwscp_parseGetFeedStatus(res)
        }
    });
}

function amwscp_selectMarketplace(market) {
    var market_id = jQuery(market).val();
    if (market_id == '')
        return;
    var sign_in = "https://sellercentral.amazon." + market_id + "/gp/mws/registration/register.html?ie=UTF8&*Version*=1&*entries*=0";
    jQuery('#amazon-sign-up').attr('href', sign_in);
    jQuery('.amazon-button').show();
    jQuery('#seller_credential_form').show();

    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            site: market_id,
            feedpath: amwscpf_object.cmdGetCredentials,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        },
        success: function (res) {
            console.log(res);
        }
    });
}

function amwscp_skip(parent) {
    jQuery('#' + parent).hide();
}

function amwscp_add_credentials() {
    var seller_id = jQuery('input[name=cpf_seller_id]').val();
    var marketplace_id = jQuery('input[name=cpf_marketplace_id]').val();
    var aws_key_id = jQuery('input[name=cpf_aws_key_id]').val();
    var secret_key_id = jQuery('input[name=cpf_secret_key_id]').val();
    var site = jQuery('select[name=cpf_country]').val();

    if (seller_id.length < 3) {
        jQuery('input[name=cpf_seller_id]').focus();
        jQuery('.accnt-err-msg').html('Invald Seller Id');
        return;
    }

    if (marketplace_id.length < 3) {
        jQuery('input[name=cpf_marketplace_id]').focus();
        jQuery('.accnt-err-msg').html('Invald Marketplace ID');
        return;
    }

    if (aws_key_id.length < 3) {
        jQuery('input[name=cpf_aws_key_id]').focus();
        jQuery('.accnt-err-msg').html('Invald AWS Key ID');
        return;
    }

    if (secret_key_id.length < 3) {
        jQuery('input[name=cpf_secret_key_id]').focus();
        jQuery('.accnt-err-msg').html('Invald Secret Key');
        return;
    }
    jQuery.ajax({
        url: ajaxurl,
        type: "post",
        data: {
            seller_id: seller_id,
            marketplace_id: marketplace_id,
            aws_key_id: aws_key_id,
            secret_key_id: secret_key_id,
            site: site,
            feedpath: amwscpf_object.cmdAddCredentials,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        },
        dataType: 'json',
        success: function (res) {

            if (res.success) {
                jQuery('.accnt-err-msg').html(res.message);
                var html = '<tr>';
                html += "<th><input type='radio' checked='checked' value='" + res.option_name + "' name='cpf_credentials'></th>";
                html += "<td>" + marketplace_id + "</td>";
                html += '</tr>';
                jQuery('#cpf_credentials_list').prepend(html);
            }
        }
    });
}

function amwscp_submitFeed() {
    var feed_id = jQuery('#feed_id').val();
    var credentials = jQuery('input[name=cpf_credentials]:checked').val();
    if (credentials == undefined) {
        alert('Credential not defined');
        return;
    }
    jQuery('img.report-spinner').show();
    jQuery('#updload_report').html('Uploading, Please Wait... ');
    jQuery.ajax({
        url: ajaxurl,
        type: "post",
        data: {
            feed_id: feed_id,
            credentials: credentials,
            cmd: 'SubmitFeed',
            feedpath: amwscpf_object.cmdSubmitFeed,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        },
        success: function (res) {
            jQuery('img.report-spinner').hide();
            jQuery('#updload_report').html(res);
        }
    })
}

function amwscp_doUpdateFeedResults() {
    jQuery('#ajax-loader-cat-import').show();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            feedpath: amwscpf_object.cmdSubmissionFeedResult,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        },
        success: function (res) {
            jQuery('#amwscpf-update-nag').show();
            jQuery('#amwscpf-update-nag').html(res);
            jQuery('#ajax-loader-cat-import').hide();
            // location.reload();
        }
    });
}

function amwscp_add_amazon_account() {
    var account_title = jQuery('input[name=amwscpf_account_title]').val();
    var market_id = jQuery('#amwscpf-amazon_market_id').val();
    var merchant_id = jQuery('#amwscpf_merchant_id').val();
    var marketplace_id = jQuery('#amwscpf_marketplace_id').val();
    var access_key = jQuery('#amwscpf_access_key_id').val();
    var secret_key = jQuery('#amwscpf_secret_key').val();
    var market_code = jQuery('#amwscpf_amazon_market_code').val();
    var account_id = jQuery('input[name=account_id]').val();
    var reloadcheck = jQuery('input[name=reloadcheck]').val();
    var is_help_page = jQuery('input[name=is_help]').val();

    if (account_title.length < 1) {
        jQuery('.acc_title').addClass('dashicons dashicons-warning').attr('title', 'Account title field is required').fadeIn(1000);
        autotrigger[amwscpf_object.step].click();
        amwscpf_object.step = 0;
        autotrigger[amwscpf_object.step].click();
        jQuery('input[name=amwscpf_account_title]').focus();
        jQuery('.acc_title').fadeOut(5000);
        return;
    }

    if (merchant_id.length < 1) {
        jQuery('.acc_merchant').addClass('dashicons dashicons-warning').attr('title', 'Merchant ID can be retrieved once you logged in with amazon. Click on Sign in with Amazon.').fadeIn(1000);
        jQuery('#amwscpf_merchant_id').focus();
        jQuery('.acc_merchant').fadeOut(5000);
        return;
    }

    if (marketplace_id.length < 1) {
        jQuery('.acc_marketplace_id').addClass('dashicons dashicons-warning').attr('title', 'Marketplace ID can be retrieved once you logged in with amazon. Click on Sign in with Amazon.').fadeIn(1000);
        jQuery('#amwscpf_marketplace_id').focus();
        jQuery('.acc_marketplace_id').fadeOut(5000);
        return;
    }

    if (access_key.length < 1) {
        jQuery('.acc_aws_key').addClass('dashicons dashicons-warning').attr('title', 'MWS Auth Token can be obtained once you authorized our developer Id').fadeIn(1000);
        jQuery('#amwscpf_access_key_id').focus();
        jQuery('.acc_aws_key').fadeOut(5000);
        return;
    }
    /*
        if (secret_key.length < 1) {
            jQuery('.acc_sectret_key').addClass('dashicons dashicons-warning').attr('title', 'Secret Key can be retrieved once you logged in with amazon. Click on Sign in with Amazon.').fadeIn(1000);
            jQuery('#amwscpf_secret_key').focus();
            jQuery('.acc_sectret_key').fadeOut(5000);
            return;
        }*/
    // var submit_url = "core/ajax/wp/amazon_processings.php";
    // var data = jQuery('#addAccountForm').serialize();
    var overlaydiv = jQuery('.loadingoverloay');
    if (overlaydiv) {
        overlaydiv.show();
    }
    jQuery('img.account').fadeIn(3000);
    jQuery.ajax({
        url: ajaxurl,
        data: {
            account_title: account_title,
            // amwscpf_amazon_market_id:market_id,
            marketplace_id: marketplace_id,
            seller_id: merchant_id,
            mws_auth_token: access_key,
            /*secret_key_id: secret_key,*/
            site: market_code,
            account_id: account_id,
            feedpath: amwscpf_object.cmdAddCredentials,
            security: amwscpf_object.security,
            action: amwscpf_object.action,
            reloadcheck: reloadcheck,
            need_help: is_help_page
        },
        type: 'post',
        // dataType:'json',
        success: function (res) {
            if (overlaydiv) {
                overlaydiv.hide();
            }
            if (res == 'added') {
                autotrigger[0].click();
                autotrigger[1].click();
                /*autotrigger[amwscpf_object.step].click();
                amwscpf_object.step = 2;
                autotrigger[amwscpf_object.step].click();*/

            }
            amwscp_deleteEscapeOption(false);
            if (jQuery('#amwscpf_btn_add_account').html() == 'Update') {
                alert("Selected Account Updated Successfully. Click Make Default to make the account default and start submitting your feeds. Thanks.");
            } else {
                alert("Account Added Successfully. Click Make Default to make the account default and start submitting your feeds. Thanks.");
            }
            jQuery('img.account').fadeOut('6000');
            jQuery('#setting-error-settings_updated').html('<p>' + res + '</p>');
            jQuery('#account_setup_2').hide();
            // location.reload();
        }
    });
}

function amwscpf_editaccount(selector) {
    var account_id = jQuery(selector).attr('data-id');
    sessionStorage.setItem('amazon_account_id', account_id);
    jQuery.ajax({
        url: ajaxurl,
        data: {
            feedpath: amwscpf_object.cmdGetCredentials,
            security: amwscpf_object.security,
            action: amwscpf_object.action,
            account_id: account_id
        },
        type: 'post',
        dataType: 'json',
        success: function (res) {
            jQuery("#amwscpf-amazon_market_id_withoutAccount").val(res.market_code);
            jQuery("#amwscpf_title").val(res.title);
            jQuery("#amwscpf_merchant_id").val(res.merchant_id);
            jQuery("#amwscpf_auth_id").val(res.mws_auth_token);
            /*jQuery('#account_setup_2').show();
            jQuery('input[name=amwscpf_account_title]').val(res.title);
            jQuery('#amwscpf-amazon_market_id').val(res.market_code);
            jQuery('#amwscpf_merchant_id').val(res.merchant_id);
            jQuery('#amwscpf_marketplace_id').val(res.marketplace_id);
            jQuery('#amwscpf_access_key_id').val(res.access_key_id);
            jQuery('#amwscpf_secret_key').val(res.secret_key);
            jQuery('#amwscpf_amazon_market_code').val(res.market_code);
            jQuery('input[name=account_id]').val(res.id);*/
            jQuery('#amc-contact-link').html('Update');
            jQuery("#amwscpf-amazon_market_id_withoutAccount").trigger('change');
        }
    });
}

function change_auto_update_status(option) {
    var swtich_value = jQuery(option).is(':checked');
    if (swtich_value) {
        jQuery('#interval_options').fadeIn('slow');
    } else {
        jQuery('#interval_options').fadeOut('slow');
    }
    jQuery.ajax({
        url: ajaxurl,
        data: {
            feedpath: amwscpf_object.cmdUpdateSwitchInterval,
            security: amwscpf_object.security,
            action: amwscpf_object.action,
            switch_value: swtich_value
        },
        type: 'post',
        success: function (res) {
            console.log(res);
        }
    });
}

function save_feed_credential(option) {
    var credential = jQuery(option).val();
    jQuery.ajax({
        url: ajaxurl,
        data: {
            feedpath: amwscpf_object.cmdSaveFeedCredential,
            action: amwscpf_object.action,
            security: amwscpf_object.security,
            credential: credential,
            feed_id: feed_id
        },
        type: 'post',
        success: function (res) {
            console.log(res);
        }
    });
}

function amwscpf_update_orders(days = false, byajax = false) {
    if (!days) days = jQuery('#amwscp_update_before').val();
    jQuery('#amwscp_order_update_msg').html('');

    if (byajax === false) {
        jQuery('#amwscp_update_before').hide();
        jQuery('.amwscpf_update_order').show();
    }
    jQuery.ajax({
        url: ajaxurl,
        data: {
            feedpath: amwscpf_object.cmdOrderUpdate,
            action: amwscpf_object.action,
            security: amwscpf_object.security,
            days: days,
            type: 'fetch_amazon_order'
        },
        dataType:'json',
        type: 'post',
        success: function (res) {
            console.log(res)
            jQuery('#amwscp_update_before').show();
            jQuery('.amwscpf_update_order').hide();
            if (res.success === true) {
                amwscp_save_amazonto_wooorder(days);
            } else if (res.success === false && byajax === false) {
                jQuery('#amwscp_order_update_msg').html('No new orders found in amazon. Thank you.');
            }
        }
    });
}

function amwscp_perform_ajax(payloads, callback) {

    jQuery.ajax({
        url: ajaxurl,
        data: payloads,
        type: 'post',
        success: function (res) {
            if (res == true) {
                callback(null, res);
            }
        },
        error: function (res) {
            callback(res, false);
        }
    });
}

function amwscp_save_amazonto_wooorder(days) {
    payloads = {
        feedpath: amwscpf_object.cmdOrderUpdate,
        action: amwscpf_object.action,
        security: amwscpf_object.security,
        type: 'create_woo_order',
        days: days
    };
    amwscp_perform_ajax(payloads, function (err, res) {
        if (err) {
            console.log(err)
        } else {
            console.log(res);
        }
    });
}


function importSomeTemplate(selector) {
    ssss = selector;
    var tpl_order = selector;
    jQuery('.template_loader').show();
    var tpl = jQuery(selector).attr('data-tpl');
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            feedpath: amwscpf_object.cmdImportTemplate,
            action: amwscpf_object.action,
            security: amwscpf_object.security,
            tpl: tpl
        },
        success: function (res) {
            jQuery('.template_loader').hide();
            if (res) {
                jQuery(tpl_order).parent('dd').html(res);
                conf = confirm('Would like to import more templates?');
                if (!conf) {
                    amwscpf_object.step = 2;
                    autotrigger[amwscpf_object.step].click();
                } else {
                    return;
                }
            }
        }
    });
}

function confirmFeedSubmission(e) {
    r = confirm("Product count in feed has been changed. Additional charges may apply. Do you want to continue ?");

    if (r == true) {
        return true;
    } else {
        return false;
    }

}

function amwscp_doFetchTemplatesByCountry(value, service) {
    jQuery('#feed-type-value-input').html('');
    jQuery('#feed-type-value-input').attr('value', '');
    jQuery('span#amwscp_feed_list').html('');
    jQuery('#feed_product_type_box').hide();
    return;
    /*var country_code = jQuery('#categoryDisplayTextbyC').val();
  jQuery('#ajax-loader-cat-import').show();
  jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {

            country_code: value,
            service_name:service,
            feedpath:amwscpf_object.cmdImportTemplatesofPcountry,
            security:amwscpf_object.security,
            action: amwscpf_object.action

        },
        success: function(res){
           jQuery('#ajax-loader-cat-import').hide();
           jQuery('#amazon-default-categories').html('');
           jQuery('#amazon-default-categories').html(res);
        }
    });*/


}


function amwscp_doSearchOrder(formid) {

    // var data = jQuery('#'+formid).serialize();
    // console.log(data); return false;

    var keywordfororderid = jQuery('#search-keyword').val();
    var keywordfororderstatus = jQuery('#searchbystatus').val();
    var searchtype = jQuery('#search-type').val();
    jQuery('#ajax-loader-cat-import').show();
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: {

            keywordfororderid: keywordfororderid,
            keywordfororderstatus: keywordfororderstatus,
            searchtype: searchtype,
            feedpath: amwscpf_object.cmdSearchOrder,
            security: amwscpf_object.security,
            action: amwscpf_object.action

        },
        success: function (res) {
            jQuery('#ajax-loader-cat-import').hide();
            jQuery('#the-list').html('');
            jQuery('#the-list').html(res);
        }
    });

}


function selectFeedType(selector) {
    jQuery('span#amwscp_feed_list').html('');
    jQuery('#feed_product_type_box').hide();
    modal = document.getElementById('myModal');
    jQuery('#variation-theme-div').remove();
    var country_code = jQuery('#categoryDisplayTextbyC').val();
    jQuery('#all-amazon-category').html('(' + country_code + ')');
    var btn = document.getElementById("myBtn");
    var span = document.getElementsByClassName("close")[0];
    var country = jQuery('#categoryDisplayTextbyC').val();
    if (jQuery('#feed-type-value-input').val().length <= 0 || jQuery('#page_action_determiner').val() == 'edit') {
        jQuery('.category-main-div').children().each(function () {
            jQuery('#' + this.id).removeAttr('style');
            jQuery('#' + this.id).html('');
        });
        jQuery('#selected-category-text').text('');
        jQuery('#ajax-loader-cat-import').show();
        jQuery.ajax({
            dataType: 'json',
            type: "post",
            url: ajaxurl,
            data: {
                country: country,
                feedpath: amwscpf_object.cmdgetAllTemplate,
                security: amwscpf_object.security,
                action: amwscpf_object.action

            },
            success: function (res) {
                jQuery('#ajax-loader-cat-import').hide();
                // console.log(res.feed_type_data);
                jQuery('#div-0').css({
                    'width': '290px',
                    'display': 'inline-block',
                    'overflow-y': 'scroll',
                    'height': '310px'
                });
                jQuery('#div-0').html(res.html);
                modal.style.display = "block";
            },
            error: function (res) {
                console.log("ERROR:");
                console.log(res);
                jQuery('ajax-loader-cat-import').hide();
            }
        });
    } else {
        modal.style.display = "block";
    }


    // When the user clicks on <span> (x), close the modal
    span.onclick = function () {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
}

jQuery(document).ready(function () {

    /*=======================================================================================================
     * amwscpf_update_orders(days=5,true);

     amwscs_timeout = setInterval(
     "amwscpf_update_orders(days=5,true)",50000
     );
     ======================================================================================================*/

    jQuery('.importTemplatehref').on('click', function () {
        jQuery('#template-import-message').hide();
        jQuery('#ajax-loader-cat-import').show();
        jQuery('#gif-message-span').html('Importing templete...');
    });


    jQuery('#search-keyword,#searchbystatus').keypress(function (e) {
        var key = e.which;
        var keyword = jQuery('#search-keyword').val();
        var searchkeyword = jQuery('#searchbystatus').val();
        if (key == 13) // the enter key code
        {
            var searchword = keyword.replace(/^\s+|\s+$/gm, '');
            searchkeyword = searchkeyword.replace(/^\s+|\s+$/gm, '');
            if (searchword.length <= 0 && searchkeyword.length <= 0) {
                location.reload();
            } else {
                amwscp_doSearchOrder('');
            }
        }
    });

    jQuery('#searchbystatus').on('change', function (e) {
        var key = e.which;
        var keyword = jQuery('#search-keyword').val();
        var searchkeyword = jQuery('#searchbystatus').val();
        // if(key == 13)  // the enter key code
        //  {
        var searchword = keyword.replace(/^\s+|\s+$/gm, '');
        searchkeyword = searchkeyword.replace(/^\s+|\s+$/gm, '');
        if (searchword.length <= 0 && searchkeyword.length <= 0) {
            location.reload();
        } else {
            amwscp_doSearchOrder('');
        }
        // }
    });

    // jQuery('#searchbystatus').keypress(function (e) {
    //  var key = e.which;
    //  var keyword = jQuery('#searchbystatus').val();
    //  if(key == 13)  // the enter key code
    //   {
    //   	var searchword = keyword.replace(/^\s+|\s+$/gm,'');
    //   	if(searchword.length<=0){
    //   		location.reload();
    //   	}else{
    //           amwscp_doSearchOrder('');
    //   	}
    //   }
    // });
    $global_date = new Date();

    jQuery(document).on('change', '#feed_product_type', async function (event) {
        //console.log(sessionStorage.getItem('amazon-templates-essentials'));
        let ajaxRequest = null;
        let feed_product_type = jQuery(this).val();
        jQuery('#ajax-loader-cat-import').show();
        let variation_theme_raw,
            _current_feed_product_type = jQuery(this).val(),
            essentials = sessionStorage.getItem('amazon-templates-essentials'),
            date = $global_date.getFullYear() + '-' + $global_date.getMonth() + 1 + '-' + $global_date.getDate(),
            _parsedEssentials = JSON.parse(essentials),
            _template_identifier = _parsedEssentials.country + '-' + _parsedEssentials.template_id,
            html = '';
        console.log(_parsedEssentials);

        /*if (typeof localStorage.getItem('Amazon-variation-theme') == 'undefined' || localStorage.getItem('Amazon-variation-theme') === null) {
            let
                proto_ = {
                    _cache: {
                        _template_identifier: _template_identifier,
                        value: '',
                        date: date,
                        days: $global_date.getDate() + 5
                    }
                };
            localStorage.setItem('Amazon-variation-theme', JSON.stringify(proto_));
        }
        let variation = JSON.parse(localStorage.getItem('Amazon-variation-theme'));
        if (variation._cache.value && variation._cache.days > $global_date.getDate() && variation._cache._template_identifier == _template_identifier) {
            variation_theme_raw = variation._cache.value;
        } else {*/

           await jQuery.post('https://apis.exportfeed.com/get-variations', essentials, function (res) {
            console.log(res);
            console.log(typeof res.data);
            if (res.data.length) {
                variation_theme_raw = res.data[0].variation_theme;
                variation_theme_raw = JSON.parse(variation_theme_raw);
                t = variation_theme_raw;
                /*variation._cache.value = variation_theme_raw;
                variation._cache.days = $global_date.getDate() + 5;
                variation._cache._template_identifier = _template_identifier;
                localStorage.setItem('Amazon-variation-theme', JSON.stringify(variation));*/
                if (variation_theme_raw) {
                    console.log(variation_theme_raw);
                    console.log(_current_feed_product_type);
                    console.log(variation_theme_raw);
                    key = ' ' + _current_feed_product_type + ' ';
                    console.log(key);
                    console.log(variation_theme_raw[key]);
                    if (variation_theme_raw.hasOwnProperty(key)) {
                        html += '<div id="variation-theme-div" class="feed-right-row cs-option"><label class="label" for="variation">Amazon Variation Theme: </label><div class="input-boxes"><select id="variation-theme-select" class="varaiation-selection" name="variation-theme-selection"><option value="0">Select variation theme</option>';
                        jQuery.each(variation_theme_raw[key], function (index, data) {
                            html += '<option value="' + data.replace(' ', '') + '">' + data.replace(' ', '') + '</option>';
                        });
                        html += '</div></select></div>';
                        jQuery('#variation-theme-div').remove();
                        jQuery('#feed_product_type_box').after(html);
                    }
                }else{
                    jQuery('#ajax-loader-cat-import').hide();
                }
            }

            jQuery.ajax({
                dataType: 'json',
                type: "post",
                url: ajaxurl,
                data: {
                    country: _parsedEssentials.country,
                    feedpath: amwscpf_object.cmdUpdatefeedProductType,
                    security: amwscpf_object.security,
                    action: amwscpf_object.action,
                    feed_product_type: feed_product_type,
                    _template_identifier: _parsedEssentials.tpl+'_'+_parsedEssentials.country
                },
                success: function (res) {
                    jQuery('#ajax-loader-cat-import').hide();
                },
                error: function (res) {
                    console.log("error");
                    jQuery('#ajax-loader-cat-import').hide();
                }
            });
        }, 'json');

        //}


        /*=========================================================================================================
        jQuery.ajax({
            dataType: 'json',
            type: "post",
            url: ajaxurl,
            data: {
                country: country,
                feedpath: amwscpf_object.cmdUpdatefeedProductType,
                security: amwscpf_object.security,
                action: amwscpf_object.action

            },
            success: function (res) {
                jQuery('#ajax-loader-cat-import').hide();
                // console.log(res.feed_type_data);
                jQuery('#div-0').css({
                    'width': '290px',
                    'display': 'inline-block',
                    'overflow-y': 'scroll',
                    'height': '310px'
                });
                jQuery('#div-0').html(res.html);
                modal.style.display = "block";
            },
            error: function (res) {
                console.log("ERROR:");
                console.log(res);
                jQuery('ajax-loader-cat-import').hide();
            }
        });
        ============================================================================================================*/

        event.preventDefault();
    });

})
;


// function importTemplateFunc(){
// 	alert("suzan");
// 	return false;
// }
function showPrev() {
    total_div = jQuery('.category-main-div').children().length;
    // console.log(total_div);
    // console.log(current_model_label);
    if (current_model_label > 0) {
        // extralabeltohide = current_model_label + 1;
        var labeltoShow = current_model_label - 3;
        // current_model_label = current_model_label - 1;
        var $check = jQuery('#div-' + labeltoShow);

        if (typeof ($check[0]) != "undefined" && $check.selector == "#div-" + labeltoShow) {
            jQuery('#div-' + current_model_label).css({
                'display': 'none'
            });
            // jQuery('#div-'+extralabeltohide).css({'display': 'none'});
            // jQuery('#div-'+labeltoShow).removeAttr('style');
            jQuery('#div-' + labeltoShow).css({
                'display': 'inline-block',
                'margin-left': '0px'
            });
            if (labeltoShow > 0) {
                current_model_label = current_model_label - 1;
            }
        } else {
            alert("this is root , cannot go further");
            // jQuery('#category_icon_left').disabled();
        }
    }

}

function showNext() {
    total_div = jQuery('.category-main-div').children().length;
    // console.log(total_div);
    // console.log(current_model_label);
    // if(current_model_label < total_div){
    var $check = jQuery('#div-' + current_model_label);
    if (typeof ($check[0]) != "undefined" && $check.selector == "#div-" + current_model_label) {
        var labeltoShow = current_model_label;
        var labeltoHide = parseInt(current_model_label) - 3;
        jQuery('#div-' + labeltoHide).css({
            'display': 'none'
        });
        jQuery('#div-' + labeltoShow).css({
            'display': 'inline-block'
        });
        if (current_model_label < total_div - 1) {
            current_model_label = parseInt(current_model_label) + 1;
        }
    } else {
        alert("this is the root, cannot go further back.");
        // jQuery('#category_icon_left').disabled();
    }
    // }
    // var $check = jQuery('#div-'+current_model_label);
    // if(current_model_label <= total_div){
    //     var labeltoShow = current_model_label;
    //  var labeltoHide = parseInt(current_model_label) - 3;
    //  jQuery('#div-'+labeltoHide).css({'display': 'none'});
    //  jQuery('#div-'+labeltoShow).css({'display': 'inline-block'});
    // }

}

function doFetchAmazonMarket(value) {
    // console.log(value);
    var mpid;
    var merchantName = null;
    var authUrl;
    switch (value) {
        case 'US':
            mpid = "ATVPDKIKX0DER";
            developerId = "349543377271";
            merchantName = "Amazon US";
            authUrl = "https://sellercentral.amazon.com/gp/mws/registration/register.html?signInPageDisplayed=1&devAuth=1";
            break;
        case 'MX':
            mpid = "A1AM78C64UM0Y8";
            developerId = "349543377271";
            merchantName = "Amazon Mexico";
            authUrl = "https://sellercentral.amazon.com.mx/gp/mws/registration/register.html?signInPageDisplayed=1&devAuth=1";
            break;
        case 'UK':
            mpid = "A1F83G8C2ARO7P";
            developerId = "572161777442";
            authUrl = "https://sellercentral.amazon.co.uk/gp/mws/registration/register.html?signInPageDisplayed=1&devAuth=1";
            break;
        case 'ES':
            mpid = "A1RKKUPIHCS9HS";
            developerId = "572161777442";
            merchantName = "Amazon Spain";
            authUrl = "https://sellercentral.amazon.es/gp/mws/registration/register.html?signInPageDisplayed=1&devAuth=1";
            break;
        case 'DE':
            mpid = "A1PA6795UKMFR9";
            developerId = "572161777442";
            merchantName = "Amazon Germany";
            authUrl = "https://sellercentral.amazon.de/gp/mws/registration/register.html?signInPageDisplayed=1&devAuth=1";
            break;
        case 'FR':
            mpid = "A13V1IB3VIYZZH";
            developerId = "572161777442";
            merchantName = "Amazon France";
            authUrl = "https://sellercentral.amazon.fr/gp/mws/registration/register.html?signInPageDisplayed=1&devAuth=1";
            break;
        case 'CA':
            mpid = "A2EUQ1WTGCTBG2";
            developerId = "349543377271";
            merchantName = "Amazon Canada";
            authUrl = "https://sellercentral.amazon.ca/gp/mws/registration/register.html?signInPageDisplayed=1&devAuth=1";
            break;
        case 'IT':
            mpid = "APJ6JRA9NG5V4";
            developerId = "572161777442";
            merchantName = "Amazon Italy";
            authUrl = "https://sellercentral.amazon.it/gp/mws/registration/register.html?signInPageDisplayed=1&devAuth=1";
            break;
        case 'AU':
            mpid = "A39IBJ37TRP1C6";
            developerId = "349543377271";
            merchantName = "Amazon Australia";
            authUrl = "https://sellercentral.amazon.com.au/gp/mws/registration/register.html?signInPageDisplayed=1&devAuth=1";
            break;
        case 'IN':
            mpid = "A21TJRUUN4KGV";
            developerId = "572161777442";
            merchantName = "Amazon India";
            authUrl = "https://sellercentral.amazon.in/gp/mws/registration/register.html?signInPageDisplayed=1&devAuth=1";
            break;

        default:
            mpid = "ATVPDKIKX0DER";
            developerId = "349543377271";
            merchantName = "Amazon US";
            authUrl = "https://sellercentral.amazon.com/gp/mws/registration/register.html?signInPageDisplayed=1&devAuth=1";
    }

    if (mpid && mpid.length > 0) {
        jQuery('#amwscpf_marketplace_id').attr('value', mpid);
        jQuery('#amwscp_hidden_marketplace_id').attr('value', mpid);
        jQuery('#amwscp_hidden_marketplace_id').val(mpid);
        jQuery("#copyDeveloperID").html(developerId);
        jQuery(".message-box-amazon-setup #copyDeveloperID").html(developerId);
        jQuery(".user_permission_page").attr('href', authUrl);
        // jQuery(".message-box-amazon-setup #copyDeveloperID").append('<span class="tooltiptext">Click to copy</span>');
        jQuery('#amwscpf_amazon_market_code_no_account').val(mpid);
        jQuery(".message-box-amazon-setup #merchant_name").html(merchantName);
    }

}

function show_advanced_attr(selector) {
    jQuery('#ajax-loader-cat-import').show();
    setTimeout(function(){
        jQuery('#attributeMappings').toggle();
        jQuery('#ajax-loader-cat-import').hide();
    },1000);
    jQuery('.dashicons').toggleClass('dashicons-arrow-down').toggleClass('dashicons-arrow-up');
    jQuery('#advance-section').toggle();
}

function colorVariation() {
    jQuery('.color-table').show();
    jQuery('.size-table').hide();
}

function sizeVariation() {
    jQuery('.color-table').hide();
    jQuery('.size-table').show();
}

function showBillingDetails() {
    jQuery('.billing .edit_address').toggle();
    jQuery('.billing .address').toggle();
    jQuery('.order_data_column.billing .save_bill').toggle();
}

function showShippingDetails() {
    jQuery('.shipping .edit_address').toggle();
    jQuery('.shipping .address').toggle();
    jQuery('.order_data_column.shipping .save_bill').toggle();
}

function selectAllProducts(selector) {
    var checked = jQuery("#order_select_all_checkbox").attr('checked');
    var checked_footer = jQuery("#order_select_all_checkbox_footer").attr('checked');

    if (checked == 'checked') {
        jQuery(".amazon_Buyer_wrapper .amazon_Buyer_List").find("tr td:first-child input[type=checkbox]").attr('checked', true);
        jQuery("#order_select_all_checkbox_footer").attr('checked', true);
    } else {
        jQuery("#order_select_all_checkbox").attr('checked', false);
        jQuery("#order_select_all_checkbox_footer").attr('checked', false);
        jQuery(".amazon_Buyer_wrapper .amazon_Buyer_List").find("tr td:first-child input[type=checkbox]").removeAttr('checked');
    }
}

function selectAllProductsFooter(selector) {
    var checked = jQuery("#order_select_all_checkbox").attr('checked');
    var checked_footer = jQuery("#order_select_all_checkbox_footer").attr('checked');

    if (checked_footer == 'checked') {
        jQuery(".amazon_Buyer_wrapper .amazon_Buyer_List").find("tr td:first-child input[type=checkbox]").attr('checked', true);
        jQuery("#order_select_all_checkbox").attr('checked', true);
    } else {
        jQuery("#order_select_all_checkbox_footer").attr('checked', false);
        jQuery("#order_select_all_checkbox").attr('checked', false);
        jQuery(".amazon_Buyer_wrapper .amazon_Buyer_List").find("tr td:first-child input[type=checkbox]").removeAttr('checked');
    }
}

function ShowSkipPopup() {
    jQuery("#skip-popup-modal").show();
}

function amwscp_AddMarketplaceCodeOption() {
    var market_code = jQuery('#amwscpf-amazon_market_id_withoutAccount').val();
    jQuery('#ajax-loader-cat-import').show();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: "json",
        data: {
            feedpath: amwscpf_object.cmdAddCredentials,
            security: amwscpf_object.security,
            action: amwscpf_object.action,
            marketplace: market_code,
            type: 'nomarketplace'
        },
        success: function (res) {
            if (res.success == true) {
                location.reload();
            } else if (res.update == true) {
                alert("MarketPlace could not be updated Successfully. May be you used the same value. please try again. Thanks.");
            } else {
                location.reload();
            }
        }
    });
}

function amwscp_deleteEscapeOption(msg = false) {
    jQuery('#ajax-loader-cat-import').show();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: "json",
        data: {
            feedpath: amwscpf_object.cmdAddCredentials,
            security: amwscpf_object.security,
            action: amwscpf_object.action,
            type: 'nomarketplace',
            query: 'delete',
        },
        success: function (res) {
            if (res.success == true) {
                if (msg) {
                    alert("MarketPlace Deleted Successfully");
                }
                location.reload();
            } else if (res.update == true) {
                if (msg) {
                    alert("MarketPlace could not be deleted . please try again. Thanks.");
                }
            }
        }
    });

}

jQuery(document).ready(function () {
    jQuery(".skip-close").click(function () {
        jQuery("#skip-popup-modal").hide();
    });
});

function toggleClassselect() {
    jQuery(".amazon-marketplace_connect").slideDown();
    jQuery(".amazon-marketplace_select").slideUp('slow');
    jQuery("#marketplaceTabs li:nth-child(2)").removeClass("disable");
    jQuery("#marketplaceTabs li:nth-child(2)").addClass("active");
}

function togglebackClassconnect() {
    jQuery(".amazon-marketplace_connect").slideUp();
    jQuery(".amazon-marketplace_select").slideDown('slow');
    jQuery("#marketplaceTabs li:nth-child(2)").removeClass("active");
    jQuery("#marketplaceTabs li:nth-child(2)").addClass("disable");
}

function toggleClassconnect() {
    jQuery(".amazon-marketplace_connect").slideUp();
    jQuery(".amazon-marketplace_default").slideDown('slow');
    jQuery("#marketplaceTabs li:nth-child(3)").removeClass("disable");
    jQuery("#marketplaceTabs li:nth-child(3)").addClass("active");
}

function togglebackClassdefault() {
    jQuery(".amazon-marketplace_connect").slideDown();
    jQuery(".amazon-marketplace_default").slideUp('slow');
    jQuery("#marketplaceTabs li:nth-child(3)").removeClass("active");
    jQuery("#marketplaceTabs li:nth-child(3)").addClass("disable");
}

function nextSlideImage() {
    // jQuery(document).scrollTo('#amazon-marketplace-select');
    //jQuery(window).scrollTop(jQuery('#amazon-marketplace-select').offset());
    if (jQuery(".carousel-inner .item:last").hasClass("active")) {
        jQuery('.right.carousel-control').unbind('click');
    } else {
        jQuery('div.item.active').removeClass('active').next('div').addClass('active');
    }
    if (jQuery(".carousel-inner .item:nth-last-child(1)").hasClass("active")) {
        jQuery('.glyphicon-chevron-right button').addClass('disabled');
    } else {
        jQuery('.glyphicon-chevron-right button').removeClass('disabled');
    }
    if (jQuery(".carousel-inner .item:first").hasClass("active")) {
        jQuery('.glyphicon-chevron-left button').addClass('disabled');
    } else {
        jQuery('.glyphicon-chevron-left button').removeClass('disabled');
    }

}

function prevSlideImage() {
    // jQuery('body').scrollTarget('#amazon-marketplace-select');
    if (jQuery(".carousel-inner .item:first").hasClass("active")) {
        jQuery('.left.carousel-control').unbind('click');
    } else {
        jQuery('div.item.active').removeClass('active').prev('div').addClass('active');
    }
    if (jQuery(".carousel-inner .item:first").hasClass("active")) {
        jQuery('.glyphicon-chevron-left button').addClass('disabled');
    } else {
        jQuery('.glyphicon-chevron-left button').removeClass('disabled');
    }
    if (jQuery(".carousel-inner .item:nth-last-child(1)").hasClass("active")) {
        jQuery('.glyphicon-chevron-right button').addClass('disabled');
    } else {
        jQuery('.glyphicon-chevron-right button').removeClass('disabled');
    }
}

/*==============================================================
Save Amazon account with our developer credentials
================================================================*/
function SubmitMarketplace() {
    jQuery('#error_msg_div').html("");
    var account_title = jQuery('#amwscpf_title').val();
    var mws_auth_token = jQuery('#amwscpf_auth_id').val();
    var marketplace_id = jQuery('#amwscp_hidden_marketplace_id').val();
    var seller_id = jQuery('#amwscpf_merchant_id').val();
    var site = jQuery('#amwscpf-amazon_market_id_withoutAccount').val();
    jQuery("#ajax-loader-cat-import").show();
    jQuery.ajax({
        url: ajaxurl,
        type: "post",
        data: {
            seller_id: seller_id,
            account_title: account_title,
            mws_auth_token: mws_auth_token,
            marketplace_id: marketplace_id,
            /*aws_key_id: aws_key_id,
            secret_key_id: secret_key_id,*/
            site: site,
            account_id: sessionStorage.getItem('amazon_account_id') ? sessionStorage.getItem('amazon_account_id') : false,
            feedpath: amwscpf_object.cmdAddCredentials,
            security: amwscpf_object.security,
            action: amwscpf_object.action
        },
        dataType: 'json',
        success: function (res) {
            sessionStorage.removeItem('amazon_account_id');
            jQuery("#ajax-loader-cat-import").hide();
            console.log(res);
            if (res.success) {
                if (res.data.link) {
                    jQuery('#error_msg_div').html(res.data.link);
                } else if (res.data.invalid) {
                    jQuery('#error_msg_div').html(res.data.invalid);
                } else {
                    jQuery('#error_msg_div').html(res.data.message);
                    alert(res.data.message);
                    location.reload();
                }
                /* jQuery('.accnt-err-msg').html(res.message);
                 var html = '<tr>';
                 html += "<th><input type='radio' checked='checked' value='" + res.option_name + "' name='cpf_credentials'></th>";
                 html += "<td>" + marketplace_id + "</td>";
                 html += '</tr>';
                 jQuery('#error_msg_div').prepend(html);*/
            } else {
                jQuery('#error_msg_div').html(res.msg);
            }
        }
    });
}

//  jQuery(document).ready(function(){

//  });

//  jQuery(document).ready(function(){

//  });

jQuery(document).ready(nextSlideImage());
jQuery(document).ready(prevSlideImage());

jQuery(document).ready(function () {
    jQuery("#amwscpf-amazon_market_id_withoutAccount").on("change", function () {
        jQuery('#error_msg_div').html('');
        var selectVal = jQuery("#amwscpf-amazon_market_id_withoutAccount option:selected").val();
        if (selectVal == 'IN' || selectVal == 'AU') {
            jQuery('.userguide-small-button').hide();
            jQuery(".skip-btn-KTG").show();
            jQuery(".select-canada-marketplace").slideUp('slow');
            jQuery(".select-eu-marketplace").slideDown('slow');
            jQuery(".select-us-marketplace").slideUp('slow');
            jQuery(".cpf-userguide-nextprev").slideDown('slow');
            jQuery("#welcome_slider_modal").show();
            jQuery("#welcome_slider_modal .carousel-inner div:nth-child(2)").addClass('active');
            jQuery("#welcome_slider_modal .carousel-inner div:nth-child(3)").removeClass('active');
        } else if (selectVal == 'null') {
            jQuery('.userguide-small-button').hide();
            jQuery(".skip-btn-KTG").show();
            jQuery(".select-canada-marketplace").slideUp('slow');
            jQuery(".select-eu-marketplace").slideDown('slow');
            jQuery(".select-us-marketplace").slideUp('slow');
            jQuery(".cpf-userguide-nextprev").slideDown('slow');
            jQuery("#welcome_slider_modal").hide();
            jQuery("#submit_after_skip").hide();
        } else {
            jQuery('.userguide-small-button').show();
            jQuery(".skip-btn-KTG").hide();
            jQuery(".select-us-marketplace").slideDown('slow');
            jQuery(".select-canada-marketplace").slideUp('slow');
            jQuery(".select-eu-marketplace").slideUp('slow');
            jQuery(".cpf-userguide-nextprev").slideDown('slow');
            jQuery("#welcome_slider_modal").show();
            jQuery("#welcome_slider_modal .carousel-inner div:nth-child(2)").removeClass('active');
            jQuery("#welcome_slider_modal .carousel-inner div:nth-child(3)").addClass('active');
        }
    });
});


// jQuery(document).ready(function(){
// var copyTextareaBtn = document.querySelector('.tooltip');
// if(copyTextareaBtn){
//     copyTextareaBtn.addEventListener('click', function(event) {
//         var copyTextarea = document.querySelector('.tooltip');
//         copyTextarea.focus();
//         copyTextarea.select();
//         try {
//             var successful = document.execCommand('copy');
//             var msg = successful ? 'successful' : 'unsuccessful';
//             console.log('Copying text command was ' + msg);
//         } catch (err) {
//             console.log('Oops, unable to copy');
//         }
//     });
// }
// });
jQuery(document).ready(function () {
    jQuery("li div#copyDeveloperID.tooltip").mouseover(function () {
        jQuery('li span.tooltiptext').show().text('Click to copy');
    });
    jQuery("li div#copyDeveloperID.tooltip").mouseleave(function () {
        jQuery('li span.tooltiptext').hide();
    });
});

function copy(that) {
    var inp = document.createElement('input');
    document.body.appendChild(inp)
    inp.value = that.textContent
    inp.select();
    document.execCommand('copy', false);
    inp.remove();
    jQuery("li div#copyDeveloperID.tooltip").mouseover(function () {
        jQuery('li span.tooltiptext').show().fadeIn(100).text('Click to copy');
    });
    jQuery('li span.tooltiptext').text('Copied').delay(800).fadeOut(100);

    // jQuery('li span.tooltiptext').delay(800).text('Click to copy').show();

}

jQuery(document).ready(function () {
    jQuery("a[name=copy_pre]").click(function () {
        var id = jQuery(this).attr('id');
        var el = document.getElementById(id);
        var range = document.createRange();
        range.selectNodeContents(el);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
        document.execCommand('copy');
        alert("Contents copied to clipboard.");
        return false;
    });
});
