<?php 

namespace MyORM;

class MyORM {
	function __construct() {
            // Changement des exceptions.
            new \MyException\MyException();

            if (version_compare(phpversion(), '5.3.0', '>=')) {
                spl_autoload_register(array(__CLASS__, 'autoload'), true, FALSE);
            } else {
                spl_autoload_register(array(__CLASS__, 'autoload'));
            }
        }
	
    public static function autoload($classname)
	{
		if(strstr($classname, "MyORM\\"))
		{
			$classname = str_replace("MyORM\\", "", $classname);

			if(file_exists(__DIR__.'/../../'. MyORMBLL . "$classname.php")) /* File exists dans le dossier BLL ? */
			{
				require __DIR__.'/../../'. MyORMBLL . "$classname.php";
			}
			elseif(file_exists(__DIR__.'/../../'. MyORMDAL . $classname .".php")) /* File exists dans le dossier DAL ? */
			{
				require __DIR__.'/../../'. MyORMDAL . $classname .".php";
			}
			else
			{
				if (EnableAPIMyORM == 1 && APIServer == 0) {
					$Query = "";

					// Création d'un flux
					$opts = array(
					  'http'=>array(
						'method'=>"GET",
						'header'=>"Accept-language: en\r\n" .
								  "APIAUTHENTIFICATION:".PublicKey."\r\n"
					  )
					);

					$context = stream_context_create($opts);

					try {
						ob_start();
						$content = @file_get_contents(APIServerURL."/".$classname."/Ineedtheclassplease", false, $context);
						ob_get_clean();

						if(MyORMALWAYSAUTOGENERATE) {
							$content = str_replace("<?php", "", $content);
							$content = str_replace("?>", "", $content);

							eval($content);
						}
						else {
							$ORM = new MyORM();
							if($ORM->saveclasstofile($classname.".php", $content, __DIR__.'/../../'. MyORMDAL)) {
									require(__DIR__.'/../../'. MyORMDAL . $classname .".php");
							}
						}
					}
					catch (Exception $e) {
						die("recuperation error API Server");
					} 
				}
				else {
					global ${"".MyORMSQL};
					$sql = ${"".MyORMSQL};

					if(MyORMAUTOGENERATE && $sql->sql_table_exists($classname)) /* Check si il peut etre générer et s'il existe */
					{
						$ORM = new MyORM();
						$content = $ORM->classgenerator($classname);

						if(MyORMALWAYSAUTOGENERATE) {
							$content = str_replace("<?php", "", $content);
							$content = str_replace("?>", "", $content);

							eval($content);
						}
						else {
							if($ORM->saveclasstofile($classname.".php", $content, __DIR__.'/../../'. MyORMDAL)) {
								require(__DIR__.'/../../'. MyORMDAL . $classname .".php");
							}
						}
					}
				}
			}
		}
	}
        
	public function saveclasstofile($filename,$filecontent,$directory)
	{
            if (!file_exists($directory))
            mkdir($directory);
            $handle = fopen($directory.$filename, "w");

            if(fwrite ( $handle , $filecontent )) {
                fclose($handle);
                return TRUE;
            }
            else 
            {
                fclose($handle);
                return FALSE;
            }
	}
	
	/**
	* Generate classname
	*/
	public function buildClassname($tablename) {
        $work = str_replace("_"," ",$tablename);
        $work = ucwords($work);
        return str_replace(" ","",$work);
    }
	
	/**
	* Initialisation ou réinitialisation des classes objects
	*/
    public function buildorm()
    {
        $DIR = __DIR__."/MyORM/";

        if (!file_exists($DIR)) {
            mkdir($DIR);
        }

        $Result = ORMBase::query('select database()',null,\PDO::FETCH_NUM);
        $Database = $Result[0][0];

		$Query = "SHOW TABLES";
        $Result = ORMBase::query($Query,null,\PDO::FETCH_NUM);

        foreach ($Result AS $row) {
            $Classname = $this->buildClassname($row[0]);
            $handle = fopen($DIR.$Classname.".php", "w");
            if(fwrite ( $handle , $this->buildclass($row[0],$Classname) )) {
                fclose($handle);
            }
            else
            {
                fclose($handle);
                return FALSE;
            }
        }
    }
	
