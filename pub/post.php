<?php $api = new API(); ?>
<!DOCTYPE html><html><head>
  <title>GET/POST CLIENT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
      body { padding-top: 1em; }
      #type { font-family: monospace; }
      #text { border: 1px solid #666; }
    </style>
</head>
<body>
  <div class="col-xs-6">
    <form class="form-horizontal">
      <div class="container-fluid">
        <div class="row-fluid">
          <div class="form-group">
            <div class="col-xs-12">
              <div class="input-group">
                <span class="input-group-btn">
                  <button id="type" class="btn btn-default" type="button">POST</button>
                </span>
                <input id="method" name="method" type="hidden" value="post">
                <input id="url" name="url" type="text" class="form-control" placeholder="url" data-cip-id="url">
                <span class="input-group-btn">
                  <button id="send" class="btn btn-default" type="button">Send &gt;</button>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <fieldset id="headers" class="container-fluid">
        <legend>Headers</legend>
        <div class="row-fluid">
          <div class="form-group">
            <div class="col-xs-5">
              <input name="headkey[]" class="form-control" type="text" placeholder="key" data-cip-id="cIPJQ342845639">
            </div>
            <div class="col-xs-5">
              <input name="headval[]" class="form-control" type="text" placeholder="val" data-cip-id="cIPJQ342845640">
            </div>
            <div class="col-xs-2">
              <div class="minus col-xs-12 btn btn-default hidden">-</div>
              <div id="headplus" class="col-xs-12 btn btn-primary">+</div>
            </div>
        </div>
      </div></fieldset>
      <fieldset id="fields" class="container-fluid">
        <legend>Fields</legend>
        <div class="row-fluid"><div class="form-group"><div class="col-xs-4"><input name="key[]" class="form-control" type="text" placeholder="key"></div><div class="col-xs-5"><input name="val[]" class="form-control" type="text" placeholder="val"></div><div class="col-xs-2"><div class="minus col-xs-12 btn btn-default hidden">-</div><div id="plus" class="col-xs-12 btn btn-primary">+</div></div></div></div></fieldset>
    </form>
  </div>
  <div class="col-xs-6">
    <div id="response" class="container-fluid"></div>
    <div class="container-fluid">
      <div id="text" contenteditable="true"></div>
    </div>
  </div>
  <script src="js/jquery.min.js"></script>
  <script>
    $(document).ready(function() {
      if($('body').css('color') != 'rgb(51, 51, 51)') {
        $localbootstrap = $('<link rel="stylesheet" href="bootstrap.min.css">');
        $("head").prepend($localbootstrap);
        $localbootstrap.on('load',function() { $('#init').remove(); });
      }else{ $('#init').remove(); }
    });
    $(document).on("click","#type",function() {
      switch($("#method").val()) {
        case "get": $(this).html("POST");$("#method").val("post"); break;
        case "post": $(this).html("GET&nbsp;");$("#method").val("get");break;
      }
    });
    $(document).on("click",".minus",function() {
      $(this).closest(".row-fluid").remove();
    });
    $(document).on("click","#headplus",function() {
      $("#headplus").remove();
      $("#headers .minus").removeClass("hidden");
      $("#headers").append("<div class='row-fluid'><div class='form-group'><div class='col-xs-5'><input name='headkey[]' class='form-control' type='text' placeholder='key' /></div><div class='col-xs-5'><input name='headval[]' class='form-control' type='text' placeholder='val' /></div><div class='col-xs-2'><div class='minus col-xs-12 btn btn-default hidden'>-</div><div id='headplus' class='col-xs-12 btn btn-primary'>+</div></div></div></div>");
    });
    $(document).on("click","#plus",function() {
      $("#plus").remove();
      $("#fields .minus").removeClass("hidden");
      $("#fields").append("<div class='row-fluid'><div class='form-group'><div class='col-xs-5'><input name='key[]' class='form-control' type='text' placeholder='key' /></div><div class='col-xs-5'><input name='val[]' class='form-control' type='text' placeholder='val' /></div><div class='col-xs-2'><div class='minus col-xs-12 btn btn-default hidden'>-</div><div id='plus' class='col-xs-12 btn btn-primary'>+</div></div></div></div>");
    });
    $(document).on("click","#send",function() {
      $.ajax({
        type: "POST",
        data: $("form").serialize(), 
        success: function(res) {
          $("#response").html(res);
        }
      });
    });
  </script>
</body></html>
<?php class API {
  function __construct() {
    if($_SERVER['REQUEST_METHOD']==='POST') {
      if(isset($_POST['url'])) {
        $req = array(
          CURLOPT_URL => $_POST['url'],
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_USERAGENT => 'curl',
        );
        if(isset($_POST['method'])) {

          switch($_POST['method']) {
            case 'post':
              $post = array();
              foreach($_POST['key'] as $k => $v) {
                $post[$v]=$_POST['val'][$k];
              }
              $req[CURLOPT_POST]=1;
              $req[CURLOPT_POSTFIELDS]=$post;
            break;
            case 'get':

            break;
          }

          $ch = curl_init();
          curl_setopt_array($ch, $req);
          $res = curl_exec($ch);
          $info = curl_getinfo($ch);
          $error = curl_error($ch);
          curl_close($ch);

          echo '<pre>';
          print_R($_POST);
          echo '<hr />';
          print_R($info);
          echo '<hr />';
          print_R($res);
          echo '</pre>';
          exit;

        }
      }
    }
  }
}#/API ?>
