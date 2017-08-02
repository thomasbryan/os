<?php $api = new API(); ?><!DOCTYPE html><html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Git</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
      html { margin-bottom: 50px; }
      body { padding-top: 1em; padding-bottom: 70px; }
      .form-group { margin: 9px; }
      .status .btn { margin-bottom: 0.5em; }
      #overlay { position: absolute; top: 0; right: 0; bottom: 0; left: 0; z-index: 9000; background: rgba(0,0,0,0.5); padding: 3em; }
      #overlay .panel-body { overflow: auto; }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-inverse navbar-fixed-bottom">
      <div class="container-fluid">
        <form method="POST" >
          <div class="form-group ">
            <div class="col-xs-3">
              <a id="cache" href="javascript:void(0);" class="btn btn-primary col-xs-12">Cache</a>
            </div>
            <div class="col-xs-3">
              <input type="text" id="n" name="n" class="form-control" placeholder="Name" />
            </div>
            <div class="col-xs-3">
              <input type="text" id="e" name="e" class="form-control" placeholder="Email" />
            </div>
            <div class="col-xs-3">
              <a id="config" href="javascript:void(0);" class="btn btn-default col-xs-12">Config</a>
            </div>
          </div>
        </form>
      </div>
    </nav>
    <div id="projects"></div>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
      $(document).on("click",".n, .u",function(e) {
        action("add&project="+$(this).closest(".project").data("project")+"&file="+$(this).data("file"));
      });
      $(document).on("click",".s",function(e) {
        action("rem&project="+$(this).closest(".project").data("project")+"&file="+$(this).data("file"));
      });
      $(document).on("click",".p",function(e) {
        action("push&project="+$(this).closest(".project").data("project")+"&email="+$("#e").val()+"&name="+$("#n").val());
      });
      $(document).on("click",".d",function(e) {
        action("pull");
      });
      $(document).on("click",".diff",function(e) {
        var project = $(this).closest(".project").data("project");
        $.ajax({
          type: "POST",
          data: "req=diff&project="+project
        }).done(function(res) {
          var html = "<div id='overlay'><div class='panel panel-info'><div class='panel-heading'><a>&nbsp;</a>Diff '"+project+"'</div><div class='panel-body'>";
          $.each(res,function(k,v) {
            var c = "info";
            switch(v.charAt(0)) {
              case "-": c = "danger"; break;
              case "+": c = "success"; break;
            }
            html += "<span class='text-"+c+"'>"+v.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\ /g,"&nbsp;")+"</span><br />";
          });
          html+= "</div></div></div>";
          $("body").append(html);
          $("#overlay .panel-body").css({"height":Math.ceil($(window).height() * 0.85)+"px"});
        });
      });
      $(document).on("click","#overlay",function(e) {
        $("#overlay").remove();
      });
      $(document).on("click","#cache",function(e) {
        action("cache");
      });
      $(document).on("click","#config",function(e) {
        app.name = $("#n").val();
        app.email = $("#e").val();
        localStorage.git = JSON.stringify(app);
      });
      var app = localStorage.git;
      $(document).ready(function() {
        if(app == null) {
          app = {"name":"","email":""};
        }else{
          app = JSON.parse(app);
        }
        $("#n").val(app.name);
        $("#e").val(app.email);
        buildstatus();
      });
      function action(req) {
        $.ajax({
          type: "POST",
          data: "req="+req
        }).done(function(res) {
          buildstatus();
        });
      }
      function buildstatus() {
        $.ajax({
          type: "POST",
          data: "req=status",
        }).done(function(res) {
          var projects = {}
            ;
          $.each(res,function(k,v) {
            var project = ""
              , c = Math.max.apply(Math, [ v.s.length, v.n.length, v.u.length ])
              , d = ""
              , s = ""
              , n = ""
              , u = ""
              , p = ""
              , t = "default"
              ;
            $.each(v.s,function(kk,vv) {
              s+=button({"c":"s","t":"success","f":vv,"a":"minus"});
            });
            $.each(v.n,function(kk,vv) {
              n+=button({"c":"n","t":"warning","f":vv,"a":"plus"});
            });
            $.each(v.u,function(kk,vv) {
              u+=button({"c":"u","t":"danger","f":vv,"a":"plus"});
            });
            if(s.length>0) p = "<a href='javascript:void(0);' class='p btn btn-primary col-xs-12'><span class='glyphicon glyphicon-upload'></span> <span class='hidden-xs'>Push</span></a>";
            if(v.s.length + v.n.length + v.u.length > 0) d = "<a href='javascript:void(0);' class='diff btn btn-default col-xs-12'><span class='glyphicon glyphicon-duplicate'></span> <span class='hidden-xs'>Diff</span></a>";
            project+= "<div data-project='"+k+"' class='project col-xs-12 col-sm-6 col-md-4'>";
            project+= "<div class='panel panel-"+t+"'>";
            project+= "<div class='panel-heading'><strong>"+k+"</strong><span class='pull-right'>"+v.b+"</span></div>";
            //text box < git grep >
            project+= "<div class='panel-body'>";
            project+= "<div class='row'><div class='col-xs-12'>";
            project+= "<div class='status col-sm-4'>";
            project+= s;
            project+= "</div>";
            project+= "<div class='status col-sm-4'>";
            project+= n;
            project+= "</div>";
            project+= "<div class='status col-sm-4'>";
            project+= u;
            project+= "</div>";
            project+= "</div></div>";
            project+= "<div class='col-sm-4'>";
            project+= p;
            project+= "</div>";
            project+= "<div class='col-sm-4'>";
            project+= "<a href='javascript:void(0);' class='d btn btn-info col-xs-12'><span class='glyphicon glyphicon-download'></span> <span class='hidden-xs'>Pull</span></a>";
            project+= "</div>";
            project+= "<div class='col-sm-4'>";
            project+= d;
            project+= "</div>";
            project+= "</div>";
            project+= "</div>";
            project+= "</div>";
            project+= "</div>";
            if(projects[c] === undefined) projects[c] = []
            projects[c].push(project);
          });
          $("#projects").html("");
          $.each(projects,function(k,v) {
            $.each(v,function(kk,vv) {
              $("#projects").prepend(vv);
            });
          });
        });
      }
      function button(req) {
        var shrapnel = req.f.split("/")
          , last = (shrapnel.length - 1)
          , name = (shrapnel[last].length > 10 ? shrapnel[last].substr(0,7)+"...":shrapnel[last]) 
          ;
        if(name.length == 0) name = "<span class='glyphicon glyphicon-folder-open'></span>";
        return "<a href='javascript:void(0);' class='col-xs-12 "+req.c+" btn btn-"+req.t+" btn-xs' data-file='"+req.f+"' title='"+req.f+"'><span class='pull-left glyphicon glyphicon-"+req.a+"'></span> <span class=''>"+name+"</span></a><br>";
      }
    </script>
  </body>
