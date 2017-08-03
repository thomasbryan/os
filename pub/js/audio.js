$(document).ready(function() {
    console.log("audio initialize.");
    var templateData = {
      "name":"thomas",
      "timeNow":"now",
    };
    $.get("htm/audio.htm", function(templates) {
      var template = $(templates).filter('#tpl-greeting').html();
      $("#app").html(Mustache.render(template, templateData));
    });
});
function audioplay() {
  console.log("i should play this song");
}
