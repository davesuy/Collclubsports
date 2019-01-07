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
<div class="collclubsports-admin-wrapper">
<h2>Shortcodes</h2>

<table class="form-table shortcodes">
	<tbody>
		<tr>
			<td class="field-label-wrapper">
				<label>Team List Page</label>
			</td>
			<td>
				<input class="field-input"  value="[team-list]" readonly />
				<label class="description-wrapper"></label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Conference List Page</label>
			</td>
			<td>
				<input class="field-input"  value="[conference-list]" readonly />
				<label class="description-wrapper"></label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Conference Page</label>
			</td>
			<td>
				<input class="field-input"  value="[conference]" readonly />
				<label class="description-wrapper"></label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Player List Page</label>
			</td>
			<td>
				<input class="field-input"  value="[player-list]" readonly />
				<label class="description-wrapper"></label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Player Page</label>
			</td>
			<td>
				<input class="field-input"  value="[stats-player]" readonly />
				<label class="description-wrapper"></label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Stats Page</label>
			</td>
			<td>
				<input class="field-input"  value="[stats]" readonly />
				<label class="description-wrapper"></label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Game Stats Page</label>
			</td>
			<td>
				<input class="field-input"  value="[game-stats]" readonly />
				<label class="description-wrapper"></label>
			</td>
		</tr>		
		<tr>
			<td class="field-label-wrapper">
				<label>Schedule Page</label>
			</td>
			<td>
				<input class="field-input"  value="[schedule]" readonly />
				<label class="description-wrapper"></label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Standing Page</label>
			</td>
			<td>
				<input class="field-input"  value="[standing]" readonly />
				<label class="description-wrapper"></label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Stats Tracker Component</label>
			</td>
			<td>
				<input class="field-input"  value="[stats-tracker]" readonly />
				<label class="description-wrapper">Shortcode for TOP Stats Tracker of the League.</label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Player Week Component</label>
			</td>
			<td>
				<input class="field-input"  value="[player-week type='' title='']" readonly />
				<label class="description-wrapper">Shortcode for Player of the Week. "type" attribute is required. The following are all type attributes: Baseball & Softball(1 - Player, 5 - Pitching), Basketball (1 - Player, 2 - Player 2), Football(3 - Offensive, 4 - Defensive), TrackAndField(6 - Male, 7 - Female).</label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Contact Information Component</label>
			</td>
			<td>
				<input class="field-input"  value="[contact-person]" readonly />
				<label class="description-wrapper">Shortcode for Contact Page Contact Information Component.</label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Team Information Component</label>
			</td>
			<td>
				<input class="field-input"  value="[team-information]" readonly />
				<label class="description-wrapper">Shortcode for Contact Page Team Information Component.</label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Search bar Component</label>
			</td>
			<td>
				<input class="field-input"  value="[search]" readonly />
				<label class="description-wrapper">Shortcode for Search bar Component.</label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Search result Page</label>
			</td>
			<td>
				<input class="field-input"  value="[search-results]" readonly />
				<label class="description-wrapper"></label>
			</td>
		</tr>
		<tr>
			<td class="field-label-wrapper">
				<label>Championship</label>
			</td>
			<td>
				<input class="field-input"  value="[championship-team]" readonly />
				<label class="description-wrapper"></label>
			</td>
		</tr>

	</tbody>
</table>
</div>
