<?php
// page_list.php - Enhanced version with DataTables + Export buttons + Improved UI & Optimizations + Dark Mode Support

// Default values with better type hinting and fallbacks
$heading      = $form_config['heading'] ?? 'Records List';
$title        = $form_config['title'] ?? 'List';
$model        = $form_config['model'] ?? null;
$method       = $form_config['method'] ?? 'get_all';
$table_config = $form_config['table'] ?? [];
$buttons      = $form_config['buttons'] ?? [];
$custom_buttons = $form_config['custom_buttons'] ?? []; // New: Custom buttons for header/actions
$status       = $form_config['status'] ?? [];
$layout       = $form_config['layout'] ?? [];
$exports      = $form_config['exports'] ?? []; // New: Export config from form_config
$method_params = $form_config['method_params'] ?? []; // New: Parameters to pass to model method

// Fetch data efficiently
$data = [];
if ($model && class_exists(ucfirst($model))) {
    $modelObj = new (ucfirst($model))(); // Ensure model name is capitalized
    if (method_exists($modelObj, $method)) {
        $data = $modelObj->$method(...$method_params);
    }
}

// Pre-load foreign key data for columns with fk => true
$fkCache = [];
foreach ($table_config['columns'] ?? [] as $col) {
    if (!empty($col['fk']) && !empty($col['model']) && !empty($col['show'])) {
        $fkModelName = ucfirst($col['model']);
        $fkShowField = $col['show'];
        $fkKeyField = $col['fk_key'] ?? 'id'; // Default to 'id' as the key field
        
        if (class_exists($fkModelName) && !isset($fkCache[$col['model']])) {
            $fkModelObj = new $fkModelName();
            if (method_exists($fkModelObj, 'get_all')) {
                $fkData = $fkModelObj->get_all();
                $fkCache[$col['model']] = [];
                foreach ($fkData as $item) {
                    $key = $item->$fkKeyField ?? '';
                    $value = $item->$fkShowField ?? '';
                    if ($key !== '') {
                        $fkCache[$col['model']][$key] = $value;
                    }
                }
            }
        }
    }
}

// Table ID (fallback to unique ID)
$table_id = $table_config['table_id'] ?? 'dataTable_' . uniqid();

// Detect dark mode from localStorage via JavaScript approach
// We'll render both themes initially and let JavaScript decide
$isDarkMode = false; // Default to light mode

// Check for theme in localStorage via JavaScript fallback
echo '<script>';
echo 'document.addEventListener("DOMContentLoaded", function() {';
echo '    const savedTheme = localStorage.getItem("theme");';
echo '    if (savedTheme === "dark") {';
echo '        document.documentElement.setAttribute("data-theme", "dark");';
echo '        document.body.classList.add("dark-mode");';
echo '    }';
echo '});';
echo '</script>';

// Default table classes (will be overridden by JavaScript if dark mode is active)
$defaultTableClasses = 'table table-hover table-bordered table-striped';
$tableClasses = $table_config['table_classes'] ?? $defaultTableClasses;

// Default card header classes
$defaultCardHeaderClasses = 'card-header bg-gradient-primary text-white';
$cardHeaderClasses = $layout['card_header_classes'] ?? $defaultCardHeaderClasses;
?>

