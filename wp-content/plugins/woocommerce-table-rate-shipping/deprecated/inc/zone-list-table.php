<?php

/*************************** LOAD THE BASE CLASS ********************************/
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

            if( WOOCOMMERCE_VERSION >= 2.1 && !isset( $woocommerce ) ) 
                $woocommerce = WC();

/************************** CREATE A PACKAGE CLASS ******************************/
class Zone_List_Table extends WP_List_Table {

    public $shipping_zones;
    
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'zone', 
            'plural'    => 'zones',
            'ajax'      => false
        ) );
        $this->shipping_zones = get_option( 'be_woocommerce_shipping_zones' );
        $shipping_zones = get_option( 'be_woocommerce_shipping_zones' );
        
    }
    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav( $which ) {
        if ( $which == "top" ){
            //The code that goes before the table is here
            echo"<span style='line-height:32px;'>To manage the shipping rates for these zones, visit the <a href=\"" . get_bloginfo( 'wpurl' ) . "/wp-admin/admin.php?page=wc-settings&tab=shipping&section=BE_Table_Rate_Shipping\">Table Rate Shipping</a> settings page.</span>";
        }
        if ( $which == "bottom" ){
            //The code that goes after the table is there
            echo"<span style='line-height:32px;'>Drag and drop the table rows to sort the zones by their priority, lowest - highest. Click the <strong>Save Changes</strong> button when finished.</span>";
        }
    }
    function column_default($item, $column_name){
        global $woocommerce;

        switch($column_name){
            case 'status':
                $url = wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_bolder_zone_enabled&zone_id=' . $item['zone_id'] ), 'woocommerce-settings' );
                $return_image = '<p style="text-align:center"><a href="' . $url . '" title="'. __( 'Toggle featured', 'be-table-ship' ) . '">';

                if ($item['zone_enabled'] == '1')
                    $return_image .=  '<img src="' . plugins_url( '../assets/success.png', __FILE__ ) . '" alt="yes" />';
                else
                    $return_image .=  '<img src="' . plugins_url( '../assets/success-off.png', __FILE__ ) . '" alt="no" />';
                return $return_image . "</a></p>";
            case 'order':
                return $item['zone_order'];
            default:
                return "Data Could Not Be Found";
        }
    }
    
    function column_title($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&tab=shipping_zones&action=%s&zone=%s">Edit</a>',$_REQUEST['page'],'edit',$item['zone_id']),
            'delete'    => sprintf('<a href="?page=%s&tab=shipping_zones&action=%s&zone=%s">Delete</a>',$_REQUEST['page'],'delete',$item['zone_id']),
        );
        
        //Return the title contents
        return sprintf('<h3 style="margin:0;">%1$s</h3><small>ID: %2$s</small><br /><i>%3$s</i><br />%4$s</p>',
            /*$1%s*/ $item['zone_title'],
            /*$2%s*/ $item['zone_id'],
            /*$3%s*/ $item['zone_description'],
            /*$4%s*/ $this->row_actions($actions)
        );
    }
    
    function column_locations($item){
        global $woocommerce;

        $countries = $woocommerce->countries->countries;//get_allowed_countries();
        $states = $woocommerce->countries->states;//get_allowed_country_states();
        $return = '';
        switch ($item['zone_type']) {
            case 'everywhere':
                $return .= __('Everywhere','be-table-ship');

                $excluded = (isset($item['zone_except'])) ? sanitize_text_field($item['zone_except']) : '';
                if( isset( $excluded ) && $excluded != '' ) {
                    $i = 0;
                    $return .= '<br /><br/><i>'.__('Except for','be-table-ship').'...</i><br />';
                    $countries_abbr = explode(',', $excluded);
                    $cur_country = "";
                    foreach ($countries_abbr as $value) {
                        $country_state = explode(':',$value);
                        $country = $country_state[0];
                        if($cur_country != $country_state[0]) {
                            if(isset($country_state[1]) && $country_state[1] != "" && $i > 0) $return .= "<br />";
                            if($i > 0) $return .= "<br />";
                            $cur_country = $country_state[0];
                            $return .= "<strong>".$countries[$country]."</strong><br />";
                            $i = 0;
                        }
                        if($i > 0) $return .= ", ";
                        if(count($country_state) > 1) {
                            $return .= $states[$country][$country_state[1]];
                            $i++;
                        }
                    }
                }
                break;
            case 'countries':
                $return_val = "";
                $i = 0;
                $countries_abbr = explode(',', sanitize_text_field($item['zone_country']));
                $cur_country = "";
                foreach ($countries_abbr as $value) {
                    $country_state = explode(':',$value);
                    $country = $country_state[0];
                    if($cur_country != $country_state[0]) {
                        if(isset($country_state[1]) && $country_state[1] != "" && $i > 0) $return_val .= "<br />";
                        if($i > 0) $return_val .= "<br />";
                        $cur_country = $country_state[0];
                        $return_val .= "<strong>".$countries[$country]."</strong><br />";
                        $i = 0;
                    }
                    if($i > 0) $return_val .= ", ";
                    if(count($country_state) > 1) {
                        $return_val .= $states[$country][$country_state[1]];
                        $i++;
                    }
                }
                if( isset( $item['zone_except'] ) && count( $item['zone_except'] != '' ) ) {
                    $excluded_states = (isset($item['zone_except']['states'])) ? sanitize_text_field($item['zone_except']['states']) : '';
                    $excluded_postal = (isset($item['zone_except']['postals'])) ? sanitize_text_field($item['zone_except']['postals']) : '';
                    $i = 0;
                    $cur_country = "";
                    $return_val .= '<br /><br/><i>'.__('Except for','be-table-ship').'...</i><br />';
                    $countries_abbr = explode(',', sanitize_text_field($item['zone_except']['states']));
                    if(count($countries_abbr)) {
                        foreach ($countries_abbr as $state) {
                            $country_state = explode(':',$state);
                            $country = $country_state[0];
                            if($cur_country != $country_state[0]) {
                                if(isset($country_state[1]) && $country_state[1] != "" && $i > 0) $return_val .= "<br />";
                                if($i > 0) $return_val .= "<br />";
                                $cur_country = $country_state[0];
                                $return_val .= "<strong>".$countries[$country]."</strong><br />";
                                $i = 0;
                            }
                            if($i > 0) $return_val .= ", ";
                            if(count($country_state) > 1) {
                                $return_val .= $states[$country][$country_state[1]];
                                $i++;
                            }
                        }
                        $return_val .= "<br />";
                    }
                    if(isset($excluded['postals']) && $excluded['postals'] != '') {
                        $return_val .= "Postal Codes: " . sanitize_text_field($excluded['postals']);
                    }
                }
                $return .= $return_val;
                break;
            case 'postal':
                $country_state = explode(':',$item['zone_country']);
                $country = $country_state[0];
                $state = (count($country_state) > 1) ? $states[$country][$country_state[1]] . " &raquo; " : '';
                $return .= "<strong>".$state."".$countries[$country]."</strong><br />".sanitize_text_field($item['zone_postal']);
                $excluded = (isset($item['zone_except'])) ? sanitize_text_field($item['zone_except']) : '';
                if( isset( $excluded ) && $excluded != '' ) {
                    $i = 0;
                    $return .= '<br /><br/><i>'.__('Except for','be-table-ship').'...</i><br />'.sanitize_text_field($item['zone_except']);
                }
                break;
            default:
                $return .= __("Data Could Not Be Found","woocommerce");
        }

        return $return;
    }


    /** ************************************************************************
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['zone_id']                //The value of the checkbox should be the record's id
        );
    }
    
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'title'     => __('Zone Title','be-table-ship'),
            'locations'    => __('Locations','be-table-ship'),
            'status'  => "<p style=\"text-align:center;margin:0;\">".__('Status','be-table-ship')."</p>"
            // 'order'  => __('Priority Order','be-table-ship')
        );
        return $columns;
    }

    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }
    
    function process_bulk_action() {
        global $wpdb;

        $shipping_zones = $this->shipping_zones;

        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            if(isset($_GET['zone']) && is_numeric($_GET['zone'])) :
                if(!array_key_exists($_GET['zone'], $shipping_zones)) 
                    echo "<div class=\"error\"><p>".__('A zone with the ID provided does not exist', 'be-table-ship').".</p></div>";
                else {
                    $zone_title = $shipping_zones[$_GET['zone']]['zone_title'];
                    unset($shipping_zones[$_GET['zone']]);
                    update_option('be_woocommerce_shipping_zones', $shipping_zones);
                    echo "<div class=\"updated\"><p>".__("The zone titled", 'be-table-ship' ) . " <strong>".$zone_title."</strong> " . __( "has been deleted",'be-table-ship').".</p></div>";
                }
            endif;
        }
    }
    
    function prepare_items() {
    global $wpdb, $_wp_column_headers;
    $screen = get_current_screen();

    /* -- Preparing your query -- */
        $data = (isset($this->shipping_zones) && is_array($this->shipping_zones)) ? array_filter( (array) $this->shipping_zones ) : array();
        $per_page = 9999;

    /* -- Register the Columns -- */
        $columns = $this->get_columns();
        $_wp_column_headers[$screen->id]=$columns;

    /* -- Fetch the items -- */

        $columns = $this->get_columns();
        $hidden = array();
        
        $this->_column_headers = array($columns, $hidden, false);
        
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;
        
    /* -- Register the pagination -- */
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );

    }

    function single_row( $item ) {
        static $row_class = '';
        $row_class = ( $row_class == '' ? ' class="alternate"' : '' );

        echo '<tr' . $row_class . '>';
        echo '<input type="hidden" name="zone_id[]" value="'.$item['zone_id'].'" />';
        echo $this->single_row_columns( $item );
        echo '</tr>';
    }


    static function tt_render_list_page(){
        global $SUCCESS;
        //Create an instance of our package class...
        $zoneListTable = new Zone_List_Table();
        //Fetch, prepare, sort, and filter our data...
        $zoneListTable->prepare_items();
        
        ?>
        <div class="wrap woocommerce">
            
            <div id="icon-users" class="icon32"><br/></div>
            <h2><?php _e( 'Shipping Zones', 'be-table-ship' ); ?> <a href="?page=<?php echo $_REQUEST['page']; ?>&tab=shipping_zones&action=new" class="add-new-h2"><?php _e( 'Add New', 'be-table-ship' ); ?></a></h2>
            </form>

            <?php if( isset($_GET['upgrade']) && $_GET['upgrade'] == 'true' ) : ?>
            <div class="updated" style="font-weight:bold;">
                <p><?php _e('Your zones have been updated', 'be-table-ship' ) . '. ' . __( 'Please test your forms to ensure that everything is in working order','be-table-ship'); ?>.</p>
            </div>
            <?php endif; ?>

            <?php if( isset($_GET['action']) && $_GET['action'] == 'delete' ) : ?>
                <?php if( isset( $SUCCESS ) && $SUCCESS ) : ?>
            <div class="updated" style="font-weight:bold;">
                <p><?php _e('The selected zones have been deleted','be-table-ship'); ?>.</p>
            </div>
                <?php else: ?>
            <div class="error" style="font-weight:bold;">
                <p><?php _e('An error has occurred and the selected zones were not deleted','be-table-ship'); ?>.</p>
            </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
            <form id="zones-filter" method="post" action="">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                <!-- Now we can render the completed list table -->
                <?php $zoneListTable->display() ?>
                <?php wp_nonce_field( 'woocommerce-settings', '_wpnonce', true, true ); ?>
                <p class="submit">
                    <input name="save" class="button-primary" type="submit" value="Save changes" />
                    <input type="hidden" name="subtab" id="last_tab" />
                </p>
            </form>
            
        </div>
        <script>
            jQuery(function() {
                var fixHelperModified = function(e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function(index)
                    {
                      jQuery(this).width($originals.eq(index).width())
                    });
                    return $helper;
                };
                jQuery("#the-list").sortable({
                    helper: fixHelperModified
                }).disableSelection();
            });
        </script>
        <?php
    }


    static function tt_render_edit_page($zone_submit_id=''){
        global $woocommerce, $wpdb;
        $zoneListTable = new Zone_List_Table();
        $shipping_zones = $zoneListTable->shipping_zones;

        $method = $_GET['action'];
        $zone_fields = array();
        $allowed_countries = $woocommerce->countries->get_allowed_countries();
        asort( $allowed_countries );
        
        $zone_fields = array(
            'zone_id' => '',
            'zone_enabled' => '',
            'zone_title' => '',
            'zone_description' => '',
            'zone_type' => '',
            'zone_country' => '',
            'zone_postal' => '',
            'zone_except' => '',
            'zone_order' => '',
            );
        ?>
        <div class="wrap woocommerce">
          
    <?php 
        if( isset( $zone_submit_id ) && $zone_submit_id != '' ) $method = 'edit';
        if($method == 'edit' ) : 
            if( $zone_submit_id == '' ) 
                $zoneID = (int) $_GET['zone'];
            else {
                $_GET['action'] == 'edit';
                $zoneID = $zone_submit_id;
            }

            if(!is_numeric($zoneID) || $zoneID == 0) { echo "<p>" . __( 'A valid zone ID must be supplied', 'be-table-ship' ) . ".</p>"; return; }

            if(!array_key_exists($zoneID, $shipping_zones)) { echo "<p>" . __( 'Sorry, a zone with the given ID could not be found', 'be-table-ship' ) . ".</p>"; return; }

            $zone_fields = $shipping_zones[$zoneID];
        else :
            if( $shipping_zones ) {
                foreach ($shipping_zones as $value) {
                    $max_keys[] = $value['zone_order'];
                }
                $zone_order_max = max($max_keys);
                $zoneID = max(array_keys($shipping_zones))+1;
            } else {
                $zone_order_max = 0;
                $zoneID = 1;
            }
        endif;
    ?>
              
    <div class="error bolder-elements-notice notice">
        <p><strong>As of Table Rate Shipping for WooCommerce 4.0, this settings page has been deprecated.</strong> It will continue to function over the next few updates to help with transition, but eventually it will be removed.
            It is recommended that you establish new Table Rate methods within the WooCommerce Shipping Zones to avoid future shipping complications.</p>
    </div>
            <div id="icon-users" class="icon32"><br/></div>
            <h2><?php if($method == 'edit') : ?>Edit<?php else : ?>Add<?php endif;?> Shipping Zone <a href="?page=<?php echo $_REQUEST['page']; ?>&tab=shipping_zones&action=new" class="add-new-h2">Add New</a></h2>

            <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
            <form id="zone-editor" method="post" action="">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                <input type="hidden" name="zone_id" value="<?php echo $zoneID ?>" />
                <!-- Now we can render the completed list table -->
                <h3><?php _e('Zone Details', 'be-table-ship'); ?></h3>
                <table class="form-table">
                <tr valign="top">
                    <th scope="row" class="titledesc"><label><?php _e('Enabled', 'be-table-ship'); ?></label></th>
                    <td><input type="checkbox" name="zone_enabled" <?php if($zone_fields['zone_enabled'] == 1 || $method != 'edit') echo "checked=checked"; ?> /> <?php _e( 'Enable &#47; Disable the use of this zone', 'be-table-ship'); ?></td>
                    </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc"><label><?php _e('Title', 'be-table-ship'); ?></label></th>
                    <td><input type="text" name="zone_title" style="min-width:450px;" value="<?php if(isset($zone_fields['zone_title'])) echo $zone_fields['zone_title']; ?>" /></td>
                    </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc"><label><?php _e('Description', 'be-table-ship'); ?></label></th>
                    <td><textarea style="width:450px;height:75px" name="zone_description"><?php if(isset($zone_fields['zone_description'])) echo $zone_fields['zone_description']; ?></textarea><br /><?php _e( 'This is an optional field to provide admins a brief description of this zone', 'be-table-ship' ) . '. ' . __( 'This will NOT appear on any shop page', 'be-table-ship' ); ?></td>
                    </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc"><label><?php _e('Type', 'be-table-ship'); ?></label></th>
                    <td><select style="min-width:450px;" class="chosen_select" id="zone_type" name="zone_type">
                        <option value="everywhere"<?php if ($zone_fields['zone_type'] == 'everywhere') echo " selected=selected"; ?>><?php _e('Everywhere', 'be-table-ship'); ?></option>
                        <option value="countries"<?php if ($zone_fields['zone_type'] == 'countries') echo " selected=selected"; ?>><?php _e('Countries &#47; States', 'be-table-ship'); ?></option>
                        <option value="postal"<?php if ($zone_fields['zone_type'] == 'postal') echo " selected=selected"; ?>><?php _e('Postal Code', 'be-table-ship'); ?></option>
                    </select></td>
                    </tr>
                </table>
                <h3><?php _e('Locations', 'be-table-ship'); ?></h3>
                <table id="location_everywhere" class="form-table" style="display:none">
                <tr valign="top">
                    <th scope="row" class="titledesc"><label><?php _e('Countries', 'be-table-ship'); ?></label></th>
                    <td>All Allowed Countries as set on the 'General' tab </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><label><?php _e('Except', 'be-table-ship'); ?>...</label></th>
                        <td class="forminp">
                            <select multiple="multiple" name="location_everywhere_except[]" style="width:450px;" data-placeholder="<?php _e( 'Choose countries &#47; states&hellip;', 'be-table-ship' ); ?>" title="Country" class="chosen_select">
                                <?php
                                    if ( $allowed_countries ) {
                                        $selections = ( isset( $zone_fields['zone_except'] ) && is_array( $zone_fields['zone_except'] ) ) ? $zone_fields['zone_except'] : explode(',', $zone_fields['zone_except']);
                                        foreach ( $allowed_countries as $key => $val ) {
                                            echo '<option value="'.$key.'" ' . selected( in_array( $key, $selections ), true, false ).'>' . $val . '</option>';
                                            $allowed_states = $woocommerce->countries->get_states($key);
                                            if( $allowed_states ) {
                                                foreach ($allowed_states as $skey => $sval) {
                                                    echo '<option value="'.$key.':'.$skey.'" ' . selected( in_array( $key.':'.$skey, $selections ), true, false ).'>&#009;' . $val . ' &mdash; ' . $sval . '</option>';
                                                }
                                            }
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <table id="location_countries" class="form-table" style="display:none">
                    <tr valign="top">
                        <th scope="row" class="titledesc"><label><?php _e('Countries', 'be-table-ship'); ?></label></th>
                        <td class="forminp">
                            <select multiple="multiple" name="location_countries[]" style="width:450px;" data-placeholder="<?php _e( 'Choose countries &#47; states&hellip;', 'be-table-ship' ); ?>" title="Country" class="chosen_select">
                                <?php
                                    if ( $allowed_countries ) {
                                        $selections = explode(',', $zone_fields['zone_country']);
                                        foreach ( $allowed_countries as $key => $val ) {
                                            echo '<option value="'.$key.'" ' . selected( in_array( $key, $selections ), true, false ).'>' . $val . '</option>';
                                            $allowed_states = $woocommerce->countries->get_states($key);
                                            if( $allowed_states ) {
                                                foreach ($allowed_states as $skey => $sval) {
                                                    echo '<option value="'.$key.':'.$skey.'" ' . selected( in_array( $key.':'.$skey, $selections ), true, false ).'>&#009;' . $val . ' &mdash; ' . $sval . '</option>';
                                                }
                                            }
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><label><?php _e('Except the States &#47; Provinces', 'be-table-ship'); ?>...</label></th>
                        <td class="forminp">
                            <select multiple="multiple" name="location_countries_exceptS[]" style="width:450px;" data-placeholder="<?php _e( 'Choose countries &#47; states&hellip;', 'be-table-ship' ); ?>" title="Country" class="chosen_select">
                                <?php
                                    if ( $woocommerce->countries->get_allowed_country_states() ) {
                                        $selections = ( isset( $zone_fields['zone_except']['states'] ) ) ? explode(',', $zone_fields['zone_except']['states']) : array();
                                        foreach ( $woocommerce->countries->get_allowed_country_states() as $key => $val ) {
                                            if( count( $val ) ) {
                                                $allowed_states = $woocommerce->countries->get_states($key);
                                                if( $allowed_states ) {
                                                    foreach ($allowed_states as $skey => $sval) {
                                                        echo '<option value="'.$key.':'.$skey.'" ' . selected( in_array( $key.':'.$skey, $selections ), true, false ).'>&#009;' . $allowed_countries[$key] . ' &mdash; ' . $sval . '</option>';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><label><?php _e('Except the Postal Codes', 'be-table-ship'); ?>...</label></th>
                        <td><textarea style="width:450px;height:75px" name="location_countries_except"><?php if(isset($zone_fields['zone_except']['postals'])) echo sanitize_text_field($zone_fields['zone_except']['postals']); ?></textarea><br ?>
                            <strong>,</strong> <small><?php _e('is used to separate all postal code entries','be-table-ship'); ?></small></br />
                            <strong>-</strong> <small><?php _e('is used to separate two postal codes in a range (numerical codes ONLY)','be-table-ship'); ?></small></br />
                            <strong>*</strong> <small><?php _e('is a wildcard used to represent multiple characters &#47; numbers','be-table-ship'); ?></small></br />
                            <strong>^</strong> <small><?php _e('is used to denote postal codes or ranges to be excluded','be-table-ship'); ?></small></br /></td>
                    </tr>
                </table>
                <table id="location_postal_code" class="form-table" style="display:none">
                <tr valign="top">
                    <th scope="row" class="titledesc"><label><?php _e('Country', 'be-table-ship'); ?></label></th>
                    <td class="forminp"><select name="location_country" style="width:450px;" data-placeholder="<?php _e( 'Choose a country&hellip;', 'be-table-ship' ); ?>" title="Country" class="chosen_select">
<?php

                    if ( $allowed_countries )
                        foreach ( $allowed_countries as $key => $val ) {
                            echo '<option value="'.$key.'" ' . selected( $key, $zone_fields['zone_country'], true, false ).'>' . $val . '</option>';
                            $allowed_states = $woocommerce->countries->get_states($key);
                            if( $allowed_states ) {
                                foreach ($allowed_states as $skey => $sval) {
                                    echo '<option value="'.$key.':'.$skey.'" ' . selected( $key.':'.$skey, $zone_fields['zone_country'], true, false ).'>&#009;' . $val . ' &mdash; ' . $sval . '</option>';
                                }
                            }
                        }
?>
                    </select></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><label><?php _e('Postal Codes', 'be-table-ship'); ?></label></th>
                        <td><textarea style="width:450px;height:75px" name="location_codes"><?php if(isset($zone_fields['zone_postal'])) echo sanitize_text_field($zone_fields['zone_postal']); ?></textarea><br ?>
                            <strong>-</strong> <small><?php _e('is used to separate two postal codes in a range (numerical codes ONLY)','be-table-ship'); ?></small></br />
                            <strong>*</strong> <small><?php _e('is a wildcard used to representent multiple characters/numbers','be-table-ship'); ?></small></br />
                            <strong>^</strong> <small><?php _e('is used to denote postal codes or ranges to be excluded','be-table-ship'); ?></small></br /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><label><?php _e('Except', 'be-table-ship'); ?>...</label></th>
                        <td><textarea style="width:450px;height:75px" name="location_postal_except"><?php if(isset($zone_fields['zone_except']) && is_string( $zone_fields['zone_except'] )) echo sanitize_text_field($zone_fields['zone_except']); ?></textarea><br ?>
                            <strong>-</strong> <small><?php _e('is used to separate two postal codes in a range (numerical codes ONLY)','be-table-ship'); ?></small></br />
                            <strong>*</strong> <small><?php _e('is a wildcard used to representent multiple characters/numbers','be-table-ship'); ?></small></br /></td>
                    </tr>
                </table>
            <script type="text/javascript">
                jQuery(function() {
                    // Load default frame
                    jQuery(window).load(function () {

                    var e = document.getElementById("zone_type");
                    var method_sel = e.options[e.selectedIndex].value;
                    if(method_sel=='everywhere') document.getElementById('location_everywhere').style.display='table-row';
                    if(method_sel=='countries') document.getElementById('location_countries').style.display='table-row';
                    if(method_sel=='postal') document.getElementById('location_postal_code').style.display='table-row';

                    return false;
                    });
                    // Event Handler for Change of Shipping Method
                    jQuery('#zone_type').change(function(){

                    document.getElementById('location_everywhere').style.display='none';
                    document.getElementById('location_countries').style.display='none';
                    document.getElementById('location_postal_code').style.display='none';

                    var e = document.getElementById("zone_type");
                    var method_sel = e.options[e.selectedIndex].value;
                    if(method_sel=='everywhere') document.getElementById('location_everywhere').style.display='table-row';
                    if(method_sel=='countries') document.getElementById('location_countries').style.display='table-row';
                    if(method_sel=='postal') document.getElementById('location_postal_code').style.display='table-row';

                    return false;
                    });

                });
            </script>
        </div>
        <?php
    }
}

function enable_zone_link() {
    global $wpdb;

    $GLOBALS['hook_suffix'] = 'wp_ajax_woocommerce_';

    if ( ! is_admin() ) die;
    if ( ! current_user_can('edit_posts') ) wp_die( __('You do not have sufficient permissions to access this page') );
    if ( ! check_admin_referer('woocommerce-settings')) wp_die( __('You have taken too long' ) . '. ' . __( 'Please go back and retry') );

    $zone_id = (isset( $_GET['zone_id'] ) && is_numeric($_GET['zone_id'])) ? (int) $_GET['zone_id'] : 0;
    if (!$zone_id) die;

    $zoneListTable = new Zone_List_Table();
    $shipping_zones = $zoneListTable->shipping_zones;
    $zoneID = (int) $_GET['zone_id'];

    if( array_key_exists($zoneID, $shipping_zones) ) {
        $zone_enabled = $shipping_zones[ $zoneID ]['zone_enabled'];

        if ( $zone_enabled == '1' ) {
            $shipping_zones[ $zoneID ]['zone_enabled'] = 0;
        } else
            $shipping_zones[ $zoneID ]['zone_enabled'] = 1;

        update_option('be_woocommerce_shipping_zones', $shipping_zones);
    }
    wp_safe_redirect( remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), wp_get_referer() ) );
    die();
}
add_action('wp_ajax_woocommerce_bolder_zone_enabled', 'enable_zone_link');
