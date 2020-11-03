<?php
/**
 * TemplatesPage class
 * 
 */

class TemplatesPage extends WPL_Page {

	const slug = 'templates';

	public function onWpInit() {
		// parent::onWpInit();

		// Add custom screen options
		$load_action = "load-".$this->main_admin_menu_slug."_page_wplister-".self::slug;
		add_action( $load_action, array( &$this, 'addScreenOptions' ) );

		add_action('wp_ajax_wpl_get_copy_template_form', array( &$this, 'ajax_wpl_get_copy_template_form' ));
		add_action('wp_ajax_wpl_duplicate_template', array( &$this, 'ajax_wpl_duplicate_template' ));

		add_action('wp_ajax_wpl_get_tpl_css', array( &$this, 'ajax_wpl_get_tpl_css' ));

		add_action( 'admin_notices', array( $this, 'validate_templates' ) );
	}

	public function validate_templates() {
	    // only run in the Templates list page
        if ( !empty( $_REQUEST['action'] ) ) {
            return;
        }
        if ( !isset( $_REQUEST['page'] ) ) {
            return;
        }
        if ( $_REQUEST['page'] != 'wplister-templates' ) {
            return;
        }

        $result = TemplatesModel::check_template_function_conflicts();

        if ( ! $result['status'] ) {
            foreach ( $result['conflicts'] as $fn => $templates ) {
                wple_show_message( sprintf( __( '<b>Warning:</b> The function <em>%s</em> is being used by %d templates (%s).', 'wp-lister-for-ebay' ), $fn, count( $templates ), join(', ', $templates ) ), 'warn' );
            }
        }

    }

