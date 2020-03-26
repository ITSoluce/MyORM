<?php
namespace MyORM;

class Common
{	
	function __construct() {
	}

	protected function makequery($type, $database, $class, $structure)
	{	
		//trouver la PK
		foreach ($structure as $thiskey => $field)
		{
			if (isset($field[4]))
			{
				if ( isset($field[2]) && $field[2] == 2 )
				{
					$Key = $thiskey;
					if (($field[1]!='timestamp')&&($field[1]!='date')&&($field[1]!='datetime')&&($field[1]!='char')&&($field[1]!='varchar')&&($field[1]!='tinyblob')&&($field[1]!='tinytext')&&($field[1]!='blob')&&($field[1]!='text')&&($field[1]!='mediumblob')&&($field[1]!='mediumtext')&&($field[1]!='longblob')&&($field[1]!='longtext')&&($field[1]!='time')&&($field[1]!='enum'))
						$KeyValue = $field[4];
					else
						$KeyValue = "'".$field[4]."'";
				}
			}
		}
		
		if ($type=='SELECT')
		{
			$fields = "";
			foreach ($structure as $field)
				if ( isset($field[1]) && ($field[1] != 'ChildObject') && ($field[1] != 'ParentObject') )
				{
					$fields.= "`$field[0]` ,";
				}
			$fields = substr($fields,0,strlen($fields)-2);
			
			$query = "SELECT ".$fields." FROM `".$database."`.`".$class."` WHERE ".$Key." = ".$KeyValue;
		}
		
		if ($type=='INSERT')
		{
			$fields = "";
			$values = "";
			foreach ($structure as $field)
			if (($field[1] != 'ChildObject')&&($field[1] != 'ParentObject'))
			{
				$test = explode("-",$field[1]);
				if ( ( $test[0] == "varchar" ) && ( isset( $test[1] ) ) )
				{
					$field[1] = "varchar";
					$field[4] = substr($field[4],0,$test[1]);
				}
				
				$fields.= "`$field[0]` ,";
	
				if ((($field[2]==1)||($field[2]==2))&&((is_null($field[4]))||($field[4]=='')))
					$values .= "null, ";
				else
				{
					if (((is_null($field[4]))||($field[4]==''))&&(($field[1]=='tinyint')||($field[1]=='smallint')||($field[1]=='mediumint')||($field[1]=='int')||($field[1]=='decimal')||($field[1]=='float')||($field[1]=='double')||($field[1]=='bit')||($field[1]=='bool')||($field[1]=='serial')))
						$field[4]=0;
					if (($field[1]!='timestamp')&&($field[1]!='date')&&($field[1]!='datetime')&&($field[1]!='char')&&($field[1]!='varchar')&&($field[1]!='tinyblob')&&($field[1]!='tinytext')&&($field[1]!='blob')&&($field[1]!='text')&&($field[1]!='mediumblob')&&($field[1]!='mediumtext')&&($field[1]!='longblob')&&($field[1]!='longtext')&&($field[1]!='time')&&($field[1]!='enum'))
						$values .= $field[4].", ";
					else
						$values .= "'".addslashes((string)$field[4])."', ";
				}
			}
			$fields = substr($fields,0,strlen($fields)-2);
			$values = substr($values,0,strlen($values)-2);
			
			$query = "INSERT INTO `".$database."`.`".$class."` ( ".$fields." ) VALUES ( ".$values." )";
		}
		
		if ($type=='UPDATE')
		{
			$fieldsup = "";

			foreach ($structure as $field)
			{
				if (isset($field[1]) && ($field[1] != 'ChildObject')&&($field[1] != 'ParentObject')&&($field[3] == '1'))
				{
					$test = explode("-",$field[1]);
					if ( ( $test[0] == "varchar" ) && ( isset( $test[1] ) ) )
					{
						$field[1] = "varchar";
						$field[4] = substr($field[4],0,$test[1]);
					}
					
					if ((($field[2]==1)||($field[2]==2))&&((is_null($field[4]))||($field[4]=='')))
						$fieldsup.= "`$field[0]` = null, ";
					else
					{
						if (((is_null($field[4]))||($field[4]==''))&&(($field[1]=='tinyint')||($field[1]=='smallint')||($field[1]=='mediumint')||($field[1]=='int')||($field[1]=='decimal')||($field[1]=='float')||($field[1]=='double')||($field[1]=='bit')||($field[1]=='bool')||($field[1]=='serial')))
							$field[4]=0;
						if (($field[1]!='timestamp')&&($field[1]!='date')&&($field[1]!='datetime')&&($field[1]!='char')&&($field[1]!='varchar')&&($field[1]!='tinyblob')&&($field[1]!='tinytext')&&($field[1]!='blob')&&($field[1]!='text')&&($field[1]!='mediumblob')&&($field[1]!='mediumtext')&&($field[1]!='longblob')&&($field[1]!='longtext')&&($field[1]!='time')&&($field[1]!='enum'))
							$fieldsup.= "`$field[0]` = ".$field[4].", ";
						else
							$fieldsup.= "`$field[0]` = '".addslashes((string)$field[4])."', ";
					}
				}
			}
			$fieldsup = substr($fieldsup,0,strlen($fieldsup)-2);

			$query = "UPDATE `".$database."`.`".$class."` SET ".$fieldsup." WHERE ".$Key." = ".$KeyValue;
		}
		
		if ($type=='DELETE')
		{
			$query = "DELETE FROM `".$database."`.`".$class."` WHERE ".$Key." = ".$KeyValue;
		}
		
                if (MySQLDebug)
                    echo $query."<br>";
		return $query;
	}
	
