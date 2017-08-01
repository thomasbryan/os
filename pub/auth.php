<?php $api = new API(); ?><!DOCTYPE html><html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Authentication/Authorization</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
      body { padding: 70px 0; overflow: hidden; }
      .form-group { margin: 9px; }
      .list-group:last-child { padding-right: 0; }
      .list-group { margin-bottom: 0; }
      .list-group a:last-child { margin-bottom: 20px; }
      .list-group-item { cursor: pointer; overflow: hidden; white-space: nowrap; }
      .list-group-item img { position:absolute; top:0; right:0; height: 100%; }
      @media (max-width: 767px) {
        body { padding-top: 100px; }
        .list-group { padding-right: 0; }
      }
      #e { position: fixed; right: 1em; z-index: 100; }
      #profile .pub {
        overflow-wrap: break-word;
        word-wrap: break-word;
        -ms-word-break: break-all;
        word-break: break-all;
        word-break: break-word;
        -ms-hyphens: auto;
        -moz-hyphens: auto;
        -webkit-hyphens: auto;
        hyphens: auto;
      }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <div class="navbar-brand">AUTH</div>
        </div>
        <div id="w" class="pull-right navbar-brand hidden"><span class="text-danger glyphicon glyphicon-warning-sign"></span> <span class="text-danger">Not Secure!</span></div>
      </div>
    </nav>
    <div class="container-fluid">
      <div class="row-fluid">
        <div id="e" class="hidden alert alert-danger" role="alert"> </div>
        <div class="col-sm-8 col-sm-offset-2">
          <div class="panel panel-info">
            <div class="panel-heading"><span class="glyphicon glyphicon-user"></span> User Profile</div>
            <div class="panel-body">
              <div id="login" class="hidden">
                <form>
                <p class='container'>
                <div class='row-fluid'>
                <input type='text' id='u' class='form-control' placeholder='Username' />
                </div >
                </p >
                <p class='container'>
                <div class='row-fluid'>
                <input type='password' id='p' class='form-control' placeholder='Password' />
                </div >
                </p >
                <p class='container'>
                <div class='row-fluid'>
                <input type='submit' class='btn btn-primary col-xs-12' id='l' value='Login' />
                </div >
                </p ></form>
              </div>
              <div id="profile" class="hidden table-responsive">
                <table class="table">
                  <tr>
                    <th>User</th>
                    <th><span class="glyphicon glyphicon-user"></span></th>
                    <td class="user"></td>
                  </tr>
                  <tr>
                    <th>SSH</th>
                    <th><span class="glyphicon glyphicon-certificate"></span></th>
                    <td class="pub"></td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
      function err(req) {
        $("#e").html("<strong>Error:</strong> "+req+"!").show().removeClass("hidden").delay(5000).fadeOut(500);
      }
      $(document).ready(function() {
        if(window.location.href.indexOf("http://")==0) $("#w").removeClass("hidden");
        $.ajax({
          type: "POST",
        }).done(function(res) {
          profile(res);
        }).fail(function() {
          login();
        });
      });
      function profile(req) {
        app("profile")
        if(req.token) {
          updateCookie("t",req.token);
          var p = req.token.split(".");
          var u = $.parseJSON(atob(p[0]));
          $("#profile .user").html(u.User);
        }
        if(req.pub) {
          $("#profile .pub").html("<span>"+req.pub+"</span>");
        }
      }
      function login() {
        app("login");
        $("#u").focus();
      }
      function app(req) {
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
          err("Required Fields");
        }else{
          $.ajax({
            type: "POST",
            data: "req=read&u="+u+"&p="+p
          }).done(function(res) {
            profile(res);
          }).fail(function() {
            err("Invalid Login");
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
    </script>
  </body>
</html><?php class API {
  /*
      git remote show origin


  */
  private $conf = '../conf.ini';
  private $auth = array();
  function __construct() {
    if($_SERVER['REQUEST_METHOD']==='POST') {
      //set auth key ???
      $res = false;
      if(file_exists($this->conf)) {
        $this->auth = parse_ini_file($this->conf);
      }else{
        $s=str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        $key='';
        foreach(array_rand($s, 32) as $k) $key .= $s[$k];
        $auth='[app]'."\n".'key = '.$key."\n";
        file_put_contents($this->conf,$auth);
        if(file_exists($this->conf)) {
          $this->auth = parse_ini_file($this->conf);
        }
      }
      if(!isset($_POST['req'])) $_POST['req'] = '';
      switch($_POST['req']) {
        case 'create': $res = $this->create();break;
        default: 
        case 'read': $res = $this->read(); break;
        case 'update': $res = $this->update();break;
        case 'delete': $res = $this->delete();break;
      }
      $this->json($res);
    }
  }
  private function create() {
    // create user only or host???
    $res = false;
    $user = false;
    $pass = false;
    if(isset($_POST['u'])) $user = $_POST['u'];
    if(isset($_POST['p'])) $pass = $_POST['p'];
    // || valid create token 
    if($user && $pass && (  count($this->auth) == 1 )) {
      $users = '[app]'."\n";
      foreach($this->auth as $k => $v) {
        $users.=$k.' = '.$v."\n";
      }
      $users.=$user.' = '.password_hash($pass,PASSWORD_DEFAULT)."\n";
      if(file_put_contents($this->conf,$users))
        exec('ssh-keygen -b 2048 -t rsa -f ~/.ssh/'.$user.' -q -N "" -C "'.$user.'"',$res);
    }
    return $res;
  }
  private function read() {
    //todo consider auth test
    $res = false;
    $token = false;
    if(isset($_COOKIE['t'])) $token = $_COOKIE['t'];
    if($token) {
      if(isset($this->auth['key'])) {
        $shrapnel = explode('.',$token);
        if(count($shrapnel) == 2) {
          if($shrapnel[1] == base64_encode(hash_hmac('sha256',$shrapnel[0],$this->auth['key']))) {
            $claim = json_decode(base64_decode($shrapnel[0]));
            $res = $this->profile($claim->User);
          }
        }
      }
    }else{
      $user = false;
      $pass = false;
      if(isset($_POST['u'])) $user = $_POST['u'];
      if(isset($_POST['p'])) $pass = $_POST['p'];
      if(isset($this->auth[$user])&&isset($this->auth['key'])) {
        if(password_verify($pass,$this->auth[$user])) {
          $res = $this->profile($user);
        }
      }
      if(!$res) {
        if($this->create()) {
          $res = $this->read();
        }
      }
    }
    return $res;
  }
  private function profile($req) {
    $res = false;
    $json = array('User' => $req,'Expr' => date('U',strtotime('+1 day')));
    $claim = base64_encode(json_encode($json));
    $pub = file_get_contents('/var/www/.ssh/'.$req.'.pub');
    $res = array(
      'token' => $claim.'.'.base64_encode(hash_hmac('sha256',$claim,$this->auth['key'])),
      'pub' => $pub
    );
    return $res;
  }
  private function update() {
    //update password
    //update git host ?
    //update ???
    $res = false;
    return $res;
  }
  private function delete($req='') {
    $res = false;
    return $res;
  }
  private function utf8ize($d) {
    if (is_array($d)) {
      foreach ($d as $k => $v) {
        $d[$k] = $this->utf8ize($v);
      }
    } else if (is_string ($d)) {
      return utf8_encode($d);
    }
    return $d;
  }
  private function json($req=false) {
    if($req) {
      header("Content-type: application/json");
      if(array_key_exists('callback', $_GET) == TRUE){
        $req=json_encode($req);
        print $_GET['callback']."(".$this->utf8ize($req).")"; 
      }else{
        echo json_encode($this->utf8ize($req));
      }
    }else{
      header('HTTP/1.0 404 Not Found');
    }
    exit;
  } 
} #/API ?>
