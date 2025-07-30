<?php
/**
 * Merchant Pro upsell notice.
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Merchant_Notice_Upsell extends Merchant_Notice {
	
	/**
	 * Constructor.
	 * 
	 */
	public function __construct() {
		$this->id        = 'merchant-upsell-notice';
        $this->only_free = true;

		parent::__construct();

		add_action( 'admin_init', array( $this, 'set_plugin_installed_time' ), 0 );
	}

	/**
	 * Set plugin installed time in database.
	 * 
	 * @return void
	 */
	public function set_plugin_installed_time() {
		if ( ! get_option( 'merchant_plugin_installed_time' ) ) {
			update_option( 'merchant_plugin_installed_time', time() );
		}
	}

    /**
     * Show HTML markup if conditions meet.
     * 
     * @return void
     */
    public function notice_markup() { 
		if ( ( get_option( 'merchant_plugin_installed_time' ) > strtotime( '-3 day' ) ) ) {
			return;
		}

	    $url = merchant_admin_upgrade_link(
		    'https://athemes.com/merchant-upgrade',
		    array(
			    'utm_source'   => 'plugin_notice',
			    'utm_content'  => 'upgrade_notice',
			    'utm_medium'   => 'button',
			    'utm_campaign' => 'Merchant',
		    ),
            'plugin-admin-notice'
	    );

		?>
        <div class="merchant-notice merchant-notice-with-thumbnail notice" style="position:relative;">
			<h3><?php echo esc_html__( 'Supercharge Your Store with Merchant Pro! 🚀', 'merchant' ); ?></h3>

			<p>
				<?php
					echo esc_html__( 'With Merchant Pro\'s 40+ powerful modules — including Product Bundles, Frequently Bought Together, and Bulk Discounts — you can boost sales, enhance customer experience, and minimize cart abandonment, all from one convenient plugin.', 'merchant' );
				?>
			</p>

			<a href="<?php echo esc_url( $url ) ?>" class="merchant-btn merchant-btn-secondary" target="_blank"><?php esc_html_e( 'Upgrade To Merchant Pro', 'merchant' ); ?></a>
			
			<a class="notice-dismiss" href="?<?php echo esc_attr( $this->id ); ?>_dismiss=1" style="text-decoration:none;"></a>
		</div>

        <?php
    }
}

new Merchant_Notice_Upsell();
