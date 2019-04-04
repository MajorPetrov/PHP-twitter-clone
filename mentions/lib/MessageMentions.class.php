<?php
/*
 * Classe permettant l'encodage du message AVANT son enregistrement dans la base
 *
 * Une instance est élaborée à partir du texte source, une détection des mentions "possibles" (syntaxiquement) est effectuée.
 *
 * La liste des possibles idents trouvés est disponibles via getFoundIdents() et getFoundIdentsString()
 * 
 * La methode setMentions($userList) permet d'indiquer lesquels de ces idents sont à considérer comme des mentions.
 *
 * Voici une utilisation typique :
 *    $mm = new MessagesMentions($texteSourceDuMessage);
 *    $idList = $mm->getFoundIdentsString();
 *    $stmt = $connexion->query("select ident from users where ident in $idList");
 *    // ... à compléter :  rassembler dans un tableau ($trueUsers) les ids trouvés dans la base 
 *    $stmt->setMentions($trueUsers);
 *    $messageAEnregistrer = $stmt->encodedMessage();
 *    // ...
 */
class MessageMentions
{
    private $sourceMessage;         //  string 
    private $foundIdents;           // array of found idents (without checking if they are real users)
    private $foundIdentsString;     // sames,  as SQL list.   e.g. ('john','joe')
    private $escapedMessage;        // message with every @ and \ escaped
    private $encodedMessage = null; // string

    public function __construct($source)
    {
        $this->sourceMessage = $source;
        $this->escapedMessage = preg_replace('/([@\\\\])/', '\\\\$1', $source);

        preg_match_all('/@([a-zA-Z0-9_]+)/', $source, $match, PREG_OFFSET_CAPTURE); // find arobase words

        $this->foundIdents = array_map(function ($v) {
            return $v[0];
        }, $match[1]); // extract found idents

        $quotedNames = array_map(function ($v) {
            return "'{$v}'";
        }, $this->foundIdents);

        $this->foundIdentsString = '(' . implode(',', $quotedNames) . ')';
    }

    /*
    * texte source du message
    */
    public function getSource()
    {
        return $this->sourceMessage;
    }

    /*
    * tableau des identifiants mentionnés, syntaxiquement (sans vérification  qu'il s'agit de réels utilisateurs)
    */
    public function getFoundIdents()
    {
        return $this->foundIdents;
    }

    /*
    * mêmes identifiants que getFoundIdents(), mais sous forme de liste SQL, pour utilisation avec l'opérateur IN  : ('john','joe') par exemple
    */
    public function getFoundIdentsString()
    {
        return $this->foundIdentsString;
    }

    /*
    * $userList doit être un tableau d'identifiants correspondant à des utilisateurs réels
    */
    public function setMentions($userList)
    {
        $s = $this->escapedMessage;

        foreach ($userList as $ident)
            $s = preg_replace("/\\\\@$ident\\b/", "@$ident", $s);

        $this->encodedMessage = $s;
    }

    /*
    * fournit le message correctement encodé.
    * à utiliser seulement après setUsers() (sinon renvoie NULL)
    */
    public function getEncodedMessage()
    {
        return $this->encodedMessage;
    }
}
