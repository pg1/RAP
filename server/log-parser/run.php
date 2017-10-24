<?php 
/**
 * Webserver logs parser, loader and enrich
 */

include_once dirname(__FILE__) . '/../../vendor/autoload.php';


//connect to db
include_once dirname(__FILE__) . '/../config.php';
$database = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($database->connect_errno){
    die(date("Y-m-d H:i:s") . " - ERROR: Failed to connect to database: (".$database->connect_errno.") ".$database->connect_error);    
}


include_once 'libs/parser-' . WEBSERVER . '.php';
include_once 'libs/enrich.php';
