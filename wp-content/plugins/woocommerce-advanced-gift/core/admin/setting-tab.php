<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class pw_woocommerc_flashsale_WC_Admin_Tabs {

	public $tab; 
	public $options; 
	
	/**
	 * Constructor
	 */
	public function __construct() {
		
		$this->options = $this->pw_woocommerce_brands_plugin_options();
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'pw_woocommerce_brands_add_tab_woocommerce' ),50);
		add_filter( 'woocommerce_page_settings', array( $this, 'pw_woocommerce_brands_add_page_setting_woocommerce' ) );
		add_action( 'woocommerce_update_options_pw_flash_sale', array( $this, 'pw_woocommerce_brands_update_options' ) );

		add_action( 'woocommerce_settings_tabs_pw_flash_sale', array( $this, 'pw_woocommerce_brands_print_plugin_options' ) );
		
		add_action( 'admin_init', array( $this, 'settings_init_brand' ) );
		add_action( 'admin_init', array( $this, 'settings_save_brand' ) );		
	}
	public function pw_woocommerce_brands_add_tab_woocommerce($tabs){
		
		$tabs['pw_flash_sale'] = __('Flash Sale','pw_wc_advanced_gift'); // or whatever you fancy
		return $tabs;
	}
	
	public function settings_init_brand() {
		add_settings_field(
			'woocommerce_product_flashsale_slug',      	// id
			__( 'Product Brand base', 'pw_wc_advanced_gift' ), 	// setting title
			array( $this, 'product_brand_slug_input' ),  // display callback
			'permalink',                 				// settings page
			'optional'                  				// settings section
		);
	}
	public function product_brand_slug_input() {
		$perm = get_option( 'pw_woocommerce_brands_base' );
		//echo $perm;
		?>
		<input name="woocommerce_product_flashsale_slug" type="text" class="regular-text code" value="<?php if ( isset( $perm ) ) echo esc_attr( $perm ); ?>" placeholder="<?php echo _x('product-brand', 'slug', 'pw_wc_advanced_gift') ?>" />
		<?php
	}	

	public function settings_save_brand() {
		if ( ! is_admin() )
			return;
					// We need to save the options ourselves; settings api does not trigger save for the permalinks page
		if ( isset( $_POST['woocommerce_product_flashsale_slug'] )) {
			$perm 	= untrailingslashit( $_POST['woocommerce_product_flashsale_slug'] );	
			update_option( 'pw_woocommerce_brands_base', $perm );	
		}	
	}
	
	
	/**
	 * Update plugin options.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function pw_woocommerce_brands_update_options() {
	global $wp_rewrite;
		foreach( $this->options as $option ) {
			woocommerce_update_options( $option );   
		}
		
	   	$wp_rewrite->flush_rules();		
	}
	
	/**
	 * Add the select for the Woocommerce Brands page in WooCommerce > Settings > Pages
	 * 
	 * @param array $settings
	 * @return array
	 * @since 1.0.0
	 */
	public function pw_woocommerce_brands_add_page_setting_woocommerce( $settings ) {
		unset( $settings[count( $settings ) - 1] );
		
		$settings[] = array(
			'name' => __( 'a Page', 'woocommerce-brands' ),
			'desc' 		=> __( 'Page contents: [pw_woocommerce_brands]', 'pw_wc_advanced_gift' ),
			'id' 		=> 'pw_woocommerce_brands_page_id',
			'type' 		=> 'single_select_page',
			'std' 		=> '',         // for woocommerce < 2.0
			'default' 	=> '',         // for woocommerce >= 2.0
			'class'		=> 'chosen_select_nostd',
			'css' 		=> 'min-width:300px;',
			'desc_tip'	=>  false,
		);
		
		$settings[] = array( 'type' => 'sectionend', 'id' => 'page_options');
		
		return $settings;
	}

	
	
	
	public function pw_woocommerce_brands_print_plugin_options() {

		?>
		<div class="subsubsub_section">
			<br class="clear" />
			<?php foreach( $this->options as $id => $tab ) : ?>
			<div class="section" id="pw_woocommerce_brands_<?php echo $id ?>">
				<?php woocommerce_admin_fields( $this->options[$id] ) ;?>
			</div>
			<?php endforeach;?>
		</div>
		<?php
	}
	
	private function pw_woocommerce_brands_plugin_options() {
		$options['general_settings'] = array(
			array( 'name' => __( 'General Settings', 'pw_wc_advanced_gift' ), 'type' => 'title', 'desc' => '', 'id' => 'pw_woocommerce_brands_general_settings' ),
			array(
				'name'      => __( 'Style Count Down', 'woocommerce-brands' , 'pw_wc_advanced_gift'),
				'desc'      => __( 'Choose Count Down', 'pw_wc_advanced_gift' ),
				'id'        => 'pw_woocommerce_flashsale_countdown',
				'type'      => 'select',
				'class'		=> 'chosen_select',
				'css' 		=> 'min-width:300px;',
				'options'   => array(
					'style1' => __( 'Style 1', 'pw_wc_advanced_gift' ),
					'style2' => __( 'Style 2', 'pw_wc_advanced_gift' ),
					'style3' => __( 'Style 3', 'pw_wc_advanced_gift' ),
				),
				'desc_tip'	=>  true
			),
			array(
				'name'      => __( 'Show Count Down Single', 'pw_wc_advanced_gift' ),
				'desc'      => __( 'Show Count Down Single.', 'pw_wc_advanced_gift'), 
				'id'        => 'pw_woocommerce_flashsale_single_countdown',
				'std' 		=> 'yes',         // for woocommerce < 2.0
				'default' 	=> 'yes',         // for woocommerce >= 2.0
				'type'      => 'checkbox'
			),
			array(
				'name'      => __( 'Show Count Down Archive', 'pw_wc_advanced_gift' ),
				'desc'      => __( 'Show Count Down Archive', 'pw_wc_advanced_gift'), 
				'id'        => 'pw_woocommerce_flashsale_archive_countdown',
				'std' 		=> 'yes',         // for woocommerce < 2.0
				'default' 	=> 'yes',         // for woocommerce >= 2.0
				'type'      => 'checkbox'
			),
			array(
				'title' => __( 'Colour', 'pw_wc_advanced_gift' ),
				'desc' 		=> __( 'The base colour for Dark Skin Colour. Default<code>#414141</code>.','pw_wc_advanced_gift'  ),
				'id' 		=> 'pw_woocommerce_flashsale_color_countdown',
				'type' 		=> 'color',
				'css' 		=> 'width:6em;',
				'default'	=> '#414141',
				'autoload'  => false
			),
			array(
				'name'      => __( 'Font Size', 'woocommerce-brands' , 'pw_wc_advanced_gift'),
				'desc'      => __( 'Font Size For CountDown', 'pw_wc_advanced_gift' ),
				'id'        => 'pw_woocommerce_flashsale_fontsize_countdown',
				'type'      => 'select',
				'class'		=> 'chosen_select',
				'css' 		=> 'min-width:300px;',
				'options'   => array(
					'small' => __( 'Small', 'pw_wc_advanced_gift' ),
					'medium' => __( 'Medium', 'woocommerce-brands' ),
					'large' => __( 'Large', 'pw_wc_advanced_gift' ),
				),
				'desc_tip'	=>  true
			),
			array( 'type' => 'sectionend', 'id' => 'pw_woocommerce_brands_image_settings' )
		);
		
		return apply_filters( 'pw_woocommerce_brands_tab_options', $options );
	}
	


	
}
new pw_woocommerc_flashsale_WC_Admin_Tabs();
?>