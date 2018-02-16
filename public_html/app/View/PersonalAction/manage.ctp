<div id="personalActions" ng-app="personalActionsApp">

	<script type="text/javascript">

		var personalActionsApp = angular.module('personalActionsApp',['chart.js']);

		personalActionsApp.controller('PersonalActionsController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {
				$scope.view = 'actions';
				$scope.hideDone = false;
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
				$scope.editController = false;
				$scope.companiesLoaded = false;

				$http({ method: 'GET', url: '/personal_action/loadlands/'}).success(function(data) {
					$scope.lands = data;
				});

				$http({ method: 'GET', url: '/personal_action/loadimprovements/'}).success(function(data) {
					$scope.improvements = data;
				});

				$scope.loadTurn();
				$scope.loadedBonusesId = 0;

				Chart.defaults.global.colours = [
				    { // yellow
				        fillColor: "rgba(253,180,92,0.2)",
				        strokeColor: "rgba(253,180,92,1)",
				        pointColor: "rgba(253,180,92,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(253,180,92,0.8)"
				    },
				    { // red
				        fillColor: "rgba(247,70,74,0.2)",
				        strokeColor: "rgba(247,70,74,1)",
				        pointColor: "rgba(247,70,74,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(247,70,74,0.8)"
				    },
				    { // green
				        fillColor: "rgba(70,191,189,0.2)",
				        strokeColor: "rgba(70,191,189,1)",
				        pointColor: "rgba(70,191,189,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(70,191,189,0.8)"
				    },
				    { // dark grey
				        fillColor: "rgba(77,83,96,0.2)",
				        strokeColor: "rgba(77,83,96,1)",
				        pointColor: "rgba(77,83,96,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(77,83,96,1)"
				    },
				    { // light grey
				        fillColor: "rgba(220,220,220,0.2)",
				        strokeColor: "rgba(220,220,220,1)",
				        pointColor: "rgba(220,220,220,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(220,220,220,0.8)"
				    },
				    { // blue
				        fillColor: "rgba(151,187,205,0.2)",
				        strokeColor: "rgba(151,187,205,1)",
				        pointColor: "rgba(151,187,205,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(151,187,205,0.8)"
				    },
				    { // grey
				        fillColor: "rgba(148,159,177,0.2)",
				        strokeColor: "rgba(148,159,177,1)",
				        pointColor: "rgba(148,159,177,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(148,159,177,0.8)"
				    }
				];

				$scope.opts = {
					datasetFill: false
				};
				
				$scope.labels = [];
			  	$scope.series = ['Order', 'Health', 'Happiness'];
			  	$scope.points = [];
			  	$scope.points[0] = [];
				$scope.points[1] = [];
				$scope.points[2] = [];

				$scope.population = [];
				$scope.population[0] = [];
				$scope.popseries = ['Population'];

				$scope.gold = [];
				$scope.gold[0] = [];
				$scope.goldseries = ['Gold'];

				$scope.trade = [];
				$scope.trade[0] = [];
				$scope.trade[1] = [];
				$scope.trade[2] = [];
				$scope.trade[3] = [];
				$scope.tradeseries = ['Stone','Lumber','Goods','Food'];
			}

			$scope.obfuscate = function(value) {
				if(value >= 90) {
					return 95
				} else if(value >= 83) {
					return 86
				} else if(value >= 78) {
					return 80
				} else if(value >= 72) {
					return 75
				} else if(value >= 65) {
					return 68
				} else if(value >= 60) {
					return 63
				} else if(value >= 40) {
					return 50
				} else {
					return 20
				}
			}

			$scope.obfuscate_wildlands = function(value) {
				if(value >= 75) {
					return 75
				} else if(value >= 50) {
					return 50
				} else if(value >= 25) {
					return 25
				} else {
					return 0
				}
			}

			$scope.convertValuesToData = function() {
				for(i = 0; i < $scope.values.length; i++) {

					$scope.labels[i] = $scope.values[i].e.name;
					$scope.points[0][i] = (parseInt($scope.values[i].sv.public_order) + 0);
					$scope.points[1][i] = (parseInt($scope.values[i].sv.health) + 0);
					$scope.points[2][i] = (parseInt($scope.values[i].sv.happiness) + 0);
					$scope.population[0][i] = (parseInt($scope.values[i].sv.population) + 0);
					$scope.gold[0][i] = (parseInt($scope.values[i].sv.gold) + 0);

					$scope.trade[0][i] = (parseInt($scope.values[i].sv.stone) + 0);
					$scope.trade[1][i] = (parseInt($scope.values[i].sv.lumber) + 0);
					$scope.trade[2][i] = (parseInt($scope.values[i].sv.goods) + 0);
					$scope.trade[3][i] = (parseInt($scope.values[i].sv.food) + 0);
				}
			}

			$scope.loadDefaultActions = function() {
				if($scope.companiesLoaded == false) {
					$http({ method: 'GET', url: '/personal_action/loadDefaultActions/'}).success(function(data) {
						$scope.turn = data;
					});
				}
			}

			$scope.loadCompanies = function() {
				if($scope.companiesLoaded == false) {
					$scope.companiesLoaded = true;
					$http({ method: 'GET', url: '/personal_action/loadCompanies/'}).success(function(data) {
						$scope.companies = data;
					});
				}
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
				$http({ method: 'GET', url: '/personal_action/getAllCurrentActionsForSettlement2/'+settlement.id}).success(function(data) {
					if(data != null) {
						$scope.settlementActions2 = data;
						$scope.settlementActions2.loaded = true;
					} else {
						$scope.settlementActions2 = {'loaded': false};
					}
				});
			  	$http({ method: 'GET', url: '/land_system/graphdata/'+settlement.id}).success(function(values) {
					$scope.values = values;

					$scope.convertValuesToData();
				});

				$http({ method: 'GET', url: '/land_system/graphdetails/'+settlement.id}).success(function(values) {
					$scope.sv = values;
				});

				$http({ method: 'GET', url: '/personal_action/loadhistory/'+settlement.id}).success(function(values) {
					$scope.hist = values;
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

			$scope.returnToEndOfTurn = function() {
				$http({ method: 'GET', url: '/personal_action/returnToEndOfTurn/'}).success(function(data) {
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

			$scope.addCommodities = function() {
				$scope.editing.TurnAction.commodity = $scope.editing.SettlementImprovement.commodity;

				$amount = Math.min($scope.editing.SettlementImprovement.rank * 3, $scope.trades[$scope.editing.SettlementImprovement.commodity]);

				$scope.editing.TurnAction.commodity_value = $amount;
			}

			$scope.computeEarningsForCharacter = function() {
				var gold = $scope.computeEarningsValue($scope.editing.Action.gold_low,$scope.editing.Action.gold_high,1,1,0,100);

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

				$scope.settlement_factor = Math.floor(((parseInt($scope.settlementValues.public_order) + parseInt($scope.settlementValues.health) + parseInt($scope.settlementValues.happiness)) / 3) * $scope.editing.Action.gold_high / 100);

				var gold = $scope.computeEarningsValue($scope.editing.Action.gold_low,$scope.editing.Action.gold_high,$scope.editing.SettlementImprovement.rank,businessActionCount, $scope.settlement_factor);

				$scope.editing.TurnAction.result_value = gold;
				$scope.editing.TurnAction.result = 
							"Your " + $scope.editing.Action.name + " earned the company " + $scope.editing.TurnAction.result_value + " gold";
			}

			$scope.computeEarningsValue = function(low, high, rank, bac, set_base) {
				if($scope.override) {
					return $scope.override_value;
				}

				low = parseInt(low);
				high = parseInt(high);
				rank = parseInt(rank);
				bac = parseInt(bac);

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

				$scope.debugBusinessMax = Math.ceil(set_base + (high * rank * 2 * (factor / bac)));
				$scope.debugBusinessMin = Math.floor(set_base + (low * rank * 2 * (factor / bac)));

				var die1 = Math.floor((Math.random() * ((high-low) * rank)) + (low * rank));
				var die2 = Math.floor((Math.random() * ((high-low) * rank)) + (low * rank));

				$scope.debugDie1 = die1;
				$scope.debugDie2 = die2;

				gold = Math.round((set_base + parseInt(die1) + parseInt(die2)) * factor / bac);
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
				$scope.editing = value;
				$scope.bonuses = null;
				$scope.trades = null;

				$scope.debugDie1 = "";
				$scope.debugDie2 = "";
				$scope.debugBusinessMin = "";
				$scope.debugBusinessMax = "";
				$scope.debugBusinessMultiplier = "";
				$scope.debugBusinessFactor = "";

				if(value != null) {
					$scope.getBonusesForCharacter(value.Character.id,value.Action.bonus_list_id);
					$scope.getPastActionsForCharacter(value.Character.id);
					$scope.getTradesForCharacter(value.Character.id);
					$scope.editing.TurnAction.saved_message = "Not Saved";
				}
			}

			$scope.getBonusesForCharacter = function(cid, aid) {
				$scope.bonuses = null;
				$http({ method: 'GET', url: '/personal_action/getBonusesForCharacter/'+cid+'/'+aid}).success(function(data) {
					$scope.bonuses = data;
				});
			}

			$scope.getTradesForCharacter = function(cid) {
				$scope.trades = null;
				$http({ method: 'GET', url: '/personal_action/getTradesForCharacter/'+cid}).success(function(data) {
					$scope.trades = data;
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

			$scope.selectImprovement = function(imp) {
				$scope.selectedImprovement = imp;
				$scope.improvementSaveStatus = "";
			}

			$scope.updateController = function() {
				$scope.improvementSaveStatus = "Not Yet Saved";
				$scope.editController = false;

				$scope.selectedImprovement.business_id = $scope.newBusiness.id;
				$scope.selectedImprovement.Business = $scope.newBusiness;
			}

			$scope.updateController = function() {
				$scope.improvementSaveStatus = "Not Yet Saved";
				$scope.editController = false;

				$scope.selectedImprovement.business_id = $scope.newBusiness.id;
				$scope.selectedImprovement.Business = null;
			}

			$scope.saveImprovement = function() {
				$scope.improvementSaveStatus = "Saving";

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/personal_action/saveimprovement/',
					data: $scope.selectedImprovement
				}).success(function(data) {
					$scope.selectedImprovement = data;
					$scope.improvementSaveStatus = "Saved";
					$scope.selectedImprovement = null;
				});
			}

			$scope.editCont = function() {
				$scope.editController = true; 
				$scope.loadCompanies();
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
						<button ng-click="loadDefaultActions()" ng-hide="turn.t.defaults_loaded == 1">Load Default Actions</button>
						<button ng-click="emailresults()">Email Results</button> {{emailsSent}}
						<button ng-click="finalizeturn()">Finalize Turn</button>
					</div>
					<div ng-show="turn.t.status == 2">
						<button ng-click="returnToEndOfTurn()">Unfinalize Turn</button>
						<button ng-click="newturn()">New Turn</button>
					</div>
				<?php } ?>
			</div>

			<div>
				<input type="radio" ng-model="view" value="actions"/> Actions<br/>
				&nbsp;&nbsp;<input type="checkbox" ng-model="hideDone"/> Hide Completed Actions</br>
				<input type="radio" ng-model="view" value="improvements"/> Improvements<br/>
				<input type="radio" ng-model="view" value="graph"/> Graph<br/>
				<input type="radio" ng-model="view" value="history"/> Historical Actions</br>

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
			<h2>{{chosenland.Land.name}} - {{chosensettlement.name}}</h2>
			<h3># of Actions {{settlementActions2.length}}</h3>

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

			<div ng-hide="view == 'graph'">
				<h3>Current Events for {{chosensettlement.name}}</h3>
				<textarea ng-model="chosensettlement.current_events" rows="5" cols="80" ></textarea>
				<h3>Staff Notes for {{chosensettlement.name}}</h3>
				<textarea ng-model="chosensettlement.staff_notes" rows="5" cols="80" ></textarea>
				<br/>
				<button ng-click="saveNotes()">Save Current Events and Staff Notes</button>
			</div>

			<div ng-show="view == 'graph'">
				<h3>Historical Graphs</h3>
				<div style="background-color:#ffffff">
					<canvas id="line" class="chart chart-line" chart-data="points" width="800" height="200"
					  chart-labels="labels" chart-legend="true" chart-series="series" chart-options="opts">
					</canvas> 
				</div>

				<div style="background-color:#ffffff">
					<canvas id="line" class="chart chart-line" chart-data="population" width="800" height="200"
					  chart-labels="labels" chart-legend="true" chart-series="popseries" chart-options="opts">
					</canvas> 
				</div>

				<div style="background-color:#ffffff">
					<canvas id="line" class="chart chart-line" chart-data="gold" width="800" height="200"
					  chart-labels="labels" chart-legend="true" chart-series="goldseries" chart-options="opts">
					</canvas> 
				</div>

				<div style="background-color:#ffffff">
					<canvas id="line" class="chart chart-line" chart-data="trade" width="800" height="200"
					  chart-labels="labels" chart-legend="true" chart-series="tradeseries" chart-options="opts">
					</canvas> 
				</div>
			</div>

			<div ng-show="view == 'history'">
				<h3>Historical Actions</h3>
				<table>
					<tr>
						<th>Character</th>
						<th>Action</th>
						<th>Turns Ago</th>
						<th colspan="3">Details</th>
					<tr ng-repeat="h in hist" ng-show="turnsAgoShow(h.Turn.id)">
						<td>{{h.Character.name}}</td>
						<td>{{h.Action.name}}</td>
						<td>{{turnsAgo(h.Turn.id)}}</td>
						<td ng-hide="h.showDetails">
							<button ng-click="h.showDetails = true">Expand</button>
						</td>
						<td ng-show="h.showDetails">
							<button ng-click="h.showDetails = false">Hide</button>
						</td>
						<td ng-show="h.showDetails">
							<b>Target:</b> {{h.TurnAction.target}}<br/>
							<b>Comment:</b> {{h.TurnAction.comments}}<br/>
							<b>Results:</b> {{h.TurnAction.result}}<br/>
						</td>
					</tr>
				</table>
			</div>

			<div ng-show="view == 'improvements'">
				<select ng-model="proveToAdd" ng-options="prove.Improvement.name for prove in improvements">
				</select>
				<button ng-click="addImprovement()">Add Improvement</button>
				<button ng-click="saveSettlement()">Save Settlement</button>

				<hr/>

				<table class="actiontab" ng-hide="selectedImprovement">
					<tr>
						<th>Improvement</th>
						<th>Business</th>
						<th>Turns to Repair</th>
						<th>Actions</th>
					</tr>
					<tr ng-repeat="si in settlement.SettlementImprovement" ng-show="si.Business.name">
						<td>{{si.Improvement.name}}</td>
						<td>{{si.Business.name}}</td>
						<td style="text-align:right">{{si.actions_to_repair}}</td>
						<td><button ng-click="selectImprovement(si)">Edit</button></td>
					</tr>
					<tr ng-repeat="si in settlement.SettlementImprovement" ng-hide="si.Business.name">
						<td>{{si.Improvement.name}}</td>
						<td>{{si.Business.name}}</td>
						<td style="text-align:right">{{si.actions_to_repair}}</td>
						<td><button ng-click="selectImprovement(si)">Edit</button></td>
					</tr>
				</table>

				<div ng-show="selectedImprovement">
					
					<h3>Editing {{selectedImprovement.name}}</h3>
					<table>
						<tr>
							<td>Name</td>
							<td><input type="text" ng-model="selectedImprovement.name"/></td>
						</tr>
						<tr>
							<td>Controlled by</td>
							<td>
								<span ng-show="selectedImprovement.Business.name">{{selectedImprovement.Business.name}}</span>
								<span ng-hide="selectedImprovement.Business.name">the Land</span>
							</td>
							<td><button ng-click="editCont()">Change</button></td>
						</tr>
						<tr ng-show="editController">
							<td>Change Control to</td>
							<td>
								<select ng-model="newBusiness" ng-options="c.Business as c.Business.name for c in companies">
									<option value="0">The Land</option>
								</select>
								<button ng-click="updateController()">Update Controller</button>
								<button ng-click="clearController()">Return Business to the Land</button>
							</td>
						</tr>
						<tr>
							<td>Rank</td>
							<td><select ng-model="selectedImprovement.rank">
								<option>1</option>
								<option>2</option>
								<option>3</option>
								<option>4</option>
							</td>
						</tr>
						<tr>
							<td>Commodity</td>
							<td><select ng-model="selectedImprovement.commodity">
								<option>Luxuries</option>
								<option>Consumables</option>
								<option>Wearables</option>
								<option>Durables</option>
							</td>
						</tr>
						<tr>
							<td>Actions to Repair</td>
							<td><input type="text" ng-model="selectedImprovement.actions_to_repair"/></td>
						</tr>
					</table>
					<button ng-click="selectedImprovement = null">Cancel Edit</button>
					<button ng-click="saveImprovement()">Save</button> {{improvementSaveStatus}}
				</div>
				
			</div>

			<div ng-show="view == 'actions'">
				<style>
					.actiontab table {  }
					.actiontab th { text-align:left;}
					.actiontab td { text-align:left; }
				</style>

				<table class="actiontab">
					<tr>
						<th>Action</th>
						<th>Target</th>
						<th>Character</th>
						<th>Business</th>
						<th>&nbsp;</th>
						<th>Results</th>
						<th>Emailed</th>
					</tr>
					<tr ng-repeat="a in settlementActions2" ng-hide="editing.TurnAction.id != a.TurnAction.id && hideDone == true && a.TurnAction.result.length > 0">
						<td>{{a.Action.name}}</th>
						<td>{{shorten(a.TurnAction.target)}}</td>
						<td>{{a.Character.name}}</td>
						<td>{{a.SettlementImprovement.name}}</td>
						<td>
							<button ng-hide="editing.TurnAction.id == a.TurnAction.id" ng-click="editAction(a)">Select</button>
							<button ng-show="editing.TurnAction.id == a.TurnAction.id" ng-click="editAction(null)">Unselect</button>
							<span ng-show="a.TurnAction.is_default == 1">Default</span>
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
					<tr ng-show="editing.Action.business == 1">
						<th>Commodity</th>
						<td>{{editing.SettlementImprovement.commodity}} <button ng-click="addCommodities()">Add Commodities</button></td>
					</tr>
					<tr ng-show="editing.Action.commodities == 1">
						<th>Commodities Produced</th>
						<td>{{editing.TurnAction.commodity_value}} {{editing.TurnAction.commodity}}</td>
					</tr>

					<tr ng-show="editing.Action.gold_high > 0">
						<th>Gold Change</th>
						<td ng-show="editing.Action.business == 1">
							<button ng-click="computeEarningsForBusiness()">Compute Earnings</button> 
							Factor: {{debugBusinessMultiplier | number:2}}  Min: {{debugBusinessMin}} Max: {{debugBusinessMax}} Base: {{settlement_factor | number:0}} Rolls: {{debugDie1}}, {{debugDie2}} 
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