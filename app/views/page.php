<?php
include_once __DIR__ . '/navbar.php';
include_once __DIR__ . '/sidebar.php';

// Fetch data based on configuration
$id_param = $form_config['data_config']['id_param'];
$data_source = $form_config['data_config']['data_source'];
$method_name = $form_config['data_config']['method_name'];

if ($id_param === 1) {
    $id = 1;
} elseif ($id_param === 2) {
    $id = 2;
} elseif (!empty($id_param)) {
    $id = isset($_GET[$id_param]) ? intval(base64_decode($_GET[$id_param])) : 0;
} else {
    $id = 0;
}

 
$data_model = $$data_source ?? null;
$row = ($id > 0 && is_object($data_model) && method_exists($data_model, $method_name))
    ? $data_model->$method_name($id)
    : null;

if ($id > 0) {
    $row = is_object($row) || is_array($row) ? $row : [];
    $form_config['inputs']['id']['value'] = $id;
}

?>

<?php
// Get upload overlay configuration from form_config
$upload_config = $form_config['upload'] ?? [];
$overlay_id = $upload_config['overlay_id'] ?? 'upload-overlay';
$card_id = $upload_config['card_id'] ?? 'upload-card';
$title_id = $upload_config['title_id'] ?? 'upload-overlay-title';
$bar_wrap_id = $upload_config['bar_wrap_id'] ?? 'upload-progress-bar-wrap';
$bar_id = $upload_config['bar_id'] ?? 'upload-progress-bar';
$pct_id = $upload_config['pct_id'] ?? 'upload-progress-pct';
$detail_id = $upload_config['detail_id'] ?? 'upload-progress-detail';
?>

<!-- Upload Progress Overlay -->
<div id="<?= htmlspecialchars($overlay_id) ?>" role="dialog" aria-modal="true" aria-label="Uploading file">
    <div class="upload-card" id="<?= htmlspecialchars($card_id) ?>">
        <div class="upload-icon">&#8679;</div>
        <h5 id="<?= htmlspecialchars($title_id) ?>">Uploading&hellip;</h5>
        <div id="<?= htmlspecialchars($bar_wrap_id) ?>">
            <div id="<?= htmlspecialchars($bar_id) ?>"></div>
        </div>
        <div id="<?= htmlspecialchars($pct_id) ?>">0%</div>
        <div id="<?= htmlspecialchars($detail_id) ?>">Preparing upload&hellip;</div>
    </div>
