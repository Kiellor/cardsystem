<style>
	.landmath table { border-collapse: collapse; border: 1px solid black; }
	.landmath th { border: 1px solid black; padding: 3px; }
	.landmath td { border: 1px solid black; padding: 3px; text-align:right; }

	.imptable table { border-collapse: collapse; border: 1px solid black; }
	.imptable th { border: 1px solid black; padding: 3px; vertical-align: top; text-align:left; }
	.imptable td { border: 1px solid black; padding: 3px; vertical-align: top;}

</style>

<div id="landSystem" ng-app="landSystemApp">

	<script type="text/javascript">

		var landSystemApp = angular.module('landSystemApp',[]);

		landSystemApp.controller('LandSystemController',['$scope','$http', '$filter', '$location', function($scope,$http,$filter,$location) {
      
			$scope.initialize = function() {
				$scope.debug = [];
				$scope.view = 'overview';
		        $scope.lands = [];
		        $scope.land_selected = null;
		        $scope.improvements = null;
		        $scope.tradeMathSaved = true;
		        $scope.turnpos = 0;
		        $scope.letterview = true;
	        
		        $scope.loadLandsURL = '/land_system/loadLands';

		        <?php if(isset($view_as_player_id) && $view_as_player_id > 0) { ?>
		          $scope.loadLandsURL = '/land_system/loadLandsAs/<?php echo $view_as_player_id; ?>';
		        <?php } ?>			    

		        $scope.loadTurns();
						
		        <?php if(AuthComponent::user('role_landadmin')) { ?>
				  $scope.loadPlayers(); 
				<?php } ?>
			}
			
			<!-- This guy grabs a table of the lands that each of the players characters can work on. -->
			$scope.loadLands = function() {
				$http({ method: 'GET', url: $scope.loadLandsURL}).success(function(data) {
					$scope.lands = data;

					if($scope.lands.length == 1) {
						$scope.land_selected = $scope.lands[0];

						$scope.loadLandValues();
					}
				});
			}

			$scope.loadImprovements = function() {
				if($scope.improvements == null) {
					$http({ method: 'GET', url: '/personal_action/loadimprovements/'}).success(function(data) {
						$scope.improvements = data;
					});
				}
			}

			$scope.loadTurns = function() {
				$http({ method: 'GET', url: '/land_system/loadTurns/'}).success(function(data) {
					$scope.turns = data;
					$scope.turnid = $scope.turns[$scope.turnpos].Turn.id;

					$scope.loadLands();
				});
			}

			$scope.updateValues = function(delta) {
				$scope.turnpos += delta;
				$scope.turnid = $scope.turns[$scope.turnpos].Turn.id;
				$scope.loadLandValues();
			}

			$scope.loadLandValues = function() {
				$http({ method: 'GET', url: '/land_system/loadLandValues/'+$scope.land_selected.Land.id+"/"+$scope.turnid}).success(function(data) {
					$scope.landValues = data;
					$scope.compressImprovements();
					$scope.tradeMath();
					$scope.convertPrioritiesToNumbers();
				});

				$http({ method: 'GET', url: '/land_system/loadTradeValues/'+$scope.land_selected.Land.id+"/"+$scope.turnid}).success(function(data) {
					$scope.trades = data;
				});

				$http({ method: 'GET', url: '/land_system/loadTurnMessages/'+$scope.land_selected.Land.id+"/"+$scope.turnid}).success(function(data) {
					$scope.messages = data;
				});

			}

			$scope.compressImprovements = function() {
				for(i = 0; i < $scope.landValues.length; i++) {
					$scope.landValues[i].ImprovementSummary = {};

					for(j = 0; j < $scope.landValues[i].Settlement.SettlementImprovement.length; j++) {
						var key = $scope.landValues[i].Settlement.SettlementImprovement[j].Improvement.sort + $scope.landValues[i].Settlement.SettlementImprovement[j].Improvement.name;

						if($scope.landValues[i].ImprovementSummary.hasOwnProperty(key)) {
							$scope.landValues[i].ImprovementSummary[key].quantity++;
							if($scope.landValues[i].Settlement.SettlementImprovement[j].active == 1) {
								$scope.landValues[i].ImprovementSummary[key].active_count++;
							}
							if($scope.landValues[i].Settlement.SettlementImprovement[j].actions_to_repair > 0) {
								$scope.landValues[i].ImprovementSummary[key].damage_count++;	
							}
						} else {
							$scope.landValues[i].ImprovementSummary[key] = [];
							$scope.landValues[i].ImprovementSummary[key].name = $scope.landValues[i].Settlement.SettlementImprovement[j].Improvement.name;
							$scope.landValues[i].ImprovementSummary[key].quantity = 1;
							if($scope.landValues[i].Settlement.SettlementImprovement[j].active == 1) {
								$scope.landValues[i].ImprovementSummary[key].active_count = 1;
							} else {
								$scope.landValues[i].ImprovementSummary[key].active_count = 0;
							}
							if($scope.landValues[i].Settlement.SettlementImprovement[j].actions_to_repair > 0) {
								$scope.landValues[i].ImprovementSummary[key].damage_count = 1;	
							} else {
								$scope.landValues[i].ImprovementSummary[key].damage_count = 0;	
							}

							$scope.landValues[i].ImprovementSummary[key].sort = $scope.landValues[i].Settlement.SettlementImprovement[j].Improvement.sort;
						}
					}
				}
			}
			
			<?php if(AuthComponent::user('role_admin')) { ?>
			<!-- This guy grabs the full list of players. -->
				$scope.loadPlayers = function() {
					$http({ method: 'GET', url: '/land_system/loadPlayers'}).success(function(data) {
						$scope.players = data;
					});
				}
			<?php } ?>

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

			$scope.focusOn = function(settlement) {
				$scope.t = settlement;
				$scope.view = 'settlement';
			}

			$scope.editImprovement = function(improvement) {
				$scope.ei = improvement;
			}

			$scope.cancelEditImprovement = function() {
				$scope.ei = null;
			}

			$scope.saveImprovement = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/saveimprovement/',
					data: $scope.ei
				}).success(function(data) {
					$scope.ei = null;
					$scope.loadImprovements();
				});				
			}

			$scope.saveGoals = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/savetradegoals/',
					data: $scope.landValues
				}).success(function(data) {
					$scope.debug = data;
					$scope.tradeGoalsSaved = true;
				});
			}

			$scope.orderfunc = function(imp) {
				return imp.Improvement.sort + imp.Improvement.name;
			}

			$scope.orderfunc2 = function(imp) {
				return imp.sort + imp.name;
			}

			$scope.tradeMathChanged = function() {
				$scope.tradeMathSaved = false;
				$scope.tradeMath();
			}

			$scope.tradeMath = function() {
				for(var i = 0; i < $scope.landValues.length; i++) {

					// $scope.landValues[i].SettlementValues.food_change = -1 * 
					// 	Math.min($scope.landValues[i].SettlementValues.food,
					// 		Math.round((parseInt($scope.landValues[i].SettlementValues.population) + 
					// 				parseInt($scope.landValues[i].SettlementValues.military)) / 10));

					$scope.landValues[i].SettlementValues.food_spent = Math.round((parseInt($scope.landValues[i].SettlementValues.population) + parseInt($scope.landValues[i].SettlementValues.military) + parseInt($scope.landValues[i].SettlementValues.criminal)) / 15);
					
					$scope.landValues[i].SettlementValues.food_earned = 0;

					$scope.landValues[i].SettlementValues.gold_change = 0;
					$scope.landValues[i].SettlementValues.stone_change = 0;
					$scope.landValues[i].SettlementValues.lumber_change = 0;
					$scope.landValues[i].SettlementValues.goods_change = 0;
					$scope.landValues[i].SettlementValues.food_storage = 0;

					$scope.landValues[i].SettlementValues.labor = Math.max(0,Math.round($scope.landValues[i].SettlementValues.population * $scope.landValues[i].SettlementValues.public_order * $scope.landValues[i].SettlementValues.health / 100 / 100));
					$scope.landValues[i].SettlementValues.labor_needed = 0;

					$scope.landValues[i].SettlementValues.labor_food = 0;

					if($scope.landValues[i].SettlementValues.food_spent > $scope.landValues[i].SettlementValues.food) {
						$scope.landValues[i].SettlementValues.labor_food = 5 * ($scope.landValues[i].SettlementValues.food_spent - $scope.landValues[i].SettlementValues.food);

						if($scope.landValues[i].SettlementValues.labor_food < $scope.landValues[i].SettlementValues.labor) {
							$scope.landValues[i].SettlementValues.food_earned = ($scope.landValues[i].SettlementValues.food_spent - $scope.landValues[i].SettlementValues.food);
						} else {
							$scope.landValues[i].SettlementValues.labor_food = $scope.landValues[i].SettlementValues.labor;
							$scope.landValues[i].SettlementValues.food_earned = Math.round($scope.landValues[i].SettlementValues.labor_food / 5);
						}
					}

					$scope.landValues[i].SettlementValues.food_change = $scope.landValues[i].SettlementValues.food_earned - $scope.landValues[i].SettlementValues.food_spent;

					for(var j = 0; j < $scope.landValues[i].Settlement.SettlementImprovement.length; j++) {
						if($scope.landValues[i].Settlement.SettlementImprovement[j].active == 1 &&
							$scope.landValues[i].Settlement.SettlementImprovement[j].completed <= $scope.turnid) {

							imp = $scope.landValues[i].Settlement.SettlementImprovement[j].Improvement;
							si = $scope.landValues[i].Settlement.SettlementImprovement[j];

							$scope.landValues[i].SettlementValues.gold_change += parseInt(imp.gold);
							$scope.landValues[i].SettlementValues.food_change += parseInt(imp.food);
							$scope.landValues[i].SettlementValues.stone_change += parseInt(imp.stone);
							$scope.landValues[i].SettlementValues.lumber_change += parseInt(imp.lumber);
							$scope.landValues[i].SettlementValues.goods_change += parseInt(imp.goods);

							if(si.business_id == 0) {
								$scope.landValues[i].SettlementValues.gold_change -= parseInt(imp.maint_gold);
								$scope.landValues[i].SettlementValues.goods_change -= parseInt(imp.maint_goods);
								$scope.landValues[i].SettlementValues.stone_change -= parseInt(imp.maint_stone);
								$scope.landValues[i].SettlementValues.lumber_change -= parseInt(imp.maint_lumber);
								
								$scope.landValues[i].SettlementValues.labor_needed += parseInt(imp.maint_labor);
							}

							$scope.landValues[i].SettlementValues.food_storage += parseInt(imp.food_storage);
						}
					}

					$scope.landValues[i].SettlementValues.labor_avail = Math.max(0,($scope.landValues[i].SettlementValues.labor - $scope.landValues[i].SettlementValues.labor_needed));

					if($scope.landValues[i].SettlementValues.labor < $scope.landValues[i].SettlementValues.labor_needed) {
						$scope.landValues[i].SettlementValues.labor_shortage = true;
					}

					$scope.landValues[i].SettlementValues.new_food = parseInt($scope.landValues[i].SettlementValues.food) + parseInt($scope.landValues[i].SettlementValues.food_change);
					$scope.landValues[i].SettlementValues.new_gold = parseInt($scope.landValues[i].SettlementValues.gold) + parseInt($scope.landValues[i].SettlementValues.gold_change);
					$scope.landValues[i].SettlementValues.new_stone = parseInt($scope.landValues[i].SettlementValues.stone) + parseInt($scope.landValues[i].SettlementValues.stone_change);
					$scope.landValues[i].SettlementValues.new_lumber = parseInt($scope.landValues[i].SettlementValues.lumber) + parseInt($scope.landValues[i].SettlementValues.lumber_change);
					$scope.landValues[i].SettlementValues.new_goods = parseInt($scope.landValues[i].SettlementValues.goods) + parseInt($scope.landValues[i].SettlementValues.goods_change);

					$scope.landValues[i].SettlementValues.food_demand = 
						Math.max(0,$scope.landValues[i].SettlementValues.food_goal - $scope.landValues[i].SettlementValues.new_food);
					$scope.landValues[i].SettlementValues.food_surplus = 
						Math.max(0,$scope.landValues[i].SettlementValues.new_food - $scope.landValues[i].SettlementValues.food_goal);

					$scope.landValues[i].SettlementValues.gold_demand = 
						Math.max(0,$scope.landValues[i].SettlementValues.gold_goal - $scope.landValues[i].SettlementValues.new_gold);
					$scope.landValues[i].SettlementValues.gold_surplus = 
						Math.max(0,$scope.landValues[i].SettlementValues.new_gold - $scope.landValues[i].SettlementValues.gold_goal);

					$scope.landValues[i].SettlementValues.stone_demand =
						Math.max(0,$scope.landValues[i].SettlementValues.stone_goal - $scope.landValues[i].SettlementValues.new_stone);
					$scope.landValues[i].SettlementValues.stone_surplus =
						Math.max(0,$scope.landValues[i].SettlementValues.new_stone - $scope.landValues[i].SettlementValues.stone_goal);

					$scope.landValues[i].SettlementValues.lumber_demand =
						Math.max(0,$scope.landValues[i].SettlementValues.lumber_goal - $scope.landValues[i].SettlementValues.new_lumber);
					$scope.landValues[i].SettlementValues.lumber_surplus =
						Math.max(0,$scope.landValues[i].SettlementValues.new_lumber - $scope.landValues[i].SettlementValues.lumber_goal);

					$scope.landValues[i].SettlementValues.goods_demand =
						Math.max(0,$scope.landValues[i].SettlementValues.goods_goal - $scope.landValues[i].SettlementValues.new_goods);
					$scope.landValues[i].SettlementValues.goods_surplus =
						Math.max(0,$scope.landValues[i].SettlementValues.new_goods - $scope.landValues[i].SettlementValues.goods_goal);
				}
			}

			$scope.startBuilding = function(settlement) {
				$scope.buildingInSettlement = settlement;
				$scope.view = 'startBuilding';
			}

			$scope.showSettlement = function(settlement) {
				if($scope.view != 'startBuilding') {
					return true;
				}

				if($scope.buildingInSettlement == settlement) {
					return true;
				}

				return false;
			}

			$scope.convertPrioritiesToNumbers = function() {
				for(i = 0; i < $scope.landValues.length; i++) {
					for(j = 0; j < $scope.landValues[i].Settlement.SettlementBuilding.length; j++) {
						$scope.landValues[i].Settlement.SettlementBuilding[j].priority = parseInt($scope.landValues[i].Settlement.SettlementBuilding[j].priority) + 0;
					}
				}
			}

			$scope.addToQueue = function(imp) {
				$scope.newImp = {};
				$scope.newImp.improvement_id = imp.Improvement.id;
				$scope.newImp.settlement_id = $scope.buildingInSettlement.Settlement.id;
				$scope.newImp.turns_left = imp.Improvement.turns_needed;
				$scope.newImp.max_labor = imp.Improvement.labor_needed / imp.Improvement.turns_needed;
				$scope.newImp.labor_left = imp.Improvement.labor_needed;
				$scope.newImp.priority = $scope.buildingInSettlement.Settlement.SettlementBuilding.length + 1;

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/addBuilding/',
					data: $scope.newImp
				}).success(function(data) {
					$scope.debug = data;
					$scope.ei = null;
					$scope.loadLands();	
					$scope.view = 'building';
				});		

			}

			$scope.totalCost = function(imp) {
				return  parseInt(imp.Improvement.stone_needed) * 10 + 
						parseInt(imp.Improvement.wood_needed) * 10 + 
						parseInt(imp.Improvement.gold_needed) +
						parseInt(imp.Improvement.goods_needed) * 10;
			}

			$scope.totalMaintenance = function(imp) {
				return	parseInt(imp.Improvement.maint_gold) +
						parseInt(imp.Improvement.maint_goods) * 10 +
						parseInt(imp.Improvement.maint_stone) * 10 +
						parseInt(imp.Improvement.maint_lumber) * 10;
			}

			$scope.disable = function(imp) {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/disableBuilding/',
					data: imp
				}).success(function(data) {
					$scope.debug = data;
					$scope.tradeMath();
					imp.active = 0;
				});	
			}

			$scope.enable = function(imp) {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/enableBuilding/',
					data: imp
				}).success(function(data) {
					$scope.debug = data;
					$scope.tradeMath();
					imp.active = 1;
				});	
			}

			$scope.removeImp = function(imp) {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/removeBuilding/',
					data: imp
				}).success(function(data) {
					$scope.debug = data;
					$scope.loadLands();	
				});	
			}

			$scope.setManualStatus = function(imp, status) {
				imp.manualbuild = status;

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/setManualStatus/',
					data: imp
				}).success(function(data) {
					$scope.debug = data;
				});	
			}

			$scope.manuallyBuild = function(sb,s) {
				s.SettlementValues.gold = parseInt(s.SettlementValues.gold) - parseInt(sb.Improvement.gold_needed);
				s.SettlementValues.stone = parseInt(s.SettlementValues.stone) - parseInt(sb.Improvement.stone_needed);
				s.SettlementValues.lumber = parseInt(s.SettlementValues.lumber) - parseInt(sb.Improvement.wood_needed);
				s.SettlementValues.goods = parseInt(s.SettlementValues.goods) - parseInt(sb.Improvement.goods_needed);

				sb.paidfor = $scope.turnid;
				sb.manualbuild = 1;
 
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/manuallyStartBuilding/',
					data: { 'SettlementBuilding': sb, 'SettlementValues': s.SettlementValues }
				}).success(function(data) {
					$scope.debug = data;
				});	
			}

			$scope.raisePriority = function(imp, settlement) {

				imp.priority--;
				if(imp.priority < 1) {
					imp.priority = 1;
				}

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/changePriority/',
					data: imp
				}).success(function(data) {
					$scope.debug = data;
				});	

				for(i = 0; i < settlement.Settlement.SettlementBuilding.length; i++) {
					imp2 = settlement.Settlement.SettlementBuilding[i];

					if(imp2.priority == imp.priority && imp2.id != imp.id) {
						imp2.priority++;
						$http({ 
							method: 'POST', 
							headers: { 'Content-Type': 'application/json' }, 
							url: '/land_system/changePriority/',
							data: imp2
						}).success(function(data) {
							$scope.debug = data;
						});	
					}
				}

			}

			$scope.lowerPriority = function(imp, settlement) {

				imp.priority++;

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/changePriority/',
					data: imp
				}).success(function(data) {
					$scope.debug = data;
				});

				for(i = 0; i < settlement.Settlement.SettlementBuilding.length; i++) {
					imp2 = settlement.Settlement.SettlementBuilding[i];

					if(imp2.priority == imp.priority && imp2.id != imp.id) {
						imp2.priority--;
						if(imp2.priority < 1) {
							imp2.priority == 1;
						}
						$http({ 
							method: 'POST', 
							headers: { 'Content-Type': 'application/json' }, 
							url: '/land_system/changePriority/',
							data: imp2
						}).success(function(data) {
							$scope.debug = data;
						});	
					}
				}	
			}

			$scope.showManualBuildButton = function(sb, s) {
				if(
					parseInt(sb.Improvement.gold_needed) <= parseInt(s.SettlementValues.gold) &&
					parseInt(sb.Improvement.stone_needed) <= parseInt(s.SettlementValues.stone) &&
					parseInt(sb.Improvement.wood_needed) <= parseInt(s.SettlementValues.lumber) &&
					parseInt(sb.Improvement.goods_needed) <= parseInt(s.SettlementValues.goods)
				) {
					return true;
				}

				return false;
			}

		}]);
		
	</script>

	<div ng-controller="LandSystemController" ng-init="initialize()">

		<table>
			<tr>
				<td colspan="2" style="vertical-align:top">
					<?php if(AuthComponent::user('role_landadmin')) { ?>
						<div>
							<div>
								<ul>
									<li ng-repeat="p in players">
										<a ng-href="/land_system/viewAs/{{p.Character.player_id}}">{{p.Land.name}} -- {{p.LandAdmin.role}} -- {{p.Character.name}}</a>
									</li>
								</ul>								
							</div>
						</div>
					<?php } ?>
						
					<h3>Values after {{turns[turnpos].Event.name}}</h3>

					<div ng-show="turns[turnpos].Turn.status == 3">Turn is Finalized</div>
					<div ng-show="turns[turnpos].Turn.status == 2">Last Known Values</div>
					<div ng-show="turns[turnpos].Turn.status == 1">Turn is Still Processing</div>
					<div ng-show="turns[turnpos].Turn.status == 0">Turn is Open</div>

					<button ng-show="turnpos < turns.length" ng-click="updateValues(1)">Prev</button>
					<button ng-show="turnpos > 0"  ng-click="updateValues(-1)">Next</button>
					<br/>Turn Id: {{turnid}}

					<div ng-show="lands.length > 1">
						<div>
							<h4>Select a land:</h4>
						</div>
						<div>
							<select ng-model="land_selected" ng-options="c.Land.name for c in lands" ng-change="loadLandValues()"></select>
						</div>
					</div>

					<h2>{{land_selected.Character.name}} ({{land_selected.Character.cardnumber}})</h2>
				</td>
			</tr>
			<tr ng-show="land_selected">
				<td style="vertical-align:top">							
					
						<div ng-show="lands.length > 0">
								<h3>{{land_selected.Land.name}}</h3>
						</div>
						
						<img ng-src="{{land_selected.Land.heraldry}}"/>

						<div>
							<input type="radio" ng-model="view" value="overview"/>Overview 
								<span ng-show="view == 'overview'"><input type="checkbox" ng-model="letterview"/>Letter View</span>
							<br/>
							<input type="radio" ng-model="view" value="settlement"/>One Settlement<br/>
							<input type="radio" ng-model="view" value="trade"/>Trade Goals<br/>
							<input type="radio" ng-model="view" value="building" ng-click="loadImprovements()"/>Building Orders<br/>
							<span ng-show="view == 'startBuilding'">&nbsp;&nbsp;&nbsp;<input type="radio" ng-model="view" value="startBuilding"/>Building In Progress<br/></span>
							<input type="radio" ng-model="view" value="improvements" ng-click="loadImprovements()"/>Improvement&nbsp;Docs<br/>
						</div>

				</td>
				<td style="vertical-align:top">

					<div ng-show="view == 'overview'">
						<h3>Overview</h3>
						<div ng-hide="landValues">No reports available for this turn</div>

						<div ng-repeat="s in landValues">
							<div width="80%">
								<a ng-click="focusOn(s)" style="text-decoration:underline;">{{s.Settlement.name}}</a> has a population of {{s.SettlementValues.population | number:0}} who are in {{superlify(s.SettlementValues.health)}} health.  Public order is {{superlify(s.SettlementValues.public_order)}} and over all happiness is {{superlify(s.SettlementValues.happiness)}}.  The area immediately surrounding the settlement is {{superlify_wildlands(s.SettlementValues.wildlands)}} and the local militia has {{s.SettlementValues.military | number:0}} soldiers at {{superlify(s.SettlementValues.military_effect)}} readiness.
								There is enough food for {{s.SettlementValues.food * 10 | number:0}} persons in the stores.  The local treasury is tracking assets worth {{s.SettlementValues.gold | number:0}} gold and our stockpiles include {{s.SettlementValues.stone | number:0}} units of stone, {{s.SettlementValues.lumber | number:0}} units of lumber and {{s.SettlementValues.goods | number:0}} units of goods.
								<span ng-repeat="m in messages" ng-show="m.SettlementMessages.settlement_id == s.Settlement.id">{{m.SettlementMessages.message}} </span>
							</div>

							<div>
								<ul ng-hide="letterview">
									<li ng-repeat="imp in s.ImprovementSummary | orderBy:orderfunc2">
										{{imp.quantity}} {{imp.name}} 
										<span ng-show="imp.damage_count > 0"> --- <b>{{imp.damage_count}} damaged</b></span>
										<span ng-show="imp.active_count < imp.quantity"> --- <b>{{imp.active_count}} active</b></span>
									</li>
								</ul>
								<br ng-show="letterview"/>

							</div>
						</div>

						<div ng-hide="trades">No trade data available for this turn<br/></div>
						<div>
							<div ng-repeat="t in trades">{{t.LandTrades.trade_line}}</div>
						</div>
					</div>

					<div ng-show="view == 'settlement'">
						<h3>Detail of {{t.Settlement.name}}</h3>
						
						<div ng-hide="landValues">No reports available for this turn</div>

						<div ng-show="landValues">
							Change to:
							<ul>
								<li ng-repeat="s in landValues">
									<a ng-click="focusOn(s)" style="text-decoration:underline;">{{s.Settlement.name}}</a>
								</li>
							</ul>
						</div>

						<div>
							<div width="80%" ng-show="t.Settlement">
								{{t.Settlement.name}} has a population of {{t.SettlementValues.population | number:0}} who are in {{superlify(t.SettlementValues.health)}} health.  Public order is {{superlify(t.SettlementValues.public_order)}} and over all happiness is {{superlify(t.SettlementValues.happiness)}}.  The area immediately surrounding the settlement is {{superlify_wildlands(t.SettlementValues.wildlands)}} and the local militia has {{t.SettlementValues.military | number:0}} soldiers at {{superlify(t.SettlementValues.military_effect)}} readiness.
								There is enough food for {{t.SettlementValues.food * 10 | number:0}} persons in the stores.  The local treasury is tracking assets worth {{t.SettlementValues.gold | number:0}} gold and our stockpiles include {{t.SettlementValues.stone | number:0}} units of stone, {{t.SettlementValues.lumber | number:0}} units of lumber and {{t.SettlementValues.goods | number:0}} units of goods.
								<br/>
								<br/>

								<table class="imptable">
									<tr><th>Improvement</th><th>Benefits</th><th>Detriments</th><th>Maintenance</th></tr>

									<tr ng-repeat="imp in t.Settlement.SettlementImprovement | orderBy:orderfunc">
										<th>
											{{imp.Improvement.name}}
											<div ng-show="imp.Improvement.optional == 1">
												<button ng-show="imp.active == 1 && imp.business_id == 0" ng-click="disable(imp)">Disable</button>
												<button ng-show="imp.active == 0" ng-click="enable(imp)">Enable</button>
											</div> 
										</th>
										<td>
											<ul ng-show="imp.active">
											<!-- Benefits -->
												<li ng-show="imp.Improvement.population > 0">Population +{{imp.Improvement.population}}</li>
												<li ng-show="imp.Improvement.public_order > 0">Public Order +{{imp.Improvement.public_order}}</li>
												<li ng-show="imp.Improvement.health > 0">Health +{{imp.Improvement.health}}</li>
												<li ng-show="imp.Improvement.happiness > 0">Happiness +{{imp.Improvement.happiness}}</li>
												<li ng-show="imp.Improvement.food > 0">Food +{{imp.Improvement.food}}</li>
												<li ng-show="imp.Improvement.food_storage > 0">Food Storage +{{imp.Improvement.food_storage}}</li>
												<li ng-show="imp.Improvement.gold > 0">Assets +{{imp.Improvement.gold}}</li>
												<li ng-show="imp.Improvement.goods > 0">Goods +{{imp.Improvement.goods}}</li>
												<li ng-show="imp.Improvement.stone > 0">Stone +{{imp.Improvement.stone}}</li>
												<li ng-show="imp.Improvement.lumber > 0">Lumber +{{imp.Improvement.lumber}}</li>
												<li ng-show="imp.Improvement.military > 0">Military +{{imp.Improvement.military}}</li>
												<li ng-show="imp.Improvement.wildlands > 0">Wildlands Safety +{{imp.Improvement.wildlands}}</li>
												<li ng-show="imp.Improvement.taxes > 0">Taxes +{{imp.Improvement.taxes}}</li>
											</ul>
										</td>
										<td>
											<ul ng-show="imp.active">
											<!-- Detriments -->
												<li ng-show="imp.Improvement.population < 0">Population {{imp.Improvement.population}}</li>
												<li ng-show="imp.Improvement.public_order < 0">Public Order {{imp.Improvement.public_order}}</li>
												<li ng-show="imp.Improvement.health < 0">Health {{imp.Improvement.health}}</li>
												<li ng-show="imp.Improvement.happiness < 0">Happiness {{imp.Improvement.happiness}}</li>
												<li ng-show="imp.Improvement.food < 0">Food {{imp.Improvement.food}}</li>
												<li ng-show="imp.Improvement.food_storage < 0">Food Storage {{imp.Improvement.food_storage}}</li>
												<li ng-show="imp.Improvement.gold < 0">Assets {{imp.Improvement.gold}}</li>
												<li ng-show="imp.Improvement.goods < 0">Goods {{imp.Improvement.goods}}</li>
												<li ng-show="imp.Improvement.stone < 0">Stone {{imp.Improvement.stone}}</li>
												<li ng-show="imp.Improvement.lumber < 0">Lumber {{imp.Improvement.lumber}}</li>
												<li ng-show="imp.Improvement.military < 0">Military {{imp.Improvement.military}}</li>
												<li ng-show="imp.Improvement.wildlands < 0">Wildlands Safety {{imp.Improvement.wildlands}}</li>
												<li ng-show="imp.Improvement.taxes < 0">Taxes {{imp.Improvement.taxes}}</li>
											</ul>
										</td>
										<td>
											<div ng-show="imp.Business.name"><b>Operated by {{imp.Business.name}}</b></div>
											<div ng-hide="imp.Business.name">
												<ul ng-show="imp.active">
												<!-- Maintenance -->
													<li ng-show="imp.Improvement.maint_gold > 0"/>Assets {{imp.Improvement.maint_gold}}</li>
													<li ng-show="imp.Improvement.maint_goods > 0"/>Goods {{imp.Improvement.maint_goods}}</li>
													<li ng-show="imp.Improvement.maint_stone > 0"/>Stone {{imp.Improvement.maint_stone}}</li>
													<li ng-show="imp.Improvement.maint_lumber > 0"/>Lumber {{imp.Improvement.maint_lumber}}</li>
													<li ng-show="imp.Improvement.maint_labor > 0"/>Labor {{imp.Improvement.maint_labor}}</li>
												</ul>
											</div>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>

					<div ng-show="view == 'trade'">
						
						<div ng-hide="landValues">Use previous turn to set trade goals</div>

						<div ng-show="turns[turnpos].Turn.status != 3">
							<button ng-hide="tradeMathSaved" ng-click="saveGoals()">Save Trade Goals</button>
						</div>
						<div ng-repeat="s in landValues">
							<div width="80%">
								<h3>{{s.Settlement.name}} Trade Goals</h3>
								<table class="landmath">
									<tr>
										<td style="border:none;"></td>
										<th>Current Value</th>
										<th>Monthly Change</th>
										<th>Projected Value</th>
										<th>Goal</th>
										<th>Projected Shortage</th>
										<th>Projected Surplus</th>
										<th>Max Food Storage</th>
										<th>Min Food Needed</th>
									</tr>
									<tr>
										<th>Food</th>
										<td>{{s.SettlementValues.food | number:0}}</td>
										<td>{{s.SettlementValues.food_change > 0 ? '+' : ''}}{{s.SettlementValues.food_change | number:0}}</td>
										<td>{{s.SettlementValues.new_food | number:0}}</td>
										<td><input type="text" ng-model="s.SettlementValues.food_goal" size="4" ng-change="tradeMathChanged()" ng-disabled="turns[turnpos].Turn.status == 3"></td>
										<td>{{s.SettlementValues.food_demand | number:0}}</td>
										<td>{{s.SettlementValues.food_surplus | number:0}}</td>
										<td>{{s.SettlementValues.food_storage | number:0}}</td>
										<td>{{s.SettlementValues.food_spent | number:0}}</td>
									</tr>
									<tr>
										<th>Assets</th>
										<td>{{s.SettlementValues.gold | number:0}}</td>
										<td>{{s.SettlementValues.gold_change > 0 ? '+' : ''}}{{s.SettlementValues.gold_change | number:0}}</td>
										<td>{{s.SettlementValues.new_gold | number:0}}</td>
										<td><input type="text" ng-model="s.SettlementValues.gold_goal" size="4" ng-change="tradeMathChanged()" ng-disabled="turns[turnpos].Turn.status == 3"></td>
										<td>{{s.SettlementValues.gold_demand | number:0}}</td>
										<td>{{s.SettlementValues.gold_surplus | number:0}}</td>
									</tr>
									<tr>
										<th>Stone</th>
										<td>{{s.SettlementValues.stone | number:0}}</td>
										<td>{{s.SettlementValues.stone_change > 0 ? '+' : ''}}{{s.SettlementValues.stone_change | number:0}}</td>
										<td>{{s.SettlementValues.new_stone | number:0}}</td>
										<td><input type="text" ng-model="s.SettlementValues.stone_goal" size="4" ng-change="tradeMathChanged()" ng-disabled="turns[turnpos].Turn.status == 3"></td>
										<td>{{s.SettlementValues.stone_demand | number:0}}</td>
										<td>{{s.SettlementValues.stone_surplus | number:0}}</td>
									</tr>
									<tr>
										<th>Lumber</th>
										<td>{{s.SettlementValues.lumber | number:0}}</td>
										<td>{{s.SettlementValues.lumber_change > 0 ? '+' : ''}}{{s.SettlementValues.lumber_change | number:0}}</td>
										<td>{{s.SettlementValues.new_lumber | number:0}}</td>
										<td><input type="text" ng-model="s.SettlementValues.lumber_goal" size="4" ng-change="tradeMathChanged()" ng-disabled="turns[turnpos].Turn.status == 3"></td>
										<td>{{s.SettlementValues.lumber_demand | number:0}}</td>
										<td>{{s.SettlementValues.lumber_surplus | number:0}}</td>
									</tr>
									<tr>
										<th>Goods</th>
										<td>{{s.SettlementValues.goods | number:0}}</td>
										<td>{{s.SettlementValues.goods_change > 0 ? '+' : ''}}{{s.SettlementValues.goods_change | number:0}}</td>
										<td>{{s.SettlementValues.new_goods | number:0}}</td>
										<td><input type="text" ng-model="s.SettlementValues.goods_goal" size="4" ng-change="tradeMathChanged()" ng-disabled="turns[turnpos].Turn.status == 3"></td>
										<td>{{s.SettlementValues.goods_demand | number:0}}</td>
										<td>{{s.SettlementValues.goods_surplus | number:0}}</td>
									</tr>
								</table>
							</div>
						</div>

						<div ng-show="turns[turnpos].Turn.status != 3">
							<button ng-hide="tradeMathSaved" ng-click="saveGoals()">Save Trade Goals</button>
						</div>

					</div>

					<div ng-show="view == 'building' || view == 'startBuilding'">
						<div>Rules:
							<ol>
								<li>When an improvement gets started its full cost in materials must be paid (e.g. materials get moved on site)</li>
								<li>Each improvement has a minimum number of turns and a maximum amout of labor that can be applied each turn.</li>
								<li>Up to Max Labor is deducted from the remaining labor each turn if that much labor is available.</li>
								<li>Once the remaining labor is zero and turns left is zero then the improvement is completed at the end of that turn.</li>
							</ol>
						</div>

						<div ng-repeat="s in landValues" ng-show="showSettlement(s)">
							<h3>{{s.Settlement.name}}</h3>
							<table>
								<tr><td>Population</td><td style="text-align:right;">{{s.SettlementValues.population | number:0}}</td></tr>
								<tr><td>Labor Available</td><td style="text-align:right;">{{s.SettlementValues.labor | number:0}}</td></tr>
								<tr><td>Labor Making Food</td><td style="text-align:right">{{s.SettlementValues.labor_food | number:0}}</td></tr>
								<tr><td>Labor Used</td><td style="text-align:right;">{{s.SettlementValues.labor_needed | number:0}}</td></tr>
								<tr><td>Labor For Building</td><td style="text-align:right;">{{s.SettlementValues.labor_avail | number:0}}</td></tr>
							</table>

							<div ng-show="s.SettlementValues.labor_shortage"><b>Labor shortage, consider turning off some improvements, Happiness will suffer</b></div>


							<button ng-click="startBuilding(s)">Start Improvement Project for {{s.Settlement.name}}</button>

							<div>
								<table>
									<tr>
										<th colspan="6"></th>
										<th>Assets</th>
										<th>Stone</th>
										<th>Lumber</th>
										<th>Goods</th>
										<th>Food</th>
									</tr>

									<tr>
										<th style="text-align:right;" colspan="6">Available</th>
										<th style="text-align:right;">{{s.SettlementValues.gold | number:0}}</th>
										<th style="text-align:right;">{{s.SettlementValues.stone | number:0}}</th>
										<th style="text-align:right;">{{s.SettlementValues.lumber | number:0}}</th>
										<th style="text-align:right;">{{s.SettlementValues.goods | number:0}}</th>
										<th style="text-align:right;">{{s.SettlementValues.food | number:0}}</th>
									</tr>

									<tr>
										<th style="text-align:right;" colspan="6">Goal</th>
										<th style="text-align:right;"><input type="text" ng-model="s.SettlementValues.gold_goal" size="4" ng-change="tradeMathChanged()" ng-disabled="turns[turnpos].Turn.status == 3"></th>
										<th style="text-align:right;"><input type="text" ng-model="s.SettlementValues.stone_goal" size="4" ng-change="tradeMathChanged()" ng-disabled="turns[turnpos].Turn.status == 3"></th>
										<th style="text-align:right;"><input type="text" ng-model="s.SettlementValues.lumber_goal" size="4" ng-change="tradeMathChanged()" ng-disabled="turns[turnpos].Turn.status == 3"></th>
										<th style="text-align:right;"><input type="text" ng-model="s.SettlementValues.goods_goal" size="4" ng-change="tradeMathChanged()" ng-disabled="turns[turnpos].Turn.status == 3"></th>
										<th style="text-align:right;"><input type="text" ng-model="s.SettlementValues.food_goal" size="4" ng-change="tradeMathChanged()" ng-disabled="turns[turnpos].Turn.status == 3"></th>
										<td><button ng-hide="tradeMathSaved" ng-click="saveGoals()">Save Trade Goals</button></td>
									</tr>

