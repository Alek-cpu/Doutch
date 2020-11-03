<?php

if ( ! defined( 'ABSPATH' ) )
	exit;

// Check if WooCommerce is active
if ( class_exists( 'Woocommerce' ) || class_exists( 'WooCommerce' ) ) {
		
	if ( class_exists( 'BETRS_Table_Rates' ) ) return;

	class BETRS_Table_Rates {

		/*
		 * Table Rate Method ID
		 */
		private $table_rate_id;

		/*
		 * Table Rates Options Class
		 */
		private $table_rate_options;

		/*
		 * Table Rates Options Class
		 */
		private $saved_table_rates = array();

	    /*
	     * List of cost options
	     */
	    public $cost_ops = array();

	    /*
	     * List of unit types for 'multiplied by' cost option
	     */
	    public $cost_units_multi = array();

	    /*
	     * List of unit types for 'every' cost option
	     */
	    public $cost_units_every = array();

	    /*
	     * List of unit types
	     */
	    public $dimension_types = array();

	    /*
	     * List of Conditional Statements
	     */
	    public $conditional_statements = array();

	    /*
	     * Secondary rules for Conditional Statements
	     */
	    public $secondary_statements = array();


		/**
		 * Constructor.
		 */
		public function __construct( $shipping_method = null ) {

			// Save table of rates
	   		//add_action( 'woocommerce_update_options_shipping_' . $this->table_rate_id, array( $this, 'process_table_rates' ) );
	   		//add_action( 'admin_head', array( $this, 'init_variables' ) );

	   		$this->init_conditions();

	   		add_action( 'load-woocommerce_page_wc-settings', array( $this, 'export_table_rows' ) );

	   		// add ajax commands
			add_action( 'wp_ajax_betrs_add_table_rates_row', array( $this, 'add_table_rates_row' ) );
			add_action( 'wp_ajax_betrs_add_extra_costs_op', array( $this, 'add_extra_costs_op' ) );
			add_action( 'wp_ajax_betrs_add_extra_conditions_op', array( $this, 'add_extra_conditions_op' ) );
			add_action( 'wp_ajax_betrs_add_costs_op_details', array( $this, 'add_costs_op_details' ) );
			add_action( 'wp_ajax_betrs_add_conds_op_details', array( $this, 'add_conds_op_details' ) );
			add_action( 'wp_ajax_betrs_generate_option_slug', array( $this, 'generate_option_slug' ) );
        	add_action( 'wp_ajax_betrs_add_table_costs_row', array( $this, 'add_table_costs_row' ) );
        	add_action( 'wp_ajax_betrs_export_table', array( $this, 'export_table_rows' ) );
        	add_action( 'wp_ajax_betrs_import_table', array( $this, 'import_table_rows' ) );
		}


		/**
		 * Setup & display table structure
		 */
		public function init_conditions() {

	        $this->cost_ops = apply_filters( 'betrs_shipping_cost_options', array(
	                ''          => get_woocommerce_currency_symbol(),
	                '%'         => '%',
	                'x'         => __( 'multiplied by', 'be-table-ship' ),
	                'every'     => __( 'for every', 'be-table-ship' ),
	            ) );

	        $this->cost_units_every = apply_filters( 'betrs_shipping_cost_units_every', array(
	                'subtotal'     => get_woocommerce_currency_symbol(),
	                'weight'    => get_option( 'woocommerce_weight_unit' ),
	                'dimensions'=> get_option( 'woocommerce_dimension_unit' ),
	                'quantity'     => __( 'Item(s)', 'be-table-ship' ),
	            ) );

	        $this->cost_units_multi = apply_filters( 'betrs_shipping_cost_units_multiplied', array(
	                'weight'    => __( 'Weight', 'woocommerce' ),
	                'length'    => __( 'Length', 'woocommerce' ),
	                'width'     => __( 'Width', 'woocommerce' ),
	                'height'    => __( 'Height', 'woocommerce' ),
	                'area'      => __( 'Surface Area', 'be-table-ship' ),
	                'volume'    => __( 'Volume', 'be-table-ship' ),
	                'quantity'     => __( 'Item Quantity', 'be-table-ship' ),
	            ) );

	        $this->dimension_types = apply_filters( 'betrs_shipping_dimension_types', array(
	                'length'    => __( 'Length', 'woocommerce' ),
	                'width'     => __( 'Width', 'woocommerce' ),
	                'height'    => __( 'Height', 'woocommerce' ),
	                'area'      => __( 'Surface Area', 'be-table-ship' ),
	                'volume'    => __( 'Volume', 'be-table-ship' ),
	            ) );

	        $this->conditional_statements = apply_filters( 'betrs_shipping_cost_conditionals', array(
	                'subtotal'  => __( 'Subtotal', 'woocommerce' ),
	                'quantity'  => __( 'Quantity', 'woocommerce' ),
	                'weight'    => __( 'Weight', 'woocommerce' ),
	                'height'    => __( 'Height', 'woocommerce' ),
	                'width'     => __( 'Width', 'woocommerce' ),
	                'length'    => __( 'Length', 'woocommerce' ),
	                'area'      => __( 'Surface Area', 'be-table-ship' ),
	                'volume'    => __( 'Volume', 'be-table-ship' ),
	                's_class'   => __( 'Shipping Class', 'woocommerce' ),
	                'product'   => __( 'Products', 'woocommerce' ),
	                'category'  => __( 'Categories', 'woocommerce' ),
	                'dates'     => __( 'Date Range', 'be-table-ship' ),
	                'times'     => __( 'Time', 'be-table-ship' ),
	                'dayweek'   => __( 'Day of Week', 'be-table-ship' ),
	                'coupon'    => __( 'Coupon', 'be-table-ship' ),
	            ) );

	        $this->secondary_statements = apply_filters( 'betrs_shipping_cost_conditionals_secondary', array(
	            'greater_than'  => array(
	                                    'title'         => __( 'Greater than', 'be-table-ship' ) . ' (>=)',
	                                    'conditions'    => array( 'subtotal', 'quantity', 'weight', 'height', 'width', 'length', 'area', 'volume' ),
	                                    'tertiary'      => 'text',
	                                    ),
	            'less_than'     => array(
	                                    'title'         => __( 'Less than', 'be-table-ship' ) . ' (<=)',
	                                    'conditions'    => array( 'subtotal', 'quantity', 'weight', 'height', 'width', 'length', 'area', 'volume' ),
	                                    'tertiary'      => 'text',
	                                    ),
	            'equal_to'      => array(
	                                    'title'         => __( 'Equal to', 'be-table-ship' ),
	                                    'conditions'    => array( 'subtotal', 'quantity', 'weight', 'height', 'width', 'length', 'area', 'volume' ),
	                                    'tertiary'      => 'text',
	                                    ),
	            'includes'      => array(
	                                    'title'         => __( 'Includes', 'be-table-ship' ),
	                                    'conditions'    => array( 's_class', 'product', 'category', 'coupon' ),
	                                    'tertiary'      => 'select',
	                                    ),
	            'excludes'      => array(
	                                    'title'         => __( 'Excludes', 'be-table-ship' ),
	                                    'conditions'    => array( 's_class', 'product', 'category', 'coupon' ),
	                                    'tertiary'      => 'select',
	                                    ),
	            ) );

	    }


		/**
		 * Setup & display table structure
		 */
		public function init_variables( $rowID = null ) {

			if( empty( $this->table_rate_options ) ) {
	    		$this->table_rate_options = new BETRS_Table_Options();
	    	}

			if( ! empty( $rowID ) ) {
	    		$this->table_rate_options->set_row_id( intval( $rowID ) );
	    	}
	    }


		/**
		 * Get Table Rate Options Class
		 */
		public function get_table_rate_ops_class() {
			$this->init_variables();

			return $this->table_rate_options;
		}


		/**
		 * Setup & display table structure
		 */
		public function display( $row_count = null, $rowID = 0 ) {

			do_action( 'betrs_before_table_rates_parent' );

			if( $row_count != null || ! empty( $this->saved_table_rates ) ) :
?>
<div id="BETRS-table-rates-parent">

	<?php $this->display_rows( $row_count, $rowID ); ?>

</div>
<div id="BETRS-table-rates-footer">

	<?php $this->display_footer(); ?>

</div>
<?php
			else :
?>
<h4 style="text-align:center;">
	<?php _e( 'How many shipping options are you giving your customers?', 'be-table-ship' ); ?>
	<select id="betrs-shipping_options-setup">
		<option></option>
		<?php for($n=1;$n<11;$n++) echo '<option>' . $n . '</option>'; ?>
	</select>
</h4>
<?php
			endif;

			do_action( 'betrs_after_table_rates_parent' );
		}


		/**
		 * Setup & display table structure
		 */
		public function display_rows( $row_count = null, $rowID = 0 ) {

			if( $row_count != null ) {
				for( $n = 0; $n < $row_count; $n++) {
					$this->single_row( array(), $rowID );
					$rowID++;
				}
			} elseif( is_array( $this->saved_table_rates ) && ! empty( $this->saved_table_rates ) ) {

				foreach( $this->saved_table_rates['settings'] as $row ) {
					$this->single_row( $row, $row['option_id'] );
					$rowID++;
				}
			} else
				echo '<p class="no-items">' . __( 'No items found.' ) . '</p>';
		}


		/**
		 * Setup & display table structure
		 */
		public function display_footer() {
?>
<div class="footer-ops">
	<span><a href="#" class="button add" id="add_table_rates_row"><?php _e( 'Add Another Option', 'be-table-ship' ); ?></a></span>
	<br class="clear" />
</div>
<?php
		}


		/**
		 * Generate new row for table
		 */
		public function single_row( $item, $rowID = 0 ) {
			global $betrs_shipping;

			if( $rowID == 0 ) $rowID++;

			// Initialize necessary variables
			$this->init_variables( $rowID );
			if( is_array( $item ) && ! empty( $item ) ) {
				if( is_array( $item['rows'] ) ) {
					foreach( $item['rows'] as $key => $value ) {
						$item['rows'][$key]['option_ID'] = $item['option_id'];
						$item['rows'][$key]['row_ID'] = $key;
					}
					$this->table_rate_options->items = $item['rows'];
					$this->table_rate_options->prepare_items();
				}
			}

			// setup variable defaults
			$item_title			= ( isset( $item['title'] ) ) ? sanitize_text_field( $item['title'] ) : '';
			$item_default		= ( isset( $item['default'] ) ) ? sanitize_title( $item['default'] ) : 'off';
			$item_hide_ops 		= ( isset( $item['hide_ops'] ) ) ? sanitize_title( $item['hide_ops'] ) : 'off';
			$item_disable_op	= ( isset( $item['disable_op'] ) ) ? sanitize_title( $item['disable_op'] ) : 'off';
			$item_combine_desc	= ( isset( $item['combine_desc'] ) ) ? sanitize_title( $item['combine_desc'] ) : 'off';
			$item_recursive 	= ( isset( $item['recursive'] ) ) ? sanitize_title( $item['recursive'] ) : 'off';
?>
<div class="single-row" data-row_id='<?php echo $rowID; ?>'>
	<h4 class="shipping-headline">Shipping Option #<?php echo $rowID; ?>
		<span><a href="#" class="remove-shipping-option" alt="<?php _e( 'Remove Shipping Option', 'be-table-ship' ); ?>" title="<?php _e( 'Remove Shipping Option', 'be-table-ship' ); ?>"></a></span></h4>
	<div class="single-row-left">
		<div class="titlewrap">
			<label>
				<?php _e( 'Shipping Option Title', 'be-table-ship' ); ?>
				<input type="text" name="option_title[<?php echo $rowID; ?>]" spellcheck="true" autocomplete="off" value="<?php echo stripslashes( $item_title ); ?>" placeholder="<?php _e( 'Enter the title customers will see (required)', 'be-table-ship' ); ?>" />
			</label>
			<input type="hidden" name="option_id[<?php echo $rowID; ?>]" value="<?php echo $rowID; ?>" />
		</div>
		<?php $this->table_rate_options->display(); ?>
	</div>
	<div class="single-row-right">
		<div class="additional-settings">
			<label><?php _e( 'Additional Settings', 'be-table-ship' ); ?></label>
			<div class="clear">
				<h5><?php _e( 'Default Selection', 'be-table-ship' ); ?></h5>
				<div class="onoffswitch">
				    <input type="checkbox" name="default_select[<?php echo $rowID; ?>]" class="onoffswitch-checkbox" id="default_switch[<?php echo $rowID; ?>]" <?php checked( 'on', $item_default, true ); ?>>
				    <label class="onoffswitch-label" for="default_switch[<?php echo $rowID; ?>]">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>
			</div>
			<div class="clear">
				<h5><?php _e( 'Hide Other Options', 'be-table-ship' ); ?></h5>
				<div class="onoffswitch">
				    <input type="checkbox" name="hide_ops[<?php echo $rowID; ?>]" class="onoffswitch-checkbox" id="hide_switch[<?php echo $rowID; ?>]" <?php checked( 'on', $item_hide_ops, true ); ?>>
				    <label class="onoffswitch-label" for="hide_switch[<?php echo $rowID; ?>]">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>
			</div>
			<div class="clear">
				<h5><?php _e( 'Disable Option', 'be-table-ship' ); ?></h5>
				<div class="onoffswitch">
				    <input type="checkbox" name="disable_op[<?php echo $rowID; ?>]" class="onoffswitch-checkbox" id="disable_switch[<?php echo $rowID; ?>]" <?php checked( 'on', $item_disable_op, true ); ?>>
				    <label class="onoffswitch-label" for="disable_switch[<?php echo $rowID; ?>]">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>
			</div>
			<div class="clear">
				<h5><?php _e( 'Combine Descriptions', 'be-table-ship' ); ?></h5>
				<div class="onoffswitch">
				    <input type="checkbox" name="combine_desc[<?php echo $rowID; ?>]" class="onoffswitch-checkbox" id="combine_switch[<?php echo $rowID; ?>]" <?php checked( 'on', $item_combine_desc, true ); ?>>
				    <label class="onoffswitch-label" for="combine_switch[<?php echo $rowID; ?>]">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>
			</div>
			<!-- COMING SOON -->
			<div class="clear" style="display:none;">
				<h5><?php _e( 'Recursive Pricing', 'be-table-ship' ); ?></h5>
				<div class="onoffswitch">
				    <input type="checkbox" name="recursive_op[<?php echo $rowID; ?>]" class="onoffswitch-checkbox" id="recursive_switch[<?php echo $rowID; ?>]" <?php checked( 'on', $item_recursive, true ); ?>>
				    <label class="onoffswitch-label" for="recursive_switch[<?php echo $rowID; ?>]">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>
			</div>
			<?php do_action( 'betrs_additional_rate_settings', $rowID, $item ); ?>
		</div>
	</div>
	<div style="clear:both;"></div>
	<div class="option-sorting">
		<a href="#" class="betrs-move-option-up" title="<?php _e( 'Move Option Up', 'betrs_shipping' ); ?>"><span class="dashicons dashicons-arrow-up-alt2"></span></a>
		<a href="#" class="betrs-move-option-down" title="<?php _e( 'Move Option Down', 'betrs_shipping' ); ?>"><span class="dashicons dashicons-arrow-down-alt2"></span></a>
	</div>
</div>
<?php
		}


		/**
		 * Generate new row for table
		 */
		public function add_table_rates_row() {

			// determine starting row ID
			$rowID = isset( $_POST['rowID'] ) ? (int) $_POST['rowID'] : 1;

			// Initialize necessary classes
			$this->init_variables();
			
			if( isset( $_POST['optionCount'] ) )
				$this->display( (int) $_POST['optionCount'], $rowID );
			else
				$this->single_row( array(), $rowID );

			die();
		}


		/**
		 * Save table rates
		 */
		public function process_table_rates( $options_save_name ) {
			global $betrs_shipping;

			// Initialize necessary variables
			$this->init_variables();

			if( isset( $_POST['option_title'] ) && is_array( $_POST['option_title'] ) ) {
				// create save variable
				$table_rates_saving = array();

				foreach ( $_POST['option_title'] as $key => $title ) {
					$key = (int) $key;

					$table_rates_saving[ $key ] = array(
						'title'			=> apply_filters( 'betrs_save_shipping_rate_label', sanitize_text_field( $title ), (int) $_POST['option_id'][ $key ], sanitize_title( $options_save_name ) ),
						'option_id'		=> (int) $_POST['option_id'][ $key ],
						'default'		=> ( isset( $_POST['default_select'][ $key ] ) ) ? 'on' : 'off',
						'hide_ops'		=> ( isset( $_POST['hide_ops'][ $key ] ) ) ? 'on' : 'off',
						'disable_op'	=> ( isset( $_POST['disable_op'][ $key ] ) ) ? 'on' : 'off',
						'combine_desc'	=> ( isset( $_POST['combine_desc'][ $key ] ) ) ? 'on' : 'off',
						'recursive'		=> ( isset( $_POST['recursive_op'][ $key ] ) ) ? 'on' : 'off',
						'rows'			=> array(),
						);

					if( isset( $_POST['option_description'][$key] ) && is_array( $_POST['option_description'][$key] ) ) {
						
						foreach ( $_POST['option_description'][$key] as $r_key => $desc ) {
							// process saved conditions
							$saved_conditions = array();

							if( isset( $_POST['cond_type'][ $key ][ $r_key ] ) && is_array( $_POST['cond_type'][ $key ][ $r_key ] ) ) {
								foreach( $_POST['cond_type'][ $key ][ $r_key ] as $c_key => $cond ) {
									// sanitize the first and second conditional entries
									$cond_type_processed = sanitize_title( $_POST['cond_type'][ $key ][ $r_key ][ $c_key ] );
									$cond_secondary_processed = sanitize_text_field( $_POST['cond_secondary'][ $key ][ $r_key ][ $c_key ] );

									// sanitize tertiary value
									if( isset( $_POST['cond_tertiary'][ $key ][ $r_key ][ $c_key ] ) && is_array( $_POST['cond_tertiary'][ $key ][ $r_key ][ $c_key ] ) ) {
										$cond_tertiary_processed = array_map( 'intval', $_POST['cond_tertiary'][ $key ][ $r_key ][ $c_key ] );
									} else {
										$cond_tertiary_processed = sanitize_text_field( $_POST['cond_tertiary'][ $key ][ $r_key ][ $c_key ] );
										// sanitize prices, weight, and dimensions according to locale
						                if( $cond_type_processed == 'subtotal' ) {
						                    $cond_tertiary_processed = wc_format_localized_price( $cond_tertiary_processed );
						                } elseif( $cond_type_processed == 'weight' ) {
						                    $cond_tertiary_processed = floatval( $cond_tertiary_processed );
						                } elseif( array_key_exists( $cond_type_processed, $this->table_rate_options->get_dimensions_types() ) ) {
						                    $cond_tertiary_processed = floatval( $cond_tertiary_processed );
						                }
									}

									$saved_conditions[ $c_key ] = array(
										'cond_type'					=> $cond_type_processed,
										'cond_secondary'			=> $cond_secondary_processed,
										'cond_tertiary'				=> $cond_tertiary_processed,
										);
								}
							}

							// process saved costs
							$saved_costs = array();
							if( isset( $_POST['cost_value'][ $key ][ $r_key ] ) && is_array( $_POST['cost_value'][ $key ][ $r_key ] ) && count( $_POST['cost_value'][ $key ][ $r_key ] ) ) {
								foreach( $_POST['cost_value'][ $key ][ $r_key ] as $c_key => $cost ) {
									$saved_costs[ $c_key ] = array(
										'cost_value'				=> wc_format_decimal( $cost ),
										'cost_type'					=> sanitize_text_field( $_POST['cost_type'][ $key ][ $r_key ][ $c_key ] ),
										'cost_op_extra'				=> sanitize_title( $_POST['cost_op_extra'][ $key ][ $r_key ][ $c_key ] ),
										'cost_op_extra_val'			=> wc_format_decimal( $_POST['cost_op_extra_val'][ $key ][ $r_key ][ $c_key ] ),
										'cost_op_extra_secondary'	=> sanitize_title( $_POST['cost_op_extra_secondary'][ $key ][ $r_key ][ $c_key ] ),
										);
								}
							}

							// compile all options for this row
							$table_rates_saving[ $key ]['rows'][$r_key] = array(
								'conditions'	=> $saved_conditions,
								'costs'			=> $saved_costs,
								'description'	=> apply_filters( 'betrs_save_shipping_rate_description', wp_kses( $desc, $betrs_shipping->allowedtags ), (int) $_POST['option_id'][ $key ], sanitize_title( $options_save_name ), $r_key ),
								);
						}
					}
				}

				$table_rates_saving = apply_filters( 'betrs_processed_table_rates_settings', $table_rates_saving );
				$final_save = array( 'mode' => 'guided', 'settings' => (array) $table_rates_saving );
				update_option( sanitize_title( $options_save_name ), $final_save );
			}

		}


	    /**
	     * add shipping option to single_row options
	     */
	    public function add_table_costs_row() {

	    	// Exit if no option ID is provided
	    	if( ! isset( $_POST['optionID'] ) ) die();
	    	if( ! isset( $_POST['rowID'] ) ) die();

			// Initialize necessary variables
			$this->init_variables();
	    	$option_ID = (int) $_POST['optionID'];
	    	$row_ID = (int) $_POST['rowID'];

	        // Add new row to the opion's table
	        $item = array( 'option_ID' => $option_ID, 'row_ID' => $row_ID );
	        $this->table_rate_options->single_row( $item );

	        die();
	    }


	    /**
	     * add another cost option settings box
	     */
	    public function add_extra_costs_op() {
	        
	    	// Exit if no option ID is provided
	    	if( ! isset( $_POST['optionID'] ) ) die();
	    	if( ! isset( $_POST['rowID'] ) ) die();

			// Initialize necessary variables
			$this->init_variables();
	    	$option_ID = (int) $_POST['optionID'];
	    	$row_ID = (int) $_POST['rowID'];

	        // Add new row to the cost section
	        $item = array( 'option_ID' => $option_ID, 'row_ID' => $row_ID );
	        echo $this->table_rate_options->generate_cost_section( $item, $option_ID, $row_ID );

	        die();
	    }


	    /**
	     * add another cost option settings box
	     */
	    public function add_extra_conditions_op() {

	    	// Exit if no option ID is provided
	    	if( ! isset( $_POST['optionID'] ) ) die();
	    	if( ! isset( $_POST['rowID'] ) ) die();

			// Initialize necessary variables
			$this->init_variables();
	    	$option_ID = (int) $_POST['optionID'];
	    	$row_ID = (int) $_POST['rowID'];

	        // Add new row to the conditions section
	        $item = array( 'option_ID' => $option_ID, 'row_ID' => $row_ID );
	        echo $this->table_rate_options->generate_conditions_section( $item, $option_ID, $row_ID );

	        die();
	    }


	    /**
	     * add additional required cost settings
	     */
	    public function add_costs_op_details() {

	    	if( ! isset( $_POST['selected'] ) ) die();

			// Initialize necessary variables
			$this->init_variables();
	    	$option_ID = ( isset( $_POST['optionID'] ) ) ? (int) $_POST['optionID'] : '';
	    	$row_ID = ( isset( $_POST['rowID'] ) ) ? (int) $_POST['rowID'] : '';

	        // Update options in the cost section
	        $item = array( 'option_ID' => $option_ID, 'row_ID' => $row_ID );
	        echo $this->table_rate_options->generate_cost_section_extras( sanitize_title( $_POST['selected'] ), $item, $option_ID, $row_ID );

	        die();
	    }


	    /**
	     * add additional required cost settings
	     */
	    public function add_conds_op_details() {

	    	if( ! isset( $_POST['selected'] ) ) exit;

	    	// Exit if no option ID is provided
	    	if( ! isset( $_POST['optionID'] ) ) die();
	    	if( ! isset( $_POST['rowID'] ) ) die();

			// Initialize necessary variables
			$this->init_variables();
	    	$option_ID = (int) $_POST['optionID'];
	    	$row_ID = (int) $_POST['rowID'];
	    	$cond_ID = ( isset( $_POST['condID'] ) ) ? (int) $_POST['condID'] : 0;

	        // Update options in the conditions section
	        $item = array( 'option_ID' => $option_ID, 'row_ID' => $row_ID );
	        echo $this->table_rate_options->generate_conditions_section_extras( sanitize_title( $_POST['selected'] ), $item, $row_ID, $option_ID, $cond_ID );

	        die();
	    }


	    /**
	     * setup the instance ID for saving purposes
	     */
	    public function set_saved_table_rates( $saved_table_rates ) {

	    	$this->saved_table_rates = $saved_table_rates;
	    }


	    /**
	     * setup the instance ID for saving purposes
	     */
	    function import_table_rows() {
	    	global $betrs_shipping;

	    	// return error if data not included
	    	if( ! isset( $_POST['csvFile'] ) )
	    		die('0');

	    	// Exit if no option ID is provided
	    	if( ! isset( $_POST['optionID'] ) ) die('0');
	    	if( ! isset( $_POST['rowID'] ) ) die('0');

	    	// sanitize incoming data
	    	$csvText = explode( PHP_EOL, stripslashes( trim( $_POST['csvFile'] ) ) );
	    	$csvClean = array_map( 'sanitize_text_field', $csvText );
	    	$csvArray = array_map( 'str_getcsv', $csvClean );

			// Initialize necessary variables
			$this->init_variables();
	    	$option_ID = (int) $_POST['optionID'];
	    	$row_ID = (int) $_POST['rowID'];

			// setup data array for options table class
			$compiled_ar = array();
			foreach( $csvArray as $key => $csv ) {
				// move to next row if first value is not a row ID
				if( ! is_numeric( $csv[0] ) )
					continue;

				$rowID = (int) $csv[0];
				if( ! array_key_exists( $rowID, $compiled_ar ) )
					$compiled_ar[ $rowID ] = array(
						'conditions'	=> array(),
						'costs'			=> array(),
						'description'	=> "",
						'option_ID'		=> $option_ID,
						);

				switch ( strtolower( sanitize_title( $csv[1] ) ) ) {
					case 'cond':
						$csv = array_pad( $csv, 5, '' );
						$compiled_ar[ $rowID ]['conditions'][] = array(
							'cond_type'			=> sanitize_title( $csv[ 2 ] ),
							'cond_secondary'	=> sanitize_text_field( $csv[ 3 ] ),
							'cond_tertiary'		=> sanitize_text_field( $csv[ 4 ] )
							);
						break;
					
					case 'cost':
						$csv = array_pad( $csv, 7, '' );
						$compiled_ar[ $rowID ]['costs'][] = array(
							'cost_value'				=> sanitize_text_field( $csv[ 2 ] ),
							'cost_type'					=> sanitize_text_field( $csv[ 3 ] ),
							'cost_op_extra'				=> sanitize_title( $csv[ 4 ] ),
							'cost_op_extra_val'			=> sanitize_text_field( $csv[ 5 ] ),
							'cost_op_extra_secondary'	=> sanitize_title( $csv[ 6 ] ),
							);
						break;
					
					case 'desc':
						$csv = array_pad( $csv, 3, '' );
						$compiled_ar[ $rowID ]['description'] = wp_kses( $csv[ 2 ], $betrs_shipping->allowedtags );
						break;
					
					default:
						$default = apply_filters( 'betrs_import_table_row_types', null, strtolower( sanitize_title( $csv[1] ) ), $csv );
						if( $default != null && is_array( $default ) ) $compiled_ar[ $rowID ] = $default;
						break;
				}

			}

			foreach( $compiled_ar as $key => $value) {
				$value['row_ID'] = $row_ID;
				$this->table_rate_options->single_row( $value );
				$row_ID++;
			}

	    	die();
	    }


	    /**
	     * setup the instance ID for saving purposes
	     */
	    function export_table_rows() {
	    	global $betrs_shipping, $current_screen;

	    	// return error if data not included
	    	if( ! is_admin() || ! isset( $_REQUEST['exporter_id'] ) || ! isset( $_REQUEST['instance_id'] ) )
	    		return;

	        // kill page if user does not have permissions for accessing this feature
	        if ( ! current_user_can( 'manage_options' ) )
	            wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );

	    	$exporter_id = intval( $_REQUEST['exporter_id'] );
	    	$instance_id = intval( $_REQUEST['instance_id'] );
			$zone = WC_Shipping_Zones::get_zone_by( 'instance_id', $instance_id );

			// return if zone could not be found
			if( ! $zone ) return;

			$shipping_methods = $zone->get_shipping_methods();
			$shipping_method = $shipping_methods[ $instance_id ];
			$settings = get_option( $shipping_method->get_options_save_name() );
			$table = ( $settings['settings'][ $exporter_id ] ) ? $settings['settings'][ $exporter_id ] : false;

			// return if table could not be found
			if( ! $table ) return;

	    	// unserialize the form data for processing
	    	$compiled_ar = array();
			foreach( $table['rows'] as $row_key => $row_value ) {
				$key = $row_key + 1;

				// add conditions to csv data
				foreach( $row_value['conditions'] as $cond_ID => $cond ) {
					$compiled_ar[] = array( 
						$key, 
						'Cond', 
						sanitize_title( $cond['cond_type'] ),
						sanitize_text_field( stripslashes( $cond['cond_secondary'] ) ),
						( ! is_array( $cond['cond_tertiary'] ) ) ? sanitize_text_field( $cond['cond_tertiary'] ) : json_encode( array_map( 'intval', $cond['cond_tertiary'] ) ),
						);
				}

				// add costs to csv data
				foreach( $row_value['costs'] as $cost_ID => $cost ) {
					$compiled_ar[] = array( 
						$key,
						'Cost',
						sanitize_text_field( $cost['cost_value'] ),
						sanitize_text_field( $cost['cost_type'] ),
						sanitize_title( $cost['cost_op_extra'] ),
						sanitize_text_field( $cost['cost_op_extra_val'] ),
						sanitize_title( $cost['cost_op_extra_secondary'] ),
						);
				}

				// add descriptions to csv data
				$compiled_ar[] = array( $key, 'Desc', wp_kses( stripslashes( $row_value['description'] ), $betrs_shipping->allowedtags ) );

			}

	    	// format CSV file data for exporting
	    	$this->outputCSV( $compiled_ar, $exporter_id );

	    	die();
	    }


	    /**
	     * Create PHP file for CSV file output
	     */
	    function outputCSV( $data, $option_ID ) {
	    	$option_ID = (int) $option_ID;
    		$filename = "betrs_backup-option_" . $option_ID;

			//header('Content-Encoding: UTF-8');
			header("Content-type: text/csv; charset=UTF-8");
		    header("Content-Disposition: attachment; filename={$filename}.csv");
		    header("Pragma: no-cache");
		    header("Expires: 0");

			echo "\xEF\xBB\xBF";

	    	// Create a stream opening it with write mode
        	$stream = fopen( 'php://output', 'w' );

        	// If stream cannot be opened, send error code
        	if( ! $stream ) {
        		$error = 'Unable to export table to a CSV file. ERROR: ';

        		if( ! ini_get('allow_url_fopen') ) {
        			$error .= 'The php ini setting "allow_url_fopen" is not enabled.';
        		}elseif( ! in_array( 'php', stream_get_wrappers() ) ) {
        			$error .= '"PHP" is not a Registered PHP Stream in your PHP configuration.';
        		}elseif( ! in_array( 'data', stream_get_wrappers() ) ) {
        			$error .= '"Data" is not a Registered PHP Stream in your PHP configuration.';
        		} elseif( version_compare( PHP_VERSION, '5.6.0', '<' ) ) {
        			$error .= 'Requires PHP version 5.6 or newer.';
        		} else {
        			$error .= 'Unknown.';
        		}

        		die( $error );
        	}

	        // Iterate over the data, writting each line to the text stream
			foreach( $data as $val ) {
			    fputcsv( $stream, $val );
			}

			// Close the stream 
			fclose($stream);

		    //$this->formatCSV( $data );
		}


	    /**
	     * Format data for CSV file output
	     */
	    function formatCSV( $data ) {
	    	if( ! is_array( $data ) ) die('0');
			// Rewind the stream
			rewind($stream);

			// You can now echo it's content
			echo stream_get_contents($stream);
	    }

	    function get_dimensions_types() {

	        return $this->dimension_types;
	    }
	}

}

?>