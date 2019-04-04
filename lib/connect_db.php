<?php
try {
    $connexion = new PDO("pgsql:host=localhost;dbname=", "", "ifbg53000292c4");
} catch (PDOException $e) {
    echo ("Erreur connexion");
    exit();
}
