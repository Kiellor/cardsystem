<div id="abilityfinder" ng-app="abilityApp">

<script type="text/javascript">

		var abilityApp = angular.module('abilityApp',[]);

		abilityApp.controller('AbilityController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function(data) {
				$scope.loadAbilities();
				
				$scope.activeonly = 0;
				$scope.listsonly = false;
				$scope.deprecated = false;
				$scope.onlyall = true;
				$scope.bulkedit = false;

				$scope.bulkEditMode = "none";

				$scope.selectedability = []; 

				$scope.cset_id = <?php echo $cset_id; ?>;
			};

			$scope.clear = function() {
				$scope.selectedability = [];
				$scope.results = [];
			}

			$scope.loadAbilities = function() {
				$http({ method: 'GET', url: '/ability/loadabilities/'}).success(function(data) {
					$scope.abilities = data;
				});
			}

			$scope.loadDeprecated = function() {
				$http({ method: 'GET', url: '/ability/loaddeprecated/'}).success(function(data) {
					$scope.abilities = data;
				});
			}

			$scope.loadLists = function() {
				$http({ method: 'GET', url: '/ability/loadlists/'}).success(function(data) {
					$scope.abilities = data;
				});
			}

			$scope.reload = function() {
				if($scope.deprecated) {
					$scope.loadDeprecated();
				} else if($scope.listsonly) {
					$scope.loadLists();
				} else {
					$scope.loadAbilities();
				}
			}

			$scope.deleteAll = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/ability/deleteall/',
					data: { deleteme: $scope.selectedability[0] }
				}).success(function(data) {
					$scope.bulkEditMode = 'none';
					$scope.debug = data;
					$scope.findCharacters();
				});				
			}

			$scope.replaceAll = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/ability/replaceall/',
					data: { replace: $scope.selectedability[0], target: $scope.selectedReplacement, option: $scope.replacementOption }
				}).success(function(data) {
					$scope.bulkEditMode = 'none';
					$scope.debug = data;
					$scope.findCharacters();
				});
			}

			$scope.clearReplacement = function() {
				$scope.selectForReplacement = false;
				$scope.selectedReplacement = null;
				$scope.selectedReplacementOptions = null;
				$scope.replacementOption = null;
			}
			$scope.selectTarget = function(ability) {
				$scope.clearReplacement();

				$scope.selectedReplacement = ability;
				if(ability.a.uses_option_list > 0) {
					$http({ method: 'GET', url: '/ability/loadoptionlist/'+ability.a.uses_option_list}).success(function(data) {
						$scope.selectedReplacementOptions = data;
					});
				}
			}

			$scope.selectAbility = function(ability) {

				if($scope.selectForReplacement == true) {
					$scope.selectTarget(ability);
				} else {
					var c = 0;
					for(var i = 0; i < $scope.selectedability.length; i++) {
						if($scope.selectedability[i].a.id == ability.a.id) {
							c = 1;
							break;
						}
					}

					if(c == 0) {
						$scope.selectedability.push(ability);
					}
					
					$scope.findCharacters();
				}		
			}

			$scope.findCharacters = function() {
				$scope.findCharURL = '/ability/whohasall/' + $scope.activeonly +"/"+ $scope.cset_id;

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: $scope.findCharURL,
					data: $scope.selectedability
				}).success(function(data) {
					$scope.results = data;
				});
			}

			$scope.unselect = function(skillname) {
				for(var i = 0; i < $scope.selectedability.length; i++) {
					if($scope.selectedability[i].a.ability_name == skillname) {
						$scope.selectedability.splice(i,1);
						break;
					}
				}
				
				$scope.findCharacters();		
			}

		}]);
</script>

<div ng-controller="AbilityController" ng-init="initialize()">

