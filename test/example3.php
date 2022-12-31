<?php
echo "<pre>require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/../MyConfig/autoload.php'; // Autoload files using MyConfig autoload

//loading la gestion des exceptions
new MyException\\MyException();
//loading de l'ORM
new MyORM\\MyORM();

$"."connection = new MySQL\sql(MySQLServer,MySQLUser,MySQLPassword,MySQLDatabase,MySQLPort);";

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/../MyConfig/autoload.php'; // Autoload files using MyConfig autoload

//loading la gestion des exceptions
new MyException\MyException();
//loading de l'ORM
new MyORM\MyORM();

$connection = new MySQL\sql(MySQLServer,MySQLUser,MySQLPassword,MySQLDatabase,MySQLPort);

class myorder extends MyORM\order
{
	function __construct($id = null)
	{
		parent::__construct($id);
	}
}
	
echo "<pre>
    
class myorder extends MyORM\\order
{
	function __construct($"."id = null)
	{
		parent::__construct($"."id);
	}
}
";


/* Init some datas into database for the demo */
$debug= 0;
echo "
Init some datas into database for the demo
";
$reference = new MyORM\reference();
$reference->Price=4.99;
$reference->Code="Ref001";
$reference->Name="First reference";
$reference->save();

$reference2 = new MyORM\reference();
$reference2->Price=9.99;
$reference2->Code="Ref002";
$reference2->save();

$customer = new MyORM\customer();
$customer->FirstName="PLATEL";
$customer->LastName="Renaud";
$customer->save();

/* Hold some ID to simulate a customer who order Ref001 ans Ref002 */
$ID_Ref = $reference->ID_Reference;
$ID_Ref2 = $reference2->ID_Reference;
$ID_Customer= $customer->ID_Customer;
unset($reference);
unset($reference2);
unset($customer);

/* Order creation with a Customer start */
$order = new MyORM\order();
$order->Parent_Customer = new MyORM\customer($ID_Customer); // set by object
$order->InvoiceNumber="Example 1";

$orderline = new MyORM\orderline();
$orderline->ID_Reference = $ID_Ref; // set by ID (faster)
$orderline->Quantity = 1;

$orderline2 = new MyORM\orderline();
$orderline2->Parent_Reference = new MyORM\reference($ID_Ref2);
$orderline2->Quantity = 2;

$orderline3 = new MyORM\orderline();
$orderline3->Parent_Reference = new MyORM\reference();
$orderline3->Parent_Reference->Price=14.99;
$orderline3->Parent_Reference->Code="Ref003";
$orderline3->Quantity = 1;

$order->add_OrderLine_Order($orderline);
$order->add_OrderLine_Order($orderline2);

$ID_Order = $order->save(); //save order and 2 orderlines with transaction if selected.

unset($order);
$debug = 1;

echo "

An order loading (only necessary datas are loaded)
$"."orderreload = new myorder($"."ID_Order);";
$orderreload = new myorder($ID_Order);

echo "
An order clone
$"."orderclone = clone $"."orderreload;
";

$orderclone = clone $orderreload;
echo "
[table][caption]After clone comparaison[/caption][tr][td]$"."orderclone->toString()[/td][td]$"."orderreload->toString()[/td][/tr][/table]

";
echo "<table><caption>After clone comparaison</caption><tr><td>".$orderclone->toString()."</td><td>".$orderreload->toString()."</td></tr></table>";

echo "

$"."orderclone->save();

";
$orderclone->save();

echo "change one orderline quantity
$"."orderline4 = $"."orderclone->OrderLine_Order[1];
$"."orderline4->Quantity = 4; // set the quantity of the second orderline

";

$orderline4 = $orderclone->OrderLine_Order[1];
$orderline4->Quantity = 4; // set the quantity of the second orderline

echo "$"."orderclone->OrderLine_Order[1]->Quantity = 5; //notice bug PHP ?
";

$orderclone->OrderLine_Order[1]->Quantity = 5; //notice bug PHP ?

echo "
echo $"."orderclone->OrderLine_Order[1]->Quantity; //5 in so there is a stupid notice cause it work
";

echo $orderclone->OrderLine_Order[1]->Quantity;

echo "
    
$"."orderclone->get_Parent_Customer()->LastName=\"Thomas\"; //u can use get and set if u want

";
		
$orderclone->get_Parent_Customer()->LastName="Thomas";

echo "
$"."orderclone->remove_OrderLine_Order($"."orderreload->OrderLine_Order[0]); // delete first row
";

$orderclone->remove_OrderLine_Order($orderreload->OrderLine_Order[0]); // delete first row (delete in SQL if exists) 

echo "
$"."orderclone->add_Orderline_Order($"."orderline3);
$"."orderclone->Save(); //save only modified datas

";

$orderclone->add_Orderline_Order($orderline3);
$orderclone->Save(); //save only modified datas

echo "
$"."orderclone->Save(); //save only modified datas</p>
";

$orderclone->Save(); //save only modified datas

echo "
About the notice bug ... =>
";

echo "
$"."orderclone->OrderLine_Order[2]->Quantity = 5; //mask notice bug PHP
";
$orderclone->OrderLine_Order[2]->Quantity = 5; // notice bug PHP
$orderclone->OrderLine_Order[2]->set_Quantity(5); //bypass notice bug
echo "
$"."orderclone->OrderLine_Order[2]->set_Quantity(5); //bypass notice bug using set
@$"."orderclone->Orderline_Order[2]->Parent_Reference=new ORM\\reference($"."ID_Ref2); //mask notice bug PHP
";
$orderclone->Orderline_Order[2]->Parent_Reference=new MyORM\reference($ID_Ref2); // notice bug PHP
$orderclone->OrderLine_Order[2]->set_Parent_Reference(new MyORM\reference($ID_Ref2)); //bypass notice bug using set

$orderclone->Save(); //save only modified datas

echo "$"."orderclone->OrderLine_Order[2]->set_Parent_Reference(new ORM\\reference($"."ID_Ref2)); //bypass notice bug using set

$"."orderclone->Save(); //save only modified datas

";

echo "</pre>";
echo "<p><font color=red>Powerfull ?</font></p>";

$connection->sql_close();