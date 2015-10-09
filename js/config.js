// for navigator language
var lang = window.navigator.language;
// you can change the language
// var lang = 'en';

var apikey = 'b5d537cc83afcc28291190d015656779';

//change weather params here:
//units: metric or imperial
var weatherParams = {
    'q':'Minneapolis, MN',
    'units':'imperial',
    'lang':lang,
    'APPID':apikey
};

//var feed = 'http://feeds.nos.nl/nosjournaal?format=rss';
//var feed = 'http://www.nu.nl/feeds/rss/achterklap.rss';
//var feed = 'http://www.nu.nl/feeds/rss/opmerkelijk.rss';
var feed = 'http://www.nytimes.com/services/xml/rss/nyt/HomePage.xml';

// compliments:
var morning = [
            'Good morning, handsome!',
            'Enjoy your day!',
            'How was your sleep?'
        ];
        
var afternoon = [
            'Hello, beauty!',
            'You look sexy!',
            'Looking good today!'
        ];
       
var evening = [
            'Wow, you look hot!',
            'You look nice!',
            'Hi, sexy!'
        ];
