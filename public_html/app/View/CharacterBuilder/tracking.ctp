<style>
	pre { margin: 0px; }

	.skilltable { border-collapse: collapse; }
	.skilltable th { border: 1px solid black; padding: 3px; vertical-align: top;}
	.skilltable td { border: 1px solid black; padding: 3px; vertical-align: top;}
</style>

<div ng-app="myApp">

	<script src="https://www.gstatic.com/firebasejs/3.6.6/firebase.js"></script>
	<script>
	  // Initialize Firebase
	  var config = {
	    apiKey: "AIzaSyBsywspyiiJkulTDppYl65mVpaecKruizw",
	    authDomain: "character-builder-2.firebaseapp.com",
	    databaseURL: "https://character-builder-2.firebaseio.com",
	    storageBucket: "character-builder-2.appspot.com",
	    messagingSenderId: "121295736981"
	  };
	  firebase.initializeApp(config);
	</script>

	<script src="https://cdn.firebase.com/js/client/2.2.4/firebase.js"></script>
	<script src="https://cdn.firebase.com/libs/angularfire/1.2.0/angularfire.min.js"></script>
	<script src="//cdn.jsdelivr.net/underscorejs/1.5.2/underscore-min.js" data-semver="1.5.2" data-require="underscore.js@*"></script>
  
	<script type="text/javascript">
		var myApp = angular.module('myApp',["firebase"]);

		myApp.factory("FireObject", ["$firebaseObject", 
			function($firebaseObject) {
				return function(childname) {
					var ref = new Firebase("https://character-builder-2.firebaseio.com/");
					var refchild = ref.child(childname);

					return $firebaseObject(refchild);
				}
			}
		]);

		myApp.factory("FireArray", ["$firebaseArray", 
			function($firebaseArray) {
				return function(childname, subchild) {
					var ref = new Firebase("https://character-builder-2.firebaseio.com/");
					var refchild = ref.child(childname);

					if(subchild == null) {
						return $firebaseArray(refchild);
					} else {
						return $firebaseArray(refchild.child(subchild));
					}
				}
			}
		]);

		myApp.directive('autoScroll', function() {
			return {
				scope: {
					autoScroll: "="
				},
				link: function (scope, element) {
					scope.$watchCollection('autoScroll', function(newValue) {
						if(newValue) {
							$(element).scrollTop($(element)[0].scrollHeight);
						}
					});
				}
			}
		});

		myApp.controller('NPMController',['$scope','$http','FireObject','FireArray', function($scope, $http, FireObject, FireArray) {

			$scope.initialize = function() {
				$scope.rooms = FireArray("locations", null);
				$scope.roomsUnwatch = $scope.rooms.$watch(function() {
					$scope.roomsUpdated();
				});

				$scope.characters = FireArray("characters",null);
				$scope.charactersUnwatch = $scope.characters.$watch(function() {
					$scope.charactersUpdated();
				});
			}

			$scope.roomsUpdated = function() {
			}

			$scope.charactersUpdated = function() {
			}

			$scope.removeCharacter = function(c) {
				console.log(JSON.stringify(c));
				$scope.characters.$remove(c);
			}

			$scope.removeRoom = function(r) {
				console.log(JSON.stringify(r));
				$scope.rooms.$remove(r);
			}
		}]);
	</script>


	<div ng-controller="NPMController" ng-init="initialize()">

		<h4>Note: delete button -- removes the token, and all of its date,  from the character builder system.  It will not delete actual cards from the card system.</h4>

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
			<tr ng-repeat="c in characters | orderBy:'meta.lastEdited'" ng-show="c.meta.emailsent == true">
				<td><a ng-href="/character_builder/index/{{c.$id}}">{{c.$id}}</a></td>
				<td>{{c.character.playerName}}</td>
				<td><a ng-href="/character_builder/index/{{c.$id}}">{{c.character.name}}</a></td>
				<th>{{c.meta.emailsent}}</th>
				<th><a ng-href="/characters/view/{{c.character.cardnumber}}">{{c.character.cardnumber}}</a></th>
				<td><button ng-click="removeCharacter(c)">delete</button></td>
				<td>{{c.meta.lastEdited | date:'yyyy-MM-dd'}}</td>
			</tr>
			<tr>
				<td colspan="7"><hr/></td>
			</tr>
			<tr ng-repeat="c in characters | orderBy:'meta.lastEdited'" ng-show="c.meta.emailsent == false">
				<td><a ng-href="/character_builder/index/{{c.$id}}">{{c.$id}}</a></td>
				<td>{{c.character.playerName}}</td>
				<td><a ng-href="/character_builder/index/{{c.$id}}">{{c.character.name}}</a></td>
				<th>{{c.meta.emailsent}}</th>
				<th><a ng-href="/characters/view/{{c.character.cardnumber}}">{{c.character.cardnumber}}</a></th>
				<td><button ng-click="removeCharacter(c)">delete</button></td>
				<td>{{c.meta.lastEdited | date:'yyyy-MM-dd'}}</td>
			</tr>
		</table>

	</div>

</div>

