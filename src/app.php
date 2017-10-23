<?php $automagic = new automagic();
class automagic {
  function __construct() {
    if(PHP_SAPI==='cli') {
      if($_SERVER['argc']==2) {
        chdir(dirname(__FILE__));
        if(file_exists($_SERVER['argv'][1])) {
          $req=false;
          $res=false;
          $json = json_decode(file_get_contents($_SERVER['argv'][1]));
          if($json) {
            foreach($json as $file) {
              if(file_exists($file)) {
                $req = json_decode(file_get_contents($file));
                $parts = explode('/',$file);
                if(count($parts)>1) {
                  $method = $parts[count($parts)-2];
                  if(method_exists($this,$method)) {
                    $res = $this->$method($req,$res);
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  private function loop($req=false,$res=false) {
  }
  private function say($req=false,$res=false) {
    return $req;
  }

  private function curl($req=false,$res=false) {
  }
  private function email($req=false,$res=false) {
  }
  private function ssh($req=false,$res=false) {
  }

  private function escape($req=false,$res=false) {
    $escape=false;
    if($req&&$res) {
      $mysqli=new mysqli($res['host'],$res['user'],$res['pass'],$res['name']);
      if(!$mysqli->connect_errno) $escape = $mysqli->real_escape_string($req);
    }
    return $escape;
  }
  private function insert($req=false,$res=false) {
    $insert=false;
    if($req&&$res) {
      $mysqli=new mysqli($res['host'],$res['user'],$res['pass'],$res['name']);
      if(!$mysqli->connect_errno){
        $key = '';
        $val = '';
        foreach($req['sql'] as $k => $v) {
          $key .= '`'.$k.'`,';
          $val .= '\''.$mysqli->real_escape_string($v).'\',';
        }
        if($mysqli->query('INSERT INTO `'.$req['tbl'].'` ('.rtrim($key,',').') VALUES ('.rtrim($val,',').')')) {
          $insert = $mysqli->insert_id;
        }else{
          $insert = $mysqli->error;
        }
        $mysqli->close();
      }
    }
    return $insert;
  }
  private function single($req=false,$res=false) {
    $single=false;
    if($req&&$res) {
      $mysqli=new mysqli($res['host'],$res['user'],$res['pass'],$res['name']);
      if(!$mysqli->connect_errno){
        $result = $mysqli->query($req);
        if(is_object($result)) {
          if($result->num_rows > 0) {
            $single = $result->fetch_assoc();
          }
        }
        $mysqli->close();
      }
    }
    return $single;
  }
  private function select($req=false,$res=false) {
    $select=false;
    if($req&&$res) {
      $mysqli=new mysqli($res['host'],$res['user'],$res['pass'],$res['name']);
      if(!$mysqli->connect_errno){
        $result = $mysqli->query($req);
        if(is_object($result)) {
          if($result->num_rows > 0) {
            while( $row = $result->fetch_assoc() ) {
              $select[] = $row;
            }
          }
        }
        $mysqli->close();
      }
    }
    return $select;
  }
  private function update($req=false,$res=false) {
    $update=false;
    if($req&&$res) {
      $mysqli=new mysqli($res['host'],$res['user'],$res['pass'],$res['name']);
      if(!$mysqli->connect_errno){
        $set = '';
        foreach($req['set'] as $k => $v) {
          $set .= '`'.$k.'`=\''.$mysqli->real_escape_string($v).'\',';
        }
        if($mysqli->query('UPDATE `'.$req['tbl'].'` SET '.rtrim($set,',').' WHERE '.$req['sql'])) {
          $update = true;
        }else{
          $update = $mysqli->error;
        }
        $mysqli->close();
      }
    }
    return $update;
  }
} #/automagic ?>
