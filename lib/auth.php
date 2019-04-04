<?php
require_once('biblio.php');

session_start();

try {
    controleAuthentification();
} catch (Exception $e) {
    require('formuLogin.php');
    exit();
}
