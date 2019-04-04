/*
 *
 *  transforme le texte du message (où caractères @ et backslash sont echappés, et où les @xxx non echappés sont une mention)
 *  en un élement HTML/DOM affichable (avec marquage sémantique des mentions)
 *
 *  COMPLETER : la méthode initMentions pour mettre en place des gestionnaires d'évènements
 *  
 **/
class MessageDisplay {

    // attribut de la classe :
    //
    //    article : elément DOM  (<article class="message">) prêt à être inséré dans un document
    //    mentioned : liste des ident mentionnés dans l'article 
    constructor(escapedMessage) {
        var reg = /(\\([@\\]))|(@([a-zA-Z0-9_]+))/g;   // expression régulière avec 4 parenthèses capturantes
        var replacement = (found, p1, p2, p3, p4) => {  // fonction (fléchée) appelée lors du replace 

            if (p1)  // escaped @ or backslash. (escaped char is p2)
                return p2;

            if (p3) // unescaped @ followed by ident (ident is p4)
                return this.mentionToHTML(p4); // replace @ident by some html stuff (semantic mark-up)
        };

        this.mentioned = [];
        var html = escapedMessage.replace(reg, replacement); // calculate unescaped message string with HTML semantic mark-up for mentions
        this.article = document.createElement("article");
        this.article.classList.add("message");
        this.article.innerHTML = html;
        this.initMentions(this.article);    // initialize event listeners       
    }

    // create semantic mark-up : <span class="mention" data-ident="???">@???</span> (where ??? is ident value)
    mentionToHTML(ident) {
        this.mentioned.push(ident);

        return '<span class="mention" data-ident="' + ident + '">@' + ident + '</span>';
    }

    initMentions() {  // à compléter : mise en place des listeners sur les mentions du message (évènements click, mouseover, mouseout ...)
        var mentions = this.article.querySelectorAll("span.mention");

        for (var m of mentions) {
            // l'identifiant concerné par la mention est dispo dans m.dataset.mention
            // m.addEventListener("click",someListener);
            console.log(m); // à supprimer
        }
    }
};