	function quote($field,$val)
	{
		$test = explode("-",$field[1]);
		if ( ( $test[0] == "varchar" ) && ( isset( $test[1] ) ) )
		{
			$field[1] = "varchar";
			$field[4] = substr($field[4],0,$test[1]);
		}
		
		if (($field[1]!='timestamp')&&($field[1]!='date')&&($field[1]!='datetime')&&($field[1]!='char')&&($field[1]!='varchar')&&($field[1]!='tinyblob')&&($field[1]!='tinytext')&&($field[1]!='blob')&&($field[1]!='text')&&($field[1]!='mediumblob')&&($field[1]!='mediumtext')&&($field[1]!='longblob')&&($field[1]!='longtext')&&($field[1]!='time')&&($field[1]!='enum'))
			return (string)$val;
		else
			return "'".addslashes((string)$val)."'";
	}
	
	function formater($field,$val)
	{
		$test = explode("-",$field[1]);
		if ( ( $test[0] == "varchar" ) && ( isset( $test[1] ) ) )
		{
			$field[1] = "varchar";
			$field[4] = substr($field[4],0,$test[1]);
		}
		
		if (($field[1]!='timestamp')&&($field[1]!='date')&&($field[1]!='datetime')&&($field[1]!='char')&&($field[1]!='varchar')&&($field[1]!='tinyblob')&&($field[1]!='tinytext')&&($field[1]!='blob')&&($field[1]!='text')&&($field[1]!='mediumblob')&&($field[1]!='mediumtext')&&($field[1]!='longblob')&&($field[1]!='longtext')&&($field[1]!='time')&&($field[1]!='enum'))
			return $val;
		else
			return stripslashes($val);
	}
		
	public static function dump($pre = FALSE)
	{
		if($pre)
		{
			echo "<pre>";
		}
		
		print_r($this);
		
		if($pre)
		{
			echo "</pre>";
		}
	}
        
        public static function getList($connection,$Object,$ConditionArray,$SubObject = null)
        {
            $Return = Array();
            
            if (EnableAPIMyORM == 1 && APIServer == 0)
            {
                $return = Common::callAPI("GET",APIServerURL."/".$Object."/".$ConditionArray[0][3]."/".$SubObject."_".$Object);
                $return = json_decode($return);

                $classname = "MyORM\\".$SubObject;
                
                foreach ($return as $key => $value) {
                    $Return[] = new $classname($value,"reloadObjectFromJsonDecodeObject");
                }
            }
            else
            {            
                $Result=$connection->sql_query(Common::getSelectQuery($SubObject,$ConditionArray));
                while($row = $connection->sql_fetch_object($Result,"MyORM\\".$SubObject))
                {
                    $Return[] = $row;
                }
            }
            return $Return;
        }
        
