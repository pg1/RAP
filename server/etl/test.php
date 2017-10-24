<?php
/**
 * Test a job
 */

//connect to db
include_once dirname(__FILE__) . '/../server/config.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->set_charset("utf8");
if($db->connect_errno > 0){
    die('Unable to connect to database [' . $db->connect_error . ']');
}

if(isset($argv[1])) $job = $argv[1];
include_once dirname(__FILE__) . "/jobs/$job.php";
$job = "job{$job}";
$job = new $job($db);

$job->run(date('Y-m-d'));