</html><?php 
class API {
  private $path = '';
  private $user = '';
  private $len = 0;
  private $cache = '';
  private $prefix = '';
  private $res;
  # required: git
  # create user profiles for repos access
  function __construct() {
    if($_SERVER['REQUEST_METHOD']==='POST') {
      $res = false;
      if(isset($_COOKIE)) {
        if(isset($_COOKIE['t'])) {
          $token = $_COOKIE['t'];
          $key = parse_ini_file('../src/conf.ini');
          if(isset($key['key'])) {
            $shrapnel = explode('.',$token);
            if(count($shrapnel) == 2) {
              if($shrapnel[1] == base64_encode(hash_hmac('sha256',$shrapnel[0],$key['key']))) {
                $claim = json_decode(base64_decode($shrapnel[0]));
                $this->user = $claim->User;
                $this->len = strlen($this->user);
                $this->cache = '../src/repos/'.$this->user;
                $this->prefix = '../src/users/'.$this->user.'/';

                if(!isset($_POST['req'])) $_POST['req'] = ''; 
                switch($_POST['req']) {
                  case 'cache': $res = $this->cache();break;
                  case 'diff': $res = $this->diff();break;
                  case 'status': $res = $this->status();break;
                  case 'config': $res = $this->config();break;
                  case 'add': $res = $this->add();break;
                  case 'rem': $res = $this->rem();break;
                  case 'pull': $res = $this->pull();break;
                  case 'push': $res = $this->push();break;
                }
              }
            }
          }
        }
      }
      $this->json($res);
    }
  }
  private function cache() {
    if(file_exists($this->cache)) {
      unlink($this->cache);
    }
    return true;
  }
  private function config() {
    exec('git config --list',$res);
    return $res;
  }
  private function diff() {
    $res = false;
    $status = $this->status();
    if(isset($status[$_POST['project']])) {
      $this->path = dirname(__FILE__);
      chdir($this->path);
      chdir($this->prefix.$_POST['project']);
      $exec = 'git diff';
      exec($exec,$res);
    }
    return $res;
  }
  private function status() {
    $res = false;
    if(!file_exists($this->cache)) {
      exec('find ../src/users/'.$this->user.' -type d -name ".git"',$dir);
      $this->path = dirname(__FILE__);
      $len = strlen(dirname(getcwd()));
      foreach($dir as $k => $v) {
        $this->gitstatus($v,$len);
      }
      $git = $this->res;
      chdir($this->path);
      file_put_contents($this->cache,serialize($git));
    }else{
      $git = unserialize(file_get_contents($this->cache));
    }
    if(isset($_POST['git'])) {
      if(isset($git[$_POST['git']])) {
        #todo 
        #$git[$_POST['git']] = $this->gitstatus();
        #file_put_contents($file,serialize($git));
        #$res = $git[$_POST['git']];
      }
    }else{
      $res = $git;
    }
    return $res;
  }
  private function gitstatus($req,$len) {
    chdir($this->path);
    chdir(dirname($req.'/'));
    $project = substr(getcwd(),$len+12+$this->len);
    exec('git status -sb',$e);
    $b = '';
    $s = array();
    $n = array();
    $u = array();
    foreach($e as $kk => $vv) {
      switch(substr($vv,0,1)) {
        case ' ':break;
        case '#':
        $p = strpos($vv,'...');
        $b = substr($vv,3,($p===false?strlen($vv):$p-3));
        break;
        case '?': $u[] = substr($vv,3); break;
        default: if(strlen($vv) > 0 ) { $s[] = substr($vv,3); } break;
      }
      switch(substr($vv,1,1)) {
        case '?': case '#': case ' ': break;
        default: if(strlen($vv) > 0 ) { $n[] = substr($vv,3); } break;
      }
    }
    $this->res[$project] = array('b'=>$b,'s'=>$s,'n'=>$n,'u'=>$u);
  }
  private function add() {
    $res = false;
    $status = $this->status();
    if(isset($status[$_POST['project']])) {
      $valid = false;
      foreach($status[$_POST['project']]['n'] as $k => $v) {
        if($v == $_POST['file']) $valid = true;
      }
      foreach($status[$_POST['project']]['u'] as $k => $v) {
        if($v == $_POST['file']) $valid = true;
      }
      if($valid) {
        $this->cache();
        $this->path = dirname(__FILE__);
        $len = strlen(dirname(getcwd()));
        chdir($this->path);
        chdir($this->prefix.$_POST['project']);
        exec('git add '.$_POST['file']);
        $res = true;
      }
    }
    return $res;
  }
  private function rem() {
    $res = false;
    $status = $this->status();
    if(isset($status[$_POST['project']])) {
      $valid = false;
      foreach($status[$_POST['project']]['s'] as $k => $v) {
        if($v == $_POST['file']) $valid = true;
      }
      if($valid) {
        $this->cache();
        $this->path = dirname(__FILE__);
        $len = strlen(dirname(getcwd()));
        chdir($this->path);
        chdir($this->prefix.$_POST['project']);
        exec('git reset HEAD "'.$_POST['file'].'"');
        $res = true;
      }
    }
    return $res;
  }
  private function pull() {
    $res = false;
    $status = $this->status();
    if(isset($status[$_POST['project']])) {
      $this->cache();
      $this->path = dirname(__FILE__);
      $len = strlen(dirname(getcwd()));
      chdir($this->path);
      chdir($this->prefix.$_POST['project']);
      exec('git pull origin '.$status[$_POST['project']]['b']);
      $res = true;
    }
    return $res;
  }
  private function push() {
    $res = false;
    $status = $this->status();
    if(isset($status[$_POST['project']])) {
      $count = count($status[$_POST['project']]['s']);
      if($count) {
        $this->cache();
        $this->path = dirname(__FILE__);
        $len = strlen(dirname(getcwd()));
        chdir($this->path);
        chdir($this->prefix.$_POST['project']);
        $msg = 'Update ';
        if($count>1) {
          $msg .= $count.' files';
        }else{
          $msg .= str_replace('"','',$status[$_POST['project']]['s'][0]);
        }
        $cfg = '';
        /*
        if(isset($_POST['name'])) {
          if(!empty($_POST['name'])) {
            if(! preg_match('/[^a-zA-Z\ ]/', $_POST['name'])) {
              $cfg .= '-c user.name="'.$_POST['name'].'" ';
            }
          }
        }
        if(isset($_POST['email'])) {
          if(!empty($_POST['email'])) {
            if(! preg_match('/[^a-zA-Z0-9\@\.]/', $_POST['email'])) {
              $cfg .= '-c user.email='.$_POST['email'].' ';
            }
          }
        }
        */
        $exec = 'git commit '.$cfg.' -m  "'.$msg.'" && git push origin '.$status[$_POST['project']]['b'] ;
        exec($exec,$res);
      }
    }
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
