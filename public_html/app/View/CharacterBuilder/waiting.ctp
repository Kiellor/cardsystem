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

			$scope.saveAlias = function(alias) {
				var aliasRef = new Firebase('https://character-builder-2.firebaseio.com/presence/users/' + $scope.userid + '/alias');

				if(alias != null) {
					aliasRef.set(alias);
				}

				aliasRef.on('value', function(snapshot) {
					$scope.nickname = snapshot.val();
					$scope.getUserInformation();
				})
			}

			$scope.getUserInformation = function() {
				var here = 'card_marshal_waiting';

				var amOnline = new Firebase('https://character-builder-2.firebaseio.com/.info/connected');
				var locationRef = new Firebase('https://character-builder-2.firebaseio.com/locations/' + here);
				locationRef.child('name').set('Lounge');
				locationRef.child('url').set('/character_builder/waiting/');
				var userRef = locationRef.child('users').child($scope.userid);

				var alias = $scope.nickname || "Needs Alias";

				amOnline.on('value', function(snapshot) {
				  if (snapshot.val()) {
				    userRef.onDisconnect().remove();
				    userRef.set({'time':Firebase.ServerValue.TIMESTAMP, 'alias':alias});
				  }
				});
			}

			$scope.removeEmptyRoom = function(child) {
				// var here = 'card_marshal_waiting';
				// var locationRef = new Firebase('https://character-builder-2.firebaseio.com/locations/');
				// locationRef.child(child).remove();
			}

			$scope.initialize = function() {
				$scope.useremail = "<?php echo AuthComponent::user('username') ?>";
				var userid = $scope.useremail.replace(".","_");
				userid = userid.replace("#","_");
				userid = userid.replace("$","_");
				userid = userid.replace("[","_");
				$scope.userid = userid.replace("]","_");

				$scope.getUserInformation();
				$scope.saveAlias();

				$scope.rooms = FireArray("locations", null);
				$scope.roomsUnwatch = $scope.rooms.$watch(function() {
					$scope.roomsUpdated();
				});

				$scope.chatMessages = FireArray("presence","chat");
				$scope.chatMessagesUnwatch = $scope.chatMessages.$watch(function() {
					$scope.chatUpdated();
				});

				var timestamp = new Date();
				timestamp.setDate(timestamp.getDate()-2);
				// console.log(timestamp.valueOf());
				// $scope.chatMessages.$ref().orderByChild('timestamp').endAt('timestamp',''+timestamp.valueOf()).on('child_added', function(snap) {
				// 	snap.ref().remove();
				// });
			}

			$scope.roomsUpdated = function() {
				//console.log(JSON.stringify($scope.rooms));
			}

			$scope.chatUpdated = function() {
			}

			$scope.addMessage = function() {
				$scope.chatMessages.$add({ 
					name: $scope.nickname,
					message: $scope.message,
					timestamp: Firebase.ServerValue.TIMESTAMP
				});
				$scope.message = "";
				$scope.saveAlias($scope.nickname);
			}
		}]);
	</script>


	<div ng-controller="NPMController" ng-init="initialize()">

		<table style="width:100%">
			<tr>
				<td style="vertical-align:top; margin-right:20px; width:80%">
					<h2>Waiting Room Chat</h2>
					<div ng-hide="printing == true" auto-scroll="chatMessages" style="height: 300px; overflow: auto; overflow-x: hidden;">
						<div ng-repeat="c in chatMessages">{{c.name}}: {{c.message}}</div>
					</div>
					<div>
						<input type="text" size="10" ng-model="nickname" placeholder="name"></input> 
						<input type="text" size="40" ng-model="message" placeholder="message (press enter to submit)" ng-keydown="$event.which === 13 && addMessage()"></input>
					</div>
				</td>

				<td style="vertical-align:top;">
					<h2>Users Online</h2>
					<div ng-repeat="r in rooms" ng-show="r.users">
						<h4><a ng-href="{{r.url}}">{{r.name}}</a></h4>
						<div ng-repeat="u in r.users">{{u.alias}}</div>
					</div>
				</td>
			</tr>
		</table>

	</div>

</div>

