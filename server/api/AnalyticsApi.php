<?php
/**
 * Analytics api class
 */

class AnalyticsApi{

    var $db;
    var $eventid;
    
    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->db->set_charset("utf8");
        if($this->db->connect_errno > 0){
            die('Unable to connect to database [' . $this->db->connect_error . ']');
        }
    }

    
    public function getData($params){
        $filter = " created >= '{$params['start']} 00:00:00' AND created <= '{$params['end']} 23:59:59'";
        
        //filters        
        if(strlen($params['country']) == 2) $filter .= " AND country='{$params['country']}'";
        if(strlen($params['state']) == 2) $filter .= " AND state='{$params['state']}'";
        if(is_numeric($params['platform'])) $filter .= " AND platform='{$params['platform']}'";
        if(strlen($params['campid'])>3) $filter .= " AND campid='{$params['campid']}'";
        if(strlen($params['states'])>1){
            $states = implode("','", explode(',', $params['states']));
            $filter .= " AND state IN('{$states}')";
        }

        //groups
        $groupby = $params['groupby'];
        if(!empty($params['groupby2'])) $groupby .= ',' . $params['groupby2'];
        if(!empty($params['groupby3'])) $groupby .= ',' . $params['groupby3'];
        if(!empty($params['limit'])) $limit = 'LIMIT ' . $params['limit'];

        if(strpos($groupby, 'eventid') === false) $filter .= " AND eventid={$params['eventid']}";
        
        $sql = "SELECT $groupby, count(*) as s FROM events WHERE $filter GROUP BY $groupby ORDER BY s DESC $limit";

        $q = $this->db->query($sql);  
        $start = round(microtime(true) * 1000);  

        while($row = $q->fetch_assoc()){
            $data[] = $row;
        }
        
        
        return $data;
    }

    public function getSummary($table, $params){
        
        $table = 'sum_' . $table . $params['eventid'];

        $filter = " created>= '{$params['start']}' AND  created <= '{$params['end']}'";
        
        //filters        
        if(strlen($params['country']) == 2) $filter .= " AND country='{$params['country']}'";
        if(strlen($params['state']) == 2) $filter .= " AND state='{$params['state']}'";
        if(is_numeric($params['platform'])) $filter .= " AND platform='{$params['platform']}'";
        if(strlen($params['campid'])>3) $filter .= " AND campid='{$params['campid']}'";
        if(strlen($params['states'])>1){
            $states = implode("','", explode(',', $params['states']));
            $filter .= " AND state IN('{$states}')";
        }
        if(!empty($params['lead_source'])) $filter .= " AND lead_source='" . $this->db->real_escape_string($params['lead_source']) . "'";


        //limit
        if(!empty($params['limit'])) $limit = 'LIMIT ' . $params['limit'];        

        //groups
        if(!empty($params['groupby']) || !empty($params['groupby2']) || !empty($params['groupby'])){
            $groupby = $params['groupby'];
            if(!empty($params['groupby2'])) $groupby .= ',' . $params['groupby2'];
            if(!empty($params['groupby3'])) $groupby .= ',' . $params['groupby3'];
            $sql = "SELECT $groupby, sum(s) as s FROM $table WHERE $filter GROUP BY $groupby ORDER BY s DESC $limit";
        }else{
            $sql = "SELECT * FROM $table WHERE $filter ORDER BY s DESC $limit";

        }
        //echo $sql;
        $q = $this->db->query($sql);  

        while($row = $q->fetch_assoc()){
            $data[] = $row;
        }
        
        return $data;
    }

    public function find($field, $value, $params){
        $field = $this->db->real_escape_string($field);
        $value = $this->db->real_escape_string($value);
        $filter = " created >= '{$params['start']} 00:00:00' AND created <= '{$params['end']} 23:59:59'";
        $filter .= " AND $field='$value'";
        
        $data = array();
        $q = $this->db->query("SELECT * FROM visitors WHERE $filter LIMIT 1000");    
        while($row = $q->fetch_assoc()){
            $data[] = $row;
        }
        
        return $data;   
    }
}
