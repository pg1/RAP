<?php 
/**
 * Parse apache logs
 *
 */


echo date("Y-m-d H:i:s") . " - INFO: Reading apache logs\n";
if(!is_dir(TMP_DIR)) mkdir(TMP_DIR, 0777, true);

$shellLog = shell_exec("grep s.gif " . APACHELOGS . " > " . TMP_DIR . "apache-s.gif" . date("Ymd.His") . ".log");

if ($handle = opendir(TMP_DIR)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $fileList[] = $entry;
        }
    }
    closedir($handle);
}

//process log files
$logFormat = array(
    'pattern' => '/(\d+\.\d+\.\d+\.\d+) ([^\s]+) ([^\s]+) \[(\d+)\/(\w+)\/(\d+):(\d{1,2}:\d{1,2}:\d{1,2} ?[\+\-]?\d*)\] "(.*) (HTTP\/\d\.\d)" (\d+) (\d+) "([^"]*)" "([^"]*)"/',
    'matches' =>array(
        1 => 'ip',
        2 => 'identd',
        3 => 'auth',
        4 => 'day',
        5 => 'month',
        6 => 'year',
        7 => 'time',
        8 => 'request',
        9 => 'http_version',
        10 => 'response_code',
        11 => 'size',
        12 => 'referrer',
        13 => 'navigator'
    )
);

$detect = new Mobile_Detect;
$data = array();
foreach($fileList as $fileName){
    $file = file(TMP_DIR . $fileName);
    foreach($file as $idx => $line){
        if (preg_match($logFormat['pattern'], $line, $matches)) {
            $row = array();
            foreach ($logFormat['matches'] as $i => $key) {
                $row[$key] = $matches[$i];
            }
       
            $date = date("Y-m-d H:i:s", strtotime($row['year'] . '-' . $row['month'] . '-' . $row['day'] . ' ' . $row['time']));            
            $device = 1;
            $detect->setUserAgent($row['navigator']);
            if($detect->isMobile()) $device = 2;
            if($detect->isTablet()) $device = 3;
            
            $tmp = explode('?', $row['request']);
            parse_str($tmp[1], $q);

            $campid = 'NA';
            $ev = 0;
            $es = '';
            $tc = '';
            if(isset($q['cid'])) $campid = $q['cid'];
            if(isset($q['ev'])) $ev = $q['ev'];
            if(isset($q['es'])) $es = $q['es'];
            if(isset($q['t'])) $tc = $q['t'];

            $data[] = array(
                'created' => $date,
                'campid' => $campid,
                'ip' => $row['ip'],
                'referrer' => $row['referrer'],
                'platform' => $device,
                'eventid' => $ev,
                'eventsource' => $es,
                'trackingcode' => $tc
            );
            

        }
    }
}


//add data
if(count($data)>0) echo date("Y-m-d H:i:s") . " - INFO: Adding ".count($data)." new rows\n";

//add rows
foreach($data as $row){
    foreach($row as $idx=>$val){
        $row[$idx] = "'" . $database->real_escape_string($val) . "'";
    }

    $database->query("INSERT INTO events(" . implode(',', array_keys($row)). ") 
                        VALUES(" . implode(",", $row). ")");

}

echo date("Y-m-d H:i:s") . " - INFO: Cleaning up \n";

shell_exec("rm -Rf " . TMP_DIR);