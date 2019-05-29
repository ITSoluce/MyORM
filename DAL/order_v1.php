<?php
namespace MyORM;

use MyException\MyException;
		
/*
*
* -----------------------------------------------------------------------------------
* ORM version : 1
* Class Name : order
* Generator : ORMGEN by PLATEL Renaud generated on DESKTOP-LUT6GOK
* Date Generated : 29.05.2019 13h
* File name : order.php
* Table : ORM_Demo.order 
* -----------------------------------------------------------------------------------
*/

class order extends Common
{
	
    // **********************
    // Variables
    // **********************
    
    private $ID_Order; // PRI
    const primary_key = 'ID_Order'; // PRI
    private $ID_Customer;
    private $InvoiceNumber;

    // **********************
    // Parents object for this class (Keys)
    // **********************
    
    public $Parent_Customer;

    // **********************
    // Childs array of object for this class (Foreign Keys)
    // **********************
    
    public $Orderline_Order;

    // **********************
    // Interface to control the variable of this class and the update flag
    // **********************

    private $Database; // Database for this object
    public $isNew = 0; // Memory for insert
    private $isToSaveOrToUpdate = 0; // Memory for update
    //Memory array of fields for update
    private $structure = array(
        'ID_Order' => array('ID_Order', 'int', '2', '0', ''),
        'ID_Customer' => array('ID_Customer', 'int', '0', '0', ''),
        'InvoiceNumber' => array('InvoiceNumber', 'varchar-20', '0', '0', ''),
        'Parent_Customer' => array('Parent_Customer', 'ParentObject', '1', '0', ''),
        'Orderline_Order' => array('Orderline_Order', 'ChildObject', '1', '0', '')
    );

    // **********************
    // Constructor
    // **********************
    function __construct ($val = null, $property = self::primary_key)
    {
        global $connection;
        if((isset($val))&&(trim($val)!=""))
        {
            $query = $connection->sql_query("SELECT * FROM `order` WHERE `$property` = ".parent::quote($this->structure[$property],$val) ." LIMIT 1");

            if($connection->sql_num_rows($query) != 0)
            {
                while($row = $connection->sql_fetch_object($query))
                {
                    $this->ID_Order = $this->formater($this->structure['ID_Order'],$row->ID_Order);
                    $this->structure['ID_Order'][4] = $this->ID_Order;
                    $this->ID_Customer = $this->formater($this->structure['ID_Customer'],$row->ID_Customer);
                    $this->structure['ID_Customer'][4] = $this->ID_Customer;
                    $this->InvoiceNumber = $this->formater($this->structure['InvoiceNumber'],$row->InvoiceNumber);
                    $this->structure['InvoiceNumber'][4] = $this->InvoiceNumber;
                    
                }
            }
            else
            {
                $this->isNew=1;
                $this->isToSaveOrToUpdate=1;
            }
        }

        if (is_null($this->{self::primary_key}))
        {
            $this->isNew=1;
            $this->isToSaveOrToUpdate=1;
        }
        else
        {
           $this->structure[self::primary_key][4] = $this->{self::primary_key};
        }
    }
    

    // **********************
    // Generic get & set method for this class
    // **********************

    public function __get( $property )
    {
        if ( is_callable( array($this,'get_'.(string)$property) ) )
        {
            return call_user_func( array($this,'get_'.(string)$property) );
        }
        else
        {
            throw new DALException("get for $property doesn't exists");
        }
    }

    public function __set( $property, $val )
    {
        if ( is_callable( array($this,'set_'.(string)$property) ) )
        {
            if ( $val != call_user_func( array($this,'get_'.(string)$property) ) )
            {
                call_user_func( array($this,'set_'.(string)$property), $val );
            }
        }
        else
        {
            throw new DALException( "set for $property doesn't exists");
        }
    }

    public function __isset($property = NULL)
    {
        if ( is_callable( array($this,'get_'.(string)$property) ) ) {
            $return = call_user_func( array($this,'get_'.(string)$property) );

            if(empty($return) || is_null($return))
            {
                return FALSE;
            }
            else
            {
                return TRUE;
            }
        }
        else {
            throw new DALException("get for $property doesn't exists");
        }
    }

    public function __unset($property)
    {
        if ( is_callable( array($this,'set_'.(string)$property) ) )
            return call_user_func( array($this,'set_'.(string)$property), NULL );
        else
            throw new DALException("set for $property doesn't exists");
    }
	
    // **********************
    // Specific get & set method for this class
    // **********************

