<?php $api = new API(); ?><!DOCTYPE html><html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Audio</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="ico/audio.ico" rel="icon" type="image/x-icon">
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
      button.update { margin-left: 1em; }
      #e { position: fixed; right: 1em; z-index: 100; }
      #l { line-height: 50px; }
      #t { color: #9d9d9d; height: 100%; line-height: 50px; overflow: hidden; position: absolute; width: 0%; white-space: nowrap; }
      #tl { bottom:0; height: 50px; left:0; position: absolute; right:0; width: 100%; }
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
        <a data-req="pp" class="navbar-brand"><span class="glyphicon glyphicon-play"></span></a>
        <a data-req="pp" class="navbar-brand hidden"><span class="glyphicon glyphicon-pause"></span></a>
        <a data-req="fo" class="navbar-brand"><span class="glyphicon glyphicon-forward"></span></a>
        <a data-req="ra" class="navbar-brand"><span class="glyphicon glyphicon-random"></span></a>
        <a data-req="dn" class="navbar-brand"><span class="glyphicon glyphicon-volume-down"></span></a>
        <a data-req="up" class="navbar-brand"><span class="glyphicon glyphicon-volume-up"></span></a>
        <a data-req="li" class="navbar-brand"><span class="glyphicon glyphicon-list"></span></a>
        <div class="edit hidden navbar-header">
          <form id="edit" method="POST" >
            <div class="form-group">
              <input type="hidden" name="req" value="edit" />
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
        <div class="col-sm-8">
          <h5></h5>
          <div id="r" class="list-group"></div>
        </div>
        <div class="col-sm-4">
          <h5><span class="glyphicon glyphicon-list"></span> <span id="playlist"></span></h5>
          <div id="p" class="list-group"></div>
        </div>
      </div>
    </div>
    <nav class="navbar navbar-default navbar-fixed-bottom">
      <div id="tl"><div id="t" class="navbar-inverse"></div><span id="l"></span></div>
    </nav>
    <audio id="a"><source src="" type="audio/mp3"></audio>
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
              google();
            });
          }else{
            if($("#r .y").length == 0) google();
          }
        }else{
          reset();
        }
      });
      $("form#edit").on("submit",function(e) {
        e.preventDefault();
        $.ajax({
          type: "POST",
          data: $(this).serialize()
        }).fail(function() {
          err("Unable to Update");
        });
        $(".edit").addClass("hidden");
        $("body").removeClass("body-edit");
      });
      $(document).on("submit","form#create",function(e) {
        e.preventDefault();
        var l = $("#create .create").val();
        if(l.length > 0) {
          $.ajax({
            type: "POST",
            data: "req=create&"+$(this).serialize()
          }).done(function(res) {
            var k = $("#r .l").length;
            $("#r").append("<a id='list-"+k+"' class='l list-group-item '>"+l+"<button class='btn btn-default btn-xs pull-right'><span class='glyphicon glyphicon-minus-sign'></span> Playlist</button></a>");
            $("#list-"+k).data("l",l);
            $("#create .create").val("");
          }).fail(function() {
            $("#create .create").val("");
          });
        }
      });
      function search(req) {
        ( req ? $(".form-control-feedback").removeClass("glyphicon-search").addClass("glyphicon-hourglass") : $(".form-control-feedback").removeClass("glyphicon-hourglass").addClass("glyphicon-search") );
      }
      function google() {
        $("#q").blur();
        $("#r .g.disabled").remove();
        search(true);
        $.ajax({
          type: "POST",
          data: "req=google&y="+$("#q").val()
        }).done(function(res) {
          search(false);
          var html = "";
          $.each(res,function(k,v){
            html += "<a class='y list-group-item' data-d='"+v.u+"'>";
            html += "<h4 class='list-group-item-heading'>"+v.n+"</h4>";
            html += "<p class='list-group-item-text'>"+v.u+"</p>";
            if(v.i!==undefined) html += "<img src='"+v.i+"' />";
            html += "</a>";
          });
          $("#r").append(html);
          if($("#r .active").length==0) {$("#r a:first").addClass("active");}
        }).fail(function() {
          search(false);
          $("#r").append("<a class='y list-group-item disabled'>Your search - <b>"+$("#q").val()+"</b> - did not match any documents.</a>");
        });
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
          localStorage.audio = JSON.stringify(app);
          reset();
          ui();
        }
      });
      $(document).on("click",".navbar-brand",function(e) {
        switch ($(this).data("req")) {
          case "pp":pp();break;
          case "fo":fo();break;
          case "ra":ra();break;
          case "dn":dn();break;
          case "up":up();break;
          case "li":list();break;
          case "ce":close();break;
          case "tn":trash();break;
        }
      });
      $(document).on("click","#p a,#r .g:not(.disabled) button:not(.update)",function(e) {
        $(".edit").removeClass("hidden");
        $("body").addClass("body-edit");
        $("#f").val(($(this).data("f")===undefined?$(this).parent().data("f"):$(this).data("f")));
        $("#n").val(($(this).data("n")===undefined?$(this).parent().data("n"):$(this).data("n"))).focus();
        e.stopPropagation();
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
          var stale = false;
          $.each($("#p a"),function(k,v) {
            if($(this).data("f") == res.f) stale = true;
          });
          if(stale) {
            fo();
          }else{
            play({"f":res.f,"n":res.n,"t":0});
          }
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
        localStorage.audio = JSON.stringify(app);
        playlist();
        audio();
        ui();
      }
      function list() {
        reset();
        if($("#create").length == 0) {
          $.ajax({
            type: "POST",
            data: "req=list"
          }).done(function(res) {
            $(".col-sm-8 h5").html("<span class='glyphicon glyphicon-list'></span> Playlists");
            $("#r").html("<div class='list-group-item list-group-item-success'><form id='create'><div class='input-group'><input class='create form-control' name='l' type='text' placeholder='Create' /><div class='input-group-btn'><button class='btn btn-default'><span class='glyphicon glyphicon-plus-sign'></span> Playlist</button></div></div></form></div>");
            if(app.l===undefined) app.l="";
            $.each(res,function(k,v) {
              var a = "";
              if(app.l == v) a = "active";
              $("#r").append("<a id='list-"+k+"' class='l list-group-item "+a+"'>"+v+"<button class='btn btn-default btn-xs pull-right'><span class='glyphicon glyphicon-minus-sign'></span> Playlist</button></a>");
              $("#list-"+k).data("l",v);
            });
          }).fail(function() {
            err("Failed Playlist Retreival");
          });
        }
      }
      function close() {
        $(".edit").addClass("hidden");
        $("body").removeClass("body-edit");
      }
      function trash() {
        $.ajax({
          type: "POST",
          data: "req=trash&f="+$("#f").val()
        }).done(function(res) {
          $(".list-group").find("[data-f='"+$("#f").val()+"']").remove();
          close();
        }).fail(function() {
          err("Failed to Delete File");
        });
      }
      function playlist() {
        $("#p").prepend("<a class='list-group-item'>"+app.n+"</a>");
        $("#p a").first().attr("data-f",app.f).attr("data-n",app.n);
        if($("#p a").length > 20 ) $("#p a").last().remove();
      }
      function ra() {
        app.s = (app.s ? false : true);
        localStorage.audio = JSON.stringify(app);
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
      function ui() {
        $(".glyphicon-play, .glyphicon-pause").parent().removeClass("hidden");
        if(app.p) {
          $(".glyphicon-play").parent().addClass("hidden");
        }else{
          $(".glyphicon-pause").parent().addClass("hidden");
        }
        if(app.s) {
          $(".glyphicon-random").addClass("text-info");
        }else{
          $(".glyphicon-random").removeClass("text-info");
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
        $("#r,.col-sm-8 h5").html("");
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
        localStorage.audio = JSON.stringify(app);
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
      var app = localStorage.audio;
      $(document).ready(function() {
        if(app == null) {
          app = {"f":"","l":"Library","n":"","p":false,"s":false,"t":0,"v":0.5};
          fo();
        }else{
          app = JSON.parse(app);
          playlist();
          audio();
          ui();
        }
      });
    </script>
  </body>
</html><?php class API {
  private $d = '';
  private $m = 'meta';
  private $l = 'Library';
  private $p = './mp3/';
  # required: youtube-dl
  # required: libav-tools
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

                $this->d = dirname(__FILE__);
                if(!isset($_POST['req'])) $_POST['req'] = '';
                switch($_POST['req']) {
                  default: $res = $this->fetch($_POST['s'],(isset($_POST['f'])?$_POST['f']:''),(isset($_POST['l'])?$_POST['l']:''));break;
                  case 'create': $res = $this->create($_POST['l']);break;
                  case 'update': $res = $this->update($_POST['f'],$_POST['l']);break;
                  case 'delete': $res = $this->delete($_POST['l']);break;
                  case 'download': $res = $this->download($_POST['d'],$_POST['q']);break;
                  case 'list': $res = $this->files($this->p,false);break;
                  case 'search': $res = $this->search($_POST['q']);break;
                  case 'edit': $res = $this->edit($_POST['n'],$_POST['f']);break;
                  case 'google': $res = $this->google($_POST['y']);break;
                  case 'trash': $res = $this->trash($_POST['f']);break;
                  case 'refresh': $res = $this->refresh();break;
                }
              }
            }
          }
        }
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
  private function download($url='',$n ='',$p = '') {
    $res = false;
    if(!empty($url) ) {
      exec($p.'youtube-dl --get-id '.$url,$id,$ret);
      if(!$ret) {
        if(count($id) == 1) {
          if(file_exists($this->p.$this->m)) $meta = json_decode(file_get_contents($this->p.$this->m),true);
          $meta[$id[0]] = $n;
          file_put_contents($this->p.$this->m,json_encode($meta));
          chdir($this->p);
          exec($p.'youtube-dl -w -x --id --audio-format mp3 '.$url,$dl,$err);
          if(!$err) {
            chmod($id[0].'.mp3',0666);
            chdir($this->d);
            $res = $this->refresh();
          }
        }else{
          echo 'Multiple IDs: Playlist?';
        }
      }else{
        if(empty($p)) $res = $this->install($url,$n);
      }
    }
    return $res;
  }
  private function install($url='',$n='') {
    $p = '/tmp/';
    $c = 'youtube-dl';
    if(!file_exists($p.$c)) {
      $data = '';
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'https://yt-dl.org/downloads/latest/youtube-dl');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      $data = curl_exec($ch);
      $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      if(!empty($data)) {
        file_put_contents($p.$c,$data);
        chmod($p.$c,0755);
      }
    }
    return $this->download($url,$n,$p);
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
  private function google($req) {
    if(!empty($req)) {
      //search soundcloud as well.
      $dom = new DOMDocument('1.0');
      @$dom->loadHTMLFile('https://www.google.com/search?q='.htmlentities('site:www.youtube.com '.$req));
      $res = array();
      $h = $dom->getElementsByTagName('h3');
      $i = $dom->getElementsByTagName('img');
      $c = $dom->getElementsByTagName('cite');
      foreach($h as $k => $v) {
        $res[$k]['n'] = $v->nodeValue;
      }
      foreach($i as $k => $v) {
        $res[$k]['i'] = $v->getAttribute('src');
      }
      foreach($c as $k => $v) {
        $res[$k]['u'] = $v->nodeValue;
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