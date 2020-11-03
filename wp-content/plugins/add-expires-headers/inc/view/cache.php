<?php

/* controlling view of cache settings*/
if ( !defined( 'ABSPATH' ) ) {
    die;
}
$errors = '';
/*if (is_null(get_option('aeh_main_setting'))){
	$aeh_main_setting = $main_setting;
}else{
	$aeh_main_setting = get_option('aeh_main_setting');
}*/

if ( !get_option( 'aeh_expires_headers_settings' ) ) {
    $aeh_expires_headers_settings = $defaults;
} else {
    $aeh_expires_headers_settings = get_option( 'aeh_expires_headers_settings' );
}


if ( isset( $_POST['aeh_expires_headers_submit'] ) ) {
    foreach ( $day_settings as $key => $value ) {
        
        if ( !empty($_POST['aeh_expires_headers_settings']['expires_days'][$key]) ) {
            
            if ( is_numeric( $_POST['aeh_expires_headers_settings']['expires_days'][$key] ) && $_POST['aeh_expires_headers_settings']['expires_days'][$key] > 0 ) {
                $aeh_expires_headers_settings['expires_days'][$key] = esc_sql( (int) $_POST['aeh_expires_headers_settings']['expires_days'][$key] );
            } else {
                $errors .= 'Please enter an positive integer amount for the "Number of Days" in "' . $key . '" file format<br/>';
            }
        
        } else {
            $errors .= 'Please enter an positive integer amount for the "Number of Days" in "' . $key . '" file format<br/>';
            $aeh_expires_headers_settings['expires_days'][$key] = $_POST['aeh_expires_headers_settings']['expires_days'][$key];
        }
    
    }
    
    if ( strlen( $errors ) > 0 ) {
        echo  "<script>\r\n\t\t\t\t\t    jQuery(document).ready(function(){\r\n\t\t\t\t\t       M.toast({html: 'Please correct following Errors:', classes: 'rounded red', displayLength:6000});\r\n\t\t\t\t\t\t\t\t M.toast({html: '" . $errors . "', classes: 'rounded red', displayLength:8000});\r\n\t\t\t\t\t    });\r\n\t\t\t\t\t</script>" ;
    } else {
        //$aeh_main_setting = isset($_POST['aeh_main_setting'])? $_POST['aeh_main_setting']:false;//isset($_POST['aeh_main_settings']['expires_headers'])? $_POST['aeh_main_settings']['expires_headers']:'';
        //if(!is_null($aeh_main_setting)){
        //update_option ('aeh_main_setting',$aeh_main_setting);
        //}
        $aeh_expires_headers_settings = AEH_Settings::get_instance()->parse_expires_headers_settings( $_POST['aeh_expires_headers_settings'] );
        if ( $aeh_expires_headers_settings ) {
            update_option( 'aeh_expires_headers_settings', $aeh_expires_headers_settings );
        }
        echo  "<script>\r\n\t\t\t\t    jQuery(document).ready(function(){\r\n\t\t\t\t       M.toast({html: 'Setting Saved!', classes: 'rounded teal', displayLength:4000});\r\n\t\t\t\t    });\r\n\t\t\t\t</script>" ;
        //Now let's modify the .htaccess file
        $write_result = AEH_Pro::get_instance()->main()->write_to_htaccess();
        
        if ( $write_result ) {
            echo  "<script>\r\n\t\t\t\t\t    jQuery(document).ready(function(){\r\n\t\t\t\t\t       M.toast({html: '.htaccess file was successfully updated!', classes: 'rounded teal', displayLength:4000});\r\n\t\t\t\t\t    });\r\n\t\t\t\t\t</script>" ;
        } else {
            echo  "<script>\r\n\t\t\t\t\t\t\tjQuery(document).ready(function(){\r\n\t\t\t\t\t\t\t\t M.toast({html: 'Unable to update changes. Make sure your .htaccess file is editable!', classes: 'rounded teal', displayLength:4000});\r\n\t\t\t\t\t\t\t});\r\n\t\t\t\t\t</script>" ;
        }
    
    }

}

?>

