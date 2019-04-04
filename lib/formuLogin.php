<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <title>Authentifiez-vous</title>
    <style>
        input:invalid {
            background-color: lightred;
        }

        input:valid {
            background-color: lightblue;
        }
    </style>
</head>

<body>
    <?php
    if (isset($_SESSION['echec']))
        echo "<p>Les login et mot de passe précédemment fournis étaient incorrects</p>";
    ?>
    <h2>Authentifiez-vous</h2>

    <form method="POST" action="">
        <fieldset>
            <label for="login">Login :</label>
            <input type="text" name="login" id="login" required="required" autofocus="autofocus" />
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required="required" />
            <button type="submit" name="valid">OK</button>
        </fieldset>
        <a href="lib/formuCreate.php"> Créer un compte </a>
    </form>
</body>

</html>