</div>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Page Header -->
    <?php
    $heading = $form_config['heading'];
    $page_config = $form_config['page_config'];
    $page_title = $id > 0 ?
        $page_config['update_title_prefix'] . " $heading" :
        $page_config['new_title_prefix'] . " $heading";
    include_once __DIR__ . '/page_header.php';
    ?>

    <!-- Main Content -->
    <section class="content">
        <div class="<?= htmlspecialchars($page_config['container_class']) ?>">
            <div class="row">
                <div class="<?= htmlspecialchars($form_config['layout']['main_column_class']) ?>">
                    <div class="<?= htmlspecialchars($page_config['card_class']) ?>">
                        <div class="<?= htmlspecialchars($page_config['card_body_class']) ?>">
                            <form action="<?= htmlspecialchars($form_config['form_action']) ?>"
                                method="<?= htmlspecialchars($form_config['method']) ?>"
                                enctype="<?= htmlspecialchars($form_config['enctype']) ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCSRFToken()) ?>">

                                <div class="<?= htmlspecialchars($form_config['layout']['form_row_class']) ?>">
                                    <?php if (is_object($data_model) && method_exists($data_model, 'renderFormElements')): ?>
                                        <?php $data_model->renderFormElements($form_config); ?>
                                    <?php endif; ?>
                                </div>

                                <?= $form_config['layout']['separator'] ?>

                                <?php
                                if (!empty($form_config['bottom_table'])) {
                                    $bottom_tables = $form_config['bottom_table'];

                                    if (isset($bottom_tables['renderer'])) {
                                        $bottom_tables = [$bottom_tables];
                                    }

                                    foreach ($bottom_tables as $bottom_table) {
                                        if (!empty($bottom_table['renderer']) && $id > 0 && is_callable($bottom_table['renderer'])) {
                                            call_user_func($bottom_table['renderer'], $id, $row, $form_config, $bottom_table);
                                        }
                                    }
                                }
                                ?>

                                <div class="<?= htmlspecialchars($form_config['layout']['button_row_class']) ?> mt-4">
                                    <?php if (isset($form_config['buttons']['submit'])): ?>
                                        <div class="<?= htmlspecialchars($form_config['buttons']['submit']['div_class']) ?>">
                                            <?php
                                            $submit_btn = $form_config['buttons']['submit'];
                                            $btn_text = $id > 0 ? $submit_btn['update_text'] : $submit_btn['create_text'];
                                            $btn_class = $id > 0 ? $submit_btn['update_class'] : $submit_btn['create_class'];
                                            ?>
                                            <button type="submit" class="<?= htmlspecialchars($btn_class) ?>">
                                                <?= htmlspecialchars($btn_text) ?>
                                            </button>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (isset($form_config['buttons']['reset']) && $form_config['buttons']['reset']['show'] && empty($id)): ?>
                                        <div class="<?= htmlspecialchars($form_config['buttons']['reset']['div_class']) ?>">
                                            <button type="reset" class="<?= htmlspecialchars($form_config['buttons']['reset']['class']) ?>">
                                                <?= htmlspecialchars($form_config['buttons']['reset']['text']) ?>
                                            </button>
                                        </div>
                                    <?php endif; ?>

                                    <?php
                                    // Generic modal-driven action buttons
                                    foreach ($form_config['buttons'] as $btn_key => $btn):
                                        if (in_array($btn_key, ['submit', 'reset'], true)) continue;
                                        if (empty($btn['show']) && $id > 0  || empty($btn['action']  && $id > 0)) continue;
                                    ?>
                                        <div class="<?= htmlspecialchars($btn['div_class'] ?? '') ?>">
                                            <button type="button"
                                                class="<?= htmlspecialchars($btn['class'] ?? 'btn btn-block btn-outline-info') ?>"
                                                data-toggle="modal"
                                                data-target="#actionFileModal"
                                                data-action="<?= htmlspecialchars($btn['action']) ?>"
                                                data-title="<?= htmlspecialchars($btn['modal_title'] ?? $btn['text'] ?? '') ?>"
                                                data-fields='<?= htmlspecialchars(json_encode($btn['fields'] ?? []), ENT_QUOTES) ?>'>
                                                <?= htmlspecialchars($btn['text'] ?? '') ?>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </form>
                        </div><!-- /.card-body -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Shared file viewer modal -->
<div class="modal fade" id="actionFileModal" tabindex="-1" role="dialog" aria-labelledby="actionFileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionFileModalLabel">File</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" style="min-height:200px; max-height:75vh; overflow:auto; padding:0;">
                <div id="actionFileModalLoading" class="text-center py-5">Loading&hellip;</div>
                <div id="actionFileModalError" class="alert alert-danger m-3" style="display:none;"></div>
                <iframe id="actionFileModalFrame" title="File preview" style="width:100%; height:70vh; border:0; display:none;"></iframe>
                <pre id="actionFileModalText" style="display:none; white-space:pre-wrap; word-break:break-word; padding:1rem; margin:0;"></pre>
            </div>
            <div class="modal-footer">
                <a id="actionFileModalDownload" href="#" target="_blank" class="btn btn-outline-secondary" download>Download</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>

<?php
// Determine if we need jsPDF
$needs_jspdf = false;
foreach ($form_config['buttons'] as $__btn) {
    if (!empty($__btn['action']) && $__btn['action'] === 'client_pdf') {
        $needs_jspdf = true;
        break;
    }
}
?>
<?php if ($needs_jspdf): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<?php endif; ?>

