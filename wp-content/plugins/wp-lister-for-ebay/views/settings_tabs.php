<?php  
    $active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_key($_GET[ 'tab' ]) : 'settings'; 
?>  

<?php if ( @$_REQUEST['page'] == 'wplister-settings-categories' ) : ?>

    <h2><?php echo __( 'Categories', 'wp-lister-for-ebay' ) ?></h2>  

<?php elseif ( @$_REQUEST['page'] == 'wplister-settings-accounts' ) : ?>

    <h2><?php echo __( 'My Account', 'wp-lister-for-ebay' ) ?></h2>  

<?php else : ?>

	<h2 class="nav-tab-wrapper">  

        <?php if ( ! is_network_admin() ) : ?>
        <a href="<?php echo $wpl_settings_url; ?>&tab=settings"   class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php echo __( 'General Settings', 'wp-lister-for-ebay' ) ?></a>  
        <?php endif; ?>

        <a href="<?php echo $wpl_settings_url; ?>&tab=accounts"  class="nav-tab <?php echo $active_tab == 'accounts' ? 'nav-tab-active' : ''; ?>"><?php echo __( 'Accounts', 'wp-lister-for-ebay' ) ?></a>  

        <?php if ( ! is_network_admin() ) : ?>
        <a href="<?php echo $wpl_settings_url; ?>&tab=categories" class="nav-tab <?php echo $active_tab == 'categories' ? 'nav-tab-active' : ''; ?>"><?php echo __( 'Categories', 'wp-lister-for-ebay' ) ?></a>  
        <?php endif; ?>

        <a href="<?php echo $wpl_settings_url; ?>&tab=advanced"   class="nav-tab <?php echo $active_tab == 'advanced' ? 'nav-tab-active' : ''; ?>"><?php echo __( 'Advanced', 'wp-lister-for-ebay' ) ?></a>  

        <?php if ( ! defined('WPLISTER_RESELLER_VERSION') || ( $active_tab == 'developer' ) ) : ?>
        <a href="<?php echo $wpl_settings_url; ?>&tab=developer"  class="nav-tab <?php echo $active_tab == 'developer' ? 'nav-tab-active' : ''; ?>"><?php echo __( 'Developer', 'wp-lister-for-ebay' ) ?></a>  
        <?php endif; ?>


    </h2>  

<?php endif; ?>


<?php /* if ( $active_tab == 'developer' ):
    $active_subtab = empty( $_REQUEST['subtab'] ) ? 'settings' : $_REQUEST['subtab'];
    ?>
    <ul class="subsubsub">
        <li><a href="?page=wplister-settings&tab=developer" class="<?php echo ( $active_subtab == 'settings' ) ? 'current' : ''; ?>"><?php _e( 'Developer Settings', 'wp-lister-for-ebay' ); ?></a> |
        </li><li><a href="?page=wplister-settings&tab=developer&subtab=stockslog" class="<?php echo ( $active_subtab == 'stockslog' ) ? 'current' : ''; ?>"><?php _e( 'Stocks Log', 'wp-lister-for-ebay' ); ?></a></li>
    </ul>
<?php endif;
*/
