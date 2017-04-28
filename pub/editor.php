<?php $api = new API(); 
### TODO Convert from audio into file editor
?><!DOCTYPE html><html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Text</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
      body { padding-top: 70px; }
      @media (max-width: 767px) {
        body { padding-top: 120px; }
      }
      .form-group { margin: 9px; }
      textarea { resize: none; }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <form id="search" method="POST" >
            <div class="form-group has-feedback">
              <input type="text" id="q" name="q" class="form-control" placeholder="Search" />
              <span class="glyphicon glyphicon-search form-control-feedback"></span>
            </div>
          </form>
        </div>
        <div class="navbar-brand">
        /path/to/file
        </div>
        <a class="navbar-brand pull-right"><span class="glyphicon glyphicon-trash text-danger"></span> <span class="text-danger">Trash</span></a>
      </div>
    </nav>
    <div class="container-fluid">
      <div class="row-fluid">
        <textarea id="d" name="d" class="form-control" rows="10" placeholder="Info"></textarea>
      </div>
    </div>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
      $("form#search").on("submit",function(e) {
        e.preventDefault();
        if($("#q").val().length > 0) {
          if($("#q").val() != $("#q").data("q")) {
            $("#q").data("q",$("#q").val());
            $("#r").html("");
          }
          if($("#r .g").length == 0 ) {
            $("#q").blur();
            search(true);
            $.ajax({
              type: "POST",
              data: $(this).serialize()
            }).done(function(res) {
              search(false);
              $(".col-sm-8 h5").html("<span class='glyphicon glyphicon-search'></span> Results");
              var html = "";
              $.each(res,function(k,v){
                html += "<a class='g list-group-item' data-f='"+v.f+"' data-n='"+v.n+"' data-t='0'>"+v.n;
                html += "<button class='update btn btn-default btn-xs pull-right'><span class='glyphicon glyphicon-plus-sign'></span> Playlist</button>";
                html += "<button class='btn btn-default btn-xs pull-right'><span class='glyphicon glyphicon-pencil'></span> Edit</button>";
                html += "</a>";
              });
              $("#r").html(html);
              if($("#r .active").length==0) {$("#r a:first").addClass("active");}
            }).fail(function() {
              $("#r").html("<a class='g list-group-item disabled'>Your search - <b>"+$("#q").val()+"</b> - did not match any documents.</a>");
            });
          }
        }else{
          reset();
        }
      });
      function search(req) {
        ( req ? $(".form-control-feedback").removeClass("glyphicon-search").addClass("glyphicon-hourglass") : $(".form-control-feedback").removeClass("glyphicon-hourglass").addClass("glyphicon-search") );
      }
      $(document).on("click","#r .g:not(.disabled) button.update",function(e) {
        var div = $(this)
          , data = div.parent().data()
          ;
        data.l = app.l;
        data.req = "update";
        $.ajax({
          type: "POST",
          data: data
        }).done(function(res) {
          div.addClass("btn-info");
        });
        e.stopPropagation();
      });
      $(document).on("click","#r .g:not(.disabled)",function(e) {
        play($(this).data());
        reset();
      });
      $(document).on("click","#r .y:not(.disabled)",function(e) {
        $(this).addClass("disabled").removeClass("active");
        $.ajax({
          type: "POST",
          data: "req=download&d="+$(this).data("d")+"&q="+$("#q").val()
        }).done(function(res) {
          reset();
        }).fail(function() {
          //TODO check not returning something??
          err("Failed to Download");
          reset();
        });
      });
      $(document).on("click","#r .l button",function(e) {
        var div = $(this).parent()
          , data = div.data()
          ;
        data.req = "delete";
        $.ajax({
          type: "POST",
          data: data
        }).done(function(res) {
          div.remove();
        }).fail(function() {
          err("Unable to Delete '"+data.l+"' Playlist");
        });
        e.stopPropagation();
      });
      $(document).on("click","#r .l",function(e) {
        if($(this).data("l")) {
          app.l = $(this).data("l");
          localStorage.app = JSON.stringify(app);
          reset();
          ui();
        }
      });
      $(document).on("click","#p a,#r .g:not(.disabled) button:not(.update)",function(e) {
        $(".edit").removeClass("hidden");
        $("body").addClass("body-edit");
        $("#f").val(($(this).data("f")===undefined?$(this).parent().data("f"):$(this).data("f")));
        $("#n").val(($(this).data("n")===undefined?$(this).parent().data("n"):$(this).data("n"))).focus();
        e.stopPropagation();
      });
      function audio() {
        // ??? //
        var init=1;
        $("#a").attr("src","").remove();
        if(app.f!== undefined) {
          $("body").append("<audio id='a'><source src='"+decodeURIComponent(app.f).replace(/'/g,'%27')+"' type='audio/mp3'></audio>");
          $("#a").get(0).volume = app.v;
          $("#a").get(0).addEventListener("ended",function() { 
            fo();
          });
          $("#a").get(0).addEventListener("canplay",function() { 
            if(init == "1") { 
              init=0; 
              $("#a").get(0).currentTime=app.t;
              if(app.p) $("#a").get(0).play();
            }
          });
        }else{
          fo();
        }
      }
      function reset() {
        $("#q").val("");
        $("#r,.col-sm-8 h5").html("");
      }
      function bookmark() {
        app.t = $("#a").get(0).currentTime;
        localStorage.app = JSON.stringify(app);
      }
      //var app = localStorage.app;
      $(document).ready(function() {
        /*
        if(app == null) {
          app = {"f":"","l":"Library","n":"","p":false,"s":false,"t":0,"v":0.5};
          fo();
        }else{
          app = JSON.parse(app);
          playlist();
          audio();
          ui();
        }
        */
      });
    </script>
  </body>
</html><?php class API {
  /*

  TODO
  version controled and non version controlled documents.
  
  ../src/txt/ < not in version control.
  ../pub/txt/ < in version control.


  create new file in directory X ???



  */
  private $d = '';
  private $m = 'meta';
  private $l = 'Library';
  private $p = './mp3/';
  # required: youtube-dl
  # required: libav-tools
  function __construct() {
    if($_SERVER['REQUEST_METHOD']==='POST') {
      $this->d = dirname(__FILE__);
      $res = false;
      if(!isset($_POST['req'])) $_POST['req'] = '';
      switch($_POST['req']) {
        default: $res = $this->fetch($_POST['s'],(isset($_POST['f'])?$_POST['f']:''),(isset($_POST['l'])?$_POST['l']:''));break;
        case 'create': $res = $this->create($_POST['l']);break;
        case 'update': $res = $this->update($_POST['f'],$_POST['l']);break;
        case 'delete': $res = $this->delete($_POST['l']);break;
        case 'list': $res = $this->files($this->p,false);break;
        case 'search': $res = $this->search($_POST['q']);break;
        case 'edit': $res = $this->edit($_POST['n'],$_POST['f']);break;
        case 'trash': $res = $this->trash($_POST['f']);break;
      }
      $this->json($res);
    }
  }
  private function create($req='') {
    $res = false;
    if($req!=$this->l && !empty($req) && !file_exists($this->p.$req.'.json')) {
      file_put_contents($this->p.$req.'.json',json_encode(array()));
      $res = true;
    }
    return $res;
  }
  private function update($f='',$l='') {
    $res = false;
    if($l != $this->l && !empty($l)) {
      if(file_exists($this->p.$l.'.json')) {
        $mp3=json_decode(file_get_contents($this->p.$this->l.'.json'),true);
        $list = json_decode(file_get_contents($this->p.$l.'.json'),true);
        $list[$f] = $mp3[$f];
        file_put_contents($this->p.$l.'.json',json_encode($list));
        $res = true;
      }
    }
    return $res;
  }
  private function delete($req='') {
    $res = false;
    if($req!=$this->l && !empty($req)) {
      unlink($this->p.$req.'.json');
      $res = true;
    }
    return $res;
  }
  private function edit($n='',$f='') {
    $res = false;
    if(file_exists($this->p.$this->l.'.json')) {
      $mp3=json_decode(file_get_contents($this->p.$this->l.'.json'),true);
      if(isset($mp3[$f])) {
        if(file_exists($this->p.$this->m)) $meta = json_decode(file_get_contents($this->p.$this->m),true);
        $meta[$mp3[$f]] = $n;
        file_put_contents($this->p.$this->m,json_encode($meta));
        $res = true;
      }
    }
    return $res;
  }
  private function search($req) {
    $res = false;
    if(!empty($req)) {
      $mp3 = json_decode(file_get_contents($this->p.$this->l.'.json'),true);
      if(file_exists($this->p.$this->m)) {
        $meta = json_decode(file_get_contents($this->p.$this->m),true);
      }
      foreach($mp3 as $k => $v) {
        $n = (isset($meta[$v]) ? $meta[$v] : $v);
        if(preg_match('/'.$req.'/i',$n)) {
          $res[] = array( 'f' => $k, 'n' => $n);
        }
      }
    }
    return $res;
  }
  private function fetch($req=false,$id='',$l='') {
    $res=false;
    if(empty($l)) $l = $this->l;
    if(file_exists($this->p.$l.'.json')) {
      $mp3=json_decode(file_get_contents($this->p.$l.'.json'),true);
    }else{
      # ???
      $this->refresh();
    }
    if(empty($id)) {
      $id = key($mp3);
    }else{
      if($req == 'true') {
        $r = array_rand($mp3,2);
        if($r[0]!=$id) {
          $id = $r[0];
        }else{
          $id = $r[1];
        }
      }else{
        while(key($mp3) !== $id) next($mp3);
        next($mp3);
        $id = key($mp3);
        if($id==null) $mp3 = false;
      }
    }
    if($mp3) {
      if(!file_exists($id)) {
        unset($mp3[$id]);
        file_put_contents($this->p.$l.'.json',json_encode($mp3));
        return $this->fetch($req,$id,$l);
      }
      if(file_exists($this->p.$this->m)) $meta=json_decode(file_get_contents($this->p.$this->m),true);
      $res = array('f' => $id, 'n' => (isset($meta[$mp3[$id]]) ? $meta[$mp3[$id]] : $mp3[$id]));
    }
    return $res;
  }
  private function trash($req) {
    $res = false;
    if(file_exists($req)) {
      if(file_exists($this->p.$this->l.'.json')) {
        $mp3=json_decode(file_get_contents($this->p.$this->l.'.json'),true);
        if(file_exists($this->p.$this->m)) $meta = json_decode(file_get_contents($this->p.$this->m),true);
        if(isset($meta)) unset($meta[$mp3[$req]]);
        unlink($req);
        if(!file_exists($req)) {
          file_put_contents($this->p.$this->m,json_encode($meta));
          $res = $this->refresh();
        }
      }
    }
    return $res;
  }
  private function files($req,$mp3=true) {
    $res = false;
    if(is_Dir($req)) {
      $res = array(); 
      $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($req));
      if($mp3) {
        foreach($rii as $file) {
          if($file->isDir()|| $file->getExtension() != 'mp3') continue;
          $res[$file->getPathname()] = $file->getBasename('.mp3'); 
        }
      }else{
        foreach($rii as $file) {
          if($file->isDir()|| $file->getExtension() != 'json') continue;
          $res[] = $file->getBasename('.json'); 
        }
      }
    }
    return $res;
  }
  private function refresh() {
    $res = false;
    $req=$this->files($this->p);
    if($req) {
      file_put_contents($this->p.$this->l.'.json',json_encode($req));
      $res = true;
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
