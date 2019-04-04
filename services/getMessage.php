<?php
require_once('../lib/auth.php');
require_once("../lib/biblio.php");

$id = $_REQUEST["id"];
$result = $bd->getMessage($id);

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
