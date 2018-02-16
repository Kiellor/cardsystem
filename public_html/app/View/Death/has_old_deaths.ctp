<style>
	.actiontable table { }
	.actiontable th { padding: 3px; vertical-align: top; text-align: left;}
	.actiontable td { padding: 3px; vertical-align: top; }

	.noborder table { border:none; }
	.noborder th { border:none; }
	.noborder td { border:none; }
</style>

<div id="deaths" ng-app="deathsApp">

	<script type="text/javascript">

		var deathsApp = angular.module('deathsApp',[]);

		deathsApp.controller('DeathsAppController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {
				$scope.loadOldDeathDetails();
			}

			$scope.loadOldDeathDetails = function() {
				$http({ method: 'GET', url: '/death/hasOldDeathsDetails'}).success(function(data) {
					$scope.od = data;
				});
			}
		}]);
	</script>

	<div ng-controller="DeathsAppController" ng-init="initialize()">
				
		<div>
			<h2>Old Style Deaths</h2>
			<ul>
				<li ng-repeat="r in od"><a ng-href="/death/edit/{{r.Character.cardnumber}}">{{r.Character.name}}</a></li>
			</ul>
		</div>

	</div>
</div>