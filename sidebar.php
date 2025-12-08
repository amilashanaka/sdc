<?php

// side menu
$side_menu = array();

array_push($side_menu, array('name' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => 'index', 'active' => 'active', 'menu' => 'menu-open', 'submenu' => ''));
array_push($side_menu, array('name' => 'Staff', 'icon' => 'fas fa-user-tie', 'url' => 'admin_list', 'active' => '', 'menu' => '', 'submenu' => array(array('name' => 'List', 'icon' => 'fas fa-list', 'url' => 'admin_list?role=Mg=='))));
array_push($side_menu, array('name' => 'Users', 'icon' => 'fas fa-users', 'url' => 'user_list', 'active' => '', 'menu' => '', 'submenu' => array(array('name' => 'List', 'icon' => 'fas fa-list', 'url' => 'user_list'))));
array_push($side_menu, array('name' => 'Signals', 'icon' => 'fas fa-signal', 'url' => '#', 'active' => '', 'menu' => '', 'submenu' => array(array('name' => 'List', 'icon' => 'fas fa-list', 'url' => 'signal_list'))));
array_push($side_menu, array('name' => 'Blogs', 'icon' => 'fas fa-book', 'url' => '#', 'active' => '', 'menu' => '', 'submenu' => array(array('name' => 'List', 'icon' => 'fas fa-list', 'url' => 'blog_list'))));
array_push($side_menu, array('name' => 'Trader Course', 'icon' => 'fas fa-graduation-cap', 'url' => '#', 'active' => '', 'menu' => '', 'submenu' => array(array('name' => 'List', 'icon' => 'fas fa-list', 'url' => 'course_list'), array('name' => 'Request List', 'icon' => 'fas fa-plus', 'url' => 'course_request_list'))));
array_push($side_menu, array('name' => 'packages', 'icon' => 'fas fa-hand-holding-usd', 'url' => '#', 'active' => '', 'menu' => '', 'submenu' => array(array('name' => 'List', 'icon' => 'fas fa-list', 'url' => 'package_list'), array('name' => 'Request List', 'icon' => 'fas fa-plus', 'url' => 'package_request_list'))));
array_push($side_menu, array('name' => 'Payments', 'icon' => 'fas fa-money-check-alt', 'url' => '#', 'active' => '', 'menu' => '', 'submenu' => array(array('name' => 'List', 'icon' => 'fas fa-list', 'url' => 'payment_list'))));
array_push($side_menu, array('name' => 'Settings', 'icon' => 'fas fa-cog', 'url' => '#', 'active' => '', 'menu' => '', 'submenu' => array(array('name' => 'System', 'icon' => 'fas fa-list', 'url' => 'settings'), array('name' => 'Slides', 'icon' => 'fa fa-image', 'url' => 'slide_list'), array('name' => 'About Us', 'icon' => 'fas fa-info-circle', 'url' => 'aboutus')))); //Logout button
array_push($side_menu, array('name' => 'Log Out', 'icon' => ' fas  fa-sign-out-alt', 'url' => 'javascript:logout()', 'active' => '', 'menu' => '', 'submenu' => ''));



?>








<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index.php" class="brand-link">
        <img src="assets/img/logo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3 " style="opacity: .8">
        <span class="brand-text font-weight-light"> <?= $setting->getSettings('f1') ?? "System Name " ?></span>
    </a><!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <?php
                if (is_array($side_menu)) {
                    foreach ($side_menu as $item) {
                ?>
                        <li class="nav-item has-treeview  <?= $item['menu'] ?>">
                            <a href="<?= $item['url'] ?>" class="nav-link  <?= $item['active'] ?>">
                                <i class="nav-icon  <?= $item['icon'] ?>"></i>
                                <p>
                                    <?= $item['name'] ?>
                                    <?= is_array($item['submenu']) ? '<i class="right fas fa-angle-left"></i>' : '' ?>
                                </p>
                            </a>
                            <?php if (is_array($item['submenu'])) {
                                foreach ($item['submenu']  as $sub_item) { ?>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="<?= $sub_item['url'] ?>" class="nav-link" style="font-size: 13px;">
                                                <i class="nav-icon <?= $sub_item['icon'] ?>"></i>
                                                <p><?= $sub_item['name'] ?></p>
                                            </a>
                                        </li>
                                    </ul>
                            <?php }
                            }  ?>
                        </li>
                <?php }
                } ?>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>