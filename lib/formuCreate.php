<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>
    <meta charset="UTF-8" />
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
    <h2>Créer un compte</h2>

    <form method="POST" action="">
        <fieldset>
            <label for="login">Login :</label>
            <input type="text" name="login" id="login" required="required" autofocus="autofocus" />
            <label for="login">Nom :</label>
            <input type="text" name="name" id="login" required="required" autofocus="autofocus" />
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required="required" />
            <button type="submit" name="valid">OK</button>
        </fieldset>
    </form>
</body>

</html>