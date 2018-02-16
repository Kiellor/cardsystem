<div id="cardfileActions" ng-app="cardfileApp">

	<script type="text/javascript">

		var cardfileApp = angular.module('cardfileApp',[]);

		cardfileApp.controller('CardFileController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {

				$scope.path = "";
				$scope.loaddirectory("");
			}

			$scope.loaddirectory = function(path) {
				$scope.path = path;
				$http({ method: 'GET', url: '/card_files/listfiles/'+path}).success(function(data) {
					$scope.cardfiles = data;
				});
			}

			$scope.loadparent = function() {
				$scope.loaddirectory("");
			}

		}]);
	</script>

	<div ng-controller="CardFileController" ng-init="initialize()">
		<ul>
			<li ng-hide="path == ''"><a ng-click="loadparent()">Return to Parent</a></li>
			<li ng-repeat="c in cardfiles[0]"><a ng-click="loaddirectory(c)">{{c}}</a></li>
			<li ng-repeat="c in cardfiles[1]"><a ng-href="/card_files/downloadfile/{{path}}/{{c}}">Download {{c}}</a></li>
		</ul>
	</div>
</div>
