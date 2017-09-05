<?php $api = new API(); ?><!DOCTYPE html><html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Video</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
      body { padding: 20px 0 0; background: #333; }
      .form-group { margin: 9px; }
      .list-group:last-child { padding-right: 0; }
      .list-group { margin-bottom: 0; }
      .list-group a:last-child { margin-bottom: 20px; }
      .list-group-item { cursor: pointer; overflow: hidden; white-space: nowrap; }
      .list-group-item img { position:absolute; top:0; right:0; height: 100%; }
      .navbar-brand { cursor: pointer; }
      #e { position: fixed; right: 1em; z-index: 100; }
      #v{
        z-index:1;
        position:absolute;
        top:0;
        right:0;
        bottom:0;
        left:0;
        width:100%;
      }
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
      </div>
    </nav>
    <div class="container-fluid">
      <div class="row-fluid">
        <div id="e" class="hidden alert alert-danger" role="alert"> </div>
        <div class="col-sm-12">
          <h5></h5>
          <div id="r" class="list-group"></div>
        </div>
      </div>
    </div>
    <video id="v" controls><source src="" type="video/mp4"></video>
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
              $(".col-sm-12 h5").html("<span class='glyphicon glyphicon-search'></span> Results");
              var html = "";
              $.each(res,function(k,v){
                html += "<a class='g list-group-item' data-f='"+v.f+"' data-n='"+v.n+"' data-t='0'>"+v.n;
                html += "</a>";
              });
              $("#r").html(html);
              if($("#r .active").length==0) {$("#r a:first").addClass("active");}
            }).fail(function() {
              search(false);
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
      $(document).on("click","#r .g:not(.disabled)",function(e) {
        play($(this).data());
        reset();
      });
      $(document).on("click",".navbar-brand",function(e) {
        switch ($(this).data("req")) {
          default: console.log($(this).data("req")+" Not Registered");break;
          case "pp":pp();break;
          case "fo":fo();break;
          case "ra":ra();break;
          case "dn":dn();break;
          case "up":up();break;
          case "li":list();break;
          case "ce":close();break;
        }
      });
      function pp() {
        if(app.p) {
          app.p = false;
          $("#v").get(0).pause();
        }else{
          app.p = true;
          if($("#v source").attr("src").length > 0 ) $("#v").get(0).play();
        }
        bookmark();
        ui();
      }
      function fo() {
        $.ajax({
          type: "POST",
          data: app
        }).done(function(res) {
          play({"f":res.f,"n":res.n,"t":0});
        }).fail(function() {
          err("Failed to Initialize");
          app.p = false;
          ui();
        });
      }
      function play(req) {
        app.f = req.f;
        app.n = req.n;
        app.t = req.t;
        localStorage.video = JSON.stringify(app);
        video();
        ui();
      }
      function close() {
        $(".edit").addClass("hidden");
        $("body").removeClass("body-edit");
      }
      function sb() {
        $("#v").get(0).currentTime=$("#v").get(0).currentTime-10;
      }
      function sf() {
        $("#v").get(0).currentTime=$("#v").get(0).currentTime+10;
      }
      function ra() {
        app.s = (app.s ? false : true);
        localStorage.video = JSON.stringify(app);
        ui();
      }
      function dn() {
        if($("#v").get(0).volume >= 0.1 && $("#v").get(0).volume <= 1 ) {
          $("#v").get(0).volume = $("#v").get(0).volume - 0.1;
        }
      }
      function up() {
        if($("#v").get(0).volume >= 0 && $("#v").get(0).volume <= 0.9 ) {
          $("#v").get(0).volume = $("#v").get(0).volume + 0.1;
        }
      }
      function prev() {
        var that=$("#r .active");
        if(that.length==0) {
          $("#r a:first").addClass("active");
        }else{
          that.removeClass("active");
          if(that.is(":first-child")) {
            $("#r a:last").addClass("active");
          }else{
            that.prev().addClass("active");
          }
        }
      }
      function next() {
        var that=$("#r .active");
        if(that.length==0) {
          $("#r a:first").addClass("active");
        }else{
          that.removeClass("active");
          if(that.is(":last-child")) {
            $("#r a:first").addClass("active");
          }else{
            that.next().addClass("active");
          }
        }
      }
      function video() {
        // ??? //
        var init=1;
        $("#v").attr("src","").remove();
        if(app.f!== undefined) {
          $("body").append("<video id='v' controls><source src='"+decodeURIComponent(app.f).replace(/'/g,'%27')+"' type='video/mp4'></video>");
          $("#v").get(0).volume = app.v;
          $("#v").get(0).addEventListener("ended",function() { 
            fo();
          });
          $("#v").get(0).addEventListener("canplay",function() { 
            if(init == "1") { 
              init=0; 
              $("#v").get(0).currentTime=app.t;
              if(app.p) $("#v").get(0).play();
            }
          });
          centered();
        }else{
          fo();
        }
      }
      function centered() {
        if($(window).height() - $("#v").height() > 50) {
          var off = (($(window).height() - $("#v").height() ) / 2)
          
          $("#v").css({"top":(off< 50?50:off) +"px"});
        }else{
          $("#v").height($(window).height()-50);
        }
      }
      function ui() {
        $(".glyphicon-play, .glyphicon-pause").parent().removeClass("hidden");
        if(app.p) {
          $(".glyphicon-play").parent().addClass("hidden");
        }else{
          $(".glyphicon-pause").parent().addClass("hidden");
        }
        var p = "&nbsp;&nbsp;&nbsp;";
        //$("#t").html(p+app.n);
        //$("#l").html(p+app.n);
        //$("#playlist").html(app.l);
        document.title = app.n;
        timeline();
      }
      function err(req) {
        $("#e").html("<strong>Error:</strong> "+req+"!").show().removeClass("hidden").delay(5000).fadeOut(500);
      }
      function reset() {
        $("#q").val("");
        $("#r,.col-sm-12 h5").html("");
      }
      setInterval(function() { timeline(); }, 3000);
      function timeline() {
        if($("#v").get(0).duration) {
          //$("#t").css({"width": $("#v").get(0).currentTime / $("#v").get(0).duration * 100 + "%"});
          bookmark();
        }
      }
      function bookmark() {
        app.t = $("#v").get(0).currentTime;
        localStorage.video = JSON.stringify(app);
      }
      $(document).on("click", "#tl",function(e){
        var percent = e.pageX / $(window).width();
        $("#v").get(0).currentTime = $("#v").get(0).duration * percent;
        bookmark();
        $("#t").css({"width":percent * 100 + "%"});
      });
      $(document).keyup(function(e) {
        if(!$("input").is(":focus")) {
          switch(e.keyCode) { 
            case 27:$("#q").focus();e.preventDefault();break;
            case 32:if($("#r .active").length==0){pp();}else{$("#r .active").click();}e.preventDefault();break;
            case 37:sb();e.preventDefault();break;
            case 38:if($("#r .active").length==0){up();}else{prev();}e.preventDefault();break;
            case 39:sf();e.preventDefault();break;
            case 40:if($("#r .active").length==0){dn();}else{next();}e.preventDefault();break;
          }
        }else{
          if(e.keyCode == 27) {$("input").blur();e.preventDefault();}
        }
      });
      $(window).resize(function () {
        centered();
      });
      var app = localStorage.video;
      $(document).ready(function() {
        if(app == null) {
          app = {"f":"","l":"Library","n":"","p":false,"s":false,"t":0,"v":0.5};
          fo();
        }else{
          app = JSON.parse(app);
          video();
          ui();
        }
      });
    </script>
  </body>
</html><?php class API {
  private $d = '';
  private $m = 'meta';
  private $l = 'Library';
  private $p = './mp4/';
  function __construct() {
    if($_SERVER['REQUEST_METHOD']==='POST') {
      $this->d = dirname(__FILE__);
      $res = false;
      if(!isset($_POST['req'])) $_POST['req'] = '';
      switch($_POST['req']) {
        default: $res = $this->fetch($_POST['s'],(isset($_POST['f'])?$_POST['f']:''),(isset($_POST['l'])?$_POST['l']:''));break;
        case 'create': $res = $this->create($_POST['l']);break;
        case 'list': $res = $this->files($this->p,false);break;
        case 'search': $res = $this->search($_POST['q']);break;
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
  private function search($req) {
    $res = false;
    if(!empty($req)) {
      $mp4 = json_decode(file_get_contents($this->p.$this->l.'.json'),true);
      if(file_exists($this->p.$this->m)) {
        $meta = json_decode(file_get_contents($this->p.$this->m),true);
      }
      foreach($mp4 as $k => $v) {
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
      $mp4=json_decode(file_get_contents($this->p.$l.'.json'),true);
    }else{
      # ???
      $this->refresh();
    }
    if(empty($id)) {
      $id = key($mp4);
    }else{
      if($req == 'true') {
        $r = array_rand($mp4,2);
        if($r[0]!=$id) {
          $id = $r[0];
        }else{
          $id = $r[1];
        }
      }else{
        while(key($mp4) !== $id) next($mp4);
        next($mp4);
        $id = key($mp4);
        if($id==null) $mp4 = false;
      }
    }
    if($mp4) {
      if(!file_exists($id)) {
        unset($mp4[$id]);
        file_put_contents($this->p.$l.'.json',json_encode($mp4));
        return $this->fetch($req,$id,$l);
      }
      if(file_exists($this->p.$this->m)) $meta=json_decode(file_get_contents($this->p.$this->m),true);
      $res = array('f' => $id, 'n' => (isset($meta[$mp4[$id]]) ? $meta[$mp4[$id]] : $mp4[$id]));
    }
    return $res;
  }
  private function files($req,$mp4=true) {
    $res = false;
    if(is_Dir($req)) {
      $res = array(); 
      $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($req));
      if($mp4) {
        foreach($rii as $file) {
          if($file->isDir()|| $file->getExtension() != 'mp4') continue;
          $res[$file->getPathname()] = $file->getBasename('.mp4'); 
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