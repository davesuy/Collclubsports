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
class CollClubSports_Public_Contact {

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
		add_shortcode('contact-person', array($this, 'contact_person'));
		add_shortcode('bio', array($this, 'bio'));
		add_shortcode('league-contact-person', array($this, 'league_contact_person'));
		add_shortcode('users', array($this, 'users'));
		add_shortcode('user-bio', array($this, 'user_bio'));
	}

	private function constructBioPageUrl($id) {
		return $this->options['bio_url'] . '?user=' . $id;
	}

	private function constructConferenceUrl($conferenceid) {
		return $this->options['conference_url'] . '?conference=' . $conferenceid;
	}

	private function constructExternalUrl($url) {
		$url = str_replace('http://', '', $url);
		$url = str_replace('https://', '', $url);
		return '//' . $url;
	}
	
	public function contact_person() {
		ob_start();
		$season = $this->api->getCurrentSeason();
		$seasonid = $season->seasonid;
		$contacts = $this->api->getContacts($seasonid);
		$league = $this->api->getLeague();
		?>
		<div class="collclubsports-component contact-wrapper">
            <h5 class="head"><?php echo $league->leaguealias; ?> FRONT OFFICE</h5>
            <?php 
        	for($i = 0; $i < sizeof($contacts->frontofficeusers);$i++){
				$bio = $contacts->frontofficeusers[$i];
            ?>
	            <ul class="contact-item-details">
	                <li class="primary-color name">
	                    <label><?php echo $bio->firstname . ' ' . $bio->lastname; ?></label>
	                </li>
	                <li class="designation">
	                    <?php echo $bio->usertitle; ?>
	                </li>
	                <li class="bio">
	                    <a href="<?php echo $this->constructBioPageUrl($bio->userid); ?>">VIEW BIO</a>
	                </li>
	            </ul>
	        <?php } ?>
        </div>
        <div class="collclubsports-component contact-wrapper">
            <h5 class="head"><?php echo $league->leaguealias; ?> CONFERENCE COORDINATORS</h5>
            <?php 
        	for($i = 0; $i < sizeof($contacts->leaguecoordinators);$i++){
        		$conferences = $contacts->leaguecoordinators[$i]->conferences;
				$bio = $contacts->leaguecoordinators[$i]->user;
            ?>
	            <ul class="contact-item-details">
	                <li class="primary-color name">
	                    <label><?php echo $bio->firstname . ' ' . $bio->lastname; ?></label>
	                </li>
	                <li class="bio">
	                    <a href="<?php echo $this->constructBioPageUrl($bio->userid); ?>">VIEW BIO</a>
	                </li>
	                <?php for($j = 0; $j < sizeof($conferences);$j++){ 
	                	$conference = $conferences[$j];
	                ?>
		                <li class="conference-link">
		                	<a href="<?php echo $this->constructConferenceUrl($conference->conferenceid); ?>">
		                		<?php echo $conference->conferencename; ?></a>
		                </li>
	                <?php } ?>
	            </ul>
	        <?php } ?>
        </div>
		<?php 
		return ob_get_clean();
	}	

	public function bio() {
		$userid = $_GET['user'];
		if(!empty($userid)) {
			$user = $this->api->getUser($userid);
			if(!empty($user)) { 
			?>
				<div class="bio-page collclubsports-page-wrapper">
					<div class="bio-profile-wrapper">
						<?php 
							if(!empty($user->profilepicture)) { ?>
								<img src="<?php echo $user->profilepicture;?>"/>
							<?php }
						?>
					</div>
					<div class="bio-information">
						<h3 class="primary-color">
							<?php echo $user->firstname . ' ' . $user->lastname;?>
						</h3>
						<p><?php echo $user->usertitle;?></p>
						<p>E-mail: <a class="primary-color" 
							href="mailto:<?php echo $user->username;?>"><?php echo $user->username;?></a></p>
						<div class="html-editor"><?php echo $user->bio;?></div>
					</div>
				</div>
			<?php }
		}
	}

	public function league_contact_person() {
		wp_enqueue_script( $this->plugin_name . '-ajax', plugin_dir_url( __FILE__ ) . 'js/collclubsports-league.js', array('jquery'), $this->version, false );

		wp_localize_script( $this->plugin_name . '-ajax', 
			'collclubsports', 
			array( 
				'ajax_url' => admin_url( 'admin-ajax.php' )
			) 
		);

		$leagues = $this->options['leagues'];
		ob_start(); ?>

		<select class="form-control" id="select-league">
			<?php foreach ($leagues as $league) { ?>
				<option value="<?php echo $league['id'];?>"><?php echo $league['alias'];?></option>
			<?php }?>
		</select>
		<?php if (count($leagues) > 0) :
			$leagueapikey = $leagues[0]['apikey'];
			$leagueid = $leagues[0]['id'];
			$leagueurl = $leagues[0]['url'];
			$leaguecurrentseasonid = $this->getLeagueCurrentSeason($leagueapikey, $leagueid);
			$contacts = $this->getLeagueContacts($leagueapikey, $leagueid, $leaguecurrentseasonid);
			?>
			<div class="league-contacts-container">
				<div class="collclubsports-component contact-wrapper">
		            <h5 class="head"><?php echo $leagues[0]['alias']; ?> FRONT OFFICE</h5>
		            <?php 
		        	for($i = 0; $i < sizeof($contacts->frontofficeusers);$i++){
						$bio = $contacts->frontofficeusers[$i];
		            ?>
			            <ul class="contact-item-details">
			                <li class="primary-color name">
			                    <label><?php echo $bio->firstname . ' ' . $bio->lastname; ?></label>
			                </li>
			                <li class="designation">
			                    <?php echo $bio->usertitle; ?>
			                </li>
			                <li class="bio">
			                    <a href="<?php echo $leagueurl . $this->constructBioPageUrl($bio->userid); ?>">VIEW BIO</a>
			                </li>
			            </ul>
			        <?php } ?>
		        </div>
		        <div class="collclubsports-component contact-wrapper">
		            <h5 class="head"><?php echo $leagues[0]['alias']; ?> CONFERENCE COORDINATORS</h5>
		            <?php 
		        	for($i = 0; $i < sizeof($contacts->leaguecoordinators);$i++){
		        		$conferences = $contacts->leaguecoordinators[$i]->conferences;
						$bio = $contacts->leaguecoordinators[$i]->user;
		            ?>
			            <ul class="contact-item-details">
			                <li class="primary-color name">
			                    <label><?php echo $bio->firstname . ' ' . $bio->lastname; ?></label>
			                </li>
			                <li class="bio">
			                    <a href="<?php echo $leagueurl . $this->constructBioPageUrl($bio->userid); ?>">VIEW BIO</a>
			                </li>
			                <?php for($j = 0; $j < sizeof($conferences);$j++){ 
			                	$conference = $conferences[$j];
			                ?>
				                <li class="conference-link">
				                	<a href="<?php echo $leagueurl . $this->constructConferenceUrl($conference->conferenceid); ?>">
				                		<?php echo $conference->conferencename; ?></a>
				                </li>
			                <?php } ?>
			            </ul>
			        <?php } ?>
		        </div>
	       	</div>
			<?php return ob_get_clean();
		endif;
	}

	public function users($attrs) {
		$leagues = $this->options['leagues'];
		ob_start();

		if ( empty($leagues) || count($leagues) == 0 ) {
			echo 'Missing leagues';
		} else if ( empty($attrs) || ( !empty($attrs) && empty($attrs['roleid']) ) ) {
			echo 'Missing "roleid"';
		} else {
			$apikey = $leagues[0]['apikey'];
			$roleid = (int)$attrs['roleid'];
			$count = !empty($attrs['count']) ? (int)$attrs['count'] : 100;
			$offset = !empty($attrs['offset']) ? (int)$attrs['offset'] : 0;
			$search = !empty($attrs['search']) ? $attrs['search'] : null;
			// Get users by role
			$users = $this->getUsersByRole($apikey, $roleid, $count, $offset, $search)->Items; 

			if ( !empty($users) && count($users) > 0 ) { ?>
				<div class="collclubsports-component contact-wrapper">
		            <h5 class="head">CCS FRONT OFFICE</h5>
		            <?php 
		        	foreach ($users as $user) { ?>
			            <ul class="contact-item-details">
			                <li class="primary-color name">
			                    <label><?php echo $user->firstname . ' ' . $user->lastname; ?></label>
			                </li>
			                <li class="designation">
			                    <?php echo $user->usertitle; ?>
			                </li>
			                <li class="bio">
			                    <a href="<?php echo $this->constructBioPageUrl($user->userid); ?>">VIEW BIO</a>
			                </li>
			            </ul>
			        <?php } ?>
		        </div>
		    <?php } 
		}
		
		return ob_get_clean();
	}

	public function user_bio() {
		$leagues = $this->options['leagues'];
		$userid = $_GET['user'];

		ob_start();

		if ( empty($leagues) || count($leagues) == 0 ) {
			echo 'Missing leagues';
		} else if ( empty($userid) ) {
			echo 'Missing "userid"';
		} else {
			$apikey = $leagues[0]['apikey'];
			$userid = $_GET['user'];
			// Get user by id
			$user = $this->getUsersById($apikey, $userid);

			if ( !empty($user) ) { ?>
				<div class="bio-page collclubsports-page-wrapper">
					<div class="bio-profile-wrapper">
						<?php 
							if(!empty($user->profilepicture)) { ?>
								<img src="<?php echo $user->profilepicture;?>"/>
							<?php }
						?>
					</div>
					<div class="bio-information">
						<h3 class="primary-color">
							<?php echo $user->firstname . ' ' . $user->lastname;?>
						</h3>
						<p><?php echo $user->usertitle;?></p>
						<p>E-mail: <a class="primary-color" 
							href="mailto:<?php echo $user->username;?>"><?php echo $user->username;?></a></p>
						<div class="html-editor"><?php echo $user->bio;?></div>
					</div>
				</div>
		    <?php } 
		}
		
		return ob_get_clean();
	}

	public function showLeagueContacts() {
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
			$contacts = $this->getLeagueContacts($leagueapikey, $leagueid, $leaguecurrentseasonid); ?>
			<div class="collclubsports-component contact-wrapper">
	            <h5 class="head"><?php echo $leaguealias; ?> FRONT OFFICE</h5>
	            <?php 
	        	for($i = 0; $i < sizeof($contacts->frontofficeusers);$i++){
					$bio = $contacts->frontofficeusers[$i];
	            ?>
		            <ul class="contact-item-details">
		                <li class="primary-color name">
		                    <label><?php echo $bio->firstname . ' ' . $bio->lastname; ?></label>
		                </li>
		                <li class="designation">
		                    <?php echo $bio->usertitle; ?>
		                </li>
		                <li class="bio">
		                    <a href="<?php echo $leagueurl . $this->constructBioPageUrl($bio->userid); ?>">VIEW BIO</a>
		                </li>
		            </ul>
		        <?php } ?>
	        </div>
	        <div class="collclubsports-component contact-wrapper">
	            <h5 class="head"><?php echo $leaguealias; ?> CONFERENCE COORDINATORS</h5>
	            <?php 
	        	for($i = 0; $i < sizeof($contacts->leaguecoordinators);$i++){
	        		$conferences = $contacts->leaguecoordinators[$i]->conferences;
					$bio = $contacts->leaguecoordinators[$i]->user;
	            ?>
		            <ul class="contact-item-details">
		                <li class="primary-color name">
		                    <label><?php echo $bio->firstname . ' ' . $bio->lastname; ?></label>
		                </li>
		                <li class="bio">
		                    <a href="<?php echo $leagueurl . $this->constructBioPageUrl($bio->userid); ?>">VIEW BIO</a>
		                </li>
		                <?php for($j = 0; $j < sizeof($conferences);$j++){ 
		                	$conference = $conferences[$j];
		                ?>
			                <li class="conference-link">
			                	<a href="<?php echo $leagueurl . $this->constructConferenceUrl($conference->conferenceid); ?>">
			                		<?php echo $conference->conferencename; ?></a>
			                </li>
		                <?php } ?>
		            </ul>
		        <?php } ?>
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

	private function getLeagueContacts($apikey, $leagueid, $seasonid) {
		$url = $this->options['api_url'] . '/v1/league/{0}/season/{1}/contact-person';
		$url = str_replace('{0}', $leagueid, $url);
		$url = str_replace('{1}', $seasonid, $url);
		
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

	private function getUsersByRole($apikey, $roleid, $count, $offset, $search) {
		$url = $this->options['api_url'] . '/v1/contact-person?roleid={0}&count={1}&offset={2}&search={3}';
		$url = str_replace('{0}', $roleid, $url);
		$url = str_replace('{1}', $count, $url);
		$url = str_replace('{2}', $offset, $url);
		$url = str_replace('{3}', $search, $url);
		
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

	private function getUsersById($apikey, $userid) {
		$url = $this->options['api_url'] . '/v1/account/{0}/bio';
		$url = str_replace('{0}', $userid, $url);
		
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
}
