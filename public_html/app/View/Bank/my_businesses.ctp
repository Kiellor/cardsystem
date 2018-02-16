<style>
	.atable table { }
	.atable th { padding: 3px; vertical-align: top; text-align: left;}
	.atable td { padding: 3px; vertical-align: top; }

	.noborder table { border:none; }
	.noborder th { border:none; }
	.noborder td { border:none; }
</style>

<div id="ledgerView" ng-app="ledgerViewApp">

	<script type="text/javascript">

		var ledgerViewApp = angular.module('ledgerViewApp',[]);

		ledgerViewApp.controller('LedgerViewController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {
				$scope.loadCharacters();
			}
			
			$scope.loadCharacters = function() {
				$http({ method: 'GET', url: '/bank/loadmycharacters/'}).success(function(data) {
					$scope.characters = data;
					
					if($scope.characters.length >= 1) {
						$scope.selectedCharacter = $scope.characters[0];
						$scope.loadCharacterDetails();
					}
				});
			}

			$scope.loadCharacterDetails = function() {
				$scope.businesses = $scope.selectedCharacter.Business;

				for(var j = 0; j < $scope.businesses.length; j++) {

					var gold_sum = 0;
					var lux_sum = 0;
					var dur_sum = 0;
					var con_sum = 0;
					var wear_sum = 0;

					for(var i = 0; i < $scope.businesses[j].Ledger.length; i++) {
						gold_sum += parseFloat($scope.businesses[j].Ledger[i].BusinessDeposit.Gold_total);
						lux_sum += parseInt($scope.businesses[j].Ledger[i].BusinessDeposit.Luxury_total);
						dur_sum += parseInt($scope.businesses[j].Ledger[i].BusinessDeposit.Durable_total);
						con_sum += parseInt($scope.businesses[j].Ledger[i].BusinessDeposit.Consumable_total);
						wear_sum += parseInt($scope.businesses[j].Ledger[i].BusinessDeposit.Wearable_total);
					}

					$scope.businesses[j].gold_sum = gold_sum;
					$scope.businesses[j].lux_sum = lux_sum;
					$scope.businesses[j].dur_sum = dur_sum;
					$scope.businesses[j].con_sum = con_sum;
					$scope.businesses[j].wear_sum = wear_sum;
				}
			}

			$scope.limitify = function(value) {
				if(value == 0) {
					return "no limit";
				} else if(value == -1) {
					return "deposit only";
				} 

				return value;
			}

		}]);
	</script>

	<div ng-controller="LedgerViewController" ng-init="initialize()">
		<h2>My Business Ledgers</h2>

		Character: <select ng-options="c as c.Character.name for c in characters" ng-model="selectedCharacter" ng-change="loadCharacterDetails()"></select>

		<div ng-hide="businesses.length > 0">
			<h3>Selected Character is not part of any Businesses</h3>
		</div>

		<div ng-show="selectedCharacter">
			<div ng-repeat="b in businesses">
				<h3>{{b.name}}</h3>
				{{b.description}}

				<table>
					<tr>
						<th>Member</th>
						<th>Position</th>
						<th>Gold Limit</th>
						<th>Commodities Limit</th>
					</tr>
					<tr ng-repeat="partner in b.BusinessPartner">
						<td>{{partner.Character.name}}</td>
						<td>{{partner.position}}</td>
						<td style="text-align:right">{{limitify(partner.gold_limit)}}</td>
						<td style="text-align:right">{{limitify(partner.commodities_limit)}}</td>
					</tr>
				</table>

				<br/>

				<table>
					<tr>
						<th>Partner</th>
						<th>Gold</th>
						<th>Luxuries</th>
						<th>Durables</th>
						<th>Consumables</th>
						<th>Wearables</th>
					</tr>
					<tr ng-repeat="deposit in b.Ledger">
						<td>{{deposit.c.name}}</td>
						<td align="right">{{deposit.BusinessDeposit.Gold_total | number:0}}</td>
						<td align="right">{{deposit.BusinessDeposit.Luxury_total | number:0}}</td>
						<td align="right">{{deposit.BusinessDeposit.Durable_total | number:0}}</td>
						<td align="right">{{deposit.BusinessDeposit.Consumable_total | number:0}}</td>
						<td align="right">{{deposit.BusinessDeposit.Wearable_total | number:0}}</td>
					</tr>

					<tr>
						<th>Current Total</th>
						<th align="right">{{b.gold_sum}}</th>
						<th align="right">{{b.lux_sum}}</th>
						<th align="right">{{b.dur_sum}}</th>
						<th align="right">{{b.con_sum}}</th>
						<th align="right">{{b.wear_sum}}</th>
					</tr>
				</table>

				<div>
					<ul>
						<li ng-repeat="imps in b.SettlementImprovement">{{imps.name}} is a Rank {{imps.rank}} {{imps.Improvement.name}} ({{imps.commodity}}) in {{imps.Settlement.name}} of {{imps.Land.name}}</li>
					</ul>
				</div>

				<hr/>

			</div>
		</div>

		
		<div>
			{{debug}}
		</div>

	</div>
</div>