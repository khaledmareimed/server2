<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Porto Container Element
 *
 * Content Collapse Func
 *
 * @since 3.1.0
 */

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
class Porto_Elementor_Container extends Elementor\Includes\Elements\Container {

	/**
	 * Before rendering the container content. (Print the opening tag, etc.)
	 *
	 * @return void
	 */
	public function before_render() {
		$settings = $this->get_settings_for_display();
		$link = $settings['link'];

		if ( ! empty( $link['url'] ) ) {
			$this->add_link_attributes( '_wrapper', $link );
		}

		if ( ! empty( $settings['is_main_header'] ) ) {
			$this->add_render_attribute( '_wrapper', 'class', 'header-main' );
		}

		?><<?php $this->print_html_tag(); ?> <?php $this->print_render_attribute_string( '_wrapper' ); ?>>
		<?php
		if ( $this->is_boxed_container( $settings ) ) { ?>
			<div class="e-con-inner">
		<?php }

		// particles effect options
		if ( isset( $settings['particles_img'] ) && ! empty( $settings['particles_img']['url'] ) ) {
			$particles_opts = array(
				'src' => esc_url( $settings['particles_img']['url'] ),
				'he'  => esc_attr( $settings['particles_hover_effect'] ),
				'ce'  => esc_attr( $settings['particles_click_effect'] ),
			);

			if ( ! empty( $settings['particles_img']['id'] ) ) {
				$img_data = wp_get_attachment_image_src( $settings['particles_img']['id'], 'full' );
				if ( ! empty( $img_data[1] ) && ! empty( $img_data[2] ) ) {
					$particles_opts['w'] = (int) $img_data[1];
					$particles_opts['h'] = (int) $img_data[2];
				}
			}
			echo '<div id="particles-' . porto_generate_rand( 4 ) . '" class="particles-wrapper fill" data-plugin-options="' . esc_attr( json_encode( $particles_opts ) ) . '"></div>';

			wp_enqueue_script( 'particles', PORTO_SHORTCODES_URL . 'assets/js/particles.min.js', array(), PORTO_FUNC_VERSION, true );
			wp_enqueue_script( 'porto-particles-loader', PORTO_SHORTCODES_URL . 'assets/js/porto-particles-loader.min.js', array( 'particles' ), PORTO_FUNC_VERSION, true );
		}

		$this->render_video_background();

		if ( ! empty( $settings['shape_divider_top'] ) ) {
			$this->render_shape_divider( 'top' );
		}

		if ( ! empty( $settings['shape_divider_bottom'] ) ) {
			$this->render_shape_divider( 'bottom' );
		}
	}
    
	/**
	 * Register the advanced controls.
	 * 
	 * @since 3.1.0
	 */
	protected function register_advanced_controls() {
		parent::register_advanced_controls();
				
		$local_inline_start = is_rtl() ? '{{RIGHT}}{{UNIT}}' : '{{LEFT}}{{UNIT}}';
		$local_inline_end = is_rtl() ? '{{LEFT}}{{UNIT}}' : '{{RIGHT}}{{UNIT}}';
		$this->update_responsive_control( 
			'padding', 
			array(
				'selectors' => [
					'{{WRAPPER}}' => "--padding-block-start: {{TOP}}{{UNIT}}; --padding-block-end: {{BOTTOM}}{{UNIT}}; --padding-inline-start: $local_inline_start; --padding-inline-end: $local_inline_end;",
					'.container-fluid .e-parent.e-con-full .e-con-boxed.elementor-element-{{ID}}' => "--padding-inline-start: $local_inline_start; --padding-inline-end: $local_inline_end;",
				],	
			)
		);
		
	}

