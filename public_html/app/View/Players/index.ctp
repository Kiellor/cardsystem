<div ng-app="playerApp">
	
	<script type="text/javascript">

		var playerApp = angular.module('playerApp',[]);
		
		playerApp.controller('PlayerController',['$scope','$http', function($scope, $http) {
			
			$scope.players = [];
			$scope.csets = [];
			$scope.loaded = false;
			$scope.selectedcset = 1;

			$http({ method: 'GET', url: '/players/getCSets'}).success(function(data) {
				$scope.csets = data;
				$scope.cset = $scope.csets[0];
			});
			
			$scope.loadPlayers = function() {	
				$http({ method: 'GET', url: '/players/getPlayers/'+$scope.selectedcset}).success(function(data) {
					$scope.players = data;
					$scope.count = $scope.players.length;
					$scope.loaded = true;
				});
			}

			$scope.csetChange = function() {
				$scope.selectedcset = $scope.cset.Cset.id;
				$scope.loadPlayers();
			}

			$scope.initialize = function() {
				$scope.players = angular.fromJson(<?php echo $players; ?>)
				$scope.count = $scope.players.length;
				$scope.loaded = true;

				$scope.search = "";
				$scope.charsearch = "";
			}
			
			$scope.searchFunction = function( item ) {

				if($scope.search.length == 0 && $scope.charsearch.length == 0) {
					return true;
				}

				if($scope.search.length > 0 && item.Player.name.toLowerCase().indexOf($scope.search.toLowerCase()) != -1) {
					return true;
				}

				if($scope.charsearch.length > 0) {
					for(var i = 0; i < item.Characters.length; i++) {
						if($scope.searchFunctionCharacter(item.Characters[i]) == true) {
							return true;
						}
					}
				}

				return false;
			}

			$scope.searchFunctionCharacter = function( item ) {
				if($scope.charsearch.length == 0) {
					return true;
				}

				if($scope.charsearch.length > 0) {	
					if(item.cardnumber.toString() == $scope.charsearch) {
						return true;
					}

					if(item.name.toLowerCase().indexOf($scope.charsearch.toLowerCase()) != -1) {
						return true;
					}
				}

				return false;
			}

			$scope.showPlayer = function(player) {
				if($scope.newcharacters) {
					for(var i = 0; i < player.Characters.length; i++) {
						if(player.Characters[i].new_character == 1) {
							return true;
						}
					}

					return false;
				}

				return true;
			}

			$scope.showCharacter = function(character) {
				if($scope.newcharacters) {
					if(character.new_character == 1) {
						return true;
					}

					return false;
				}

				return true;
			}

		}]);
	</script>


	<div ng-controller="PlayerController" ng-init="initialize()">
	
		<h1>Players and Characters</h1>

		<div id="loading" ng-show="loaded == false">
			Loading...
		</div>

		<div id="csetchoice" ng-show="loaded == true">
			<table>
				<tr><td>Character Set: </td><td><select ng-model="cset" ng-options="c.Cset.name for c in csets" ng-change="csetChange()"></select></td></tr>
				<tr><td>Search by Player:</td><td><input type="text" ng-model="search" placeholder="player name" size="60"></td></tr>
				<tr><td>Search by Character:</td><td><input type="text" ng-model="charsearch" placeholder="character name, card number" size="60"></td></tr>
				<tr><td>New Characters Only:</td><td><input type="checkbox" ng-model="newcharacters"/></td></tr>
				<tr><td>Result Size: <span>{{filteredplayers.length}}</span></td><td></td></tr>
			</table>

			<br/>

			<div id="listing">				
				<span ng-repeat="player in filteredplayers = (players | filter:searchFunction) | limitTo:10" ng-show="showPlayer(player)">
					<a ng-href="/players/view/{{player.Player.id}}">{{player.Player.name}}</a>
					<ul>
						<li ng-repeat="character in player.Characters | filter:searchFunctionCharacter" ng-show="showCharacter(character)">
							<span ng-show="character.new_character == 1">NEW -- </span>
							<a ng-href="/characters/view/{{character.cardnumber}}">{{character.name}} ({{character.cardnumber}})  View</a> | <a ng-href="/cards/page1/{{character.cardnumber}}">Enter Card Data</a>
						</li>
					</ul>
				</span>
				<span ng-show="filteredplayers.length > 10">more...</span>
			</div>
		</div>

		<div id="newplayer" ng-show="loaded == true && filteredplayers.length == 0">
			<h1>Add Player</h1>

			<form action="/players/" id="PlayerIndexForm" method="post" accept-charset="utf-8" class="ng-pristine ng-valid">
				<input type="hidden" name="_method" value="POST">
				Player Name: <input name="data[Player][name]" maxlength="50" type="text" id="PlayerName" placeholder="last name, first name">
				<input type="submit" value="Save Player">
			</form>


		</div>

	</div>
</div>
