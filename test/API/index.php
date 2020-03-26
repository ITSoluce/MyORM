<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/../../MyConfig/autoload.php'; // Autoload files using MyConfig autoload

//loading la gestion des exceptions
new MyException\MyException();
//loading de l'ORM
new MyORM\MyORM();

$connection = new MySQL\sql(MySQLServer,MySQLUser,MySQLPassword,MySQLDatabase,MySQLPort);

global $call;
$call = file_get_contents('php://input');
 
$bom = pack('H*','EFBBBF');
$call = preg_replace("/^$bom/", '', $call);
 
$call = json_decode($call);

if ( (!isset($_SERVER["HTTP_APIAUTHENTIFICATION"]))&&(!isset($_GET["Token"])) ) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}
else {
    if (isset($_GET["Token"])) {
            $Token = $_GET["Token"];
    }
    else {
            $Token = $_SERVER["HTTP_APIAUTHENTIFICATION"];
    }
    unset($_GET["Token"]);

    date_default_timezone_set('Europe/Paris');
    
    if (!(str_replace("0","",dechex(hexdec(PrivateKey)+hexdec($Token)+1))=="1"))
    {
        //Invalid Token Authentification Key
        header("HTTP/1.1 401 Token Key is unvalid");
        exit();
    }
    
    if (!isset($_GET["Object"]))
    {
        die('Where is the Object route ?');
    }
    
    $INPUT_TYPE = $_SERVER['REQUEST_METHOD'];
    $ClassName =  "MyORM\\".$_GET["Object"];
    switch ($INPUT_TYPE) {
        case 'GET':
            if (($_GET["Object"] == "DirectQueryToDataBase")&&(isset($call->sql))) {
                header('Content-Type: application/json; charset=utf-8');
                echo MyORM\Common::get_toJson(MyORM\Common::query($connection,$call->sql));
                die();
            }
            if (isset($_GET["Id"]))
            {
                if ($_GET["Id"] == "Ineedtheclassplease")
                {
                    $reflector = new \ReflectionClass($ClassName);
                    $filename = $reflector->getFileName();
                    $handle = fopen($filename, "r");
                    $contents = fread($handle, filesize($filename));
                    fclose($handle);
                    echo $contents;
                }
                else
                {
                    if (isset($_GET["Variable"]) && $_GET["Variable"] == "ForcedObjectFromID")
                    {
                        $_GET["Property"] = $_GET["Variable"];
                        unset($_GET["Variable"]);
                    }
                    
                    if (isset($_GET["Property"]))
                    {
                        $Return = new $ClassName($_GET["Id"],$_GET["Property"]);
                    }
                    else {
                        $Return = new $ClassName($_GET["Id"]);
                    }
                    
                    if (isset($_GET["Variable"]))
                    {
                        $Return = $Return->{$_GET["Variable"]};
                    }
                    
                    if (isset($_GET["Properties"])) {
                        MyORM\Common::loadFromProperties($Return, MyORM\Common::makePropertiesArray($_GET["Properties"]));
                    }
                    
                    if (is_object($Return)) {
                        header('Content-Type: application/json; charset=utf-8');
                        if ( ($Return->IsNew != 1) || ($_GET["Property"] == "ForcedObjectFromID") )
                            echo $Return->toJson("this");
                    }
                    else {
                        if (is_array($Return)) {
                            header('Content-Type: application/json; charset=utf-8');
                            echo MyORM\Common::get_toJson($Return);
                        }
                        else {
                            echo $Return;
                        }
                    }
                }
            }
            break;
        case 'POST': //INSERT
            $obj = new $ClassName($call,"reloadObjectFromJsonDecodeObject");
            if ( (isset($_GET["Print"]))&&(($_GET["Print"]??'')=='Json') ) {
                header('Content-Type: application/json; charset=utf-8');
                MyORM\Common::loadFromProperties($obj, MyORM\Common::makePropertiesArray($_GET["Properties"]));
                echo $obj->toJson("this");
            }
            else {
                echo $obj->save();
            }
            break;
        case 'DELETE':
            $obj = new $ClassName($_GET["Id"]);
            $obj->delete();
            break;
        case 'PUT': //UPDATE
            $obj = new $ClassName($call,"reloadObjectFromJsonDecodeObject");
            if ( (isset($_GET["Print"]))&&(($_GET["Print"]??'')=='Json') ) {
                header('Content-Type: application/json; charset=utf-8');
                MyORM\Common::loadFromProperties($obj, MyORM\Common::makePropertiesArray($_GET["Properties"]));
                echo $obj->toJson("this");
            }
            else {
                echo $obj->save();
            }
            break;
        default :
            die('GET/POST/DELETE/PUT are only supported');
            break;
    }
}