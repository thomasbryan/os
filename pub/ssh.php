<?php $api = new API(); ?><!DOCTYPE html><html lang="en">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="favicon.ico" rel="icon" type="image/x-icon">
  <title>SSH</title>
  <meta charset="utf-8">
	<style>
    body { padding-top: 70px; }
    .navbar .form-group { margin: 9px 0; }
    button { width: 100%; }
    #or { margin-bottom: 15px; }
  </style>
</head>
<body>
  <form id="data" class="" method="POST" enctype="multipart/form-data">
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
				<div class="form-group col-xs-6 pull-left">
          <a href="javascript:void(0);" id="back" class="btn btn-primary col-xs-12 hidden">
            <span class="glyphicon glyphicon-arrow-left"></span>
            Back
          </a>
			  </div>
        <div class="form-group col-xs-6 pull-right">
          <a href="javascript:void(0);" id="next" class="btn btn-primary col-xs-12 visible-sm visible-xs">
            Next
            <span class="glyphicon glyphicon-arrow-right"></span>
          </a>
          <button id="submit" type="submit" name="submit" class="btn btn-primary hidden-sm hidden-xs">
            Submit
            <span id="hourglass" class="glyphicon glyphicon-share"></span>
          </button>
			  </div>
      </div>
    </nav>
    <div class="container-fluid">
      <div class="row-fluid">
        <div id="req" class="col-xs-12">
            <div id="profile" class="col-md-4">
              <div id="profiles"></div>
					    <div class="form-group">
        		    <input id="user" type="text" name="user" placeholder="USER" class="form-control" />
					    </div>
					    <div class="form-group">
        		    <input id="host" type="text" name="host" placeholder="HOST" class="form-control" />
					    </div>
            </div>
            <div id="password" class="col-md-4 hidden-sm hidden-xs">
					    <div class="profile text-center"></div>
					    <div class="form-group">
        			  <input id="pass" type="password" name="pass" placeholder="PASS" class="form-control" />
					    </div>
              <div id="or" class="text-center">- or -</div>
					    <div class="form-group">
        			  <input id="key" type="file" name="key" placeholder="KEY" class="form-control" />
					    </div>
					    <div class="form-group">
        			  <input id="phrase" type="password" name="phrase" placeholder="PHRASE" class="form-control" />
					    </div>
            </div>
            <div id="command" class="col-md-4 hidden-sm hidden-xs">
					    <div class="profile text-center"></div>
              <div id="commands"></div>
					    <div class="form-group">
        			  <input id="cmd" type="text" name="cmd" placeholder="CMD" class="form-control" />
					    </div>
            </div>
        </div>
        <div id="res" class="col-xs-12 hidden"></div>
      </div>
    </div>
  </form>
  <script src="js/jquery.min.js"></script>
  <script>
    $("form#data").on("submit", function(e) {
	    e.preventDefault();
      var user = $("#user").val()
        , host = $("#host").val()
        , pass = $("#pass").val()
        , key = $("#key").prop("files")[0]
        , cmd = $("#cmd").val()
        , data = new FormData()
        ;
      if($("#next").is(":visible")) $("#next").click();
      if(user.length > 0 && host.length > 0 && ( pass.length > 0 || $("#key").val().length > 0 ) && cmd.length > 0) {
        $("#submit,#req").addClass("hidden");
        $("#back,#res").removeClass("hidden");
        $("#res").html("<span class='glyphicon glyphicon-hourglass'></span>");
        data.append("host",host);
        data.append("user",user);
        data.append("pass",pass);
        data.append("key", key);
        data.append("phrase",$("#phrase").val());
        data.append("cmd",cmd);
        if(app.profile!==undefined) {
          if(app.profiles[app.profile].profile == user+"@"+host) {
            app.profiles[app.profile].count++
          }else{
            profiles(user+"@"+host);
          }
        }else{
          profiles(user+"@"+host);
        }
        if(app.command!==undefined) {
          if(app.commands[app.command].command == cmd) {
            app.commands[app.command].count++
          }else{
            commands(cmd);
          }
        }else{
          commands(cmd);
        }
        localStorage.ssh = JSON.stringify(app);
        state();
        $.ajax({
          dataType: "jsonp",
          cache: false,
          contentType: false,
          processData: false,
          data: data,
          type: "post",
          error: function() {
            if(!$("#res").hasClass("hidden")) {
              $("#res").html("<code>Error</code>");
            }
          },
          success: function(res) {
            if(!$("#res").hasClass("hidden")) {
              $("#res").html("<pre></pre>");
              $.each(res,function(k,v) {
                $("#res pre").append(v+"\n");
              });
            }
          }
        });
      }
    });
    $(document).on("click","#back",function() {
      var curr = $(".col-md-4:visible").attr("id");
      switch(curr) {
        case "password":
          $("#profile").removeClass("hidden-sm hidden-xs");
          $("#password").addClass("hidden-sm hidden-xs");
          $("#back").addClass("hidden");
          $("#user").focus();
        break;
        case "command":
          $("#password").removeClass("hidden-sm hidden-xs");
          $("#command").addClass("hidden-sm hidden-xs");
          $("#submit").addClass("hidden-sm hidden-xs");
          $("#next").addClass("visible-sm visible-xs");
          $("#next").removeClass("hidden");
        break;
        default:
          $("#submit,#req").removeClass("hidden");
          $("#res").addClass("hidden");
          if($(".col-md-4:visible").attr("id") != "command") $("#back").addClass("hidden");
        break;
      }
    });
    $(document).on("click","#next",function() {
      var curr = $(".col-md-4:visible").attr("id");
      switch(curr) {
        case "profile":
          $("#profile .form-group").removeClass("has-error");
          if($("#user").val().length > 0 && $("#host").val().length > 0) {
            $("#profile").addClass("hidden-sm hidden-xs");
            $("#password").removeClass("hidden-sm hidden-xs");
            $("#back").removeClass("hidden");
            $(".profile").html("<h4>"+$("#user").val()+"@"+$("#host").val()+"</h4>");
          }else{
            $("#profile .form-group").addClass("has-error");
          }
          empty();
        break;
        case "password":
          if($("#pass").val().length > 0 || $("#key").val().length > 0) {
            $("#password").addClass("hidden-sm hidden-xs");
            $("#command").removeClass("hidden-sm hidden-xs");
            $("#submit").removeClass("hidden-sm hidden-xs");
            $("#next").removeClass("visible-sm visible-xs");
            $("#next").addClass("hidden");
          }else{
            $("#profile .form-group").addClass("has-error");
            empty();
          }
        break;
      }
    });
    $(document).on("click","#profiles a",function() {
      var profiles = $(this).text().split("@");
      if(profiles.length == 2) {
        app.profile = $(this).data("index");
        $("#user").val(profiles[0]);
        $("#host").val(profiles[1]);
      }
      if($("#next").is(":visible")) $("#next").click();
    });
    $(document).on("click","#commands a",function() {
      app.command = $(this).data("index");
      $("#cmd").val($(this).text());
      $("#submit").click();
    });
    var app = localStorage.ssh;
    $(document).ready(function() {
      if(app == null) {
        app = {"profiles":[],"commands":[]};
      }else{
        app = JSON.parse(app);
      }
      state();
    });
    function state() {
      delete app.profile;
      delete app.command;
      if(app.profiles.length > 0) {
        $("#profiles").html("<div class='list-group'></div>");
        $.each(app.profiles.sort(vsort("-count")) ,function(k,v) {
          $("#profiles .list-group").append("<a href='javascript:void(0);' class='list-group-item' data-index='"+k+"'>"+v.profile);
        });
      }
      if(app.commands.length > 0) {
        $("#commands").html("<div class='list-group'></div>");
        $.each(app.commands.sort(vsort("-count")) ,function(k,v) {
          $("#commands .list-group").append("<a href='javascript:void(0);' class='list-group-item' data-index='"+k+"'>"+v.command);
        });
      }
    }
    function vsort(req) {
      var sort = 1;
      if(req[0] === "-") {
        sort = -1;
        req = req.substr(1);
      }
      return function (a,b) {
        var res = (a[req] < b[req]) ? -1 : (a[req] > b[req]) ? 1 : 0;
        return res * sort;
      }
    }
    function empty() {
      $("input").each(function(){
        if($(this).val() == ""){
          this.focus();
          return false;
        }
      });
    }
    function profiles(req) {
      var res = false;
      $.each(app.profiles,function(k,v) {
        if(v.profile == req) {
          app.profiles[k].count++;
          res = true;
        }
      });
      if(!res) {
        app.profiles.push({"profile":req,"count":1});
      }
    }
    function commands(req) {
      var res = false;
      $.each(app.commands,function(k,v) {
        if(v.command == req) {
          app.commands[k].count++;
          res = true;
        }
      });
      if(!res) {
        app.commands.push({"command":req,"count":1});
      }
    }
  </script>
