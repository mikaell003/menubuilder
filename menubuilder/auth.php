<?php require 'config.php';
$err='';$reg=isset($_GET['register']);
if($_SERVER['REQUEST_METHOD']==='POST'){check();
 $email=strtolower(trim($_POST['email']??''));$pw=$_POST['password']??'';
 if($reg){
  $name=trim($_POST['name']);$slug=strtolower(trim($_POST['slug']));$wa=preg_replace('/\D/','',$_POST['whatsapp']);
  if(preg_match('/^0[17]\d{8}$/',$wa))$wa='254'.substr($wa,1);
  if(!$name||!filter_var($email,FILTER_VALIDATE_EMAIL))$err='Enter a valid name and email.';
  elseif(!preg_match('/^[a-z0-9-]{3,30}$/',$slug)||in_array($slug,['auth','dashboard','logout','menu','assets','uploads','config']))$err='Link must be 3-30 letters, numbers or dashes.';
  elseif(!preg_match('/^254\d{9}$/',$wa))$err='Use a Kenyan WhatsApp number e.g. 0712345678.';
  elseif(strlen($pw)<8)$err='Password needs 8+ characters.';
  else{try{$pdo->prepare('INSERT INTO restaurants(name,slug,whatsapp,email,password_hash) VALUES(?,?,?,?,?)')->execute([$name,$slug,$wa,$email,password_hash($pw,PASSWORD_DEFAULT)]);
   session_regenerate_id(true);$_SESSION['rid']=$pdo->lastInsertId();header('Location: dashboard.php');exit;}
   catch(PDOException $x){$err='That email or link is already taken.';}}
 }else{
  $s=$pdo->prepare('SELECT * FROM restaurants WHERE email=?');$s->execute([$email]);$r=$s->fetch();
  if($r&&password_verify($pw,$r['password_hash'])){session_regenerate_id(true);$_SESSION['rid']=$r['id'];header('Location: dashboard.php');exit;}
  $err='Wrong email or password.';}
}
head($reg?'Register':'Login');?>
<main class="card narrow"><h1><?=$reg?'Create your menu':'Welcome back'?></h1>
<?php if($err)echo '<p class="err">'.e($err).'</p>';?>
<form method="post"><input type="hidden" name="t" value="<?=csrf()?>">
<?php if($reg):?><label>Restaurant name<input name="name" required></label>
<label>Menu link<input name="slug" placeholder="mama-njeri" required></label>
<label>WhatsApp number<input name="whatsapp" placeholder="0712345678" required></label><?php endif;?>
<label>Email<input type="email" name="email" required></label>
<label>Password<input type="password" name="password" required></label>
<button><?=$reg?'Create account':'Log in'?></button></form>
<p><?=$reg?'<a href="auth.php">Have an account? Log in</a>':'<a href="auth.php?register=1">New here? Register free</a>'?></p></main></body></html>
