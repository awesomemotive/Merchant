<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$merchant_module      = ( ! empty( $_GET['module'] ) ) ? sanitize_text_field( wp_unslash( $_GET['module'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$merchant_module_info = Merchant_Admin_Modules::get_module_info( $merchant_module );

Merchant_Admin_Preview::set_preview( $merchant_module );

?>

<?php if ( ! empty( $merchant_module_info ) ) : ?>

    <div class="merchant-module-page">

        <div class="merchant-module-page-content">

            <form method="post" action="" class="merchant-module-page-ajax-form" autocomplete="off">

                <input type="hidden" name="merchant_nonce" value="<?php echo esc_attr( wp_create_nonce( 'merchant_nonce' ) ); ?>"/>

                <div class="merchant-module-page-ajax-header">
                    <div class="merchant-module-page-ajax-notice"><?php esc_html_e( 'Your settings have been modified. Save?', 'merchant' ); ?></div>
                    <div class="merchant-module-page-ajax-buttons">
                        <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'merchant', 'module' => $merchant_module ), 'admin.php' ) ); ?>"
                            class="merchant-module-discard-button"><?php esc_html_e( 'Discard', 'merchant' ); ?></a>
                        <button type="submit" name="merchant_save" value="save" class="merchant-module-save-button"><span class="merchant-module-save-button-label"><?php esc_html_e( 'Save',
									'merchant' ); ?></span><i class="dashicons dashicons-update-alt"></i></button>
                    </div>
                </div>

                <div class="merchant-module-page-header">

                    <div class="merchant-module-page-header-content">

                        <div class="merchant-module-page-back">
                            <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'merchant' ), 'admin.php' ) ); ?>">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="16" height="16" rx="2" fill="white"/>
                                    <path d="M13.3329 7.20013H4.46628L7.19961 4.20013L6.46628 3.4668L2.59961 7.6668L6.46628 11.5335L7.19961 10.8001L4.53294 8.20013H13.3329V7.20013Z" fill="#1D2327"/>
                                </svg>
                                <span><?php echo esc_html__( 'Go Back', 'merchant' ); ?></span>
                            </a>
                        </div>

                        <div class="merchant-module-page-heading">
                            <div class="merchant-module-page-info">
                                <div class="merchant-module-page-title">
									<?php echo esc_html( $merchant_module_info['title'] ); ?>
                                </div>
                                <div class="merchant-module-page-desc"><?php echo esc_html( $merchant_module_info['desc'] ); ?></div>
                            </div>
                        </div>

                        <div class="merchant-module-page-actions">

                            <div class="merchant-module-page-actions-inner">

								<?php
								$merchant_module_enabled   = ( Merchant_Modules::is_module_active( $merchant_module ) ) ? ' merchant-enabled' : '';
								$merchant_module_is_upsell = ! defined( 'MERCHANT_PRO_VERSION' ) && isset( $merchant_module_info['pro'] ) && true === $merchant_module_info['pro'];
								?>

                                <div class="merchant-module-action<?php echo esc_attr( $merchant_module_enabled ); ?>">


									<?php if ( $merchant_module_is_upsell ) :
										$url = merchant_admin_upgrade_link(
											'https://athemes.com/merchant-upgrade',
											array(
												'utm_source'   => 'module_inner_settings',
												'utm_content'  => $merchant_module,
												'utm_medium'   => 'merchant_dashboard',
												'utm_campaign' => 'Merchant',
											),
											'module-inner-settings-upgrade-link'
										);
										?>
                                        <div class="merchant-module-buy">

                                            <a href="<?php echo esc_url( $url ); ?>" target="_blank"
                                                class="merchant-module-page-button ">
												<?php esc_html_e( 'Buy Pro', 'merchant' ); ?>
                                            </a>

                                        </div>
									<?php else : 
                                        /**
                                         * Hook 'merchant_admin_module_{module_id}_activate_button_class'
                                         * Filters the class name for the activate button.
                                         * 
                                         * @param string $button_activate_class The class name for the activate button.
                                         * @since 1.0
                                         */
                                        $button_activate_class = apply_filters( "merchant_admin_module_{$merchant_module}_activate_button_class", 'merchant-module-page-button merchant-module-activate merchant-module-page-button-action-activate' );
                                        ?>

                                        <div data-module="<?php echo esc_attr( $merchant_module ); ?>"
                                            class="<?php echo esc_attr( $button_activate_class ); ?>">
											<?php esc_html_e( 'Enable', 'merchant' ); ?>
                                        </div>
                                        <div class="merchant-module-deactivate">

                                            <div class="merchant-module-page-button merchant-module-page-button-deactivate">
												<?php esc_html_e( 'Enabled', 'merchant' ); ?>
                                                <svg width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M7.09832 3.98009e-07L0.901685 -1.43717e-07C0.15069 -2.09371e-07 -0.269991 0.75351 0.193991 1.2676L3.29231 4.70055C3.65265 5.09982 4.34735 5.09982 4.70769 4.70055L7.80601 1.2676C8.26999 0.753511 7.84931 4.63664e-07 7.09832 3.98009e-07Z"
                                                            fill="#3858E9"/>
                                                </svg>
                                            </div>

                                            <div class="merchant-module-deactivate-dropdown">
                                                <div data-module="<?php echo esc_attr( $merchant_module ); ?>" class="merchant-module-page-button-action-deactivate">
													<?php echo esc_html__( 'Disable', 'merchant' ) . ' ' . esc_html( $merchant_module_info['title'] ); ?>
                                                </div>
                                            </div>

                                        </div>
									<?php endif ?>


                                    <div class="merchant-module-dropdown merchant-module-question-list-dropdown">
                                        <div class="merchant-module-question-title">
											<?php esc_html_e( 'Did something go wrong?', 'merchant' ); ?><i class="dashicons dashicons-no-alt merchant-module-dropdown-close"></i>
                                        </div>
                                        <ul>
                                            <li data-answer-target="bug">
                                                <span><?php esc_html_e( 'Submit a bug: the module doesn’t work as expected', 'merchant' ); ?></span>
                                            </li>
                                            <li data-answer-target="missing">
                                                <span><?php esc_html_e( 'Some options are missing', 'merchant' ); ?></span>
                                            </li>
                                            <li data-answer-target="conflict">
                                                <span><?php esc_html_e( 'It conflicts with another feature', 'merchant' ); ?></span>
                                            </li>
                                            <li data-answer-target="thank-you">
                                                <span><?php esc_html_e( 'I don’t need it anymore.', 'merchant' ); ?></span>
                                            </li>
                                            <li data-answer-target="other">
                                                <span><?php esc_html_e( 'Other', 'merchant' ); ?></span>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="merchant-module-dropdown merchant-module-question-answer-dropdown">

                                        <div class="merchant-module-question-answer">

                                            <div class="merchant-module-question-answer-title" data-answer-title="bug">
												<?php esc_html_e( 'Please tell us why, and we’ll look into it.', 'merchant' ); ?><i
                                                        class="dashicons dashicons-no-alt merchant-module-dropdown-close"></i>
                                            </div>

                                            <div class="merchant-module-question-answer-title" data-answer-title="missing">
												<?php esc_html_e( 'What feature are you looking for?', 'merchant' ); ?><i class="dashicons dashicons-no-alt merchant-module-dropdown-close"></i>
                                            </div>

                                            <div class="merchant-module-question-answer-title" data-answer-title="conflict">
												<?php esc_html_e( 'Please tell us what plugin/theme it conflicts with.', 'merchant' ); ?><i
                                                        class="dashicons dashicons-no-alt merchant-module-dropdown-close"></i>
                                            </div>

                                            <div class="merchant-module-question-answer-title" data-answer-title="other">
												<?php esc_html_e( 'Would you mind telling us why?', 'merchant' ); ?><i class="dashicons dashicons-no-alt merchant-module-dropdown-close"></i>
                                            </div>

                                        </div>

                                        <div class="merchant-module-question-answer-form">
                                            <textarea class="merchant-module-question-answer-textarea" data-subject=""></textarea>
                                            <button class="merchant-module-page-button merchant-module-question-answer-button" disabled><?php esc_html_e( 'Submit', 'merchant' ); ?></button>
                                        </div>

                                    </div>

                                    <div class="merchant-module-dropdown merchant-module-question-thank-you-dropdown">

                                        <div class="merchant-module-question-answer-title">
											<?php esc_html_e( 'Thank you', 'merchant' ); ?><i class="dashicons dashicons-no-alt merchant-module-dropdown-close"></i>
                                        </div>

                                        <div class="merchant-module-question-answer-thank-you-text">
											<?php esc_html_e( 'Your feedback helps us improve Merchant.', 'merchant' ); ?>
                                        </div>

                                    </div>

                                </div>

                                <div class="merchant-module-page-actions-links">

									<?php if ( ! empty( $merchant_module_info['tutorial_url'] ) ) : ?>
                                        <a href="<?php echo esc_url( $merchant_module_info['tutorial_url'] ); ?>" class="merchant-module-page-link merchant-module-page-link-tutorial" target="_blank">
                                            <span><?php echo esc_html__( 'Tutorial', 'merchant' ); ?></span>
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11.4375 0H8.25C7.94531 0 7.66406 0.1875 7.54688 0.492188C7.42969 0.773438 7.5 1.10156 7.71094 1.3125L8.67188 2.27344L4.14844 6.79688C3.84375 7.07812 3.84375 7.57031 4.14844 7.85156C4.28906 7.99219 4.47656 8.0625 4.6875 8.0625C4.875 8.0625 5.0625 7.99219 5.20312 7.85156L9.72656 3.32812L10.6875 4.28906C10.8281 4.42969 11.0156 4.5 11.2266 4.5C11.3203 4.5 11.4141 4.5 11.5078 4.45312C11.8125 4.33594 12 4.05469 12 3.75V0.5625C12 0.257812 11.7422 0 11.4375 0ZM9.1875 7.5C8.85938 7.5 8.625 7.75781 8.625 8.0625V10.6875C8.625 10.8047 8.53125 10.875 8.4375 10.875H1.3125C1.19531 10.875 1.125 10.8047 1.125 10.6875V3.5625C1.125 3.46875 1.19531 3.375 1.3125 3.375H3.9375C4.24219 3.375 4.5 3.14062 4.5 2.8125C4.5 2.50781 4.24219 2.25 3.9375 2.25H1.3125C0.585938 2.25 0 2.85938 0 3.5625V10.6875C0 11.4141 0.585938 12 1.3125 12H8.4375C9.14062 12 9.75 11.4141 9.75 10.6875V8.0625C9.75 7.75781 9.49219 7.5 9.1875 7.5Z"
                                                        fill="#3858E9"/>
                                            </svg>
                                        </a>
									<?php endif; ?>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

				<?php
				/**
				 * Hook 'merchant_admin_after_module_page_page_header'
				 *
				 * @since 1.0
                 * @updated 2.0 added $merchant_module as parameter
				 */
				do_action( 'merchant_admin_after_module_page_page_header', $merchant_module );

				?>

                <div class="merchant-module-page-body <?php echo Merchant_Admin_Preview::has_preview() ? 'has-preview' : ''; ?>">

                    <div class="merchant-module-page-content">

						<?php

						/**
						 * Hook: merchant_module_file_path
						 *
						 * @since 1.0
						 */
						$merchant_module_file = apply_filters( 'merchant_module_file_path',
							MERCHANT_DIR . 'admin/modules/' . $merchant_module . '/admin-page-' . $merchant_module . '.php',
							$merchant_module );

						if ( file_exists( $merchant_module_file ) ) {
							require $merchant_module_file;
						}

						?>
                        <div class="merchant-module-page-settings backup-restore-section">
                            <div class="merchant-module-page-setting-box">
                                <div class="merchant-module-page-setting-title">
                                    <?php esc_html_e( 'Backup & Restore', 'merchant' ); ?>
                                </div>
                                <div class="merchant-module-page-setting-fields">
                                    <section class="backup-section">
                                        <h3><?php esc_html_e( 'Backup Module Settings', 'merchant' ); ?></h3>
                                        <p><?php esc_html_e( 'Download a backup of your currently saved module settings as a JSON file. To capture the most up-to-date configuration, remember to save your settings before downloading the backup. You can then use this file to restore your settings later.', 'merchant' ); ?></p>
                                        <div class="form-field">
                                            <button type="button" data-module-id="<?php echo esc_attr($merchant_module)?>" id="download-backup-button"><?php esc_html_e( 'Download', 'merchant' ); ?></button>
                                            <span class="merchant-loading-spinner"></span>
                                        </div>
                                    </section>
                                    <section class="restore-section">
                                        <h3><?php esc_html_e( 'Restore Module Settings', 'merchant' ); ?></h3>
                                        <p><?php esc_html_e( 'Restore the module settings by uploading a previously saved backup file. Please be aware that restoring settings will overwrite your current module configurations.', 'merchant' ); ?></p>
                                        <div class="form-field">
                                            <label for="backup-file"><?php esc_html_e( 'Upload Backup File', 'merchant' ); ?></label>
                                            <input type="file" id="merchant-backup-file" class="merchant-backup-file" name="backup-file" accept=".json">
                                            <i style="display: none;" class="dashicons dashicons-no-alt backup-file-remove"></i>
                                            <span class="merchant-loading-spinner"></span>
                                            <button type="button" data-module-id="<?php echo esc_attr( $merchant_module )?>" id="restore-backup-button" class="restore-button"><?php esc_html_e( 'Restore', 'merchant' ); ?></button>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>

					<?php if ( Merchant_Admin_Preview::has_preview() ) : ?>

                        <div class="merchant-module-page-preview">
                            <div class="merchant-module-page-preview-box">
                                <div class="merchant-module-page-preview-title">
									<?php esc_html_e( 'Preview', 'merchant' ); ?>
                                </div>
                                <div class="merchant-module-page-preview-browser">
                                    <div class="merchant-module-page-preview-browser-top">
                                        <span class="merchant-module-page-preview-browser-top-circle"></span>
                                        <span class="merchant-module-page-preview-browser-top-circle"></span>
                                        <span class="merchant-module-page-preview-browser-top-circle"></span>
                                    </div>
                                    <div class="merchant-module-page-preview-browser-inner">
										<?php echo wp_kses( Merchant_Admin_Preview::get_html(), merchant_kses_allowed_tags( array( 'all' ) ) ); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

					<?php endif; ?>

                </div>

            </form>

        </div>

    </div>

    <div class="merchant-module-alert">
        <div class="merchant-module-alert-overlay"></div>
        <div class="merchant-module-alert-wrapper">
            <div class="merchant-module-alert-header"><strong><?php esc_html_e( 'Important info!', 'merchant' ); ?></strong><a href="#" class="merchant-module-alert-close"><i
                            class="dashicons dashicons-no-alt"></i></a></div>
            <div class="merchant-module-alert-content">
                <figure><img src="<?php echo esc_url( MERCHANT_URI . 'assets/images/enable-module.png' ); ?>"/></figure>
                <p><?php esc_html_e( 'This module is currently disabled. Please enable the module to see it in your store.', 'merchant' ); ?></p>
            </div>
        </div>
    </div>

    <div class="merchant-module-footer-text">
		<?php esc_html_e( 'Copyright © aThemes ' . gmdate( 'Y' ) . '. All Rights Reserved.', 'merchant' ); ?>
    </div>

<?php else : ?>

	<?php wp_safe_redirect( add_query_arg( array( 'page' => 'merchant' ), admin_url( 'admin.php' ) ) ); ?>

<?php endif; ?>