</body></html>
<?php
class API {
  private $path = '../phpseclib/phpseclib';
  function __construct() {
    if($_SERVER['REQUEST_METHOD']==='POST') {
      $res = false;
      $ssh = $this->path.'/Net/SSH2.php';
      $rsa = $this->path.'/Crypt/RSA.php';
      if(file_exists($ssh)&&file_exists($rsa)) {
        set_include_path(get_include_path().PATH_SEPARATOR.$this->path);
        include($ssh);
        include($rsa);

        $host = $_POST['host'];
        $user = $_POST['user'];
        if(!empty($user) && !empty($host)) {
          $pass = $_POST['pass'];
			    $key = '';
			    if(isset($_FILES['key'])) {
      	    $key = file_get_contents($_FILES['key']['tmp_name']);
			    }
          $phrase = $_POST['phrase'];
          $cmd  = $_POST['cmd'];
          $ssh = new Net_SSH2($host);
          if(empty($key)) {
            if(!$ssh->login($user,$pass)) {
			        $this->json($res);
            }
          }else{
            $rsa = new Crypt_RSA();
            $rsa->setPassword($phrase);
            $rsa->loadKey($key);
            if (!$ssh->login($user, $rsa)) {
			        $this->json($res);
            }
          }
          //ssh->settimeout(1);
          $res = explode("\n",$ssh->exec($cmd));
        }
      }
			$this->json($res);
    }
  }
  private function json($req=false) {
    if($req) {
      header("Content-type: application/json");
      if(array_key_exists('callback', $_GET) == TRUE){
        $req=json_encode($req);
        print $_GET['callback']."(".$req.")"; 
      }else{
        echo json_encode($req);
      }
    }else{
      header('HTTP/1.0 404 Not Found');
    }
    exit;
  } 
} #/API ?>
