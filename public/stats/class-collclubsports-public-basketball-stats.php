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
class CollClubSports_Public_Basketball_Stats {

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
		add_shortcode('basketball-stats', array($this, 'stats'));
		add_shortcode('basketball-game-stats', array($this, 'gamestats'));
	}

	private function constructPlayerUrl($playerid, $seasonid = null) {
		$url = $this->options['player_url'] . '?player=' . $playerid;

		if(!empty($seasonid)) {
			$url = $url . '&season=' . $seasonid;
		}
		return $url;
	}

	private function constructTeamUrl($teamid, $seasonid = null) {
		$url = $this->options['team_url'] . '?team=' . $teamid;

		if(!empty($seasonid)) {
			$url = $url . '&season=' . $seasonid;
		}
		return $url;
	}

	private function constructSortUrl($key, $sorttype = null, $seasonid = null, $teamid = null, $playerid = null, $type = null) {
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
		return $url;
	}

	public function gamestats($attrs) {
		$gameid = $attrs['game'];
		$gamestats = $this->api->getBasketballStatsPerGame($gameid);
		$this->tableoptions = array(
			'disablesort' => true
		);
		// echo json_encode($gamestats);
		$columns = $this->getStandardColumns(null, null,  null, null, null, $gameid);
		echo '<div class="stats-page collclubsports-page-wrapper game-stats-wrapper">';
			$visitingteamscore = ' ('. $gamestats->game->visitingteamscore . ')';
			echo '<h3>Visiting Team: ' . $gamestats->visitingteam->teamname . $visitingteamscore . '</h3>';
			$this->basketballStats($gamestats->visitingteamstats, $columns);

			$homescore = ' ('. $gamestats->game->hometeamscore . ')';
			echo '<h3>Home Team: ' . $gamestats->hometeam->teamname . $homescore . '</h3>';
			$this->basketballStats($gamestats->hometeamstats, $columns);
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
		$stats = $this->api->getBasketballStats($seasonid, $teamid, $playerid);
		$this->basketballStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
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

	public function basketballStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $disablesort = false) {
		$items = array();
		$total = null;
		$sort = $sort == null ? 'weekname' : $sort;
		if(!empty($stats)) {
			$statsItems = $stats;
			if(is_array($stats->stats)) {
				$statsItems = $stats->stats;
			}
			foreach ($statsItems as $val) {
				// $val->name = ucwords(strtolower($val->lastname . ', ' . $val->firstname));
				$val->name = str_replace("Inactive","<span class='warn'>INACTIVE</span>",$val->name);
				$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);
				$val->teamlink = $this->constructTeamUrl($val->teamid, $seasonid);

				$val->ftpercentage = $this->formatDecimal($val->ftpercentage);
				$val->mpg = $this->formatDecimal($val->mpg);
				$val->rpg = $this->formatDecimal($val->rpg);
				$val->apg = $this->formatDecimal($val->apg);
				$val->spg = $this->formatDecimal($val->spg);
				$val->ppg = $this->formatDecimal($val->ppg);

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
			
			if(!empty($stats->statstotal)) {
				$total = array(
					'matchup' => 'Total',
					'name' => 'Total',
					'hasplayed' => $stats->statstotal->hasplayed,
					'gs' => $stats->statstotal->gs,
					'pts' => $stats->statstotal->pts,
					'dd2' => $stats->statstotal->dd2,
					'dd3' => $stats->statstotal->dd3,
					'min' => $stats->statstotal->min,
					'fgm' => $stats->statstotal->fgm,
					'threepm' => $stats->statstotal->threepm,
					'ftm' => $stats->statstotal->ftm,
					'fta' => $stats->statstotal->fta,
					'reb' => $stats->statstotal->reb,
					'ast' => $stats->statstotal->ast,
					'stl' => $stats->statstotal->stl,
					'pf' => $stats->statstotal->pf,
					'to' => $stats->statstotal->to,
					'ftpercentage' => $this->formatDecimal($stats->statstotal->ftpercentage),
					'mpg' => $this->formatDecimal($stats->statstotal->mpg),
					'rpg' => $this->formatDecimal($stats->statstotal->rpg),
					'apg' => $this->formatDecimal($stats->statstotal->apg),
					'spg' => $this->formatDecimal($stats->statstotal->spg),
					'ppg' => $this->formatDecimal($stats->statstotal->ppg)
				);
			}
		}
		if(!empty($disablesort) && $disablesort) {
			$this->tableoptions = array(
				'disablesort' => true
			);
		}

		// array_push($columns, array(
		// 	'headerlink' => $this->constructSortUrl('min', $this->getSortType('min', $sort, $sorttype), $seasonid, $teamid, $playerid),
		// 	'label' => 'MIN',
		// 	'key' => 'min'
		// ));
		// array_push($columns, array(
		// 	'headerlink' => $this->constructSortUrl('mpg', $this->getSortType('mpg', $sort, $sorttype), $seasonid, $teamid, $playerid),
		// 	'label' => 'MPG',
		// 	'key' => 'mpg'
		// ));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('fgm', $this->getSortType('fgm', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'FGM',
			'key' => 'fgm',
			'tooltip' => 'Field Goals Made'
		));
		// array_push($columns, array(
		// 	'headerlink' => $this->constructSortUrl('xxxx', $this->getSortType('xxxx', $sort, $sorttype), $seasonid, $teamid, $playerid),
		// 	'label' => 'FGA',
		// 	'key' => 'xxxx'
		// ));
		// array_push($columns, array(
		// 	'headerlink' => $this->constructSortUrl('xxxx', $this->getSortType('xxxx', $sort, $sorttype), $seasonid, $teamid, $playerid),
		// 	'label' => 'FG%',
		// 	'key' => 'xxxx'
		// ));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('threepm', $this->getSortType('threepm', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => '3PM',
			'key' => 'threepm',
			'tooltip' => 'Three Points Made'
		));
		// array_push($columns, array(
		// 	'headerlink' => $this->constructSortUrl('xxxx', $this->getSortType('xxxx', $sort, $sorttype), $seasonid, $teamid, $playerid),
		// 	'label' => '3PA',
		// 	'key' => 'xxxx'
		// ));
		// array_push($columns, array(
		// 	'headerlink' => $this->constructSortUrl('xxxx', $this->getSortType('xxxx', $sort, $sorttype), $seasonid, $teamid, $playerid),
		// 	'label' => '3P%',
		// 	'key' => 'xxxx'
		// ));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ftm', $this->getSortType('ftm', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'FTM',
			'key' => 'ftm',
			'tooltip' => 'Free Throws Made'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('fta', $this->getSortType('fta', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'FTA',
			'key' => 'fta',
			'tooltip' => 'Free Throws Attempted'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ftpercentage', $this->getSortType('ftpercentage', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'FT%',
			'key' => 'ftpercentage',
			'tooltip' => 'Free Throw Percentage'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('reb', $this->getSortType('reb', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'REB',
			'key' => 'reb',
			'tooltip' => 'Rebounds'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('rpg', $this->getSortType('rpg', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'RPG',
			'key' => 'rpg',
			'tooltip' => 'Rebounds Per Game'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ast', $this->getSortType('ast', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'AST',
			'key' => 'ast',
			'tooltip' => 'Assists'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('apg', $this->getSortType('apg', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'APG',
			'key' => 'apg',
			'tooltip' => 'Assists Per Game'
		));
		// array_push($columns, array(
		// 	'headerlink' => $this->constructSortUrl('xxxx', $this->getSortType('xxxx', $sort, $sorttype), $seasonid, $teamid, $playerid),
		// 	'label' => 'BLK',
		// 	'key' => 'xxxx'
		// ));
		// array_push($columns, array(
		// 	'headerlink' => $this->constructSortUrl('xxxx', $this->getSortType('xxxx', $sort, $sorttype), $seasonid, $teamid, $playerid),
		// 	'label' => 'BPG',
		// 	'key' => 'xxxx'
		// ));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('stl', $this->getSortType('stl', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'STL',
			'key' => 'stl',
			'tooltip' => 'Steals'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('spg', $this->getSortType('spg', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'SPG',
			'key' => 'spg',
			'tooltip' => 'Steals Per Game'
		));
		// array_push($columns, array(
		// 	'headerlink' => $this->constructSortUrl('pf', $this->getSortType('pf', $sort, $sorttype), $seasonid, $teamid, $playerid),
		// 	'label' => 'PF',
		// 	'key' => 'pf',
		// 	'tooltip' => 'XXXX'
		// ));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('to', $this->getSortType('to', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'TO',
			'key' => 'to',
			'tooltip' => 'Turnovers'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pts', $this->getSortType('pts', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'PTS',
			'key' => 'pts',
			'tooltip' => 'Points'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ppg', $this->getSortType('ppg', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'PPG',
			'key' => 'ppg',
			'tooltip' => 'Points Per Game'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('dd2', $this->getSortType('dd2', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'DD2',
			'key' => 'dd2',
			'tooltip' => 'Double Double'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('dd3', $this->getSortType('dd3', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'DD3',
			'key' => 'dd3',
			'tooltip' => 'Triple Double'
		));
		$columns = $this->setupSortColumn($columns, $sort, $sorttype);
		$defaultsort = $sort != 'pts' ? 'pts' : null;
		echo '<h3 class="stats-title">Basketball Stats</h3>';
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

	public function getStandardColumns( $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $gameid = null) {
		$playerColumn = array(
				'headerlink' => $this->constructSortUrl('name', $this->getSortType('name', $sort, $sorttype), $seasonid, $teamid, $playerid),
				'label' => 'Player',
				'key' => 'name',
				'keylink' => 'playerlink',
				'thclass' => 'name-header'
			);
		$teamColumn = array(
				'headerlink' => $this->constructSortUrl('teamname', $this->getSortType('teamname', $sort, $sorttype), $seasonid, $teamid, $playerid), 
				'label' => 'Team',
				'key' => 'teamname',
				'keylink' => 'teamlink',
				'thclass' => 'name-header'
			);
		$columns = array();

		if(!empty($gameid)) {
			array_push($columns, $playerColumn);
		}
		else {
			if(empty($teamid) && empty($playerid)) {
				array_push($columns, $playerColumn);
				array_push($columns, $teamColumn);
				array_push($columns, array(
					'headerlink' => $this->constructSortUrl('hasplayed', $this->getSortType('hasplayed', $sort, $sorttype), $seasonid, $teamid, $playerid), 
					'label' => 'G',
					'key' => 'hasplayed',
					'tooltip' => 'Games'
				));
			}
			else if(!empty($teamid)) {
				array_push($columns, $playerColumn);
				array_push($columns, array(
					'headerlink' => $this->constructSortUrl('hasplayed', $this->getSortType('hasplayed', $sort, $sorttype), $seasonid, $teamid, $playerid), 
					'label' => 'G',
					'key' => 'hasplayed',
					'tooltip' => 'Games'
				));
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
			// array_push($columns, array(
			// 	'headerlink' => $this->constructSortUrl('gs', $this->getSortType('gs', $sort, $sorttype), $seasonid, $teamid, $playerid),
			// 	'label' => 'GS',
			// 	'key' => 'gs'
			// ));
		}
		return $columns;
	}

	private function setupSortColumn($columns = array(), $sort = null, $sorttype = null) {
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

	private function endDiv() {
		echo "</div>";
	}
	private function addButton($label = null, $target = null, $class = null) {
		$tpl = '<div class="button accent folded-corner {0}" target="{1}">{2}</div>';
		$tpl = str_replace('{0}', $class, $tpl);
		$tpl = str_replace('{1}', $target, $tpl);
		$tpl = str_replace('{2}', $label, $tpl);
		echo $tpl;
	}
}
