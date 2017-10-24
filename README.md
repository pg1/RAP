# RAP - Retro Analytics Platform

Retro Analytics Platform was born about 15 years ago and it's still used in many places.
You can use it to record multiple events like page loads, app installs, clicks, taps, etc.
Then you can do A/B testing analysis, funnel analysis, CRO, etc. 

## How it works?

Just add s.gif?ev=1&t=test12-ab&es=event_source&ts=1234568&cid=testcampid to your page or app. Setup log parser. 

## Install

- Clone or donwload code to your server. 
- Run composer. 
- Download Maxmind's geolite2 City db.
- Create a new database
- Import schema docs/schema.sql

~~~~~
git clone 
cd rap
composer install
wget http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz
tar -xvf GeoLite2-City.tar.gz
mv GeoLite2-City_20.../GeoLite2-City.mmdb .
mysql -u... rap < docs/schema.sql

~~~~~

## Setup

- Setup tracking
- Update server/config.php
- Set cron to run log-parser/run.php every hour or so 
- Create a new subdomain and set enpoint to ...rap/www
- If you have too much data start using ETL jobs and create summary tables


## Tracker

~~~~~
<script>

window.RAPAnalytics = (function() {

	'use strict';
	
	function getSource() {
	    var parser = document.createElement('a');
	    parser.href = window.location;

	    var leadSource = parser.hostname;
	    leadSource = leadSource.replace("www.", "");
	    leadSource = leadSource.split(".")[0]; // hostname without .com, .net etc.
	    return leadSource;
  	}

    function init(){
    	var img = new Image();
        img.src = "https://yoursub.cloudfront.net/s.gif?ev=1&t=test&ls="+getSource()+"&ts="+Date.now()+"&cid=yourcampid";
    }


	if (window.attachEvent){
		window.attachEvent('onload', init);
	}else{
		window.addEventListener('load', init, false);
	}
	
	return null;

})();

</script>
~~~~~

## Api

You can use the api to get latest data. You can use the following parameters to get a summary:
- start: start date. Default is first day of current month 
- end: end date. Default is current day
- eventid: Eventid. Default is 1  
- groupby: Group data by x field
- groupby2: More grouping
- groupby3: One more grouping
- country: Filter data by country
- state: Filter by state
- platform: Filter by platform. 1 Desktop, 2 Smartphone, 3 Tablet
- campid: Filter by campaign id
- eventsource: Filter by event source
- trackingcode: Filter by tracking code
- limit: Limit results 
- format: default is json. You can use csv too

Examples: 
- List eventsources and platforms from 2017-10-24: ?groupby=eventsource&groupby2=platform&start=2017-10-24 
- Get all US events by platform: ?groupby=state&groupby2=platform&country=US
