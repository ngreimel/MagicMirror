<!doctype html>
<html xmlns:ng="http://angularjs.org" ng-app="mirror" ng-controller="init">
<head>
	<title>Magic Mirror</title>
	<link rel="stylesheet" type="text/css" href="css/main.css">
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
        <div class="date small dimmed">{{ clock | date:'EEEE, MMMM d y' }}</div>
        <div class="time">{{ clock | date:'HH:mm' }}<span class="sec">{{ clock | date:'ss' }}</span></div>
      </div>
      <div ng-controller="CalCtrl">
        <div class="calendar xxsmall">{{ calendar }}</div>
      </div>
    </div>

	<div class="top right">
      <div class="fade" ng-controller="WeatherCtrl" ng-show="show">
        <div class="windsun small dimmed">
          <span ng-style="sun.sunriseStyle">
            <span class="wi wi-sunrise xdimmed"></span> {{ sun.sunrise | date: 'HH:mm' }}
          </span>
          <span ng-style="sun.sunsetStyle">
            <span class="wi wi-sunset xdimmed"></span> {{ sun.sunset | date: 'HH:mm' }}
          </span>
        </div>
        <div class="temp"><span class="icon dimmed wi" ng-class="temp.icon"></span>{{ temp.temp + '&deg;' }}</div>
        <div class="forecast small dimmed">
          <table class="forecast-table">
            <tr ng-repeat="i in forecast" ng-style="{ opacity: {{ 1 - $index * 0.155 }} }">
              <td class="day">{{ i.date | date: 'EEE' }}</td>
              <td class="icon-small" ng-class="i.icon"></td>
              <td class="temp-max">{{ i.temp_max }}</td>
              <td class="temp-min">{{ i.temp_min }}</td>
            </tr>
          </table>
        </div>
      </div>
    </div>

	<div class="center-ver center-hor"></div>

	<div class="lower-third center-hor">
      <div ng-controller="FuzzyCtrl">
        <div class="fade compliment light" ng-show="show">{{ compliment }}</div>
      </div>
    </div>

	<div class="bottom center-hor">
      <div ng-controller="NewsCtrl">
        <div class="fade news medium" ng-show="show">{{ news }}</div>
      </div>
    </div>

    <script src="js/jquery-2.1.4.min.js"></script>
    <script src="js/angular.min.js"></script>
    <script src="js/angular-animate.min.js"></script>
    <script src="js/config.js"></script>
    <script src="js/main.js?<?php echo microtime(true); ?>"></script>
</body>
</html>
