<div id="questActions" ng-app="questApp">

	<script type="text/javascript">

		var questApp = angular.module('questApp',[]);

		questApp.controller('QuestController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {
				$scope.quests = {};
				$scope.selected_quest = null;
				$scope.loadQuests(null);
			}

			$scope.loadQuests = function(selected_object) {
				$http({ method: 'GET', url: '/quest/loadQuests'}).success(function(data) {
					$scope.quests = data;

					if(selected_object != null) {
						for( i = 0; i < data.length; i++) {
							if(data[i].QuestPath.id == selected_object.QuestPath.id) {
								$scope.selected_quest = data[i];							
							}
						}
					}
				});
			}

			$scope.openNewQuest = function() {
				$scope.selected_quest = null;
				$scope.newquest = true;
			}

			$scope.createNewQuest = function() {

				var submission = { newQuestName: $scope.newQuestName };
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/quest/createNewQuest/',
					data: submission
				}).success(function(data) {
					$scope.loadQuests(data);
					$scope.newquest = false;
				});
			}

			$scope.addStage = function() {
				$scope.clearStageForm(null);
				$scope.addstage = true;
			}

			$scope.addListItem = function() {
				$scope.clearStageForm(null);
				$scope.addstage = true;
			}

			$scope.clearStageForm = function(parent_stage) {
				$scope.newStage = { 'quest_path_id': $scope.selected_quest.QuestPath.id, 'entry_id': parent_stage };
			}

			$scope.submitNewStage = function() {
				var submission = $scope.newStage;
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/quest/createNewStage/',
					data: submission
				}).success(function(data) {
					$scope.loadQuests($scope.selected_quest);
					$scope.clearStageForm($scope.newStage.entry_id);
				});
			}

			$scope.deleteStage = function(stage_id) {
				$http({ method: 'GET', url: '/quest/deleteStage/'+stage_id}).success(function(data) {
					$scope.loadQuests($scope.selected_quest);
				});
			}

			$scope.questSelectionChanged = function() {
				$scope.addstage = false;
			}
			
		}]);
	</script>

	<div ng-controller="QuestController" ng-init="initialize()">

		<select ng-model="selected_quest" ng-options="quest.QuestPath.name for quest in quests" ng-change="questSelectionChanged()">
		</select>
		<button ng-click="openNewQuest()">New Quest</button>

		<div ng-show="newquest">
			<h2>New Quest</h2>
			Name: <input ng-model="newQuestName" size="40"/>
			<button ng-click="createNewQuest()">Create</button>
		</div>

		<div ng-show="selected_quest">
			<h2>{{selected_quest.QuestPath.name}}</h2>
			<button ng-click="addStage()">Add Stage</button>

			<div ng-show="addstage">
				<table>
					<tr>
						<th>Quest Stage Number:</th>
						<td><input ng-model="newStage.quest_stage" size="10"/> (required, example: "2")</td>
					</tr>
					<tr>
						<th>Stage Code:</th>
						<td><input ng-model="newStage.stage_code" size="10"/> (required, example: "b")</td>
					</tr>
					<tr>
						<th>Stage Description:</th>
						<td><input ng-model="newStage.description" size="80"/> (required, what do they do to finish this step?) </td>
					</tr>
					<tr>
						<th>Reward:</th>
						<td><input ng-model="newStage.reward" size="80"/> (optional, what will they get at Logistics?)</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<button ng-click="submitNewStage()">Submit</button>
						</td>
					</tr>
				</table>
			</div>

			<div ng-repeat="stage in selected_quest.QuestStage" ng-show="stage.description" style="margin-top: 5px;">
				<div ng-show="stage.description">
					[{{stage.quest_stage}}-{{stage.stage_code}}] {{stage.description}}
					<button ng-click="deleteStage(stage.id)">x</button>
					<div ng-show="stage.reward">{{stage.reward}}</div> 
				</div>
			</div>
		</div>
		

	</div>
</div>