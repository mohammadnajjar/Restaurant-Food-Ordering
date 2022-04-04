<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
?>
<?php
$screen_login = "screen-active";
$screen_forgot = '';
$screen_sign_up = '';

if( isset( $_POST[ 'screen_sign_up' ] ) && $_POST[ 'screen_sign_up' ] === 'true' ){
    $screen_login = '';
    $screen_sign_up = 'screen-active';
}

?>
<style type="text/css">
    #wpfooter, #wpadminbar, #adminmenumain {
        display: none;
    }

    #wpcontent, #wpfooter {
        margin-left: 0px;
        padding-left: 0px;
    }

    html.wp-toolbar {
        padding-top: 0px;
    }

    #wpbody-content {
        padding-bottom: 0px;
    }

</style>
<?php
// Login Screen

//check if empty installation_id
Glf_Utils::$_GLF->installation_id = get_option( 'glf_mor_installation_id', '' );
if( empty( Glf_Utils::$_GLF->installation_id )  ){
    Glf_Utils::$_GLF->installation_id = wp_generate_uuid4();
    update_option( 'glf_mor_installation_id', Glf_Utils::$_GLF->installation_id );
}
?>

<div id="screen_login" class="screen <?php echo $screen_login; ?> glf-section-content glf-w-544 glf-white-back flx-col flx-align-center glf-spacing-v1">
    <div class="section-title"><?php _e( 'Log in to your account', 'menu-ordering-reservations' ); ?></div>
    <?php
    glf_notification_section_html();
    ?>
    <form class="glf-form">
        <div class="glf-form-field">
            <div class="label"><?php _e( 'Email', 'menu-ordering-reservations' ); ?></div>
            <div class="field">
                <input id="email" name="email" type="text" />
            </div>
        </div>
        <div class="glf-form-field">
            <div class="label"><?php _e( 'Password', 'menu-ordering-reservations' ); ?></div>
            <div class="field">
                <input id="password" name="password" type="password" />
            </div>
        </div>

        <?php
        if ( isset( $_GET[ 'debug' ] ) && $_GET[ 'debug' ] === 'true' ) {
            ?>
            <div class="glf-form-field">
                <div class="label">Connection type:</div>
                <div class="field">
                    <select id="glf_connection_type" name="glf_connection_type" value="production" style="margin-top: 10px;">
                        <option value="production">Production</option>
                        <option value="staging">Staging</option>
                    </select>
                </div>
            </div>
            <?php
        }
        ?>

        <div class="glf-form-field flx-row form-footer">
            <div class="glf-filed-checkbox">
                <input id="checkbox_log" name="checkbox_log" type="checkbox">
                <label for="checkbox_log"><?php _e( 'I’m setting up the restaurant for a client', 'menu-ordering-reservations' ); ?></label>
            </div>
            <div class="glf-filed-submit">
                <?php wp_nonce_field( 'glf-mor-auth' ) ?>
                <input type="hidden" id="partner_program" name="partner_program" value="false">
                <input type="hidden" name="source" value="WORDPRESS">
                <input type="hidden" name="installation_id" value="<?= Glf_Utils::$_GLF->installation_id; ?>">
                <input type="submit" data-action="glf_form_login" value="<?php _e( 'Login', 'menu-ordering-reservations' ); ?>" class="button">
            </div>
        </div>

        <div class="glf-form-field" style="">
            <div class="glf-line-spacer"></div>
            <div class="glf-box glf-space-between">
                <a href="#" class="glf-button-action" data-id="screen_forgot_password" data-action="screen_change" target="_parent"><?php _e( 'Lost your password', 'menu-ordering-reservations' ); ?></a>
                <a href="#" class="glf-button-action" data-id="screen_sign_up" data-action="screen_change" target="_parent"><?php _e( 'Create a new account', 'menu-ordering-reservations' ); ?></a>
            </div>
        </div>
    </form>
</div>
<?php
//  Forgot Password Screen
?>
<div id="screen_forgot_password" class="screen <?php echo $screen_forgot; ?> glf-section-content glf-w-544 glf-white-back flx-col flx-align-center glf-spacing-v1">
    <div class="section-title"><?php _e( 'Reset password', 'menu-ordering-reservations' ); ?></div>
    <form class="glf-form">
        <div class="glf-form-field">
            <div class="label"><?php _e( 'Email', 'menu-ordering-reservations' ); ?></div>
            <div class="field">
                <input id="email" name="email" type="text" />
            </div>
        </div>


        <div class="glf-form-field flx-row  glf-align-end">
            <div class="glf-filed-submit">
                <?php wp_nonce_field( 'glf-mor-auth' ) ?>
                <input type="hidden" name="source" value="WORDPRESS">
                <input type="hidden" name="installation_id" value="<?= Glf_Utils::$_GLF->installation_id; ?>">
                <input type="submit" data-action="glf_form_forgot_password" value="<?php _e( 'Get New Password', 'menu-ordering-reservations' ); ?>" class="button">
            </div>
        </div>

        <div class="glf-form-field" style="margin-top: 16px;">
            <div class="glf-line-spacer"></div>
            <div class="glf-box">
                <a href="#" class="glf-button-action" data-id="screen_login" data-action="screen_change" target="_parent"><?php _e( 'Login', 'menu-ordering-reservations' ); ?></a>
            </div>
        </div>
    </form>
