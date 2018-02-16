<style>
	pre { margin: 0px; }

	th { white-space: nowrap; }
	.skilltable { border-collapse: collapse; }
	.skilltable th { border: 1px solid black; padding: 3px; vertical-align: top;}
	.skilltable td { border: 1px solid black; padding: 3px; vertical-align: top;}
	pre {
	    white-space: pre-wrap;       /* CSS 3 */
	    white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
	    white-space: -pre-wrap;      /* Opera 4-6 */
	    white-space: -o-pre-wrap;    /* Opera 7 */
	    word-wrap: break-word;       /* Internet Explorer 5.5+ */
	}
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
		function formatDisplay(display, option, build, qty) {
			var total = qty * build;
			var rtn = replaceAll(display,"^","<br/>&nbsp;&nbsp;");

			if(option != null) {
				rtn = rtn+' '+option;
			}
			if(total == 0) {
				rtn = rtn+' ('+build+')';
			} else if(qty > 1) {
				if(display.indexOf("+1") > -1) {
					rtn = rtn+' ('+build+')';
					rtn = replaceAll(rtn,"+1","+"+qty);
				} else {
					rtn = rtn+' x'+qty+' ('+build+') = '+total;
				}
			} else if(qty < 0) {
				rtn = rtn+' x '+qty+' ('+build+') = ['+total+']';
			} else {
				rtn = rtn+' ('+build+')';
			}

			return rtn;
		};

		function escapeRegExp(str) {
		    return str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
		}

		function replaceAll(str, find, replace) {
			return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
		}

		var myApp = angular.module('myApp',['firebase','ngSanitize']);

		myApp.filter('abilityDisplay', function($sce) {
			return function(input) {
				var display = input.a.display_name;
				if(display == null || display.length == 0) {
					display = input.a.ability_name;
				}
				if(input.hasOwnProperty("alo")) {
					return $sce.trustAsHtml(formatDisplay(display,input.alo.ability_name,input.la.build_cost,input.qty));
				} else {
					return $sce.trustAsHtml(formatDisplay(display,"",input.la.build_cost,input.qty));
				}
			};
		});

		myApp.filter('skillListing', function() {
			return function(input) {
				if(input.hasOwnProperty("alo") && input.alo.ability_name != null) {
					return input.alo.ability_name;
				} else {
					return input.a.ability_name;
				}
			}
		});
		
		myApp.filter('abilityBubbles', function() {
			return function(input) {
				if(input.qty > 0) {
					var bubbles = '';
					var i = input.qty * input.a.uses;
					
					switch(input.a.abilitytype_id) {
					case '1': // Periodic
						while(i > 5) {
							if(bubbles.length > 0) {
								bubbles += "\n";
							}
							bubbles += '  OOOOO|OOOOO|OOOOO|OOOOO';
							i -= 5;
						}
						if(i > 0) {
							subbub = '';
							while(i > 0) {
								subbub += 'O';
								i--;
							}
							while(subbub.length < 5) {
								subbub += ' ';
							}
							if(bubbles.length > 0) {
								bubbles += "\n";
							}
							bubbles += '  '+subbub+'|'+subbub+'|'+subbub+'|'+subbub;
						}		
						break;
					case '2': // Event
						while(i > 15) {
							if(bubbles.length > 0) {
								bubbles += "\n";
							}
							bubbles += '  [OOOOO OOOOO OOOOO]';
							i -= 15;
						}
						if(i > 0) {
							if(bubbles.length > 0) {
								bubbles += "\n";
							}
							bubbles += ' [';
							while(i > 5) {
								bubbles += 'OOOOO ';
								i -= 5;
							}
							while(i >= 1) {
								bubbles += 'O';
								i--;
							}
							bubbles += ']';
						}
						break;
					}
					return bubbles;
				}
				return null;
			};
		});
		
		myApp.factory("FireObject", ["$firebaseObject", 
			function($firebaseObject) {
				return function(nested) {
					if(typeof nested === 'string') {
						var ref = new Firebase("https://character-builder-2.firebaseio.com/"+nested);
						return $firebaseObject(ref.child(nested));
					} else {
						var path = nested.join("/");
						var ref = new Firebase("https://character-builder-2.firebaseio.com/"+path);
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
						ref = new Firebase("https://character-builder-2.firebaseio.com/"+nested);
					} else {
						var path = nested.join("/");
						ref = new Firebase("https://character-builder-2.firebaseio.com/"+path);
					}

					if(sortkey != null) {
						return $firebaseArray(ref.orderByChild(sortkey));
					} else {
						return $firebaseArray(ref);
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

		myApp.controller('NPMController',['$scope','$http','$timeout','FireObject','FireArray', function($scope, $http, $timeout, FireObject, FireArray) {

			$scope.getStarted = function() {
				$scope.meta.stage = 1;
				$scope.meta.showProfessions = 'common';
				$scope.meta.profession_count = 0;
				$scope.meta.lastEdited = Firebase.ServerValue.TIMESTAMP;
				$scope.meta.emailsent = false;
				$scope.meta.readonly = false;
				$scope.meta.importdone = false;

				$scope.character.startingBuild = 55;
				$scope.character.token = $scope.token;

				//$scope.character.$save();

				//$scope.goToToken = "/character_builder/index/"+$scope.token;
				//window.location = "/character_builder/index/"+$scope.token;
			}

			$scope.saveAlias = function() {
				var alias = $scope.nickname;
				var aliasRef = new Firebase('https://character-builder-2.firebaseio.com/presence/users/' + $scope.userid + '/alias');

				if(alias != null) {
					aliasRef.set(alias);
				}

				aliasRef.on('value', function(snapshot) {
					$scope.nickname = snapshot.val();
					$scope.getUserInformation();
				})
			}

			$scope.forgetToken = function(token) {
				var tokenRef = new Firebase('https://character-builder-2.firebaseio.com/presence/users/' + $scope.userid + '/tokens/' + token.ref);
				tokenRef.remove();
			}

			$scope.rememberToken = function() {
				var tokenRef = new Firebase('https://character-builder-2.firebaseio.com/presence/users/' + $scope.userid + '/tokens');

				var name = $scope.character.name;
				if(name == null || name.length == 0) {
					name = "new character";
				}

				var child = tokenRef.push();
				child.set({token: $scope.token, time: Firebase.ServerValue.TIMESTAMP, name: name, ref: child.key()});

				$scope.tokenSaved = true;
			}

			$scope.loadRememberedTokens = function() {
				$scope.oldTokens = [];

				var tokenRef = new Firebase('https://character-builder-2.firebaseio.com/presence/users/' + $scope.userid + '/tokens');

				tokenRef.on('value', function(snapshot) {
					$scope.oldTokens = snapshot.val();
				});
			}

			$scope.getUserInformation = function() {
				var here = 'character_builder-'+$scope.token;

				var amOnline = new Firebase('https://character-builder-2.firebaseio.com/.info/connected');
				var locationRef = new Firebase('https://character-builder-2.firebaseio.com/locations/' + here);
				locationRef.child('name').set('Character '+$scope.token);
				locationRef.child('url').set('/character_builder/index/'+$scope.token);

				var userRef = locationRef.child('users').child($scope.userid);

				var alias = $scope.nickname;
				if(alias == null || alias.length == 0) {
					alias = "guest";
				}

				amOnline.on('value', function(snapshot) {
				  if (snapshot.val()) {
				    userRef.onDisconnect().remove();
				    userRef.set({'time':Firebase.ServerValue.TIMESTAMP, 'alias':alias});
				  }
				});
			}

			$scope.initialize = function() {
				$scope.token = "<?php echo $token; ?>";
				$scope.printing = "<?php echo $printing; ?>";
				$scope.isAdmin = "<?php echo $isAdmin; ?>";
				$scope.clientIP = "<?php echo $clientIP; ?>";
				$scope.useremail = "<?php echo AuthComponent::user('username') ?>";

				if($scope.useremail.length == 0) {
					$scope.useremail = $scope.clientIP;
				}				
				if($scope.useremail.length == 0) {
					$scope.useremail = "guest-"+$scope.token;
				}

				var userid = replaceAll($scope.useremail,".","_");
				userid = replaceAll(userid,"#","_");
				userid = replaceAll(userid,"$","_");
				userid = replaceAll(userid,"[","_");
				$scope.userid = replaceAll(userid,"]","_");

				$scope.initiateVariables();
				$scope.loadRememberedTokens();
				$scope.saveAlias();
				$scope.getUserInformation();

				$scope.sublists = [];

				FireObject(["datastore",$scope.token]).$bindTo($scope, "datastore");
				FireObject(["characters",$scope.token,"character"]).$bindTo($scope,"character");
				FireObject(["characters",$scope.token,"meta"]).$bindTo($scope, "meta");

				$scope.savedabilities = FireArray(["savedabilities",$scope.token,"abilities"], "order_by");
				$scope.savedabilitiesUnwatch = $scope.savedabilities.$watch(function() {
					$scope.savedabilitiesUpdated();
				});

				$scope.chatMessages = FireArray(["tokenchat",$scope.token], null);
				$scope.chatMessagesUnwatch = $scope.chatMessages.$watch(function() {
					$scope.chatUpdated();
				})

				$scope.chatUsers = FireObject(["locations",$scope.token]);
				$scope.chatUsersUnwatch = $scope.chatUsers.$watch(function() {
					$scope.chatUsersUpdated();
				})

				$scope.maxstage = 4;

				$http({ method: 'GET', url: '/character_builder/getRaces'}).success(function(data) {
					$scope.races = data;
				});

				$http({ method: 'GET', url: '/character_builder/getReligions'}).success(function(data) {
					$scope.religions = data;
				});
			}

			var updateCharacterPromise = false;
			$scope.delayedSaveCharacter= function() {
				// if(updateCharacterPromise) {
			 //  		$timeout.cancel(updateCharacterPromise);
			 //  	}
			 //  	updateCharacterPromise = $timeout(function() {
			 //  		$scope.character.$save();
			 //  	}, 2000);
			}

			$scope.chatUpdated = function() {
			}

			$scope.chatUsersUpdated = function() {
			}

			$scope.characterUpdated = function() {
			}

			var updateAbilitiesPromise = false;
			$scope.savedabilitiesUpdated = function() {
				if(updateAbilitiesPromise) {
					$timeout.cancel(updateAbilitiesPromise);
				}
				updateAbilitiesPromise = $timeout(function() {
					$scope.computeSpent();
					var skills = _.reduce(
						$scope.savedabilities,
						function(header, item) {
						  var title = item.ag.name;
						  if (header[title]) //if title is a key
						    header[title].push(item); //Add name to its list
						  else
						    header[title] = [item]; // Else add a key
						  return header;
						},
						{}
					);

					$scope.skillColumns = [];
					var total = $scope.savedabilities.length;
					var maxCol = 1;
					if($scope.meta.stage > 4){
						maxCol = 4;
					}
					if($scope.printing == true) {
						maxCol = 4;
					}
					var target = Math.floor(total / maxCol);
					var pos = 0;
					var col = 0;

					for(var property in skills) {
						if(skills.hasOwnProperty(property)) {
							pos += skills[property].length;
							if($scope.skillColumns[col] == null) {
								$scope.skillColumns[col] = {}
							}
							$scope.skillColumns[col][property] = skills[property];
							if(pos >= target) {
								pos = 0;
								col++;
							}
						}
					}
				}, 100);
			}

			$scope.abilityBubbleShow = function(ability) {
				var retval = false;
				switch(ability.a.abilitytype_id) {
					case '1': // Periodic
					case '2': // Event
						retval = true;
						break;
				}

				return retval;
			}
			
			$scope.initiateVariables = function() {
				$scope.spentBuild = 0;
				$scope.level = 0;
				$scope.unspentBuild = 55;

				$scope.armorRatio = 1.0;
				$scope.bodyRatio = 1.0;
				$scope.bodyPoints = 10;
				$scope.bodyBuild = 0;
				$scope.spentBuild = 0;

				$scope.MP = 0;
				$scope.FP = 0;
				$scope.CP = 0;
				$scope.PP = 0;
				$scope.HP = 0;
				$scope.SP = 0;
				$scope.DP = 0;
				$scope.PsiP = 0;

				$scope.ratio_build = 0;
				$scope.ratio_total = 0;
			}

			$scope.computeSpent = function() {
				$scope.initiateVariables();

				$scope.unspentBuild = $scope.character.startingBuild;
				$scope.bodyPoints = parseInt($scope.character.race.a.BP);

				$scope.sublists_old_value = $scope.sublists.join();
				$scope.sublists = [];

				$scope.is_psionicist = false;
				
				var len = $scope.savedabilities.length;
				for(i = 0; i < len; i++) {
					var ability = $scope.savedabilities[i];
					if(ability.qty > 0) {
						$scope.spentBuild += parseInt(ability.qty) * parseInt(ability.la.build_cost);

						if(ability.a.hasOwnProperty("MP")) {
							if(ability.a.MP > 0) {
								$scope.MP += parseInt(ability.a.MP) * parseInt(ability.qty);
							}
						}
						if(ability.a.hasOwnProperty("FP")) {
							if(ability.a.FP > 0) {
								$scope.FP += parseInt(ability.a.FP) * parseInt(ability.qty);
							}
						}
						if(ability.a.hasOwnProperty("CP")) {
							if(ability.a.CP > 0) {
								$scope.CP += parseInt(ability.a.CP) * parseInt(ability.qty);
							}
						}
						if(ability.a.hasOwnProperty("PP")) {
							if(ability.a.PP > 0) {
								$scope.PP += parseInt(ability.a.PP) * parseInt(ability.qty);
							}
						}
						if(ability.a.hasOwnProperty("HP")) {
							if(ability.a.HP > 0) {
								$scope.HP += parseInt(ability.a.HP) * parseInt(ability.qty);
							}
						}
						if(ability.a.hasOwnProperty("SP")) {
							if(ability.a.SP > 0) {
								$scope.SP += parseInt(ability.a.SP) * parseInt(ability.qty);
							}
						}
						if(ability.a.hasOwnProperty("DP")) {
							if(ability.a.DP > 0) {
								$scope.DP += parseInt(ability.a.DP) * parseInt(ability.qty);
							}
						}
						if(ability.a.hasOwnProperty("PsiP")) {
							if(ability.a.PsiP > 0) {
								$scope.PsiP += parseInt(ability.a.PsiP) * parseInt(ability.qty);
							}
						}

					}

					if(!ability.hasOwnProperty("is_profession") && ability.a.opens_list_id > 0) {
						$scope.sublists.push(ability.a);
					}

					if(ability.a.Ratio > 0) {
						$scope.ratio_build += parseInt(ability.la.build_cost) * parseInt(ability.qty);
						$scope.ratio_total += parseInt(ability.la.build_cost) * parseInt(ability.qty) * parseInt(ability.a.Ratio) * 10;
					}

					if(ability.a.BP > 0) {
						$scope.bodyBuild += parseInt(ability.la.build_cost) * parseInt(ability.qty);
					}

					// Figure out if character is psionicist
					if(ability.a.ability_name == "Psionicist (B)") {
						$scope.is_psionicist = true;
					}
				}

				// Calculate Body Ratio
				if($scope.ratio_build > 0) {
					$scope.bodyRatio = parseInt($scope.ratio_total) / parseInt($scope.ratio_build);

					// Compute Armor Ratio
					if($scope.bodyRatio >= 25) {
						$scope.armorRatio = 3;
					} else if($scope.bodyRatio >= 15) {
						$scope.armorRatio = 2;
					} else {
						$scope.armorRatio = 1;
					}

					$scope.bodyRatio = Math.ceil(parseFloat($scope.bodyRatio) * parseFloat($scope.character.race.a.Ratio)) / 10;
				}

				// Figure out if character is psionicist
				if($scope.is_psionicist) {
					$scope.bodyRatio = 1.0;
				}

				var count = 0;
				var bodybuild = parseInt($scope.bodyBuild);

				// Compute Body -- include bonus for first 15, increase cost
				while(bodybuild > 0 && count < 15) {
					$scope.bodyPoints = parseFloat($scope.bodyPoints) + parseFloat($scope.bodyRatio) + 1;
					count++;
					bodybuild--;
				}

				$scope.bodyPoints = Math.ceil($scope.bodyPoints);

				if(bodybuild > 0) {
					// Compute the remaing body points
					count = 0;
					var cost = 1;

					while(bodybuild >= cost) {
						bodybuild -= cost;
						$scope.bodyPoints = parseFloat($scope.bodyPoints) + parseFloat($scope.bodyRatio);

						count++;
						if(count >= 15) {
							count = 0;
							cost++;
							// Round the body total up after each Rank
							$scope.bodyPoints = Math.ceil($scope.bodyPoints);
						}
					}
				}

				$scope.bodyPoints = Math.ceil($scope.bodyPoints);
			
				$scope.unspentBuild = parseInt($scope.character.startingBuild) - $scope.spentBuild;
				$scope.level = Math.floor($scope.spentBuild / 10);

				if($scope.sublists_old_value != $scope.sublists.join()) {
					$scope.getSubAbilities();
				}
			}
			
			$scope.goToStage = function(st) {
				$scope.meta.lastEdited = Firebase.ServerValue.TIMESTAMP;
				$scope.meta.stage = st;
				if($scope.meta.stage > $scope.maxstage) {
					$scope.meta.stage = $scope.maxstage;
				} else if($scope.meta.stage < 1) {
					$scope.meta.stage = 1;
				}
			}
			
			$scope.makeReadOnly = function(val) {
				$scope.meta.readonly = val;
			}

			$scope.setRace = function() {
				$scope.meta.lastEdited = Firebase.ServerValue.TIMESTAMP;
				$scope.getProfessionsURL = '/character_builder/getProfessionsForRace/' + $scope.character.race.a.id;

				$http({ method: 'GET', url: $scope.getProfessionsURL}).success(function(data) {
					$scope.datastore.lowerlists = data;
				});

				$scope.getOtherAbilitiesURL = '/character_builder/getCommonRacialAbilities/'+$scope.character.race.a.opens_list_id;

				$http({ method: 'GET', url: $scope.getOtherAbilitiesURL}).success(function(data) {
					$scope.datastore.otherabilities = data;
				});
				//$scope.character.$save();
			};

			$scope.setReligion = function() {
				$scope.meta.lastEdited = Firebase.ServerValue.TIMESTAMP;
				//$scope.character.$save();
			}

			$scope.addProfession = function() {
				$scope.meta.lastEdited = Firebase.ServerValue.TIMESTAMP;
				var selected = JSON.parse(JSON.stringify($scope.character.profession));

				selected.order_by = selected.ag.sorting_name;

				if($scope.meta.hasOwnProperty("profession_count")) {
					$scope.meta.profession_count++;
				} else {
					$scope.meta.profession_count = 1;
				}

				selected.is_profession = true;
				selected.qty = 1;
				if($scope.meta.profession_count == 1) {
					selected.la.build_cost = 0;
				}
				$scope.savedabilities.$add(selected);
			}

			$scope.addSelected = function(record) {
				
				$scope.meta.lastEdited = Firebase.ServerValue.TIMESTAMP;
				var selected = JSON.parse(JSON.stringify(record));
				if( !selected.hasOwnProperty('original_cost')) {
					selected.original_cost = selected.la.build_cost;
				}

				var autoIncrement = false;
				var usesOption = false;
				if( selected.a.abilitytype_id == 1 || 
					selected.a.abilitytype_id == 2 ||
					selected.a.abilitytype_id == 24  ) {
					autoIncrement = true;
				}
				var aname = selected.a.ability_name;
				if(aname.search("\\+1") > -1) {
					autoIncrement = true;
				}
				if(selected.a.uses_option_list > 0) {
					autoIncrement = false;
					usesOption = true;
				}

				var build_cost = selected.la.build_cost;

				// Check for cost increasing skills
				if(autoIncrement) {
					for(i = 0; i < $scope.savedabilities.length; i++) {
						if($scope.savedabilities[i].a.id == selected.a.id) {
							if(selected.a.cost_increase_interval > 0 && 
							   $scope.savedabilities[i].qty >= selected.a.cost_increase_interval  ) {
								build_cost = parseInt(build_cost) + parseInt(selected.la.build_cost);
							} else {
								$scope.savedabilities[i].qty++;
								$scope.savedabilities.$save($scope.savedabilities[i]);
								// increment and exit
								return;
							}
						}
					}
				} else if( !usesOption ) {
					for(i = 0; i < $scope.savedabilities.length; i++) {
						if($scope.savedabilities[i].a.id == selected.a.id) {
							// it already exists, don't allow it to be added again by exiting
							return;
						}
					}
				}

				selected.order_by = selected.ag.sorting_name;
				selected.la.build_cost = build_cost;

				selected.qty = 1;
				if(selected.la.free_set > 0) {
					var free_set_id = selected.la.elist_id + ":" + selected.la.free_set;
					if( !$scope.meta.hasOwnProperty(free_set_id) ) {
						$scope.meta[free_set_id] = 0;
					}

					if($scope.meta[free_set_id] < selected.la.free_set_limit) {
						selected.la.build_cost = 0;
						$scope.meta[free_set_id]++;
					}
				}

				if(selected.a.uses_option_list > 0) {
					$http({ method: 'GET', url: "/character_builder/getOptionList/"+selected.a.uses_option_list}).success(function(data) {
						selected.options = data;
						$scope.savedabilities.$add(selected);
					});
				} else {
					$scope.savedabilities.$add(selected);
				}
			}

			$scope.removeAbility = function(record) {
				$scope.meta.lastEdited = Firebase.ServerValue.TIMESTAMP;
				if(record.is_profession) {
					$scope.meta.profession_count--;
					if($scope.meta.profession_count < 0) {
						$scope.meta.profession_count = 0;
					}
				}
				if(record.la.free_set > 0 && record.la.build_cost == 0) {
					var free_set_id = record.la.elist_id + ":" + record.la.free_set;
					if($scope.meta.hasOwnProperty(free_set_id)) {
						if($scope.meta[free_set_id] > 0) {
							$scope.meta[free_set_id]--;
						}
					} else {
						$scope.meta[free_set_id] = 0;
					}
				}

				$scope.savedabilities.$remove(record);
			}

			$scope.updateQuantity = function(ability) {
				if(ability.qty < 1) {
					ability.qty = 1;
				}

				var addOneMore = false;
				if(ability.a.cost_increase_interval > 0) {
					if(parseInt(ability.qty) > parseInt(ability.a.cost_increase_interval)) {
						ability.qty = parseInt(ability.a.cost_increase_interval);
					}
				}

				ability.qtychg = 0;
				$scope.savedabilities.$save(ability);
			}

			$scope.increaseCost = function(ability) {
				ability.la.build_cost = parseInt(ability.la.build_cost) + parseInt(ability.original_cost);
				$scope.savedabilities.$save(ability);
			}

			$scope.getAllAbilities = function() {
				$scope.meta.abilities = [];

				$scope.getAbilitiesURL = '/character_builder/getAbilitiesForProfession/' + $scope.character.profession.a.opens_list_id;

				$http({ method: 'GET', url: $scope.getAbilitiesURL}).success(function(data) {
					$scope.datastore.abilities = data;
				});
			};

			$scope.getSubAbilities = function() {
				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/character_builder/getSubListAbilities/',
					data: $scope.sublists
				}).success(function(data) {
					$scope.subabilities = data;
				});
			}

			$scope.reloadAll = function() {
				$scope.setRace();
				$scope.getAllAbilities();
				$scope.getSubAbilities();
			}

			$scope.isSkill = function(ability) {
				return ability.la.is_footnote == 0;
			}

			$scope.isFootnote = function(ability) {
				return ability.la.is_footnote == 1;
			}

			$scope.addMessage = function() {
				$scope.chatMessages.$add({ name: $scope.nickname, message: $scope.message });
				$scope.message = "";
			}

			$scope.needsOptionPicked = function(ability) {
				if(ability.a.uses_option_list == 0) {
					return false;
				}

				if(ability.hasOwnProperty("option_picked")) {
					return false;
				}

				if(ability.hasOwnProperty("alo")) {
					return false;
				}

				return true;
			}

			$scope.abilityHeaderIsNew = function(ability) {
				if( !$scope.hasOwnProperty("currentHeader")) {
					$scope.currentHeader = ability.ag.name;
					return true;
				}

				if(ability.ag.name != $scope.currentHeader) {
					$scope.currentHeader = ability.ag.name;
					return true;
				}

				return false;
			}

			$scope.earnedBuild = function() {
				//$scope.character.$save()
				$scope.computeSpent();
			}

			$scope.print = function() {
				window.location = "/character_builder/printable/"+$scope.token;
			}

			$scope.edit = function() {
				window.location = "/character_builder/index/"+$scope.token;
			}

			$scope.startOver = function() {
				window.location = "/character_builder/";
			}
			$scope.goToTracking = function() {
				window.location = "/character_builder/tracking";
			}

			$scope.goToToken = function() {
				window.location = "/character_builder/index/"+$scope.goToken;
			}

			$scope.submitToCardTeam = function() {
				$scope.meta.emailsent = true;

				// $http({ 
				// 	method: 'POST', 
				// 	headers: { 'Content-Type': 'application/json' }, 
				// 	url: '/character_builder/sendemail/',
				// 	data: $scope.character
				// }).success(function(data) {
				// 	$scope.meta.emailsent = true;
				// });
			}

			$scope.importCard = function() {
				var payload = { character: $scope.character, abilities: $scope.savedabilities };

				$http({ 
					method: 'POST', 
					headers: { 'Content-Type': 'application/json' }, 
					url: '/character_builder/import/',
					data: payload
				}).success(function(data) {
					if(data == 0) {
						$scope.meta.importdone = true;
						$scope.importFailed = data;
					} else {
						$scope.meta.importdone = false;
						$scope.importFailed = "Import Failed, "+data+" skills still on target card.";
					}
				});
			}

			$scope.showQtySpacer = function(ability) {
				if($scope.meta.stage < 3) {
					return !$scope.showQty(ability);
				}

				return false;
			}
			$scope.showQty = function(ability) {
				if($scope.meta.stage >= 3) {
					return false;
				}

				if(ability.a.abilitytype_id == 1) {
					return true;
				}
				if(ability.a.abilitytype_id == 2) {
					return true;
				}
				if(ability.a.abilitytype_id == 24) {
					return true;
				}
				var aname = ability.a.ability_name;
				if(aname.search("\\+1") > -1) {
					return true;
				}
				return false;
			}

			$scope.clearOption = function(ability) {
				ability.option_picked = null;
			}

		}]);
	</script>


	<div ng-controller="NPMController" ng-init="initialize()">

		<div style="float:left; width:360px; margin-right:30px;">
			<div ng-show="meta.stage > 1">
				<h3>{{character.name}} ({{token}})</h3>
				<b>Player:</b> {{character.playerName}} ({{character.email}})<br/>
				<b>Race:</b> {{character.race.a.ability_name}}  <b>Religion:</b> {{character.religion.a.ability_name}}<br/>
				<table style="border: solid 1px black;">
					<tr>
						<td>Level:</td><td>{{level}}</td>
					</tr><tr>
						<td>Build Spent:</td><td>{{spentBuild}}</td>
					</tr><tr>
						<td>Unspent:</td><td>{{unspentBuild}}</td>
					</tr><tr>
						<td>Earned:</td><td>
						<input ng-change="earnedBuild()" ng-show="isAdmin == true" type="text" size="3" ng-model="character.startingBuild"/>
						<span ng-hide="isAdmin == true">{{character.startingBuild}}</span>
					</td>
					</tr><tr>
						<td>BP:</td><td>{{bodyPoints | number:0}}</td>
					</tr><tr>
						<td>BP Ratio:</td><td>{{bodyRatio | number:1}}</td>
					</tr><tr>
						<td>AP Multiple:</td><td>{{armorRatio | number:0}}</td>
					</tr><tr ng-show="MP > 0">
						<td>MP:</td><td>{{MP | number:0}}</td>
					</tr><tr ng-show="FP > 0">
						<td>FP:</td><td>{{FP | number:0}}</td>
					</tr><tr ng-show="CP > 0">
						<td>CP:</td><td>{{CP | number:0}}</td>
					</tr><tr ng-show="PP > 0">
						<td>PP:</td><td>{{PP | number:0}}</td>
					</tr><tr ng-show="HP > 0">
						<td>HP:</td><td>{{HP | number:0}}</td>
					</tr><tr ng-show="SP > 0">
						<td>SP:</td><td>{{SP | number:0}}</td>
					</tr><tr ng-show="DP > 0">
						<td>DP:</td><td>{{DP | number:0}}</td>
					</tr><tr ng-show="PsiP > 0">
						<td>PsiP:</td><td>{{PsiP | number:0}}</td>
					</tr>

				</table>
			</div>

			<div ng-show="meta.stage >= 1">
				<h2 ng-hide="printing == true">Chat</h2>
				<div ng-hide="printing == true" auto-scroll="chatMessages" style="border: solid black 1px; height: 200px; overflow: auto; overflow-x: hidden; margin:3px; background: white;">
					<div ng-repeat="c in chatMessages">{{c.name}}: {{c.message}}</div>
				</div>
				<div ng-hide="printing == true">
					<!-- input type="text" size="10" ng-model="nickname" placeholder="name"></input --> 
					<input type="text" size="56" style="border: solid black 1px; margin:3px;" ng-model="message" placeholder="message (press enter to submit)" ng-keydown="$event.which === 13 && addMessage()"></input>
				</div>

				<div>
				Chat Users: 
					<span ng-repeat="u in chatUsers.users">{{u.alias}}{{$last ? '' : ', '}}</span> 
				</div>
			</div>
		</div>

		<div ng-show="meta.importdone == true">
			<h2>This card has been imported as card {{character.cardnumber}}</h2>
		</div>
		<div ng-show="meta.readonly == true">
			Character is now read only
		</div>
		<div ng-show="meta.stage && printing == false">
			<button ng-click="startOver()">Home</button>
			<span ng-hide="meta.readonly == true && isAdmin == false">
				<button ng-click="goToStage(1)">1. Name, Race, Religion, Concept</button>
				<button ng-click="goToStage(2)">2. Professions and Skills</button>
				<button ng-click="goToStage(3)">3. Character Preview</button>
				<button ng-click="goToStage(4)">4. Final Check and Submission</button>
			</span>
			<span ng-show="isAdmin == true">
				<button ng-click="isAdmin = false">View as Player</button>

				<button ng-click="makeReadOnly(true)" ng-show="meta.readonly == false">5. Begin Import</button>
				<button ng-click="makeReadOnly(false)" ng-show="meta.readonly == true">5. Stop Import</button>
				<button ng-click="goToTracking()">Back to Tracking</button>
			</span>
		</div>

		<div ng-show="meta && !meta.stage">
			<h2>Welcome to the Knight Realms Character Builder!</h2>
			<button ng-click="getStarted()">Start New Character</button>

			<h4>Visit a Token</h4>
			<ul>
				<li>Enter Token to visit: <input type="text" ng-model="goToken" size="8"> <button ng-click="goToToken()">Go</button>
				</li>
			</ul>

			<h4>Saved Tokens</h4>
			<ul>
				<li ng-repeat="t in oldTokens">
					<a ng-href="/character_builder/index/{{t.token}}">{{t.token}} {{t.name}}</a>
					<button ng-click="forgetToken(t)">delete bookmark</button>
				</li>
			</ul>
		</div>

		<div ng-show="meta.stage == 1">
			<H2>Character Builder (beta)</H2>
		
			<h3>Guidelines</h3>
			This character builder does not enforce all of the character creation rules for Knight Realms. Please use the <a style="text-decoration:underline" href="http://www.knightrealms.com/documents/rulebook/knightrealms-rulebook-core.html" target="rulebook">Core Gameplay Rulebook</a> (specifically the section called <a style="text-decoration:underline" href="http://www.knightrealms.com/documents/rulebook/knightrealms-rulebook-core.html#toc17" target="rulebook">Making a Character</a>) as your guide when building a character.  Final approval of a character must be done by a Card Marshal or a New Player Marshal.  Characters built with this system cannot be played until approved and stamped as paid.
			<hr/>
		</div>

		<div ng-show="meta.stage == 1">
			<table>
				<tr><th>
					Your Chat Alias:
				</th><td>
					<input ng-change="saveAlias()" type="text" size="40" ng-model="nickname" placeholder="enter alias">
				</td></tr>
				<tr><th>
					Player Name:
					</th><td>
					<input ng-change="delayedSaveCharacter()" type="text" size="40" ng-model="character.playerName" placeholder="Last, First">
				</td></tr>
				<tr><th>
					Player Email:
					</th><td>
					<input ng-change="delayedSaveCharacter()" type="text" size="40" ng-model="character.email" placeholder="someone@somewhere">
				</td></tr>
				<tr>
					<th>Who Referred You?</th>
					<td>
						<input type="text" ng-model="character.referral" size="40" ng-change="delayedSaveCharacter()"> +2 build to character of referrer's choice
					</td>
				</tr>

				<tr>
					<th>
						Character Name:
					</th><td>
						<input ng-change="delayedSaveCharacter()" type="text" size="40" ng-model="character.name" placeholder="enter character name">
						*characters given name, not an alias!
					</td>
				</tr>
				<tr><th>
					Race:
					</th><td>
						<select ng-change="setRace()" ng-model="character.race" ng-options="r as r.a.ability_name for r in races track by r.a.id">
						</select>
						Racial Body Modifier {{character.race.a.Ratio}}</td>
				</tr>

				<tr><th>
					Religion:
					</th><td>
					<select ng-change="setReligion()" ng-model="character.religion" ng-options="r.a.ability_name for r in religions track by r.a.id">
						<option value="">None</option>
					</select>
				</td></tr>
				<tr>
					<th>
					</th>
					<td colspan="2">
						Use this space below to write down notes to help you with your character concept.  For example, writing a few sentences that capture the essence of your concept or give details that can be helpful in creation.  This will help to focus your character and serve as a guide to others who might be helping you build out the character.
					</td>
				</tr>
				<tr>
					<th>Character Concept:</th>
					<td colspan="2">
						<textarea name="entry" id="entry" ng-model="character.concept" style="width:500px; height:100px;" ng-change="delayedSaveCharacter()"></textarea>
					</td>
				</tr>
				<tr>
					<th>Token:</th>
					<td>
						Your randomly generated access token is
						<b><a style="text-decoration:underline" ng-href="/character_builder/index/{{token}}">{{token}}</a></b>
						without this token you will not be able to return to this character.  Either bookmark this URL or write the token down or, once you have entered a character name, press the Save Token button below.<br/>
						<button ng-click="rememberToken()">Save Token</button>
						<span ng-show="tokenSaved == true">Saved!</span>
					</td>
				</tr>
			</table>

		</div>

		<div ng-show="meta.stage == 2">
			<h2>Character Concept</h2>
			<pre>{{character.concept}}</pre>
		</div>

		<div ng-show="meta.stage == 2" style="float:left; margin-right:20px; width:500px;">
			<h2>Available Skills</h2>
			<h4>{{unspentBuild}} build remaining</h4>
			<br/>

			<input type="radio" ng-model="meta.showProfessions" value="common">Racial / Commoner</input>
			<input type="radio" ng-model="meta.showProfessions" value="pro">Professions</input>
			<input type="radio" ng-model="meta.showProfessions" value="other">Other Lists</input>

			<br/><br/>

			<div ng-show="meta.showProfessions == 'common'">
				<table class="skilltable">
					<tr>
						<td style="border:none;"></td>
						<td colspan="3">
							Commoner and Racial Skills
						</td>
					</tr>
					<tr><th style="border:none;"></th><th>Skill</th><th>Build</th><th>Pre-Requisites</th><tr ng-repeat="r in datastore.otherabilities">
						<td ng-show="isSkill(r)" style="border:none;">
							<button ng-click="addSelected(r)" ng-show="unspentBuild > 0">add</button>
						</td>
						<td ng-show="isSkill(r)">
							{{r | skillListing}}
						</td>
						<td ng-show="isSkill(r)">
							{{r.la.build_cost}}
						</td>
						<td ng-show="isSkill(r)">
							{{r.la.prerequisites}}
						</td>
						<td ng-show="isFootnote(r)" style="border:none;"></td>
						<td colspan="3" ng-show="isFootnote(r)">
							<b>{{r.la.footnote}}</b>
						</td>
					</tr>
				</table>
			</div>

			<div ng-show="meta.showProfessions == 'pro'">

				<table class="skilltable">

					<tr>
						<td style="border:none;">
							<button ng-click="addProfession()" ng-show="unspentBuild > 0">add</button>
						</td>
						<td colspan="3">
							<select ng-change="getAllAbilities()" ng-model="character.profession" ng-options="p.a.ability_name for p in datastore.lowerlists track by p.a.id">
							</select> (P) is for Penalized, (B) is for Bloodline
						</td>
					</tr>

					<tr><th style="border:none;"></th><th>Skill</th><th>Build</th><th>Pre-Requisites</th></tr>
					<tr ng-repeat="r in datastore.abilities">
						<td ng-show="isSkill(r)" style="border:none;">
							<button ng-click="addSelected(r)" ng-show="unspentBuild > 0">add</button>
						</td>
						<td ng-show="isSkill(r)">
							<span ng-show="nickname == 'debug'">{{r.a.id}} </span>{{r | skillListing}}
						</td>
						<td ng-show="isSkill(r)">
							{{r.la.build_cost}}
						</td>
						<td ng-show="isSkill(r)">
							{{r.la.prerequisites}}
						</td>
						<td ng-show="isFootnote(r)" style="border:none;"></td>
						<td colspan="3" ng-show="isFootnote(r)">
							<b>{{r.la.footnote}}</b>
						</td>
					</tr>
				</table>
			</div>

			<div ng-show="meta.showProfessions == 'other'">

				<table class="skilltable">

					<tr>
						<td style="border:none;"></td>
						<td colspan="3">
							<select ng-change="getSubAbilities()" ng-model="datastore.other" ng-options="p.ability.ability_name for p in subabilities track by p.ability.id">
							</select>
						</td>
					</tr>

					<tr><th style="border:none;"></th><th>Skill</th><th>Build</th><th>Pre-Requisites</th></tr>
					<tr ng-repeat="r in datastore.other.results">
						<td ng-show="isSkill(r)" style="border:none;">
							<button ng-click="addSelected(r)" ng-show="unspentBuild > 0">add</button>
						</td>
						<td ng-show="isSkill(r)">
							{{r | skillListing}}
						</td>
						<td ng-show="isSkill(r)">
							{{r.la.build_cost}}
						</td>
						<td ng-show="isSkill(r)">
							{{r.la.prerequisites}}
						</td>
						<td ng-show="isFootnote(r)" style="border:none;"></td>
						<td colspan="3" ng-show="isFootnote(r)">
							<b>{{r.la.footnote}}</b>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<div ng-show="meta.stage == 4">
			<br/>
			<ul>
				<li>Please enter any notes you would like the Card Team to consider as they review your character.</li>
				<li>Once you click submit the Card Team will take what is here and convert it into an official character card.</li>
				<li>Edits that you make after you click "Final Submit" are not guaranteed to appear on your final card</li>
				<li>Please be patient -- the team may notify you when the import is done, but the team may also be busy and forget to do so, please allow several days before checking in on progress</li>
			</ul>
			<textarea name="entry" id="entry" ng-model="character.submission" style="width:500px; height:100px;" ng-change="delayedSaveCharacter()"></textarea>
			<br/>
			<button ng-show="meta.emailsent == false" ng-click="submitToCardTeam()">Final Submit</button>
			<div ng-show="meta.emailsent == true">Email sent to card team!</div>
		</div>

		<div ng-show="meta.stage == 4 && meta.emailsent == true && isAdmin == true && meta.importdone == false">
			<h2>Card Import</h2>
			Referred by: {{character.referral}}<br/>
			<br/>IMPORTANT -- CARD being imported into MUST already be Empty<br/><br/>
			Enter Card Number to Import To: <input type="text" ng-change="delayedSaveCharacter()" ng-model="character.cardnumber">
			<button ng-click="importCard()">Import Card</button>
			<h4 ng-show="importFailed">{{importFailed}}</h4>
		</div>

		<div ng-show="meta.stage > 1">

			<h2 ng-show="meta.stage < 3">Selected Lists and Skills</h2>
			<div ng-show="meta.stage == 3"/>
				<br/>
				<div ng-show="printing == false">
					<button ng-click="print()">Show Printable Version</button>
				</div>
				<div ng-show="printing == true">
					<button ng-click="edit()">Back</button>
				</div>
			</div>

			<ul>
				<li ng-hide="character.race">Select your Race (on page 1)</li>
				<li ng-show="meta.profession_count < 1">Add your first Profession <button ng-click="addProfession()" ng-show="character.profession">add {{character.profession.a.ability_name}}</button></li>
				<li ng-show="meta.profession_count > 1 && spentBuild < 50">You may not purchase a second Profession until you have spent 50 build</li>
				<li ng-repeat="ability in savedabilities" ng-show="needsOptionPicked(ability)">
					Pick Option for: {{ability.a.ability_name}}
				</li>
			</ul>

			<table>
				<tr>
					<td style="vertical-align:top;" ng-repeat="skills in skillColumns">
						<div ng-show="meta.stage > 1" ng-repeat="(header,data) in skills">
						<h4>{{header}}</h4>

						<table>
							<tr ng-repeat="ability in data">
								<td style="vertical-align:top;">
									<button ng-click="removeAbility(ability)" ng-show="meta.stage < 3">x</button>
								</td>
								<td style="vertical-align:top;">
									<span ng-show="showQtySpacer(ability)">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
									<input type="text" size="3" ng-model="ability.qty" ng-show="showQty(ability)" ng-change="ability.qtychg = 1" ng-show="meta.stage < 3"/>
								</td>
								<td style="vertical-align:top;">
									<span ng-bind-html="ability | abilityDisplay"></span>
									<button ng-show="ability.qtychg > 0" ng-click="updateQuantity(ability)">save qty change</button>							
									<pre ng-show="abilityBubbleShow(ability)">{{ability | abilityBubbles}}</pre>
									<span ng-show="needsOptionPicked(ability)">
										<select ng-change="savedabilities.$save(ability)" ng-model="ability.option_picked" ng-options="opt.a.ability_name for opt in ability.options track by opt.a.id">
										</select>
									</span>
									{{ability.option_picked.a.ability_name}}
								</td>
								<td><button ng-click="increaseCost(ability)" ng-show="ability.a.cost_increase_interval > 0 && meta.stage < 3">increase cost</button></td>

							</tr>
						</table>

					</td>
				</tr>
			</table>
		</div>

	</div>

</div>

