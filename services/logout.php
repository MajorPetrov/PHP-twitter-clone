<?php
require_once('../lib/auth.php');
require_once("../lib/biblio.php");

// script accessible uniquement pour un utilisateur authentifié
$personne = $_SESSION['ident'];
$login = $personne->getLogin();

unset($_SESSION['ident']);
session_destroy();

$reponse = array("status" => "ok", "args" => $login, "result" => $login);

header("Content-type: application/json;charset=UTF-8");
echo json_encode($reponse);

return;
