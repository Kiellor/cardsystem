<style>
	pre { margin: 0px; }

	.skilltable { border-collapse: collapse; }
	.skilltable th { border: 1px solid black; padding: 3px; vertical-align: top;}
	.skilltable td { border: 1px solid black; padding: 3px; vertical-align: top;}
</style>

<div ng-app="myApp">

	<script src="https://cdn.firebase.com/js/client/2.2.4/firebase.js"></script>
	<script src="https://cdn.firebase.com/libs/angularfire/1.2.0/angularfire.min.js"></script>
	<script src="//cdn.jsdelivr.net/underscorejs/1.5.2/underscore-min.js" data-semver="1.5.2" data-require="underscore.js@*"></script>
  
	<script type="text/javascript">
		var myApp = angular.module('myApp',["firebase"]);

		myApp.factory("FireObject", ["$firebaseObject", 
			function($firebaseObject) {
				return function(nested) {
					if(typeof nested === 'string') {
						var ref = new Firebase("https://shining-torch-9307.firebaseio.com/"+nested);
						return $firebaseObject(ref.child(nested));
					} else {
						var path = nested.join("/");
						var ref = new Firebase("https://shining-torch-9307.firebaseio.com/"+path);
						return $firebaseObject(ref);
					}
				}
			}
		]);

		myApp.factory("FireArray", ["$firebaseArray", 
			function($firebaseArray) {
				return function(nested, sortkey) {

					var ref;
					if(typeof nested === 'string') {
						ref = new Firebase("https://shining-torch-9307.firebaseio.com/"+nested);
					} else {
						var path = nested.join("/");
						ref = new Firebase("https://shining-torch-9307.firebaseio.com/"+path);
					}

					if(sortkey != null) {
						return $firebaseArray(ref.orderByChild(sortkey));
					} else {
						return $firebaseArray(ref);
					}
				}
			}
		]);

		myApp.controller('NPMController',['$scope','$http','FireObject','FireArray', function($scope, $http, FireObject, FireArray) {

			$scope.initialize = function() {
				$scope.loaded = false;

				$scope.characters = FireArray("characters",null);
				$scope.charactersUnwatch = $scope.characters.$watch(function() {
					$scope.charactersUpdated();
				});
			}

			$scope.charactersUpdated = function() {
				$scope.loaded = true;
			}

			$scope.migrateAll = function() {

			}

			$scope.migrateCharacter = function(token) {

				var character = JSON.parse(JSON.stringify(token));

				var datastore = FireObject(["datastore",token.$id]);
				var newabilities = FireObject(["savedabilities",token.$id]);
				
				datastore.$loaded(function() {
					if(character.meta.hasOwnProperty('abilities')) {
						datastore.abilities = character.meta.abilities;
					}
					if(character.meta.hasOwnProperty('lowerlists')) {
						datastore.lowerlists = character.meta.lowerlists;
					}
					if(character.meta.hasOwnProperty('otherabilities')) {
						datastore.otherabilities = character.meta.otherabilities;
					}
					
					datastore.$save();
				});

				newabilities.$loaded(function() {
					$scope.debug = character.abilities;
					newabilities.abilities = character.abilities;
					newabilities.$save();
				});

				if(character.meta.hasOwnProperty('profession')) {
					token.character.profession = character.meta.profession;
				}
				
				token.abilities = null;
				token.meta.abilities = null;
				token.meta.lowerlists = null;
				token.meta.otherabilities = null;
				token.meta.professions = null;

				$scope.characters.$save(token);
			}

		}]);
	</script>


	<div ng-controller="NPMController" ng-init="initialize()">

		<table class="skilltable">
			<tr>
				<th>Token</th>
				<th>Player</th>
				<th>Character Name</th>
				<th>Submitted</th>
				<th>Imported As</th>
				<th>Actions</th>
				<th>Last Edited</th>
			</tr>
			<tr ng-repeat="c in characters | orderBy:'-meta.lastEdited'">
				<td><a ng-href="/character_builder/index/{{c.$id}}">{{c.$id}}</a></td>
				<td>{{c.character.playerName}}</td>
				<td><a ng-href="/character_builder/index/{{c.$id}}">{{c.character.name}}</a></td>
				<th>{{c.meta.emailsent}}</th>
				<th><a ng-href="/characters/view/{{c.character.cardnumber}}">{{c.character.cardnumber}}</a></th>
				<td><button ng-click="migrateCharacter(c)">migrate</button></td>
				<td>{{c.meta.lastEdited | date:'yyyy-MM-dd'}}</td>
			</tr>
		</table>

	</div>

</div>

