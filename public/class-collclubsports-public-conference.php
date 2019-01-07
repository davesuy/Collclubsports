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
class CollClubSports_Public_Conference {

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
		add_shortcode('conference-list', array($this, 'conference_list'));
		add_shortcode('conference', array($this, 'conference'));
		add_shortcode('conference-standing', array($this, 'conference_standing'));
	}

	private function constructConferenceUrl($seasonid, $conference) {
		$url = $this->options['conference_url'];
		if(!empty($seasonid)) {
			$url = $url . '?season=' . $seasonid;
		}
		if(!empty($seasonid) && !empty($conference)) {
			$url = $url . '&conference=' . $conference->conferenceid;
		}else if(empty($seasonid) && !empty($conference)) {
			$url = $url . '?conference=' . $conference->conferenceid;
		}
		return $url;
	}

	private function constructGameNotesUrl($gameid) {
		return  $this->options['game_notes_url'] . '?game=' . $gameid;
	}

	private function constructExternalUrl($url) {
		$url = str_replace('http://', '', $url);
		$url = str_replace('https://', '', $url);
		return '//' . $url;
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

	private function constructBioPageUrl($id) {
		return $this->options['bio_url'] . '?user=' . $id;
	}

	public function conference_standing($attrs) {
		$conferenceid = $attrs['conference'];
		$seasonid = $attrs['season'];
		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}
		$standings = $this->api->getTeamStandings($seasonid, $conferenceid);
		$this->setupConferencePageStanding($standings, $seasonid);
	}

	public function conference($attrs) {
		$seasonid = $_GET['season'];
		$conferenceid = $_GET['conference'];

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

		$conferences = $this->api->getConferences($seasonid);
		if(empty($conferenceid) && count($conferences) > 0) {
			$conferenceid = $conferences[0]->conferenceid;
		}
		$conference = $this->api->getConferenceInfo($seasonid, $conferenceid);
		echo '<div class="conference-page collclubsports-page-wrapper">';
			$this->setupConferencePageOptions($seasons, $conferences);
			$this->setupConferencePageInfo($conference, $seasonid, $conferenceid);
			echo '<div>';
				$this->setupConferencePageStanding($conference->teamstanding, $seasonid);
				do_shortcode('[stats-tracker season="' . $seasonid . '" conference="' . $conferenceid . '" title="Conference Stat Tracker"]');
				$this->setupConferencePageSchedule($seasonid, $conference->schedules);
			echo '</div>';
			$this->setupConferencePageCoordinator($conference);
		echo '</div>';
	}

	private function setupConferencePageInfo($conference, $seasonid, $conferenceid) { ?>
		<h3 class="conference-section-title"><?php echo $conference->conferencename; ?></h3>
		<div class="conference-info-wrapper">
			<div class="conference-profile-logo-url">
				<?php if(!empty($conference->logourl)) { ?>
					<img src="<?php echo $conference->logourl; ?>">
				<?php } 
				else {?>
					<div class="no-image"></div>
				<?php } ?>
			</div>
			<div class="conference-player-week">
				<?php $this->setupConferencePagePlayerWeek($seasonid, $conferenceid); ?>
			</div>
		</div>
	<?php }

	private function setupConferencePagePlayerWeek($seasonid, $conferenceid) {
		switch($this->options['sport_type']) {
			case 1: { // Baseball
				$shortcode = '[player-week type="1" season="' . $seasonid . '" conference="' . $conferenceid . '" title="Player of the Week"]';
				$shortcode2 = '[player-week type="5" season="' . $seasonid . '" conference="' . $conferenceid . '" title="Pitcher of the Week"]';
				break;
			}
			case 2: { // Softball
				$shortcode = '[player-week type="1" season="' . $seasonid . '" conference="' . $conferenceid . '" title="Player of the Week"]';
				$shortcode2 = '[player-week type="5" season="' . $seasonid . '" conference="' . $conferenceid . '" title="Pitcher of the Week"]';
				break;
			}
			case 3: { // Basketball
				$shortcode = '[player-week type="1" season="' . $seasonid . '" conference="' . $conferenceid . '" title="Player of the Week"]';
				$shortcode2 = '[player-week type="2" season="' . $seasonid . '" conference="' . $conferenceid . '" title="Player of the Week"]';
				break;
			}
			case 4: { // Football
				$shortcode = '[player-week type="3" season="' . $seasonid . '" conference="' . $conferenceid . '" title="Offensive Player of the Week"]';
				$shortcode2 = '[player-week type="4" season="' . $seasonid . '" conference="' . $conferenceid . '" title="Defensive Player of the Week"]';
				break;
			}
			case 5: { // TrackAndField
				$shortcode = '[player-week type="6" season="' . $seasonid . '" conference="' . $conferenceid . '" title="Male Athlete of the Week"]';
				$shortcode2 = '[player-week type="7" season="' . $seasonid . '" conference="' . $conferenceid . '" title="Female Athlete of the Week"]';
				break;
			}
		}
		echo do_shortcode($shortcode);
		echo do_shortcode($shortcode2);
	}

	private function setupConferencePageOptions($seasons, $conferences) { ?>
		<div class="player-option-wrapper">
				<div class="form-group select-field">
					<label for="select-season">Select Season</label>
					<select class="form-control" id="select-season">
						<?php for($i = 0; $i < sizeof($seasons);$i++){
							$season = $seasons[$i]; ?>
							<option value="<?php echo $season->seasonid;?>"><?php echo $season->seasonname;?></option>
						<?php }?>
					</select>
				</div>
				<div class="form-group select-field">
					<label for="select-conference">Select Conference</label>
					<select class="form-control" id="select-conference">
						<?php for($i = 0; $i < sizeof($conferences);$i++){
							$conference = $conferences[$i]; ?>
							<option value="<?php echo $conference->conferenceid;?>"><?php echo $conference->conferencename;?></option>
						<?php }?>
					</select>
				</div>
			</div>
	<?php }

	public function setupConferencePageStanding($teamstandings, $seasonid = null) { 
		?>
		<div class="collclubsports-component box-wrapper team-standing-box-wrapper">
			<div class="box-title">Conference Standings</div>
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
											<div class="image" style="background-image: url(<?php echo $teamstanding->teamlogourl; ?>)"></div>
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

	private function setupConferencePageSchedule($seasonid, $schedules) {
		$weeks = $this->api->getWeeks($seasonid); ?>
		<div class="collclubsports-component box-wrapper schedule-box-wrapper">
			<div class="box-title">Conference Schedule</div>
			<div class="schedule-wrapper box-body-wrapper">
				<div class="description">
					<span class="primary-color">***</span> 
					<span>&nbsp;indicates conference game</span>
				</div>
			<?php
				for($i = 0; $i < sizeof($weeks);$i++) {
					 $hasSchedule = false;
					$week = $weeks[$i];
					$startdate = new DateTime($week->startdate);
					$enddate = new DateTime($week->enddate);
					$weekname = 'Week ' . $week->ordinalnumber . ' ' . $startdate->format('m/d/Y') . ' - ' . $enddate->format('m/d/Y');
					?>
					<div class="schedule-item-wrapper">
						<label class="schedule-week-detail">
							<?php echo $weekname; ?>
						</label>
						<?php for($j = 0; $j < sizeof($schedules);$j++) {
							$schedule = $schedules[$j];
							if($schedule->weekid == $week->weekid) { $hasSchedule = true;?>
								<div class="schedule-game-detail">
									<div style="margin-bottom: 10px;">
										<?php if($schedule->isconference) { ?>
											<span class="primary-color">***</span>
										<?php } ?>
										<?php 
											if(!empty($schedule->visitingteam->teamname)) { ?>
												<a class="schedule-team-name primary-color"
													href="<?php echo $this->constructTeamUrl($schedule->visitingteam->teamid, $seasonid);?>">
													<?php 
														if($schedule->visitingteam->isprobation) {
															echo '*';
														}
														echo $schedule->visitingteam->teamname; 
														echo ' - ' . $schedule->visitingteamscore;
													?>
												</a>
											<?php } else { ?>
												<span class="schedule-team-name primary-color">
													<?php 
														echo $schedule->visitingteamwritein; 
														echo ' - ' . $schedule->visitingteamscore;
													?>
												</span>
											<?php }
										?>
										<span>&nbsp;@&nbsp;</span>
										<?php 
											if(!empty($schedule->hometeam->teamname)) { ?>
												<a class="schedule-team-name primary-color"
													href="<?php echo $this->constructTeamUrl($schedule->hometeam->teamid, $seasonid);?>">
													<?php 
														if($schedule->hometeam->isprobation) {
															echo '*';
														}
														echo $schedule->hometeam->teamname;
														echo ' - ' . $schedule->hometeamscore;
													?>
												</a>
											<?php } else { ?>
												<span class="schedule-team-name primary-color">
													<?php 
														echo $schedule->hometeamwritein; 
														echo ' - ' . $schedule->hometeamscore;
													?>
												</span>
											<?php }
										?>
										
										<?php if($schedule->isforfeit) { ?>
											<div class="warn">
												<label>Forfeit</label>
											</div>
										<?php } ?>
									</div>
									<a class="button accent folded-corner" 
										href="<?php echo $this->constructGameNotesUrl($schedule->gameid);?>">Game Notes/Stats</a>
								</div>
								
							<?php }
						?>
						<?php } ?>
						<?php if(!$hasSchedule) {?>
							<div class="no-schedule">No Games Scheduled</div>
						<?php } ?>
					</div>
					
			<?php }
			echo '</div>';
		echo '</div>';
	}

	private function setupConferencePageCoordinator($conference) { ?>
		<br/><h3 class="conference-section-title">Conference Coordinator</h3>
		<div class="conference-info-wrapper">
			<?php if(!empty($conference->coordinator)) { ?>
				<div class="conference-profile-logo-url">
					<?php if(!empty($conference->coordinator->profilepicture)) { ?>
						<img src="<?php echo $conference->coordinator->profilepicture; ?>">
					<?php } 
					else {?>
						<div class="no-image"></div>
					<?php } ?>
				</div>
				<div class="conference-coordinator">
					<p>Coordinator: 
						<span class="primary-color">
							<?php echo $conference->coordinator->firstname . ' ' . $conference->coordinator->lastname; ?>
						</span>
					</p>
					<p>E-mail: <a class="primary-color" href="mailto:<?php echo $conference->coordinator->username;?>"><?php echo $conference->coordinator->username;?></a></p>
					<a class="button" href="<?php echo $this->constructBioPageUrl($conference->coordinator->userid); ?>">
						View Bio
					</a>
				</div>
			<?php } ?>
		</div>
	<?php }

	public function conference_list($attrs) {
		$seasonid = $_GET['season'];
		$conferenceid = $_GET['conference'];
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
		$conferences = $this->api->getConferences($seasonid);
		?>
		<div class="conference-list-page collclubsports-page-wrapper">
			<div class="conference-option-wrapper">
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
			
			<div class="conference-list-wrapper">
				<?php for($i = 0; $i < sizeof($conferences);$i++){ 
					$conference = $conferences[$i];
				?>
					<a class="conference-list-item-wrapper primary-color"
						href="<?php echo $this->constructConferenceUrl($seasonid, $conference);?>" >
						<div class="conference-logo-wrapper">
							<?php if(!empty($conference->logourl)) { ?>
								<img src="<?php echo $conference->logourl; ?>"/>
							<?php } 
							else { ?> 
								<div class="no-image"></div>
							<?php }?>
						</div>
						<div class="conference-name"><?php echo $conference->conferencename;?></div>
					</a>
				<?php } ?>
			</div>
		</div>
		<?php
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
