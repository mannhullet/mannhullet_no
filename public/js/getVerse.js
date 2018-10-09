function getVerse() {
  var text = document.getElementById("hentvers").innerHTML;
  var obj = JSON.parse(text);
  var antallVers = obj.length;

  // Create a list of verse indexes that have not yet been used, if it does
  // not already exist. Repopulate it if it is empty.
  if (typeof getVerse.availableIndexes == 'undefined') {
    getVerse.availableIndexes = [];
  }
  if (getVerse.availableIndexes.length === 0) {
    for (n = 0; n < antallVers; n++) {
      getVerse.availableIndexes[n] = n;
    }
  }

  // Draw one of the available indexes at random, and remove it from the list
  var index = Math.floor((Math.random() * getVerse.availableIndexes.length));
  var i = getVerse.availableIndexes.splice(index, 1)[0];

  document.getElementById("plassholder").innerHTML = "<span class=\"infoDescription\"> Trykk igjen for nytt vers! </span>";
  document.getElementById("visVers").innerHTML = obj[i].tekst;

  var forfatter = obj[i].forfatter;
  var kommentar = obj[i].kommentar;
  var dato = obj[i].dato;

  // Give default values to fields that are not defined
  if (forfatter === undefined) {
    forfatter = "Ukjent";
  }
  if (kommentar === undefined) {
    kommentar = "";
  }
  if (dato === undefined) {
    dato = "Ukjent";
  }

  document.getElementById("visForfatter").innerHTML = "<span class=\"infoDescription\">Forfatter: </span>" + forfatter;
  document.getElementById("visKommentar").innerHTML = "<span class=\"infoDescription\">Kommentar: </span>" + kommentar;
  document.getElementById("visDato").innerHTML = "<span class=\"infoDescription\">Skrevet: </span>" + dato;

}
