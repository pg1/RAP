<?php 
/**
 * Enrich data
 */

use GeoIp2\Database\Reader;

echo date("Y-m-d H:i:s") . " - INFO: Enriching rows\n";

$reader = new Reader(dirname(__FILE__) . '/../../../GeoLite2-City.mmdb');

//run geoip 
$q = $database->query("SELECT * FROM events WHERE country IS NULL");
while($row = $q->fetch_assoc()){
	$country = '';
	$state = '';
	try{
	    $geo = $reader->city($row['ip']);
	    $country = $geo->country->isoCode;
	    $state = $geo->mostSpecificSubdivision->isoCode;
    }catch(Exception $e){}

    $database->query("UPDATE events SET country='$country', state='$state' WHERE id={$row['id']}");    
}
