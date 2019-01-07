<?php
require_once plugin_dir_path( __DIR__ ) . 'public/partials/collclubsports-table.php';
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
 * @author     Jamie Gerona <jamiegerona@gmail.com>
 */
class CollClubSports_Public_Search {

	protected $api = false;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct($api) {
		$this->api = $api;
		$this->register_shortcodes();
		$this->options = get_option('collclubsports_admin');
	}

	private function register_shortcodes() {
		add_shortcode('search', array($this, 'search'));
		add_shortcode('search-results', array($this, 'search_results'));
	}

	private function constructBioPageUrl($id) {
		return $this->options['bio_url'] . '?user=' . $id;
	}

	private function constructTeamUrl($teamid, $seasonid = null) {
		$url = $this->options['team_url'] . '?team=' . $teamid;

		if(empty($seasonid)) {
			$seasonid = $_GET['season'];
		}
		if(!empty($seasonid)) {
			$url = $url . '&season=' . $seasonid;
		}
		return $url;
	}

	private function constructConferenceUrl($conferenceid, $seasonid = null) {
		$url = $this->options['conference_url'] . '?conference=' . $conferenceid;

		if(empty($seasonid)) {
			$seasonid = $_GET['season'];
		}
		if(!empty($seasonid)) {
			$url = $url . '&season=' . $seasonid;
		}
		return $url;
	}

	public function search() {
		$url = home_url( $this->options['search_result_url']);

		$form = '<form role="search" method="get" id="searchform" action="' . $url . '">
				    <div>
				        <label class="screen-reader-text" for="s">Search for:</label>
				        <input type="search" class="search-field" placeholder="' . esc_attr_x( 'Search &hellip;', 'placeholder' ) . '" value="" name="keyword" id="keyword" />
				        <input type="submit" id="searchsubmit" class="searchsubmit" value="Search" />
				        <div class="search_btn"><img src="' . get_template_directory_uri() . '/images/search-icon.svg"></div>
				    </div>
				</form>';

		return $form;
	}

	public function search_results($attrs) {
		ob_start();
		$search_query = $_GET['keyword'];

		if (!$search_query || trim($search_query) == '') {
			return;
		}
		$resultsAPI = $this->api->search($search_query);
		$resultsW = new WP_Query(array( 's' => $search_query ));

		if (sizeof($resultsAPI) > 0 || $resultsW->have_posts()) {
		?>
		
		<h1 class="page-title" ><?php printf( __( 'Search Results for: %s', 'twentythirteen' ), $search_query ); ?></h1>

		<?php
			/* API results */
			for($i = 0; $i < sizeof($resultsAPI);$i++){
				$item = $resultsAPI[$i];

				switch($item->searchtypeid) {
					case 1: { // Player
						$url = $this->constructBioPageUrl($item->player->userid);
						break;
					}
					case 2: { // Team
						$url = $this->constructTeamUrl($item->team->teamid, $item->seasonid);
						break;
					}
					case 3: { // Conference
						$url = $this->constructConferenceUrl($item->conference->conferenceid, $item->seasonid);
						break;
					}
				}
			?>
			
			<h1 class="entry-title"><a href="<?php echo $url ?>"><?php echo $item->title ?></a></h1>
			

			<?php
			}
			/* Wordpress results*/
			if ( $resultsW->have_posts() ) :
				while ( $resultsW->have_posts() ) : $resultsW->the_post();
					get_template_part( 'content', get_post_format() );
				endwhile;
			endif;
		} else {
			?>
			<h1 class="page-title"><?php _e( 'Sorry, no posts were found', 'twentythirteen' ); ?></h1>
			<?php
		}

		return ob_get_clean();
	}
}
