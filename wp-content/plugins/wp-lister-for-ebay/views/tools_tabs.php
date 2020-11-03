<?php  
    $active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_key($_GET[ 'tab' ]) : 'inventory'; 
?>  

<?php if ( @$_REQUEST['page'] == 'wplister-settings-categories' ) : ?>

    <!-- <h2><?php echo __( 'Categories', 'wp-lister-for-ebay' ) ?></h2>   -->

<?php else : ?>

    <h2 class="nav-tab-wrapper">  

        <a href="<?php echo $wpl_tools_url; ?>&tab=inventory" class="nav-tab <?php echo $active_tab == 'inventory' ? 'nav-tab-active' : ''; ?>"><?php echo __( 'Inventory', 'wp-lister-for-ebay' ) ?></a>
        <a href="<?php echo $wpl_tools_url; ?>&tab=stock_log" class="nav-tab <?php echo $active_tab == 'stock_log' ? 'nav-tab-active' : ''; ?>"><?php echo __( 'Stock Log', 'wp-lister-for-ebay' ) ?></a>
        <a href="<?php echo $wpl_tools_url; ?>&tab=developer" class="nav-tab <?php echo $active_tab == 'developer' ? 'nav-tab-active' : ''; ?>"><?php echo __( 'Developer', 'wp-lister-for-ebay' ) ?></a>

    </h2>  

<?php endif; ?>
