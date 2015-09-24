<!doctype html>
<html xmlns:ng="http://angularjs.org" ng-app="mirror" ng-controller="init">
<head>
	<title>Magic Mirror</title>
	<style type="text/css">
		<?php include('css/main.css') ?>
	</style>
	<link rel="stylesheet" type="text/css" href="css/weather-icons.css">
	<script type="text/javascript">
		var gitHash = '<?php echo trim(`git rev-parse HEAD`) ?>';
	</script>
	<meta name="google" value="notranslate" />
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
</head>
<body>

	<div class="top left">
      <div ng-controller="TimeCtrl">
        <div class="date small dimmed">{{ clock | date:'EEE MMM d y' }}</div>
        <div class="time">{{ clock | date:'HH:mm' }}<span class="sec">{{ clock | date:'ss' }}</span></div>
        <div class="calendar xxsmall">{{ calendar }}</div>
      </div>
    </div>

	<div class="top right">
        <div class="windsun small dimmed">{{ windsun }}</div>
        <div class="temp">{{ temp }}</div>
        <div class="forecast small dimmed">{{ forecast }}</div>
    </div>

	<div class="center-ver center-hor"></div>

	<div class="lower-third center-hor">
      <div ng-controller="FuzzyCtrl">
        <div class="compliment light">{{ compliment }}</div>
      </div>
    </div>

	<div class="bottom center-hor">
        <div class="news medium">{{ news }}</div>
    </div>

    <script src="js/angular.min.js"></script>
    <script src="js/config.js"></script>

    <script>
var app = angular.module('mirror', []);
app.controller('init', function ($scope) {});
app.controller('TimeCtrl', function ($scope, $interval) {
    var tick = function() {
        $scope.clock = new Date();
    }
    tick();
    $interval(tick, 1000);
});
app.controller('FuzzyCtrl', function ($scope, $interval) {
    var warmFuzzy = function () {
        var compliments;
        var compliment = 0;
        var date = new Date();
        var hour = date.getHours();
        if (3 <= hour && hour < 12) {
            compliments = morning;
        } else if (12 <= hour && hour < 17) {
            compliments = afternoon;
        } else {
            compliments = evening;
        }

        while (compliments[compliment] == $scope.compliment) {
            compliment = Math.floor(Math.random() * compliments.length);
        }

        $scope.compliment = compliments[compliment];
    }
    warmFuzzy();
    $interval(warmFuzzy, 30000);
});
    </script>
</body>
</html>
