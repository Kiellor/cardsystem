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
				$scope.loadEvents();
				$scope.loadOldDeaths();
			}

			$scope.loadOldDeaths = function() {
				$http({ method: 'GET', url: '/death/loadOldDeaths/<?php echo $cardnumber ?>'}).success(function(data) {
					$scope.od = data;
				});
			}

			$scope.loadEvents = function() {
				$http({ method: 'GET', url: '/death/loadevents'}).success(function(data) {
					$scope.events = data;

					$scope.loadCharacter();					
				});
			}

			$scope.loadCharacter = function() {
				$http({ method: 'GET', url: '/death/load/<?php echo $cardnumber ?>'}).success(function(data) {
					$scope.c = data.Character;

					for(i = 0; i < $scope.events.length; i++) {
						var eid = $scope.events[i].Events.id;
						var ename = $scope.events[i].Events.name;

						if($scope.c.resurrect_1 == eid) {
							$scope.c.resurrect_1_event = ename;
						}
						if($scope.c.resurrect_2 == eid) {
							$scope.c.resurrect_2_event = ename;
						}
						if($scope.c.resurrect_3 == eid) {
							$scope.c.resurrect_3_event = ename;
						}
						if($scope.c.resurrect_4 == eid) {
							$scope.c.resurrect_4_event = ename;
						}

						if($scope.c.reanimate_1 == eid) {
							$scope.c.reanimate_1_event = ename;
						}
						if($scope.c.reanimate_2 == eid) {
							$scope.c.reanimate_2_event = ename;
						}
						if($scope.c.reanimate_3 == eid) {
							$scope.c.reanimate_3_event = ename;
						}
						if($scope.c.reanimate_4 == eid) {
							$scope.c.reanimate_4_event = ename;
						}
						if($scope.c.reanimate_5 == eid) {
							$scope.c.reanimate_5_event = ename;
						}

						if($scope.c.cheatdeath_1 == eid) {
							$scope.c.cheatdeath_1_event = ename;
							$scope.hascheatdeath = true;
						}
						if($scope.c.cheatdeath_2 == eid) {
							$scope.c.cheatdeath_2_event = ename;
							$scope.hascheatdeath = true;
						}
						if($scope.c.cheatdeath_3 == eid) {
							$scope.c.cheatdeath_3_event = ename;
							$scope.hascheatdeath = true;
						}
						if($scope.c.cheatdeath_4 == eid) {
							$scope.c.cheatdeath_4_event = ename;
							$scope.hascheatdeath = true;
						}
						if($scope.c.cheatdeath_5 == eid) {
							$scope.c.cheatdeath_5_event = ename;
							$scope.hascheatdeath = true;
						}

						if($scope.c.empathy_rank4 == eid) {
							$scope.c.empathy_rank4_event = ename;
						}
						if($scope.c.finaldeath == eid) {
							$scope.c.finaldeath_event = ename;
						}
					}
				});
			}

		}]);
	</script>

	<div ng-controller="DeathsAppController" ng-init="initialize()">
		<h2>Deaths for {{c.name}} ({{c.cardnumber}})</h2>

		<div id="CardOptions">
		<ul>
			<li><a ng-href="/characters/view/{{c.cardnumber}}">View Character</a>
		</ul>
		</div>
		<br/>

		<span ng-show="c.finaldeath != null">Character experienced final death -- {{c.finaldeath_event}}</span>
		<br/>
		<span ng-show="c.empathy_rank4 != null">Character was saved by Empathy Rank IV -- {{c.empathy_rank4_event}}</span>

		<table>
			<tr>
				<th>Life #</th>
				<th>Reanimated?</th>
				<th ng-show="hascheatdeath">Cheated Death?</th>
				<th>Resurrected?</th>
			</tr>
			<tr>
				<th>1</th>
				<th>
					<span ng-show="c.reanimate_1 != null">{{c.reanimate_1_event}}</span>
				</th>
				<th ng-show="hascheatdeath">
					<span ng-show="c.cheatdeath_1 != null">{{c.cheatdeath_1_event}}</span>
				</th>
				<th>
					<span ng-show="c.resurrect_1 != null">{{c.resurrect_1_event}}</span>
				</th>
			</tr>
			<tr>
				<th>2</th>
				<th>
					<span ng-show="c.reanimate_2 != null">{{c.reanimate_2_event}}</span>
				</th>
				<th ng-show="hascheatdeath">
					<span ng-show="c.cheatdeath_2 != null">{{c.cheatdeath_2_event}}</span>
				</th>
				<th>
					<span ng-show="c.resurrect_2 != null">{{c.resurrect_2_event}}</span>
				</th>
			</tr>
			<tr>
				<th>3</th>
				<th>
					<span ng-show="c.reanimate_3 != null">{{c.reanimate_3_event}}</span>
				</th>
				<th ng-show="hascheatdeath">
					<span ng-show="c.cheatdeath_3 != null">{{c.cheatdeath_3_event}}</span>
				</th>
				<th>
					<span ng-show="c.resurrect_3 != null">{{c.resurrect_3_event}}</span>
				</th>
			</tr>
			<tr>
				<th>4</th>
				<th>
					<span ng-show="c.reanimate_4 != null">{{c.reanimate_4_event}}</span>
				</th>
				<th ng-show="hascheatdeath">
					<span ng-show="c.cheatdeath_4 != null">{{c.cheatdeath_4_event}}</span>
				</th>
				<th>
					<span ng-show="c.resurrect_4 != null">{{c.resurrect_4_event}}</span>
				</th>
			</tr>
			<tr>
				<th>5</th>
				<th>
					<span ng-show="c.reanimate_5 != null">{{c.reanimate_5_event}}</span>
				</th>
				<th ng-show="hascheatdeath">
					<span ng-show="c.cheatdeath_5 != null">{{c.cheatdeath_5_event}}</span>
				</th>
			</tr>
		</table>

		
		<div ng-show="od.length > 0">
			<h2>Old Style Deaths</h2>
			<ul>
				<li ng-repeat="r in od">{{r.Ability.ability_name}}  {{r.Event.name}} (x {{r.CharacterAbility.quantity}})</li>
			</ul>
		</div>

	</div>
</div>