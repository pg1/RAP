<?php
/**
 * ETL job for analytics summary
 */

//connect to db
include_once dirname(__FILE__) . '/../config.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->set_charset("utf8");
if($db->connect_errno > 0){
    die('Unable to connect to database [' . $db->connect_error . ']');
}

$dateStart = date('Y-m-d', strtotime('-1 day'));
$dateEnd = date('Y-m-d');

$tmp = array_diff(scandir(dirname(__FILE__) . '/jobs/'), array('.', '..'));
$jobs = array();
foreach($tmp as $job){
    $jobs[] = str_replace('.php', '', $job);
}

//if params are set 
if(isset($argv[1])) $dateStart = $argv[1];
if(isset($argv[2])) $dateEnd = $argv[2];
if(isset($argv[3])) $jobs = array($argv[3]);

$datePeriod = new DatePeriod(
	new DateTime($dateStart),
	new DateInterval('P1D'),
	new DateTime($dateEnd)
);

if($dateStart == $dateEnd) $datePeriod = array($dateStart);

echo date("Y-m-d H:i:s") . " - INFO: Running ETL jobs between $dateStart and $dateEnd\n";

//include jobs
$jobList = array();
foreach($jobs as $job){
    include_once dirname(__FILE__) . "/jobs/$job.php";
    $job = "job{$job}";
    if(class_exists($job)){ 
        $jobList[] = new $job($db)    ;
    }
}


foreach($datePeriod as $date) { 
    if(is_object($date)) $date = $date->format('Y-m-d');
    
    //clear tmp table and add data
    echo date("Y-m-d H:i:s") . " - INFO: Creating tmp table for $date\n";
    $db->query("DROP TABLE events_tmp");
    $db->query("CREATE TABLE events_tmp LIKE events");
    $db->query("ALTER TABLE events_tmp DISABLE KEYS");
    $db->query("INSERT INTO events_tmp 
                SELECT * FROM events 
                WHERE CONVERT_TZ('$date 00:00:00', '+00:00','".TIMEZONE."') <= created AND 
                      CONVERT_TZ('$date 23:59:59', '+00:00','".TIMEZONE."') >= created
            ");
    $db->query("ALTER TABLE events_tmp ENABLE KEYS");
    
    //run each job
    foreach($jobList as $job){
        $job->run($date);
    }
}



