<div id="warActions" ng-app="warActionsApp">

	<script type="text/javascript">

		var warActionsApp = angular.module('warActionsApp',[]);

		warActionsApp.controller('WarActionsController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {
				$scope.loadStats();
			}

			$scope.loadStats = function() {
				$http({ method: 'GET', url: '/personal_action/warstats/'}).success(function(data) {
					$scope.stats = data;
				});
			}

		}]);
	</script>

	<div ng-controller="WarActionsController" ng-init="initialize()">
		
		<table>
			<tr><td>Running</td><td>{{stats.run}}</td></tr>
			<tr><td>Hiding</td><td>{{stats.hide}}</td></tr>
			<tr><td>Fighting</td><td>{{stats.fight}}</td></tr>
			<tr><td>Total</td><td>{{stats.total}}</td></tr>
		</table>
		
	</div>
</div>