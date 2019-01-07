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
 * @author     Cielito Cantero <cielitomcantero@gmail.com>
 */
class CollClubSports_Public_Championship_Team {

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
		add_shortcode('championship-team', array($this, 'championship_team'));
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

	public function championship_team($attrs) {
		$seasonid = $_GET['season'];
		$seasons = $this->api->getChampionshipSeasons();

		if (empty($seasonid) && sizeof($seasons) > 0) {
			$seasonid = $seasons[0]->seasonid;
		}
		
		$teams = $this->api->getChampionshipTeamsPerSeason($seasonid);
		?>
		<div class="championship-list-page collclubsports-page-wrapper">
			<div class="championship-option-wrapper">
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
			<h3>Teams</h3>
			<div class="championship-list-wrapper">
				<?php if(sizeof($teams) == 0) {
					echo '<div class="no-item">Please check back for updates!</div>';
				}?>
				<?php for($i = 0; $i < sizeof($teams);$i++){ 
					$team = $teams[$i];
				?>
					<div class="championship-list-item-wrapper">
						<div class="team-logo-wrapper">
							<?php if(!empty($team->logourl)) { ?>
								<div class="image" style="background-image: url(<?php echo $team->logourl; ?>)"></div>
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
										echo $this->constructConferenceUrl($seasonid, $team->conferenceid);?>" 
									class="primary-color"><?php echo $team->conferencename;?></a>
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

	private function formatPercentage($val) {
		$val = round($val, 3);
		return  number_format($val, 3, '.', '');
	}
}

