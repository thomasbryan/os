var git = localStorage.git;
$(document).ready(function() {
  if(git == null) {
    git = {"name":"","email":"","repo":"","grep":""};
  }else{
    git = JSON.parse(git);
  }
	gitinit();
});
function gitinit() {
	action({"req":"status"},"gitdone","gitfail");
}
function gitdone(req) {
	/* Order repos by max items */
	var project = {}
		, projects = []
		;
	$.each(req,function(k,v) {
		var c = Math.max.apply(Math, [v.s.length, v.n.length, v.u.length] );
		if(project[c] === undefined) project[c] = [];
		project[c].push(v);
	});
	$.each(project,function(k,v) {
		$.each(v,function(kk,vv) {
			projects.unshift(vv);
		});
	});
	$.get("htm/home.htm", function(templates) {
		var template = $(templates).filter('#tpl-git').html();
		$("#app").html(Mustache.render(template,{"projects":projects}));
    if(git.repo.length > 0) {
      $("#projects li:not(.action)[data-repo='"+git.repo+"']").click();
    }
    if(git.grep.length > 0) {
			$("#q").val(git.grep);
    }
  });
}
function gitfail() {
	action({"req":"cache"});
}
$("form#search").on("submit",function(e) {
  e.preventDefault();
	git.grep = $("#q").val();
	localStorage.git = JSON.stringify(git);
  if($("#q").val().length > 0) {
		var repo = $("#project .repo:not(.hidden)").data("repo");
		action({"req":"grep","grep":git.grep,"project":repo},"gitgrep");
  }
});
function gitgrep(req) {
  $("#q").blur();
  var html = "<div id='overlay'><div class='panel panel-info'><div class='panel-heading'><a>&nbsp;</a>Grep '"+git.grep+"' '"+req.repo+"'</div><div class='panel-body'>";
  $.each(req.grep,function(k,v) {
    html += "<div class='line'>"+v.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\ /g,"&nbsp;")+"</div>";
  }); 
  html+= "</div></div></div>";
  $("body").append(html);
  $(".navbar, #app").addClass("hidden");
  $("#overlay .panel-body").css({"height":Math.ceil($(window).height() * 0.85)+"px"});
}
$(document).on("click","#cache",function(e) {
	action({"req":"cache"},"gitinit");
});
$(document).on("click","#projects li:not(.action)",function(e) {
  e.stopPropagation();
  var repo = $(this).data("repo");
  $("#projects li").removeClass("disabled");
  $(this).addClass("disabled");
  $("#project > div").addClass("hidden");
  $("#project > div[data-repo='"+repo+"']").removeClass("hidden");
  git.repo = repo;
  localStorage.git = JSON.stringify(git);
});
$(document).on("click",".action",function(e) {
  e.stopPropagation();
  var repo = $(this).closest(".repo").data("repo");
  switch($(this).data("action")) {
    case "all":
      action({"req":"all","project":repo},"gitinit");
    break;
    case "add":
      action({"req":"add","project":repo,"file":$(this).data("file")},"gitinit");
    break;
    case "rem":
      action({"req":"rem","project":repo,"file":$(this).data("file")},"gitinit");
    break;
    case "push":
      action({"req":"push","project":repo},"gitinit");
    break;
    case "pull":
      action({"req":"pull","project":repo},"gitinit");
    break;
    case "diff":
      action({"req":"diff","project":repo},"gitdiff");
    break;
  }
});
function gitdiff(req) {
  //TODO template?
  var html = "<div id='overlay'><div class='panel panel-info'><div class='panel-heading'><a>&nbsp;</a>Diff '"+req.repo+"'</div><div class='panel-body'>";
  $.each(req.diff,function(k,v) {
    var c = "info";
    switch(v.charAt(0)) {
      case "-": c = "danger"; break;
      case "+": c = "success"; break;
    }   
    html += "<span class='text-"+c+"'>"+v.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\ /g,"&nbsp;")+"</span><br />";
  }); 
  html+= "</div></div></div>";
  $("body").append(html);
  $(".navbar, #app").addClass("hidden");
  $("#overlay .panel-body").css({"height":Math.ceil($(window).height() * 0.85)+"px"});
}

$(document).on("click","#overlay",function(e) {
	$(".navbar, #app").removeClass("hidden");
	$("#overlay").remove();
});