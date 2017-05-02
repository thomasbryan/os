<?php $api = new API(); ?><!DOCTYPE html><html lang="en">
<head>
  <title>SSH</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
	<style>
    body { padding-top: 70px; }
    .navbar .form-group { margin: 9px 0; }
    button { width: 100%; }
  </style>
</head>
<body>
  <form id="data" class="" method="POST" enctype="multipart/form-data">
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
				<div class="form-group col-xs-6 pull-left">
          <div id="back" class="btn btn-primary col-xs-12 hidden">
            <span class="glyphicon glyphicon-arrow-left"></span>
            Back
          </div>
			  </div>
				<div class="form-group col-xs-6 pull-right">
          <button id="submit" type="submit" name="submit" class="btn btn-primary">
            Submit
            <span id="hourglass" class="glyphicon glyphicon-share"></span>
          </button>
			  </div>
      </div>
    </nav>
    <div class="container-fluid">
      <div class="row-fluid">
        <div id="req" class="col-xs-12">
            <div class="col-sm-4">
              <div id="profiles"></div>
					    <div class="form-group">
        		    <input id="user" type="text" name="user" placeholder="USER" class="form-control" />
					    </div>
					    <div class="form-group">
        		    <input id="host" type="text" name="host" placeholder="HOST" class="form-control" />
					    </div>
            </div>
            <div class="col-sm-4">
					    <div class="form-group">
        			  <input id="pass" type="password" name="pass" placeholder="PASS" class="form-control" />
					    </div>
					    <div class="form-group">
        			  <input id="key" type="file" name="key" placeholder="KEY" class="form-control" />
					    </div>
					    <div class="form-group">
        			  <input id="phrase" type="password" name="phrase" placeholder="PHRASE" class="form-control" />
					    </div>
            </div>
            <div class="col-sm-4">
              <div id="commands"></div>
					    <div class="form-group">
        			  <input id="cmd" type="text" name="cmd" placeholder="CMD" class="form-control" />
					    </div>
            </div>
        </div>
        <div id="res" class="col-xs-12 hidden">
        </div>
      </div>
    </div>
  </form>
  <script src="js/jquery.min.js"></script>
  <script>
    $("form#data").on("submit", function(e) {
	    e.preventDefault();
      var user = $("#user").val()
        , host = $("#host").val()
        , cmd = $("#cmd").val()
        , key = $("#key").prop("files")[0]
        , data = new FormData()
        ;
      if(user.length > 0 && host.length > 0 && cmd.length > 0) {
        $("#submit,#req").addClass("hidden");
        $("#back,#res").removeClass("hidden");
        $("#res").html("<span class='glyphicon glyphicon-hourglass'></span>");
        data.append("host",host);
        data.append("user",user);
        data.append("pass",$("#pass").val());
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
      $("#submit,#req").removeClass("hidden");
      $("#back,#res").addClass("hidden");
    });
    $(document).on("click","#profiles a",function() {
      var profiles = $(this).text().split("@");
      if(profiles.length == 2) {
        app.profile = $(this).data("index");
        $("#user").val(profiles[0]);
        $("#host").val(profiles[1]);
      }
    });
    $(document).on("click","#commands a",function() {
      app.command = $(this).data("index");
      $("#cmd").val($(this).text());
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
    /*
      TODO 
      step 1: pick profile / enter user and host
      step 2: enter password / passphrase / choose key
      step 3: pick command / enter command
      step 4: results
    */
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
