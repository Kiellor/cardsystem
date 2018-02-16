<style>
	pre { margin: 0px; }

	.skilltable { border-collapse: collapse; }
	.skilltable th { border: 1px solid black; padding: 3px; vertical-align: top;}
	.skilltable td { border: 1px solid black; padding: 3px; vertical-align: top;}
</style>

<div ng-app="myApp">
  
	<script type="text/javascript">
		
		var myApp = angular.module('myApp',[]);

		myApp.controller('NPMController',['$scope','$http', function($scope, $http) {

			$scope.initialize = function() {
				$scope.list_id = <?php echo $list_id; ?>

				$http({ method: 'GET', url: '/elists/getlist/'+$scope.list_id}).success(function(data) {
					$scope.list = data.list.Elist;
					$scope.skills = data.skills;
				});
			}

			$scope.saveSkill = function(skill) {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/elists/saveskill/',
					data: skill
				}).success(function(data) {
					$scope.debug = data;
				});
			}

			$scope.saveAll = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/elists/saveallskills/',
					data: $scope.skills
				}).success(function(data) {
					$scope.debug = data;
				});
			}

		}]);
	</script>


	<div ng-controller="NPMController" ng-init="initialize()">

		<h3>Edit List {{list.list_name}}</h3>
		<a href="/elists/">Back to All Lists</a><br/>
		<a ng-href="/elists/edit/{{list.id}}">Add or Remove Skills</a>

		<table>
			<tr>
				<th>id</th>
				<th>ability name</th>
				<th>ability option</th>
				<th>build cost</th>
				<th>pre-requisites</th>
				<th>sort order</th>
				<th>footnote</th>
				<th>free set</th>
				<th>free set limit</th>
			</tr>
			<tr ng-repeat="skill in skills">
				<td>{{skill.a.id}}</td>
				<td>{{skill.a.ability_name}}</td>
				<td>{{skill.alo.ability_name}}</td>
				<td>{{skill.la.build_cost}}</td>
				<td><input type="text" size="40" ng-model="skill.la.prerequisites"/></td>
				<td><input type="text" size="3" ng-model="skill.la.sort_order"/></td>
				<td><input type="text" size="3" ng-model="skill.la.is_footnote"/><input type="text" size="40" ng-model="skill.la.footnote"/></td>
				<td><input type="text" size="3" ng-model="skill.la.free_set"/></td>
				<td><input type="text" size="3" ng-model="skill.la.free_set_limit"/></td>
			</tr>
		</table>

		<button ng-click="saveAll()">Save All</button>

	</div>

</div>

