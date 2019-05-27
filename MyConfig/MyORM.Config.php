<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// Business Logic Layer Directory
$MyORMBLL = "../BLL/";
// Data access layer Directory, automatic oriented object relational mapping cache (u must delete file if u make some database change)
$MyORMDAL = "../DAL/";

//SQL connect for select
$MyORMSQL = "connection";
//SQL connect for update delete insert (Can manage Master / Slave)
$MyORMSQL2 = "connection";

/* ORM configuration */
$MyORMAUTOGENERATE = true;
$MyORMALWAYSAUTOGENERATE = false;
// if will able the Generator to make the missing class in DAL/class if on true
// so you can use this option, and delete DAL/ repertory after a database structure modification.

$MyORMFileVersion = 1;
// You can upgrade version to have new classtoo.

$MyORMMYAUTOLOAD = true;
// if will load your specific class if exists in BLL/myclass

$MyORMRELATION = 'FK'; //RelationTableName
// For FK remplacement, u can use a table named in $MyORMRELATION
// FK or RelationTableName or EMPTY ''
// define DAL will work for relation between object
// if FK based on database foreign key.
// if RELATION based on a RelationTableName table (if exists).
/*CREATE TABLE IF NOT EXISTS `relation` (
  `TABLE_SCHEMA` varchar(50) NOT NULL,
  `TABLE_NAME` varchar(50) NOT NULL,
  `COLUMN_NAME` varchar(50) NOT NULL,
  `REFERENCED_TABLE_SCHEMA` varchar(50) NOT NULL,
  `REFERENCED_TABLE_NAME` varchar(50) NOT NULL DEFAULT '',
  `REFERENCED_COLUMN_NAME` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;*/
// if '' no relation in DAL.

$MyORMCASCADE = true;
//code cascade, children deleted before parents

$intotheset = "
";
$MyORMINTOTHESET = $intotheset;
//Define specific code when set is use (u can update a date field, so ur database must be normalise)
//Don't forget to test field existance if some table doesn't have ur field.