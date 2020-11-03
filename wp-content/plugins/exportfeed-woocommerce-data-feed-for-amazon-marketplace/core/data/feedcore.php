<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class AMWSCP_PFeedCore {

	public $banner = true; //In Createfeed page
	public $callSuffix = ''; //So getList() can map into getListJ() and getListW() depending on the cms
	public $form_method = 'GET';
	public $hide_outofstock = false;
	public $isJoomla = false;
	public $isWordPress = false;
	public $siteHost; //eg: www.mysite.com
	public $shopID = 0; //Used by RapidCart Source
  
  function __construct() {

		if (defined('_JEXEC')) {
			/********************************************************************
			Joomla init
			********************************************************************/
			if (file_exists(JPATH_ADMINISTRATOR . '/components/com_virtuemart')) {
				$this->callSuffix = 'J';
				$this->cmsName = 'Joomla!';
				$this->cmsPluginName = 'Virtuemart';
				$this->currency = '$'; //!Should not be hard-coded
				$this->dimension_unit =  'cm';
				$this->form_method = 'POST';
				$this->isJoomla = true;
				$this->siteHost = JURI::root(false);
				$this->siteHostAdmin = $this->siteHost;
				$this->timezone = JFactory::getConfig()->get('config.offset');
				$this->weight_unit = 'kg'; //!Should not be hard-coded
			}
			elseif (file_exists(JPATH_ADMINISTRATOR . '/components/com_hikashop'))  {
				$this->callSuffix = 'JH';
				$this->cmsName = 'Joomla!';
				$this->cmsPluginName = 'Hikashop';
				$this->currency = '$';
				$this->dimension_unit =  'cm';
				$this->form_method = 'POST';
				$this->isJoomla = true;
				$this->siteHost = JURI::root(false);
				$this->siteHostAdmin = $this->siteHost;
				$this->timezone = JFactory::getConfig()->get('config.offset');
				$this->weight_unit = 'kg';
			}
			elseif (file_exists(JPATH_ADMINISTRATOR . '/components/com_rapidcart')) {
				$this->callSuffix = 'JS';
				$this->cmsName = 'Joomla!';
				$this->cmsPluginName = 'RapidCart';
				$this->currency = '$';
				$this->dimension_unit =  'cm';
				$this->form_method = 'POST';
				$this->isJoomla = true;
				$this->siteHost = JURI::root(false);
				$this->siteHostAdmin = $this->siteHost;
				$this->timezone = JFactory::getConfig()->get('config.offset');
				$this->weight_unit = 'kg';
			}
		} else {
			/********************************************************************
			Wordpress init
			********************************************************************/
			//require_once dirname(__FILE__) . '/../../../../../wp-load.php'; Not safe to call this from inside a function!

			//check what plugin is available, assuming WooCommerce
			$pluginName = 'WooCommerce';
			$all_plugins = get_plugins();
			foreach($all_plugins as $index => $this_plugin){
                if ($this_plugin['Name'] == 'WP e-Commerce' || $this_plugin['Name'] == 'WP eCommerce') {
                    $pluginName = 'WP e-Commerce';
                    break;
                }
            }

			switch ($pluginName) {
				case 'WooCommerce':
					global  $woocommerce;
					$this->callSuffix = 'W';
					$this->cmsName = 'WordPress';
					$this->cmsPluginName = 'Woocommerce';
					if (function_exists('get_woocommerce_currency'))
						$this->currency = get_woocommerce_currency();
					else
						$this->currency = '$'; //!Should not be hard-coded
					$this->isWordPress = true;
					$this->siteHost = site_url();
					$this->siteHostAdmin = admin_url();
					$this->weight_unit = esc_attr(get_option('woocommerce_weight_unit'));
					$this->dimension_unit =  esc_attr(get_option( 'woocommerce_dimension_unit' )); //cm
					$this->manage_stock = strtolower(get_option('woocommerce_manage_stock')) == 'yes';
					$this->hide_outofstock = strtolower(get_option('woocommerce_hide_out_of_stock_items')) == 'yes';
					
					break;
				case 'WP e-Commerce':
					$this->callSuffix = 'We';
					$this->cmsName = 'WordPress';
					$this->cmsPluginName = 'WP e-Commerce';
					$this->currency = '$'; //!Should not be hard-coded
					$this->isWordPress = true;
					$this->siteHost = site_url();
					$this->siteHostAdmin = admin_url();
					$this->weight_unit = 'kg'; //!Should not be hard-coded
					break;
			}
		}
	}

	public function listOfRapidCartShops() {
		$user = JFactory::getUser();
		$groups = $user->groups;
		if (isset($groups[8]) && $groups[8])
			$where = '';
		else
			$where = 'WHERE created_by = ' . $user->id;
		$db = JFactory::getDBO();
		$db->setQuery('
			SELECT id, name
			FROM #__rapidcart_shops
			' . $where);
		return $db->loadObjectList();
	}

	public function loadRequires($name) {

		//Allow external plugins to load a particular Feed object (Intended for WordPress)
		require_once dirname(__FILE__) . '/../classes/dialogbasefeed.php';
		require_once dirname(__FILE__) . '/../feeds/' . $name . '/feed.php';
		require_once dirname(__FILE__) . '/../feeds/' . $name . '/dialognew.php';

	}

	function localizedDate($format, $data) {
		$getListCall = 'localizedDate' . $this->callSuffix;
		return $this->$getListCall($format, $data);
	}

	function localizedDateJ($format, $data) {
		$this_date = new JDate($data, $this->timezone);
		return $this_date->format($format, false, false);
	}

	function localizedDateJH($format, $data) {
		$this_date = new JDate($data, $this->timezone);
		return $this_date->format($format, false, false);
	}

	function localizedDateJS($format, $data) {
		$this_date = new JDate($data, $this->timezone);
		return $this_date->format($format, false, false);
	}

	function localizedDateW($format, $data) {
		return date_i18n($format, $data);
	}

	function localizedDateWE($format, $data) {
		return date_i18n($format, $data);
	}

	function settingDelete($settingName) {
		$getListCall = 'settingDelete' . $this->callSuffix;
		return $this->$getListCall($settingName);
	}

	function settingDeleteJ($settingName) {
		$db = JFactory::getDBO();
		$db->setQuery('
			DELETE
			FROM #__cartproductfeed_options
			WHERE name = ' . $db->quote($settingName));
		$db->execute();

		$result = $db->loadResult();
		return $result;
	}

	function settingDeleteJH($settingName) {
		return $this->settingDeleteJ($settingName);
	}

	function settingDeleteJS($settingName) {
		global $amwcore;
		$db = JFactory::getDBO();
		$db->setQuery('
			DELETE
			FROM #__cartproductfeed_options
			WHERE name = ' . $db->quote($settingName) . ' AND (shop_id = ' . $amwcore->shopID . ')');
		$result = $db->loadResult();
		return $result;
	}
  
	function settingDeleteW($settingName) {
		return delete_option($settingName);
	}

	function settingDeleteWe($settingName) {
		return delete_option($settingName);
	}

	function settingGet($settingName) {
		$getListCall = 'settingGet' . $this->callSuffix;
		return $this->$getListCall($settingName);
	}

	function settingGetJ($settingName) {
		$db = JFactory::getDBO();
		$db->setQuery('
			SELECT value
			FROM #__cartproductfeed_options
			WHERE name = ' . $db->quote($settingName));
		$db->execute();

		$result = $db->loadResult();
		return $result;
	}

	function settingGetJH($settingName) {
		return $this->settingGetJ($settingName);
	}

	function settingGetJS($settingName) {
		global $amwcore;
		if ($amwcore->shopID == -1)
			$shopCondition = '';
		else
			$shopCondition = ' AND (shop_id = ' . $amwcore->shopID . ')';
		$db = JFactory::getDBO();
		$db->setQuery('
			SELECT value
			FROM #__cartproductfeed_options
			WHERE name = ' . $db->quote($settingName) . $shopCondition);
		$result = $db->loadResult();
		return $result;
	}

	function getVersion() {
		return 2;
	}
  
	function settingGetW($settingName) {
		return get_option($settingName);
	}

	function settingGetWe($settingName) {
		return get_option($settingName);
	}

	function settingSet($settingName, $value) {
		$getListCall = 'settingSet' . $this->callSuffix;
		$this->$getListCall($settingName, $value);
	}
  
	function settingSetJ($settingName, $value) {

		//Initialize
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		//Does this value already exist?
		$query = '
			SELECT id
			FROM #__cartproductfeed_options
			WHERE name = ' . $db->quote( $settingName);
		$db->setQuery($query);
		$result = $db->loadResult();
		if (($result == null) || ($result == 0))
			$isNew = true;
		else
			$isNew = false;

		$setting = new stdClass();
		$setting->name = $settingName;
		$setting->value = $value;
		if ($isNew) {
			$setting->kind = 0;
			//$setting->ordering int,
			$setting->created = $date->toSql();
			$setting->created_by = $user->get('id');
		} else
			$setting->id = $result;
		//$setting->catid
		$setting->modified = $date->toSql();
		$setting->modified_by = $user->get('id');

		if ($isNew)
			$db->insertObject('#__cartproductfeed_options', $setting, 'id');
		else
			$db->updateObject('#__cartproductfeed_options', $setting, 'id');

	}

	function settingSetJH($settingName, $value) {
		return $this->settingSetJ($settingName, $value);
	}

	function settingSetJS($settingName, $value) {

		//Initialize
		global $amwcore;
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		//Does this value already exist?
		$query = '
			SELECT id
			FROM #__cartproductfeed_options
			WHERE name = ' . $db->quote($settingName) . ' AND (shop_id = ' . $amwcore->shopID . ')';
		$db->setQuery($query);
		$result = $db->loadResult();
		if (($result == null) || ($result == 0))
			$isNew = true;
		else
			$isNew = false;

		$setting = new stdClass();
		$setting->name = $settingName;
		$setting->value = $value;
		if ($isNew) {
			$setting->kind = 0;
			$setting->created = $date->toSql();
			$setting->created_by = $user->get('id');
		} else
			$setting->id = $result;
		$setting->modified = $date->toSql();
		$setting->modified_by = $user->get('id');
		$setting->shop_id = $amwcore->shopID;

		if ($isNew)
			$db->insertObject('#__cartproductfeed_options', $setting, 'id');
		else
			$db->updateObject('#__cartproductfeed_options', $setting, 'id');
	}
  
	function settingSetW($settingName, $value) {
		update_option($settingName, $value);
	}

	function settingSetWe($settingName, $value) {
		update_option($settingName, $value);
	}

	public function trigger($eventname) {
		$getListCall = 'trigger' . $this->callSuffix;
		$this->$getListCall($eventname);
	}

	private function triggerJ($eventname) {
	}

	private function triggerJH($eventname) {
		$this->triggerJ($eventname);
	}

	private function triggerJS($eventname) {
	}

	private function triggerW($eventname) {
		do_action($eventname);
	}

	private function triggerWE($eventname) {
		do_action($eventname);
	}

	public function triggerFilter($eventname, $param1 = null, $param2 = null, $param3 = null) {
		$getListCall = 'triggerFilter' . $this->callSuffix;
		return $this->$getListCall($eventname, $param1, $param2, $param3);
	}

	private function triggerFilterJ($eventname, $param1, $param2, $param3) {
		if (!isset($amwcore->dispatcher))
			$amwcore->dispatcher = JEventDispatcher::getInstance();
		$results = $amwcore->dispatcher->trigger( $eventname, array($param1, $param2, $param3) );
	}

	private function triggerFilterJH($eventname, $param1, $param2, $param3) {
		return $this->triggerFilterJ($eventname, $param1, $param2, $param3);
	}

	private function triggerFilterJS($eventname, $param1, $param2, $param3) {
	}

	private function triggerFilterW($eventname, $param1, $param2, $param3) {
		return apply_filters($eventname, $param1, $param2, $param3);
	}

	private function triggerFilterWE($eventname, $param1, $param2, $param3) {
		return apply_filters($eventname, $param1, $param2, $param3);
	}

    public function amwscpf_tooltip($desc){
        echo '<img class="help_tip" data-tip="' . $desc . '" src="' . AMWSCPF_URL . '/images/help.png" height="16" width="16" />';
    }
    public function amwscpf_loader($class){
        if(!strlen($class) > 0)
            return;
        echo '<img class="'.$class.'" style="display:none" src="' . AMWSCPF_URL . '/images/loading_balls.gif" height="25" width="30" />';
    }

    public function amwscpf_loader_2($class){
        if (!strlen($class) > 0 )
            return;
        echo '<img class="'.$class.'" style="display:none" src="'.AMWSCPF_URL.'images/infinity.gif'.'" height = "25" width="30"/>';
    }

    public function amwscpf_loader_big($class,$css = array()){
        $style = "";
        if (count($css) > 0){
            foreach ($css as $key => $value){
                $style .= $key .':'.$value.';';
            }
        }
        if (!strlen($class) > 0 )
            return;
        echo '<img class="'.$class.'" style="'.$style.'" src="'.AMWSCPF_URL.'images/new_gif.gif'.'"/>';
    }

    public function amwscpf_overlay_loader($class,$css=array(),$img_css=array()){
        $style = "";
        $style2 = "";
        if (count($css) > 0){
            foreach ($css as $key => $value){
                $style .= $key .':'.$value.';';
            }
        }
        if (count($img_css) > 0){
            foreach ($img_css as $key => $value){
                $style2 .= $key .':'.$value.';';
            }
        }
        if (!strlen($class) > 0){
            return '';
        }
        echo '<div style="'.$style.'" class="'.$class.'"><img src="'.AMWSCPF_URL.'images/new_gif.gif'.'" style="'.$style2.'"/></div>';
    }

    public function on_amazon(){
//        echo
    }
	public function checkLicense($licenseKey){
		global $wpdb;
		$option_id = $wpdb->get_var($wpdb->prepare( "SELECT option_id from $wpdb->options where option_value = %s", $licenseKey));
		return $option_id;
	}

}

global $amwcore;
$amwcore = new AMWSCP_PFeedCore();
