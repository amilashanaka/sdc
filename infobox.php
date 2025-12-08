<?php
$infobox = [
  ['caption'=>'Signals','items'=>$signal->get_count(),'icon'=>'fas fa-signal','bg'=>'bg-info','url'=>'signal_list'],
  ['caption'=>'Blogs','items'=>$blog->get_count(),'icon'=>'fas fa-book','bg'=>'bg-danger','url'=>'blog_list'],
  ['caption'=>'Admins','items'=>$admin->get_count(),'icon'=>'fas fa-user-tie','bg'=>'bg-success','url'=>'admin_list'],
  ['caption'=>'Users','items'=>$user->get_count(),'icon'=>'fas fa-users','bg'=>'bg-warning','url'=>'user_list'],
];
?>
<div class="container">
  <div class="row">
    <?php foreach ($infobox as $info): ?>
      <div class="col">
        <div class="info-box">
          <span class="info-box-icon <?= $info['bg'] ?> elevation-1"><i class="<?= $info['icon'] ?>"></i></span>
          <div class="info-box-content">
            <span class="info-box-text"><?= $info['caption'] ?></span>
            <a href="<?= $info['url'] ?>"><span class="info-box-number"><?= $info['items'] ?></span></a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
