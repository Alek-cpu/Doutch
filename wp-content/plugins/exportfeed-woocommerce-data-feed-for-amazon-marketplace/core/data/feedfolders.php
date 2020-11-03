<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class AMWSCP_PFeedFolder {
	public static function feedURL() {
		global $amwcore;

        if (!$amwcore->callSuffix){
            $amwcore = new stdClass();
            $amwcore->callSuffix = 'W';
        }

		$feedURL = 'feedURL' . $amwcore->callSuffix;
		return AMWSCP_PFeedFolder::$feedURL();
	}
  
	private static function feedURLJ() {
		global $amwcore;
		return $amwcore->siteHost . '/administrator/index.php?option=com_cartproductfeed&view=instantiatefeed';
	}

	private static function feedURLJH() {
		global $amwcore;
		return $amwcore->siteHost . '/administrator/index.php?option=com_cartproductfeed&view=instantiatefeed';
	}

	private static function feedURLJS() {
		global $amwcore;
		return $amwcore->siteHost . '/administrator/index.php?option=com_cartproductfeed&view=instantiatefeed';
	}

	private static function feedURLW() {
		global $amwcore;
		return $amwcore->siteHost;
	}

	private static function feedURLWe() {
		global $amwcore;
		return $amwcore->siteHost;
	}

	/********************************************************************
	uploadFolder is where the plugin should make the file
	********************************************************************/
	public static function uploadFolder() {
		global $amwcore;
        if (!$amwcore->callSuffix){
            $amwcore = new stdClass();
            $amwcore->callSuffix = 'W';
        }

        $uploadFolder = 'uploadFolder' . $amwcore->callSuffix;
		return AMWSCP_PFeedFolder::$uploadFolder();
	}

	private static function uploadFolderJ() {
		return JPATH_SITE . '/media/amazon_mws_feeds/';
	}

	private static function uploadFolderJH() {
		return JPATH_SITE . '/media/amazon_mws_feeds/';
	}

	private static function uploadFolderJS() {
		return JPATH_SITE . '/media/amazon_mws_feeds/';
	}

	private static function uploadFolderW() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . '/amazon_mws_feeds/';
	}

	private static function uploadFolderWe() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . '/amazon_mws_feeds/';
	}

	/********************************************************************
	uploadRoot is where the plugin should make the file (same as uploadFolder)
	but no "amazon_mws_feeds". Useful for ensuring folder exists
	********************************************************************/

	public static function uploadRoot() {

		global $amwcore;
        if (!$amwcore->callSuffix){
            $amwcore = new stdClass();
            $amwcore->callSuffix = 'W';
        }
		$uploadRoot = 'uploadRoot' . $amwcore->callSuffix;
		return AMWSCP_PFeedFolder::$uploadRoot();
	}

	private static function uploadRootJ() {
		return  JPATH_SITE . '/media/';
	}

	private static function uploadRootJH() {
		return  JPATH_SITE . '/media/';
	}

	private static function uploadRootJS() {
		return  JPATH_SITE . '/media/';
	}

	private static function uploadRootW() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'];
	}

	private static function uploadRootWe() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'];
	}

	/********************************************************************
	URL we redirect the client to in order for the user to see the feed
	********************************************************************/

	public static function uploadURL() {
		global $amwcore;
		$uploadURL = 'uploadURL' . $amwcore->callSuffix;
		return AMWSCP_PFeedFolder::$uploadURL();
	}

	private static function uploadURLJ() {
		return JURI::root() . 'media/amazon_mws_feeds/';
	}

	private static function uploadURLJH() {
		return JURI::root() . 'media/amazon_mws_feeds/';
	}

	private static function uploadURLJS() {
		return JURI::root() . 'media/amazon_mws_feeds/';
	}

	private static function uploadURLW() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['baseurl'] . '/amazon_mws_feeds/';
	}

	private static function uploadURLWe() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['baseurl'] . '/amazon_mws_feeds/';
	}

}