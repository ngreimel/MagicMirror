"use strict";

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
app.controller('init', function ($scope, $http, $interval) {
    var checkVersion = function () {
        $http.get('githash.php').
            then(function (response) {
                if (response && response.data && response.data.gitHash != gitHash) {
                    window.location.reload();
                    window.location.href = window.location.href;
                }
            }, function (response) {}
            );
    };
    $interval(checkVersion, 5000);
});
app.controller('TimeCtrl', function ($scope, $interval) {
    var tick = function () {
        $scope.clock = new Date();
    }
    tick();
    $interval(tick, 1000);
});
app.controller('CalCtrl', ['$scope', '$interval', '$timeout', '$http', 'MomentService', function ($scope, $interval, $timeout, $http, Moment) {
    $scope.events = [];
    var update = function () {
        if (typeof calendarFeed == 'undefined') {
            return false;
        }
        $http.get('calendar.php?url=' + encodeURIComponent(calendarFeed)).
            then(function (response) {
                if ('OK' == response.statusText) {
                    var eventList = [];
                    for (var i in response.data) {
                        var event = response.data[i];
                        eventList.push({
                            description: event.SUMMARY.replace(/\\(.)/mg, "$1"),
                            days: Moment.fromNow(event.DTSTART)
                        });
                    }
                    $scope.events = eventList;
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
}]);
app.controller('FuzzyCtrl', function ($scope, $interval, $timeout) {
    $scope.compliment = '';
    var warmFuzzy = function () {
        var compliment;
        var compliments;
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
        } while ($scope.compliment == compliments[compliment]);

        $scope.show = false;
        $timeout(function () {
            $scope.compliment = compliments[compliment];
            $scope.show = true;
        }, 1000);
    }
    $timeout(warmFuzzy, 2500);
    $interval(warmFuzzy, 30000);
});
app.controller('WeatherCtrl', function ($scope, $http, $interval, $timeout) {
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
            $scope.show = true;
        }, function (response) {
            console.log(response);
        });
    }

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
    weather();
    $interval(function () {
        forecast();
        weather();
    }, 60000);
});
app.controller('NewsCtrl', ['$scope', '$interval', '$timeout', 'FeedService', function ($scope, $interval, $timeout, Feed) {
    var stories = [];
    var headline = 0;
    var update = function () {
        stories = [];
        headline = 0;
        Feed.parseFeed(feed).
            then(function (response) {
                for (var i in response.data.responseData.feed.entries) {
                    if (response.data.responseData.feed.entries[i].title) {
                        stories.push(response.data.responseData.feed.entries[i].title);
                    }
                }
            }, function (response) {
                console.log('error');
                console.log(response);
            });
    };
    var rotate = function () {
        $scope.show = false;
        if (stories.length) {
            $timeout(function () {
                $scope.news = stories[headline++ % stories.length];
                $scope.show = true;
            }, 1000);
        }
    };
    update();
    $interval(update, 300000);
    $interval(rotate, 5500);
}]);
app.factory('FeedService', ['$http', function ($http) {
    return {
        parseFeed: function (url) {
            return $http.jsonp('https://ajax.googleapis.com/ajax/services/feed/load?v=1.0&callback=JSON_CALLBACK&q=' + encodeURIComponent(url));
        }
    };
}]);
app.factory('MomentService', function () {
    return {
        fromNow: function (time) {
            var days = [];
            days.push('Sunday');
            days.push('Monday');
            days.push('Tuesday');
            days.push('Wednesday');
            days.push('Thursday');
            days.push('Friday');
            days.push('Saturday');

            if (typeof time != 'Date') {
                var dateString = time.substring(0,4)
                               + '-' + time.substring(4,6)
                               + '-' + time.substring(6,8);
                if (time.length > 8) {
                    dateString += 'T' + time.substring(9,11)
                                + ':' + time.substring(11,13)
                                + ':' + time.substring(13);
                }
                time = new Date(dateString);
            }
            var now = new Date();
            var diff = Math.abs(time.getTime() - now.getTime()) / 1000;
            var ago = time.getTime() < now.getTime() ? ' ago' : '';
            if (diff < 45) {
                return 'a few seconds' + ago;
            } else if (diff < 90) {
                return 'a minute' + ago;
            } else if (diff < 45 * 60) {
                return Math.round(diff / 60) + ' minutes' + ago;
            } else if (diff < 90 * 60) {
                return 'an hour' + ago;
            } else if (diff < 5 * 60 * 60) {
                return Math.round(diff / 60 / 60) + ' hours' + ago;
            } else if (diff < 48 * 60 * 60) {
                var str = '';
                if (time.getDay() == now.getDay()) {
                    str = 'Today at ';
                } else if (diff < 7 * 24 * 60 * 60) {
                    if ((time.getDay() - now.getDay()) % 7 == 1) {
                        if (time.getTime() > now.getTime()) {
                            str = 'Tomorrow at ';
                        } else {
                            str = 'Yesterday at ';
                        }
                    } else {
                        if (time.getTime() < now.getTime()) {
                            str = 'Last ';
                        }
                        str += days[time.getDay()] + ' at ';
                    }
                } else {
                    return time.toLocaleDateString();
                }
                str += time.toTimeString().substring(0, 5);
                return str;
            } else if (diff < 25 * 24 * 60 * 60) {
                return Math.round(diff / 24 / 60 / 60) + ' days' + ago;
            } else if (diff < 45 * 24 * 60 * 60) {
                return 'a month' + ago;
            } else if (diff < 345 * 24 * 60 * 60) {
                return Math.round(diff / 30 / 24 / 60 / 60) + ' months' + ago;
            } else if (diff < 545 * 24 * 60 * 60) {
                return 'a year' + ago;
            } else {
                return Math.round(diff / 365 / 24 / 60 / 60) + ' years' + ago;
            }
        }
    };
});
app.animation('.fade', function () {
    return {
        addClass: function (element, className, doneFn) {
            jQuery(element).fadeOut(1000, doneFn);
        },
        removeClass: function (element, className, doneFn) {
            element.css('display', 'none');
            jQuery(element).fadeIn(1000, doneFn);
        }
    };
});