	function classgenerator($tablename,$class = null)
	{		
		global ${"".MyORMSQL};
		$sqlormconnect = ${"".MyORMSQL};

		$select = MyORMSQL;
		$save = MyORMSQL2;
		if (!isset($save))
			$save = MyORMSQL;
	
		$relation = MyORMRELATION;
		$cascade = MyORMCASCADE;
	
		$intotheset = MyORMINTOTHESET;
		$interface = array();	
		$parents_func = array();
		if (is_null($class)) {
			$class = $tablename;
		}
		$keychar="";
		$thisvarinparent = array();
		$key = "";
		$childobject  ="";
		
		$table_relation_exists = ($sqlormconnect->sql_table_exists($relation)) ? TRUE : FALSE;
	
$c = "<?php
namespace MyORM;

use MyException\MyException;
		
/*
*
* -----------------------------------------------------------------------------------
* ORM version : ". MyORMFileVersion ."
* Class Name : $class
* Generator : ORMGEN by PLATEL Renaud generated on ". gethostname() ."
* Date Generated : ".date("d.m.Y H")."h
* File name : $class.php
* Table : ".$sqlormconnect->get_Database().".$tablename 
* -----------------------------------------------------------------------------------
*/

class $class extends ORMBase implements \JsonSerializable
{
	
    // **********************
    // Variables
    // **********************
    ";

        /**
         * Generate variables
         */
        $Result = ORMBase::query("SHOW COLUMNS FROM  `".$tablename."`");
        foreach ($Result AS $row) {
            $col = $row->Field;
            if (strpos($row->Type,"(")) {
                $type=substr($row->Type,0,strpos($row->Type,"("));
                if ($type == "varchar") {
                    $type = str_replace("(","-",substr($row->Type,0,strpos($row->Type,")")));
                }
            }
            else {
                $type=$row->Type;
            }

            if ($row->Null == "NO") {
                $isnull = 0;
            }
            else {
                $isnull = 1;
            }

            if($row->Key == "PRI") {
                $PK = $row->Field;
$c.= "
    protected $$col; // PRI
    const primary_key = '$col'; // PRI";
                if (!(($type!='timestamp')&&($type!='date')&&($type!='datetime')&&($type!='char')&&($type!='varchar')&&($type!='tinyblob')&&($type!='tinytext')&&($type!='blob')&&($type!='text')&&($type!='mediumblob')&&($type!='mediumtext')&&($type!='longblob')&&($type!='longtext')&&($type!='time')&&($type!='enum'))) {
                $keychar =1;
                }
                $key = $col;
                $isnull = 2;
            }
            else {
$c.= "
    protected $$col;";
            }
            $interface[$col] = array($col, $type, $isnull, 0);
        }

        if ($key == "") {
            throw new \Exception("No primary key on table `".$tablename."` ORM can't work");
        }
		

		if ($relation!="")
		{
$c.="

    // **********************
    // Parents object for this class (Keys)
    // **********************
    ";
			/* Relations for relation table of your database or foreign keys */
			if ($table_relation_exists)
			{
				$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE TABLE_NAME = '".$tablename."'";
				$result = $sqlormconnect->sql_query($sqlorm);
				$i=0;

				while ($row = $sqlormconnect->sql_fetch_object($result))
				{					
					$parentvar="Parent".str_replace ( "Id" , "" , str_replace ( "id" , "" , str_replace ( "ID" , "" , $row->COLUMN_NAME )));
					$interface[$parentvar] = array($parentvar, "ParentObject", 1, 0);
					$thisvarinparent[$i]=$row->COLUMN_NAME;
					$i++;
					$c.= "
    protected $$parentvar;";
				}
			}
			else
			{
				$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '".$tablename."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."' AND REFERENCED_COLUMN_NAME IS NOT NULL";
				$result = $sqlormconnect->sql_query($sqlorm);
				$i=0;
				while ($row = $sqlormconnect->sql_fetch_object($result))
				{
					$parentvar="Parent".str_replace ( "Id" , "" , str_replace ( "id" , "" , str_replace ( "ID" , "" , $row->COLUMN_NAME )));
					$interface[$parentvar] = array($parentvar, "ParentObject", 1, 0);
					$thisvarinparent[$i]=$row->COLUMN_NAME;
					$i++;
$c.= "
	protected $".$parentvar.";";
				}
			}
		}		

		if ($relation!="")
		{
$c.="

    // **********************
    // Childs array of object for this class (Foreign Keys)
    // **********************
    ";

			/* Relations for relation table of your database or foreign keys */
			if ($table_relation_exists)
			{
				$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE REFERENCED_TABLE_NAME = '".$tablename."'";
				$result = $sqlormconnect->sql_query($sqlorm);

				while ($row = $sqlormconnect->sql_fetch_object($result))
				{
					$childvar=ucfirst($row->TABLE_NAME).str_replace ( "Id" , "" , str_replace ( "id" , "" , str_replace ( "ID" , "" , $row->COLUMN_NAME )));
					$interface[$childvar] = array($childvar, "ChildObject", 1, 0);
$c.= "
    protected $$childvar;";
				}
			}
			else
			{
				$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = '".$tablename."' AND REFERENCED_COLUMN_NAME = '".$key."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."'";
				$result = $sqlormconnect->sql_query($sqlorm);
				while ($row = $sqlormconnect->sql_fetch_object($result))
				{
					$childvar=ucfirst($row->TABLE_NAME).str_replace ( "Id" , "" , str_replace ( "id" , "" , str_replace ( "ID" , "" , $row->COLUMN_NAME )));
					$interface[$childvar] = array($childvar, "ChildObject", 1, 0);
$c.= "
    protected $$childvar;";
				}
			}
		}

$c.= "

    // **********************
    // Interface to control the variable of this class and the update flag
    // **********************

    private \$Database; // Database for this object
    private \$isNew = 0; // Memory for insert
    private \$isToSaveOrToUpdate = 0; // Memory for update
    //Memory array of fields for update
    private \$structure = array(
        ";
		foreach($interface as $colomName=>$colomArray)
		{
			// 0 : Nom du champ
			// 1 : type
			// 2 : Nullalbe
			// 3 : Is to save ?
			// 4 : Valeur
        $c .= "'$colomName' => array('". $colomArray[0] ."', '". $colomArray[1] ."', '". $colomArray[2] ."', '". $colomArray[3] ."', '')";

			if(end($interface) !== $colomArray)
			{
        $c .= ",
        ";
			}
		}
$c .= "
    );

    // **********************
    // Constructor
    // **********************
    function __construct (\$val = null, \$property = self::primary_key, \$properties = null)
    {
        global \$$select;
        \$forced = 0;
        
        if (is_null(\$property)) {
            \$property = self::primary_key;
        }
        else {
            if (\$property == \"ForcedObjectFromID\") {
                \$property = self::primary_key;
                \$forced = 1;
            }
        }

        if ( (isset(\$val)) && (!is_null(\$val)) ) {
            if (\$property == \"reloadObjectFromJsonDecodeObject\") {
                foreach (\$val AS \$key => \$value) {
                    if (!isset(\$this->structure[\$key])) {
                        throw new \\MyException\\MyException(\"set for \$key doesn't exists\");
                    }
                    else {
                        if ( (\$this->structure[\$key][1] == \"ParentObject\") && (!empty(\$value)) ) {
                            \$classname = str_replace(\"parent_\",\"\",'MyORM\\\\'.strtolower(\$key));
                            \$this->{\$key} = new \$classname(\$value,\"reloadObjectFromJsonDecodeObject\");
                        }
                        elseif ( (\$this->structure[\$key][1] == \"ChildObject\") && (!empty(\$value)) ) {
                            \$Return = array();
                            \$classname = str_replace(\"_".$class."\",\"\",'MyORM\\\\'.strtolower(\$key));
                            foreach (\$value AS \$key2 => \$value2) {
                                \$Return[] = new \$classname(\$value2,\"reloadObjectFromJsonDecodeObject\");
                            }
                            \$this->{\$key} = \$Return;
                        }
                        else {
                            \$this->{\$key} = \$this->formater(\$this->structure[\$key],\$value);
                            \$this->structure[\$key][4] = \$this->{\$key};
                            if ( (APIServer == 1) && (!is_null(\$this->{self::primary_key})) ) {                            
                                \$this->structure[\$key][3] = 1;
                                \$this->isToSaveOrToUpdate=1;
                            }
                        }
                    }
                }
                
                if ( (APIServer == 1) && (empty(\$this->{self::primary_key})) ) {
                    \$this->isNew=1;
                }
            }
            else {
                \$row = array();
                if (EnableAPIMyORM == 1 && APIServer == 0) {
                    if (\$forced == 1) {
                        \$return = Parent::callAPI(\"GET\",APIServerURL.\"/".$class."/\".\$val.\"/ForcedObjectFromID\");
                        }
                    else {
                        \$return = Parent::callAPI(\"GET\",APIServerURL.\"/".$class."/\".\$val);
                    }

                    \$return = json_decode(\$return);

                    if (!is_array(\$return)) {
                        \$array[] = \$return;
                    }

                    if(count(\$array)!=0) {
                        \$row = \$array[0];
                    }
                    else {
                        \$this->isNew=1;
                        \$this->isToSaveOrToUpdate=1;
                    }
                }
                else {
                    \$result = ORMBase::query(parent::getSelectQuery(\"".$class."\",array(array (\"\",\$property,\"Equal\",parent::quote(\$this->structure[\$property],\$val))),\"1\"));

                    if(count(\$result) != 0) {
                        \$row = \$result[0];
                    }
                    else {
                        \$this->isNew=1;
                        \$this->isToSaveOrToUpdate=1;
                    }
                }
    
                if (\$this->isNew==0) {
                    foreach (\$this->structure AS \$key => \$value) {
                        if ( (\$this->structure[\$key][1] != \"ParentObject\") && (\$this->structure[\$key][1] != \"ChildObject\") ) {
                            \$this->{\$key} = \$this->formater(\$this->structure[\$key],\$row->{\$key});
                            \$this->structure[\$key][4] = \$row->{\$key};
                        }
                        elseif ( (\$this->structure[\$key][1] == \"ParentObject\") && (!empty(\$row->{\$key})) ) {
                                \$classname = str_replace(\"parent_\",\"\",'MyORM\\\\'.strtolower(\$key));
                                \$this->{\$key} = new \$classname(\$row->{$key},\"reloadObjectFromJsonDecodeObject\");
                            }
                            elseif ( (\$this->structure[\$key][1] == \"ChildObject\") && (!empty(\$row->{\$key})) ) {
                                \$Return = array();
                                \$classname = str_replace(\"_".$class."\",\"\",'MyORM\\\\'.strtolower(\$key));
                                foreach (\$row->{\$key} AS \$key2 => \$value2)
                                {
                                    \$Return[] = new \$classname(\$value2,\"reloadObjectFromJsonDecodeObject\");
                                }
                                \$this->{\$key} = \$Return;
                            }
                    }
                }
                
                if (\$forced == 1) {
                    \$this->{\$property} = \$this->formater(\$this->structure[\$property],\$val);
                    \$this->structure[\$property][4] = \$val;
                }
            }
        }
    }
    ";

    $c.="

    // **********************
    // Generic get & set method for this class
    // **********************

    ";

        $c.="public function __get( \$property ) {
        if ( is_callable( array(\$this,'get_'.(string)\$property) ) ) {
            return call_user_func( array(\$this,'get_'.(string)\$property) );
        }
        else {
            throw new \\Exception(\"get for \$property doesn't exists\");
        }
    }

    public function __set( \$property, \$val ) {
        if ( is_callable( array(\$this,'set_'.(string)\$property) ) ) {
            if ( \$val !== call_user_func( array(\$this,'get_'.(string)\$property) ) ) {
                call_user_func( array(\$this,'set_'.(string)\$property), \$val );
            }
        }
        else {
            throw new \\Exception( \"set for \$property doesn't exists\");
        }
    }

    public function __isset(\$property = NULL) {
        if ( is_callable( array(\$this,'get_'.(string)\$property) ) ) {
            \$return = call_user_func( array(\$this,'get_'.(string)\$property) );

            if(empty(\$return) || is_null(\$return)) {
                return FALSE;
            }
            else {
                return TRUE;
            }
        }
        else {
            throw new \\Exception(\"get for \$property doesn't exists\");
        }
    }

    public function __unset(\$property)
    {
        if ( is_callable( array(\$this,'set_'.(string)\$property) ) )
            return call_user_func( array(\$this,'set_'.(string)\$property), NULL );
        else
            throw new \\MyException\\MyException(\"set for \$property doesn't exists\");
    }
	
    // **********************
    // Specific get & set method for this class
    // **********************

    /*
    * @return int
    */
    public function get_isNew() {
		if (empty(\$this->{self::primary_key})) {
            \$this->isNew = 1;
        }
        return intval(\$this->isNew);
    }

    /*
    * @return int
    */
    public function get_isToSaveOrToUpdate () {
        return intval(\$this->isToSaveOrToUpdate );
    }

    /*
    * @return array
    */
    public function get_structure() {
        return \$this->structure;
    }
	
    ";
	
	
	$sqlorm="SHOW COLUMNS FROM `".$tablename."`";
	
	$result = $sqlormconnect->sql_query($sqlorm);
	while ($row = $sqlormconnect->sql_fetch_object($result))
	{
		$col=$row->Field;
		$c.="/*
    * @return ".ORMBase::getTypeFromDatabaseType($row->Type)."
    */
	public function get_".$col."()
    {
		if (!empty(\$this->".$col.")) {
			return stripslashes(\$this->".$col.");
		}
		else {
			return \$this->".$col.";
		}
    }

    ";

            if ($col != $PK)
            {
    $c.="/*
    * @return ".ORMBase::getTypeFromDatabaseType($row->Type)."
    */
	public function set_".$col."(\$valeur = null) {
        if ( ( \$this->$col != \$valeur ) || ((!is_null(\$valeur)) && (is_null(\$this->$col))) || ((is_null(\$valeur)) && (!is_null(\$this->$col))) ) {
            if(is_null(\$valeur)) {
                \$this->$col = NULL;
            }
            else {
                \$this->$col = parent::quote(\$this->structure['$col'][1],\$valeur);
            }
            \$this->isToSaveOrToUpdate=1;
            \$this->structure['$col'][3]=1;

            \$test = explode(\"-\",\$this->structure['$col'][1]);
            if ( ( \$test[0] == \"varchar\" ) && ( isset( \$test[1] ) ) ) {
                \$this->$col = substr(\$this->$col,0,\$test[1]);
            }
        }";
		
            if (trim($intotheset)!="")
        $c.=trim($intotheset);

        if (is_array($thisvarinparent)) {
                if (in_array($col, $thisvarinparent)) {
                    $varname="Parent".str_replace ( "Id" , "" , str_replace ( "id" , "" , str_replace ( "ID" , "" , $col )));

                    $c.="
        if (trim(\$this->".$col.")=='') {
            \$this->".$col." = null;
            \$this->set_".$varname."(null);
        }

        if (!is_null(\$this->".$varname.")) {
            \$this->get_".ucfirst($varname)."(1);
        }";

                }
            }
            $c.="
        return \$valeur;
    }

    ";
	}
	}
	
	if ($relation!="")
	{
	$c.="// **********************
    // Specific get & set method for parents objects
    // **********************

    ";
	
		if ($table_relation_exists)
		{
			$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE TABLE_NAME = '".$tablename."'";
		}
		else
		{
			$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '".$tablename."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."' AND REFERENCED_COLUMN_NAME IS NOT NULL";
		}
		$result = $sqlormconnect->sql_query($sqlorm);
		
		if ($sqlormconnect->sql_num_rows($result) !== 0)
		{	
			while ($row = $sqlormconnect->sql_fetch_object($result))
			{
				$varname=str_replace ( "Id" , "" , str_replace ( "id" , "" , str_replace ( "ID" , "" , $row->COLUMN_NAME )));
				$parenttable=$row->REFERENCED_TABLE_NAME;
				$parentcolumn=$row->REFERENCED_COLUMN_NAME;
				$childcolumn=$row->COLUMN_NAME;
				$childobject = $row->REFERENCED_TABLE_NAME;
					
				$parents_func[] = "get_Parent".ucfirst($varname)."";
			
    $c.= "/*
    * @return ".$childobject."
    */
    public function get_Parent".ucfirst($varname)."(\$forced = null) {
        if ( ( is_null(\$this->Parent".$varname.") || !empty(\$forced) ) && (!empty(\$this->".$childcolumn.") ) ) {
            \$this->Parent".$varname." = new ".$childobject."(\$this->".$childcolumn.");
        }

        return \$this->Parent".$varname.";
    }

     /*
    * @return ".$childobject."
    */
    public function set_Parent".ucfirst($varname)."(\$$childobject) {
        \$this->$childcolumn=$".$childobject."->".$parentcolumn." ?? null;
        \$this->structure[\"$childcolumn\"][3]=1;
        \$this->isToSaveOrToUpdate=1;
        \$this->Parent$varname=$$childobject;
        return ($".$childobject.");
    }

    ";
			}
		}
	}
	
	if ($relation!="")
	{
    $c.="// **********************
    // Specific get & set method for childs objets
    // **********************
    ";
	
		if ($table_relation_exists)
		{
			$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE REFERENCED_TABLE_NAME = '".$tablename."' AND REFERENCED_COLUMN_NAME = '".$key."'";
		}
		else
		{
			$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = '".$tablename."' AND REFERENCED_COLUMN_NAME = '".$key."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."'";
		}
		$result = $sqlormconnect->sql_query($sqlorm);

		if ($sqlormconnect->sql_num_rows($result)!=0)
		{
			while ($row = $sqlormconnect->sql_fetch_object($result))
			{

				$varname=ucfirst($row->TABLE_NAME).str_replace ( "Id" , "" , str_replace ( "id" , "" , str_replace ( "ID" , "" , $row->COLUMN_NAME )));
				
				$childtable=$row->TABLE_NAME;
				$childobject=$row->TABLE_NAME;
				$childcolumn=$row->COLUMN_NAME;
			
				$col = $sqlormconnect->sql_primary_key($childtable);
				if (is_null($col))
				{
					$orderby="";
				}
				else
				{
					$orderby=" ORDER BY ".$col;
				}
			
				if ($keychar==1)
				{
					$querydelete = "\"DELETE FROM `$childtable` WHERE $childcolumn = '\".$"."this->$key".".\"'\"";
					$query="\"SELECT * FROM `$childtable` WHERE $childcolumn = '\".$"."this->$key".".\"'";
					if ($orderby!="") $query.=$orderby."\""; else $query.= "\"";
				}
				else
				{
					$query="\"SELECT * FROM `$childtable` WHERE $childcolumn = \".$"."this->$key";
					if ($orderby!="") $query.=".\"".$orderby."\"";
					$querydelete = "\"DELETE FROM `$childtable` WHERE $childcolumn = \".$"."this->$key";
				}
	
		$c.="
    /*
    * @return ".$this->buildClassname($childtable)."[]
    */
    public function get_".ucfirst($varname)."() {
        if ((is_null(\$this->".$varname."))&&(!is_null(\$this->$key))) {
            \$this->".$varname." = \$this->getList(\"".$childtable."\",array(array (\"\",\"".$childcolumn."\",\"Equal\",\$this->$key)),\"".$this->buildClassname($childtable)."\");
        }
        return (\$this->".$varname.");
    }

    /*
    * @return ".$this->buildClassname($childtable)."[]
    */
    public function set_".ucfirst($varname)."($".$childobject."s) {
        if (!isset(\$this->".$varname.") || (isset(\$this->".$varname.") && count(\$this->".$varname.")==0)) {
            foreach ($".$childobject."s as \$var) {
                \$this->add_".ucfirst($varname)."(\$var);
            }
        }
        else  {
            throw new \\Exception( \"Can set ".$varname." cause actual ".$varname." is not empty\");
        }
        return ($".$childobject."s);
    }

    public function add_".ucfirst($varname)."(".$childobject." $".$childobject.") {
        if ($".$childobject."->$childcolumn!=\$this->$key) {
            $".$childobject."->set_$childcolumn(\$this->$key);
        }
        if (isset(\$this->".$varname.") && is_array(\$this->".$varname.")) {
            \$count=count(\$this->".$varname.");
        }
        else {
            \$count=0;
        }
        while (isset(\$this->".$varname."[\$count])) {
            \$count++;
        }
        \$this->".$varname."[\$count]=$".$childobject.";
        \$this->isToSaveOrToUpdate = 1;
    }

    public function remove_".ucfirst($varname)."(".$childobject." \$remove".$childobject.") {
        \$this->get_".ucfirst($varname)."();
        if (!empty(\$this->".ucfirst($varname).")) {
            foreach (\$this->".ucfirst($varname)." as \$key => \$var) {
                if (\$remove".$childobject." == \$var) {
                    \$var->delete();
                    unset(\$this->".ucfirst($varname)."[\$key]);
                    \$this->".ucfirst($varname)." = array_values(\$this->".ucfirst($varname).");
                }
            }
        }
    }

    protected function save_".ucfirst($varname)."(\$transaction = null) {
        \$this->get_".ucfirst($varname)."();
        if (!empty(\$this->".ucfirst($varname).")) {
            foreach (\$this->$varname as \$$childobject) {
                if ($".$childobject."->$childcolumn!=\$this->$key) {
                    $".$childobject."->set_$childcolumn(\$this->$key);
                }
                $".$childobject."->save(\$transaction);
            }
        }
    }

    public function delete_".ucfirst($varname)."(\$transaction = null) {
        \$this->get_".ucfirst($varname)."();
        if (isset(\$this->$varname)) {
            foreach (\$this->$varname as \$$childobject) {
                $".$childobject."->delete(\$transaction);
            }
        }
        \$this->".ucfirst($varname)."= null;
    }
    ";
			}
		}
	}
	
	/* delete, cascade for childs arrays*/
	$c.="
    // **********************
    // DELETE
    // **********************

    public function delete(\$transaction = null) {
        \$thistransaction = \"Off\";
        \$return = null;

        if ((isset(\$this->$key))&&(\$this->$key!=0)) {
            if (is_null(\$transaction)) {
                \$thistransaction=\"On\";
                \$transaction = \"On\";
                ORMBase::beginTransaction();
            }";
	
	if ($table_relation_exists)
	{
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE REFERENCED_TABLE_NAME = '".$tablename."' AND REFERENCED_COLUMN_NAME = '".$key."'";
	}
	else
	{
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = '".$tablename."' AND REFERENCED_COLUMN_NAME = '".$key."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."'";
	}
	$result = $sqlormconnect->sql_query($sqlorm);
	
	if ($sqlormconnect->sql_num_rows($result)>0)
	{
	while ($row = $sqlormconnect->sql_fetch_object($result))
	{
		$varname=ucfirst($row->TABLE_NAME).str_replace ( "Id" , "" , str_replace ( "id" , "" , str_replace ( "ID" , "" , $row->COLUMN_NAME )));
		$childtable=$row->TABLE_NAME;
		$childobject= $row->TABLE_NAME;
		$childcolumn=$row->COLUMN_NAME;
	
$c.="
            if ( null !== \$this->get_".$varname."() && (count(\$this->get_".$varname."())!=0) ) {
                \$this->delete_".$varname."(\$transaction);
            }";
		}
	}
	
$c.="
			if (\$transaction==\"On\") {
                \$this->updateStructureArray();
                if (\$Query = parent::makequery('DELETE', '".$tablename."', \$this->structure)) {
                    \$result=ORMBase::queryNoReturn(\$Query,'write');
                }
            }

            if ((\$transaction==\"On\")&&(\$thistransaction==\"On\")) {
                ORMBase::commit();
            }
        }

        unset(\$This);

        return \$return;
    }
";
	
	$c.="
    // **********************
    // SAVE (INSERT or UPDATE)
    // **********************

    public function save(\$transaction = null)
    {		
        global $"."$save;
        \$thistransaction = \"Off\";

        if (is_null(\$transaction)) {
            \$thistransaction=\"On\";
            \$transaction = \"On\";
            ORMBase::beginTransaction();
        }

        if ((is_null(\$this->".$key."))||(\$this->".$key."==0)) {
            \$this->isToSaveOrToUpdate=1;
            \$this->isNew=1;
            \$this->structure['$key'][\"3\"] = 1;
        }";

	
        /* Parents save if some changes */
        if ($table_relation_exists)
        {
                $sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE TABLE_NAME = '".$tablename."'";
        }
        else
        {
                $sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '".$tablename."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."' AND REFERENCED_COLUMN_NAME IS NOT NULL";
        }
        $result = $sqlormconnect->sql_query($sqlorm);
        if ($sqlormconnect->sql_num_rows($result)>0)
        {
            while ($row = $sqlormconnect->sql_fetch_object($result))
            {
                $varname=str_replace ( "Id" , "" , str_replace ( "id" , "" , str_replace ( "ID" , "" , $row->COLUMN_NAME )));
                $parenttable=$row->REFERENCED_TABLE_NAME;
                $parentcolumn=$row->REFERENCED_COLUMN_NAME;
                $childcolumn=$row->COLUMN_NAME;
                $childobject = $row->REFERENCED_TABLE_NAME;

    $c.="
        if (!empty(\$this->"."Parent".$varname.")) {
            \$this->set_".$childcolumn."(\$this->"."Parent".$varname."->save(\$transaction));
        }
        ";
            }
        }
        
	$c.="
        if (\$this->isToSaveOrToUpdate == 1) {
            if (EnableAPIMyORM == 1 && APIServer == 0) {
                \$json = \$this->toJson('onlychange');

                if ((isset(\$this->$key))&&(\$this->$key!=\"0\")&&(\$this->isNew!=1)) {
                    Parent::callAPI(\"PUT\",APIServerURL.\"/".$class."/\".\$this->$key,\$json);
                }
                else {
                    \$this->$key=Parent::callAPI(\"POST\",APIServerURL.\"/".$class."/\",\$json);
                    \$this->structure['$key'][\"4\"] = \$this->$key;
                    \$this->isNew=0;
                } 
            }
            else {
				\$this->updateStructureArray();
				if ((isset(\$this->$key))&&(\$this->$key!=\"0\")&&(\$this->isNew!=1)) {
					if (\$Query = parent::makequery('UPDATE', '".$tablename."', \$this->structure)) {
						\$result=ORMBase::queryNoReturn(\$Query,'write');
					}
				}
				else {
					if (\$Query = parent::makequery('INSERT', '".$tablename."', \$this->structure)) {
						\$result=ORMBase::queryNoReturn(\$Query,'write');
					}
					\$this->$key=ORMBase::lastInsertId();
					\$this->structure['$key'][\"4\"] = \$this->$key;
					\$this->isNew=0;
				}
            }
        }
	";
	
        /* Childs save if some changes */
        if ($table_relation_exists) {
            $sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE REFERENCED_TABLE_NAME = '".$tablename."' AND REFERENCED_COLUMN_NAME = '".$key."'";
        }
        else {
            $sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = '".$tablename."' AND REFERENCED_COLUMN_NAME = '".$key."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."'";
        }
        $result = $sqlormconnect->sql_query($sqlorm);
        if ($sqlormconnect->sql_num_rows($result)>0)
        {
            while ($row = $sqlormconnect->sql_fetch_object($result))
            {
                $varname=ucfirst($row->TABLE_NAME).str_replace ( "Id" , "" , str_replace ( "id" , "" , str_replace ( "ID" , "" , $row->COLUMN_NAME )));
                $childtable=$row->TABLE_NAME;
                $childobject= $row->TABLE_NAME;
                $childcolumn=$row->COLUMN_NAME;
				$c.="
		if (!empty($"."this->".$varname."))
				$"."this->save_".$varname."($"."transaction);
		";
            }
        }
	
		$c.= "
        $"."this->isToSaveOrToUpdate=0;
        foreach ($"."this->structure as \$field)
            if(isset(\$field[0]))
            {
                $"."this->structure[\$field[0]][3]=0;
            }

        if (\$thistransaction == \"On\") {
            \$thistransaction=\"Off\";
            \$transaction = null;
            ORMBase::commit();
		}
		
        return $"."this->$key;
    }
	
	public function __toString() {
		$"."this->toString();
	}

    public function toString($"."var = 'first')
    {
        $"."this->LoadAllParents();
        $"."this->LoadAllChilds();
        $"."return = \"Object \".__CLASS__.\" (<br>\";
        foreach ($"."this->structure as \$field)
        {
            if ( (\$field[1] == 'ChildObject') && (!is_null($"."this->{"."\$field[0]})) && ( ( $"."var == 'first' ) || ( $"."var == 'down' ) ) )
            {
                $"."return .= '\"'.\$field[0].'\" =>';
                $"."return .= \" Array ( <br>\";
                $"."i=0;
                foreach ($"."this->{"."\$field[0]} as &$"."childvar)
                {
                    $"."return .= $"."childvar->toString('down');
                    $"."return .= \",<br>\";
                    $"."i++;
                }
                $"."return = substr($"."return, 0, -5);
                $"."return .= \"<br> ) <br>\";
            }
            else
            {
                if ( (\$field[1] == 'ParentObject') && (!is_null($"."this->{"."\$field[0]})) && ( $"."var == 'first' ) )
                {
                    $"."return .= '\"'.\$field[0].'\" => ';
                    $"."return .= $"."this->{"."\$field[0]}->toString('none');
                    $"."return .= \"<br>\";
                }
                else
                {					
                    if ($"."this->{"."\$field[0]}==\"\")
                    {
                        if (\$field[2]==1)
                        {
                            $"."return .= '\"'.\$field[0].'\" => null<br>';
                        }
                        else
                        {
                            $"."return .= '\"'.\$field[0].'\" => \"\"<br>';
                        }
                    }
                    else
                    {
                        if ( (\$field[1] != 'ParentObject') && (\$field[1] != 'ChildObject') )
                        {
                            $"."return .= '\"'.\$field[0].'\" => '.$"."this->{"."\$field[0]}.'<br>';
                        }
                    }
                }
            }
        }
        
        $"."return .= \")\";
        return $"."return;
    }

    public function __clone() {
        \$this->LoadAllChilds();
        \$this->$key = null;
        \$this->structure['$key'][4] = \"\";
        \$this->isNew = 1;
        \$this->isToSaveOrToUpdate = 1;
        foreach (\$this->structure as \$field) {
            if ((\$field[1] == 'ChildObject')&&(!is_null(\$this->{\$field[0]}))) {
                foreach (\$this->{\$field[0]} as &\$childvar)
                    \$childvar = clone \$childvar;
            }
            elseif (\$field[1] != 'ParentObject') {
                \$this->structure[\$field[0]][3] = 1;
            }
        }
    }
	
	public function duplicate() {
        \$this->$key = null;
        \$this->structure['$key'][4] = \"\";
        \$this->isNew = 1;
        \$this->isToSaveOrToUpdate = 1;
        foreach (\$this->structure as \$field) {
            if (\$field[1] != 'ParentObject') {
                \$this->structure[\$field[0]][3] = 1;
            }
        }

        return \$this;
    }
	
    public function LoadAllParents()
    {
        ";
	if ($table_relation_exists)
	{
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE REFERENCED_TABLE_NAME = '".$tablename."' AND REFERENCED_COLUMN_NAME = '".$key."'";
	}
	else
	{
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = '".$tablename."' AND REFERENCED_COLUMN_NAME = '".$key."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."'";
	}
	$result = $sqlormconnect->sql_query($sqlorm);
	if ($sqlormconnect->sql_num_rows($result)>0)
	{
		while ($row = $sqlormconnect->sql_fetch_object($result))
		{
			$varname=ucfirst($row->TABLE_NAME).str_replace ( "Id" , "" , str_replace ( "id" , "" , str_replace ( "ID" , "" , $row->COLUMN_NAME )));
			$childtable=$row->TABLE_NAME;
			$childobject= $row->TABLE_NAME;
			$childcolumn=$row->COLUMN_NAME;
	
			$c.="if (!is_null($"."this->".$varname."))
            $"."this->get_".$varname."();
";
	
		}
	}
		
$c .= "
    }
	
    private function updateStructureArray() {
        foreach (\$this->structure as \$field) {
            if ( (\$field[1] != 'ParentObject') && (\$field[1] != 'ChildObject') ) {
                \$this->structure[\$field[0]][4] = \$this->{\$field[0]};
            }
        }
    }

    public function LoadAllChilds() {";
	$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = '".$tablename."' AND REFERENCED_COLUMN_NAME = '".$key."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."'";
    $Result = ORMBase::query($sqlorm);
    foreach ($Result AS $row) {
        $varname=ucfirst($row->TABLE_NAME).str_replace ( "Id" , "" , str_replace ( "id" , "" , str_replace ( "ID" , "" , $row->COLUMN_NAME )));
        $childtable=$row->TABLE_NAME;
        $childobject= $row->TABLE_NAME;
        $childcolumn=$row->COLUMN_NAME;

        $c.="
        \$this->get_".$varname."();";
	}
	$c.="
    }

    public function jsonSerialize():mixed {
        //\$this->LoadAllChilds();
        \$return =  array();
        foreach (array_keys(\$this->structure) AS \$Key){
            \$return[\$Key] = \$this->{\$Key};
        }
        return \$return;
    }
    //endofclass
}";
    return $c;
	}
	
}