<!-- Content Wrapper -->
<div class="<?= htmlspecialchars($layout['content_wrapper_class'] ?? 'content-wrapper') ?>">
    <section class="<?= htmlspecialchars($layout['section_class'] ?? 'content') ?>">
        <div class="<?= htmlspecialchars($layout['row_class'] ?? 'row') ?>">
            <div class="<?= htmlspecialchars($layout['col_class'] ?? 'col-12') ?>">

                <div class="<?= htmlspecialchars($layout['card_classes'] ?? 'card shadow') ?>">

                    <!-- Card Header with improved layout -->
                    <div class="<?= htmlspecialchars($cardHeaderClasses) ?> d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i> <?= htmlspecialchars($heading) ?>
                        </h3>

                        <div class="d-flex gap-2">
                            <!-- Custom Header Buttons -->
                            <?php foreach ($custom_buttons as $btnKey => $btnConfig): 
                                if (!empty($btnConfig['header']) && $btnConfig['header'] === true):
                                    $url = $btnConfig['url'] ?? '#';
                                    $btnClass = $btnConfig['class'] ?? 'btn btn-light btn-sm';
                                    $btnIcon = $btnConfig['icon'] ?? 'fas fa-cog';
                                    $btnText = $btnConfig['text'] ?? $btnKey;
                            ?>
                            <a href="<?= htmlspecialchars($url) ?>"
                               class="<?= htmlspecialchars($btnClass) ?>">
                                <i class="<?= htmlspecialchars($btnIcon) ?> me-1"></i>
                                <?= htmlspecialchars($btnText) ?>
                            </a>
                            <?php endif; endforeach; ?>

                            <!-- Add New Button -->
                            <?php if (!empty($buttons['add_new'])): ?>
                            <a href="<?= BASE_URL . '/' . ($form_config['new'] ?? '') ?>"
                               class="<?= htmlspecialchars($buttons['add_new']['class'] ?? 'btn btn-light') ?>">
                                <i class="<?= htmlspecialchars($buttons['add_new']['icon'] ?? 'fas fa-plus') ?>"></i>
                                <?= htmlspecialchars($buttons['add_new']['text'] ?? 'Add New') ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="<?= htmlspecialchars($layout['card_body_classes'] ?? 'card-body p-0') ?>"> <!-- Removed padding for cleaner look -->
                  

                        <div class="table-responsive"> <!-- Added for better mobile handling -->
                            
                            <table id="<?= htmlspecialchars($table_id) ?>"
                                   class="<?= htmlspecialchars($tableClasses) ?>"
                                   <?= htmlspecialchars($table_config['table_attributes'] ?? 'width="100%"') ?>>
                                   

                                <thead> <!-- Improved header styling -->
                                    <tr>
                                        <?php foreach ($table_config['th'] ?? [] as $th): ?>
                                            <th><?= htmlspecialchars($th) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                          
                                </thead>

                                <tbody>
                                    <?php $rowNum = 1; ?>
                                    <?php foreach ($data as $row): ?>
                                    <tr>
<?php
                                          $colIndex = 0;
                                          foreach ($table_config['columns'] ?? [] as $col):
                                              $field = $col['name'];
                                              $isLink = $col['link'] ?? false;
                                              $isFk = !empty($col['fk']);
                                              $type = $isFk ? 'fk' : ($col['type'] ?? $col['format'] ?? 'text');
                                          ?>
                                        <td <?php if ($type === 'status_badge'): ?>data-order="<?= htmlspecialchars($row->{$status['column'] ?? 'status'} ?? '') ?>"<?php endif; ?>>
                                            <?php if ($field === '#'): ?>
                                                <?= $rowNum++ ?>
                                            <?php elseif ($type === 'icon' && isset($row->$field)): ?>
                                                <i class="fas <?= htmlspecialchars($row->$field ?? '') ?>"></i>
<?php elseif ($type === 'status_badge' && isset($status['column'])): 
                                                  $val = $row->{$status['column']} ?? '';
                                                  $isActive = $val == ($status['active'] ?? '1');
                                              ?>
                                                  <span class="badge <?= $isActive ? htmlspecialchars($status['badge_active'] ?? 'bg-success') : htmlspecialchars($status['badge_inactive'] ?? 'bg-danger') ?>">
                                                      <?= $isActive ? htmlspecialchars($status['text_active'] ?? 'Active') : htmlspecialchars($status['text_inactive'] ?? 'Inactive') ?>
                                                  </span>
                                             <?php elseif ($type === 'priority_badge' && isset($row->$field)): 
                                                  $priority = $row->$field ?? 0;
                                                  $priorityConfig = [
                                                      0 => ['class' => 'bg-info', 'text' => 'Info'],
                                                      1 => ['class' => 'bg-warning', 'text' => 'Warning'],
                                                      2 => ['class' => 'bg-danger', 'text' => 'Error'],
                                                  ];
                                                  $p = $priorityConfig[$priority] ?? $priorityConfig[0];
                                              ?>
                                                  <span class="badge <?= htmlspecialchars($p['class']) ?>"><?= htmlspecialchars($p['text']) ?></span>
<?php elseif ($type === 'datetime' && isset($row->$field)):
                                                  $value = $row->$field;
                                                  if ($value) {
                                                      $dt = new DateTime($value);
                                                      $displayValue = htmlspecialchars($dt->format('Y-m-d H:i:s'));
                                                  } else {
                                                      $displayValue = '';
                                                  }
                                                  if ($isLink && !empty($table_config['link_base']) && !empty($table_config['id_column'])):
                                                      $id = base64_encode(urlencode($row->{$table_config['id_column']} ?? ''));
                                              ?>
                                                      <a href="<?= htmlspecialchars($table_config['link_base']) ?>?id=<?= $id ?>" class="text-decoration-none">
                                                          <?= $displayValue ?>
                                                      </a>
                                              <?php else: ?>
                                                      <?= $displayValue ?>
                                              <?php endif; ?>
                                              <?php elseif ($type === 'fk' && isset($row->$field)):
                                                  $fkValue = $row->$field;
                                                  $fkModel = $col['model'] ?? '';
                                                  $displayValue = $fkCache[$fkModel][$fkValue] ?? $fkValue;
                                                  if ($isLink && !empty($table_config['link_base']) && !empty($table_config['id_column'])):
                                                      $id = base64_encode(urlencode($row->{$table_config['id_column']} ?? ''));
                                              ?>
                                                      <a href="<?= htmlspecialchars($table_config['link_base']) ?>?id=<?= $id ?>" class="text-decoration-none">
                                                          <?= htmlspecialchars($displayValue) ?>
                                                      </a>
                                              <?php else: ?>
                                                      <?= htmlspecialchars($displayValue) ?>
                                              <?php endif; ?>
