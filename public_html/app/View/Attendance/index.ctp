<div id="attendance" ng-app="attendanceApp">

	<script type="text/javascript">

		var attendanceApp = angular.module('attendanceApp',[]);

		attendanceApp.controller('AttendanceController',['$scope','$http', function($scope,$http) {
		
			$scope.initialize = function() {
				$scope.loadPlayers();
				$scope.loadEvent();
				$scope.eventDetails();

				$scope.loadAttendees();

				$scope.mode = 'attend';
				$scope.wincount = 1;
			}

			$scope.loadPlayers = function() {
				$http({ method: 'GET', url: '/attendance/loadPlayers'}).success(function(data) {
					$scope.players = data;
				});
			}

			$scope.loadAttendees = function() {
				$http({ method: 'GET', url: '/attendance/loadAttendees'}).success(function(data) {
					$scope.attendees = data;
				});
			}

			$scope.doorprize = function() {
				$range = $scope.attendees.length;

				$scope.winner = Math.floor(Math.random() * $range, 0);

				if( !$scope.attendees[$scope.winner].hasOwnProperty('winner') ) {
					$scope.attendees[$scope.winner].winner = $scope.wincount;
					$scope.wincount++;
				}
			}

			$scope.loadEvent = function() {
				$http({ method: 'GET', url: '/attendance/loadEvent'}).success(function(data) {
					$scope.event = data;
				});
			}

			$scope.select = function(player) {
				$scope.selectedPlayer = player;
				$scope.eventDetails();

				if($scope.selectedPlayer != null) {
					$http({ method: 'GET', url: '/attendance/loadAttendance/'+$scope.selectedPlayer.Player.id}).success(function(data) {
						$scope.selectedPlayer.attendance = data;

						// Match up the actual attendance with the character array so that we can display which cards have already been given out.

						for(var c = 0; c < $scope.selectedPlayer.Character.length; c++) {
							$scope.selectedPlayer.Character[c].cardGiven = false; 
						}

						for(var i = 0; i < data.ca.length; i++) {
							for(var c = 0; c < $scope.selectedPlayer.Character.length; c++) {
								if($scope.selectedPlayer.Character[c].id == data.ca[i]['CharacterAttendance']['character_id']) {

									$scope.selectedPlayer.Character[c].cardGiven = true;
									if(data.ca[i]['CharacterAttendance']['card_returned'] == 1) {
										$scope.selectedPlayer.Character[c].cardReturned = true;
									}
								} 
							}
						}
					});
				}

				if(player == null) { 
					$scope.search = "";
				}
			}

			$scope.cardGivenOut = function(player, character) {

				if(character != null) {
					var payload = {'player': player, 'character': character};
				} else {
					var payload = {'player': player};
				}

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/attendance/cardGivenOut', 
					data: payload
				}).success(function(data) {
					$scope.debug = data;

					$scope.select($scope.selectedPlayer);
					$scope.loadAttendees();
				});
			}

			$scope.cardTurnedIn = function(player, character) {
				var payload = {'player': player, 'character': character};

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/attendance/cardTurnedIn', 
					data: payload
				}).success(function(data) {
					$scope.debug = data;

					$scope.select($scope.selectedPlayer);
				});
			}
		
			$scope.searchFunction = function( item ) {

				if($scope.search == null || $scope.search.length == 0) {
					return true;
				}

				if($scope.search.length > 0) {
					if(item.Player.name.toLowerCase().indexOf($scope.search.toLowerCase()) != -1) {
						return true;
					}

					if(item.Player.cardnumber_prefix == $scope.search) {
						return true;
					}
				}

				return false;
			}

			$scope.printCard = function(character) {
				location.href = "/characters/downloadpdf/"+character.cardnumber;
			}

			$scope.eventDetails = function() {
				
				$http({ method: 'GET', url: '/attendance/eventAttendance'}).success(function(data) {
					$scope.eventdetails = data;
				});
			}

			$scope.undoAttendance = function(player) {
				var payload = {'player': player};

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/attendance/undoAttendance', 
					data: payload
				}).success(function(data) {
					$scope.debug = data;

					$scope.select($scope.selectedPlayer);
					$scope.loadAttendees();
				});
			}

			$scope.waiverReceived = function(player) {
				var payload = {'player': player};

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/attendance/waiverReceived', 
					data: payload
				}).success(function(data) {
					$scope.debug = data;
					$scope.selectedPlayer.Player.has_waiver = 1;
				});
			}

			$scope.ageChecked = function(player) {
				var payload = {'player': player};

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/attendance/ageChecked', 
					data: payload
				}).success(function(data) {
					$scope.debug = data;
					$scope.selectedPlayer.Player.is_not_minor = 1;
				});
			}

		}]);
	</script>

	<div ng-controller="AttendanceController" ng-init="initialize()">
		<div>
			<input type="radio" ng-model="mode" value="attend"/> Attendance 
			<input type="radio" ng-model="mode" value="list"/> List of Attendees
			<input type="radio" ng-model="mode" value="prize"/> Door Prize
		</div>

		<table width="100%" ng-show="mode == 'attend'">
			<tr>
				<td width="70%" style="vertical-align:top">
					<div style="font-size:150%;" ng-hide="selectedPlayer">
						Player Name or Number: <input ng-model="search"/> {{filteredplayers.length}}
					</div>

					<ul ng-hide="selectedPlayer">
						<li ng-repeat="p in filteredplayers = (players | filter:searchFunction) | limitTo:10"><a ng-click="select(p)">({{p.Player.cardnumber_prefix}}) {{p.Player.name}} </a></li>
						<li ng-show="filteredplayers.length > 10">more...</li>
					</ul>

					<div id="newplayer" ng-hide="selectedPlayer || filteredplayers.length > 0">
						<h1>Add Player</h1>

						<form action="/players/" id="PlayerIndexForm" method="post" accept-charset="utf-8" class="ng-pristine ng-valid">
							<input type="hidden" name="_method" value="POST">
							Player Name: <input name="data[Player][name]" maxlength="50" type="text" id="PlayerName" placeholder="last name, first name">
							<input type="submit" value="Save Player">
						</form>
					</div>

					<div ng-show="selectedPlayer">
						<button ng-click="select(null)">Back</button>
						<H3>Attendance for ({{selectedPlayer.Player.cardnumber_prefix}}) {{selectedPlayer.Player.name}}</H3>
					
						<div ng-hide="selectedPlayer.Player.has_waiver == 1">
							Waiver Needed!!! <button ng-click="waiverReceived(selectedPlayer)">Waiver Received</button>
						</div>

						<div ng-hide="selectedPlayer.Player.is_not_minor == 1">
							Parent or Guardian Needed!!! <button ng-click="ageChecked(selectedPlayer)">ID Checked, Player is not a minor</button>
						</div>

						<div>Check In: {{selectedPlayer.attendance.pa.PlayerAttendance.arrival}}</div>
						<!--
							<div>Check Out: {{selectedPlayer.attendance.pa.PlayerAttendance.departure}}</div>
						-->
						<button ng-click="undoAttendance(selectedPlayer)">Clear Attendance for this Player</button>
						
						<br/>

						<button ng-click="cardGivenOut(selectedPlayer,null)" ng-hide="selectedPlayer.attendance.pa.PlayerAttendance">NPC Only</button> <span ng-show="selectedPlayer.attendance.pa.PlayerAttendance">Player is already counted</span>

						<table>
							<tr>
								<th>Card Number</th>
								<th>Name</th>
								<th>Actions</th>
							</tr>
							<tr ng-repeat="c in selectedPlayer.Character">
								<td>{{c.cardnumber}}</td>
								<td>{{c.name}}</td>
								<td>
									<button ng-click="printCard(c)">Download Card</button>

									<button ng-click="cardGivenOut(selectedPlayer,c)" ng-hide="c.cardGiven">Card Given Out</button> <span ng-show="c.cardGiven">Card Given Out</span>

									<!-- <button ng-click="cardTurnedIn(selectedPlayer,c)" ng-hide="c.cardReturned">Card Turned In</button> <span ng-show="c.cardReturned">Card Turned In</span> -->
								</td>
							</tr>
						</table>
					</div>
				</td>
				<td width="30%" style="vertical-align:top">
					<div style="font-size:150%;">
						<table>
							<tr>
								<td>Event</td><th>{{event.Event.name}}</th>
							</tr>
							<tr>
								<td>Players at Event</td><th>{{eventdetails.cnt | number:0}}</th>
							</tr>
							<tr>
								<td></td>
								<th>Average</th>
								<th>Median</th>
							</tr>
							<tr>
								<td>Level</td>
								<th>{{eventdetails.lvl | number:0}}</th>
								<th>{{eventdetails.median | number:0}}</th>
							</tr>
							<tr>
								<td>Body</td>
								<th>{{eventdetails.body | number:0}}</th>
								<th>{{eventdetails.medbody | number:0}}</th>
							</tr>

							<tr>
								<td>Fighter Level*</td><th>{{eventdetails.three | number:0}}</th>
							</tr>
							<tr>
								<td>Rogue Level*</td><th>{{eventdetails.two | number:0}}</th>
							</tr>
							<tr>
								<td>Caster Level*</td><th>{{eventdetails.one | number:0}}</th>
							</tr>

						</table>

						<span style="font-size:75%">*Average Fighter, Rogue and Caster level only takes into account characters with more than 5 levels worth of Fighter, Rogue or Caster skills.</span>
					</div>
				</td>
			</tr>
		</table>

		<div ng-show="mode == 'list'">
			<ul>
				<li ng-repeat="att in attendees">{{att.Player.name}}</li>
			</ul>
		</div>

		<div ng-show="mode == 'prize'">
			<button ng-click="doorprize()">Select Winner(s)</button> 

			<h3 ng-repeat="att in attendees | orderBy:'winner'" ng-show="att.winner">{{att.Player.name}}</h3>
		</div>

	</div>

</div>