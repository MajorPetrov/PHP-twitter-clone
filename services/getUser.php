<?php
require_once('../lib/auth.php');
require_once("../lib/biblio.php");

$personne = $_SESSION['ident'];
$user = $_REQUEST["user"];
$type = $REQUEST["type"];
$result = $bd->getUser($user, $type);

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

/* modifier les méthodes pour récupérer:
* le nom de l'utilisateur en cas de short
* le nom, et la présentation en cas de long
* mettre ça dans un tableau: (ident,name)
* ou dans un tableau (ident,name,description)
* faire un json_encode(variable)
* retourner un JSON avec: (status,arg,result (qui est le json_encode détaillé dans la ligne précédente))
*/
