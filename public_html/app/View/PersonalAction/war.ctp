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

						
			$scope.submitVotes = function(character) {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/personal_action/saveVotes/', 
					data: character
				}).success(function(data) {
					$scope.debug = data;
					$scope.loadCharacters();
				});
			}

			$scope.run = function(c) {
				c.War.run = 1;
				c.War.hide = 0;
				c.War.fight = 0;
				$scope.submitVotes(c);
			}

			$scope.hide = function(c) {
				c.War.run = 0;
				c.War.hide = 1;
				c.War.fight = 0;
				$scope.submitVotes(c);
			}

			$scope.fight = function(c) {
				c.War.run = 0;
				c.War.hide = 0;
				c.War.fight = 1;
				$scope.submitVotes(c);
			}

		}]);
	</script>

	<div ng-controller="WarActionsController" ng-init="initialize()">
		<h2>Land System / Between Game Actions</h2>
		<table width="700px;" class="vote">
			<tr>
				<td style="border:none;"></td>
				<td style="border:none;" colspan="3">The world has been turned upside down.  Life will not return to normal for a while.  Instead you have a simple choice.  Will you run, hide or fight?  Choose wisely, many lives depend on your choice.</td>
			</tr>
			<tr>
				<td style="border:none;"></td><th>Run</th><th>Hide</th><th>Fight</th>
			</tr>
			<tr>
				<td style="border:none;"></td>

				<td>Now that the initial flight is past it is time to find as many survivors as possible, gather them together, move them to the main group where they will be safe.  This involves running out into the wild, dangerous, newly unknown areas, avoiding husks and nulls and finding the people.  Then running them back to the main group.  So much running.</td>

				<td>Hiding is no longer really an option -- not for the people.  So you secure as many essentials as you can and hide anything non-essential in the hope it will be overlooked.  Then you get as many people to safety as you can.  With supplies in hand you move ahead with the throng, staying out of sight and away from the fighting when it breaks out.</td>

				<td>This might work.  The Barony has a plan and the people are responding.  Our enemies are not stopping but neither will we.  With careful planning Travance has won some of these battles and saved many lives.  Protecting the flanks, breaking through at the front and defending the rear Travance will keep the people alive.  The crowds of people do not move quickly, but they can be protected.</td>
			</tr>
		<tr ng-repeat="c in characters">
			<th style="white-space:nowrap; border:none;">
				{{c.Character.name}}
				<span ng-hide="c.War.id">choose:</span>
			</th>

			<th>
				<span ng-show="c.War.run == 1">Run</span>
				<button ng-click="run(c)">+</button>
			</th>
			<th>
				<span ng-show="c.War.hide == 1">Hide</span>
				<button ng-click="hide(c)">+</button> 
			</th>
			<th>
				<span ng-show="c.War.fight == 1">Fight</span>
				<button ng-click="fight(c)">+</button> 
			</th>
						
		</tr>
	</table>

	</div>
</div>