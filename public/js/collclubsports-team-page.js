(function( $ ) {
	'use strict';

	$(document).ready(function() {
		var teamPageButtonsEl = $('.team-page-button'),
			hasFiredClickEvent = false;

		function getParameterByName(name) {
		    name = name.replace(/[\[\]]/g, "\\$&");
		    var url = window.location.href,
		    	regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
		        results = regex.exec(url);
		    if (!results) return null;
		    if (!results[2]) return '';
		    return decodeURIComponent(results[2].replace(/\+/g, " "));
		}

		function setupTab() {
			var tabEl = $('.collclubsports-component.tab-wrapper');

			if(tabEl.length > 0) {
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
		}

		function setupPagination() {
			var prevEl = jQuery('.previous-button'),
				nextEl = jQuery('.next-button'),
				_setupTbody = function(tbodyEl, currentPage) {
					for(var i=0; i < tbodyEl.length; i++) {
						$(tbodyEl[i]).removeClass('active');
					}
					$(tbodyEl[currentPage]).addClass('active');
				},
				_setupBtnClass = function(tbodyEl, currentPage) {
					if(currentPage == (tbodyEl.length - 1)) {
						nextEl.addClass('btn-disabled');
					} else {
						nextEl.removeClass('btn-disabled');
					}
					if(currentPage == 0) {
						prevEl.addClass('btn-disabled');
					} else {
						prevEl.removeClass('btn-disabled');
					}
				},
				_getTbody = function(button) {
					return $(button).parent().siblings('table').find('tbody');
				},
				_getCurrentIndex = function(button) {
					var activeEl = $(button).parent().siblings('table').find('tbody.active');
					if (activeEl != null) {
						var pageAttr = activeEl.attr('name'),
							pageNum;
						if (pageAttr != null) {
							pageNum = pageAttr.match(/\d+/)[0];
						}
						if (pageNum != null) {
							var index = parseInt(pageNum);
							return (index > 0 ? index-1 : 0);
						}
					}
					return 0;
				};

			prevEl.addClass('btn-disabled');
			prevEl.on('click', function() {
				if (hasFiredClickEvent) return;
				hasFiredClickEvent = true;
				setTimeout(function() {
					hasFiredClickEvent = false;
				}, 500);
				var prevIndex = _getCurrentIndex(this) - 1,
					tbodyEl = _getTbody(this);
				if (prevIndex < 0) {
					prevIndex = 0;
				}
				_setupTbody(tbodyEl, prevIndex);
				_setupBtnClass(tbodyEl, prevIndex);
				window.scrollTo(0,0);
			});

			nextEl.on('click', function() {
				if (hasFiredClickEvent) return;
				hasFiredClickEvent = true;
				setTimeout(function() {
					hasFiredClickEvent = false;
				}, 500);
				var nextIndex = _getCurrentIndex(this) + 1,
					tbodyEl = _getTbody(this);
				if (nextIndex >= tbodyEl.length) {
					nextIndex = tbodyEl.length-1;
				}
				_setupTbody(tbodyEl, nextIndex);
				_setupBtnClass(tbodyEl, nextIndex);
				window.scrollTo(0,0);
			});

			$('[data-toggle="tooltip"]').tooltip();
		}

		function setupTeamPage() {
			var options = {
            	type: 'GET',
            	url: collclubsports.ajax_url,
                data: {
					'teamid': getParameterByName('team'),
					'seasonid': getParameterByName('season')
				}
            };

			// Conference standing
			options.data.action = 'collclubsports_conference_standing';
			$.ajax(options).done(function (response) {
            	if (response != null && response !=0 && response.length > 0) {
            		var el = $('#conference-standing-container');
            		if (el != null) {
            			el.find('.box-body-wrapper').html(response);
            		}
            	}
            });

            // Stats tracker
            options.data.action = 'collclubsports_stats_tracker';
            $.ajax(options).done(function (response) {
            	if (response != null && response !=0 && response.length > 0) {
            		var el = $('#stats-tracker-container');
            		if (el != null) {
            			el.find('.box-body-wrapper').html(response);
            		}
            	}
            });

            // Player of the week Ads
            options.data.action = 'collclubsports_player_of_the_week_ad';
            $.ajax(options).done(function (response) {
            	if (response != null && response !=0 && response.length > 0) {
            		$('#team-player-week-container').html(response);
            	}
            });

            // Team schedule
            options.data.action = 'collclubsports_team_schedule';
            $.ajax(options).done(function (response) {
            	if (response != null && response !=0 && response.length > 0) {
            		var el = $('#team-schedule-container');
            		if (el != null) {
            			el.find('.box-body-wrapper').html(response);
            			setupPagination();
            		}
            	}
            });

            // Team roster
            options.data.action = 'collclubsports_team_roster';
            $.ajax(options).done(function (response) {
            	if (response != null && response !=0 && response.length > 0) {
            		var el = $('#team-roster-container');
            		if (el != null) {
            			el.find('.box-body-wrapper').html(response);
            			setupPagination();
            		}
            	}
            });

            // Team stats
            // Baseball
            if (collclubsports.sport_type == 1) {
            	var el = $('#team-stats-container');
            	options.data.action = 'collclubsports_baseball_hitting_stats';
	            $.ajax(options).done(function (res1) {
	            	if (res1 != null && res1 !=0 && res1.length > 0) {
	            		if (el != null) {
	            			$(el).find('[name=baseball-hitting-stats-table]').html(res1);
	            			setupPagination();
	            			setupTab();
	            		}
	            	}
	            	options.data.action = 'collclubsports_baseball_pitching_stats';
		            $.ajax(options).done(function (res2) {
		            	if (res2 != null && res2 !=0 && res2.length > 0) {
		            		if (el != null) {
		            			$(el).find('[name=baseball-pitching-stats-table]').html(res2);
		            			setupPagination();
		            			setupTab();
		            		}
		            	}
		            });
	            });
            }
            // Softball
            else if (collclubsports.sport_type == 2) {
            	var el = $('#team-stats-container');
            	options.data.action = 'collclubsports_softball_hitting_stats';
	            $.ajax(options).done(function (res1) {
	            	if (res1 != null && res1 !=0 && res1.length > 0) {
	            		if (el != null) {
	            			$(el).find('[name=softball-hitting-stats-table]').html(res1);
	            			setupPagination();
	            			setupTab();
	            		}
	            	}
	            	options.data.action = 'collclubsports_softball_pitching_stats';
		            $.ajax(options).done(function (res2) {
		            	if (res2 != null && res2 !=0 && res2.length > 0) {
		            		if (el != null) {
		            			$(el).find('[name=softball-pitching-stats-table]').html(res2);
		            			setupPagination();
		            			setupTab();
		            		}
		            	}
		            });
	            });
            }
            // Basketball
            else if (collclubsports.sport_type == 3) {
            	var el = $('#team-stats-container');
            	options.data.action = 'collclubsports_basketball_stats';
	            $.ajax(options).done(function (res1) {
	            	if (res1 != null && res1 !=0 && res1.length > 0) {
	            		if (el != null) {
	            			$(el).find('[name=basketball-stats-table]').html(res1);
	            			setupPagination();
	            			setupTab();
	            		}
	            	}
	            });
            }
            // Football
            else if (collclubsports.sport_type == 4) {
            	var el = $('#team-stats-container');
            	options.data.action = 'collclubsports_football_offensive_team_stats';
	            $.ajax(options).done(function (res1) {
	            	if (res1 != null && res1 !=0 && res1.length > 0) {
	            		if (el != null) {
	            			$(el).find('[name=football-offensive-stats-table]').html(res1);
	            			setupPagination();
	            			setupTab();
	            		}
	            	}
	            	options.data.action = 'collclubsports_football_defensive_team_stats';
		            $.ajax(options).done(function (res2) {
		            	if (res2 != null && res2 !=0 && res2.length > 0) {
		            		if (el != null) {
		            			$(el).find('[name=football-defensive-stats-table]').html(res2);
		            			setupPagination();
		            			setupTab();
		            		}
		            	}
		            	options.data.action = 'collclubsports_football_special_team_stats';
			            $.ajax(options).done(function (res3) {
			            	if (res3 != null && res3 !=0 && res3.length > 0) {
			            		if (el != null) {
			            			$(el).find('[name=football-special-team-stats-table]').html(res3);
			            			setupPagination();
			            			setupTab();
			            		}
			            	}
			            });
		            });
	            });
            }
		}

		if(teamPageButtonsEl.length > 0) {
			setupTeamPage();
		}

	});

})( jQuery );

