$("form#edit").on("submit",function(e) {
  e.preventDefault();
  switch(app.app) {
    case "audio":
      ajx($(this).serialize());
      $(".edit").addClass("hidden");
      $("body").removeClass("body-edit");
    break;
    case "edit":
      console.log("update this information");
      console.log($(this).serialize());
          /*
          $.ajax({
            type: "POST",
                url: "api.php?app=editor",
            data: $(this).serialize()
          }).fail(function() {
            mesg(false,"Unable to Update");
          });
          */
      $(".edit").addClass("hidden");
      $("body").removeClass("body-edit");
    break;
  }
});
$(document).on("submit","form#create",function(e) {
  e.preventDefault();
  var l = $("#create .create").val();
  if(l.length > 0) {
    $.ajax({
      type: "POST",
      url: "api.php?app=audio",
      data: "req=create&"+$(this).serialize()
    }).done(function(res) {
      var k = $("#r .l").length;
      $("#r").append("<a id='list-"+k+"' class='l list-group-item '>"+l+"<button class='btn btn-default btn-xs pull-right'><span class='glyphicon glyphicon-minus-sign'></span> Playlist</button></a>");
      $("#list-"+k).data("l",l);
      $("#create .create").val("");
    }).fail(function() {
      $("#create .create").val("");
    });
  }
});
function search(req) {
  ( req ? $(".form-control-feedback").removeClass("glyphicon-search").addClass("glyphicon-hourglass") : $(".form-control-feedback").removeClass("glyphicon-hourglass").addClass("glyphicon-search") );
}
function google() {
  $("#q").blur();
  $("#r .g.disabled").remove();
  search(true);
  ajx("req=google&y="+$("#q").val(),"googledone","googlefail");
}
function googledone(res) {
  search(false);
  var html = "";
  $.each(res,function(k,v){
    html += "<a class='y list-group-item' data-d='"+v.u+"'>";
    html += "<h4 class='list-group-item-heading'>"+v.n+"</h4>";
    html += "<p class='list-group-item-text'>"+v.u+"</p>";
    if(v.i!==undefined) html += "<img src='"+v.i+"' />";
    html += "</a>";
  });
  $("#r").append(html);
  if($("#r .active").length==0) {$("#r a:first").addClass("active");}
}
function googlefail() {
  search(false);
  $("#r").append("<a class='y list-group-item disabled'>Your search - <b>"+$("#q").val()+"</b> - did not match any documents.</a>");
}
$(document).on("click",".audio #r .g:not(.disabled) button.update",function(e) {
  var div = $(this)
    , data = div.parent().data()
    ;
  data.l = audio.l;
  data.req = "update";
  $.ajax({
    type: "POST",
    url: "api.php?app=audio",
    data: data
  }).done(function(res) {
    div.addClass("btn-info");
  });
  e.stopPropagation();
});
$(document).on("click",".audio #r .g:not(.disabled)",function(e) {
  play($(this).data());
  reset();
});
$(document).on("click",".audio #r .y:not(.disabled)",function(e) {
  $(this).addClass("disabled").removeClass("active");
  $.ajax({
    type: "POST",
      url: "api.php?app=audio",
    data: "req=download&d="+$(this).data("d")+"&q="+$("#q").val()
  }).done(function(res) {
    reset();
  }).fail(function() {
    //TODO check not returning something??
    mesg(false,"Failed to Download");
    reset();
  });
});
$(document).on("click",".audio #r .l button",function(e) {
  var div = $(this).parent()
    , data = div.data()
    ;
  data.req = "delete";
  $.ajax({
    type: "POST",
    url: "api.php?app=audio",
    data: data
  }).done(function(res) {
    div.remove();
  }).fail(function() {
    mesg(false,"Unable to Delete '"+data.l+"' Playlist");
  });
  e.stopPropagation();
});
$(document).on("click",".audio #r .l",function(e) {
  if($(this).data("l")) {
    audio.l = $(this).data("l");
    localStorage.audio = JSON.stringify(audio);
    reset();
    ui();
  }
});
$(document).on("click",".navbar-brand",function(e) {
  switch ($(this).data("req")) {
    case "ce":close();break;
    case "tn":trash();break;
    case "etn":edittrash();break;
  }
});
$(document).on("click","#p a,.audio #r .g:not(.disabled) button:not(.update)",function(e) {
  tmpl("#audio-edit","#tpl-audio-edit",$(this).parent().data());
  $(".edit").removeClass("hidden");
  $("body").addClass("body-edit");
  $("#f").val(($(this).data("f")===undefined?$(this).parent().data("f"):$(this).data("f")));
  $("#n").val(($(this).data("n")===undefined?$(this).parent().data("n"):$(this).data("n"))).focus();
  e.stopPropagation();
});
function st() {
  $("#a").get(0).currentTime = 0;
}
function pp() {
  if(audio.p) {
    audio.p = false;
    $("#a").get(0).pause();
  }else{
    audio.p = true;
    if($("#a source").attr("src").length > 0 ) $("#a").get(0).play();
  }
  bookmark();
  ui();
}
function fo() {
  ajx(audio,"fodone","fofail");
}
function fodone(res) {
  audio.c = res.c;
  var stale = false;
  if(res.c > $("#p a").length) {
    $.each($("#p a"),function(k,v) {
      if($(this).data("f") == res.f) stale = true;
    });
  }
  if(stale) {
    fo();
  }else{
    play({"f":res.f,"n":res.n,"t":0});
  }
}
function fofail() {
  mesg(false,"Failed to Initialize");
  audio.p = false;
  ui();
}
function play(req) {
  audio.f = req.f;
  audio.n = req.n;
  audio.t = req.t;
  localStorage.audio = JSON.stringify(audio);
  playlist();
  playaudio();
  ui();
}
function audiolist() {
  reset();
  if($("#create").length == 0) {
    $.ajax({
      type: "POST",
      url: "api.php?app=audio",
      data: "req=list"
    }).done(function(res) {
      $(".col-sm-8 h5").html("<span class='glyphicon glyphicon-list'></span> Playlists");
      $("#r").html("<div class='list-group-item list-group-item-success'><form id='create'><div class='input-group'><input class='create form-control' name='l' type='text' placeholder='Create' /><div class='input-group-btn'><button class='btn btn-default'><span class='glyphicon glyphicon-plus-sign'></span> Playlist</button></div></div></form></div>");
      if(audio.l===undefined) audio.l="";
      $.each(res,function(k,v) {
        var a = "";
        if(audio.l == v) a = "active";
        $("#r").append("<a id='list-"+k+"' class='l list-group-item "+a+"'>"+v+"<button class='btn btn-default btn-xs pull-right'><span class='glyphicon glyphicon-minus-sign'></span> Playlist</button></a>");
        $("#list-"+k).data("l",v);
      });
    }).fail(function() {
      mesg(false,"Failed Playlist Retreival");
    });
  }
}
function close() {
  $(".edit").addClass("hidden");
  $("body").removeClass("body-edit");
}
function trash() {
  $.ajax({
    type: "POST",
      url: "api.php?app=audio",
    data: "req=trash&f="+$("#f").val()
  }).done(function(res) {
    $(".list-group").find("[data-f='"+$("#f").val()+"']").remove();
    close();
  }).fail(function() {
    mesg(false,"Failed to Delete File");
  });
}
function playlist() {
  $("#p").prepend("<a class='list-group-item'>"+audio.n+"</a>");
  $("#p a").first().attr("data-f",audio.f).attr("data-n",audio.n);
  if($("#p a").length > audio.c ) $("#p a").last().remove();
}
function ra() {
  audio.s = (audio.s ? false : true);
  localStorage.audio = JSON.stringify(audio);
  ui();
}
function dn() {
  if($("#a").get(0).volume >= 0.1 && $("#a").get(0).volume <= 1 ) {
    $("#a").get(0).volume = $("#a").get(0).volume - 0.1;
    vol($("#a").get(0).volume);
  }
}
function up() {
  if($("#a").get(0).volume >= 0 && $("#a").get(0).volume <= 0.9 ) {
    $("#a").get(0).volume = $("#a").get(0).volume + 0.1;
    vol($("#a").get(0).volume);
  }
}
function vol(req) {
    mesg(true,"Volume "+($("#a").get(0).volume * 100)+"%");
}
function prev() {
  var that=$("#r .active");
  if(that.length==0) {
    $("#r a:first").addClass("active");
  }else{
    that.removeClass("active");
    if(that.is(":first-child")) {
      $("#r a:last").addClass("active");
    }else{
      that.prev().addClass("active");
    }
  }
}
function next() {
  var that=$("#r .active");
  if(that.length==0) {
    $("#r a:first").addClass("active");
  }else{
    that.removeClass("active");
    if(that.is(":last-child")) {
      $("#r a:first").addClass("active");
    }else{
      that.next().addClass("active");
    }
  }
}
function playaudio() {
  // ??? //
  var init=1;
  $("#a").attr("src","").remove();
  if(audio.f!== undefined) {
    $("#body").append("<audio id='a'><source src='api.php?app=audio&f="+decodeURIComponent(audio.f).replace(/'/g,'%27')+"' type='audio/mp3'></audio>");
    $("#a").get(0).volume = audio.v;
    $("#a").get(0).addEventListener("ended",function() { 
      fo();
    });
    $("#a").get(0).addEventListener("canplay",function() { 
      if(init == "1") { 
        init=0; 
        $("#a").get(0).currentTime=audio.t;
        if(audio.p) $("#a").get(0).play();
      }
    });
  }else{
    fo();
  }
}
function ui() {
  $(".glyphicon-play, .glyphicon-pause").parent().parent().removeClass("hidden");
  if(audio.p) {
    $(".glyphicon-play").parent().parent().addClass("hidden");
  }else{
    $(".glyphicon-pause").parent().parent().addClass("hidden");
  }
  if(audio.s) {
    $(".glyphicon-random").addClass("text-info");
  }else{
    $(".glyphicon-random").removeClass("text-info");
  }
  var p = "&nbsp;&nbsp;&nbsp;";
  $("#t").html(p+audio.n);
  $("#l").html(p+audio.n);
  $("#playlist").html(audio.l);
  document.title = audio.n;
  timeline();
}
function reset() {
  $("#q").val("");
  $("#r,.col-sm-8 h5").html("");
}
function timeline() {
  if($("#a").length) {
    if($("#a").get(0).duration) {
      $("#t").css({"width": $("#a").get(0).currentTime / $("#a").get(0).duration * 100 + "%"});
      bookmark();
    }
    setTimeout(timeline,3000);
  }
}
function bookmark() {
  audio.t = $("#a").get(0).currentTime;
  localStorage.audio = JSON.stringify(audio);
}
$(document).on("click", "#tl",function(e){
  var percent = e.pageX / $(window).width();
  $("#a").get(0).currentTime = $("#a").get(0).duration * percent;
  bookmark();
  $("#t").css({"width":percent * 100 + "%"});
});
$(document).keyup(function(e) {
  if(!$("input").is(":focus")) {
    switch(e.keyCode) { 
      case 27:$("#q").focus();e.preventDefault();break;
      case 32:if($("#r .active").length==0){pp();}else{$("#r .active").click();}e.preventDefault();break;
      case 37:ra();e.preventDefault();break;
      case 38:if($("#r .active").length==0){up();}else{prev();}e.preventDefault();break;
      case 39:fo();e.preventDefault();break;
      case 40:if($("#r .active").length==0){dn();}else{next();}e.preventDefault();break;
    }
  }else{
    if(e.keyCode == 27) {$("input").blur();e.preventDefault();}
  }
});
var audio = localStorage.audio;
function audioready() {
  tmpl("#app","#tpl-audio",{});
    if(audio == null) {
      audio = {"c":20,"f":"","l":"Library","n":"","p":false,"s":false,"t":0,"v":0.5};
      localStorage.audio = JSON.stringify(audio);
      fo();
    }else{
      if(typeof audio === 'string') audio = JSON.parse(audio);
      playlist();
      playaudio();
      ui();
    }
}
function automagicready() {
  ajx({"req":"listWorkflows"},"listWorkflows");
}
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
      ajx({"req":"readWorkflows","n":workflow},"readWorkflows");
    break;
    case "remove":
    break;
    case "run":
      ajx({"req":"runWorkflows","n":workflow});
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
      ajx({"req":"deleteActions","f":actions},"deleteActions");
    break;
    case "read": readActions(actions); break;
  }
});
function listWorkflows(req) {
  tmpl("#app","#tpl-automagic",req);
}
function createWorkflows() {
  ajx({"req":"listActions"},"listActions");
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
    mesg(false,err.replace(/,\s*$/,""));
  }else{
    var data = [];
    $.each($(".actions .action tr"),function(k,v) {
      data.push($(this).data("action"));
    });
    ajx({"req":"createWorkflows","n":name,"d":data},"view");
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
  ajx({"req":"deleteWorkflows","n":$("#workflow-run").data("n")},"view");
}
function runWorkflows() {
  ajx({"req":"runWorkflows","n":$("#workflow-run").data("n")});
}
function listActions(req) {
  tmpl("#app .automagic > .actions .list","#tpl-automagic-actions",req);
}
function createActions() {
  if($("#app .automagic > .methods .list").html().length == 0) {
    ajx({"req":"listMethods"},"listMethods");
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
    mesg(false,err.replace(/,\s*$/,""));
  }else{
    data = $("#method-"+method+" textarea").val();
    if(data === undefined) {
      mesg(false,"Method Not Allowed");
    }else{
      if(data.length == 0) {
        mesg(false,"Data Required");
      }else{
        ajx({"req":"createActions","n":name,"m":method,"d":data},"view");
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
  tmpl("#app .automagic > .methods .list","#tpl-automagic-methods",req);
}
function readActions(req) {
  ajx({"req":"readActions","f":req},"readAction");
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
function chessready() {
  $("#app").html("chess");
}
function videoready() {
  $("#app").html("video");
}
var git = localStorage.git;
function gitready() {
  if(git == null) {
    git = {"name":"","email":"","repo":"","grep":""};
  }else{
    if(typeof git === 'string') git = JSON.parse(git);
  }
  gitinit();
}
function gitinit() {
	ajx({"req":"status"},"gitdone","gitfail");
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
  tmpl("#app","#tpl-git",{"projects":projects});
  if(git.repo.length > 0) {
    $("#projects li:not(.action)[data-repo='"+git.repo+"']").click();
  }
  if(git.grep.length > 0) {
	  $("#q").val(git.grep);
  }
}
function gitfail() {
	ajx({"req":"cache"});
  tmpl("#app","#tpl-git",{});
}
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
	ajx({"req":"cache"},"gitinit");
});
$(document).on("submit","form#clone",function(e) {
  e.preventDefault();
  ajx({"req":"clone","url":$("#url").val()},"gitinit");
});
$(document).on("submit","form.config",function(e) {
  e.preventDefault();
  ajx($(this).serialize());
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
    case "all":ajx({"req":"all","project":repo},"gitpush"); break;
    case "add":ajx({"req":"add","project":repo,"file":$(this).data("file")},"gitinit"); break;
    case "rem":ajx({"req":"rem","project":repo,"file":$(this).data("file")},"gitinit"); break;
    case "push": gitpush(repo); break;
    case "pull":ajx({"req":"pull","project":repo},"gitinit"); break;
    case "diff":ajx({"req":"diff","project":repo},"gitdiff"); break;
    case "log":ajx({"req":"log","project":repo},"gitlog"); break;
    case "branch":ajx({"req":"branch","project":repo},"gitbranch"); break;
    case "fetch":ajx({"req":"fetch","project":repo},"gitinit"); break;
  }
});
function gitpush(req) {
	ajx({"req":"push","project":req},"gitinit");
}
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
function gitlog(req) {
  var html = "<div id='overlay'><div class='panel panel-info'><div class='panel-heading'><a>&nbsp;</a>Log '"+req.repo+"'</div><div class='panel-body'>";
  $.each(req.log,function(k,v) {
    html += "<div class='line'>"+v.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\ /g,"&nbsp;")+"</div>";
  }); 
  html+= "</div></div></div>";
  $("body").append(html);
  $(".navbar, #app").addClass("hidden");
  $("#overlay .panel-body").css({"height":Math.ceil($(window).height() * 0.85)+"px"});
}
function gitbranch(req) {
  var html = "<div id='overlay'><div class='panel panel-info'><div class='panel-heading'><a>&nbsp;</a>Branches '"+req.repo+"'</div><div class='panel-body'>";
  $.each(req.branch,function(k,v) {
    html += "<div class='line'>"+v.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\ /g,"&nbsp;")+"</div>";
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
var home = localStorage.home
  , clock
  , mon = ["January","February","March","April","May","June","July","August","September","October","November","December"]
  , day = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]
  ;

function startTime() {
  if(app.app == "home") {
    var d = new Date()
      , h = (( d.getHours() + 11) % 12 + 1)
		  , m = d.getMinutes()
		  , o = (60 - d.getSeconds())*1000
		  , m = checkTime(m)
      , a = (d.getHours() < 12 ? "AM" : "PM")
      , c = h+":"+m+" "+a 
      ;
    clock = setTimeout(startTime, o);

    $("#date").html(day[d.getDay()]+", "+mon[d.getMonth()]+" "+d.getDate()+" "+d.getFullYear());

    today(d);
  
    $("#clock").html(c);
    document.title = c;
  }else{
    clearTimeout(clock);
  }
}
function checkTime(i) { return (i < 10 ? i = "0" + i : i); }
function monthly() {
  var first = new Date(home.y, home.m, 1).getDay()
    , last = new Date(home.y, home.m+1, 0).getDate()
    , cal = []
    , c = 0
    , w = 0
    ;
  cal[w] = [];
  for(i=0;i<first;i++) {
    cal[w].push('');
    c++;
  }
  for(i=1;i<=last;i++) {
    if(c%7==0) {
      w++;
      cal[w] = [];
    }
    cal[w].push(i);
    c++;
  }
  for(i=0;i<6;i++) {
    if(c%7==0) {
    //  w++;
      continue;
    }
    cal[w].push('');
    c++;
  }
  return {"year":home.y,"month":home.m,"title":mon[home.m]+" "+home.y,"calendar":cal};
}
function prev() {
  if(home.m == 0) {
    home.m = 11;
    home.y--;
  }else{
    home.m--;
  }
  localStorage.home = JSON.stringify(home);
  render();
}
function curr() {
  var t = new Date();
  home = {"m":t.getMonth(),"y":t.getFullYear()};
  localStorage.index = JSON.stringify(home);
  render();
}
function next() {
  if(home.m == 11) {
    home.m = 0;
    home.y++;
  }else{
    home.m++;
  }
  localStorage.home = JSON.stringify(home);
  render();
}
function today(req) {
  $("table#calendar td").removeClass("info");
  $("#day-"+req.getFullYear()+"-"+req.getMonth()+"-"+req.getDate()).addClass("info");
}
function homeready() {
  if(home == null) {
    var t = new Date();
    home = {"m":t.getMonth(),"y":t.getFullYear(),"t":t};
    localStorage.home = JSON.stringify(home);
  }else{
    if(typeof home === 'string') home = JSON.parse(home);
  }
  tmpl("#app","#tpl-home",{});
  render();
  ajx({},"profile","homefail");
}
function homefail() {
  page("login");
  $("#u").focus();
}
function render() {
  tmpl("#app .home.jumbotron","#tpl-calendar",monthly());
  clearTimeout(clock);
  startTime();
}
function profile(req) {
  $("#user").attr("href","/~"+user()+"/").attr("target","_blank");
  $("#user .user").html(user());
  page("profile")
}
function page(req) {
  $(".page").addClass("hidden");
  $("."+req).removeClass("hidden");
}
$(document).on("submit","form#userpass",function(e) {
  e.preventDefault();
  var u = $("#u").val()
    , p = $("#p").val()
    , e = false
    ;
  if(u.length > 0) {
  }else{
    e = true;
  }
  if(p.length > 0) {
  }else{
    e = true;
  }
  if(e) {
    mesg(false,"Required Fields");
  }else{
    $.ajax({
      type: "POST",
      url: "api.php",
      data: "u="+u+"&p="+p
    }).done(function(res) {
      profile(res);
    }).fail(function() {
      mesg(false,"Invalid Login");
    });
  }
});
function quotas() {
  ajx({"req":"quotas"},"quotasdone");
}
function quotasdone(req) {
  tmpl("#app .quota","#tpl-quotas",{"quotas":req});
}
function ssh() {
  ajx({"req":"ssh"},"sshdone");
}
function sshdone(req) {
  $("#app .ssh").html(req);
}
function add() {
  $("#app .import").removeClass("hidden");
}
function save() {
  var data = $(".import textarea").val();
  if(data.length > 0) {
    ajx({"req":"import","import":data},"savedone");
  }
}
function savedone() {
  $("#app .import").addClass("hidden");
  $("#app .import textarea").val("");
}
function logs() {
  ajx({"req":"logs"},"logsdone");
}
function logsdone(req) {
  $("#app .log").html("");
  $.each(req,function(k,v) {
    $("#app .log").append(v);
  });
}
function logout() {
  $.ajax({
    type: "POST",
    url: "api.php?app=auth",
    data: "req=logout"
  }).done(function(res) {
    page("login");
    $("#u").focus();
  });
}
function update() {
  $.ajax({
    type: "POST",
    url: "api.php?app=auth",
    data: "req=softwareupdate"
  }).done(function(res) {
    mesg(true,"Software Will Update");
  }).fail(function() {
    mesg(false,"Software Not Updated");
  });
}
function videoready() {
  $("#app").html("video");
}
      $(document).on("submit","form#new",function(e) {
        e.preventDefault();
        edit.n = $("#new .form-control").val();
        if(edit.n.length > 0) {
          ajx({"req":"create","f":edit.f,"n":edit.n},"createdone","createfail");
        }
      });
      function createdone(res) {
        $("#new .form-control").val("");
        edit.f = edit.f+(edit.f.length>0?"/":"")+edit.n;
        delete edit.n;
        editstate();
      }
      function createfail() {
        $("#new .form-control").val("");
        mesg(false,"Failed to Create File");
        delete edit.n;
      }
      function search(req) {
        ( req ? $(".form-control-feedback").removeClass("glyphicon-search").addClass("glyphicon-hourglass") : $(".form-control-feedback").removeClass("glyphicon-hourglass").addClass("glyphicon-search") );
      }
      $(document).on("click",".editor #r a:not(.disabled),#b a",function(e) {
        edit.f = $(this).data("f");
        editstate();
      });
      $(document).on("click",".navbar-brand",function(e) {
        switch ($(this).data("req")) {
        }
      });
      $(document).on("click","#p a,.editor #r a:not(.disabled) button:not(.update)",function(e) {
        $(".edit").removeClass("hidden");
        $("body").addClass("body-edit");
        $("#f").val(($(this).data("f")===undefined?$(this).parent().data("f"):$(this).data("f")));
        $("#n").html("Delete '"+($(this).data("n")===undefined?$(this).parent().data("n"):$(this).data("n"))+"'?");
        e.stopPropagation();
      });
      $(document).on("click",".editor #m button",function(e) {
        $("#m button").removeClass("active");
        $("#d,#l").addClass("hidden");
        $(this).addClass("active");
        if($(this).hasClass("l")) {
          edit.m = 0;
          $("#l").removeClass("hidden");
          //scroll to active
        }else{
          edit.m = 1;
          $("#d").removeClass("hidden").focus();
          //scroll to top
        }
        //localStorage.editor = JSON.stringify(edit);
      });
      function edittrash() {
        var f = $("#f").val();
        $.ajax({
          type: "POST",
          url: "api.php?app=editor",
          data: "req=delete&f="+f
        }).done(function(res) {
          $(".list-group").find("[data-f='"+f+"']").remove();
        }).fail(function() {
          mesg(false,"Failed to Delete '"+f+"'");
        });
        close();
      }
      function editreset() {
        $("#q").val("");
        editstate();
      }
      function editstate() {
        $("#q").val("");
        $("#b").html("<li><a href='javascript:void(0);' data-f=''><span class='glyphicon glyphicon-home'></span></a></li>");
        $("#m").addClass("hidden");
        if(edit.f.length > 0) {
          var b = edit.f.split("/")
            , f = ""
            ;
          $.each(b,function(k,v) {
            f=f+v;
            $("#b").append("<li><a href='javascript:void(0);' data-f='"+f+"'>"+v+"</a></li>");
            f=f+"/";
          });
        }
        $("#b li").last().html($("#b li:last a").html()).addClass("active").append(" <span class='hidden glyphicon glyphicon-floppy-disk'>");
        document.title = $("#b .active").text()+($("#b .active").text().trim().length>0?"- ":"")+"Editor"
        ajx({"req":"read","f":edit.f},"readdone");
      }
      function readdone(res) {
          var html = ""
            , val = ""
            , lines = ""
            , form = "<div class='list-group-item list-group-item-success'><form id='new'><div class='input-group'><input class='form-control' name='n' type='text' placeholder='Create'><div class='input-group-btn'><button class='btn btn-default'><span class='glyphicon glyphicon-plus-sign'></span> New</button></div></div></form></div>"
            ;
          $(window).scrollTop(0);
          if($.isArray(res)) {
            $("#m").removeClass("hidden");
            $("#m > button").removeClass("active");
            $.each(res,function(k,v) {
              lines+="<a href='javascript:void(0);' class='list-group-item'>"+v.replace(/</g,"&lt;").replace(/>/g,"&gt;")+"<span class='badge'>"+(k+1)+"</span></a>";
              val=val+v+"\n";
            });
            $("#d").val($.trim(val)).height(($(window).height()-$("nav").height()-$("#b").height()-150));
            $("#l").html(lines);
            if(edit.m) {
              $("#d").removeClass("hidden").focus();
              $("#m .d").addClass("active");
            }else{
              $("#l").removeClass("hidden");
              $("#m .l").addClass("active");
              $("#l a").first().addClass("active");
            }
            edit.d = $("#d").val().length;
          }else{
            $("#d,#l").addClass("hidden");
            if($.isPlainObject(res)) {
              html += form;
              html += editlist(res,false);
            }else{
              if(res.length === undefined) {
                html += form;
              }else{
                html += "<img class='img-responsive' src='"+res+"' />";
              }
              console.log(res.length);
            }
          }
          $("#r").html(html);
      }
      function editlist(req,t) {
        var res = ""
          , type = ""
          , keys = []
          , k
          , i
          , len
        ;
        for (k in req) {
          if (req.hasOwnProperty(k)) {
            keys.push(k);
          }
        }
        keys.sort();
        len = keys.length;
        for(i = 0; i < len; i++) {
          k = keys[i];
          switch(req[k].t) {
            default:type="file";break;
            case "d":type = "folder-open";break;
            case "i":type = "picture";break;
          }
          res += "<a class='list-group-item' data-n='"+req[k].n+"' data-f='"+k+"' title='"+k+"'><span class='glyphicon glyphicon-"+type+"'></span> "+(t ? k : req[k].n);
          res += "<button class='btn btn-danger'><span class='glyphicon glyphicon-trash'></span> Delete</button>";
          res += "</a>";
        }
        return res;
      }
      setInterval(function() { 
        if($("#d").is(":visible")) {
          var d = $("#d").val().length;
          if(edit.d != d) {
            edit.d = d;
            $.ajax({
              type: "POST",
              url: "api.php?app=editor",
              data: "req=update&f="+edit.f+"&d="+encodeURIComponent($("#d").val())
            }).done(function(res) {
              $("#b .active .glyphicon-floppy-disk").show().removeClass("hidden").delay(500).fadeOut(500);
            });
          }
        }
      }, 5000);
	var edit = localStorage.editor;
      function editorready() {
        if(edit == null) {
          edit = {"f":"","d":0,"m":1};
        }else{
          if(typeof edit === 'string') edit = JSON.parse(edit);
        }
        tmpl("#app","#tpl-edit",{});
        editstate();
      }
      
$(window).on("hashchange", function (e) {
    state(location.hash);
}).trigger("hashchange");
function state(req) {
  switch(req) {
    case "#audio":
    case "#automagic":
    case "#chess":
    case "#editor":
    case "#git":
    case "#video":
    case "#home":break;
    default: req = "#home"; history.pushState(null,null,req); break;
  }
  var uri = req.split("#");
  app.app = uri[1];
  document.title = "OS/"+app.app;
  window[uri[1]+"ready"]();
  //$.getScript("js/"+uri[1]+".js");

  $("#slidemenu ul.nav li").removeClass("active");
  $("#slidemenu ul.nav ."+uri[1]).addClass("active");
  if($(".navbar-toggle").hasClass("slide-active")) $(".navbar-toggle").click();
}
$(document).on("submit","form#search",function(e) {
  e.preventDefault();
  switch(app.app) {
    case "audio":
      if($("#q").val().length > 0) {
        if($("#q").val() != $("#q").data("q")) {
          $("#q").data("q",$("#q").val());
          $("#r").html("");
        }
        if($("#r .g").length == 0 ) {
          $("#q").blur();
          search(true);
          $.ajax({
            type: "POST",
          url: "api.php?app=audio",
            data: $(this).serialize()
          }).done(function(res) {
            search(false);
            $(".col-sm-8 h5").html("<span class='glyphicon glyphicon-search'></span> Results");
            var html = "";
            $.each(res,function(k,v){
              html += "<a class='g list-group-item' data-f='"+v.f+"' data-n='"+v.n+"' data-t='0'>"+v.n;
              html += "<button class='update btn btn-default btn-xs pull-right'><span class='glyphicon glyphicon-plus-sign'></span> Playlist</button>";
              html += "<button class='btn btn-default btn-xs pull-right'><span class='glyphicon glyphicon-pencil'></span> Edit</button>";
              html += "</a>";
            });
            $("#r").html(html);
            if($("#r .active").length==0) {$("#r a:first").addClass("active");}
          }).fail(function() {
            $("#r").html("<a class='g list-group-item disabled'>Your search - <b>"+$("#q").val()+"</b> - did not match any documents.</a>");
            google();
          });
        }else{
          if($("#r .y").length == 0) google();
        }
      }else{
        reset();
      }
    break;
    case "edit":
      if($("#q").val().length > 0) {
        $("#d").addClass("hidden");
        if($("#q").val() != $("#q").data("q")) {
          $("#q").data("q",$("#q").val());
          $("#r").html("");
          $("#b").html("<li><a href='javascript:void(0);' data-f=''><span class='glyphicon glyphicon-home'></span></a></li>");
        }
        if($("#r .g").length == 0 ) {
          $("#q").blur();
          search(true);
          $.ajax({
            type: "POST",
            url: "api.php?app=editor",
            data: $(this).serialize()
          }).done(function(res) {
            search(false);
            var html = "";
            html += editlist(res,true);
            $("#r").html(html);
          }).fail(function() {
            search(false);
            $("#r").html("<a class='g list-group-item disabled'>Your search - <b>"+$("#q").val()+"</b> - did not match any documents.</a>");
          });
        }
      }else{
        editreset();
      }
    break;
    case "git":
	    git.grep = $("#q").val();
	    localStorage.git = JSON.stringify(git);
      if($("#q").val().length > 0) {
		    var repo = $("#project .repo:not(.hidden)").data("repo");
		    ajx({"req":"grep","grep":git.grep,"project":repo},"gitgrep");
      }
    break;
  }
  console.log(app.app);
});

$(document).ready(function () {
  if(!$("#msg").length) $("body").append("<div id='msg'></div>");
  $("#slide-nav.navbar-inverse").after($('<div class="inverse" id="navbar-height-col"></div>'));
  $("#slide-nav.navbar-default").after($('<div id="navbar-height-col"></div>'));  
  var toggler = ".navbar-toggle"
    , pagewrapper = "#page-content"
    , navigationwrapper = ".navbar-header"
    , menuwidth = "100%" 
    , slidewidth = "80%"
    , menuneg = "-100%"
    , slideneg = "-80%"
    ;
  if($("#slidemenu").height() > $(window).height())
    $(".navbar.navbar-fixed-top.slide-active").css({"position":"fixed"});

  $("#slide-nav").on("click", toggler, function (e) {
    var selected = $(this).hasClass("slide-active");
    $("#slidemenu").stop().animate({
      left: selected ? menuneg : "0px"
    });
    $("#navbar-height-col").stop().animate({
      left: selected ? slideneg : "0px"
    });
    $(pagewrapper).stop().animate({
      left: selected ? "0px" : slidewidth
    });
    $(navigationwrapper).stop().animate({
      left: selected ? "0px" : slidewidth
    });
    $(this).toggleClass("slide-active", !selected);
    $("#slidemenu").toggleClass("slide-active");
    $("#page-content, .navbar, body, .navbar-header").toggleClass("slide-active");
  });
  var selected = "#slidemenu, #page-content, body, .navbar, .navbar-header";
  $(window).on("resize", function () {
    if ($(window).width() > 767 && $(".navbar-toggle").is(":hidden")) {
      $(selected).removeClass("slide-active");
    }
  });
});
function ajx(data,done,fail) {
  if(data===undefined) data = "";
  $.ajax({
    type: "POST",
    url: "api.php?app="+app.app,
    data: data
  }).done(function(res) {
    if(done===undefined) {
      mesg(true,res);
    }else{
      if($.isPlainObject(done)) {
        //$.each(done.req,function(k,v) {
        //}
        //switch(done.req) {
        //tmpl(src,dst,res);
        //}
      }else{
        window[done](res);
      }
    }
  }).fail(function(res) {
    if(fail===undefined) {
      mesg(false,res.responseText);
    }else{
      window[fail]();
    }
  });
}
function tmpl(src,dst,req) {
  $(src).html(Mustache.render($(dst).html(),req));
}
function user() {
  var e = encodeURIComponent("t") + "="
    , d = document.cookie.split(";")
    ;
  for(var i = 0; i < d.length; i++) {
    var c = d[i];
    while(c.charAt(0) === " ") c = c.substring(1, c.length);
    if(c.indexOf(e) === 0) {
      var t = decodeURIComponent(c.substring(e.length,c.length)).split(".")
        , u = $.parseJSON(atob(t[0]))
        ;
      return u.User;
    }
  }
  return null;
}
function mesg(req,res) {
  $("#e").removeClass("alert-success").addClass("alert-danger");
  if(req) $("#e").removeClass("alert-danger").addClass("alert-success");
  $("#e").html("<strong></strong> "+res+"!").show().removeClass("hidden").delay(5000).fadeOut(500);
}
/* New Framework */
//$(document).ready(function() { ready(); });
$(document).on("click",".select",function() {
  var c = $(this).data("c");
  $(this).parent().children().removeClass(c);
  $(this).addClass(c);
});
$(document).on("click",".mustache",function() { tpl($(this).data()); });
$(document).on("submit","form",function(e) { 
  //TODO tpl() option
  if(! $.isEmptyObject($(this).data())) {
    ajax($(this).data()); 
  }
  e.preventDefault();
  e.stopPropagation(); 
});
$(document).on("click",".ajax",function() { ajax($(this).data()); });
$(document).on("click",".remove",function() { $(this).parent().remove(); });
$(document).on("click",".toggle",function() {
  var c = $(this).data("c");
  if($(this).hasClass(c)) {
    $(this).removeClass(c);
  }else{
    $(this).addClass(c);
  }
});
function ready() {
  //TODO check that jquery is loaded
  //TODO check that mustache is loaded
  if(!$("#msg").length) $("body").append("<div id='msg'></div>");
  //TODO validate app
  ajax(app); 
}
function ajax(r) {
  if(r.at===undefined) r.at = app.at;
  if(r.au===undefined) r.au = app.au;
  if($(r.af).length) r.ad = $(r.af).serialize();
  if(r.dd===undefined) r.dd = app.dd;
  if(r.fd===undefined) r.fd = app.fd;
  $.ajax({
    type: r.at,
    url: r.au,
    data: r.ad,
  }).done(function(res) {
    if($(r.dd).length && $(r.dt).length) {
      tpl({"a":r.da,"d":r.dd,"t":r.dt,"r":res});
    }else{
      msg(true,res);
    }
  }).fail(function(res) {
    if($(r.fd).length && $(r.ft).length) {
      tpl({"a":r.fa,"d":r.fd,"t":r.ft,"r":res});
    }else{
      msg(false,res.statusText);
    }
  });
}
function tpl(r) { 
  //TODO revise logic
  if($(r.d).length && $(r.t).length) {
    if(r.a===undefined) {
      if(r.g===undefined) {
        $(r.d).html(Mustache.render($(r.t).html(),r.r)); 
      }else{
        $.get(r.g,function(t) {
          $(r.d).html(Mustache.render( $(t).filter(r.t).html(),r.r));
        });
      }
    }else{
      if(r.g===undefined) {
        $(r.d).append(Mustache.render($(r.t).html(),r.r)); 
      }else{
        $.get(r.g,function(t) {
          $(r.d).append(Mustache.render( $(t).filter(r.t).html(),r.r));
        });
      }
    }
  }
}
function msg(t,r) {
	var s = "Error"
    , c = {"position":"fixed","top":"1em","right":"1em","z-index":"2147483647","max-width":"97%","padding": "15px","margin-bottom": "20px","border": "1px solid transparent","border-radius": "4px","color":"#a94442","background-color": "#f2dede","border-color": "#ebccd1"}
    ;
	if(t) {
		s = "Success";
		c["color"] = "#3c763d";
		c["background-color"] = "#dff0d8";
		c["border-color"] = "#d6e9c6";
	}
	$("#msg").html("<strong>"+s+":</strong> "+r+"!").css(c).show().delay(5000).fadeOut(500);
}
