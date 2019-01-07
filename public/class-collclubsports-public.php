<?php
require_once plugin_dir_path( __DIR__ ) . 'includes/class-collclubsports-api.php';
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CollClubSports
 * @subpackage CollClubSports/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CollClubSports
 * @subpackage CollClubSports/public
 * @author     Bretch Guire Garcinez <bgarcinez@gmail.com>
 */
class CollClubSports_Public {

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

	private $team;

	private $conference;

	private $schedule;

	private $stats;

	private $player;

	private $contact;

	private $search;

	private $championshipteam;
	
	private $api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->load_dependencies();
		$this->define_hooks();
	}

	/**
	 * Load required dependencies
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for team shortcodes
		 */
		require_once plugin_dir_path( __DIR__ ) . 'public/class-collclubsports-public-team.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-collclubsports-public-conference.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-collclubsports-public-schedule.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-collclubsports-public-stats.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-collclubsports-public-player.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-collclubsports-public-contact.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-collclubsports-public-search.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-collclubsports-public-championship-team.php';
	}

	/**
	 * Register all of the hooks 
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_hooks() {
		$this->api = new CollClubSports_Api();
		$this->team = new CollClubSports_Public_Team($this->api);
		$this->conference = new CollClubSports_Public_Conference($this->api);
		$this->schedule = new CollClubSports_Public_Schedule($this->api);
		$this->stats = new CollClubSports_Public_Stats($this->api);
		$this->player = new CollClubSports_Public_Player($this->api);
		$this->contact = new CollClubSports_Public_Contact($this->api);
		$this->search = new CollClubSports_Public_Search($this->api);
		$this->championshipteam = new CollClubSports_Public_Championship_Team($this->api);
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/collclubsports-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name .'-bootstrap', plugin_dir_url( __FILE__ ) . 'css/bootstrap.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-schedule', plugin_dir_url( __FILE__ ) . 'css/collclubsports-schedule.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-team-list', plugin_dir_url( __FILE__ ) . 'css/collclubsports-team-list.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-conference-list', plugin_dir_url( __FILE__ ) . 'css/collclubsports-conference-list.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-stats', plugin_dir_url( __FILE__ ) . 'css/collclubsports-stats.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-player', plugin_dir_url( __FILE__ ) . 'css/collclubsports-player.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-conference', plugin_dir_url( __FILE__ ) . 'css/collclubsports-conference.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-contact', plugin_dir_url( __FILE__ ) . 'css/collclubsports-contact.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-championship-team', plugin_dir_url( __FILE__ ) . 'css/collclubsports-championship-team-list.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-fontawesome', plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/collclubsports-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'collclub_options', get_option('collclubsports_admin') );
	}
}
