<?php

if ( !defined( 'ABSPATH' ) ) {
    die;
}
/*
 * Declaring Class
 */
class AEH_Admin_View extends AEH_View
{
    public function on_load()
    {
        global  $is_apache ;
        $aeh_tabs = array(
            'cache'    => 'Cache Settings',
            'external' => 'External Resources',
            'advance'  => 'Advance Settings',
            'status'   => 'Plugin Status',
        );
        $this->tabs = apply_filters( 'aeh_setting_tabs', $aeh_tabs );
    }
    
    public function render()
    {
        ?>
    <div class="row">
     <div class="col s12 m12 l3 xl3">
			 <div class="content-pad">
         <img class="responsive-img small-plugin-image" src="<?php 
        echo  AEH_URL . 'assests/images/AddExpiresHeaders.png' ;
        ?>">
       </div>
			 <ul class="collection menu-collection">
				 <?php 
        foreach ( $this->get_tabs() as $tab => $name ) {
            ?>
					 <a href="<?php 
            echo  esc_url( $this->get_tab_url( $tab ) ) ;
            ?>" class="collection-item <?php 
            echo  ( $tab === $this->get_current_tab() ? 'white z-depth-1' : null ) ;
            ?>">
						 <span><?php 
            echo  esc_html( $name ) ;
            ?></span><?php 
            
            if ( $tab === 'advance' || $tab === 'external' ) {
                echo  ( dd_aeh()->can_use_premium_code() ? '<i class="material-icons right">check_circle</i>' : '<i class="material-icons right text-color-free">info</i>' ) ;
            } else {
                echo  '<i class="material-icons right">check_circle</i>' ;
            }
            
            ?></li>
					 </a>
				 <?php 
        }
        ?>
	    </ul>
     </div>
		 <div class="content-pad">
	     <div class="col s12 m12 l9 xl9 white main-content">
				 <?php 
        $current_tab = $this->get_current_tab();
        $expires_headers = $this->view_options( $current_tab );
        $this->view( $current_tab, $expires_headers );
        ?>
		   </div>
		</div>
  <?php 
    }
    
    private function view_options( $tab )
    {
        $aeh_settings = AEH_Settings::get_instance();
        switch ( $tab ) {
            case 'cache':
                return array(
                    'image_types'       => $aeh_settings->expires_headers_image_types,
                    'audio_types'       => $aeh_settings->expires_headers_audio_types,
                    'video_types'       => $aeh_settings->expires_headers_video_types,
                    'font_types'        => $aeh_settings->expires_headers_font_types,
                    'text_types'        => $aeh_settings->expires_headers_text_types,
                    'application_types' => $aeh_settings->expires_headers_application_types,
                    'general_settings'  => $aeh_settings->expires_headers_general_settings,
                    'day_settings'      => $aeh_settings->expires_headers_days_settings,
                    'defaults'          => $aeh_settings->init_general_defaults(),
                );
            case 'advance':
                return array();
            case 'external':
                return array();
            default:
                return array();
        }
    }

}