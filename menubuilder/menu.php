<?php require 'config.php';
$s=$pdo->prepare('SELECT * FROM restaurants WHERE slug=?');$s->execute([$_GET['s']??'']);$r=$s->fetch();
if(!$r){http_response_code(404);head('Not found');echo '<main class="card narrow"><h1>Menu not found</h1></main>';exit;}
$c=$pdo->prepare('SELECT * FROM categories WHERE restaurant_id=?');$c->execute([$r['id']]);$cats=$c->fetchAll();
$i=$pdo->prepare('SELECT * FROM items WHERE restaurant_id=? ORDER BY available DESC,id');$i->execute([$r['id']]);
$by=[];foreach($i->fetchAll() as $x)$by[$x['category_id']][]=$x;
head($r['name'].' Menu');?>
<header class="hero"><h1><?=e($r['name'])?></h1></header>
<nav class="cats"><?php foreach($cats as $k)if(!empty($by[$k['id']]))echo '<a href="#c'.$k['id'].'">'.e($k['name']).'</a>';?></nav>
<main class="menu"><?php foreach($cats as $k)if(!empty($by[$k['id']])):?><h2 id="c<?=$k['id']?>"><?=e($k['name'])?></h2>
<?php foreach($by[$k['id']] as $x):$txt=rawurlencode("Hello {$r['name']}, I'd like to order: {$x['name']}");?>
<article class="dish<?=$x['available']?'':' off'?>"><?php if($x['image'])echo '<img loading="lazy" src="/uploads/'.e($x['image']).'" alt="'.e($x['name']).'">';?>
<div><h3><?=e($x['name'])?></h3><p><?=e($x['description'])?></p><b>KES <?=number_format($x['price'])?></b>
<?=$x['available']?'<a class="wa" href="https://wa.me/'.e($r['whatsapp']).'?text='.$txt.'">Order on WhatsApp</a>':'<span class="so">Sold out</span>'?></div></article>
<?php endforeach;endif;?></main></body></html>
