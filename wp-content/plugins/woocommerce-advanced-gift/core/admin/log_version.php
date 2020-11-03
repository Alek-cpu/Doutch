<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$message='';
if(isset($_GET['clear']) && $_GET['clear']=='session')
{
	global $wpdb;
	$wpdb->query( "TRUNCATE {$wpdb->prefix}woocommerce_sessions" );
	$result = absint( $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key='_woocommerce_persistent_cart_" . get_current_blog_id() . "';" ) ); // WPCS: unprepared SQL ok.
	wp_cache_flush();
	$message='<div class="updated inline"><p>'.__('Deleted all active sessions','pw_wc_advanced_gift').'</p></div>';
}
			
?>
<div class="pw-form-cnt">
	<div class="pw-form-content">
	<?php echo $message; ?>
<table class="wc_status_table wc_status_table--tools widefat">

	<tbody class="tools">
		<tr class="clear_sessions">
			<th>
				<strong class="name"><?php _e('Clear customer sessions','woocommerce');?></strong>
				<p class="description"><?php echo sprintf(
					'<strong class="red">%1$s</strong> %2$s',
					__( 'Note:', 'woocommerce' ),
					__( 'This tool will delete all customer session data from the database, including current carts and saved carts in the database.', 'woocommerce'));?></p>
			</th>
			<td class="run-tool">
				<a href="<?php echo admin_url('admin.php?page=rule_gift&tab=log_version&pw_action_type=list&clear=session');?>" class="button button-large clear_sessions"><?php _e('Clear','woocommerce');?></a>
			</td>
		</tr>	
	</tbody>
</table>

<pre>
Version 5.0
-------------
<div style="background-color: #c51b1b;color: #fff;">Note : Please update All rule  and Clear all customer session data Before/after update to this version</div>
</pre>	
	</div>
</div>