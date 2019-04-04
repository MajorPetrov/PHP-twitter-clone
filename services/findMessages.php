<?php
require_once('../lib/auth.php');
require_once("../lib/biblio.php");

$author = $_REQUEST["author"];
$follower = $_REQUEST["follower"];
$mentioned = $_REQUEST["mentioned"];
$before = $_REQUEST["before"];
$after = $_REQUEST["after"];
$count = $_REQUEST["count"];
$result = $bd->findMessages($author, $follower, $mentioned, $before, $after, $count);

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
