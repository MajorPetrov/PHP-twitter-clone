<?php
require_once('../lib/auth.php');
require_once("../lib/biblio.php");

$user = $_REQUEST["user"];
$size = $REQUEST["size"];
$result = $bd->getAvatar($user, $size);

if (isset($result))
    $status = "ok";
else {
    $status = "error";
    $result = null;
    $reponse = array("status" => $status, "args" => $_REQUEST, "result" => $result);

    header("Content-type: application/json;charset=UTF-8");
    echo json_encode($reponse);

    return;
}

echo $result;

return;
