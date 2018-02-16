<style>
	.tradetable table { border-collapse: collapse; border: 1px solid black; }
	.tradetable th { border: 1px solid black; padding: 3px; vertical-align: top;}
	.tradetable td { border: 1px solid black; padding: 3px; vertical-align: top; text-align: right;}

	.noborder table { border:none; }
	.noborder th { border:none; }
	.noborder td { border:none; }
</style>

<div id="landSystem" ng-app="landSystemApp">

	<script type="text/javascript">

		var landSystemApp = angular.module('landSystemApp',[]);

		landSystemApp.controller('LandSystemController',['$scope','$http', '$filter', '$location', function($scope,$http,$filter,$location) {
      
			$scope.initialize = function() {
				$scope.allowBuilding = true;
				$scope.allowTrades = true;
				$scope.allowImprovements = true;
				$scope.readyToRun = false;
				$scope.adjustNumbers = false;
				$scope.loadingMessage = "Loading";
				$scope.manualValuesStatus = "";

		        $scope.lands = [];
				$scope.trades = {};
				$scope.taxes = {};
				$scope.messages = [];

				$scope.profits = {};

				$scope.taxesTotal = 0;
				$scope.loadTurn();
				$scope.saved = false;

				$scope.debugtrades = [];
			}
			
			$scope.loadTurn = function() {
				$http({ method: 'GET', url: '/personal_action/loadturn/'}).success(function(data) {
					$scope.turn = data;
					$scope.loadLands();		
				});
			}

			$scope.loadLands = function() {
				$http({ method: 'GET', url: '/land_system/loadLandsAdmin'}).success(function(data) {
					$scope.actions = data.TurnAction;
					$scope.settlementvalues = data.SettlementValues;
					$scope.convertPrioritiesToNumbers();

					$scope.readyToRun = true;
				});
			}

			$scope.runTheNumbers = function() {

				$scope.loadingMessage = "Already Run";
				$scope.readyToRun = false;

				$scope.turnTheCrank();
				$scope.populationMovements();
				$scope.computeTrades();

				if($scope.allowBuilding) {
					$scope.buildThings();
				}
			}

			$scope.saveGoals = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/savetradegoals/',
					data: $scope.landValues
				}).success(function(data) {
					// $scope.debug = data;
					$scope.tradeGoalsSaved = true;
				});
			}

			$scope.saveFinalValues = function() {
				$scope.saved = 1;

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/saveAllSettlementValues/',
					data: $scope.newSettlementValues
				}).success(function(data) {
					// $scope.debug = data;
					$scope.saved++;
				});

				$http({
					method: 'POST',
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/saveAllTrades/',
					data: { "trades": $scope.trades, "taxes": $scope.taxes }
				}).success(function(data) {
					$scope.saved++;
				});

				$http({
					method: 'POST',
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/saveAllMessages/',
					data: { "messages": $scope.messages }
				}).success(function(data) {
					$scope.saved++;
				});

			}

			$scope.setMessage = function(land_id, settlement_id, order, message) {
				$scope.messages.push({ turn_id: $scope.turn.t.id, land_id: land_id, settlement_id: settlement_id, order: order, message: message });
				console.log(message);
			}

			$scope.registerTradeImpl = function(land1, land2, land1_give, land2_give, land1_amt, land2_amt) {
				var tradekey = land1.name+":"+land2.name;

				if(!$scope.trades.hasOwnProperty(tradekey)) {
					$scope.trades[tradekey] = {
						turnid: $scope.turn.t.id,
						from: land1.name,
						fromid: land1.id, 
						to: land2.name, 
						toid: land2.id,
						traded: { gold:0, food:0, stone:0, lumber:0, goods:0 }
					};
				} 

				$scope.trades[tradekey].traded[land1_give] = $scope.trades[tradekey].traded[land1_give] - land1_amt;
				$scope.trades[tradekey].traded[land2_give] = $scope.trades[tradekey].traded[land2_give] + land2_amt;
			}

			$scope.tradebalance = function(trade) {
				return trade.gold + 
					(trade.lumber * $scope.lumber_price)+ 
					(trade.food * $scope.food_price)+ 
					(trade.stone * $scope.stone_price)+ 
					(trade.goods * $scope.goods_price);
			}

			$scope.registerTrade = function(land1, land2, land1_give, land2_give, land1_amt, land2_amt) {				
				$scope.registerTradeImpl(land1, land2, land1_give, land2_give, land1_amt, land2_amt);
				$scope.registerTradeImpl(land2, land1, land2_give, land1_give, land2_amt, land1_amt);
			}

			$scope.registerTax = function(land, amount) {
				if( !$scope.taxes.hasOwnProperty(land) ) {
					$scope.taxes[land] = { amount: 0, name: land };
				}
				$scope.taxes[land].amount += parseInt(amount);
				$scope.taxesTotal += parseInt(amount);
			}

			$scope.registerIncomeExpense = function(land, amount) {
				if( !$scope.profits.hasOwnProperty(land) ) {
					$scope.profits[land] = { amount: 0, losses: 0, total: 0, name: land };
				}

				$scope.profits[land].total += parseInt(amount);
				
				if(amount < 0) {
					$scope.profits[land].losses -= parseInt(amount);
				} else {
					$scope.profits[land].amount += parseInt(amount);
				}
			}

			$scope.convertPrioritiesToNumbers = function() {
				for(i = 0; i < $scope.settlementvalues.length; i++) {
					for(j = 0; j < $scope.settlementvalues[i].Settlement.SettlementBuilding.length; j++) {
						$scope.settlementvalues[i].Settlement.SettlementBuilding[j].priority = parseInt($scope.settlementvalues[i].Settlement.SettlementBuilding[j].priority) + 0;
					}
				}
			}

			$scope.influenceBonus = function(swing, pop) {
				retval = parseInt(swing);
				s = parseInt(swing);
				p = parseInt(pop);

				// 10% bonus for every 10 swing points
				retval += Math.floor(s / 10);

				// 25% bonus for smaller settlements, min of +1 if over threshold
				if(s > p / 100) {
					retval += Math.ceil(s / 4);
				}

				return retval;
			}

			$scope.hasEnoughResources = function(gold, stone, lumber, goods, imp) {
				if(parseInt(imp.maint_gold) > 0 && gold < parseInt(imp.maint_gold)) {
					return false;
				}

				if(parseInt(imp.maint_stone) > 0 && stone < parseInt(imp.maint_stone)) {
					return false;
				}

				if(parseInt(imp.maint_lumber) > 0 && stone < parseInt(imp.maint_lumber)) {
					return false;
				}

				if(parseInt(imp.maint_goods) > 0 && stone < parseInt(imp.maint_goods)) {
					return false;
				}

				return true;
			}

			$scope.turnTheCrank = function() {
				$scope.newSettlementValues = [];

				$scope.total_income = 0;
				$scope.total_gold_income = 0;
				$scope.total_stone_income = 0;
				$scope.total_lumber_income = 0;
				$scope.total_goods_income = 0;
				$scope.total_food_income = 0;

				$scope.total_expense = 0;
				$scope.total_gold_expense = 0;
				$scope.total_stone_expense = 0;
				$scope.total_lumber_expense = 0;
				$scope.total_goods_expense = 0;
				$scope.total_food_expense = 0;

				$scope.total_population = 0;
				$scope.total_housing = 0;

				for(var svi = 0; svi < $scope.settlementvalues.length; svi++) {
					var svbase = $scope.settlementvalues[svi];

					var sv = svbase.SettlementValues;
					sv.Settlement = svbase.Settlement;
					land_id = sv.land_id;
					settlement_id = sv.settlement_id;

					var nsv = {};

					nsv.Settlement = svbase.Settlement;
					nsv.land_id = sv.land_id;
					nsv.settlement_id = sv.settlement_id;
					nsv.turn_id = $scope.turn.t.id;

					sv.total_repair = Math.max(5, parseInt(sv.population) / 100);

					// Labor
					sv.labor_pool = parseInt(sv.population) + Math.round(sv.military / 2);
					sv.labor = Math.max(0,Math.round(sv.labor_pool * sv.public_order * sv.health / 100 / 100));

					$scope.setMessage(land_id, settlement_id, 1, "Starting labor pool had "+sv.labor+" workers.");

					sv.labor_misc = 0;
					sv.labor_jobs = 0;
					sv.labor_used = 0;
					sv.labor_shortage = 0;
					sv.labor_avail = 0;

					// Food
					sv.food_earned = 0;
					sv.labor_food = 0;
					sv.food_spent = Math.round((parseInt(sv.population) + parseInt(sv.military) + parseInt(sv.criminal)) / 15);

					$scope.total_population += parseInt(sv.population);

					$scope.total_expense += parseInt(sv.food_spent) * 10;
					$scope.total_food_expense += parseInt(sv.food_spent) * 10;

					// Check Food in Storage -- if there is not enough then Labor gets used to produce Food first

					if(sv.food_spent > sv.food) {
						sv.labor_food = 5 * (sv.food_spent - sv.food);
						if(sv.labor_food < sv.labor) {
							sv.food_earned = (sv.food_spent - sv.food);
						} else {
							sv.labor_food = sv.labor;
							sv.food_earned = Math.round(sv.labor_food / 5);
						}

						$scope.setMessage(land_id, settlement_id, 2, "Food supply was short, citizens had to forage, "+sv.labor_food+" labor expended on foraging.");
					}

					sv.labor_jobs += sv.labor_food;

					sv.gold_spent = 0;
					sv.gold_earned = 0;
					sv.stone_spent = 0;
					sv.stone_earned = 0;
					sv.lumber_spent = 0;
					sv.lumber_earned = 0;
					sv.goods_spent = 0;
					sv.goods_earned = 0;

					sv.public_order_baseline = -1 * Math.floor(Math.pow((sv.population / 750),2));
					sv.public_order_population_drop = sv.public_order_baseline;
					sv.health_baseline = 0;
					sv.happiness_baseline = 0;
					sv.criminal_baseline = 0;
					sv.military_baseline = 0;
					sv.military_effect_baseline = 0;
					// sv.military_effect_baseline = -1 * Math.floor(Math.pow((sv.military / 50),2));
					sv.wildlands_baseline = 0;
					sv.population_baseline = 0;

					sv.public_order_swing = 0;
					sv.health_swing = 0;
					sv.happiness_swing = 0;
					sv.criminal_swing = 0;
					sv.military_swing = 0;
					sv.military_effect_swing = 0;
					sv.wildlands_swing = 0;

					sv.food_storage = 0;

					var repair_needed = 0;

					// Improvements
					for(i = 0; i < svbase.Settlement.SettlementImprovement.length; i++) {

						imp = svbase.Settlement.SettlementImprovement[i].Improvement;
						si = svbase.Settlement.SettlementImprovement[i];

						// new lines
						var gold = sv.gold - sv.gold_spent + sv.gold_earned;
						var stone = sv.stone - sv.stone_spent + sv.stone_earned;
						var lumber = sv.lumber - sv.lumber_spent + sv.lumber_earned;
						var goods = sv.goods - sv.goods_spent + sv.goods_earned;

						repair_needed += parseInt(si.actions_to_repair);

						if($scope.allowImprovements == true) {
							if( si.active == 1 && 
								si.actions_to_repair == 0 && 
								si.completed <= nsv.turn_id &&
								( 
									si.business_id != 0 || 
									$scope.hasEnoughResources(gold, stone, lumber, goods, imp)
								)
							){

								// non-business, deduct maintenance
								if(si.business_id == 0) {
									sv.gold_spent += parseInt(imp.maint_gold);
									sv.goods_spent += parseInt(imp.maint_goods);
									sv.stone_spent += parseInt(imp.maint_stone);
									sv.lumber_spent += parseInt(imp.maint_lumber);

									sv.labor_jobs += parseInt(imp.maint_labor);
								}

								sv.public_order_baseline += parseInt(imp.public_order);
								sv.health_baseline += parseInt(imp.health);
								sv.happiness_baseline += parseInt(imp.happiness);
								sv.military_baseline += parseInt(imp.military);
								sv.criminal_baseline += parseInt(imp.criminal);
								sv.wildlands_baseline += parseInt(imp.wildlands);
								sv.population_baseline += parseInt(imp.population);
								$scope.total_housing += parseInt(imp.population);

								sv.gold_earned += parseInt(imp.gold);
								sv.food_earned += parseInt(imp.food);
								sv.stone_earned += parseInt(imp.stone);
								sv.lumber_earned += parseInt(imp.lumber);
								sv.goods_earned += parseInt(imp.goods);

								sv.food_storage += parseInt(imp.food_storage);

								if($scope.allowTrades == true) {
									$scope.registerTax(svbase.Settlement.Land.name, parseInt(imp.taxes));
								}
							} else {
								if(si.completed <= nsv.turn_id) {
									if(si.actions_to_repair > 0) {
										$scope.setMessage(land_id, settlement_id, 3, si.Improvement.name +" damaged, " + si.actions_to_repair + " rebuilding actions required.");
									} else if(si.active == 1) {
										$scope.setMessage(land_id, settlement_id, 3, si.Improvement.name +" granted no benefits, not enough resources to pay maintenance.");
									}
								}
							}
						} 
					}

					$scope.income = sv.gold_earned + (sv.food_earned + sv.stone_earned + sv.lumber_earned + sv.goods_earned) * 10;
					$scope.expense = sv.gold_spent + (sv.food_spent + sv.stone_spent + sv.lumber_spent + sv.goods_spent) * 10;

					$scope.registerIncomeExpense(svbase.Settlement.Land.name, $scope.income);
					$scope.registerIncomeExpense(svbase.Settlement.Land.name, -1 * $scope.expense);

					// console.log(svbase.Settlement.Land.name + ":" + $scope.income + ":" + $scope.expense);

					$scope.total_income += $scope.income;
					$scope.total_gold_income += sv.gold_earned;
					$scope.total_stone_income += sv.stone_earned * 10;
					$scope.total_lumber_income += sv.lumber_earned * 10;
					$scope.total_goods_income += sv.goods_earned * 10;
					$scope.total_food_income += sv.food_earned * 10;

					$scope.total_expense += $scope.expense;
					$scope.total_gold_expense += sv.gold_spent;
					$scope.total_stone_expense += sv.stone_spent * 10;
					$scope.total_lumber_expense += sv.lumber_spent * 10;
					$scope.total_goods_expense += sv.goods_spent * 10;

					sv.public_health_baseline = Math.min(90,sv.public_health_baseline);
					sv.happiness_baseline = Math.min(90,sv.happiness_baseline);

					// Actions
					for(i = 0; i < $scope.actions.length; i++) {
						turnact = $scope.actions[i].TurnAction;
						act = $scope.actions[i].Action;

						if(turnact.settlement_id == sv.settlement_id) {	
							sv.public_order_swing += parseInt(act.public_order) + parseInt(turnact.public_order);
							sv.health_swing += parseInt(act.health) + parseInt(turnact.health);
							sv.happiness_swing += parseInt(act.happiness) + parseInt(turnact.happiness);
							sv.military_effect_swing += parseInt(act.military_effect) + parseInt(turnact.military_effect);
							sv.criminal_swing += parseInt(act.criminal) + parseInt(turnact.criminal);
							sv.wildlands_swing += parseInt(act.wildlands) + parseInt(turnact.wildlands);

							sv.labor += parseInt(act.labor) + parseInt(turnact.labor);
							sv.total_repair += parseInt(act.repair);
						}
					}

					sv.public_order_swing = $scope.influenceBonus(sv.public_order_swing, sv.population);
					sv.health_swing = $scope.influenceBonus(sv.health_swing, sv.population);
					sv.happiness_swing = $scope.influenceBonus(sv.happiness_swing, sv.population);

					// Implement Repairs
					var repair_left = sv.total_repair;

					while(repair_left > 0 && repair_needed > 0) {

						for(i = 0; i < nsv.Settlement.SettlementImprovement.length; i++) {

							if( !nsv.Settlement.SettlementImprovement[i].hasOwnProperty("actions_repairing") ) {
								nsv.Settlement.SettlementImprovement[i].actions_repairing=0;
							}

							if(repair_left > 0) {
								imp = nsv.Settlement.SettlementImprovement[i].Improvement;
								nsi = nsv.Settlement.SettlementImprovement[i];

								if(nsi.actions_to_repair > 0) {
									nsv.Settlement.SettlementImprovement[i].actions_repairing++;
									nsv.Settlement.SettlementImprovement[i].actions_to_repair--;
									repair_needed--;
									repair_left--;
								}
							}
						}
					}

					// Left over Labor stays unemployed
					sv.labor_used = sv.labor_jobs + sv.labor_misc;
					sv.labor_unused = sv.labor - sv.labor_used - sv.labor_food;

					if(sv.labor_unused < 0) {
						// Labor shortages cause unhappiness
						sv.labor_shortage = sv.labor_unused * -1;
						sv.labor_used += sv.labor_unused;
						sv.labor_unused = 0;

						if(sv.labor_shortage > 100) {
							sv.happiness_swing -= 10;
							$scope.setMessage(land_id, settlement_id, 4, "Labor shortage, workers have to work extra shifts, happiness decreased, "+sv.labor_shortage+" more labor needed.");
						} else {
							$scope.setMessage(land_id, settlement_id, 4, "Labor shortage, some workers have to work extra shifts, "+sv.labor_shortage+" more labor needed.");
						}
					}

					nsv.labor_avail = sv.labor_unused;

					nsv.food_goal = sv.food_goal;
					nsv.gold_goal = sv.gold_goal;
					nsv.stone_goal = sv.stone_goal;
					nsv.lumber_goal = sv.lumber_goal;
					nsv.goods_goal = sv.goods_goal;

					// Calculate left over food and storage
					nsv.food = Math.min(sv.food_storage, (sv.food - sv.food_spent + sv.food_earned));

					nsv.gold = sv.gold - sv.gold_spent + sv.gold_earned;
					nsv.stone = sv.stone - sv.stone_spent + sv.stone_earned;
					nsv.lumber = sv.lumber - sv.lumber_spent + sv.lumber_earned;
					nsv.goods = sv.goods - sv.goods_spent + sv.goods_earned;

					nsv.health = Math.min(100,parseInt(sv.health) + Math.round((parseInt(sv.health_baseline) + parseInt(sv.health_swing) - parseInt(sv.health)) / 3));
					nsv.happiness = Math.min(100,parseInt(sv.happiness) + Math.round((parseInt(sv.happiness_baseline) + parseInt(sv.happiness_swing) - parseInt(sv.happiness)) / 3));
					nsv.wildlands = Math.min(100,parseInt(sv.wildlands) + Math.round((parseInt(sv.wildlands_baseline) + parseInt(sv.wildlands_swing) - parseInt(sv.wildlands)) / 3));

					// Troop counts change very fast!  But that is bad for military training
					nsv.military = Math.floor(parseInt(sv.military) + Math.round((parseInt(sv.military_baseline) - parseInt(sv.military)) / 2));
					
					nsv.new_mil = nsv.military - parseInt(sv.military);
					if(nsv.new_mil > 0) {
						$scope.setMessage(land_id, settlement_id, 5, nsv.new_mil+" new troops added, training has begun.");
					}

					// Military Effect -- Training takes time but once in place only drops with addition of troops
					nsv.military_effect = 
						Math.round(
						Math.min(100,(5 + parseInt(sv.military_effect_swing) + (
							(parseInt(sv.military_effect) * parseInt(sv.military)) /
							(parseInt(nsv.military))
						))));
					if(nsv.military_effect > 92 && nsv.military_effect < 100) {
						$scope.setMessage(land_id, settlement_id, 5, "Troops are nearly done training.");
					}
					
					nsv.criminal = Math.max(0,(parseInt(sv.criminal_baseline) + parseInt(sv.criminal_swing)));

					// Public Order over 80 not allowed by Improvements alone
					sv.public_order_baseline = Math.min(80,sv.public_order_baseline);

					// Public Order is affected by Military too (Every 15 perfectly trained troops adds 1 to the public order)
					nsv.military_public_order_swing = parseInt(nsv.military) * parseInt(nsv.military_effect) / 1500;
					sv.public_order_baseline += nsv.military_public_order_swing;

					// Public Order over 85 not allowed by Improvements + Military alone
					sv.public_order_baseline = Math.min(85,sv.public_order_baseline);

					nsv.public_order_old = parseInt(sv.public_order);
					nsv.public_order_population_drop = parseInt(sv.public_order_population_drop);
					nsv.public_order_baseline = parseInt(sv.public_order_baseline);
					nsv.public_order_swing = parseInt(sv.public_order_swing);

					nsv.public_order = Math.min(100,parseInt(sv.public_order) + Math.round((parseInt(sv.public_order_baseline) + parseInt(sv.public_order_swing) - parseInt(sv.public_order)) / 3));

					nsv.overcrowding = 0;
					// Population 
					nsv.population = parseInt(sv.population); // + Math.round((parseInt(sv.population_baseline) - parseInt(sv.population)) / 3);
					nsv.population_baseline = sv.population_baseline;

					nsv.population_without_boost = nsv.population;

					// Population (Life and Death)
					// Random birth rate between 0 and 20 births per 1000 
					nsv.new_births = Math.floor(Math.random() * 20 * parseInt(sv.population) / 1000 / 10);
					nsv.population += nsv.new_births;
					$scope.total_population += nsv.new_births;

					// Random death rate between 5 and (100 - health) per 1000 population
					nsv.new_deaths = Math.floor(Math.random() * Math.max(5,(100 - parseInt(nsv.health))) * (parseInt(nsv.population) / 1000) / 10);
					nsv.population -= nsv.new_deaths;
					$scope.total_population -= nsv.new_deaths;
					nsv.population_baseline = sv.population_baseline;

					nsv.total_repair = sv.total_repair;

					$scope.settlementvalues[svi] = sv;
					$scope.newSettlementValues[svi] = nsv;
				}
			}

			//***************************************************************************************************************

			$scope.populationMovements = function() {
				$scope.migration = Math.round((parseInt($scope.total_housing) - parseInt($scope.total_population)) / 9);
				$scope.total_overcrowding = Math.max(0,(parseInt($scope.total_population) - parseInt($scope.total_housing)));

console.log("Total Population: "+$scope.total_population);
console.log("Total Housing: "+$scope.total_housing);
console.log("Total Migration: "+$scope.migration);
console.log("Total Overcrowding: "+$scope.total_overcrowding);

				$scope.total_relocating = $scope.migration;

				// First figure out who, if anyone, is moving
				for(var nsvi = 0; nsvi < $scope.newSettlementValues.length; nsvi++) {
					var nsv = $scope.newSettlementValues[nsvi];

					nsv.popularity = 0;
					nsv.avail_housing = Math.max(0,nsv.population_baseline - nsv.population);

					if(nsv.public_order > 80) {
						nsv.popularity += Math.floor(nsv.population / 100 * (nsv.public_order - 80));
					} else if(nsv.public_order < 60) {
						nsv.popularity -= Math.floor(nsv.population / 100 * (60 - nsv.public_order));
					}

					if(nsv.health > 75) {
						nsv.popularity += Math.floor(nsv.population / 100 * (nsv.health - 75));
					} else if(nsv.health < 50) {
						nsv.popularity -= Math.floor(nsv.population / 100 * (50 - nsv.health));
					}

					if(nsv.happiness > 70) {
						nsv.popularity += Math.floor(nsv.population / 100 * (nsv.happiness - 70));
					} else if(nsv.happiness < 40) {
						nsv.popularity -= Math.floor(nsv.population / 100 * (40 - nsv.happiness));
					}

// console.log(nsv.Settlement.name+" popularity "+nsv.popularity);

					if(nsv.popularity < 0) {
						// even if people are stupidly unhappy it takes a while for them to decide to move... or to be able to move
						nsv.relocating = Math.floor(nsv.popularity / 12) * -1;
						nsv.people_demand = 0;
					} else {
						if(nsv.avail_housing > nsv.popularity) {
							nsv.people_demand = nsv.avail_housing;
						} else {
							nsv.people_demand = Math.min(nsv.popularity,nsv.avail_housing);
						}
						nsv.relocating = 0;
					}

					// deduct those who moved out
					if(nsv.relocating > Math.floor(nsv.population / 10)) {
						// a settlement should never have more than one tenth of its population leave in one turn
						nsv.relocating = Math.floor(nsv.population / 10);
					}
					nsv.population -= nsv.relocating;
					// and put them into the general pool
					$scope.total_relocating += nsv.relocating;

					if(nsv.people_demand > 0) { console.log(nsv.Settlement.name+" demand "+nsv.people_demand); }
					if(nsv.relocating > 0) { console.log(nsv.Settlement.name+" relocating "+nsv.relocating); }

					nsv.moved_in = 0;

					$scope.newSettlementValues[nsvi] = nsv;
				}

console.log("Total people moving "+$scope.total_relocating);

				// second pass to redistribute relocating people
				var still_working = true;
				while(still_working && $scope.total_relocating > 0) {
					still_working = false;
					for(var nsvi = 0; nsvi < $scope.newSettlementValues.length; nsvi++) {
						var nsv = $scope.newSettlementValues[nsvi];

						if(nsv.people_demand >= 1 && $scope.total_relocating > 0) {
							nsv.population += 1;
							nsv.moved_in += 1;
							nsv.people_demand -= 1;
							$scope.total_relocating--;
							still_working = true;
						}

						$scope.newSettlementValues[nsvi] = nsv;
					}
				}

				// console.log("Total people moving "+$scope.total_relocating);

				// third pass to redistribute people who could not find a place that demanded them. 
				while($scope.total_relocating > 0) {
					for(var nsvi = 0; nsvi < $scope.newSettlementValues.length && $scope.total_relocating > 0; nsvi++) {
						var nsv = $scope.newSettlementValues[nsvi];

						nsv.population += 1;
						nsv.moved_in += 1;
						$scope.total_relocating--;

						$scope.newSettlementValues[nsvi] = nsv;
					}
				}

				// console.log("Total people moving "+$scope.total_relocating);

				// final pass to adjust scores for Overcrowding
				for(var nsvi = 0; nsvi < $scope.newSettlementValues.length; nsvi++) {
					var nsv = $scope.newSettlementValues[nsvi];

					land_id = nsv.Settlement.Land.id;
					settlement_id = nsv.Settlement.id;

					nsv.overcrowding = Math.floor(Math.max(0,((parseInt(nsv.population) - parseInt(nsv.population_baseline)) / 100)));

					if(nsv.overcrowding > 0) {
						nsv.health -= nsv.overcrowding;
						nsv.happiness -= nsv.overcrowding;

						$scope.setMessage(land_id, settlement_id, 6, "Overcrowding leads to homelessness, health and happiness impacted.");
					}

					if(nsv.moved_in > 0) { 
						console.log(nsv.Settlement.name+" moved in "+nsv.moved_in); 
						$scope.setMessage(land_id, settlement_id, 6, nsv.moved_in + " new people have moved in.");
					}

					$scope.newSettlementValues[nsvi] = nsv;
				}
			}

			//***************************************************************************************************************

			$scope.noTrades = function() {
				// find newSettlementValues for all settlements, copy from old values
				for(var i = 0; i < $scope.newSettlementValues.length; i++) {
					sv = $scope.newSettlementValues[i];

					sv['food_dist'] = sc['food'];
					sv['gold_dist'] = sc['gold'];
					sv['stone_dist'] = sc['stone'];
					sv['wood_dist'] = sc['wood'];
					sv['lumber_dist'] = sc['lumber'];
					sv['goods_dist'] = sc['goods'];

					$scope.newSettlementValues[i] = sv;
				}
			}

			$scope.computeTrades = function() {
				$scope.landtotal = {};
				var taxesCollected = 0;

				// initialize land totals
				for(var i = 0; i < $scope.newSettlementValues.length; i++) {
					land_id = $scope.newSettlementValues[i].Settlement.Land.id;

					if( !(land_id in $scope.landtotal)) {
						var lv = {};
						lv.name = $scope.newSettlementValues[i].Settlement.Land.name;
						lv.id = land_id;
						lv.food = 0;
						lv.food_goal = 0;
						
						lv.gold = 0;
						if(land_id == 1 && taxesCollected == 0) {
							lv.gold = $scope.taxesTotal;	
							$scope.registerIncomeExpense(lv.name,$scope.taxesTotal);
							$scope.total_income += $scope.taxesTotal;
							taxesCollected = 1;
						}
						lv.gold_goal = 0;
						
						lv.stone = 0;
						lv.stone_goal = 0;
						lv.lumber = 0;
						lv.lumber_goal = 0;
						lv.goods = 0;
						lv.goods_goal = 0;

						$scope.landtotal[land_id] = lv;
					}
				}

				$scope.total = {};
				
				$scope.total.food_surplus = 0;
				$scope.total.gold_surplus = 0;
				$scope.total.stone_surplus = 0;
				$scope.total.lumber_surplus = 0;
				$scope.total.goods_surplus = 0;

				$scope.total.food_demand = 0;
				$scope.total.gold_demand = 0;
				$scope.total.stone_demand = 0;
				$scope.total.lumber_demand = 0;
				$scope.total.goods_demand = 0;

				// get one total for each Land to reduce complexity of trades
				for(var i = 0; i < $scope.newSettlementValues.length; i++) {
					land_id = $scope.newSettlementValues[i].Settlement.Land.id;
					var lv = $scope.landtotal[land_id];
					sv = $scope.newSettlementValues[i];

					sv.food_goal = Math.max(1,parseInt(sv.food_goal) + 0);
					lv.food += parseInt(sv.food);
					lv.food_goal += parseInt(sv.food_goal);
					lv.food_demand = Math.max(0,lv.food_goal - lv.food);
					lv.food_surplus = Math.max(0,lv.food - lv.food_goal);
					lv.food_change = 0;
					lv.food_needed = lv.food_demand;
					lv.food_left = lv.food_surplus;
					
					sv.gold_goal = Math.max(1,parseInt(sv.gold_goal) + 0);
					lv.gold += parseInt(sv.gold);
					lv.gold_goal += parseInt(sv.gold_goal);
					lv.gold_demand = Math.max(0,lv.gold_goal - lv.gold);
					lv.gold_surplus = Math.max(0,lv.gold - lv.gold_goal);					
					lv.gold_change = 0;
					lv.gold_needed = lv.gold_demand;
					lv.gold_left = lv.gold_surplus;

					sv.stone_goal = Math.max(1,parseInt(sv.stone_goal) + 0);
					lv.stone += parseInt(sv.stone);
					lv.stone_goal += parseInt(sv.stone_goal);
					lv.stone_demand = Math.max(0,lv.stone_goal - lv.stone);
					lv.stone_surplus = Math.max(0,lv.stone - lv.stone_goal);
					lv.stone_change = 0;
					lv.stone_needed = lv.stone_demand;
					lv.stone_left = lv.stone_surplus;

					sv.lumber_goal = Math.max(1,parseInt(sv.lumber_goal) + 0);
					lv.lumber += parseInt(sv.lumber);
					lv.lumber_goal += parseInt(sv.lumber_goal);
					lv.lumber_demand = Math.max(0,lv.lumber_goal - lv.lumber);
					lv.lumber_surplus = Math.max(0,lv.lumber - lv.lumber_goal);
					lv.lumber_change = 0;
					lv.lumber_needed = lv.lumber_demand;
					lv.lumber_left = lv.lumber_surplus;

					sv.goods_goal = Math.max(1,parseInt(sv.goods_goal) + 0);
					lv.goods += parseInt(sv.goods);
					lv.goods_goal += parseInt(sv.goods_goal);
					lv.goods_demand = Math.max(0,lv.goods_goal - lv.goods);
					lv.goods_surplus = Math.max(0,lv.goods - lv.goods_goal);
					lv.goods_change = 0;
					lv.goods_needed = lv.goods_demand;
					lv.goods_left = lv.goods_surplus;

					lv.food_newvalue = lv.food;
					lv.gold_newvalue = lv.gold;
					lv.stone_newvalue = lv.stone;
					lv.lumber_newvalue = lv.lumber;
					lv.goods_newvalue = lv.goods;
					
					$scope.landtotal[land_id] = lv;
					$scope.newSettlementValues[i] = sv;
				}

				for(var key in $scope.landtotal) {
					var lv = $scope.landtotal[key];

					$scope.total.food_demand += parseInt(lv.food_demand);
					$scope.total.food_surplus += parseInt(lv.food_surplus);
					$scope.total.gold_demand += parseInt(lv.gold_demand);
					$scope.total.gold_surplus += parseInt(lv.gold_surplus);
					$scope.total.stone_demand += parseInt(lv.stone_demand);
					$scope.total.stone_surplus += parseInt(lv.stone_surplus);
					$scope.total.lumber_demand += parseInt(lv.lumber_demand);
					$scope.total.lumber_surplus += parseInt(lv.lumber_surplus);
					$scope.total.goods_demand += parseInt(lv.goods_demand);
					$scope.total.goods_surplus += parseInt(lv.goods_surplus);
				}

				$scope.food_price = $scope.total.food_demand > $scope.total.food_surplus ? 11 : 10;
				$scope.stone_price = $scope.total.stone_demand > $scope.total.stone_surplus ? 11 : 10;
				$scope.lumber_price = $scope.total.lumber_demand > $scope.total.lumber_surplus ? 11 : 10;
				$scope.goods_price = $scope.total.goods_demand > $scope.total.goods_surplus ? 11 : 10;

				var types = ['food','stone','lumber','goods','gold'];

				//loop over each combination of types of goods and then each land looking for 1:1 trade options
				//If the price of the goods it not equal then register a gold cost as well

				if($scope.allowTrades == true) {
					var remaining = true;
					while(remaining) {
						remaining = false;
						for(var t = 0; t < 4; t++) {
							for(var f = 0; f < 5; f++) {
								
								if(t != f)
								{
									var type1 = types[t];
									var type2 = types[f];

									for(var land1 in $scope.landtotal) {
										for(var land2 in $scope.landtotal) {
											var lv1 = $scope.landtotal[land1];
											var lv2 = $scope.landtotal[land2];
											
											if(lv1.name != lv2.name) {

												var demand_1_1  = lv1[type1+"_goal"]     - lv1[type1+"_newvalue"];
												var surplus_1_2 = lv1[type2+"_newvalue"] - lv1[type2+"_goal"];
												var demand_2_2  = lv2[type2+"_goal"]     - lv2[type2+"_newvalue"];
												var surplus_2_1 = lv2[type1+"_newvalue"] - lv2[type1+"_goal"];
												
												if(type2 == "gold") {
													demand_2_2 = 1000000;
												}

												var amt = Math.min(demand_1_1, surplus_1_2, demand_2_2, surplus_2_1);

												if(amt > 0) {

													// limit to trading one unit at a time so that trades are distributed evenly
													amt = 1;
													remaining = true;
													var amt2 = 1;

													// type1 cannot be gold because of the loops
													if(type2 == "gold") {
														amt2 = $scope[type1+"_price"];
													} else {
														if($scope[type1+"_price"] > $scope[type2+"_price"]) {
															$scope.registerTrade(lv1,lv2,"gold",type1,amt,0);
															lv1["gold_newvalue"] -= amt;
															lv1["gold_change"] -= amt;

															lv2["gold_newvalue"] += amt;
															lv2["gold_change"] += amt;
														}
														if($scope[type2+"_price"] > $scope[type1+"_price"]) {
															$scope.registerTrade(lv1,lv2,type2,"gold",0,amt);
															lv1["gold_newvalue"] += amt;
															lv1["gold_change"] += amt;
															
															lv2["gold_newvalue"] -= amt;
															lv2["gold_change"] -= amt;
														}
													}

													$scope.registerTrade(lv1,lv2,type2,type1,amt2,amt);

													lv1[type1+"_newvalue"] += amt;
													lv1[type1+"_change"] += amt;
													
													lv2[type1+"_newvalue"] -= amt;
													lv2[type1+"_change"] -= amt;

													lv2[type2+"_newvalue"] += amt2;
													lv2[type2+"_change"] += amt2;
													
													lv1[type2+"_newvalue"] -= amt2;
													lv1[type2+"_change"] -= amt2;

													$scope.landtotal[land1] = lv1;
													$scope.landtotal[land2] = lv2;
												}
											}
										}
									}
								}
							}
						}
					}
				}

				// now spread the resources back out to the settlements to save them in the database
				for(var land in $scope.landtotal) {
					var lv = $scope.landtotal[land];
					
					// Pick one resource type at a time to make tracking totals easier
					for(var t = 0; t < 5; t++) {
						var type = types[t];
						
						var total = lv[type+"_newvalue"];
						var lastfoundindex = 0;
						// find newSettlementValues for this land
						for(var i = 0; i < $scope.newSettlementValues.length; i++) {
							sv = $scope.newSettlementValues[i];

							if(lv.name == sv.Settlement.Land.name) {
								lastfound = i;
								var amount = lv[type+"_newvalue"] * sv[type+"_goal"] / lv[type+"_goal"];
								sv[type+"_dist"] = Math.floor(amount);
								total -= Math.floor(amount);
							}
							$scope.newSettlementValues[i] = sv;
						}

						// handle rounding errors
						if(total > 0) {
							$scope.newSettlementValues[lastfound][type+"_dist"] += total;
						}
					}
				}
			}

			$scope.sortByPriority = function(a,b) {
				if(a.priority < b.priority) {
					return -1;
				} 
				if(a.priority > b.priority) {
					return 1;
				}
				return 0;
			}

			$scope.buildThings = function() {
				for(var key in $scope.newSettlementValues) {
					var nsv = $scope.newSettlementValues[key];
					var buildings = nsv.Settlement.SettlementBuilding;

					buildings.sort($scope.sortByPriority);

					console.log("Building Purchases -- "+nsv.Settlement.Land.name+"::"+nsv.Settlement.name);
					console.log("labor avail = "+nsv.labor_avail);

					for(var i = 0; i < buildings.length; i++) {

						// Not paid for, or paid for already this turn... need to deduct the values again as this is a rerun of the trades
						if(buildings[i].manualbuild == 0 && (buildings[i].paidfor == 0 || buildings[i].paidfor == nsv.turn_id)) {
							var imp = buildings[i].Improvement;
							
							// Just in case the values have changed since this project was queued
							buildings[i].turns_left = imp.turns_needed;
							buildings[i].labor_left = imp.labor_needed;
							buildings[i].max_labor = parseInt(imp.labor_needed) / parseInt(imp.turns_needed);

							if( parseInt(nsv.stone_dist) >= parseInt(imp.stone_needed) && 
								parseInt(nsv.lumber_dist) >= parseInt(imp.wood_needed) && 
								parseInt(nsv.gold_dist) >= parseInt(imp.gold_needed) && 
								parseInt(nsv.goods_dist) >= parseInt(imp.goods_needed) ) {

								// Can pay for the building
								nsv.stone_dist  = nsv.stone_dist - parseInt(imp.stone_needed);
								nsv.lumber_dist = nsv.lumber_dist - parseInt(imp.wood_needed);
								nsv.gold_dist   = nsv.gold_dist - parseInt(imp.gold_needed);
								nsv.goods_dist  = nsv.goods_dist - parseInt(imp.goods_needed);
								buildings[i].paidfor = nsv.turn_id;

								buildings[i].stone_paid = parseInt(imp.stone_needed);
								buildings[i].wood_paid = parseInt(imp.wood_needed);
								buildings[i].gold_paid = parseInt(imp.gold_needed);
								buildings[i].goods_paid = parseInt(imp.goods_needed);

								$scope.registerIncomeExpense(nsv.Settlement.Land.name, -1 * ((parseInt(imp.stone_needed) + parseInt(imp.wood_needed) + parseInt(imp.goods_needed)) * 10 + parseInt(imp.gold_needed)));

							} else {

								buildings[i].paidfor = 0;
							}
						}
						
						// Work has started, 
						if(buildings[i].paidfor != 0) {
							buildings[i].labor_avail = nsv.labor_avail;

							// need to roll back the labor spent if we are recomputing this turn
							if(nsv.last_labor_turn == nsv.turnid) {
								buildings[i].labor_left = parseInt(buildings[i].labor_left) + parseInt(buildings[i].last_labor_spent);
								buildings[i].last_labor_turn = 0;
								if(buildings[i].labor_left > 0) {
									buildings[i].completed = 0;
								}
							}

							// apply labor and adjust turns left
							if(nsv.labor_avail > 0) {
								console.log(buildings[i].id+": labor left = "+buildings[i].labor_left +" max("+buildings[i].max_labor+")");

								if(nsv.labor_avail > buildings[i].max_labor) {
									buildings[i].labor_left -= parseInt(buildings[i].max_labor);
									nsv.labor_avail -= parseInt(buildings[i].max_labor);
									buildings[i].last_labor_spent = parseInt(buildings[i].max_labor);
									buildings[i].last_labor_turn = parseInt(nsv.turnid);
								} else {
									buildings[i].labor_left -= parseInt(nsv.labor_avail);
									buildings[i].last_labor_spent = parseInt(nsv.labor_avail);
									buildings[i].last_labor_turn = parseInt(nsv.turnid);
									nsv.labor_avail = 0;
								}

								if(buildings[i].labor_left > 0) {
									buildings[i].turns_left = Math.ceil(parseInt(buildings[i].labor_left) / parseInt(buildings[i].max_labor));
								} else {
									buildings[i].turns_left = 0;
									buildings[i].labor_left = 0; // just to be sure.
								}
								console.log(buildings[i].id+": labor left = "+buildings[i].labor_left +", turns left = "+buildings[i].turns_left);
							}
							
							//Work has completed
							if(buildings[i].labor_left == 0) {
								buildings[i].completed = nsv.turn_id;
							}
						}

					}

					$scope.newSettlementValues[key] = nsv;
				}
			}

			$scope.saveManualValues = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/land_system/saveManualValues/',
					data: $scope.settlementvalues
				}).success(function(data) {
					$scope.manualValuesStatus = "saved";
					//$scope.debug = data;
				});
			}

			$scope.loadManualValues = function() {
				$http({ method: 'GET', url: '/land_system/loadManualValues/'}).success(function(data) {
					$scope.manualValues = data;
					$scope.manualValuesStatus = "loaded";

					for(i = 0; i < $scope.settlementvalues.length; i++) {
						for(j = 0; j < $scope.manualValues.length; j++) {
							if($scope.settlementvalues[i].SettlementValues.settlement_id == $scope.manualValues[j].SettlementValuesAdjusted.settlement_id) {
								$scope.settlementvalues[i].SettlementValues.happiness = $scope.manualValues[j].SettlementValuesAdjusted.happiness;
								$scope.settlementvalues[i].SettlementValues.population = $scope.manualValues[j].SettlementValuesAdjusted.population;
								$scope.settlementvalues[i].SettlementValues.public_order = $scope.manualValues[j].SettlementValuesAdjusted.public_order;
								$scope.settlementvalues[i].SettlementValues.health = $scope.manualValues[j].SettlementValuesAdjusted.health;
								$scope.settlementvalues[i].SettlementValues.military = $scope.manualValues[j].SettlementValuesAdjusted.military;
								$scope.settlementvalues[i].SettlementValues.criminal = $scope.manualValues[j].SettlementValuesAdjusted.criminal;
								$scope.settlementvalues[i].SettlementValues.wildlands = $scope.manualValues[j].SettlementValuesAdjusted.wildlands;
								$scope.settlementvalues[i].SettlementValues.gold = $scope.manualValues[j].SettlementValuesAdjusted.gold;
								$scope.settlementvalues[i].SettlementValues.food = $scope.manualValues[j].SettlementValuesAdjusted.food;
								$scope.settlementvalues[i].SettlementValues.stone = $scope.manualValues[j].SettlementValuesAdjusted.stone;
								$scope.settlementvalues[i].SettlementValues.lumber = $scope.manualValues[j].SettlementValuesAdjusted.lumber;
								$scope.settlementvalues[i].SettlementValues.goods = $scope.manualValues[j].SettlementValuesAdjusted.goods;
							}
						}
					}
				});
			}
      	
		}]);
		
	</script>

	<div ng-controller="LandSystemController" ng-init="initialize()">
		
		<h2 ng-hide="readyToRun == true">{{loadingMessage}}</h2>
		<div ng-show="readyToRun == true">
			<input type="checkbox" ng-model="allowTrades"/>Allow Trades?<br/>
			<input type="checkbox" ng-model="allowBuilding"/>Allow Building?<br/>
			<input type="checkbox" ng-model="allowImprovements"/>Allow Improvements<br/>
			<input type="checkbox" ng-model="adjustNumbers"/>Manual Adjust<br/>

			<div ng-show="adjustNumbers == true">

				<button ng-click="saveManualValues()">Save Manual Values</button> <button ng-click="loadManualValues()">Load Manual Values</button> {{manualValuesStatus}}
				<table class="tradetable">
					<tr>
						<td style="border:none;" colspan="2"></td>

						<th>Order</th>
						<th>Health</th>
						<th>Happiness</th>
						<th>Wildlands</th>
						<th>Population</th>
						<th>Military</th>
						<th>Criminals</th>
						<th>Gold</th>
						<th>Stone</th>
						<th>Lumber</th>
						<th>Goods</th>
						<th>Food</th>
					</tr>

					<tr ng-repeat="s in settlementvalues">
						<th>{{s.Settlement.Land.name}}</th>
						<th>{{s.Settlement.name}}</th>
						
						<td><input type="text" ng-model="s.SettlementValues.public_order" size="4"/></td>
						<td><input type="text" ng-model="s.SettlementValues.health" size="4"/></td>
						<td><input type="text" ng-model="s.SettlementValues.happiness" size="4"/></td>
						<td><input type="text" ng-model="s.SettlementValues.wildlands" size="4"/></td>
						<td><input type="text" ng-model="s.SettlementValues.population" size="4"/></td>
						<td><input type="text" ng-model="s.SettlementValues.military" size="4"/></td>
						<td><input type="text" ng-model="s.SettlementValues.criminal" size="4"/></td>
						<td><input type="text" ng-model="s.SettlementValues.gold" size="4"/></td>
						<td><input type="text" ng-model="s.SettlementValues.stone" size="4"/></td>
						<td><input type="text" ng-model="s.SettlementValues.lumber" size="4"/></td>
						<td><input type="text" ng-model="s.SettlementValues.goods" size="4"/></td>
						<td><input type="text" ng-model="s.SettlementValues.food" size="4"/></td>
					</tr>
					
				</table>
			</div>

			<button ng-click="runTheNumbers()">Run</button>
		</div>

		<h3>End of Turn Values</h3>
		<table class="tradetable">
			<tr>
				<td style="border:none;" colspan="2"></td>
				<th colspan="4">Metrics</th>
				<td style="border:none;" colspan="1"></td>
				<th colspan="6">Labor Pool</th>
				<td style="border:none;" colspan="3"></td>
				<th colspan="5">Resources</th>
			</tr>
			<tr>
				<td style="border:none;" colspan="2"></td>

				<th>Order</th>
				<th>Health</th>
				<th>Happiness</th>
				<th>Wildlands</th>
				<th>Population</th>
				<th>Available</th>
				<th>Making Food</th>
				<th>Misc Jobs</th>
				<th>Imp Jobs</th>
				<th>Employed</th>
				<th>Unemployed</th>
				<th>Military</th>
				<th>Effect</th>
				<th>Criminals</th>
				<th>Gold</th>
				<th>Stone</th>
				<th>Lumber</th>
				<th>Goods</th>
				<th>Food</th>
			</tr>

			<tr ng-repeat="s in settlementvalues">
				<th>{{s.Settlement.Land.name}}</th>
				<th>{{s.Settlement.name}}</th>
				
				<td>{{s.public_order | number:0}}</td>
				<td>{{s.health | number:0}}</td>
				<td>{{s.happiness | number:0}}</td>
				<td>{{s.wildlands | number:0}}</td>
				<td>{{s.population | number:0}}</td>
				<td>{{s.labor | number:0}}</td>
				<td>{{s.labor_food | number:0}}</td>
				<td>{{s.labor_misc | number:0}}</td>
				<td>{{s.labor_jobs | number:0}}</td>
				<td>{{s.labor_used | number:0}}</td>
				<td>{{s.labor_unused | number:0}} / {{s.labor_shortage | number:0}}</td>
				<td>{{s.military | number:0}}</td>
				<td>{{s.military_effect | number:0}}</td>
				<td>{{s.criminal | number:0}}</td>
				<td>{{s.gold | number:0}}</td>
				<td>{{s.stone | number:0}}</td>
				<td>{{s.lumber | number:0}}</td>
				<td>{{s.goods | number:0}}</td>
				<td>{{s.food | number:0}}</td>
			</tr>
			
		</table>

		<h3>Supply and Demand</h3>
		
		<table class="tradetable">
			<tr>
				<th>Income Total: </td><td>{{total_income | number:0}}</td>
				<th>Gold: </td><td>{{total_gold_income | number:0}}  </td>
				<th>Stone: </td><td>{{total_stone_income | number:0}}</td>
				<th>Lumber: </td><td>{{total_lumber_income | number:0}}</td>
				<th>Goods: </td><td>{{total_goods_income | number:0}}</td>
				<th>Food: </td><td>{{total_food_income | number:0}}</td>
			</tr><tr>
				<th>Expense Total: </td><td>{{total_expense | number:0}} </td> 
				<th>Gold: </td><td>{{total_gold_expense | number:0}}  </td>
				<th>Stone: </td><td>{{total_stone_expense | number:0}}</td>
				<th>Lumber: </td><td>{{total_lumber_expense | number:0}}</td>
				<th>Goods: </td><td>{{total_goods_expense | number:0}}</td>
				<th>Food: </td><td>{{total_food_expense | number:0}}</td>
			</tr>
		</table>

		<table class="tradetable">
			<tr>
				<td style="border:none;"></td>
				<td style="border:none;"></td>

				<th colspan="4">Gold</th>
				<th colspan="4">Stone ({{stone_price}})</th>
				<th colspan="4">Lumber ({{lumber_price}})</th>
				<th colspan="4">Goods ({{goods_price}})</th>
				<th colspan="4">Food ({{food_price}})</th>
			</tr>
			<tr>
				<th colspan="2" style="text-align:right;">Available</th>
				<td colspan="4">{{total.gold_surplus | number:0}}</td>
				<td colspan="4">{{total.stone_surplus | number:0}}</td>
				<td colspan="4">{{total.lumber_surplus | number:0}}</td>
				<td colspan="4">{{total.goods_surplus | number:0}}</td>
				<td colspan="4">{{total.food_surplus | number:0}}</td>
			</tr>
			<tr>
				<th colspan="2" style="text-align:right;">Demand</th>
				<td colspan="4">{{total.gold_demand | number:0}}</td>
				<td colspan="4">{{total.stone_demand | number:0}}</td>
				<td colspan="4">{{total.lumber_demand | number:0}}</td>
				<td colspan="4">{{total.goods_demand | number:0}}</td>
				<td colspan="4">{{total.food_demand | number:0}}</td>
			</tr>

			<tr>
				<td style="border:none;"></td>
				<td style="border:none;"></td>

				<th>cur</th>
				<th>goal</th>
				<th>sur</th>
				<th>dem</th>

				<th>cur</th>
				<th>goal</th>
				<th>sur</th>
				<th>dem</th>

				<th>cur</th>
				<th>goal</th>
				<th>sur</th>
				<th>dem</th>

				<th>cur</th>
				<th>goal</th>
				<th>sur</th>
				<th>dem</th>

				<th>cur</th>
				<th>goal</th>
				<th>sur</th>
				<th>dem</th>
			</tr>
			
			<tr ng-repeat="l in landtotal">
				<th colspan="2">{{l.name}}</th>
				
				<td>{{l.gold | number:0}}</td>
				<td>{{l.gold_goal | number:0}}</td>
				<td>{{l.gold_surplus | number:0}}</td>
				<td>{{l.gold_demand | number:0}}</td>

				<td>{{l.stone | number:0}}</td>
				<td>{{l.stone_goal | number:0}}</td>
				<td>{{l.stone_surplus | number:0}}</td>
				<td>{{l.stone_demand | number:0}}</td>

				<td>{{l.lumber | number:0}}</td>
				<td>{{l.lumber_goal | number:0}}</td>
				<td>{{l.lumber_surplus | number:0}}</td>
				<td>{{l.lumber_demand | number:0}}</td>

				<td>{{l.goods | number:0}}</td>
				<td>{{l.goods_goal | number:0}}</td>
				<td>{{l.goods_surplus | number:0}}</td>
				<td>{{l.goods_demand | number:0}}</td>

				<td>{{l.food | number:0}}</td>
				<td>{{l.food_goal | number:0}}</td>
				<td>{{l.food_surplus | number:0}}</td>
				<td>{{l.food_demand | number:0}}</td>
			</tr>
		</table>

		<h3>Taxes</h3>

		<table class="noborder">
			<tr ng-repeat="t in taxes">
				<td>{{t.name}} generated </td><td> {{t.amount | number:0}} gold in taxes</td>
			</tr>
			<tr>
				<td>Total Tax Received: {{taxesTotal | number:0}} gold</td>
			</tr>
		</table>

		<h3>Profit / Loss</h3>

		<table class="noborder">
			<tr ng-repeat="pl in profits">
				<td>{{pl.name}} had </td>
				<td> {{pl.amount | number:0}} total income </td>
				<td> and {{pl.losses | number:0}} total expenses</td>
				<td> net change of {{pl.total | number:0}}</td>
			</tr>
		</table>

		<h3>Trades</h3>

		<table class="noborder">
			<tr ng-repeat="t in trades">
				<td>{{t.from}} traded </td>
				<td><span ng-show="t.traded.gold < 0">{{t.traded.gold * -1 | number:0}} gold </span> 
					<span ng-show="t.traded.food < 0">{{t.traded.food * -1 | number:0}} food </span> 
					<span ng-show="t.traded.stone < 0">{{t.traded.stone * -1 | number:0}} stone </span> 
					<span ng-show="t.traded.lumber < 0">{{t.traded.lumber * -1 | number:0}} lumber </span> 
					<span ng-show="t.traded.goods < 0">{{t.traded.goods * -1 | number:0}} goods </span>
				</td>
				<td>to</td>
				<td>{{t.to}} for </td>
				<td><span ng-show="t.traded.gold > 0">{{t.traded.gold | number:0}} gold </span> 
					<span ng-show="t.traded.food > 0">{{t.traded.food | number:0}} food </span> 
					<span ng-show="t.traded.stone > 0">{{t.traded.stone | number:0}} stone </span> 
					<span ng-show="t.traded.lumber > 0">{{t.traded.lumber | number:0}} lumber </span> 
					<span ng-show="t.traded.goods > 0">{{t.traded.goods | number:0}} goods </span>
				</td>
				<td><span ng-show="tradebalance(t.traded) != 0">{{tradebalance(t.traded)}}</span></td>
			</tr>
		</table>

		<h3>Post Trade Values</h3>

		<table class="tradetable">
			<tr>
				<td style="border:none;"></td>
				<td style="border:none;"></td>

				<th colspan="5">Gold</th>
				<th colspan="5">Stone ({{stone_price}})</th>
				<th colspan="5">Lumber ({{lumber_price}})</th>
				<th colspan="5">Goods ({{goods_price}})</th>
				<th colspan="5">Food ({{food_price}})</th>
			</tr>
			<tr>
				<td style="border:none;"></td>
				<td style="border:none;"></td>

				<th>before</th>
				<th>demand</th>
				<th>surplus</th>
				<th>cha</th>
				<th>now</th>

				<th>before</th>
				<th>demand</th>
				<th>surplus</th>
				<th>cha</th>
				<th>now</th>

				<th>before</th>
				<th>demand</th>
				<th>surplus</th>
				<th>cha</th>
				<th>now</th>

				<th>before</th>
				<th>demand</th>
				<th>surplus</th>
				<th>cha</th>
				<th>now</th>

				<th>before</th>
				<th>demand</th>
				<th>surplus</th>
				<th>cha</th>
				<th>now</th>
			</tr>

			<tr ng-repeat="l in landtotal">
				<th colspan="2">{{l.name}}</th>
				
				<td>{{l.gold | number:0}}</td>
				<td>{{l.gold_demand | number:0}}</td>
				<td>{{l.gold_surplus | number:0}}</td>
				<td>{{l.gold_change | number:0}}</td>
				<td>{{l.gold_newvalue | number:0}}</td>

				<td>{{l.stone | number:0}}</td>
				<td>{{l.stone_demand | number:0}}</td>
				<td>{{l.stone_surplus | number:0}}</td>
				<td>{{l.stone_change | number:0}}</td>
				<td>{{l.stone_newvalue | number:0}}</td>

				<td>{{l.lumber | number:0}}</td>
				<td>{{l.lumber_demand | number:0}}</td>
				<td>{{l.lumber_surplus | number:0}}</td>
				<td>{{l.lumber_change | number:0}}</td>
				<td>{{l.lumber_newvalue | number:0}}</td>

				<td>{{l.goods | number:0}}</td>
				<td>{{l.goods_demand | number:0}}</td>
				<td>{{l.goods_surplus | number:0}}</td>
				<td>{{l.goods_change | number:0}}</td>
				<td>{{l.goods_newvalue | number:0}}</td>

				<td>{{l.food | number:0}}</td>
				<td>{{l.food_demand | number:0}}</td>
				<td>{{l.food_surplus | number:0}}</td>
				<td>{{l.food_change | number:0}}</td>
				<td>{{l.food_newvalue | number:0}}</td>
			</tr>
		</table>

		<h3>Resdistribution</h3>
		<table class="tradetable">
			<tr>
				<td colspan="22" style="border:none;">&nbsp;</td>
			</tr>

			<tr>
				<td style="border:none;" colspan="2"></td>

				<th colspan="2">Gold</th>
				<th colspan="2">Stone</th>
				<th colspan="2">Lumber</th>
				<th colspan="2">Goods</th>
				<th colspan="2">Food</th>
			</tr>
			<tr>
				<td style="border:none;" colspan="2"></td>
				<th>Goal</th>
				<th>Value</th>
				<th>Goal</th>
				<th>Value</th>
				<th>Goal</th>
				<th>Value</th>
				<th>Goal</th>
				<th>Value</th>
				<th>Goal</th>
				<th>Value</th>
			</tr>

			<tr ng-repeat="s in newSettlementValues">
				<th>{{s.Settlement.Land.name}}</th>
				<th>{{s.Settlement.name}}</th>
				
				<td>{{s.gold_goal | number:0}}</td>
				<td>{{s.gold_dist | number:0}}</td>
				
				<td>{{s.stone_goal | number:0}}</td>
				<td>{{s.stone_dist | number:0}}</td>
				
				<td>{{s.lumber_goal | number:0}}</td>
				<td>{{s.lumber_dist | number:0}}</td>
				
				<td>{{s.goods_goal | number:0}}</td>
				<td>{{s.goods_dist | number:0}}</td>

				<td>{{s.food_goal | number:0}}</td>
				<td>{{s.food_dist | number:0}}</td>
			</tr>

		</table>

		<h3>Final Numbers</h3>

		<table class="tradetable">
			<tr>
				<td style="border:none;" colspan="2"></td>
				<th colspan="6">Order</th>
				<th colspan="3">Metrics</th>
				<th colspan="6">Population</th>
				<td style="border:none;" colspan="4"></td>
				<th colspan="5">Resources</th>
			</tr>
			<tr>
				<td style="border:none;" colspan="2"></td>

				<th>New</th><th>Old</th><th>Base</th><th>Pop</th><th>Swing</th><th>Mil</th>
				<th>Health</th>
				<th>Happy</th>
				
				<th>Wilds</th>
				<th>New</th><th>Raw</th><th>Base</th><th>Crowd</th><th>Birth</th><th>Death</th>
				<th>Military</th>
				<th>Effect</th>
				<th>Criminals</th>
				<th>Gold</th>
				<th>Stone</th>
				<th>Lumber</th>
				<th>Goods</th>
				<th>Food</th>
				<th>Labor</th>
			</tr>

			<tr ng-repeat="s in newSettlementValues">
				<th>{{s.Settlement.Land.name}}</th>
				<th>{{s.Settlement.name}}</th>
				
				<td>{{s.public_order | number:0}}</td>
				<td> {{s.public_order_old | number:0}}</td>
				<td> {{s.public_order_baseline | number:0}}</td>
				<td> {{s.public_order_population_drop | number:0}}</td>
				<td> {{s.public_order_swing | number:0}}</td>
				<td> {{s.military_public_order_swing | number:0}}</td>

				<td>{{s.health | number:0}}</td>
				<td>{{s.happiness | number:0}}</td>
				<td>{{s.wildlands | number:0}}</td>

				<td>{{s.population | number:0}}</td>
				<td> {{s.population_without_boost | number:0}}</td>
				<td> {{s.population_baseline | number:0}}</td>
				<td> {{s.overcrowding | number:0}}</td>
				<td> {{s.new_births | number:0}}</td>
				<td> {{s.new_deaths | number:0}}</td>

				<td>{{s.military | number:0}}</td>
				<td>{{s.military_effect | number:0}}</td>
				<td>{{s.criminal | number:0}}</td>
				<td>{{s.gold_dist | number:0}}</td>
				<td>{{s.stone_dist | number:0}}</td>
				<td>{{s.lumber_dist | number:0}}</td>
				<td>{{s.goods_dist | number:0}}</td>
				<td>{{s.food_dist | number:0}}</td>
				<td>{{s.labor_avail | number:0}}</td>
			</tr>
			
		</table>

		<table class="tradetable">
			<tr>
				<td style="border:none;"></td>
				<td style="border:none;"></td>

				<td>Building Status</td>

				<td>Labor Left Over</td>
			</tr>

			<tr ng-repeat="nsv in newSettlementValues">
				<td>{{nsv.Settlement.Land.name}}</td>
				<td>{{nsv.Settlement.name}}</td>

				<td>
					<table class="tradetable">
						<tr>
							<th>Improvement</th>
							<th>Paid For?</th>
							<th>Completed?</th>
							<th>Turns Left</th>
							<th>Labor Left</th>
							<th>Priority</th>
						</tr>
						<tr ng-repeat="build in nsv.Settlement.SettlementBuilding">
							<td>{{build.Improvement.name}}</td>
							<td>{{build.paidfor}}</td>
							<td>{{build.completed}}</td>
							<td>{{build.turns_left}}</td>
							<td>{{build.labor_left}}</td>
							<td>{{build.priority}}</td>
						</tr>
					</table>
				</td>
				<td>{{nsv.labor_avail}}</td>
			</tr>
		</table>

		<?php if(AuthComponent::user('role_landadmin')) { ?>
			<button ng-click="saveFinalValues()">Save Values</button> Only click this if you know what you are doing.
			<div ng-show="saved >= 2">Values Saved</div>
			{{debug}}
		<?php } ?>		

	</div> <!-- end of the ng-controller div. -->

</div>