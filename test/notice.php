<?php
error_reporting(E_ALL | E_STRICT); 

class test
{
    function __construct()
    {
    }
}

class order extends test
{
	public $OrderLine;
	private $Invoice;
	
	function __construct()
	{
	}
	public function __get( $property )
	{
            return $this->{$property};
	}
	public function __set( $property, $value )
	{
            echo get_class($this)." ".$property." ".$value."<br>";
            $this->{$property}=$value;
	}
        public function add_OrderLine( $row )
        {
            $this->OrderLine[] = $row;
        }
}

class orderline extends test
{
	private $Quantity;
	private $Ref;
	
	function __construct()
	{
	}
	public function __get( $property )
	{
            return $this->{$property};
	}
	public function __set( $property, $value )
	{
            echo get_class($this)." ".$property." ".$value."<br>";
            $this->{$property}=$value;
	}
}

$order = new order();

$orderline = new orderline();
$orderline->Quantity=2;
$orderline->Ref="1";

$order->Invoice = "1";
$order->add_OrderLine($orderline);
$order->OrderLine[1]=clone $orderline;

$order->OrderLine[0]->Quantity = 1;

echo "<pre>";
print_r($order);
echo "</pre>";

/*
echo "<p>error_reporting(E_ALL | E_STRICT);</p> 

<p>class order<br>
{<br>
	private $"."OrderLine;<br>
	private $"."Invoice;<br>
	<br>
	function order()<br>
	{<br>
	}<br>
	public function __get( $"."property )<br>
	{<br>
		return $"."this->{"."$"."property};<br>
	}<br>
	public function __set( $"."property, $"."value )<br>
	{<br>
	  	$"."this->{"."$"."property}=$"."value;<br>
	}<br>
}<br>
<br>
class orderline<br>
{<br>
	private $"."Quantity;<br>
	private $"."Ref;<br>
	
	function orderline()<br>
	{<br>
	}<br>
	public function __get( $"."property )<br>
	{<br>
		return $"."this->{"."$"."property};<br>
	}<br>
	public function __set( $"."property, $"."value )<br>
	{<br>
	  	$"."this->{"."$"."property}=$"."value;<br>
	}<br>
}<br>
<br>
$"."order = new order();<br>
<br>
$"."orderline = new orderline();<br>
$"."orderline->Quantity=2;<br>
$"."orderline->Ref=\"1\";<br>
<br>
$"."order->Invoice = \"1\";<br>
$"."order->OrderLine[0]=$"."orderline;<br>
$"."order->OrderLine[1]=$"."orderline;<br>
<br>
$"."order->OrderLine[0]->Quantity = 1;<br>
<br>
$"."order->OrderLine[0]->Quantity;</p>
";
*/