</div>
<?php
//  Sign Up Screen
?>
<div id="screen_sign_up" class="screen <?php echo $screen_sign_up; ?> glf-section-content glf-w-544 glf-white-back flx-col flx-align-center glf-spacing-v1" style="min-height: 500px;">
    <div class="section-title"><?php _e( 'Let’s start by creating a GloriaFood account', 'menu-ordering-reservations' ); ?></div>
    <?php
    glf_notification_section_html();
    ?>
    <form id="signup_form" class="glf-form">
        <div class="glf-form-field">
            <div class="label"><?php _e( 'Restaurant Name', 'menu-ordering-reservations' ); ?></div>
            <div class="field">
                <input id="restaurant_name" name="restaurant_name" type="text" />
            </div>
        </div>
        <div class="glf-form-field">
            <div class="label"><?php _e( 'Email', 'menu-ordering-reservations' ); ?></div>
            <div class="field">
                <input id="email" name="email" type="text" />
            </div>
        </div>
        <div class="glf-form-field glf-w-50 left">
            <div class="label"><?php _e( 'First Name', 'menu-ordering-reservations' ); ?></div>
            <div class="field">
                <input id="first_name" name="first_name" type="text" />
            </div>
        </div>
        <div class="glf-form-field glf-w-50 right">
            <div class="label"><?php _e( 'Last Name', 'menu-ordering-reservations' ); ?></div>
            <div class="field">
                <input id="last_name" name="last_name" type="text" />
            </div>
        </div>
        <div class="glf-form-field">
            <div class="label"><?php _e( 'Password', 'menu-ordering-reservations' ); ?></div>
            <div class="field">

                <input id="password" name="password" type="password" />
            </div>
        </div>


        <div class="glf-form-field flx-row form-footer">
            <div class="glf-filed-checkbox">
                <input id="checkbox_sign" name="checkbox_sign" type="checkbox">
                <label for="checkbox_sign"><?php _e( 'I’m setting up the restaurant for a client', 'menu-ordering-reservations' ); ?></label>
            </div>
            <div class="glf-filed-submit">
                <?php wp_nonce_field( 'glf-mor-auth' ) ?>
                <input type="hidden" id="partner_program" name="partner_program" value="false">
                <input type="hidden" name="source" value="WORDPRESS">
                <input type="hidden" name="installation_id" value="<?= Glf_Utils::$_GLF->installation_id; ?>">
                <input type="hidden" name="account_source" value="wp-plugin">
                <input type="hidden" name="account_type" value="restaurant">
                <input type="hidden" name="campaign" value="">
                <input type="hidden" name="keyword" value="">
                <input type="hidden" name="language_code" value="<?= get_user_locale(); ?>">
                <input type="hidden" name="keyword" value="">
                <input type="hidden" name="phone" value="">
                <input type="hidden" name="signup_source" value="<?php echo('gloriafood-restaurant' == wp_get_theme()->get( 'TextDomain' ) ? 'wordpress-theme' : 'wordpress'); ?>">
                <input type="hidden" name="type" value="login">
                <input type="hidden" name="website" value="">
                <input type="submit" data-action="glf_form_sign_up" value="<?php _e( 'Create account', 'menu-ordering-reservations' ); ?>" class="button">
            </div>
        </div>

        <div class="glf-form-field" style="margin-top: 16px;">
            <div class="glf-box">
                <h4><?php _e( 'By signing up you agree to our', 'menu-ordering-reservations' ); ?> <a href="https://www.globalfoodsoft.com/api/legal?type=eula_restaurants&add_header=1&language=<?php echo explode( '_', get_locale() )[ 0 ]; ?>" target="_blank"><?php _e( 'terms & privacy policy', 'menu-ordering-reservations' ); ?></a></h4>
            </div>
            <div class="glf-line-spacer"></div>
            <div class="glf-box">
                <a href="#login_screen" class="glf-button-action" data-id="screen_login" data-action="screen_change" target="_parent"><?php _e( 'Login into your existing account', 'menu-ordering-reservations' ); ?></a>
            </div>
        </div>
    </form>
</div>
<?php
function glf_notification_section_html(){
?>

<div class="glf-notifications disabled glf-section-content glf-w-544flx-col flx-align-center glf-spacing-v1">
    <div class="glf-form">
        <div class="glf-form-notification" data-type="error"><?php _e( 'There was an error on your previous input!', 'menu-ordering-reservations' ); ?></div>
    </div>
</div>
<?php
}
?>