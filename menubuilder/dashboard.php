<?php require 'config.php';$r=need();$rid=$r['id'];$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){check();$a=$_POST['a']??'';
 if($a==='cat'&&trim($_POST['name']??''))$pdo->prepare('INSERT INTO categories(restaurant_id,name) VALUES(?,?)')->execute([$rid,trim($_POST['name'])]);
 if($a==='delcat')$pdo->prepare('DELETE FROM categories WHERE id=? AND restaurant_id=?')->execute([(int)$_POST['id'],$rid]);
 if($a==='toggle')$pdo->prepare('UPDATE items SET available=1-available WHERE id=? AND restaurant_id=?')->execute([(int)$_POST['id'],$rid]);
 if($a==='delitem'){$pdo->prepare('DELETE FROM items WHERE id=? AND restaurant_id=?')->execute([(int)$_POST['id'],$rid]);}
 if($a==='item'){
  $c=$pdo->prepare('SELECT id FROM categories WHERE id=? AND restaurant_id=?');$c->execute([(int)$_POST['cat'],$rid]);
  $price=(float)$_POST['price'];$name=trim($_POST['name']);
  if(!$c->fetch()||!$name||$price<0)$msg='Pick a category, name and valid price.';
  else{$img=upload($_FILES['image']??[]);$id=(int)($_POST['id']??0);
   if($id){$pdo->prepare('UPDATE items SET category_id=?,name=?,description=?,price=? WHERE id=? AND restaurant_id=?')->execute([(int)$_POST['cat'],$name,trim($_POST['desc']),$price,$id,$rid]);
    if($img)$pdo->prepare('UPDATE items SET image=? WHERE id=? AND restaurant_id=?')->execute([$img,$id,$rid]);}
   else $pdo->prepare('INSERT INTO items(restaurant_id,category_id,name,description,price,image) VALUES(?,?,?,?,?,?)')->execute([$rid,(int)$_POST['cat'],$name,trim($_POST['desc']),$price,$img]);}}
 if(!$msg){header('Location: dashboard.php');exit;}}
$cats=$pdo->prepare('SELECT * FROM categories WHERE restaurant_id=?');$cats->execute([$rid]);$cats=$cats->fetchAll();
$it=$pdo->prepare('SELECT * FROM items WHERE restaurant_id=? ORDER BY id DESC');$it->execute([$rid]);$items=$it->fetchAll();
$edit=null;if(isset($_GET['edit']))foreach($items as $i)if($i['id']==(int)$_GET['edit'])$edit=$i;
$url=(isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].'/'.$r['slug'];
head('Dashboard');?>
<header class="bar"><b><?=e($r['name'])?></b><span><a class="btn" target="_blank" href="/<?=e($r['slug'])?>">Preview menu</a> <a href="logout.php">Logout</a></span></header>
<main class="grid">
<section class="card"><h2>Your public link</h2><p><a href="<?=e($url)?>" target="_blank"><?=e($url)?></a></p>
<div id="qr"></div><button onclick="dl()">Download QR</button></section>
<section class="card"><h2>Categories</h2>
<form method="post" class="row"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="a" value="cat"><input name="name" placeholder="e.g. Mains" required><button>Add</button></form>
<?php foreach($cats as $c):?><form method="post" class="row"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="a" value="delcat"><input type="hidden" name="id" value="<?=$c['id']?>"><span><?=e($c['name'])?></span><button class="ghost" onclick="return confirm('Delete category and its dishes?')">Delete</button></form><?php endforeach;?></section>
<section class="card wide"><h2><?=$edit?'Edit':'Add'?> dish</h2><?php if($msg)echo '<p class="err">'.e($msg).'</p>';?>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="a" value="item"><input type="hidden" name="id" value="<?=$edit['id']??0?>">
<label>Name<input name="name" value="<?=e($edit['name']??'')?>" required></label>
<label>Category<select name="cat"><?php foreach($cats as $c)echo '<option value="'.$c['id'].'"'.(($edit['category_id']??0)==$c['id']?' selected':'').'>'.e($c['name']).'</option>';?></select></label>
<label>Price (KES)<input type="number" step="1" min="0" name="price" value="<?=e($edit['price']??'')?>" required></label>
<label>Description<input name="desc" maxlength="255" value="<?=e($edit['description']??'')?>"></label>
<label>Photo (JPG/PNG/WebP, max 2MB)<input type="file" name="image" accept="image/*"></label>
<button>Save dish</button></form></section>
<section class="card wide"><h2>Dishes (<?=count($items)?>)</h2>
<?php foreach($items as $i):?><div class="row"><span><?=e($i['name'])?> — KES <?=number_format($i['price'])?><?=$i['available']?'':' (sold out)'?></span><span>
<a href="?edit=<?=$i['id']?>">Edit</a>
<form method="post" style="display:inline"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$i['id']?>"><button class="ghost" name="a" value="toggle"><?=$i['available']?'Mark sold out':'Mark available'?></button><button class="ghost" name="a" value="delitem" onclick="return confirm('Delete dish?')">Delete</button></form></span></div><?php endforeach;?></section>
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>new QRCode(document.getElementById('qr'),{text:<?=json_encode($url)?>,width:200,height:200});
function dl(){const c=document.querySelector('#qr canvas');const a=document.createElement('a');a.href=c.toDataURL('image/png');a.download='menu-qr.png';a.click();}</script></body></html>
