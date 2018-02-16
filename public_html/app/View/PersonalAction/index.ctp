<style>
	.actiontable table { }
	.actiontable th { padding: 3px; vertical-align: top; text-align: left;}
	.actiontable td { padding: 3px; vertical-align: top; }

	.noborder table { border:none; }
	.noborder th { border:none; }
	.noborder td { border:none; }
</style>

<div id="personalActions" ng-app="personalActionsApp">

	<script type="text/javascript">

		var personalActionsApp = angular.module('personalActionsApp',[]);

		personalActionsApp.controller('PersonalActionsController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {
				$scope.allow_business = true;
				$scope.actions = {};
				$scope.turnsBack = 3;
				$scope.action1type = "land";
				$scope.action2type = "land";

				$scope.savedAction1_id = 0;
				$scope.savedAction2_id = 0;

				$scope.isadmin = false;
				
				$scope.loadCharactersURL = '/personal_action/loadcharacters/';

				<?php if(isset($admin)) { ?>
					$scope.isadmin = true;
					$scope.loadCharactersURL = '/personal_action/loadcharactersAs/<?php echo $player_id; ?>';
				<?php } ?>
				
				$scope.loadLands();
				$scope.loadTurn();
				
			}

			$scope.loadTurn = function() {
				$http({ method: 'GET', url: '/personal_action/loadturn/'}).success(function(data) {
					$scope.turn = data;
				});
			}
			
			$scope.loadCharacters = function() {
				$http({ method: 'GET', url: $scope.loadCharactersURL}).success(function(data) {
					$scope.characters = data;
					if($scope.characters.length >= 1) {
						$scope.selectedCharacter = $scope.characters[0];
						$scope.loadCharacterDetails();
					}
				});
			}

			$scope.loadActions = function() {

				if($scope.selectedCharacter != null && $scope.selectedSettlement != null) {
					$http({ method: 'GET', url: '/personal_action/loadactions/'+$scope.selectedCharacter.Character.id+'/'+$scope.selectedSettlement.id}).success(function(data) {
						$scope.actionInfo = data;

						// load the current_actions if they exist
						for(j = 0; j < $scope.details.current_actions.length; j++) {
							var act = $scope.details.current_actions[j];

							if(act.TurnAction.action_number == 1) {
								for(i = 0; i < $scope.actionInfo.actions.length; i++) {
									if($scope.actionInfo.actions[i].Action.id == act.TurnAction.action_id) {
										$scope.selectedAction1 = $scope.actionInfo.actions[i];
									}
								}
							}
							if(act.TurnAction.action_number == 2) {
								for(i = 0; i < $scope.actionInfo.actions.length; i++) {
									if($scope.actionInfo.actions[i].Action.id == act.TurnAction.action_id) {
										$scope.selectedAction2 = $scope.actionInfo.actions[i];
									}
								}
							}
						}

						if($scope.savedAction1_id != 0) {
							for(i = 0; i < $scope.actionInfo.actions.length; i++) {
								if($scope.actionInfo.actions[i].Action.id == $scope.savedAction1_id) {
									$scope.selectedAction1 = $scope.actionInfo.actions[i];
									$scope.computeActions1();
								}
							}
						}

						if($scope.savedAction2_id != 0) {
							for(i = 0; i < $scope.actionInfo.actions.length; i++) {
								if($scope.actionInfo.actions[i].Action.id == $scope.savedAction2_id) {
									$scope.selectedAction2 = $scope.actionInfo.actions[i];
									$scope.computeActions2();
								}
							}
						}

					});
				} else {
					$scope.actionInfo = null;
				}
			}

			$scope.loadBusinessImprovements = function() {
				if($scope.selectedCharacter != null && $scope.selectedBusiness != null) {
					$http({ method: 'GET', url: '/personal_action/loadbusinessimprovements/'+$scope.selectedCharacter.Character.id+'/'+$scope.selectedBusiness.id}).success(function(data) {
						$scope.businessImprovements = data;

						// select the current_actions business improvement if it exists
						for(j = 0; j < $scope.details.current_actions.length; j++) {
							var act = $scope.details.current_actions[j];

							if(act.TurnAction.action_number == 1) {
								for(i = 0; i < $scope.businessImprovements.length; i++) {
									if($scope.businessImprovements[i].SettlementImprovement.id == act.TurnAction.improvement_id) {
										$scope.selectedBI = $scope.businessImprovements[i];
									}
								}
								for(i = 0; i < $scope.actionInfo.actions.length; i++) {
									if($scope.actionInfo.actions[i].Action.id == act.TurnAction.action_id) {
										$scope.selectedAction1 = $scope.actionInfo.actions[i];
									}
								}
							}
						}
					});
				} else {
					$scope.businessImprovements = null;
				}
			}

			$scope.loadBusinessImprovements2 = function() {
				if($scope.selectedCharacter != null && $scope.selectedBusiness != null) {
					$http({ method: 'GET', url: '/personal_action/loadbusinessimprovements/'+$scope.selectedCharacter.Character.id+'/'+$scope.selectedBusiness2.id}).success(function(data) {
						$scope.businessImprovements2 = data;

						// select the current_actions business improvement if it exists
						for(j = 0; j < $scope.details.current_actions.length; j++) {
							var act = $scope.details.current_actions[j];

							if(act.TurnAction.action_number == 2) {
								for(i = 0; i < $scope.businessImprovements2.length; i++) {
									if($scope.businessImprovements2[i].SettlementImprovement.id == act.TurnAction.improvement_id) {
										$scope.selectedBI2 = $scope.businessImprovements2[i];
									}
								}
							}
						}
					});
				} else {
					$scope.businessImprovements2 = null;
				}
			}

			$scope.loadBusinessActions = function() {
				if($scope.selectedCharacter != null) {
					$http({ method: 'GET', url: '/personal_action/loadbusinessactions/'+$scope.selectedCharacter.Character.id}).success(function(data) {
						$scope.businessActionInfo = data;

						// load the current_actions if they exist
						for(j = 0; j < $scope.details.current_actions.length; j++) {
							var act = $scope.details.current_actions[j];

							if(act.TurnAction.action_number == 1) {
								for(i = 0; i < $scope.businessActionInfo.actions.length; i++) {
									if($scope.businessActionInfo.actions[i].Action.id == act.TurnAction.action_id) {
										$scope.selectedAction1 = $scope.businessActionInfo.actions[i];
									}
								}
							}
							if(act.TurnAction.action_number == 2) {
								for(i = 0; i < $scope.businessActionInfo.actions.length; i++) {
									if($scope.businessActionInfo.actions[i].Action.id == act.TurnAction.action_id) {
										$scope.selectedAction2 = $scope.businessActionInfo.actions[i];
									}
								}
							}
						}

						if($scope.savedAction1_id != 0) {
							for(i = 0; i < $scope.businessActionInfo.actions.length; i++) {
								if($scope.businessActionInfo.actions[i].Action.id == $scope.savedAction1_id) {
									$scope.selectedAction1 = $scope.businessActionInfo.actions[i];
									$scope.computeActions1();
								}
							}
						}

						if($scope.savedAction2_id != 0) {
							for(i = 0; i < $scope.businessActionInfo.actions.length; i++) {
								if($scope.businessActionInfo.actions[i].Action.id == $scope.savedAction2_id) {
									$scope.selectedAction2 = $scope.businessActionInfo.actions[i];
									$scope.computeActions2();
								}
							}
						}

					});
				} else {
					$scope.businessActionInfo = null;
				}
			}

			$scope.loadLands = function() {
				$http({ method: 'GET', url: '/personal_action/loadlands/'}).success(function(data) {
					$scope.lands = data;

					$scope.loadCharacters();
					$scope.checkForLandAdmin();
				});
			}

			$scope.checkForLandAdmin = function() {
				$http({ method: 'GET', url: '/land_system/loadLands'}).success(function(data) {
					$scope.islandadmin = data.length >= 1;
				});
			}

			$scope.hasValue = function(s) {
				if(s != null && s.length > 0) {
					return true;
				}

				return false;
			}

			// New Methods
			$scope.reset1 = function() {
				$scope.selectedLand = {};
				$scope.selectedSettlement = {};
				$scope.selectedAction1 = {};
				$scope.selectedBusiness = {};
				$scope.selectedBI = {};
				$scope.selectedComment1 = "";
				$scope.selectedTarget1 = "";
				$scope.actions.action1 = {};
			}

			$scope.reset2 = function() {
				$scope.selectedLand2 = {};
				$scope.selectedSettlement2 = {};
				$scope.selectedAction2 = {};
				$scope.selectedBusiness2 = {};
				$scope.selectedBI2 = {};
				$scope.selectedComment2 = "";
				$scope.selectedTarget2 = "";
				$scope.actions.action2 = {};
			}

			$scope.turnsAgo = function(turnid) {
				return $scope.turn.t.id - turnid;
			}

			$scope.turnsAgoShow = function(turnid) {
				var diff = $scope.turnsAgo(turnid);

				if(diff > 0 && diff <= $scope.turnsBack) {
					return true;
				}

				return false;
			}

			$scope.loadCharacterDetails = function() {
				$http({ method: 'GET', url: '/personal_action/getPastActionsForCharacter/'+$scope.selectedCharacter.Character.id}).success(function(data) {
					$scope.pastactions = data;
				});
				

				$http({ method: 'GET', url: '/personal_action/getCharacterDetails/'+$scope.selectedCharacter.Character.id}).success(function(data) {
					$scope.reset1();
					$scope.reset2();
					$scope.action1type = "land";
					$scope.action2type = "land";
					$scope.actions.character_id = $scope.selectedCharacter.Character.id;

					$scope.details = data;

					if($scope.details.current_actions.length > 0) {
						for(j = 0; j < $scope.details.current_actions.length; j++) {
							var act = $scope.details.current_actions[j];

							if(act.TurnAction.action_number == 1) {
								for(i = 0; i < $scope.selectedCharacter.Business.length; i++) {
									if($scope.selectedCharacter.Business[i].id == act.TurnAction.business_id) {
										$scope.selectedBusiness = $scope.selectedCharacter.Business[i];
										$scope.action1type = 'business';
									}
								}

								for(i = 0; i < $scope.lands.length; i++) {
									if($scope.lands[i].Land.id == act.Land.id) {
										$scope.selectedLand = $scope.lands[i];
									}
								}

								for(i = 0; i < $scope.selectedLand.Settlement.length; i++) {
									if($scope.selectedLand.Settlement[i].id == act.Settlement.id) {
										$scope.selectedSettlement = $scope.selectedLand.Settlement[i];
									}
								}
								$scope.selectedTarget1 = act.TurnAction.target;
								$scope.selectedComment1 = act.TurnAction.comments;
								$scope.selectedResults1 = act.TurnAction.result;
							}
							if(act.TurnAction.action_number == 2) {

								for(i = 0; i < $scope.selectedCharacter.Business.length; i++) {
									if($scope.selectedCharacter.Business[i].id == act.TurnAction.business_id) {
										$scope.selectedBusiness2 = $scope.selectedCharacter.Business[i];
										$scope.action2type = 'business';
									}
								}

								for(i = 0; i < $scope.lands.length; i++) {
									if($scope.lands[i].Land.id == act.Land.id) {
										$scope.selectedLand2 = $scope.lands[i];
									}
								}

								for(i = 0; i < $scope.selectedLand.Settlement.length; i++) {
									if($scope.selectedLand.Settlement[i].id == act.Settlement.id) {
										$scope.selectedSettlement2 = $scope.selectedLand.Settlement[i];
									}
								}

								$scope.selectedTarget2 = act.TurnAction.target;
								$scope.selectedComment2 = act.TurnAction.comments;
								$scope.selectedResults2 = act.TurnAction.result;
							}
						}
						
						$scope.loadActions();
						$scope.loadBusinessActions();
						$scope.loadBusinessImprovements();
						$scope.loadBusinessImprovements2();
					}

				});
			}

			$scope.selectAction1 = function() {
				$scope.computeActions1();
				$scope.savedAction1_id = $scope.selectedAction1.Action.id;
			}

			$scope.selectAction2 = function() {
				$scope.computeActions2();
				$scope.savedAction2_id = $scope.selectedAction2.Action.id;
			}

			$scope.computeActions1 = function() {
				$scope.actions.action1 = {};

				$scope.actions.action1.action_id = $scope.selectedAction1.Action.id;
				$scope.actions.action1.negative  = $scope.selectedAction1.Action.negative;

				if($scope.action1type == 'land') {
					$scope.actions.action1.selectedLand = $scope.selectedLand.Land.id;
					$scope.actions.action1.selectedSettlement = $scope.selectedSettlement.id;
					$scope.actions.action1.selectedBusiness = '0';
					$scope.actions.action1.selectedBI = '0';
				} else if($scope.action1type == 'business') {
					$scope.actions.action1.selectedLand = $scope.selectedBI.Settlement.land_id;
					$scope.actions.action1.selectedSettlement = $scope.selectedBI.Settlement.id;
					$scope.actions.action1.selectedBusiness = $scope.selectedBusiness.id;
					$scope.actions.action1.selectedBI = $scope.selectedBI.SettlementImprovement.id;
				}

				$scope.actions.action1.target = $scope.selectedTarget1;
				$scope.actions.action1.comment = $scope.selectedComment1;
			}

			$scope.computeActions2 = function() {
				if($scope.details.civil_service == '1') {
					$scope.actions.action2 = {};

					$scope.actions.action2.action_id = $scope.selectedAction2.Action.id;
					$scope.actions.action2.negative  = $scope.selectedAction2.Action.negative;

					if($scope.action2type == 'land') {
						$scope.actions.action2.selectedLand = $scope.selectedLand.Land.id;
						$scope.actions.action2.selectedSettlement = $scope.selectedSettlement.id;
						$scope.actions.action2.selectedBusiness = '0';
						$scope.actions.action2.selectedBI = '0';
					} else if($scope.action2type == 'business') {
						$scope.actions.action2.selectedLand = $scope.selectedBI2.Settlement.land_id;
						$scope.actions.action2.selectedSettlement = $scope.selectedBI2.Settlement.id;
						$scope.actions.action2.selectedBusiness = $scope.selectedBusiness2.id;
						$scope.actions.action2.selectedBI = $scope.selectedBI2.SettlementImprovement.id;
					}

					$scope.actions.action2.target = $scope.selectedTarget2;
					$scope.actions.action2.comment = $scope.selectedComment2;
				}
			}

			$scope.loadLandDetails = function() {
				$scope.selectedSettlement = {};
				$scope.selectedAction1 = {};
				$scope.selectedAction2 = {};
			}

			$scope.loadSettlementDetails = function() {
				$scope.selectedAction1 = {};
				$scope.selectedAction2 = {};
				$scope.loadActions();
			}

			$scope.selectBusinessLocation = function() {
				$scope.selectedLand = {};
				$scope.selectedLand.Land = $scope.selectedBI.Land;
				$scope.selectedSettlement = $scope.selectedBI.Settlement;
				$scope.loadActions();
				$scope.loadBusinessActions();
			}

			$scope.selectBusinessLocation2 = function() {
				$scope.selectedLand2 = {};
				$scope.selectedLand2.Land = $scope.selectedBI2.Land;
				$scope.selectedSettlement2 = $scope.selectedBI2.Settlement;
				$scope.loadActions();
				$scope.loadBusinessActions();
			}

			$scope.submitAction = function() {
				$scope.computeActions1();
				$scope.computeActions2();

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/personal_action/saveTurnActions/', 
					data: $scope.actions
				}).success(function(data) {
					$scope.debug = data;
					$scope.saveResults = "Saved";
				});
			}

		}]);
	</script>

	<div ng-controller="PersonalActionsController" ng-init="initialize()">
		<h2>Land System / Between Game Actions</h2>

		<h3 ng-show="islandadmin"><a href="/land_system">Land Management</a></h3>

		Character: <select ng-options="c as c.Character.name for c in characters" ng-model="selectedCharacter" ng-change="loadCharacterDetails()"></select>

		<div ng-show="selectedCharacter">
			<h3>{{selectedCharacter.Character.name}} Past Actions and Results</h3>
			<div>
				<table>
					<tr>
						<th>Past Action</th>
						<th>Settlement</th>
						<th>Turns Ago</th>
						<th colspan="3">Details</th>
					<tr ng-repeat="pact in pastactions" ng-show="turnsAgoShow(pact.Turn.id)">
						<td>{{pact.Action.name}}</td>
						<td>{{pact.Settlement.name}}</td>
						<td style="text-align:center;">{{turnsAgo(pact.Turn.id)}}</td>
						<td ng-hide="pact.showDetails">
							<button ng-click="pact.showDetails = true">Expand</button>
						</td>
						<td ng-show="pact.showDetails">
							<button ng-click="pact.showDetails = false">Hide</button>
						</td>
						<td ng-show="pact.showDetails">
							<b>Target:</b> {{pact.TurnAction.target}}<br/>
							<b>Comment:</b> {{pact.TurnAction.comments}}<br/>
							<b>Results:</b> {{pact.TurnAction.result}}<br/>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<h3 ng-show="turn.t.status != 0">Submissions for this turn have ended</h3>

		<div ng-show="isadmin">Override Turn (0 = open, 1 = closed, 2 = published) <input type="text" size="3" ng-model="turn.t.status"></div>

		<div ng-show="selectedCharacter && turn.t.status != 0">
			<h3>{{selectedCharacter.Character.name}} Submitted Actions</h3>
			<div>
				{{selectedSettlement.description}}<br/><br/>{{selectedSettlement.current_events}}
			</div>
			<hr/>
			<div ng-show="selectedAction1.Action.name">
				<div><b>Action #1:</b> {{selectedAction1.Action.name}} in {{selectedSettlement.name}}, {{selectedLand.Land.name}}</div>
				<div ng-show="action1type == 'business'">
					<b>Company:</b> {{selectedBusiness.name}} -- {{selectedBI.SettlementImprovement.name}}
				</div>
				<div><b>Target:</b> {{selectedTarget1}}</div>
				<div><b>Comment:</b> {{selectedComment1}}</div>
				<div ng-show="turn.t.status > 1">Results: {{selectedResults1}}</div>
			</div>
			<hr ng-show="selectedAction2.Action.name"/>
			<div ng-show="selectedAction2.Action.name">
				<div><b>Action #2:</b> {{selectedAction2.Action.name}} in {{selectedSettlement.name}}, {{selectedLand.Land.name}}</div>
				<div ng-show="action2type == 'business'">
					<b>Company:</b> {{selectedBusiness2.name}} -- {{selectedBI2.SettlementImprovement.name}}
				</div>
				<div><b>Target:</b> {{selectedTarget2}}</div>
				<div><b>Comment:</b> {{selectedComment2}}</div>
				<div ng-show="turn.t.status > 1">Results: {{selectedResults2}}</div>
			</div>
		</div>

		<div ng-show="selectedCharacter && turn.t.status == 0">
			<h3>{{selectedCharacter.Character.name}} Current Actions</h3>
			<div>
				{{selectedSettlement.description}}<br/><br/>{{selectedSettlement.current_events}}
			</div>
			<div>
				<table class="actiontable">
					<tr ng-show="selectedCharacter.Business.length > 0 && allow_business">
						<th></th>
						<td>
							<input type="radio" ng-model="action1type" value="land" ng-change="reset1()">Land</input>
							<input type="radio" ng-model="action1type" value="business" ng-change="reset1()">Business</input>
						</td>
					</tr>
					<tr ng-hide="allow_business">
						<th></th>
						<td>
							Business Actions are currently unavailable
						</td>
					</tr>


					<tbody ng-show="action1type == 'land'">
						<tr>
							<th>Land</th>
							<td>
								<select ng-options="l as l.Land.name for l in lands" ng-model="selectedLand" ng-change="loadLandDetails()"></select>
							</td>
						</tr>
						<tr>
							<th>Settlement</th>
							<td>
								<select ng-options="s as s.name for s in selectedLand.Settlement" ng-model="selectedSettlement" ng-change="loadSettlementDetails()"></select>
							</td>
						</tr>
					</tbody>

					<tbody ng-show="action1type == 'business'">
						<tr>
							<th>Company</th>
							<td>
								<select ng-options="b as b.name for b in selectedCharacter.Business" ng-model="selectedBusiness" ng-change="loadBusinessImprovements()"></select>
							</td>
						</tr>
						<tr>
							<th>Business</th>
							<td>
								<select ng-options="si as si.SettlementImprovement.name for si in businessImprovements" ng-model="selectedBI" ng-change="selectBusinessLocation()"></select>
							</td>
						</tr>
						<tr>
							<th>Location</th><td>{{selectedLand.Land.name}} -- {{selectedSettlement.name}}</td>
						</tr>
						<tr>
							<th>Business Type</th><td>{{selectedBI.Improvement.name}}</td>
						</tr>
						<tr>
							<th>Rank</th><td>{{selectedBI.SettlementImprovement.rank}}</td>
						</tr>
						<tr>
							<th>Commodity</th><td>{{selectedBI.SettlementImprovement.commodity}}</td>
						</tr>
					</tbody>

					

					<tbody ng-show="selectedSettlement">
						<tr>
							<th>Action</th>
							<td ng-show="action1type == 'land'">
								<select ng-options="act as act.Action.name for act in actionInfo.actions" ng-model="selectedAction1" ng-change="selectAction1()"></select>
							</td><td ng-hide="action1type == 'land'">
								<select ng-options="act as act.Action.name for act in businessActionInfo.actions" ng-model="selectedAction1" ng-change="selectAction1()"></select>

							</td>
							<td rowspan="3" style="width:400px">
								<b>{{selectedAction1.Action.name}}</b> {{selectedAction1.Action.description}}
								<div ng-show="selectedAction1.Action.warning">
									<br/><b>{{selectedAction1.Action.warning}}</b>
								</div>
							</td>
						</tr>
						<tr>
							<th>Specific Target</th>
							<td><input ng-model="selectedTarget1" size="50"/></td>
						</tr>
						<tr>
							<th>Comments</th>
							<td><textarea ng-model="selectedComment1" rows="3" cols="50"></textarea></td>
						</tr>
					</tbody>

					
					<tbody ng-show="details.civil_service == 1">
						<tr>
							<td colspan="2"><hr/></td>
						</tr>
						<tr ng-show="details.quadrivium == 1 && allow_business">
							<th></th>
							<td>
								<input type="radio" ng-model="action2type" value="land" ng-change="reset2()">Land</input>
								<input type="radio" ng-model="action2type" value="business" ng-change="reset2()">Business</input>
							</td>
						</tr>
						<tr ng-show="action2type == 'land'">
							<th>Location</th><td>{{selectedLand.Land.name}} -- {{selectedSettlement.name}}</td>
						</tr>
					</tbody>
					<tbody ng-show="action2type == 'business'">
						<tr>
							<th>Company</th>
							<td>
								<select ng-options="b as b.name for b in selectedCharacter.Business" ng-model="selectedBusiness2" ng-change="loadBusinessImprovements2()"></select>
							</td>
						</tr>
						<tr>
							<th>Business</th>
							<td>
								<select ng-options="si as si.SettlementImprovement.name for si in businessImprovements2" ng-model="selectedBI2" ng-change="selectBusinessLocation2()"></select>
							</td>
						</tr>
						<tr>
							<th>Location</th><td>{{selectedLand2.Land.name}} -- {{selectedSettlement2.name}}</td>
						</tr>
						<tr>
							<th>Business Type</th><td>{{selectedBI.Improvement.name}}</td>
						</tr>
						<tr>
							<th>Rank</th><td>{{selectedBI.SettlementImprovement.rank}}</td>
						</tr>
						<tr>
							<th>Commodity</th><td>{{selectedBI.SettlementImprovement.commodity}}</td>
						</tr>
					</tbody>
					<tbody ng-show="details.civil_service == 1">
						<tr>
							<th>Action</th>
							<td ng-show="action2type == 'land'">
								<select ng-options="act as act.Action.name for act in actionInfo.actions" ng-model="selectedAction2" ng-change="selectAction2()"></select>
							</td><td ng-hide="action2type == 'land'">
								<select ng-options="act as act.Action.name for act in businessActionInfo.actions" ng-model="selectedAction2" ng-change="selectAction2()"></select>
							</td>
							<td rowspan="3" style="width:400px">
								<b>{{selectedAction2.Action.name}}</b> {{selectedAction2.Action.description}}
								<div ng-show="selectedAction2.Action.warning">
									<br/><b>{{selectedAction2.Action.warning}}</b>
								</div>
							</td>
						</tr>
						<tr>
							<th>Specific Target</th>
							<td><input type="text" ng-model="selectedTarget2" size="50"/>
						</tr>
						<tr>
							<th>Comments</th>
							<td><textarea ng-model="selectedComment2" rows="3" cols="50"></textarea>
						</tr>
					</tbody>

					<tr ng-hide="false">
						<th>Make Default Action</th>
						<td><input type="checkbox" ng-model="actions.makedefault"/></td>
					</tr>
					<tr ng-show="actions.makedefault && (selectedAction1.Action.negative == '1' || selectedAction2.Action.negative == '1')">
						<th>WARNING</th>
						<td>Negative Actions may not be set as default actions</td>
					</tr>

				</table>
				<button ng-click="submitAction()">Save</button> {{saveResults}}

			</div>
		</div>

		<button ng-show="false" ng-click="showdebug=true">+</button>
		<div ng-show="showdebug">
			{{debug}}
		</div>

	</div>
</div>