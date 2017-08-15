$("form#search").on("submit",function(e) {
  e.preventDefault();
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
});
$("form#edit").on("submit",function(e) {
  e.preventDefault();
  action($(this).serialize());
  $(".edit").addClass("hidden");
  $("body").removeClass("body-edit");
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
  action("req=google&y="+$("#q").val(),"googledone","googlefail");
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
$(document).on("click","#r .g:not(.disabled) button.update",function(e) {
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
$(document).on("click","#r .g:not(.disabled)",function(e) {
  play($(this).data());
  reset();
});
$(document).on("click","#r .y:not(.disabled)",function(e) {
  $(this).addClass("disabled").removeClass("active");
  $.ajax({
    type: "POST",
      url: "api.php?app=audio",
    data: "req=download&d="+$(this).data("d")+"&q="+$("#q").val()
  }).done(function(res) {
    reset();
  }).fail(function() {
    //TODO check not returning something??
    msg(false,"Failed to Download");
    reset();
  });
});
$(document).on("click","#r .l button",function(e) {
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
    msg(false,"Unable to Delete '"+data.l+"' Playlist");
  });
  e.stopPropagation();
});
$(document).on("click","#r .l",function(e) {
  if($(this).data("l")) {
    audio.l = $(this).data("l");
    localStorage.audio = JSON.stringify(audio);
    reset();
    ui();
  }
});
$(document).on("click",".navbar-brand",function(e) {
  switch ($(this).data("req")) {
    case "pp":pp();break;
    case "fo":fo();break;
    case "ra":ra();break;
    case "dn":dn();break;
    case "up":up();break;
    case "li":list();break;
    case "ce":close();break;
    case "tn":trash();break;
  }
});
$(document).on("click","#p a,#r .g:not(.disabled) button:not(.update)",function(e) {
  $(".edit").removeClass("hidden");
  $("body").addClass("body-edit");
  $("#f").val(($(this).data("f")===undefined?$(this).parent().data("f"):$(this).data("f")));
  $("#n").val(($(this).data("n")===undefined?$(this).parent().data("n"):$(this).data("n"))).focus();
  e.stopPropagation();
});
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
  action(audio,"fodone","fofail");
}
function fodone(res) {
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
  msg(false,"Failed to Initialize");
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
function list() {
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
      msg(false,"Failed Playlist Retreival");
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
    msg(false,"Failed to Delete File");
  });
}
function playlist() {
  $("#p").prepend("<a class='list-group-item'>"+audio.n+"</a>");
  $("#p a").first().attr("data-f",audio.f).attr("data-n",audio.n);
  if($("#p a").length > 20 ) $("#p a").last().remove();
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
    msg(true,"Volume "+($("#a").get(0).volume * 100)+"%");
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
    $("#body").append("<audio id='a'><source src='"+decodeURIComponent(audio.f).replace(/'/g,'%27')+"' type='audio/mp3'></audio>");
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
  $(".glyphicon-play, .glyphicon-pause").parent().removeClass("hidden");
  if(audio.p) {
    $(".glyphicon-play").parent().addClass("hidden");
  }else{
    $(".glyphicon-pause").parent().addClass("hidden");
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
$(document).ready(function() {
  var templateData = {
    "playlist": [
      ],
  };
  $.get("htm/audio.htm", function(templates) {
    var template = $(templates).filter('#tpl-audio').html();
    $("#app").html(Mustache.render(template, templateData));
    if(audio == null) {
      audio = {"f":"","l":"Library","n":"","p":false,"s":false,"t":0,"v":0.5};
      localStorage.audio = JSON.stringify(audio);
      fo();
    }else{
      audio = JSON.parse(audio);
      playlist();
      playaudio();
      ui();
    }
  });
});
