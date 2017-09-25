var app = {};
$(window).on("hashchange", function (e) {
    state(location.hash);
}).trigger("hashchange");
function state(req) {
  switch(req) {
		case "#login":
    case "#audio":
    case "#chess":
    case "#edit":
    case "#git":
    case "#post":
    case "#ssh":
    case "#video":
    case "#home":break;
    default: req = "#home"; history.pushState(null,null,req); break;
  }
  var uri = req.split("#");
  app.app = uri[1];
  document.title = "OS/"+app.app;
  $.getScript("js/"+uri[1]+".js");

  $("#slidemenu ul.nav li").removeClass("active");
  $("#slidemenu ul.nav ."+uri[1]).addClass("active");
  if($(".navbar-toggle").hasClass("slide-active")) $(".navbar-toggle").click();
}
$(document).on("submit","form#search",function(e) {
  e.preventDefault();
  console.log(app.app);
});

$(document).ready(function () {
  //stick in the fixed 100% height behind the navbar but don't wrap it
  $('#slide-nav.navbar-inverse').after($('<div class="inverse" id="navbar-height-col"></div>'));
  $('#slide-nav.navbar-default').after($('<div id="navbar-height-col"></div>'));  
  // Enter your ids or classes
  var toggler = '.navbar-toggle';
  var pagewrapper = '#page-content';
  var navigationwrapper = '.navbar-header';
  var menuwidth = '100%'; // the menu inside the slide menu itself
  var slidewidth = '80%';
  var menuneg = '-100%';
  var slideneg = '-80%';
  if($("#slidemenu").height() > $(window).height())
    $(".navbar.navbar-fixed-top.slide-active").css({"position":"fixed"});

  $("#slide-nav").on("click", toggler, function (e) {
    var selected = $(this).hasClass('slide-active');
    $('#slidemenu').stop().animate({
      left: selected ? menuneg : '0px'
    });
    $('#navbar-height-col').stop().animate({
      left: selected ? slideneg : '0px'
    });
    $(pagewrapper).stop().animate({
      left: selected ? '0px' : slidewidth
    });
    $(navigationwrapper).stop().animate({
      left: selected ? '0px' : slidewidth
    });
    $(this).toggleClass('slide-active', !selected);
    $('#slidemenu').toggleClass('slide-active');
    $('#page-content, .navbar, body, .navbar-header').toggleClass('slide-active');
  });
  var selected = '#slidemenu, #page-content, body, .navbar, .navbar-header';
  $(window).on("resize", function () {
    if ($(window).width() > 767 && $('.navbar-toggle').is(':hidden')) {
      $(selected).removeClass('slide-active');
    }
  });
});
function action(data,done,fail) {
  if(data===undefined) data = "";
  $.ajax({
    type: "POST",
    url: "api.php?app="+app.app,
    data: data
  }).done(function(res) {
    if(done===undefined) {
      msg(true,res);
    }else{
      window[done](res);
    }
  }).fail(function(res) {
    if(fail===undefined) {
      msg(false,res.responseText);
    }else{
      window[fail]();
    }
  });
}
function user() {
  var e = encodeURIComponent("t") + "="
    , d = document.cookie.split(';')
    ;
  for(var i = 0; i < d.length; i++) {
    var c = d[i];
    while(c.charAt(0) === ' ') c = c.substring(1, c.length);
    if(c.indexOf(e) === 0) {
      var t = decodeURIComponent(c.substring(e.length,c.length)).split(".")
        , u = $.parseJSON(atob(t[0]))
        ;
      return u.User;
    }
  }
  return null;
}
function msg(req,res) {
  $("#e").removeClass("alert-success").addClass("alert-danger");
  if(req) $("#e").removeClass("alert-danger").addClass("alert-success");
  $("#e").html("<strong></strong> "+res+"!").show().removeClass("hidden").delay(5000).fadeOut(500);
}