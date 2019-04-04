<?php
require_once('../lib/auth.php');
require_once("../lib/biblio.php");

// script accessible uniquement pour un utilisateur authentifié

/*
 * Params : $image of size $largeur x $hauteur
 * Returns new image  of size $cote x $cote
 * copy of the maximal centered square of original
 **/
function cropSquare($image, $cote)
{
    $largeur = imagesx($image);
    $hauteur = imagesy($image);
    $dim = min($hauteur, $largeur);
    $xOrigine = ($largeur - $dim) / 2;
    $yOrigine = ($hauteur - $dim) / 2;
    $newImg = imagecreatetruecolor($cote, $cote);

    if (!imagecopyresampled($newImg, $image, 0, 0, $xOrigine, $yOrigine, $cote, $cote, $dim, $dim))
        throw new Exception("copie impossible");

    return $newImg;
}

/*
 * Returns image ressource initialized with data from $fileName (auto-detect format)
 */
function getImageFromFile($fileName)
{
    return imagecreatefromstring(file_get_contents($fileName));
}

/*
 * Returns image ressource initialized with data from $stream (auto-detect format)
 */
function getImageFromStream($stream)
{
    return imagecreatefromstring(stream_get_contents($stream));
}

/*
 * get stream reader from $image ressource
 * s$format can be either "jpeg","png","bmp","gif"
 * or  mime type : "image/jpeg", ...etc
 * returns = stream ("r+" mode)
 */
function getStreamFromImage($image, $format)
{
    $format = preg_replace("/^image\//", "", $format); //remove "image/" prefix

    if (!in_array($format, ["jpeg", "png", "bmp", "gif"]))
        throw new Exception();

    $funcName = "image$format"; // set correct functionName, according to output format
    $fd = fopen("php://memory", "r+");  // open stream to store temporary data in memory

    ob_start();         // redirect normal output to buffer
    $funcName($image);  // write image data to buffer
    fwrite($fd, ob_get_clean()); // write buffered data to $fd and stop output redirection
    rewind($fd);        // $fd now positioned to read data

    return $fd;
}

try {
    $fileDesc = $_FILES['fichier-avatar'];
    $image = getImageFromFile($fileDesc['tmp_name']);
    $img70 = cropSquare($image, 70);
    $newType = "image/png";
    $stream70 = getStreamFromImage($img70, $newType);
    $done = $bd->setAvatar($_SESSION['ident']->getLogin(), $stream70, $newType);

    if ($done) {
        $status = "ok";
        $result = true;
    } else {
        $status = "error";
        $result = null;
    }
} catch (Exception $e) {
    $status = "error";
    $result = null;
} finally {
    $args = array(
        $_FILES['nomAChoisir']['name'], $_FILES['nomAChoisir']['size'],
        $_FILES['nomAChoisir']['type'], $_FILES['nomAChoisir']['error']
    );

    $reponse = array("status" => $status, "args" => $args, "result" => $result);

    header("Content-type: application/json;charset=UTF-8");
    echo json_encode($reponse);
}

return;
