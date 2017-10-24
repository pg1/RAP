<?php 
/**
 * Parse cf logs
 *
 * Fields {
 *      0 date
 *      1 time
 *      x-edge-location
 *      sc-bytes
 *      4 c-ip
 *      cs-method
 *      cs(Host)
 *      7 cs-uri-stem
 *      8 sc-status
 *      9 cs(Referer)
 *      cs(User-Agent)
 *      cs-uri-query
 *      cs(Cookie)
 *      x-edge-result-type
 *      x-edge-request-id
 *      x-host-header
 *      cs-protocol
 *      cs-bytes
 *      time-taken
 *      x-forwarded-for
 *      ssl-protocol
 *      ssl-cipher
 *      x-edge-response-result-type
 *      cs-protocol-version
 * }
 */

echo date("Y-m-d H:i:s") . " - INFO: Getting logs from S3\n";

if(!is_dir(TMP_DIR)) mkdir(TMP_DIR, 0777, true);
shell_exec("s3cmd sync ". CFLOGS . "  " . TMP_DIR);


$fileList = array();
if ($handle = opendir(TMP_DIR)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $fileList[] = $entry;
        }
    }
    closedir($handle);
}

if(count($fileList)>0) echo date("Y-m-d H:i:s") . " - INFO: Processing log files\n";

$detect = new Mobile_Detect;


//process files
$data = array();
$hashes = array();
foreach($fileList as $fileCSV){
    //read file
    $file = gzfile(TMP_DIR . $fileCSV);
    //remove header
    unset($file[0]); 
    foreach($file as $line){
        $row = str_getcsv($line, "\t");
        if($count > 1){
            //analytics tracking pixel
            if($row[7] == '/s.gif'){
                $device = 1;
                $detect->setUserAgent(rawurldecode(rawurldecode($row[10])));
                if($detect->isMobile()) $device = 2;
                if($detect->isTablet()) $device = 3;

                parse_str($row[11], $q);
                $campid = 'NA';
                $ev = 0;
                $ls = '';
                $tc = '';
                if(isset($q['cid'])) $campid = $q['cid'];
                if(isset($q['ev'])) $ev = $q['ev'];
                if(isset($q['ls'])) $ls = $q['ls'];
                if(isset($q['t'])) $tc = $q['t'];

                $data[] = array(
                    'created' => $row[0] . ' ' . $row[1],
                    'campid' => $campid,
                    'ip' => $row[4],
                    'referrer' => $row[9],
                    'querystring' => $row[11],
                    'platform' => $device,
                    'eventid' => $ev,
                    'eventsource' => $ls,
                    'trackingcode' => $tc
                );

            }
        }
        
    }
}

if(count($data)>0) echo date("Y-m-d H:i:s") . " - INFO: Adding ".count($data)." new rows\n";


//add rows
foreach($data as $row){
    foreach($row as $idx=>$val){
        $row[$idx] = "'" . $database->real_escape_string($val) . "'";
    }

    $database->query("INSERT INTO events(" . implode(',', array_keys($row)). ") 
                        VALUES(" . implode(",", $row). ")");

}

echo date("Y-m-d H:i:s") . " - INFO: Cleaning up logs S3\n";

foreach($fileList as $fileCSV){
    $out = shell_exec("s3cmd mv " . CFLOGS . "$fileCSV  .".CFLOGSARCHIVE."$fileCSV");
   // print_r($out);
}

shell_exec("rm -Rf " . TMP_DIR);