<?php $api = new API(); ?><!DOCTYPE html><html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Video</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="ico/video.ico" rel="icon" type="image/x-icon">
    <style>
      body { padding: 50px 0; }
      .form-group { margin: 9px; }
      .list-group:last-child { padding-right: 0; }
      .list-group { margin-bottom: 0; }
      .list-group a:last-child { margin-bottom: 20px; }
      .list-group-item { cursor: pointer; overflow: hidden; white-space: nowrap; }
      .list-group-item img { position:absolute; top:0; right:0; height: 100%; }
      .navbar-brand { cursor: pointer; }
      @media (max-width: 767px) {
        body { padding-top: 100px; }
        body.body-edit { padding-top: 215px; }
        .list-group { padding-right: 0; }
      }
      #e { position: fixed; right: 1em; z-index: 100; }
      #l { line-height: 50px; }
      #t { color: #9d9d9d; height: 100%; line-height: 50px; overflow: hidden; position: absolute; width: 0%; white-space: nowrap; }
      #tl { bottom:0; height: 50px; left:0; position: absolute; right:0; width: 100%; }
    <?php /*
    TODO Complete converting code base.
      html,body{
        overflow:auto;
        -webkit-overflow-scrolling:touch;
      }
      body{
        margin:0;
        padding:0;
        top:0;
        right:0;
        left:0;
        bottom:0;
        font-family:monaco,monospace;
        font-size:20px;
        background-color:#333;
        -webkit-user-select:none;
      }
      .r{
        position:relative;
        min-width:325px;
        height:64px;
        line-height:64px;
        cursor:default;
        padding:0 1em;
        font-weight:bold;
        color:#fcfcfc;
        overflow:hidden;
        word-break:break-all;
      }
      .r.a{
        background-color:#0063CE;
        color: #fff;
      }
      #c{
        -webkit-overflow-scrolling:touch;
        position:absolute;
        top:0;
        bottom:0;
        right:0;
        left:0;
        padding:1em 0;
        overflow:auto;
      }
      #v{
        z-index:1;
      }
      #o{
        z-index:2147483647;
      }
      #n {
        z-index:2147483647;
        position:absolute;
      }
      #o.a{
        background-color:rgba(0,0,0,0.75);
      }
      #o,#v{
        position:absolute;
        top:0;
        right:0;
        bottom:0;
        left:0;
        width:100%;
      }
      .icon-stop {
        top:25%;
        left:25%;
      }
      .icon-backward {
        top:75%;
        left: 25%;
      }
      #p {
        left:50%;
        top:50%;
        line-height: 142px !important;
        height: 128px !important;
        width: 128px !important;
        margin-top: -64px !important;
        margin-left: -64px !important;
        font-size: 64px !important;
      }
      .icon-forward {
        top:75%;
        left:75%;
      }
      #f {
        top:25%;
        left:75%;
      }
      #n > i {
        margin-top: -32px;
        margin-left: -32px;
        background-color: #fff;
        font-size:32px;
        width: 64px;
        height: 64px;
        line-height: 72px;
        text-align: center;
        position: fixed;
      }
      #tl {
        right:0;
        bottom:0;
        left:0;
        position: fixed;
        width: 100%;
        height: 64px;
        background-color: #fff;
      }
      #t {
        position: absolute;
        width: 1%;
        height: 100%;
        background-color:#0063CE;
      }
      .ui-loading .ui-loader{display:block;}
      .ui-loader{display:none;position:absolute;opacity:.85;z-index:100;left:50%;width:200px;margin-left:-130px;margin-top:-35px;padding:10px 30px;}
    */ ?>
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
        <a data-req="ba" class="navbar-brand"><span class="glyphicon glyphicon-backward"></span></a>
        <a data-req="pp" class="navbar-brand"><span class="glyphicon glyphicon-play"></span></a>
        <a data-req="pp" class="navbar-brand hidden"><span class="glyphicon glyphicon-pause"></span></a>
        <a data-req="fo" class="navbar-brand"><span class="glyphicon glyphicon-forward"></span></a>
        <a data-req="dn" class="navbar-brand"><span class="glyphicon glyphicon-volume-down"></span></a>
        <a data-req="up" class="navbar-brand"><span class="glyphicon glyphicon-volume-up"></span></a>
      </div>
    </nav>
    <div id="c"></div>
    <div class="container-fluid">
      <div class="row-fluid">
        <div id="e" class="hidden alert alert-danger" role="alert"> </div>
        <div class="col-sm-12">
          <h5></h5>
          <div id="r" class="list-group"></div>
        </div>
      </div>
    </div>
    <nav class="navbar navbar-default navbar-fixed-bottom">
      <div id="tl"><div id="t" class="navbar-inverse"></div><span id="l"></span></div>
    </nav>
    <video id="a"><source src="" type="video/mp4"></video>
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
          $("#a").get(0).pause();
        }else{
          app.p = true;
          if($("#a source").attr("src").length > 0 ) $("#a").get(0).play();
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
        //playlist();
        audio();
        ui();
      }
      function close() {
        $(".edit").addClass("hidden");
        $("body").removeClass("body-edit");
      }
      function ra() {
        app.s = (app.s ? false : true);
        localStorage.video = JSON.stringify(app);
        ui();
      }
      function dn() {
        if($("#a").get(0).volume >= 0.1 && $("#a").get(0).volume <= 1 ) {
          $("#a").get(0).volume = $("#a").get(0).volume - 0.1;
        }
      }
      function up() {
        if($("#a").get(0).volume >= 0 && $("#a").get(0).volume <= 0.9 ) {
          $("#a").get(0).volume = $("#a").get(0).volume + 0.1;
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
      function audio() {
        // ??? //
        var init=1;
        $("#a").attr("src","").remove();
        if(app.f!== undefined) {
          $("body").append("<video id='a'><source src='"+decodeURIComponent(app.f).replace(/'/g,'%27')+"' type='video/mp4'></video>");
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
      function ui() {
        $(".glyphicon-play, .glyphicon-pause").parent().removeClass("hidden");
        if(app.p) {
          $(".glyphicon-play").parent().addClass("hidden");
        }else{
          $(".glyphicon-pause").parent().addClass("hidden");
        }
        var p = "&nbsp;&nbsp;&nbsp;";
        $("#t").html(p+app.n);
        $("#l").html(p+app.n);
        $("#playlist").html(app.l);
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
        if($("#a").get(0).duration) {
          $("#t").css({"width": $("#a").get(0).currentTime / $("#a").get(0).duration * 100 + "%"});
          bookmark();
        }
      }
      function bookmark() {
        app.t = $("#a").get(0).currentTime;
        localStorage.video = JSON.stringify(app);
      }
      $(document).on("click", "#tl",function(e){
        var percent = e.pageX / $(window).width();
        $("#a").get(0).currentTime = $("#a").get(0).duration * percent;
        bookmark();
        $("#t").css({"width":percent * 100 + "%"});
      });
      $(document).keyup(function(e) {
        if(!$("input").is(":focus")) {
          switch(e.keyCode) { 
            case 27:$("#q").focus();e.preventDefault();break;
            case 32:if($("#r .active").length==0){pp();}else{$("#r .active").click();}e.preventDefault();break;
            case 37:ra();e.preventDefault();break;
            case 38:if($("#r .active").length==0){up();}else{prev();}e.preventDefault();break;
            case 39:fo();e.preventDefault();break;
            case 40:if($("#r .active").length==0){dn();}else{next();}e.preventDefault();break;
          }
        }else{
          if(e.keyCode == 27) {$("input").blur();e.preventDefault();}
        }
      });
      var app = localStorage.video;
      $(document).ready(function() {
        if(app == null) {
          app = {"f":"","l":"Library","n":"","p":false,"s":false,"t":0,"v":0.5};
          fo();
        }else{
          app = JSON.parse(app);
          //playlist();
          audio();
          ui();
        }
      });
      /* TODO convert code */
      var o="click",p=false,f=false;
      /*** Events ***/
      /* Select Video */
      $(document).on(o, ".r", function(e){
        $(".r").removeClass("a");
        $(this).addClass("a");
        e.preventDefault();
      });
      /* Play Video */
      $(document).on(o, ".r.a", function(e){
        video(e);
        e.preventDefault();
      });
      $(document).on(o, "#tl",function(e){
        var percent = e.pageX / $(window).width();
        $("#v").get(0).currentTime = $("#v").get(0).duration * percent;
        $("#t").css({"width":percent * 100 + "%"});
      });
      function state() {
        return (p ? "icon-pause" : "icon-play");
      }
      function frame() {
        return (f ? "icon-compress" : "icon-expand");
      }
      $(document).on(o, "#o.i", function(e){
        $("#o").removeClass("i").addClass("a");
        $("#n").append("<i id='f' class='"+frame()+"' onclick='full()'></i><i class='icon-stop' onclick='exitVideo()'></i><i class='icon-backward' onclick='leftKey()'></i><i id='p' class='"+state()+"' onclick='playpause()'></i><i class='icon-forward' onclick='rightKey()'></i><div id='tl'><div id='t'></div></div>");
        $("#t").css({"width":($("#v").get(0).currentTime / $("#v").get(0).duration )* 100 + "%"});
        e.preventDefault();
      });
      $(document).on(o, "#o.a", function(e){
        $("#o").removeClass("a").addClass("i");
        $("#n > div,#n > i").remove();
        e.preventDefault();
      });
      function video(e) {
        var init=1;
        $("#c").hide();
        $("body").append("<video id='v'><source src='"+decodeURIComponent($(".r.a").data("href"))+"' type='video/mp4'></video><div id='o' class='i'></div><div id='n'></div>");
        $("#v").get(0).addEventListener("canplay",function() { 
          if(init == "1") { 
            init=0; 
            $("#v").get(0).currentTime=$(".r.a").data("time");
            $("#v").get(0).play();
            if($("#v").get(0).paused == false) p=true;
          }
        });
        if($(window).height() - $("#v").height() > 0) {
          $("#v").css({"top":($(window).height() - $("#v").height()) / 2+"px"});
        }else{
          $("#v").height($(window).height());
        }
        //update video. send id and currentTime if playing every 60 seconds.
      }
      /* Exit Video */
      function exitVideo() {
        if($(".r.a").data("time") != $("#v").get(0).currentTime) {
          $(".r.a").data("time",$("#v").get(0).currentTime);
          bookmarkVideo({f:$(".r.a").data("href"),t:$("#v").get(0).currentTime});
        }
        $("#c").show();
        $("#n,#o").remove();
        $("#v").attr("src","").remove();
        p=false;
        f=false;
      }
      function playpause() {
        $("#p").removeClass(state());
        if(p) {
          $("#v").get(0).pause();
          p=false;
          bookmarkVideo({f:$(".r.a").data("href"),t:$("#v").get(0).currentTime});
        }else{
          $("#v").get(0).play();
          p=true;
        }
        $("#p").addClass(state());
      }
      function full() {
        $("#f").removeClass(frame());
        if(f) {
          var video=$("#v");
          if(video.length != 0) {
            try { video.get(0).webkitExitFullscreen(); f=false; }
            catch(err) { }
            try { video.get(0).mozCancelFullscreen(); f=false; }
            catch(err) { }
            try { video.get(0).exitFullscreen(); f=false; }
            catch(err) { }
          }
        }else{
          enterKey();
        }
        $("#f").addClass(frame());
      }
      /* Bookmark Time */
      function bookmarkVideo(req) {
        $.post("video.php",req);
      }
      function enterKey(e) {
        var video=$("#v");
        if(video.length != 0) {
          try { video.get(0).webkitRequestFullscreen(); f=true; }
          catch(err) { }
          try { video.get(0).mozRequestFullscreen(); f=true; }
          catch(err) { }
          try { video.get(0).requestFullscreen(); f=true; }
          catch(err) { }
        }
      }
      function escapeKey(e) {
        if($("#v").length != 0) {
          exitVideo();
        }
      }
      function spaceKey(e) {
        var v=$("#v");
        if(v.length != 0) {
          playpause();
        }else{
          video(e);
        }
      }
      function leftKey(e) {
        var v=$("#v");
        if(v.length != 0) {
          v.get(0).currentTime=v.get(0).currentTime-10;
          $("#t").css({"width":($("#v").get(0).currentTime / $("#v").get(0).duration )* 100 + "%"});
        }
      }
      function upKey(e) {
        var that=$(".r.a");
        if($(".r.a").length==0){
          $(".r:first").addClass("a");
          $("html,body").animate({ scrollTop: $(".r.a").offset().top });
        }else{
          that.removeClass("a");
          if(that.is(":first-child")) {
            $(".r:last").addClass("a");
            $("#c").animate({ scrollTop: $(".r.a").index()*$(".r").height() },0);
          }else{
            that.prev().addClass("a");
            $("#c").animate({ scrollTop: $(".r.a").index()*$(".r").height() },0);
          }
        }
      }
      function rightKey(e) {
        var v=$("#v");
        if(v.length != 0) {
          v.get(0).currentTime=v.get(0).currentTime+10;
          $("#t").css({"width":($("#v").get(0).currentTime / $("#v").get(0).duration )* 100 + "%"});
        }
      }
      function downKey(e) {
        var that=$(".r.a");
        if(that.length==0){
          $(".r:first").addClass("a");
          $("html,body").animate({ scrollTop: $(".r.a").offset().top });
        }else{
          that.removeClass("a");
          if(that.is(":last-child")) {
            $(".r:first").addClass("a");
            $("#c").animate({ scrollTop: $(".r.a").index()*$(".r").height() },0);
          }else{
            that.next().addClass("a");
            $("#c").animate({ scrollTop: $(".r.a").index()*$(".r").height() },0);
          }
        }
      }
      function isTouchDevice(){ return typeof window.ontouchstart !== "undefined"; }
      /* Keyboard Shortcuts 
      $(document).keyup(function(e) {
        switch(e.keyCode) { 
          case 13:enterKey(e);e.preventDefault();break;
          case 27:escapeKey(e);e.preventDefault();break;
          case 32:spaceKey(e);e.preventDefault();break;
          case 72: case 37:leftKey(e);e.preventDefault();break;
          case 75: case 38:upKey(e);e.preventDefault();break;
          case 76: case 39:rightKey(e);e.preventDefault();break;
          case 74: case 40:downKey(e);e.preventDefault();break;
        }
      });
      */
      /* Initialize 
      $(document).ready(function() {
        if(isTouchDevice()) {
          o = "tap";
          $.getScript("mobile.js",function(data) { });
        }
        $.getJSON("video.php",function(res) {
          $("#c").html("");
          $.each(res,function(file,path) {
            $("#c").append("<div class='r' data-href='"+file+"' data-time='"+path.time+"' >"+
            decodeURIComponent(decodeURIComponent(file).slice(decodeURIComponent(file).lastIndexOf("/")+1,-4)));
          });
        });
      });
      */
      <?php /* $api = new API();
      class API {
        private $json = '../src/videos.json';
        function __construct() {
          if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
            $video=json_decode(file_get_contents($this->json),true);
            $video[$_POST['f']]['time']=$_POST['t'];
            file_put_contents($this->json,json_encode($video));
            exit;
          }
          if ($_SERVER['REQUEST_METHOD'] === 'GET') { 
            echo file_get_contents($this->json);
            exit;
          }
        }
      } */ ?>
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
