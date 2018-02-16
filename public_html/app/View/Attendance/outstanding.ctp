<div id="attendance" ng-app="attendanceApp">

	<script type="text/javascript">

		var attendanceApp = angular.module('attendanceApp',[]);

		attendanceApp.controller('AttendanceController',['$scope','$http', function($scope,$http) {
		
			$scope.initialize = function() {
				$scope.loadOutstanding();
			}

			$scope.loadOutstanding = function() {
				$http({ method: 'GET', url: '/attendance/loadOutstanding'}).success(function(data) {
					$scope.outstanding = data;
				});
			}

		}]);
	</script>

	<div ng-controller="AttendanceController" ng-init="initialize()">

		<h3>Cards Waiting to be Updated {{outstanding.length}}</h3>
		<ul>
			<li ng-repeat="o in outstanding">
				<a ng-href="/players/view/{{o.Player.id}}">{{o.Player.name}}</a> -- 
				<a ng-href="/characters/view/{{o.Character.cardnumber}}">{{o.Character.name}} ({{o.Character.cardnumber}})</a>
			</li>
		</ul>
	</div>

</div>