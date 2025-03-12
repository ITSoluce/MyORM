<?php
namespace MyORM;

class ORMBase
{	
	protected function get_OffSetTableForBinValue($tableName,$binValueTable= 'binvalue') {
		$Query = "SELECT Name,Value FROM `".$binValueTable."` WHERE TableName = '".$tableName."'";
		$Resultat = ORMBase::query($Query);

		if ( count($Resultat)>0 ) {
			$Array = array();
			foreach ($Resultat AS $Value) {
				$Array[$Value->Name] = $Value->Value;
			}
			return $Array;
		}

		return null;
	}

	protected function makequery($type, $tablename, $structure)
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
			$fields = substr($fields ?? '',0,strlen($fields)-2);

			if ($KeyValue=="") {
				return false;
			}
			
			$query = "SELECT ".$fields." FROM `".$tablename."` WHERE ".$Key." = ".$KeyValue;
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
					$field[4] = substr($field[4] ?? '',0,$test[1]);
				}
				
				$fields.= "`$field[0]` ,";
	
				if ((($field[2]==1)||($field[2]==2))&&((is_null($field[4]))||($field[4]=='')))
					$values .= "null, ";
				else
				{
					if (((is_null($field[4]))||($field[4]==''))&&(($field[1]=='tinyint')||($field[1]=='smallint')||($field[1]=='mediumint')||($field[1]=='int')||($field[1]=='bigint')||($field[1]=='decimal')||($field[1]=='float')||($field[1]=='double')||($field[1]=='bit')||($field[1]=='bool')||($field[1]=='serial')))
						$field[4]=0;
					if (($field[1]!='timestamp')&&($field[1]!='date')&&($field[1]!='datetime')&&($field[1]!='char')&&($field[1]!='varchar')&&($field[1]!='tinyblob')&&($field[1]!='tinytext')&&($field[1]!='blob')&&($field[1]!='text')&&($field[1]!='mediumblob')&&($field[1]!='mediumtext')&&($field[1]!='longblob')&&($field[1]!='longtext')&&($field[1]!='time')&&($field[1]!='enum'))
						$values .= $field[4].", ";
					else
						$values .= "'".addslashes((string)$field[4] ?? '')."', ";
				}
			}
			$fields = substr($fields ?? '',0,strlen($fields)-2);
			$values = substr($values ?? '',0,strlen($values)-2);

			if ($fields=="") {
				return false;
			}
			
			$query = "INSERT INTO `".$tablename."` ( ".$fields." ) VALUES ( ".$values." )";
		}
		
		if ($type=='UPDATE')
		{
			$fieldsup = "";

			foreach ($structure as $field)
			{
				if (isset($field[1]) && ($field[1] != 'ChildObject')&&($field[1] != 'ParentObject')&&($field[3] == '1'))
				{
					$test = explode("-",$field[1] ?? '');
					if ( ( $test[0] == "varchar" ) && ( isset( $test[1] ) ) )
					{
						$field[1] = "varchar";
						$field[4] = substr($field[4] ?? '',0,$test[1]);
					}

					if ((empty($field[4]))&&(strlen($field[4])==0)&&(($field[2]==1)||($field[2]==2))) {
						$fieldsup.= "`$field[0]` = null, ";
					}
					else
					{
						if (((is_null($field[4]))||($field[4]==''))&&(($field[1]=='tinyint')||($field[1]=='smallint')||($field[1]=='mediumint')||($field[1]=='int')||($field[1]=='bigint')||($field[1]=='decimal')||($field[1]=='float')||($field[1]=='double')||($field[1]=='bit')||($field[1]=='bool')||($field[1]=='serial')))
							$field[4]=0;
						if (($field[1]!='timestamp')&&($field[1]!='date')&&($field[1]!='datetime')&&($field[1]!='char')&&($field[1]!='varchar')&&($field[1]!='tinyblob')&&($field[1]!='tinytext')&&($field[1]!='blob')&&($field[1]!='text')&&($field[1]!='mediumblob')&&($field[1]!='mediumtext')&&($field[1]!='longblob')&&($field[1]!='longtext')&&($field[1]!='time')&&($field[1]!='enum'))
							$fieldsup.= "`$field[0]` = ".$field[4].", ";
						else
							$fieldsup.= "`$field[0]` = '".addslashes((string)$field[4] ?? '')."', ";
					}
				}
			}
			$fieldsup = substr($fieldsup ?? '',0,strlen($fieldsup)-2);

			if (($fieldsup=="")||($KeyValue=="")) {
				return false;
			}

			$query = "UPDATE `".$tablename."` SET ".$fieldsup." WHERE ".$Key." = ".$KeyValue;
		}
		
		if ($type=='DELETE')
		{
			if ($KeyValue=="") {
				return false;
			}

			$query = "DELETE FROM `".$tablename."` WHERE ".$Key." = ".$KeyValue;
		}
		
		return $query;
	}
	
	function quote($field,$val)
	{
		$test = explode("-",$field[1]);
		if ( ( $test[0] == "varchar" ) && ( isset( $test[1] ) ) )
		{
			$field[1] = "varchar";
			$field[4] = substr($field[4] ?? '',0,$test[1]);
		}
		
		if (($field[1]!='timestamp')&&($field[1]!='date')&&($field[1]!='datetime')&&($field[1]!='char')&&($field[1]!='varchar')&&($field[1]!='tinyblob')&&($field[1]!='tinytext')&&($field[1]!='blob')&&($field[1]!='text')&&($field[1]!='mediumblob')&&($field[1]!='mediumtext')&&($field[1]!='longblob')&&($field[1]!='longtext')&&($field[1]!='time')&&($field[1]!='enum'))
			return $val;
		else
			return "'".addslashes((string)$val ?? '')."'";
	}
	
	function formater($field,$val)
	{
		$test = explode("-",$field[1]);
		if ( ( $test[0] == "varchar" ) && ( isset( $test[1] ) ) )
		{
			$field[1] = "varchar";
			$field[4] = substr($field[4] ?? '',0,$test[1]);
		}
		
		if (($field[1]!='timestamp')&&($field[1]!='date')&&($field[1]!='datetime')&&($field[1]!='char')&&($field[1]!='varchar')&&($field[1]!='tinyblob')&&($field[1]!='tinytext')&&($field[1]!='blob')&&($field[1]!='text')&&($field[1]!='mediumblob')&&($field[1]!='mediumtext')&&($field[1]!='longblob')&&($field[1]!='longtext')&&($field[1]!='time')&&($field[1]!='enum'))
			return $val;
		else
			return stripslashes($val ?? '');
	}

	public static function getTypeFromDatabaseType($sqlType) {
		if ($pos = strpos($sqlType,'(')) {
			$sqlType = substr($sqlType,0,$pos);
		}
		switch (strtolower($sqlType))
		{
			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'int':
			case 'bigint':
			case 'bit':
			case 'boolean':
			case 'serial':
			case 'timestamp':
			case 'year':
				$Type = "int";
				break;
			case 'decimal':
			case 'numeric':
			case 'real' :
			case 'double' :	
				$Type = "float";
				break;
			case 'date':
			case 'datetime':
			case 'time':
			case 'tinytext':
			case 'text':
			case 'mediumtext':
			case 'longtext':
			case 'varbinary':
			case 'binary':
			case 'varchar':
			case 'char':
			case 'tinyblob':
			case 'mediumblob':
			case 'blob':
			case 'longblob':
			case 'set':
			case 'enum':
				$Type = "string";
				break;
			default:
				echo "Type de champ inconnu à prendre en charge :";
				dd($sqlType);
				break;
		}
		return $Type;
	}

	public function getList($tablename,$ConditionArray,$Object = null)
	{
		return self::query(self::getSelectQuery($tablename,$ConditionArray),$Object);
	}
	
	public static function getSelectQuery($tablename,$ConditionArray,$Limit = null)
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
		
		$Query = "SELECT * FROM `".$tablename."` ".$Where;
		
		if ( isset($Limit) && !is_null($Limit) && !empty($Limit) )
			$Query .= "LIMIT ".$Limit;

		return $Query;
	}
	
	public static function get_toJson($Object, $ToJson = true)
	{	
		if (is_array($Object)) {
			foreach ($Object as $k => $val) {
				$Object[$k] = self::get_toJson($val,false);
			}
			$Object = array_values($Object);
		} elseif (is_object($Object)) {
			foreach ($Object as $k => $val) {
				$Object->$k = self::get_toJson($val,false);
			}
		}
		return $ToJson ? json_encode($Object) : $Object;
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
							ORMBase::loadFromProperties($Object->{$Key2},$ArrayNext);
						}
						elseif ( ($Value2[1]=='ChildObject') && ($Key2 == $Value) ) {
							$Object->{'get_'.$Key2}();
							foreach($Object->{$Key2} AS $Key3 => $Value3) {
								ORMBase::loadFromProperties($Value3,$ArrayNext);
							}
						}
					}
				}
			}
		}    
	}

	public static function query($sql,$object = null,$option = null) {
		if (EnableAPIMyORM == 1 && APIServer == 0) {
			//Appel de l'API
			$Return = ORMBase::callAPI("GET",APIServerURL."/DirectQueryToDataBase/","{\"sql\":\"".$sql."\"}");
			return json_decode($Return);
		}
		else {
			try {
				global ${MyORMSQL};

				$Result=${MyORMSQL}->sql_query($sql);
				if ($Result === FALSE) {
					throw new \Exception('Requête non executée : '.$sql.' ('.DB::getPdo()->errorInfo()[2].')');
				}
			}
			catch (\Exception $e) {
				throw new \Exception('Requête non executée : '.$sql.' ('.DB::getPdo()->errorInfo()[2].')');
			}
			if (is_null($option)) {
				if (is_null($object)) {
					return $Result->fetchAll(\PDO::FETCH_CLASS);
				}
				else {
					return $Result->fetchAll(\PDO::FETCH_CLASS, __NAMESPACE__.'\\'.$object);
					//return $Result->fetchAll(\PDO::FETCH_CLASS, $object);
				}
			}
			else {
				return $Result->fetchAll($option);
			}
		}
	}
	
	public static function queryToObject($sql,$object = null,$option = null) {
		return ORMBase::sql_query($sql,$object,$option);
	}
	
	public static function queryNoReturn($sql, $write = null) {
		if (is_null($write)) {
			global ${MyORMSQL};
			try {
				$Result=${MyORMSQL}->sql_query($sql);
				if ($Result === FALSE) {
					throw new \Exception('Requête non executée : '.$sql.' ('.${MyORMSQL}->errorInfo()[2].')');
				}
			}
			catch (\Exception $e) {
				throw new \Exception('Requête non executée : '.$sql.' ('.${MyORMSQL}->errorInfo()[2].')');
			}
		}
		else {
			global ${MyORMSQL2};
			try {
				$Result=${MyORMSQL2}->sql_query($sql);
				if ($Result === FALSE) {
					throw new \Exception('Requête non executée : '.$sql.' ('.${MyORMSQL}->errorInfo()[2].')');
				}
			}
			catch (\Exception $e) {
				throw new \Exception('Requête non executée : '.$sql.' ('.${MyORMSQL2}->errorInfo()[2].')');
			}
		}
		return $Result;
	}
	
	public static function lastInsertId() {
		global ${MyORMSQL};
		global ${MyORMSQL2};
		
		if (${MyORMSQL} === ${MyORMSQL2}) {
			try {
				$Result=${MyORMSQL}->lastInsertId();
			}
			catch (\Exception $e) {
				throw new \Exception('Requête non executée : '.$sql.' ('.${MyORMSQL}->errorInfo()[2].')');
			}
		}
		else {
							try {
				$Result=${MyORMSQL2}->lastInsertId();
			}
			catch (\Exception $e) {
				throw new \Exception('Requête non executée : '.$sql.' ('.${MyORMSQL2}->errorInfo()[2].')');
			}
		}
		return $Result;
	}
	
	public static function beginTransaction() {
		global ${MyORMSQL};
		global ${MyORMSQL2};
		
		if (${MyORMSQL} === ${MyORMSQL2}) {
			try {
				$Result=${MyORMSQL}->beginTransaction();
			}
			catch (\Exception $e) {
				throw new \Exception('Requête non executée : '.$sql.' ('.${MyORMSQL}->errorInfo()[2].')');
			}
		}
		else {
							try {
				$Result=${MyORMSQL2}->beginTransaction();
			}
			catch (\Exception $e) {
				throw new \Exception('Requête non executée : '.$sql.' ('.${MyORMSQL2}->errorInfo()[2].')');
			}
		}
	}
	
	public static function commit() {
		global ${MyORMSQL};
		global ${MyORMSQL2};
		
		if (${MyORMSQL} === ${MyORMSQL2}) {
			try {
				$Result=${MyORMSQL}->commit();
			}
			catch (\Exception $e) {
				throw new \Exception('Requête non executée : '.$sql.' ('.${MyORMSQL}->errorInfo()[2].')');
			}
		}
		else {
							try {
				$Result=${MyORMSQL2}->commit();
			}
			catch (\Exception $e) {
				throw new \Exception('Requête non executée : '.$sql.' ('.${MyORMSQL2}->errorInfo()[2].')');
			}
		}
	}

	public static function rollBack() {
		global ${MyORMSQL};
		global ${MyORMSQL2};
		
		if (${MyORMSQL} === ${MyORMSQL2}) {
			try {
				$Result=${MyORMSQL}->rollBack();
			}
			catch (\Exception $e) {
				throw new \Exception('Requête non executée : '.$sql.' ('.${MyORMSQL}->errorInfo()[2].')');
			}
		}
		else {
							try {
				$Result=${MyORMSQL2}->rollBack();
			}
			catch (\Exception $e) {
				throw new \Exception('Requête non executée : '.$sql.' ('.${MyORMSQL2}->errorInfo()[2].')');
			}
		}
	}
}