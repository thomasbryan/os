/*S*/
$(document).on("click",".select",function() {
  var c = $(this).data("c");
  $(this).parent().children().removeClass(c);
  $(this).addClass(c);
});
/*M*/
$(document).on("click",".mustache",function() { tpl($(this).data()); });
/*A*/
$(document).on("click",".ajax",function() { ajax($(this).data()); });
/*R*/
$(document).on("click",".remove",function() { $(this).parent().remove(); });
/*T*/
$(document).on("click",".toggle",function() {
  var c = $(this).data("c");
  if($(this).hasClass(c)) {
    $(this).removeClass(c);
  }else{
    $(this).addClass(c);
  }
});
$(document).on("submit","form",function(e) { 
  //TODO tpl() option
  ajax($(this).data()); 
  e.preventDefault();
  e.stopPropagation(); 
});
$(document).ready(function() { ready(); });
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
  }).done(function(d) {
    if($(r.dd).length) {
      tpl({"a":r.da,"d":r.dd,"g":r.dg,"t":r.dt,"r":d});
    }else{
      msg(true,d);
    }
  }).fail(function(f) {
    if($(r.fd).length) {
      tpl({"a":r.fa,"d":r.fd,"g":r.fg,"t":r.ft,"r":f});
    }else{
      msg(false,f.statusText);
    }
  });
}
function tpl(r) { 
  //TODO revise logic
  if($(r.d).length) {
    if(r.a===undefined) {
      if(r.g===undefined) {
        if($(r.t).length) {
          $(r.d).html(Mustache.render($(r.t).html(),r.r)); 
        }else{
          msg(false,"Template does not exist");
        }
      }else{
        //TODO additional warning messages
        $.get(r.g,function(t) {
          $(r.d).html(Mustache.render($(t).filter(r.t).html(),r.r));
        });
      }
    }else{
      if(r.g===undefined) {
        if($(r.t).length) {
          $(r.d).append(Mustache.render($(r.t).html(),r.r)); 
        }else{
          msg(false,"Template does not exist");
        }
      }else{
        //TODO additional warning messages
        $.get(r.g,function(t) {
          $(r.d).append(Mustache.render($(t).filter(r.t).html(),r.r));
        });
      }
    }
  }else{
    msg(false,"DOM element does not exist");
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
