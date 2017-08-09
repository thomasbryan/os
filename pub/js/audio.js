$(document).ready(function() {
  action({"req":"","done":"audioplay"});
    console.log("audio initialize.");
    var templateData = {
      "playlist": [
      ],
    };
    $.get("htm/audio.htm", function(templates) {
      var template = $(templates).filter('#tpl-greeting').html();
      $("#app").html(Mustache.render(template, templateData));
    });
});
function audioplay() {
  console.log("i should play this song");
}
