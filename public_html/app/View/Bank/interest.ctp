<div id="bank" ng-app="bankApp">

<script type="text/javascript">

		var bankApp = angular.module('bankApp',[]);

		bankApp.controller('BankController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function(data) {
				$scope.loadCharacters();
				$scope.interestDeposits = new Array();
			};

			$scope.loadCharacters = function() {
				$http({ method: 'GET', url: '/bank/loadCharacters/'}).success(function(data) {
					$scope.characters = data;

					$scope.computeInterest();
				});
			}

			$scope.computeInterest = function() {
				for(var i = 0; i < $scope.characters.length; i++) {
					var c = $scope.characters[i];

					c.interest = Math.floor(c.Character.bank_balance / 25) / 10;
					c.new_balance = parseFloat(c.Character.bank_balance) + parseFloat(c.interest);

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
		}]);
</script>

<div ng-controller="BankController" ng-init="initialize()">

	<div ng-show="saved">Saved</div>
	<button ng-click="saveInterest()">Save Computed Interest for this Event</button>

	<table>
		<tr>
			<th>Character</th>
			<th>Current Deposits</th>
			<th>Interest to be Paid</th>
			<th>Ending Balance</th>
		</tr>
		<tr ng-repeat="c in characters">
			<td style="text-align:left"><a href="/characters/view/{{c.c.cardnumber}}">{{c.c.name}} ({{c.c.cardnumber}})</a></td>
			<td style="text-align:right">{{c.Character.bank_balance | number : 2}}</td>
			<td style="text-align:right">{{c.interest | number : 2}}</td>
			<td style="text-align:right">{{c.new_balance | number : 2}}</td>
		</tr>
	</table>

</div>
</div>