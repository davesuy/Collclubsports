(function( $ ) {
	'use strict';

	$(document).ready(function() {
		var conferencePageEl = $('.conference-page.collclubsports-page-wrapper');

		function getParameterByName(name) {
		    name = name.replace(/[\[\]]/g, "\\$&");
		    var url = window.location.href,
		    	regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
		        results = regex.exec(url);
		    if (!results) return null;
		    if (!results[2]) return '';
		    return decodeURIComponent(results[2].replace(/\+/g, " "));
		}

		function setupConferencePage() {
			var options = {
    	   type: 'GET',
         url: collclubsports.ajax_url,
         data: {
					'seasonid': getParameterByName('season'),
					'conferenceid': getParameterByName('conference'),
					'sporttype': collclubsports.sport_type
		    }
      };

      // Conference standings
			options.data.action = 'collclubsports_conference_standings';
			$.ajax(options).done(function (response) {
      	if (response != null && response !=0 && response.length > 0) {
      		var el = conferencePageEl.find('.team-standing-box-wrapper');
      		if (el != null) {
      			el.replaceWith(response);
      		}
      	}
      });

      // Conference stats tracker
			options.data.action = 'collclubsports_conference_stats_tracker';
			$.ajax(options).done(function (response) {
      	if (response != null && response !=0 && response.length > 0) {
      		var el = conferencePageEl.find('.stats-tracker-box-wrapper .box-body-wrapper');
      		if (el != null) {
      			el.html(response);
      		}
      	}
      });

			// Conference schedules
			options.data.action = 'collclubsports_conference_schedule';
			$.ajax(options).done(function (response) {
      	if (response != null && response !=0 && response.length > 0) {
      		var el = conferencePageEl.find('.schedule-box-wrapper');
      		if (el != null) {
      			el.replaceWith(response);
      		}
      	}
      });

      // Conference player of the week and ads
			options.data.action = 'collclubsports_conference_player_of_the_week_ads';
			$.ajax(options).done(function (response) {
      	if (response != null && response !=0 && response.length > 0) {
      		var el = conferencePageEl.find('.player-of-the-week-ads-container');
      		if (el != null) {
      			el.html(response);
      		}
      	}
      });

		}

		if (conferencePageEl.length > 0) {
			setupConferencePage();
		}

	});

})( jQuery );