    public function get_isNew()
    {
            return intval($this->isNew);
    }

    public function get_structure()
    {
            return $this->structure;		
    }
	
    public function get_ID_Order()
    {
        return stripslashes($this->ID_Order);
    }

    public function get_ID_Customer()
    {
        return stripslashes($this->ID_Customer);
    }

    public function set_ID_Customer($valeur = null)
    {
        if ( ( $this->ID_Customer != parent::quote($this->structure['ID_Customer'][1],$valeur) ) && ( $this->ID_Customer != $valeur ) )
        {	
            if(is_null($valeur))
            {
                $this->ID_Customer = NULL;
            }
            else
            {
                $this->ID_Customer = parent::quote($this->structure['ID_Customer'][1],$valeur);
            }
            $this->isToSaveOrToUpdate=1;
            $this->structure['ID_Customer']["3"]=1;
            $this->structure['ID_Customer']["4"] = $this->ID_Customer;

            $test = explode("-",$this->structure['ID_Customer'][1]);
            if ( ( $test["0"] == "varchar" ) && ( isset( $test["1"] ) ) )
            {
                $this->structure['ID_Customer']["4"] = substr($this->structure['ID_Customer']["4"],0,$test["1"]);
            }
            
        }
        return $valeur;
    }

    public function get_InvoiceNumber()
    {
        return stripslashes($this->InvoiceNumber);
    }

    public function set_InvoiceNumber($valeur = null)
    {
        if ( ( $this->InvoiceNumber != parent::quote($this->structure['InvoiceNumber'][1],$valeur) ) && ( $this->InvoiceNumber != $valeur ) )
        {	
            if(is_null($valeur))
            {
                $this->InvoiceNumber = NULL;
            }
            else
            {
                $this->InvoiceNumber = parent::quote($this->structure['InvoiceNumber'][1],$valeur);
            }
            $this->isToSaveOrToUpdate=1;
            $this->structure['InvoiceNumber']["3"]=1;
            $this->structure['InvoiceNumber']["4"] = $this->InvoiceNumber;

            $test = explode("-",$this->structure['InvoiceNumber'][1]);
            if ( ( $test["0"] == "varchar" ) && ( isset( $test["1"] ) ) )
            {
                $this->structure['InvoiceNumber']["4"] = substr($this->structure['InvoiceNumber']["4"],0,$test["1"]);
            }
            
        }
        return $valeur;
    }

    // **********************
    // Specific get & set method for parents objects
    // **********************

    public function get_Parent_Customer($forced = 0)
    {

            $this->Parent_Customer = new customer($this->ID_Customer);

            return $this->Parent_Customer;
    }

    public function set_Parent_Customer($customer)
    {
            $this->ID_Customer=$customer->ID_Customer;
            $this->structure["ID_Customer"][3]=1;
            $this->structure["ID_Customer"][4]=$customer->ID_Customer;
            $this->isToSaveOrToUpdate=1;
            $this->Parent_Customer=$customer;
            return ($customer);
    }

    // **********************
    // Specific get & set method for childs objets
    // **********************
    
    public function get_Orderline_Order()
    {
        global $connection;

        if ((is_null($this->Orderline_Order))&&(!is_null($this->ID_Order)))
        {
            $query="SELECT * FROM `orderline` WHERE ID_Order = ".$this->ID_Order." ORDER BY ID_OrderLine";
            $result=$connection->sql_query($query);
            while($row = $connection->sql_fetch_object($result,'MyORM\orderline'))
            {
                $this->add_Orderline_Order($row);
            }
        }
        return ($this->Orderline_Order);
    }

    public function set_Orderline_Order($orderlines)
    {
        if (!isset($this->Orderline_Order) || (isset($this->Orderline_Order) && count($this->Orderline_Order)==0))
            foreach ($orderlines as $var)
                add_Orderline_Order($var);
        else
            throw new DALException( "Can set Orderline_Order cause actual Orderline_Order is not empty");
        return ($orderlines);
    }

    public function add_Orderline_Order(orderline $orderline)
    {
        if ($orderline->ID_Order!=$this->ID_Order)
            $orderline->set_ID_Order($this->ID_Order);
        if (isset($this->Orderline_Order) && is_array($this->Orderline_Order)) {
            $count=count($this->Orderline_Order);
        }
        else {
            $count=0;
        }
        while (isset($this->Orderline_Order[$count]))
            $count++;
        $this->Orderline_Order[$count]=$orderline;
    }

