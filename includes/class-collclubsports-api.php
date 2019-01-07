<?php

class CollClubSports_Api {

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$options = get_option('collclubsports_admin');
		$this->api_url = $options['api_url'];
		$this->league_api_key = $options['league_api_key'];
		$this->league_id = $options['league_id'];
	}

	public function getLeague() {
		$url = $this->api_url . '/v1/league/{0}';
		$url = str_replace('{0}', $this->league_id, $url);
		return $this->request($url);
	}

	public function getLeagueById($leagueid) {
		$url = $this->api_url . '/v1/league/{0}';
		$url = str_replace('{0}', $leagueid, $url);
		return $this->request($url);
	}

	public function getUser($id) {
		$url = $this->api_url . '/v1/account/{0}/bio';
		$url = str_replace('{0}', $id, $url);
		return $this->request($url);
	}

	public function getGameDetails($gameid) {
		$url = $this->api_url . '/v1/game/{0}';
		$url = str_replace('{0}', $gameid, $url);
		return $this->request($url);
	}
	// Contact API
	public function getContacts($seasonid) {
		$url = $this->api_url . '/v1/league/{0}/season/{1}/contact-person';
		$url = str_replace('{0}', $this->league_id, $url);
		$url = str_replace('{1}', $seasonid, $url);
		return $this->request($url);
	}
	// Season API
	public function getSeasons() {
		$url = $this->api_url . '/v1/league/{0}/season';
		$url = str_replace('{0}', $this->league_id, $url);
		return $this->request($url);
	}

	public function getCurrentSeason() {
		$url = $this->api_url . 'v1/league/{0}/season/current';
		$url = str_replace('{0}', $this->league_id, $url);
		return $this->request($url);
	}
	// End Of Season API

	// Conference API
	public function getConferences($seasonid) {
		$url = $this->api_url . '/v1/season/{0}/conference';
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getConferenceDetails($conferenceid) {
		$url = $this->api_url . 'v1/league/{0}/conference/{1}';
		$url = str_replace('{0}', $this->league_id, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		return $this->request($url);
	}

	public function getConferenceCoordinator($seasonid, $conferenceid) {
		$url = $this->api_url . 'v1/season/{0}/conference/{1}/coordinator';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		return $this->request($url);
	}

	public function getConferenceInfo($seasonid, $conferenceid) {
		$url = $this->api_url . 'v1/season/{0}/conference/{1}/info';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		return $this->request($url);
	}
	// End Conference API

	// Team API
	public function getTeam($seasonid, $teamid) {
		$url = $this->api_url . '/v1/season/{0}/team/{1}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $teamid, $url);
		return $this->request($url);
	}
	public function getTeams($seasonid = null, $conferenceid = null, $issortteamname = null) {
		$url;
		if(!empty($seasonid) && empty($conferenceid)) {
			$url = $this->api_url . '/v1/season/{0}/team';
			$url = str_replace('{0}', $seasonid, $url);
		} else if(!empty($seasonid) && !empty($conferenceid)) {
			$url = $this->api_url . '/v1/season/{0}/conference/{1}/team';
			$url = str_replace('{0}', $seasonid, $url);
			$url = str_replace('{1}', $conferenceid, $url);
		}else if(empty($seasonid) && empty($conferenceid)) {
			$url = $this->api_url . '/v1/league/{0}/team';
			$url = str_replace('{0}', $this->league_id, $url);
		}
		$items = $this->request($url);
		if($issortteamname) {
			usort($items, function($a, $b) {
				return $a->teamname > $b->teamname ? 1 : -1;
			});
		}
		return $items;
	}

	public function getTeamStandings($seasonid = null, $conferenceid = null) {
		$url = $this->api_url . '/v1/season/{0}/conference-team-standing';
		if(!empty($conferenceid)) {
			$url = $this->api_url . '/v1/season/{0}/conference/{1}/team-standing';
			$url = str_replace('{1}', $conferenceid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getTeamPageDetails($seasonid = null, $teamid = null) {
		$url = $this->api_url . '/v1/season/{0}/team/{1}/details';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $teamid, $url);
		return $this->request($url);
	}
	// End Team API


	// Player API
	public function getPlayers($seasonid, $teamid = null) {
		$url = $this->api_url . '/v1/season/{0}/player';
		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/player';
			$url = str_replace('{1}', $teamid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		$items = $this->request($url);
		usort($items, function($a, $b) {
			return $a->name > $b->name ? 1 : -1;
		});
		return $items;
	}
	// End Player API

	// Game Schedule API
	public function getSchedules($seasonid = null, $weekid = null, $conferenceid = null, $teamid = null) {
		$url = $this->api_url . '/v1/season/{0}/game?weekId={1}&conferenceId={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $weekid, $url);
		$url = str_replace('{2}', $conferenceid, $url);
		$url = str_replace('{3}', $teamid, $url);
		return $this->request($url);
	}
	// End Game Schedule API


	// Week API
	public function getWeek($weekid) {
		$url = $this->api_url . '/v1/week/{0}';
		$url = str_replace('{0}', $weekid, $url);
		$week = $this->request($url);
		$startdate = new DateTime($week->startdate);
		$enddate = new DateTime($week->enddate);
		$week->weekname = 'Week ' . $week->ordinalnumber . ' ' 
			. $startdate->format('m/d/Y') . ' - ' . $enddate->format('m/d/Y');
			return $week;
	}

	public function getWeeks($seasonid) {
		$url = $this->api_url . '/v1/league/{0}/season/{1}/week';
		$url = str_replace('{0}', $this->league_id, $url);
		$url = str_replace('{1}', $seasonid, $url);
		$weeks = $this->request($url);
		for($i = 0; $i < sizeof($weeks);$i++){
			$week = $weeks[$i];
			$startdate = new DateTime($week->startdate);
			$enddate = new DateTime($week->enddate);
			$week->weekname = 'Week ' . $week->ordinalnumber . ' ' 
				. $startdate->format('m/d/Y') . ' - ' . $enddate->format('m/d/Y');
		}
		return $weeks;
	}
	// End Week API

	// Baseball Stats
	public function getBaseballStatsPerGame($gameid) {
		$url = $this->api_url . '/v1/game/{0}/stats/baseball/view';
		$url = str_replace('{0}', $gameid, $url);
		return $this->request($url);
	}

	public function getBaseballHittingStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/baseball/offensive/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/baseball/offensive/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/baseball/offensive/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getBaseballPitchingStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/baseball/pitching/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/baseball/pitching/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/baseball/pitching/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getBaseballTopAVG($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/baseball/offensive/top-avg?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
				$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getBaseballTopHR($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/baseball/offensive/top-hr?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
				$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getBaseballTopRBI($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/baseball/offensive/top-rbi?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
				$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getBaseballTopERA($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/baseball/pitching/top-era?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
				$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getBaseballTopSO($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/baseball/pitching/top-so?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
				$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getBaseballTopW($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/baseball/pitching/top-w?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
				$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}
	// End Baseball Stats

	// Softball Stats
	public function getSoftballStatsPerGame($gameid) {
		$url = $this->api_url . '/v1/game/{0}/stats/softball/view';
		$url = str_replace('{0}', $gameid, $url);
		return $this->request($url);
	}

	public function getSoftballHittingStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/softball/offensive/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/softball/offensive/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/softball/offensive/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getSoftballPitchingStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/softball/pitching/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/softball/pitching/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/softball/pitching/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getSoftballTopAVG($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/softball/offensive/top-avg?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
				$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getSoftballTopHR($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/softball/offensive/top-hr?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
				$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getSoftballTopRBI($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/softball/offensive/top-rbi?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
				$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getSoftballTopERA($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/softball/pitching/top-era?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
				$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getSoftballTopSO($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/softball/pitching/top-so?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
				$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getSoftballTopW($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/softball/pitching/top-w?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
				$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}
	// End Softball Stats

	// Basketball Stats
	public function getBasketballStatsPerGame($gameid) {
		$url = $this->api_url . '/v1/game/{0}/stats/basketball/view';
		$url = str_replace('{0}', $gameid, $url);
		return $this->request($url);
	}
	
	public function getBasketballStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/basketball/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/basketball/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/basketball/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getBasketballTopAPG($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/basketball/top-apg?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getBasketballTopDD2($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/basketball/top-dd2?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getBasketballTopDD3($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/basketball/top-dd3?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getBasketballTopPPG($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/basketball/top-ppg?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getBasketballTopREB($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/basketball/top-reb?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getBasketballTopSPG($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/basketball/top-spg?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}
	// End Basketball Stats


	// Football Stats
	public function getFootballStatsPerGame($gameid) {
		$url = $this->api_url . '/v1/game/{0}/stats/football/view';
		$url = str_replace('{0}', $gameid, $url);
		return $this->request($url);
	}

	public function getFootballOffensiveStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/football/offensive/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/football/offensive/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/football/offensive/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getFootballOffensivePassingStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/football/offensive/passing/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/football/offensive/passing/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/football/offensive/passing/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getFootballOffensiveRushingStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/football/offensive/rushing/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/football/offensive/rushing/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/football/offensive/rushing/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getFootballOffensiveReceivingStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/football/offensive/receiving/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/football/offensive/receiving/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/football/offensive/receiving/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getFootballOffensiveFieldGoalStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/football/offensive/fieldgoal/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/football/offensive/fieldgoal/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/football/offensive/fieldgoal/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getFootballDefensiveStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/football/defensive/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/football/defensive/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/football/defensive/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getFootballDefensiveDefenseStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/football/defensive/defense/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/football/defensive/defense/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/football/defensive/defense/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getFootballDefensiveInterceptionStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/football/defensive/interception/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/football/defensive/interception/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/football/defensive/interception/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getFootballSpecialTeamStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/football/special-team/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/football/special-team/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/football/special-team/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getFootballSpecialTeamPuntingStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/football/special-team/punting/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/football/special-team/punting/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/football/special-team/punting/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getFootballSpecialTeamPuntReturnStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/football/special-team/puntreturn/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/football/special-team/puntreturn/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/football/special-team/puntreturn/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getFootballSpecialTeamKickReturnStats($seasonid, $teamid=null, $playerid=null) {
		$url = $this->api_url . '/v1/season/{0}/stats/football/special-team/kickreturn/view';

		if(!empty($teamid)) {
			$url = $this->api_url . 'v1/season/{0}/team/{1}/stats/football/special-team/kickreturn/view';
			$url = str_replace('{1}', $teamid, $url);
		}

		if(!empty($playerid)) {
			$url = $this->api_url . 'v1/season/{0}/player/{1}/stats/football/special-team/kickreturn/view';
			$url = str_replace('{1}', $playerid, $url);
		}
		$url = str_replace('{0}', $seasonid, $url);
		return $this->request($url);
	}

	public function getFootballTopPassingCompPct($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/football/offensive/top-passing-comppct?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getFootballTopPassingTDS($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/football/offensive/top-passing-tds?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getFootballTopPassingYDS($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/football/offensive/top-passing-yds?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getFootballTopReceivingTDS($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/football/offensive/top-receiving-tds?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getFootballTopReceivingYDS($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/football/offensive/top-receiving-yds?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getFootballTopRushingTDS($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/football/offensive/top-rushing-tds?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getFootballTopRushingYDS($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/football/offensive/top-rushing-yds?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getFootballTopInt($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/football/defensive/top-int?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getFootballTopSACK($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/football/defensive/top-sack?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}

	public function getFootballTopTackles($seasonid, $conferenceid = null, $limit = 5, $teamId = null) {
		$url = $this->api_url . 'v1/season/{0}/stats/football/defensive/top-tackles?conferenceId={1}&limit={2}&teamId={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $limit, $url);
		$url = str_replace('{3}', $teamId, $url);
		return $this->request($url);
	}
	// End Football Stats

	public function getPlayerOfWeek($seasonid, $type, $conferenceid = null, $teamid = null) {
		$url = $this->api_url . 'v1/season/{0}/player-week/view?conferenceId={1}&teamId={2}&type={3}';
		$url = str_replace('{0}', $seasonid, $url);
		$url = str_replace('{1}', $conferenceid, $url);
		$url = str_replace('{2}', $teamid, $url);
		$url = str_replace('{3}', $type, $url);
		return $this->request($url);
	}

	//Search API
	public function search($key) {
		$url = $this->api_url . '/v1/league/{0}/search/{1}';
		$url = str_replace('{0}', $this->league_id, $url);
		$url = str_replace('{1}', $key, $url);
		return $this->request($url);
	}
	// End Search API

	// Championship Team API
	public function getChampionshipTeamsPerSeason($seasonid) {
		$url = $this->api_url . 'v1/league/{0}/season/{1}/championship-team';
		$url = str_replace('{0}', $this->league_id, $url);
		$url = str_replace('{1}', $seasonid, $url);
		$items = $this->request($url);
		if ( !empty($items) ) {
			usort($items, function($a, $b) {
				return $a->teamname > $b->teamname ? 1 : -1;
			});
		}

		return $items;
	}

	// Championship Season API
	public function getChampionshipSeasons() {
		$url = $this->api_url . '/v1/league/{0}/championship-seasons';
		$url = str_replace('{0}', $this->league_id, $url);
		return $this->request($url);
	}

	// End Championship Team API

	public function request($url) {
		try {
			$response = wp_remote_get($url, $this->getRequestArguments());
			// Don't throw the error
			if($response['response']['code'] == 200) {
				return json_decode($response['body']);
			}
			else {
				return null;
			}
		}
		catch (HttpException $ex){
			return $ex;
		}
	}

	public function getRequestArguments() {
		return array( 
			'timeout' => 120, 
			'httpversion' => '1.1',
			'headers' => array('league-api-key' => $this->league_api_key));
	}
}
