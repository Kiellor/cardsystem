<div id="businessActions" ng-app="businessActionsApp">

	<script type="text/javascript">

		var businessActionsApp = angular.module('businessActionsApp',['chart.js']);

		businessActionsApp.controller('BusinessActionsController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {

				$http({ method: 'GET', url: '/bank/loadlands/'}).success(function(data) {
					$scope.lands = data;
				});

				$http({ method: 'GET', url: '/bank/loadimprovements/'}).success(function(data) {
					$scope.improvements = data;
				});

				$http({ method: 'GET', url: '/bank/loadCompanies/'}).success(function(data) {
					$scope.companies = data;
				});
			}

			$scope.chooseSettlement = function(settlement) {
				$scope.chosensettlement = settlement;
				$scope.loadSettlement(settlement);
			}

			$scope.loadSettlement = function(settlement) {
				$http({ method: 'GET', url: '/bank/loadsettlement/'+settlement.id}).success(function(data) {
					if(data != null) {
						$scope.settlement = data;
						$scope.settlement.loaded = true;
					} else {
						$scope.settlement = {'loaded': false};
					}
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

			$scope.clearController = function() {
				$scope.improvementSaveStatus = "Not Yet Saved";
				$scope.editController = false;

				$scope.selectedImprovement.business_id = 0;
				$scope.selectedImprovement.Business = null;
			}

			$scope.saveImprovement = function() {
				$scope.improvementSaveStatus = "Saving";

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/bank/saveimprovement/',
					data: $scope.selectedImprovement
				}).success(function(data) {
					$scope.debug = data;
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

	<div ng-controller="BusinessActionsController" ng-init="initialize()">

		<h3>Add Business to Settlement</h3>

		<table style="border-collapse: collapse; border: 1px solid black;">
			<tr style="border:solid black 1px;">
				<td ng-repeat="land in lands" style="vertical-align:top; border:solid black 1px;">
					{{land.Land.name}}
					<ul>
						<li ng-repeat="settlement in land.Settlement" ng-show="settlement.active">
							<a ng-click="chooseSettlement(settlement)">{{settlement.name}}</a>
						</li>
					</ul>
				</td>
			</tr>
		</table>

		<h3>{{chosenland.Land.name}} {{chosensettlement.name}}</h3>

		<select ng-model="proveToAdd" ng-options="prove.Improvement.name for prove in improvements">
		</select>
		<button ng-click="addImprovement()">Add Improvement</button>

		<hr/>

		<table class="actiontab" ng-hide="selectedImprovement">
			<tr>
				<th>Improvement</th>
				<th>Business</th>
				<th>Actions</th>
			</tr>
			<tr ng-repeat="si in settlement.SettlementImprovement" ng-show="si.Improvement.sort == 'Business'">
				<td>{{si.Improvement.name}}</td>
				<td>{{si.Business.name}}</td>
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
			</table>
			<button ng-click="selectedImprovement = null">Cancel Edit</button>
			<button ng-click="saveImprovement()">Save</button> {{improvementSaveStatus}}
		</div>

	</div>
</div>