    public function remove_Orderline_Order(orderline $removeorderline)
    {
        foreach ($this->Orderline_Order as $var)
        {
            if ($removeorderline == $var)
            {
                $var->delete();
                $this->orderline = null;
            }
        }
    }

    protected function save_Orderline_Order($transaction = null)
    {
        foreach ($this->Orderline_Order as $orderline)
        {
            if ($orderline->ID_Order!=$this->ID_Order)
                $orderline->set_ID_Order($this->ID_Order);
            $orderline->save($transaction);
        }
    }

    public function delete_Orderline_Order($transaction = null)
    {
        global $connection;
        if (isset($this->Orderline_Order))
            foreach ($this->Orderline_Order as $orderline)
            {
                $orderline->delete($transaction);
            }

        $query = "DELETE FROM `orderline` WHERE ID_Order = ".$this->ID_Order;
        $result = $connection->sql_query($query);
        if ($connection->sql_error($result))
        {
            $erreur=$connection->sql_error($result)."<br>".$query;
            if ($transaction=="On")
            {
                $connection->sql_rollbacktransaction();
            }
            throw new DALException($erreur);
        }
        else
            return $connection->sql_affected_rows($result);

        $this->Orderline_Order= null;
    }
    
    // **********************
    // DELETE
    // **********************

    public function delete($transaction = null)
    {
        global $connection;
        $thistransaction = "Off";
        $return = null;

        if ((isset($this->ID_Order))&&($this->ID_Order!=0))
        {
            if (is_null($transaction))
            {
                $thistransaction="On";
                $transaction = "On";
                if ($connection->TransactionMode == 1)
                    $connection->sql_starttransaction();
            }
	
            if ( null !== $this->get_Orderline_Order() && (count($this->get_Orderline_Order())!=0) && (MyORMCASCADE) )
                $this->delete_Orderline_Order($transaction);

            if (($transaction=="On"))
            {
                $query = parent::makequery('DELETE', $connection->Database, 'order', $this->structure);
                $Result = $connection->sql_query($query);
                if ($connection->sql_error($Result))
                {
                    $erreur=$connection->sql_error($Result)."<br>".$query;
                    if ($connection->TransactionMode == 1)
                    {
                        $connection->sql_rollbacktransaction();
                    }
                    throw new DALException($erreur);
                }
                else
                    $return = $connection->sql_affected_rows($Result);
            }

            if (($connection->TransactionMode == 1)&&($thistransaction=="On"))
            {
                $connection->sql_committransaction();
            }
            return $return;
        }
        foreach ($this->structure as $field)
            unset($this->$field[0]);	
    }

    // **********************
    // SAVE (INSERT or UPDATE)
    // **********************

    public function save($transaction = null)
    {		
        global $connection;
        $thistransaction = "Off";

        if ((is_null($this->ID_Order))||($this->ID_Order==0))
        {
            $this->isToSaveOrToUpdate=1;
            $this->isNew=1;
        }
        
        if (!is_null($this->Parent_Customer))
                $this->ID_Customer = $this->set_ID_Customer($this->Parent_Customer->save($transaction));	 
        
        if ($this->isToSaveOrToUpdate==1)
        {

            if ((isset($this->ID_Order))&&($this->ID_Order!="0")&&($this->isNew!=1))
            {
                $query = parent::makequery('UPDATE', $connection->Database, 'order', $this->structure);
                $result=$connection->sql_query($query);
            }
            else
            {
                $query = parent::makequery('INSERT', $connection->Database, 'order', $this->structure);
                $connection->sql_query($query);
                $this->ID_Order=$connection->sql_insert_id();
                $this->structure['ID_Order']["4"] = $this->ID_Order;
                $this->isNew=0;
            }
        }
	
        if (!is_null($this->Orderline_Order))
                $this->save_Orderline_Order($transaction);
        
        $this->isToSaveOrToUpdate=0;
        foreach ($this->structure as $field)
            if(isset($field[0]))
            {
                $this->structure[$field[0]][3]=0;
            }

        return $this->ID_Order;
    }

    public function last_insert($property = self::primary_key)
    {
        global $connection;

        $query = $connection->sql_query("SELECT $property AS last FROM `order` ORDER BY ".self::primary_key." DESC LIMIT 1");
        $last = $connection->sql_fetch_row($query);

        return $last["last"];
    }

