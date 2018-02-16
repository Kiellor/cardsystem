<style>
	.vote table { border-collapse: collapse; border: 1px solid black; }
	.vote th { border: 1px solid black; padding: 3px; vertical-align: top;}
	.vote td { border: 1px solid black; padding: 3px; vertical-align: top;}
</style>
<div id="warActions" ng-app="warActionsApp">

	<script type="text/javascript">

		var warActionsApp = angular.module('warActionsApp',[]);

		warActionsApp.controller('WarActionsController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {
				$scope.loadCharacters();
			}

			$scope.loadCharacters = function() {
				$http({ method: 'GET', url: '/personal_action/loadcharacters/'}).success(function(data) {
					$scope.characters = data;
				});
			}

						
			// $scope.submitVotes = function(character) {
			// 	$http({ 
			// 		method: 'POST', 
			// 		headers: { 'Content-Type': 'application/json' }, 
			// 		url: '/personal_action/saveVotes/', 
			// 		data: character
			// 	}).success(function(data) {
			// 		$scope.debug = data;
			// 		$scope.loadCharacters();
			// 	});
			// }

			// $scope.run = function(c) {
			// 	c.War.run = 1;
			// 	c.War.hide = 0;
			// 	c.War.fight = 0;
			// 	$scope.submitVotes(c);
			// }

			// $scope.hide = function(c) {
			// 	c.War.run = 0;
			// 	c.War.hide = 1;
			// 	c.War.fight = 0;
			// 	$scope.submitVotes(c);
			// }

			// $scope.fight = function(c) {
			// 	c.War.run = 0;
			// 	c.War.hide = 0;
			// 	c.War.fight = 1;
			// 	$scope.submitVotes(c);
			// }

		}]);
	</script>

	<div ng-controller="WarActionsController" ng-init="initialize()">
		<h2>Land System / Between Game Actions</h2>

		<h3>Voting is now closed</h3>
		<b>Thank you for participating in this special round of the Land System</b>

		<table width="700px;" class="vote">
			<tr>
				<td style="border:none;"></td>
				<td style="border:none;" colspan="3">The world was been turned upside down.  You made your choice.  Now it is time to wait.</td>
			</tr>
			<tr>
				<td style="border:none;"></td><th>Run</th><th>Hide</th><th>Fight</th>
			</tr>
			<tr>
				<td style="border:none;"></td>
				<td>You gather as many people as possible and try to evacuate them to safer locations, away from the fighting, away from the Settlements. You keep moving with minimal supplies to stay fast and mobile. Just keep moving and maybe you'll survive.</td>
				<td>You find somewhere you think is safe. You huddle alone or in small groups in basements, caves, hidden wooden areas - anywhere the fighting won’t be. You bring supplies, valuables, and things that need protecting, and hope that you are overlooked.</td>
				<td>You decide to stand your ground and fight to the death if necessary. Joining the ranks of the military, the able-bodied, the brave and the foolish. You face down everything that is coming and try to fight for what is yours.</td>
			</tr>
		<tr ng-repeat="c in characters">
			<th style="white-space:nowrap; border:none; vertical-align:middle;">
				{{c.Character.name}}
				<span ng-hide="c.War.id">did not choose:</span>
			</th>

			<th>
				<img src="/images/KR_run_200.gif" ng-show="c.War.run == 1"/>
				<img src="/images/KR_run_G200.png" ng-hide="c.War.run == 1"/>
			</th>
			<th>
				<img src="/images/KR_hide_200.gif" ng-show="c.War.hide == 1"/>
				<img src="/images/KR_hide_G200.png" ng-hide="c.War.hide == 1"/>
			</th>
			<th>
				<img src="/images/KR_fight_200.gif" ng-show="c.War.fight == 1"/>
				<img src="/images/KR_fight_G200.png" ng-hide="c.War.fight == 1"/>
			</th>
						
		</tr>
	</table>

	</div>
</div>