        public static function getSelectQuery($Object,$ConditionArray,$Limit = null)
        {
            /*
            $ConditionArray[][LogicOperator] AND OR (First will be ignore)
            $ConditionArray[][Field]
            $ConditionArray[][Operator] Egal / Superior / Inferior / Between / LikeLeft / LikeRight / Like / In
            $ConditionArray[][Value]
            $ConditionArray[][Value2] (For Between)
            */
            
            $Where = "WHERE ";
            
            if ( empty($ConditionArray) || is_null($ConditionArray) || count($ConditionArray) == 0 )
            {
                $Where .= "1 = 1 ";
            }
            else
            {
                //$class = new \ReflectionClass("MyORM\\".$Object);
                
                $MemLogicOperator = "";
                $Open = 0;
                foreach ($ConditionArray AS $Value)
                {
                    $Value["LogicOperator"] = $Value[0];
                    $Value["Field"] = $Value[1];
                    $Value["Operator"] = $Value[2];
                    $Value["Value"] = $Value[3];
                    if (isset($Value[4]))
                    {
                        $Value["Value2"] = $Value[4];
                    }
                    
                    //$property = $class->getProperty($Value["Field"]);
                    
                    if ($MemLogicOperator != "")
                    {
                        if ($MemLogicOperator != $Value["LogicOperator"])
                        {
                            $Where .= "( ";
                            $Open = 1;
                        }
                        $Where .= $Value["LogicOperator"]." ";
                    }
                    $Where .= "`".$Value["Field"]."` ";
                    switch ($Value["Operator"]) {
                        case "Equal" :
                            $Where .= "= ".$Value["Value"]." ";
                            break;
                        case "Superior" :
                            $Where .= "> ".$Value["Value"]." ";
                            break;
                        case "Inferior" :
                            $Where .= "< ".$Value["Value"]." ";
                            break;
                        case "Between" :
                            $Where .= "BETWEEN ".$Value["Value"]." AND ".$Value["Value2"]." ";
                            break;
                        case "LikeLeft" :
                            $Where .= "LIKE '%".$Value["Value"]."' ";
                            break;
                        case "LikeRight" :
                            $Where .= "LIKE '".$Value["Value"]."%' ";
                            break;
                        case "Like" :
                            $Where .= "LIKE '%".$Value["Value"]."%' ";
                            break;
                        case "In" :
                            $Where .= "IN ( ".$Value["Value"]." ) ";
                            break;
                    }
                }
                
                if ($Open == 1)
                {
                    $Where .= ") ";
                }
            }
            
            $Query = "SELECT * FROM `".$Object."` ".$Where;
            
            if ( isset($Limit) && !is_null($Limit) && !empty($Limit) )
                $Query .= "LIMIT ".$Limit;
 
            return $Query;
        }
        
        public static function get_toJson($Object)
        {
            $return = "";
            if (is_array($Object)||is_object($Object))
            {
                if (is_array($Object)) {
                    $return .= "[";
                }
                else {
                    $return .= "{";
                }
                foreach ($Object as $Key => $Value) {
                    if (is_array($Value)) {
                        $return .= "\"".$Key."\":[";
                        foreach ($Value as $Key2 => $Value2) {
                            $return .= Common::get_toJson($Value2);
                            $return .= ",";
                        }
                        if (count($Value)>0) {
                            $return = substr($return,0,-1);
                        }
                        $return .= "],";
                    }
                    elseif (is_object($Value)) {
                            if (is_array($Object))
                                $return .= Common::get_toJson($Value).",";
                            else
                                $return .= "\"".$Key."\":".Common::tojson($Value).",";
                        }
                        else {
                            //if (in_array($Key, $Object->Structure))
                            //{
                                $return .= "\"".$Key."\":\"".str_replace("\n","",str_replace("\r","",str_replace("\t","",str_replace('"','\"',$Value))))."\",";
                            //}
                        }
                }
                $return = substr($return,0,-1);
                if (is_array($Object)) {
                    $return .= "]";
                }
                else {
                    $return .= "}";
                }
            }
            return $return;
        }
        
