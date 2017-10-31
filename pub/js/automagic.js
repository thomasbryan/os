$(document).ready(function() {
  action({"req":"listWorkflows"},"listWorkflows");
});
$(document).on("click","#method a",function() {
  $("#method a").removeClass("active");
  $(this).addClass("active");
  $("#attributes > div").addClass("hidden");
  $("#method-"+$(this).data("method")).removeClass("hidden");
  $("#method-"+$(this).data("method")+" textarea").focus();
});
$(document).on("click",".workflows td a:not(.hidden)",function() {
  var workflow = $(this).closest("tr").data("workflow");
  switch($(this).closest("td").data("action")) {
    case "read":
      action({"req":"readWorkflows","n":workflow},"readWorkflows");
    break;
    case "remove":
    break;
    case "run":
      action({"req":"runWorkflows","n":workflow});
    break;
  }
});
$(document).on("click",".actions .list td a:not(.hidden)",function() {
  var actions = $(this).closest("tr").data("action");
  switch($(this).closest("td").data("action")) {
    case "add":
      var num = $(".actions .action tr").length
      if(!$(this).hasClass("active")) {
        $(".actions .action tbody").append("<tr data-action='"+actions+"' class='action-"+num+"'><td><div onclick='javascript:deleteAction("+num+");' class='btn-group col-xs-12'><div class='btn btn-default col-xs-10'>"+actions+"</div><div class='btn btn-danger col-xs-2'><span class='glyphicon glyphicon-trash'></span> Remove</div></div></td></tr>");
        $(this).addClass("active action-"+num);
      }
    break;
    case "remove":
      action({"req":"deleteActions","f":actions},"deleteActions");
    break;
    case "read": readActions(actions); break;
  }
});
function listWorkflows(req) {
  $.get("htm/home.htm", function(templates) {
    var template = $(templates).filter('#tpl-automagic').html();
    $("#app").html(Mustache.render(template, req));
  });
}
function createWorkflows() {
  action({"req":"listActions"},"listActions");
  view("actions");
  $(".automagic > .actions input").val("").focus();
  $(".automagic > .actions > .action.list-group").html("");
}
function createWorkflow() {
  var err = ""
    , name = $("#workflowName").val()
    ;
  if(name.length == 0) {
    err += "Name Required,";
  }
  if($(".actions .action tr").html().length == 0) {
    err += "Action Required,";
  }
  if(err.length > 0) {
    msg(false,err.replace(/,\s*$/,""));
  }else{
    var data = [];
    $.each($(".actions .action tr"),function(k,v) {
      data.push($(this).data("action"));
    });
    action({"req":"createWorkflows","n":name,"d":data},"view");
  }
}
function readWorkflows(req) {
  $("#workflow-n").html(req.n);
  $("#workflow-req").html("");
  $.each(req.req,function(k,v) {
    $("#workflow-req").append(v+"\n");
  });
  $("#workflow-res").html(req.res);
  $("#workflow-run").data("n",req.n);
  view("workflow");
}
function deleteWorkflows() {
  action({"req":"deleteWorkflows","n":$("#workflow-run").data("n")},"view");
}
function runWorkflows() {
  action({"req":"runWorkflows","n":$("#workflow-run").data("n")});
}
function listActions(req) {
  $.get("htm/home.htm", function(templates) {
    var template = $(templates).filter('#tpl-automagic-actions').html();
    $("#app .automagic > .actions .list").html(Mustache.render(template, req));
  });
}
function createActions() {
  if($("#app .automagic > .methods .list").html().length == 0) {
    action({"req":"listMethods"},"listMethods");
  }
  view("methods");
  $(".automagic > .methods input").val("").focus();
  $(".automagic > .methods textarea").val("");
}
function createAction() {
  var err = ""
    , name = $("#actionName").val()
    , method = $("#method .active").data("method") 
    , data = ""
    ;
  if(name.length == 0) {
    err += "Name Required,";
  }
  if(method === undefined) {
    err += "Method Required,";
  }
  if(err.length > 0) {
    msg(false,err.replace(/,\s*$/,""));
  }else{
    data = $("#method-"+method+" textarea").val();
    if(data === undefined) {
      msg(false,"Method Not Allowed");
    }else{
      if(data.length == 0) {
        msg(false,"Data Required");
      }else{
        action({"req":"createActions","n":name,"m":method,"d":data},"view");
      }
    }
  }
}
function deleteAction(req) {
  $(".actions .action tr.action-"+req).remove();
  $(".action-"+req).removeClass("active action-"+req);
}
function deleteActions(req) {
  $(".actions .list tr[data-action='"+req+"']").remove();
}
function listMethods(req) {
  $.get("htm/home.htm", function(templates) {
    var template = $(templates).filter('#tpl-automagic-methods').html();
    $("#app .automagic > .methods .list").html(Mustache.render(template, req));
  });
}
function readActions(req) {
  action({"req":"readActions","f":req},"readAction");
}
function readAction(req) {
  try {
    var json = $.parseJSON(req);
  } catch(err) {
    var json = JSON.stringify(req);
  }       
  $(".action pre.content").html(json);
  view("action");
}
function view(req) {
  $(".automagic > div").addClass("hidden");
  $(".automagic > ."+req).removeClass("hidden");
}
