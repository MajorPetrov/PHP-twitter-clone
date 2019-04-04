<?php
require('hashUtil.php'); // on peut s'en passer si on utilise la fonction standard password_hash()

/*
 * Classe consacrée à l'interfaçage avec la base de donnée.
 * À instancier avec une connexion PDO valide
 *
 **/
class BD
{
    private $connexion;

    function __construct($connexion)
    {
        $this->connexion = $connexion;
        $this->connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // force déclenchement exception en cas d'erreur
        $this->connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /*
   * Info sur une personne, sous forme de tableau associatif
   */
    function getUser($login, $type = "short")
    {
        if ($type = "long") {
            $stmt = $this->connexion->prepare("select * from membres where pseudo=:login");
            $stmt->bindValue(":login", $login);
        } else {
            $stmt = $this->connexion->prepare("select pseudo, nom from membres where pseudo=:login");
            $stmt->bindValue(":login", $login);
        }

        try {
            $stmt->execute();
            $res = $stmt->fetch();

            return $res;
        } catch (PDOException $e) {
            // print_r($stmt->errorInfo());   // debug

            return null;
        }
    }

    function getMessage($id)
    {
        $stmt = $this->$connexion->prepare("select * from message where id=:id");
        $stmt->bindValue(":id", $id);

        try {
            $stmt->execute();
            $res = $stmt->fetch();

            return $res;
        } catch (PDOException $e) {
            // print_r($stmt->errorInfo());   // debug

            return null;
        }
    }

    /*
   * Info sur une personne, sous forme d'objet Identité
   */
    function getIdentite($login)
    {
        $info = $this->getUser($login);

        return new Identite($login, $info['nom']);
    }
    /*
   * Liste des intérêts, sous forme de tableau
   */
    function getInterestsTab($login)
    {
        $stmt = $this->connexion->prepare("select sujet from s10.interets where pseudo=:login");
        $stmt->bindValue(":login", $login);
        $stmt->execute();

        try {
            $tab = array();

            while ($res = $stmt->fetch())
                $tab[] = $res['sujet'];

            return $tab;
        } catch (PDOException $e) {
            // print_r($stmt->errorInfo());   // debug

            return null;
        }
    }

    /*
   * Liste des intérêts, sous forme de chaîne de caractères (séparateur : virgule)
   */
    function getInterests($login)
    {
        $tab = $this->getInterestsTab($login);

        return implode(",", $tab);
    }

    /*
   * Mise à jour des intérêts
   * $list est une chaîne (séparateur : virgule)
   */
    function updateInterest($login, $list)
    {
        $tab = explode(',', $list);
        $stmt = $this->connexion->prepare("delete from s10.interets where login = :login");

        $stmt->bindValue(":login", $login);
        $stmt->execute();  // effacement de toutes les valeurs précédentes

        $stmt = $this->connexion->prepare("insert into s10.interets (sujet,login) values (:sujet,:login)");
        $stmt->bindValue(":login", $login);

        foreach ($tab as $sujet) {
            $sujet = trim($sujet);

            if ($sujet !== "") {
                $stmt->bindValue(":sujet", trim($sujet));

                try {
                    $stmt->execute();
                } catch (PDOException $e) {
                    // print_r( $stmt->errorInfo());   // debug

                    return null;
                }
            }
        }

        return true;
    }

    /*
   * Renvoie une liste (tableau) d'utilisateurs
   */
    function searchByInterest($sujet)
    {
        $stmt = $this->connexion->prepare("select login, nom, prenom
         from s10.interets natural join s10.users where sujet = :sujet");
        $stmt->bindValue(":sujet", $sujet);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        try {
            if (!$stmt->execute())
                print_r($stmt->errorInfo());

            $res = array();

            while ($ligne = $stmt->fetch())
                $res[] = $ligne;

            return $res;
        } catch (PDOException $e) {
            // print_r( $stmt->errorInfo());   // debug

            return null;
        }
    }

    /*
   * Ajout d'utilisateur
   */
    function createUser($ident, $password, $name)
    {
        $stmt = $this->connexion->prepare(
            "insert into membres (pseudo,nom,password) values (:pseudo, :nom, :password)"
        );

        $stmt->bindValue(":pseudo", $ident);
        $stmt->bindValue(":nom", $name);
        $stmt->bindValue(":password", crypt($password, randomSalt()));
        $stmt->execute();

        $stmt = $this->connexion->prepare("select pseudo,nom from message where pseudo=$ident");
        $stmt->execute();

        $res = $stmt->fetch();

        return $res;
    }

    /*
   * Mise à jour d'un profil :  password et/ou nom et/ou prenom  (ignorés si NULL ou "")
   */
    function  setProfile($login, $password, $nom, $description)
    {
        if ($password)
            $password = crypt($password, randomSalt()); // on peut aussi utiliser la fonction standard PHP: $password = password_hash($password, PASSWORD_BCRYPT);           

        $infos = ['nom' => $nom, 'presentation' => $description, 'password' =>  $password]; // informations susceptibles d'être mises à jour

        foreach ($infos as $key => $val) {  // on élimine les informations non fournies
            if ($val === null || $val === "")
                unset($infos[$key]);
        }

        if (count($infos) == 0) // aucun update à faire
            return null;

        $atts = array_keys($infos);
        $attsString  = implode(',', $atts);  // liste des noms d'attributs à mettre à jour. ex : nom, prenom
        $valString  = implode(',', array_map(function ($v) {
            return ":" . $v;
        }, $atts)); // construction de la liste des pseudos-variables (même nom que l'attribut). ex = :nom, :prenom

        $sql = "update membres set ($attsString) = ($valString) where login=:login";
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindValue("login", $login);

        foreach ($infos as $key => $val)
            $stmt->bindValue($key, $val); // exécution des bind() pour la requête préparée

        try {
            $stmt->execute();

            $res = $this->getUser($login, $type = "long");

            return $res;
        } catch (PDOException $e) {
            // print_r( $stmt->errorInfo());   // debug

            return null;
        }
    }
    /*
   * récupère l'avatar d'un utilisateur.
   * Si aucun avatar n'est présent dans la base une image par défaut est envoyée (ou NULL)`
   * Résultat : objet à 2 attributs : flux (flux data ouvert en lecture) et type (mimetype)
   */
    function getAvatar($login, $size = "small", $defaultFileName = null)
    {
        $attsString = "type, contenu as avatar";
        $stmt = $this->connexion->prepare("select $attsString from avatars where pseudo = :login");

        $stmt->bindValue("login", $login);
        $stmt->bindColumn('type', $mimeType);
        $stmt->bindColumn('avatar', $flux, PDO::PARAM_LOB);
        $stmt->execute();

        if (!$stmt->fetch()) { // il n'y a pas d'image dans la base
            if ($defaultFileName === null || !is_readable($defaultFileName))
                return null;

            $fi = new finfo(FILEINFO_MIME);
            $mimeType = $fi->file($defaultFileName);
            $flux = fopen($defaultFileName, 'rb');
        }
        /* début modifications */

        /* fin modifications */
        if ($size == "small")
            return redimensionner_image((object)["type" => $mimeType, "flux" => $flux], 48);
        else
            return redimensionner_image((object)["type" => $mimeType, "flux" => $flux], 256);
    }

    /*
   * méthode privée, utilisable seulement par les autres méthodes de la classe
  */
    private function redimensionner_image($fichier, $nouvelle_taille)
    {
        //VARIABLE D'ERREUR
        global $error;

        //TAILLE EN PIXELS DE L'IMAGE REDIMENSIONNEE
        $longueur = $nouvelle_taille;
        $largeur = $nouvelle_taille;

        //TAILLE DE L'IMAGE ACTUELLE
        $taille = getimagesize($fichier);

        //SI LE FICHIER EXISTE
        if ($taille) {

            //SI JPG
            if ($taille['mime'] == 'image/jpeg') {
                $img_big = imagecreatefromjpeg($fichier); //OUVERTURE DE L'IMAGE ORIGINALE
                $img_new = imagecreate($longueur, $largeur);

                //CREATION DE LA MINIATURE
                $img_petite = imagecreatetruecolor($longueur, $largeur) or $img_petite = imagecreate($longueur, $largeur);

                //COPIE DE L'IMAGE REDIMENSIONNEE
                imagecopyresized($img_petite, $img_big, 0, 0, 0, 0, $longueur, $largeur, $taille[0], $taille[1]);
                imagejpeg($img_petite, $fichier);
            }

            //SI PNG
            else if ($taille['mime'] == 'image/png') {
                //OUVERTURE DE L'IMAGE ORIGINALE
                $img_big = imagecreatefrompng($fichier); // On ouvre l'image d'origine
                $img_new = imagecreate($longueur, $largeur);

                //CREATION DE LA MINIATURE
                $img_petite = imagecreatetruecolor($longueur, $largeur) or $img_petite = imagecreate($longueur, $largeur);

                //COPIE DE L'IMAGE REDIMENSIONNEE
                imagecopyresized($img_petite, $img_big, 0, 0, 0, 0, $longueur, $largeur, $taille[0], $taille[1]);
                imagepng($img_petite, $fichier);
            }
            // GIF
            else if ($taille['mime'] == 'image/gif') {
                //OUVERTURE DE L'IMAGE ORIGINALE
                $img_big = imagecreatefromgif($fichier);
                $img_new = imagecreate($longueur, $largeur);

                //CREATION DE LA MINIATURE
                $img_petite = imagecreatetruecolor($longueur, $largeur) or $img_petite = imagecreate($longueur, $largeur);

                //COPIE DE L'IMAGE REDIMENSIONNEE
                imagecopyresized($img_petite, $img_big, 0, 0, 0, 0, $longueur, $largeur, $taille[0], $taille[1]);
                imagegif($img_petite, $fichier);
            }
        }
    }
    
    /*
   * exécute la commande $sql d'insertion ou mise à jour d'un avatar dans la base
   * $sql possède   3 pseudo-variables :login,  :data, :type
   */
    private function storeAvatar($login, $stream1, $type, $sql)
    {
        $stmt = $this->connexion->prepare($sql);
        $stmt->bindValue(":login", $login);
        $stmt->bindValue(":type", $type);
        $stmt->bindValue(":data", $stream1, PDO::PARAM_LOB);
        $stmt->execute();
    }

    /*
   * enregistre une image avatar dans la base
   * $stream1 est un flux de données ouvert en lecture
   * $type est le mime type
   */
    function setAvatar($login, $stream1, $type)
    {
        try {
            //echo "try insert---";
            $this->storeAvatar(
                $login,
                $stream1,
                $type,
                'insert into avatars (pseudo, type, contenu) values (:login, :type, :data)'
            );
        } catch (PDOException $e) { // en cas de collision ("login"  est clé primaire)
            try {
                //echo "try update---";
                rewind($stream1);
                $this->storeAvatar(
                    $login,
                    $stream1,
                    $type,
                    'update avatars set (type, contenu) = (:type, :data) where pseudo=:login'
                );
            } catch (PDOException $e) {
                return null;
            }
        }
        
        return true;
    }
    
    /*
   * Liste des utilisateurs s'intéressant au $sujet
   *
   * Renvoie un tableau d'identifiants
   */
    function getByInterest($sujet)
    {
        $sql = "select login from s10.interets where sujet=:sujet";
        $stmt = $this->connexion->prepare($sql);
        
        $stmt->bindValue(":sujet", $sujet);
        $stmt->execute();
        
        $res = array();
        $ligne = $stmt->fetch();
        
        while ($ligne) {
            $res[] = $ligne['login'];
            $ligne = $stmt->fetch();
        }
        
        return $res;
    }
    
    /*
   * Insère ou met à jour la table still_alive, avec la date actuelle
   */
    function stampUser($login)
    {
        // try update---
        $sql = "update s10.still_alive set stamp=default where login=:login";
        $stmt = $this->connexion->prepare($sql);
        
        $stmt->bindValue(":login", $login);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) { // l'utilisateur était absent de la table
            // ---> insert
            $sql = "insert into s10.still_alive (login) values (:login)";
            $stmt = $this->connexion->prepare($sql);
            
            $stmt->bindValue(":login", $login);
            $stmt->execute();
        }
    }
    
    /*
   * Liste des utilisateurs présents dans still_alive
   */
    function getAliveUsers()
    {
        $sql = "select login from s10.still_alive";
        $stmt = $this->connexion->query($sql);
        $res = array();
        $ligne = $stmt->fetch();
        
        while ($ligne) {
            $res[] = $ligne['login'];
            $ligne = $stmt->fetch();
        }
        
        return $res;
    }
    
    /*
   * Supprime de  still_alive les utilisateurs n'ayant pas été pointés présents depuis plus de 30s
   */
    function cleanAlive()
    {
        $sql = "delete from s10.still_alive where now()-stamp > '30 s'::interval ";
        
        $this->connexion->exec($sql);
    }

    /*
   * Liste de messages correspondant aux critères passés en argument.
   * Les critères sont cumulatifs. Un critère absent ou vide n'est pas appliqué.
  */
    function findMessages($author, $follower, $mentioned, $before, $after, $count = 15)
    {
        $stock = $this->connexion->query("select abonne form abonnements where membre = :follower");
        $attsString = "auteur = :membres and message like '%$stock%' and id < :before  and id > :after limit $count order by desc";
        $stmt = $this->connexion->prepare("select * from messages where auteur = :author and $attsString");
        
        $stmt->bindValue("author", $author);
        $stmt->bindValue("follower", $follower);
        $stmt->bindValue("mentioned", $mentioned);
        $stmt->bindValue("before", $before);
        $stmt->bindValue("after", $after);
        $stmt->bindValue("membres", $stock);
        $stmt->execute();
        
        // /!\ attention! vérifier ceci
        $res = array();
        $ligne = $stmt->fetch();
        
        while ($ligne) {
            $res[] = $ligne;
            $ligne = $stmt->fetch();
        }
        
        return $res;
    }

    function findUsers($searched, $scope = "both", $type = "short")
    {
        if ($scope = "ident")
            $attsString = "pseudo like '%$searched%'";
        elseif ($scope = "name")
            $attsString = "nom '%$searched%'";
        else
            $attsString = "concat(pseudo,nom) like '%$searched%'";

        if ($type == "long")
            $stmt = $this->connexion->prepare("select pseudo, nom from membres where $attsString");
        else
            $stmt = $this->connexion->prepare("select * from membres where $attsString");

        $stmt->execute();
        
        // /!\ attention! vérifier ceci
        $res = array();
        $ligne = $stmt->fetch();
        
        while ($ligne) {
            $res[] = $ligne;
            $ligne = $stmt->fetch();
        }
        
        return $res;
    }

    function postMessage($login, $name, $source)
    {
        $attsString = "values ('$login','$name','$source')";
        $stmt = $this->connexion->prepare("insert into messages(pseudo,auteur,message) $attsString");
        
        $stmt->execute();

        $stmt = $this->connexion->prepare("select id from message where message=$source");
        $stmt->execute();

        $res = $stmt->fetch();
        
        return $res;
    }
}
