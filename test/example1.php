<?php

echo "<pre>require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/../MyConfig/autoload.php'; // Autoload files using MyConfig autoload

//loading de l'ORM
new MyORM\MyORM();

$"."connection = new MySQL\sql(MySQLServer,MySQLUser,MySQLPassword,MySQLDatabase,MySQLPort);";

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/../MyConfig/autoload.php'; // Autoload files using MyConfig autoload

//loading de l'ORM
new MyORM\MyORM();

$connection = new MySQL\sql(MySQLServer,MySQLUser,MySQLPassword,MySQLDatabase,MySQLPort);

echo "Init some datas into database for the demo
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

echo "$"."reference2 = clone $"."reference;
$"."reference2->Price=9.99;
$"."reference2->Code=\"Ref002\";
echo $"."reference2->save(); // will echo the inserted ID</p>

";

$reference2 = clone $reference;
$reference2->Price=9.99;
$reference2->Code="Ref002";
echo $reference2->save(); // will echo the inserted ID

echo "

$"."customer = new MyORM\\customer();
$"."customer->FirstName=\"PLATEL\";
$"."customer->LastName=\"Renaud\";
$"."customer->save();

";

$customer = new MyORM\customer();
$customer->FirstName="PLATEL";
$customer->LastName="Renaud";
$customer->save();

echo "

$"."customer2 = new MyORM\\customer($"."customer->ID); //reload customer
$"."customer2->LastName=\"Thibault\";
echo $"."customer2->save(); // will echo the ID

";

$customer2 = new MyORM\customer($customer->ID_Customer); //reload customer
$customer2->LastName="Thibault";
echo $customer2->save(); // will echo the ID

$connection->sql_close();
echo "

$"."connection->sql_close();</pre>";