<?php
/**
 * Campid summary/day / state / platfrom
 */


class job1{

	var $table;
	var $db;
	var $events;

	public function __construct($db){
		$this->db = $db;
		$this->events = array(1, 2);
		$this->table = 'sum_campid_state'; 
		
		//check if summary table exists and create if not
		foreach($this->events as $event){
			$table = $this->table . $event;
			$db->query("SELECT * FROM $table");
			if($db->affected_rows == -1){
				$res = $db->query("CREATE TABLE $table(
				  `s` int(11) DEFAULT '0',
				  `created` date DEFAULT NULL,
				  `country` varchar(2) DEFAULT NULL,
				  `state` varchar(2) DEFAULT NULL,
				  `campid` varchar(100) DEFAULT NULL,
				  `platform` tinyint(1) DEFAULT '0',
				  `lead_source` varchar(100) DEFAULT NULL,
				  KEY `created` (`created`)
				)");
				echo date("Y-m-d H:i:s") . " - INFO: New table $table created\n";
			}
		}

	}

	public function run($date){

		echo date("Y-m-d H:i:s") . " - INFO: Rendering campid summary $date \n";

		foreach($this->events as $event){
			$table = $this->table . $event;
			$this->db->query("DELETE FROM $table WHERE created='$date'");
			$this->db->query("INSERT INTO $table(s, created, state, platform, campid, country, lead_source)
			SELECT count(*) as s, '$date', state, platform, campid, country, lead_source 
			FROM events_tmp 
			WHERE eventid=$event 
				AND country='US' 
			GROUP BY 2,3,4,5,6");
		}
		
	}
} 
