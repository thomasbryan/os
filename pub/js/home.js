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
$(document).ready(function() {
  if(home == null) {
    var t = new Date();
    home = {"m":t.getMonth(),"y":t.getFullYear(),"t":t};
    localStorage.home = JSON.stringify(home);
  }else{
    if(typeof home === 'string') home = JSON.parse(home);
  }
  $.get("htm/home.htm", function(templates) {
    var template = $(templates).filter('#tpl-home').html();
    $("#app").html(Mustache.render(template, {}));
    render();
    $.ajax({
      type: "POST",
      url: "api.php?app=auth"
    }).done(function(res) {
      profile(res);
    }).fail(function() {
      page("login");
      $("#u").focus();
    });
  });
});
function render() {
  $.get("htm/home.htm", function(templates) {
    var template = $(templates).filter('#tpl-calendar').html();
    $("#app .home.jumbotron").html(Mustache.render(template, monthly()));
    clearTimeout(clock);
    startTime();
  });
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
    msg(false,"Required Fields");
  }else{
    $.ajax({
      type: "POST",
      url: "api.php",
      data: "u="+u+"&p="+p
    }).done(function(res) {
      profile(res);
    }).fail(function() {
      msg(false,"Invalid Login");
    });
  }
});
function quotas() {
  action({"req":"quotas"},"quotasdone");
}
function quotasdone(req) {
  console.log(req);
  $.get("htm/home.htm", function(templates) {
    var template = $(templates).filter('#tpl-quotas').html();
    $("#app .quota").html(Mustache.render(template,{"quotas":req}));
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
    msg(true,"Software Will Update");
  }).fail(function() {
    msg(false,"Software Not Updated");
  });
}