<?php if(AuthComponent::user('role_admin') || AuthComponent::user('role_cards')) { ?>

	<div id="abilitylist" style="float:left; width:330px;">
		<h2>Search for an Ability</h2>
		Filter: <input name="search" ng-model="search"/><br/>
		All:<input type="radio" name="activeonly" ng-model="activeonly" ng-change="findCharacters()" value="0"/><br/>
		Active only:<input type="radio" name="activeonly" ng-model="activeonly" ng-change="findCharacters()" value="1"/><br/>
		At Current Event:<input type="radio" name="activeonly" ng-model="activeonly" ng-change="findCharacters()" value="2"/><br/>
		<br/>
		Characters with ALL of these:<input type="checkbox" name="onlyall" ng-model="onlyall"/><br/>
		<br/>
		Lists only:<input type="checkbox" name="listsonly" ng-model="listsonly" ng-change="reload()"/><br/>
		Deprecated only:<input type="checkbox" name="deprecated" ng-model="deprecated" ng-change="reload()"/><br/>
		Bulk edit actions:<input type="checkbox" name="bulkedit" ng-model="bulkedit"/>
		<ul>
			<li ng-repeat="ability in abilities | filter:search">
				{{ability.a.id}}
				<a ng-click="selectAbility(ability)">{{ability.a.ability_name}}</a>
			</li>
		</ul>
	</div>

	<div id="characterlist" ng-show="selectedability != null" style="float:left; width:530px;">
		<div>
			<div ng-show="bulkedit">
				<h3>Bulk Edit Options</h3>
				<div ng-hide="selectedability.length == 1">
					Select exactly one skill in the filter
				</div>
				<div ng-show="selectedability.length == 1">
					The following actions are permanent and not reversible, act with caution
					<ul ng-show="bulkEditMode == 'none'">
						<li>Delete all instances of skill from all characters
							<button ng-click="bulkEditMode = 'delete'">Begin</button></li>
						<li>Replace all instances of skill with another skill on all characters
							<button ng-click="bulkEditMode = 'replace'">Begin</button></li>
					</ul>

					<div ng-hide="bulkEditMode == 'none'">
						<button ng-click="bulkEditMode = 'none'">Cancel Bulk Edit</button>
					</div>

					<div ng-show="bulkEditMode == 'replace'">
						<h3>Replace all instances of skill with another skill on all characters</h3>
						<button ng-click="clearReplacement()">Clear</button>
						<button ng-click="selectForReplacement = true">Select Replacement Skill</button>
						<span ng-show="selectForReplacement == true">Select a skill using list on left</span>
						<span ng-show="selectedReplacement">Replace with: {{selectedReplacement.a.ability_name}}</span>

						<select ng-show="selectedReplacement.a.uses_option_list > 0" ng-model="replacementOption" ng-options="r as r.a.ability_name for r in selectedReplacementOptions track by r.a.id"></select>

						<button ng-click="replaceAll()">Execute Replacement</button>
					</div>

					<div ng-show="bulkEditMode == 'delete'">
						<h3>Delete all instances of skill from all characters</h3>
						<button ng-click="deleteAll()">Execute Delete</button>
					</div>
				</div>
			</div>

			<h3>
				<span ng-show="activeonly == 0">Characters</span>
			 	<span ng-show="activeonly == 1">Active characters</span>
				<span ng-show="activeonly == 2">Characters at the Current Event</span>
			 	with 
				<span ng-show="onlyall">all</span>
				<span ng-hide="onlyall">some</span>
			 	of these skills</h3>

				<ul><li ng-repeat="(skill, total) in results.totals">{{skill}} ({{total}}) <a ng-click="unselect(skill)">X</a></li></ul>
				<button ng-click="clear()">Clear Filter</button>

			<table ng-show="onlyall == true">
				<tr ng-show="character.abilities.length == selectedability.length" ng-repeat="character in results.characters">
					<td><a ng-href="/characters/view/{{character.cardnumber}}">{{character.name}}</a></td>
					<td ng-repeat="ab in character.abilities">{{ab.name}}: <b>{{ab.count}}</b></td>
				</tr>
			</table>
			<table ng-show="onlyall == false">
				<tr ng-repeat="character in results.characters">
					<td><a ng-href="/characters/view/{{character.cardnumber}}">{{character.name}}</a></td>
					<td ng-repeat="ab in character.abilities">{{ab.name}}: <b>{{ab.count}}</b></td>
				</tr>
			</table>
		</div>

	</div>

<?php } else { ?>

	<div id="abilitylist" style="float:left; width:330px;">
		Filter: <input name="search" ng-model="search"/><br/>

		<ul>
			<li ng-repeat="ability in abilities | filter:search">
				{{ability.a.ability_name}}
			</li>
		</ul>
	</div>

<?php } ?>

</div>

</div>