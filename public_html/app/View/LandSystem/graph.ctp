<style>
	
</style>

<div id="landSystemGraph" ng-app="landSystemGraphApp">

	<script type="text/javascript">

		var landSystemGraphApp = angular.module('landSystemGraphApp',['chart.js']);

		landSystemGraphApp.controller('LandSystemGraphController',['$scope','$http', '$filter', '$location', function($scope,$http,$filter,$location) {
      
			$scope.initialize = function() {

				Chart.defaults.global.colours = [
				    { // yellow
				        fillColor: "rgba(253,180,92,0.2)",
				        strokeColor: "rgba(253,180,92,1)",
				        pointColor: "rgba(253,180,92,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(253,180,92,0.8)"
				    },
				    { // red
				        fillColor: "rgba(247,70,74,0.2)",
				        strokeColor: "rgba(247,70,74,1)",
				        pointColor: "rgba(247,70,74,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(247,70,74,0.8)"
				    },
				    { // green
				        fillColor: "rgba(70,191,189,0.2)",
				        strokeColor: "rgba(70,191,189,1)",
				        pointColor: "rgba(70,191,189,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(70,191,189,0.8)"
				    },
				    { // dark grey
				        fillColor: "rgba(77,83,96,0.2)",
				        strokeColor: "rgba(77,83,96,1)",
				        pointColor: "rgba(77,83,96,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(77,83,96,1)"
				    },
				    { // light grey
				        fillColor: "rgba(220,220,220,0.2)",
				        strokeColor: "rgba(220,220,220,1)",
				        pointColor: "rgba(220,220,220,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(220,220,220,0.8)"
				    },
				    { // blue
				        fillColor: "rgba(151,187,205,0.2)",
				        strokeColor: "rgba(151,187,205,1)",
				        pointColor: "rgba(151,187,205,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(151,187,205,0.8)"
				    },
				    { // grey
				        fillColor: "rgba(148,159,177,0.2)",
				        strokeColor: "rgba(148,159,177,1)",
				        pointColor: "rgba(148,159,177,1)",
				        pointStrokeColor: "#fff",
				        pointHighlightFill: "#fff",
				        pointHighlightStroke: "rgba(148,159,177,0.8)"
				    }
				];

				$scope.opts = {
					datasetFill: false
				};
				
				$scope.labels = [];
			  	$scope.series = ['Order', 'Health', 'Happiness'];
			  	$scope.points = [];
			  	$scope.points[0] = [];
				$scope.points[1] = [];
				$scope.points[2] = [];

				$scope.population = [];
				$scope.population[0] = [];
				$scope.popseries = ['Population'];
				// $scope.population[1] = [];
				// $scope.popseries = ['Population','Total'];

			  	$scope.onClick = function (points, evt) {
			    	console.log(points, evt);
			  	};

				$http({ method: 'GET', url: '/land_system/graphdata/<?php echo $settlement_id; ?>'}).success(function(values) {
					$scope.values = values;

					$scope.convertValuesToData();
				});

				$http({ method: 'GET', url: '/land_system/graphdetails/<?php echo $settlement_id; ?>'}).success(function(values) {
					$scope.sv = values;
				});

				// $http({ method: 'GET', url: '/land_system/populationTotals/'}).success(function(values) {
				// 	$scope.poptotal = values;
				// 	$scope.convertPopToData();
				// });


			}

			$scope.obfuscate = function(value) {
				if(value >= 90) {
					return 95
				} else if(value >= 83) {
					return 86
				} else if(value >= 78) {
					return 80
				} else if(value >= 72) {
					return 75
				} else if(value >= 65) {
					return 68
				} else if(value >= 60) {
					return 63
				} else if(value >= 40) {
					return 50
				} else {
					return 20
				}
			}

			$scope.obfuscate_wildlands = function(value) {
				if(value >= 75) {
					return 75
				} else if(value >= 50) {
					return 50
				} else if(value >= 25) {
					return 25
				} else {
					return 0
				}
			}

			$scope.convertValuesToData = function() {
				for(i = 0; i < $scope.values.length; i++) {

					$scope.labels[i] = $scope.values[i].e.name;
					$scope.points[0][i] = $scope.obfuscate(parseInt($scope.values[i].sv.public_order) + 0);
					$scope.points[1][i] = $scope.obfuscate(parseInt($scope.values[i].sv.health) + 0);
					$scope.points[2][i] = $scope.obfuscate(parseInt($scope.values[i].sv.happiness) + 0);
					$scope.population[0][i] = (parseInt($scope.values[i].sv.population) + 0);
				}
			}

			// $scope.convertPopToData = function() {
			// 	for(i = 0; i < $scope.values.length; i++) {

			// 		$scope.labels[i] = $scope.poptotal[i].e.name;
			// 		$scope.population[1][i] = (parseInt($scope.poptotal[i][0].population) + 0);
			// 	}
			// }

		}]);
		
	</script>

	<div ng-controller="LandSystemGraphController" ng-init="initialize()">

		<h2>{{sv.Land.name}} -- {{sv.Settlement.name}}</h2>

		<div style="background-color:#ffffff">
			<canvas id="line" class="chart chart-line" chart-data="points" width="800" height="150"
			  chart-labels="labels" chart-legend="true" chart-series="series" chart-options="opts">
			</canvas> 
		</div>

		<div style="background-color:#ffffff">
			<canvas id="line" class="chart chart-line" chart-data="population" width="800" height="150"
			  chart-labels="labels" chart-legend="true" chart-series="popseries" chart-options="opts">
			</canvas> 
		</div>

	</div> <!-- end of the ng-controller div. -->

</div>