<?php
require_once('../lib/auth.php');
require_once("../lib/biblio.php");

// script accessible uniquement pour un utilisateur authentifié
$personne = $_SESSION['ident'];
$nom = inputFilterString('nom', false);
$password = inputFilterString('password', false);
$description = $_POST["description"];
$result = $bd->setProfile($personne->getLogin(), $personne->getNom(), $password, $description);

if (isset($result)) {
    $status = "ok";
    $_SESSION['ident'] = $bd->getIdentite($personne->getLogin()); // mise à jour des infos pour la session en cours
} else {
    $status = "error";
    $result = null;
}

$reponse = array("status" => $status, "args" => array("name" => $nom, "description" => $description), "result" => $result);

header("Content-type: application/json;charset=UTF-8");
echo json_encode($reponse);

return;
