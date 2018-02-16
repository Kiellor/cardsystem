<div id="rewrite" ng-app="rewriteApp">

	<script type="text/javascript">

		var rewriteApp = angular.module('rewriteApp',[]);

		rewriteApp.controller('RewriteController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {
				$http({ method: 'GET', url: '/rewrite/listStragglers'}).success(function(data) {
					$scope.proposals = data;

					if($scope.proposals.length > 0) {
						location.href = "/rewrite/index/"+$scope.proposals[0].c.cardnumber;
					}
				});
			}
		}]);
	</script>

	<div ng-controller="RewriteController" ng-init="initialize()">
		<ul>
			<li ng-repeat="p in proposals"><a ng-href="/rewrite/index/{{p.c.cardnumber}}">{{p.c.name}} ({{p.c.cardnumber}})</a></li>
		</ul>
	</div>
</div>