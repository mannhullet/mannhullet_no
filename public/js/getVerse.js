function getVerse () {
  $.ajax({
           type: "POST",
           url: 'application//ajax.php',
           data:{action:'call_this'},
           success:function(html) {
             alert(html);
           }

      });
}
