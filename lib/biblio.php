<?php
require_once("connect_db.php");
date_default_timezone_set("Europe/Paris");

$bd = new BD($connexion);

function __autoload($className)
{
    include $className . '.class.php';
}


/*
  Si login et password sont corrects, alors
  le résultat est une instance d'Identite décrivant cet utilisateur
  Sinon le résultat vaut null
*/
function authentifie($login, $password)
{
    global $bd;
    $info = $bd->getUser($login);

    if (!$info)
        return null;

    if (crypt($password, $info['password']) != $info['password'])
        return null;

    return new Identite($login, $info['nom'], $info['prenom']);
}

/*
 Verifie l'authentification
 La fonction se termine normalement
 - Si l'état de la session indique que l'authentification a déjà eu lieu
 - Si des paramètres login/password corrects ont été fournis
 Après exécution correcte,  $_SESSION['ident'] contient l'identité de l'utilisateur
 Dans tous les autres cas, une exception est déclenchée
*/
function controleAuthentification()
{
    if (isset($_SESSION['ident']))
        return;

    $login = inputFilterString('login');
    $password = inputFilterString('password');
    // if (! isset($_REQUEST['login'])  || ! isset($_REQUEST['password']))
    //   throw new Exception('aucune authentification possible');

    $ident = authentifie($login, $password);

    if (!$ident) {
        $_SESSION['echec'] = true;
        throw new Exception('login/password incorrects');
    }

    $_SESSION['ident'] = $ident;   // Notons que l'on range un objet, instance de Identite

    unset($_SESSION['echec']); // au cas où c'était positionné
}

function inputFilterString($name, $requis = true)
{
    $v = filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING);

    if ($requis && $v == null)
        throw new Exception("argument $name est requis");

    return $v;
}
