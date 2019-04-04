window.addEventListener('load', ecrireTable);

/*
 * toutes les secondes, envoyer une nouvelle requête au service web
*/
function ecrireTable() {
    setInterval(sendRequest, 1000);
}

/*
 * Envoi d'une requête au service web :
 */
function sendRequest() {
    var url = "services/findMessages.php";
    var id = document.querySelector('#id_message').value.trim(); // ajouter à l'URL le paramètre capteur, si nécessaire

    if (capteur != "")
        url += "?id_message=" + id;

    // créer l'objet XMLHttpRequest
    var xhr = new XMLHttpRequest();
    xhr.open('GET', encodeURI(url), true);
    xhr.addEventListener('load', fillTable); // mettre en place le gestionnaire d'évènement pour traiter le résultat
    xhr.send(null);// exécuter la requête
}

/*
 * Fonction déclenchée à la réception des données :
 * Vérifie si le résultat est correct puis
 * met à jour la page
 */
function fillTable(ev) {
    var reponse = JSON.parse(this.responseText); // reconstiter la réponse serveur sous forme d'ojet javascript :

    if (!reponse.ok) // si la requête s'est mal passée, ne rien faire.
        return;

    // récupérer dans le champ input le nombre maximal de lignes à faire apparaître :
    var maxText = document.querySelector('#nombreMax').value.trim();
    var max = null;

    if (maxText != "")
        max = parseInt(maxText);

    // remplacer l'ancien tbody par un autre, avec le nouveau résultat
    var oldTbody = document.querySelector('#temps tbody');
    var newTbody = createTbody(reponse.result, max);

    oldTbody.parentNode.replaceChild(newTbody, oldTbody);
}

/*
 * Fabrique un élément DOM de type tbody dont le contenu est fourni en paramètre
 * paramètres :
 * tab : tableau d'objets (attribut result de la réponse serveur)
 * max : nombre maximal de lignes à créer dans l'élément tbod, ou null si pas de max
 *
 */
function createTbody(tab, max) {
    if (max == null)
        max = tab.length;

    var tbody = document.createElement('tbody');

    for (var i = 0; i < Math.min(tab.length, max); i++) {
        var tr = document.createElement('tr');

        for (var field in tab[i]) {
            var td = document.createElement('td');
            td.setAttribute('class', field);
            td.textContent = tab[i][field];
            tr.appendChild(td);
        }

        tbody.appendChild(tr);
    }

    return tbody;
}
