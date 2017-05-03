<?php $api = new API(); ?><!DOCTYPE html><html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Editor</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
      body { padding-top: 70px; }
      .form-group { margin: 9px; }
      textarea { resize: none; }
      .list-group:last-child { padding-right: 0; }
      .list-group { margin-bottom: 0; }
      .list-group a:last-child { margin-bottom: 20px; }
      .list-group-item { cursor: pointer; overflow: hidden; white-space: nowrap; }
      .list-group-item img { position:absolute; top:0; right:0; height: 100%; }
      .navbar-brand { cursor: pointer; }
      @media (max-width: 767px) {
        body.body-edit { padding-top: 170px; }
        .list-group { padding-right: 0; }
      }
      #e { position: fixed; right: 1em; z-index: 100; }
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
              <input type="text" id="n" name="n" class="form-control" placeholder="Name" />
            </div>
          </form>
        </div>
        <a data-req="ce" class="edit hidden navbar-brand"><span class="glyphicon glyphicon-remove-sign"></span></a>
        <a data-req="tn" class="edit hidden navbar-brand pull-right"><span class="glyphicon glyphicon-trash text-danger"></span> <span class='text-danger'>Trash</span></a>
      </div>
    </nav>
    <div class="container-fluid">
      <div class="row-fluid">
        <div id="e" class="hidden alert alert-danger" role="alert"> </div>
        <div class="col-xs-12">
          <ol id="b" class="breadcrumb"><li class="active"><span class="glyphicon glyphicon-home"></span></li></ol>
          <div id="r" class="list-group"></div>
        </div>
        <div class="col-xs-12">
          <textarea id="d" name="d" class="hidden form-control" rows="10" placeholder="Info"></textarea>
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
              html += list(res);
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
        $("#n").val(($(this).data("n")===undefined?$(this).parent().data("n"):$(this).data("n"))).focus();
        e.stopPropagation();
      });
      function close() {
        $(".edit").addClass("hidden");
        $("body").removeClass("body-edit");
      }
      function trash() {
        $.ajax({
          type: "POST",
          data: "req=delete&f="+$("#f").val()
        }).done(function(res) {
          $(".list-group").find("[data-f='"+$("#f").val()+"']").remove();
          close();
        }).fail(function() {
          err("Failed to Delete File");
        });
      }
      function err(req) {
        $("#e").html("<strong>Error:</strong> "+req+"!").show().removeClass("hidden").delay(5000).fadeOut(500);
      }
      function reset() {
        app.q = "";
        $("#q").val("");
        state();
      }
      function state() {
        $("#q").val("");
        $("#b").html("<li><a href='javascript:void(0);' data-f=''><span class='glyphicon glyphicon-home'></span></a></li>");
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
        $.ajax({
          type: "POST",
          data: "req=read&f="+app.f
        }).done(function(res) {
          var html = ""
            , val = ""
            ;
          if($.isArray(res)) {
            $.each(res,function(k,v) {
              val=val+v+"\n";
            });
            $("#d").removeClass("hidden").val($.trim(val)).height(($(window).height()-$("nav").height()-$("#b").height()-100)).focus();
            app.d = $("#d").val().length;
          }else{
            $("#d").addClass("hidden");
            html += "<div class='list-group-item list-group-item-success'><form id='create'><div class='input-group'><input class='form-control' name='n' type='text' placeholder='Create'><div class='input-group-btn'><button class='btn btn-default'><span class='glyphicon glyphicon-plus-sign'></span> New</button></div></div></form></div>";
            if($.isPlainObject(res)) {
              html += list(res);
            }
          }
          $("#r").html(html);
        });
      }
      function list(req) {
        var res = ""
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
          var type = (req[k].t=="d" ? "folder-close":"file");
          res += "<a class='list-group-item' data-n='"+req[k].n+"' data-f='"+k+"' title='"+k+"'><span class='glyphicon glyphicon-"+type+"'></span> "+req[k].n;
          res += "<button class='btn btn-default btn-xs pull-right'><span class='glyphicon glyphicon-pencil'></span> Edit</button>";
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
          app = {"f":"","q":"","d":0};
        }else{
          app = JSON.parse(app);
        }
        state();
      });
    </script>
  </body>
</html><?php class API {
  private $d = '';
  private $p = '../src/';
  function __construct() {
    if($_SERVER['REQUEST_METHOD']==='POST') {
      chdir($this->p);
      $this->d = getcwd().'/';
      $res = false;
      if(!isset($_POST['req'])) $_POST['req'] = '';
      switch($_POST['req']) {
        case 'search': $res = $this->search($_POST['q']);break;
        case 'create': $res = $this->create($_POST['f'],$_POST['n']);break;
        case 'read': $res = $this->read($_POST['f']);break;
        case 'update': $res = $this->update($_POST['f'],$_POST['d']);break;
        case 'delete': $res = $this->delete($_POST['f']);break;
      }
      $this->json($res);
    }
  }
  private function search($req) {
    $res = false;
    if(!empty($req)) {
      $res = array();
      $count = strlen($this->d);
      $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->d));
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
          $res[substr($file->getPathname(),$count)] = array('n'=>$file->getFilename(),'t'=>($file->isDir()?'d':'f'));
        }
        if(empty($res)) $res=true;
      }else{
        $res=explode("\n",file_get_contents($req));
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
