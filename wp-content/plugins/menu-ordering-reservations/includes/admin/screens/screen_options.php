<?php
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
$css_screen_options = ( !Glf_Utils::$_GLF->is_authenticated() ? '' : 'screen-active' );
$glf_wordpress_options = Glf_Utils::glf_wp_options_data();
if(  $_POST['action'] !== 'forgot_password' ){
?>
<div id="screen_options" class="screen <?php echo $css_screen_options; ?> glf-section glf-w-520 glf-white-back flx-col" style="align-self:center; margin-top: 24px;">
    <div class="section-title"><?php _e( 'Select what options you want to setup', 'menu-ordering-reservations' ); ?></div>
    <div class="section-subtitle"><?php _e( 'Choose the features that you need', 'menu-ordering-reservations' ); ?></div>
    <div class="glf-list flx-col">
         <div class="glf-form-field list flx-row form-footer">
            <?php
            foreach ( Glf_Helper_Screens::get_extra_options() as $extra_option_name =>  $extra_option ){

                if( !isset( $glf_wordpress_options->app_options[ $extra_option_name ] ) ){
                    $preselected = ( (isset( $extra_option[ 'preselected' ] ) && $extra_option[ 'preselected' ] === 'true' ) ? 'checked' : ''  );
                }
                else{
                    $preselected = ( $glf_wordpress_options->app_options[ $extra_option_name ] === 'true' ? 'checked' : '' );
                }
            ?>

                <div class="glf-filed-checkbox">
                    <input id="<?php echo $extra_option_name; ?>" name="<?php echo $extra_option_name; ?>" type="checkbox" <?php echo $preselected; ?> >
                    <label for="<?php echo $extra_option_name; ?>"><?php echo self::get_to_do_text_for_translation( $extra_option_name, 'label' ); ?></label>
                    <?php
                    if( isset( $extra_option[ 'paid' ] ) && $extra_option[ 'paid' ] === 'true' ){
                    ?>
                        <div class="glf-tag"><?php _e('Premium', 'menu-ordering-reservations'); ?></div>
                    <?php
                    }
                    ?>
                </div>
            <?php
            }
            ?>
            <div class="glf-filed-submit">
                <div class="glf-button glf-button-action" data-id="screen_alternative_flow" data-action="glf_chosen_options"><?php _e( 'Continue', 'menu-ordering-reservations' ); ?></div>
            </div>
        </div>
    </div>
</div>
<?php
}
?>

<?php
$css_screen_usage = ( $_POST[ 'action' ] === 'forgot_password' ? 'screen-active' : '');
?>
<div id="screen_forgot_password_done" class="screen <?php echo $css_screen_usage; ?> glf-section-content glf-w-544 glf-white-back flx-col flx-align-center glf-spacing-v1">
        <div class="section-title"><?php _e( 'Reset password', 'menu-ordering-reservations' ); ?></div>
        <div class="glf-form">
            <div class="glf-form-notification" data-type="success"><?php _e( 'You will receive an email message with instructions on how to reset your password.', 'menu-ordering-reservations' ); ?></div>
            <div class="glf-form-field" style="margin-top: 16px;">
                <div class="glf-line-spacer"></div>
                <div class="glf-box">
                    <a href="#" class="glf-button-action" data-id="screen_login" data-action="screen_change" target="_parent"><?php _e( 'Login', 'menu-ordering-reservations' ); ?></a>
                </div>
            </div>
        </div>
    </div>
<?php
$css_screen_usage = ( (empty( $glf_wordpress_options->app_options[ 'share_usage_data' ] ) && $_POST[ 'action' ] !== 'forgot_password' ) ? 'screen-active' : '');
?>
<div id="screen_data_usage" class="screen <?php echo $css_screen_usage; ?> glf-section-overlay">
    <div class="glf-section-content glf-w-544 glf-white-back flx-col flx-align-center glf-spacing-v1">
        <div class="section-title"><?php _e( 'Help us improve GloriaFood', 'menu-ordering-reservations' ); ?></div>
        <div class="glf-line-spacer"></div>
        <div class="glf-overlay-text">
            <span><?php _e( 'Become a super contributor by opting in to share non-sensitive plugin data.', 'menu-ordering-reservations' ); ?><a href="https://www.gloriafood.com/privacy" target="_blank"><?php _e( 'Learn more', 'menu-ordering-reservations' ); ?></a></span>
        </div>
        <div class="glf-column glf-col-h-right glf-col-spacing-top">
            <a href="" class="glf-link glf-button-action" data-id="screen_options" data-value="no" data-action="glf_set_option" data-option="share_usage_data"><?php _e( 'Don’t share', 'menu-ordering-reservations' ); ?></a>
            <a href="" class="glf-button glf-button-action" data-id="screen_options" data-value="yes" data-action="glf_set_option" data-option="share_usage_data"><?php _e( 'Share usage data', 'menu-ordering-reservations' ); ?></a>
        </div>
    </div>
</div>

<?php
$css_screen_usage = '';
?>
<div id="screen_alternative_flow" class="screen glf-section-overlay">
    <div class="glf-section-content glf-w-544 glf-white-back flx-col flx-align-center glf-spacing-v1">
        <div class="section-title"><?php _e( 'Admin setup', 'menu-ordering-reservations' ); ?></div>
        <div class="glf-overlay-text light" style="margin-top: 8px;">
            <span><?php _e( 'You will now need to go to GloriaFood Admin in order to setup your restaurant profile. After finishing the setup you can come back and add the widgets on your website.', 'menu-ordering-reservations' ); ?></span>
        </div>
        <div class="glf-column glf-col-h-center glf-col-spacing-top" style="margin-bottom: 16px;">
            <a href="<?= Glf_Utils::$_GLF->get_glf_mor_token() . '&r=app.admin.setup'; ?>" class="glf-button" target="_blank"><span class="icon"></span><?php _e( 'Start the setup', 'menu-ordering-reservations' ); ?></a>
        </div>
    </div>
</div>

<div id="screen_goto_overview" class="screen glf-section-overlay">
    <div class="glf-section-content glf-w-544 glf-white-back flx-col flx-align-center glf-spacing-v1">
        <div class="section-title"><?php _e( 'Your restaurant profile is already setup', 'menu-ordering-reservations' ); ?></div>
        <div class="glf-overlay-text light" style="margin-top: 8px;">
            <span><?php _e( 'You can now continue to setup the rest of the account', 'menu-ordering-reservations' ); ?></span>
        </div>
        <div class="glf-column glf-col-h-center glf-col-spacing-top" style="margin-bottom: 16px;">
            <a href="<?= Glf_Utils::$_GLF->get_glf_mor_token() . '&r=app.admin.setup'; ?>" class="glf-button" target="_blank"><span class="icon"></span><?php _e( 'Continue setting up online ordering', 'menu-ordering-reservations' ); ?></a>
        </div>
    </div>
</div>