<?php

// side menu
$side_menu = array();

array_push($side_menu, array('name' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt nav-icon', 'url' => 'index', 'active' => 'active', 'menu' => 'menu-open', 'submenu' => ''));
array_push($side_menu, array('name' => 'ADC', 'icon' => 'fas fa-wave-square nav-icon', 'url' => '#', 'active' => '', 'menu' => '', 'submenu' => array(array('name' => 'Scope', 'icon' => 'fas fa-bullseye', 'url' => 'scope'))));
array_push($side_menu, array('name' => 'Modules', 'icon' => 'fas fa-cubes nav-icon', 'url' => '#', 'active' => '', 'menu' => '', 'submenu' => array(array('name' => 'List', 'icon' => 'fas fa-list', 'url' => 'blog_list'))));

array_push($side_menu, array('name' => 'Logs', 'icon' => 'fas fa-list nav-icon', 'url' => '#', 'active' => '', 'menu' => '', 'submenu' => array(array('name' => 'List', 'icon' => 'fas fa-list', 'url' => 'payment_list'))));
array_push($side_menu, array('name' => 'Settings', 'icon' => 'fas fa-cog nav-icon', 'url' => '#', 'active' => '', 'menu' => '', 'submenu' => array(array('name' => 'System', 'icon' => 'fas fa-list', 'url' => 'settings'))));

$current_url = $_GET['url'] ?? '';

?>
<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="./assets/img/logo.png" class="brand-logo" alt="Spicer Consulting Logo">
        <span class="brand-text">SDC</span>
    </div>

    <!-- User Panel -->

    <ul class="sidebar-menu">
        <li class="nav-header">MAIN NAVIGATION</li>

        <?php foreach ($side_menu as $item): ?>
            <?php if (!empty($item['submenu'])): ?>
                <li class="menu-toggle <?= $item['menu']; ?>">
                    <a href="#" onclick="toggleSubmenu(this); return false;" class="<?= $item['active']; ?>">
                        <i class="<?= $item['icon']; ?>"></i>
                        <span><?= $item['name']; ?></span>
                        <i class="fas fa-angle-left right"></i>
                    </a>
                    <ul class="nav-treeview">
                        <?php foreach ($item['submenu'] as $sub): ?>
                            <li class="nav-item">
                                <a href="<?= BASE_URL ?>/<?= $sub['url']; ?>"
                                    class="nav-link <?= ($current_url ?? '') === $sub['url'] ? 'active' : ''; ?>">
                                    <i class="<?= $sub['icon']; ?>"></i>
                                    <p><?= $sub['name']; ?></p>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>/<?= $item['url']; ?>"
                        class="nav-link <?= ($current_url ?? '') === $item['url'] ? 'active' : ''; ?>">
                        <i class="<?= $item['icon']; ?>"></i>
                        <span><?= $item['name']; ?></span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>

            <li>
        <a href="#" onclick="loadContent('Logout'); return false;">
            <a href="<?php echo BASE_URL; ?>/login/logout"><i class="fas fa-sign-out-alt nav-icon"></i> Log Out</a>

        </a>
    </li>
    </ul>
</aside>