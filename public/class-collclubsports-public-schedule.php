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
class CollClubSports_Public_Schedule {

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
		add_shortcode('schedule', array($this, 'schedule'));
		add_shortcode('schedule-conference', array($this, 'schedule_conference'));
		add_shortcode('schedule-team', array($this, 'schedule_team'));
	}

	private function constructConferenceUrl($seasonid, $conferenceid) {
		$url = $this->options['conference_url'];
		if(!empty($seasonid)) {
			$url = $url . '?season=' . $seasonid;
		}
		if(!empty($seasonid) && !empty($conferenceid)) {
			$url = $url . '&conference=' . $conferenceid;
		}else if(empty($seasonid) && !empty($conferenceid)) {
			$url = $url . '?conference=' . $conferenceid;
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

	public function schedule_conference($attrs) {
		$seasonid = $attrs['season'];
		$conferenceid = $attrs['conference'];
		$weeks = $this->api->getWeeks($seasonid);
		$schedules = $this->api->getSchedules($seasonid, null, $conferenceid);
		for($i = 0; $i < sizeof($schedules);$i++){
			$schedule = $schedules[$i];
		    for($j = 0; $j < sizeof($weeks);$j++){
		    	if($weeks[$j]->weekid == $schedule->weekid) {
		    		$schedules[$i]->week = $weeks[$j];
		    		break;
		    	}
		    }
		}
		$this->setupSchedule($schedules, $seasonid);
	}

	public function schedule_team($attrs) {
		$seasonid = $attrs['season'];
		$teamid = $attrs['team'];
		$weeks = $this->api->getWeeks($seasonid);
		$schedules = $this->api->getSchedules($seasonid, null, null, $teamid);
		for($i = 0; $i < sizeof($schedules);$i++){
			$schedule = $schedules[$i];
		    for($j = 0; $j < sizeof($weeks);$j++){
		    	if($weeks[$j]->weekid == $schedule->weekid) {
		    		$schedules[$i]->week = $weeks[$j];
		    		break;
		    	}
		    }
		}
		$this->setupSchedule($schedules, $seasonid);
	}

	public function schedule($attrs) {
		$seasonid = $_GET['season'];
		$weekid = $_GET['week'];
		$conferenceid = $_GET['conference'];
		$teamid = $_GET['team'];
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
		$weeks = $this->api->getWeeks($seasonid);
		$conferences = $this->api->getConferences($seasonid);
		$teams = $this->api->getTeams($seasonid, $conferenceid, true);
		$schedules = $this->api->getSchedules($seasonid, $weekid, $conferenceid, $teamid);

		for($i = 0; $i < sizeof($schedules);$i++){
			$schedule = $schedules[$i];
		    for($j = 0; $j < sizeof($weeks);$j++){
		    	if($weeks[$j]->weekid == $schedule->weekid) {
		    		$schedules[$i]->week = $weeks[$j];
		    		break;
		    	}
		    }
		}
		?>
		<div class="schedule-page collclubsports-page-wrapper">
			<div class="schedule-option-wrapper">
				<div class="form-group select-field">
					<label for="select-season">Select Season</label>
					<select class="form-control sched-select-season" id="sched-select-season">
						<?php for($i = 0; $i < sizeof($seasons);$i++){
							$season = $seasons[$i]; ?>
							<option value="<?php echo $season->seasonid;?>"><?php echo $season->seasonname;?></option>
						<?php }?>
					</select>
				</div>
				<div class="schedule-option-wrapper">
					<div class="form-group select-field">
						<label for="select-week">Sort by Week</label>
						<select class="form-control sched-select-week" id="sched-select-week">
							<option value="">Select Week</option>
							<?php for($i = 0; $i < sizeof($weeks);$i++){
								$week = $weeks[$i]; ?>
								<option value="<?php echo $week->weekid;?>"><?php echo $week->weekname;?></option>
							<?php }?>
						</select>
					</div>
					<div class="form-group select-field">
						<label for="select-conference">Sort by Conference</label>
						<select class="form-control sched-select-conference" id="sched-select-conference">
							<option value="">Select Conference</option>
							<?php for($i = 0; $i < sizeof($conferences);$i++){
								$conference = $conferences[$i]; ?>
								<option value="<?php echo $conference->conferenceid;?>"><?php echo $conference->conferencename;?></option>
							<?php }?>
						</select>
					</div>
					<div class="form-group select-field">
						<label for="select-team">Sort by Team</label>
						<select class="form-control sched-select-team" id="sched-select-team">
							<option value="">Select Team</option>
							<?php for($i = 0; $i < sizeof($teams);$i++){
								$team = $teams[$i]; ?>
								<option value="<?php echo $team->teamid;?>"><?php   
									if($team->isprobation) {
										echo '*';
									}
									echo $team->teamname;?></option>
							<?php }?>
						</select>
					</div>
					<div class="form-group select-field">

						<input type="submit" value="Go" class="form-control" id="schedule-submit" style="width: initial; padding: 0px 10px; margin-top: 25px;" />
					</div>

					<div class="form-group select-field">

						<input type="submit" value="Reset" class="form-control" id="reset-submit" style="width: initial; padding: 0px 10px; margin-top: 25px;" />
					</div>
				</div>
			</div>
			
			<?php $this->setupSchedule($schedules, $seasonid); ?>
		</div>
		<?php
	}

	public function setupSchedule($schedules, $seasonid = null) { ?>
		<?php
		if($_GET['team'] != "" || $_GET['season'] != "" || $_GET['week'] || $_GET['conference']) {
		?>
		<div class="schedule-wrapper">
			<?php if(sizeof($schedules) == 0) {?>
				<div class="no-schedule">No Games Scheduled</div>
			<?php } else { ?>
				<div class="description">
					<span class="primary-color">***</span> 
					<span>&nbsp;indicates conference game</span>
				</div>
			<?php }?>
			<?php for($i = 0; $i < sizeof($schedules);$i++){ 
				$schedule = $schedules[$i];
			?>
				<div class="schedule-item-wrapper">
					<label class="schedule-week-detail">
						<?php 
							if (empty($schedule->week->weekname)) {
								$week = $schedule->week;
								$startdate = new DateTime($week->startdate);
								$enddate = new DateTime($week->enddate);
								$weekname = 'Week ' . $week->ordinalnumber . ' ' 
									. $startdate->format('m/d/Y') . ' - ' . $enddate->format('m/d/Y');
								echo $weekname; 
							} else {
								echo $schedule->week->weekname; 
							}
							
						?>
					</label>
					<div class="schedule-game-detail">
						<div class="schedule-team-wrapper">
							<?php 
								if(!empty($schedule->visitingteam->teamname)) { ?>
									<a class="schedule-team-name primary-color"
										href="<?php echo  $this->constructTeamUrl($schedule->visitingteam->teamid, $seasonid);?>">
										<?php   
											if($schedule->visitingteam->isprobation) {
												echo '*';
											}
											echo $schedule->visitingteam->teamname;?>
									</a>
								<?php } else { ?>
									<span class="schedule-team-name primary-color"><?php echo $schedule->visitingteamwritein; ?></span>
								<?php }
							?>
							<div class="schedule-team-detail">
								<label>Score: </label>
								<label class="primary-color">
									<?php echo $schedule->visitingteamscore; ?>
								</label>
							</div>
							<div class="schedule-team-detail">
								<label>Conference Record: </label>
								<label class="primary-color"><?php echo $schedule->visitingteam->conferencerecord;?></label>
							</div>
							<div class="schedule-team-detail">
								<label>Overall Record: </label>
								<label class="primary-color"><?php echo $schedule->visitingteam->overallrecord;?></label>
							</div>
							<div class="schedule-team-detail">
								<label>Conference: </label>
								<a class="primary-color"
									href="<?php echo $this->constructConferenceUrl($seasonid, $schedule->visitingteam->conferenceid);?>" >
									<?php echo $schedule->visitingteam->conferencename; ?>

								</a>
							</div>
						</div>
						<div class="schedule-team-wrapper">
							<?php if($schedule->isconference) { ?>
								<span class="primary-color">***</span>
							<?php } ?>
							<?php 
								if(!empty($schedule->hometeam->teamname)) { ?>
									<a class="schedule-team-name primary-color"
										href="<?php echo  $this->constructTeamUrl($schedule->hometeam->teamid, $seasonid);?>">
										@ <?php   
											if($schedule->hometeam->isprobation) {
												echo '*';
											}
											echo $schedule->hometeam->teamname;?>
									</a>
								<?php } else { ?>
									<span class="schedule-team-name primary-color">@ <?php echo $schedule->hometeamwritein; ?></span>
								<?php }
							?>
							<div class="schedule-team-detail">
								<label>Score: </label>
								<label class="primary-color">
									<?php echo $schedule->hometeamscore; ?>
								</label>
							</div>
							<div class="schedule-team-detail">
								<label>Conference Record: </label>
								<label class="primary-color"><?php echo $schedule->hometeam->conferencerecord;?></label>
							</div>
							<div class="schedule-team-detail">
								<label>Overall Record: </label>
								<label class="primary-color"><?php echo $schedule->hometeam->overallrecord;?></label>
							</div>
							<div class="schedule-team-detail">
								<label>Conference: </label>
								<a class="primary-color"
									href="<?php echo $this->constructConferenceUrl($seasonid, $schedule->hometeam->conferenceid);?>" >
									<?php echo $schedule->hometeam->conferencename; ?>
								</a>
							</div>
						</div>
						<?php if($schedule->isforfeit) { ?>
							<div class="warn">
								<label>Forfeit</label>
							</div>
						<?php } ?>
					</div>
					<a class="button accent folded-corner" 
							href="<?php echo $this->constructGameNotesUrl($schedule->gameid);?>">Game Notes/Stats</a>
				</div>
			<?php } ?>
		</div>
		<?php
		} else {

			?>
			<div class="schedule-wrapper">
				<h3 style="text-align: center; margin-top: 100px">Please select from Season, Week, Conference and Team filters above to view results</h3>
			</div>
			<?php

		}

		?>
	<?php }
}