	public function onWpAdminMenu() {
		parent::onWpAdminMenu();

		add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( 'Templates' ), __( 'Templates', 'wp-lister-for-ebay' ),
						  'manage_ebay_listings', $this->getSubmenuId( 'templates' ), array( &$this, 'onDisplayTemplatesPage' ) );
	}

	public function handleSubmit() {
		if ( ! current_user_can('manage_ebay_listings') ) return;

		// handle download template
		if ( $this->requestAction() == 'download_listing_template' ) {
		    check_admin_referer( 'wple_templates_page' );
			$this->downloadTemplate();
		}
		// handle delete action
		if ( $this->requestAction() == 'delete_listing_template' ) {
		    check_admin_referer( 'wple_templates_page' );
			$this->deleteTemplate();
		}

	}

	// handle save template action
	// this needs to be called after WooCommerce initialized its post types, but before the first byte is sent
	public function onWpAdminInit() {

		// handle save template
		if ( $this->requestAction() == 'wple_save_template' ) {
		    check_admin_referer( 'wplister_save_template' );

			$this->saveTemplate();
			if ( @$_POST['return_to'] == 'listings' ) {
				$return_url = get_admin_url().'admin.php?page=wplister';
		        if ( isset($_REQUEST['listing_status']) )	$return_url = add_query_arg( 'listing_status', wple_clean($_REQUEST['listing_status']), $return_url );
		        if ( isset($_REQUEST['s']) )				$return_url = add_query_arg( 's', wple_clean($_REQUEST['s']), $return_url );
				wp_redirect( $return_url );
			}
		}
		// handle preview action
		if ( $this->requestAction() == 'wple_preview_template' ) {
            check_admin_referer( 'wple_templates_page' );

			// handle parameters to select custom listing item
			$listing_id = isset( $_REQUEST['listing_id'] ) ? intval($_REQUEST['listing_id']) : false;
			if ( ! $listing_id && isset( $_REQUEST['post_id'] ) ) {
				$listing_id = WPLE_ListingQueryHelper::getListingIDFromPostID( intval($_REQUEST['post_id']) );
			}

			$this->previewTemplate( wple_clean($_REQUEST['template']), $listing_id );
			exit();
		}

	}

	function addScreenOptions() {
		$option = 'per_page';
		$args = array(
	    	'label' => 'Templates',
	        'default' => 20,
	        'option' => 'templates_per_page'
	        );
		add_screen_option( $option, $args );
		$this->temmplatesTable = new TemplatesTable();
		add_thickbox();

        wp_enqueue_style( 'farbtastic' );
		wp_enqueue_script( 'farbtastic' );

 	    // If the WordPress version is greater than or equal to 3.5, then load the new WordPress color picker.
		/*
		global $wp_version;
	    if ( 3.5 <= $wp_version ){
	        wp_enqueue_style( 'wp-color-picker' );
	        wp_enqueue_script( 'wp-color-picker' );
	    } else {
	        wp_enqueue_style( 'farbtastic' );
			wp_enqueue_script( 'farbtastic' );
	    }
	    */

	}
	


	public function onDisplayTemplatesPage() {
		$this->check_wplister_setup();
	
		// edit template
		if ( ( $this->requestAction() == 'edit' ) || ( $this->requestAction() == 'add_new_template' ) ) {

			$this->displayEditPage();

		// show list
		} else {

			$this->displayListPage();			

		}

	}


	private function displayListPage() {

		// handle upload template
		if ( $this->requestAction() == 'wpl_upload_template' ) {
            if ( ! current_user_can( 'manage_ebay_options' ) || ! check_admin_referer( 'wpl_upload_template' ) ) return false;

			$this->uploadTemplate();
		}

		// init model
		$templatesModel = new TemplatesModel();
	
		// get all items
		$templates = $templatesModel->getAll();

	    //Create an instance of our package class...
	    $templatesTable = new TemplatesTable();
    	//Fetch, prepare, sort, and filter our data...
	    // $templatesTable->db_items = $templates; // no effect on WP4.2
	    $templatesTable->prepare_items();

	    // refresh cache of template names and descriptions
	    $this->refreshTemplatesCache( $templates );

		// process errors 		
		#if ($this->IC->message) $this->showMessage( $this->IC->message,1 );
		
		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			// 'templates'					=> $templates,
			'templatesTable'			=> $templatesTable,
		
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-templates'
		);

		$this->display( 'templates_page', $aData );
		
	}


	private function displayEditPage() {

		if ( $this->requestAction() == 'add_new_template' ) {
			
			// add new template
			$template = false;
			$templatesModel 		= new TemplatesModel();
			$item 					= $templatesModel->newItem();
			$html 					= $templatesModel->getHTML(); 
			$css					= $templatesModel->getCSS ();
			$header					= $templatesModel->getHeader();
			$footer					= $templatesModel->getFooter();				
			$functions				= $templatesModel->getFunctions();				
			$slider 				= $templatesModel->getGallerySlider();
			$thumbnails				= $templatesModel->getThumbnails();
			$thumbnails_nojs		= $templatesModel->getThumbnailsNoJS();

		} else {

			// edit template
			$template 				= wple_clean( urldecode( $_REQUEST['template'] ) );
			$templatesModel 		= new TemplatesModel( $template );
			$item 					= $templatesModel->getItem();
			$html					= $templatesModel->getHTML();
			$css					= $templatesModel->getCSS ();
			$header					= $templatesModel->getHeader();
			$footer					= $templatesModel->getFooter();				
			$functions				= $templatesModel->getFunctions();				
			$slider 				= $templatesModel->getGallerySlider();
			$thumbnails				= $templatesModel->getThumbnails();
			$thumbnails_nojs		= $templatesModel->getThumbnailsNoJS();
		}

		// init template - and run syntax check
		$templatesModel->initTemplate( true );

		// remove template header from stylesheet
		if ( preg_match('/^\/\*.*^\*\//uUsm', $css, $matches ) ) {
			$css = str_replace($matches[0], '', $css);
		}

		// check for CDATA tag in html, header and footer
		if ( strpos($html, '<![CDATA[') > 0 ) {
			$this->showMessage( "Warning: Your template HTML code contains CDATA tags which can break the listing process. You should remove them as they don't fullfill any purpose in an eBay listing anyway.", 1 );
		}
		if ( strpos($header, '<![CDATA[') > 0 ) {
			$this->showMessage( "Warning: Your template header contains CDATA tags which can break the listing process. You should remove them as they don't fullfill any purpose in an eBay listing anyway.", 1 );
		}
		if ( strpos($footer, '<![CDATA[') > 0 ) {
			$this->showMessage( "Warning: Your template footer contains CDATA tags which can break the listing process. You should remove them as they don't fullfill any purpose in an eBay listing anyway.", 1 );
		}

		$prepared_listings  = WPLE_ListingQueryHelper::countItemsUsingTemplate( $template, 'prepared' );
		$verified_listings  = WPLE_ListingQueryHelper::countItemsUsingTemplate( $template, 'verified' );
		$published_listings = WPLE_ListingQueryHelper::countItemsUsingTemplate( $template, 'published' );

		// this turned out to be to ressource intensive with 10k listings:
		// $prepared_listings  = WPLE_ListingQueryHelper::getAllPreparedWithTemplate( $template );
		// $verified_listings  = WPLE_ListingQueryHelper::getAllVerifiedWithTemplate( $template );
		// $published_listings = WPLE_ListingQueryHelper::getAllPublishedWithTemplate( $template );

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'item'						=> $item,
			'html'						=> $html,
			'css'						=> $css,
			'header'					=> $header,
			'footer'					=> $footer,
			'functions'					=> $functions,
			'slider'    				=> $slider,
			'thumbnails'				=> $thumbnails,
			'thumbnails_nojs'			=> $thumbnails_nojs,
			'template_location'			=> $item['template_path'],
			'add_new_template'			=> ( $this->requestAction() == 'add_new_template' ) ? true : false,
			'tpl_fields'  			    => $templatesModel->fields,

			'prepared_listings_count'   => $prepared_listings,
			'verified_listings_count'   => $verified_listings,
			'published_listings_count'  => $published_listings,
			'total_listings_count'      => $prepared_listings + $verified_listings + $published_listings,
			'disable_wysiwyg_editor'    => self::getOption( 'disable_wysiwyg_editor', 0 ),
			
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-templates'
		);
		$this->display( 'templates_edit_page', $aData );

	}


	private function refreshTemplatesCache( $templates ) {

		// build array with foldername as keys
		$templates_cache = array();		
		foreach ($templates as $tpl) {
			$templates_cache[ $tpl['template_id'] ] = $tpl;
		}
		
		// save as option
		self::updateOption( 'templates_cache', $templates_cache );
		// WPLE()->logger->info( print_r($templates_cache,1));

	}


	private function saveTemplate() {
		if ( ! current_user_can('manage_ebay_listings') ) return;

		// set templates root folder
		$upload_dir = wp_upload_dir();
		$templates_dir = $upload_dir['basedir'].'/wp-lister/templates/';

		// handle add_new_template
		// if ( $this->getValueFromPost('add_new_template') == 1 ) {
		if ( isset( $_REQUEST['wpl_add_new_template'] ) ) {

			// check folder name
            // trim dots from the beginning and end of the template name #11538
			$dirname = trim( strtolower( sanitize_file_name( $this->getValueFromPost( 'template_name' ) ) ), '.' );
			if ( $dirname == '') {
				$this->showMessage( "Could not create template. No template name was provided.", 1 );	
				return false;				
			}
			
			// tpl_dir is the full path to the template
			$tpl_dir = $templates_dir . $dirname;

			// if folder exists, append '-1', '-2', .. '-99'
			if ( is_dir( $tpl_dir ) ) {
				for ($i=1; $i < 100; $i++) { 
					$new_tpl_dir = $tpl_dir . '-' . $i;					
					if ( ! is_dir( $new_tpl_dir ) ) {
						$tpl_dir = $new_tpl_dir;
						break;
					}
				}
			}

			// make new folder
			$result  = @mkdir( $tpl_dir );

			// handle errors
			if ($result===false) {
				$this->showMessage( "Could not create template folder: " . $tpl_dir, 1 );	
				return false;
			} else {
				$this->showMessage( __( 'New template created in folder:', 'wp-lister-for-ebay' ) .' '. basename($tpl_dir) );
			}

			// init default template to handle setting
			$templatesModel = new TemplatesModel();
			$templatesModel->folderpath = WPLE_PLUGIN_PATH . '/templates/default/';
			$templatesModel->initTemplate();
		
		// save existing template
		} else {
			
			$dirname = $this->getValueFromPost( 'template_id' );
			$tpl_dir = $templates_dir . sanitize_file_name( $dirname );
			$changed = 0;

			// handle delayed update option
			if ( isset($_POST['wple_delay_template_application']) ) {

				update_option( 'wple_job_reapply_template_id', $dirname );

			} else {

				// re-apply profile to all published
				$listingsModel = new ListingsModel();
				$items = WPLE_ListingQueryHelper::getAllPublishedWithTemplate( $dirname );
				if ( ! empty( $items ) ) {
			        foreach ($items as $item) {

			        	// don't mark locked items as changed
			        	if ( ! $item['locked'] ) {
				        	// ListingsModel::updateListing( $item['id'], array('status' => 'changed') ); // for some reason, this was messing up quantities
				        	$listingsModel->reapplyProfileToItem( $item['id'] );
				        	$changed++;
			        	}
				        
			        }
					$this->showMessage( sprintf( __( '%s published items marked as changed.', 'wp-lister-for-ebay' ), $changed ) );
				}

			}


			// init template to handle setting
			$templatesModel = new TemplatesModel( $dirname );
			$templatesModel->initTemplate();
		
		}

		// destination files
		$file_html					= $tpl_dir . '/template.html';
		$file_css					= $tpl_dir . '/style.css';
		$file_header				= $tpl_dir . '/header.php';
		$file_footer				= $tpl_dir . '/footer.php';
		$file_functions				= $tpl_dir . '/functions.php';
		$file_slider	    		= $tpl_dir . '/gallery_slider.php';
		$file_thumbnails			= $tpl_dir . '/thumbnails.php';
		$file_thumbnails_nojs		= $tpl_dir . '/thumbnails_nojs.php';
		$file_settings				= $tpl_dir . '/config.json';

		// With the $dirname sanitized using sanitize_file_name(), we just need to unslash the posted HTML
        // then write them directly to their appropriate files (as what's being done by WP in wp_edit_theme_plugin_file())
		$tpl_html	 				= wp_unslash( $_POST['wpl_e2e_tpl_html'] );
		$tpl_css	 				= wp_unslash( $_POST['wpl_e2e_tpl_css'] );
		$tpl_header	 				= wp_unslash( $_POST['wpl_e2e_tpl_header'] );
		$tpl_footer	 				= wp_unslash( $_POST['wpl_e2e_tpl_footer'] );
		$tpl_functions	 			= wp_unslash( $_POST['wpl_e2e_tpl_functions'] );
		$tpl_slider 	 			= wp_unslash( $_POST['wpl_e2e_tpl_slider'] );
		$tpl_thumbnails	 			= wp_unslash( $_POST['wpl_e2e_tpl_thumbnails'] );
		$tpl_thumbnails_nojs	 	= wp_unslash( $_POST['wpl_e2e_tpl_thumbnails_nojs'] );
		
		$template_name 				= stripslashes( $this->getValueFromPost( 'template_name'  ) );
		$template_description 		= stripslashes( $this->getValueFromPost( 'template_description'  ) );
		$template_version 			= stripslashes( $this->getValueFromPost( 'template_version'  ) );

		// strip CDATA tags
		$tpl_html   = $this->stripCDATA( $tpl_html );
		$tpl_header = $this->stripCDATA( $tpl_header );
		$tpl_footer = $this->stripCDATA( $tpl_footer );

		// handle custom fields settings
		$settings = array();
		if ( is_array( $templatesModel->fields ) ) {
			foreach ($templatesModel->fields as $field_id => $field) {
				$value = $this->getValueFromPost( 'tpl_field_'.$field_id );
				if ( $value ) {
					$settings[ $field_id ] = stripslashes( $value );
				}
			}
		}

		// add template header
		$header_css = "/* \n";
		$header_css .= "Template: $template_name\n";
		$header_css .= "Description: $template_description\n";
		$header_css .= "Version: $template_version\n";
		$header_css .= "*/\n";
		$tpl_css = $header_css . $tpl_css;

		// update template files
		$result = file_put_contents($file_css , $tpl_css);
		$result = file_put_contents($file_functions , $tpl_functions);
		$result = file_put_contents($file_slider , $tpl_slider);
		$result = file_put_contents($file_thumbnails , $tpl_thumbnails);
		$result = file_put_contents($file_thumbnails_nojs , $tpl_thumbnails_nojs);
		$result = file_put_contents($file_footer , $tpl_footer);
		$result = file_put_contents($file_header , $tpl_header);
		$result = file_put_contents($file_html, $tpl_html);
		$result = file_put_contents($file_settings, json_encode( $settings ) );

		// catch any errors about permissions, safe mode, etc.
	    global $php_errormsg;
		ini_set('track_errors', 1); 
		if ( ! touch( $file_css ) ) {
			$this->showMessage( $php_errormsg, true );				
		}

		// proper error handling
		if ($result===false) {
			$this->showMessage( "WP-Lister failed to save your template because it could not write to the file <pre>$file_css</pre> Please check the file and folder permissions and make sure that PHP safe_mode is disabled.", true );	
		} else {
			// hide double success message when adding new template
			if ( !isset( $_REQUEST['wpl_add_new_template'] ) ) $this->showMessage( __( 'Template saved.', 'wp-lister-for-ebay' ) );

			// if we were updating this template as part of setup, move to next step
			if ( '3' == self::getOption('setup_next_step') ) self::updateOption('setup_next_step', 4);

		}

	}


	public function stripCDATA( $content ) {
        $content = str_replace('// <![CDATA[', 	'', $content );
        $content = str_replace('// ]]>', 		'', $content );
        $content = str_replace('//<![CDATA[', 	'', $content );
        $content = str_replace('//]]>', 		'', $content );
        // $content = str_replace('<![CDATA[', 	'', $content );
        // $content = str_replace(']]>', 		'', $content );
		return $content;
	}


	public function previewTemplate( $template_id, $listing_id = false ) {
		if ( ! current_user_can('manage_ebay_listings') ) return;
	
		// init model
		$ibm = new ItemBuilderModel();
		$preview_html = $ibm->getPreviewHTML( $template_id, $listing_id );
		echo $preview_html;
		exit();		

	}



	private function uploadTemplate() {
		if ( ! current_user_can('manage_ebay_options') ) return;

		// set templates root folder
		$upload_dir = wp_upload_dir();
		$templates_dir = $upload_dir['basedir'].'/wp-lister/templates/';

	    $filename = $_FILES['fupload']['name'];
	    $tmp_name = $_FILES['fupload']['tmp_name'];
	    $type = $_FILES['fupload']['type']; 
	    $name = explode('.', $filename); 
	    $target = $templates_dir;

	    // permission settings for newly created folders
	    $chmod = 0755;  
		
		// set up WP_Filesystem() required for unzip_file
		WP_Filesystem();

	    $saved_file_location = $target . basename($filename);
        // TODO: use wp_handle_upload() instead
	    if (move_uploaded_file($tmp_name, $saved_file_location)) {

	    	// extract zip archive
	        $return = unzip_file($saved_file_location, $target);
			if ( is_wp_error($return) ) {
				$msg  = __( 'There was a problem while extracting the archive:', 'wp-lister-for-ebay' ) . '<br><br>';
				$msg .= 'WordPress said:     '. $return->get_error_message() . '<br><br>';
				$msg .= 'archive location:   '. $saved_file_location . '<br>';
				$msg .= 'destination folder: '. $target . '<br>';
				$this->showMessage( $msg, 1 ) ;				
				// echo "<pre>";print_r($return);echo"</pre>";#die();
			} else {
				$this->showMessage( __( 'Your listing template was uploaded and installed.', 'wp-lister-for-ebay' ) );
			}
			// remove archive
			unlink( $saved_file_location );

	    } else {
			$this->showMessage( __( 'There was a problem while processing your upload.', 'wp-lister-for-ebay' ) );
	    }

	}

	private function deleteTemplate() {
		if ( ! current_user_can( 'manage_ebay_options' ) || ! check_admin_referer( 'wple_templates_page' ) ) return false;

		$templatesModel = new TemplatesModel();
		$templatesModel->deleteTemplate( wple_clean($_REQUEST['template']) );

		$this->showMessage( "Template deleted: ".wple_clean($_REQUEST['template']) );
	}

	/**
     * Zip a directory recursively
     *
     * @access private
     * @param  string $source
     * @param  string $destination
     * @param  string $template_id
     * @return bool
     */
	private function Zip($source, $destination, $template_id)
	{

		if (!extension_loaded('zip') || !file_exists($source)) {
			return false;
		}

		$zip = new ZipArchive();
		if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
			return false;
		}

		$source = str_replace('\\', '/', realpath($source));

		$zip->addEmptyDir( $template_id . '/' );

		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

		foreach ($files as $file)
		{
			$file = str_replace('\\', '/', $file);

			// Ignore "." and ".." folders
			if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
				continue;
				
			if (is_dir($file) === true)
			{
				$zip->addEmptyDir(str_replace($source . '/', '', $template_id . '/' . $file . '/'));
			}
			else if (is_file($file) === true)
			{
				$zip->addFromString(str_replace($source . '/', '', $template_id . '/' . $file), file_get_contents($file));
			}
		}

		$zip->close();

		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=".$template_id.".zip");
		header("Content-Type: application/zip");
		header("Content-length: " . filesize( $destination ) . "\n\n");
		header("Content-Transfer-Encoding: binary");

		// output data to the browser
		readfile( $destination );

		// remove archive
		unlink( $destination );

		return true;
	}

	/**
     * Download a template
     *
     * @access private
     * @param  void
     * @return void
     */
	private function downloadTemplate() {

		if ( ! current_user_can( 'manage_ebay_options' ) || ! check_admin_referer( 'wple_templates_page' ) ) return false;
		if ( ! class_exists('ZipArchive') ) die('Error: Class "ZipArchive" does not exist. To download a listing template, your server requires the PHP zip extension.');

		// set templates root folder
		$upload_dir = wp_upload_dir();
		$templates_dir = $upload_dir['basedir'].'/wp-lister/templates/';

		$template_id = basename( wple_clean($_REQUEST['template']) );
	    $folder  = $templates_dir . $template_id . '/';
	    $tmpfile = $templates_dir . $template_id . '.zip';

	    // create ZipArchive
		$this->Zip($folder, $tmpfile, $template_id);
	}

	function ajax_wpl_get_tpl_css() {
		if ( ! current_user_can('manage_ebay_listings') ) return;

		$tpl = wple_clean($_REQUEST['tpl']);
		$templatesModel = new TemplatesModel( $tpl );
		$templatesModel->initTemplate();

		$css = $templatesModel->getCSS();
		$css = $templatesModel->processCustomFields( $css );
		
		header('Content-Type: text/css');
		echo $css;
		exit;
	}


	function ajax_wpl_get_copy_template_form() {
		if ( ! current_user_can('manage_ebay_listings') ) return;

		// get template
		$template_id 			= wple_clean( urldecode( @$_REQUEST['template_id'] ) );
		if ( ! $template_id ) die('template not found.');
		$templatesModel 		= new TemplatesModel( $template_id );
		$item 					= $templatesModel->getItem();


		$aData = array(
			'item'						=> $item
		);

		$this->display( 'templates_copy_form', $aData );
		exit;

	}
	

	function ajax_wpl_duplicate_template() {
		if ( ! current_user_can('manage_ebay_listings') ) return;

		// get template
		$template_id 			= urldecode( $this->getValueFromPost( 'template_id' ) );
		if ( ! $template_id ) die('template not found.');
		$templatesModel 		= new TemplatesModel( $template_id );
		$item 					= $templatesModel->getItem();

		// echo "<pre>";print_r($templatesModel);echo"</pre>";die();


		// set templates root folder
		$upload_dir = wp_upload_dir();
		$templates_dir = $upload_dir['basedir'].'/wp-lister/templates/';

		// check folder name
		$dirname = strtolower( sanitize_file_name( $this->getValueFromPost( 'template_name' ) ) );
		if ( $dirname == '') {
			echo( "Could not create template. No template name was provided." );	
			exit;				
		}
		
		// tpl_dir is the full path to the duplicated template
		$tpl_dir = $templates_dir . $dirname;

		// src_tpl_dir is the full path to the original template
		$src_tpl_dir = $templates_dir . $template_id;

		// if folder exists, append '-1', '-2', .. '-99'
		if ( is_dir( $tpl_dir ) ) {
			for ($i=1; $i < 100; $i++) { 
				$new_tpl_dir = $tpl_dir . '-' . $i;					
				if ( ! is_dir( $new_tpl_dir ) ) {
					$tpl_dir = $new_tpl_dir;
					break;
				}
			}
		}

		// make new folder
		$result  = @mkdir( $tpl_dir );

		// handle errors
		if ($result===false) {
			echo( "Could not create template folder: " . $tpl_dir );	
			exit;
		}


		// destination files
		$file_html					= $tpl_dir . '/template.html';
		$file_css					= $tpl_dir . '/style.css';
		$file_header				= $tpl_dir . '/header.php';
		$file_footer				= $tpl_dir . '/footer.php';
		$file_functions				= $tpl_dir . '/functions.php';
		$file_config				= $tpl_dir . '/config.json';
		
		$tpl_html	 				= $templatesModel->getHTML();
		$tpl_css	 				= $templatesModel->getCSS();
		$tpl_header	 				= $templatesModel->getHeader();
		$tpl_footer	 				= $templatesModel->getFooter();
		$tpl_functions	 			= $templatesModel->getFunctions();
		$tpl_config	 				= file_exists( $src_tpl_dir . '/config.json' ) ? file_get_contents( $src_tpl_dir . '/config.json') : false;
		
		$template_name 				= stripslashes( $this->getValueFromPost( 'template_name'  ) );
		$template_description 		= stripslashes( $this->getValueFromPost( 'template_description'  ) );
		$template_version 			= $item[ 'template_version' ];

        // rename the functions to prevent having duplicate template functions
        preg_match_all( '/function ([a-z0-9_]+)\(/i', $tpl_functions, $matches );
        if ( !empty( $matches[1] ) ) {
            foreach ( $matches[1] as $func ) {
                $new_func = $func . '_1';
                $tpl_functions = str_replace( $func, $new_func, $tpl_functions );
            }
        }

		// add template header
		$header_css = "\n/* \n";
		$header_css .= "Template: $template_name\n";
		$header_css .= "Description: $template_description\n";
		$header_css .= "Version: $template_version\n";
		$header_css .= "*/\n";
		$tpl_css = $header_css . $tpl_css;

		// update template files
		$result = file_put_contents($file_css , $tpl_css);
		$result = file_put_contents($file_functions , $tpl_functions);
		$result = file_put_contents($file_footer , $tpl_footer);
		$result = file_put_contents($file_header , $tpl_header);
		$result = file_put_contents($file_html, $tpl_html);		
		if ( $tpl_config ) $result = file_put_contents($file_config, $tpl_config);

		// proper error handling
		if ($result===false) {
			echo( "There was a problem duplicating your template." );	
		} else {
			echo "success";
		}

		exit;

	}
	

}
