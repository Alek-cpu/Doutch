// Add functionality to BETRS Table of Rates
jQuery(document).ready(function($) {

    // Add new table rate option
    jQuery(document).on('change', '#betrs-shipping_options-setup', function(e){
        e.preventDefault();

        var parent_div = jQuery(this).parent();
        parent_div.block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });

        var option_count = jQuery(this).val();
        var data = { action: 'betrs_add_table_rates_row', optionCount: option_count };

        $.post( ajaxurl, data, function( response ) {
            // append new row to table
            jQuery(parent_div).replaceWith( response );
        });

        return false;
    });

    // Add new table rate option after button click
    jQuery(document).on('click', '#add_table_rates_row', function(e){
        e.preventDefault();

        jQuery(this).block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });

        var link_clicked = jQuery(this);
        var table_body = jQuery('#BETRS-table-rates-parent');

        // determine Row ID
        var IDs = [];
        jQuery('#BETRS-table-rates-parent').find(".single-row").each(function(){ IDs.push(jQuery(this).attr('data-row_id')); });
        var row_id = Math.max.apply(Math,IDs) + 1;

        var data = { action: 'betrs_add_table_rates_row', rowID: row_id };

        $.post( ajaxurl, data, function( response ) {
            // append new row to table
            jQuery(table_body).append( response );
            jQuery(document).trigger( 'betrs_update_sortables' );
            link_clicked.find('.blockUI').remove();

        });

        return false;
    });

    // Event trigger for adding row to cost table
    jQuery(document).on('click', '#BETRS-table-rates-parent a.betrs_add_ops', function(e){
        e.preventDefault();

        jQuery(this).block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });

        var link_clicked = jQuery(this);
        var cost_table = jQuery(this).closest('.single-row').find('.wp-list-table.table_rates');
        var option_ID = jQuery(this).closest('.single-row').attr('data-row_id');
        var row_ID = cost_table.find("tbody > tr").length;

        //alter row_ID if there is only one row and it has the 'no-rows' class
        if( row_ID == 1 && cost_table.find("tbody").children('tr:first').hasClass('no-items') ) {
            row_ID--;
        }

        var data = { action: 'betrs_add_table_costs_row', optionID: option_ID, rowID: row_ID };

        $.post( ajaxurl, data, function( response ) {
            // append new row to table
            jQuery(cost_table).find('tbody').append( response );

            // Remove 'No Items' row if exists
            if( cost_table.find( 'tr.no-items' ) != undefined )
                cost_table.find( 'tr.no-items' ).remove();
            link_clicked.find('.blockUI').remove();

        });

    });

    // Event trigger for adding additional cost to table
    jQuery(document).on('click', '#BETRS-table-rates-parent a.add_table_cost_op', function(e){
        e.preventDefault();

        jQuery(this).block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });

        var link_clicked = jQuery(this);
        var link_container = jQuery(this).closest('div.cost_op');
        var option_ID = jQuery(this).closest('.single-row').attr('data-row_id');
        var row_ID = jQuery(this).closest('tr').index();
        var data = { action: 'betrs_add_extra_costs_op', optionID: option_ID, rowID: row_ID };

        $.post( ajaxurl, data, function( response ) {
            // append new row to table
            jQuery(link_clicked).before( response );

            link_clicked.find('.blockUI').remove();

        });

    });

    // Event trigger for updating cost settings
    jQuery(document).on('change', '#BETRS-table-rates-parent select.cost_type', function(e){
        e.preventDefault();

        var box_changed = jQuery(this);
        var box_container = box_changed.parent().parent();
        var box_selection = box_changed.val();

        box_container.block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });

        var option_ID = jQuery(this).closest('.single-row').attr('data-row_id');
        var row_ID = jQuery(this).closest('tr').index();
        var data = { action: 'betrs_add_costs_op_details', selected: box_selection, optionID: option_ID, rowID: row_ID };

        $.post( ajaxurl, data, function( response ) {
            // append new row to table
            if( box_container.find('.cost_op_extras').length > 0 ) {
                box_container.find('.cost_op_extras').replaceWith( response );
            } else {
                jQuery(box_changed).parent().after( response );
            }

            box_container.find('.blockUI').remove();

        });

    });

    // Event trigger for showing / hiding dimensions select box
    jQuery(document).on('change', '#BETRS-table-rates-parent select.cost_op_every', function(e){
        e.preventDefault();

        var every_dimensions = jQuery(this).parent().find('select.cost_op_every_dim');

        if( jQuery(this).val() == 'dimensions' ) {
            every_dimensions.show();
        } else {
            every_dimensions.hide();
        }

    });

    // Event trigger for adding additional conditions to table
    jQuery(document).on('click', '#BETRS-table-rates-parent a.add_table_condition_op', function(e){
        e.preventDefault();

        jQuery(this).block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });

        var link_clicked = jQuery(this);
        var link_container = jQuery(this).closest('div.condition_op');
        var option_ID = jQuery(this).closest('.single-row').attr('data-row_id');
        var row_ID = jQuery(this).closest('tr').index();
        var data = { action: 'betrs_add_extra_conditions_op', optionID: option_ID, rowID: row_ID };

        $.post( ajaxurl, data, function( response ) {
            // append new row to table
            jQuery(link_clicked).before( response );
            jQuery(document).trigger( 'betrs_update_options' );

            link_clicked.find('.blockUI').remove();

        });

    });

    // Event trigger for updating conditional settings
    jQuery(document).on('change', '#BETRS-table-rates-parent select.cond_type', function(e){
        e.preventDefault();

        var box_changed = jQuery(this);
        var box_container = box_changed.closest('div.condition_op');
        var box_selection = box_changed.val();

        box_container.block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });

        var option_ID = jQuery(this).closest('.single-row').attr('data-row_id');
        var row_ID = jQuery(this).closest('tr').index();
        var cond_ID = box_container.parent().find('div.condition_op').length - 1;
        var data = { action: 'betrs_add_conds_op_details', selected: box_selection, optionID: option_ID, rowID: row_ID, condID: cond_ID };

        $.post( ajaxurl, data, function( response ) {
            // append new row to table
            if( box_container.find('.cond_op_extras').length > 0 ) {
                box_container.find('.cond_op_extras').replaceWith( response );
            } else {
                jQuery(box_changed).parent().after( response );
            }

            jQuery(document).trigger( 'betrs_update_options' );
            box_container.find('.blockUI').remove();
            box_container.trigger('wc-enhanced-select-init');

        });

    });

    // Event trigger for displaying additional measurement select box
    jQuery(document).on('change', '#BETRS-table-rates-parent select.cost_op_extra', function(e){
        e.preventDefault();

        var box_changed = jQuery(this);
        var box_container = box_changed.closest('div.condition_op');
        var box_selection = box_changed.val();
        var box_secondary = box_changed.parent().find('select.cost_op_extra_secondary');

        if( box_selection == 'dimensions' ) {
            box_secondary.css('display','inline');
        } else {
            box_secondary.css('display','none');
        }
    });

    // Event trigger for removing single cond from conditions column
    jQuery(document).on('click', '#BETRS-table-rates-parent span.betrs_delete_ops_cond', function(){
        var answer = confirm(betrs_data.text_delete_confirmation);

        if (answer) {
            var cost_table = jQuery(this).closest('div.condition_op').remove();
        }
        return false;
    });

    // Event trigger for removing single cost from cost column
    jQuery(document).on('click', '#BETRS-table-rates-parent span.betrs_delete_ops_cost', function(){
        var answer = confirm(betrs_data.text_delete_confirmation);

        if (answer) {
            var cost_table = jQuery(this).closest('div.cost_op').remove();
        }
        return false;
    });

    // Event trigger for removing row from cost table
    jQuery(document).on('click', '#BETRS-table-rates-parent a.betrs_delete_ops', function(){
        var answer = confirm(betrs_data.text_delete_confirmation);

        if (answer) {
            var cost_table = jQuery(this).closest('.single-row').find('.wp-list-table.table_rates tbody tr th input:checked');
            cost_table.each(function(i, el){
                jQuery(el).closest('tr').remove();
            });

            // redo variable names so IDs match their appropriate rows
            jQuery(this).closest('.single-row').find('.wp-list-table.table_rates tbody').children("tr").each(function (idx) {
                var $inp = jQuery(this).find('td:not(:first-child, :last-child, :nth-last-child(2))').find('input,textarea,select');
                $inp.each(function () {
                    str = this.name;
                    // find 2nd occurence of '[' string and assign to 'var i'
                    var i = -1;
                    var n = 2;
                    while( n-- && i++ < str.length ) {
                        i = str.indexOf('[', i);
                        if (i < 0) break;
                    }
                    removeID = str.substring( 0, i );
                    newName = removeID + '[' + idx + '][]';

                    // find condition key if multiple select type
                    if( this.attributes['multiple'] ) {
                        // find 3rd occurence of '[' string and assign to 'var i'
                        var i = -1;
                        var n = 3;
                        while( n-- && i++ < str.length ) {
                            i = str.indexOf('[', i);
                            if (i < 0) break;
                        }
                        cidx_str = str.substring( i+1 );
                        j = cidx_str.indexOf(']');
                        cidx = cidx_str.substring( 0, j );
                        newName = removeID + '[' + idx + '][' + cidx + '][]';
                    } else {
                        newName = removeID + '[' + idx + '][]';
                    }
                            
                    this.name = newName;
                })
            });
        }
        return false;
    });

    // Event trigger for removing row from table of rates
    jQuery(document).on('click', '#BETRS-table-rates-parent a.remove-shipping-option', function(){
        var answer = confirm(betrs_data.text_delete_confirmation);

        if (answer) {
            var cost_table = jQuery(this).closest('.single-row').remove();
        }
        return false;
    });

    // Setup date range picker
    jQuery(document).on( 'betrs_update_dateranges', function() {
        jQuery(".cond_secondary_datepicker").daterangepicker({
             datepickerOptions : {
                 numberOfMonths : 2,
                 maxDate: null
             },
             presetRanges: [],
             initialText: "<?php _e( 'Select date range...', 'be-table-ship' ); ?>",
             dateFormat: "d M yy"
         });
    }).trigger( 'betrs_update_dateranges' );

    // Create sortable element area to drop elements into
    jQuery(document).on( 'betrs_update_sortables', function() {
        if( jQuery('.wp-list-table.table_rates tbody').length != 0 ) {
            jQuery('.wp-list-table.table_rates tbody').sortable({
                handle: 'td.column-sort div.move_row',
                cursor: 'move',
                placeholder: "row-placeholder ui-corner-all",
                helper: function(e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function(index)
                    {
                      $(this).width($originals.eq(index).width())
                    });
                    return $helper;
                },
                stop: function(e,ui) {
                    jQuery(this).children("tr").each(function (idx) {
                        var $inp = jQuery(this).find('td:not(:first-child, :last-child, :nth-last-child(2))').find('input,textarea,select');
                        $inp.each(function () {
                            str = this.name;
                            // find 2nd occurence of '[' string and assign to 'var i'
                            var i = -1;
                            var n = 2;
                            while( n-- && i++ < str.length ) {
                                i = str.indexOf('[', i);
                                if (i < 0) break;
                            }
                            removeID = str.substring( 0, i );

                            // find condition key if multiple select type
                            if( this.attributes['multiple'] ) {
                                // find 3rd occurence of '[' string and assign to 'var i'
                                var i = -1;
                                var n = 3;
                                while( n-- && i++ < str.length ) {
                                    i = str.indexOf('[', i);
                                    if (i < 0) break;
                                }
                                cidx_str = str.substring( i+1 );
                                j = cidx_str.indexOf(']');
                                cidx = cidx_str.substring( 0, j );
                                newName = removeID + '[' + idx + '][' + cidx + '][]';
                            } else {
                                newName = removeID + '[' + idx + '][]';
                            }

                            this.name = newName;
                        })
                    });
                }
            });
        }

    }).trigger( 'betrs_update_sortables' );

});
