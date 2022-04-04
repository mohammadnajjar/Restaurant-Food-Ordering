<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !Glf_Utils::$_GLF->is_authenticated() ) {
    require(GLF_PLUGIN_DIR . 'includes/admin/overview/overview.php');
    die();
}
$custom_css_location = Glf_Utils::glf_get_from_wordpress_options( 'default_location', '' );

if ( isset( $_POST[ 'css' ] ) ) {
    if ( !(current_user_can( 'manage_options' ) && isset( $_POST[ '_wpnonce' ] ) && wp_verify_nonce( $_POST[ '_wpnonce' ], 'glf-mor-customize-css' )) ) {
        die( 'Access restricted, security check failed!' );
    }

    $custom_css = json_decode( stripslashes( sanitize_text_field( $_POST[ 'css' ] ) ), true );
    $custom_css_location = stripslashes( sanitize_text_field( $_POST[ 'location' ] ) );

    $temp_custom_css = Glf_Utils::glf_get_locations_custom_css( $custom_css_location );
    $temp_custom_css[ $custom_css[ 'type' ] ] = $custom_css;
    Glf_Utils::glf_set_locations_custom_css( $temp_custom_css, $custom_css_location );
    /*Glf_Utils::$_GLF->custom_css[$custom_css['type']] = $custom_css;
    Glf_Utils::$_GLF->save_user_data(array('custom_css' => Glf_Utils::$_GLF->custom_css));*/

    $glf_wordpress_options = Glf_Utils::glf_wp_options_data();
    $per_user_app_options = $glf_wordpress_options->{'app_options'}[ Glf_Utils::$_GLF->user->id ];
    $per_user_app_options[ 'default_location' ] = $custom_css_location;
    $glf_wordpress_options->{'app_options'}[ Glf_Utils::$_GLF->user->id ] = $per_user_app_options;
    Glf_Utils::glf_database_option_operation( 'update', 'glf_wordpress_options', $glf_wordpress_options );
}
if ( !empty( $_POST[ 'refresh_menu' ] ) ) {
    if ( Glf_Module_Shortcodes::glf_mor_restaurant_menu( $_POST[ 'refresh_menu' ], true ) ) {
        ?>
        <script type="text/javascript">
            alert( 'Menu refreshed' );
        </script>
        <?php
    }
}
if ( !empty( $_POST[ 'refresh_opening_hours' ] ) ) {
    if ( Glf_Module_Shortcodes::glf_mor_restaurant_opening_hours( $_POST[ 'refresh_opening_hours' ], true ) ) {
        ?>
        <script type="text/javascript">
            alert( 'Opening Hours refreshed' );
        </script>
        <?php
    }
}
?>
<div id="glf-consent" data-consent="<?php echo Glf_Utils::glf_get_from_wordpress_options( 'share_usage_data', '' ) ?>"></div>
<div class="glf-settings-tabs">
    <div class="settings-tab active" data-id="settings-customize"><?php _e( 'Customize Widgets', 'menu-ordering-reservations' ); ?></div>
    <div class="settings-tab" data-id="settings-account"><?php _e( 'Account', 'menu-ordering-reservations' ); ?></div>
