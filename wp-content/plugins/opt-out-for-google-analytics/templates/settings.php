<div class="wrap" id="gaoo">
    <h2><?php esc_html_e( 'Google Analytics Opt-Out', 'ga-opt-out' ); ?></h2>

    <?php $this->messages->render( true ); ?>

    <p>
		<?php printf( esc_html__( "Use this shortcode on every page or post you want, to display the GA Opt Out: %s", 'ga-opt-out' ), '<code title="' . esc_attr__( 'Click to copy the shortcode!', 'ga-opt-out' ) . '">' . esc_html( GAOO_SHORTCODE ) . '</code>' ); ?>
        <span class="gaoo-clipboard dashicons dashicons-admin-page" title="<?php esc_attr_e( 'Click to copy the shortcode!', 'ga-opt-out' ); ?>" data-copy="<?php echo esc_attr( GAOO_SHORTCODE ); ?>"></span>
        <br />

        <?php
            $language = explode( '_', GAOO_LOCALE );
            $language = array_shift( $language );
        ?>
        <small><?php printf( __( "Do you have a data processing agreement for Google Analytics? <a href='%s' target='_blank'>More infos.</a>", 'ga-opt-out' ), esc_url( 'https://support.google.com/analytics/answer/3379636?hl=' . ( empty( $language ) ?: $language ) ) ); ?></small>
    </p>

    <?php
        $_check   = '';
        $checked  = 0;
        $dontknow = 0;

        foreach ( $checklist as $check ):
            if ( ! empty( $check[ 'url' ] ) ) {
                $check[ 'label' ] = '<a title="' . esc_html__( "Got to page", 'ga-opt-out' ) . '" href="' . esc_url( $check[ 'url' ] ) . '">' . $check[ 'label' ] . '<span class="dashicons dashicons-external"></span></a>';
            }

            $_check .= sprintf( '<li class="%s">%s</li>', ( false !== $check[ 'checked' ] ? ( is_null( $check[ 'checked' ] ) ? 'dontknow' : 'check' ) : '' ), $check[ 'label' ] );

            if ( ! empty( $check[ 'checked' ] ) ) {
                $checked++;
            }
            elseif ( is_null( $check[ 'checked' ] ) ) {
                $dontknow++;
            }
        endforeach;
    ?>

    <div id="gaoo-checklist" class="<?php echo( ( count( $checklist ) - $dontknow ) == $checked ? ( $dontknow == 0 ? 'done' : 'dontknow' ) : 'todo' ); ?>">
        <h2><?php esc_html_e( 'Current status of this website', 'ga-opt-out' ); ?></h2>
        <ul class="gaoo-check"><?php echo $_check; ?></ul>
    </div>

    <?php if ( ! empty( $promotion ) && ! empty( $promotion[ 'link' ] ) ): ?>
        <a href="<?php echo esc_url( $promotion[ 'link' ] ); ?>" target="_blank" class="gaoo-promo">
            <?php if ( ! empty( $promotion[ 'img' ] ) ): ?>
                <img src="<?php echo esc_url( $promotion[ 'img' ] ); ?>" alt="<?php echo empty( $promotion[ 'link_txt' ] ) ? '' : esc_attr( $promotion[ 'link_txt' ] ); ?>">
            <?php else: ?>
                <?php echo $promotion[ 'link_txt' ]; ?>
            <?php endif; ?>
        </a>
    <?php endif; ?>

    <form method="post">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e( "Status", 'ga-opt-out' ); ?></label>
                </th>
                <td>
                    <label for="gaoo-status">
                        <input type="checkbox" name="gaoo[status]" id="gaoo-status" value="on" <?php checked( true, ( $status == 'on' || empty( $status ) ) ); ?> />
                        <?php esc_html_e( "Enable Opt-Out function", 'ga-opt-out' ); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label><?php esc_html_e( "UA-Code", 'ga-opt-out' ); ?></label>
                </th>
                <td>
                    <fieldset>
						<?php $is_manual = ( $ga_plugin == 'manual' || empty( $ga_plugin ) ); ?>

                        <label for="ga-plugin-manual">
                            <input type="radio" name="gaoo[ga_plugin]" id="ga-plugin-manual" value="manual" <?php checked( true, $is_manual ); ?> />
                            <input type="text" id="gaoo-ua-code" name="gaoo[ua_code]" class="text" placeholder="UA-XXXXXX-Y" value="<?php echo esc_attr( $ua_code ); ?>" />
                        </label>

                        <p <?php echo $is_manual ? '' : 'class="hide"'; ?>>
                            <textarea id="ga-plugin-tracking-code" class="regular-text" rows="10" name="gaoo[tracking_code]" placeholder="<?php esc_attr_e( 'Enter here your tracking code for Google Analytics to insert it on your whole website. Leave empty if you do not want it.', 'ga-opt-out' ); ?>"><?php echo stripslashes( $tracking_code ); ?></textarea>
                        </p>
                        <br />

                        <?php foreach ( $ga_plugins as $key => $info ): ?>
                            <label for="ga-plugin-<?php echo $key; ?>">
                                <input type="radio" name="gaoo[ga_plugin]" id="ga-plugin-<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php echo ( ! isset( $info[ 'is_active' ] ) || $info[ 'is_active' ] ) ? '' : 'disabled="disabled"'; ?> <?php checked( $key, $ga_plugin ); ?> />
                                <?php echo esc_html( $info[ 'label' ] ); ?>

                                <?php
                                    $link_text = null;

                                    if ( isset( $info[ 'is_active' ] ) && ! $info[ 'is_active' ] ):
                                        $link_text = esc_html__( 'Not activated', 'ga-opt-out' );
                                        $url       = esc_url( $info[ 'url_activate' ] );
                                    endif;

                                    if ( isset( $info[ 'is_active' ] ) && ! $info[ 'is_installed' ] ):
                                        $link_text = esc_html__( 'Not installed', 'ga-opt-out' );
                                        $url       = esc_url( $info[ 'url_install' ] );
                                    endif;
                                ?>

                                <?php if ( ! empty( $link_text ) ): ?>
                                    <small>(<a href="<?php echo $url; ?>" target="<?php echo isset( $info[ 'target' ] ) ? esc_attr( $info[ 'target' ] ) : '_self'; ?>"><?php echo $link_text; ?></a>)</small>
                                <?php endif; ?>
                            </label>
                            <br />
                        <?php endforeach; ?>
                    </fieldset>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label><?php esc_html_e( "Status check", 'ga-opt-out' ); ?></label>
                </th>
                <td>
                    <fieldset>
                        <label>
							<?php esc_html_e( "Run the status check", 'ga-opt-out' ); ?>
                            <select name="gaoo[status_intervall]">
                                <option value="daily" <?php selected( $status_intervall, 'daily' ); ?>><?php esc_html_e( 'daily', 'ga-opt-out' ); ?></option>
                                <option value="weekly" <?php selected( $status_intervall, 'weekly' ); ?>><?php esc_html_e( 'weekly', 'ga-opt-out' ); ?></option>
                                <option value="monthly" <?php selected( $status_intervall, 'monthly' ); ?>><?php esc_html_e( 'monthly', 'ga-opt-out' ); ?></option>
                            </select>
                        </label>
                        <br>

                        <label>
							<?php esc_html_e( "Send mail if the status check failed, to", 'ga-opt-out' ); ?>
                            <input type="text" class="regular-text" name="gaoo[status_mails]" id="gaoo-status-mails" value="<?php echo esc_attr( $status_mails ); ?>" title="<?php esc_attr_e( "Leave empty to disable.", 'ga-opt-out' ); ?>" placeholder="<?php esc_attr_e( "e.g. admin@example.org", 'ga-opt-out' ); ?>" <?php echo empty( $status_mails_sync ) ?: 'readonly'; ?>>
                        </label>

                        <label>
                            <small>
                                <input type="checkbox" name="gaoo[status_mails_sync]" value="1" id="gaoo-status-mails-sync" <?php checked( $status_mails_sync, 1 ); ?> data-mail="<?php echo esc_attr( $wp_admin_mail ); ?>">
                                <?php esc_html_e( "synchronize with WordPress admin mail", 'ga-opt-out' ); ?>
                                <a href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>" target="_blank" title="<?php esc_html_e( "Go to settings", 'ga-opt-out' ); ?>" class="dashicons dashicons-external"></a></label>
                        </small>
                        </label>
                        <br>

                        <label for="gaoo-disable-monitoring"><input type="checkbox" name="gaoo[disable_monitoring]" value="1" id="gaoo-disable-monitoring" <?php checked( $disable_monitoring, 1 ); ?>> <?php esc_html_e( "Suppress the message in the dashboard if the settings are not data protection compliant.", 'ga-opt-out' ); ?></label>
                    </fieldset>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label><?php esc_html_e( "Force reload", 'ga-opt-out' ); ?></label>
                </th>
                <td>
                    <label for="gaoo-force-reload"><input type="checkbox" name="gaoo[force_reload]" value="1" id="gaoo-force-reload" <?php checked( $force_reload, 1 ); ?>> <?php esc_html_e( "Force page reload after the click on the link.", 'ga-opt-out' ); ?></label>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="gaoo-privacy-page"><?php esc_html_e( "Privacy Policy Page", 'ga-opt-out' ); ?></label>
                </th>
                <td>
					<?php wp_dropdown_pages( array( 'selected' => empty( $privacy_page_id ) ? 0 : $privacy_page_id, 'id' => 'gaoo-privacy-page', 'name' => 'gaoo[privacy_page_id]', 'show_option_none' => esc_html__( "— Select —", 'ga-opt-out' ), 'option_none_value' => 0, 'post_status' => 'publish,draft', ) ); ?>

                    <a href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . $privacy_page_id ) ); ?>" class="<?php echo empty( $privacy_page_id ) ? 'hide' : ''; ?> gaoo-edit-link dashicons dashicons-welcome-write-blog" target="_blank" title="<?php esc_html_e( 'Edit selected page', 'ga-opt-out' ); ?>"></a>

                    <p>
                        <small><?php esc_html_e( "Select the page, where you will use this shortcode, for the monitoring.", 'ga-opt-out' ); ?></small>
                    </p>

                    <?php if ( version_compare( get_bloginfo( 'version' ), '4.9.6', '>=' ) ): ?>
                        <p>
                            <label for="gaoo-wp-privacy-page"><input type="checkbox" name="gaoo[wp_privacy_page]" value="1" id="gaoo-wp-privacy-page" data-id="<?php echo esc_attr( $wp_privacy_page_id ); ?>" <?php checked( $wp_privacy_page, 1 ); ?>> <?php esc_html_e( "Synchronize with WordPress Privacy Policy page.", 'ga-opt-out' ); ?>
                                <a href="<?php echo esc_url( admin_url( 'privacy.php' ) ); ?>" target="_blank" title="<?php esc_html_e( "Go to settings", 'ga-opt-out' ); ?>" class="dashicons dashicons-external"></a></label>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="gaoo-link-deactivate"><?php esc_html_e( "Text of link for deactivate", 'ga-opt-out' ); ?></label>
                </th>
                <td>
                    <input type="text" id="gaoo-link-deactivate" name="gaoo[link_deactivate]" class="regular-text" value="<?php echo esc_attr( $link_deactivate ); ?>" />
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="gaoo-popup-deactivate"><?php esc_html_e( "Popup Text for deactivate", 'ga-opt-out' ); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="gaoo-popup-deactivate" name="gaoo[popup_deactivate]" class="regular-text" value="<?php echo esc_attr( $popup_deactivate ); ?>" /> <span class="gaoo-empty-popup <?php echo empty( $popup_deactivate ) ? 'empty' : ''; ?>" title="<?php esc_attr_e( 'Click to empty field, to disable the popup.', 'ga-opt-out' ); ?>">&#10006;</span>
                    <p>
                        <small><?php esc_html_e( "Leave empty to disable popup", 'ga-opt-out' ); ?></small>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="gaoo-link-activate"><?php esc_html_e( "Text of link for activate", 'ga-opt-out' ); ?></label>
                </th>
                <td>
                    <input type="text" id="gaoo-link-activate" name="gaoo[link_activate]" class="regular-text" value="<?php echo esc_attr( $link_activate ); ?>" />
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="gaoo-popup-activate"><?php esc_html_e( "Popup Text for activate", 'ga-opt-out' ); ?></label>
                </th>
                <td>
                    <input type="text" id="gaoo-popup-activate" name="gaoo[popup_activate]" class="regular-text" value="<?php echo esc_attr( $popup_activate ); ?>" /> <span class="gaoo-empty-popup <?php echo empty( $popup_activate ) ? 'empty' : ''; ?>" title="<?php esc_attr_e( 'Click to empty field, to disable the popup.', 'ga-opt-out' ); ?>">&#10006;</span>
                    <p>
                        <small><?php esc_html_e( "Leave empty to disable popup", 'ga-opt-out' ); ?></small>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="gaoo-custom-css"><?php esc_html_e( "Add custom css", 'ga-opt-out' ); ?></label>
                </th>
                <td>
                    <table>
                        <tr>
                            <td>
                                <textarea id="gaoo-custom-css" name="gaoo[custom_css]" class="regular-text" rows="15"><?php echo wp_strip_all_tags( $custom_css ); ?></textarea>
                                <p>
                                    <small><?php esc_html_e( "This CSS is only inserted where the shortcode is used.", 'ga-opt-out' ); ?></small>
                                </p>
                            </td>
                            <td class="valign-top">
                                <p><strong><?php esc_html_e( 'Overview CSS classes', 'ga-opt-out' ); ?></strong></p>

                                <hr>

                                <p>
                                    <strong>#gaoo-link { }</strong><br>
                                    <?php esc_html_e( 'The link itselfs.', 'ga-opt-out' ); ?>
                                </p>

                                <p>&nbsp;</p>

                                <p>
                                    <strong>.gaoo-link-activate { }</strong><br>
                                    <?php esc_html_e( 'If the user has DISALLOWED tracking and wants to allow it again.', 'ga-opt-out' ); ?>
                                </p>

                                <p>&nbsp;</p>

                                <p>
                                    <strong>.gaoo-link-deactivate { }</strong><br>
                                    <?php esc_html_e( 'If the user has ALLOWED tracking and wants to diallow it again.', 'ga-opt-out' ); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            </tbody>
        </table>

        <?php wp_nonce_field( 'gaoo-settings' ); ?>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( "Save Changes" ); ?>">
        </p>
    </form>
</div>