<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$array = get_defined_vars();

foreach (glob(__DIR__ . "/*.Config.php") as $filename) {
    require_once($filename);
}

$array2 = get_defined_vars();

foreach($array2 AS $variable => $value) {
    if ( (!isset($array[$variable])) && ($variable != 'array2')  && ($variable != 'array')  && ($variable != 'filename') ) {
	define($variable,$value);
    }
}