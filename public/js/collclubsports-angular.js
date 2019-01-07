angular.module('ccs', ['textAngular'])
.controller("CCSCtrl", CCSCtrl);

function CCSCtrl($scope) {
		$scope.show=true;
		$scope.htmlContent = jQuery('#ccs-content').html();
	};