<!-- File: /app/View/Characters/level.ctp -->

<style>
table.explained
{
	border:1px solid black;
	text-align:center;
}
table.explained td
{
	border:1px solid black;
	text-align:center;
}
</style>

<table>
<tr><td style="vertical-align:top;">

	<h1><?php 
		echo $character['Character']['name']; 
		echo ' (';
		echo $character['Character']['cardnumber']; 
		echo ') ';
	?></h1>

	<div id="CardOptions">
	<ul>
		<?php
			echo '<li>';
			echo $this->Html->link('View Character',array('controller' => 'characters', 'action' => 'view', $character['Character']['cardnumber']));
			echo '</li>';	
		?>
	</ul>
	</div>

	<?php
		echo '<h2>Actual Body Calculation</h2>';
		echo '<br/>Ratio Explained: ';

		$runningtotal = 0;

		foreach($showwork as $work) {
			if($work['a']['ratio'] != 0 && $work[0]['total'] != 0) {
				if($runningtotal > 0) {
					echo ' + ';
				}

				echo '('.$work[0]['total'].' x '.$work['a']['ratio'].')';
				$runningtotal += $work[0]['total'];
			}
		}
		echo ' / '.$runningtotal;

		echo '<br/>';
		echo 'Ratio: ' . number_format($ratioraw,1) . '<br/>';
		echo 'Racial Modifier: ' . $racialmod . '<br/>';
		echo 'Ratio w/ Race: ' . number_format($ratio,1) . '<br/>';
		echo 'Armor Modifier: '.$armormod.'x<br/>';
		echo 'Build Spent on Body: ' . $build . '<br/>';
		echo 'Starting Body: ' . $starting . '<br/>';
		echo 'Body: ' . ceil($body) . '<br/>';
		echo 'Remainder Build: ' . $remainder;


		echo '<table class="explained"><tr><th>Cost</th><th>Build Spent</th><th>Ratio</th><th>Body Earned</th><th>Body Earned</th></tr>';

		foreach($ranks as $rank) {
			echo '<tr><td>'.$rank['cost'].'</td><td>'.$rank['build'].'</td><td>'.number_format($rank['ratio'],1).'</td><td>'.number_format($rank['body'],1).'</td><td>'.number_format(ceil($rank['body']),0).'</td></tr>';
		}

		echo '</table>';
	?>
</td><td style="vertical-align:top;">