<script>
    const formConfig = <?= json_encode($form_config); ?>;

    // Execute configured preview function
    <?php if (isset($form_config['scripts']['preview_function'])): ?>
        <?= $form_config['scripts']['preview_function'] ?>(formConfig);
    <?php endif; ?>

    // Execute additional scripts if configured
    <?php if (!empty($form_config['scripts']['additional_scripts'])): ?>
        <?php foreach ($form_config['scripts']['additional_scripts'] as $script): ?>
            <?= $script ?>
        <?php endforeach; ?>
    <?php endif; ?>

    // Optimized Form Validation with Working Reset
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (!form) return;

        // Conditional field visibility (depends_on)
        (function() {
            var dependentFields = form.querySelectorAll('[data-depends-on]');
            if (!dependentFields.length) return;

            function updateDependentFields() {
                dependentFields.forEach(function(fieldDiv) {
                    var sourceId = fieldDiv.getAttribute('data-depends-on');
                    var requiredValue = fieldDiv.getAttribute('data-depends-value');
                    var source = document.getElementById(sourceId);
                    var input = fieldDiv.querySelector('select, input, textarea');
                    if (!source || !input) return;

                    if (source.value === requiredValue) {
                        fieldDiv.style.removeProperty('display');
                        input.disabled = false;
                    } else {
                        fieldDiv.style.display = 'none';
                        input.disabled = true;
                    }
                });
            }

            dependentFields.forEach(function(fieldDiv) {
                var sourceId = fieldDiv.getAttribute('data-depends-on');
                if (sourceId) {
                    var source = document.getElementById(sourceId);
                    if (source) source.addEventListener('change', updateDependentFields);
                }
            });

            updateDependentFields();
        })();

        // Validation message controller
        const ValidationUI = {
            show(input, isValid, message = '') {
                const validationDiv = input.parentElement.querySelector('.validation-message');
                if (!validationDiv) return;

                if (isValid) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                    validationDiv.style.display = 'none';
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                    validationDiv.textContent = message;
                    validationDiv.style.display = 'block';
                }
            },

            clear(input) {
                input.classList.remove('is-invalid', 'is-valid');
                const validationDiv = input.parentElement.querySelector('.validation-message');
                if (validationDiv) validationDiv.style.display = 'none';
            },

            clearAll(form) {
                const inputs = form.querySelectorAll('.is-valid, .is-invalid');
                inputs.forEach(input => this.clear(input));
            }
        };

        // Validators object for different field types
        const Validators = {
            required(input, value) {
                if (!value) {
                    return {
                        valid: false,
                        message: 'This field is required.'
                    };
                }
                return {
                    valid: true
                };
            },

            pattern(input, value) {
                const pattern = input.getAttribute('pattern');
                if (pattern && value) {
                    const regex = new RegExp(pattern);
                    if (!regex.test(value)) {
                        return {
                            valid: false,
                            message: input.getAttribute('title') || 'Invalid format.'
                        };
                    }
                }
                return {
                    valid: true
                };
            },

            email(input, value) {
                if (value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        return {
                            valid: false,
                            message: 'Please enter a valid email address.'
                        };
                    }
                }
                return {
                    valid: true
                };
            },

            number(input, value) {
                if (!value) return {
                    valid: true
                };

                const numValue = parseFloat(value);
                if (isNaN(numValue)) {
                    return {
                        valid: false,
                        message: 'Please enter a valid number.'
                    };
                }

                const min = input.getAttribute('min');
                const max = input.getAttribute('max');

                if (min && numValue < parseFloat(min)) {
                    return {
                        valid: false,
                        message: `Value must be at least ${min}.`
                    };
                }

                if (max && numValue > parseFloat(max)) {
                    return {
                        valid: false,
                        message: `Value must not exceed ${max}.`
                    };
                }

                return {
                    valid: true
                };
            },

            url(input, value) {
                if (value) {
                    try {
                        new URL(value);
                    } catch {
                        return {
                            valid: false,
                            message: 'Please enter a valid URL.'
                        };
                    }
                }
                return {
                    valid: true
                };
            },

            tel(input, value) {
                if (value) {
                    const telPattern = input.getAttribute('pattern') || '^[+]?[0-9\\s\\-\\(\\)]{10,}$';
                    const telRegex = new RegExp(telPattern);
                    if (!telRegex.test(value)) {
                        return {
                            valid: false,
                            message: 'Please enter a valid phone number.'
                        };
                    }
                }
                return {
                    valid: true
                };
            },

            file(input) {
                if (input.hasAttribute('required') && !input.files.length) {
                    return {
                        valid: false,
                        message: 'Please select a file.'
                    };
                }
                return {
                    valid: true
                };
            }
        };

        // Main validation function
        function validateField(input) {
            const value = input.value.trim();
            const type = input.getAttribute('type') || input.tagName.toLowerCase();
            const isRequired = input.hasAttribute('required');

            // Skip if empty and not required
            if (!value && !isRequired && type !== 'file') {
                ValidationUI.clear(input);
                return true;
            }

            // Run required validation
            if (isRequired) {
                const result = Validators.required(input, value);
                if (!result.valid) {
                    ValidationUI.show(input, false, result.message);
                    return false;
                }
            }

            // Run type-specific validation
            if (type === 'file') {
                const result = Validators.file(input);
                if (!result.valid) {
                    ValidationUI.show(input, false, result.message);
                    return false;
                }
            } else if (value) {
                // Pattern validation (applies to all types)
                let result = Validators.pattern(input, value);
                if (!result.valid) {
                    ValidationUI.show(input, false, result.message);
                    return false;
                }

                // Type-specific validation
                if (Validators[type]) {
                    result = Validators[type](input, value);
                    if (!result.valid) {
                        ValidationUI.show(input, false, result.message);
                        return false;
                    }
                }
            }

            ValidationUI.show(input, true);
            return true;
        }

        // Get all validatable inputs
        const inputs = form.querySelectorAll(
            'input[type="text"], input[type="email"], input[type="password"], ' +
            'input[type="number"], input[type="tel"], input[type="url"], ' +
            'input[type="search"], input[type="file"], textarea, select'
        );

        // Attach event listeners
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });

            // Real-time validation for previously invalid fields
            if (input.type !== 'file') {
                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid') || this.value.trim()) {
                        validateField(this);
                    }
                });
            }
        });

        // ── Upload progress bar helpers ─────────────────────────────
        const uploadConfig = formConfig['upload'] || {};
        const overlayId    = uploadConfig['overlay_id'] || 'upload-overlay';
        const barId        = uploadConfig['bar_id'] || 'upload-progress-bar';
        const pctId        = uploadConfig['pct_id'] || 'upload-progress-pct';
        const detailId     = uploadConfig['detail_id'] || 'upload-progress-detail';
        const cardId       = uploadConfig['card_id'] || 'upload-card';
        const titleId      = uploadConfig['title_id'] || 'upload-overlay-title';
        
        const overlay      = document.getElementById(overlayId);
        const bar          = document.getElementById(barId);
        const pct          = document.getElementById(pctId);
        const detail       = document.getElementById(detailId);
        const cardEl       = document.getElementById(cardId);
        const overlayTitle = document.getElementById(titleId);

        function formHasFile() {
            const fileInputs = form.querySelectorAll('input[type="file"]');
            for (const fi of fileInputs) {
                if (fi.files && fi.files.length > 0) return true;
            }
            return false;
        }

        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (bytes / Math.pow(k, i)).toFixed(1) + ' ' + sizes[i];
        }

        function formatSpeed(bytesPerSec) {
            return formatBytes(bytesPerSec) + '/s';
        }

        function showOverlay() {
            bar.style.width = '0%';
            bar.classList.remove('error');
            cardEl.classList.remove('error');
            pct.textContent = '0%';
            pct.style.color = '#28a745';
            detail.textContent = 'Preparing upload…';
            overlayTitle.textContent = 'Uploading…';
            overlay.classList.add('active');
        }

        function updateProgress(loaded, total) {
            const percent = total > 0 ? Math.round((loaded / total) * 100) : 0;
            bar.style.width = percent + '%';
            pct.textContent = percent + '%';

            const now = performance.now();
            if (!updateProgress._start) {
                updateProgress._start = now;
                updateProgress._startBytes = loaded;
            }
            const elapsed = (now - updateProgress._start) / 1000;
            const transferred = loaded - updateProgress._startBytes;
            const speed = elapsed > 0.5 ? transferred / elapsed : 0;
            const remaining = speed > 0 ? (total - loaded) / speed : 0;

            let detailText = formatBytes(loaded) + ' / ' + formatBytes(total);
            if (speed > 0 && percent < 100) {
                detailText += ' &nbsp;•&nbsp; ' + formatSpeed(speed);
                if (remaining < 3600) {
                    const mins = Math.floor(remaining / 60);
                    const secs = Math.round(remaining % 60);
                    detailText += ' &nbsp;•&nbsp; ~' + (mins > 0 ? mins + 'm ' : '') + secs + 's remaining';
                }
            }
            detail.innerHTML = detailText;
        }

        function showSuccess() {
            bar.style.width = '100%';
            pct.textContent = '100%';
            overlayTitle.textContent = 'Upload complete. Processing…';
            detail.textContent = 'Please wait while the server processes your file.';
        }

        function showError(msg) {
            bar.classList.add('error');
            cardEl.classList.add('error');
            pct.style.color = '#dc3545';
            overlayTitle.textContent = 'Upload failed';
            pct.textContent = '✕';
            detail.textContent = msg || 'An error occurred. Please try again.';
            setTimeout(() => { overlay.classList.remove('active'); }, 4000);
        }

        // Form submission
        form.addEventListener('submit', function(e) {
            // 1. Client-side validation first
            let isFormValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) isFormValid = false;
            });

            if (!isFormValid) {
                e.preventDefault();
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            if (!formHasFile()) {
                return;
            }

            e.preventDefault();
            updateProgress._start = null;

            const formData = new FormData(form);
            const xhr = new XMLHttpRequest();

            showOverlay();

            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            xhr.upload.addEventListener('progress', function(ev) {
                if (ev.lengthComputable) {
                    updateProgress(ev.loaded, ev.total);
                } else {
                    bar.style.width = '60%';
                    detail.textContent = 'Uploading… (size unknown)';
                }
            });

            xhr.upload.addEventListener('load', function() {
                showSuccess();
            });

            xhr.addEventListener('load', function() {
                if (xhr.status >= 200 && xhr.status < 400) {
                    if (xhr.responseURL) {
                        window.location.href = xhr.responseURL;
                    } else {
                        window.location.reload();
                    }
                } else {
                    showError('Server returned status ' + xhr.status + '. Please try again.');
                    if (submitBtn) submitBtn.disabled = false;
                }
            });

            xhr.addEventListener('error', function() {
                showError('Network error. Check your connection and try again.');
                if (submitBtn) submitBtn.disabled = false;
            });

            xhr.addEventListener('abort', function() {
                showError('Upload was cancelled.');
                if (submitBtn) submitBtn.disabled = false;
            });

            xhr.open(form.getAttribute('method') || 'POST', form.getAttribute('action'));
            xhr.send(formData);
        });

        // Reset button handler
        form.addEventListener('reset', function() {
            setTimeout(() => {
                ValidationUI.clearAll(form);

                const fileInputs = form.querySelectorAll('input[type="file"]');
                fileInputs.forEach(input => {
                    const label = input.parentElement.querySelector('.custom-file-label');
                    if (label) label.textContent = 'Choose file';
                    const preview = input.parentElement.querySelector('img[id$="_preview"]');
                    if (preview) preview.style.display = 'none';
                });

                const summernoteElements = form.querySelectorAll('.summernote');
                if (typeof $ !== 'undefined' && summernoteElements.length > 0) {
                    summernoteElements.forEach(editor => {
                        if ($(editor).summernote) {
                            $(editor).summernote('code', '');
                            $(editor).summernote('reset');
                        }
                    });
                }

                const switches = form.querySelectorAll('input[data-bootstrap-switch]');
                switches.forEach(sw => {
                    if (typeof $ !== 'undefined' && typeof $(sw).bootstrapSwitch === 'function') {
                        $(sw).bootstrapSwitch('state', sw.defaultChecked);
                    }
                });

                const select2Elements = form.querySelectorAll('.select2');
                if (typeof $ !== 'undefined' && select2Elements.length > 0) {
                    select2Elements.forEach(sel => {
                        $(sel).val(null).trigger('change');
                    });
                }
            }, 10);
        });

        const switches = form.querySelectorAll('input[data-bootstrap-switch]');
        switches.forEach(switchInput => {
            switchInput.addEventListener('switchChange.bootstrapSwitch', function() {
                validateField(this);
            });
        });
    });
