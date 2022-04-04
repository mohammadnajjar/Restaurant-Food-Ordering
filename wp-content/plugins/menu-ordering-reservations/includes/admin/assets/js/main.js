var GLF = GLF || {};
( function ( $ ) {
    "use strict";
    GLF.core = {
        _doc: '',
        _body: '',
        ajaxRequest: '',
        activeScreen: 'screen_sign_up',
        consentInstance: '',
        consent: false,

        domLoaded: function () {
            console.log( 'domLoaded' );
            GLF.core.init();
        },
        init: function () {
            this.consentInstance = $('#glf-consent');
            this.consent = this.consentInstance.attr('data-consent') === 'yes';
            GLF.core.glf_buttons()
            GLF.core.forms();
            GLF.core.settings_tabs();
            let redirect = $('.glf-wrapper');
            if( redirect.length > 0 && redirect.attr('data-redirect') === 'glf_load_screen' ){
                $('[data-action="glf_load_screen"]').trigger('click');
            }
        },
        settings_tabs: function(){
            $('.settings-tab').off('click').on('click', function(e){
                let _this = $(this);
                if(!_this.hasClass('active') ){

                    _this.parent().find( '.active' ).removeClass( 'active' );
                    _this.addClass( 'active' );
                    $( '.glf-settings-content.active' ).removeClass( 'active' );
                    $( '.' + _this.attr( 'data-id' ) ).addClass( 'active' );
                }
            });
        },
        dropdown_change: function(){
            var ruid = $( '#js_glf_mor_ruid' ).val(),
                data = { location: ruid },
                anim_btn = $( '.glf-white-box.publish label' ),
                list = $('.glf-list.flx-col');
            GLF.core.glf_circular_animation( 'on', anim_btn );
            GLF.core.glf_circular_animation( 'on', list );
            GLF.core.ajaxCall( 'glf_get_updated_urls', data, 'on_dropdown_overview_AjaxComplete', [ anim_btn, list ] );
        },
        on_dropdown_overview_AjaxComplete: function( args, response ){
            let data = response.data;
            if( data.todo !== undefined ){
                $('.glf-list').empty().append( data.todo );
            }
            if( data.admin_url !== undefined ){
                $('.glf-section-header .glf-button').attr( 'data-flow_url', data.admin_url );
            }

            GLF.core.glf_circular_animation( 'off', args[0] );
            GLF.core.glf_circular_animation( 'off', args[1] );
            GLF.core.update();

        },
        glf_circular_instance: '',
        glf_circular_animation: function ( state, _btn ) {
            _btn = (_btn === undefined ) ? $('.screen-active') : _btn;
            if( _btn === undefined || _btn.length <= 0  ){
                return false;
            }
            if( state === 'on' ) {
                let btn_animation = $( '#glf_circular_animation > div' ).clone();
                _btn.append( btn_animation );
                _btn.addClass( 'disabled' );
            } else if( state === 'off' ) {
                _btn.find( '> .glf-loader-animation' ).remove();
                _btn.removeClass( 'disabled' );
            }
        },
        forms: function(){
            //jQuery(this).serialize()
            $('form.glf-form').off('submit').on('submit', function(e){
                e.preventDefault();
                let _this = $( this ),
                    submit = $( 'input[type="submit"]', _this ),
                    action = submit.attr( 'data-action' ),
                    checkbox = _this.find( 'input[type="checkbox"]' );
                if( checkbox.length > 0 ){
                    _this.find('#partner_program').val( checkbox.is(':checked') );
                }
                GLF.core.notificationsFormsHide();
                GLF.core.glf_circular_animation( 'on', undefined );
                GLF.core.ajaxCall( action, _this.serialize(), 'onFormAjaxComplete', submit  )
            });
        },
        onFormAjaxComplete: function(args, response){
            let data = response.data;
            if( data.errors !== undefined ){
                GLF.core.glf_circular_animation( 'off', undefined );
                GLF.core.onFormErrors( data.errors );
                return false;
            }

            if( data.action === 'disconnect' ){
                window.open( args.attr( 'data-id' ), '_parent' );
                return false;
            }
            if( data.action === 'redirect' ){
                window.open( data.screen_url, '_parent' );
                return false;
            }

            if(data.status === 'success' ){
                GLF.core.glf_circular_animation( 'off', undefined );
                if( data.empty === 'true' ){
                    $( '.screen-active' ).removeClass( 'screen-active' );
                }
                $( '.glf-wrapper' ).append( data.screen );
                GLF.core.update();
            }else{
                GLF.core.glf_circular_animation( 'off', undefined );
            }
            GLF.core.steps_overview_update();

        },
        onFormErrors: function ( errors ){
            let notif_wrap = $( '.glf-notifications' );
            if( notif_wrap.length > 0 ){
                notif_wrap.removeClass('disabled');
                let error_html = '';
                $.each( errors, function(key, val){
                    error_html += '<span>' + val + '</span>';
                } );
                notif_wrap.find( '.glf-form-notification' ).empty().append( error_html );
            }
        },
        notificationsFormsHide: function(){
            let notif_wrap = $( '.glf-notifications' );
            if( notif_wrap.length > 0 ) {
                notif_wrap.removeClass( 'disabled' ).addClass( 'disabled' );
            }
        },
        glf_buttons:function(){
            $( '.glf-button-action-demo-page' ).off('click').on('click', function(e){
                e.stopImmediatePropagation();
                e.stopPropagation();
                e.preventDefault();
                let _this = $( this );
                if( !_this.hasClass('ajax-active') && !_this.hasClass('state-disabled') ){
                    _this.removeClass( 'ajax-active' ).addClass('ajax-active');
                    GLF.core.glf_create_demo_page( _this );
                }
            } );
            $( '.glf-button-action-redirect' ).off('click').on('click', function(e){
                let _this = $( this );
                setTimeout( function () {
                    window.open( _this.attr( 'data-overview' ), '_parent' );
                }, 200 );
            } );
            $( '.glf-button-action' ).off('click').on('click', function(e){
                e.stopImmediatePropagation();
                e.stopPropagation();
                e.preventDefault();
                let _this = $( this ),
                    _type = _this.attr( 'data-action' ),
                    _id = _this.attr('id'),
                    _title = _this.attr( 'data-title' );

                if( ( _this.attr( 'data-completed' ) === 'true' || _this.hasClass( 'state-disabled' ) )){
                    if( ( _id !== 'setup_menu' && _id !== 'setup_restaurant' && _id !== 'enroll_partner_program' ) || ( _id === 'setup_menu' && _this.prev().attr( 'data-completed' ) === 'false') ){
                        return true;
                    }
                }

                //EVENT TRACKING
                if( _title !== undefined ){
                    let  selected_ruid = '';
                    if( $('#js_glf_mor_ruid').length > 0 ){
                        selected_ruid = $( '#js_glf_mor_ruid' ).val();
                    }
                    GLF.core.glf_send_tracking( { type: 'todo', todo: _title, ruid: selected_ruid } );
                }

                // Delay the click event in order to have time to run the ajax call
                // for the event tracking
                if( _type === undefined && _this.attr('href') !== '#' ){
                    setTimeout( function () {
                        window.open( _this.attr( 'href' ), '_parent' );
                    }, 200 );
                    return true;
                }
                try {
                    GLF.core[_type]( _this );
                } catch ( error ) {
                    console.log( 'Error calling method[' + _type + '] - error message:', error.message.replace( '_type', '"' + _type + '"' ) );
                }
            });
        },

        glf_create_demo_page: function ( btn ) {
            GLF.core.glf_circular_animation( 'on', btn );
            GLF.core.ajaxCall( 'glf_create_demo_page', {}, 'glf_create_demo_page_ajaxComplete', btn );
        },

        glf_create_demo_page_ajaxComplete: function ( args, response ) {
            args.removeClass('ajax-active');
            $('.glf-button-action-demo-page').each(function(){
                $(this).attr('href', response.data.url);
            });
            GLF.core.glf_circular_animation( 'off', args );
            window.open( response.data.url, '_parent' );
        },

        glf_send_tracking:function( data ){
            if( GLF.core.consentInstance.length <= 0 ){
                console.clear();
                console.info('We are outside the GloriaFood Panel. Let the back-end decide!');
            }else if( !GLF.core.consent ){
                console.clear();
                console.warn('Consent not given!');
            }
            GLF.core.ajaxCall( 'glf_tracking', data, 'glf_tracking_ajaxComplete', '' );
        },

        glf_tracking_ajaxComplete:function( args, response ){
        },
        glf_load_screen: function( _btn ){
            let data = {
                screen: _btn.attr( 'data-screen' )
            };
            GLF.core.glf_circular_animation( 'on', undefined );
            GLF.core.ajaxCall( _btn.attr('data-action'), data, 'on_load_screen_ajaxComplete', _btn );
        },
        on_load_screen_ajaxComplete: function ( args, response ) {

            if( response.data.empty === 'true' ) {
                $( '.screen-active' ).removeClass( 'screen-active' );
            }
            $( '.glf-wrapper' ).empty().append( response.data.screen );
            GLF.core.update();
        },

        glf_switch_set: function( _btn ){
            let checkbox = _btn.find('input'),
                data = {
                option_name: _btn.attr( 'data-option' ),
                option_value: checkbox.is(':checked') ? 'no' : 'yes'
            };

            checkbox.prop('checked', !checkbox.is( ':checked' ) );
            GLF.core.ajaxCall( 'glf_set_option', data, 'onSwitchComplete', '' );
            GLF.core.glf_set_consent( data.option_value );
            if( data.option_value === 'yes' ){
                GLF.core.glf_send_tracking( { type: 'consent', consent: 'true', source: 'settings' } );
            }
        },
        onSwitchComplete: function( args, repsonse ){
        },
        glf_set_consent: function( val ){
            $('#glf-consent').attr('data-consent', val );
            GLF.core.consent = val === 'yes';
        },
        glf_set_option: function( _btn ){
            let data = {
                option_name: _btn.attr('data-option'),
                option_value: _btn.attr('data-value')
            };
            if( _btn.attr('data-screen') !== undefined ){
                data['screen'] = _btn.attr('data-screen');
            }

            GLF.core.glf_circular_animation( 'on', undefined );
            GLF.core.ajaxCall( 'glf_set_option', data, 'onSetOptionAjaxComplete', _btn )

            if( data.option_name === 'share_usage_data'  ) {
                GLF.core.glf_set_consent( data.option_value );
                if( data.option_value === 'yes' ){
                    GLF.core.glf_send_tracking( { type: 'consent', consent: 'true', source: 'wizard' } );
                }
            }
        },
        onSetOptionAjaxComplete: function( args, response ){
            let data = response.data;
            if( data.status === 'success' ) {
                if( data.action === 'redirect' ) {
                    if( args.attr('data-id') === 'new_tab_link' ){
                        window.open( args.attr( 'data-flow-url' ), args.attr( 'data-target' ) );
                    }
                    window.open( data.screen_url, '_parent' );
                    return false;
                }
                if( data.action === 'remove-overlay' ){
                    $('.screen-active').removeClass('screen-active');
                    return false;
                }

                if( data.screen !== undefined ){
                    $( '.glf-wrapper' ).empty().append( data.screen );
                }
                else{
                    GLF.core.screen_change( args );
                }
            } else {
                console.log( 'onSetOptionAjaxComplete Error', data );
            }
        },
        glf_chosen_options: function( _btn ){
            let data = {},
                selected = [];
            _btn.closest('.glf-form-field').find('> .glf-filed-checkbox input').each( function( id, el){
                el = $(el);
                data[ el.attr( 'id' ) ] = el.is( ":checked" );
                if( el.is( ":checked" ) ){
                    selected.push( el.attr('data-tracking') );
                }
            } );
            GLF.core.glf_circular_animation( 'on', undefined );
            GLF.core.ajaxCall( 'glf_chosen_options', data, 'onChosenOptionsAjaxComplete', _btn )
            if( selected.length > 0 ){
                GLF.core.glf_send_tracking( { type: 'options', options: selected } );
            }

        },
        onChosenOptionsAjaxComplete: function( args, response ){
            let data = response.data;
            if( data.status === 'success' ) {
                GLF.core.screen_change( args );
            } else {
                GLF.core.glf_circular_animation( 'off', undefined );
                console.log( 'onChosenOptionsAjaxComplete Error', data );
            }
        },
        option_dismiss: function( _btn ){
            let data = { option: _btn.attr('data-id') };
            GLF.core.ajaxCall( 'glf_setup_options_remove', data, 'onOptionsDismissAjaxComplete', _btn )
        },
        onOptionsDismissAjaxComplete: function( args, response ){
            let data = response.data;
            if( data.status === 'success' ) {
                args.closest( '.glf-list-item' ).remove();
            } else {
                console.log( 'onChosenOptionsAjaxComplete Error', data );
            }
        },
        screen_change: function(_btn){
            let new_screen = _btn.attr( 'data-id' ),
                screen = $( '#' + new_screen );
            GLF.core.notificationsFormsHide();
            if( new_screen === 'new_tab_link' ){
                setTimeout( function () {
                    window.open( _btn.attr( 'data-flow-url' ), '_blank' );
                }, 200 );

                return false;
            }
            if( new_screen === 'redirect' ){
                window.open( _btn.attr( 'data-id_url' ), '_parent' );
                return false;
            }
            if( screen.length > 0 ){
                GLF.core.glf_circular_animation( 'off', undefined );
                var new_screenInst = $( '#' + new_screen );
                $( '.screen-active' ).removeClass( 'screen-active' );
                new_screenInst.addClass('screen-active');
            }
            else if( $('.glf-wrapper.overview').length > 0 ){
                $( '.screen-active' ).removeClass( 'screen-active' );
            }
            GLF.core.steps_overview_update();
        },
        steps_overview_update: function (){
            let overviewSteps = $('.glf-steps-overview'),
                activeScreen = $('.screen-active').attr('id'),
                step = 1,
                id = 0;
            if( activeScreen === 'screen_login' || activeScreen === 'screen_sign_up' || activeScreen === 'screen_forgot_password' ){
                id = 1;
            }
            else if( activeScreen === 'screen_options' ){
                id = 2;
            }
            else if( activeScreen === 'screen_data_usage' || activeScreen === 'screen_partner_program' || activeScreen === 'screen_alternative_flow' ){
                id = 3;
            }
            overviewSteps.find( '> .current' ).removeClass( 'current' );
            overviewSteps.find( '> .active' ).removeClass( 'active' );

            for( step = 1; step <= id; step++ ){
                if( step < id ){
                    overviewSteps.find( '> div:nth-of-type(' + step + ')' ).addClass( 'active' );
                } else if( step === id ){
                    overviewSteps.find( '> div:nth-of-type(' + step + ')' ).addClass( 'current' );
                }
            }

        },
        choose_options: function(_btn){
            GLF.core.overlay_show(_btn);
        },
        open_new_tab: function(_btn){
            window.open( _btn.attr('data-id'), '_blank' );
        },
        run_screen: function(_btn){
            GLF.core.overlay_close(_btn);
            GLF.core.show_iframe( _btn.attr('href') );

        },
        show_iframe: function( url ){
            let over_iframe = $( '.glf-section-iframe' );
            $('.glf-step.current').addClass('active');
            over_iframe.css('display', 'flex');
            over_iframe.find('iframe').attr('src', url);
        },
        overlay_close: function(_btn){
            let overlay = _btn.closest( '.glf-section-overlay' );
            if( overlay.length > 0){
                _btn.closest( '.glf-section-overlay' ).css( 'display', 'none' );
            }
        },
        overlay_show: function( _btn ){
            let overlay = _btn.closest( '.glf-section-overlay' );
            if( overlay.length > 0 ) {
                _btn.closest( '.glf-section-overlay' ).css( 'display', 'flex' );
            }
        },
        update: function(){
            GLF.core.glf_buttons();
            GLF.core.forms();
        },
        ajaxCall: function ( action, data, onCompleteMethod = '', onCompleteArgs = '' ) {
            GLF.core.ajaxRequest = $.ajax( {
                    url: glf_ajax_url.ajax_url + ( window.location.search.indexOf( 'debug=true' ) > 0 ? '?debug=true' : '' ),
                    type: "POST",
                    data: {
                        action: action,
                        data: data,
                    },
                    dataType: "html",
                    onCompleteMethod: onCompleteMethod,
                    dataFilter: function ( data, dataType ) {
                        return data = {
                            data: $.parseJSON( data ),
                            onCompleteMethod: onCompleteMethod,
                            onCompleteArgs: onCompleteArgs,
                            dataType: 'json'
                        };
                    }
                }
            );
            GLF.core.ajaxRequestFail( GLF.core.ajaxRequest );
            GLF.core.ajaxRequest.done( function ( response ) {
                if( response.onCompleteMethod !== '' ) {
                    GLF.core[response.onCompleteMethod]( response.onCompleteArgs, response );
                }
                else{
                    console.log( 'ajaxCall DONE -> RESPONSE', response );
                }

            } );
        },
        ajaxRequestFail: function ( request, parent, instance, args ) {
            request.fail( function ( jqXHR, exception ) {
                var messag = "";
                if( jqXHR.status === 0 ) {
                    messag = "Internet not working! Verify network connection!";
                } else if( jqXHR.status == 404 ) {
                    messag = "[404] - Requested page not found. Check that files are on the server!";
                } else if( jqXHR.status == 500 ) {
                    messag = "[500] - Internal Server Error!";
                } else if( exception === 'parsererror' ) {
                    messag = "Requested JSON parse failed!";
                } else if( exception === 'timeout' ) {
                    messag = "Time out error!";
                } else if( exception === 'abort' ) {
                    messag = "Ajax request aborted!";
                } else {
                    messag = "Uncaught Error: " + jqXHR.responseText;
                }
                console.log( 'request.fail', messag );
            } );
        },
    };
    document.addEventListener( "DOMContentLoaded", function ( event ) {
        if( document.readyState === 'interactive' ) {
            GLF.core.domLoaded();
            window.dispatchEvent( new Event( 'resize' ) );
        }
    } );
}( jQuery ) );