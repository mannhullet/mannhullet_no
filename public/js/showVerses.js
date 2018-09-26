function showVerses(text) {
  console.log(text);

  var obj = JSON.parse(text);

  var antallVers = obj.sjiraffenvers.length;

  console.log(obj.sjiraffenvers[2]['tekst']);


  for (i = 0; i < antallVers; i++) {
    document.getElementById("visAlleVers").innerHTML += obj.sjiraffenvers[i]['tekst'];
    document.getElementById("visAlleVers").innerHTML += "<br />\r\n <br />\r\n";
  }



}
