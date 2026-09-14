<?php

// side menu
$side_menu = array();

array_push($side_menu, array('name' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'url' => 'dashboard', 'submenu' => ''));
array_push($side_menu, array('name' => 'ADC', 'icon' => 'fas fa-wave-square', 'url' => '#', 'submenu' => array(array('name' => 'Scope', 'icon' => 'fas fa-bullseye', 'url' => 'scope'))));
array_push($side_menu, array('name' => 'Modules', 'icon' => 'fas fa-cubes', 'url' => '#', 'submenu' => array(array('name' => 'List', 'icon' => 'fas fa-list', 'url' => 'blog_list'))));
array_push($side_menu, array('name' => 'Logs', 'icon' => 'fas fa-list', 'url' => '#', 'submenu' => array(array('name' => 'List', 'icon' => 'fas fa-list', 'url' => 'log_list'))));
array_push($side_menu, array('name' => 'Companies', 'icon' => 'fas fa-building', 'url' => '#', 'submenu' => array(array('name' => 'List', 'icon' => 'fas fa-list', 'url' => 'company_list'))));
array_push($side_menu, array('name' => 'Settings', 'icon' => 'fas fa-cog', 'url' => '#', 'submenu' => array(array('name' => 'System', 'icon' => 'fas fa-list', 'url' => 'settings'))));
array_push($side_menu, array('name' => 'Log Out', 'icon' => 'fas fa-sign-out-alt', 'url' => 'login/logout', 'submenu' => ''));

$current_url = trim($_GET['url'] ?? '', '/');
$system_name = isset($setting) ? ($setting->getSettings('f1') ?? 'SDC') : 'SDC';

?>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar sidebar-dark-primary elevation-4" id="sidebar">
    <!-- Brand Logo -->
    <a href="<?= BASE_URL; ?>/dashboard" class="brand-link">
        <img src="<?= BASE_URL; ?>/assets/img/logo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light"><?= $system_name; ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar sidebar-menu flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <?php foreach ($side_menu as $item): ?>
                    <?php
                    $has_submenu = is_array($item['submenu']) && !empty($item['submenu']);
                    $item_url = trim($item['url'], '/');
                    $submenu_active = false;

                    if ($has_submenu) {
                        foreach ($item['submenu'] as $sub_item) {
                            if ($current_url === trim($sub_item['url'], '/')) {
                                $submenu_active = true;
                                break;
                            }
                        }
                    }

                    $item_active = $current_url === $item_url || $submenu_active;
                    $menu_open = $has_submenu && $submenu_active;
                    $href = $item['url'] === '#' ? '#' : BASE_URL . '/' . ltrim($item['url'], '/');
                    ?>
                    <li class="nav-item <?= $has_submenu ? 'has-treeview' : ''; ?> <?= $menu_open ? 'menu-open' : ''; ?>">
                        <a href="<?= $href; ?>" class="nav-link <?= $item_active ? 'active' : ''; ?>" <?= $has_submenu ? 'onclick="toggleSubmenu(this); return false;"' : ''; ?>>
                            <i class="nav-icon <?= $item['icon']; ?>"></i>
                            <p>
                                <?= $item['name']; ?>
                                <?= $has_submenu ? '<i class="right fas fa-angle-left"></i>' : ''; ?>
                            </p>
                        </a>
                        <?php if ($has_submenu): ?>
                            <ul class="nav nav-treeview <?= $menu_open ? 'show' : ''; ?>">
                                <?php foreach ($item['submenu'] as $sub_item): ?>
                                    <?php $sub_active = $current_url === trim($sub_item['url'], '/'); ?>
                                    <li class="nav-item">
                                        <a href="<?= BASE_URL; ?>/<?= ltrim($sub_item['url'], '/'); ?>" class="nav-link <?= $sub_active ? 'active' : ''; ?>" style="font-size: 13px;">
                                            <i class="nav-icon <?= $sub_item['icon']; ?>"></i>
                                            <p><?= $sub_item['name']; ?></p>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</aside>
