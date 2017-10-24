<?php
/**
 * Main config
 */

//DB settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rap');

//Temp location
define('TMP_DIR', '/tmp/rap-logs/');

//AWS Cloudfront Locations
define('CFLOGS', 's3://analytics/cf-logs/');
define('CFLOGSARCHIVE', 's3://analytics/cf-logs-archive/');

//Apache logs
define('APACHELOGS', '/var/log/apache2/access*log');

//Webserver: apache OR awscf
define('WEBSERVER', 'apache');

//Timezone for daily summary jobs UTC+2
define('TIMEZONE', '+02:00');

