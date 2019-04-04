<?php
require_once('../lib/auth.php');
require_once("../lib/biblio.php");

// script accessible uniquement pour un utilisateur authentifié
$source = $_POST["source"];
$result = $bd->postMessage($_SESSION['ident']->getLogin(), $_SESSION["ident"]->getNom(), $source);

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
