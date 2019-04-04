<?php
require_once('../lib/auth.php');
require_once("../lib/biblio.php");

// script accessible uniquement pour un utilisateur authentifié
$login = $_POST["login"];
$password = $_POST["password"];
$name = $_POST["name"];
$result = $bd->createUser($login, $password, $name);

if (isset($result))
    $status = "ok";
else {
    $status = "error";
    $result = null;
}

$reponse = array("status" => $status, "args" => array("ident" => $_REQUEST["ident"], "name" => $_REQUEST["name"]), "result" => $result);

header("Content-type: application/json;charset=UTF-8");
echo json_encode($reponse);

return;
