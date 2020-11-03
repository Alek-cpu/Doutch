<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/*
 * Declaring Class
 */
class AEH_Settings {
  public $expires_headers_image_types = array(
    'gif' => true,
    'ico' => true,
    'jpeg'=> true,
    'jpg'=> true,
		'png'=> true,
		'tiff'=> true,
		'webp'=> true,
  );

  public $expires_headers_audio_types = array(
    'dct' => true,
    'gsm' => true,
    'mp3'=> true,
    'ogg'=> true,
		'raw'=> true,
		'vox'=> true,
		'wav'=>true,
  );

  public $expires_headers_video_types = array(
    '3gp' => true,
    'avi' => true,
    'flv'=> true,
    'mkv'=> true,
		'mp4'=> true,
		'webm'=> true,
		'wmv'=>true,
  );
	public $expires_headers_font_types = array(
    'otf' => true,
		'ttf' => true,
		'woff' => true,
		'woff2' => true,
  );
  public $expires_headers_text_types = array(
    'css' => true,
  );

  public $expires_headers_application_types = array(
    'js' => true,
		'javascript' => true,
		'x-javascript' => true,
  );

  public $expires_headers_general_settings = array(
    'image' => false,
    'audio' => false,
    'video' => false,
		'font' => false,
    'text' => false,
    'application' => false,
  );

  public $expires_headers_days_settings = array(
    'image' => 30,
    'audio' => 30,
    'video' => 30,
		'font'=> 30,
    'text' => 30,
    'application' => 30,
  );

  private static $instance = null;
  public static function get_instance() {
    if ( ! self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }
  public function parse_expires_headers_settings($settings) {
    $args = array(
      'general'          => array(
        'filter' => FILTER_VALIDATE_BOOLEAN,
        'flags'  => FILTER_REQUIRE_ARRAY,
      ),
      'image'          => array(
        'filter' => FILTER_VALIDATE_BOOLEAN,
        'flags'  => FILTER_REQUIRE_ARRAY,
      ),
      'audio'          => array(
        'filter' => FILTER_VALIDATE_BOOLEAN,
        'flags'  => FILTER_REQUIRE_ARRAY,
      ),
      'video'          => array(
        'filter' => FILTER_VALIDATE_BOOLEAN,
        'flags'  => FILTER_REQUIRE_ARRAY,
      ),
			'font'          => array(
				'filter' => FILTER_VALIDATE_BOOLEAN,
				'flags'  => FILTER_REQUIRE_ARRAY,
			),
      'text'          => array(
        'filter' => FILTER_VALIDATE_BOOLEAN,
        'flags'  => FILTER_REQUIRE_ARRAY,
      ),
      'application'          => array(
        'filter' => FILTER_VALIDATE_BOOLEAN,
        'flags'  => FILTER_REQUIRE_ARRAY,
      ),
      'expires_days'         => array(
        'filter' => FILTER_VALIDATE_INT,
        'flags'  => FILTER_REQUIRE_ARRAY,
      ),
    );

    $settings = filter_var_array( $settings, $args );
    return $settings;
  }

	public function parse_expires_headers_main_settings($settings) {
    $args = array(
			'expires_headers' => array(
        'filter' => FILTER_VALIDATE_BOOLEAN,
      ),
		);

    $settings = filter_var_array( $settings, $args );
    return $settings;
  }

  public function init_general_defaults() {
		$defaults = array(
			'general' => array(
        'image' => true,
        'audio' => false,
        'video' => false,
				'font' => false,
        'text' => false,
        'application' => false,
			),
			'image'   => array(
        'gif' => true,
        'ico' => true,
        'jpeg'=> true,
        'jpg'=> true,
        'png'=> true,
        'tiff'=> true,
        'webp'=>true,
			),
			'audio'       => array(
        'dct' => true,
        'gsm' => true,
        'mp3'=> true,
        'ogg'=> true,
        'raw'=> true,
        'vox'=> true,
        'wav'=>true,
			),
			'video'         => array(
        '3gp' => true,
        'avi' => true,
        'flv'=> true,
        'mkv'=> true,
        'mp4'=> true,
        'webm'=> true,
        'wmv'=>true,
			),
			'font' => array(
        'otf' => true,
				'woff' => true,
				'woff2' => true,
			),
			'text'   =>array(
        'css' => true,
      ),
			'application' => array(
        'js' => true,
				'javascript' => true,
				'x-javascript' => true,
      ),
			'expires_days' => array (
        'image' => 30,
        'audio' => 30,
        'video' => 30,
				'font' => 30,
        'text' => 30,
        'application' => 30,
      ),
		);
    return $defaults;
	}
}