        public static function callAPI($Mode,$Route,$Json = "")
        {
            // Création d'un flux
            $opts = array(
              'http'=>array(
                'method'=>"$Mode",
                'header'=>"Accept-language: en\r\n" .
                          "Content-Length: ".strlen($Json)."\r\n".
                          "content-type:application/json\r\n".
                          "APIAUTHENTIFICATION:".PublicKey."\r\n",
                'content'=>$Json
              )
            );
            $context = stream_context_create($opts);

            try {
                ob_start();
                $return = @file_get_contents($Route, false, $context);
                ob_get_clean();
            }
            catch (Exception $e) {
                die("recuperation error API Server");
            }
            return $return;
        }
        
        public static function makePropertiesArray($Properties) {
            $removed = array("[", "]", "{", "}", "(", ")", "'");
            $Properties = str_replace($removed, "", $Properties);
            $Array = explode(",",$Properties);
            $Array3 = array();
            foreach ($Array AS $Key => $Value) {
                $Array2 = explode(":",$Value);
                $Array3[$Array2[0]][] = $Array2[1];
            }
            return $Array3;
        }
        
        public static function loadFromProperties($Object, $ArrayOfProperties) {
            $ClassName = get_class($Object);
    
            if ($pos = strrpos($ClassName, '\\')) {
                $ClassName = substr($ClassName, $pos + 1);
            }
    
            $ArrayNext = $ArrayOfProperties;
            if (isset($ArrayNext[$ClassName])) {
                unset($ArrayNext[$ClassName]);
            }

            if (isset($ArrayOfProperties[$ClassName])) { 
                foreach ($ArrayOfProperties[$ClassName] AS $Key => $Value)
                {
                    foreach ($Object->structure AS $Key2 => $Value2)
                    {
                        if ( (property_exists($Object,$Value)) && ($Key2 == $Value) ) {
                            if ($Value2[1]=='ParentObject') {
                                $Object->{'get_'.$Key2}();
                                Common::loadFromProperties($Object->{$Key2},$ArrayNext);
                            }
                            elseif ( ($Value2[1]=='ChildObject') && ($Key2 == $Value) ) {
                                $Object->{'get_'.$Key2}();
                                foreach($Object->{$Key2} AS $Key3 => $Value3) {
                                    Common::loadFromProperties($Value3,$ArrayNext);
                                }
                            }
                        }
                    }
                }
            }    
        }
        
        public static function query($connection,$sql) {
            $Return = array();
            if (EnableAPIMyORM == 1 && APIServer == 0)
            {
                //Appel de l'API
                $Return = Common::callAPI("GET",APIServerURL."/DirectQueryToDataBase/","{\"sql\":\"".$sql."\"}");
                $Return = json_decode($Return);
            }
            else
            {
                $Result=$connection->sql_query($sql);
                while($row = $connection->sql_fetch_object($Result))
                {
                    $Return[] = $row;
                }
            }
            if (is_array($Return) && count($Return)>0) {
                return $Return;
            }
        }
        
        public static function queryToObject($connection,$sql,$object) {
            $Return = array();
            if (EnableAPIMyORM == 1 && APIServer == 0)
            {
                //Appel de l'API
                $Return = Common::callAPI("GET",APIServerURL."/DirectQueryToDataBase/","{\"sql\":\"".$sql."\"}");
                $Return = json_decode($Return);
                
                $classname = "MyORM\\".$object;
                if (is_array($Return)) {
                    foreach ($Return as $key => $value) {
                        $Return[$key] = new $classname($value,"reloadObjectFromJsonDecodeObject");
                    }
                }
            }
            else
            {
                $Result=$connection->sql_query($sql);
                while($row = $connection->sql_fetch_object($Result,"MyORM\\".$object))
                {
                    $Return[] = $row;
                }
            }
            if (is_array($Return) && count($Return)>0) {
                return $Return;
            }
        }
}