</div>
<div id="glf-main" class="wrap settings-customize glf-settings-content active">
    <div class="clear"><br></div>
    <div class="glf-d-flex glf-white-box publish"><?php
        if ( count( Glf_Utils::glf_get_restaurants() ) > 1 ) {
            ?>
            <div class="glf-select-label"><?php _e( 'Restaurant', 'menu-ordering-reservations' ); ?></div>
            <?php
        }
        ?>
        <label for="glf_mor_restaurant">
            <?php
            $selected_option = '';
            if ( isset( $custom_css_location ) ) {
                $selected_option = $custom_css_location;
            }
            if ( count( Glf_Utils::glf_get_restaurants() ) > 1 ) {
                Glf_Utils::glf_get_restaurants_dropdown( 'js_glf_mor_ruid', 'ruid', 'uid', $selected_option, 'glfDisplayShortcode' );
            } else {
                ?>
                <span class="single-restaurant"><?php echo Glf_Utils::glf_get_restaurants()[ 0 ]->name; ?></span>
                <input type="hidden" name="ruid" id="js_glf_mor_ruid" value="<?= Glf_Utils::glf_get_restaurants()[ 0 ]->uid; ?>">
                <?php
            } ?>
        </label>
    </div>
    <div class="glf-white-box publish">
        <table class="form-table">
            <tbody>
            <tr class="glf-border-bottom">
                <td colspan="2" class="glf-slim-cell"><strong><?php _e( 'Button Preview', 'menu-ordering-reservations' ); ?></strong></td>
                <td class="glf-slim-cell"><strong><?php _e( 'Shortcode', 'menu-ordering-reservations' ); ?></strong></td>
            </tr>
            <tr class="glf-gray-bg">
                <td class="glf-cell glf-ordering-location" data-location="<?= Glf_Utils::glf_get_restaurants()[ 0 ]->uid ?>">
                    <?= Glf_Module_Shortcodes::add_ordering_shortcode( array( 'rid' => Glf_Utils::glf_get_restaurants()[ 0 ]->uid ), 'settings' ) ?>
                </td>
                <td nowrap="true" class="glf-cell">
                    <a class="glf-customize" href="#" onclick="glf_mor_showThickBox('restaurant_system_customize_button', 'type=ordering')"> <img class="glf-customize-img" src="<?= GLF_PLUGIN_URL . 'assets/images/configure.png' ?>"><strong><?php _e( 'Customize', 'menu-ordering-reservations' ); ?></strong></a>
                </td>
                <td nowrap="true" class="glf-cell">
                        <input type="text" class="glf-input-disabled" readonly id="js_glf_mor_ordering" size="78">
                        <button class="copy-ordering-button glf-copy" value="Copy" data-clipboard-action="copy" data-clipboard-target="#js_glf_mor_ordering"><?php _e( 'Copy', 'menu-ordering-reservations' ); ?></button>
                </td>
            </tr>
            <tr class="glf-gray-bg">
                <td class="glf-cell glf-reservations-location" data-location="<?= Glf_Utils::glf_get_restaurants()[ 0 ]->uid ?>">
                    <?= Glf_Module_Shortcodes::add_reservations_shortcode( array( 'rid' => Glf_Utils::glf_get_restaurants()[ 0 ]->uid ), 'settings' ) ?>
                </td>
                <td nowrap="true" class="glf-cell">
                    <a class="glf-customize" href="#" onclick="glf_mor_showThickBox('restaurant_system_customize_button', 'type=reservations')"> <img class="glf-customize-img" src="<?= GLF_PLUGIN_URL . 'assets/images/configure.png' ?>"><strong><?php _e( 'Customize', 'menu-ordering-reservations' ); ?></strong></a>
                </td>
                <td nowrap="true" class="glf-cell">
                    <input type="text" class="glf-input-disabled" readonly id="js_glf_mor_reservations" size="78">
                    <button class="copy-reservations-button glf-copy" value="Copy" data-clipboard-action="copy" data-clipboard-target="#js_glf_mor_reservations"><?php _e( 'Copy', 'menu-ordering-reservations' ); ?></button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="glf-white-box publish">
        <table class="form-table">
            <tbody>
            <tr class="glf-border-bottom">
                <td class="glf-slim-cell">
                    <strong><?php _e( 'Menu Shortcode', 'menu-ordering-reservations' ); ?></strong></td>
            </tr>
            <tr class="glf-gray-bg">
                <td nowrap="true" class="glf-cell" style="text-align: center;">
                    <input type="text" class="glf-input-disabled" readonly id="js_glf_mor_full_menu" style="width:91%; margin-top: 9px;">
                    <button class="copy-full-menu-button glf-copy" value="Copy" data-clipboard-action="copy" data-clipboard-target="#js_glf_mor_full_menu"><?php _e( 'Copy', 'menu-ordering-reservations' ); ?></button>
                    <div style="box-sizing: border-box; white-space: normal; display: flex;margin-top: 16px;margin-left: 0;align-items: center;">
                        <button class="button button-primary" onClick="glfRefreshShortcode(this)" style="margin-left:0;" data-name="refresh_menu" data-page="<?php menu_page_url( 'glf-publishing', true ); ?>" style="margin-right: 18px; margin-left: 20px;"><?php _e( 'Refresh menu', 'menu-ordering-reservations' ); ?>
                        </button><span style="font-style: normal; line-height: 1.3; margin-left:36px; text-align: left;"><?php _e( 'Hit Refresh menu to publish your menu edits on the website.', 'menu-ordering-reservations' ); ?></span>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="glf-white-box publish">
        <table class="form-table">
            <tbody>
            <tr class="glf-border-bottom">
                <td class="glf-slim-cell">
                    <strong><?php _e( 'Opening Hours Shortcode', 'menu-ordering-reservations' ); ?></strong></td>
            </tr>
            <tr class="glf-gray-bg">
                <td nowrap="true" class="glf-cell" style="text-align: center;">
                    <input type="text" class="glf-input-disabled" readonly id="js_glf_mor_opening_hours" style="width:91%; margin-top: 9px;">
                    <button class="copy-full-menu-button glf-copy" value="Copy" data-clipboard-action="copy" data-clipboard-target="#js_glf_mor_opening_hours"><?php _e( 'Copy', 'menu-ordering-reservations' ); ?></button>
                    <div style="box-sizing: border-box; white-space: normal; display: flex;margin-top: 16px;margin-left: 0;align-items: center;">
                        <button class="button button-primary" onClick="glfRefreshShortcode(this)" style="margin-left:0;" data-name="refresh_opening_hours" data-page="<?php menu_page_url( 'glf-publishing', true ); ?>" style="margin-right: 18px; margin-left: 20px;"><?php _e( 'Refresh opening hours', 'menu-ordering-reservations' ); ?>
                        </button><span style="font-style: normal; line-height: 1.3; margin-left:36px; text-align: left;"><?php _e( 'Hit Refresh opening hours to publish your opening hours edits on the website.', 'menu-ordering-reservations' ); ?></span>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="settings-account glf-settings-content">
    <div class="glf-wrapper">
        <div class="glf-section glf-spacing-v1 flx-col">
            <div class="section-title big"><?php _e( 'You are already logged in', 'menu-ordering-reservations' ); ?></div>
            <div class="glf-section-content glf-white-back flx-col flx-align-left">
                <div class="glf-form" style="padding:0px;">
                    <div class="glf-form-notification info" data-type="success"><?php _e( 'If you disconnect your account, the See Menu & Order and Table Reservation buttons that are already published on your pages will remain as they are. However, you will not longer be able to make changes to your restaurant profile from within the WordPress interface.', 'menu-ordering-reservations' ); ?></div>
                </div>
            </div>
            <div class="glf-section-content glf-w-544 glf-white-back flx-col flx-align-left glf-spacing-v1">
                <form class="glf-form account">
                    <div class="glf-form-field">
                        <div class="label"><?php _e( 'Email', 'menu-ordering-reservations' ); ?></div>
                        <div class="field">
                            <input id="user_email" name="user_email" type="text" value="<?php echo Glf_Utils::$_GLF->user->email; ?>" readonly/>
                        </div>
                    </div>
                    <div class="glf-form-field">
                        <div class="label"><?php _e( 'Installation ID', 'menu-ordering-reservations' ); ?></div>
                        <div class="field">
                            <input id="user_install_id" name="user_install_id" type="text" value="<?php echo Glf_Utils::$_GLF->installation_id; ?>" readonly/>
                        </div>
                    </div>


                    <div class="glf-form-field flx-row form-footer ">
                        <div class="glf-filed-submit" style="margin-left: auto;">
                            <input type="submit" data-action="glf_form_disconnect" data-id="admin.php?page=glf-overview" value="Disconnect" class="button v2">
                        </div>
                    </div>
                </form>
            </div>
            <div class="glf-section-content account glf-w-544 glf-white-back flx-col flx-align-left glf-spacing-v1">
                <div class="section-title"><?php _e( 'Help us improve GloriaFood', 'menu-ordering-reservations' ); ?></div>
                <div class="glf-overlay-text glf-gap-top-8 glf-gap-bottom-24">
                    <span><?php _e( 'Become a super contributor by opting in to share non-sensitive plugin data and to receive periodic email updates from us.', 'menu-ordering-reservations' ); ?><a href="https://www.gloriafood.com/privacy" target="_blank"><?php _e( 'Learn more', 'menu-ordering-reservations' ); ?></a></span>
                </div>
                <div class="glf-switch-control">
                    <?php
                    $share_checked = (Glf_Utils::glf_get_from_wordpress_options( 'share_usage_data', '' ) === 'yes' ? 'checked' : '' );
                    ?>
                    <label for="share_usage_data" class=" glf-button-action" data-option="share_usage_data" data-action="glf_switch_set">
                        <input id="share_usage_data" name="share_usage_data" type="checkbox" autocomplete="off" <?php echo $share_checked; ?>>
                        <div class="switch-control">
                            <div class="switch-graphic"></div>
                        </div>
                    </label>
                    <div class="switch-label"><?php _e( 'Share usage data', 'menu-ordering-reservations' ); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>


