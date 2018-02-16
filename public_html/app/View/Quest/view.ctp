<div id="questView" ng-app="questViewApp">

	<script type="text/javascript">

		var questViewApp = angular.module('questViewApp',[]);

		questViewApp.controller('QuestViewController',['$scope','$http', function($scope,$http) {

			$scope.initialize = function() {
				$scope.loadQuests(<?php echo $cardnumber; ?>);
				$scope.questsSaved = false;
				$scope.hideCompleted = true;
				$scope.loadAllQuests();
			}

			$scope.loadAllQuests = function(selected_object) {
				$http({ method: 'GET', url: '/quest/loadQuests'}).success(function(data) {
					$scope.allQuests = data;
				});
			}

			$scope.loadQuests = function(cardnum) {
				$scope.cardnum = cardnum;
				$http({ method: 'GET', url: '/quest/loadCharacterProgress/'+cardnum}).success(function(data) {
					$scope.quests = data;
					$scope.questsSaved = false;
				});
			}

			$scope.updateQuests = function() {
				var submission = { cardnumber: $scope.cardnum, updates: $scope.quests };
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/quest/updateQuests/',
					data: submission
				}).success(function(data) {
					$scope.debug = data;
					$scope.loadQuests(<?php echo $cardnumber; ?>);
					$scope.questsSaved = true;
				});

			}

			$scope.addQuest = function() {
				var submission = { cardnumber: $scope.cardnum, add: $scope.selected_quest.QuestPath.id };
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/quest/addQuestsToCharacter/',
					data: submission
				}).success(function(data) {
					$scope.debug = data;
					$scope.loadQuests(<?php echo $cardnumber; ?>);
				});

			}
		
		}]);
	
	</script>

	<div ng-controller="QuestViewController" ng-init="initialize()">
		<select ng-model="selected_quest" ng-options="aquest.QuestPath.name for aquest in allQuests">
		</select>
		<button ng-click="addQuest()">Add Quest</button>

		<input ng-model="hideCompleted" type="checkbox"/>Hide Completed Quests<br/>
		<button ng-click="updateQuests()">Update Quests</button>

		<div ng-repeat="quest in quests" ng-hide="hideCompleted && quest.EventCompleted.name">
			<h3>{{quest.QuestPath.name}}</h3>
			<table>
				<tr>
					<td><input type="checkbox" ng-model="quest.dropped"/></td>
					<td>Drop the "{{quest.QuestPath.name}}" Quest</td>
				</tr>
				<tbody ng-repeat="stage in quest.QuestStages">
					<tr>
						<td>
							<input ng-hide="stage.EventCompleted.name" type="checkbox" ng-model="stage.completed"/>
							<input ng-show="stage.EventCompleted.name" type="checkbox" checked disabled/>
						</td>
						<td>{{stage.QuestStage.description}}
							<span ng-show="stage.EventAdded.name"><i>Added {{stage.EventAdded.name}}</i></span>
							<span ng-show="stage.EventCompleted.name"><i>Completed {{stage.EventCompleted.name}}</i></span>
						</td>
					</tr>
					<tr ng-show="stage.QuestStage.reward">
						<td>
							<input ng-hide="stage.EventCollected.name" type="checkbox" ng-model="stage.collected"/>
							<input ng-show="stage.EventCollected.name" type="checkbox" checked disabled/>
						</td>
						<td>{{stage.QuestStage.reward}}
							<span ng-show="stage.EventCollected.name"><i>Collected {{stage.EventCollected.name}}</i></span>
						</td>
					</tr>
			</table>
		</div>
		<button ng-click="updateQuests()">Update Quests</button>

		<div>{{debug}}</div>

	</div>


</div>
