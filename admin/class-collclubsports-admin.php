<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CollClubSports
 * @subpackage CollClubSports/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CollClubSports
 * @subpackage CollClubSports/admin
 * @author     Bretch Guire Garcinez <bgarcinez@gmail.com>
 */
class CollClubSports_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	const MENU_SLUG = "collclubsports-admin";

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'admin_menu', array ( &$this, 'collclubsports_admin_menu' ) );
		
		add_action( 'admin_init', array ( &$this, 'collclubsports_admin_init' ) );

	}

	/**
	 * Load admin page field settings
	 */
	public function collclubsports_admin_init() {
		register_setting('collclubsports-admin-settings', 'collclubsports_admin');
		add_filter( 'pre_update_option_collclubsports_admin', array ( &$this, 'collclubsports_update' ));
	}

	public function collclubsports_update($options) {
		try {
			$url = $options['api_url'] . '/v1/league/' . $options['league_id'];
			$header = array( 
				'timeout' => 120, 
				'httpversion' => '1.1',
				'headers' => array('league-api-key' => $options['league_api_key']));
			$response = wp_remote_get($url, $header);
			$options['sport_type'] = json_decode($response['body'])->sporttype;
		}
		catch (HttpException $ex){
			return $ex;
		}
		return $options;
	}

	/**
	 * Create dedicated admin menu
	 */
	public function collclubsports_admin_menu() {
		if ( current_user_can( 'manage_options' ) ) {
			add_menu_page(
				'CollClubSports',                                 // page title
				'CollClubSports',                                 // menu title
				'manage_options',                              // capability
				'collclubsports-admin',                                 // menu slug
				array ( &$this, 'admin_page_collclubsports' ),    // callback function, defined in this class
				'',                                            // logo
				null                                           // position
			);
		}
	}

	/**
	 * Load admin display field
	 */
	public function admin_page_collclubsports() {
		echo '<form action="options.php" method="POST" class="collclubsports-admin-wrapper">';
		settings_fields('collclubsports-admin-settings');
		do_settings_sections( 'collclubsports-admin-settings' );
		require_once plugin_dir_path( __DIR__ ) . 'admin/partials/collclubsports-admin-display.php';
		submit_button();
		echo '</form>';
		require_once plugin_dir_path( __DIR__ ) . 'admin/partials/collclubsports-admin-shortcodes.php';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in CollClubSports_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The CollClubSports_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/collclubsports-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in CollClubSports_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The CollClubSports_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/collclubsports-admin.js', array( 'jquery' ), $this->version, false );
		// For color picker
		wp_enqueue_style( 'wp-color-picker' );
    	wp_enqueue_script( 'wp-color-picker-script-handler', plugins_url('js/collclubsports-admin.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
	}

}
