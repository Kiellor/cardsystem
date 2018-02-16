<div id="company" ng-app="companyApp">

<script type="text/javascript">

		var companyApp = angular.module('companyApp',[]);

		companyApp.controller('companyController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function(data) {
				$scope.loadCompanies();
				$scope.loadCharacters();
				$scope.view="normal";
				$scope.compfilter = "";
				$scope.editingPartner = null;
				$scope.addingPartner = false;
				$scope.generating = 0;
			};

			$scope.loadCompanies = function() {
				$http({ method: 'GET', url: '/bank/loadCompanies/'}).success(function(data) {
					$scope.companies = data;
				});
			}

			$scope.loadCharacters = function() {
				$http({ method: 'GET', url: '/bank/getCharacters/'}).success(function(data) {
					$scope.characters = data;
				});	
			}

			$scope.loadCompanyDetails = function() {
				$scope.gold_sum = 0.0;
				$scope.lux_sum = 0;
				$scope.dur_sum = 0;
				$scope.con_sum = 0;
				$scope.wear_sum = 0;

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/bank/loadCompanyDetails/',
					data: $scope.selectedCompany
				}).success(function(data) {
					$scope.selectedCompanyDetails = data;


					for(var i = 0; i < $scope.selectedCompanyDetails.length; i++) {
						$scope.gold_sum += parseFloat($scope.selectedCompanyDetails[i].BusinessDeposit.Gold_total);
						$scope.lux_sum += parseInt($scope.selectedCompanyDetails[i].BusinessDeposit.Luxury_total);
						$scope.dur_sum += parseInt($scope.selectedCompanyDetails[i].BusinessDeposit.Durable_total);
						$scope.con_sum += parseInt($scope.selectedCompanyDetails[i].BusinessDeposit.Consumable_total);
						$scope.wear_sum += parseInt($scope.selectedCompanyDetails[i].BusinessDeposit.Wearable_total);
					}
				});
			}

			$scope.computeInterest = function() {
				for(var i = 0; i < $scope.companies.length; i++) {
					var c = $scope.companies[i];

					c.interest = Math.floor(c.Character.company_balance / 25) / 10;
					c.new_balance = parseFloat(c.Character.company_balance) + parseFloat(c.interest);

					if(c.interest > 0) {
						$scope.interestDeposits.push({id: c.c.id, interest: c.interest});
					}
				}
			}

			$scope.saveInterest = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/bank/saveInterest/',
					data: $scope.interestDeposits
				}).success(function(data) {
					$scope.saved = true;
				});
			}

			$scope.selectCompany = function(comp) {
				$scope.selectedCompany = comp;
				$scope.loadCompanyDetails();
				$scope.newDeposits = new Array();

				$scope.gold_deposit = 0;
				$scope.lux_deposit = 0;
				$scope.dur_deposit = 0;
				$scope.con_deposit = 0;
				$scope.wear_deposit = 0;
				$scope.gold_draw = 0;
				$scope.lux_draw = 0;
				$scope.dur_draw = 0;
				$scope.con_draw = 0;
				$scope.wear_draw = 0;
			}

			$scope.makedeposit = function() {
				var deposit = {
						character: $scope.depositby, 
						business: $scope.selectedCompany,
						gold_total: ($scope.gold_deposit - $scope.gold_draw),
						luxury_total: ($scope.lux_deposit - $scope.lux_draw),
						durable_total: ($scope.dur_deposit - $scope.dur_draw),
						consumable_total: ($scope.con_deposit - $scope.con_draw),
						wearable_total: ($scope.wear_deposit - $scope.wear_draw)
				};

				$scope.newDeposits.push(deposit);

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/bank/makeCompanyDeposit/',
					data: deposit
				}).success(function(data) {
					$scope.returnData = data;
					$scope.loadCompanyDetails();
				});

				$scope.gold_deposit = 0;
				$scope.lux_deposit = 0;
				$scope.dur_deposit = 0;
				$scope.con_deposit = 0;
				$scope.wear_deposit = 0;
				$scope.gold_draw = 0;
				$scope.lux_draw = 0;
				$scope.dur_draw = 0;
				$scope.con_draw = 0;
				$scope.wear_draw = 0;
			}

			$scope.searchFunction = function( item ) {

				if($scope.compfilter.length == 0) {
					return true;
				}

				if($scope.compfilter.length > 0 && item.Business.name.toLowerCase().indexOf($scope.compfilter.toLowerCase()) != -1) {
					return true;
				}

				if($scope.compfilter.length > 0) {
					for(var i = 0; i < item.BusinessPartner.length; i++) {
						if(item.BusinessPartner[i].Character.name.toLowerCase().indexOf($scope.compfilter.toLowerCase()) != -1) {
							return true;
						}
					}
				}

				return false;
			}

			$scope.addLedger = function() {
				var ledger = { name: $scope.compfilter };

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/bank/addLedger/',
					data: ledger
				}).success(function(data) {
					$scope.addingLedger="success";
					$scope.loadCompanies();
				});
			}

			$scope.limitify = function(value) {
				if(value == 0) {
					return "no limit";
				} else if(value == -1) {
					return "deposit only";
				} 

				return value;
			}

			$scope.editPartner = function(partner) {
				$scope.editingPartner = partner;
			}

			$scope.savePartner = function(partner) {
				var submission = { partner: partner };

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/bank/savePartner/',
					data: submission
				}).success(function(data) {
					$scope.editingPartner = null;
					$scope.loadCompanies();
					$scope.loadCompanyDetails();
				});
			}

			$scope.deletePartner = function(partner) {
				var submission = { partner: partner };

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/bank/deletePartner/',
					data: submission
				}).success(function(data) {
					$scope.editingPartner = null;
					$scope.loadCompanies();
					$scope.loadCompanyDetails();
				});
			}

			$scope.addPartner = function(partner) {
				$scope.newpartner.business_id = $scope.selectedCompany.Business.id;
				var submission = { partner: $scope.newpartner };

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/bank/addPartner/',
					data: submission
				}).success(function(data) {
					$scope.loadCompanies();
					$scope.loadCompanyDetails();
					$scope.selectCompany($scope.selectedCompany);
					$scope.editingPartner = null;
					$scope.addingPartner = false;
				});
			}

			$scope.generateAllLedgers = function() {

				$scope.generating = $scope.companies.length;
				for(var i = 0; i < $scope.companies.length; i++) {
					var companyid = $scope.companies[i].Business.id;
					$http({ method: 'GET', url: '/bank/generateCompanyLedger/'+companyid}).success(function(data) {
						$scope.generating--;
					});
				}
			}

