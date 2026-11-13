<?php
session_start();
$pdo=new PDO('mysql:host=localhost;dbname=menubuilder;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function csrf(){return $_SESSION['t']??=bin2hex(random_bytes(16));}
function check(){if(!hash_equals($_SESSION['t']??'',$_POST['t']??''))die('Invalid request');}
function need(){global $pdo;if(!empty($_SESSION['rid'])){$s=$pdo->prepare('SELECT * FROM restaurants WHERE id=?');$s->execute([$_SESSION['rid']]);if($r=$s->fetch())return $r;}header('Location: auth.php');exit;}
function upload($f){
 if(empty($f['tmp_name'])||$f['error']!==0||$f['size']>2097152)return null;
 $m=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
 $x=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$m]??null;
 if(!$x)return null;
 $n=bin2hex(random_bytes(8)).".$x";
 return move_uploaded_file($f['tmp_name'],__DIR__."/uploads/$n")?$n:null;}
function head($t){echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.e($t).'</title><link rel="stylesheet" href="/assets/style.css"></head><body>';}
