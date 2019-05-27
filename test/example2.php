<?php
echo "<pre>require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/../MyConfig/autoload.php'; // Autoload files using MyConfig autoload

//loading la gestion des exceptions
new MyException\\MyException();
//loading de l'ORM
new MyORM\MyORM();

$"."connection = new MySQL\sql(MySQLServer,MySQLUser,MySQLPassword,MySQLDatabase,MySQLPort);";

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/../MyConfig/autoload.php'; // Autoload files using MyConfig autoload

//loading la gestion des exceptions
new MyException\MyException();
//loading de l'ORM
new MyORM\MyORM();

$connection = new MySQL\sql(MySQLServer,MySQLUser,MySQLPassword,MySQLDatabase,MySQLPort);

echo "
//Init some datas into database for the demo
$"."reference = new MyORM\\reference();
$"."reference->Price=4.99;
$"."reference->Code=\"Ref001\";
$"."reference->Name=\"First reference\";
$"."reference->save();

";

$reference = new MyORM\reference();
$reference->Price=4.99;
$reference->Code="Ref001";
$reference->Name="First reference";
$reference->save();

echo "
$"."reference2 = clone $"."reference;
$"."reference2->Price=9.99;
$"."reference2->Code=\"Ref002\";
echo $"."reference2->save(); // will echo the inserted ID
";

$reference2 = clone $reference;
$reference2->Price=9.99;
$reference2->Code="Ref002";
echo $reference2->save(); // will echo the inserted ID

echo "

$"."customer = new MyORM\\mycustomer(); // specific custom class herit from DAL class
$"."customer->FirstName=\"PLATEL\";
$"."customer->LastName=\"Renaud\";
// not saved cause will be save with order
";

$customer = new MyORM\customer();
$customer->FirstName="PLATEL";
$customer->LastName="Renaud";

echo "
/* Hold a ID for simulate a customer who order Ref001 ans Ref002 */
$"."ID_Ref2 = $"."reference2->ID_Reference; //we have an ID cause it has been saved";

/* Hold a ID for simulate a customer who order Ref001 ans Ref002 */
$ID_Ref2 = $reference2->ID_Reference; //we have an ID cause it has been saved

echo "Order creation with a Customer start
$"."order = new MyORM\\order();
$"."order->Parent_Customer = $"."customer; // set by object
$"."order->InvoiceNumber=\"Example 1\";

";

$order = new MyORM\order();
$order->Parent_Customer = $customer; // set by object
$order->InvoiceNumber="Example 1";

echo "$"."orderline = new MyORM\\orderline();
$"."orderline->ID_Reference = $"."reference->ID_Reference; // set by ID
$"."orderline->Quantity = 1;

";

$orderline = new MyORM\orderline();
$orderline->ID_Reference = $reference->ID_Reference; // set by ID
$orderline->Quantity = 1;

echo "$"."orderline2 = new MyORM\\orderline();
$"."orderline2->Parent_Reference = new MyORM\\reference($"."ID_Ref2); // set by load an object (slow)

";

$orderline2 = new MyORM\orderline();
$orderline2->Parent_Reference = new MyORM\reference($ID_Ref2); // set by load an object (slow)
$orderline2->Quantity = 2;

echo "
$"."orderline2->Quantity = 2;

$"."orderline3 = new MyORM\\orderline();
$"."orderline3->Parent_Reference = new MyORM\\reference(); //set by object creation
$"."orderline3->Parent_Reference->Price=14.99;
$"."orderline3->Parent_Reference->Code=\"Ref003\";
$"."orderline3->Quantity = 1;

";

$orderline3 = new MyORM\orderline();
$orderline3->Parent_Reference = new MyORM\reference(); //set by object creation
$orderline3->Parent_Reference->Price=14.99;
$orderline3->Parent_Reference->Code="Ref003";
$orderline3->Quantity = 1;

echo "We link/add all the orderlines to the order :
$"."order->add_OrderLine_Order($"."orderline);
$"."order->add_OrderLine_Order($"."orderline2);
$"."order->add_OrderLine_Order($"."orderline3);

Just enjoy the order save to database :
$"."order->save();

";

$order->add_OrderLine_Order($orderline);
$order->add_OrderLine_Order($orderline2);
$order->add_OrderLine_Order($orderline3);

$order->save();

echo "
Lets do something stupid ... but it is an example :
$"."customer->delete(); //U can enjoy this / code cascade

";

$customer->delete();

echo "
$"."connection->sql_close();</pre>";
$connection->sql_close();