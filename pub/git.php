<?php $api = new API(); ?><!DOCTYPE html><html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Git</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
      body { padding-top: 25px; }
      .status .btn { margin-bottom: 0.5em; }
      #cache { position:fixed; bottom:1em; right:1em; }
    </style>
  </head>
  <body>
    <div id="projects"></div>
    <a id="cache" href="javascript:void(0);" class="btn btn-primary">Update Cache</a>
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
        action("push&project="+$(this).closest(".project").data("project"));
      });
      $(document).on("click",".d",function(e) {
        action("pull");
      });
      $(document).ready(function() {
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
          var projects = "";
          $.each(res,function(k,v) {
            var s = ""
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
            if(s.length>0) p = "<div class='p btn btn-primary col-xs-12'><span class='glyphicon glyphicon-upload'></span> <span class='hidden-xs'>Push</span></div>";
            if(k=="/") t = "primary";
            projects+= "<div data-project='"+k+"' class='project col-xs-12 col-sm-6 col-md-4'>";
            projects+= "<div class='panel panel-"+t+"'>";
            projects+= "<div class='panel-heading'><strong>"+k+"</strong><span class='pull-right'>"+v.b+"</span></div>";
            projects+= "<div class='panel-body'>";
            projects+= "<div class='row'><div class='col-xs-12'>";
            projects+= "<div class='status col-sm-4'>";
            projects+= s;
            projects+= "</div>";
            projects+= "<div class='status col-sm-4'>";
            projects+= n;
            projects+= "</div>";
            projects+= "<div class='status col-sm-4'>";
            projects+= u;
            projects+= "</div>";
            projects+= "</div></div>";
            projects+= "<div class='col-sm-6'>";
            projects+= "<div class='d btn btn-default col-xs-12'><span class='glyphicon glyphicon-download'></span> <span class='hidden-xs'>Pull</span></div>";
            projects+= "</div>";
            projects+= "<div class='col-sm-6'>";
            projects+= p;
            projects+= "</div>";
            projects+= "</div>";
            projects+= "</div>";
            projects+= "</div>";
            projects+= "</div>";
          });
          $("#projects").html(projects);
        });
      }
      function button(req) {
        var shrapnel = req.f.split("/")
          , last = (shrapnel.length - 1)
          , name = (shrapnel[last].length > 10 ? shrapnel[last].substr(0,7)+"...":shrapnel[last]) 
          ;
        return "<div class='col-xs-12 "+req.c+" btn btn-"+req.t+" btn-xs' data-file='"+req.f+"' title='"+req.f+"'><span class='pull-left glyphicon glyphicon-"+req.a+"'></span> <span class=''>"+name+"</span></div><br>";
      }
    </script>
  </body>
</html><?php 
class API {
  private $path = '';
  private $res;
  # required: git
  # create user profiles for repos access
  function __construct() {
    if($_SERVER['REQUEST_METHOD']==='POST') {
      if(!isset($_POST['req'])) $_POST['req'] = ''; 
        switch($_POST['req']) {
          case 'cache': $res = $this->cache();break;
          case 'status': $res = $this->status();break;
          case 'add': $res = $this->add();break;
          case 'rem': $res = $this->rem();break;
          case 'pull': $res = $this->pull();break;
          case 'push': $res = $this->push();break;
        }
      $this->json($res);
    }
  }
  private function cache() {
    $prefix = '../src/';
    unlink($prefix.'.git');
    return true;
  }
  private function status() {
    $res = false;
    $file = '../src/.git';
    if(!file_exists($file)) {
      exec('find ../src/ -type d -name ".git"',$dir);
      array_unshift($dir,'../.git');
      $this->path = dirname(__FILE__);
      $len = strlen(dirname(getcwd()));
      foreach($dir as $k => $v) {
        $this->gitstatus($v,$len);
      }
      $git = $this->res;
      chdir($this->path);
      file_put_contents($file,serialize($git));
    }else{
      $git = unserialize(file_get_contents($file));
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
    if(strlen(getcwd()) > $len+4) {
      $project = substr(getcwd(),$len+5);
    }else{
      $project = '/';
    }
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
        $prefix = '../src/';
        unlink($prefix.'.git');
        if($_POST['project'] == '/') $prefix = '..';
        $this->path = dirname(__FILE__);
        $len = strlen(dirname(getcwd()));
        chdir($this->path);
        chdir($prefix.$_POST['project']);
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
        $prefix = '../src/';
        unlink($prefix.'.git');
        if($_POST['project'] == '/') $prefix = '..';
        $this->path = dirname(__FILE__);
        $len = strlen(dirname(getcwd()));
        chdir($this->path);
        chdir($prefix.$_POST['project']);
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
      $prefix = '../src/';
      unlink($prefix.'.git');
      if($_POST['project'] == '/') $prefix = '..';
      $this->path = dirname(__FILE__);
      $len = strlen(dirname(getcwd()));
      chdir($this->path);
      chdir($prefix.$_POST['project']);
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
        $prefix = '../src/';
        unlink($prefix.'.git');
        if($_POST['project'] == '/') $prefix = '..';
        $this->path = dirname(__FILE__);
        $len = strlen(dirname(getcwd()));
        chdir($this->path);
        chdir($prefix.$_POST['project']);
        $msg = 'Update ';
        if($count>1) {
          $msg .= $count.' files';
        }else{
          $msg .= str_replace('"','',$status[$_POST['project']]['s'][0]);
        }
        $res = exec('git commit -m  "'.$msg.'" && git push origin '.$status[$_POST['project']]['b']);
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
