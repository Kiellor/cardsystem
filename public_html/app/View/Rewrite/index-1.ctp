<style>
	.grid table { border-collapse: collapse; border: 1px solid black; }
	.grid th { border: 1px solid black; padding: 3px; vertical-align: top;}
	.grid td { border: 1px solid black; padding: 3px; vertical-align: top; text-align: right;}
</style>

<div id="rewrite" ng-app="rewriteApp">

	<script type="text/javascript">

		var rewriteApp = angular.module('rewriteApp',[]);

		rewriteApp.controller('RewriteController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {
				$scope.toBeUnlearned = new Array();
				$scope.alsoUnlearn = new Array();

				$scope.loadCharacter();
				$scope.loadProposal();
				$scope.upgradeRace();
				$scope.loadAllRaces();
			}

			$scope.loadCharacter = function() {
				$http({ method: 'GET', url: '/rewrite/loadCharacter/<?php echo $cardnumber; ?>'}).success(function(data) {
					$scope.character = data;
				});
			}

			$scope.loadProposal = function() {
				$http({ method: 'GET', url: '/rewrite/loadProposal/<?php echo $cardnumber; ?>'}).success(function(data) {
					$scope.proposal = data;
				});
			}

			$scope.loadAllRaces = function() {
				$http({ method: 'GET', url: '/rewrite/loadAllRaces/<?php echo $cardnumber; ?>'}).success(function(data) {
					$scope.allraces = data;
				});
			}

			$scope.upgradeRace = function() {
				$http({ method: 'GET', url: '/rewrite/upgradeRace/<?php echo $cardnumber; ?>'}).success(function(data) {
					$scope.upgrade = data;
					$scope.computeSums();

					if($scope.upgrade.NewAbilities[0].Ability.ability_name == "Kormyrian Spirit") {
						$scope.upgrade.NewAbilities[0].CharacterAbility.quantity = 1;
						$scope.submitProposal();
					}
				});
			}

			$scope.selectNewRace = function(nr) {
				$http({ method: 'GET', url: '/rewrite/upgradeRace/<?php echo $cardnumber; ?>/'+nr.Ability.id}).success(function(data) {
					$scope.upgrade = data;
					$scope.computeSums();
					$scope.changeRace = false;
				});
			}

			$scope.validateQty = function(d) {
				if(d.CharacterAbility.quantity < 0) {
					d.CharacterAbility.quantity = 0;
				} else if(d.Ability.abilitygroup_id == 51 && d.CharacterAbility.quantity > 1) {
					d.CharacterAbility.quantity = 1;
				}

				$scope.computeSums();
			}

			$scope.computeSums = function() {
				$scope.unlearningTotal = 0;
				$scope.learningTotal = 0;

				for(var i = 0; i < $scope.upgrade.OldAbilities.length; i++) {

					$scope.unlearningTotal += $scope.upgrade.OldAbilities[i].CharacterAbility.quantity * $scope.upgrade.OldAbilities[i].CharacterAbility.build_spent;

					if(typeof $scope.upgrade.OldAbilities[i].CharacterAbility.newcost !== 'undefined') {
						$scope.learningTotal += $scope.upgrade.OldAbilities[i].CharacterAbility.quantity * $scope.upgrade.OldAbilities[i].CharacterAbility.newcost;
					}
				}

				for(var i = 0; i < $scope.upgrade.NewAbilities.length; i++) {
					if(typeof $scope.upgrade.NewAbilities[i].CharacterAbility === 'undefined') {
						$scope.upgrade.NewAbilities[i].CharacterAbility = { quantity : 0 };
					}

					$scope.learningTotal += $scope.upgrade.NewAbilities[i].CharacterAbility.quantity * $scope.upgrade.NewAbilities[i].ListAbility.build_cost;
				}
			}

			$scope.submitProposal = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/rewrite/submitProposal/<?php echo $cardnumber; ?>', 
					data: $scope.upgrade
				}).success(function(data) {
					$scope.debug = data;
					$scope.saveResults = "Saved";

					// $scope.loadProposal();
					$scope.acceptProposal();
				});
			}

			$scope.deleteProposal = function() {
				$http({ method: 'GET', url: '/rewrite/deleteProposal/<?php echo $cardnumber; ?>'}).success(function(data) {
					$scope.accepted = "deleted";

					$scope.loadProposal();
				});
			}

			<?php
			if(AuthComponent::user('role_admin') || AuthComponent::user('role_cards')) {
			?>

			$scope.acceptProposal = function() {
				$http({ method: 'GET', url: '/rewrite/acceptProposal/<?php echo $cardnumber; ?>'}).success(function(data) {
					$scope.accepted = "Accepted";

					//$scope.initialize();
					location.href = "/rewrite/listProposals";
				});
			}

			$scope.listProposals = function() {
				location.href = "/rewrite/listProposals";
			}

			<?php
			}
			?>

		}]);
	</script>

	<div ng-controller="RewriteController" ng-init="initialize()">
		
		<h1>Race Rewrite Preview for {{character.Character.name}} ({{character.Character.cardnumber}})</h1>

		<div id="CardOptions">
			<ul>
				<li><a ng-href="/characters/view/{{character.Character.cardnumber}}">View Character</a></li>
			</ul>
		</div>

		<table width="100%">
			<tr>
				<td width="50%" valign="top">

					<h3>To Be Unlearned from {{upgrade.OldRace.Ability.ability_name}}</h3>
					<table>
						<tr>
							<th>Qty</th>
							<th>Build</th>
							<th>Ability</th>
							<th>Total</th>
						</tr>
						<tr ng-repeat="d in upgrade.OldAbilities">
							<td>{{d.CharacterAbility.quantity}}</td>
							<td>{{d.CharacterAbility.build_spent}}</td>
							<td>
								{{d.Ability.ability_name}}
								{{d.AbilityOption.ability_name}}
							</td>
							<td>{{d.CharacterAbility.quantity * d.CharacterAbility.build_spent}}</td>
						</tr>
						<tr>
							<th colspan="4">Total</th>
							<th>{{unlearningTotal}}</th>
						</tr>
					</table>
				</td>




				<td width="50%" valign="top">
					<h3>Learning {{upgrade.NewRace.Ability.ability_name}}</h3>
					<button ng-click="changeRace = true" ng-hide="changeRace">Select a Different Race</button>
					<button ng-click="changeRace = false" ng-show="changeRace">Cancel</button>
					<div ng-show="changeRace == true">
						<ul>
							<li ng-repeat="race in allraces"><a ng-click="selectNewRace(race)">{{race.Ability.ability_name}}</a></li>
						</ul>
					</div>
					<table>
						<tr>
							<th>Qty</th>
							<th>Build</th>
							<th>Ability</th>
							<th>Total</th>
						</tr>

						<!-- show items that have a new cost and are thus just translated over, not relearned -->
						<tr ng-repeat="d in upgrade.OldAbilities" ng-show="d.CharacterAbility.newcost">
							<td>{{d.CharacterAbility.quantity}}</td>
							<td>{{d.CharacterAbility.newcost}}</td>
							<td>
								{{d.Ability.ability_name}}
								{{d.AbilityOption.ability_name}}
							</td>
							<td>{{d.CharacterAbility.quantity * d.CharacterAbility.newcost}}</td>
						</tr>

						<tr ng-repeat="d in upgrade.NewAbilities">
							<td>
								<input type="text" size="2" ng-model="d.CharacterAbility.quantity" ng-change="validateQty(d)"/>
							</td>
							<td>{{d.ListAbility.build_cost}}</td>
							<td>
								{{d.Ability.ability_name}}
								{{d.AbilityOption.ability_name}}
								from {{d.Elist.list_name}}
							</td>
							<td><select ng-show="d.AbilityOptionList" ng-model="d.selectedOption" ng-options="item.Ability.ability_name for item in d.AbilityOptionList"></select></td>
						</tr>

						<tr>
							<th colspan="4">Total</th>
							<th>{{learningTotal}}</th>
						</tr>

					</table>

					<button ng-click="submitProposal()">Submit Proposal</button> {{saveResults}}

				</td>
			</tr>
		</table>

		<h3>Existing Proposal</h3>
		<table class="grid">
			<tr>
				<th>Qty</th>
				<th>Build</th>
				<th>Ability</th>
				<th>Total</th>
				<th>Action</th>
			</tr>
			<tr ng-repeat="row in proposal">
				<td>{{row.RewriteProposal.quantity}}</td>
				<td>{{row.RewriteProposal.build_spent}}</td>
				<td>{{row.Ability.ability_name}} {{row.AbilityOption.ability_name}}</td>
				<td>{{row.RewriteProposal.quantity * row.RewriteProposal.build_spent}}</td>
				<td>
					<span ng-show="row.RewriteProposal.delete == 1">Deleting</span>
					<span ng-show="row.RewriteProposal.newcost > 0">New Cost of {{row.RewriteProposal.newcost}}</span>
					<span ng-show="row.RewriteProposal.newability_id > 0">Change to {{row.RewriteProposal.newability_display}}</span>
					<span ng-show="row.RewriteProposal.adding == 1">Adding</span>
				</td>
			</tr>
		</table>

		<button ng-click="deleteProposal()">Delete Proposal</button>
		<?php
			if(AuthComponent::user('role_admin') || AuthComponent::user('role_cards')) {
		?>
			<button ng-click="acceptProposal()">Accept Proposal</button>
			<button ng-click="listProposals()">List Other Proposals</button>

		<?php
			}
		?>

		{{accepted}}

		<?php
			if(AuthComponent::user('role_admin') || AuthComponent::user('role_cards')) {
		?>
			<div>
				
			</div>
		<?php
			}
		?>

	</div>

</div>