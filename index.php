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
        <div class="date small dimmed">{{ clock | date:'EEEE, MMMM d y' }}</div>
        <div class="time">{{ clock | date:'HH:mm' }}<span class="sec">{{ clock | date:'ss' }}</span></div>
      </div>
      <div ng-controller="CalCtrl">
        <div class="calendar xxsmall">{{ calendar }}</div>
      </div>
    </div>

	<div class="top right">
      <div ng-controller="WeatherCtrl">
        <div class="windsun small dimmed">
          <span ng-style="sun.sunriseStyle">
            <span class="wi wi-sunrise xdimmed"></span> {{ sun.sunrise | date: 'HH:mm' }}
          </span>
          <span ng-style="sun.sunsetStyle">
            <span class="wi wi-sunset xdimmed"></span> {{ sun.sunset | date: 'HH:mm' }}
          </span>
        </div>
        <div class="temp"><span class="icon dimmed wi {{ temp.icon }}"></span>{{ temp.temp + '&deg;' }}</div>
        <div class="forecast small dimmed">
          <table class="forecast-table">
            <tr ng-repeat="i in forecast" ng-style="{ opacity: {{ 1 - $index * 0.155 }}}">
              <td class="day">{{ i.date | date: 'EEE' }}</td>
              <td class="icon-small {{ i.icon }}"></td>
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
        <div class="fade compliment light" ng-show="compliment.show">{{ compliment.text }}</div>
      </div>
    </div>

	<div class="bottom center-hor">
      <div ng-controller="NewsCtrl">
        <div class="news medium">{{ news }}</div>
      </div>
    </div>

    <script src="js/angular.min.js"></script>
    <script src="js/angular-animate.min.js"></script>
    <script src="js/config.js"></script>

    <script>
var iconTable = {
    '01d':'wi-day-sunny',
    '02d':'wi-day-cloudy',
    '03d':'wi-cloudy',
    '04d':'wi-cloudy-windy',
    '09d':'wi-showers',
    '10d':'wi-rain',
    '11d':'wi-thunderstorm',
    '13d':'wi-snow',
    '50d':'wi-fog',
    '01n':'wi-night-clear',
    '02n':'wi-night-cloudy',
    '03n':'wi-night-cloudy',
    '04n':'wi-night-cloudy',
    '09n':'wi-night-showers',
    '10n':'wi-night-rain',
    '11n':'wi-night-thunderstorm',
    '13n':'wi-night-snow',
    '50n':'wi-night-alt-cloudy-windy'
};
var app = angular.module('mirror', ['ngAnimate']);
app.controller('init', function ($scope) {});
app.controller('TimeCtrl', function ($scope, $interval) {
    var tick = function () {
        $scope.clock = new Date();
    }
    tick();
    $interval(tick, 1000);
});
app.controller('CalCtrl', function ($scope, $interval, $http) {
    var update = function () {
        if (typeof calendarFeed == 'undefined') {
            return false;
        }
        $http.get('calendar.php?url=' + encodeURIComponent(calendarFeed)).
            then(function (response) {
                if ('OK' == response.statusText) {
                    console.log(response.data);
                } else {
                    console.log(response.statusText);
                }
            }, function (response) {
                console.log('error');
                console.log(response);
            });
    }
    update();
    $interval(update, 60000);
});
app.controller('FuzzyCtrl', function ($scope, $interval, $timeout) {
    $scope.compliment = {
        show: false,
        text: ''
    };
    var warmFuzzy = function () {
        if ($scope.compliment.show) {
            return $scope.compliment.show = false;
        }
        var compliments;
        var compliment;
        var date = new Date();
        var hour = date.getHours();
        if (3 <= hour && hour < 12) {
            compliments = morning;
        } else if (12 <= hour && hour < 17) {
            compliments = afternoon;
        } else {
            compliments = evening;
        }

        do {
            compliment = Math.floor(Math.random() * compliments.length);
        } while (compliments[compliment] == $scope.compliment.text);

        $scope.compliment = {
            show: !$scope.compliment.show,
            text: compliments[compliment]
        };
        $timeout(function () { $scope.compliment.show = false; }, 25000);
    }
    warmFuzzy();
    $interval(warmFuzzy, 30000);
});
app.controller('WeatherCtrl', function ($scope, $http, $interval) {
    var weather = function () {
        $http({
            method: 'get',
            url: 'http://api.openweathermap.org/data/2.5/weather',
            params: weatherParams
        }).then(function (response) {
            if (response && response.data && response.data.main) {
                var temp = {
                    'temp': Math.round(response.data.main.temp * 10) / 10,
                    'min': Math.round(response.data.main.temp_min * 10) / 10,
                    'max': Math.round(response.data.main.temp_max * 10) / 10,
                    'icon': iconTable[response.data.weather[0].icon]
                };
                $scope.temp = temp;

                var sun = {
                    'sunrise': new Date(response.data.sys.sunrise * 1000),
                    'sunset': new Date(response.data.sys.sunset * 1000),
                    'now': new Date(),
                    'sunriseStyle': {
                        opacity: 0.5
                    },
                    'sunsetStyle': {
                        opacity: 0.5
                    }
                };
                if (sun.now < sun.sunrise) {
                    sun.sunriseStyle.opacity = 1;
                } else if (sun.now < sun.sunset) {
                    sun.sunsetStyle.opacity = 1;
                }
                $scope.sun = sun;
            }
        }, function (response) {
            console.log(response);
        });
    }
    weather();
    $interval(weather, 60000);

    var forecast = function () {
        $http({
            method: 'get',
            url: 'http://api.openweathermap.org/data/2.5/forecast/daily',
            params: weatherParams
        }).then(function (response) {
            if (response && response.data && response.data.list) {
                var forecastData = [];
                for (var i in response.data.list) {
                    var row = response.data.list[i];
                    forecastData.push({
                        'timestamp': row.dt,
                        'date': new Date(row.dt * 1000),
                        'icon': iconTable[row.weather[0].icon],
                        'temp_min': Math.round(row.temp.min * 10) / 10,
                        'temp_max': Math.round(row.temp.max * 10) / 10
                    });
                }
                $scope.forecast = forecastData;
            }
        }, function (response) {
            console.log('error');
            console.log(response);
        });
    }
    forecast();
    $interval(forecast, 60000);
});
app.controller('NewsCtrl', ['$scope', '$interval', 'FeedService', function ($scope, $interval, Feed) {
    var news = [];
    var headline = 0;
    var update = function () {
        news = [];
        Feed.parseFeed(feed).
            then(function (response) {
                for (var i in response.data.responseData.feed.entries) {
                    news.push(response.data.responseData.feed.entries[i].title);
                }
            }, function (response) {
                console.log('error');
                console.log(response);
            });
    };
    update();
    var rotate = function () {
        $scope.news = news[headline++ % news.length];
    };
    rotate();
    $interval(update, 300000);
    $interval(rotate, 10000);
}]);
app.factory('FeedService', ['$http', function ($http) {
    return {
        parseFeed: function (url) {
            return $http.jsonp('https://ajax.googleapis.com/ajax/services/feed/load?v=1.0&callback=JSON_CALLBACK&q=' + encodeURIComponent(url));
        }
    };
}]);
    </script>
</body>
</html>
