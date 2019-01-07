<?php
require_once plugin_dir_path( __DIR__ ) . 'partials/collclubsports-table.php';
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
class CollClubSports_Public_Football_Stats {

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
		add_shortcode('football-stats', array($this, 'stats'));
		add_shortcode('football-game-stats', array($this, 'gamestats'));
	}

	private function constructPlayerUrl($playerid, $seasonid = null) {
		$url = $this->options['player_url'] . '?player=' . $playerid;

		if(!empty($seasonid)) {
			$url = $url . '&season=' . $seasonid;
		}
		return $url;
	}

	private function constructSortUrl($key, $sorttype = null, $seasonid = null, $teamid = null, $playerid = null, $type = null, $subtype = null) {
		$url = $this->options['stats_url'] . '?sort=' . $key;

		if(!empty($sorttype)) {
			$url = $url . '&sorttype=' . $sorttype;
		} else {
			$url = $url . '&sorttype=asc';
		}
		if(!empty($seasonid)) {
			$url = $url . '&season=' . $seasonid;
		}
		if(!empty($teamid)) {
			$url = $url . '&team=' . $teamid;
		}
		if(!empty($playerid)) {
			$url = $url . '&player=' . $playerid;
		}
		if(!empty($type)) {
			$url = $url . '&type=' . $type;
		}
		if (!empty($subtype)) {
			$url = $url . '&subtype=' . $subtype;
		}
		return $url;
	}

	private function setup() {
		echo '<div class="tab-button-wrapper">';
			$this->addButton('Passing Stats', 'passing-stats', 'active');
			$this->addButton('Rushing Stats', 'rushing-stats');
			$this->addButton('Receiving Stats', 'receiving-stats');
			$this->addButton('Kicking Stats', 'field-goal-stats');
		$this->endDiv();
	}

	public function gamestats($attrs) {
		$gameid = $attrs['game'];
		$gamestats = $this->api->getFootballStatsPerGame($gameid);
		$this->tableoptions = array(
			'disablesort' => true
		);
		// echo json_encode($gamestats);
		$columns = $this->getStandardColumns(null, null,  null, null, null, $gameid);
		echo '<div class="stats-page collclubsports-page-wrapper game-stats-wrapper">';
			$visitingteamscore = ' ('. $gamestats->game->visitingteamscore . ')';
			$visitingteamname = (!empty($gamestats->visitingteam) && !empty($gamestats->visitingteam->teamname)) 
				? $gamestats->visitingteam->teamname : $gamestats->game->visitingteamwritein;
			echo '<h3>Visiting Team: ' . $visitingteamname . $visitingteamscore . '</h3>';
			$this->setupTab();
				echo '<div class="tab-button-wrapper game-stats-button-wrapper">';
					$this->addButton('Offensive Stats', 'visitingteam-offensive-stats', 'active');
					$this->addButton('Defensive Stats', 'visitingteam-defensive-stats');
					$this->addButton('Special Team  Stats', 'visitingteam-special-stats');
				$this->endDiv();
				$this->setupTabBody();
					$this->addTabContentWrapper('visitingteam-offensive-stats', true);
						$this->setupOffensiveStats($gamestats->visitingteamoffensivestats, $columns, null, null, null, null, null, $gameid);
					$this->endDiv();
					$this->addTabContentWrapper('visitingteam-defensive-stats');
						$this->setupDefensiveStats($gamestats->visitingteamdefensivestats, $columns);
					$this->endDiv();
					$this->addTabContentWrapper('visitingteam-special-stats');
						$this->setupSpecialTeamStats($gamestats->visitingteamspecialteamstats, $columns);
					$this->endDiv();
				$this->endDiv();
			$this->endDiv();

			$homescore = ' ('. $gamestats->game->hometeamscore . ')';
			$hometeamname = (!empty($gamestats->hometeam) && !empty($gamestats->hometeam->teamname)) 
				? $gamestats->hometeam->teamname : $gamestats->game->hometeamwritein;
			echo '<h3>Home Team: ' . $hometeamname . $homescore . '</h3>';
			$this->setupTab();
				echo '<div class="tab-button-wrapper game-stats-button-wrapper">';
					$this->addButton('Offensive Stats', 'hometeam-offensive-stats', 'active');
					$this->addButton('Defensive Stats', 'hometeam-defensive-stats');
					$this->addButton('Special Team  Stats', 'hometeam-special-stats');
				$this->endDiv();
				$this->setupTabBody();
					$this->addTabContentWrapper('hometeam-offensive-stats', true);
						$this->setupOffensiveStats($gamestats->hometeamoffensivestats, $columns, null, null, null, null, null, $gameid);
					$this->endDiv();
					$this->addTabContentWrapper('hometeam-defensive-stats');
						$this->setupDefensiveStats($gamestats->hometeamdefensivestats, $columns);
					$this->endDiv();
					$this->addTabContentWrapper('hometeam-special-stats');
						$this->setupSpecialTeamStats($gamestats->hometeamspecialteamstats, $columns);
					$this->endDiv();
				$this->endDiv();
			$this->endDiv();
		$this->endDiv();
	}

	public function stats($attrs) {
		$seasonid = $attrs['season'];
		$teamid = null;
		$playerid = null;
		$sort = null;
		$sorttype = null;

		if(!empty($attrs['team'])) {
			$teamid = $attrs['team'];
		}
		if(!empty($attrs['player'])) {
			$playerid = $attrs['player'];
			// $teamid must be null when the filter is per player
			$teamid = null;
		}
		if(!empty($attrs['sort'])) {
			$sort = $attrs['sort'];
		}
		if(!empty($attrs['sorttype'])) {
			$sorttype = $attrs['sorttype'];
		}

		if(!empty($attrs['disablesort']) && $attrs['disablesort']) {
			$this->tableoptions = array(
				'disablesort' => true
			);
		}
		$columns = $this->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype);
		switch ($attrs['type']) {
			case 'offensive': {
				$stats = $this->api->getFootballOffensiveStats($seasonid, $teamid, $playerid);
				$this->setupOffensiveStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				break;
			}
			case 'offensive-passing': {
				$stats = $this->api->getFootballOffensiveStats($seasonid, $teamid, $playerid);
				$this->offensivePassingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				break;
			}
			case 'offensive-rushing': {
				$stats = $this->api->getFootballOffensiveStats($seasonid, $teamid, $playerid);
				$this->offensiveRushingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				break;
			}
			case 'offensive-receiving': {
				$stats = $this->api->getFootballOffensiveStats($seasonid, $teamid, $playerid);
				$this->offensiveReceivingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				break;
			}
			case 'offensive-fieldgoal': {
				$stats = $this->api->getFootballOffensiveStats($seasonid, $teamid, $playerid);
				$this->offensiveFieldGoalStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				break;
			}
			case 'defensive': {
				$stats = $this->api->getFootballDefensiveStats($seasonid, $teamid, $playerid);
				$this->setupDefensiveStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				break;
			}
			case 'defensive-defense': {
				$stats = $this->api->getFootballDefensiveStats($seasonid, $teamid, $playerid);
				$this->defensiveDefenseStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				break;
			}
			case 'defensive-intercept': {
				$stats = $this->api->getFootballDefensiveStats($seasonid, $teamid, $playerid);
				$this->defensiveInterceptionStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				break;
			}
			case 'special-team': {
				$stats = $this->api->getFootballSpecialTeamStats($seasonid, $teamid, $playerid);
				$this->setupSpecialTeamStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				break;
			}
			case 'special-team-punting': {
				$stats = $this->api->getFootballSpecialTeamStats($seasonid, $teamid, $playerid);
				$this->specialTeamPuntingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				break;
			}
			case 'special-team-punt-return': {
				$stats = $this->api->getFootballSpecialTeamStats($seasonid, $teamid, $playerid);
				$this->specialTeamPuntReturnStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				break;
			}
			case 'special-team-kick-return': {
				$stats = $this->api->getFootballSpecialTeamStats($seasonid, $teamid, $playerid);
				$this->specialTeamKickReturnStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				break;
			}
		}
		
	}

	public function setupOffensiveStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $gameid = null, $disablesort = false) {
		$this->setupTab();
			$this->setupOffensiveTabButton();
			$this->setupTabBody();
				$this->addTabContentWrapper('passing-stats', true);
					$this->offensivePassingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $gameid, $disablesort);
				$this->endDiv();

				$this->addTabContentWrapper('rushing-stats');
					$this->offensiveRushingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $gameid, $disablesort);
				$this->endDiv();

				$this->addTabContentWrapper('receiving-stats');
					$this->offensiveReceivingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $gameid, $disablesort);
				$this->endDiv();

				$this->addTabContentWrapper('field-goal-stats');
					$this->offensiveFieldGoalStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
				$this->endDiv();
			$this->endDiv();
		$this->endDiv();
	}

	public function setupDefensiveStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $disablesort = false) {
		$this->setupTab();
			$this->setupDefensiveTabButton();
			$this->setupTabBody();
				$this->addTabContentWrapper('defense-stats', true);
					$this->defensiveDefenseStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
				$this->endDiv();

				$this->addTabContentWrapper('intercept-stats');
					$this->defensiveInterceptionStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
				$this->endDiv();
			$this->endDiv();
		$this->endDiv();
	}

	public function setupSpecialTeamStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $disablesort = false) {
		$this->setupTab();
			$this->setupSpecialTeamTabButton();
			$this->setupTabBody();
				$this->addTabContentWrapper('punting-stats', true);
					$this->specialTeamPuntingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
				$this->endDiv();

				$this->addTabContentWrapper('punt-return-stats');
					$this->specialTeamPuntReturnStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
				$this->endDiv();

				$this->addTabContentWrapper('kick-return-stats');
					$this->specialTeamKickReturnStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
			$this->endDiv();
		$this->endDiv();
	}

	private function getSortType($key, $currentSort, $currentSortType) {
		if($key == $currentSort) {
			if(empty($currentSortType) || $currentSortType == 'desc') {
				return 'asc';
			} else if($currentSortType == 'asc') {
				return 'desc';
			}
		} else {
			return $currentSortType;
		}
	}

	public function offensivePassingStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $gameid = null, $disablesort = false, $placeholder = null) {
		$items = array();
		$total = null;
		$sort = $sort == null ? 'weekname' : $sort;
		if(!empty($stats)) {
			foreach ($stats->passing as $val) {
				//$val->name = ucwords(strtolower($val->lastname . ', ' . $val->firstname));
				$val->name = str_replace("Inactive","<span class='warn'>INACTIVE</span>",$val->name);
				$val->pa_rate = $this->formatDecimal($val->pa_rate);
				$val->pa_cmppct = $this->formatDecimal($val->pa_cmppct);
				$val->pa_ydsatt = $this->formatDecimal($val->pa_ydsatt);
				$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);

				if(!empty($playerid)) {
					$val->weekname = 'Week ' + $val->ordinalnumber;
					$val->matchup =  '';
					if (!empty($val->visitingteam)) {
						$val->matchup .= $val->visitingteam;
					} else if (!empty($val->visitingteamwritein)) {
						$val->matchup .= $val->visitingteamwritein;
					}
					$val->matchup .= ' @ ';
					if (!empty($val->hometeam)) {
						$val->matchup .= $val->hometeam;
					} else if (!empty($val->hometeamwritein)) {
						$val->matchup .= $val->hometeamwritein;
					}
				}
				array_push($items, json_decode(json_encode($val), true));
			}
			
			if(!empty($stats->passingtotal)) {
				$total = array(
					'matchup' => 'Total',
					'name' => 'Total',
					'hasplayed' => $stats->passingtotal->hasplayed,
					'pa_rate' => $this->formatDecimal($stats->passingtotal->pa_rate),
					'pa_cmppct' => $this->formatDecimal($stats->passingtotal->pa_cmppct),
					'pa_ydsatt' => $this->formatDecimal($stats->passingtotal->pa_ydsatt),
					'pa_atts' => $stats->passingtotal->pa_atts,
					'pa_comp' => $stats->passingtotal->pa_comp,
					'pa_yds' => $stats->passingtotal->pa_yds,
					'pa_td' => $stats->passingtotal->pa_td,
					'pa_int' => $stats->passingtotal->pa_int,
					'pa_long' => $stats->passingtotal->pa_long,
					'pa_passypg' => $stats->passingtotal->pa_passypg
				);
			}
		}

		if(!empty($disablesort) && $disablesort) {
			$this->tableoptions['disablesort'] = true;
		}

		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pa_comp', $this->getSortType('pa_comp', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'passing-stats'), 
			'label' => 'COMP',
			'key' => 'pa_comp'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pa_atts', $this->getSortType('pa_atts', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'passing-stats'), 
			'label' => 'ATTS',
			'key' => 'pa_atts'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pa_cmppct', $this->getSortType('pa_cmppct', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'passing-stats'),
			'label' => 'CMPPCT',
			'key' => 'pa_cmppct'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pa_yds', $this->getSortType('pa_yds', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'passing-stats'),
			'label' => 'YDS',
			'key' => 'pa_yds'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pa_ydsatt', $this->getSortType('pa_ydsatt', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'passing-stats'),
			'label' => 'YDS/ATT',
			'key' => 'pa_ydsatt'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pa_td', $this->getSortType('pa_td', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'passing-stats'),
			'label' => 'TD',
			'key' => 'pa_td'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pa_int', $this->getSortType('pa_int', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'passing-stats'),
			'label' => 'INT',
			'key' => 'pa_int'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pa_long', $this->getSortType('pa_long', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'passing-stats'),
			'label' => 'LONG',
			'key' => 'pa_long'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pa_rate', $this->getSortType('pa_rate', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'passing-stats'),
			'label' => 'RATE',
			'key' => 'pa_rate'
		));
		if(empty($playerid) && empty($gameid)) {
			array_push($columns, array(
				'headerlink' => $this->constructSortUrl('pa_passypg', $this->getSortType('pa_passypg', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'passing-stats'),
				'label' => 'PASS YPG',
				'key' => 'pa_passypg'
			));
		}
		$columns = $this->setupSortColumn($columns, $sort, $sorttype);
		if (!empty($placeholder)) {
			$this->tableoptions['noItemLabel'] = $placeholder;
		}
		$defaultsort = $sort != 'pa_cmppct' ? 'pa_cmppct' : null;
		echo '<h3 class="stats-title">Passing Stats</h3>';
		echo '<div style="overflow:auto;">';
			new CollClubSports_Table($columns,  $items, $total, $defaultsort, 'desc', $sort, $sorttype, $this->tableoptions);
		echo $this->endDiv();
		?>
			
		<?php
	}

	public function offensiveRushingStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $gameid = null, $disablesort = false, $placeholder = null) {
		$items = array();
		$total = null;
		$sort = $sort == null ? 'weekname' : $sort;
		if(!empty($stats)) {
			foreach ($stats->rushing as $val) {
				//$val->name = ucwords(strtolower($val->lastname . ', ' . $val->firstname));
				$val->name = str_replace("Inactive","<span class='warn'>INACTIVE</span>",$val->name);
				$val->ru_avg = $this->formatDecimal($val->ru_avg);
				$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);

				if(!empty($playerid)) {
					$val->weekname = 'Week ' + $val->ordinalnumber;
					$val->matchup =  '';
					if (!empty($val->visitingteam)) {
						$val->matchup .= $val->visitingteam;
					} else if (!empty($val->visitingteamwritein)) {
						$val->matchup .= $val->visitingteamwritein;
					}
					$val->matchup .= ' @ ';
					if (!empty($val->hometeam)) {
						$val->matchup .= $val->hometeam;
					} else if (!empty($val->hometeamwritein)) {
						$val->matchup .= $val->hometeamwritein;
					}
				}
				array_push($items, json_decode(json_encode($val), true));
			}
			if(!empty($stats->rushingtotal)) {
				$total = array(
					'matchup' => 'Total',
					'name' => 'Total',
					'hasplayed' => $stats->rushingtotal->hasplayed,
					'ru_avg' => $this->formatDecimal($stats->rushingtotal->ru_avg),
					'ru_atts' => $stats->rushingtotal->ru_atts,
					'ru_long' => $stats->rushingtotal->ru_long,
					'ru_tds' => $stats->rushingtotal->ru_tds,
					'ru_yds' => $stats->rushingtotal->ru_yds,
					'ru_rushypg' => $stats->rushingtotal->ru_rushypg
				);
			}
		}

		if(!empty($disablesort) && $disablesort) {
			$this->tableoptions['disablesort'] = true;
		}

		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ru_atts', $this->getSortType('ru_atts', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'rushing-stats'), 
			'label' => 'ATTS',
			'key' => 'ru_atts'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ru_yds', $this->getSortType('ru_yds', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'rushing-stats'), 
			'label' => 'YDS',
			'key' => 'ru_yds'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ru_avg', $this->getSortType('ru_avg', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'rushing-stats'),
			'label' => 'AVG',
			'key' => 'ru_avg'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ru_long', $this->getSortType('ru_long', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'rushing-stats'),
			'label' => 'LONG',
			'key' => 'ru_long'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ru_tds', $this->getSortType('ru_tds', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'rushing-stats'),
			'label' => 'TDS',
			'key' => 'ru_tds'
		));
		if(empty($playerid) && empty($gameid)) {
			array_push($columns, array(
				'headerlink' => $this->constructSortUrl('ru_rushypg', $this->getSortType('ru_rushypg', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'rushing-stats'),
				'label' => 'RUSH YPG',
				'key' => 'ru_rushypg'
			));
		}
		$columns = $this->setupSortColumn($columns, $sort, $sorttype);
		if (!empty($placeholder)) {
			$this->tableoptions['noItemLabel'] = $placeholder;
		}
		$defaultsort = $sort != 'ru_yds' ? 'ru_yds' : null;
		echo '<h3 class="stats-title">Rushing Stats</h3>';
		echo '<div style="overflow:auto;">';
			new CollClubSports_Table($columns,  $items, $total, $defaultsort, 'desc', $sort, $sorttype, $this->tableoptions);
		echo $this->endDiv();
		?>
		<?php
	}

	public function offensiveReceivingStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $gameid = null, $disablesort = false, $placeholder = null) {
		$items = array();
		$total = null;
		$sort = $sort == null ? 'weekname' : $sort;
		if(!empty($stats)) {
			foreach ($stats->receiving as $val) {
				//$val->name = ucwords(strtolower($val->lastname . ', ' . $val->firstname));
				$val->name = str_replace("Inactive","<span class='warn'>INACTIVE</span>",$val->name);
				$val->re_avg = $this->formatDecimal($val->re_avg);
				$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);

				if(!empty($playerid)) {
					$val->weekname = 'Week ' + $val->ordinalnumber;
					$val->matchup =  '';
					if (!empty($val->visitingteam)) {
						$val->matchup .= $val->visitingteam;
					} else if (!empty($val->visitingteamwritein)) {
						$val->matchup .= $val->visitingteamwritein;
					}
					$val->matchup .= ' @ ';
					if (!empty($val->hometeam)) {
						$val->matchup .= $val->hometeam;
					} else if (!empty($val->hometeamwritein)) {
						$val->matchup .= $val->hometeamwritein;
					}
				}
				array_push($items, json_decode(json_encode($val), true));
			}

			if(!empty($stats->receivingtotal)) {
				$total = array(
					'matchup' => 'Total',
					'name' => 'Total',
					'hasplayed' => $stats->receivingtotal->hasplayed,
					're_avg' => $this->formatDecimal($stats->receivingtotal->re_avg),
					're_recs' => $stats->receivingtotal->re_recs,
					're_yds' => $stats->receivingtotal->re_yds,
					're_long' => $stats->receivingtotal->re_long,
					're_tds' => $stats->receivingtotal->re_tds,
					're_recypg' => $stats->receivingtotal->re_recypg
				);
			}
		}

		if(!empty($disablesort) && $disablesort) {
			$this->tableoptions['disablesort'] = true;
		}

		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('re_recs', $this->getSortType('re_recs', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'receiving-stats'), 
			'label' => 'RECS',
			'key' => 're_recs'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('re_yds', $this->getSortType('re_yds', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'receiving-stats'), 
			'label' => 'YDS',
			'key' => 're_yds'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('re_avg', $this->getSortType('re_avg', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'receiving-stats'),
			'label' => 'AVG',
			'key' => 're_avg'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('re_long', $this->getSortType('re_long', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'receiving-stats'),
			'label' => 'LONG',
			'key' => 're_long'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('re_yds', $this->getSortType('re_yds', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'receiving-stats'),
			'label' => 'TDS',
			'key' => 're_tds'
		));
		if(empty($playerid) && empty($gameid)) {
			array_push($columns, array(
				'headerlink' => $this->constructSortUrl('re_recypg', $this->getSortType('re_recypg', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'receiving-stats'),
				'label' => 'REC YPG',
				'key' => 're_recypg'
			));
		}
		$columns = $this->setupSortColumn($columns, $sort, $sorttype);
		if (!empty($placeholder)) {
			$this->tableoptions['noItemLabel'] = $placeholder;
		}
		$defaultsort = $sort != 're_yds' ? 're_yds' : null;
		echo '<h3 class="stats-title">Receiving Stats</h3>';
		echo '<div style="overflow:auto;">';
			new CollClubSports_Table($columns,  $items, $total, $defaultsort, 'desc', $sort, $sorttype, $this->tableoptions);
		echo $this->endDiv();
		?>
		<?php
	}

	public function offensiveFieldGoalStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $disablesort = false, $placeholder = null) {
		$items = array();
		$total = null;
		$sort = $sort == null ? 'weekname' : $sort;
		if(!empty($stats)) {
			foreach ($stats->fieldgoal as $val) {
				//$val->name = ucwords(strtolower($val->lastname . ', ' . $val->firstname));
				$val->name = str_replace("Inactive","<span class='warn'>INACTIVE</span>",$val->name);
				$val->fg_pct = $this->formatDecimal($val->fg_pct);
				$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);

				if(!empty($playerid)) {
					$val->weekname = 'Week ' + $val->ordinalnumber;
					$val->matchup =  '';
					if (!empty($val->visitingteam)) {
						$val->matchup .= $val->visitingteam;
					} else if (!empty($val->visitingteamwritein)) {
						$val->matchup .= $val->visitingteamwritein;
					}
					$val->matchup .= ' @ ';
					if (!empty($val->hometeam)) {
						$val->matchup .= $val->hometeam;
					} else if (!empty($val->hometeamwritein)) {
						$val->matchup .= $val->hometeamwritein;
					}
				}
				array_push($items, json_decode(json_encode($val), true));
			}

			if(!empty($stats->fieldgoaltotal)) {
				$total = array(
					'matchup' => 'Total',
					'name' => 'Total',
					'hasplayed' => $stats->fieldgoaltotal->hasplayed,
					'fg_pct' => $this->formatDecimal($stats->fieldgoaltotal->fg_pct),
					'fg_atts' => $stats->fieldgoaltotal->fg_atts,
					'fg_made' => $stats->fieldgoaltotal->fg_made,
					'fg_long' => $stats->fieldgoaltotal->fg_long,
					'fg_extra_pts_made' => $stats->fieldgoaltotal->fg_extra_pts_made,
					'fg_xpa' => $stats->fieldgoaltotal->fg_xpa,
					'fg_xp_perc' => $stats->fieldgoaltotal->fg_xp_perc
				);
			}
		}

		if(!empty($disablesort) && $disablesort) {
			$this->tableoptions['disablesort'] = true;
		}

		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('fg_made', $this->getSortType('fg_made', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'field-goal-stats'), 
			'label' => 'MADE',
			'key' => 'fg_made'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('fg_atts', $this->getSortType('fg_atts', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'field-goal-stats'), 
			'label' => 'ATTS',
			'key' => 'fg_atts'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('fg_pct', $this->getSortType('fg_pct', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'field-goal-stats'),
			'label' => 'FG %',
			'key' => 'fg_pct'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('fg_long', $this->getSortType('fg_long', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'field-goal-stats'),
			'label' => 'LONG',
			'key' => 'fg_long'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('fg_extra_pts_made', $this->getSortType('extra_pts_made', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'field-goal-stats'),
			'label' => 'XP Made',
			'key' => 'fg_extra_pts_made'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('fg_extra_pts_made', $this->getSortType('extra_pts_made', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'field-goal-stats'),
			'label' => 'XP Attempted',
			'key' => 'fg_xpa'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('fg_extra_pts_made', $this->getSortType('extra_pts_made', $sort, $sorttype), $seasonid, $teamid, $playerid, null, 'field-goal-stats'),
			'label' => 'XP %',
			'key' => 'fg_xp_perc'
		));
		$columns = $this->setupSortColumn($columns, $sort, $sorttype);
		if (!empty($placeholder)) {
			$this->tableoptions['noItemLabel'] = $placeholder;
		}
		$defaultsort = $sort != 'fg_pct' ? 'fg_pct' : null;
		echo '<h3 class="stats-title">Kicking Stats</h3>';
		echo '<div style="overflow:auto;">';
			new CollClubSports_Table($columns,  $items, $total, $defaultsort, 'desc', $sort, $sorttype, $this->tableoptions);
		echo $this->endDiv();
		?>
		<?php
	}

	public function defensiveDefenseStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $disablesort = false, $placeholder = null) {
		$items = array();
		$total = null;
		$sort = $sort == null ? 'weekname' : $sort;
		if(!empty($stats)) {
			foreach ($stats->defense as $val) {
				//$val->name = ucwords(strtolower($val->lastname . ', ' . $val->firstname));
				$val->name = str_replace("Inactive","<span class='warn'>INACTIVE</span>",$val->name);
				$val->def_sack = $this->formatDecimal($val->def_sack);
				$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);

				if(!empty($playerid)) {
					$val->weekname = 'Week ' + $val->ordinalnumber;
					$val->matchup =  '';
					if (!empty($val->visitingteam)) {
						$val->matchup .= $val->visitingteam;
					} else if (!empty($val->visitingteamwritein)) {
						$val->matchup .= $val->visitingteamwritein;
					}
					$val->matchup .= ' @ ';
					if (!empty($val->hometeam)) {
						$val->matchup .= $val->hometeam;
					} else if (!empty($val->hometeamwritein)) {
						$val->matchup .= $val->hometeamwritein;
					}
				}
				array_push($items, json_decode(json_encode($val), true));
			}

			if(!empty($stats->defensetotal)) {
				$total = array(
					'matchup' => 'Total',
					'name' => 'Total',
					'hasplayed' => $stats->defensetotal->hasplayed,
					'def_sack' => $this->formatDecimal($stats->defensetotal->def_sack),
					'def_asst' => $stats->defensetotal->def_asst,
					'def_solo' => $stats->defensetotal->def_solo,
					'def_ffum' => $stats->defensetotal->def_ffum,
					'def_fumr' => $stats->defensetotal->def_fumr,
					'def_fumtd' => $stats->defensetotal->def_fumtd
				);
			}
		}

		if(!empty($disablesort) && $disablesort) {
			$this->tableoptions['disablesort'] = true;
		}

		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('def_solo', $this->getSortType('def_solo', $sort, $sorttype), $seasonid, $teamid, $playerid, 'defensive', 'defense-stats'), 
			'label' => 'SOLO',
			'key' => 'def_solo'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('def_asst', $this->getSortType('def_asst', $sort, $sorttype), $seasonid, $teamid, $playerid, 'defensive', 'defense-stats'), 
			'label' => 'ASST',
			'key' => 'def_asst'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('def_sack', $this->getSortType('def_sack', $sort, $sorttype), $seasonid, $teamid, $playerid, 'defensive', 'defense-stats'),
			'label' => 'SACK',
			'key' => 'def_sack'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('def_ffum', $this->getSortType('def_ffum', $sort, $sorttype), $seasonid, $teamid, $playerid, 'defensive', 'defense-stats'),
			'label' => 'F-FUM',
			'key' => 'def_ffum'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('def_fumr', $this->getSortType('def_fumr', $sort, $sorttype), $seasonid, $teamid, $playerid, 'defensive', 'defense-stats'),
			'label' => 'FUM-R',
			'key' => 'def_fumr'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('def_fumtd', $this->getSortType('def_fumtd', $sort, $sorttype), $seasonid, $teamid, $playerid, 'defensive', 'defense-stats'),
			'label' => 'FUM-TD',
			'key' => 'def_fumtd'
		));
		$columns = $this->setupSortColumn($columns, $sort, $sorttype);
		if (!empty($placeholder)) {
			$this->tableoptions['noItemLabel'] = $placeholder;
		}
		$defaultsort = $sort != 'def_sack' ? 'def_sack' : null;
		echo '<h3 class="stats-title">Defensive Stats</h3>';
		echo '<div style="overflow:auto;">';
			new CollClubSports_Table($columns,  $items, $total, $defaultsort, 'desc', $sort, $sorttype, $this->tableoptions);
		echo $this->endDiv();
		?>
		<?php
	}

	public function defensiveInterceptionStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $disablesort = false, $placeholder = null) {
		$items = array();
		$total = null;
		$sort = $sort == null ? 'weekname' : $sort;
		if(!empty($stats)) {
			foreach ($stats->intercept as $val) {
				//$val->name = ucwords(strtolower($val->lastname . ', ' . $val->firstname));
				$val->name = str_replace("Inactive","<span class='warn'>INACTIVE</span>",$val->name);
				$val->def_sack = $this->formatDecimal($val->def_sack);
				$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);

				if(!empty($playerid)) {
					$val->weekname = 'Week ' + $val->ordinalnumber;
					$val->matchup =  '';
					if (!empty($val->visitingteam)) {
						$val->matchup .= $val->visitingteam;
					} else if (!empty($val->visitingteamwritein)) {
						$val->matchup .= $val->visitingteamwritein;
					}
					$val->matchup .= ' @ ';
					if (!empty($val->hometeam)) {
						$val->matchup .= $val->hometeam;
					} else if (!empty($val->hometeamwritein)) {
						$val->matchup .= $val->hometeamwritein;
					}
				}
				array_push($items, json_decode(json_encode($val), true));
			}

			if(!empty($stats->intercepttotal)) {
				$total = array(
					'matchup' => 'Total',
					'name' => 'Total',
					'hasplayed' => $stats->intercepttotal->hasplayed,
					'int_int' => $stats->intercepttotal->int_int,
					'int_yds' => $stats->intercepttotal->int_yds,
					'int_long' => $stats->intercepttotal->int_long,
					'int_td' => $stats->intercepttotal->int_td,
					'int_pdef' => $stats->intercepttotal->int_pdef
				);
			}
		}

		if(!empty($disablesort) && $disablesort) {
			$this->tableoptions['disablesort'] = true;
		}

		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('int_pdef', $this->getSortType('int_pdef', $sort, $sorttype), $seasonid, $teamid, $playerid, 'defensive', 'intercept-stats'),
			'label' => 'PDEF',
			'key' => 'int_pdef'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('int_int', $this->getSortType('int_int', $sort, $sorttype), $seasonid, $teamid, $playerid, 'defensive', 'intercept-stats'), 
			'label' => 'INT',
			'key' => 'int_int'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('int_yds', $this->getSortType('int_yds', $sort, $sorttype), $seasonid, $teamid, $playerid, 'defensive', 'intercept-stats'), 
			'label' => 'YDS',
			'key' => 'int_yds'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('int_long', $this->getSortType('int_long', $sort, $sorttype), $seasonid, $teamid, $playerid, 'defensive', 'intercept-stats'),
			'label' => 'LONG',
			'key' => 'int_long'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('int_td', $this->getSortType('int_td', $sort, $sorttype), $seasonid, $teamid, $playerid, 'defensive', 'intercept-stats'),
			'label' => 'TD',
			'key' => 'int_td'
		));
		$columns = $this->setupSortColumn($columns, $sort, $sorttype);
		if (!empty($placeholder)) {
			$this->tableoptions['noItemLabel'] = $placeholder;
		}
		$defaultsort = $sort != 'int_int' ? 'int_int' : null;
		echo '<h3 class="stats-title">Interception Stats</h3>';
		echo '<div style="overflow:auto;">';
			new CollClubSports_Table($columns,  $items, $total, $defaultsort, 'desc', $sort, $sorttype, $this->tableoptions);
		echo $this->endDiv();
		?>
		<?php
	}

	public function specialTeamPuntingStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $disablesort = false, $placeholder = null) {
		$items = array();
		$total = null;
		$sort = $sort == null ? 'weekname' : $sort;
		if(!empty($stats)) {
			foreach ($stats->punting as $val) {
				//$val->name = ucwords(strtolower($val->lastname . ', ' . $val->firstname));
				$val->name = str_replace("Inactive","<span class='warn'>INACTIVE</span>",$val->name);
				$val->punt_avg = $this->formatDecimal($val->punt_avg);
				$val->punt_i20pct = $this->formatDecimal($val->punt_i20pct);
				$val->punt_tbpct = $this->formatDecimal($val->punt_tbpct);
				$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);

				if(!empty($playerid)) {
					$val->weekname = 'Week ' + $val->ordinalnumber;
					$val->matchup =  '';
					if (!empty($val->visitingteam)) {
						$val->matchup .= $val->visitingteam;
					} else if (!empty($val->visitingteamwritein)) {
						$val->matchup .= $val->visitingteamwritein;
					}
					$val->matchup .= ' @ ';
					if (!empty($val->hometeam)) {
						$val->matchup .= $val->hometeam;
					} else if (!empty($val->hometeamwritein)) {
						$val->matchup .= $val->hometeamwritein;
					}
				}
				array_push($items, json_decode(json_encode($val), true));
			}

			if(!empty($stats->puntingtotal)) {
				$total = array(
					'matchup' => 'Total',
					'name' => 'Total',
					'hasplayed' => $stats->puntingtotal->hasplayed,
					'punt_punts' => $stats->puntingtotal->punt_punts,
					'punt_yds' => $stats->puntingtotal->punt_yds,
					'punt_long' => $stats->puntingtotal->punt_long,
					'punt_avg' => $this->formatDecimal($stats->puntingtotal->punt_avg),
					'punt_in20' => $stats->puntingtotal->punt_in20,
					'punt_i20pct' => $this->formatDecimal($stats->puntingtotal->punt_i20pct)
				);
			}
		}

		if(!empty($disablesort) && $disablesort) {
			$this->tableoptions['disablesort'] = true;
		}

		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('punt_punts', $this->getSortType('punt_punts', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'punting-stats'), 
			'label' => 'PUNTS',
			'key' => 'punt_punts'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('punt_yds', $this->getSortType('punt_yds', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'punting-stats'), 
			'label' => 'Total YDS',
			'key' => 'punt_yds'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('punt_long', $this->getSortType('punt_long', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'punting-stats'),
			'label' => 'LONG',
			'key' => 'punt_long'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('punt_avg', $this->getSortType('punt_avg', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'punting-stats'),
			'label' => 'AVG',
			'key' => 'punt_avg'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('punt_in20', $this->getSortType('punt_in20', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'punting-stats'),
			'label' => 'IN20',
			'key' => 'punt_in20'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('punt_i20pct', $this->getSortType('punt_i20pct', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'punting-stats'),
			'label' => 'I20%',
			'key' => 'punt_i20pct'
		));
		$columns = $this->setupSortColumn($columns, $sort, $sorttype);
		if (!empty($placeholder)) {
			$this->tableoptions['noItemLabel'] = $placeholder;
		}
		$defaultsort = $sort != 'punt_avg' ? 'punt_avg' : null;
		echo '<h3 class="stats-title">Punting Stats</h3>';
		echo '<div style="overflow:auto;">';
			new CollClubSports_Table($columns,  $items, $total, $defaultsort, 'desc', $sort, $sorttype, $this->tableoptions);
		echo $this->endDiv();
		?>
		<?php
	}

	public function specialTeamPuntReturnStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $disablesort = false, $placeholder = null) {
		$items = array();
		$total = null;
		$sort = $sort == null ? 'weekname' : $sort;
		if(!empty($stats)) {
			foreach ($stats->puntreturn as $val) {
				//$val->name = ucwords(strtolower($val->lastname . ', ' . $val->firstname));
				$val->name = str_replace("Inactive","<span class='warn'>INACTIVE</span>",$val->name);
				$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);
				$val->pure_avg = $this->formatDecimal($val->pure_avg);

				if(!empty($playerid)) {
					$val->weekname = 'Week ' + $val->ordinalnumber;
					$val->matchup =  '';
					if (!empty($val->visitingteam)) {
						$val->matchup .= $val->visitingteam;
					} else if (!empty($val->visitingteamwritein)) {
						$val->matchup .= $val->visitingteamwritein;
					}
					$val->matchup .= ' @ ';
					if (!empty($val->hometeam)) {
						$val->matchup .= $val->hometeam;
					} else if (!empty($val->hometeamwritein)) {
						$val->matchup .= $val->hometeamwritein;
					}
				}
				array_push($items, json_decode(json_encode($val), true));
			}

			if(!empty($stats->puntreturntotal)) {
				$total = array(
					'matchup' => 'Total',
					'name' => 'Total',
					'hasplayed' => $stats->puntreturntotal->hasplayed,
					'pure_rtrns' => $stats->puntreturntotal->pure_rtrns,
					'pure_yds' => $stats->puntreturntotal->pure_yds,
					'pure_avg' => $this->formatDecimal($stats->puntreturntotal->pure_avg),
					'pure_long' => $stats->puntreturntotal->pure_long,
					'pure_td' => $stats->puntreturntotal->pure_td
				);
			}
		}

		if(!empty($disablesort) && $disablesort) {
			$this->tableoptions['disablesort'] = true;
		}

		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pure_rtrns', $this->getSortType('pure_rtrns', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'punt-return-stats'), 
			'label' => 'Returns',
			'key' => 'pure_rtrns'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pure_yds', $this->getSortType('pure_yds', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'punt-return-stats'), 
			'label' => 'YDS',
			'key' => 'pure_yds'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pure_avg', $this->getSortType('pure_avg', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'punt-return-stats'),
			'label' => 'AVG',
			'key' => 'pure_avg'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pure_long', $this->getSortType('pure_long', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'punt-return-stats'),
			'label' => 'LONG',
			'key' => 'pure_long'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pure_td', $this->getSortType('pure_td', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'punt-return-stats'),
			'label' => 'TD',
			'key' => 'pure_td'
		));
		$columns = $this->setupSortColumn($columns, $sort, $sorttype);
		if (!empty($placeholder)) {
			$this->tableoptions['noItemLabel'] = $placeholder;
		}
		$defaultsort = $sort != 'pure_avg' ? 'pure_avg' : null;
		echo '<h3 class="stats-title">Punt Return Stats</h3>';
		echo '<div style="overflow:auto;">';
			new CollClubSports_Table($columns,  $items, $total, $defaultsort, 'desc', $sort, $sorttype, $this->tableoptions);
		echo $this->endDiv();
		?>
		<?php
	}

	public function specialTeamKickReturnStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $disablesort = false, $placeholder = null) {
		$items = array();
		$total = null;
		$sort = $sort == null ? 'weekname' : $sort;
		if(!empty($stats)) {
			foreach ($stats->kickreturn as $val) {
				//$val->name = ucwords(strtolower($val->lastname . ', ' . $val->firstname));
				$val->name = str_replace("Inactive","<span class='warn'>INACTIVE</span>",$val->name);
				$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);
				$val->kire_avg = $this->formatDecimal($val->kire_avg);

				if(!empty($playerid)) {
					$val->weekname = 'Week ' + $val->ordinalnumber;
					$val->matchup =  '';
					if (!empty($val->visitingteam)) {
						$val->matchup .= $val->visitingteam;
					} else if (!empty($val->visitingteamwritein)) {
						$val->matchup .= $val->visitingteamwritein;
					}
					$val->matchup .= ' @ ';
					if (!empty($val->hometeam)) {
						$val->matchup .= $val->hometeam;
					} else if (!empty($val->hometeamwritein)) {
						$val->matchup .= $val->hometeamwritein;
					}
				}
				array_push($items, json_decode(json_encode($val), true));
			}

			if(!empty($stats->kickreturntotal)) {
				$total = array(
					'matchup' => 'Total',
					'name' => 'Total',
					'hasplayed' => $stats->kickreturntotal->hasplayed,
					'kire_rtrns' => $stats->kickreturntotal->kire_rtrns,
					'kire_yds' => $stats->kickreturntotal->kire_yds,
					'kire_avg' => $this->formatDecimal($stats->kickreturntotal->kire_avg),
					'kire_long' => $stats->kickreturntotal->kire_long,
					'kire_td' => $stats->kickreturntotal->kire_td
				);
			}
		}

		if(!empty($disablesort) && $disablesort) {
			$this->tableoptions['disablesort'] = true;
		}

		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('kire_rtrns', $this->getSortType('kire_rtrns', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'kick-return-stats'), 
			'label' => 'Returns',
			'key' => 'kire_rtrns'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('kire_yds', $this->getSortType('kire_yds', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'kick-return-stats'), 
			'label' => 'YDS',
			'key' => 'kire_yds'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('kire_avg', $this->getSortType('kire_avg', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'kick-return-stats'),
			'label' => 'AVG',
			'key' => 'kire_avg'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('kire_long', $this->getSortType('kire_long', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'kick-return-stats'),
			'label' => 'LONG',
			'key' => 'kire_long'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('kire_td', $this->getSortType('kire_td', $sort, $sorttype), $seasonid, $teamid, $playerid, 'special-team', 'kick-return-stats'),
			'label' => 'TD',
			'key' => 'kire_td'
		));
		$columns = $this->setupSortColumn($columns, $sort, $sorttype);
		if (!empty($placeholder)) {
			$this->tableoptions['noItemLabel'] = $placeholder;
		}
		$defaultsort = $sort != 'kire_avg' ? 'kire_avg' : null;
		echo '<h3 class="stats-title">Kick Return Stats</h3>';
		echo '<div style="overflow:auto;">';
			new CollClubSports_Table($columns,  $items, $total, $defaultsort, 'desc', $sort, $sorttype, $this->tableoptions);
		echo $this->endDiv();
		?>
		<?php
	}

	private function formatDecimal($val) {
		$val = round($val, 2);
		return  number_format($val, 2, '.', '');
	}

	public function getStandardColumns( $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $gameid = null, $type = null, $subtype = null) {
		$playerColumn = array(
				'headerlink' => $this->constructSortUrl('name', $this->getSortType('name', $sort, $sorttype), $seasonid, $teamid, $playerid, $type, $subtype),
				'label' => 'Player',
				'key' => 'name',
				'keylink' => 'playerlink',
				'thclass' => 'name-header'
			);
		$teamColumn = array(
				'headerlink' => $this->constructSortUrl('teamname', $this->getSortType('teamname', $sort, $sorttype), $seasonid, $teamid, $playerid, $type, $subtype), 
				'label' => 'Team',
				'key' => 'teamname',
				'thclass' => 'name-header'
			);
		$gamesPlayedColumn = array(
				'headerlink' => $this->constructSortUrl('hasplayed', $this->getSortType('hasplayed', $sort, $sorttype), $seasonid, $teamid, $playerid, $type, $subtype), 
				'label' => 'Games Played',
				'key' => 'hasplayed'
			);
		$columns = array();

		if(!empty($gameid)) {
			array_push($columns, $playerColumn);
		}
		else if(empty($teamid) && empty($playerid)) {
			array_push($columns, $playerColumn);
			array_push($columns, $teamColumn);
			array_push($columns, $gamesPlayedColumn);
		}
		else if(!empty($teamid)) {
			array_push($columns, $playerColumn);
			array_push($columns, $gamesPlayedColumn);
		}
		else if(!empty($playerid)) {
			$columns = array(
				array(
					'label' => 'Week',
					'key' => 'weekname'
				),
				array(
					'label' => 'Match-up',
					'key' => 'matchup',
					'thclass' => 'matchup-header'
				)
			);
		}
		return $columns;
	}

	public function setupSortColumn($columns = array(), $sort = null, $sorttype = null) {
		if(!empty($sort)) {
			for($i = 0; $i < sizeof($columns);$i++){
				if($columns[$i]['key'] == $sort) {
					$columns[$i]['sort'] = true;
					if(!empty($sorttype)) {
						$columns[$i]['sorttype'] = $sorttype;
					} else {
						$columns[$i]['sorttype'] = 'asc';
					}
					break;
				}
			}
		}
		return $columns;
	}

	public function setupOffensiveTabButton() {
		echo '<div class="tab-button-wrapper">';
			$this->addButton('Passing Stats', 'passing-stats', 'active');
			$this->addButton('Rushing Stats', 'rushing-stats');
			$this->addButton('Receiving Stats', 'receiving-stats');
			$this->addButton('Kicking Stats', 'field-goal-stats');
		$this->endDiv();
	}

	public function setupDefensiveTabButton() {
		echo '<div class="tab-button-wrapper">';
			$this->addButton('Defense Stats', 'defense-stats', 'active');
			$this->addButton('Interception Stats', 'intercept-stats');
		$this->endDiv();
	}

	public function setupSpecialTeamTabButton() {
		echo '<div class="tab-button-wrapper">';
			$this->addButton('Punting Stats', 'punting-stats', 'active');
			$this->addButton('Punt Return Stats', 'punt-return-stats');
			$this->addButton('Kick Return Stats', 'kick-return-stats');
		$this->endDiv();
	}

	public function setupTab() {
		echo '<div class="collclubsports-component tab-wrapper"> ';
	}

	public function setupTabBody() {
		echo '<div class="tab-body-wrapper">';
	}

	public function addTabContentWrapper($name = null, $isactive = false) {
		$tpl = '<div class="tab-content-wrapper {0}" name="{1}">';

		if($isactive) {
			$tpl = str_replace('{0}', 'active', $tpl);
		} else {
			$tpl = str_replace('{0}', '', $tpl);
		}
		$tpl = str_replace('{1}', $name, $tpl);
		echo $tpl;
	}

	public function endDiv() {
		echo "</div>";
	}
	public function addButton($label = null, $target = null, $class = null) {
		$tpl = '<div class="button accent folded-corner {0}" target="{1}">{2}</div>';
		$tpl = str_replace('{0}', $class, $tpl);
		$tpl = str_replace('{1}', $target, $tpl);
		$tpl = str_replace('{2}', $label, $tpl);
		echo $tpl;
	}
}
