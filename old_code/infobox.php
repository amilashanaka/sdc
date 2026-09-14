<?php
$infobox = [
    ['caption'=>'Scope','items'=>$user->get_count(),'icon'=>'fas fa-wave-square','bg'=>'bg-secondary','url'=>'scope'],
  ['caption'=>'Modules','items'=>$signal->get_count(),'icon'=>'fas fa-cubes','bg'=>'bg-info','url'=>'module_list'],
  ['caption'=>'Logs','items'=>$blog->get_count(),'icon'=>'fas fa-book','bg'=>'bg-danger','url'=>'blog_list'],
  ['caption'=>'Admins','items'=>$admin->get_count(),'icon'=>'fas fa-user-tie','bg'=>'bg-success','url'=>'admin_list'],
  ['caption'=>'Helath','items'=>$user->get_count(),'icon'=>'fas fa-heart','bg'=>'bg-warning','url'=>'health_list'],
  ['caption'=>'Settings','items'=>$user->get_count(),'icon'=>'fas fa-cog','bg'=>'bg-primary','url'=>'settings'],

];
?>
<div class="container">
  <div class="row">
    <?php foreach ($infobox as $info): ?>
      <div class="col">
        <a href="<?= $info['url'] ?>"> <div class="info-box">
          <span class="info-box-icon <?= $info['bg'] ?> elevation-1"><i class="<?= $info['icon'] ?>"></i></span>
         <div class="info-box-content">
            <span class="info-box-text"><?= $info['caption'] ?></span>
            <span class="info-box-number"><?= $info['items'] ?></span>
          </div>
        </div></a>
      </div>
    <?php endforeach; ?>
  </div>
</div>
