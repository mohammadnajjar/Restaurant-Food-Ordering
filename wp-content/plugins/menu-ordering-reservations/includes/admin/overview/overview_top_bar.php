<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
?>
<div class="glf-section flx-col top-bar">
    <div class="glf-section-top-bar">
        <a href="admin.php?page=glf-overview" class="glf-bar-button"><span class="icon"></span></a>
        <div class="glf-steps-overview">
            <div class="glf-step current" data-id="connect_account">
                <div class="icon"><span>1</span></div>
                <div class="title glf-acc"><?php _e( 'GloriaFood Account', 'menu-ordering-reservations' ); ?></div>
            </div>
            <span class="line-spacer" data-id="screen_options"></span>
            <div class="glf-step">
                <div class="icon"><span>2</span></div>
                <div class="title glf-chs"><?php _e( 'Choose Options', 'menu-ordering-reservations' ); ?></div>
            </div>
            <span class="line-spacer" data-id="screen_alternative_flow"></span>
            <div class="glf-step">
                <div class="icon"><span>3</span></div>
                <div class="title glf-ssp"><?php _e( 'Start Setup', 'menu-ordering-reservations' ); ?></div>
            </div>
        </div>
    </div>
</div>