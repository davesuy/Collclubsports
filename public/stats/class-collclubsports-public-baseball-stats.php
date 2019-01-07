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
class CollClubSports_Public_Baseball_Stats {

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
		add_shortcode('baseball-stats', array($this, 'stats'));
		add_shortcode('baseball-game-stats', array($this, 'gamestats'));
	}

	private function constructPlayerUrl($playerid, $seasonid = null) {
		$url = $this->options['player_url'] . '?player=' . $playerid;

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
		$gamestats = $this->api->getBaseballStatsPerGame($gameid);
		$this->tableoptions = array(
			'disablesort' => true
		);
		// echo json_encode($gamestats);
		$columns = $this->getStandardColumns(null, null,  null, null, null, $gameid);
		echo '<div class="stats-page collclubsports-page-wrapper game-stats-wrapper">';
			$visitingteamscore = ' ('. $gamestats->game->visitingteamscore . ')';
			echo '<h3>Visiting Team: ' . $gamestats->visitingteam->teamname . $visitingteamscore . '</h3>';
			$this->setupTab();
				echo '<div class="tab-button-wrapper game-stats-button-wrapper">';
					$this->addButton('Hitting Stats', 'visitingteam-hitting-stats', 'active');
					$this->addButton('Pitching Stats', 'visitingteam-pitching-stats');
				$this->endDiv();
				$this->setupTabBody();
					$this->addTabContentWrapper('visitingteam-hitting-stats', true);
						$this->hittingStats($gamestats->visitingteamoffensivestats, $columns);
					$this->endDiv();
					$this->addTabContentWrapper('visitingteam-pitching-stats');
						$this->pitchingStats($gamestats->visitingteampitchingstats, $columns);
					$this->endDiv();
				$this->endDiv();
			$this->endDiv();

			$homescore = ' ('. $gamestats->game->hometeamscore . ')';
			echo '<h3>Home Team: ' . $gamestats->hometeam->teamname . $homescore . '</h3>';
			$this->setupTab();
				echo '<div class="tab-button-wrapper game-stats-button-wrapper">';
					$this->addButton('Hitting Stats', 'hometeam-hitting-stats', 'active');
					$this->addButton('Pitching Stats', 'hometeam-pitching-stats');
				$this->endDiv();
				$this->setupTabBody();
					$this->addTabContentWrapper('hometeam-hitting-stats', true);
						$this->hittingStats($gamestats->hometeamoffensivestats, $columns);
					$this->endDiv();
					$this->addTabContentWrapper('hometeam-pitching-stats');
						$this->pitchingStats($gamestats->hometeampitchingstats, $columns);
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
			case 'hitting': {
				$stats = $this->api->getBaseballHittingStats($seasonid, $teamid, $playerid);

				$this->setupTab();
					$this->hittingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				$this->endDiv();
				break;
			}
			case 'pitching': {
				$stats = $this->api->getBaseballPitchingStats($seasonid, $teamid, $playerid);
				$this->setupTab();
					$this->pitchingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype);
				$this->endDiv();
				break;
			}
		}
		
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

	public function hittingStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $disablesort = false, $placeholder = null) {
		$items = array();
		$total = null;
		$sort = $sort == null ? 'weekname' : $sort;
		if(!empty($stats)) {
			$statsItems = $stats;
			
			if(is_array($stats->offensive)) {
				$statsItems = $stats->offensive;
			}
			foreach ($statsItems as $val) {
				//$val->name = ucwords(strtolower($val->lastname . ', ' . $val->firstname));
				$val->name = str_replace("Inactive","<span class='warn'>INACTIVE</span>",$val->name);
				$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);

				$val->avg = $this->formatDecimal($val->avg, 3);
				$val->obp = $this->formatDecimal($val->obp, 3);
				$val->slg = $this->formatDecimal($val->slg, 3);
				$val->ops = $this->formatDecimal($val->ops, 3);

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
			
			if(!empty($stats->offensivetotal)) {
				$total = array(
					'matchup' => 'Total',
					'name' => 'Total',
					'hasplayed' => $stats->offensivetotal->hasplayed,
					'ab' => $stats->offensivetotal->ab,
					'r' => $stats->offensivetotal->r,
					'h' => $stats->offensivetotal->h,
					'twob' => $stats->offensivetotal->twob,
					'threeb' => $stats->offensivetotal->threeb,
					'hr' => $stats->offensivetotal->hr,
					'rbi' => $stats->offensivetotal->rbi,
					'bb' => $stats->offensivetotal->bb,
					'so' => $stats->offensivetotal->so,
					'sb' => $stats->offensivetotal->sb,
					'cs' => $stats->offensivetotal->cs,
					'avg' => $this->formatDecimal($stats->offensivetotal->avg, 3),
					'obp' => $this->formatDecimal($stats->offensivetotal->obp, 3),
					'slg' => $this->formatDecimal($stats->offensivetotal->slg, 3),
					'ops' => $this->formatDecimal($stats->offensivetotal->ops, 3),
					'ibb' => $stats->offensivetotal->ibb,
					'hbp' => $stats->offensivetotal->hbp,
					'sacb' => $stats->offensivetotal->sacb,
					'sacf' => $stats->offensivetotal->sacf,
					'tb' => $stats->offensivetotal->tb,
					'xhb' => $stats->offensivetotal->xhb,
					'pa' => $stats->offensivetotal->pa
				);
			}
		}

		if(!empty($disablesort) && $disablesort) {
			$this->tableoptions = array(
				'disablesort' => true
			);
		}

		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ab', $this->getSortType('ab', $sort, $sorttype), $seasonid, $teamid, $playerid), 
			'label' => 'AB',
			'key' => 'ab',
			'tooltip' => 'At-Bats'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('r', $this->getSortType('r', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'R',
			'key' => 'r',
			'tooltip' => 'Runs'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('h', $this->getSortType('h', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'H',
			'key' => 'h',
			'tooltip' => 'Hits'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('twob', $this->getSortType('twob', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => '2B',
			'key' => 'twob',
			'tooltip' => 'Doubles'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('threeb', $this->getSortType('threeb', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => '3B',
			'key' => 'threeb',
			'tooltip' => 'Triples'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('hr', $this->getSortType('hr', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'HR',
			'key' => 'hr',
			'tooltip' => 'HR'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('rbi', $this->getSortType('rbi', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'RBI',
			'key' => 'rbi',
			'tooltip' => 'RBI'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('bb', $this->getSortType('bb', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'BB',
			'key' => 'bb',
			'tooltip' => 'Walks'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('so', $this->getSortType('so', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'SO',
			'key' => 'so',
			'tooltip' => 'Strikeouts'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('sb', $this->getSortType('sb', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'SB',
			'key' => 'sb',
			'tooltip' => 'Stolen Bases'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('cs', $this->getSortType('cs', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'CS',
			'key' => 'cs',
			'tooltip' => 'Caught Stealing'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('avg', $this->getSortType('avg', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'AVG',
			'key' => 'avg',
			'tooltip' => 'Batting Average'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('obp', $this->getSortType('obp', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'OBP',
			'key' => 'obp',
			'tooltip' => 'On Base Percentage'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('slg', $this->getSortType('slg', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'SLG',
			'key' => 'slg',
			'tooltip' => 'Slugging Percentage'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ops', $this->getSortType('ops', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'OPS',
			'key' => 'ops',
			'tooltip' => 'On base plus Slugging Percentage'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ibb', $this->getSortType('ibb', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'IBB',
			'key' => 'ibb',
			'tooltip' => 'Intentional Walks'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('hbp', $this->getSortType('hbp', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'HBP',
			'key' => 'hbp',
			'tooltip' => 'Hit By Pitch'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('sacb', $this->getSortType('sacb', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'SAC-B',
			'key' => 'sacb',
			'thclass' => 'sacb-header',
			'tooltip' => 'Sacrifice Bunt'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('sacf', $this->getSortType('sacf', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'SAC-F',
			'key' => 'sacf',
			'thclass' => 'sacf-header',
			'tooltip' => 'Sacrifice Fly'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('tb', $this->getSortType('tb', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'TB',
			'key' => 'tb',
			'tooltip' => 'Total Bases'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('xhb', $this->getSortType('xhb', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'XBH',
			'key' => 'xhb',
			'tooltip' => 'Extra Base Hits'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pa', $this->getSortType('pa', $sort, $sorttype), $seasonid, $teamid, $playerid),
			'label' => 'PA',
			'key' => 'pa',
			'tooltip' => 'Plate Appearances'
		));
		$columns = $this->setupSortColumn($columns, $sort, $sorttype);
		if (!empty($placeholder)) {
			$this->tableoptions['noItemLabel'] = $placeholder;
		}
		$defaultsort = $sort != 'avg' ? 'avg' : null;
		echo '<h3 class="stats-title">Hitting Stats</h3>';
		echo '<div style="overflow:auto;">';
			new CollClubSports_Table($columns,  $items, $total, $defaultsort, 'desc', $sort, $sorttype, $this->tableoptions);
		echo $this->endDiv();
		?>
			
		<?php
	}

	public function pitchingStats($stats = null, $columns = array(), $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $disablesort = false, $placeholder = null) {
		$items = array();
		$total = null;
		$sort = $sort == null ? 'weekname' : $sort;
		if(!empty($stats)) {
			$statsItems = $stats;

			if(is_array($stats->pitching)) {
				$statsItems = $stats->pitching;
			}
			foreach ($statsItems as $val) {
				//$val->name = ucwords(strtolower($val->lastname . ', ' . $val->firstname));
				$val->name = str_replace("Inactive","<span class='warn'>INACTIVE</span>",$val->name);
				$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);

				$val->ip = $this->formatDecimal($val->ip);
				$val->era = $this->formatDecimal($val->era);
				$val->kper9 = $this->formatDecimal($val->kper9);
				$val->wper9 = $this->formatDecimal($val->wper9);
				$val->hper9 = $this->formatDecimal($val->hper9);
				$val->kbb = $this->formatDecimal($val->kbb);
				$val->whip = $this->formatDecimal($val->whip);
				$val->wpercentage = $this->formatDecimal($val->wpercentage);

				if($val->w == false) {
					$val->w = 0;
				} else if($val->l == false) {
					$val->l = 0;
				}

				if($val->gamestart == false) {
					$val->gamestart = 0;
				}

				if($val->cg == false) {
					$val->cg = 0;
				}

				if($val->sho == false) {
					$val->sho = 0;
				}

				if($val->sv == false) {
					$val->sv = 0;
				}

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
			
			if(!empty($stats->pitchingtotal)) {
				$total = array(
					'matchup' => 'Total',
					'name' => 'Total',
					'hasplayed' => $stats->pitchingtotal->hasplayed,
					'gamestart' => $stats->pitchingtotal->gamestart,
					'w' => $stats->pitchingtotal->w,
					'l' => $stats->pitchingtotal->l,
					'cg' => $stats->pitchingtotal->cg,
					'sho' => $stats->pitchingtotal->sho,
					'sv' => $stats->pitchingtotal->sv,
					'ip' => $this->formatDecimal($stats->pitchingtotal->ip),
					'era' => $this->formatDecimal($stats->pitchingtotal->era),
					'kper9' => $this->formatDecimal($stats->pitchingtotal->kper9),
					'wper9' => $this->formatDecimal($stats->pitchingtotal->wper9),
					'hper9' => $this->formatDecimal($stats->pitchingtotal->hper9),
					'kbb' => $this->formatDecimal($stats->pitchingtotal->kbb),
					'whip' => $this->formatDecimal($stats->pitchingtotal->whip),
					'wpercentage' => $this->formatDecimal($stats->pitchingtotal->wpercentage),
					'svo' => $stats->pitchingtotal->svo,
					'h' => $stats->pitchingtotal->h,
					'r' => $stats->pitchingtotal->r,
					'er' => $stats->pitchingtotal->er,
					'hr' => $stats->pitchingtotal->hr,
					'hbp' => $stats->pitchingtotal->hbp,
					'bb' => $stats->pitchingtotal->bb,
					'ibb' => $stats->pitchingtotal->ibb,
					'so' => $stats->pitchingtotal->so,
					'bk' => $stats->pitchingtotal->bk,
					'wp' => $stats->pitchingtotal->wp,
					'pk' => $stats->pitchingtotal->pk
				);
			}
		}

		if(!empty($disablesort) && $disablesort) {
			$this->tableoptions = array(
				'disablesort' => true
			);
		}
		
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('w', $this->getSortType('w', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'W',
			'key' => 'w',
			'tooltip' => 'Wins'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('l', $this->getSortType('l', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'L',
			'key' => 'l',
			'tooltip' => 'Losses'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('era', $this->getSortType('era', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'ERA',
			'key' => 'era',
			'tooltip' => 'Earn Run Average'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('hasplayed', $this->getSortType('hasplayed', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'G',
			'key' => 'hasplayed',
			'tooltip' => 'Games'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('gamestart', $this->getSortType('gamestart', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'GS',
			'key' => 'gamestart',
			'tooltip' => 'Games Started'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('cg', $this->getSortType('cg', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'CG',
			'key' => 'cg',
			'tooltip' => 'Complete Games'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('sv', $this->getSortType('sv', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'SV',
			'key' => 'sv',
			'tooltip' => 'Saves'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('svo', $this->getSortType('svo', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'SVO',
			'key' => 'svo',
			'tooltip' => 'Save Opportunity'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ip', $this->getSortType('ip', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'IP',
			'key' => 'ip',
			'tooltip' => 'Innings Pitched'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('h', $this->getSortType('h', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'H',
			'key' => 'h',
			'tooltip' => 'Hits'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('r', $this->getSortType('r', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'R',
			'key' => 'r',
			'tooltip' => 'Runs'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('er', $this->getSortType('er', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'ER',
			'key' => 'er',
			'tooltip' => 'Earned Runs'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('hr', $this->getSortType('hr', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'HR',
			'key' => 'hr',
			'tooltip' => 'HR'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('bb', $this->getSortType('bb', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'BB',
			'key' => 'bb',
			'tooltip' => 'Walks'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('so', $this->getSortType('so', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'SO',
			'key' => 'so',
			'tooltip' => 'Strikeouts'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('whip', $this->getSortType('whip', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'WHIP',
			'key' => 'whip',
			'tooltip' => 'Walks Hits per inning pitched'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('sho', $this->getSortType('sho', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'SHO',
			'key' => 'sho',
			'tooltip' => 'SHO'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('hbp', $this->getSortType('hbp', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'HBP',
			'key' => 'hbp',
			'tooltip' => 'Hit By Pitch'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('ibb', $this->getSortType('ibb', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'IBB',
			'key' => 'ibb',
			'tooltip' => 'Intentional Walk'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('wp', $this->getSortType('wp', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'WP',
			'key' => 'wp',
			'tooltip' => 'WP'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('bk', $this->getSortType('bk', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'BK',
			'key' => 'bk',
			'tooltip' => 'BK'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('pk', $this->getSortType('pk', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'PK',
			'key' => 'pk',
			'tooltip' => 'PK'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('wpercentage', $this->getSortType('wpercentage', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'W%',
			'key' => 'wpercentage',
			'tooltip' => 'Winning Percentage'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('kper9', $this->getSortType('kper9 ', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'KPER9',
			'key' => 'kper9',
			'tooltip' => 'KPER9'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('wper9', $this->getSortType('wper9', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'WPER9',
			'key' => 'wper9',
			'tooltip' => 'WPER9'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('hper9', $this->getSortType('hper9', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'HPER9',
			'key' => 'hper9',
			'tooltip' => 'HPER9'
		));
		array_push($columns, array(
			'headerlink' => $this->constructSortUrl('kbb', $this->getSortType('kbb', $sort, $sorttype), $seasonid, $teamid, $playerid, 'pitching'), 
			'label' => 'K/BB',
			'key' => 'kbb',
			'tooltip' => 'K/BB'
		));
		$columns = $this->setupSortColumn($columns, $sort, $sorttype);
		if (!empty($placeholder)) {
			$this->tableoptions['noItemLabel'] = $placeholder;
		}
		$defaultsort = $sort != 'era' ? 'era' : null;
		echo '<h3 class="stats-title">Pitching Stats</h3>';
		echo '<div style="overflow:auto;">';
			new CollClubSports_Table($columns, $items, $total, $defaultsort, 'asc', $sort, $sorttype, $this->tableoptions);
		echo $this->endDiv();
		?>
		<?php
	}

	private function formatDecimal($val, $decimalplaces = null) {
		if (empty($decimalplaces) || !isset($decimalplaces))
				$decimalplaces = 2;
		$val = round($val, $decimalplaces);
		return  number_format($val, $decimalplaces, '.', '');
	}

	public function getStandardColumns( $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $gameid = null, $type = null) {
		$playerColumn = array(
				'headerlink' => $this->constructSortUrl('name', $this->getSortType('name', $sort, $sorttype), $seasonid, $teamid, $playerid, $type),
				'label' => 'Player',
				'key' => 'name',
				'keylink' => 'playerlink',
				'thclass' => 'name-header'
			);
		$teamColumn = array(
				'headerlink' => $this->constructSortUrl('teamname', $this->getSortType('teamname', $sort, $sorttype), $seasonid, $teamid, $playerid, $type), 
				'label' => 'Team',
				'key' => 'teamname',
				'thclass' => 'name-header'
			);
		$columns = array();

		if(!empty($gameid)) {
			array_push($columns, $playerColumn);
		}
		else if(empty($teamid) && empty($playerid)) {
			array_push($columns, $playerColumn);
			array_push($columns, $teamColumn);
			array_push($columns, array(
				'headerlink' => $this->constructSortUrl('hasplayed', $this->getSortType('hasplayed', $sort, $sorttype), $seasonid, $teamid, $playerid), 
				'label' => 'G',
				'key' => 'hasplayed',
				'tooltip' => 'Game'
			));
		}
		else if(!empty($teamid)) {
			array_push($columns, $playerColumn);
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

	public function setupTab() {
		echo '<div class="collclubsports-component tab-wrapper"> ';
	}

	public function setupTabBody() {
		echo '<div class="tab-body-wrapper">';
	}

	private function addTabContentWrapper($name = null, $isactive = false) {
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

	private function addButton($label = null, $target = null, $class = null) {
		$tpl = '<div class="button accent folded-corner {0}" target="{1}">{2}</div>';
		$tpl = str_replace('{0}', $class, $tpl);
		$tpl = str_replace('{1}', $target, $tpl);
		$tpl = str_replace('{2}', $label, $tpl);
		echo $tpl;
	}
}