<form method="post" id="glf-customize-button">
    <?php wp_nonce_field( 'glf-mor-customize-css' ) ?>
    <input name="location" id="glf-button-custom-css-location" type="hidden">
    <input name="css" id="glf-button-custom-css" type="hidden">
</form>


    <script>
        var clipboard1 = new Clipboard( '.copy-ordering-button' );
        var clipboard2 = new Clipboard( '.copy-reservations-button' );
        var clipboard3 = new Clipboard( '.copy-full-menu-button' );

        clipboard1.on( 'success', function ( e ) {
            alert( 'Code copied!' )
        } );

        clipboard1.on( 'error', function ( e ) {
            alert( 'Error! Please manually copy the code.' )
        } );

        clipboard2.on( 'success', function ( e ) {
            alert( 'Code copied!' )
        } );

        clipboard2.on( 'error', function ( e ) {
            alert( 'Error! Please manually copy the code.' )
        } );

        clipboard3.on( 'success', function ( e ) {
            alert( 'Code copied!' )
        } );

        clipboard3.on( 'error', function ( e ) {
            alert( 'Error! Please manually copy the code.' )
        } );


        jQuery( document ).find( '.glf-button' ).css( 'pointer-events', 'none' );

        document.addEventListener( "DOMContentLoaded", function ( event ) {
            if( document.readyState === 'interactive' ) {
                glfDisplayShortcode();
            }
        } );
    </script>

