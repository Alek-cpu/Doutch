<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/*
 * Declaring Class
 */
abstract class AEH_View {

  private $slug;
  private $page_id = null;
  protected $tabs = array();

  public function __construct( $title, $slug = 'aeh_pro_plugin_options', $submenu = false ) {
		$this->slug     = $slug;
    $this->page_id = add_menu_page(
			$title,
			$title,
			'manage_options',
			$this->slug,
			array( $this, 'render' ),
			AEH_URL.'assests/images/AddExpiresHeadersFevicon.png'
		);

		add_filter( 'load-' . $this->page_id, array( $this, 'on_load' ) );
	}

  public function get_slug() {
    return $this->slug;
  }

  public function view( $name, $options = array() ) {
		$file    = AEH_DIR . "inc/view/{$name}.php";
		$content = '';
		if ( is_file( $file ) ) {
			ob_start();
			if ( isset( $options['id'] ) ) {
				$options['orig_id'] = $options['id'];
				$options['id']      = str_replace( '/', '-', $options['id'] );
			}
			extract( $options );
			include $file;
			$content = ob_get_clean();
		}
		echo $content;
	}

  public function get_current_tab() {
		$tabs = $this->get_tabs();
		if ( isset( $_GET['view'] ) && array_key_exists( wp_unslash( $_GET['view'] ), $tabs ) ) { // Input var ok.
			return wp_unslash( $_GET['view'] ); // Input var ok.
		}
		if ( empty( $tabs ) ) {
			return false;
		}
		reset( $tabs );
		return key( $tabs );
	}

  public function show_tabs() {
    $this->view(
      'tabs',
      array(
        'tabs'      => $this->get_tabs(),
        'is_hidden' => is_network_admin() && ! $this->settings->is_network_enabled(),
      )
    );
  }

  public function get_tab_url( $tab ) {
    $tabs = $this->get_tabs();
    if ( ! isset( $tabs[ $tab ] ) ) {
      return '';
    }
    if ( is_multisite() && is_network_admin() ) {
      return network_admin_url( 'admin.php?page=' . $this->slug . '&view=' . $tab );
    } else {
      return admin_url( 'admin.php?page=' . $this->slug . '&view=' . $tab );
    }
  }

  protected function get_tabs() {
    return apply_filters( 'aeh_admin_page_tabs_' . $this->slug, $this->tabs );
  }

}