<div class="col s12 aeh-options">
	<form action="" method="POST">
		<div class="col s12" style="margin-top:15px">
			<h5 class="left margin-zero" style="margin:0px">Cache Settings</h5>
			<?php 
if ( dd_aeh()->is_plan( 'pro' ) ) {
    ?>
				<div class="switch right">
				    <a href="https://www.addexpiresheaders.com/technical-support/" target="_blank" style="margin-left:15px" class="waves-effect waves-light btn-small right"><i class="material-icons left">message</i>Support</a>
				</div>
    <?php 
}
?>
		<?php 

if ( dd_aeh()->is_not_paying() ) {
    ?>
			<div class="switch right">
				<a class="waves-effect waves-light btn-small right" href="<?php 
    echo  dd_aeh()->get_upgrade_url() ;
    ?>"><i class="material-icons left">local_offer</i>Sign-up for Pro Version!</a>
			</div>
	<?php 
}

?>
		</div>
		<div class="clearfix" style="clear:both"></div>
		<div class="divider" style="margin-top:15px"></div>
		<?php 
foreach ( $general_settings as $key => $value ) {
    ?>
		<div class="row">
      <div class="col m4 s12" style="margin-top:15px">
				<div class="switch">
				  <label>
				    <input type="checkbox" name="aeh_expires_headers_settings[general][<?php 
    echo  $key ;
    ?>]" <?php 
    checked( isset( $aeh_expires_headers_settings['general'][$key] ) );
    ?>>
				    <span class="lever"></span>
						<span><?php 
    echo  ucfirst( $key ) ;
    ?></span>
				  </label>
				</div>
			</div>
      <div class="col m4 s12">
				<div class="row">
					<?php 
    $col = array_chunk( ${$key . '_types'}, ceil( count( ${$key . '_types'} ) / 2 ), true );
    if ( !empty($col) ) {
        foreach ( $col as $single_col ) {
            ?>
									<div class="col m6 s6" style="margin-top:10px">
                  <?php 
            foreach ( $single_col as $key1 => $value1 ) {
                ?>
										<p>
											<label>
								        <input type="checkbox" name="aeh_expires_headers_settings[<?php 
                echo  $key ;
                ?>][<?php 
                echo  $key1 ;
                ?>]" <?php 
                checked( isset( $aeh_expires_headers_settings[$key][$key1] ) );
                ?> />
								        <span><?php 
                echo  $key1 ;
                ?></span><?php 
                if ( !empty($a2) && is_array( $a2 ) ) {
                    if ( array_key_exists( $key1, $a2 ) ) {
                        echo  '<span class="new badge" data-badge-caption="pro"></span>' ;
                    }
                }
                ?>
								      </label>
										</p>
									<?php 
            }
            ?>
								</div>
								<?php 
        }
    }
    ?>
				 </div>
			</div>
			<div class="col m4 s12" style="margin-top:15px">
				<div class="input-field">
					<input type="number" min="0" step="1" name="aeh_expires_headers_settings[expires_days][<?php 
    echo  $key ;
    ?>]" placeholder="In no. of days" value="<?php 
    if ( isset( $aeh_expires_headers_settings['expires_days'][$key] ) ) {
        echo  $aeh_expires_headers_settings['expires_days'][$key] ;
    }
    ?>">
	        <label for="aeh_expires_headers_settings[expires_days][<?php 
    echo  $key ;
    ?>]">Expiry Time in Days</label>
				</div>
			</div>
			<div class="clearfix" style="clear:both"></div>
			<?php 
    
    if ( dd_aeh()->is_not_paying() ) {
        ?>
				<div>
					<p class="center-align">Want to add more <?php 
        echo  $key ;
        ?> file types? <a href="<?php 
        echo  dd_aeh()->get_upgrade_url() ;
        ?>">upgrade now!</a></p>
				</div>
			<?php 
    }
    
    ?>
    </div>
		<div class="divider" style="margin-top:15px"></div>
	<?php 
}
?>
	<div class="row center-align">
		<button class="btn waves-effect waves-light" style="margin-top:15px"  type="submit" name="aeh_expires_headers_submit">Submit</button>
	</div>
	</form>
</div>