<div ng-app="bodyApp">

	<script type="text/javascript">

		var bodyApp = angular.module('bodyApp',[]);

		bodyApp.controller('BodyController',['$scope','$http','$window', function($scope,$http,$window) {

			$scope.pagepath = $window.location.pathname.split("/");
			$scope.cardnumber = $scope.pagepath[3];
			$scope.bodytotal = 0;
			
			$scope.buildthreeplus = 0;
			$scope.buildtwoplus = 0;
			$scope.buildoneplus = 0;

			$scope.buildthreeminus = 0;
			$scope.buildtwominus = 0;
			$scope.buildoneminus = 0;

			$scope.computeRatio = function() {
				$scope.buildthreetotal = Number($scope.buildthree) + Number($scope.buildthreeplus) - Number($scope.buildthreeminus);
				$scope.buildtwototal = Number($scope.buildtwo) + Number($scope.buildtwoplus) - Number($scope.buildtwominus);
				$scope.buildonetotal = Number($scope.buildone) + Number($scope.buildoneplus) - Number($scope.buildoneminus);

				$scope.total = Number($scope.buildthreetotal) + Number($scope.buildtwototal) + Number($scope.buildonetotal);
				
				if($scope.total > 0) {
					$scope.rawratio = (($scope.buildthreetotal * 30) + ($scope.buildtwototal * 20) + ($scope.buildonetotal * 10)) / ($scope.total);
				} else {
					$scope.rawratio = 0.0;
				}

				$scope.ratio = Number($scope.rawratio) * Number($scope.racialmod);

				$scope.ratio = Math.ceil($scope.ratio) / 10;
				$scope.rawratio = Math.ceil($scope.rawratio * 100) / 1000;

				if($scope.psion >= 1) {
					$scope.ratio = 1.0;
					$scope.rawratio = 1.0;
				}

				$scope.count = 0;
				$scope.cost = 1;
				$scope.bodytotal = Number($scope.startingbody);
				$scope.bodytotalrounded = 0;
				$scope.ranks = new Array();
				$scope.ranks[0] = new Object();
				$scope.ranks[0].ratio = Number($scope.ratio) + 1;
				$scope.ranks[0].build = 0;
				$scope.ranks[0].body = 0;
				$scope.ranks[0].bodyearned = 0;
				$scope.ranks[0].cost = 1;
				$scope.bodybuildrem = Number($scope.bodybuild);

				// Compute the first 15 with the boost
				while($scope.bodybuildrem > 0 && $scope.count < 15) {
					$scope.bodytotal += Number($scope.ratio) + 1;
					$scope.count++;
					$scope.bodybuildrem--;

					$scope.ranks[0].build++;
					$scope.ranks[0].body += Number($scope.ratio) + 1;
				}

				// Round the body total up after each Rank
				$scope.ranks[0].body = Math.ceil($scope.ranks[0].body * 10) / 10;
				$scope.ranks[0].bodyearned = Math.ceil($scope.ranks[0].body);
				$scope.bodytotal = Math.ceil(Number($scope.bodytotal));
				$scope.bodytotalrounded = Math.ceil($scope.bodytotal);

				if($scope.bodybuildrem > 0) {
					// Compute the rest
					$scope.count = 0;
					$scope.cost = 1;

					$scope.ranks[$scope.cost] = new Object();
					$scope.ranks[$scope.cost].ratio = Number($scope.ratio);
					$scope.ranks[$scope.cost].build = 0;
					$scope.ranks[$scope.cost].body = 0;
					$scope.ranks[$scope.cost].bodyearned = 0;
					$scope.ranks[$scope.cost].cost = 1;

					while($scope.bodybuildrem >= $scope.cost) {
						$scope.ranks[$scope.cost].build += Number($scope.cost);
						$scope.ranks[$scope.cost].body += Number($scope.ratio);
						$scope.ranks[$scope.cost].bodyearned = Math.ceil($scope.ranks[$scope.cost].body);
						$scope.bodybuildrem -= $scope.cost;
						$scope.bodytotal += Number($scope.ratio);
						$scope.bodytotalrounded = Math.ceil($scope.bodytotal);

						$scope.count++;
						if($scope.count >= 15) {
							// Round the body total up after each Rank
							$scope.ranks[$scope.cost].body = Math.ceil($scope.ranks[$scope.cost].body * 10) / 10;
							$scope.ranks[$scope.cost].bodyearned = Math.ceil($scope.ranks[$scope.cost].body);
							$scope.bodytotal = Math.ceil(Number($scope.bodytotal));
							$scope.bodytotalrounded = Math.ceil($scope.bodytotal);

							$scope.count = 0;
							if($scope.bodybuildrem > 0) {
								$scope.cost++;
								$scope.ranks[$scope.cost] = new Object();
								$scope.ranks[$scope.cost].ratio = Number($scope.ratio);
								$scope.ranks[$scope.cost].build = 0;
								$scope.ranks[$scope.cost].body = 0;
								$scope.ranks[$scope.cost].bodyearned = 0;
								$scope.ranks[$scope.cost].cost = $scope.cost;
							}
						}
					}
				}
	
				$scope.ranks[$scope.cost].body = Math.ceil($scope.ranks[$scope.cost].body * 10) / 10;
				$scope.ranks[$scope.cost].bodyearned = Math.ceil($scope.ranks[$scope.cost].body);
				$scope.bodytotalrounded = Math.ceil($scope.bodytotal);
			}

			$scope.loadDetails = function() {	
				$http({ method: 'GET', url: '/characters/newbodyajax/'+$scope.cardnumber}).success(function(data) {
					$scope.showwork = data;
					$scope.buildthree = $scope.showwork['showwork']['3.0'];
					$scope.buildtwo = $scope.showwork['showwork']['2.0'];
					$scope.buildone = $scope.showwork['showwork']['1.0'];
					$scope.bodybuild = $scope.showwork['bodybuild'];
					$scope.racialmod = $scope.showwork['racialmod'];
					$scope.psion = $scope.showwork['psion'];
					$scope.startingbody = $scope.showwork['startingbody'];
					$scope.computeRatio();
				});
			}

			$scope.loadDetails();
						
		}]);
	</script>


	<div ng-controller="BodyController" style="background-color: antiquewhite; border: 2px solid black; padding: 5px;">
		<h2>Body Ratio Playground</h2>
		<table>
			<tr><th></th><th>Current</th><th>Plus</th><th>Minus</th></tr>
			<tr>
				<td>Build on 3 ratio skills</td>
				<td><input size="5" type="text" ng-model="buildthree" ng-change="computeRatio()"></td>
				<td><input size="5" type="text" ng-model="buildthreeplus" ng-change="computeRatio()"></td>
				<td><input size="5" type="text" ng-model="buildthreeminus" ng-change="computeRatio()"></td>
			</tr>
			<tr>
				<td>Build on 2 ratio skills</td>
				<td><input size="5" type="text" ng-model="buildtwo" ng-change="computeRatio()"></td>
				<td><input size="5" type="text" ng-model="buildtwoplus" ng-change="computeRatio()"></td>
				<td><input size="5" type="text" ng-model="buildtwominus" ng-change="computeRatio()"></td>
			</tr>
			<tr>
				<td>Build on 1 ratio skills</td>
				<td><input size="5" type="text" ng-model="buildone" ng-change="computeRatio()"></td>
				<td><input size="5" type="text" ng-model="buildoneplus" ng-change="computeRatio()"></td>
				<td><input size="5" type="text" ng-model="buildoneminus" ng-change="computeRatio()"></td>
			</tr>
		</table>
		Total build considered {{total}}<hr/>
		Ratio (before race and rounding): {{rawratio | number:3 }}
		<h2>Ratio: {{ratio}}</h2>
		Build spent on Body <input type="text" ng-model="bodybuild" ng-change="computeRatio()"><br/>
		Starting Body {{startingbody}}

		<table class="explained">
			<tr><th>Cost</th><th>Build Spent</th><th>Ratio</th><th>Body Earned</th><th>Body Earned</th></tr>
			<tr ng-repeat="rank in ranks">
				<td>{{rank.cost}}</td>
				<td>{{rank.build}}</td>
				<td>{{rank.ratio}}</td>
				<td>{{rank.body}}</td>
				<td>{{rank.bodyearned}}</td>
			</tr>
		</table>
		<h2>Body: {{bodytotalrounded}}</h2>
		
	</div>
	
</div>

</td>
</table>