    public function toString($var = 'first')
    {
        $this->LoadAllParents();
        $this->LoadAllChilds();
        $return = "Object ".__CLASS__." (<br>";
        foreach ($this->structure as $field)
        {
            if ( ($field[1] == 'ChildObject') && (!is_null($this->{$field[0]})) && ( ( $var == 'first' ) || ( $var == 'down' ) ) )
            {
                $return .= '"'.$field[0].'" =>';
                $return .= " Array ( <br>";
                $i=0;
                foreach ($this->{$field[0]} as &$childvar)
                {
                    $return .= $childvar->toString('down');
                    $return .= ",<br>";
                    $i++;
                }
                $return = substr($return, 0, -5);
                $return .= "<br> ) <br>";
            }
            else
            {
                if ( ($field[1] == 'ParentObject') && (!is_null($this->{$field[0]})) && ( $var == 'first' ) )
                {
                    $return .= '"'.$field[0].'" => ';
                    $return .= $this->{$field[0]}->toString('none');
                    $return .= "<br>";
                }
                else
                {					
                    if ($this->{$field[0]}=="")
                    {
                        if ($field[2]==1)
                        {
                            $return .= '"'.$field[0].'" => null<br>';
                        }
                        else
                        {
                            $return .= '"'.$field[0].'" => ""<br>';
                        }
                    }
                    else
                    {
                        if ( ($field[1] != 'ParentObject') && ($field[1] != 'ChildObject') )
                        {
                            $return .= '"'.$field[0].'" => '.$this->{$field[0]}.'<br>';
                        }
                    }
                }
            }
        }
        $return = substr($return,0,-1);
        $return .= ")";
        return $return;
    }

    public function get_toJson($var = 'first')
    {
        $this->LoadAllParents();
        $this->LoadAllChilds();
        $return = "{";
        foreach ($this->structure as $field)
        {
            if ( ($field[1] == 'ChildObject') && (!is_null($this->{$field[0]})) && ( ( $var == 'first' ) || ( $var == 'down' ) ) )
            {
                $return .= '"'.$field[0].'":';
                $return .= "[";
                $i=0;
                foreach ($this->{$field[0]} as &$childvar)
                {
                    $return .= $childvar->get_toJson('down');
                    $return .= ",";
                    $i++;
                }
                $return = substr($return, 0, -1);
                $return .= "],";
            }
            else
            {
                if ( ($field[1] == 'ParentObject') && (!is_null($this->{$field[0]})) && ( $var == 'first' ) )
                {
                    $return .= '"'.$field[0].'":';
                    $return .= $this->{$field[0]}->get_toJson('none');
                    $return .= ",";
                }
                else
                {					
                    if ($this->{$field[0]}=="")
                    {
                        if ($field[2]==1)
                        {
                            $return .= '"'.$field[0].'":null,';
                        }
                        else
                        {
                            $return .= '"'.$field[0].'":"",';
                        }
                    }
                    else
                    {
                        if ( ($field[1] != 'ParentObject') && ($field[1] != 'ChildObject') )
                        {	
                            if (($field[1]!='timestamp')&&($field[1]!='date')&&($field[1]!='datetime')&&($field[1]!='char')&&($field[1]!='varchar')&&($field[1]!='tinyblob')&&($field[1]!='tinytext')&&($field[1]!='blob')&&($field[1]!='text')&&($field[1]!='mediumblob')&&($field[1]!='mediumtext')&&($field[1]!='longblob')&&($field[1]!='longtext')&&($field[1]!='time')&&($field[1]!='enum'))
                            {
                                $return .= '"'.$field[0].'":'.$this->{$field[0]}.',';
                            }
                            else
                            {
                                $return .= '"'.$field[0].'":"'.$this->{$field[0]}.'",';
                            }
                        }
                    }
                }
            }
        }
        $return = substr($return,0,-1);
        $return .= "}";
        return $return;
    }

    public function __clone()
    {
        $this->LoadAllChilds();
        $this->ID_Order = null;
        $this->structure['ID_Order'][4] = "";
        $this->isNew = 1;
        $this->isToSaveOrToUpdate = 1;
        foreach ($this->structure as $field)
        {
            if (($field[1] == 'ChildObject')&&(!is_null($this->{$field[0]})))
            {
                foreach ($this->{$field[0]} as &$childvar)
                    $childvar = clone $childvar;
            }
            elseif ($field[1] != 'ParentObject')
            {
                $this->structure[$field[0]][3] = 1;
                $this->structure[$field[0]][4] = $this->{$field[0]};
            }
        }
    }

    public function LoadAllParents()
    {
        if (!is_null($this->Orderline_Order))
            $this->get_Orderline_Order();

    }
	
    public function LoadAllChilds()
    {
        $this->get_Orderline_Order();
    }
		
    //endofclass
}
	