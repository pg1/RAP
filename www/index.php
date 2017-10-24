<?php
/**
 * Analytics Platform Api
 */

include_once '../server/config.php';
include_once '../server/api/AnalyticsApi.php';

$analyticsApi = new AnalyticsApi();

//set params
$params = array(
    'start' => date('Y-m-01'), 
    'end' => date('Y-m-d'),
    'groupby' => 'campid',
    'eventid' => 1,
    'groupby2' => '',
    'groupby3' => '',
    'country' => '',
    'state' => '',
    'states' => '',
    'platform' => '',
    'campid' => '',
    'eventsource' => '',
    'trackingcode' => '',
    'limit' => 10000
);

foreach($params as $idx=>$val){
    if(isset($_GET[$idx])) $params[$idx] = $analyticsApi->db->real_escape_string($_GET[$idx]);
}


//select action
if(isset($_GET['action'])){
    switch($_GET['action']){
        case 'find':
            if(!isset($_GET['field']) || !isset($_GET['value'])) die('Field or value is missing!');
            $data = $analyticsApi->find($_GET['field'], $_GET['value'], $params);
        break;

        case 'summary':
            if(!isset($_GET['table'])) die('Table is missing!');
            $table = preg_replace('/[^a-z_]*/', '', $_GET['table']);
            if($params['groupby'] == 'campid') $params['groupby'] = '';
            $data = $analyticsApi->getSummary($table, $params);
        break;

    }
}else{
    $data = $analyticsApi->getData($params);
}

if(isset($_GET['format']) && $_GET['format'] == 'csv'){
	header("Content-type: application/csv-tab-delimited-table; charset=utf-8" );
	header("Content-Disposition: attachment; filename=analytics_".date("Ymd-His").".csv" );
	header("Content-Transfer-Encoding: binary" );
	echo '"' . implode('","', array_keys($data[0])) . '"' . "\n";
	foreach($data as $row){
		echo implode(',', $row) . "\n";
	}
	die();
}

header('Content-Type: application/json');
echo json_encode($data);