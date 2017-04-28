<?php $api = new API(); ?><!DOCTYPE html><html lang="en">
<head>
  <title>SSH</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
	<style>
    body { padding-top: 70px; }
    .navbar .form-group { margin: 9px; }
  </style>
</head>
<body>
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
      </div>
    </nav>
  <div class="container-fluid">
    <div class="row-fluid">
      <div class="col-xs-12">
      	<form id="data" class="" method="POST" enctype="multipart/form-data">
					<div class="form-group col-sm-6">
						<div class="col-sm-12">
        			<input id="user" type="text" name="user" placeholder="USER" class="form-control" />
						</div>
					</div>
					<div class="form-group col-sm-4">
						<div class="col-sm-12">
        			<input id="host" type="text" name="host" placeholder="HOST" class="form-control" />
						</div>
					</div>
					<div class="form-group col-sm-2">
						<div class="col-sm-12">
        			<input id="port" type="text" name="port" placeholder="PORT" class="form-control" value="22" />
						</div>
					</div>
					<div class="form-group col-sm-4">
						<div class="col-sm-12">
        			<input id="pass" type="password" name="pass" placeholder="PASS" class="form-control" />
						</div>
					</div>
					<div class="form-group col-sm-4">
						<div class="col-sm-12">
        			<input id="key" type="file" name="key" placeholder="KEY" class="form-control" />
						</div>
					</div>
					<div class="form-group col-sm-4">
						<div class="col-sm-12">
        			<input id="phrase" type="password" name="phrase" placeholder="PHRASE" class="form-control" />
						</div>
					</div>
					<div class="form-group col-sm-8">
						<div class="col-sm-12">
        			<input id="cmd" type="text" name="cmd" placeholder="CMD" class="form-control" />
						</div>
					</div>
					<div class="form-group has-feedback col-sm-4">
						<div class="col-sm-12">
              <button type="submit" name="submit" class="btn btn-primary col-xs-12">
                Submit
                <span id="submit" class="glyphicon glyphicon-share"></span>
              </button>
						</div>
					</div>
      	</form>
      </div>
      <div class="col-xs-12">
      	<div id="res"></div>
      </div>
    </div>
  </div>
  <script src="js/jquery.min.js"></script>
  <script>
    function ssh(req) {
      (req ? $("#submit").removeClass("glyphicon-share").addClass("glyphicon-hourglass") : $("#submit").removeClass("glyphicon-hourglass").addClass("glyphicon-share"))
    }
    $('form#data').on('submit', function(e) {
	    e.preventDefault();
      ssh(true)
      $("#res").html("");
      var file_data = $('#key').prop('files')[0];
      var form_data = new FormData();
      form_data.append('host',$("#host").val());
      form_data.append('port',$("#port").val());
      form_data.append('user',$("#user").val());
      form_data.append('pass',$("#pass").val());
      form_data.append('key', file_data);
      form_data.append('phrase',$("#phrase").val());
      form_data.append('cmd',$("#cmd").val());
      $.ajax({
        dataType: "jsonp",
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,
        type: "post",
        error: function() {
          ssh(false);
          $("#res").html("<code>Error</code>");
        },
        success: function(res) {
          ssh(false);
          $("#res").html("<pre></pre>");
          $.each(res,function(k,v) {
            $("#res pre").append(v+"\n");
          });
        }
      });
    });
  </script>
</body></html>
<?php
class API {
  private $path = '../src/ssh';
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
        $port = $_POST['port'];
        $user = $_POST['user'];
        if(!empty($user) && !empty($port) && !empty($host)) {
          $pass = $_POST['pass'];
			    $key = '';
			    if(isset($_FILES['key'])) {
      	    $key = file_get_contents($_FILES['key']['tmp_name']);
			    }
          $phrase = $_POST['phrase'];
          $cmd  = $_POST['cmd'];
          $ssh = new Net_SSH2($host, $port);
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
