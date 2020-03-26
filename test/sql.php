<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/../MyConfig/autoload.php'; // Autoload files using MyConfig autoload

//loading la gestion des exceptions
new MyException\MyException();
//loading de l'ORM
new MyORM\MyORM();

$connection = new MySQL\sql(MySQLServer,MySQLUser,MySQLPassword,MySQLDatabase,MySQLPort);

echo "<pre>";
print_r(MyORM\Common::query($connection,"SELECT * FROM `order`"));
echo "</pre>";

echo "<pre>";
print_r(MyORM\Common::queryToObject($connection,"SELECT * FROM `order`",'order'));
echo "</pre>";


$object = new MyORM\order(1,'ForcedObjectFromID');
echo "<pre>";
print_r($object);