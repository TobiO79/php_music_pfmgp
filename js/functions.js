/* 
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

var elem=document.getElementsByClassName("BR");
for (var i=0; i<elem.length; i++) elem[i].addEventListener('change',neutral);
    
function neutral() {
    var mittelwert=document.getElementById("neutral");
    var mittel=mittelwert.value;
    var min=mittelwert.value*0.9;
    var max=mittelwert.value*1.1;
    var button=document.getElementsByClassName("BR");
    var summe=0;
    for(var i=0; i<button.length; i++) if(button[i].checked) summe=summe+parseInt(button[i].value);
    var text=document.getElementById("Kommentar");
    if (summe>max) text.innerHTML='Deine Bewertung ist sehr positiv. Überprüfe noch einmal, ob du nicht etwas kritischer abstimmen möchtest.';
    if (summe<min) text.innerHTML='Deine Bewertung ist sehr negativ. Versuche doch noch ein paar Punkte mehr zu vergeben.';
    if (summe<=max) if (summe>=min) text.innerHTML='';
    var tendenz=document.getElementById("tendenz");
    tendenz.value=summe-mittel;
      
}

