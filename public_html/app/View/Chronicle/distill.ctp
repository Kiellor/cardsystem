<div id="distill" ng-app="distillApp">

	<script type="text/javascript">

		var distillApp = angular.module('distillApp',['nlpCompromise']);

		distillApp.controller('distillAppController',['$scope','$http','$sce','nlp', function($scope,$http,$sce,nlp) {

			$scope.initialize = function() {
				$scope.nouns = "";
				$scope.text = "";
				$scope.loadChronicles();
			}

			$scope.loadChronicles = function() {

				$http({ method: 'GET', url: '/chronicle/loadChronicles/<?php echo $cardnumber; ?>'}).success(function(data) {
					$scope.text = data[0].CharacterChronicle.entry;

					$scope.process = $scope.strip($scope.text);

					$scope.spot = nlp.spot($scope.process);

					$scope.uni = $scope.unique($scope.spot);
				});
			}

			$scope.unique = function(origArr) {
			    var newArr = [],
			        origLen = origArr.length,
			        found, x, y;

			    for (x = 0; x < origLen; x++) {
			        found = false;
			        for (y = 0; y < newArr.length; y++) {
			            if (origArr[x] === newArr[y]) {
			                found = true;
			                break;
			            }
			        }
			        if (!found) {
			            newArr.push(origArr[x]);
			        }
			    }
			    return newArr;
			}

			$scope.strip = function(html)
			{
			   var tmp = document.createElement("DIV");
			   tmp.innerHTML = html;
			   return tmp.textContent || tmp.innerText || "";
			}

			$scope.renderHtml = function(value) {
				return $sce.trustAsHtml(value);
			}

		}]);


	</script>

	<div ng-controller="distillAppController" ng-init="initialize()">

		<table>
			<tr>
				<td style="vertical-align:top;">
					<ul>
						<li ng-repeat="n in uni">{{n.text}}</li>
					</ul>
				</td>
				<td style="vertical-align:top;">
					<div ng-bind-html="renderHtml(text)"></div>
				</td>
			</tr>
		</table>
	</div>

</div>