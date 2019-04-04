<?php
//require_once('../lib/auth.php');
require_once("lib/biblio.php");

// script accessible uniquement pour un utilisateur authentifié
session_start();

try {
    controleAuthentification();
} catch (Exception $e) {
    require('lib/formuLogin.php');
    exit();
}
