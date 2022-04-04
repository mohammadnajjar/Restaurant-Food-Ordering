( function ( wp ) {

    var registerBlockType = wp.blocks.registerBlockType;

    var el = wp.element.createElement,
        SelectControl = wp.components.SelectControl,
        Fragment = wp.element.Fragment,
        InspectorControls = wp.blockEditor.InspectorControls;

    var options = [];
    var restaurants = js_data;

    const { __ } = wp.i18n;

    const menuIcon = el( 'svg',
        {
            width: 18,
            height: 18
        },
        el( 'path',
            {
                d: "M8.991 0C4.023 0 0 4.032 0 9C0 13.968 4.023 18 8.991 18C13.968 18 18 13.968 18 9C18 4.032 13.968 0 8.991 0ZM9 16.2C5.022 16.2 1.8 12.978 1.8 9C1.8 5.022 5.022 1.8 9 1.8C12.978 1.8 16.2 5.022 16.2 9C16.2 12.978 12.978 16.2 9 16.2ZM9.45 4.5H8.1V9.9L12.825 12.735L13.5 11.628L9.45 9.225V4.5Z"
            }
        )
    );

    for ( x in restaurants ) {
        options.push( {
            label: restaurants[x]['name'], value: restaurants[x]['uid']
        } );
    }

    registerBlockType( 'menu-ordering-reservations/opening-hours', {

        title: __( 'Opening Hours', 'menu-ordering-reservations' ),

        icon: menuIcon,

        category: 'widgets',


        supports: {
            // Removes support for an HTML mode.
            html: false,
        },
        attributes: {
            ruid: {
                default: restaurants[0]['uid'],
                type: 'string'
            }
        },


        edit: function ( props ) {

            const attributes = props.attributes;
            const { serverSideRender: ServerSideRender } = wp;

            var content = props.attributes.content,
                ruid = props.attributes.ruid;

            function onChangeSelectField( newValue ) {
                props.setAttributes( { ruid: newValue } );
            }

            return (
                el(
                    Fragment,
                    null,
                    el(
                        InspectorControls,
                        null,

                        el(
                            SelectControl,
                            {
                                label: __( 'Select restaurant', 'menu-ordering-reservations' ),
                                value: ruid,
                                options: options,
                                onChange: onChangeSelectField,
                                className: "gblock"
                            }
                        )
                    ),
                    el( 'div', {}, [
                        //Preview a block with a PHP render callback
                        el( ServerSideRender, {
                            block: 'menu-ordering-reservations/opening-hours',
                            attributes: attributes
                        } ),
                    ] )
                )
            );

        },


        save: function ( props ) {
            var content = props.attributes.content,
                ruid = props.attributes.ruid;

            return null;
        }
    } );


} )(
    window.wp
);
