<?php

/**
 * The plugin bootstrap file
 *
 * @link              http://collclubsports.com
 * @since             1.0.0
 * @package           CollClubSports
 *
 * @wordpress-plugin
 * Plugin Name:       CollClubSports
 * Plugin URI:        http://collclubsports.com
 * Description:       CollClubSports plugin that will retrieve all data from the centralized database.
 * Version:           1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-collclubsports-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __DIR__ ) . 'collclubsports/includes/class-collclubsports-activator.php';
	CollClubSports_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-collclubsports-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __DIR__ ) . 'collclubsports/includes/class-collclubsports-deactivator.php';
	CollClubSports_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __DIR__ )  . 'collclubsports/includes/class-collclubsports.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new CollClubSports();
	$plugin->run();

}
run_plugin_name();

// Team Stats Page

add_action( 'wp_ajax_collclubsports_conference_standing', 'collclubsports_conference_standing_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_conference_standing', 'collclubsports_conference_standing_callback' );
function collclubsports_conference_standing_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	echo $team->showConferenceStanding();
	die();
}

add_action( 'wp_ajax_collclubsports_stats_tracker', 'collclubsports_stats_tracker_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_stats_tracker', 'collclubsports_stats_tracker_callback' );
function collclubsports_stats_tracker_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	echo $team->showStatsTracker();
	die();
}

add_action( 'wp_ajax_collclubsports_player_of_the_week_ad', 'collclubsports_player_of_the_week_ad_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_player_of_the_week_ad', 'collclubsports_player_of_the_week_ad_callback' );
function collclubsports_player_of_the_week_ad_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	echo $team->showPlayerOfTheWeekAds();
	die();
}

add_action( 'wp_ajax_collclubsports_team_schedule', 'collclubsports_team_schedule_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_team_schedule', 'collclubsports_team_schedule_callback' );
function collclubsports_team_schedule_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	echo $team->showTeamSchedule();
	die();
}

add_action( 'wp_ajax_collclubsports_team_roster', 'collclubsports_team_roster_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_team_roster', 'collclubsports_team_roster_callback' );
function collclubsports_team_roster_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	$team_roster = $team->showTeamRoster();
	echo '<div style="overflow:auto;">';
		$team_roster;
	echo '</div>';
	die();
}

add_action( 'wp_ajax_collclubsports_football_offensive_team_stats', 'collclubsports_football_offensive_team_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_football_offensive_team_stats', 'collclubsports_football_offensive_team_stats_callback' );
function collclubsports_football_offensive_team_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	$team->showFootballOffensiveTeamStats();
	die();
}

add_action( 'wp_ajax_collclubsports_football_defensive_team_stats', 'collclubsports_football_defensive_team_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_football_defensive_team_stats', 'collclubsports_football_defensive_team_stats_callback' );
function collclubsports_football_defensive_team_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	$team->showFootballDefensiveTeamStats();
	die();
}

add_action( 'wp_ajax_collclubsports_football_special_team_stats', 'collclubsports_football_special_team_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_football_special_team_stats', 'collclubsports_football_special_team_stats_callback' );
function collclubsports_football_special_team_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	$team->showFootballSpecialTeamStats();
	die();
}

add_action( 'wp_ajax_collclubsports_softball_hitting_stats', 'collclubsports_softball_hitting_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_softball_hitting_stats', 'collclubsports_softball_hitting_stats_callback' );
function collclubsports_softball_hitting_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	$team->showSoftballHittingStats();
	die();
}

add_action( 'wp_ajax_collclubsports_softball_pitching_stats', 'collclubsports_softball_pitching_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_softball_pitching_stats', 'collclubsports_softball_pitching_stats_callback' );
function collclubsports_softball_pitching_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	$team->showSoftballPitchingStats();
	die();
}

add_action( 'wp_ajax_collclubsports_baseball_hitting_stats', 'collclubsports_baseball_hitting_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_baseball_hitting_stats', 'collclubsports_baseball_hitting_stats_callback' );
function collclubsports_baseball_hitting_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	$team->showBaseballHittingStats();
	die();
}

add_action( 'wp_ajax_collclubsports_baseball_pitching_stats', 'collclubsports_baseball_pitching_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_baseball_pitching_stats', 'collclubsports_baseball_pitching_stats_callback' );
function collclubsports_baseball_pitching_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	$team->showBaseballPitchingStats();
	die();
}

add_action( 'wp_ajax_collclubsports_basketball_stats', 'collclubsports_basketball_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_basketball_stats', 'collclubsports_basketball_stats_callback' );
function collclubsports_basketball_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	$team->showBasketballStats();
	die();
}

// Stats Page

add_action( 'wp_ajax_collclubsports_football_offensive_passing_stats', 'collclubsports_football_offensive_passing_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_football_offensive_passing_stats', 'collclubsports_football_offensive_passing_stats_callback' );
function collclubsports_football_offensive_passing_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Stats($api);
	$team->showFootballOffensivePassingStats();
	die();
}

add_action( 'wp_ajax_collclubsports_football_offensive_rushing_stats', 'collclubsports_football_offensive_rushing_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_football_offensive_rushing_stats', 'collclubsports_football_offensive_rushing_stats_callback' );
function collclubsports_football_offensive_rushing_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Stats($api);
	$team->showFootballOffensiveRushingStats();
	die();
}

add_action( 'wp_ajax_collclubsports_football_offensive_receiving_stats', 'collclubsports_football_offensive_receiving_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_football_offensive_receiving_stats', 'collclubsports_football_offensive_receiving_stats_callback' );
function collclubsports_football_offensive_receiving_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Stats($api);
	$team->showFootballOffensiveReceivingStats();
	die();
}

add_action( 'wp_ajax_collclubsports_football_offensive_fieldgoal_stats', 'collclubsports_football_offensive_fieldgoal_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_football_offensive_fieldgoal_stats', 'collclubsports_football_offensive_fieldgoal_stats_callback' );
function collclubsports_football_offensive_fieldgoal_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Stats($api);
	$team->showFootballOffensiveFieldGoalStats();
	die();
}

add_action( 'wp_ajax_collclubsports_football_defensive_defense_stats', 'collclubsports_football_defensive_defense_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_football_defensive_defense_stats', 'collclubsports_football_defensive_defense_stats_callback' );
function collclubsports_football_defensive_defense_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Stats($api);
	$team->showFootballDefensiveDefenseStats();
	die();
}

add_action( 'wp_ajax_collclubsports_football_defensive_interception_stats', 'collclubsports_football_defensive_interception_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_football_defensive_interception_stats', 'collclubsports_football_defensive_interception_stats_callback' );
function collclubsports_football_defensive_interception_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Stats($api);
	$team->showFootballDefensiveInterceptionStats();
	die();
}

add_action( 'wp_ajax_collclubsports_football_special_team_punting_stats', 'collclubsports_football_special_team_punting_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_football_special_team_punting_stats', 'collclubsports_football_special_team_punting_stats_callback' );
function collclubsports_football_special_team_punting_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Stats($api);
	$team->showFootballSpecialTeamPuntingStats();
	die();
}

add_action( 'wp_ajax_collclubsports_football_special_team_punt_return_stats', 'collclubsports_football_special_team_punt_return_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_football_special_team_punt_return_stats', 'collclubsports_football_special_team_punt_return_stats_callback' );
function collclubsports_football_special_team_punt_return_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Stats($api);
	$team->showFootballSpecialTeamPuntReturnStats();
	die();
}

add_action( 'wp_ajax_collclubsports_football_special_team_kick_return_stats', 'collclubsports_football_special_team_kick_return_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_football_special_team_kick_return_stats', 'collclubsports_football_special_team_kick_return_stats_callback' );
function collclubsports_football_special_team_kick_return_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Stats($api);
	$team->showFootballSpecialTeamKickReturnStats();
	die();
}

add_action( 'wp_ajax_collclubsports_softball_stats_page_hitting_stats', 'collclubsports_softball_stats_page_hitting_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_softball_stats_page_hitting_stats', 'collclubsports_softball_stats_page_hitting_stats_callback' );
function collclubsports_softball_stats_page_hitting_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Stats($api);
	$team->showSoftballHittingStats();
	die();
}

add_action( 'wp_ajax_collclubsports_softball_stats_page_pitching_stats', 'collclubsports_softball_stats_page_pitching_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_softball_stats_page_pitching_stats', 'collclubsports_softball_stats_page_pitching_stats_callback' );
function collclubsports_softball_stats_page_pitching_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Stats($api);
	$team->showSoftballPitchingStats();
	die();
}

add_action( 'wp_ajax_collclubsports_baseball_stats_page_hitting_stats', 'collclubsports_baseball_stats_page_hitting_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_baseball_stats_page_hitting_stats', 'collclubsports_baseball_stats_page_hitting_stats_callback' );
function collclubsports_baseball_stats_page_hitting_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Stats($api);
	$team->showBaseballHittingStats();
	die();
}

add_action( 'wp_ajax_collclubsports_baseball_stats_page_pitching_stats', 'collclubsports_baseball_stats_page_pitching_stats_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_baseball_stats_page_pitching_stats', 'collclubsports_baseball_stats_page_pitching_stats_callback' );
function collclubsports_baseball_stats_page_pitching_stats_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Stats($api);
	$team->showBaseballPitchingStats();
	die();
}

add_action( 'wp_ajax_collclubsports_conference_teams', 'collclubsports_conference_teams_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_conference_teams', 'collclubsports_conference_teams_callback' );
function collclubsports_conference_teams_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Conference($api);
	$team->showConferenceTeams();
	die();
}

add_action( 'wp_ajax_collclubsports_conference_standings', 'collclubsports_conference_standings_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_conference_standings', 'collclubsports_conference_standings_callback' );
function collclubsports_conference_standings_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Conference($api);
	$team->showConferenceStandings();
	die();
}

add_action( 'wp_ajax_collclubsports_conference_stats_tracker', 'collclubsports_conference_stats_tracker_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_conference_stats_tracker', 'collclubsports_conference_stats_tracker_callback' );
function collclubsports_conference_stats_tracker_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Conference($api);
	$team->showConferenceStatsTracker();
	die();
}

add_action( 'wp_ajax_collclubsports_conference_schedule', 'collclubsports_conference_schedule_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_conference_schedule', 'collclubsports_conference_schedule_callback' );
function collclubsports_conference_schedule_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Conference($api);
	$team->showConferenceSchedule();
	die();
}

add_action( 'wp_ajax_collclubsports_conference_player_of_the_week_ads', 'collclubsports_conference_player_of_the_week_ads_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_conference_player_of_the_week_ads', 'collclubsports_conference_player_of_the_week_ads_callback' );
function collclubsports_conference_player_of_the_week_ads_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Conference($api);
	echo $team->showConferencePlayerOfTheWeekAds();
	die();
}

// Home page POW and Ads section
add_action( 'wp_ajax_collclubsports_home_pow_ads', 'collclubsports_home_pow_ads_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_home_pow_ads', 'collclubsports_home_pow_ads_callback' );
function collclubsports_home_pow_ads_callback() {
	$api = new CollClubSports_Api();
	$player = new CollClubSports_Public_Player($api);
	echo $player->showLeaguePlayerWeek();
	die();
}

// Contact page contact person
add_action( 'wp_ajax_collclubsports_league_contacts', 'collclubsports_league_contacts_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_league_contacts', 'collclubsports_league_contacts_callback' );
function collclubsports_league_contacts_callback() {
	$api = new CollClubSports_Api();
	$contact = new CollClubSports_Public_Contact($api);
	echo $contact->showLeagueContacts();
	die();
}

// Contact page team information
add_action( 'wp_ajax_collclubsports_league_team_information', 'collclubsports_league_team_information_callback' );
add_action( 'wp_ajax_nopriv_collclubsports_league_team_information', 'collclubsports_league_team_information_callback' );
function collclubsports_league_team_information_callback() {
	$api = new CollClubSports_Api();
	$team = new CollClubSports_Public_Team($api);
	echo $team->showLeagueTeamInformation();
	die();
}

/**
 * Add inline CSS with defined collclub plugin primary and secondary colors
 */