</script>

 
<script>
    function generateKey() {
        const fieldName = formConfig['generate_key_field']
            || (formConfig['inputs'] && formConfig['inputs']['generate_key_field']
                ? formConfig['inputs']['generate_key_field']
                : null);
        
        if (!fieldName) {
            console.warn('generate_key_field not configured in form_config');
            return;
        }
        
        const keyField = document.querySelector('input[name="' + fieldName + '"]');
        if (keyField) {
            const randomKey = Math.random().toString(36).substr(2, 10).toUpperCase();
            keyField.value = randomKey;
        }
    }
</script>

<script>
    // Generic handler for the shared #actionFileModal
    (function() {
        if (typeof $ === 'undefined') return;

        function readFieldValues(fieldKeys) {
            return fieldKeys.map(function(key) {
                const inputCfg = (formConfig.inputs && formConfig.inputs[key]) || {};
                const label = inputCfg.label || key;
                const el = document.querySelector('[name="' + key + '"]');
                let value = '';
                if (el) {
                    if (el.tagName === 'SELECT') {
                        value = el.options[el.selectedIndex] ? el.options[el.selectedIndex].text.trim() : el.value;
                    } else {
                        value = el.value;
                    }
                }
                return { label: String(label), value: String(value || '-') };
            });
        }

        function buildCertificatePdf(fieldKeys, heading) {
            const jspdfNs = window.jspdf;
            if (!jspdfNs || !jspdfNs.jsPDF) {
                throw new Error('PDF library failed to load. Check your connection and try again.');
            }
            const doc = new jspdfNs.jsPDF();
            const marginX = 15;
            let y = 20;

            doc.setFontSize(16);
            doc.text((formConfig.heading || 'Sensor') + ' - ' + heading, marginX, y);
            y += 8;
            doc.setFontSize(9);
            doc.setTextColor(120);
            doc.text('Generated ' + new Date().toLocaleString(), marginX, y);
            doc.setTextColor(0);
            y += 12;
            doc.setDrawColor(200);
            doc.line(marginX, y, 195, y);
            y += 10;

            doc.setFontSize(11);
            readFieldValues(fieldKeys).forEach(function(f) {
                doc.setFont(undefined, 'bold');
                doc.text(f.label + ':', marginX, y);
                doc.setFont(undefined, 'normal');
                doc.text(f.value, marginX + 55, y);
                y += 8;
                if (y > 280) { doc.addPage(); y = 20; }
            });

            return doc;
        }

        function buildCalibrationText(fieldKeys, heading) {
            const lines = [heading, 'Generated ' + new Date().toLocaleString(), ''];
            readFieldValues(fieldKeys).forEach(function(f) {
                lines.push(f.label + ': ' + f.value);
            });
            return lines.join('\n');
        }

        let frameBlobUrl = null;
        let downloadBlobUrl = null;

        function revokeIfBlob(url) {
            if (url && url.indexOf('blob:') === 0) {
                URL.revokeObjectURL(url);
            }
        }

        $(document).on('show.bs.modal', '#actionFileModal', function(e) {
            const $btn = $(e.relatedTarget);
            if (!$btn.length) return;

            const action = $btn.data('action');
            const title  = $btn.data('title') || 'File';

            const $loading  = $('#actionFileModalLoading');
            const $error    = $('#actionFileModalError').hide().text('');
            const $frame    = $('#actionFileModalFrame').hide().attr('src', '');
            const $text     = $('#actionFileModalText').hide().text('');
            const $download = $('#actionFileModalDownload').attr('href', '#').removeAttr('download').hide();

            $('#actionFileModalLabel').text(title);
            $loading.show();

            let fieldKeys = [];
            try { fieldKeys = JSON.parse($btn.attr('data-fields') || '[]'); } catch (parseErr) { fieldKeys = []; }

            if (window.customModalActions && window.customModalActions[action]) {
                window.customModalActions[action]({
                    action: action,
                    title: title,
                    btn: $btn,
                    loading: $loading,
                    error: $error,
                    frame: $frame,
                    text: $text,
                    download: $download,
                    fieldKeys: fieldKeys,
                    formConfig: formConfig
                });
                return;
            }

            try {
                if (action === 'client_pdf') {
                    const template = formConfig.pdf_template || '';
                    if (template) {
                        if (typeof window.jspdf === 'undefined' || typeof window.jspdf.jsPDF === 'undefined') {
                            throw new Error('PDF library (jsPDF) not loaded.');
                        }
                        if (typeof window.html2canvas === 'undefined') {
                            throw new Error('PDF rendering library (html2canvas) not loaded.');
                        }

                        // Read field values (select => visible text, same as readFieldValues)
                        const fieldValues = {};
                        fieldKeys.forEach(key => {
                            const el = document.querySelector('[name="' + key + '"]');
                            if (!el) { fieldValues[key] = ''; return; }
                            if (el.tagName === 'SELECT') {
                                fieldValues[key] = el.options[el.selectedIndex] ? el.options[el.selectedIndex].text.trim() : el.value;
                            } else {
                                fieldValues[key] = el.value;
                            }
                        });

                        // Merge settings values (e.g. {{img3}} for letterhead)
                        if (formConfig.settings) {
                            Object.keys(formConfig.settings).forEach(function(key) {
                                fieldValues[key] = formConfig.settings[key] || '';
                            });
                        }

                        let htmlContent = template;
                        Object.keys(fieldValues).forEach(key => {
                            const placeholder = '{{' + key + '}}';
                            htmlContent = htmlContent.split(placeholder).join(fieldValues[key] || '');
                        });
                        // Any placeholder left over (field not in fieldKeys, e.g. {{customer}}
                        // that isn't captured on this form yet) is blanked rather than shown literally.
                        htmlContent = htmlContent.replace(/\{\{\s*[\w.]+\s*\}\}/g, '');

                        const { jsPDF } = window.jspdf;
                        const doc = new jsPDF('p', 'mm', 'a4');

                        // Render the filled template in a real (off-screen) DOM node so
                        // html2canvas can rasterise actual layout: tables, borders, images, CSS.
                        const renderHost = document.createElement('div');
                        renderHost.style.position = 'fixed';
                        renderHost.style.left = '-99999px';
                        renderHost.style.top = '0';
                        renderHost.style.width = '794px'; // ~210mm @ 96dpi (A4 width)
                        renderHost.style.background = '#ffffff';
                        renderHost.innerHTML = htmlContent;
                        document.body.appendChild(renderHost);

                        // Rasterise the host directly with html2canvas, then place the
                        // resulting image into the PDF with manual multi-page slicing.
                        // (jsPDF's doc.html() often returns a blank canvas for off-screen nodes.)
                        window.html2canvas(renderHost, {
                            scale: 2,
                            useCORS: true,
                            backgroundColor: '#ffffff',
                            windowWidth: 794,
                            logging: false
                        }).then(function (canvas) {
                            const imgData = canvas.toDataURL('image/png');
                            const pageW = 210;
                            const pageH = 297;
                            const margin = 10;
                            const usableH = pageH - margin * 2;
                            const imgW = pageW - margin * 2;
                            const imgH = canvas.height * imgW / canvas.width;

                            let heightLeft = imgH;
                            let position = margin;

                            doc.addImage(imgData, 'PNG', margin, position, imgW, imgH);
                            heightLeft -= usableH;

                            while (heightLeft > 0) {
                                doc.addPage();
                                position = heightLeft - imgH + margin;
                                doc.addImage(imgData, 'PNG', margin, position, imgW, imgH);
                                heightLeft -= usableH;
                            }

                            document.body.removeChild(renderHost);
                            const blobUrl = doc.output('bloburl');
                            frameBlobUrl = blobUrl;
                            downloadBlobUrl = blobUrl;
                            $loading.hide();
                            $frame.attr('src', blobUrl).show();
                            $download.attr('href', blobUrl).attr('download', 'certificate.pdf').show();
                        }).catch(function (err) {
                            document.body.removeChild(renderHost);
                            throw err;
                        });
                    } else {
                        // No template – use plain text fallback
                        const doc = buildCertificatePdf(fieldKeys, title);
                        const blobUrl = doc.output('bloburl');
                        frameBlobUrl = blobUrl;
                        downloadBlobUrl = blobUrl;
                        $loading.hide();
                        $frame.attr('src', blobUrl).show();
                        $download.attr('href', blobUrl).attr('download', 'certificate.pdf').show();
                    }
                } else if (action === 'client_text') {
                    const content = buildCalibrationText(fieldKeys, title);
                    const blob = new Blob([content], { type: 'text/plain' });
                    downloadBlobUrl = URL.createObjectURL(blob);
                    $loading.hide();
                    $text.text(content).show();
                    $download.attr('href', downloadBlobUrl).attr('download', 'calibration.txt').show();
                } else if (action === 'client_file') {
                    const fileUrl = $btn.data('fileUrl') || '';
                    if (!fileUrl) {
                        throw new Error('No file URL provided.');
                    }
                    $loading.hide();
                    $frame.attr('src', fileUrl).show();
                    $download.attr('href', fileUrl).attr('download', '').show();
                } else {
                    throw new Error('Unknown action: ' + action);
                }
            } catch (err) {
                $loading.hide();
                $error.text(err.message || 'Could not generate the file.').show();
            }
        });

        $('#actionFileModal').on('hidden.bs.modal', function() {
            revokeIfBlob(frameBlobUrl);
            if (downloadBlobUrl !== frameBlobUrl) revokeIfBlob(downloadBlobUrl);
            frameBlobUrl = null;
            downloadBlobUrl = null;
            $('#actionFileModalFrame').attr('src', '');
        });
    })();