    /**
	 * Render the element JS template.
	 *
	 * @return void
	 */
	public function content_template() {
        ?>
        <#
		if ( settings.parallax_speed.size ) {
			let extra_attr = '';
			extra_attr += ' data-parallax-speed=' + parseFloat(settings.parallax_speed.size);

			if (settings.parallax_horizontal) {
				extra_attr += ' data-parallax-type=' + 'horizontal';
			}
			if ( settings.parallax_scale ) {
				if ( settings.parallax_scale_invert ) {
					extra_attr += ' data-parallax-scale=' + 'invert';
				} else {
					extra_attr += ' data-parallax-scale';
				}
			} #>
            <div class="porto-parallax" {{{extra_attr}}}></div>
		<# }

		if ( 'yes' == settings.content_collapse ) {
            #>
            <div class="content-collapse-empty"></div>
            <#
        }
		if ( 'boxed' === settings.content_width ) { #>
            <div class="e-con-inner">
        <#
        }
        
        // particles effect options
		if ( settings.particles_img && settings.particles_img.url ) {
			var particles_opts = { src: settings.particles_img.url, he: settings.particles_hover_effect, ce:settings.particles_click_effect },
				particles_id = 'particles-' + Math.ceil( Math.random() * 10000 );

			var particlesImg = new Image();
			particlesImg.onload = function() {
				particles_opts.w = particlesImg.width;
				particles_opts.h = particlesImg.height;

				const iframeWindow = elementorFrontend.elements.$window.get(0);
				iframeWindow.jQuery( '#' + particles_id ).attr( 'data-plugin-options', JSON.stringify( particles_opts ) );
				return;
			};
			particlesImg.src = settings.particles_img.url;
            #>
                <div id="{{ particles_id }}" class="particles-wrapper fill" data-plugin-options="{{ JSON.stringify( particles_opts ) }}"></div>
            <#
        }

        if ( settings.background_video_link ) {
            let videoAttributes = 'autoplay muted playsinline';

            if ( ! settings.background_play_once ) {
                videoAttributes += ' loop';
            }

            view.addRenderAttribute( 'background-video-container', 'class', 'elementor-background-video-container' );

            if ( ! settings.background_play_on_mobile ) {
                view.addRenderAttribute( 'background-video-container', 'class', 'elementor-hidden-phone' );
            }
            #>
            <div {{{ view.getRenderAttributeString( 'background-video-container' ) }}}>
                <div class="elementor-background-video-embed"></div>
                <video class="elementor-background-video-hosted elementor-html5-video" {{ videoAttributes }}></video>
            </div>
        <# } #>
        <div class="elementor-shape elementor-shape-top"></div>
        <div class="elementor-shape elementor-shape-bottom"></div>
        <# if ( 'boxed' === settings.content_width ) { #>
            </div>
        <# } #>
        <?php
    }

	/**
	 * Render shape divider
	 * 
	 * @since 3.1.0
	 */
	protected function render_shape_divider( $side ) {
		$settings         = $this->get_active_settings();
		$base_setting_key = "shape_divider_$side";
		$negative         = ! empty( $settings[ $base_setting_key . '_negative' ] );

		if ( 'custom' != $settings[ $base_setting_key ] ) {
			$shape_path = Elementor\Shapes::get_shape_path( $settings[ $base_setting_key ], $negative );
			if ( ! is_file( $shape_path ) || ! is_readable( $shape_path ) ) {
				return;
			}
		}
		?>
		<div class="elementor-shape elementor-shape-<?php echo esc_attr( $side ); ?>" data-negative="<?php
		// PHPCS - the variable $negative is getting a setting value with a strict structure.
		echo var_export( $negative ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>">
			<?php
			if ( 'custom' != $settings[ $base_setting_key ] ) {
				// PHPCS - The file content is being read from a strict file path structure.
				echo file_get_contents( $shape_path ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				if ( isset( $settings[ "shape_divider_{$side}_custom" ] ) && isset( $settings[ "shape_divider_{$side}_custom" ]['value'] ) ) {
					\ELEMENTOR\Icons_Manager::render_icon( $settings[ "shape_divider_{$side}_custom" ] );
				}
			}
			?>
		</div>
		<?php
	}
}
add_action( 'elementor/element/container/section_effects/after_section_end', 'porto_elementor_animation_controls', 10, 2 );
add_action( 'elementor/element/container/section_shape_divider/after_section_end', 'porto_el_container_shape_divider', 10, 2 );
add_action( 'elementor/element/container/section_background/before_section_end', 'porto_elementor_element_add_parallax', 10, 2 );
add_action( 'elementor/element/container/section_layout/after_section_end', 'porto_elementor_mpx_controls');
add_action( 'elementor/frontend/container/before_render', 'porto_elementor_container_add_custom_attrs', 10, 1 );
add_action( 'elementor/element/container/section_effects/after_section_end', 'porto_elementor_animation_controls', 10, 2 );
add_action( 'elementor/element/container/section_layout_container/after_section_end', function( $self ) {
	if ( is_singular( PortoBuilders::BUILDER_SLUG ) ) {
		$builder_type = get_post_meta( get_the_ID(), PortoBuilders::BUILDER_TAXONOMY_SLUG, true );
		if ( 'header' == $builder_type ) {
			$self->add_control (
				'is_main_header',
				array(
					'type'        => Elementor\Controls_Manager::SWITCHER,
					'label'       => esc_html__( 'Make the container sticky?', 'porto-functionality' ),
					'description' => sprintf( esc_html__( 'This section will be displayed in %1$ssticky header%2$s.', 'porto-functionality' ), '<a href="' . porto_get_theme_option_url( 'sticky-header-effect' ) . '" target="_blank">', '</a>' ),
					'prefix_class' => '',
					'return_value' => 'header-main',
				),
				array(
					'position' => array(
						'at' => 'before',
						'of' => 'content_width',
					),
				)
			);
		}
	}
} );

/**
 * Add Porto additional effects
 *
 * @since 3.1.0
 *
 * @param Object $self Object of elementor container
 * @param Array  $args
 */
function porto_elementor_container_add_custom_attrs( $self ) {
	$settings = $self->get_settings_for_display();
    if ( 'yes' == $settings['content_collapse'] ) {
        $self->add_render_attribute( '_wrapper', 'class', 'content-collapse-wrap' );
    }

	// Scroll Background Parallax
	if ( ! empty( $settings['parallax_speed']['size'] ) ) {
		$self->add_render_attribute( '_wrapper', 'data-plugin-parallax', '' );
		$self->add_render_attribute( '_wrapper', 'data-plugin-options', '{"speed": ' . floatval( $settings['parallax_speed']['size'] ) . '}' );

		if ( ! empty( $settings['parallax_horizontal'] ) ) {
			$self->add_render_attribute( '_wrapper', 'data-parallax-type', 'horizontal' );
		}
		if ( ! empty( $settings['parallax_scale'] ) ) {
			if ( ! empty( $settings['parallax_scale_invert'] ) ) {
				$self->add_render_attribute( '_wrapper', 'data-parallax-scale', 'invert' );
			} else {
				$self->add_render_attribute( '_wrapper', 'data-parallax-scale', '' );
			}
		}
		wp_enqueue_script( 'skrollr' );
	}

	// scroll progress options
	if ( isset( $settings['scroll_parallax'] ) && 'yes' == $settings['scroll_parallax'] ) {
		$self->add_render_attribute( '_wrapper', 'data-plugin', 'scroll-parallax' );

		$sp_options = array( 'cssValueStart' => empty( $settings['scroll_parallax_width']['size'] ) ? 40 : absint( $settings['scroll_parallax_width']['size'] ) );
		$sp_options['cssValueUnit'] = '%';
		$self->add_render_attribute( '_wrapper', 'data-sp-options', json_encode( $sp_options ) );

		wp_enqueue_script( 'porto-scroll-parallax', PORTO_SHORTCODES_URL . 'assets/js/porto-scroll-parallax.min.js', array( 'jquery-core' ), PORTO_FUNC_VERSION, true );
	}

    // scroll effect in viewport
	if ( isset( $settings['scroll_inviewport'] ) && 'yes' == $settings['scroll_inviewport'] ) {
		$extra_options = array();
		if ( isset( $settings['scroll_bg_scale'] ) && 'yes' == $settings['scroll_bg_scale'] ) {
			$extra_options['scroll_bg_scale']   = true;
			if ( !empty( $settings['scale_bg'] ) ) {
				$extra_options['scale_bg'] = $settings['scale_bg'];
			}
			$extra_options['scale_extra_class'] = $settings['scale_extra_class'];

			if ( isset( $settings['set_round'] ) && 'yes' == $settings['set_round'] ) {
				$extra_options['scale_extra_class'] .= ' rounded-circle';
			}			
			wp_enqueue_script( 'porto-gsap' );
			wp_enqueue_script( 'porto-scroll-trigger' );
		} else {
			if ( ! empty( $settings['scroll_bg'] ) ) {
				$extra_options['styleIn'] = array(
					'background-color' => $settings['scroll_bg'],
				);
			}
			if ( ! empty( $settings['scroll_bg_inout'] ) ) {
				$extra_options['styleOut'] = array(
					'background-color' => $settings['scroll_bg_inout'],
				);
			}
			if ( ! empty( $settings['scroll_top_mode'] ) ) {
				$extra_options['modTop'] = '-' . $settings['scroll_top_mode'] . 'px';
			}
			if ( ! empty( $settings['scroll_bottom_mode'] ) ) {
				$extra_options['modBottom'] = '-' . $settings['scroll_bottom_mode'] . 'px';
			}
		}
		$self->add_render_attribute( '_wrapper', 'data-inviewport-style', '' );
		$self->add_render_attribute( '_wrapper', 'data-plugin-options', esc_attr( json_encode( $extra_options ) ) );
	}
}

/**
 * Add Shape divider option to elementor container.
 *
 * @since 3.1.0
 *
 * @param Object $self Object of elementor container
 * @param Array  $args
 */
function porto_el_container_shape_divider( $self, $args ) {

	$shapes_options = array(
		'' => esc_html__( 'None', 'elementor' ),
	);
	foreach ( Elementor\Shapes::get_shapes() as $shape_name => $shape_props ) {
		$shapes_options[ $shape_name ] = $shape_props['title'];
	}
	$shapes_options['custom'] = esc_html__( 'Custom', 'porto-functionality' );

	$self->update_control(
		'shape_divider_top',
		array(
			'label'              => esc_html__( 'Type', 'elementor' ),
			'type'               => Controls_Manager::SELECT,
			'options'            => $shapes_options,
			'render_type'        => 'none',
			'frontend_available' => true,
		),
		array(
			'overwrite' => true,
		)
	);
	$self->update_control(
		'shape_divider_bottom',
		array(
			'label'              => esc_html__( 'Type', 'elementor' ),
			'type'               => Controls_Manager::SELECT,
			'options'            => $shapes_options,
			'render_type'        => 'none',
			'frontend_available' => true,
		),
		array(
			'overwrite' => true,
		)
	);

	$self->update_control(
		'shape_divider_top_color',
		array(
			'label'     => esc_html__( 'Color', 'elementor' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => [
				'shape_divider_top!' => '',
			],
			'selectors' => [
				'{{WRAPPER}} > .elementor-shape-top .elementor-shape-fill' => 'fill: {{UNIT}};',
				'{{WRAPPER}} > .elementor-shape-top svg' => 'fill: {{UNIT}};',
				'{{WRAPPER}} > .e-con-inner > .elementor-shape-top .elementor-shape-fill' => 'fill: {{UNIT}};',
				'{{WRAPPER}} > .e-con-inner > .elementor-shape-top svg' => 'fill: {{UNIT}};',
			],
		),
		array(
			'overwrite' => true,
		)
	);
	$self->update_control(
		'shape_divider_bottom_color',
		array(
			'label'     => esc_html__( 'Color', 'elementor' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => [
				'shape_divider_bottom!' => '',
			],
			'selectors' => [
				'{{WRAPPER}} > .elementor-shape-bottom .elementor-shape-fill' => 'fill: {{UNIT}};',
				'{{WRAPPER}} > .elementor-shape-bottom svg' => 'fill: {{UNIT}};',
				'{{WRAPPER}} > .e-con-inner > .elementor-shape-bottom .elementor-shape-fill' => 'fill: {{UNIT}};',
				'{{WRAPPER}} > .e-con-inner > .elementor-shape-bottom svg' => 'fill: {{UNIT}};',
			],
		),
		array(
			'overwrite' => true,
		)
	);

	$self->add_control(
		'shape_divider_top_custom',
		array(
			'label'                  => esc_html__( 'Custom SVG', 'porto-functionality' ),
			'type'                   => Controls_Manager::ICONS,
			'label_block'            => false,
			'skin'                   => 'inline',
			'exclude_inline_options' => array( 'icon' ),
			'render_type'            => 'none',
			'frontend_available'     => true,
			'condition'              => array(
				'shape_divider_top' => 'custom',
			),
		),
		array(
			'position' => array(
				'of' => 'shape_divider_top',
			),
		)
	);
	$self->add_control(
		'shape_divider_bottom_custom',
		array(
			'label'                  => esc_html__( 'Custom SVG', 'porto-functionality' ),
			'type'                   => Controls_Manager::ICONS,
			'label_block'            => false,
			'skin'                   => 'inline',
			'exclude_inline_options' => array( 'icon' ),
			'render_type'            => 'none',
			'frontend_available'     => true,
			'condition'              => array(
				'shape_divider_bottom' => 'custom',
			),
		),
		array(
			'position' => array(
				'of' => 'shape_divider_bottom',
			),
		)
	);

	$self->update_control(
		'gap_columns_custom',
		array(
			'selectors' => [
				'{{WRAPPER}} .elementor-column-gap-custom > .elementor-column > .elementor-element-populated, {{WRAPPER}} .elementor-column-gap-custom >.elementor-row > .elementor-column > .elementor-element-populated>.elementor-widget-wrap, {{WRAPPER}} .elementor-column-gap-custom .elementor-column > .pin-wrapper > .elementor-element-populated'      => 'padding: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} > .elementor-column-gap-custom'                         => '--porto-column-spacing: {{SIZE}}{{UNIT}}; width: calc(100% + var(--porto-column-spacing) * 2); margin-left: -{{SIZE}}{{UNIT}}; margin-right: -{{SIZE}}{{UNIT}}; --porto-flick-carousel-width: calc(var(--porto-container-width) - var(--porto-grid-gutter-width) + var(--porto-column-spacing) * 2 );',
				'{{WRAPPER}}.elementor-section-boxed > .elementor-column-gap-custom' => 'max-width: calc(var(--porto-container-width) - var(--porto-grid-gutter-width) + var(--porto-column-spacing) * 2 );',
			],
		),
		array(
			'overwrite' => true,
		)
	);
}