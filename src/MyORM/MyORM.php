<?php 

namespace MyORM;

class MyORM {
	function __construct() {
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
                elseif(file_exists(__DIR__.'/../../'. MyORMDAL . $classname . "_v". MyORMFileVersion .".php")) /* File exists dans le dossier DAL ? */
                {
                    require __DIR__.'/../../'. MyORMDAL . $classname . "_v". MyORMFileVersion .".php";
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
                                if($ORM->saveclasstofile($classname ."_v" . MyORMFileVersion .".php", $content, __DIR__.'/../../'. MyORMDAL)) {
                                        require(__DIR__.'/../../'. MyORMDAL . $classname ."_v". MyORMFileVersion .".php");
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
                                if($ORM->saveclasstofile($classname ."_v" . MyORMFileVersion .".php", $content, __DIR__.'/../../'. MyORMDAL)) {
                                    require(__DIR__.'/../../'. MyORMDAL . $classname ."_v". MyORMFileVersion .".php");
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
	
	function classgenerator($table)
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
		$class = $table;
		$keychar="";
		$thisvarinparent ="";
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
* Table : ".$sqlormconnect->get_Database().".$table 
* -----------------------------------------------------------------------------------
*/

class $class extends Common
{
	
    // **********************
    // Variables
    // **********************
    ";

            /**
             * Generate variables
             */
                            $result = $sqlormconnect->sql_query("SHOW COLUMNS FROM  `".$table."`");
                            while ($row = $sqlormconnect->sql_fetch_object($result))
                            {
                                    $col = $row->Field;
                                    if (strpos($row->Type,"("))
                                    {
                                            $type=substr($row->Type,0,strpos($row->Type,"("));
                                            if ($type == "varchar")
                                            {
                                                    $type = str_replace("(","-",substr($row->Type,0,strpos($row->Type,")")));
                                            }
                                    }
                                    else
                                            $type=$row->Type;
                                    if ($row->Null == "NO")
                                            $isnull = 0;
                                    else
                                            $isnull = 1;

                                    if($row->Key == "PRI")
                                    {
                                            $PK = $row->Field;
                                            $c.= "
    protected $$col; // PRI
    const primary_key = '$col'; // PRI";
                                            if ($type!="int")
                                            $keychar =1;
                                            $key = $col;
                                            $isnull = 2;
                                    }
                                    else
                                    {
                                            $c.= "
    protected $$col;";
                                    }
                                    $interface[$col] = array($col, $type, $isnull, 0);
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
                                    $sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE TABLE_NAME = '".$table."'";
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
                                    $sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '".$table."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."' AND REFERENCED_COLUMN_NAME IS NOT NULL";
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
                                    $sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE REFERENCED_TABLE_NAME = '".$table."'";
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
                                    $sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = '".$table."' AND REFERENCED_COLUMN_NAME = '".$key."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."'";
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
                    \$query = \$connection->sql_query(parent::getSelectQuery(\"".$class."\",array(array (\"\",\$property,\"Equal\",parent::quote(\$this->structure[\$property],\$val))),\"1\"));

                    if(\$connection->sql_num_rows(\$query) != 0) {
                        \$row = \$connection->sql_fetch_object(\$query);
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
        else
        {   
            \$this->isNew=1;
            \$this->isToSaveOrToUpdate=1;
        }
    }
    ";

    $c.="

    // **********************
    // Generic get & set method for this class
    // **********************

    ";

    $c.="public function __get( \$property )
    {
        if ( is_callable( array(\$this,'get_'.(string)\$property) ) )
        {
            return call_user_func( array(\$this,'get_'.(string)\$property) );
        }
        else
        {
            throw new \\MyException\\MyException(\"get for \$property doesn't exists\");
        }
    }

    public function __set( \$property, \$val )
    {
        if ( is_callable( array(\$this,'set_'.(string)\$property) ) )
        {
            if ( \$val != call_user_func( array(\$this,'get_'.(string)\$property) ) )
            {
                call_user_func( array(\$this,'set_'.(string)\$property), \$val );
            }
        }
        else
        {
            throw new \\MyException\\MyException( \"set for \$property doesn't exists\");
        }
    }

    public function __isset(\$property = NULL)
    {
        if ( is_callable( array(\$this,'get_'.(string)\$property) ) ) {
            \$return = call_user_func( array(\$this,'get_'.(string)\$property) );

            if(empty(\$return) || is_null(\$return))
            {
                return FALSE;
            }
            else
            {
                return TRUE;
            }
        }
        else {
            throw new \\MyException\\MyException(\"get for \$property doesn't exists\");
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

    public function get_isNew()
    {
            return intval(\$this->isNew);
    }

    public function get_structure()
    {
            return \$this->structure;		
    }
	
    ";
	
	
	$sqlorm="SHOW COLUMNS FROM `".$table."`";
	
	$result = $sqlormconnect->sql_query($sqlorm);
	while ($row = $sqlormconnect->sql_fetch_object($result))
	{
		$col=$row->Field;
		$c.="public function get_".$col."()
    {
        return stripslashes(\$this->".$col.");
    }

    ";

            if ($col != $PK)
            {
    $c.="public function set_".$col."(\$valeur = null)
    {
        if ( ( \$this->$col != parent::quote(\$this->structure['$col'][1],\$valeur) ) && ( \$this->$col != \$valeur ) )
        {	
            if(is_null(\$valeur))
            {
                \$this->$col = NULL;
            }
            else
            {
                \$this->$col = parent::quote(\$this->structure['$col'][1],\$valeur);
            }
            \$this->isToSaveOrToUpdate=1;
            \$this->structure['$col'][\"3\"]=1;
            \$this->structure['$col'][\"4\"] = \$this->$col;

            \$test = explode(\"-\",\$this->structure['$col'][1]);
            if ( ( \$test[\"0\"] == \"varchar\" ) && ( isset( \$test[\"1\"] ) ) )
            {
                \$this->structure['$col'][\"4\"] = substr(\$this->structure['$col'][\"4\"],0,\$test[\"1\"]);
            }
            ";

            if (trim($intotheset)!="")
            $c.=trim($intotheset);

            $c .= "
        }";

                    if (is_array($thisvarinparent))
                    {
                            if (in_array($col, $thisvarinparent))
                            {
                                    $varname="Parent".str_replace ( "Id" , "" , str_replace ( "id" , "" , str_replace ( "ID" , "" , $col )));

                                    $c.="
        if (trim($"."this->".$col.")=='')
        {
            $"."this->".$col." = null;
            $"."this->".$varname." = null;
        }

        if (!is_null($"."this->".$varname."))
            $"."this->get_".ucfirst($varname)."(1);";

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
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE TABLE_NAME = '".$table."'";
	}
	else
	{
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '".$table."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."' AND REFERENCED_COLUMN_NAME IS NOT NULL";
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
			
    $c.= "public function get_Parent".ucfirst($varname)."(\$forced = 0)
    {

            \$this->Parent".$varname." = new $childobject($"."this->".$childcolumn.");

            return $"."this->Parent".$varname.";
    }

    public function set_Parent".ucfirst($varname)."($"."$childobject)
    {
            \$this->$childcolumn=$".$childobject."->".$parentcolumn.";
            \$this->structure[\"$childcolumn\"][3]=1;
            \$this->structure[\"$childcolumn\"][4]=$".$childobject."->".$parentcolumn.";
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
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE REFERENCED_TABLE_NAME = '".$table."' AND REFERENCED_COLUMN_NAME = '".$key."'";
	}
	else
	{
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = '".$table."' AND REFERENCED_COLUMN_NAME = '".$key."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."'";
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
    public function get_".ucfirst($varname)."()
    {
        global \$$select;

        if ((is_null(\$this->".$varname."))&&(!is_null(\$this->$key)))
        {
            \$this->".$varname." = Common::getList(\$$select,\"".$class."\",array(array (\"\",\"".$childcolumn."\",\"Equal\",\$this->$key)),\"".$childtable."\");
        }
        return ($"."this->".$varname.");
    }

    public function set_".ucfirst($varname)."($".$childobject."s)
    {
        if (!isset(\$this->".$varname.") || (isset(\$this->".$varname.") && count(\$this->".$varname.")==0))
            foreach ($".$childobject."s as \$var)
                \$this->add_".ucfirst($varname)."(\$var);
        else
            throw new \\MyException\\MyException( \"Can set ".$varname." cause actual ".$varname." is not empty\");
        return ($".$childobject."s);
    }

    public function add_".ucfirst($varname)."(".$childobject." $".$childobject.")
    {
        if ($".$childobject."->$childcolumn!=$"."this->$key)
            $".$childobject."->set_$childcolumn($"."this->$key);
        if (isset($"."this->".$varname.") && is_array($"."this->".$varname.")) {
            $"."count=count($"."this->".$varname.");
        }
        else {
            $"."count=0;
        }
        while (isset($"."this->".$varname."[$"."count]))
            $"."count++;
        $"."this->".$varname."[$"."count]=$".$childobject.";
    }

    public function remove_".ucfirst($varname)."(".$childobject." $"."remove".$childobject.")
    {
        foreach ($"."this->".ucfirst($varname)." as $"."var)
        {
            if ($"."remove".$childobject." == $"."var)
            {
                $"."var->delete();
                $"."this->".$childobject." = null;
            }
        }
    }

    protected function save_".ucfirst($varname)."($"."transaction = null)
    {
        foreach ($"."this->$varname as $"."$childobject)
        {
            if ($".$childobject."->$childcolumn!=$"."this->$key)
                $".$childobject."->set_$childcolumn($"."this->$key);
            $".$childobject."->save($"."transaction);
        }
    }

    public function delete_".ucfirst($varname)."($"."transaction = null)
    {
        global $"."$save;
        if (isset($"."this->$varname))
            foreach ($"."this->$varname as $"."$childobject)
            {
                $".$childobject."->delete($"."transaction);
            }

        $"."query = ".$querydelete.";
        $"."result = $".$save."->sql_query($"."query);
        if ($".$save."->sql_error($"."result))
        {
            $"."erreur=$".$save."->sql_error($"."result).\"<br>\".$"."query;
            if ($"."transaction==\"On\")
            {
                $".$save."->sql_rollbacktransaction();
            }
            throw new \\MyException\\MyException($"."erreur);
        }
        else
            return $".$save."->sql_affected_rows($"."result);

        $"."this->".ucfirst($varname)."= null;
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

    public function delete($"."transaction = null)
    {
        global $"."$save;
        $"."thistransaction = \"Off\";
        $"."return = null;

        if ((isset($"."this->$key))&&($"."this->$key!=0))
        {
            if (is_null($"."transaction))
            {
                $"."thistransaction=\"On\";
                $"."transaction = \"On\";
                if ($".$save."->TransactionMode == 1)
                    $".$save."->sql_starttransaction();
            }
	";
	
	if ($table_relation_exists)
	{
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE REFERENCED_TABLE_NAME = '".$table."' AND REFERENCED_COLUMN_NAME = '".$key."'";
	}
	else
	{
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = '".$table."' AND REFERENCED_COLUMN_NAME = '".$key."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."'";
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
            if ( null !== $"."this->get_".$varname."() && (count($"."this->get_".$varname."())!=0) && (MyORMCASCADE) )
                $"."this->delete_".$varname."($"."transaction);

            ";
	}
	}
	
	$c.= "if (($"."transaction==\"On\"))
            {
                $"."query = parent::makequery('DELETE', $".$save."->Database, '".$class."', $"."this->structure);
                $"."Result = $".$save."->sql_query($"."query);
                if ($".$save."->sql_error($"."Result))
                {
                    $"."erreur=$".$save."->sql_error($"."Result).\"<br>\".$"."query;
                    if ($".$save."->TransactionMode == 1)
                    {
                        $".$save."->sql_rollbacktransaction();
                    }
                    throw new \\MyException\\MyException($"."erreur);
                }
                else
                    $"."return = $".$save."->sql_affected_rows($"."Result);
            }

            if (($".$save."->TransactionMode == 1)&&($"."thistransaction==\"On\"))
            {
                $".$save."->sql_committransaction();
            }
            return $"."return;
        }
        foreach ($"."this->structure as \$field)
            unset($"."this->\$field[0]);	
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

        if ((is_null($"."this->".$key."))||($"."this->".$key."==0))
        {
            $"."this->isToSaveOrToUpdate=1;
            $"."this->isNew=1;
        }
        ";
	
        /* Parents save if some changes */
        if ($table_relation_exists)
        {
                $sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE TABLE_NAME = '".$table."'";
        }
        else
        {
                $sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '".$table."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."' AND REFERENCED_COLUMN_NAME IS NOT NULL";
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
        if (!empty($"."this->"."Parent".$varname."))
            $"."this->$childcolumn = $"."this->set_".$parentcolumn."($"."this->"."Parent".$varname."->save($"."transaction));	 
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
            else {";
                
        
                $c.="
                if ((isset($"."this->$key))&&($"."this->$key!=\"0\")&&($"."this->isNew!=1))
                {
                    \$query = parent::makequery('UPDATE', $".$save."->Database, '".$class."', \$this->structure);
                    \$result=$".$save."->sql_query($"."query);
                }
                else
                {
                    \$query = parent::makequery('INSERT', $".$save."->Database, '".$class."', $"."this->structure);
                    $".$save."->sql_query($"."query);
                    \$this->$key=$".$save."->sql_insert_id();
                    \$this->structure['$key'][\"4\"] = \$this->$key;
                    \$this->isNew=0;
                }";          
                
                $c.="
            }
        }
	";
	
        /* Childs save if some changes */
        if ($table_relation_exists) {
            $sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE REFERENCED_TABLE_NAME = '".$table."' AND REFERENCED_COLUMN_NAME = '".$key."'";
        }
        else {
            $sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = '".$table."' AND REFERENCED_COLUMN_NAME = '".$key."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."'";
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

        return $"."this->$key;
    }

    public function last_insert(\$property = self::primary_key)
    {
        global $"."$save;

        \$query = $".$save."->sql_query(\"SELECT \$property AS last FROM `$class` ORDER BY \".self::primary_key.\" DESC LIMIT 1\");
        \$last = $".$save."->sql_fetch_row(\$query);

        return \$last[\"last\"];
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

    public function toJson(\$var = 'all') {
    /*
     * this : return the loaded object
     * all : return all childs collection / all parent from this object
     * onlychange : return change on the loaded object (work only on loaded childs collection and loaded parent
     * '{void}' : only this object attributes.
     * all other : return all childs collection and this object attributes.
     */
    if ( (\$var != \"onlychange\") && (\$var != \"this\") ) {
        \$this->LoadAllParents();
        \$this->LoadAllChilds();
    }

    \$return = \"{\";
    foreach (\$this->structure as \$field) {
        if ( (\$field[1] == 'ChildObject') && (!empty(\$this->{\$field[0]})) && ( !empty(\$var) ) && ( \$var != 'onlychange' ) ) {
            \$return .= '\"'.\$field[0].'\":';
            \$return .= '[';
            foreach (\$this->{\$field[0]} as &\$childvar)
            {
                \$return .= \$childvar->toJson(\$var);
                \$return .= ',';
            }
            \$return = substr(\$return, 0, -1);
            \$return .= '],';
        }
        elseif ( (\$field[1] == 'ParentObject') && (!empty(\$this->{\$field[0]})) && ( ( \$var == 'all' ) || ( \$var == 'this' ) ) ) {
                \$return .= '\"'.\$field[0].'\":';
                \$return .= \$this->{\$field[0]}->toJson(\$var);
                \$return .= ',';
            }
            elseif ( (\$field[1] != 'ParentObject') && (\$field[1] != 'ChildObject') ) {
                    if ( ( ( (\$field[3]==1) || (\$field[0]==self::primary_key) ) && ( \$var == 'onlychange' ) ) || ( \$var != 'onlychange' ) ) {
                        if (empty(\$this->{\$field[0]}))
                        {
                            if (\$field[2]==1)
                            {
                                \$return .= '\"'.\$field[0].'\":null,';
                            }
                            else
                            {
                                \$return .= '\"'.\$field[0].'\":\"\",';
                            }
                        }
                        else
                        {
                            \$t=explode('-',\$field[1]);
                            if ((\$t[0]!='timestamp')&&(\$t[0]!='date')&&(\$t[0]!='datetime')&&(\$t[0]!='char')&&(\$t[0]!='varchar')&&(\$t[0]!='tinyblob')&&(\$t[0]!='tinytext')&&(\$t[0]!='blob')&&(\$t[0]!='text')&&(\$t[0]!='mediumblob')&&(\$t[0]!='mediumtext')&&(\$t[0]!='longblob')&&(\$t[0]!='longtext')&&(\$t[0]!='time')&&(\$t[0]!='enum'))
                            {
                                \$return .= '\"'.\$field[0].'\":'.\$this->{\$field[0]}.',';
                            }
                            else
                            {
                                \$return .= '\"'.\$field[0].'\":\"'.\$this->{\$field[0]}.'\",';
                            }
                        }
                    }
                }

        }
        if (\$return!='{') {
            \$return = substr(\$return,0,-1);
        }

        \$return .= '}';
        return \$return;
    }

    public function __clone()
    {
        \$this->LoadAllChilds();
        \$this->$key = null;
        \$this->structure['$key'][4] = \"\";
        \$this->isNew = 1;
        \$this->isToSaveOrToUpdate = 1;
        foreach (\$this->structure as \$field)
        {
            if ((\$field[1] == 'ChildObject')&&(!is_null(\$this->{\$field[0]})))
            {
                foreach (\$this->{\$field[0]} as &\$childvar)
                    \$childvar = clone \$childvar;
            }
            elseif (\$field[1] != 'ParentObject')
            {
                \$this->structure[\$field[0]][3] = 1;
                \$this->structure[\$field[0]][4] = $"."this->{\$field[0]};
            }
        }
    }

    public function LoadAllParents()
    {
        ";
	if ($table_relation_exists)
	{
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE REFERENCED_TABLE_NAME = '".$table."' AND REFERENCED_COLUMN_NAME = '".$key."'";
	}
	else
	{
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = '".$table."' AND REFERENCED_COLUMN_NAME = '".$key."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."'";
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
	
    public function LoadAllChilds()
    {";
	if ($table_relation_exists)
	{
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME ,REFERENCED_COLUMN_NAME FROM ".$relation." WHERE REFERENCED_TABLE_NAME = '".$table."' AND REFERENCED_COLUMN_NAME = '".$key."'";
	}
	else
	{
		$sqlorm="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = '".$table."' AND REFERENCED_COLUMN_NAME = '".$key."' AND TABLE_SCHEMA = '".$sqlormconnect->get_Database()."'";
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
        \$this->get_".$varname."();";
		}
	}
	$c.="
    }
		
    //endofclass
}
	";
		return $c;
	}
	
}
