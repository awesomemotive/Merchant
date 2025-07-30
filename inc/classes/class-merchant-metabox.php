<?php

/**
 * Metabox.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Metabox class.
 *
 */
if ( ! class_exists( 'Merchant_Metabox' ) ) {
	class Merchant_Metabox {

		private static $initialized = null;

		/**
		 * Options.
		 *
		 */
		public static $options = array();

		/**
		 * Constructor.
		 *
		 */
		public function __construct() {
			if ( ! is_null( self::$initialized ) ) {
				return;
			}

			self::$initialized = true;

			add_action( 'load-post.php', array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
			add_action( 'wp_ajax_merchant_select_ajax', array( $this, 'select_content_ajax' ) );
		}

		/**
		 * Init metabox.
		 *
		 */
		public function init_metabox() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_metabox_scripts' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
			add_action( 'save_post', array( $this, 'save_metabox' ) );
		}

		/**
		 * Enqueue scripts.
		 *
		 */
		public function enqueue_metabox_scripts() {
			wp_enqueue_code_editor(
				array(
					'type'       => 'text/html',
					'codemirror' => array(
						'indentUnit' => 2,
						'tabSize'    => 2,
					),
				) );

			wp_enqueue_script( 'merchant-select2', MERCHANT_URI . 'assets/vendor/select2/select2.full.min.js', array( 'jquery' ), '4.0.13', true );
			wp_enqueue_style( 'merchant-select2', MERCHANT_URI . 'assets/vendor/select2/select2.min.css', array(), '4.0.13', 'all' );

			wp_enqueue_style( 'merchant-metabox-styles', MERCHANT_URI . 'assets/css/admin/metabox.min.css', array(), MERCHANT_VERSION );
			wp_enqueue_script( 'merchant-metabox-scripts', MERCHANT_URI . 'assets/js/admin/merchant-metabox.min.js', array( 'jquery', 'jquery-ui-sortable' ), MERCHANT_VERSION, true );

			wp_localize_script( 'merchant-metabox-scripts', 'merchant_metabox', array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'ajaxnonce' => wp_create_nonce( 'merchant_metabox' ),
			) );
		}

		/**
		 * Select content ajax callback.
		 *
		 */
		public function select_content_ajax() {
			$term   = ( isset( $_GET['term'] ) ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
			$nonce  = ( isset( $_GET['nonce'] ) ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
			$source = ( isset( $_GET['source'] ) ) ? sanitize_text_field( wp_unslash( $_GET['source'] ) ) : '';

			// Check current user capabilities
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You are not allowed to do this.' );
			}

			if ( ! empty( $term ) && ! empty( $source ) && ! empty( $nonce ) && wp_verify_nonce( $nonce, 'merchant_metabox' ) ) {
				$options = array();

				switch ( $source ) {
					case 'post':
					case 'product':
						$query = new WP_Query( array(
							's'              => $term,
							'post_type'      => $source,
							'post_status'    => 'publish',
							'posts_per_page' => 25,
							'order'          => 'DESC',
						) );

						if ( ! empty( $query->posts ) ) {
							foreach ( $query->posts as $post ) {
								$options[] = array(
									'id'   => $post->ID,
									'text' => $post->post_title,
								);
							}
						}

						break;
				}

				wp_send_json_success( $options );
			} else {
				wp_send_json_error();
			}
		}

		/**
		 * Options.
		 *
		 */
		public function metabox_options() {
			/**
			 * Hook: merchant_metabox_options
			 *
			 * @since 1.0
			 */
			do_action( 'merchant_metabox_options', self::$options );

			/**
			 * Hook: merchant_metabox_options_filter
			 *
			 * @since 1.0
			 */
			self::$options = apply_filters( 'merchant_metabox_options_filter', self::$options );

			// Set priority order
			self::$options = wp_list_sort( self::$options, array( 'priority' => 'ASC' ), 'ASC', true );

			foreach ( self::$options as $key => $value ) {
				self::$options[ $key ]['fields'] = wp_list_sort( $value['fields'], array( 'priority' => 'ASC' ), 'ASC', true );
			}

			return self::$options;
		}

		/**
		 * Add section.
		 *
		 */
		public function add_section( $id, $args ) {
			if ( ! empty( $args['post_type'] ) && ! in_array( get_post_type(), $args['post_type'], true ) ) {
				return;
			}

			if ( ! empty( $args['exclude'] ) && in_array( get_post_type(), $args['exclude'], true ) ) {
				return;
			}

			$args = wp_parse_args( $args, array(
				'title'    => '',
				'fields'   => array(),
				'priority' => ( count( self::$options ) + 1 ) * 10,
			) );

			self::$options[ $id ] = $args;
		}

		/**
		 * Add field.
		 *
		 */
		public function add_field( $id, $args ) {
			if ( ( ! empty( $args['post_type'] ) && ! in_array( get_post_type(), $args['post_type'], true ) ) || empty( self::$options[ $args['section'] ] ) ) {
				return;
			}

			$args = wp_parse_args( $args, array(
				'priority' => ( count( self::$options[ $args['section'] ]['fields'] ) + 1 ) * 10,
			) );

			self::$options[ $args['section'] ]['fields'][ $id ] = $args;
		}

		/**
		 * Add metabox.
		 *
		 */
		public function add_metabox( $post_type ) {
			global $post;

			// Do not render the metabox on attachment post type.
			if ( 'attachment' === $post_type ) {
				return;
			}

			$types = get_post_types( array(
				'public' => true,
			) );

			if ( ! in_array( $post_type, $types, true ) ) {
				return;
			}

			$options   = $this->metabox_options();
			if ( empty( $options ) ) {
				return;
			}

			$metabox_title = esc_html__( 'Options', 'merchant' );
			switch ( $post_type ) {
				case 'post':
					$metabox_title = esc_html__( 'Post Options', 'merchant' );
					break;

				case 'page':
					$metabox_title = esc_html__( 'Page Options', 'merchant' );
					break;

				case 'product':
					$metabox_title = esc_html__( 'Product Options', 'merchant' );
					break;
			}

			// Botiga theme compatibility:
			// Do not render the metabox in the Botiga templates builder.
			if ( class_exists( 'Botiga_Modules' ) && Botiga_Modules::is_module_active( 'templates' ) ) {
				unset( $types['athemes_hf'] );
			}

			$title_prefix = ! defined( 'MERCHANT_AWL_ACTIVE' ) ? esc_html__( 'Merchant ', 'merchant' ) : '';

			/**
			 * Hook: merchant_metabox_title
			 *
			 * @since 1.0
			 */
			$metabox_title = apply_filters( 'merchant_metabox_title', $title_prefix . $metabox_title, $post_type );

			add_meta_box( 'merchant_metabox', $metabox_title, array( $this, 'render_metabox_content' ), $types, 'normal', 'low' );
		}

		/**
		 * Metabox content.
		 *
		 */
		public function render_metabox_content( $post ) {
			$options   = $this->metabox_options();
			$post_type = get_post_type( $post );

			wp_nonce_field( 'merchant_metabox', 'merchant_metabox_nonce' );

			echo '<div class="merchant-metabox">';
			$has_tabs = ( ! empty( array_filter( array_column( $options, 'title' ) ) ) ) ? true : false;

			if ( ! empty( $has_tabs ) ) {
				echo '<div class="merchant-metabox-tabs">';

				$num = 0;
				foreach ( $options as $option ) {
					if ( ! empty( $option['title'] ) ) {
						$active = ( 0 === $num ) ? ' active' : '';
						echo '<a href="#" class="merchant-metabox-tab' . esc_attr( $active ) . '">' . esc_html( $option['title'] ) . '</a>';

						++$num;
					}
				}

				echo '</div>';
			}

			echo '<div class="merchant-metabox-contents">';

			$num = 0;
			foreach ( $options as $option ) {
				$active = ( 0 === $num ) ? ' active' : '';
				echo '<div class="merchant-metabox-content' . esc_attr( $active ) . '">';

				if ( ! empty( $option['title'] ) ) {
					echo '<h4 class="merchant-metabox-content-title">' . esc_html( $option['title'] ) . '</h4>';
				}

				if ( ! empty( $option['fields'] ) ) {
					foreach ( $option['fields'] as $field_id => $field ) {
						$separator = ( ! empty( $field['separator'] ) ) ? $field['separator'] : 'after';
						$classes   = array();
						$classes[] = 'merchant-metabox-field';
						$classes[] = 'merchant-metabox-field-separator-' . $separator;
						$classes[] = 'merchant-metabox-field-' . $field['type'];

						if ( ! empty( $field['class'] ) ) {
							$classes[] = $field['class'];
						}

						if ( ! empty( $field['inline'] ) ) {
							$classes[] = 'merchant-metabox-field-inline';
						}

						if ( ! empty( $field['depend'] ) ) {
							$depend_meta = get_post_meta( $post->ID, $field['depend'], true );

							if ( empty( $depend_meta ) ) {
								$classes[] = 'merchant-metabox-field-hidden';
							}

							echo '<div class="' . esc_attr( join( ' ', $classes ) ) . '" data-depend-on="' . esc_attr( $field['depend'] ) . '">';
						} else {
							echo '<div class="' . esc_attr( join( ' ', $classes ) ) . '">';
						}

						if ( isset( $field['title'] ) || isset( $field['subtitle'] ) ) {
							echo '<div class="merchant-metabox-field-title">';

							if ( ! empty( $field['title'] ) ) {
								echo '<h4>' . wp_kses_post( $field['title'] ) . '</h4>';
							}

							if ( ! empty( $field['subtitle'] ) ) {
								echo '<small class="merchant-metabox-field-subtitle">' . wp_kses_post( $field['subtitle'] ) . '</small>';
							}

							echo '</div>';
						}

						echo '<div class="merchant-metabox-field-content">';

						$meta    = get_post_meta( $post->ID, $field_id, false );
						$default = ( isset( $field['default'] ) ) ? $field['default'] : null;
						$value   = ( isset( $meta[0] ) ) ? $meta[0] : $default;

						$this->get_field( $field_id, $field, $value );

						if ( ! empty( $field['desc'] ) ) {
							echo '<div class="merchant-metabox-field-description">' . wp_kses_post( $field['desc'] ) . '</div>';
						}

						echo '</div>';

						echo '</div>';
					}
				}

				echo '</div>';

				++$num;
			}

			echo '</div>';
			echo '</div>';
		}

		/**
		 * Save metabox.
		 *
		 */
		public function save_metabox( $post_id ) {
			if ( ! isset( $_POST['merchant_metabox_nonce'] ) ) {
				return $post_id;
			}

			$nonce = sanitize_key( wp_unslash( $_POST['merchant_metabox_nonce'] ) );
			if ( ! wp_verify_nonce( $nonce, 'merchant_metabox' ) ) {
				return $post_id;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}

			$options = $this->metabox_options();
			if ( empty( $options ) ) {
				return $post_id;
			}

			foreach ( $options as $option ) {
				if ( ! empty( $option['fields'] ) ) {
					foreach ( $option['fields'] as $field_id => $field ) {
						if ( $field['type'] === 'content' ) {
							continue;
						}

						$value = $this->sanitize( $field, $field_id, $_POST );

						update_post_meta( $post_id, $field_id, $value );
					}
				}
			}
		}

		/**
		 * Sanitize.
		 *
		 */
		public function sanitize( $field, $field_id, $post_data ) {
			$value = isset( $post_data[ $field_id ] ) ? wp_unslash( $post_data[ $field_id ] ) : null;

			switch ( $field['type'] ) {
				case 'text':
				case 'coupons':
				case 'sidebar-select':
				case 'size-chart-select':
					return sanitize_text_field( $value );
					break;

				case 'textarea':
					return sanitize_textarea_field( $value );
					break;

				case 'checkbox':
				case 'switcher':
					return ( '1' === $value ) ? 1 : 0;
					break;

				case 'number':
					return absint( $value );
					break;

				case 'select':
				case 'choices':
					return ( in_array( $value, array_keys( $field['options'] ), true ) ) ? sanitize_key( $value ) : '';
					break;

				case 'wc-attributes':
				case 'select-ajax':
					return ( is_array( $value ) && ! empty( $value ) ) ? array_filter( array_map( 'sanitize_text_field', $value ) ) : array();
					break;

				case 'wc-attributes':
					return ( is_array( $value ) && ! empty( $value ) ) ? array_filter( array_map( 'sanitize_text_field', $value ) ) : array();
					break;

				case 'repeater':
				case 'uploads':
					return ( is_array( $value ) && ! empty( $value ) ) ? array_filter( map_deep( $value, 'sanitize_text_field' ) ) : array();
					break;

				case 'size-chart':
				case 'uploads':
					return ( is_array( $value ) && ! empty( $value ) ) ? array_filter( map_deep( $value, 'sanitize_text_field' ) ) : array();
					break;

				case 'wp-editor':
					return wp_kses_post( $value );
					break;

				case 'flexible-content':
					return is_array( $value ) && ! empty( $value ) ? array_map( function ( $sub_fields ) use ( $field ) {
						foreach ( $sub_fields as $sub_field => $value ) {
							$sub_fields[ $sub_field ] = $this->sanitize( $field['layouts'][ $sub_fields['type'] ]['fields'][ $sub_field ], $sub_field, $sub_fields );
						}

						return $sub_fields;
					}, $value ) : array();
					break;
			}

			return $value;
		}

		/**
		 * Get field.
		 *
		 */
		public function get_field( $field_id, $field, $value ) {
			switch ( $field['type'] ) {
				case 'text':
					if ( isset( $field['append'] ) || isset( $field['prepend'] ) ) {
						echo '<div class="merchant-metabox-field-input-container">';
						if ( isset( $field['prepend'] ) ) {
							echo '<div class="merchant-metabox-field-prepend">' . esc_attr( $field['prepend'] ) . '</div>';
						}
						echo '<input type="text" name="' . esc_attr( $field_id ) . '" value="' . esc_attr( $value ) . '" />';
						if ( isset( $field['append'] ) ) {
							echo '<div class="merchant-metabox-field-append">' . esc_attr( $field['append'] ) . '</div>';
						}
						echo '</div>';
					} else {
						echo '<input type="text" name="' . esc_attr( $field_id ) . '" value="' . esc_attr( $value ) . '" />';
					}
					break;

				case 'number':
					$style = '';
					$step = 'any';
					if ( isset( $field['style'] ) && ! empty( $field['style'] ) ) {
						$style = 'style="' . esc_attr( str_replace( array( '&', '=' ), array( '; ', ': ' ), http_build_query( $field['style'] ) ) ) . '"';
					}
					if ( isset( $field['step'] ) && ! empty( $field['step'] ) ) {
						$step = $field['step'];
					}
					if ( isset( $field['append'] ) || isset( $field['prepend'] ) ) {
						echo '<div class="merchant-metabox-field-input-container">';
						if ( isset( $field['prepend'] ) ) {
							echo '<div class="merchant-metabox-field-prepend">' . esc_attr( $field['prepend'] ) . '</div>';
						}
						echo '<input step="' . esc_attr( $step ) . '" type="number" name="' . esc_attr( $field_id ) . '" value="' . esc_attr( $value ) . '" ' . wp_kses_post( $style ) . ' />';
						if ( isset( $field['append'] ) ) {
							echo '<div class="merchant-metabox-field-append">' . esc_attr( $field['append'] ) . '</div>';
						}
						echo '</div>';
					} else {
						echo '<input step="' . esc_attr( $step ) . '" type="number" '.' name="' . esc_attr( $field_id ) . '" value="' . esc_attr( $value ) . '" ' . wp_kses_post( $style ) . ' />';
					}
					break;

				case 'textarea':
					echo '<textarea name="' . esc_attr( $field_id ) . '">' . esc_textarea( $value ) . '</textarea>';
					break;

				case 'checkbox':
				case 'switcher':
					$field = wp_parse_args( $field, array(
						'label' => '',
					) );

					echo '<label>';
					echo '<input type="checkbox" name="' . esc_attr( $field_id ) . '" value="1"' . checked( $value, true, false ) . ' />';

					if ( 'switcher' === $field['type'] ) {
						echo '<i></i>';
					}

					if ( ! empty( $field['label'] ) ) {
						echo '<span>' . esc_html( $field['label'] ) . '</span>';
					}
					echo '</label>';
					break;

				case 'select':
					echo '<select name="' . esc_attr( $field_id ) . '">';

					foreach ( $field['options'] as $key => $option ) {
						echo '<option value="' . esc_attr( $key ) . '"' . selected( $key, $value, false ) . '>' . esc_html( $option ) . '</option>';
					}

					echo '</select>';
					break;

				case 'select-ajax':
					$field = wp_parse_args( $field, array(
						'source' => 'post',
					) );
					$ids   = ( is_array( $value ) && ! empty( $value ) ) ? $value : (array) $value;
					if ( isset( $field['multiple'] ) ) {
						$multiple = $field['multiple'] === true ? 'multiple' : '';
					} else {
						$multiple = 'multiple';
					}

					echo '<select name="' . esc_attr( $field_id ) . '[]" ' . esc_attr( $multiple ) . ' data-source="' . esc_attr( $field['source'] ) . '">';

					if ( ! empty( $ids ) ) {
						foreach ( $ids as $id ) {
							switch ( $field['source'] ) {
								case 'post':
								case 'product':
									$post = get_post( $id );

									if ( ! empty( $post ) ) {
										echo '<option value="' . esc_attr( $post->ID ) . '" selected>' . esc_html( $post->post_title ) . '</option>';
									}
									break;
							}
						}
					}

					echo '</select>';
					break;

				case 'wc-attributes':
					$attributes = wp_list_pluck( wc_get_attribute_taxonomies(), 'attribute_label', 'attribute_id' );
					$values     = ( is_array( $value ) && ! empty( $value ) ) ? $value : array();

					if ( ! empty( $attributes ) ) {
						echo '<div class="merchant-metabox-field-attributes">';
						echo '<ul class="merchant-sortable">';

						$selected_attributes = array();
						foreach ( $values as $id ) {
							if ( isset( $attributes[ $id ] ) ) {
								$selected_attributes[ $id ] = $attributes[ $id ];
								unset( $attributes[ $id ] );
							}
						}

						$attributes = array_replace( $selected_attributes, $attributes );
						foreach ( $attributes as $attribute_id => $attribute_label ) {
							// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
							$checked = ( in_array( $attribute_id, $values ) ) ? ' checked' : '';

							echo '<li class="merchant-sortable-item">';
							echo '<label>';
							echo '<input type="checkbox" name="' . esc_attr( $field_id ) . '[]" value="' . esc_attr( $attribute_id ) . '"' . esc_attr( $checked ) . ' />';
							echo '<span>' . esc_html( $attribute_label ) . '</span>';
							echo '</label>';
							echo '<span class="merchant-sortable-move dashicons dashicons-menu"></span>';
							echo '</li>';
						}

						echo '</ul>';
						echo '</div>';
					}

					break;

				case 'choices':
					echo '<div class="merchant-metabox-field-choices-images">';

					foreach ( $field['options'] as $key => $option ) {
						echo '<label>';
						echo '<input type="radio" name="' . esc_attr( $field_id ) . '" value="' . esc_attr( $key ) . '"' . checked( $value, $key, false ) . ' />';
						echo '<figure><img src="' . esc_url( sprintf( $option['image'],
								MERCHANT_URI ) ) . '" title="' . esc_attr( $option['label'] ) . '" alt="' . esc_attr( $option['label'] ) . '" /></figure>';
						echo '</label>';
					}

					echo '</div>';
					break;

				case 'content':
					echo wp_kses_post( $field['content'] );
					break;

				case 'repeater':
					$field = wp_parse_args( $field, array(
						'button' => '',
					) );

					echo '<div class="merchant-metabox-field-repeater-content" data-id="' . esc_attr( $field_id ) . '">';
					$values = ( is_array( $value ) && ! empty( $value ) ) ? $value : array();

					echo '<ul class="merchant-metabox-field-repeater-list">';
					echo '<li class="merchant-metabox-field-repeater-list-item hidden">';
					if ( isset( $field['fields'] ) ) {
						echo '<div class="merchant-metabox-field-repeater-list-item-fields">';
						foreach ( $field['fields'] as $sub_field_id => $sub_field ) {
							echo '<div class="merchant-metabox-field-repeater-list-item-field">';
							if ( isset( $sub_field['title'] ) ) {
								echo '<span class="merchant-metabox-field-repeater-list-item-field-title">' . esc_html( $sub_field['title'] ) . '</span>';
							}
							echo '<div class="merchant-metabox-field-repeater-list-item-field-input" data-id="' . esc_attr( $sub_field_id ) . '">';
							if ( $sub_field['type'] === 'text' ) {
								echo '<input type="text" name="" value="" data-name="' . esc_attr( $field_id ) . '[0][' . esc_attr( $sub_field_id ) . ']" />';
							}
							if ( $sub_field['type'] === 'number' ) {
								echo '<input type="number" name="" value="" data-name="' . esc_attr( $field_id ) . '[0][' . esc_attr( $sub_field_id ) . ']" />';
							}
							echo '</div>';
							echo '</div>';
						}
						echo '</div>';
					} else {
						echo '<input type="text" name="" value="" data-name="' . esc_attr( $field_id ) . '[]" />';
					}
					echo '<span class="merchant-metabox-field-repeater-move dashicons dashicons-menu"></span>';
					echo '<span class="merchant-metabox-field-repeater-remove dashicons dashicons-trash"></span>';
					echo '</li>';

					foreach ( $values as $key => $value ) {
						echo '<li class="merchant-metabox-field-repeater-list-item">';
						if ( isset( $field['fields'] ) ) {
							echo '<div class="merchant-metabox-field-repeater-list-item-fields">';
							foreach ( $field['fields'] as $sub_field_id => $sub_field ) {
								echo '<div class="merchant-metabox-field-repeater-list-item-field">';
								if ( isset( $sub_field['title'] ) ) {
									echo '<span class="merchant-metabox-field-repeater-list-item-field-title">' . esc_attr( $sub_field['title'] ) . '</span>';
								}
								echo '<div class="merchant-metabox-field-repeater-list-item-field-input" data-id="' . esc_attr( $sub_field_id ) . '">';
								if ( $sub_field['type'] === 'text' ) {
									echo '<input type="text" name="' . esc_attr( $field_id ) . '[' . esc_attr( $key ) . '][' . esc_attr( $sub_field_id ) . ']" value="' . esc_attr( $value[ $sub_field_id ] ) . '" />';
								}
								if ( $sub_field['type'] === 'number' ) {
									echo '<input type="number" name="' . esc_attr( $field_id ) . '[' . esc_attr( $key ) . '][' . esc_attr( $sub_field_id ) . ']" value="' . esc_attr( $value[ $sub_field_id ] ) . '" />';
								}
								echo '</div>';
								echo '</div>';
							}
							echo '</div>';
						} else {
							echo '<input type="text" name="' . esc_attr( $field_id ) . '[]" value="' . esc_attr( $value ) . '" />';
						}

						echo '<span class="merchant-metabox-field-repeater-move dashicons dashicons-menu"></span>';
						echo '<span class="merchant-metabox-field-repeater-remove dashicons dashicons-trash"></span>';
						echo '</li>';
					}
					echo '</ul>';
					echo '<button class="merchant-metabox-field-repeater-add button button-primary">' . esc_html( $field['button'] ) . '</button>';
					echo '</div>';

					break;

				case 'media':
					$placeholder  = class_exists( 'Woocommerce' ) ? wc_placeholder_img_src( 'thumbnail' ) : MERCHANT_URI . '/assets/placeholder.svg';
					$hidden_class = ( empty( $value ) ) ? ' hidden' : '';

					if ( ! empty( $value ) ) {
						$attachment = wp_get_attachment_image_src( $value, 'thumbnail' );
						$thumbnail  = ( is_array( $attachment ) && ! empty( $attachment[0] ) ) ? $attachment[0] : $placeholder;
					} else {
						$thumbnail = $placeholder;
					}

					echo '<div class="merchant-metabox-field-media-content">';
					echo '<figure class="merchant-metabox-field-media-preview">';
					echo '<img src="' . esc_url( $thumbnail ) . '" data-placeholder="' . esc_url( $placeholder ) . '" />';
					echo '</figure>';

					echo '<div class="merchant-metabox-field-media-button">';
					echo '<a href="#" class="merchant-metabox-field-media-upload button">' . esc_html__( 'Upload/Add Image', 'merchant' ) . '</a>';
					echo '<a href="#" class="merchant-metabox-field-media-remove merchant-button-remove button' . esc_attr( $hidden_class ) . '">' . esc_html__( 'Remove Image', 'merchant' ) . '</a>';
					echo '<input type="hidden" name="' . esc_attr( $field_id ) . '" value="' . esc_attr( $value ) . '" class="merchant-metabox-field-media-input" />';
					echo '</div>';
					echo '</div>';

					break;

				case 'uploads':
					$field = wp_parse_args( $field, array(
						'button'  => '',
						'library' => 'image',
					) );

					$enable_thumb = isset( $field['enable_thumb'] ) && $field['enable_thumb'];

					echo '<div class="merchant-metabox-field-uploads-content">';
					$values     = ( is_array( $value ) && ! empty( $value ) ) ? $value : array();
					$name       = 'video' === $field['library'] ? $field_id . '[0][src]' : $field_id . '[]';
					$thumb_name = $field_id . '[0][thumb]';

					echo '<ul class="merchant-metabox-field-uploads-list" data-library="' . esc_attr( $field['library'] ) . '">';
					echo '<li class="merchant-metabox-field-uploads-list-item hidden">';

					if ( 'video' === $field['library'] || $enable_thumb ) {
						echo '<div class="merchant-metabox-field-uploads-thumbnail">';
						echo '<a href="#" class="merchant-metabox-field-uploads-thumbnail-remove dashicons dashicons-dismiss" style="display:none"></a>';
						echo '<a href="#" class="merchant-metabox-field-uploads-thumbnail-upload"><span>+</span></a>';
						echo '<input type="hidden" name="" value="" data-name="' . esc_attr( $thumb_name ) . '" />';
						echo '</div>';
					}

					echo '<input type="text" name="" value="" data-name="' . esc_attr( $name ) . '" />';
					echo '<button class="merchant-metabox-field-uploads-upload button">' . esc_html__( 'Upload', 'merchant' ) . '</button>';
					echo '<span class="merchant-metabox-field-uploads-move dashicons dashicons-menu"></span>';
					echo '<span class="merchant-metabox-field-uploads-remove dashicons dashicons-trash"></span>';
					echo '</li>';

					foreach ( $values as $key => $value ) {
						$item_name  = 'video' === $field['library'] || $enable_thumb ? str_replace( '0', $key, $name ) : $name;
						$item_value = is_array( $value ) ? ( isset( $value['src'] ) ? $value['src'] : '' ) : $value;

						echo '<li class="merchant-metabox-field-uploads-list-item">';
						if ( 'video' === $field['library'] || $enable_thumb ) {
							$item_thumb      = is_array( $value ) && isset( $value['thumb'] ) ? $value['thumb'] : '';
							$item_thumb_name = str_replace( '0', $key, $thumb_name );

							echo '<div class="merchant-metabox-field-uploads-thumbnail">';
							echo '<a href="#" class="merchant-metabox-field-uploads-thumbnail-remove dashicons dashicons-dismiss" ' . ( empty( $item_thumb ) ? 'style="display: none"' : '' ) . '></a>';
							echo '<a href="#" class="merchant-metabox-field-uploads-thumbnail-upload">';
							echo empty( $item_thumb )
								? '<span>+</span>'
								: '<img src="' . esc_url( wp_get_attachment_thumb_url( $item_thumb ) ) . '" /><span style="display: none">+</span>';
							echo '</a>';
							echo '<input type="hidden" name="' . esc_attr( $item_thumb_name ) . '" value="' . absint( $item_thumb ) . '" />';
							echo '</div>';
						}
						echo '<input type="text" name="' . esc_attr( $item_name ) . '" value="' . esc_attr( $item_value ) . '" />';

						echo '<button class="merchant-metabox-field-uploads-upload button">' . esc_html__( 'Upload', 'merchant' ) . '</button>';
						echo '<span class="merchant-metabox-field-uploads-move dashicons dashicons-menu"></span>';
						echo '<span class="merchant-metabox-field-uploads-remove dashicons dashicons-trash"></span>';
						echo '</li>';
					}

					echo '</ul>';
					echo '<button class="merchant-metabox-field-uploads-add button button-primary">' . esc_html( $field['button'] ) . '</button>';
					echo '</div>';

					break;

				case 'size-chart':
					$field = wp_parse_args( $field, array(
						'button' => '',
					) );

					echo '<div class="merchant-metabox-field-size-chart-content">';
					echo '<ul>';
					echo '<li class="hidden">';
					echo '<table>';
					echo '<thead>';
					echo '<tr>';
					echo '<td colspan="100%">';
					echo '<label>';
					echo '<strong>' . esc_html__( 'Chart Name', 'merchant' ) . ':</strong>';
					echo '<input type="text" value="" data-name="' . esc_attr( $field_id ) . '[0][name]" />';
					echo '</label>';
					echo '</td>';
					echo '</tr>';
					echo '</thead>';
					echo '<tbody>';
					echo '<tr>';
					for ( $a = 0; $a < 4; $a ++ ) {
						echo '<td><div class="merchant-buttons"><a href="#" class="merchant-add-col">+</a><a href="#" class="merchant-del-col">-</a></div></td>';
					}
					echo '<td><a href="#" class="merchant-duplicate" title="' . esc_attr__( 'Duplicate', 'merchant' ) . '"><i class="dashicons dashicons-admin-page"></i></td>';
					echo '</tr>';
					for ( $b = 0; $b < 4; $b ++ ) {
						echo '<tr>';
						for ( $c = 0; $c < 4; $c ++ ) {
							echo '<td><input type="text" value="" data-name="' . esc_attr( $field_id ) . '[0][sizes][0][0]" /></td>';
						}
						echo '<td><div class="merchant-buttons"><a href="#" class="merchant-add-row">+</a><a href="#" class="merchant-del-row">-</a></div></td>';
						echo '</tr>';
					}
					echo '</tbody>';
					echo '<tfoot>';
					echo '<tr>';
					echo '<td colspan="100%">';
					echo '<a href="#" class="merchant-remove button button-primary">' . esc_html__( 'Remove', 'merchant' ) . '</a>';
					echo '</td>';
					echo '</tr>';
					echo '</tfoot>';
					echo '</table>';
					echo '</li>';
					$tabs = ( is_array( $value ) && ! empty( $value ) ) ? $value : array();
					if ( ! empty( $tabs ) ) {
						foreach ( $tabs as $tab_key => $tab ) {
							$name  = ( ! empty( $tab['name'] ) ) ? $tab['name'] : '';
							$sizes = ( ! empty( $tab['sizes'] ) ) ? $tab['sizes'] : array();
							echo '<li>';
							echo '<table>';
							echo '<thead>';
							echo '<tr>';
							echo '<td colspan="100%">';
							echo '<label>';
							echo '<strong>' . esc_html__( 'Chart Name', 'merchant' ) . ':</strong>';
							echo '<input type="text" value="' . esc_attr( $name ) . '" name="' . esc_attr( $field_id . '[' . $tab_key . '][name]' ) . '" />';
							echo '</label>';
							echo '</td>';
							echo '</tr>';
							echo '</thead>';
							echo '<tbody>';
							foreach ( $sizes as $row_key => $rows ) {
								if ( 0 === $row_key ) {
									echo '<tr>';
									for ( $i = 0; $i < count( $rows ); $i ++ ) {
										echo '<td><div class="merchant-buttons"><a href="#" class="merchant-add-col">+</a><a href="#" class="merchant-del-col">-</a></div></td>';
									}
									echo '<td><a href="#" class="merchant-duplicate" title="' . esc_attr__( 'Duplicate', 'merchant' ) . '"><i class="dashicons dashicons-admin-page"></i></td>';
									echo '</tr>';
								}
								echo '<tr>';
								foreach ( $rows as $col_key => $col ) {
									echo '<td><input type="text" name="' . esc_attr( $field_id . '[' . $tab_key . '][sizes][' . $row_key . '][' . $col_key . ']' ) . '" value="' . esc_attr( $col ) . '" /></td>';
								}
								echo '<td><div class="merchant-buttons"><a href="#" class="merchant-add-row">+</a><a href="#" class="merchant-del-row">-</a></div></td>';
								echo '</tr>';
							}
							echo '</tbody>';
							echo '<tfoot>';
							echo '<tr>';
							echo '<td colspan="100%">';
							echo '<a href="#" class="merchant-remove button button-primary">' . esc_html__( 'Remove', 'merchant' ) . '</a>';
							echo '</td>';
							echo '</tr>';
							echo '</tfoot>';
							echo '</table>';
							echo '</li>';
						}
					}
					echo '</ul>';
					echo '<button class="merchant-add button button-primary">' . esc_html__( 'Add Size Chart', 'merchant' ) . '</button>';
					echo '</div>';

					break;

				case 'size-chart-select':
					$options = array();
					$posts   = get_posts( array(
						'post_type'      => 'merchant_size_chart',
						'posts_per_page' => - 1,
						'post_status'    => 'publish',
					) );

					if ( ! is_wp_error( $posts ) && ! empty( $posts ) ) {
						foreach ( $posts as $_post ) {
							$options[ $_post->ID ] = $_post->post_title;
						}
					}

					echo '<select name="' . esc_attr( $field_id ) . '">';
					echo '<option value="">' . esc_html__( 'Select a size chart', 'merchant' ) . '</option>';

					foreach ( $options as $key => $option ) {
						echo '<option value="' . esc_attr( $key ) . '"' . selected( $key, $value, false ) . '>' . esc_html( $option ) . '</option>';
					}
					echo '</select>';

					break;

				case 'sidebar-select':
					global $wp_registered_sidebars;

					$options = array();
					if ( ! empty( $wp_registered_sidebars ) ) {
						foreach ( $wp_registered_sidebars as $sidebar ) {
							$options[ $sidebar['id'] ] = $sidebar['name'];
						}
					}

					echo '<select name="' . esc_attr( $field_id ) . '">';
					echo '<option value="">' . esc_html__( 'Default', 'merchant' ) . '</option>';

					foreach ( $options as $key => $option ) {
						echo '<option value="' . esc_attr( $key ) . '"' . selected( $key, $value, false ) . '>' . esc_html( $option ) . '</option>';
					}
					echo '</select>';

					break;

				case 'wp-editor':
					$field = wp_parse_args( $field, array(
						'height' => 150,
					) );

					wp_editor( $value, $field_id, array(
						'editor_height' => $field['height'],
					) );

					break;

				case 'code-editor':
					echo '<textarea name="' . esc_attr( $field_id ) . '">' . esc_textarea( $value ) . '</textarea>';
					break;

				case 'coupons':
					$coupons = get_posts( array(
						'posts_per_page' => - 1,
						'orderby'        => 'title',
						'order'          => 'asc',
						'post_type'      => 'shop_coupon',
						'post_status'    => 'publish',
					) );
					if ( $coupons ) {
						echo '<select name="' . esc_attr( $field_id ) . '">';
						echo '<option value="">' . esc_html__( 'Select a coupon', 'merchant' ) . '</option>';

						foreach ( $coupons as $coupon ) {
							$coupon_code = strtolower( $coupon->post_title );

							echo '<option value="' . esc_attr( $coupon_code ) . '"' . selected( esc_attr( $coupon_code ), $value, false ) . '>';
							echo esc_html( $coupon_code );
							echo '</option>';
						}

						echo '</select>';
						echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=shop_coupon' ) ) . '" target="_blank" style="margin-left: 10px;">' . esc_html__( 'Manage coupons',
								'merchant' ) . '</a>';
					} else {
						echo '<div style="color: red;">';
						echo wp_kses_post(  
							sprintf( 
								/* Translators: 1. Coupon admin url 2. Link target attribute value */
								__( 'No coupons found! <a href="%1$s" target="%2$s">Create a new coupon</a>', 'merchant' ),
								admin_url( 'post-new.php?post_type=shop_coupon' ),
								'_blank' 
							) 
						);
						echo '</div>';
						echo '<input type="hidden" name="' . esc_attr( $field_id ) . '" value="" />';
					}
					break;

				case 'flexible-content':
					$field  = wp_parse_args( $field, array(
						'button' => '',
					) );
					$values = ( is_array( $value ) && ! empty( $value ) ) ? $value : array();

					$empty = empty( $values ) ? 'empty' : '';

					echo '<ul data-id="' . esc_attr( $field_id ) . '" class="merchant-metabox-field-flexible-content-list ' . esc_attr( $empty ) . '">';

					ob_start();
					echo '<li class="merchant-metabox-field-flexible-content-item">';
					foreach ( $field['layouts'] as $layout_type => $layout ) {
						echo '<div data-layout="' . esc_attr( $layout_type ) . '">';
						echo '<div class="merchant-metabox-field-flexible-content-item-header">';
						echo '<div class="merchant-metabox-field-flexible-content-item-count">0</div>';
						echo '<div class="merchant-metabox-field-flexible-content-item-title">' . esc_html( $layout['title'] ) . '</div>';
						echo '<div class="merchant-metabox-field-flexible-content-item-actions">';
						echo '<span class="merchant-metabox-field-flexible-content-move dashicons dashicons-menu"></span>';
						echo '<span class="merchant-metabox-field-flexible-content-remove dashicons dashicons-trash"></span>';
						echo '</div>';
						echo '</div>';
						echo '<div class="merchant-metabox-field-flexible-content-item-content">';
						foreach ( $layout['fields'] as $sub_field_key => $sub_field ) {
							$sub_field_classes = array( 'merchant-metabox-field-flexible-content-item-' . esc_attr( $sub_field['type'] ) );
							$sub_field_id      = $field_id . '[0][' . $sub_field_key . ']';
							$sub_field_value   = '';
							if ( $sub_field['type'] === 'select-ajax' ) {
								$sub_field_classes[] = 'merchant-metabox-field-flexible-content-select-ajax-clone';
								$sub_field_value     = array();
								$sub_field           = wp_parse_args( $sub_field, array(
									'source' => 'post',
								) );
							}
							echo '<div class="' . esc_attr( implode( ' ', $sub_field_classes ) ) . '">';
							echo '<label>';
							echo esc_html( $sub_field['title'] );
							echo '</label>';
							echo '<div class="merchant-metabox-field-flexible-content-item-field" data-id="' . esc_attr( $sub_field_key ) . '">';
							$this->get_field( $sub_field_id, $sub_field, $sub_field_value );
							echo '</div>';
							echo '</div>';
						}
						echo '</div>';
						echo '<input type="hidden" name="' . esc_attr( $field_id ) . '[0][layout]" value="' . esc_attr( $layout_type ) . '">';
						echo '</div>';
					}
					echo '</li>';
					$sub_fields = ob_get_clean();

					echo wp_kses( str_replace( 'name=', 'data-name=', $sub_fields ), merchant_kses_allowed_tags( array( 'all' ) ) );

					foreach ( $values as $value_key => $value ) {
						echo '<li class="merchant-metabox-field-flexible-content-item">';
						echo '<div class="merchant-metabox-field-flexible-content-item-header">';
						echo '<div class="merchant-metabox-field-flexible-content-item-count">' . esc_html( ( $value_key + 1 ) ) . '</div>';
						echo '<div class="merchant-metabox-field-flexible-content-item-title">' . esc_html( $field['layouts'][ $value['layout'] ]['title'] ) . '</div>';
						echo '<div class="merchant-metabox-field-flexible-content-item-actions">';
						echo '<span class="merchant-metabox-field-flexible-content-move dashicons dashicons-menu"></span>';
						echo '<span class="merchant-metabox-field-flexible-content-remove dashicons dashicons-trash"></span>';
						echo '</div>';
						echo '</div>';
						echo '<div class="merchant-metabox-field-flexible-content-item-content">';
						foreach ( $field['layouts'][ $value['layout'] ]['fields'] as $sub_field_key => $sub_field ) {
							$sub_field_classes = array( 'merchant-metabox-field-flexible-content-' . esc_attr( $sub_field['type'] ) );
							$sub_field_id      = $field_id . '[' . $value_key . '][' . $sub_field_key . ']';
							$sub_field_value   = isset( $value[ $sub_field_key ] ) ? $value[ $sub_field_key ] : '';
							if ( $sub_field['type'] === 'select-ajax' ) {
								$sub_field_classes[] = 'merchant-metabox-field-flexible-content-select-ajax';
								$sub_field_value     = empty( $sub_field_value ) ? array() : $sub_field_value;
								$sub_field           = wp_parse_args( $sub_field, array(
									'source' => 'post',
								) );
							}
							echo '<div class="' . esc_attr( implode( ' ', $sub_field_classes ) ) . '">';
							echo '<label>';
							echo esc_html( $sub_field['title'] );
							echo '</label>';
							echo '<div class="merchant-metabox-field-flexible-content-' . esc_attr( $sub_field['type'] ) . '-field" data-id="' . esc_attr( $sub_field_key ) . '">';
							$this->get_field( $sub_field_id, $sub_field, $sub_field_value );
							echo '</div>';
							echo '</div>';
						}
						echo '</div>';
						echo '<input type="hidden" name="' . esc_attr( $field_id ) . '[' . esc_attr( $value_key ) . '][layout]" value="' . esc_attr( $value['layout'] ) . '">';
						echo '</li>';
					}

					echo '</ul>';
					echo '<div class="merchant-metabox-field-flexible-content-add-wrapper">';
					echo '<div class="merchant-metabox-field-flexible-content-add-list">';
					foreach ( $field['layouts'] as $layout_type => $layout ) {
						echo '<a class="merchant-metabox-field-flexible-content-add" data-id="' . esc_attr( $field_id ) . '" data-layout="' . esc_attr( $layout_type ) . '" href="#">' . esc_html( $layout['title'] ) . '</a>';
					}
					echo '</div>';
					echo '<button class="merchant-metabox-field-flexible-content-add-button button button-primary">' . esc_html( $field['button'] ) . '</button>';
					echo '</div>';

					break;
			}
		}
	}
}
