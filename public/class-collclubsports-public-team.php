<?php
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
class CollClubSports_Public_Team {

	protected $api = false;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct($api) {
		$this->api = $api;
		$this->register_shortcodes();
		$this->options = get_option('collclubsports_admin');
		$this->conference = new CollClubSports_Public_Conference($this->api);
		$this->schedule = new CollClubSports_Public_Schedule($this->api);
		$this->player = new CollClubSports_Public_Player($this->api);
		$this->stats = new CollClubSports_Public_Stats($this->api);
		$this->baseballstats = new CollClubSports_Public_Baseball_Stats($this->api);
		$this->softballstats = new CollClubSports_Public_Softball_Stats($this->api);
		$this->basketballstats = new CollClubSports_Public_Basketball_Stats($this->api);
		$this->footballstats = new CollClubSports_Public_Football_Stats($this->api);
	}

	private function register_shortcodes() {
		add_shortcode('team-list', array($this, 'team_list'));
		add_shortcode('standing', array($this, 'standing'));
		add_shortcode('team-information', array($this, 'team_information'));
		add_shortcode('team', array($this, 'team'));
		add_shortcode('league-team-information', array($this, 'league_team_information'));
	}

	private function constructConferenceUrl($seasonid, $conference, $conferenceid) {
		$url = $this->options['conference_url'];
		if(!empty($seasonid)) {
			$url = $url . '?season=' . $seasonid;
		}
		if (!empty($conference)) {
			$conferenceid = $conference->conferenceid;
		}
		if(!empty($seasonid) && !empty($conferenceid)) {
			$url = $url . '&conference=' . $conferenceid;
		}else if(empty($seasonid) && !empty($conferenceid)) {
			$url = $url . '?conference=' . $conferenceid;
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

	private function constructPlayerUrl($playerid, $seasonid = null) {
		$url = $this->options['player_url'] . '?player=' . $playerid;

		if(!empty($seasonid)) {
			$url = $url . '&season=' . $seasonid;
		}
		return $url;
	}

	private function construcTeamPlayerUrl($seasonid, $team) {
		$url = $this->options['player_list_url'];
		if(!empty($seasonid)) {
			$url = $url . '?season=' . $seasonid;
		}
		if(!empty($seasonid) && !empty($team)) {
			$url = $url . '&team=' . $team->teamid;
		}else if(empty($seasonid) && !empty($team)) {
			$url = $url . '?team=' . $team->teamid;
		}
		return $url;
	}

	private function construcTeamStatsUrl($seasonid, $team) {
		$url = $this->options['stats_url'];
		if(!empty($seasonid)) {
			$url = $url . '?season=' . $seasonid;
		}
		if(!empty($seasonid) && !empty($team)) {
			$url = $url . '&team=' . $team->teamid;
		}else if(empty($seasonid) && !empty($team)) {
			$url = $url . '?team=' . $team->teamid;
		}
		return $url;
	}

	private function constructExternalUrl($url) {
		$url = str_replace('http://', '', $url);
		$url = str_replace('https://', '', $url);
		return '//' . $url;
	}

	private function constructGameNotesUrl($gameid) {
		return  $this->options['game_notes_url'] . '?game=' . $gameid;
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

    private function constructSortUrl($key, $sorttype = null, $seasonid = null, $teamid = null, $playerid = null, $type = null) {
		$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
		$url = $uri_parts[0] . '?sort=' . $key;

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

	public function team_information() {
		ob_start();
		$season = $this->api->getCurrentSeason();
		$seasonid = $season->seasonid;
		$conferences = $this->api->getConferences($seasonid);
		$teams = $this->api->getTeams($seasonid);
		$league = $this->api->getLeague();
		?>
		<div class="collclubsports-component team-information">
			<h5 ><?php echo $league->leaguealias; ?> TEAM INFORMATION</h5>
			<div class="team-information-items-wrapper">
				<?php for($i = 0; $i < sizeof($conferences);$i++){
					$conference = $conferences[$i];
				?>
					<div class="team-item-details">
						<a class="button"
							href="<?php echo $this->constructConferenceUrl($seasonid, $conference, null);?>"
						><?php echo $conference->conferencename; ?></a>
						<ul>
							<?php for($j = 0; $j < sizeof($teams);$j++){
								$team = $teams[$j];
								if($team->conferenceid == $conference->conferenceid) {?>
									<li>
										<a href="<?php
											echo $this->constructTeamUrl($team->teamid, $seasonid);?>"
										>
											<?php
												if($team->isprobation) {
													echo '*';
												}
												echo $team->teamname;
											?>
										</a>
									</li>
								<?php }
							?>
							<?php }?>
						</ul>
					</div>
					<?php if($i%3 == 0) { ?>
						<div class="col-md-12">
							<hr>
						</div>
					<?php }?>
				<?php }?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function standing($attrs) {
		$seasonid = $_GET['season'];
		$seasons = $this->api->getSeasons();

		for($i = 0; $i < sizeof($seasons);$i++){
			$season = $seasons[$i];
		    if ($season->iscurrentseason==true) {
		    	if(empty($seasonid)) {
					$seasonid = $season->seasonid;
				}
		        break;
		    }
		}
		$standings = $this->api->getTeamStandings($seasonid);
		?>
		<div class="team-standing-page collclubsports-page-wrapper">
			<div class="collclubsports-option-wrapper">
				<div class="form-group select-field">
					<label for="select-season">Select Season</label>
					<select class="form-control" id="select-season">
						<?php for($i = 0; $i < sizeof($seasons);$i++){
							$season = $seasons[$i]; ?>
							<option value="<?php echo $season->seasonid;?>"><?php echo $season->seasonname;?></option>
						<?php }?>
					</select>
				</div>
			</div>
			<div class="team-standing-list-wrapper">
				<?php
				for($i = 0; $i < sizeof($standings);$i++){
					$standing = $standings[$i];
				    $this->setupStanding($standing->teamstandings, $standing->conference->conferencename, $seasonid);
				}
				?>
			</div>
		</div>
	<?php
	}

	public function team($attrs) {
		wp_enqueue_script( $this->plugin_name . '-angularjs', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular.min.js');
		wp_enqueue_script( $this->plugin_name . '-textAngular-rangy', 'https://cdnjs.cloudflare.com/ajax/libs/textAngular/1.3.0/dist/textAngular-rangy.min.js');
		wp_enqueue_script( $this->plugin_name . '-textAngular-sanitize', 'https://cdnjs.cloudflare.com/ajax/libs/textAngular/1.3.0/dist/textAngular-sanitize.min.js');
		wp_enqueue_script( $this->plugin_name . '-textAngular', 'https://cdnjs.cloudflare.com/ajax/libs/textAngular/1.3.0/dist/textAngular.min.js');
		wp_enqueue_script( $this->plugin_name . '-module', plugin_dir_url( __FILE__ ) . 'js/collclubsports-angular.js');
		wp_enqueue_script( $this->plugin_name . '-ajax', plugin_dir_url( __FILE__ ) . 'js/collclubsports-team-page.js', array('jquery'), $this->version, false );
		$teamid = $_GET['team'];
		$seasonid = $_GET['season'];
		$seasons = $this->api->getSeasons();

		if (!empty($teamid)) {
			$teamdetails = $this->api->getTeamPageDetails($seasonid, $teamid);
		}

		for ($i = 0; $i < sizeof($seasons); $i++) {
			$season = $seasons[$i];
		    if ($season->iscurrentseason==true) {
		    	if(empty($seasonid)) {
					$seasonid = $season->seasonid;
				}
		        break;
		    }
		}

		if (empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		if (isset($teamdetails) && !empty($teamdetails)) {
			$teamdetails = $this->api->getTeamPageDetails($seasonid, $teamid);
			$sporttype = $teamdetails->sporttype;
			$sportname = '';
			switch($sporttype) {
				case 1:
					$sportname = 'Baseball';
					break;
				case 2:
					$sportname = 'Softball';
					break;
				case 3:
					$sportname = 'Basketball';
					break;
				case 4:
					$sportname = 'Football';
					break;
			}
			$team = $teamdetails->team;

			wp_localize_script( $this->plugin_name . '-ajax',
				'collclubsports',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'sport_type' => $sporttype
				)
			);

			$teamStyling = '';
	 		if (!empty($team->TeamColor) && $team->TeamColor !== '#') {
				$teamStyling .=
				  '.header .header-container .header-menu .header-menu-items li a:hover,'
				. '.team-page.collclubsports-page-wrapper .primary-color,'
				. '.team-page.collclubsports-page-wrapper a.primary-color,'
				. '.team-page.collclubsports-page-wrapper .stats-table td a,'
				. '.team-page .team-page-button-wrapper .team-page-button,'
				. '.team-page .team-page-button-wrapper .team-social-media {'
				. '		color: ' . $team->TeamColor . ' !important;'
				. '}'
				. '.full_width,'
				. '.page-container .league-page-main-container .page-heading,'
				. '.team-page.collclubsports-page-wrapper .button.accent,'
				. '.team-page.collclubsports-page-wrapper .box-title,'
				. '.team-page.collclubsports-page-wrapper .stats-table thead {'
				. '		background-color: ' . $team->TeamColor . ' !important;'
				. '}'
				. '.team-page.collclubsports-page-wrapper .box-wrapper {'
				. '		border: 1px solid ' . $team->TeamColor . ' !important;'
				. '}';
			}  ?>
			<style type="text/css"><?php echo $teamStyling; ?></style>
			<div class="team-page collclubsports-page-wrapper">
				<?php if($team->logourl) { ?>
				<div class="team-title-logo-wrapper">
					<?php if(!empty($team->logourl)) {?>
						<div class="team-logo-wrapper">
							<img src="<?php echo $team->logourl;?>"/>
						</div>
						<div class="team-title-wrapper">

							<h1 class="primary-color">
							<?php
								if ($team->isprobation)
									echo '*';
								echo $team->teamname . ' Club ' . $sportname;
							?>
							</h1>
						</div>
					<?php } else {?>
						<h1 class="primary-color">
						<?php
							if ($team->isprobation)
								echo '*';
							echo $team->teamname . ' Club ' . $sportname;
						?>
						</h1>
					<?php } ?>
				</div>
				<?php } else { ?>
				<h1 class="primary-color"><?php
							if($team->isprobation) {
								echo '*';
							}
							echo $team->teamname;?></h1>
				<?php } ?>
			<div class="team-page-detail collclubsports-page-wrapper">
			<div class="team-option-wrapper">
				<div class="team-page-button-wrapper team-page-subpage">
					<div target="team-main"
						class="back-main-page-icon team-page-button back-main-button"
						data-toggle="tooltip"
						data-placement="top"
						title="Back to main">
						<i class="fa fa-arrow-left" aria-hidden="true"></i>
					</div>
					<div target="team-schedule"
						class="schedule-icon team-page-button"
						data-toggle="tooltip"
						data-placement="top"
						title="Go to team schedule">
						<i class="fa fa-calendar" aria-hidden="true"></i>
						<span>Schedule</span>
					</div>
					<div target="team-player"
						class="player-icon team-page-button"
						data-toggle="tooltip"
						data-placement="top"
						title="Go to team roster">
						<i class="fa fa-user" aria-hidden="true"></i>
						<span>Roster</span>
					</div>
					<div target="team-stats"
						class="stats-icon team-page-button"
						data-toggle="tooltip"
						data-placement="top"
						title="Go to team stats">
						<i class="fa fa-line-chart" aria-hidden="true"></i>
						<span>Stats</span>
					</div>
                    <?php if(!empty($team->facebookurl)) { ?>
						<a class="team-social-media facebook-icon"
							target="_blank"
							data-toggle="tooltip"
							data-placement="top"
							title="Go to team facebook account"
							href="<?php echo $this->constructExternalUrl($team->facebookurl);?>">
							<i class="fa fa-facebook" aria-hidden="true"></i>
							<span>Facebook</span>
						</a>
                    <?php } ?>
                    <?php if(!empty($team->twitterurl)) { ?>
						<a class="team-social-media twitter-icon"
							target="_blank"
							data-toggle="tooltip"
							data-placement="top"
							title="Go to team twitter account"
							href="<?php echo $this->constructExternalUrl($team->twitterurl);?>">
							<i class="fa fa-twitter" aria-hidden="true"></i>
							<span>Twitter</span>
						</a>
                    <?php } ?>
                    <?php if(!empty($team->siteurl)) { ?>
						<a class="team-social-media web-icon"
							target="_blank"
							data-toggle="tooltip"
							data-placement="top"
							title="Go to team web page"
							href="<?php echo $this->constructExternalUrl($team->siteurl);?>">
							<i class="fa fa-globe" aria-hidden="true"></i>
							<span>Team Webpage</span>
						</a>
                    <?php } ?>
				</div>
				<div class="form-group select-field">
					<label for="select-season">Select Season</label>
					<select class="form-control" id="select-season">
						<option value="">Select Season</option>
						<?php for($i = 0; $i < sizeof($seasons);$i++){
							$season = $seasons[$i]; ?>
							<option value="<?php echo $season->seasonid;?>"><?php echo $season->seasonname;?></option>
						<?php }?>
					</select>
				</div>
			</div>
			</div>
				<div class="team-page-detail-wrapper">
					<div class="team-page-button-wrapper">

						<br>
					</div>
					<div class="collclubsports-pagination">
						<div name="team-main" class="team-content-wrapper">
							<?php if($team->content) { ?>
							    <br/>
								<div class="team-content" ng-app="ccs" ng-controller="CCSCtrl">
									<div class="html-editor" ng-show="show" id="ccs-content"
										ta-bind ng-model="htmlContent"><?php echo $team->content; ?></div>
								</div>
							<?php } ?>
							<div class="team-box-items-wrapper">
								<div class="team-standing-tracker-wrapper">
									<?php $this->conference->setupConferencePageStanding($teamdetails->teamstanding, $seasonid); ?>
									<div id="stats-tracker-container" class="collclubsports-component box-wrapper stats-tracker-box-wrapper">
										<div class="box-title">Team Stats Tracker</div>
										<div class="schedule-wrapper box-body-wrapper">
											<div class="placeholder">Loading items</div>
										</div>
									</div>
								</div>
								<div class="team-player-week">
									<?php $this->setupTeamPagePlayerWeek($teamdetails->playeroftheweek); ?>
								</div>
							</div>
						</div>
						<div name="team-schedule" class="team-content-wrapper">
							<div class="collclubsports-component box-wrapper ">
								<div class="box-title">Team Schedule</div>
								<div class="box-body-wrapper">
									<?php $this->schedule->setupSchedule($teamdetails->gameschedule, $seasonid); ?>
								</div>
							</div>
						</div>
						<div name="team-player" class="team-content-wrapper">
							<div class="collclubsports-component box-wrapper">
								<div class="box-title">Team Roster</div>
								<div class="box-body-wrapper">
									<?php $this->player->setupPlayers($seasonid, $teamid, $teamdetails->players); ?>
								</div>
							</div>
						</div>
						<div name="team-stats" class="team-content-wrapper">
							<div class="collclubsports-component box-wrapper ">
								<div class="box-title">Team Stats</div>
								<div class="box-body-wrapper team-page-stats-tab">
									<?php
										switch($sporttype) {
											case 1: {
									?>
												<div class="tab-button-wrapper">
													<div class="button accent folded-corner baseball-stats-button"
														target="baseball-hitting-stats-table">Hitting Stats</div>
													<div class="button accent folded-corner baseball-stats-button"
														target="baseball-pitching-stats-table">Pitching Stats</div>
												</div>
												<div name="baseball-hitting-stats-table" class="team-stats-table">
									<?php
												$columns = $this->baseballstats->getStandardColumns($seasonid, $teamid, null, null, null);
												$this->baseballstats->setupTab();
													$this->baseballstats->hittingStats($teamdetails->baseballoffensivestats, $columns, $seasonid, $teamid, null, null, null, true);
												$this->baseballstats->endDiv();
									?>
												</div>
												<div name="baseball-pitching-stats-table" class="team-stats-table">
									<?php
												$columns = $this->baseballstats->getStandardColumns($seasonid, $teamid, null, null, null);
												$this->baseballstats->setupTab();
													$this->baseballstats->pitchingStats($teamdetails->baseballpitchingstats, $columns, $seasonid, $teamid, null, null, null, true);
												$this->baseballstats->endDiv();
									?>
												</div>
									<?php
												break;
											}
											case 2: {
									?>
												<div class="tab-button-wrapper">
													<div class="button accent folded-corner softball-stats-button"
														target="softball-hitting-stats-table">Hitting Stats</div>
													<div class="button accent folded-corner softball-stats-button"
														target="softball-pitching-stats-table">Pitching Stats</div>
												</div>
												<div name="softball-hitting-stats-table" class="team-stats-table">
									<?php
												$columns = $this->softballstats->getStandardColumns($seasonid, $teamid, null, null, null);
												$this->softballstats->setupTab();
													$this->softballstats->hittingStats($teamdetails->softballoffensivestats, $columns, $seasonid, $teamid, null, null, null, true);
												$this->softballstats->endDiv();
									?>
												</div>
												<div name="softball-pitching-stats-table" class="team-stats-table">
									<?php
												$columns = $this->softballstats->getStandardColumns($seasonid, $teamid, null, null, null);
												$this->softballstats->setupTab();
													$this->softballstats->pitchingStats($teamdetails->softballpitchingstats, $columns, $seasonid, $teamid, null, null, null, true);
												$this->softballstats->endDiv();
									?>
												</div>
									<?php
												break;
											}
											case 3: {
												$columns = $this->basketballstats->getStandardColumns($seasonid, $teamid, null, null, null);
												$this->basketballstats->basketballStats($teamdetails->basketballstats, $columns, $seasonid, $teamid, null, null, null, true);
												break;
											}
											case 4: {
									?>
												<div class="tab-button-wrapper">
													<div class="button accent folded-corner football-stats-button"
														target="football-offensive-stats-table">Offensive Stats</div>
													<div class="button accent folded-corner football-stats-button"
														target="football-defensive-stats-table">Defensive Stats</div>
													<div class="button accent folded-corner football-stats-button"
														target="football-special-team-stats-table">Special Team Stats</div>
												</div>
												<div name="football-offensive-stats-table" class="team-stats-table">
									<?php
												$columns = $this->footballstats->getStandardColumns($seasonid, $teamid, null, null, null);
												$this->footballstats->setupOffensiveStats($teamdetails->footballoffensivestats, $columns, $seasonid, $teamid, null, null, null);
									?>
												</div>
												<div name="football-defensive-stats-table" class="team-stats-table">
									<?php
												$columns = $this->footballstats->getStandardColumns($seasonid, $teamid, null, null, null);
												$this->footballstats->setupDefensiveStats($teamdetails->footballdefensivestats, $columns, $seasonid, $teamid, null, null, null);
									?>
												</div>
												<div name="football-special-team-stats-table" class="team-stats-table">
									<?php
												$columns = $this->footballstats->getStandardColumns($seasonid, $teamid, null, null, null);
												$this->footballstats->setupSpecialTeamStats($teamdetails->footballspecialteamstats, $columns, $seasonid, $teamid, null, null, null);
									?>
												</div>
									<?php
												break;
											}
											case 5: {
												break;
											}
										}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php } else { ?>
			<div class="team-page-detail collclubsports-page-wrapper">
				<div class="team-option-wrapper">
					<div class="form-group select-field">
						<label for="select-season">Select Season</label>
						<select class="form-control" id="select-season">
							<option value="">Select Season</option>
							<?php for($i = 0; $i < sizeof($seasons);$i++){
								$season = $seasons[$i]; ?>
								<option value="<?php echo $season->seasonid;?>"><?php echo $season->seasonname;?></option>
							<?php }?>
						</select>
					</div>
				</div>
			</div>
			<h3 style="padding-left: 50px;">Team not found in the selected season.</h3>
		<?php }
	}

	public function team_list($attrs) {
		$seasonid = $_GET['season'];
		$conferenceid = $_GET['conference'];
		$all = $_GET['all'];
		$seasons = $this->api->getSeasons();

		for($i = 0; $i < sizeof($seasons);$i++){
			$season = $seasons[$i];
		    if ($season->iscurrentseason==true) {
		    	if(empty($seasonid)) {
					$seasonid = $season->seasonid;
				}
		        break;
		    }
		}

		if($all) {
			$teams = $this->api->getTeams();
		} else {
			$teams = $this->api->getTeams($seasonid, $conferenceid);
		}
		$conferences = $this->api->getConferences($seasonid);
		for($i = 0; $i < sizeof($teams);$i++){
			$team = $teams[$i];
		    for($j = 0; $j < sizeof($conferences);$j++){
		    	if($conferences[$j]->conferenceid == $team->conferenceid) {
		    		$teams[$i]->conference = $conferences[$j];
		    		break;
		    	}
		    }
		}
		?>
		<div class="team-list-page collclubsports-page-wrapper">
			<div class="team-option-wrapper">
				<div class="form-group select-field">
					<label for="select-season">Select Season</label>
					<select class="form-control" id="select-season">
						<option value="">Select Season</option>
						<?php for($i = 0; $i < sizeof($seasons);$i++){
							$season = $seasons[$i]; ?>
							<option value="<?php echo $season->seasonid;?>"><?php echo $season->seasonname;?></option>
						<?php }?>
					</select>
				</div>
				<div class="form-group select-field">
					<label for="select-conference">Sort by Conference</label>
					<select class="form-control" id="select-conference">
						<option value="">Select Conference</option>
						<?php for($i = 0; $i < sizeof($conferences);$i++){
							$conference = $conferences[$i]; ?>
							<option value="<?php echo $conference->conferenceid;?>"><?php echo $conference->conferencename;?></option>
						<?php }?>
					</select>
				</div>
				<div class="form-group">
					<a href="<?php echo $this->options['team_list_url'] .'?all=true'?>" class="primary-color" id="button-view-all">View all teams</a>
				</div>
			</div>

			<div class="team-list-wrapper">
				<?php if(sizeof($teams) == 0) {
					echo '<div class="no-item">No Teams Available</div>';
				}?>
				<?php for($i = 0; $i < sizeof($teams);$i++){
					$team = $teams[$i];
				?>
					<div class="team-list-item-wrapper">
						<div class="team-logo-wrapper">
							<?php if(!empty($team->logourl)) { ?>
								<div class="image" style="background-image: url('<?php echo $team->logourl; ?>')"></div>
							<?php }
							else { ?>
								<div class="no-image"></div>
							<?php }?>
						</div>
						<div class="team-details-wrapper">
							<div class="team-schoolname"><?php
								if($team->isprobation) {
									echo '*';
								}
								 echo $team->schoolname;?></div>
							<div class="team-detail">
								<label>Nickname: </label>
								<label class="primary-color"><?php
									if($team->isprobation) {
										echo '*';
									}
									echo $team->teamname;?></label>
							</div>
							<div class="team-detail">
								<label>Mascot: </label>
								<label class="primary-color"><?php echo $team->mascot;?></label>
							</div>
							<div class="team-detail">
								<label>Conference: </label>
								<a href="<?php
										echo $this->constructConferenceUrl($seasonid, $team->conference, null);?>"
									class="primary-color"><?php echo $team->conference->conferencename;?></a>
							</div>
							<a class="button"
								href="<?php echo $this->constructTeamUrl($team->teamid, $seasonid);?>">Team Page</a>
							<a class="button accent folded-corner"
								href="<?php echo $this->construcTeamPlayerUrl($seasonid, $team); ?>">Team Roster</a>
							<a class="button accent folded-corner"
								href="<?php echo $this->construcTeamStatsUrl($seasonid, $team); ?>">Team Stats</a>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	private function setupStanding($teamstandings, $title, $seasonid = null) {
		?>
		<div class="collclubsports-component box-wrapper team-standing-box-wrapper">
			<div class="box-title"><?php echo $title; ?></div>
			<div class="conference-standing-wrapper box-body-wrapper">
				<table  class="collclubsports-component table-reponsive">
					<thead>
						<tr>
							<th></th>
							<th colspan="4" class="is-align-left">Conference</th>
							<th colspan="9" class="is-align-left">Overall</th>
						</tr>
						<tr>
							<th>Teams</th>
							<th>W</th>
							<th>L</th>
							<th>T</th>
							<th class="has-padding-right">%</th>
							<th>W</th>
							<th>L</th>
							<th>T</th>
							<th>%</th>
							<th>Home</th>
							<th>Away</th>
							<th>PF</th>
							<th>PA</th>
							<th>Streak</th>
						</tr>
					</thead>
					<tbody>
						<?php for($i = 0; $i < sizeof($teamstandings);$i++){
							$teamstanding = $teamstandings[$i]; ?>
							<tr>
								<td>
									<a class="standing-team-link primary-color"
										href="<?php echo $this->constructTeamUrl($teamstanding->teamid, $seasonid);?>">
										<?php 
										if (!empty($teamstanding->teamlogourl)) { ?>
											<div class="image" style="background-image: url('<?php echo $teamstanding->teamlogourl; ?>')"></div>
										<?php } else { ?>
											<div class="no-image"></div>
										<?php } ?>
										<span>
											<?php if ($teamstanding->isprobation) echo '*';
											echo $teamstanding->teamname; ?>
										</span>
									</a>
								</td>
								<td><?php echo $teamstanding->conferencewinscount; ?></td>
								<td><?php echo $teamstanding->conferencelossescount; ?></td>
								<td><?php echo $teamstanding->conferencetiescount; ?></td>
								<td><?php echo $this->formatPercentage($teamstanding->conferencewinpercentage); ?></td>
								<td><?php echo $teamstanding->overallwinscount; ?></td>
								<td><?php echo $teamstanding->overalllossescount; ?></td>
								<td><?php echo $teamstanding->overalltiescount; ?></td>
								<td><?php echo $this->formatPercentage($teamstanding->overallwinpercentage); ?></td>
								<td><?php echo $teamstanding->overallhomerecord; ?></td>
								<td><?php echo $teamstanding->overallawayrecord; ?></td>
								<td><?php echo $this->formatNumber($teamstanding->overallteamtotalscore); ?></td>
								<td><?php echo $this->formatNumber($teamstanding->overallteamagainsttotalscore); ?></td>
								<td><?php echo $this->formatString($teamstanding->overallgamestreak); ?></td>
							</tr>
						<?php }?>
					</tbody>
				</table>
			</div>
		</div>
	<?php }

	private function setupTeamPagePlayerWeek($players) {
		switch($this->options['sport_type']) {
			case 1: {
				$player1title = "Player of the Week";
				$player2title = "Pitcher of the Week";
				break;
			}
			case 2: {
				$player1title = "Player of the Week";
				$player2title = "Pitcher of the Week";
				break;
			}
			case 3: {
				$player1title = "Player of the Week";
				$player2title = "Player of the Week";
				break;
			}
			case 4: {
				$player1title = "Offensive Player of the Week";
				$player2title = "Defensive Player of the Week";
				break;
			}
			case 5: {
				$player1title = "Male Athlete of the Week";
				$player2title = "Female Athlete of the Week";
				break;
			}
		}
		if (!empty($players)) {
			foreach ($players as $val) {
				if(empty($val->ads)) {
					$this->player->setupPlayerWeek($val, $player1title, $seasonid);
				}
				else {
					$this->player->setupPlayerWeekAds($val->ads, $player2title);
				}
			}
		}
	}

	private function setupStatsTrackerTopStats($seasonid, $topstats) {
		$html = '';
		for($i = 0; $i < sizeof($topstats); $i++) {
			$stat = $topstats[$i];

			$html .= '<div class="top-stats-item-wrapper ';

			if ($i == 0)
				$html .= 'active" style="display: block;">';
			else
				$html .= '">';

			$html .= ('<h3>' . $stat['title'] . '</h3>');
			$html .= ('<table><tbody>');

			for ($j = 0; $j < sizeof($stat['stats']);$j++) {
				$item = $stat['stats'][$j];
				$html .= '<tr>';
				$html .= '<td># ';
					$html .= ($j + 1);
					$html .= '</td>';
				$html .= ('<td><a class="primary-color" href="' . $this->constructPlayerUrl($item->playerid, $seasonid) . '">' . $item->name . '</a></td>');
				$html .= ('<td><a class="primary-color" href="' . $this->constructTeamUrl($item->teamid, $seasonid) . '">');
					if ($item->isprobation) {
						$html .= '*';
					}
					$html .= $item->teamname;
					$html .= ('</a></td>');
				$html .= ('<td>' . number_format($item->total, 2) . '</td>');
				$html .= '</tr>';
			}

			$html .= ('</tbody></table></div>');
		}
		return $html;
	}

	private function setupPlayerWeekAds($ads, $title) {
		$html = '<div class="collclubsports-component box-wrapper player-week-wrapper ads-wrapper">';

		if ( !empty($ads->hyperlink) ) {
			$html .= ('<a href="' . $this->constructExternalUrl($ads->hyperlink) . '"><img src="' . $ads->imageurl . '"></a>');
		} else {
			$html .= ('<img src="' . $ads->imageurl . '">');
		}

		$html .= '</div>';

		return $html;
	}

	private function setupPlayerWeek($playerweek, $title, $seasonid) {
		$html =
		'<div class="collclubsports-component box-wrapper player-week-wrapper">
			<div class="box-title player-week-title"> '
				. $title . '
			</div>
			<div class="box-body-wrapper player-week-body">';

		if (!empty($playerweek)) {
			if (!empty($playerweek->player->primaryposition)) {
				$position = explode("-", $playerweek->player->primaryposition)[0];
				if (!empty($playerweek->player->secondaryposition)) {
					$position = $position . '/' . explode("-", $playerweek->player->secondaryposition)[0];
				}
			}
			if (!empty($playerweek->player->jerseynumber)) {
				$jerseynumber = ' - #' . $playerweek->player->jerseynumber;
			}
			$playername = $playerweek->player->firstname . ' ' . $playerweek->player->lastname . ' ' . $playerweek->player->lastnamesuffix . $jerseynumber  . ' ' . $position;

			$html .= '<p class="player-week-name">' . $playername . '</p>';
			$html .= '<p class="player-week-team">';
			if ($playerweek->player->intendteam->isprobation) {
				$html .= '*';
			}
			$html .= $playerweek->player->intendteam->teamname;
			$html .= '</p>';
			$html .= '<p class="notes">' . $playerweek->notes . '</p>';
			$html .= '<div class="player-week-image-wrapper">';
			if (!empty($playerweek->player->profilepictureurl)) {
				$html .= '<a class="player-profile-wrapper"
					href="' . $this->constructPlayerUrl($playerweek->player->playerid, $playerweek->seasonid) . '">
					<img src="' . $playerweek->player->profilepictureurl . '">
				</a>';
			}
			else {
				$html .= '<a class="player-profile-wrapper"
					href="' . $this->constructPlayerUrl($playerweek->player->playerid, $playerweek->seasonid) . '">
					<div class="no-image"></div>
				</a>';
			}
			if (!empty($playerweek->player->intendteam->logourl)) {
				$html .= '<a class="player-team-wrapper"
					href="' . $this->constructTeamUrl($playerweek->player->intendteam->teamid, $seasonid) . '">
					<img src="' . $playerweek->player->intendteam->logourl . '">
				</a>';
			}
			else {
				$html .= '<a class="player-team-wrapper"
					href="' . $this->constructTeamUrl($playerweek->player->intendteam->teamid, $seasonid)  . '">
						<div class="no-image"></div>
				</a>';
			}
			$html .= '</div>';
		}
		else {
			$html .= '<div class="player-week-none-selected">None selected</div>';
		}

		$html .= '</div></div>';

		return $html;
	}

	public function showConferenceStanding() {
		$teamid = $_GET['teamid'];
		$seasonid = $_GET['seasonid'];

		if( !empty($teamid) ) {
			if(empty($seasonid)) {
				$season = $this->api->getCurrentSeason();
				$seasonid = $season->seasonid;
			}
			$teamdetails = $this->api->getTeam($seasonid, $teamid);
			$team = $teamdetails->team;
			$conferenceid = $teamdetails->conference->conferenceid;

			$teamstandings = $this->api->getTeamStandings($seasonid, $conferenceid);

			$html =
			'<table  class="collclubsports-component table-reponsive">
				<thead>
					<tr>
						<th></th>
						<th colspan="4" class="is-align-left">Conference</th>
						<th colspan="9" class="is-align-left">Overall</th>
					</tr>
					<tr>
						<th>Teams</th>
						<th>W</th>
						<th>L</th>
						<th>T</th>
						<th class="has-padding-right">%</th>
						<th>W</th>
						<th>L</th>
						<th>T</th>
						<th>%</th>
						<th>Home</th>
						<th>Away</th>
						<th>PF</th>
						<th>PA</th>
						<th>Streak</th>
					</tr>
				</thead>
				<tbody>';

			for ($i = 0; $i < sizeof($teamstandings); $i++) {
				$teamstanding = $teamstandings[$i];
				$html .=
					('<tr><td><a class="standing-team-link primary-color"
						href="' . $this->constructTeamUrl($teamstanding->teamid, $seasonid) . '">');
				if (!empty($teamstanding->teamlogourl)) {
					$html .= '<div class="image" style="background-image: url(' . $teamstanding->teamlogourl . ')"></div>';
				} else {
					$html .= '<div class="no-image"></div>';
				}
				if ($teamstanding->isprobation) {
					$html .= '*';
				}
				$html .= $teamstanding->teamname;
				$html .=
					('</a></td>
					<td>' . $teamstanding->conferencewinscount . '</td>
					<td>' . $teamstanding->conferencelossescount . '</td>
					<td>' . $teamstanding->conferencetiescount . '</td>
					<td>' . $this->formatPercentage($teamstanding->conferencewinpercentage) . '</td>
					<td>' . $teamstanding->overallwinscount . '</td>
					<td>' . $teamstanding->overalllossescount . '</td>
					<td>' . $teamstanding->overalltiescount . '</td>
					<td>' . $this->formatPercentage($teamstanding->overallwinpercentage) . '</td>
					<td>' . $teamstanding->overallhomerecord . '</td>
					<td>' . $teamstanding->overallawayrecord . '</td>
					<td>' . $this->formatNumber($teamstanding->overallteamtotalscore) . '</td>
					<td>' . $this->formatNumber($teamstanding->overallteamagainsttotalscore) . '</td>
					<td>' . $this->formatString($teamstanding->overallgamestreak) . '</td>
					</tr>');
			}

			$html .= ('</tbody></table>');

			return $html;
		}
	}

	public function showStatsTracker() {
		$teamid = $_GET['teamid'];
		$seasonid = $_GET['seasonid'];

		if( !empty($teamid) ) {
			if(empty($seasonid)) {
				$season = $this->api->getCurrentSeason();
				$seasonid = $season->seasonid;
			}
			$teamdetails = $this->api->getTeam($seasonid, $teamid);
			$team = $teamdetails->team;
			$conferenceid = $teamdetails->conference->conferenceid;
			$sporttype = $teamdetails->sporttype;

			$topstats = array();
			switch($sporttype) {
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
					break;
				}
				case 5: { // TrackAndField
					break;
				}
			}

			return $this->setupStatsTrackerTopStats($seasonid, $topstats);
		}
	}

	public function showPlayerOfTheWeekAds(){
		$teamid = $_GET['teamid'];
		$seasonid = $_GET['seasonid'];

		if( !empty($teamid) ) {
			if(empty($seasonid)) {
				$season = $this->api->getCurrentSeason();
				$seasonid = $season->seasonid;
			}
			$teamdetails = $this->api->getTeam($seasonid, $teamid);
			$team = $teamdetails->team;
			$conferenceid = $teamdetails->conference->conferenceid;
			$sporttype = $teamdetails->sporttype;

			switch($sporttype) {
				case 1: { // Baseball
					$type1 = '1';
					$type2 = '5';
					$title1 = 'Player of the Week';
					$title2 = 'Pitcher of the Week';
					break;
				}
				case 2: { // Softball
					$type1 = '1';
					$type2 = '5';
					$title1 = 'Player of the Week';
					$title2 = 'Pitcher of the Week';
					break;
				}
				case 3: { // Basketball
					$type1 = '1';
					$type2 = '2';
					$title1 = 'Player of the Week';
					$title2 = 'Player of the Week';
					break;
				}
				case 4: { // Football
					$type1 = '3';
					$type2 = '4';
					$title1 = 'Offensive Player of the Week';
					$title2 = 'Defensive Player of the Week';
					break;
				}
				case 5: { // TrackAndField
					$type1 = '6';
					$type2 = '7';
					$title1 = 'Male Athlete of the Week';
					$title2 = 'Female Athlete of the Week';
					break;
				}
			}

			$html = '';

			$playerweek1 = $this->api->getPlayerOfWeek($seasonid, $type1, $conferenceid, $teamid);
			if ( empty($playerweek1->ads) ) {
				$html .= $this->setupPlayerWeek($playerweek1, $title1, $seasonid);
			}
			else {
				$html .= $this->setupPlayerWeekAds($playerweek1->ads, $title1);
			}

			$playerweek2 = $this->api->getPlayerOfWeek($seasonid, $type2, $conferenceid, $teamid);
			if ( empty($playerweek2->ads) ) {
				$html .= $this->setupPlayerWeek($playerweek2, $title2, $seasonid);
			}
			else {
				$html .= $this->setupPlayerWeekAds($playerweek2->ads, $title2);
			}

			return $html;
		}
	}

	public function showTeamSchedule() {
		$teamid = $_GET['teamid'];
		$seasonid = $_GET['seasonid'];

		if ( !empty($teamid) ) {
			if(empty($seasonid)) {
				$season = $this->api->getCurrentSeason();
				$seasonid = $season->seasonid;
			}
			$weeks = $this->api->getWeeks($seasonid);
			$schedules = $this->api->getSchedules($seasonid, null, null, $teamid);
			for ($i = 0; $i < sizeof($schedules); $i++) {
				$schedule = $schedules[$i];
			    for ($j = 0; $j < sizeof($weeks);$j++) {
			    	if ($weeks[$j]->weekid == $schedule->weekid) {
			    		$schedules[$i]->week = $weeks[$j];
			    		break;
			    	}
			    }
			}

			$html = '<div class="schedule-wrapper">';

			if( sizeof($schedules) == 0) {
				$html .= '<div class="no-schedule">No Games Scheduled</div>';
			} else {
				$html .=
					'<div class="description">
						<span class="primary-color">***</span>
						<span>&nbsp;indicates conference game</span>
					</div>';

				for($i = 0; $i < sizeof($schedules); $i++) {
					$schedule = $schedules[$i];
					$html .= '<div class="schedule-item-wrapper">';
					$html .= '<label class="schedule-week-detail">';
					if (empty($schedule->week->weekname)) {
						$week = $schedule->week;
						$startdate = new DateTime($week->startdate);
						$enddate = new DateTime($week->enddate);
						$weekname = 'Week ' . $week->ordinalnumber . ' '
							. $startdate->format('m/d/Y') . ' - ' . $enddate->format('m/d/Y');
						$html .= $weekname;
					} else {
						$html .= $schedule->week->weekname;
					}
					$html .= '</label>';
					$html .= '<div class="schedule-game-detail"><div class="schedule-team-wrapper">';
					if(!empty($schedule->visitingteam->teamname)) {
						$html .= '<a class="schedule-team-name primary-color"
							href="'. $this->constructTeamUrl($schedule->visitingteam->teamid, $seasonid) . '">';
							if($schedule->visitingteam->isprobation) {
								$html .= '*';
							}
							$html .= $schedule->visitingteam->teamname;
						$html .= '</a>';
					} else {
						$html .= '<span class="schedule-team-name primary-color">' . $schedule->visitingteamwritein . '</span>';
					}
					$html .= '<div class="schedule-team-detail">
									<label>Score: </label>
									<label class="primary-color">' . $schedule->visitingteamscore . '</label>
								</div>
								<div class="schedule-team-detail">
									<label>Conference Record: </label>
									<label class="primary-color">' . $schedule->visitingteam->conferencerecord . '</label>
								</div>
								<div class="schedule-team-detail">
									<label>Overall Record: </label>
									<label class="primary-color">' . $schedule->visitingteam->overallrecord . '</label>
								</div>
								<div class="schedule-team-detail">
									<label>Conference: </label>
									<a class="primary-color"
										href="' . $this->constructConferenceUrl($seasonid, null, $schedule->visitingteam->conferenceid) . '" >'
										. $schedule->visitingteam->conferencename . '
									</a>
								</div>
							</div>
							<div class="schedule-team-wrapper">';
								if ($schedule->isconference) {
									$html .= '<span class="primary-color">***</span>';
								}
								if(!empty($schedule->hometeam->teamname)) {
									$html .= '<a class="schedule-team-name primary-color"
										href="' . $this->constructTeamUrl($schedule->hometeam->teamid, $seasonid) . '">
										@ ';
										if($schedule->hometeam->isprobation) {
											$html .= '*';
										}
										$html .= $schedule->hometeam->teamname;
									$html .= '</a>';
								} else {
									$html .= '<span class="schedule-team-name primary-color">@ ' . $schedule->hometeamwritein . '</span>';
								}
								$html .='<div class="schedule-team-detail">
									<label>Score: </label>
									<label class="primary-color">
										' . $schedule->hometeamscore . '
									</label>
								</div>
								<div class="schedule-team-detail">
									<label>Conference Record: </label>
									<label class="primary-color">' . $schedule->hometeam->conferencerecord . '</label>
								</div>
								<div class="schedule-team-detail">
									<label>Overall Record: </label>
									<label class="primary-color">' . $schedule->hometeam->overallrecord . '</label>
								</div>
								<div class="schedule-team-detail">
									<label>Conference: </label>
									<a class="primary-color" href="' . $this->constructConferenceUrl($seasonid, null, $schedule->hometeam->conferenceid) . '">' . $schedule->hometeam->conferencename . '</a>
								</div>
							</div>
						</div>
						<a class="button accent folded-corner" href="' . $this->constructGameNotesUrl($schedule->gameid) . '">Game Notes/Stats</a>
					</div>';
				}
			}

			$html .= '</div>';

			return $html;
		}
	}

	public function showTeamRoster() {
		$teamid = $_GET['teamid'];
		$seasonid = $_GET['seasonid'];

		if ( !empty($teamid) ) {
			if(empty($seasonid)) {
				$season = $this->api->getCurrentSeason();
				$seasonid = $season->seasonid;
			}
			$players = $this->api->getPlayers($seasonid, $teamid);

			$items = array();
			$columns = array();
			foreach ($players as $val) {
				$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);

				if (!empty($val->heightfeet)) {
					$val->height = $val->heightfeet . "' " . $val->heightinches .'"';
				}

				$val->hometown = $val->highschoolhometown . ', ' . $val->highschoolstate;
				$val->eligyr = $this->eligYearSuffix($val->yrdife) . ' / ' .  $this->eligIntT($val->yrdife);
				$val->dateofbirth = new DateTime($val->dateofbirth);
				$val->dateofbirth = $val->dateofbirth->format('m/Y');
				$val->teamname = $val->intendteam->teamname;

				if ($val->intendteam->isprobation) {
					$val->teamname = '*' . $val->intendteam->teamname;
				}

				if (!empty($val->primaryposition)) {
					$primaryposition = explode("-", $val->primaryposition)[0];
					if(!empty($val->secondaryposition)) {
						$secondaryposition = explode("-", $val->secondaryposition)[0];
					}
					$val->position = $primaryposition . ' / ' . $secondaryposition;
				}

				array_push($items, json_decode(json_encode($val), true));
			}

			array_push($columns, array(
				'label' => '#',
				'key' => 'jerseynumber'
			));
			array_push($columns, array(
				'label' => 'Player',
				'key' => 'name',
				'keylink' => 'playerlink',
				'thclass' => 'name-header'
			));
			array_push($columns, array(
				'label' => 'DOB',
				'key' => 'dateofbirth'
			));
			array_push($columns, array(
				'label' => 'Yr/Elig',
				'key' => 'eligyr'
			));
			array_push($columns, array(
				'label' => 'HT',
				'key' => 'height'
			));
			array_push($columns, array(
				'label' => 'WT',
				'key' => 'weight'
			));
			array_push($columns, array(
				'label' => 'POS',
				'key' => 'position'
			));
			array_push($columns, array(
				'label' => 'Hometown',
				'key' => 'hometown'
			));
			array_push($columns, array(
				'label' => 'High School',
				'key' => 'highschool'
			));

			return new CollClubSports_Table($columns,  $items);
		}
	}

	public function showFootballOffensiveTeamStats() {
		$teamid = $_GET['teamid'];
		$seasonid = $_GET['seasonid'];

		if( !empty($teamid) ) {
			if(empty($seasonid)) {
				$season = $this->api->getCurrentSeason();
				$seasonid = $season->seasonid;
			}

			$columns = $this->getStandardFootballStatsColumns($seasonid, $teamid);
			$offensive_stats = $this->api->getFootballOffensiveStats($seasonid, $teamid);
			$this->footballstats->setupOffensiveStats($offensive_stats, $columns, $seasonid, $teamid, null, null, null, null, true);
		}
	}

	public function showFootballDefensiveTeamStats() {
		$teamid = $_GET['teamid'];
		$seasonid = $_GET['seasonid'];

		if( !empty($teamid) ) {
			if(empty($seasonid)) {
				$season = $this->api->getCurrentSeason();
				$seasonid = $season->seasonid;
			}

			$columns = $this->getStandardFootballStatsColumns($seasonid, $teamid);
			$defensive_stats = $this->api->getFootballDefensiveStats($seasonid, $teamid);
			$this->footballstats->setupDefensiveStats($defensive_stats, $columns, $seasonid, $teamid, null, null, null, true);
		}
	}

	public function showFootballSpecialTeamStats() {
		$teamid = $_GET['teamid'];
		$seasonid = $_GET['seasonid'];

		if( !empty($teamid) ) {
			if(empty($seasonid)) {
				$season = $this->api->getCurrentSeason();
				$seasonid = $season->seasonid;
			}

			$columns = $this->getStandardFootballStatsColumns($seasonid, $teamid);
			$special_team_stats = $this->api->getFootballSpecialTeamStats($seasonid, $teamid);
			$this->footballstats->setupSpecialTeamStats($special_team_stats, $columns, $seasonid, $teamid, null, null, null, true);
		}
	}

	private function getStandardFootballStatsColumns($seasonid, $teamid) {
		return array(
			array(
				'headerlink' => $this->constructSortUrl('name', $this->getSortType('name', null, null), $seasonid, $teamid, null),
				'label' => 'Player',
				'key' => 'name',
				'keylink' => 'playerlink',
				'thclass' => 'name-header'
			),
			array(
				'headerlink' => $this->constructSortUrl('hasplayed', $this->getSortType('hasplayed', null, null), $seasonid, $teamid, null),
				'label' => 'Games Played',
				'key' => 'hasplayed'
			)
		);
	}

	public function showSoftballHittingStats() {
		$teamid = $_GET['teamid'];
		$seasonid = $_GET['seasonid'];

		if( !empty($teamid) ) {
			if(empty($seasonid)) {
				$season = $this->api->getCurrentSeason();
				$seasonid = $season->seasonid;
			}

			$columns = $this->getSoftballStandardColumns($seasonid, $teamid);
			$hitting_stats = $this->api->getSoftballHittingStats($seasonid, $teamid);
			$this->softballstats->setupTab();
				$this->softballstats->hittingStats($hitting_stats, $columns, $seasonid, $teamid, null, null, null, true, null);
			$this->softballstats->endDiv();
		}
	}

	public function showSoftballPitchingStats() {
		$teamid = $_GET['teamid'];
		$seasonid = $_GET['seasonid'];

		if( !empty($teamid) ) {
			if(empty($seasonid)) {
				$season = $this->api->getCurrentSeason();
				$seasonid = $season->seasonid;
			}

			$columns = $this->getSoftballStandardColumns($seasonid, $teamid);
			$pitching_stats = $this->api->getSoftballPitchingStats($seasonid, $teamid);
			$this->softballstats->setupTab();
				$this->softballstats->pitchingStats($pitching_stats, $columns, $seasonid, $teamid, null, null, null, true, null);
			$this->softballstats->endDiv();
		}
	}

	private function getSoftballStandardColumns( $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $gameid = null) {
		return array(
			array(
				'headerlink' => $this->constructSortUrl('name', $this->getSortType('name', $sort, $sorttype), $seasonid, $teamid, $playerid),
				'label' => 'Player',
				'key' => 'name',
				'keylink' => 'playerlink',
				'thclass' => 'name-header'
			)
		);
	}

	public function showBaseballHittingStats() {
		$teamid = $_GET['teamid'];
		$seasonid = $_GET['seasonid'];

		if( !empty($teamid) ) {
			if(empty($seasonid)) {
				$season = $this->api->getCurrentSeason();
				$seasonid = $season->seasonid;
			}

			$columns = $this->getBaseballStandardColumns($seasonid, $teamid);
			$hitting_stats = $this->api->getBaseballHittingStats($seasonid, $teamid);
			$this->baseballstats->setupTab();
				$this->baseballstats->hittingStats($hitting_stats, $columns, $seasonid, $teamid, null, null, null, true, null);
			$this->baseballstats->endDiv();
		}
	}

	public function showBaseballPitchingStats() {
		$teamid = $_GET['teamid'];
		$seasonid = $_GET['seasonid'];

		if( !empty($teamid) ) {
			if(empty($seasonid)) {
				$season = $this->api->getCurrentSeason();
				$seasonid = $season->seasonid;
			}

			$columns = $this->getBaseballStandardColumns($seasonid, $teamid);
			$pitching_stats = $this->api->getBaseballPitchingStats($seasonid, $teamid);
			$this->baseballstats->setupTab();
				$this->baseballstats->pitchingStats($pitching_stats, $columns, $seasonid, $teamid, null, null, null, true, null);
			$this->baseballstats->endDiv();
		}
	}

	private function getBaseballStandardColumns( $seasonid = null, $teamid = null, $playerid = null, $sort = null, $sorttype = null, $gameid = null) {
		return array(
			array(
				'headerlink' => $this->constructSortUrl('name', $this->getSortType('name', $sort, $sorttype), $seasonid, $teamid, $playerid),
				'label' => 'Player',
				'key' => 'name',
				'keylink' => 'playerlink',
				'thclass' => 'name-header'
			)
		);
	}

	public function showBasketballStats() {
		$teamid = $_GET['teamid'];
		$seasonid = $_GET['seasonid'];

		if( !empty($teamid) ) {
			if(empty($seasonid)) {
				$season = $this->api->getCurrentSeason();
				$seasonid = $season->seasonid;
			}

			$columns = $this->basketballstats->getStandardColumns($seasonid, $teamid, null, null, null);
			$stats = $this->api->getBasketballStats($seasonid, $teamid);
			$this->basketballstats->basketballStats($stats, $columns, $seasonid, $teamid, null, null, null, true);
		}
	}

	public function league_team_information() {
		wp_enqueue_script( $this->plugin_name . '-ajax', plugin_dir_url( __FILE__ ) . 'js/collclubsports-league.js', array('jquery'), $this->version, false );

		wp_localize_script( $this->plugin_name . '-ajax',
			'collclubsports',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			)
		);

		$leagues = $this->options['leagues'];
		ob_start();

		if (count($leagues) > 0) :
			$leagueapikey = $leagues[0]['apikey'];
			$leagueid = $leagues[0]['id'];
			$leaguealias = $leagues[0]['alias'];
			$leagueurl = $leagues[0]['url'];
			$leaguecurrentseasonid = $this->getLeagueCurrentSeason($leagueapikey, $leagueid);
			$conferences = $this->getLeagueConferences($leagueapikey, $leaguecurrentseasonid);
			$teams = $this->getLeagueTeams($leagueapikey, $leaguecurrentseasonid); ?>
			<div class="collclubsports-component team-information">
				<h5 ><?php echo $leaguealias; ?> TEAM INFORMATION</h5>
				<div class="team-information-items-wrapper">
					<?php for($i = 0; $i < sizeof($conferences);$i++){
						$conference = $conferences[$i];
					?>
						<div class="team-item-details">
							<a class="button"
								href="<?php echo $leagueurl . $this->constructConferenceUrl($seasonid, $conference, null);?>"
							><?php echo $conference->conferencename; ?></a>
							<ul>
								<?php for($j = 0; $j < sizeof($teams);$j++){
									$team = $teams[$j];
									if($team->conferenceid == $conference->conferenceid) {?>
										<li>
											<a href="<?php
												echo $leagueurl . $this->constructTeamUrl($team->teamid, $seasonid);?>"
											>
												<?php
													if($team->isprobation) {
														echo '*';
													}
													echo $team->teamname;
												?>
											</a>
										</li>
									<?php }
								?>
								<?php }?>
							</ul>
						</div>
						<?php if($i%3 == 0) { ?>
							<div class="col-md-12">
								<hr>
							</div>
						<?php }?>
					<?php }?>
				</div>
			</div>
		<?php endif;

		return ob_get_clean();
	}

	public function showLeagueTeamInformation() {
		$leagues = $this->options['leagues'];
		$leagueid = $_GET['leagueid'];
		if (!empty($leagueid) && !empty($leagues)) {
			foreach ($leagues as $league) {
				if ($league['id'] == $leagueid) {
					$leagueapikey = $league['apikey'];
					$leaguealias = $league['alias'];
					$leagueurl = $league['url'];
				}
			}
			$leaguecurrentseasonid = $this->getLeagueCurrentSeason($leagueapikey, $leagueid);
			$conferences = $this->getLeagueConferences($leagueapikey, $leaguecurrentseasonid);
			$teams = $this->getLeagueTeams($leagueapikey, $leaguecurrentseasonid); ?>
			<div class="collclubsports-component team-information">
				<h5 ><?php echo $leaguealias; ?> TEAM INFORMATION</h5>
				<div class="team-information-items-wrapper">
					<?php for($i = 0; $i < sizeof($conferences);$i++){
						$conference = $conferences[$i];
					?>
						<div class="team-item-details">
							<a class="button"
								href="<?php echo $leagueurl . $this->constructConferenceUrl($seasonid, $conference, null);?>"
							><?php echo $conference->conferencename; ?></a>
							<ul>
								<?php for($j = 0; $j < sizeof($teams);$j++){
									$team = $teams[$j];
									if($team->conferenceid == $conference->conferenceid) {?>
										<li>
											<a href="<?php
												echo $leagueurl . $this->constructTeamUrl($team->teamid, $seasonid);?>"
											>
												<?php
													if($team->isprobation) {
														echo '*';
													}
													echo $team->teamname;
												?>
											</a>
										</li>
									<?php }
								?>
								<?php }?>
							</ul>
						</div>
						<?php if($i%3 == 0) { ?>
							<div class="col-md-12">
								<hr>
							</div>
						<?php }?>
					<?php }?>
				</div>
			</div>
		<?php }
	}

	private function getLeagueCurrentSeason($leagueapikey, $leagueid) {
		$url = $this->options['api_url'] . '/v1/league/{0}/season/current';
		$url = str_replace('{0}', $leagueid, $url);
		$response = wp_remote_get( $url, array(
			'timeout' => 120,
			'httpversion' => '1.1',
			'headers' => array('league-api-key' => $leagueapikey)) );
		if ($response['response']['code'] == 200)
			return json_decode($response['body'])->seasonid;
		else
			return null;
	}

	private function getLeagueConferences($apikey, $seasonid) {
		$url = $this->options['api_url'] . '/v1/season/{0}/conference';
		$url = str_replace('{0}', $seasonid, $url);

		try {
			$response = wp_remote_get( $url, array(
				'timeout' => 120,
				'httpversion' => '1.1',
				'headers' => array('league-api-key' => $apikey)) );
			// Don't throw the error
			if ($response['response']['code'] == 200)
				return json_decode($response['body']);
			else
				return null;
		} catch (HttpException $ex){
			return $ex;
		}
	}

	private function getLeagueTeams($apikey, $seasonid) {
		$url = $this->options['api_url'] . '/v1/season/{0}/team';
		$url = str_replace('{0}', $seasonid, $url);

		try {
			$response = wp_remote_get( $url, array(
				'timeout' => 120,
				'httpversion' => '1.1',
				'headers' => array('league-api-key' => $apikey)) );
			// Don't throw the error
			if ($response['response']['code'] == 200)
				return json_decode($response['body']);
			else
				return null;
		} catch (HttpException $ex){
			return $ex;
		}
	}

	private function formatPercentage($val) {
		$val = round($val, 3);
		return  number_format($val, 3, '.', '');
	}

	private function formatNumber($val) {
		if (empty($val)) {
			return 0;
		} else {
			return $val;
		}
	}

	private function formatString($val) {
		if (empty($val)) {
			return "--";
		} else {
			return $val;
		}
	}
}
