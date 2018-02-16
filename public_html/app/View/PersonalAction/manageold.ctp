<div id="personalActions" ng-app="personalActionsApp">

	<script type="text/javascript">

		var personalActionsApp = angular.module('personalActionsApp',['ngGrid']);

		personalActionsApp.controller('PersonalActionsController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {
				$scope.view = 'experimental';
				$scope.autoSave = false;
				$scope.chosensettlement = null;
				$scope.settlement = {'loaded': false};
				$scope.settlementValues = {'loaded': false};
				$scope.settlementActions = {'loaded': false};
				$scope.businessActions = {'loaded': false};
				$scope.newSettlementValues = {};
				$scope.repeatedvalue = "";
				$scope.e = {};
				$scope.turnsBack = 3;

				$http({ method: 'GET', url: '/personal_action/loadlands/'}).success(function(data) {
					$scope.lands = data;
				});

				$http({ method: 'GET', url: '/personal_action/loadimprovements/'}).success(function(data) {
					$scope.improvements = data;
				});

				$scope.loadTurn();
				$scope.loadedBonusesId = 0;
			}

			$scope.getBonusesForCharacter = function(row, event) {
				if($scope.loadedBonusesId != row.entity.Character.id) {
					$scope.bonuses = "";
					$scope.loadedBonusesId = row.entity.Character.id;
					$http({ method: 'GET', url: '/personal_action/getBonusesForCharacter/'+row.entity.Character.id+'/'+row.entity.Action.bonus_list_id}).success(function(data) {
						$scope.bonuses = data;
					});
				}
			}

			$scope.getPastActionsForCharacter = function(cid) {
				$http({ method: 'GET', url: '/personal_action/getPastActionsForCharacter/'+cid}).success(function(data) {
						$scope.pastactions = data;
					});
			}


			$scope.actionOptions = {
				data: 'settlementActions',
				enableCellSelection: false,
				enableRowSelection: true,
				enableCellEdit: true,
				multiSelect: false,
				selectedItems: [],
				afterSelectionChange: function(row,event) { $scope.getBonusesForCharacter(row,event); },
				columnDefs: [
					{field: 'Action.name', displayName: 'Action', enableCellEdit: false, width: "30%"},
					{field: 'Character.name', displayName: 'Name', enableCellEdit: false, width: "30%"},
					{field: 'TurnAction.result', displayName: 'Results', enableCellEdit: false, width: "40%"}
				]
			};

			$scope.businessOptions = {
				data: 'businessActions',
				enableCellSelection: false,
				enableRowSelection: true,
				enableCellEdit: true,
				multiSelect: false,
				selectedItems: [],
				afterSelectionChange: function(row,event) { $scope.getBonusesForCharacter(row,event); },
				columnDefs: [
					{field: 'Action.name', displayName: 'Action', enableCellEdit: false, width: "25%"},
					{field: 'Character.name', displayName: 'Name', enableCellEdit: false, width: "20%"},
					{field: 'Business.name', displayName: 'Business', enableCellEdit: false, width: "20%"},
					{field: 'SettlementImprovement.name', displayName: 'Establishment', enableCellEdit: false, width: "20%"},
					{field: 'TurnAction.result', displayName: 'Results', enableCellEdit: false, width: "15%"}
				]
			};


			$scope.gridOptions = {
				data: 'settlement.SettlementImprovement',
				enableCellSelection: true,
				enableRowSelection: true,
				enableCellEdit: true,
				multiSelect: false,
				selectedItems: [],
				columnDefs: [
					{field: 'Improvement.name', displayName: 'Name', enableCellEdit: false, width: "50%"},
					{field: 'Business.name', displayName: 'Business', enableCellEdit: false, width: "50%"}
				]
			};

			$scope.chooseSettlement = function(settlement) {
				$scope.chosensettlement = settlement;
				$scope.loadSettlement(settlement);
				$scope.editAction(null);
			}

			$scope.chooseLand = function(land) {
				$scope.chosensettlement = null;
				$scope.chosenland = land;
				$scope.editAction(null);
			}

			$scope.loadSettlement = function(settlement) {
				$scope.settlement.loaded = false;
				$scope.settlementValues.loaded = false;
				$scope.settlementActions.loaded = false;
				$scope.businessActions.loaded = false;

				$http({ method: 'GET', url: '/personal_action/loadsettlement/'+settlement.id}).success(function(data) {
					if(data != null) {
						$scope.settlement = data;
						$scope.settlement.loaded = true;
					} else {
						$scope.settlement = {'loaded': false};
					}
				});
				$http({ method: 'GET', url: '/personal_action/loadsettlementvalues/'+settlement.id}).success(function(data) {
					if(data.SettlementValues != null) {
						$scope.settlementValues = data.SettlementValues;
						$scope.settlementValues.loaded = true;
					} else {
						$scope.settlementValues = {'loaded': false};
					}
				});
				$http({ method: 'GET', url: '/personal_action/getAllCurrentActionsForSettlement/'+settlement.id}).success(function(data) {
					if(data != null) {
						$scope.settlementActions = data;
						$scope.settlementActions.loaded = true;
					} else {
						$scope.settlementActions = {'loaded': false};
					}
				});
				$http({ method: 'GET', url: '/personal_action/getAllCurrentBusinessActionsForSettlement/'+settlement.id}).success(function(data) {
					if(data != null) {
						$scope.businessActions = data;
						$scope.businessActions.loaded = true;
					} else {
						$scope.businessActions = {'loaded': false};
					}
				});
				$http({ method: 'GET', url: '/personal_action/getAllCurrentActionsForSettlement2/'+settlement.id}).success(function(data) {
					if(data != null) {
						$scope.settlementActions2 = data;
						$scope.settlementActions2.loaded = true;
					} else {
						$scope.settlementActions2 = {'loaded': false};
					}
				});
			}

			$scope.superlify = function(value) {
				if(value >= 90) {
					return "superb"
				} else if(value >= 83) {
					return "excellent"
				} else if(value >= 78) {
					return "good"
				} else if(value >= 72) {
					return "fair"
				} else if(value >= 65) {
					return "nominal"
				} else if(value >= 60) {
					return "poor"
				} else if(value >= 40) {
					return "very poor"
				} else {
					return "terrible"
				}
			}

			$scope.superlify_wildlands = function(value) {
				if(value >= 75) {
					return "settled"
				} else if(value >= 50) {
					return "patrolled"
				} else if(value >= 25) {
					return "scouted"
				} else {
					return "wild"
				}
			}

			$scope.generateLetter = function() {
				$scope.repeatedvalue = "";

				if($scope.chosenland.Land.id == 1) {
					// Get all values for the Baronial Report
					$http({ method: 'GET', url: '/personal_action/loadlandvalues/-1'}).success(function(data) {
						$scope.letterValues = data;
					});
				} else {
					$http({ method: 'GET', url: '/personal_action/loadlandvalues/'+$scope.chosenland.Land.id}).success(function(data) {
						$scope.letterValues = data;
					});
				}
			}

			// $scope.saveActionResults = function() {
			// 	$http({ 
			// 		method: 'POST', 
			// 		headers: { 'Content-Type': 'application/json' }, 
			// 		url: '/personal_action/saveactionresults/'+$scope.settlement.Settlement.id,
			// 		data: $scope.settlementActions
			// 	}).success(function(data) {
			// 		$scope.settlementActions = data;
			// 	});				
			// }

			$scope.saveActionResults2 = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/personal_action/saveactionresults2/',
					data: $scope.editing
				}).success(function(data) {
					$scope.debug = data;
					$scope.editing.TurnAction.saved_message = data.TurnAction.saved_message;
					$scope.editing.TurnAction.emailed = 0;
				});	
			}

			$scope.saveBusinessResults = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/personal_action/savebusinessactionresults/'+$scope.settlement.Settlement.id,
					data: $scope.businessActions
				}).success(function(data) {
					$scope.businessActions = data;
				});				
			}


			$scope.saveSettlement = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/personal_action/savesettlement/'+$scope.settlement.Settlement.id,
					data: $scope.settlement
				}).success(function(data) {
					$scope.settlement = data;
				});
			}

			$scope.addImprovement = function() {
				$scope.objecttest = {
						Settlement: $scope.chosensettlement,
						Improvement: $scope.proveToAdd.Improvement
					} ;
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/personal_action/addimprovement/', 
					data: $scope.objecttest
				}).success(function(data) {
					$scope.settlement = data;
				});
			}

			$scope.loadTurn = function() {
				$http({ method: 'GET', url: '/personal_action/loadturn/'}).success(function(data) {
					$scope.turn = data;
				});
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

			$scope.endturn = function() {
				$http({ method: 'GET', url: '/personal_action/endturn/'}).success(function(data) {
					$scope.turn = data;
				});
			}

			$scope.reopenturn = function() {
				$http({ method: 'GET', url: '/personal_action/reopenturn/'}).success(function(data) {
					$scope.turn = data;
				});
			}

			$scope.finalizeturn = function() {
				$http({ method: 'GET', url: '/personal_action/finalizeturn/'}).success(function(data) {
					$scope.turn = data;
				});
			}

			$scope.unfinalizeturn = function() {
				$http({ method: 'GET', url: '/personal_action/unfinalizeturn/'}).success(function(data) {
					$scope.turn = data;
				});
			}

			$scope.newturn = function() {
				$http({ method: 'GET', url: '/personal_action/newturn/'}).success(function(data) {
					$scope.turn = data;
				});
			}

			$scope.emailresults = function() {
				$http({ method: 'GET', url: '/personal_action/emailresults/'}).success(function(data) {
					$scope.emailsSent = 'Emails Sent';
				});
			}

			$scope.computeEarnings = function() {
				var high = parseInt($scope.businessOptions.selectedItems[0].Action.gold_high);
				var low = parseInt($scope.businessOptions.selectedItems[0].Action.gold_low);
				
				if(low != 0 && high != 0) {
					var gold = Math.floor((Math.random() * (high - low)) + low);
					var gold = gold * $scope.businessOptions.selectedItems[0].SettlementImprovement.rank;

					if($scope.override) {
						gold = $scope.override_value;
					}

					var deposit = {
							character: $scope.businessOptions.selectedItems[0].TurnAction.character_id, 
							business: $scope.businessOptions.selectedItems[0].Business.id, 
							gold_total: gold,
							luxury_total: 0,
							durable_total: 0,
							consumable_total: 0,
							wearable_total: 0,
							turn_action_id: $scope.businessOptions.selectedItems[0].TurnAction.id
					};

					$http({ 
						method: 'POST', 
						headers: { 'Content-Type': 'application/json' }, 
						url: '/bank/makeCompanyDepositFromAction/',
						data: deposit
					}).success(function(data) {
						$scope.debug = data;
						$scope.businessOptions.selectedItems[0].TurnAction.result_value = gold;
						$scope.businessOptions.selectedItems[0].TurnAction.result = 
							"Your " + $scope.businessOptions.selectedItems[0].Action.name + " earned the company " + $scope.businessOptions.selectedItems[0].TurnAction.result_value + " gold";
					});
				} 
			}

			$scope.computeEarningsForCharacter = function() {
				var gold = $scope.computeEarningsValue($scope.editing.Action.gold_low,$scope.editing.Action.gold_high,1,1);

				$scope.editing.TurnAction.result_value = gold;
			}

			$scope.computeEarningsForBusiness = function() {
				var businessActionCount = 0;
				var imp_id = $scope.editing.SettlementImprovement.id;

				for(i = 0; i < $scope.settlementActions2.length; i++) {
					if($scope.settlementActions2[i].SettlementImprovement.id == imp_id) {
						businessActionCount++;
					}
				}

				$scope.debugBusinessCount = businessActionCount;

				var gold = $scope.computeEarningsValue($scope.editing.Action.gold_low,$scope.editing.Action.gold_high,$scope.editing.SettlementImprovement.rank,businessActionCount);

				$scope.editing.TurnAction.result_value = gold;
				$scope.editing.TurnAction.result = 
							"Your " + $scope.editing.Action.name + " earned the company " + $scope.editing.TurnAction.result_value + " gold";
			}

			$scope.computeEarningsValue = function(low, high, rank, bac) {
				if($scope.override) {
					return $scope.override_value;
				}

				var factor = 0;
				var addOn = 1;
				for(j = 1; j <= bac; j++) {
					factor += addOn;
					if(j >= 3) {
						addOn = addOn / 2;
					}
				}

				$scope.debugBusinessFactor = factor;
				$scope.debugBusinessMultiplier = (factor / bac);

				$scope.debugBusinessMax = high * rank * (factor / bac);
				$scope.debugBusinessMin = low * rank * (factor / bac);

				var gold = Math.floor((Math.random() * (parseInt(high) - parseInt(low))) + parseInt(low));
				gold = gold * rank * (factor / bac);
				return gold;
			}

			$scope.saveNotes = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/personal_action/saveSettlementNotes',
					data: $scope.chosensettlement
				}).success(function(data) {
					$scope.debug = data;
				});
			}

			$scope.saveSettlementValues = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/personal_action/savesettlementvalues/',
					data: $scope.newSettlementValues
				}).success(function(data) {
					$scope.settlementValuesSaved = data;
					$scope.settlementValues.saved = true;
				});
			}


			$scope.ifNotRepeated = function(value) {
				var retval = $scope.repeatedvalue != value;

				$scope.repeatedvalue = value;

				return retval;
			}

			$scope.editAction = function(value) {
				$scope.bonuses = null;
				$scope.editing = value;
				if(value != null) {
					$scope.getBonusesForCharacter(value.Character.id,value.Action.bonus_list_id);
					$scope.getPastActionsForCharacter(value.Character.id);
					$scope.editing.TurnAction.saved_message = "Not Saved";
				}
			}

			$scope.getBonusesForCharacter = function(cid, aid) {
				$scope.bonuses = "";
				$http({ method: 'GET', url: '/personal_action/getBonusesForCharacter/'+cid+'/'+aid}).success(function(data) {
					$scope.bonuses = data;
				});
			}

			$scope.shorten = function(value) {
				if(value == null) {
					return "";
				} else if(value.length > 40) {
					return value.substring(0,40) + "...";
				} else {
					return value;
				}
			}

			$scope.yesNo = function(value) {
				if(value == "1") {
					return "yes";
				}

				return "no";
			}

		}]);
	</script>

	<div ng-controller="PersonalActionsController" ng-init="initialize()">

		<div style="float:left;width:20%;">
			<div>
				<?php
					if(  AuthComponent::user('role_admin') || 
						(AuthComponent::user('role_landadmin') == 2) ) {
				?>
					<div ng-show="turn.t.status == 0">
						{{turn.t.started}} <button ng-click="endturn()">End Turn</button>
					</div>
					<div ng-show="turn.t.status == 1">
						<button ng-click="reopenturn()">Reopen Turn</button>
						<button ng-click="emailresults()">Email Results</button> {{emailsSent}}
						<button ng-click="finalizeturn()">Finalize Turn</button>
					</div>
					<div ng-show="turn.t.status == 2">
						<button ng-click="unfinalizeturn()">Unfinalize Turn</button> {{emailsSent}}
						<button ng-click="newturn()">New Turn</button>
					</div>
				<?php } ?>
			</div>

			<div>
				<input type="radio" ng-model="view" value="experimental"/> Actions<br/>
				<input type="radio" ng-model="view" value="improvements"/> Improvements<br/>
			</div>

			<div ng-repeat="land in lands">
				<h2 ng-click="chooseLand(land)">{{land.Land.name}}</h2>
				<ul ng-show="chosenland == land">
					<li ng-repeat="settlement in land.Settlement" ng-show="settlement.active">
						<a ng-click="chooseSettlement(settlement)">{{settlement.name}}</a>
					</li>
				</ul>
			</div>
		</div>

		<div ng-show="chosensettlement != null" style="float:left;width:80%;">
			<h2>{{chosenland.Land.name}} - {{chosensettlement.name}} ({{settlementActions.length}} :: {{businessActions.length}})</h2>

			<div>
				<style>
					#landmath table { border-collapse: collapse; border: 1px solid black; }
					#landmath th { border: 1px solid black; padding: 3px; }
					#landmath td { border: 1px solid black; padding: 3px; text-align:right; }
				</style>
				
				<table id="landmath">
					<tr>
						<th>Public Order (%)</th>
						<th>Public Health (%)</th>
						<th>Happiness (%)</th>
						<th>Wildland Safety (%)</th>
						<th>Population</th>
						<th>Military</th>
						<th>Military Effectiveness</th>
						<th>Criminals</th>
					</tr>
					<tr>
						<td>{{settlementValues.public_order}}% {{superlify(settlementValues.public_order)}}</td>
						<td>{{settlementValues.health}}% {{superlify(settlementValues.health)}}</td>
						<td>{{settlementValues.happiness}}% {{superlify(settlementValues.happiness)}}</td>
						<td>{{settlementValues.wildlands}}% {{superlify_wildlands(settlementValues.wildlands)}}</td>
						<td>{{settlementValues.population}}</td>
						<td>{{settlementValues.military}}</td>
						<td>{{settlementValues.military_effect}}% {{superlify(settlementValues.military_effect)}}</td>
						<td>{{settlementValues.criminal}}</td>
					</tr>
				</table>
			</div>

			<div>
				<h3>Current Events for {{chosensettlement.name}}</h3>
				<textarea ng-model="chosensettlement.current_events" rows="5" cols="80" ></textarea>
				<h3>Staff Notes for {{chosensettlement.name}}</h3>
				<textarea ng-model="chosensettlement.staff_notes" rows="5" cols="80" ></textarea>
				<br/>
				<button ng-click="saveNotes()">Save Current Events and Staff Notes</button>
			</div>

			<div ng-show="view == 'improvements'">
				<select ng-model="proveToAdd" ng-options="prove.Improvement.name for prove in improvements">
				</select>
				<button ng-click="addImprovement()">Add Improvement</button>
				<button ng-click="saveSettlement()">Save Settlement</button>

				<style>
					.gridStyle {
					    border: 1px solid rgb(212,212,212);
					    width: 600px; 
	    				height: 250px;
	    				font-size: 70%;
					}
				</style>

				<div class="gridStyle" ng-grid="gridOptions"></div>
			</div>

			<div ng-show="view == 'actions'">
				<style>
					.actionStyle {
					    border: 1px solid rgb(212,212,212);
					    width: 600px; 
	    				height: 250px;
	    				font-size: 70%;
					}
				</style>

				<div class="actionStyle" ng-grid="actionOptions"></div>

				<table>
					<tr><td>Character</td><td><a ng-href="/characters/view/{{actionOptions.selectedItems[0].Character.cardnumber}}">{{actionOptions.selectedItems[0].Character.name}}</a></td></tr>
					<tr><td>Bonus Items</td>
						<td>
							<ul>
								<li ng-repeat="bonus in bonuses">
									{{bonus}}
								</li>
							</ul>
						</td>
					</tr>
					<tr><td>Action</td><td>{{actionOptions.selectedItems[0].Action.name}}</td></tr>
					<tr><td>Target</td><td>{{actionOptions.selectedItems[0].TurnAction.target}}</td></tr>
					<tr><td>Comments</td><td>{{actionOptions.selectedItems[0].TurnAction.comments}}</td></tr>
					<tr><td>Result</td><td><textarea rows="5" cols="80" ng-model="actionOptions.selectedItems[0].TurnAction.result" ng-change="actionOptions.selectedItems[0].TurnAction.changed = 1"/></textarea></tr>
					<tr><td>
						Public Order</td><td>({{actionOptions.selectedItems[0].Action.public_order}}) 
						<input type="text" size="3" ng-model="actionOptions.selectedItems[0].TurnAction.public_order" 
								ng-change="actionOptions.selectedItems[0].TurnAction.changed = 1"/>
					</td></tr>
					<tr><td>
						Health</td><td>({{actionOptions.selectedItems[0].Action.health}}) 
						<input type="text" size="3" ng-model="actionOptions.selectedItems[0].TurnAction.health" 
								ng-change="actionOptions.selectedItems[0].TurnAction.changed = 1"/>
					</td></tr>
					<tr><td>
						Happiness</td><td>({{actionOptions.selectedItems[0].Action.happiness}}) 
						<input type="text" size="3" ng-model="actionOptions.selectedItems[0].TurnAction.happiness" 
								ng-change="actionOptions.selectedItems[0].TurnAction.changed = 1"/>
					</td></tr>
					<tr><td>
						Military Effectiveness</td><td>({{actionOptions.selectedItems[0].Action.military_effect}}) 
						<input type="text" size="3" ng-model="actionOptions.selectedItems[0].TurnAction.military_effect" 
								ng-change="actionOptions.selectedItems[0].TurnAction.changed = 1"/>
					</td></tr>
					<tr><td>
						Wildland Safety</td><td>({{actionOptions.selectedItems[0].Action.wildlands}}) 
						<input type="text" size="3" ng-model="actionOptions.selectedItems[0].TurnAction.wildlands" 
								ng-change="actionOptions.selectedItems[0].TurnAction.changed = 1"/>
					</td></tr>
					<tr><td>
						Criminals</td><td>({{actionOptions.selectedItems[0].Action.criminal}}) 
						<input type="text" size="3" ng-model="actionOptions.selectedItems[0].TurnAction.criminal" 
								ng-change="actionOptions.selectedItems[0].TurnAction.changed = 1"/>
					</td></tr>
					<tr><td>
					</td>
					
					</tr>
				</table>

				<button ng-click="saveActionResults()">Save Action Results</button>				

			</div>

			<div ng-show="view == 'business'">
				<button ng-click="saveActionResults()">Save Action Results</button>				

				<style>
					.actionStyle {
					    border: 1px solid rgb(212,212,212);
					    width: 600px; 
	    				height: 250px;
	    				font-size: 70%;
					}
				</style>

				<div class="actionStyle" ng-grid="businessOptions"></div>

				<table>
					<tr><td>Character</td><td><a ng-href="/characters/view/{{businessOptions.selectedItems[0].Character.cardnumber}}">{{businessOptions.selectedItems[0].Character.name}}</a></td></tr>
					<tr><td>Bonus Items</td>
						<td>
							<ul>
								<li ng-repeat="bonus in bonuses">
									{{bonus}}
								</li>
							</ul>
						</td>
					</tr>
					<tr><td>Action</td><td>{{businessOptions.selectedItems[0].Action.name}}</td></tr>
					<tr><td>Target</td><td>{{businessOptions.selectedItems[0].TurnAction.target}}</td></tr>
					<tr><td>Comments</td><td>{{businessOptions.selectedItems[0].TurnAction.comments}}</td></tr>
					<tr><td>Rank</td><td>{{businessOptions.selectedItems[0].SettlementImprovement.rank}}</td></tr>
					<tr><td>Commodity</td><td>{{businessOptions.selectedItems[0].SettlementImprovement.commodity}}</td></tr>
					<tr>
						<td>Earned Value</td>
						<td>
							Override: <input type="checkbox" ng-model="override"/> <input type="text" ng-model="override_value"/>
							{{businessOptions.selectedItems[0].TurnAction.result_value}}
							<button ng-click="computeEarnings()">Compute Earnings</button>
						</td>
					</tr>
					<tr><td>Result</td><td><textarea rows="5" cols="80" ng-model="businessOptions.selectedItems[0].TurnAction.result"/></textarea></tr>
				</table>

				<button ng-click="saveBusinessResults()">Save Business Results</button>				

			</div>

			<div ng-show="view == 'experimental'">

				<hr/>

				<style>
					.actiontab table {  }
					.actiontab th { text-align:left;}
					.actiontab td { text-align:left; }
				</style>


				<table class="actiontab">
					<tr>
						<th>Action</th>
						<th>Character</th>
						<th>Business</th>
						<th>&nbsp;</th>
						<th>Results</th>
						<th>Emailed</th>
					</tr>
					<tr ng-repeat="a in settlementActions2">
						<td>{{a.Action.name}}</th>
						<td>{{a.Character.name}}</td>
						<td>{{a.SettlementImprovement.name}}</td>
						<td>
							<button ng-hide="editing.TurnAction.id == a.TurnAction.id" ng-click="editAction(a)">Select</button>
							<button ng-show="editing.TurnAction.id == a.TurnAction.id" ng-click="editAction(null)">Unselect</button>
						</td>
						<td>{{shorten(a.TurnAction.result)}}</td>
						<td>{{yesNo(a.TurnAction.emailed)}}</td>
					</tr>
				</table>

				<hr/>

				<table class="actiontab" ng-show="editing != null">
					<tr><th>Character</th><td><a ng-href="/characters/view/{{editing.Character.cardnumber}}">{{editing.Character.name}}</a></td></tr>
					<tr><th>Bonus Items</th>
						<td>
							<ul>
								<li ng-repeat="bonus in bonuses">
									{{bonus}}
								</li>
							</ul>
						</td>
					</tr>
					<tr>
						<th>Past Actions <input size="3" ng-model="turnsBack"/></th>
						<td>
							<table>
								<tr>
									<th>Action</th>
									<th>Settlement</th>
									<th>Turns Ago</th>
									<th colspan="3">Details</th>
								<tr ng-repeat="pact in pastactions" ng-show="turnsAgoShow(pact.Turn.id)">
									<td>{{pact.Action.name}}</td>
									<td>{{pact.Settlement.name}}</td>
									<td>{{turnsAgo(pact.Turn.id)}}</td>
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
						</td>
					</th>
					<tr><th>Action</th><td>{{editing.Action.name}}</td></tr>
					<tr><th>Target</th><td>{{editing.TurnAction.target}}</td></tr>
					<tr><th>Comments</th><td>{{editing.TurnAction.comments}}</td></tr>

					<tr ng-show="editing.Action.business == 1"><th>Rank</th><td>{{editing.SettlementImprovement.rank}}</td></tr>
					<tr ng-show="editing.Action.business == 1"><th>Commodity</th><td>{{editing.SettlementImprovement.commodity}}</td></tr>
					
					<tr ng-show="editing.Action.gold_high > 0">
						<th>Compute Gold Change</th>
						<td ng-show="editing.Action.business == 1">
							<button ng-click="computeEarningsForBusiness()">Compute Earnings</button> 
							Factor: {{debugBusinessMultiplier}}  Min: {{debugBusinessMin}} Max: {{debugBusinessMax}}
						</td>
						<td ng-hide="editing.Action.business == 1">
							<button ng-click="computeEarningsForCharacter()">Compute Earnings</button>
						</td>
					</tr>

					<tr>
						<th>Bank Change</th>
						<td>
							<input type="checkbox" ng-model="override"/> 
							<span ng-hide="override">{{editing.TurnAction.result_value}}</span>
							<input ng-show="override" type="text" size="4" ng-model="editing.TurnAction.result_value"/>
						</td>
					</tr>

					<tr><th>Result</th><td><textarea rows="5" cols="80" ng-model="editing.TurnAction.result" ng-change="editing.TurnAction.changed = 1"/></textarea></tr>
					<tr><th>
						Public Order</th><td>({{editing.Action.public_order}}) 
						<input type="text" size="3" ng-model="editing.TurnAction.public_order" 
								ng-change="editing.TurnAction.changed = 1"/>
					</td></tr>
					<tr><th>
						Health</th><td>({{editing.Action.health}}) 
						<input type="text" size="3" ng-model="editing.TurnAction.health" 
								ng-change="editing.TurnAction.changed = 1"/>
					</td></tr>
					<tr><th>
						Happiness</th><td>({{editing.Action.happiness}}) 
						<input type="text" size="3" ng-model="editing.TurnAction.happiness" 
								ng-change="editing.TurnAction.changed = 1"/>
					</td></tr>
					<tr><th>
						Military Effectiveness</th><td>({{editing.Action.military_effect}}) 
						<input type="text" size="3" ng-model="editing.TurnAction.military_effect" 
								ng-change="editing.TurnAction.changed = 1"/>
					</td></tr>
					<tr><th>
						Wildland Safety</th><td>({{editing.Action.wildlands}}) 
						<input type="text" size="3" ng-model="editing.TurnAction.wildlands" 
								ng-change="editing.TurnAction.changed = 1"/>
					</td></tr>
					<tr><th>
						Criminals</th><td>({{editing.Action.criminal}}) 
						<input type="text" size="3" ng-model="editing.TurnAction.criminal" 
								ng-change="editing.TurnAction.changed = 1"/>
					</td></tr>
					<tr><td>
					</td>
					
					</tr>
				</table>

				<button ng-show="editing != null" ng-click="saveActionResults2()">Save Action Results</button>			
				{{editing.TurnAction.saved_message}}
			</div>

		</div>

	</div>
</div>