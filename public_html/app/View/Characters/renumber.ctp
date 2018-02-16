<div id="renumberCharacter" ng-app="renumberCharacterApp">

	<script type="text/javascript">

		var renumberCharacterApp = angular.module('renumberCharacterApp',[]);

		renumberCharacterApp.controller('RenumberCharacterController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {
				$scope.loadData();

				$scope.alternateRenumber = false;
				$scope.automatic = false;
			}

			$scope.loadData = function() {
				$http({ method: 'GET', url: '/characters/getRenumber'}).success(function(data) {
					$scope.renumber = data;

					if($scope.renumber.length == 0) {
						$scope.alternateRenumber = true;
						if($scope.lower != 0 && $scope.upper !=0) {
							$scope.getRange();
						}
					}
					$scope.selectedEntry = null;
					$scope.selectedCharacter = null;
					$scope.saveResults = "";
				});
			}

			$scope.getRange = function() {
				$http({ method: 'GET', url: '/characters/getRange/'+$scope.lower+'/'+$scope.upper}).success(function(data) {
					$scope.renumber = data;

					if($scope.renumber.length == 0) {
						$scope.alternateRenumber = true;
					}
					$scope.selectedEntry = null;
					$scope.selectedCharacter = null;
					$scope.saveResults = "";
				});
			}

			$scope.selectEntry = function(choice) {
				$scope.selectedEntry = choice;

				$http({ method: 'GET', url: '/characters/loadPlayer/'+choice.p.id}).success(function(data) {
					$scope.player = data;

					if($scope.automatic == true) {
						if($scope.player.Player.active == 1) {
							$scope.getNextPlayerNumber();
						} else {
							$scope.renumberAll();
							$scope.renumberCharacter();
						}
					}
				});
			}

			$scope.selectCard = function(character) {
				$scope.selectedCharacter = character;
			}

			$scope.cancel = function() {
				$scope.selectedEntry = null;
				$scope.selectedCharacter = null;
			}

			$scope.renumberCharacter = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/characters/renumberCharacters', 
					data: $scope.player
				}).success(function(data) {
					$scope.debug = data;
					$scope.saveResults = "Saved";

					$scope.loadData();
				}).error(function(data) {
					$scope.debug = data;
					$scope.saveResults = "Error Saving";
				});
			}

			$scope.getNextPlayerNumber = function() {
				$http({ method: 'GET', url: '/players/getNextCardPrefix'}).success(function(data) {
					$scope.player.Player.cardnumber_prefix = data;
					$scope.renumberAll();
				});
			}

			$scope.renumberAll = function() {

				// Renumber in order of current numbers to match Player Number with letter suffixes

				var suffixes = [
					"","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",
					"aa","ab","ac","ad","ae","af","ag","ah","ai","aj","ak","al","am","an","ao","ap","aq","ar","as","at","au","av","aw","ax","ay","az",
					"ba","bb","bc","bd","be","bf","bg","bh","bi","bj","bk","bl","bm","bn","bo","bp","bq","br","bs","bt","bu","bv","bw","bx","by","bz"];
				var suffixPos = 0;

				var playernumber = parseInt($scope.player.Player.cardnumber_prefix);

				for(i = 0; i < $scope.player.Character.length; i++) {
					var cardnum = parseInt(Number($scope.player.Character[i].cardnumber.replace(/[a-z]/gi,'')),10);
					if(cardnum == playernumber) {
						suffixPos++;
					} else if(cardnum != playernumber) {
						$scope.player.Character[i].cardnumber = $scope.player.Player.cardnumber_prefix + suffixes[suffixPos];
						suffixPos++;
					}
				}
			}

		}]);
	</script>

	<div ng-controller="RenumberCharacterController" ng-init="initialize()">

		<div ng-show="alternateRenumber">
			Load Cards between card number <input type="text" ng-model="lower"/> and <input type="text" ng-model="upper"/> <button ng-click="getRange()">Load</button>
		</div>

		<div ng-hide="selectedEntry">
			<table>
				<tr ng-repeat="r in renumber">
					<td>
						<a ng-click="selectEntry(r)">{{r.p.name}}</a>
					</td>
					<td>{{r.p.cardnumber_prefix}}</td>
					<td>{{r.c.cardnumber}}</td>
					<td>{{r.c.name}}</td>
					<td ng-show="r.p.active == 0">Inactive</td>
				</tr>
			</table>
		</div>

		<div ng-show="selectedEntry">
			<h3>Renumber Characters for <a ng-href="/players/view/{{player.Player.id}}">{{player.Player.name}} ({{player.Player.cardnumber_prefix}})</a></h3>

			<h4 ng-show="player.Player.active == 0">Player is not active</h4>

			New Player Number: <input type="text" ng-model="player.Player.cardnumber_prefix"/> 

			<br/>
			<button ng-click="getNextPlayerNumber()">Assign Lowest Available Player Number</button>
			<br/>
			<button ng-click="renumberAll()">Renumber Listed Characters</button>

			<h3>Existing Characters</h3>
			Reuse the base card number and add lower case letters for additional cards
			<ul>
				<li ng-repeat="c in player.Character | orderBy:'cardnumber'"><a ng-click="selectCard(c)">{{c.cardnumber}} -- {{c.name}}</a><span ng-show="c.new_character == 1"> -- NEW</span><span ng-show="c.cset_id != 1"> -- CSet = {{c.cset_id}}</span></li>
			</ul>

			<table>
				<tr>
					<td>Name:</td>
					<td>{{selectedCharacter.name}}</td>
				</tr>
				<tr>
					<td>Cardnumber:</td>
					<td><input type="text" ng-model="selectedCharacter.cardnumber"/></td>
				</tr>
			</table>

			<button ng-click="cancel()">Cancel</button> <button ng-click="renumberCharacter()">Save All Changes</button> {{saveResults}}

			
		</div>

	</div>
</div>