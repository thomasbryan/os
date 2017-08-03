var home = localStorage.home
  , mon = ["January","February","March","April","May","June","July","August","September","October","November","December"]
  , day = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]
  ;

function startTime() {
  console.log(app);
  if(app.app == "home") {
    var d = new Date()
      , h = (( d.getHours() + 11) % 12 + 1)
		  , m = d.getMinutes()
		  , o = (60 - d.getSeconds())*1000
		  , m = checkTime(m)
		  , t = setTimeout(startTime, o)
      , a = (d.getHours() < 12 ? "AM" : "PM")
      , c = h+":"+m+" "+a
      ;
    console.log(d);

    $("#date").html(day[d.getDay()]+", "+mon[d.getMonth()]+" "+d.getDate()+" "+d.getFullYear());

    today(d);
  
    $("#clock").html(c);

    document.title = c;
  }else{
    console.log("whats");
    console.log(app);

  }
}
function checkTime(i) { return (i < 10 ? i = "0" + i : i); }
function buildCal() {
      var t = new Date();
      if(home == null) {
        home = {"m":t.getMonth(),"y":t.getFullYear()};
        localStorage.home = JSON.stringify(home);
      }else{
        if(typeof home === 'string') home = JSON.parse(home);
      }
      var first = new Date(home.y, home.m, 1).getDay()
        , last = new Date(home.y, home.m+1, 0).getDate()
        , html = "<tr>"
        , c = 0
        ;
      $("#title").html(mon[home.m]+" "+home.y);
      for(i=0;i<first;i++) {
        html+= "<td></td>";
        c++;
      }
      for(i=1;i<=last;i++) {
        if(c%7==0) {
          html+="</tr><tr>";
        }
        html+= "<td id='day-"+home.y+"-"+home.m+"-"+i+"'>"+i+"</td>";
        c++;
      }
      for(i=0;i<6;i++) {
        if(c%7==0) {
          html+="</tr>";
          continue;
        }
        html+= "<td></td>";
        c++;
      }
      $("#calendar tbody").html(html);
      today(t);
}
function prev() {
      if(home.m == 0) {
        home.m = 11;
        home.y--;
      }else{
        home.m--;
      }
      localStorage.home = JSON.stringify(home);
      buildCal();
}
function curr() {
			var t = new Date();
      home = {"m":t.getMonth(),"y":t.getFullYear()};
      localStorage.index = JSON.stringify(home);
      buildCal();
}
function next() {
      if(home.m == 11) {
        home.m = 0;
        home.y++;
      }else{
        home.m++;
      }
      localStorage.home = JSON.stringify(home);
      buildCal();
}
function today(req) {
      $("table#calendar td").removeClass("info");
      $("#day-"+req.getFullYear()+"-"+req.getMonth()+"-"+req.getDate()).addClass("info");
}
$(document).ready(function() {
  console.log(app);
  var templateData = {
    "name":"thomas",
    "timeNow":"now",
  };
  $.get("htm/home.htm", function(templates) {
    var template = $(templates).filter('#tpl-home').html();
    $("#app").html(Mustache.render(template, templateData));
  });
  console.log("buildCal");
  buildCal();
  console.log("startTime");
  startTime();
});
