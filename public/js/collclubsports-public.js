(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note that this assume you're going to use jQuery, so it prepares
	 * the $ function reference to be used within the scope of this
	 * function.
	 *
	 * From here, you're able to define handlers for when the DOM is
	 * ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * Or when the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and so on.
	 *
	 * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
	 * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
	 * be doing this, we should try to minimize doing that in our own work.
	 */

	// Sanity check
	if (!window.Utils) {
	    window.Utils = {};
	}

	Utils = ({
		getParameterByName: function(name) {
			 name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
		        results = regex.exec(location.search);
		    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
		}
	});

	$(document).ready(function() {
		var selectSeasonEl = $('#select-season'),
			SchedselectSeasonEl = $('#sched-select-season'),
			selectConferenceEl = $('#select-conference'),
			SchedselectConferenceEl = $('#sched-select-conference'),
			selectWeekEl = $('#select-week'),
			SchedselectWeekEl = $('#sched-select-week'),
			selectTeamEl = $('#select-team'),
			SchedselectTeamEl = $('#sched-select-team'),
			selectPlayerEl = $('#select-player'),
			paginationEl = $('.collclubsports-component.pagination-wrapper'),
			tabEl = $('.collclubsports-component.tab-wrapper'),
			statsTrackerEl = $('.stats-tracker-box-wrapper'),
			teamPageButtonsEl = $('.team-page-button'),
			params = {},
			currentPage = 0,
			baseballStatsButtonsEl = $('.baseball-stats-button'),
			softballStatsButtonsEl = $('.softball-stats-button'),
			footballStatsButtonsEl = $('.football-stats-button'),
			getCommonParams = function() {
				var season = Utils.getParameterByName('season'),
					conference = Utils.getParameterByName('conference'),
					team = Utils.getParameterByName('team'),
					player = Utils.getParameterByName('player'),
					week = Utils.getParameterByName('week'),
					params = {};

				if (season != null && season != '') params['season'] = season;
				if (conference != null && conference != '') params['conference'] = conference;
				if (team != null && team != '') params['team'] = team;
				if (player != null && player != '') params['player'] = player;
				if (week != null && week != '') params['week'] = week;

				return params;
			};


		// var team_submit = $('#team-submit');

		// team_submit.on('click', function() {
	
		// 	params = getCommonParams();
		// 	params['season'] = selectSeasonEl.val();
		// 	params['conference'] = selectConferenceEl.val();
		// 	window.location.search = jQuery.param(params);
		// });

		// var conference_submit = $('#conference-submit');

		// conference_submit.on('click', function() {
		
		// 	params = getCommonParams();
		// 	params['season'] = selectSeasonEl.val();
		// 	window.location.search = jQuery.param(params);
		// });

		// var stats_submit = $('#stats-submit');

		// stats_submit.on('click', function() {
		// 	params = getCommonParams();
		// 	params['season'] = selectSeasonEl.val();
		// 	params['team'] = selectTeamEl.val();

		// 	window.location.search = jQuery.param(params);
		// });



		// var team_standing_submit = $('#team-standing-submit');

		// team_standing_submit.on('click', function() {

		// 	params = getCommonParams();
		// 	params['season'] = selectSeasonEl.val();
		
		// 	window.location.search = jQuery.param(params);
		// });

		$('#reset-submit').click(function(){
			//alert(1);
        	$('.sched-select-season, .sched-select-week, .sched-select-team, .sched-select-conference').prop('selectedIndex',0);
    	});


		var schedule_submit = $('#schedule-submit');

		schedule_submit.on('click', function() {
			params = getCommonParams();
			params['season'] =  $('.sched-select-season').val();
			params['week'] =  $('.sched-select-week').val();
			params['team'] =  $('.sched-select-team').val();
			params['conference'] =  $('.sched-select-conference').val();

			window.location.search = jQuery.param(params);
		});

		$('.sched-select-conference').on('change', function() {
			params = getCommonParams();
			params['season'] =  $('.sched-select-season').val();
			params['week'] =  $('.sched-select-week').val();
			params['conference'] = $('.sched-select-conference').val();
			window.location.search = jQuery.param(params);
		});


		selectSeasonEl.on('change', function() {
			params = getCommonParams();
			params['season'] = this.value;
			window.location.search = jQuery.param(params);
		});

		selectConferenceEl.on('change', function() {
			params = getCommonParams();
			params['conference'] = this.value;
			window.location.search = jQuery.param(params);
		});

		selectWeekEl.on('change', function() {
			params = getCommonParams();
			params['week'] = this.value;
			window.location.search = jQuery.param(params);
		});

		selectTeamEl.on('change', function() {
			params = getCommonParams();
			params['team'] = this.value;
			window.location.search = jQuery.param(params);
		});

		selectPlayerEl.on('change', function() {
			params = getCommonParams();
			params['player'] = this.value;
			window.location.search = jQuery.param(params);
		});

		function init() {
			var seasonParam = Utils.getParameterByName('season'),
				conferenceParam = Utils.getParameterByName('conference'),
				teamParam = Utils.getParameterByName('team'),
				weekParam = Utils.getParameterByName('week'),
				typeParam = Utils.getParameterByName('type'),
				playerParam = Utils.getParameterByName('player'),
				allParam = Utils.getParameterByName('all');

			if(seasonParam && !allParam) {
				selectSeasonEl.val(seasonParam);
				SchedselectSeasonEl.val(seasonParam);
				params['season'] = seasonParam;
			}

			if(conferenceParam) {
				selectConferenceEl.val(conferenceParam);
				SchedselectConferenceEl.val(conferenceParam);
				params['conference'] = conferenceParam;
				if(selectConferenceEl.val() == null) {
					selectConferenceEl.val('');
					
				}

				if(SchedselectConferenceEl.val() == null) {
					
					SchedselectConferenceEl.val('');
				}
			}

			if(teamParam) {
				selectTeamEl.val(teamParam);
				SchedselectTeamEl.val(teamParam);

				params['team'] = teamParam;
				if(selectTeamEl.val() == null) {
					selectTeamEl.val('');
				}

				if(SchedselectTeamEl.val() == null) {
					SchedselectTeamEl.val('');
				}
			}

			if(weekParam) {
				selectWeekEl.val(weekParam);
				SchedselectWeekEl.val(weekParam);
				params['week'] = weekParam;
				if(selectWeekEl.val() == null) {
					selectWeekEl.val('');
				}

				if(SchedselectWeekEl.val() == null) {
					SchedselectWeekEl.val('');
				}
			}

			if(playerParam) {
				selectPlayerEl.val(playerParam);
				params['player'] = playerParam;
				if(selectPlayerEl.val() == null) {
					selectPlayerEl.val('');
				}
			}

			if(typeParam) {
				params['type'] = typeParam;
			}

			if(allParam && !allParam) {
				selectConferenceEl.val(allParam);
				SchedselectConferenceEl.val(allParam);
				params = {};
				params['all'] = allParam;
			}

			if(paginationEl.length > 0) {
				setupPagination();
			}

			if(tabEl.length > 0) {
				setupTab();
			}

			if(statsTrackerEl.length > 0) {
				setupStatsTracker();
			}

			if(teamPageButtonsEl.length > 0) {
				setupTeamPage();
			}

			if(baseballStatsButtonsEl.length > 0) {
				setupTeamPageStatsTab('baseball');
			}

			if(softballStatsButtonsEl.length > 0) {
				setupTeamPageStatsTab('softball');
			}

			if(footballStatsButtonsEl.length > 0) {
				setupTeamPageStatsTab('football');
			}

			$('[data-toggle="tooltip"]').tooltip();
		}

		function setupPagination() {
			currentPage = 1;
			var prevEl = jQuery('.previous-button'),
				nextEl = jQuery('.next-button');

			prevEl.addClass('btn-disabled');
			prevEl.on('click', function() {
				currentPage--;
				setupTbody(getTbody(this));
				setupBtnClass(getTbody(this));
				window.scrollTo(0,0);
			});

			nextEl.on('click', function() {
				currentPage++;
				setupTbody(getTbody(this));
				setupBtnClass(getTbody(this));
				window.scrollTo(0,0);
			});

			function setupTbody(tbodyEl) {
				for(var i=0; i < tbodyEl.length; i++) {
					$(tbodyEl[i]).removeClass('active');
				}
				$(tbodyEl[currentPage-1]).addClass('active');
			}

			function setupBtnClass(tbodyEl) {
				if(currentPage == tbodyEl.length) {
					nextEl.addClass('btn-disabled');
				} else {
					nextEl.removeClass('btn-disabled');
				}
				if(currentPage == 1) {
					prevEl.addClass('btn-disabled');
				} else {
					prevEl.removeClass('btn-disabled');
				}
			}

			function getTbody(button) {
				return $(button).parent().siblings('table').find('tbody');
			}
		}

		function setupTab() {
			var tabButtons = $('.tab-button-wrapper .button[target]');
			tabButtons.on('click', function() {
				var btn = $(this),
					tabEl = btn.parents('.tab-wrapper'),
					target = btn.attr('target');
				// Remove current active
				$(tabEl.children()[0]).children().removeClass('active');
				$(tabEl.children()[1]).children().removeClass('active');

				btn.addClass('active');
				 $(tabEl.children()[1]).find('[name=' + target + ']').addClass('active');
			});
		}

		function setupStatsTracker() {
			for(var i=0; i < statsTrackerEl.length; i++){
				var el = $(statsTrackerEl[i]);
				setInterval(function() {
					var children = el.find('.box-body-wrapper').children(),
						currentActive = el.find('.box-body-wrapper').find('.active'),
						currentIndex = children.index(currentActive);
					currentIndex++;
					if(children.length == currentIndex) {
						currentIndex = 0;
					}
					children.removeClass('active');
					currentActive.fadeOut('slow', function() {
						$(children[currentIndex]).addClass('active');
						$(children[currentIndex]).fadeIn();
					});
				}, 5000);
			}
		}

		function setupTeamPage() {
			var paginationEl = $('.collclubsports-pagination'),
				backMainButton = $('.back-main-button'),
				hash = window.location.hash.replace('#', '');

			teamPageButtonsEl.on('click', function() {
				showPage($(this).attr('target'));
			});

			if(hash && paginationEl.find('[name=' + hash + ']').length > 0){
				showPage(hash);
			} else {
				showPage('team-main');
			}

			function showPage (target) {
				$(paginationEl.children()).removeClass('active');
				
				paginationEl.find('[name=' + target + ']').addClass('active');

				if(target != 'team-main') {
					backMainButton.addClass('show');
				} else {
					backMainButton.removeClass('show');
				}
				window.location.hash = target;
			}
		}

		function setupTeamPageStatsTab(sport_type) {
			var teamPageStatsTabEl = $('.team-page-stats-tab'),
				defaultTab,
				statsButtonEl;

			switch (sport_type) {
				case 'baseball': {
					defaultTab = 'baseball-hitting-stats-table';
					statsButtonEl = baseballStatsButtonsEl;
					break;
				}
				case 'softball': {
					defaultTab = 'softball-hitting-stats-table';
					statsButtonEl = softballStatsButtonsEl;
					break;
				}
				case 'football': {
					defaultTab = 'football-offensive-stats-table';
					statsButtonEl = footballStatsButtonsEl;
					break;
				}
			}

			// Show default active tab
			if (!$(teamPageStatsTabEl.children()).hasClass('active')) {
				_showTab(defaultTab);
			}

			statsButtonEl.on('click', function() {
				_showTab($(this).attr('target'));
			});

			function _showTab(target) {
				$(teamPageStatsTabEl.children()).removeClass('active');
				$(statsButtonEl).removeClass('active');
				teamPageStatsTabEl.find('[name=' + target + ']').addClass('active');
				teamPageStatsTabEl.find('[target=' + target + ']').addClass('active');
			}
		}
		init();
	});

})( jQuery );

