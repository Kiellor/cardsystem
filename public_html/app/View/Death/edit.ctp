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

			$scope.deleteOldDeaths = function() {
				$http({ method: 'GET', url: '/death/deleteOldDeaths/<?php echo $cardnumber ?>'}).success(function(data) {
					$scope.od = data;

					location.href = "/death/hasOldDeaths";
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

					$scope.hascheatdeath = ($scope.c.cheatdeath_1 != null || $scope.c.cheatdeath_2 != null || $scope.c.cheatdeath_3 != null || $scope.c.cheatdeath_4 != null || $scope.c.cheatdeath_5 != null)

					$scope.lookupEvents();
					
				});
			}

			$scope.saveValues = function() {
				$scope.saved_message = "Saving";

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/death/saveValues',
					data: $scope.c
				}).success(function(data) {
					$scope.c = data.Character;
					$scope.debug = data;
					$scope.saved_message = "Saved";

					$scope.lookupEvents();
				});	
			}

			$scope.lookupEvents = function() {
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
					}
					if($scope.c.cheatdeath_2 == eid) {
						$scope.c.cheatdeath_2_event = ename;
					}
					if($scope.c.cheatdeath_3 == eid) {
						$scope.c.cheatdeath_3_event = ename;
					}
					if($scope.c.cheatdeath_4 == eid) {
						$scope.c.cheatdeath_4_event = ename;
					}
					if($scope.c.cheatdeath_5 == eid) {
						$scope.c.cheatdeath_5_event = ename;
					}

					if($scope.c.empathy_rank4 == eid) {
						$scope.c.empathy_rank4_event = ename;
					}
					if($scope.c.finaldeath == eid) {
						$scope.c.finaldeath_event = ename;
					}
				}
			}

			$scope.enableEdit = function(key) {
				$scope.c["old_"+key] = $scope.c[key];
				$scope.c[key] = null;
			}
			$scope.disableEdit = function(key) {
				$scope.c[key] = $scope.c["old_"+key];
				$scope.c["old_"+key] = null;
			}

		}]);
	</script>

	<div ng-controller="DeathsAppController" ng-init="initialize()">
		<h2>Deaths for {{c.name}} ({{c.cardnumber}})</h2>

		<div id="CardOptions">
		<ul>
			<li><a ng-href="/characters/view/{{c.cardnumber}}">View Character</a>
			<li><a ng-href="/cards/page1/{{c.cardnumber}}">Enter Card Data</a>
		</ul>
		</div>
		<br/>

		Character experienced final death <span ng-show="c.finaldeath != null">{{c.finaldeath_event}} <button ng-click="enableEdit('finaldeath')">X</button></span>
		<select ng-show="c.finaldeath == null" ng-model="c.new_finaldeath" ng-options="e.Events.id as e.Events.name for e in events">
			<option value="">-- no entry --</option>
			<button ng-show="c.old_finaldeath != null" ng-click="disableEdit('finaldeath')">revert to {{c_finaldeath_event}}</button>
		</select>
		
		<br/>

		Character was saved by Empathy Rank IV <span ng-show="c.empathy_rank4 != null">{{c.empathy_rank4_event}} <button ng-click="enableEdit('empathy_rank4')">X</button></span>
		<select ng-show="c.empathy_rank4 == null" ng-model="c.new_empathy_rank4" ng-options="e.Events.id as e.Events.name for e in events">
			<option value="">-- no entry --</option>
		</select>
		<button ng-show="c.old_empathy_rank4 != null" ng-click="disableEdit('empathy_rank4')">revert to {{c.olpathy_rank4_event}}</button>

		<table>
			<tr>
				<th>Life #</th>
				<th>Reanimated?</th>
				<th>Cheated Death?</th>
				<th>Resurrected?</th>
			</tr>
			<tr>
				<th>1</th>
				<th>
					<span ng-show="c.reanimate_1 != null">{{c.reanimate_1_event}} <button ng-click="enableEdit('reanimate_1')">X</button></span>
					<select ng-show="c.reanimate_1 == null" ng-model="c.new_reanimate_1" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_reanimate_1 != null" ng-click="disableEdit('reanimate_1')">revert to {{c.reanimate_1_event}}</button>
				</th>
				<th>
					<span ng-show="c.cheatdeath_1 != null">{{c.cheatdeath_1_event}} <button ng-click="enableEdit('cheatdeath_1')">X</button></span>
					<select ng-show="c.cheatdeath_1 == null" ng-model="c.new_cheatdeath_1" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_cheatdeath_1 != null" ng-click="disableEdit('cheatdeath_1')">revert to {{c.oheatdeath_1_event}}</button>
				</th>
				<th>
					<span ng-show="c.resurrect_1 != null">{{c.resurrect_1_event}} <button ng-click="enableEdit('resurrect_1')">X</button></span>
					<select ng-show="c.resurrect_1 == null" ng-model="c.new_resurrect_1" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_resurrect_1 != null" ng-click="disableEdit('resurrect_1')">revert to {{c.resurrect_1_event}}</button>
				</th>
			</tr>
			<tr>
				<th>2</th>
				<th>
					<span ng-show="c.reanimate_2 != null">{{c.reanimate_2_event}} <button ng-click="enableEdit('reanimate_2')">X</button></span>
					<select ng-show="c.reanimate_2 == null" ng-model="c.new_reanimate_2" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_reanimate_2 != null" ng-click="disableEdit('reanimate_2')">revert to {{c.reanimate_2_event}}</button>
				</th>
				<th>
					<span ng-show="c.cheatdeath_2 != null">{{c.cheatdeath_2_event}} <button ng-click="enableEdit('cheatdeath_2')">X</button></span>
					<select ng-show="c.cheatdeath_2 == null" ng-model="c.new_cheatdeath_2" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_cheatdeath_2 != null" ng-click="disableEdit('cheatdeath_2')">revert to {{c.oheatdeath_2_event}}</button>
				</th>
				<th>
					<span ng-show="c.resurrect_2 != null">{{c.resurrect_2_event}} <button ng-click="enableEdit('resurrect_2')">X</button></span>
					<select ng-show="c.resurrect_2 == null" ng-model="c.new_resurrect_2" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_resurrect_2 != null" ng-click="disableEdit('resurrect_2')">revert to {{c.resurrect_2_event}}</button>
				</th>
			</tr>
			<tr>
				<th>3</th>
				<th>
					<span ng-show="c.reanimate_3 != null">{{c.reanimate_3_event}} <button ng-click="enableEdit('reanimate_3')">X</button></span>
					<select ng-show="c.reanimate_3 == null" ng-model="c.new_reanimate_3" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_reanimate_3 != null" ng-click="disableEdit('reanimate_3')">revert to {{c.reanimate_3_event}}</button>
				</th>
				<th>
					<span ng-show="c.cheatdeath_3 != null">{{c.cheatdeath_3_event}} <button ng-click="enableEdit('cheatdeath_3')">X</button></span>
					<select ng-show="c.cheatdeath_3 == null" ng-model="c.new_cheatdeath_3" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_cheatdeath_3 != null" ng-click="disableEdit('cheatdeath_3')">revert to {{c.oheatdeath_3_event}}</button>
				</th>
				<th>
					<span ng-show="c.resurrect_3 != null">{{c.resurrect_3_event}} <button ng-click="enableEdit('resurrect_3')">X</button></span>
					<select ng-show="c.resurrect_3 == null" ng-model="c.new_resurrect_3" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_resurrect_3 != null" ng-click="disableEdit('resurrect_3')">revert to {{c.resurrect_3_event}}</button>
				</th>
			</tr>
			<tr>
				<th>4</th>
				<th>
					<span ng-show="c.reanimate_4 != null">{{c.reanimate_4_event}} <button ng-click="enableEdit('reanimate_4')">X</button></span>
					<select ng-show="c.reanimate_4 == null" ng-model="c.new_reanimate_4" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_reanimate_4 != null" ng-click="disableEdit('reanimate_4')">revert to {{c.reanimate_4_event}}</button>
				</th>
				<th>
					<span ng-show="c.cheatdeath_4 != null">{{c.cheatdeath_4_event}} <button ng-click="enableEdit('cheatdeath_4')">X</button></span>
					<select ng-show="c.cheatdeath_4 == null" ng-model="c.new_cheatdeath_4" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_cheatdeath_4 != null" ng-click="disableEdit('cheatdeath_4')">revert to {{c.oheatdeath_4_event}}</button>
				</th>
				<th>
					<span ng-show="c.resurrect_4 != null">{{c.resurrect_4_event}} <button ng-click="enableEdit('resurrect_4')">X</button></span>
					<select ng-show="c.resurrect_4 == null" ng-model="c.new_resurrect_4" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_resurrect_4 != null" ng-click="disableEdit('resurrect_4')">revert to {{c.resurrect_4_event}}</button>
				</th>
			</tr>
			<tr>
				<th>5</th>
				<th>
					<span ng-show="c.reanimate_5 != null">{{c.reanimate_5_event}} <button ng-click="enableEdit('reanimate_5')">X</button></span>
					<select ng-show="c.reanimate_5 == null" ng-model="c.new_reanimate_5" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_reanimate_5 != null" ng-click="disableEdit('reanimate_5')">revert to {{c.reanimate_5_event}}</button>
				</th>
				<th>
					<span ng-show="c.cheatdeath_5 != null">{{c.cheatdeath_5_event}} <button ng-click="enableEdit('cheatdeath_5')">X</button></span>
					<select ng-show="c.cheatdeath_5 == null" ng-model="c.new_cheatdeath_5" ng-options="e.Events.id as e.Events.name for e in events">
						<option value="">-- no entry --</option>
					</select>
					<button ng-show="c.old_cheatdeath_5 != null" ng-click="disableEdit('cheatdeath_5')">revert to {{c.oheatdeath_5_event}}</button>
				</th>
			</tr>
		</table>

		<button ng-click="saveValues()">Save Entries</button> {{saved_message}}

		<div>
			<h2>Old Style Deaths</h2>
			<ul>
				<li ng-repeat="r in od">{{r.Ability.ability_name}} {{r.Event.name}} (x {{r.CharacterAbility.quantity}})</li>
			</ul>
			<span ng-hide="od.length > 0">None</span>
		</div>

		<div ng-hide="od.length == 0">
			Do not press this button unless you have recorded the characters old deaths.
			<button ng-click="deleteOldDeaths()">Delete Old Deaths</button>
		</div>
		
	</div>
</div>