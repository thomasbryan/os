<?php $api = new API(); ?><!DOCTYPE html><html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Editor</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="ico/editor.ico" rel="icon" type="image/x-icon">
    <style>
      body { padding-top: 70px; }
      .form-group { margin: 9px; }
      textarea { resize: none; }
      .list-group:last-child { padding-right: 0; }
      .list-group { margin-bottom: 0; }
      .list-group .list-group-item:nth-of-type(even) { background-color: #f9f9f9; }
      .list-group a:last-child { margin-bottom: 20px; }
      .list-group-item { cursor: pointer; overflow: hidden; white-space: nowrap; }
      .list-group-item img { position:absolute; top:0; right:0; height: 100%; }
      .navbar-brand { cursor: pointer; }
      .btn-danger { position: absolute; top:0;right:0;bottom:0;border:0;border-radius:0; }
      @media (max-width: 767px) {
        body.body-edit { padding-top: 170px; }
        .list-group { padding-right: 0; }
      }
      #e { position: fixed; right: 1em; z-index: 100; }
      #m { position: absolute; right: 2em; top: 0.5em; z-index: 100; }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <form id="search" method="POST" >
            <input type="hidden" name="req" value="search" />
            <div class="form-group has-feedback">
              <input type="text" id="q" name="q" class="form-control" placeholder="Search" />
              <span class="glyphicon glyphicon-search form-control-feedback"></span>
            </div>
          </form>
        </div>
        <div class="edit hidden navbar-header">
          <form id="edit" method="POST" >
            <div class="form-group">
              <input type="hidden" id="f" name="f" />
            </div>
          </form>
        </div>
        <a id="n" class="edit hidden navbar-brand"></a>
        <a data-req="ce" class="edit hidden navbar-brand pull-right"><span class="glyphicon glyphicon-remove-sign"></span> No</a>
        <a data-req="tn" class="edit hidden navbar-brand pull-right"><span class="glyphicon glyphicon-ok-sign text-danger"></span> <span class="text-danger">Yes</span></a>
      </div>
    </nav>
    <div class="container-fluid">
      <div class="row-fluid">
        <div id="e" class="hidden alert alert-danger" role="alert"> </div>
        <div class="col-xs-12">
          <div id="m" class="btn-group btn-group-xs hidden">
            <button type="button" class="l btn btn-default"><span class="glyphicon glyphicon-tasks"></span></button>
            <button type="button" class="d btn btn-default"><span class="glyphicon glyphicon-file"></span></button>
          </div>
          <ol id="b" class="breadcrumb"><li class="active"><span class="glyphicon glyphicon-home"></span></li></ol>
          <div id="r" class="list-group"></div>
        </div>
        <div class="col-xs-12">
          <textarea id="d" name="d" class="hidden form-control" rows="10" placeholder="Info"></textarea>
          <div id="l" class="list-group hidden"></div>
        </div>
      </div>
    </div>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
      $("form#search").on("submit",function(e) {
        e.preventDefault();
        if($("#q").val().length > 0) {
          $("#d").addClass("hidden");
          if($("#q").val() != $("#q").data("q")) {
            $("#q").data("q",$("#q").val());
            $("#r").html("");
            $("#b").html("<li><a href='javascript:void(0);' data-f=''><span class='glyphicon glyphicon-home'></span></a></li>");
          }
          if($("#r .g").length == 0 ) {
            $("#q").blur();
            search(true);
            $.ajax({
              type: "POST",
              data: $(this).serialize()
            }).done(function(res) {
              search(false);
              var html = "";
              html += list(res,true);
              $("#r").html(html);
            }).fail(function() {
              search(false);
              $("#r").html("<a class='g list-group-item disabled'>Your search - <b>"+$("#q").val()+"</b> - did not match any documents.</a>");
            });
          }
        }else{
          reset();
        }
      });
      $("form#edit").on("submit",function(e) {
        e.preventDefault();
        console.log("update this information");
        console.log($(this).serialize());
        /*
        $.ajax({
          type: "POST",
          data: $(this).serialize()
        }).fail(function() {
          err("Unable to Update");
        });
        */
        $(".edit").addClass("hidden");
        $("body").removeClass("body-edit");
      });
      $(document).on("submit","form#create",function(e) {
        e.preventDefault();
        var n = $("#create .form-control").val();
        if(n.length > 0) {
          $.ajax({
            type: "POST",
            data: "req=create&f="+app.f+"&n="+n
          }).done(function(res) {
            $("#create .form-control").val("");
            app.f = app.f+(app.f.length>0?"/":"")+n;
            state();
          }).fail(function() {
            $("#create .form-control").val("");
            err("Failed to Create File");
          });
        }
      });
      function search(req) {
        ( req ? $(".form-control-feedback").removeClass("glyphicon-search").addClass("glyphicon-hourglass") : $(".form-control-feedback").removeClass("glyphicon-hourglass").addClass("glyphicon-search") );
      }
      $(document).on("click","#r a:not(.disabled),#b a",function(e) {
        app.f = $(this).data("f");
        state();
      });
      $(document).on("click",".navbar-brand",function(e) {
        switch ($(this).data("req")) {
          case "ce":close();break;
          case "tn":trash();break;
        }
      });
      $(document).on("click","#p a,#r a:not(.disabled) button:not(.update)",function(e) {
        $(".edit").removeClass("hidden");
        $("body").addClass("body-edit");
        $("#f").val(($(this).data("f")===undefined?$(this).parent().data("f"):$(this).data("f")));
        $("#n").html("Delete '"+($(this).data("n")===undefined?$(this).parent().data("n"):$(this).data("n"))+"'?");
        e.stopPropagation();
      });
      $(document).on("click","#m button",function(e) {
        $("#m button").removeClass("active");
        $("#d,#l").addClass("hidden");
        $(this).addClass("active");
        if($(this).hasClass("l")) {
          app.m = 0;
          $("#l").removeClass("hidden");
          //scroll to active
        }else{
          app.m = 1;
          $("#d").removeClass("hidden").focus();
          //scroll to top
        }
        //localStorage.editor = JSON.stringify(app);
      });
      function close() {
        $(".edit").addClass("hidden");
        $("body").removeClass("body-edit");
      }
      function trash() {
        var f = $("#f").val();
        $.ajax({
          type: "POST",
          data: "req=delete&f="+f
        }).done(function(res) {
          $(".list-group").find("[data-f='"+f+"']").remove();
        }).fail(function() {
          err("Failed to Delete '"+f+"'");
        });
        close();
      }
      function err(req) {
        $("#e").html("<strong>Error:</strong> "+req+"!").show().removeClass("hidden").delay(5000).fadeOut(500);
      }
      function reset() {
        $("#q").val("");
        state();
      }
      function state() {
        $("#q").val("");
        $("#b").html("<li><a href='javascript:void(0);' data-f=''><span class='glyphicon glyphicon-home'></span></a></li>");
        $("#m").addClass("hidden");
        if(app.f.length > 0) {
          var b = app.f.split("/")
            , f = ""
            ;
          $.each(b,function(k,v) {
            f=f+v;
            $("#b").append("<li><a href='javascript:void(0);' data-f='"+f+"'>"+v+"</a></li>");
            f=f+"/";
          });
        }
        $("#b li").last().html($("#b li:last a").html()).addClass("active").append(" <span class='hidden glyphicon glyphicon-floppy-disk'>");
        document.title = $("#b .active").text()+($("#b .active").text().trim().length>0?"- ":"")+"Editor"
        $.ajax({
          type: "POST",
          data: "req=read&f="+app.f
        }).done(function(res) {
          var html = ""
            , val = ""
            , lines = ""
            , form = "<div class='list-group-item list-group-item-success'><form id='create'><div class='input-group'><input class='form-control' name='n' type='text' placeholder='Create'><div class='input-group-btn'><button class='btn btn-default'><span class='glyphicon glyphicon-plus-sign'></span> New</button></div></div></form></div>"
            ;
          $(window).scrollTop(0);
          if($.isArray(res)) {
            $("#m").removeClass("hidden");
            $("#m > button").removeClass("active");
            $.each(res,function(k,v) {
              lines+="<a href='javascript:void(0);' class='list-group-item'>"+v.replace(/</g,"&lt;").replace(/>/g,"&gt;")+"<span class='badge'>"+(k+1)+"</span></a>";
              val=val+v+"\n";
            });
            $("#d").val($.trim(val)).height(($(window).height()-$("nav").height()-$("#b").height()-100));
            $("#l").html(lines);
            if(app.m) {
              $("#d").removeClass("hidden").focus();
              $("#m .d").addClass("active");
            }else{
              $("#l").removeClass("hidden");
              $("#m .l").addClass("active");
              $("#l a").first().addClass("active");
            }
            app.d = $("#d").val().length;
          }else{
            $("#d,#l").addClass("hidden");
            if($.isPlainObject(res)) {
              html += form;
              html += list(res,false);
            }else{
              if(res.length === undefined) {
                html += form;
              }else{
                html += "<img class='img-responsive' src='"+res+"' />";
              }
              console.log(res.length);
            }
          }
          $("#r").html(html);
        });
      }
      function list(req,t) {
        var res = ""
          , type = ""
          , keys = []
          , k
          , i
          , len
        ;
        for (k in req) {
          if (req.hasOwnProperty(k)) {
            keys.push(k);
          }
        }
        keys.sort();
        len = keys.length;
        for(i = 0; i < len; i++) {
          k = keys[i];
          switch(req[k].t) {
            default:type="file";break;
            case "d":type = "folder-open";break;
            case "i":type = "picture";break;
          }
          res += "<a class='list-group-item' data-n='"+req[k].n+"' data-f='"+k+"' title='"+k+"'><span class='glyphicon glyphicon-"+type+"'></span> "+(t ? k : req[k].n);
          res += "<button class='btn btn-danger'><span class='glyphicon glyphicon-trash'></span> Delete</button>";
          res += "</a>";
        }
        return res;
      }
      setInterval(function() { 
        if($("#d").is(":visible")) {
          var d = $("#d").val().length;
          if(app.d != d) {
            app.d = d;
            $.ajax({
              type: "POST",
              data: "req=update&f="+app.f+"&d="+encodeURIComponent($("#d").val())
            }).done(function(res) {
              $("#b .active .glyphicon-floppy-disk").show().removeClass("hidden").delay(500).fadeOut(500);
            });
          }
        }
      }, 5000);
      var app = localStorage.editor;
      $(document).ready(function() {
        if(app == null) {
          app = {"f":"","d":0,"m":1};
        }else{
          app = JSON.parse(app);
        }
        state();
      });
    </script>
  </body>
</html><?php class API {
  private $d = '';
  private $p = '../src/users/';
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
                chdir($this->p.$claim->User.'/');
                $this->d = getcwd().'/';
                if(!isset($_POST['req'])) $_POST['req'] = '';
                switch($_POST['req']) {
                  case 'search': $res = $this->search($_POST['q']);break;
                  case 'create': $res = $this->create($_POST['f'],$_POST['n']);break;
                  case 'read': $res = $this->read($_POST['f']);break;
                  case 'update': $res = $this->update($_POST['f'],$_POST['d']);break;
                  case 'delete': $res = $this->delete($_POST['f']);break;
                }
              }
            }
          }
        }
      }
      $this->json($res);
    }
  }
  private function search($req) {
    $res = false;
    if(!empty($req)) {
      $res = array();
      $count = strlen($this->d);
      $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->d,FilesystemIterator::SKIP_DOTS));
      foreach($files as $file) {
        $f = substr($file->getPathname(),$count);
        $pos = strpos($f, $req);
        if($pos !== false) {
          $res[$f] = array('n'=>$file->getFilename(),'t'=>($file->isDir()?'d':'f'));
        }
      }
    }
    return $res;
  }
  private function create($f='',$n='') {
    $res = false;
    if(!empty($n)) {
      if($this->valid($f)) {
        chdir(realpath($this->d.$f));
        if(substr($n,-1)=='/') {
          mkdir($n);
        }else{
          touch($n);
          chmod($n,0664);
        }
        $res = true;
      }
    }
    return $res;
  }
  private function read($req='') {
    $res = false;
    if($this->valid($req)) {
      $req = realpath($this->d.$req);
      if(is_dir($req)) {
        $res = array(); 
        $count = strlen($this->d);
        $files = new DirectoryIterator($req);
        foreach($files as $file) {
          if($file->isDot()) continue;
          if(file_exists($file->getPathname())) $m = mime_content_type($file->getPathname());
          switch($m) {
            case 'directory':$t='d';break;
            default:
              $p = explode('/',$m);
              switch($p[0]) {
                default: $t='f';break;
                case 'image':$t='i';break;
              }
            break;
          }
          $res[substr($file->getPathname(),$count)] = array('n'=>$file->getFilename(),'t'=>$t);
        }
        if(empty($res)) $res=true;
      }else{
        $m = mime_content_type($req);
        $p = explode('/',$m);
        switch($p[0]) {
          case 'image': $res='data:'.$m.';base64,'.base64_encode(file_get_contents($req)); break;
          default: $res=explode("\n",file_get_contents($req)); break;
        }
      }
    }
    return $res;
  }
  private function update($f='',$d='') {
    $res = false;
    if($this->valid($f)) {
      $f = realpath($this->d.$f);
      file_put_contents($f, $d);
      $res = true;
    }
    return $res;
  }
  private function delete($req='') {
    $res = false;
    if($this->valid($req)) {
      $req = realpath($this->d.$req);
      if(is_dir($req)) {
        rmdir($req);
      }else{
        unlink($req);
      }
      $res = true;
    }
    return $res;
  }
  private function valid($req=false) {
    $res = false;
    if(empty($req)) return true;
    if(is_file($this->d.$req) || is_dir($this->d.$req)) {
      $pos = strpos(realpath($this->d.$req),$this->d);
      if($pos!==false) {
        if($pos==0) {
          $res = true;
        }
      }
    }
    return $res;
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