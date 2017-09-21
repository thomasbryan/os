$(document).ready(function() {
	gitinit();
});
function gitinit() {
	action({"req":"status"},"gitdone");
}
function gitdone(req) {
	$.get("htm/home.htm", function(templates) {
		var template = $(templates).filter('#tpl-git').html();
		$("#app").html(Mustache.render(template,{"projects":req}));
  });
}
$(document).on("click","#cache",function(e) {
	action({"req":"cache"},"gitinit");
});