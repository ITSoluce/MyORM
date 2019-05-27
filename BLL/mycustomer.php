<?php

namespace MyORM;

class mycustomer extends customer
{
	protected $MyCustomerData;
	
	//constructeur
	function mycustomer($id = null)
	{  
		parent :: customer($id);
		
		if (is_null($id))
		{
			$this->isNew = 1;
		}
	}
        //....
}
?>