<?php else:
  $value = htmlspecialchars($row->$field ?? '');
  if ($isLink && !empty($table_config['link_base']) && !empty($table_config['id_column'])):
      $id = base64_encode(urlencode($row->{$table_config['id_column']} ?? ''));
?>
    <a href="<?= htmlspecialchars($table_config['link_base']) ?>?id=<?= $id ?>" class="text-decoration-none">
        <?= $value ?>
    </a>
<?php else: ?>
    <?= $value ?>
<?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <?php $colIndex++; endforeach; ?>

                                        <!-- Action Column with tooltips -->
                                        <td class="text-center action-column" style="<?= htmlspecialchars($table_config['action_style'] ?? '') ?>">
                                            <?php if (!empty($buttons['archive'])): ?>
                                                <?php 
                                                    $statusCol = $status['column'] ?? 'status';
                                                    $isActive = ($row->$statusCol ?? '') == ($status['active'] ?? '1');
                                                    $action = $isActive ? 'archive' : 'unarchive';
                                                    $icon = $isActive ? 'fas fa-archive' : 'fas fa-box-open';
                                                    $title = $isActive ? 'Archive' : 'Restore';
                                                    $class = $isActive ? 'btn btn-sm btn-warning' : 'btn btn-sm btn-success';
                                                ?>
                                                <a href="<?= htmlspecialchars($table_config['link_base'] ?? '') ?>/<?= $action ?>/<?= base64_encode((string) ($row->id ?? '')) ?>"
                                                   class="<?= $class ?>" data-bs-toggle="tooltip" title="<?= $title ?>">
                                                    <i class="<?= $icon ?>"></i>
                                                </a>
                             <?php endif; ?>

                             <?php if (!empty($buttons['toggle_status'])): ?>
                                 <?php 
                                     $statusCol = $status['column'] ?? 'status';
                                     $isActive = ($row->$statusCol ?? '') == ($status['active'] ?? '1');
                                     $action = $isActive ? 'deactivate' : 'activate';
                                     $icon = $isActive ? 'fas fa-pause-circle' : 'fas fa-play-circle';
                                     $title = $isActive ? 'Deactivate' : 'Activate';
                                     $class = $isActive ? 'btn btn-sm btn-warning' : 'btn btn-sm btn-success';
                                 ?>
                                 <a href="<?= htmlspecialchars($table_config['link_base'] ?? '') ?>/<?= $action ?>/<?= base64_encode((string) ($row->id ?? '')) ?>"
                                    class="<?= $class ?>" data-bs-toggle="tooltip" title="<?= $title ?>">
                                     <i class="<?= $icon ?>"></i>
                                 </a>
                             <?php endif; ?>

                                             <?php 
                                             // Custom action buttons (dynamic based on row data)
                                            foreach ($custom_buttons as $btnKey => $btnConfig):
                                                // Skip header-only buttons in action column
                                                if (!empty($btnConfig['header']) && $btnConfig['header'] === true) {
                                                    continue;
                                                }
                                                $condition = $btnConfig['condition'] ?? null;
                                                $show = true;
                                                if ($condition) {
                                                    eval('$show = ' . $condition . ';');
                                                }
                                                if ($show):
                                                    $url = $btnConfig['url'] ?? '#';
                                                    // Replace placeholders
                                                    $url = str_replace('{id}', base64_encode((string)($row->id ?? '')), $url);
                                                    $url = str_replace('{link_base}', $table_config['link_base'] ?? '', $url);
                                                    $btnClass = $btnConfig['class'] ?? 'btn btn-sm btn-secondary';
                                                    if (!empty($btnConfig['confirm']) && strpos($btnClass, 'delete-btn') === false) {
                                                        $btnClass .= ' delete-btn';
                                                    }
                                                    $dataAttributes = '';
                                                    if (!empty($btnConfig['confirm'])) {
                                                        $dataAttributes = ' data-id="' . base64_encode((string) ($row->id ?? '')) . '"'
                                                            . ' data-redirect="' . htmlspecialchars($form_config['redirect'] ?? '') . '"';
                                                    }
                                                    $btnIcon = $btnConfig['icon'] ?? 'fas fa-cog';
                                                    $btnTitle = $btnConfig['title'] ?? $btnKey;
                                            ?>
                                            <a href="<?= htmlspecialchars($url) ?>"
                                               class="<?= htmlspecialchars($btnClass) ?>" data-bs-toggle="tooltip" title="<?= htmlspecialchars($btnTitle) ?>"<?= $dataAttributes ?>>
                                                <i class="<?= htmlspecialchars($btnIcon) ?>"></i>
                                            </a>
                                            <?php endif; endforeach; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>

