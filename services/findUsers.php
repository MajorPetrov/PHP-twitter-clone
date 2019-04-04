<?php
require_once('../lib/auth.php');
require_once("../lib/biblio.php");

$searched = $_REQUEST["searched"];
$scope = $_REQUEST["scope"];
$type = $_REQUEST["type"];
$result = $bd->findUsers($searched, $scope, $type);

if (isset($result))
    $status = "ok";
else {
    $status = "error";
    $result = null;
}

$reponse = array("status" => $status, "args" => $_REQUEST, "result" => $result);

header("Content-type: application/json;charset=UTF-8");
echo json_encode($reponse);

return;
