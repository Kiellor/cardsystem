<div ng-app="attendanceApp">
	
	<script type="text/javascript">

		var attendanceApp = angular.module('attendanceApp',[]);
		
		attendanceApp.controller('AttendanceController',['$scope','$http', function($scope, $http) {
			
			$scope.attendance = [];
			$scope.events = {};
			$scope.event_ids = [];

			$scope.loadattendance = function() {	
				$http({ method: 'GET', url: '/demographics/loadlatestattendance'}).success(function(data) {
					$scope.attendance = data;

					for(var i = 0; i < $scope.attendance.length; i++) {
						var event_id = $scope.attendance[i].b.event_id;

						if( event_id in $scope.events ) {
							$scope.events[event_id]["attendees"].push($scope.attendance[i]);
							$scope.events[event_id]["count"]++;
						}
						
					}

				});
			}

			$scope.selectevent = function(event_id) {
				if($scope.selectedEvent == event_id) {
					$scope.selectedEvent = null;
				} else {
					$scope.selectedEvent = event_id;
				}
			}

			$scope.initialize = function() {
				$http({ method: 'GET', url: '/demographics/loadevents'}).success(function(data) {
					$scope.eventlist = data;
					for(var i = 0; i < $scope.eventlist.length; i++) {
						var event_id = $scope.eventlist[i].events.id;

						if( !(event_id in $scope.events)) {
							$scope.event_ids.push(event_id);
							$scope.events[event_id] = {"name": $scope.eventlist[i].events.name, "event_id": event_id, "count": 0, "attendees": [], "pos": i };
						}
					}
					$scope.loadattendance();
				});				
			}
			
		}]);
	</script>


	<div style="float:left;" ng-controller="AttendanceController" ng-init="initialize()">
		<h2>Last Event Build Earned at</h2>
		<div ng-repeat="event_id in event_ids" ng-show="events[event_id].count > 0">
			<h4><a ng-click="selectevent(event_id)">{{events[event_id].name}} ({{events[event_id].count}})</a></h4>
			<div ng-show="selectedEvent == event_id">
				<table>
					<tr ng-repeat="person in events[event_id].attendees">
						<td>{{person.b.playername}}</td>
						<td>
							<span ng-show="person.b.username == null">no email on file</span>
							<a ng-href="mailto:{{person.b.username}}">{{person.b.username}}</a>
						</td>
						<td><a ng-href="/characters/view/{{person.b.cardnumber}}">{{person.b.name}}</a></td>
						<td>{{person.b.attendance_note}}</td>
					</tr>
				</table>
				</ul>
			</div>
		</div>
	</div>

</div>