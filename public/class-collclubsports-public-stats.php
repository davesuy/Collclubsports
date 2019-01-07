<?php
require_once plugin_dir_path( __DIR__ ) . 'public/stats/class-collclubsports-public-baseball-stats.php';
require_once plugin_dir_path( __DIR__ ) . 'public/stats/class-collclubsports-public-softball-stats.php';
require_once plugin_dir_path( __DIR__ ) . 'public/stats/class-collclubsports-public-basketball-stats.php';
require_once plugin_dir_path( __DIR__ ) . 'public/stats/class-collclubsports-public-football-stats.php';
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
class CollClubSports_Public_Stats {

	protected $api = false;

	private $baseballstats;

	private $softballstats;
	
	private $basketballstats;

	private $footballstats;
	
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct($api) {
		$this->api = $api;
		$this->register_shortcodes();
		$this->options = get_option('collclubsports_admin');
		$this->define_hooks();
	}

	/**
	 * Register all of the hooks 
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_hooks() {
		$this->baseballstats = new CollClubSports_Public_Baseball_Stats($this->api);
		$this->softballstats = new CollClubSports_Public_Softball_Stats($this->api);
		$this->basketballstats = new CollClubSports_Public_Basketball_Stats($this->api);
		$this->footballstats = new CollClubSports_Public_Football_Stats($this->api);
	}

	private function register_shortcodes() {
		add_shortcode('stats', array($this, 'stats'));
		add_shortcode('stats-team', array($this, 'statsteam'));
		add_shortcode('stats-player', array($this, 'statsplayer'));
		add_shortcode('stats-tracker', array($this, 'statstracker'));
		add_shortcode('game-stats', array($this, 'gamestats'));
	}

	private function constructExternalUrl($url) {
		$url = str_replace('http://', '', $url);
		$url = str_replace('https://', '', $url);
		return '//' . $url;
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

		if(empty($seasonid)) {
			$seasonid = $_GET['season'];
		}
		if(!empty($seasonid)) {
			$url = $url . '&season=' . $seasonid;
		}
		return $url;
	}

	public function gamestats($attrs) {
		$gameid = $_GET['game'];
		$this->setupGameData($gameid);
		if(!empty($gameid)) {
			switch($this->options['sport_type']) {
				case 1: { // Baseball
					do_shortcode('[baseball-game-stats game="' . $gameid . '"]');
					break;
				}
				case 2: { // Softball
					do_shortcode('[softball-game-stats game="' . $gameid . '"]');
					break;
				}
				case 3: { // Basketball
					do_shortcode('[basketball-game-stats game="' . $gameid . '"]');
					break;
				}
				case 4: { // Football
					do_shortcode('[football-game-stats game="' . $gameid . '"]');
					break;
				}
				case 5: { // TrackAndField
					break;
				}
			}	
		}
	}

	public function statstracker($attrs) {
		$seasonid = $attrs['season'];
		$title = 'Stat Tracker';
		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		if(!empty($attrs['conference'])) {
			$conferenceid = $attrs['conference'];
		}

		if(!empty($attrs['team'])) {
			$teamid = $attrs['team'];
		}

		if(!empty($attrs['title'])) {
			$title = $attrs['title'];
		}

		?>
		<div class="collclubsports-component box-wrapper stats-tracker-box-wrapper">
			<div class="box-title"><?php echo $title;?></div>
			<div class="stats-note">Stats are updated at 12 PM EST daily</div>
			<div class="schedule-wrapper box-body-wrapper">
		<?php 
		$topstats = array();
		switch($this->options['sport_type']) {
			case 1: { // Baseball
				array_push($topstats, array(
					'title' => 'Wins',
					'stats' => $this->api->getBaseballTopW($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'ERA (Minimum 25 IP)',
					'stats' => $this->api->getBaseballTopERA($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'Strikeouts',
					'stats' => $this->api->getBaseballTopSO($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'AVG (Minimum 45 PA)',
					'stats' => $this->api->getBaseballTopAVG($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'RBI',
					'stats' => $this->api->getBaseballTopRBI($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'HR',
					'stats' => $this->api->getBaseballTopHR($seasonid, $conferenceid, 5, $teamid)
				));
				$this->setupStatsTrackerTopStats($seasonid, $topstats);
				break;
			}
			case 2: { // Softball
				array_push($topstats, array(
					'title' => 'Wins',
					'stats' => $this->api->getSoftballTopW($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'ERA (Minimum 25 IP)',
					'stats' => $this->api->getSoftballTopERA($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'Strikeouts',
					'stats' => $this->api->getSoftballTopSO($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'AVG (Minimum 45 PA)',
					'stats' => $this->api->getSoftballTopAVG($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'RBI',
					'stats' => $this->api->getSoftballTopRBI($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'HR',
					'stats' => $this->api->getSoftballTopHR($seasonid, $conferenceid, 5, $teamid)
				));
				$this->setupStatsTrackerTopStats($seasonid, $topstats);
				break;
			}
			case 3: { // Basketball
				array_push($topstats, array(
					'title' => 'Top Five RPG',
					'stats' => $this->api->getBasketballTopREB($seasonid, $conferenceid, 5, $teamid)
				));

				array_push($topstats, array(
					'title' => 'Top Five APG',
					'stats' => $this->api->getBasketballTopAPG($seasonid, $conferenceid, 5, $teamid)
				));

				array_push($topstats, array(
					'title' => 'Top Five SPG',
					'stats' => $this->api->getBasketballTopSPG($seasonid, $conferenceid, 5, $teamid)
				));

				array_push($topstats, array(
					'title' => 'Top Five PPG',
					'stats' => $this->api->getBasketballTopPPG($seasonid, $conferenceid, 5, $teamid)
				));

				array_push($topstats, array(
					'title' => 'Top Five DD2',
					'stats' => $this->api->getBasketballTopDD2($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'Top Five DD3',
					'stats' => $this->api->getBasketballTopDD3($seasonid, $conferenceid, 5, $teamid)
				));
				$this->setupStatsTrackerTopStats($seasonid, $topstats);
				break;
			}
			case 4: { // Football
				array_push($topstats, array(
					'title' => 'Passing Yards',
					'stats' => $this->api->getFootballTopPassingYDS($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'Comp% (Min. 14 atts. per game)',
					'stats' => $this->api->getFootballTopPassingCompPct($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'Passing TDs',
					'stats' => $this->api->getFootballTopPassingTDS($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'Rushing Yards',
					'stats' => $this->api->getFootballTopRushingYDS($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'Rushing TDs',
					'stats' => $this->api->getFootballTopRushingTDS($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'Receiving Yards',
					'stats' => $this->api->getFootballTopReceivingYDS($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'Receiving TDs',
					'stats' => $this->api->getFootballTopReceivingTDS($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'Tackles',
					'stats' => $this->api->getFootballTopTackles($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'Sacks',
					'stats' => $this->api->getFootballTopSACK($seasonid, $conferenceid, 5, $teamid)
				));
				array_push($topstats, array(
					'title' => 'Interceptions',
					'stats' => $this->api->getFootballTopInt($seasonid, $conferenceid, 5, $teamid)
				));
				$this->setupStatsTrackerTopStats($seasonid, $topstats);
				break;
			}
			case 5: { // TrackAndField
				break;
			}
		}
		?>
			</div>
		</div>
		<?php 
	}

	public function setupStatsTrackerTopStats($seasonid, $topstats) {
		for($i = 0; $i < sizeof($topstats);$i++){
			$stat = $topstats[$i]; ?>

			<div class="top-stats-item-wrapper <?php if($i==0) echo 'active';?>" <?php if($i==0) echo 'style="display: block;"';?>>
				<h3><?php echo $stat['title']; ?></h3>
				<table>
					<tbody>
					<?php for($j = 0; $j < sizeof($stat['stats']);$j++){ 
						$item = $stat['stats'][$j];
						?>
						<tr>
							<td># <?php echo $j + 1; ?></td>
							<td>
								<a class="primary-color"
									href="<?php echo $this->constructPlayerUrl($item->playerid, $seasonid);?>">
									<?php echo $item->name; ?>
								</a>
							</td>
							<td>
								<a class="primary-color"
									href="<?php echo $this->constructTeamUrl($item->teamid, $seasonid);?>">
									<?php   
										if($item->isprobation) {
											echo '*';
										}
										echo $item->teamname; ?>
								</a>
							</td>
							<td>
								<?php echo number_format($item->total,2); ?>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
		<?php }
	}

	public function statsplayer($attrs) {
		$this->stats($attrs, true);
	}

	public function statsteam($attrs) {
		$seasonid = $attrs['season'];
		$teamid = $attrs['team'];
		switch($this->options['sport_type']) {
			case 1: { // Baseball
				$this->setupBaseballStats($seasonid, $teamid, null, null, null, null, true);
				break;
			}
			case 2: { // Softball
				$this->setupSoftballStats($seasonid, $teamid, null, null, null, null, true);
				break;
			}
			case 3: { // Basketball
				$this->setupBasketballStats($seasonid, $teamid, null, null, null, null, true);
				break;
			}
			case 4: { // Football
				$this->setupFootballStats($seasonid, $teamid, null, null, null, null, true);
				break;
			}
			case 5: { // TrackAndField
				// Nothing here. No stats for track and field
				break;
			}
		}
	}

	public function stats($attrs , $isplayerstats = false) {
		$seasonid = $_GET['season'];
		$seasons = $this->api->getSeasons();
		$league = $this->api->getLeague();
		
		for($i = 0; $i < sizeof($seasons);$i++){
			$season = $seasons[$i];
		    if ($season->iscurrentseason==true) {
		    	if(empty($seasonid)) {
					$seasonid = $season->seasonid;
				}
		        break;
		    }
		}

		$teams = $this->api->getTeams($seasonid, null, true);
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$type = $_GET['type'];
		$teamid = $_GET['team'];
		if (!empty($teams)) {
			$team_in_season = false;
			foreach ($teams as $t) {
				if (isset($t->teamid) && $t->teamid == $teamid)
	            	$team_in_season = true;
			}
		}

		if($isplayerstats) {
			$playerid = $_GET['player'];
			$teamid = null;
			$players = $this->api->getPlayers($seasonid);
			if(empty($playerid) && count($players)) {
				$playerid = $players[0]->playerid;
			}
			foreach ($players as $val) {
				if($val->playerid == $playerid) {
					$player = $val;
				}
			}
		}
		
		wp_enqueue_script( $this->plugin_name . '-ajax', plugin_dir_url( __FILE__ ) . 'js/collclubsports-stats-page.js', array('jquery'), $this->version, false );

		wp_localize_script( $this->plugin_name . '-ajax', 
			'collclubsports', 
			array( 
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'sport_type' => $league->sporttype,
				'playerid' => $playerid,
				'disablesort' => $isplayerstats,
				'team_in_season' => $team_in_season
			) 
		);

		?>
		<div id="stats-page" class="stats-page collclubsports-page-wrapper">
			<div class="stats-option-wrapper">
				<div class="form-group select-field">
					<label for="select-season">Select Season</label>
					<select class="form-control" id="select-season">
						<?php for($i = 0; $i < sizeof($seasons);$i++){
							$season = $seasons[$i]; ?>
							<option value="<?php echo $season->seasonid;?>"><?php echo $season->seasonname;?></option>
						<?php }?>
					</select>
				</div>
				<?php if(!$isplayerstats) { ?>
					<div class="form-group select-field">
						<label for="select-team">Sort by Team</label>
						<select class="form-control" id="select-team">
							<option value="">Select Team</option>
							<?php for($i = 0; $i < sizeof($teams);$i++){
								$team = $teams[$i]; ?>
								<option value="<?php echo $team->teamid;?>"><?php if($team->isprobation) {
											echo '*';
										}
										 echo   
										$team->teamname;?></option>
							<?php }?>
						</select>
					</div>
				<?php } ?>
				<?php if($isplayerstats) { ?>
					<div class="form-group select-field">
						<label for="select-player">Sort by Player</label>
						<select class="form-control" id="select-player">
							<?php for($i = 0; $i < sizeof($players);$i++){?>
								<option value="<?php echo $players[$i]->playerid;?>"><?php echo $players[$i]->name;?></option>
							<?php }?>
						</select>
					</div>
				<?php } ?>
			</div>
			<div class="stats-note">Stats are updated at 12 PM EST daily</div>
			<?php if($isplayerstats) { 
				$this->setupPlayer($player);
			}?>
			<?php 
				switch($this->options['sport_type']) {
					case 1: { // Baseball
						echo '<div class="tab-button-wrapper main-buttons">
							<div class="button accent folded-corner baseball-stats-button" target="baseball-hitting-stats-table">Hitting Stats</div>
							<div class="button accent folded-corner baseball-stats-button" target="baseball-pitching-stats-table">Pitching Stats</div>
						</div>';
						$columns = $this->baseballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype);
						// Hitting stats
						echo '<div name="baseball-hitting-stats-table" class="stats-table-container">';
							$this->baseballstats->setupTab();
								$this->baseballstats->hittingStats(null, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $isplayerstats, 'Loading items...');
							$this->baseballstats->endDiv();
						echo '</div>';
						// Pitching stats
						echo '<div name="baseball-pitching-stats-table" class="stats-table-container">';
							$this->baseballstats->setupTab();
								$this->baseballstats->pitchingStats(null, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $isplayerstats, 'Loading items...');
							$this->baseballstats->endDiv();
						echo '</div>';
						break;
					}
					case 2: { // Softball
						echo '<div class="tab-button-wrapper main-buttons">
							<div class="button accent folded-corner softball-stats-button" target="softball-hitting-stats-table">Hitting Stats</div>
							<div class="button accent folded-corner softball-stats-button" target="softball-pitching-stats-table">Pitching Stats</div>
						</div>';
						$columns = $this->softballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype);
						// Hitting stats
						echo '<div name="softball-hitting-stats-table" class="stats-table-container">';
							$this->softballstats->setupTab();
								$this->softballstats->hittingStats(null, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $isplayerstats, 'Loading items...');	
							$this->softballstats->endDiv();
						echo '</div>';
						// Pitching stats
						echo '<div name="softball-pitching-stats-table" class="stats-table-container">';
							$this->softballstats->setupTab();
								$this->softballstats->pitchingStats(null, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $isplayerstats, 'Loading items...');	
							$this->softballstats->endDiv();
						echo '</div>';
						break;
					}
					case 3: { // Basketball
						$this->setupBasketballStats($seasonid, $teamid, $playerid, $type, $sort, $sorttype, $isplayerstats);
						break;
					}
					case 4: { // Football
						echo '<div class="tab-button-wrapper main-buttons">
							<div class="button accent folded-corner football-stats-button" target="football-offensive-stats-table">Offensive Stats</div>
							<div class="button accent folded-corner football-stats-button" target="football-defensive-stats-table">Defensive Stats</div>
							<div class="button accent folded-corner football-stats-button" target="football-special-team-stats-table">Special Team Stats</div>
						</div>';
						$columns = $this->footballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype);
						// Offensive stats
						echo '<div name="football-offensive-stats-table" class="stats-table-container">';
							$this->footballstats->setupTab();
								$this->footballstats->setupOffensiveTabButton();
								$this->footballstats->setupTabBody();
									$this->footballstats->addTabContentWrapper('passing-stats', true);
										$this->footballstats->offensivePassingStats(null, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $gameid, $isplayerstats, 'Loading items...');
									$this->footballstats->endDiv();
									$this->footballstats->addTabContentWrapper('rushing-stats');
										$this->footballstats->offensiveRushingStats(null, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $gameid, $isplayerstats, 'Loading items...');
									$this->footballstats->endDiv();
									$this->footballstats->addTabContentWrapper('receiving-stats');
										$this->footballstats->offensiveReceivingStats(null, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $gameid, $isplayerstats, 'Loading items...');
									$this->footballstats->endDiv();
									$this->footballstats->addTabContentWrapper('field-goal-stats');
										$this->footballstats->offensiveFieldGoalStats(null, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $isplayerstats, 'Loading items...');
									$this->footballstats->endDiv();
								$this->footballstats->endDiv();
							$this->footballstats->endDiv();
						echo '</div>';
						// Defensive stats
						echo '<div name="football-defensive-stats-table" class="stats-table-container">';
							$this->footballstats->setupTab();
								$this->footballstats->setupDefensiveTabButton();
								$this->footballstats->setupTabBody();
									$this->footballstats->addTabContentWrapper('defense-stats', true);
										$this->footballstats->defensiveDefenseStats(null, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $isplayerstats, 'Loading items...');
									$this->footballstats->endDiv();
									$this->footballstats->addTabContentWrapper('intercept-stats');
										$this->footballstats->defensiveInterceptionStats(null, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $isplayerstats, 'Loading items...');
									$this->footballstats->endDiv();
								$this->footballstats->endDiv();
							$this->footballstats->endDiv();
						echo '</div>';
						// Special team stats
						echo '<div name="football-special-team-stats-table" class="stats-table-container">';
							$this->footballstats->setupTab();
								$this->footballstats->setupSpecialTeamTabButton();
								$this->footballstats->setupTabBody();
									$this->footballstats->addTabContentWrapper('punting-stats', true);
										$this->footballstats->specialTeamPuntingStats(null, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $isplayerstats, 'Loading items...');
									$this->footballstats->endDiv();
									$this->footballstats->addTabContentWrapper('punt-return-stats');
										$this->footballstats->specialTeamPuntReturnStats(null, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $isplayerstats, 'Loading items...');
									$this->footballstats->endDiv();
									$this->footballstats->addTabContentWrapper('kick-return-stats');
										$this->footballstats->specialTeamKickReturnStats(null, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $isplayerstats, 'Loading items...');
								$this->footballstats->endDiv();
							$this->footballstats->endDiv();
						echo '</div>';
						break;
					}
					case 5: { // TrackAndField
						// Nothing here. No stats for track and field
						break;
					}
				}
			?>
		</div>
		<?php 
	}

	private function setupPlayer($player) { 
		if(empty($player)) {
			return;
		}
		$profileurl = $player->profilepictureurl;
	?>
		<div class="player-detail-wrapper">
			<div class="profile-wrapper">
				<?php if(!empty($profileurl)) { ?> 
				<img src="<?php echo $profileurl; ?>">
				<?php 
				} else { ?>
					<div class="no-image"></div>
				<?php } ?>
			</div>
			<div class="player-information-wrapper">
				<h3><?php echo str_replace("Inactive","<span class='warn'>INACTIVE</span>",$player->name);
					if(!empty($player->jerseynumber)) {
						echo ' - #' . $player->jerseynumber;
					}  ?></h3>
				<?php if(!empty($player->intendteam)) { ?> 
					<div>
						<span>Team: </span>
						<label class="primary-color"><?php 
							if($player->intendteam->isprobation) {
								echo '*';
							}
							 echo $player->intendteam->schoolname . ' - ' . $player->intendteam->teamname; ?></label>
					</div>
				<?php } ?>
				<?php if(!empty($player->heightfeet)) { ?> 
					<div>
						<span>Height: </span>
						<label class="primary-color"><?php echo $player->heightfeet . "' " . $player->heightinches .'"'; ?></label>
					</div>
				<?php } ?>
				<?php if(!empty($player->weight)) { ?> 
					<div>
						<span>Weight: </span>
						<label class="primary-color"><?php echo $player->weight;?></label>
					</div>
				<?php } ?>
				<?php if(!empty($player->primaryposition)) { 
					$primaryposition = explode("-", $player->primaryposition)[0];
					if(!empty($player->secondaryposition)) {
						$secondaryposition = explode("-", $player->secondaryposition)[0];
					}
				?> 
					<div>
						<span>Position: </span>
						<label class="primary-color"><?php echo $primaryposition . ' / ' . $secondaryposition;?></label>
					</div>
				<?php } ?>
				<?php if(!empty($player->bats)) { ?> 
					<div>
						<span>Bats: </span>
						<label class="primary-color"><?php echo $player->bats;?></label>
					</div>
				<?php } ?>
				<?php if(!empty($player->throws)) { ?> 
					<div>
						<span>Throws: </span>
						<label class="primary-color"><?php echo $player->throws;?></label>
					</div>
				<?php } ?>
				<?php if(!empty($player->dateofbirth)) { ?> 
					<div>
						<span>Date of Birth: </span>
						<label class="primary-color"><?php $dob = new DateTime($player->dateofbirth); echo $dob->format('m/d/Y');?></label>
					</div>
				<?php } ?>
				<?php if(!empty($player->dateofbirth)) { ?> 
					<div>
						<span>EligYear: </span>
						<label class="primary-color"><?php echo $this->eligYearSuffix($player->yrdife) . ' / ' .  $this->eligIntT($player->yrdife);?></label>
					</div>
				<?php } ?>
				<?php if(!empty($player->highschool)) { ?> 
					<div>
						<span>High School: </span>
						<label class="primary-color"><?php echo $player->highschool . ', ' . $player->highschoolhometown . ', ' . $player->highschoolstate;?></label>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php }
	
	public function setupBaseballStats($seasonid, $teamid = null, $playerid = null, $type = null, $sort = null, $sorttype = null, $disablesort = false, $excludeshortcode = false) {
		if(empty($type)) {
			$type = 'hitting';
		}
		echo '<div class="tab-button-wrapper">';
			if($type == 'hitting') {
				$this->addButton('Hitting Stats', $this->getStatsLink($seasonid, 'hitting', $teamid, $playerid), 'active');
			} else {
				$this->addButton('Hitting Stats', $this->getStatsLink($seasonid, 'hitting', $teamid, $playerid));
			}

			if($type == 'pitching') {
				$this->addButton('Pitching Stats', $this->getStatsLink($seasonid, 'pitching', $teamid, $playerid), 'active');
			} else {
				$this->addButton('Pitching Stats', $this->getStatsLink($seasonid, 'pitching', $teamid, $playerid));
			}
		echo '</div>';
		echo '<div class="stats-note">Stats are updated at 12 PM EST daily</div>';
		if (!$excludeshortcode) {
			$shortcode = '[baseball-stats type="' . $type .'" season="' . $seasonid .'" team="' . $teamid .'" player="' . $playerid .'" sort="' . $sort .'" sorttype="' . $sorttype .'" disablesort="' . $disablesort .'"]';
			do_shortcode($shortcode);
		}
	}

	public function setupSoftballStats($seasonid, $teamid = null, $playerid = null, $type = null, $sort = null, $sorttype = null, $disablesort = false, $excludeshortcode = false) {
		if(empty($type)) {
			$type = 'hitting';
		}
		echo '<div class="tab-button-wrapper">';
			if($type == 'hitting') {
				$this->addButton('Hitting Stats', $this->getStatsLink($seasonid, 'hitting', $teamid, $playerid), 'active');
			} else {
				$this->addButton('Hitting Stats', $this->getStatsLink($seasonid, 'hitting', $teamid, $playerid));
			}

			if($type == 'pitching') {
				$this->addButton('Pitching Stats', $this->getStatsLink($seasonid, 'pitching', $teamid, $playerid), 'active');
			} else {
				$this->addButton('Pitching Stats', $this->getStatsLink($seasonid, 'pitching', $teamid, $playerid));
			}
		echo '</div>';
		echo '<div class="stats-note">Stats are updated at 12 PM EST daily</div>';
		if (!$excludeshortcode) {
			$shortcode = '[softball-stats type="' . $type .'" season="' . $seasonid .'" team="' . $teamid .'" player="' . $playerid .'" sort="' . $sort .'" sorttype="' . $sorttype .'" disablesort="' . $disablesort .'"]';
			do_shortcode($shortcode);
		}
	}

	private function setupBasketballStats($seasonid, $teamid = null, $playerid = null, $type = null, $sort = null, $sorttype = null, $disablesort = false) {
		$shortcode = '[basketball-stats season="' . $seasonid .'" team="' . $teamid .'" player="' . $playerid .'" sort="' . $sort .'" sorttype="' . $sorttype .'" disablesort="' . $disablesort .'"]';
		do_shortcode($shortcode);
	}

	public function setupFootballStats($seasonid, $teamid = null, $playerid = null, $type = null, $sort = null, $sorttype = null, $disablesort = false, $excludeshortcode = false) {
		if(empty($type)) {
			$type = 'offensive';
		}
		echo '<div class="tab-button-wrapper">';
			if($type == 'offensive') {
				$this->addButton('Offensive Stats', $this->getStatsLink($seasonid, 'offensive', $teamid, $playerid), 'active');
			} else {
				$this->addButton('Offensive Stats', $this->getStatsLink($seasonid, 'offensive', $teamid, $playerid));
			}

			if($type == 'defensive') {
				$this->addButton('Defensive Stats', $this->getStatsLink($seasonid, 'defensive', $teamid, $playerid), 'active');
			} else {
				$this->addButton('Defensive Stats', $this->getStatsLink($seasonid, 'defensive', $teamid, $playerid));
			}

			if($type == 'special-team') {
				$this->addButton('Special Team Stats', $this->getStatsLink($seasonid, 'special-team', $teamid, $playerid), 'active');
			} else {
				$this->addButton('Special Team Stats', $this->getStatsLink($seasonid, 'special-team', $teamid, $playerid));
			}
		echo '</div>';
		echo '<div class="stats-note">Stats are updated at 12 PM EST daily</div>';
		if (!$excludeshortcode) {
			$shortcode = '[football-stats type="' . $type .'" season="' . $seasonid .'" team="' . $teamid .'" player="' . $playerid .'" sort="' . $sort .'" sorttype="' . $sorttype .'" disablesort="' . $disablesort .'"]';
			do_shortcode($shortcode);
		}
	}

	private function setupGameData($gameid) {
		$game = $this->api->getGameDetails($gameid);
		$week = $this->api->getWeek($game->weekid);
		?>
		<div class="collclubsports-component">
			<label><?php echo $week->weekname;?></label>
			<div>Day: <b><span class="primary-color"><?php echo $game->dayofgame;?></span></b></div> 
			<div>Time: <b><span class="primary-color"><?php echo $game->officialstarttime;?></span></b></div> 
			<div>Field: <b><span class="primary-color"><?php echo $game->fieldname;?></span></b></div> 
			<div>Location: <b><span class="primary-color"><?php echo $game->fieldaddress;?></span></b></div> 
		</div>
		<div class="stats-note">Stats are updated at 12 PM EST daily</div>
	<?php }

	private function addButton($label = null, $target = null, $class = null) {
		$tpl = '<a class="button accent folded-corner {0}" href="{1}">{2}</a>';
		$tpl = str_replace('{0}', $class, $tpl);
		$tpl = str_replace('{1}', $target, $tpl);
		$tpl = str_replace('{2}', $label, $tpl);
		echo $tpl;
	}

	private function getStatsLink($seasonid, $type, $teamid = null, $playerid = null ) {
		$url = '?season=' .$seasonid .'&type=' .$type;
		if(!empty($teamid)) {
			$url = $url . '&team=' . $teamid;
		}

		if(!empty($playerid)) {
			$url = $url . '&player=' . $playerid;
		}

		return $url;
	}

	private function eligYearSuffix($numyears)
    {
        if ($numyears > 25)
        {
            return "25th+";
        }
        if($numyears == 0) {
        	return 0;
        }
        switch ($numyears)
        {
            case 1:
                return "1st";
            case 2:
                return "2nd";
            case 3:
                return "3rd";
            case 21:
                return "21st";
            case 22:
                return "22nd";
            case 23:
                return "23rd";
        }
        return $numyears . "th";
    }

    private function eligIntT($numyears)
    {
        switch ($numyears)
        {
            case 0:
            case 1:
                return "Fr";
            case 2:
                return "So";
            case 3:
                return "Jr";
            case 4:
                return "Sr";
            case 5:
                return "5Sr";
        }
        return "5SR+";
    }

    public function showFootballOffensivePassingStats() {
    	$seasonid = $_GET['seasonid'];
		$teamid = $_GET['teamid'];
		$gameid = $_GET['gameid'];
		$playerid = $_GET['playerid'];
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$disablesort = $_GET['disablesort'];

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		$columns = $this->footballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype, null, null, 'passing-stats');
		$stats = $this->api->getFootballOffensivePassingStats($seasonid, $teamid, $playerid);
		$this->footballstats->offensivePassingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, null, $disablesort);
    }

    public function showFootballOffensiveRushingStats() {
    	$seasonid = $_GET['seasonid'];
		$teamid = $_GET['teamid'];
		$gameid = $_GET['gameid'];
		$playerid = $_GET['playerid'];
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$disablesort = $_GET['disablesort'];

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		$columns = $this->footballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype, null, null, 'rushing-stats');
		$stats = $this->api->getFootballOffensiveRushingStats($seasonid, $teamid, $playerid);
		$this->footballstats->offensiveRushingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, null, $disablesort);
    }

    public function showFootballOffensiveReceivingStats() {
    	$seasonid = $_GET['seasonid'];
		$teamid = $_GET['teamid'];
		$gameid = $_GET['gameid'];
		$playerid = $_GET['playerid'];
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$disablesort = $_GET['disablesort'];

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		$columns = $this->footballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype, null, null, 'receiving-stats');
		$stats = $this->api->getFootballOffensiveReceivingStats($seasonid, $teamid, $playerid);
		$this->footballstats->offensiveReceivingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, null, $disablesort);
    }

    public function showFootballOffensiveFieldGoalStats() {
    	$seasonid = $_GET['seasonid'];
		$teamid = $_GET['teamid'];
		$gameid = $_GET['gameid'];
		$playerid = $_GET['playerid'];
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$disablesort = $_GET['disablesort'];

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		$columns = $this->footballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype, null, null, 'field-goal-stats');
		$stats = $this->api->getFootballOffensiveFieldGoalStats($seasonid, $teamid, $playerid);
		$this->footballstats->offensiveFieldGoalStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
    }

    public function showFootballDefensiveDefenseStats() {
    	$seasonid = $_GET['seasonid'];
		$teamid = $_GET['teamid'];
		$gameid = $_GET['gameid'];
		$playerid = $_GET['playerid'];
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$disablesort = $_GET['disablesort'];

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		$columns = $this->footballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype, null, 'defensive', 'defense-stats');
		$stats = $this->api->getFootballDefensiveDefenseStats($seasonid, $teamid, $playerid);
		$this->footballstats->defensiveDefenseStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
    }

    public function showFootballDefensiveInterceptionStats() {
    	$seasonid = $_GET['seasonid'];
		$teamid = $_GET['teamid'];
		$gameid = $_GET['gameid'];
		$playerid = $_GET['playerid'];
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$disablesort = $_GET['disablesort'];

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		$columns = $this->footballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype, null, 'defensive', 'intercept-stats');
		$stats = $this->api->getFootballDefensiveInterceptionStats($seasonid, $teamid, $playerid);
		$this->footballstats->defensiveInterceptionStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
    }

    public function showFootballSpecialTeamPuntingStats() {
    	$seasonid = $_GET['seasonid'];
		$teamid = $_GET['teamid'];
		$gameid = $_GET['gameid'];
		$playerid = $_GET['playerid'];
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$disablesort = $_GET['disablesort'];

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		$columns = $this->footballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype, null, 'special-team', 'punting-stats');
		$stats = $this->api->getFootballSpecialTeamPuntingStats($seasonid, $teamid, $playerid);
		$this->footballstats->specialTeamPuntingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
    }

    public function showFootballSpecialTeamPuntReturnStats() {
    	$seasonid = $_GET['seasonid'];
		$teamid = $_GET['teamid'];
		$gameid = $_GET['gameid'];
		$playerid = $_GET['playerid'];
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$disablesort = $_GET['disablesort'];

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		$columns = $this->footballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype, null, 'special-team', 'punt-return-stats');
		$stats = $this->api->getFootballSpecialTeamPuntReturnStats($seasonid, $teamid, $playerid);
		$this->footballstats->specialTeamPuntReturnStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
    }

    public function showFootballSpecialTeamKickReturnStats() {
    	$seasonid = $_GET['seasonid'];
		$teamid = $_GET['teamid'];
		$gameid = $_GET['gameid'];
		$playerid = $_GET['playerid'];
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$disablesort = $_GET['disablesort'];

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		$columns = $this->footballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype, null, 'special-team', 'kick-return-stats');
		$stats = $this->api->getFootballSpecialTeamKickReturnStats($seasonid, $teamid, $playerid);
		$this->footballstats->specialTeamKickReturnStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
    }

    public function showSoftballHittingStats() {
    	$seasonid = $_GET['seasonid'];
		$teamid = $_GET['teamid'];
		$gameid = $_GET['gameid'];
		$playerid = $_GET['playerid'];
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$disablesort = $_GET['disablesort'];

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		$columns = $this->softballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype);
		$stats = $this->api->getSoftballHittingStats($seasonid, $teamid, $playerid);
		$this->softballstats->setupTab();
			$this->softballstats->hittingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
		$this->softballstats->endDiv();
    }

    public function showSoftballPitchingStats() {
    	$seasonid = $_GET['seasonid'];
		$teamid = $_GET['teamid'];
		$gameid = $_GET['gameid'];
		$playerid = $_GET['playerid'];
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$disablesort = $_GET['disablesort'];

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		$columns = $this->softballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype, null, 'pitching');
		$stats = $this->api->getSoftballPitchingStats($seasonid, $teamid, $playerid);
		$this->softballstats->setupTab();
			$this->softballstats->pitchingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
		$this->softballstats->endDiv();
    }

    public function showBaseballHittingStats() {
    	$seasonid = $_GET['seasonid'];
		$teamid = $_GET['teamid'];
		$gameid = $_GET['gameid'];
		$playerid = $_GET['playerid'];
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$disablesort = $_GET['disablesort'];

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		$columns = $this->baseballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype);
		$stats = $this->api->getBaseballHittingStats($seasonid, $teamid, $playerid);
		$this->baseballstats->setupTab();
			$this->baseballstats->hittingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
		$this->baseballstats->endDiv();
    }

    public function showBaseballPitchingStats() {
    	$seasonid = $_GET['seasonid'];
		$teamid = $_GET['teamid'];
		$gameid = $_GET['gameid'];
		$playerid = $_GET['playerid'];
		$sort = $_GET['sort'];
		$sorttype = $_GET['sorttype'];
		$disablesort = $_GET['disablesort'];

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}
		
		$columns = $this->baseballstats->getStandardColumns($seasonid, $teamid, $playerid, $sort, $sorttype, null, 'pitching');
    	$stats = $this->api->getBaseballPitchingStats($seasonid, $teamid, $playerid);
		$this->baseballstats->setupTab();
			$this->baseballstats->pitchingStats($stats, $columns, $seasonid, $teamid, $playerid, $sort, $sorttype, $disablesort);
		$this->baseballstats->endDiv();
    }
}