</script>

</body>
<style>
    /* ── Upload Progress Overlay ── */
    #upload-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }
    #upload-overlay.active {
        display: flex;
    }
    #upload-overlay .upload-card {
        background: #fff;
        border-radius: 10px;
        padding: 2rem 2.5rem;
        width: 90%;
        max-width: 480px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.25);
        text-align: center;
    }
    #upload-overlay .upload-card h5 {
        margin-bottom: 1.25rem;
        font-weight: 600;
        color: #333;
    }
    #upload-overlay .upload-card .upload-icon {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        color: #28a745;
    }
    #upload-progress-bar-wrap {
        background: #e9ecef;
        border-radius: 50px;
        height: 22px;
        overflow: hidden;
        margin-bottom: 0.6rem;
    }
    #upload-progress-bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #28a745, #20c997);
        border-radius: 50px;
        transition: width 0.2s ease;
        position: relative;
    }
    #upload-progress-bar::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(90deg,
            rgba(255,255,255,0) 0%,
            rgba(255,255,255,0.35) 50%,
            rgba(255,255,255,0) 100%);
        animation: shimmer 1.4s infinite;
    }
    @keyframes shimmer {
        0%   { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    #upload-progress-pct {
        font-size: 1.05rem;
        font-weight: 700;
        color: #28a745;
        margin-bottom: 0.3rem;
    }
    #upload-progress-detail {
        font-size: 0.82rem;
        color: #6c757d;
        min-height: 1.2em;
    }
    #upload-progress-bar.error {
        background: linear-gradient(90deg, #dc3545, #e85d71);
    }
    #upload-overlay .upload-card.error h5 { color: #dc3545; }

    /* Form Validation Styles */
    .validation-message {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }

    .is-invalid~.validation-message {
        display: block;
    }

    .is-valid {
        border-color: #28a745;
    }

    .is-invalid {
        border-color: #dc3545;
    }

    .form-control:focus.is-valid {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    .form-control:focus.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .validation-message {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

</html>