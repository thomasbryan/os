$(document).ready(function() {
    	console.log("home base");
    var templateData = {
      "name":"thomas",
      "timeNow":"now",
    };
    $.get("htm/home.htm", function(templates) {
      // Fetch the <script /> block from the loaded external
      // template file which contains our greetings template.
      var template = $(templates).filter('#tpl-home').html();
      $("#app").html(Mustache.render(template, templateData));
    });
});