add_action( 'wp_head', 'collclub_custom_inline_css' );
function collclub_custom_inline_css() {
	// Get settings and check colors
	$options = get_option('collclubsports_admin');
	if ( !empty($options) && !empty($options['color_primary']) )
		$primaryColor = $options['color_primary'];
	if ( !empty($options) && !empty($options['color_secondary']) )
		$secondayColor = $options['color_secondary'];
	// Default colors
	if ( !isset($primaryColor) || empty($primaryColor) )
		$primaryColor = '#000';
	if ( !isset($secondayColor) || empty($secondayColor) )
		$secondayColor = '#848484';

	// All the user input CSS settings as set in the plugin settings
    echo 
      '<style type="text/css">'
    // Primary color
    . '	.collclubsports-component.player-week-wrapper .player-week-body .no-image { border-color: ' . $primaryColor . '; }'
    . '	.collclubsports-component.player-week-wrapper .player-week-image-wrapper .player-profile-wrapper .no-image,'
	. '	.collclubsports-component.player-week-wrapper .player-week-image-wrapper .player-profile-wrapper img { border-color: ' . $primaryColor . '; }'
    . '	.collclubsports-page-wrapper .primary-color, .collclubsports-component .primary-color { color: ' . $primaryColor . '; }'
    . '	.collclubsports-page-wrapper  .primary-bg-color { background: ' . $primaryColor . '; }'
    . '	.collclubsports-page-wrapper .primary-border-color { border-color: ' . $primaryColor . '; }'
    . '	.collclubsports-page-wrapper .link { border-bottom-color: ' . $primaryColor . '; }'
    . '	.collclubsports-page-wrapper .button, .collclubsports-component .button { color: ' . $primaryColor . '; }'
    . '	.collclubsports-page-wrapper a.button:hover, .collclubsports-page-wrapper a.button:focus,'
	. '	.collclubsports-component a.button:hover, .collclubsports-component a.button:focus { color: ' . $primaryColor . ' !important; }'
	. '	.collclubsports-page-wrapper .button.accent { background: ' . $primaryColor . '; }'
	. '	.collclubsports-page-wrapper a.primary-color { color: ' . $primaryColor . '; }'
	. '	.collclubsports-page-wrapper a.primary-color:hover, .collclubsports-page-wrapper a.primary-color:focus { color: ' . $primaryColor . ' !important; }'
    . '	.collclubsports-page-wrapper .stats-table thead { background-color: ' . $primaryColor . '; }'
    . '	.collclubsports-page-wrapper .stats-table td a { color: ' . $primaryColor . ' !important; }'
    . '	.collclubsports-page-wrapper .tab-button-wrapper .button.active { background: ' . $primaryColor . ' !important; }'
    . '	.collclubsports-component.box-wrapper { border-color: ' . $primaryColor . '; }'
    . '	.collclubsports-component.box-wrapper .box-title { background-color: ' . $primaryColor . '; }'
    . '	#stats-page.collclubsports-page-wrapper .tab-button-wrapper .button.active { background: ' . $primaryColor . ' !important; }'
    . '	.collclubsports-component.team-information h5 { background-color: ' . $primaryColor . '; }'
    . '	.collclubsports-component.team-information .button { border-color: ' . $primaryColor . '; }'
    . '	.bio-page .bio-profile-wrapper img { border-color: ' . $primaryColor . '; }'
    . '	.team-page .team-page-button-wrapper .team-page-button, .team-page .team-page-button-wrapper .team-social-media { color: ' . $primaryColor . '; }'
    // Secondary color
    . '	.collclubsports-page-wrapper .button.folded-corner:after { border-bottom-color: ' . $secondayColor . '; border-left-color: ' . $secondayColor . '; }'
    . '</style>';
}
