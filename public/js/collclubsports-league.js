(function( $ ) {
'use strict';

	var homeSelectLeagueEl = jQuery('.home #select-league'),
      contactSelectLeagueEl = jQuery('.contact-page #select-league');

	homeSelectLeagueEl.on('change', function() {
		var leagueid = this.value,
			options = {
            	type: 'GET',
            	url: collclubsports.ajax_url,
                data: {
					'leagueid': leagueid
				}
            };

		// Home page POW and Ads
		options.data.action = 'collclubsports_home_pow_ads';
		jQuery.ajax(options).done(function (response) {
        	if (response != null && response !=0 && response.length > 0) {
        		var el = $('#home-pow-ads');
        		if (el != null) {
        			el.find('.pow-ads-container').html(response);
        		}
        	}
        });
	});

	contactSelectLeagueEl.on('change', function() {
		var leagueid = this.value,
			options = {
            	type: 'GET',
            	url: collclubsports.ajax_url,
                data: {
					'leagueid': leagueid
				}
            };

		// Contact page contact info
		options.data.action = 'collclubsports_league_contacts';
		jQuery.ajax(options).done(function (response) {
        	if (response != null && response !=0 && response.length > 0) {
        		var el = $('.contact-page');
        		if (el != null) {
        			el.find('.league-contacts-container').html(response);
        		}
        	}
        });

        // Contact page team info
		options.data.action = 'collclubsports_league_team_information';
		jQuery.ajax(options).done(function (response) {
        	if (response != null && response !=0 && response.length > 0) {
        		var el = $('.contact-page');
        		if (el != null) {
        			el.find('.collclubsports-component.team-information').replaceWith(response);
        		}
        	}
        });
	});

})( jQuery );