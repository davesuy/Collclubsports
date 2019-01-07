<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CollClubSports
 * @subpackage CollClubSports/admin/partials
 */
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<h2>CollClubSports Setup</h2>
<?php $options = get_option('collclubsports_admin');?>
<table class="form-table">
	<tbody>
		<tr>
			<td class="field-label-wrapper">
				<label>CollClubSports API URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[api_url]" value="<?php echo $options['api_url']?>" />
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>League ID</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[league_id]" value="<?php echo $options['league_id']?>" />
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>League API Key</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[league_api_key]" value="<?php echo $options['league_api_key']?>" />
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Conference List Page URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[conference_list_url]" value="<?php echo $options['conference_list_url']?>" />
				<label class="description-wrapper">Add the base url of the conference list page, DOMAIN SHOULD NOT BE INCLUDED. E.g. /conferences </label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Conference Page URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[conference_url]" value="<?php echo $options['conference_url']?>" />
				<label class="description-wrapper">Add the base url of the conference page, DOMAIN SHOULD NOT BE INCLUDED. E.g. /conference </label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Team List Page URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[team_list_url]" value="<?php echo $options['team_list_url']?>" />
				<label class="description-wrapper">Add the base url of the team list page, DOMAIN SHOULD NOT BE INCLUDED. E.g. /teams </label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Team Page URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[team_url]" value="<?php echo $options['team_url']?>" />
				<label class="description-wrapper">Add the base url of the team page, DOMAIN SHOULD NOT BE INCLUDED. E.g. /team </label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Player Page URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[player_url]" value="<?php echo $options['player_url']?>" />
				<label class="description-wrapper">Add the base url of the player page, DOMAIN SHOULD NOT BE INCLUDED. E.g. /player </label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Player List Page URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[player_list_url]" value="<?php echo $options['player_list_url']?>" />
				<label class="description-wrapper">Add the base url of the player list page, DOMAIN SHOULD NOT BE INCLUDED. E.g. /players </label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Stats Page URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[stats_url]" value="<?php echo $options['stats_url']?>" />
				<label class="description-wrapper">Add the base url of the stats page, DOMAIN SHOULD NOT BE INCLUDED. E.g. /stats </label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Game Notes/Stats Page URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[game_notes_url]" value="<?php echo $options['game_notes_url']?>" />
				<label class="description-wrapper">Add the base url of the game notes/stats page, DOMAIN SHOULD NOT BE INCLUDED. E.g. /game-notes-stats/ </label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Schedule Page URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[schedule_url]" value="<?php echo $options['schedule_url']?>" />
				<label class="description-wrapper">Add the base url of the schedule page, DOMAIN SHOULD NOT BE INCLUDED. E.g. /schedule </label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Standing Page URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[standing_url]" value="<?php echo $options['standing_url']?>" />
				<label class="description-wrapper">Add the base url of the standing page, DOMAIN SHOULD NOT BE INCLUDED. E.g. /standing </label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Bio Page URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[bio_url]" value="<?php echo $options['bio_url']?>" />
				<label class="description-wrapper">Add the base url of the bio page, DOMAIN SHOULD NOT BE INCLUDED. E.g. /bio </label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Search Result Page URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[search_result_url]" value="<?php echo $options['search_result_url']?>" />
				<label class="description-wrapper">Add the base url of the search result page, DOMAIN SHOULD NOT BE INCLUDED. E.g. /search </label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Championship Page URL</label>
			</td>
			<td>
				<input class="field-input" type="text" name="collclubsports_admin[championship_url]" value="<?php echo $options['championship_url']?>" />
				<label class="description-wrapper">Add the base url of the championship page, DOMAIN SHOULD NOT BE INCLUDED. E.g. /championship </label>
			</td>
		</tr>
		<tr valign="top" id="leagues_options">
			<th scope="row" class="titledesc">Leagues</th>
			<td class="forminp">
				<style type="text/css">
					.leagues {
						max-width: 750px;
					}
					.leagues td{
						vertical-align: middle;
						padding: 4px 7px;
					}
					.leagues th {
						padding: 9px 7px;
					}
					.leagues td input[type="text"]{
						width: 100%;
					}
					.leagues td input {
						margin-right: 4px;
					}
					.leagues .check-column {
						vertical-align: middle;
						text-align: left;
						padding: 0 7px;
						width: 20px;
					}
				</style>
				<table class="leagues widefat" style="max-width: 95%;">
					<thead>
						<tr>
							<th class="check-column"><input type="checkbox" /></th>
							<th style="width: 10%; line-height: 45px;">Alias</th>
							<th style="width: 25%; line-height: 45px;">ID</th>
							<th style="width: 30%; line-height: 45px;">API Key</th>
							<th style="width: 10%; line-height: 45px;">POW Type 1</th>
							<th style="width: 10%; line-height: 45px;">POW Type 2</th>
							<th style="width: 15%; line-height: 45px;">Site URL</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th colspan="5">
								<a href="#" class="button plus insert">Add League</a>
								<a href="#" class="button minus remove">Remove selected league(s)</a>
							</th>
						</tr>
					</tfoot>
					<tbody id="leagues">
						<?php
							if ( $options['leagues'] && ! empty( $options['leagues'] ) ) {
								foreach ( $options['leagues'] as $key => $league ) {
									?>
									<tr>
										<td class="check-column"><input type="checkbox" /></td>
										<td><input type="text" name="collclubsports_admin[leagues][<?php echo $key; ?>][alias]" value="<?php echo esc_attr( $league["alias"] ); ?>" /></td>
										<td><input type="text" name="collclubsports_admin[leagues][<?php echo $key; ?>][id]" value="<?php echo esc_attr( $league["id"] ); ?>" /></td>
										<td><input type="text" name="collclubsports_admin[leagues][<?php echo $key; ?>][apikey]" value="<?php echo esc_attr( $league["apikey"] ); ?>" /></td>
										<td><input type="text" name="collclubsports_admin[leagues][<?php echo $key; ?>][powtype1]" value="<?php echo esc_attr( $league["powtype1"] ); ?>" /></td>
										<td><input type="text" name="collclubsports_admin[leagues][<?php echo $key; ?>][powtype2]" value="<?php echo esc_attr( $league["powtype2"] ); ?>" /></td>
										<td><input type="text" name="collclubsports_admin[leagues][<?php echo $key; ?>][url]" value="<?php echo esc_attr( $league["url"] ); ?>" /></td>
									</tr>
									<?php
								}
							}
						?>
					</tbody>
				</table>
				<script type="text/javascript">

					jQuery(window).load(function(){

						jQuery('.leagues .insert').click( function() {
							var $tbody = jQuery('.leagues').find('tbody');
							var size = $tbody.find('tr').size();
							var code = '<tr class="new">\
									<td class="check-column"><input type="checkbox"/></td>\
									<td><input type="text" name="collclubsports_admin[leagues][' + size + '][alias]"/></td>\
									<td><input type="text" name="collclubsports_admin[leagues][' + size + '][id]"/></td>\
									<td><input type="text" name="collclubsports_admin[leagues][' + size + '][apikey]"/></td>\
									<td><input type="text" name="collclubsports_admin[leagues][' + size + '][powtype1]"/></td>\
									<td><input type="text" name="collclubsports_admin[leagues][' + size + '][powtype2]"/></td>\
									<td><input type="text" name="collclubsports_admin[leagues][' + size + '][url]"/></td>\
								</tr>';

							$tbody.append( code );

							return false;
						} );

						jQuery('.leagues .remove').click(function() {
							var $tbody = jQuery('.leagues').find('tbody');

							$tbody.find('.check-column input:checked').each(function() {
								jQuery(this).closest('tr').remove();
							});

							return false;
						});

					});

				</script>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Primary Color</label>
			</td>
			<td>
				<input class="color-picker" type="text" name="collclubsports_admin[color_primary]" value="<?php echo $options['color_primary']?>" />
				<label class="description-wrapper">This will be the primary color applied to shortcodes displayed by this plugin</label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Secondary Color</label>
			</td>
			<td>
				<input class="color-picker" type="text" name="collclubsports_admin[color_secondary]" value="<?php echo $options['color_secondary']?>" />
				<label class="description-wrapper">This will be the secondary color applied to shortcodes displayed by this plugin</label>
			</td>
		</tr>
	</tbody>
</table>
