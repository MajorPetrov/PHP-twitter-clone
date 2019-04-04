<?php
require_once('services/login.php');
$ident = $_SESSION['ident'];
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <title>Accueil</title>
</head>

<body>
    <a href="services/logout.php">Se déconnecter</a>

    <p id="filMessages">
        <?php
        if (isset($_SESSION['ident'])) {
            $abonnements = json_decode(require_once("services/findMessages.php"));
            foreach ($abonnements as $gugus) {
                echo json_decode(require_once("services/findMessages.php&author=$gugus"));
            }
        } else
            echo json_decode(require_once("services/findMessages.php"));
        ?>
    </p>
    <p id="rechercherMembre">
        <form action="findUsers.php" method="GET" id="update">
            <button name="valid" value="ok" type="submit">rechercher</button>
        </form>
    </p>
</body>

</html>