<!-- DataTables JS Initialization with Optimizations and Dark Mode Support -->
<script>
$(document).ready(function () {
    // Function to update table theme based on data-theme attribute
    function updateTableTheme() {
        const html = document.documentElement;
        const currentTheme = html.getAttribute('data-theme') || localStorage.getItem('theme') || 'light';
        const isDarkMode = currentTheme === 'dark';
        const table = $('#<?= htmlspecialchars($table_id) ?>');
        const cardHeader = table.closest('.card').find('.card-header');
        
        // Update table class
        if (isDarkMode) {
            table.addClass('table-dark').removeClass('table-light');
            // Update card header for dark mode
            cardHeader.removeClass('bg-gradient-primary').addClass('bg-gradient-dark');
            // Update DataTables buttons
            table.closest('.dataTables_wrapper').find('.dt-buttons .btn').addClass('btn-outline-light');
        } else {
            table.addClass('table-light').removeClass('table-dark');
            // Update card header for light mode
            cardHeader.removeClass('bg-gradient-dark').addClass('bg-gradient-primary');
            // Revert DataTables buttons
            table.closest('.dataTables_wrapper').find('.dt-buttons .btn').removeClass('btn-outline-light');
        }
        
        // Update status badge colors for dark mode
        if (isDarkMode) {
            table.find('.badge.bg-success').addClass('bg-success').removeClass('bg-light-success');
            table.find('.badge.bg-danger').addClass('bg-danger').removeClass('bg-light-danger');
        }
    }
    
    // Initialize with current theme
    updateTableTheme();
    
    // Dynamically build buttons from form_config exports
    const exportButtons = [];
    <?php foreach ($exports as $type => $opts): ?>
        let btn_<?= $type ?> = { extend: '<?= $type ?>' };
        <?php foreach ($opts as $key => $val): ?>
            btn_<?= $type ?>['<?= $key ?>'] = <?= json_encode($val) ?>;
        <?php endforeach; ?>
        exportButtons.push(btn_<?= $type ?>);
    <?php endforeach; ?>

    const table = $('#<?= htmlspecialchars($table_id) ?>').DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 text-end'f>>" +  // Buttons left-top, search right-top
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 text-end'lp>>",  // Info left-bottom, length and pagination right-bottom
        buttons: exportButtons,
        responsive: true, // Enable responsive
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "All"]],
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search records...",
            emptyTable: "No data available",
            info: "Showing _START_ to _END_ of _TOTAL_ entries"
        },
        columnDefs: [
            { responsivePriority: 1, targets: 0 },  // '#' always visible
            { responsivePriority: 1, targets: 1 },  // 'Name' always visible
            { responsivePriority: 2, targets: -1 }, // 'Action' medium priority
            { responsivePriority: 100, targets: '_all' } // Others hide first
        ],
        drawCallback: function() {
            // Enable Bootstrap tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            
            // Update theme on redraw
            updateTableTheme();
        },
        initComplete: function() {
            // Listen for theme changes
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'data-theme') {
                        updateTableTheme();
                    }
                });
            });
            
            // Observe html element for data-theme changes
            observer.observe(document.documentElement, { 
                attributes: true, 
                attributeFilter: ['data-theme'] 
            });
            
            // Also observe localStorage changes (for cross-tab sync)
            window.addEventListener('storage', function(e) {
                if (e.key === 'theme') {
                    updateTableTheme();
                }
            });
        }
    });

    // Exclude action column from exports
    table.column('.action-column').nodes().to$().addClass('no-export');
    
    // Listen for custom theme change events (if your theme toggle fires events)
    $(document).on('theme:changed', function(e, theme) {
        updateTableTheme();
    });
});

// Function to handle theme changes from navbar
function handleThemeChange() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Dispatch custom event for table to update
    $(document).trigger('theme:changed', [newTheme]);
}
</script>

<!-- Improved Delete Confirmation with SweetAlert -->
<script>
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const id = this.dataset.id;
        const redirect = this.dataset.redirect;
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `<?= htmlspecialchars($table_config['link_base'] ?? '') ?>/delete/${id}${redirect ? '?redirect=' + redirect : ''}`;
            }
        });
    });
});
</script>

</body>
</html>