<!--
									<tr>
										<th style="text-align:right;" colspan="6">Total Needed</th>
										<th style="text-align:right;">{{s.SettlementValues.gold_needed | number:0}}</th>
										<th style="text-align:right;">{{s.SettlementValues.stone_needed | number:0}}</th>
										<th style="text-align:right;">{{s.SettlementValues.lumber_needed | number:0}}</th>
										<th style="text-align:right;">{{s.SettlementValues.goods_needed | number:0}}</th>
										<th style="text-align:right;">{{s.SettlementValues.food_needed | number:0}}</th>
									</tr>
-->
									<tr>
										<th>Improvement</th>
										<th>Turns Left</th>
										<th>Max Labor Per Turn</th>
										<th>Labor Left</th>
										<th>In progress?</th>
										<th>Priority</th>
										<th>Assets</th>
										<th>Stone</th>
										<th>Lumber</th>
										<th>Goods</th>
										<th>Food</th>
									</tr>
									

									<tr ng-repeat="sb in s.Settlement.SettlementBuilding | orderBy:'priority'" ng-show="sb.completed == 0">
										<td>{{sb.Improvement.name}}</td>
										<td style="text-align:right">{{sb.turns_left}}</td>
										<td style="text-align:right">{{sb.max_labor}}</td>
										<td style="text-align:right">{{sb.labor_left}}</td>
										<td>
											<span ng-hide="sb.paidfor == 0">Yes</span>
											<span ng-show="sb.paidfor == 0">
												<button ng-show="sb.manualbuild == 0" ng-click="setManualStatus(sb,1)">Auto</button>
												<button ng-show="sb.manualbuild == 1" ng-click="setManualStatus(sb,0)">Manual</button>
											</span>
										</td>
										<td>{{sb.priority}}
											<button ng-click="raisePriority(sb,s)" ng-hide="sb.priority == 1">Up</button>
											<button ng-click="lowerPriority(sb,s)">Down</button>
										</td>
										<td ng-show="sb.paidfor == 0" style="text-align:right;">{{sb.Improvement.gold_needed}}</td>
										<td ng-show="sb.paidfor == 0" style="text-align:right;">{{sb.Improvement.stone_needed}}</td>
										<td ng-show="sb.paidfor == 0" style="text-align:right;">{{sb.Improvement.wood_needed}}</td>
										<td ng-show="sb.paidfor == 0" style="text-align:right;">{{sb.Improvement.goods_needed}}</td>
										<td></td>
										<td ng-show="sb.paidfor == 0">
											<button ng-show="showManualBuildButton(sb,s)" ng-click="manuallyBuild(sb,s)">Start Project Now</button>
										</td>
										<td ng-show="sb.paidfor == 0">
											<button ng-click="removeImp(sb)">Cancel Project</button>
										</td>

									</tr>

									<tr ng-repeat="sb in s.Settlement.SettlementBuilding | orderBy:'priority'" ng-show="sb.completed >= turnid">
										<td>{{sb.Improvement.name}}</td>
										<td></td>
										<td></td>
										<td></td>
										<td>Done</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
								</table>
							</div>
						</div>
					</div>

					<div ng-show="view == 'improvements' || view == 'startBuilding'">
						<h3>Improvements Documentation</h3>
						Note: These are the current numbers but they are still being balanced and completed -- most of the intial build costs have not been done yet.  Some of the maintenance numbers may be way off as well.  Please continue to be patient with this system.
						<br/>
						Search Improvements: <input ng-model="impsearch"/>
						<div ng-repeat="imp in improvements | filter:impsearch">
							<h3>{{imp.Improvement.name}}</h3>
							<div ng-show="view == 'startBuilding'">
								<div ng-show="imp.Improvement.turns_needed > 0">
									<button ng-click="addToQueue(imp)">Add To Work Queue</button>
								</div>
								<div ng-hide="imp.Improvement.turns_needed > 0">
									Special -- Cannot be built
								</div>
							</div>
							
							<?php if(AuthComponent::user('role_landadmin')) { ?>
								<button ng-click="editImprovement(imp)">Edit {{imp.Improvement.name}}</button>

								<div ng-show="imp == ei">
									Sort: <input type="text" ng-model="ei.Improvement.sort"/><br>
									Name: <input type="text" ng-model="ei.Improvement.name"/><br>
									<textarea ng-model="ei.Improvement.description" rows="3" cols="80"></textarea><br/>

									<table>
										<tr><th>Cost to Build</th>
											<td>
												Turns: <input type="text" size="3" ng-model="ei.Improvement.turns_needed"/>
												Stone: <input type="text" size="3" ng-model="ei.Improvement.stone_needed"/>
												Wood: <input type="text" size="3" ng-model="ei.Improvement.wood_needed"/>
												Assets: <input type="text" size="3" ng-model="ei.Improvement.gold_needed"/>
												Labor: <input type="text" size="3" ng-model="ei.Improvement.labor_needed"/>
												Goods: <input type="text" size="3" ng-model="ei.Improvement.goods_needed"/>
											</td></tr>
										<tr><th>Maintenance</th>
											<td>
												Assets: <input type="text" size="3" ng-model="ei.Improvement.maint_gold"/>
												Goods: <input type="text" size="3" ng-model="ei.Improvement.maint_goods"/>
												Stone: <input type="text" size="3" ng-model="ei.Improvement.maint_stone"/>
												Lumber: <input type="text" size="3" ng-model="ei.Improvement.maint_lumber"/>
												Labor: <input type="text" size="3" ng-model="ei.Improvement.maint_labor"/>
											</td></tr>
										<tr><th>Benefits</th>
											<td>
												Population:
												<input type="text" size="3" ng-model="ei.Improvement.population"/>
												Public Order:
												<input type="text" size="3" ng-model="ei.Improvement.public_order"/>
												Health:
												<input type="text" size="3" ng-model="ei.Improvement.health"/>
												Happiness:
												<input type="text" size="3" ng-model="ei.Improvement.happiness"/>
												<br/>
												Food:
												<input type="text" size="3" ng-model="ei.Improvement.food"/>
												Food Storage:
												<input type="text" size="3" ng-model="ei.Improvement.food_storage"/>
												<br/>
												Assets:
												<input type="text" size="3" ng-model="ei.Improvement.gold"/>
												Goods:
												<input type="text" size="3" ng-model="ei.Improvement.goods"/>
												Stone:
												<input type="text" size="3" ng-model="ei.Improvement.stone"/>
												Lumber:
												<input type="text" size="3" ng-model="ei.Improvement.lumber"/>
												<br/>
												Military:
												<input type="text" size="3" ng-model="ei.Improvement.military"/>
												Wildlands Safety:
												<input type="text" size="3" ng-model="ei.Improvement.wildlands"/>
												Taxes: 
												<input type="text" size="3" ng-model="ei.Improvement.taxes"/>
											</td>
										</tr>
									</table>
									<button ng-click="cancelEditImprovement()">Cancel</button>
									<button ng-click="saveImprovement()">Save</button>

								</div>
							<?php } ?>

							<div ng-hide="imp == ei">
								<div>{{imp.Improvement.description}}</div>
								<table>
									<tr><th>Cost to Build</th>
										<td>
											Turns: {{imp.Improvement.turns_needed}}
											Stone: {{imp.Improvement.stone_needed}} 
											Lumber: {{imp.Improvement.wood_needed}} 
											Assets: {{imp.Improvement.gold_needed}} 
											Goods: {{imp.Improvement.goods_needed}} 
											Labor: {{imp.Improvement.labor_needed}} 

											<b>Total Cost: {{totalCost(imp)}}</b>
										</td></tr>
									<tr><th>Maintenance</th>
										<td>
											Assets: {{imp.Improvement.maint_gold}} 
											Goods: {{imp.Improvement.maint_goods}} 
											Stone: {{imp.Improvement.maint_stone}} 
											Lumber: {{imp.Improvement.maint_lumber}} 
											Labor: {{imp.Improvement.maint_labor}} 

											<b>Total Maintenance: {{totalMaintenance(imp)}}</b>
										</td></tr>
									<tr><th>Benefits</th>
										<td>
											<span ng-show="imp.Improvement.population > 0">Population +{{imp.Improvement.population}}</span>
											<span ng-show="imp.Improvement.public_order > 0">Public Order +{{imp.Improvement.public_order}}</span>
											<span ng-show="imp.Improvement.health > 0">Health +{{imp.Improvement.health}}</span>
											<span ng-show="imp.Improvement.happiness > 0">Happiness +{{imp.Improvement.happiness}}</span>
											<span ng-show="imp.Improvement.food > 0">Food +{{imp.Improvement.food}}</span>
											<span ng-show="imp.Improvement.food_storage > 0">Food Storage +{{imp.Improvement.food_storage}}</span>
											<span ng-show="imp.Improvement.gold > 0">Assets +{{imp.Improvement.gold}}</span>
											<span ng-show="imp.Improvement.goods > 0">Goods +{{imp.Improvement.goods}}</span>
											<span ng-show="imp.Improvement.stone > 0">Stone +{{imp.Improvement.stone}}</span>
											<span ng-show="imp.Improvement.lumber > 0">Lumber +{{imp.Improvement.lumber}}</span>
											<span ng-show="imp.Improvement.military > 0">Military +{{imp.Improvement.military}}</span>
											<span ng-show="imp.Improvement.wildlands > 0">Wildlands Safety +{{imp.Improvement.wildlands}}</span>
											<span ng-show="imp.Improvement.taxes > 0">Taxes +{{imp.Improvement.taxes}}</span>
										</td>
									</tr>
									<tr><th>Detriments</th>
										<td>
											<span ng-show="imp.Improvement.population < 0">Population {{imp.Improvement.population}}</span>
											<span ng-show="imp.Improvement.public_order < 0">Public Order {{imp.Improvement.public_order}}</span>
											<span ng-show="imp.Improvement.health < 0">Health {{imp.Improvement.health}}</span>
											<span ng-show="imp.Improvement.happiness < 0">Happiness {{imp.Improvement.happiness}}</span>
											<span ng-show="imp.Improvement.food < 0">Food {{imp.Improvement.food}}</span>
											<span ng-show="imp.Improvement.food_storage < 0">Food Storage {{imp.Improvement.food_storage}}</span>
											<span ng-show="imp.Improvement.gold < 0">Assets {{imp.Improvement.gold}}</span>
											<span ng-show="imp.Improvement.goods < 0">Goods {{imp.Improvement.goods}}</span>
											<span ng-show="imp.Improvement.stone < 0">Stone {{imp.Improvement.stone}}</span>
											<span ng-show="imp.Improvement.lumber < 0">Lumber {{imp.Improvement.lumber}}</span>
											<span ng-show="imp.Improvement.military < 0">Military {{imp.Improvement.military}}</span>
											<span ng-show="imp.Improvement.wildlands < 0">Wildlands Safety {{imp.Improvement.wildlands}}</span>
											<span ng-show="imp.Improvement.taxes < 0">Taxes {{imp.Improvement.taxes}}</span>
										</td>
									</tr>
								</table>
								<br/>
							</div>
						</div>

					</div>

				</td>
			</tr>
		</table>

	</div> <!-- end of the ng-controller div. -->

</div>