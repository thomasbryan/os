$(document).ready(function() {
  //
    var templateData = {
      "name":"thomas",
      "timeNow":"now",
    };
    $.get("htm/login.htm", function(templates) {
      // Fetch the <script /> block from the loaded external
      // template file which contains our greetings template.
      var template = $(templates).filter('#tpl-login').html();
      $("#app").html(Mustache.render(template, templateData));
        if(window.location.href.indexOf("http://")==0) $("#w").removeClass("hidden");
    });
});

      $(document).ready(function() {
        if(window.location.href.indexOf("http://")==0) $("#w").removeClass("hidden");

        $.ajax({
          type: "POST",
          url: "api.php?app=auth"
        }).done(function(res) {
          profile(res);
        }).fail(function() {
          login();
        });

      });
      function profile(req) {
        page("profile")
        if(req.token) {
          var p = req.token.split(".");
          var u = $.parseJSON(atob(p[0]));
          $("#profile .user").html("<a target='_blank' href='/~"+u.User+"/'>"+u.User+"</a>");
        }
        if(req.pub) {
          $("#profile .pub").html("<span>"+req.pub+"</span>");
        }
      }
      function login() {
        page("login");
        $("#u").focus();
      }
      function page(req) {
        $(".body-panel > div").addClass("hidden");
        $("#"+req).removeClass("hidden");
      }
      $("form").on("submit",function(e) {
        e.preventDefault();
        console.log("hi");
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
            data: "req=read&u="+u+"&p="+p
          }).done(function(res) {
            profile(res);
          }).fail(function() {
            msg(false,"Invalid Login");
          });
        }
      });
      function createCookie(n,v,d) {
        var e = ""
          , t = new Date()
          , s = ""
          ;
        if(d) {
          t.setTime(t.getTime() + (d * 24 * 60 * 60 * 1000));
          e = "; expires=" + t.toGMTString();
        }
        if(window.location.href.indexOf("https://")==0) s = "secure";
        document.cookie = encodeURIComponent(n)+"="+encodeURIComponent(v)+e+"; path=/;"+s;
      }
      function readCookie(n) {
        var e = encodeURIComponent(n) + "="
          , d = document.cookie.split(';')
          ;
        for(var i = 0; i < d.length; i++) {
          var c = d[i];
          while(c.charAt(0) === ' ') c = c.substring(1, c.length);
          if(c.indexOf(e) === 0) return decodeURIComponent(c.substring(e.length,c.length));
        }
        return null;
      }
      function updateCookie(n,v,d) {
        deleteCookie(n);
        createCookie(n,v,d);
      }
      function deleteCookie(n) { createCookie(n,"",-1); }
