<div id="addCharacter" ng-app="addCharacterApp">

	<script type="text/javascript">

		var addCharacterApp = angular.module('addCharacterApp',[]);

		addCharacterApp.controller('AddCharacterController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {
				$scope.loadPlayers();
				$scope.loadCsets();
			}

			$scope.loadCsets = function() {
				$http({ method: 'GET', url: '/characters/getCsets'}).success(function(data) {
					$scope.csets = data;

					for(i = 0; i < $scope.csets.length; i++) {
						if($scope.csets[i].Cset.id == 1) {
							$scope.selectedCset = $scope.csets[i];
						}
					}
				});
			}

			$scope.loadPlayers = function() {
				$http({ method: 'GET', url: '/characters/loadPlayers'}).success(function(data) {
					$scope.players = data;

					<?php if(isset($player_id)) { ?>
						$scope.selectedPlayerId = <?php echo $player_id ?>;
					<?php } ?>

					for(i = 0; i < $scope.players.length; i++) {
						if($scope.players[i].Player.cardnumber_prefix == $scope.selectedPlayerId) {
							$scope.selectPlayer($scope.players[i]);
						}
					}

				});
			}			

			$scope.cancel = function() {
				$scope.selectedPlayer = null;
				$scope.selectedPlayerId = null;
				$scope.newCharacter = {};
			}

			$scope.selectPlayer = function(player) {
				$scope.selectedPlayer = player;
				$scope.selectedPlayerId = player.Player.cardnumber_prefix;

				$scope.newCharacter = {};
				$scope.newCharacter.player_id = player.Player.id;
				$scope.newCharacter.past_event_count = 0;
				$scope.newCharacter.cardnumber = player.Player.cardnumber_prefix;
			}

			$scope.addCharacter = function() {

				$scope.newCharacter.cset_id = $scope.selectedCset.Cset.id;

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/characters/actualAddCharacter', 
					data: $scope.newCharacter
				}).success(function(data) {
					$scope.debug = data;
					$scope.saveResults = "Saved";

					$scope.loadPlayers();
				}).error(function(data) {
					$scope.debug = data;
					$scope.saveResults = "Error Saving";
				});
			}

		}]);
	</script>

	<div ng-controller="AddCharacterController" ng-init="initialize()">
		
		<div ng-hide="selectedPlayer">
			<h3>Select Player</h3>
			<input type="text" ng-model="searchText"/>
			<ul>
				<li ng-repeat="p in players | filter:searchText"><a ng-click="selectPlayer(p)">{{p.Player.name}} ({{p.Player.cardnumber_prefix}})</a></li>
			</ul>
		</div>

		<div ng-show="selectedPlayer">
			<h3>New Character for {{selectedPlayer.Player.name}} ({{selectedPlayer.Player.cardnumber_prefix}})</h3>

			<table>
				<tr>
					<td>Name:</td>
					<td><input type="text" ng-model="newCharacter.name"/></td>
				</tr>
				<tr>
					<td>Character Set:</td>
					<td><select ng-model="selectedCset" ng-options="c.Cset.name for c in csets"/></td>
				</tr>
				<tr>
					<td>Cardnumber:</td>
					<td><input type="text" ng-model="newCharacter.cardnumber"/></td>
				</tr>
				<tr>
					<td>Events Attended:</td>
					<td><input type="text" ng-model="newCharacter.past_event_count"/></td>
				</tr>
			</table>

			<button ng-click="cancel()">Cancel</button> <button ng-click="addCharacter()">Create Character</button> {{saveResults}}

			<h3>Existing Characters</h3>
			Reuse the base card number and add lower case letters for additional cards
			<ul>
				<li ng-repeat="c in selectedPlayer.Character | orderBy:'cardnumber'"><a ng-href="/characters/view/{{c.cardnumber}}">{{c.cardnumber}} -- {{c.name}}</a><span ng-show="c.new_character == 1"> -- NEW</span></li>
			</ul>
		</div>

	</div>
</div>