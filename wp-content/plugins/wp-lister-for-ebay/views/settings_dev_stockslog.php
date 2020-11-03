<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>
<style type="text/css">

    #poststuff #side-sortables .postbox input.text_input,
    #poststuff #side-sortables .postbox select.select {
        width: 50%;
    }
    #poststuff #side-sortables .postbox label.text_label {
        width: 45%;
    }
    #poststuff #side-sortables .postbox p.desc {
        margin-left: 5px;
    }

</style>

<div class="wrap wplister-page">
    <div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/hammer-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
    <?php include_once( dirname(__FILE__).'/tools_tabs.php' ); ?>
    <?php echo $wpl_message ?>

    <!-- show listings table -->
    <?php $wpl_listingsTable->views(); ?>
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="listings-filter" method="get" action="<?php echo $wpl_form_action; ?>" >
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <input type="hidden" name="tab"  value="<?php echo esc_attr( $_REQUEST['tab'] ) ?>" />
        <!-- <input type="hidden" name="subtab"  value="<?php //echo esc_attr( $_REQUEST['subtab'] ) ?>" /> -->
        <!-- Now we can render the completed list table -->
        <?php $wpl_listingsTable->search_box( __( 'Search', 'wp-lister-for-ebay' ), 'listing-search-input' ); ?>
        <?php $wpl_listingsTable->display() ?>
    </form>

    <div class="submit" style="">

        <form method="post" action="<?php echo $wpl_form_action; ?>">
            <div class="submit" style="padding-top: 0; float: left;">
                <?php wp_nonce_field( 'wple_clear_stocks_log' ); ?>
                <input type="hidden" name="action" value="wple_clear_stocks_log" />
                <input type="submit" value="<?php echo __( 'Empty log', 'wp-lister-for-ebay' ) ?>" name="submit" class="button">
                <!-- &nbsp; current size: <?php echo $wpl_tableSize ?> mb -->
            </div>
        </form>

        <form method="post" action="<?php echo $wpl_form_action; ?>">
            <div class="submit" style="padding-top: 0; float: left; padding-left:15px;">
                <?php wp_nonce_field( 'wple_optimize_stocks_log' ); ?>
                <input type="hidden" name="action" value="wple_optimize_stocks_log" />
                <input type="submit" value="<?php echo __( 'Optimize log', 'wp-lister-for-ebay' ) ?>" name="submit" class="button">
            </div>
        </form>

    </div>

    <br style="clear:both;"/>

    Current log size: <?php echo $wpl_tableSize ?> mb

    <script type="text/javascript">
        jQuery( document ).ready( function () {

            // init tooltips
            jQuery(".wide_error_tip").tipTip({
                'attribute' : 'data-tip',
                'maxWidth' : '100%',
                'fadeIn' : 50,
                'fadeOut' : 50,
                'delay' : 200
            });

        });
    </script>

</div>