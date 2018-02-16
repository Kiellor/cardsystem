<div id="viewMap" ng-app="viewMapApp">

	<script type="text/javascript">

		var viewMapApp = angular.module('viewMapApp',[]);

		viewMapApp.controller('ViewMapController',['$scope','$http', function($scope,$http) {
			$scope.initialize = function() {
				$scope.debug ="hello";

				$http({ method: 'GET', url: '/personal_action/loadPlots/<?php echo $settlement_id; ?>'}).success(function(data) {
					$scope.areas = data;
				});

				$scope.hidden = {'visibility':'hidden'};
				$scope.shownLabel = {'text-anchor':'middle'};

				$scope.shownPlot = {'stroke':'#896c34', 'stroke-width':'3', 'fill-opacity':'0.1' };				
				$scope.hiddenPlot = {'stroke':'#896c34', 'stroke-width':'0', 'fill-opacity':'0' };				
			}

			$scope.hoverOver = function(a) {
				$scope.selectedA = a;
			}

			$scope.getCenterX = function(a) {
				return parseInt(a.SettlementPlot.x) + (parseInt(a.SettlementPlot.w) / 2);
			}
			$scope.getCenterY = function(a) {
				return parseInt(a.SettlementPlot.y) + (parseInt(a.SettlementPlot.h) / 2);
			}

			$scope.getRectStyle = function(a) {
				if($scope.selectedA == a || $scope.addingShape) {
					return $scope.shownPlot;
				}

				return $scope.hiddenPlot;
			}

			$scope.getLabelStyle = function(a) {
				if($scope.selectedA == a) {
					return $scope.shownLabel;
				}

				return $scope.hidden;
			}

			$scope.addNewShape = function() {
				$scope.addingShape = true;
				$scope.clickCount = 0;

				$scope.newShape = {SettlementPlot:{'x':'1', 'y':'1', 'w':'50', 'h':'50', 'label':'New'}};
				$scope.areas.push($scope.newShape);
				$scope.selectedA = $scope.newShape;
			}

			$scope.svgMove = function(e) {
				if($scope.addingShape) {
					if($scope.clickCount == 0) {
						$scope.newShape.SettlementPlot.x = e.offsetX;
						$scope.newShape.SettlementPlot.y = e.offsetY;
					} else if($scope.clickCount == 1) {
						$scope.newShape.SettlementPlot.w = e.offsetX - $scope.newShape.SettlementPlot.x;
						$scope.newShape.SettlementPlot.h = e.offsetY - $scope.newShape.SettlementPlot.y;
					}
				}
			}

			$scope.svgClicked = function(e) {
				$scope.clickCount++;
				if($scope.clickCount > 1) {
					$scope.addingShape = false;
				}
			}

		}]);
	</script>

	<div ng-controller="ViewMapController" ng-init="initialize()">

		<button ng-click="addNewShape()">Add</button>
		<br/>
		<div>
			<table>
				<tr ng-repeat="a in areas" ng-mouseover="hoverOver(a)">
					<td>x="{{a.SettlementPlot.x}}"</td>
					<td>y="{{a.SettlementPlot.y}}"</td>
					<td>width="{{a.SettlementPlot.w}}"</td>
					<td>height="{{a.SettlementPlot.h}}"</td>
					<td><input type="text" ng-model="a.SettlementPlot.label" ng-change="hoverOver(a)"/></td>
				</tr>
			</table>
		</div>

		<svg width="1346" height="841" ng-click="svgClicked($event)" ng-mousemove="svgMove($event)">
			<image xlink:href="/images/land/valdalis_test.jpg" x="0" y="0" height="841px" width="1346px"/>

			<rect ng-repeat="a in areas" x="{{a.SettlementPlot.x}}" y="{{a.SettlementPlot.y}}" width="{{a.SettlementPlot.w}}" height="{{a.SettlementPlot.h}}" ng-style="getRectStyle(a)" ng-mouseover="hoverOver(a)" ng-mouseleave="hoverOver(null)"/>

			<text ng-repeat="a in areas" ng-show="a == selectedA" x="{{getCenterX(a)}}" y="{{getCenterY(a)}}" ng-style="getLabelStyle(a)"ng-mouseover="hoverOver(a)" ng-mouseleave="hoverOver(null)">{{a.SettlementPlot.label}}</text>

		</svg>

	</div>
</div>