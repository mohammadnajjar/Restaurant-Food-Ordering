<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Scheme_Typography;

if ( !class_exists( 'Glf_Widget_Button_Base' ) ) {

    /**
     * GloriaFood Elementor Widget Button Base
     *
     * @since 1.5.0
     */
    class Glf_Widget_Button_Base extends \Elementor\Widget_Base {

        /**
         * Set widget name
         *
         * Used by elementor to identify the widget
         */
        public function get_name() {
            return 'glf_elementor_reservations';
        }
        /**
         * Get base widget keywords
         *
         * Widget keywords used for searching
         */
        public function get_base_keywords() {
            return [ 'restaurant', 'gloria', 'food' ];
        }

        /**
         * Set widget category
         *
         * We are creating our own custom category
         *
         * Default values: 'base' , 'general'
         */
        public function get_categories() {
            return [ 'gloria-food' ];
        }

        /**
         * Get button sizes.
         *
         * Retrieve an array of button sizes for the button widget.
         *
         * @return array An array containing button sizes.
         * @since 1.0.0
         * @access public
         * @static
         *
         */
        public function get_button_sizes() {
            return [
                'xs' => esc_html__( 'Extra Small', 'elementor' ),
                'sm' => esc_html__( 'Small', 'elementor' ),
                'md' => esc_html__( 'Medium', 'elementor' ),
                'lg' => esc_html__( 'Large', 'elementor' ),
                'xl' => esc_html__( 'Extra Large', 'elementor' ),
            ];
        }

        /**
         * Set widget default label
         *
         * Widget button default label
         */
        public function get_button_default_label() {
            return esc_html__( 'Click here', 'elementor' );
        }

        /**
         * Register Ordering widget controls.
         *
         * Adds a select field for location and an input field for the custom css class.
         *
         * @since 1.0.0
         * @access public
         */
        public function _register_controls() {

            $this->start_controls_section(
                'content_section',
                [
                    'label' => __( 'Settings', 'menu-ordering-reservations' ),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                ]
            );

            $select = Glf_Utils::glf_get_restaurants_dropdown_elementor();
            $this->add_control(
                'glf_ruid',
                [
                    'label' => __( 'Select restaurant', 'menu-ordering-reservations' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => $select[ 'default' ],
                    'options' => $select[ 'options' ],
                ]
            );

            $this->add_control(
                'text',
                [
                    'label' => esc_html__( 'Text', 'elementor' ),
                    'type' => Controls_Manager::TEXT,
                    'dynamic' => [
                        'active' => false,
                    ],
                    'default' => $this->get_button_default_label(),
                    'placeholder' => $this->get_button_default_label(),
                ]
            );

            $this->add_responsive_control(
                'align',
                [
                    'label' => esc_html__( 'Alignment', 'elementor' ),
                    'type' => Controls_Manager::CHOOSE,
                    'options' => [
                        'left' => [
                            'title' => esc_html__( 'Left', 'elementor' ),
                            'icon' => 'eicon-text-align-left',
                        ],
                        'center' => [
                            'title' => esc_html__( 'Center', 'elementor' ),
                            'icon' => 'eicon-text-align-center',
                        ],
                        'right' => [
                            'title' => esc_html__( 'Right', 'elementor' ),
                            'icon' => 'eicon-text-align-right',
                        ],
                        'justify' => [
                            'title' => esc_html__( 'Justified', 'elementor' ),
                            'icon' => 'eicon-text-align-justify',
                        ],
                    ],
                    'prefix_class' => 'elementor%s-align-',
                    'default' => '',
                ]
            );

            $this->add_control(
                'size',
                [
                    'label' => esc_html__( 'Size', 'elementor' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'lg',
                    'options' => self::get_button_sizes(),
                    'style_transfer' => true,
                ]
            );

            $this->add_control(
                'selected_icon',
                [
                    'label' => esc_html__( 'Icon', 'elementor' ),
                    'type' => Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'skin' => 'inline',
                    'label_block' => false,
                ]
            );

            $this->add_control(
                'icon_align',
                [
                    'label' => esc_html__( 'Icon Position', 'elementor' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'left',
                    'options' => [
                        'left' => esc_html__( 'Before', 'elementor' ),
                        'right' => esc_html__( 'After', 'elementor' ),
                    ],
                    'condition' => [
                        'selected_icon[value]!' => '',
                    ],
                ]
            );

            $this->add_control(
                'icon_indent',
                [
                    'label' => esc_html__( 'Icon Spacing', 'elementor' ),
                    'type' => Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'max' => 50,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-button .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
                        '{{WRAPPER}} .elementor-button .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_control(
                'view',
                [
                    'label' => esc_html__( 'View', 'elementor' ),
                    'type' => Controls_Manager::HIDDEN,
                    'default' => 'traditional',
                ]
            );

            $this->add_control(
                'button_css_id',
                [
                    'label' => esc_html__( 'Button ID', 'elementor' ),
                    'type' => Controls_Manager::TEXT,
                    'dynamic' => [
                        'active' => true,
                    ],
                    'default' => '',
                    'title' => esc_html__( 'Add your custom id WITHOUT the Pound key. e.g: my-id', 'elementor' ),
                    'description' => sprintf(
                    /* translators: %1$s Code open tag, %2$s: Code close tag. */
                        esc_html__( 'Please make sure the ID is unique and not used elsewhere on the page this form is displayed. This field allows %1$sA-z 0-9%2$s & underscore chars without spaces.', 'elementor' ),
                        '<code>',
                        '</code>'
                    ),
                    'separator' => 'before',

                ]
            );

            $this->end_controls_section();

            $this->start_controls_section(
                'section_style',
                [
                    'label' => esc_html__( 'Button', 'elementor' ),
                    'tab' => Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'typography',
                    'global' => [
                        'default' => Global_Typography::TYPOGRAPHY_ACCENT,
                    ],
                    'selector' => '{{WRAPPER}} .elementor-button',
                ]
            );

            $this->add_group_control(
                Group_Control_Text_Shadow::get_type(),
                [
                    'name' => 'text_shadow',
                    'selector' => '{{WRAPPER}} .elementor-button',
                ]
            );

            $this->start_controls_tabs( 'tabs_button_style' );

            $this->start_controls_tab(
                'tab_button_normal',
                [
                    'label' => esc_html__( 'Normal', 'elementor' ),
                ]
            );

            $this->add_control(
                'button_text_color',
                [
                    'label' => esc_html__( 'Text Color', 'elementor' ),
                    'type' => Controls_Manager::COLOR,
                    'default' => '',
                    'selectors' => [
                        '{{WRAPPER}} .elementor-button' => 'fill: {{VALUE}}; color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Background::get_type(),
                [
                    'name' => 'background',
                    'label' => esc_html__( 'Background', 'elementor' ),
                    'types' => [ 'classic', 'gradient' ],
                    'exclude' => [ 'image' ],
                    'selector' => '{{WRAPPER}} .elementor-button',
                    'fields_options' => [
                        'background' => [
                            'default' => 'classic',
                        ],
                        'color' => [
                            'global' => [
                                'default' => Global_Colors::COLOR_ACCENT,
                            ],
                        ],
                    ],
                ]
            );

            $this->end_controls_tab();

            $this->start_controls_tab(
                'tab_button_hover',
                [
                    'label' => esc_html__( 'Hover', 'elementor' ),
                ]
            );

            $this->add_control(
                'hover_color',
                [
                    'label' => esc_html__( 'Text Color', 'elementor' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .elementor-button:hover, {{WRAPPER}} .elementor-button:focus' => 'color: {{VALUE}};',
                        '{{WRAPPER}} .elementor-button:hover svg, {{WRAPPER}} .elementor-button:focus svg' => 'fill: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Background::get_type(),
                [
                    'name' => 'button_background_hover',
                    'label' => esc_html__( 'Background', 'elementor' ),
                    'types' => [ 'classic', 'gradient' ],
                    'exclude' => [ 'image' ],
                    'selector' => '{{WRAPPER}} .elementor-button:hover, {{WRAPPER}} .elementor-button:focus',
                    'fields_options' => [
                        'background' => [
                            'default' => 'classic',
                        ],
                    ],
                ]
            );

            $this->add_control(
                'button_hover_border_color',
                [
                    'label' => esc_html__( 'Border Color', 'elementor' ),
                    'type' => Controls_Manager::COLOR,
                    'condition' => [
                        'border_border!' => '',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-button:hover, {{WRAPPER}} .elementor-button:focus' => 'border-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'hover_animation',
                [
                    'label' => esc_html__( 'Hover Animation', 'elementor' ),
                    'type' => Controls_Manager::HOVER_ANIMATION,
                ]
            );

            $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'border',
                    'selector' => '{{WRAPPER}} .elementor-button',
                    'separator' => 'before',
                ]
            );

            $this->add_control(
                'border_radius',
                [
                    'label' => esc_html__( 'Border Radius', 'elementor' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'button_box_shadow',
                    'selector' => '{{WRAPPER}} .elementor-button',
                ]
            );

            $this->add_responsive_control(
                'text_padding',
                [
                    'label' => esc_html__( 'Padding', 'elementor' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator' => 'before',
                ]
            );

            $this->end_controls_section();

        }

        /**
         * Render button widget output on the frontend.
         *
         * Written in PHP and used to generate the final HTML.
         *
         * @since 1.0.0
         * @access protected
         */
        public function render() {
            $settings = $this->get_settings_for_display();

            $this->add_render_attribute( 'wrapper', 'class', 'elementor-button-wrapper' );

            $this->add_render_attribute( 'button', 'class', 'elementor-button' );
            $this->add_render_attribute( 'button', 'role', 'button' );

            if ( !empty( $settings[ 'button_css_id' ] ) ) {
                $this->add_render_attribute( 'button', 'id', $settings[ 'button_css_id' ] );
            }

            if ( !empty( $settings[ 'size' ] ) ) {
                $this->add_render_attribute( 'button', 'class', 'elementor-size-' . $settings[ 'size' ] );
            }

            if ( $settings[ 'hover_animation' ] ) {
                $this->add_render_attribute( 'button', 'class', 'elementor-animation-' . $settings[ 'hover_animation' ] );
            }

            if ( $settings[ 'glf_ruid' ] ) {
                $this->add_render_attribute( 'span', 'data-glf-ruid', $settings[ 'glf_ruid' ] );
            }

            if( $this->get_name() === 'glf_elementor_widget_reservations' ){
                $this->add_render_attribute( 'custom', 'data-glf-reservation', "true" );
                Glf_Utils::glf_add_to_wordpress_options( 'button_reservations', 'true' );
                $data = array(
                    'type' => 'added',
                    'added' => 'reservations',
                    'editor' => 'elementor',
                    'version' => '2.0',
                    'ruid' => $settings[ 'glf_ruid' ]
                );
            } else {
                Glf_Utils::glf_add_to_wordpress_options( 'button_ordering', 'true' );
                $data = array(
                    'type' => 'added',
                    'added' => 'ordering',
                    'editor' => 'elementor',
                    'version' => '2.0',
                    'ruid' => $settings[ 'glf_ruid' ]
                );
            }
            $currentOption = Glf_Utils::glf_get_from_wordpress_options( $data[ 'editor' ] . '_' . $data[ 'added' ], 'false' );
            if ( $currentOption === 'false' ) {
                Glf_Utils::glf_tracking_send( $data );
            }

            ?>
            <div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<a <?php $this->print_render_attribute_string( 'button' ); ?> data-glf-cuid="" <?php $this->print_render_attribute_string( 'span' ); ?> <?php $this->print_render_attribute_string( 'custom' ); ?>>
                     <?php $this->render_text(); ?>
                <?php /*$this->render_text(); */ ?>
                <script src="https://www.fbgcdn.com/embedder/js/ewm2.js" defer async></script>
			</a>
		</div>
            <?php
        }

        /**
         * Render button widget output in the editor.
         *
         * Written as a Backbone JavaScript template and used to generate the live preview.
         *
         * @since 2.9.0
         * @access protected
         */
        public function content_template() {
            ?>
            <#
            view.addRenderAttribute( 'text', 'class', 'elementor-button-text' );
            view.addInlineEditingAttributes( 'text', 'none' );
            var iconHTML = elementor.helpers.renderIcon( view, settings.selected_icon, { 'aria-hidden': true }, 'i' , 'object' ),
            migrated = elementor.helpers.isIconMigrated( settings, 'selected_icon' );
            #>
            <div class="elementor-button-wrapper">
			<a id="{{ settings.button_css_id }}" class="elementor-button elementor-size-{{ settings.size }} elementor-animation-{{ settings.hover_animation }}" href="#" role="button">
				<span class="elementor-button-content-wrapper">
					<# if ( settings.icon || settings.selected_icon ) { #>
					<span class="elementor-button-icon elementor-align-icon-{{ settings.icon_align }}">
						<# if ( ( migrated || ! settings.icon ) && iconHTML.rendered ) { #>
							{{{ iconHTML.value }}}
						<# } else { #>
							<i class="{{ settings.icon }}" aria-hidden="true"></i>
						<# } #>
					</span>
					<# } #>
					<span {{{ view.getRenderAttributeString( 'text' ) }}}>{{{ settings.text }}}</span>
                </span>
			</a>
		</div>
            <?php
        }

        /**
         * Render button text.
         *
         * Render button widget text.
         *
         * @since 1.5.0
         * @access protected
         */
        public function render_text() {
            $settings = $this->get_settings_for_display();

            $migrated = isset( $settings[ '__fa4_migrated' ][ 'selected_icon' ] );
            $is_new = empty( $settings[ 'icon' ] ) && Icons_Manager::is_migration_allowed();

            if ( !$is_new && empty( $settings[ 'icon_align' ] ) ) {
                // @todo: remove when deprecated
                // added as bc in 2.6
                //old default
                $settings[ 'icon_align' ] = $this->get_settings( 'icon_align' );
            }

            $this->add_render_attribute( [
                'content-wrapper' => [
                    'class' => 'elementor-button-content-wrapper',
                ],
                'icon-align' => [
                    'class' => [
                        'elementor-button-icon',
                        'elementor-align-icon-' . $settings[ 'icon_align' ],
                    ],
                ],
                'text' => [
                    'class' => 'elementor-button-text',
                ],
            ] );

            $this->add_inline_editing_attributes( 'text', 'none' );
            ?>
            <span <?php $this->print_render_attribute_string( 'content-wrapper' ); ?>>
			<?php if ( !empty( $settings[ 'icon' ] ) || !empty( $settings[ 'selected_icon' ][ 'value' ] ) ) : ?>
                <span <?php $this->print_render_attribute_string( 'icon-align' ); ?>>
				<?php if ( $is_new || $migrated ) :
                    Icons_Manager::render_icon( $settings[ 'selected_icon' ], [ 'aria-hidden' => 'true' ] );
                else : ?>
                    <i class="<?php echo esc_attr( $settings[ 'icon' ] ); ?>" aria-hidden="true"></i>
                <?php endif; ?>
			</span>
            <?php endif; ?>
			<span <?php $this->print_render_attribute_string( 'text' ); ?>><?php $this->print_unescaped_setting( 'text' ); ?></span>
		</span>
            <?php
        }

        public function on_import( $element ) {
            return Icons_Manager::on_import_migration( $element, 'icon', 'selected_icon' );
        }
    }
}