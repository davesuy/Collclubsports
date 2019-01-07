(function( $ ) {
	'use strict';

	$(document).ready(function() {
		var statsPage = $('#stats-page'),
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

		function setupStatsPage() {
			var tabButtons = statsPage.find('.tab-button-wrapper.main-buttons .button[target]');

			var parentTarget = getParameterByName('type'),
				subTarget = getParameterByName('subtype');

			if (parentTarget || subTarget) {
				// Baseball
            	if (collclubsports.sport_type == 1) {
            		switch(parentTarget) {
	            		case 'pitching':
	            			parentTarget = 'baseball-pitching-stats-table';
	            			break;
	            		default:
	            			parentTarget = 'baseball-hitting-stats-table';
	            			break;
	            	}
				}
	            // Softball
	            else if (collclubsports.sport_type == 2) {
	            	switch(parentTarget) {
	            		case 'pitching':
	            			parentTarget = 'softball-pitching-stats-table';
	            			break;
	            		default:
	            			parentTarget = 'softball-hitting-stats-table';
	            			break;
	            	}
				}
	            // Basketball
	            else if (collclubsports.sport_type == 3) {
	            	
	            }
	            // Football
	            else if (collclubsports.sport_type == 4) {
	            	var defaultSubTarget;
					switch (parentTarget) {
						case 'defensive':
							parentTarget = 'football-defensive-stats-table';
							defaultSubTarget = 'defense-stats';
							break;
						case 'special-team':
							parentTarget = 'football-special-team-stats-table';
							defaultSubTarget = 'punting-stats';
							break;
						default:
							parentTarget = 'football-offensive-stats-table';
							defaultSubTarget = 'passing-stats';
					}
					if (!subTarget) {
						subTarget = defaultSubTarget;
					}
				}
				$('div').removeClass('active');
				var statsTable = $('[name=' + parentTarget + ']'),
					statsTableBtn = $('[target=' + parentTarget + ']'),
					subStatsTable,
					subStatsTableBtn;
				if (subTarget) {
					subStatsTable = $('[name=' + subTarget + ']'),
					subStatsTableBtn = $('[target=' + subTarget + ']');
				}
				if (statsTable) {
					statsTable.addClass('active');
				}
				if (statsTableBtn) {
					statsTableBtn.addClass('active');
				}
				if (subStatsTable) {
					subStatsTable.addClass('active');
				}
				if (subStatsTableBtn) {
					subStatsTableBtn.addClass('active');
				}
			} else {
				// Set default active tab
				var statsTable = $('.stats-table-container');
				if (statsTable && statsTable.length > 0) {
					$(statsTable[0]).addClass('active');
				}
				$(tabButtons[0]).addClass('active');
			}

			if (tabButtons && tabButtons.length > 0) {
				// Handle click event
				tabButtons.on('click', function() {
					var btn = $(this),
						tabEls = $('#stats-page').find('.stats-table-container'),
						target = btn.attr('target'),
						targetEl;

					// Remove current active
					for (var i = 0; i < tabEls.length; i++) {
						$(tabEls[i]).removeClass('active');
						if ($(tabEls[i]).attr('name') == target) {
							targetEl = tabEls[i];
						}
					}

					btn.addClass('active');

					if (targetEl) {
						$(targetEl).addClass('active');
						var childTable = $(targetEl).find('.tab-content-wrapper'),
							childActiveTable = $(targetEl).find('.tab-content-wrapper.active');
						if (   childActiveTable && childActiveTable.length == 0 
							&& childTable && childTable.length > 0) {
							var subtable = $(childTable[0]).attr('name');
							$('[name=' + subtable + ']').addClass('active');
							$('[target=' + subtable + ']').addClass('active');
						}
					}
				});	
			}

			var options = {
            	type: 'GET',
            	url: collclubsports.ajax_url,
                data: {
					'seasonid': getParameterByName('season'),
					'teamid': collclubsports.team_in_season != null && collclubsports.team_in_season != '' ? getParameterByName('team') : null,
					'gameid': getParameterByName('game'),
					'playerid': getParameterByName('player'),
					'sort': getParameterByName('sort'),
					'sorttype': getParameterByName('sorttype'),
					'disablesort': collclubsports.disablesort
				}
            };

            if (collclubsports.playerid != null) {
            	options.data.playerid = collclubsports.playerid;
            }

            // Stats
			// Baseball
            if (collclubsports.sport_type == 1) {
            	options.data.action = 'collclubsports_baseball_stats_page_hitting_stats';
	            $.ajax(options).done(function (res) {
	            	if (res != null && res !=0 && res.length > 0) {
            			$(statsPage).find('[name=baseball-hitting-stats-table]').html(res);
            			setupPagination();
	            	}
	            });

	            options.data.action = 'collclubsports_baseball_stats_page_pitching_stats';
	            $.ajax(options).done(function (res) {
	            	if (res != null && res !=0 && res.length > 0) {
            			$(statsPage).find('[name=baseball-pitching-stats-table]').html(res);
            			setupPagination();
	            	}
	            });
            }
            // Softball
            else if (collclubsports.sport_type == 2) {
            	options.data.action = 'collclubsports_softball_stats_page_hitting_stats';
	            $.ajax(options).done(function (res) {
	            	if (res != null && res !=0 && res.length > 0) {
            			$(statsPage).find('[name=softball-hitting-stats-table]').html(res);
            			setupPagination();
	            	}
	            });

	            options.data.action = 'collclubsports_softball_stats_page_pitching_stats';
	            $.ajax(options).done(function (res) {
	            	if (res != null && res !=0 && res.length > 0) {
            			$(statsPage).find('[name=softball-pitching-stats-table]').html(res);
            			setupPagination();
	            	}
	            });
            }
            // Basketball
            else if (collclubsports.sport_type == 3) {
            	
            }
            // Football
            else if (collclubsports.sport_type == 4) {
            	options.data.action = 'collclubsports_football_offensive_passing_stats';
	            $.ajax(options).done(function (res) {
	            	if (res != null && res !=0 && res.length > 0) {
            			$(statsPage).find('[name=passing-stats]').html(res);
            			setupPagination();
	            	}
	            });

	            options.data.action = 'collclubsports_football_offensive_rushing_stats';
	            $.ajax(options).done(function (res) {
	            	if (res != null && res !=0 && res.length > 0) {
            			$(statsPage).find('[name=rushing-stats]').html(res);
            			setupPagination();
	            	}
	            });

	            options.data.action = 'collclubsports_football_offensive_receiving_stats';
	            $.ajax(options).done(function (res) {
	            	if (res != null && res !=0 && res.length > 0) {
            			$(statsPage).find('[name=receiving-stats]').html(res);
            			setupPagination();
	            	}
	            });

	            options.data.action = 'collclubsports_football_offensive_fieldgoal_stats';
	            $.ajax(options).done(function (res) {
	            	if (res != null && res !=0 && res.length > 0) {
            			$(statsPage).find('[name=field-goal-stats]').html(res);
            			setupPagination();
	            	}
	            });

	            options.data.action = 'collclubsports_football_defensive_defense_stats';
	            $.ajax(options).done(function (res) {
	            	if (res != null && res !=0 && res.length > 0) {
            			$(statsPage).find('[name=defense-stats]').html(res);
            			setupPagination();
	            	}
	            });

	            options.data.action = 'collclubsports_football_defensive_interception_stats';
	            $.ajax(options).done(function (res) {
	            	if (res != null && res !=0 && res.length > 0) {
            			$(statsPage).find('[name=intercept-stats]').html(res);
            			setupPagination();
	            	}
	            });

	            options.data.action = 'collclubsports_football_special_team_punting_stats';
	            $.ajax(options).done(function (res) {
	            	if (res != null && res !=0 && res.length > 0) {
            			$(statsPage).find('[name=punting-stats]').html(res);
            			setupPagination();
	            	}
	            });

	            options.data.action = 'collclubsports_football_special_team_punt_return_stats';
	            $.ajax(options).done(function (res) {
	            	if (res != null && res !=0 && res.length > 0) {
            			$(statsPage).find('[name=punt-return-stats]').html(res);
            			setupPagination();
	            	}
	            });

	            options.data.action = 'collclubsports_football_special_team_kick_return_stats';
	            $.ajax(options).done(function (res) {
	            	if (res != null && res !=0 && res.length > 0) {
            			$(statsPage).find('[name=kick-return-stats]').html(res);
            			setupPagination();
	            	}
	            });
            }

		}

		if(statsPage && statsPage.length > 0) {
			setupStatsPage();
		}

	});

})( jQuery );