$scope.combined = function(c){
        return c.name + " " + c.cardnumber;
    }
		}]);
</script>

<div ng-controller="companyController" ng-init="initialize()">
 	
 	<div>
		<div style="float:left; width:30%;">
			Ledger Name: <input ng-model="compfilter"/> (to add a new ledger, type its name in first)
			
			<div ng-show="filteredcompanies.length == 0">
				No Ledger by that name exists<br/>
				Enter full name of Ledger and click button to add it.
				<button  ng-click="addLedger()">Open New Ledger</button> 
				{{addingLedger}}
			</div>

			<div>
				<button ng-click="generateAllLedgers()">Generate All Ledgers</button>
				<span ng-show="generating > 0">{{generating}}</span>
			</div>

			<ul>
				<li ng-repeat="company in filteredcompanies = ( companies | filter:searchFunction)">
					<a ng-click="selectCompany(company)">{{company.Business.name}}</a> 
					<a ng-href="/bank/generateCompanyLedger/{{company.Business.id}}">pdf</a>
				</li>
			</ul>

		</div>

		<div ng-show="selectedCompany" style="float:left; width:50%; border: 1px solid black; background-color: antiquewhite;">
			<button style="float:right;" ng-click="selectedCompany = null">x</button>
			<h3>{{selectedCompany.Business.name}}</h3>

			<table width="100%">
				<tr>
					<th>Member</th>
					<th>Position</th>
					<th>Gold Limit</th>
					<th>Commodities Limit</th>
				</tr>
				<tr ng-repeat="partner in selectedCompany.BusinessPartner" ng-show="editingPartner != partner">
					<td>{{partner.Character.name}}</td>
					<td>{{partner.position}}</td>
					<td style="text-align:right">{{limitify(partner.gold_limit)}}</td>
					<td style="text-align:right">{{limitify(partner.commodities_limit)}}</td>
					<td ng-show="editingPartner == null"><button ng-click="editPartner(partner)">Edit</button></td>
				</tr>
				<tr ng-repeat="partner in selectedCompany.BusinessPartner" ng-show="editingPartner == partner">
					<td>{{partner.Character.name}}</td>
					<td><input ng-model="partner.position"/></td>
					<td><input ng-model="partner.gold_limit"/></td>
					<td><input ng-model="partner.commodities_limit"/></td>
					<td>
						<button ng-click="editPartner(null)">cancel</button>
						<button ng-click="savePartner(partner)">save</button>
						<button ng-click="deletePartner(partner)">delete</button>
					</td>
				</tr>
			</table>
			<button ng-show="addingPartner == false" ng-click="addingPartner = true">+</button>
			
			<table ng-show="addingPartner == true">
				<tr><th>filter</th>
					<td><input ng-model="newpartner.name"/></td>
				<tr><th>select</th>
					<td><select ng-model="newpartner.character_id">
							<option ng-repeat="c in characters | filter:newpartner.name" value="{{c.characters.id}}">{{c.characters.name}} ({{c.characters.cardnumber}})</option>
						</select></td></tr>
				<tr><th>position</th>
					<td><input ng-model="newpartner.position"/></td></tr>
				<tr><th>gold limit</th>
					<td><input ng-model="newpartner.gold_limit"/></td></tr>
				<tr><th>commodities limit</th>
					<td><input ng-model="newpartner.commodities_limit"/></td></tr>
				<tr><th></th>
					<td>
						<button ng-click="addingPartner = false">cancel</button>
						<button ng-click="addPartner()">save</button>
					</td>
				</tr>
			</table>

			<div ng-show="editingPartner != null || addingPartner" style="text-align:center;">
				<b>Use: -1 for Deposit only and 0 for No Limits</b>
			</div>

			<div ng-show="editingPartner == null && addingPartner == false">
				<table>
					<tr><td>Deposit / Withdrawl by</td>
						<td>
							<select ng-model="depositby" ng-options="partner.Character as combined(partner.Character) for partner in selectedCompany.BusinessPartner">
								<option value="" disabled>-- choose character --</option>
							</select>
						</td></tr>
					<tr><td></td>
						<th>Deposit</th>
						<th>Withdrawal</th></tr>
					<tr><td>Gold</td>
						<td><input ng-model="gold_deposit"/></td>
						<td><input ng-model="gold_draw"/></td></tr>
					<tr><td>Luxuries</td>
						<td><input ng-model="lux_deposit"/></td>
						<td><input ng-model="lux_draw"/></td></tr>
					<tr><td>Durables</td>
						<td><input ng-model="dur_deposit"/></td>
						<td><input ng-model="dur_draw"/></td></tr>
					<tr><td>Consumables</td>
						<td><input ng-model="con_deposit"/></td>
						<td><input ng-model="con_draw"/></td></tr>
					<tr><td>Wearables</td>
						<td><input ng-model="wear_deposit"/></td>
						<td><input ng-model="wear_draw"/></td></tr>
				</table>
				<button ng-click="makedeposit()">Add Transaction</button>

				<table>
					<tr>
						<th>Partner</th>
						<th>Gold</th>
						<th>Luxuries</th>
						<th>Durables</th>
						<th>Consumables</th>
						<th>Wearables</th>
					</tr>

					<tr>
						<th>Total</th>
						<th align="right">{{gold_sum}}</th>
						<th align="right">{{lux_sum}}</th>
						<th align="right">{{dur_sum}}</th>
						<th align="right">{{con_sum}}</th>
						<th align="right">{{wear_sum}}</th>
					</tr>
					<tr ng-repeat="deposit in selectedCompanyDetails">
						<td>{{deposit.c.name}}</td>
						<td align="right">{{deposit.BusinessDeposit.Gold_total}}</td>
						<td align="right">{{deposit.BusinessDeposit.Luxury_total}}</td>
						<td align="right">{{deposit.BusinessDeposit.Durable_total}}</td>
						<td align="right">{{deposit.BusinessDeposit.Consumable_total}}</td>
						<td align="right">{{deposit.BusinessDeposit.Wearable_total}}</td>
					</tr>
				</table>

				<div>
					<ul>
						<li ng-repeat="imps in selectedCompany.SettlementImprovement">{{imps.name}} is a Rank {{imps.rank}} {{imps.Improvement.name}} ({{imps.commodity}}) in {{imps.Settlement.name}} of {{imps.Land.name}}</li>
					</ul>
				</div>
			</div>

		</div>
	</div>
</div>
</div>