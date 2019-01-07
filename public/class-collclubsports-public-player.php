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
 * @author     Bretch Guire Garcinez <bgarcinez@gmail.com>
 */
class CollClubSports_Public_Player {

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
		add_shortcode('player-list', array($this, 'player_list'));
		add_shortcode('player-week', array($this, 'player_week'));
		add_shortcode('league-player-week', array($this, 'league_player_week'));
		add_shortcode('player-list-team', array($this, 'player_list_team'));
	}

	private function constructPlayerUrl($playerid, $seasonid = null) {
		$url = $this->options['player_url'] . '?player=' . $playerid;

		if(!empty($seasonid)) {
			$url = $url . '&season=' . $seasonid;
		}
		return $url;
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

	public function player_week($attrs) {
		ob_start();
		$seasonid = $attrs['season'];
		$type =  $attrs['type'];
		$teamid = null;
		$conferenceid = null;
		$title = 'Player of the Week';

		if(!empty($attrs['team'])) {
			$teamid = $attrs['team'];
		}

		if(!empty($attrs['conference'])) {
			$conferenceid = $attrs['conference'];
		}

		if(!empty($attrs['title'])) {
			$title = $attrs['title'];
		}

		if(empty($seasonid)) {
			$season = $this->api->getCurrentSeason();
			$seasonid = $season->seasonid;
		}

		$playerweek = $this->api->getPlayerOfWeek($seasonid, $type, $conferenceid, $teamid);

		if(empty($playerweek->ads)) {
			$this->setupPlayerWeek($playerweek, $title, $seasonid);
		}
		else {
			$this->setupPlayerWeekAds($playerweek->ads, $title);
		}
		
		return ob_get_clean();
	}

	public function setupPlayerWeekAds($ads) { ?>
		<div class="collclubsports-component box-wrapper player-week-wrapper ads-wrapper">
			<?php if(!empty($ads->hyperlink)) { ?>
				<a class=""
					href="<?php echo $this->constructExternalUrl($ads->hyperlink); ?>">
					<img src="<?php echo $ads->imageurl; ?>">
				</a>
			<?php } 
			else { ?>
				<img src="<?php echo $ads->imageurl; ?>">
			<?php }?>
		</div>
	<?php }

	public function setupPlayerWeek($playerweek, $title, $seasonid) { ?>
		<div class="collclubsports-component box-wrapper player-week-wrapper">
			<div class="box-title player-week-title">
				<?php echo $title; ?>
			</div>
			<div class="box-body-wrapper player-week-body">
				<?php if(!empty($playerweek)) {
						if(!empty($playerweek->player->primaryposition)) { 
							$position = explode("-", $playerweek->player->primaryposition)[0];
							if(!empty($playerweek->player->secondaryposition)) {
								$position = $position . '/' . explode("-", $playerweek->player->secondaryposition)[0];
							}
						}

						if(!empty($playerweek->player->jerseynumber)) { 
							$jerseynumber = ' - #' . $playerweek->player->jerseynumber;
						}
						$playername = $playerweek->player->firstname . ' ' . $playerweek->player->lastname . ' ' . $playerweek->player->lastnamesuffix . $jerseynumber  . ' ' . $position;
						
						if(!empty($playerweek->leagueid)) {
							$league_logourl = $this->api->getLeagueById($playerweek->leagueid)->logourl;
						}
					?>
					<p class="player-week-name">
						<?php echo $playername; ?>
					</p>
					<p class="player-week-team">
						<?php   
							if($playerweek->player->intendteam->isprobation) {
								echo '*';
							}
							echo $playerweek->player->intendteam->teamname; ?>
					</p>
					<p class="notes">
						<?php echo $playerweek->notes; ?>
					</p>
					<div class="player-week-image-wrapper">
						<?php if(!empty($playerweek->player->profilepictureurl)) { ?>
							<a class="player-profile-wrapper"
								href="<?php echo $this->constructPlayerUrl($playerweek->player->playerid, $playerweek->seasonid); ?>">
								<img src="<?php echo $playerweek->player->profilepictureurl; ?>">
							</a>
						<?php } 
						else {?>
							<a class="player-profile-wrapper"
								href="<?php echo $this->constructPlayerUrl($playerweek->player->playerid, $playerweek->seasonid); ?>">
								<?php if (!empty($league_logourl)) { ?>
									<img src="<?php echo $league_logourl; ?>">
								<?php } else { ?>
									<div class="no-image"></div>
								<?php } ?>
							</a>
						<?php } ?>

						<?php if(!empty($playerweek->player->intendteam->logourl)) { ?>
							<a class="player-team-wrapper"
								href="<?php echo $this->constructTeamUrl($playerweek->player->intendteam->teamid, $seasonid); ?>">
								<img src="<?php echo $playerweek->player->intendteam->logourl; ?>">
							</a>
						<?php } 
						else {?>
							<a class="player-team-wrapper"
								href="<?php echo $this->constructTeamUrl($playerweek->player->intendteam->teamid, $seasonid);?>">
								<?php if (!empty($league_logourl)) { ?>
									<img src="<?php echo $league_logourl; ?>">
								<?php } else { ?>
									<div class="no-image"></div>
								<?php } ?>
							</a>
						<?php } ?>
					</div>
				<?php } 
				else {?>
					<div class="player-week-none-selected">
						None selected
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } 

	public function player_list_team($attrs) {
		$seasonid = $attrs['season'];
		$teamid = $attrs['team'];
		$this->setupPlayers($seasonid, $teamid);
	}

	public function player_list($attrs) {
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
		$teamid = $_GET['team'];
		$teams = $this->api->getTeams($seasonid, null, true);
		?>
		<div class="player-list-page collclubsports-page-wrapper">
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
					<label for="select-team">Sort by Team</label>
					<select class="form-control" id="select-team">
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
			</div>
		<?php
		for($i = 0; $i < sizeof($teams);$i++){
		    if($teams[$i]->teamid == $teamid) {
		    	$selectedteam = $teams[$i];
		    }
		}
		$title = '<h3 class="stats-title">{0} Roster</h3>';
		if(!empty($selectedteam)) {
			$title = str_replace('{0}', $selectedteam->teamname, $title);
		}
		else {
			$title = str_replace('{0}', '', $title);
		}
		echo $title;
		$this->setupPlayers($seasonid, $teamid);
		echo '</div>';
	}

	public function setupPlayers($seasonid = null, $teamid = null, $playerlist = null) {
		$league = $this->api->getLeague();
		if (empty($playerlist)) {
			$players = $this->api->getPlayers($seasonid, $teamid);
		} else {
			$players = $playerlist;
		}
		$items = array();
		$columns = array();
		foreach ($players as $val) {
			//$val->name = ucwords(strtolower($val->lastname . ', ' . $val->firstname));
			$val->name = str_replace("Inactive","<span class='warn'>INACTIVE</span>",$val->name);
			$val->playerlink = $this->constructPlayerUrl($val->playerid, $seasonid);
			if(!empty($val->heightfeet)) {
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

			if(!empty($val->primaryposition)) { 
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

		if(empty($teamid)) {
			array_push($columns, array(
				'label' => 'Team',
				'key' => 'teamname',
				'thclass' => 'name-header'
			));
		}
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
		if (   !empty($league) && !empty($league->sporttype)
			&& ($league->sporttype === 1 || $league->sporttype === 2)) {
			array_push($columns, array(
				'label' => 'Bats',
				'key' => 'bats'
			));
			array_push($columns, array(
				'label' => 'Throws',
				'key' => 'throws'
			));
		}
		array_push($columns, array(
			'label' => 'Hometown',
			'key' => 'hometown'
		));
		array_push($columns, array(
			'label' => 'High School',
			'key' => 'highschool'
		));
		echo '<div style="overflow:auto;">';
			new CollClubSports_Table($columns, $items, null, null, null, null, null, array(
				'pageSize' => 30
			));
		echo '</div>';
	}

	public function league_player_week($attrs) {
		wp_enqueue_script( $this->plugin_name . '-ajax', plugin_dir_url( __FILE__ ) . 'js/collclubsports-league.js', array('jquery'), $this->version, false );

		wp_localize_script( $this->plugin_name . '-ajax', 
			'collclubsports', 
			array( 
				'ajax_url' => admin_url( 'admin-ajax.php' )
			) 
		);

		$leagues = $this->options['leagues'];
		$pow_titles = array(
			'Player of the Week',
			'Player of the Week',
			'Offensive Player of the Week',
			'Defensive Player of the Week',
			'Pitcher of the Week' );
		ob_start(); ?>
		<div id="home-pow-ads" class="basketball-association leagues">
			<?php // Show POW for the first league in the list
			if (count($leagues) > 0) { 
				$id = rand(0, count($leagues) - 1); ?>
				<select class="form-control" id="select-league">
					<?php foreach ($leagues as $key => $league) { ?>
						<option value="<?php echo $league['id'];?>" <?php if ($key == $id) echo 'selected'; ?>><?php echo $league['alias'];?></option>
					<?php }?>
				</select>
				<div class="pow-ads-container">
					<?php 
					$leagueid = $leagues[$id]['id'];
					$leagueapikey = $leagues[$id]['apikey'];
					$powtype1 = (int)$leagues[$id]['powtype1'];
					$powtype2 = (int)$leagues[$id]['powtype2'];
					$leaguecurrentseasonid = $this->getLeagueCurrentSeason($leagueapikey, $leagueid);
					
					// First POW/Ad box
					$playerweek1 = $this->getLeaguePlayerWeekAd($leagueapikey, $leaguecurrentseasonid, $powtype1);
					if (empty($playerweek1->ads))
						$this->setupPlayerWeek($playerweek1, $pow_titles[$powtype1 - 1], $leaguecurrentseasonid);
					else
						$this->setupPlayerWeekAds($playerweek1->ads, $pow_titles[$powtype1 - 1]);

					// Second POW/Ad box
					$playerweek2 = $this->getLeaguePlayerWeekAd($leagueapikey, $leaguecurrentseasonid, $powtype2);
					if (empty($playerweek2->ads))
						$this->setupPlayerWeek($playerweek2, $pow_titles[$powtype2 - 1], $leaguecurrentseasonid);
					else
						$this->setupPlayerWeekAds($playerweek2->ads, $pow_titles[$powtype2 - 1]);
					?>
				</div>
			<?php } else {
				echo '<br/>No details found.';
			} ?>
		</div>
		<?php
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	public function showLeaguePlayerWeek() {
		$leagues = $this->options['leagues'];
		$leagueid = $_GET['leagueid'];
		$pow_titles = array(
			'Player of the Week',
			'Player of the Week',
			'Offensive Player of the Week',
			'Defensive Player of the Week',
			'Pitcher of the Week' );
		if (empty($leagueid) || empty($leagues) || (!empty($leagues) && count($leagues) == 0)) {
			echo '<br/>No details found.';
		} else {
			foreach ($leagues as $league) {
				if ($league['id'] == $leagueid) {
					$leagueapikey = $league['apikey'];
					$powtype1 = (int)$league['powtype1'];
					$powtype2 = (int)$league['powtype2'];
				}
			}
			$leaguecurrentseasonid = $this->getLeagueCurrentSeason($leagueapikey, $leagueid);
			
			// First POW/Ad box
			$playerweek1 = $this->getLeaguePlayerWeekAd($leagueapikey, $leaguecurrentseasonid, $powtype1);
			if (empty($playerweek1->ads))
				$this->setupPlayerWeek($playerweek1, $pow_titles[$powtype1 - 1], $leaguecurrentseasonid);
			else
				$this->setupPlayerWeekAds($playerweek1->ads, $pow_titles[$powtype1 - 1]);

			// Second POW/Ad box
			$playerweek2 = $this->getLeaguePlayerWeekAd($leagueapikey, $leaguecurrentseasonid, $powtype2);
			if (empty($playerweek2->ads))
				$this->setupPlayerWeek($playerweek2, $pow_titles[$powtype2 - 1], $leaguecurrentseasonid);
			else
				$this->setupPlayerWeekAds($playerweek2->ads, $pow_titles[$powtype2 - 1]);
		}
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

	private function getLeaguePlayerWeekAd($apikey, $seasonid, $type) {
		$url = $this->options['api_url'] . '/v1/season/{0}/player-week/view?conferenceId=&teamId=&type={1}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $type, $url);
		
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
}
