$(document).ready(function() {
    console.log("audio initialize.");
    var templateData = {
      "playlist": [
      ],
    };
    $.get("htm/audio.htm", function(templates) {
      var template = $(templates).filter('#tpl-greeting').html();
      $("#app").html(Mustache.render(template, templateData));
$("#p").html(
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"+
"<a class='list-group-item'>a</a>"
      );
    });
});
function audioplay() {
  console.log("i should play this song");
}
