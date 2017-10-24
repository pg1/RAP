<?php
/**
 * US state summary
 */

class job2{

	var $table;
	var $db;
	var $events;

	public function __construct($db){
		$this->db = $db;
		$this->events = array(1, 2, 3, 4);
		$this->table = 'sum_state'; 
		
		//check if summary table exists and create if not
		foreach($this->events as $event){
			$table = $this->table . $event;
			$db->query("SELECT * FROM $table");
			if($db->affected_rows == -1){
				$res = $db->query("CREATE TABLE $table(
				  `s` int(11) DEFAULT '0',
				  `created` date DEFAULT NULL,
				  `country` varchar(2) DEFAULT NULL,
				  `state` varchar(5) DEFAULT NULL,
				  `platform` tinyint(1) DEFAULT '0',
				  KEY `created` (`created`),
				  KEY `state` (`state`),
				  KEY `country` (`country`),
				  KEY `platform` (`platform`)
				)");
				echo date("Y-m-d H:i:s") . " - INFO: New table $table created\n";
			}
		}

	}

	public function run($date){

		echo date("Y-m-d H:i:s") . " - INFO: Rendering US state summary $date \n";

		$filter = " country='US' ";
		foreach($this->events as $event){
			$table = $this->table . $event;
			$this->db->query("DELETE FROM $table WHERE created='$date'");
			$this->db->query("INSERT INTO $table(s, created, country, state, platform)
			SELECT count(*) as s, '$date', country, state, platform 
			FROM events_tmp 
			WHERE eventid=$event AND $filter
			GROUP BY 2,3,4,5");
		}
		
	}
} 
