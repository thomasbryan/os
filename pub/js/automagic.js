$(document).ready(function() {
  action({"req":"listWorkflows"},"listWorkflows");
});
$(document).on("click","#method a",function() {
  $("#method a").removeClass("active");
  $(this).addClass("active");
  $("#attributes > div").addClass("hidden");
  $("#method-"+$(this).data("method")).removeClass("hidden");
});
function listWorkflows(req) {
  $.get("htm/home.htm", function(templates) {
    var template = $(templates).filter('#tpl-automagic').html();
    $("#app").html(Mustache.render(template, req));
  });
}
function createWorkflow() {
  if($("#app .automagic > .actions .list").html().length == 0) {
    action({"req":"listActions"},"listActions");
  }
  view("actions");
  $(".automagic > .actions input").val("").focus();
  $(".automagic > .actions > .action").html("");
}
function listActions(req) {
  $.get("htm/home.htm", function(templates) {
    var template = $(templates).filter('#tpl-automagic-actions').html();
    $("#app .automagic > .actions .list").html(Mustache.render(template, req));
  });
}
function createAction() {
  if($("#app .automagic > .methods .list").html().length == 0) {
    action({"req":"listMethods"},"listMethods");
  }
  view("methods");
  $(".automagic > .methods input").val("").focus();
  $(".automagic > .methods textarea").val("");
}
function listMethods(req) {
  $.get("htm/home.htm", function(templates) {
    var template = $(templates).filter('#tpl-automagic-methods').html();
    $("#app .automagic > .methods .list").html(Mustache.render(template, req));
  });
}
function readAction(req) {
  view("action");
}
function view(req) {
  $(".automagic > div").addClass("hidden");
  $(".automagic > ."+req).removeClass("hidden");
}
