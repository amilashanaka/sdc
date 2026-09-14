<?php

function replaceArrayKey($array, $oldKey, $newKey)
{
    if (array_key_exists($oldKey, $array)) {
        $keys = array_keys($array);
        $keys[array_search($oldKey, $keys)] = $newKey;
        $array = array_combine($keys, $array);
    }
    return $array;
}

function uploaFile($file, $upload_dir)
{
    if (isset($_FILES[$file]) && $_FILES[$file]['error'] == UPLOAD_ERR_OK) {
        $allowed_types = array('application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $file_name = $_FILES[$file]['name'];
        $file_type = $_FILES[$file]['type'];
        $file_tmp = $_FILES[$file]['tmp_name'];
        $file_size = $_FILES[$file]['size'];

        if (in_array($file_type, $allowed_types)) {
            if ($file_size <= 5242880) {
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $file_name = preg_replace("/[^a-zA-Z0-9\.\-_]/", "", $file_name);
                $file_path = $upload_dir . basename($file_name);
                if (move_uploaded_file($file_tmp, $file_path)) {
                    return $file_name;
                } else {
                    return "Failed to move the uploaded file.";
                }
            } else {
                return "File is too large. Maximum file size is 5MB.";
            }
        } else {
            return "Invalid file type. Only PDF and DOC files are allowed.";
        }
    } else {
        return "No file uploaded or there was an upload error.";
    }
}

function uploadPic($file_name, $target_dir)
{
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (($_FILES[$file_name]["name"]) != '') {
        $target_user_image = $target_dir . basename($_FILES[$file_name]["name"]);
        $uploadFileType_user_image = pathinfo($target_user_image, PATHINFO_EXTENSION);
        $newfilename_user_image = round(microtime(true)) . rand(1000, 9999) . '.' . $uploadFileType_user_image;

        if (basename($_FILES[$file_name]["name"]) != '') {
            if ($uploadFileType_user_image != "jpg" && $uploadFileType_user_image != "png" && $uploadFileType_user_image != "jpeg" && $uploadFileType_user_image != "gif" && $uploadFileType_user_image != "JPG" && $uploadFileType_user_image != "PNG" && $uploadFileType_user_image != "JPEG" && $uploadFileType_user_image != "GIF") {
                return '';
            } else {
                if (move_uploaded_file($_FILES[$file_name]["tmp_name"], $target_dir . $newfilename_user_image)) {
                    return  $newfilename_user_image;
                } else {
                    return '';
                }
            }
        }
    } else {
        return '';
    }
}

function getResizeImg($file, $target_dir, $width, $height)
{
    if (basename($_FILES[$file]["name"]) != '') {
        $pd_Main_img = reSize($_FILES[$file]['tmp_name'], $_FILES[$file]['name'], 1, $target_dir, $width, $height);
    }
    return $pd_Main_img;
}

function reSize($file, $var_file, $var_name, $folderPath, $targetWidth, $targetHeight)
{
    $sourceProperties = getimagesize($file);
    $fileNewName = time() . $var_name;

    $ext = pathinfo($var_file, PATHINFO_EXTENSION);

    $imageType = $sourceProperties[2];

    switch ($imageType) {
        case IMAGETYPE_PNG:
            $imageResourceId = imagecreatefrompng($file);
            $targetLayer = imageResize($imageResourceId, $sourceProperties[0], $sourceProperties[1], $targetWidth, $targetHeight);
            imagepng($targetLayer, $folderPath . $fileNewName . "." . $ext);
            break;

        case IMAGETYPE_GIF:
            $imageResourceId = imagecreatefromgif($file);
            $targetLayer = imageResize($imageResourceId, $sourceProperties[0], $sourceProperties[1], $targetWidth, $targetHeight);
            imagegif($targetLayer, $folderPath . $fileNewName . "." . $ext);
            break;

        case IMAGETYPE_JPEG:
            $imageResourceId = imagecreatefromjpeg($file);
            $targetLayer = imageResize($imageResourceId, $sourceProperties[0], $sourceProperties[1], $targetWidth, $targetHeight);
            imagejpeg($targetLayer, $folderPath . $fileNewName . "." . $ext);
            break;

        default:
            echo "Invalid Image type.";
            exit;
            break;
    }

    $file_save_as =  $fileNewName . "." . $ext;
    move_uploaded_file($file, $folderPath . $file_save_as);

    return $file_save_as;
}

function imageResize($imageResourceId, $width, $height, $targetWidth, $targetHeight)
{
    $targetLayer = imagecreatetruecolor($targetWidth, $targetHeight);
    imagecopyresampled($targetLayer, $imageResourceId, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

    return $targetLayer;
}

function printTime($date)
{
    try {
        $dateObject = new DateTime($date);
        return $dateObject->format("H:i:s");
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}

function printDate($date)
{
    $ndate = date_create($date);
    return date_format($ndate, "d-m-Y");
}

function printDateTime($date)
{
    $ndate = date_create($date);
    return date_format($ndate, 'd-m-Y H:i:s');
}

function setExpDate($today, $days = 100)
{
    return date('d-m-Y H:i:s', strtotime($today . ' + ' . $days . 'days'));
}

function calculateAge($birthdate)
{
    $birthDate = new DateTime($birthdate);
    $currentDate = new DateTime();
    $age = $currentDate->diff($birthDate);
    return $age->y;
}

function redirect_page($is_update, $result, $id, $page)
{
    if (($is_update && $result['error'] === null && $result['code'] === 204) || (!$is_update && $result['code'] === 200)) {
        $info = $is_update ? $result['message'] : '';
        $redirect_url = $is_update 
            ? "../" . $page . ".php?id=" . base64_encode($id) . "&error=" . base64_encode(1) . "&info=" . base64_encode($info)
            : "../" . $page . "_list.php?error=" . base64_encode(4);
    } else {
        $redirect_url = $is_update 
            ? "../" . $page . ".php?id=" . base64_encode($id) . "&error=" . base64_encode(3)
            : "../" . $page . ".php?id=" . base64_encode($id) . "&error=" . base64_encode(3);
    }

    return $redirect_url;
}

function redirect_page_single($is_update, $result, $id, $page)
{
    if (($is_update && $result['error'] === null && $result['status'] === 1) || (!$is_update && $result['code'] === 200)) {
        $info = $is_update ? implode(" ", $result['message']) : '';
        $redirect_url = $is_update 
            ? "../" . $page . ".php?error=" . base64_encode(1) . "&info=" . base64_encode($info)
            : "../" . $page . ".php?error=" . base64_encode(4);
    } else {
        $redirect_url = $is_update 
            ? "../" . $page . ".php?id=" . base64_encode($id) . "&error=" . base64_encode(3)
            : "../" . $page . ".php?id=" . base64_encode($id) . "&error=" . base64_encode(3);
    }

    return $redirect_url;
}

function uploadMedia($file_name, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov', 'mkv'])
{
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (!empty($_FILES[$file_name]["name"])) {
        $original_file_name = basename($_FILES[$file_name]["name"]);
        $file_extension = pathinfo($original_file_name, PATHINFO_EXTENSION);
        $new_file_name = round(microtime(true)) . rand(1000, 9999) . '.' . $file_extension;
        $target_file_path = $target_dir . $new_file_name;

        if (!in_array(strtolower($file_extension), $allowed_types)) {
            return '';
        }

        if (move_uploaded_file($_FILES[$file_name]["tmp_name"], $target_file_path)) {
            return $new_file_name;
        } else {
            return '';
        }
    }

    return '';
}

/**
 * Upload compressed/update files with version-based naming
 * Supports: zip, rar, tar, gz, 7z
 * File naming format: version.extension (e.g., 1.0.0.zip)
 * 
 * @param string $file_name The file input name from $_FILES
 * @param string $target_dir The target directory to save the file (relative path from data folder)
 * @param string $version The version number to use in filename
 * @param array $allowed_types Allowed file extensions
 * @return string Filename on success, empty string on failure
 */
function uploadCompressedFile($file_name, $target_dir, $version, $allowed_types = ['zip', 'rar', 'tar', 'gz', '7z', 'tar.gz'])
{
    // Convert relative path to absolute path
    $abs_target_dir = dirname(dirname(__FILE__)) . '/' . ltrim($target_dir, './');
    
    if (!is_dir($abs_target_dir)) {
        if (!mkdir($abs_target_dir, 0777, true)) {
            return '';
        }
    }

    if (empty($_FILES[$file_name]["name"])) {
        return '';
    }

    $original_file_name = basename($_FILES[$file_name]["name"]);
    $file_extension = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
    
    // Handle tar.gz case
    if ($file_extension === 'gz' && strtolower(pathinfo($original_file_name, PATHINFO_FILENAME)) . '.tar' !== '') {
        if (substr($original_file_name, -7) === '.tar.gz') {
            $file_extension = 'tar.gz';
        }
    }

    // Validate file type
    if (!in_array($file_extension, $allowed_types)) {
        return '';
    }

    // Sanitize version string (alphanumeric, dots, dashes only)
    $version = preg_replace('/[^a-zA-Z0-9._-]/', '', $version);
    
    if (empty($version)) {
        return '';
    }

    // Create filename with version: version.extension
    $new_file_name = $version . '.' . $file_extension;
    $target_file_path = $abs_target_dir . $new_file_name;

    // Move uploaded file
    if (move_uploaded_file($_FILES[$file_name]["tmp_name"], $target_file_path)) {
        return $new_file_name;
    }

    return '';
}

/**
 * renderFormElements()
 * Renders all form inputs defined in $form_config['inputs'].
 *
 * Required fields automatically get a red asterisk (*) appended to their
 * label via the requiredLabel() helper — no JavaScript needed.
 */

/**
 * Build a <label> tag, appending a red star when $isRequired is true.
 *
 * @param string $text       Raw label text (will be htmlspecialchars'd).
 * @param string $for        Value for the `for` attribute; pass '' to omit.
 * @param bool   $isRequired Whether to append the star.
 */
function requiredLabel(string $text, string $for = '', bool $isRequired = false): string
{
    $forAttr = $for !== '' ? ' for="' . htmlspecialchars($for) . '"' : '';
    $star    = $isRequired
        ? ' <span class="required-star" aria-hidden="true" title="Required field">*</span>'
        : '';
    return '<label' . $forAttr . '>' . htmlspecialchars($text) . $star . '</label>';
}

function renderFormElements($form_config, $row)
{
    if (empty($form_config['inputs'])) {
        return;
    }

    foreach ($form_config['inputs'] as $key => $input) {
        if (!empty($input['skip'])) {
            continue;
        }

        $isRequired = !empty($input['required']);

        // ── Image-preview file input (early return) ───────────────────────
        if ($input['type'] === 'file'
            && isset($input['accept'])
            && strpos($input['accept'], 'image/') !== false
        ) {
            $image_src = isset($row[$key]) && $row[$key] !== ''
                ? './' . htmlspecialchars($row[$key])
                : './assets/img/photo1.png';

            $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
            $label    = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
            $required = $isRequired ? 'required' : '';
            $accept   = htmlspecialchars($input['accept']);
            $multiple = !empty($input['multiple']) ? 'multiple' : '';
            $disabled = !empty($input['disabled']) ? 'disabled' : '';

            echo <<<HTML
            <div class="$divClass">
                $label
                <div class="mb-2" id="preview_$key">
                    <img src="$image_src" class="img-thumbnail" style="max-width: 150px;" />
                </div>
                <input type="file" name="$key" id="$key" class="form-control"
                       accept="$accept" $required $multiple $disabled />
            </div>
            HTML;
            continue;
        }

        // ── Validation message block ──────────────────────────────────────
        // Always emit the div (even when empty) so JS can write into it.
        $validation_message_html = !empty($input['validation_message'])
            ? '<div class="invalid-feedback validation-message">'
              . htmlspecialchars($input['validation_message'])
              . '</div>'
            : '<div class="invalid-feedback validation-message"></div>';

        // ── Field type switch ─────────────────────────────────────────────
        switch ($input['type']) {

            // ── text ──────────────────────────────────────────────────────
            case 'text':
                $divClass    = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label       = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value       = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                $class       = htmlspecialchars($input['class'] ?? 'form-control');
                $required    = $isRequired ? 'required' : '';
                $pattern     = !empty($input['validation'])         ? 'pattern="'     . htmlspecialchars($input['validation'])         . '"' : '';
                $title       = !empty($input['validation_message']) ? 'title="'       . htmlspecialchars($input['validation_message']) . '"' : '';
                $placeholder = !empty($input['placeholder'])        ? 'placeholder="' . htmlspecialchars($input['placeholder'])        . '"' : '';
                $maxlength   = !empty($input['maxlength'])          ? 'maxlength="'   . htmlspecialchars($input['maxlength'])          . '"' : '';
                $readonly    = !empty($input['readonly']) ? 'readonly' : '';
                $disabled    = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="text" class="$class" id="$key" name="$key" value="$value"
                           $required $pattern $title $placeholder $maxlength $readonly $disabled>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── email ─────────────────────────────────────────────────────
            case 'email':
                $divClass    = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label       = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value       = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                $class       = htmlspecialchars($input['class'] ?? 'form-control');
                $required    = $isRequired ? 'required' : '';
                $placeholder = !empty($input['placeholder']) ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
                $multiple    = !empty($input['multiple']) ? 'multiple' : '';
                $readonly    = !empty($input['readonly']) ? 'readonly' : '';
                $disabled    = !empty($input['disabled']) ? 'disabled' : '';
                $unique      = !empty($input['unique']) ? 'data-unique="true"' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="email" class="$class" id="$key" name="$key" value="$value"
                           $required $placeholder $multiple $readonly $disabled $unique>
                    <?php if (isset(\$_SESSION['field_errors']['$key'])): ?>
                    <div class="text-danger"><?php echo htmlspecialchars(\$_SESSION['field_errors']['$key']); ?></div>
                    <?php unset(\$_SESSION['field_errors']['$key']); ?>
                    <?php endif; ?>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── password ──────────────────────────────────────────────────
            case 'password':
                $divClass    = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label       = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value       = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                $class       = htmlspecialchars($input['class'] ?? 'form-control');
                $required    = $isRequired ? 'required' : '';
                $pattern     = !empty($input['pattern'])      ? 'pattern="'     . htmlspecialchars($input['pattern'])      . '"' : '';
                $placeholder = !empty($input['placeholder'])  ? 'placeholder="' . htmlspecialchars($input['placeholder'])  . '"' : '';
                $minlength   = !empty($input['minlength'])    ? 'minlength="'   . htmlspecialchars($input['minlength'])    . '"' : '';
                $maxlength   = !empty($input['maxlength'])    ? 'maxlength="'   . htmlspecialchars($input['maxlength'])    . '"' : '';
                $readonly    = !empty($input['readonly']) ? 'readonly' : '';
                $disabled    = !empty($input['disabled']) ? 'disabled' : '';
                $type        = 'password';
                $toggleButton = '';
                if (!empty($input['toggle'])) {
                    $toggleButton = '<button class="btn btn-outline-secondary" type="button" id="toggle-' . $key . '" aria-label="Toggle password visibility" onclick="const wrapper=this.closest(\'.input-group\'); const input=wrapper ? wrapper.querySelector(\'input\') : null; if (!input) return; const icon=this.querySelector(\'i\'); const show=input.type === \'password\'; input.type = show ? \'text\' : \'password\'; if (icon) icon.className = show ? \'fas fa-eye-slash\' : \'fas fa-eye\';"><i class="fas fa-eye"></i></button>';
                }
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <div class="input-group">
                        <input type="$type" class="$class" id="$key" name="$key" value="$value"
                               $required $pattern $placeholder $minlength $maxlength $readonly $disabled>
                        $toggleButton
                    </div>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── number ────────────────────────────────────────────────────
            case 'number':
                $divClass    = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label       = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value       = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                $class       = htmlspecialchars($input['class'] ?? 'form-control');
                $required    = $isRequired ? 'required' : '';
                $placeholder = !empty($input['placeholder']) ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
                $min         = !empty($input['min'])  ? 'min="'  . htmlspecialchars($input['min'])  . '"' : '';
                $max         = !empty($input['max'])  ? 'max="'  . htmlspecialchars($input['max'])  . '"' : '';
                $step        = !empty($input['step']) ? 'step="' . htmlspecialchars($input['step']) . '"' : '';
                $readonly    = !empty($input['readonly']) ? 'readonly' : '';
                $disabled    = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="number" class="$class" id="$key" name="$key" value="$value"
                           $required $placeholder $min $max $step $readonly $disabled>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── tel ───────────────────────────────────────────────────────
            case 'tel':
                $divClass    = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label       = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value       = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                $class       = htmlspecialchars($input['class'] ?? 'form-control');
                $required    = $isRequired ? 'required' : '';
                $pattern     = !empty($input['pattern'])      ? 'pattern="'     . htmlspecialchars($input['pattern'])     . '"' : '';
                $placeholder = !empty($input['placeholder'])  ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
                $maxlength   = !empty($input['maxlength'])    ? 'maxlength="'   . htmlspecialchars($input['maxlength'])   . '"' : '';
                $readonly    = !empty($input['readonly']) ? 'readonly' : '';
                $disabled    = !empty($input['disabled']) ? 'disabled' : '';
                $unique      = !empty($input['unique']) ? 'data-unique="true"' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="tel" class="$class" id="$key" name="$key" value="$value"
                           $required $pattern $placeholder $maxlength $readonly $disabled $unique>
                    <?php if (isset(\$_SESSION['field_errors']['$key'])): ?>
                    <div class="text-danger"><?php echo htmlspecialchars(\$_SESSION['field_errors']['$key']); ?></div>
                    <?php unset(\$_SESSION['field_errors']['$key']); ?>
                    <?php endif; ?>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── url ───────────────────────────────────────────────────────
            case 'url':
                $divClass    = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label       = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value       = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                $class       = htmlspecialchars($input['class'] ?? 'form-control');
                $required    = $isRequired ? 'required' : '';
                $pattern     = !empty($input['pattern'])      ? 'pattern="'     . htmlspecialchars($input['pattern'])     . '"' : '';
                $placeholder = !empty($input['placeholder'])  ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
                $readonly    = !empty($input['readonly']) ? 'readonly' : '';
                $disabled    = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="url" class="$class" id="$key" name="$key" value="$value"
                           $required $pattern $placeholder $readonly $disabled>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── search ────────────────────────────────────────────────────
            case 'search':
                $divClass    = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label       = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value       = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                $class       = htmlspecialchars($input['class'] ?? 'form-control');
                $required    = $isRequired ? 'required' : '';
                $placeholder = !empty($input['placeholder']) ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
                $maxlength   = !empty($input['maxlength'])   ? 'maxlength="'   . htmlspecialchars($input['maxlength'])   . '"' : '';
                $readonly    = !empty($input['readonly']) ? 'readonly' : '';
                $disabled    = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="search" class="$class" id="$key" name="$key" value="$value"
                           $required $placeholder $maxlength $readonly $disabled>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── datetime-local ────────────────────────────────────────────
            case 'datetime-local':
                $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label    = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value    = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                $class    = htmlspecialchars($input['class'] ?? 'form-control');
                $required = $isRequired ? 'required' : '';
                $min      = !empty($input['min'])  ? 'min="'  . htmlspecialchars($input['min'])  . '"' : '';
                $max      = !empty($input['max'])  ? 'max="'  . htmlspecialchars($input['max'])  . '"' : '';
                $step     = !empty($input['step']) ? 'step="' . htmlspecialchars($input['step']) . '"' : '';
                $readonly = !empty($input['readonly']) ? 'readonly' : '';
                $disabled = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="datetime-local" class="$class" id="$key" name="$key" value="$value"
                           $required $min $max $step $readonly $disabled>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── date ──────────────────────────────────────────────────────
            case 'date':
                $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label    = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value    = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                $class    = htmlspecialchars($input['class'] ?? 'form-control');
                $required = $isRequired ? 'required' : '';
                $min      = !empty($input['min']) ? 'min="' . htmlspecialchars($input['min']) . '"' : '';
                $max      = !empty($input['max']) ? 'max="' . htmlspecialchars($input['max']) . '"' : '';
                $readonly = !empty($input['readonly']) ? 'readonly' : '';
                $disabled = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="date" class="$class" id="$key" name="$key" value="$value"
                           $required $min $max $readonly $disabled>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── time ──────────────────────────────────────────────────────
            case 'time':
                $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label    = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value    = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                $class    = htmlspecialchars($input['class'] ?? 'form-control');
                $required = $isRequired ? 'required' : '';
                $min      = !empty($input['min'])  ? 'min="'  . htmlspecialchars($input['min'])  . '"' : '';
                $max      = !empty($input['max'])  ? 'max="'  . htmlspecialchars($input['max'])  . '"' : '';
                $step     = !empty($input['step']) ? 'step="' . htmlspecialchars($input['step']) . '"' : '';
                $readonly = !empty($input['readonly']) ? 'readonly' : '';
                $disabled = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="time" class="$class" id="$key" name="$key" value="$value"
                           $required $min $max $step $readonly $disabled>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── month ─────────────────────────────────────────────────────
            case 'month':
                $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label    = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value    = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                $class    = htmlspecialchars($input['class'] ?? 'form-control');
                $required = $isRequired ? 'required' : '';
                $min      = !empty($input['min']) ? 'min="' . htmlspecialchars($input['min']) . '"' : '';
                $max      = !empty($input['max']) ? 'max="' . htmlspecialchars($input['max']) . '"' : '';
                $readonly = !empty($input['readonly']) ? 'readonly' : '';
                $disabled = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="month" class="$class" id="$key" name="$key" value="$value"
                           $required $min $max $readonly $disabled>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── week ──────────────────────────────────────────────────────
            case 'week':
                $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label    = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value    = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                $class    = htmlspecialchars($input['class'] ?? 'form-control');
                $required = $isRequired ? 'required' : '';
                $min      = !empty($input['min']) ? 'min="' . htmlspecialchars($input['min']) . '"' : '';
                $max      = !empty($input['max']) ? 'max="' . htmlspecialchars($input['max']) . '"' : '';
                $readonly = !empty($input['readonly']) ? 'readonly' : '';
                $disabled = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="week" class="$class" id="$key" name="$key" value="$value"
                           $required $min $max $readonly $disabled>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── color ─────────────────────────────────────────────────────
            case 'color':
                $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label    = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value    = htmlspecialchars($row[$key] ?? $input['value'] ?? '#000000');
                $class    = htmlspecialchars($input['class'] ?? 'form-control form-control-color');
                $required = $isRequired ? 'required' : '';
                $disabled = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="color" class="$class" id="$key" name="$key" value="$value"
                           $required $disabled>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── date-range ────────────────────────────────────────────────
            case 'date-range':
                $divClass   = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label      = isset($input['label']) ? requiredLabel($input['label'], '', $isRequired) : '';
                $class      = htmlspecialchars($input['class'] ?? 'form-control');
                $required   = $isRequired ? 'required' : '';
                $startLabel = htmlspecialchars($input['start_label'] ?? 'From');
                $endLabel   = htmlspecialchars($input['end_label']   ?? 'To');
                $separator  = htmlspecialchars($input['separator']   ?? ' to ');

                $existingValue = $startValue = $endValue = '';
                if (isset($row[$key]) && !empty($row[$key])) {
                    $existingValue = $row[$key];
                    $parts = preg_split('/\s+to\s+|\s+-\s+|\s+\|\s+/', $existingValue, 2);
                    if (count($parts) === 2) {
                        $startValue = htmlspecialchars(trim($parts[0]));
                        $endValue   = htmlspecialchars(trim($parts[1]));
                    }
                } elseif (isset($row[$key . '_start']) || isset($row[$key . '_end'])) {
                    $startValue    = htmlspecialchars($row[$key . '_start'] ?? '');
                    $endValue      = htmlspecialchars($row[$key . '_end']   ?? '');
                    $existingValue = $startValue && $endValue
                        ? $startValue . $separator . $endValue
                        : ($startValue ?: $endValue);
                } elseif (!empty($input['value'])) {
                    $existingValue = $input['value'];
                    $parts = preg_split('/\s+to\s+|\s+-\s+|\s+\|\s+/', $existingValue, 2);
                    if (count($parts) === 2) {
                        $startValue = htmlspecialchars(trim($parts[0]));
                        $endValue   = htmlspecialchars(trim($parts[1]));
                    }
                } else {
                    $startValue    = htmlspecialchars($input['start_value'] ?? '');
                    $endValue      = htmlspecialchars($input['end_value']   ?? '');
                    $existingValue = $startValue && $endValue
                        ? $startValue . $separator . $endValue
                        : ($startValue ?: $endValue);
                }

                echo <<<HTML
                <div class="$divClass">
                    $label
                    <div class="row">
                        <div class="col-md-6">
                            <label class="small">$startLabel</label>
                            <input type="date" class="$class date-range-start" id="{$key}_start"
                                   data-target="$key" value="$startValue" $required>
                        </div>
                        <div class="col-md-6">
                            <label class="small">$endLabel</label>
                            <input type="date" class="$class date-range-end" id="{$key}_end"
                                   data-target="$key" value="$endValue" $required>
                        </div>
                    </div>
                    <input type="hidden" id="$key" name="$key" value="$existingValue">
                    $validation_message_html
                    <script>
                        (function() {
                            const s = document.getElementById('{$key}_start');
                            const e = document.getElementById('{$key}_end');
                            const h = document.getElementById('$key');
                            const sep = '$separator';
                            function sync() { h.value = s.value && e.value ? s.value + sep + e.value : (s.value || e.value); }
                            s.addEventListener('change', sync);
                            e.addEventListener('change', sync);
                            if (!h.value && (s.value || e.value)) sync();
                        })();
                    </script>
                </div>
                HTML;
                break;

            // ── hidden ────────────────────────────────────────────────────
            case 'hidden':
                $value = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                echo <<<HTML
                <input type="hidden" id="$key" name="$key" value="$value">
                HTML;
                break;

            // ── textarea ──────────────────────────────────────────────────
            case 'textarea':
                $divClass    = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label       = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value       = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
                $class       = htmlspecialchars($input['class'] ?? 'form-control');
                $required    = $isRequired ? 'required' : '';
                $rows        = $input['rows'] ?? '5';
                $placeholder = !empty($input['placeholder']) ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
                $readonly    = !empty($input['readonly']) ? 'readonly' : '';
                $disabled    = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <textarea class="$class" id="$key" name="$key" rows="$rows"
                              $required $placeholder $readonly $disabled>$value</textarea>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── select / dropdown ─────────────────────────────────────────
            case 'select':
            case 'dropdown':
                $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label    = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $class    = htmlspecialchars($input['class'] ?? 'form-control');
                $required = $isRequired ? 'required' : '';
                $disabled = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <select class="$class" id="$key" name="$key" $required $disabled>
                HTML;
                if (!empty($input['placeholder'])) {
                    echo '<option value="">' . htmlspecialchars($input['placeholder']) . '</option>';
                }
                foreach ($input['items'] as $item) {
                    $selected = (isset($row[$key]) && $row[$key] == $item['value']) ||
                                (!isset($row[$key]) && isset($input['value']) && $input['value'] == $item['value'])
                                ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($item['value']) . '" ' . $selected . '>'
                        . htmlspecialchars($item['label']) . '</option>';
                }
                echo <<<HTML
                    </select>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── radio ─────────────────────────────────────────────────────
            case 'radio':
                $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label    = isset($input['label']) ? requiredLabel($input['label'], '', $isRequired) : '';
                $required = $isRequired ? 'required' : '';
                $disabled = !empty($input['disabled'])  ? 'disabled' : '';
                $inline   = !empty($input['inline'])    ? 'form-check-inline' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <div class="form-check-container">
                HTML;
                foreach ($input['items'] as $item) {
                    $checked = (isset($row[$key]) && $row[$key] == $item['value']) ||
                               (!isset($row[$key]) && isset($input['value']) && $input['value'] == $item['value'])
                               ? 'checked' : '';
                    $radioId = $key . '_' . $item['value'];
                    echo <<<HTML
                        <div class="form-check $inline">
                            <input class="form-check-input" type="radio" id="$radioId"
                                   name="$key" value="{$item['value']}" $checked $required $disabled>
                            <label class="form-check-label" for="$radioId">{$item['label']}</label>
                        </div>
                    HTML;
                }
                echo <<<HTML
                    </div>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── checkbox-group ────────────────────────────────────────────
            case 'checkbox-group':
                $divClass       = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label          = isset($input['label']) ? requiredLabel($input['label'], '', $isRequired) : '';
                $disabled       = !empty($input['disabled']) ? 'disabled' : '';
                $inline         = !empty($input['inline'])   ? 'form-check-inline' : '';
                $selectedValues = isset($row[$key])
                    ? (is_array($row[$key]) ? $row[$key] : explode(',', $row[$key]))
                    : (isset($input['value'])
                        ? (is_array($input['value']) ? $input['value'] : explode(',', $input['value']))
                        : []);
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <div class="form-check-container">
                HTML;
                foreach ($input['items'] as $item) {
                    $checked    = in_array($item['value'], $selectedValues) ? 'checked' : '';
                    $checkboxId = $key . '_' . $item['value'];
                    echo <<<HTML
                        <div class="form-check $inline">
                            <input class="form-check-input" type="checkbox" id="$checkboxId"
                                   name="{$key}[]" value="{$item['value']}" $checked $disabled>
                            <label class="form-check-label" for="$checkboxId">{$item['label']}</label>
                        </div>
                    HTML;
                }
                echo <<<HTML
                    </div>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── switch ────────────────────────────────────────────────────
            case 'switch':
                $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $checked  = !empty($input['checked']) ? 'checked' : '';
                $offColor = htmlspecialchars($input['color'][1] ?? 'primary');
                $onColor  = htmlspecialchars($input['color'][0] ?? 'success');
                $offText  = htmlspecialchars($input['label'][1] ?? 'OFF');
                $onText   = htmlspecialchars($input['label'][0] ?? 'ON');
                echo <<<HTML
                <div class="$divClass">
                    <input type="checkbox" id="$key" name="$key" $checked data-bootstrap-switch
                        data-off-color="$offColor" data-on-color="$onColor"
                        data-off-text="$offText" data-on-text="$onText">
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── multi-select ──────────────────────────────────────────────
            case 'multy-select':
            case 'multi-select':
                $divClass       = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label          = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $selectedColor  = 'select2-' . ($input['selected-color'] ?? 'info');
                $dropdownColor  = 'select2-' . ($input['dropdown-color'] ?? 'info');
                $placeholder    = htmlspecialchars($input['placeholder'] ?? '');
                $disabled       = !empty($input['disabled']) ? 'disabled' : '';
                $selectedValues = isset($row[$key])
                    ? (is_array($row[$key]) ? $row[$key] : explode(',', $row[$key]))
                    : (isset($input['value'])
                        ? (is_array($input['value']) ? $input['value'] : explode(',', $input['value']))
                        : []);
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <div class="$selectedColor">
                        <select id="$key" name="{$key}[]" class="select2 modal-class" multiple="multiple"
                            data-placeholder="$placeholder"
                            data-dropdown-css-class="$dropdownColor"
                            style="width: 100%;" $disabled>
                HTML;
                foreach ($input['items'] as $item) {
                    $selected = in_array($item['value'], $selectedValues) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($item['value']) . '" ' . $selected . '>'
                        . htmlspecialchars($item['label']) . '</option>';
                }
                echo <<<HTML
                        </select>
                    </div>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── checkbox (single) ─────────────────────────────────────────
            case 'checkbox':
                $divClass = $input['div_class'] ?? 'icheck-primary d-inline';
                $checked  = !empty($input['checked']) ? 'checked' : '';
                $labelTxt = htmlspecialchars($input['label'] ?? '');
                $disabled = !empty($input['disabled']) ? 'disabled' : '';
                $starHtml = $isRequired
                    ? ' <span class="required-star" aria-hidden="true" title="Required field">*</span>'
                    : '';
                echo <<<HTML
                <div class="form-group">
                    <div class="$divClass">
                        <input type="checkbox" id="$key" name="$key" $checked $disabled>
                        <label for="$key">$labelTxt$starHtml</label>
                    </div>
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── file ──────────────────────────────────────────────────────
            case 'file':
                $divClass  = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label     = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $class     = htmlspecialchars($input['class'] ?? 'form-control-file');
                $required  = $isRequired ? 'required' : '';
                $accept    = !empty($input['accept'])   ? 'accept="'   . htmlspecialchars($input['accept'])   . '"' : '';
                $multiple  = !empty($input['multiple']) ? 'multiple'   : '';
                $disabled  = !empty($input['disabled']) ? 'disabled'   : '';
                $help_text = !empty($input['help_text'])
                    ? '<small class="form-text text-muted d-block mt-2">'
                      . htmlspecialchars($input['help_text']) . '</small>'
                    : '';

                // For f4, keep the input element in DOM during edit (as hidden) so "Replace File" button can trigger it.
                $hidden = '';
                if (!empty($row) && !empty($row[$key]) && $key === 'f4') {
                    $required = '';
                    $hidden = 'style="display:none;"';
                }

                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="file" class="$class" id="$key" name="$key" $required $accept $multiple $disabled $hidden>
                    $help_text
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── range ─────────────────────────────────────────────────────
            case 'range':
                $divClass  = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label     = isset($input['label']) ? requiredLabel($input['label'], $key, $isRequired) : '';
                $value     = htmlspecialchars($row[$key] ?? $input['value'] ?? '0');
                $class     = htmlspecialchars($input['class'] ?? 'form-range');
                $min       = htmlspecialchars($input['min']  ?? '0');
                $max       = htmlspecialchars($input['max']  ?? '100');
                $step      = htmlspecialchars($input['step'] ?? '1');
                $disabled  = !empty($input['disabled']) ? 'disabled' : '';
                $showValue = !empty($input['show_value']);
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="range" class="$class" id="$key" name="$key"
                           value="$value" min="$min" max="$max" step="$step" $disabled>
                HTML;
                if ($showValue) {
                    echo '<span class="range-value" id="' . $key . '_value">' . $value . '</span>';
                    echo '<script>document.getElementById("' . $key . '").addEventListener("input",function(){'
                        . 'document.getElementById("' . $key . '_value").textContent=this.value;});</script>';
                }
                echo <<<HTML
                    $validation_message_html
                </div>
                HTML;
                break;

            // ── button ────────────────────────────────────────────────────
            case 'button':
                $divClass   = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $class      = htmlspecialchars($input['class'] ?? 'btn btn-primary');
                $text       = htmlspecialchars($input['text'] ?? $input['label'] ?? 'Button');
                $onclick    = !empty($input['onclick']) ? 'onclick="' . $input['onclick'] . '"' : '';
                $disabled   = !empty($input['disabled']) ? 'disabled' : '';
                $label_html = isset($input['label']) ? '<label>&nbsp;</label>' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label_html
                    <button type="button" class="$class" id="$key" name="$key" $onclick $disabled>$text</button>
                </div>
                HTML;
                break;

            // ── submit ────────────────────────────────────────────────────
            case 'submit':
                $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $class    = htmlspecialchars($input['class'] ?? 'btn btn-success');
                $value    = htmlspecialchars($input['value'] ?? 'Submit');
                $disabled = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    <input type="submit" class="$class" id="$key" name="$key" value="$value" $disabled>
                </div>
                HTML;
                break;

            // ── reset ─────────────────────────────────────────────────────
            case 'reset':
                $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $class    = htmlspecialchars($input['class'] ?? 'btn btn-secondary');
                $value    = htmlspecialchars($input['value'] ?? 'Reset');
                $disabled = !empty($input['disabled']) ? 'disabled' : '';
                echo <<<HTML
                <div class="$divClass">
                    <input type="reset" class="$class" id="$key" name="$key" value="$value" $disabled>
                </div>
                HTML;
                break;

            // ── divider ───────────────────────────────────────────────────
            case 'divider':
                $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12';
                $text     = htmlspecialchars($input['text'] ?? '');
                echo <<<HTML
                <div class="$divClass">
                    <hr>
                    <p class="text-muted">$text</p>
                </div>
                HTML;
                break;

            // ── currentfile ───────────────────────────────────────────────
            case 'currentfile':
                $divClass     = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
                $label        = isset($input['label']) ? requiredLabel($input['label'], '', $isRequired) : '';
                $file_key     = $input['file_key'] ?? 'f4';
                $current_file = isset($row[$file_key]) && !empty($row[$file_key]) ? $row[$file_key] : null;

                if ($current_file) {
                    $file_basename = basename($current_file);
                    $download_url  = './uploads/update/' . htmlspecialchars($file_basename);
                    echo <<<HTML
                    <div class="$divClass">
                        $label
                        <div class="alert alert-info d-flex justify-content-between align-items-center" role="alert">
                            <div>
                                <strong>Current File:</strong> <code>$file_basename</code>
                            </div>
                            <div>
                                <a href="$download_url" class="btn btn-sm btn-outline-info mr-2"
                                   title="Download current file">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="document.getElementById('$file_key').click();"
                                        title="Replace current file">
                                    <i class="fas fa-sync-alt"></i> Replace File
                                </button>
                            </div>
                        </div>
                    </div>
                    HTML;
                }
                break;

            // ── html (raw) ────────────────────────────────────────────────
            case 'html':
                $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12';
                $content  = $input['content'] ?? '';
                echo <<<HTML
                <div class="$divClass">
                    $content
                </div>
                HTML;
                break;

            // ── custom / default ──────────────────────────────────────────
            default:
                if ($input['type'] === 'custom' && !empty($input['value'])) {
                    echo $input['value'];
                }
                break;
        }
    }
}
function generateSessionKey($email)
{
    $csrf = generateCSRFToken();
    $sessionId = session_id();
    $s_key = $csrf . $email . $sessionId;
    $key = base64_encode($s_key);
    $_SESSION['session_key'] = $key;

    return $key;
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function input($key)
{
    global $form_config, $row;

    if (!isset($form_config['inputs'][$key])) {
        return '';
    }

    $input = $form_config['inputs'][$key];

    if (isset($input['skip']) && $input['skip'] === true) {
        return '';
    }

    ob_start();

    switch ($input['type']) {
        case 'text':
            $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
            $label = isset($input['label']) ? '<label>' . htmlspecialchars($input['label']) . '</label>' : '';
            $value = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
            $class = htmlspecialchars($input['class'] ?? 'form-control');
            $required = !empty($input['required']) ? 'required' : '';
            $pattern = !empty($input['validation']) ? 'pattern="' . htmlspecialchars($input['validation']) . '"' : '';
            $title = !empty($input['validation_message']) ? 'title="' . htmlspecialchars($input['validation_message']) . '"' : '';
            $placeholder = !empty($input['placeholder']) ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
            $maxlength = !empty($input['maxlength']) ? 'maxlength="' . htmlspecialchars($input['maxlength']) . '"' : '';
            $readonly = !empty($input['readonly']) ? 'readonly' : '';
            $disabled = !empty($input['disabled']) ? 'disabled' : '';
            
            // Generate validation message HTML
            $validation_message_html = '';
            if (!empty($input['validation_message'])) {
                $validation_message_html = '<div class="invalid-feedback validation-message">' . 
                                          htmlspecialchars($input['validation_message']) . 
                                          '</div>';
            }
            
            echo <<<HTML
            <div class="$divClass">
                $label
                <input type="text" class="$class" id="$key" name="$key" value="$value" 
                       $required $pattern $title $placeholder $maxlength $readonly $disabled>
                $validation_message_html
            </div>
            HTML;
            break;

        case 'text-button':
        ?>
            <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 input-group' ?>">
                <?php if (isset($input['label'])) echo ' <label class="' . (isset($input['label-class']) ? $input['label-class'] : "") . '">' . $input['label'] . '</label>'; ?>
                <?php if (isset($input['input-div-class'])) : ?>
                    <div class="<?= $input['input-div-class'] ?>">
                    <?php endif; ?>

                    <div class="input-group">
                        <input type="<?= $input['type'] ?>" class="<?= isset($input['class']) ? $input['class'] : 'form-control' ?>" id="<?= isset($input['id']) ? $input['id'] : $key ?>" name="<?= $key ?>" <?php if (isset($input['required'])) echo 'required'; ?> <?php if (isset($input['pattern'])) echo 'pattern="' . $input['pattern'] . '"'; ?> value="<?= isset($row[$key]) ? $row[$key] : (isset($input['value']) ? $input['value'] : '') ?>">
                        <span class="input-group-append">
                            <button id="email_button" type="button" class="btn btn-success">Verify</button>
                        </span>
                    </div>
                    <?php if (isset($input['input-div-class'])) : ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php
            break;

        case 'password':
        ?>
            <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">
                <?php if (isset($input['label'])) : ?>
                    <label class="<?= isset($input['label-class']) ? $input['label-class'] : '' ?>"><?= $input['label'] ?></label>
                <?php endif; ?>

                <?php if (isset($input['input-div-class'])) : ?>
                    <div class="<?= $input['input-div-class'] ?>">
                    <?php endif; ?>

                    <div class="input-group mb-3 position-relative">
                        <input type="password" id="<?= isset($input['id']) ? $input['id'] : $key ?>" placeholder="<?= isset($input['placeholder']) ? $input['placeholder'] : '' ?>" name="<?= $key ?>" class="form-control rounded-right <?= isset($input['class']) ? $input['class'] : '' ?>" <?= isset($input['required']) ? 'required' : '' ?> value="<?= isset($input['value']) ? $input['value'] : '' ?>">
                        <button id="toggle-password-<?= $key ?>" type="button" class="position-absolute" aria-label="Show password as plain text. Warning: this will display your password on the screen.">
                            <i id="toggle-icon-<?= $key ?>" class="fa fa-eye"></i>
                        </button>
                    </div>

                    <?php if (isset($input['input-div-class'])) : ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php
            break;

        case 'custom':
            if (isset($input['value']) && $input['value'] != '') {
                echo $input['value'];
            }
            break;

        case 'h':
        ?>
            <input type="hidden" id="<?= isset($input['id']) ? $input['id'] : $key ?>" name="<?= $key ?>" value="<?= isset($row[$key]) ? $row[$key] : (isset($input['value']) ? $input['value'] : '') ?>">
        <?php
            break;

        case 'number':
        ?>
            <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">
                <?php if (isset($input['label'])) echo ' <label class="' . (isset($input['label-class']) ? $input['label-class'] : "") . '">' . $input['label'] . '</label>'; ?>
                <?php if (isset($input['input-div-class'])) : ?>
                    <div class="<?= $input['input-div-class'] ?>">
                    <?php endif; ?>

                    <input type="number" <?php if (isset($input['minlength'])) echo 'minlength="' . $input['minlength'] . '"'; ?> class="<?= isset($input['class']) ? $input['class'] : 'form-control' ?>" id="<?= isset($input['id']) ? $input['id'] : $key ?>" name="<?= $key ?>" <?php if (isset($input['required'])) echo 'required'; ?> <?php if (isset($input['pattern'])) echo 'pattern="' . $input['pattern'] . '"'; ?> value="<?= isset($row[$key]) ? $row[$key] : (isset($input['value']) ? $input['value'] : '') ?>">
                    <?php if (isset($input['input-div-class'])) : ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php
            break;

        case 'switch':
        ?>
            <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">
                <input type="checkbox" id="<?= isset($input['id']) ? $input['id'] : $key ?>" name="<?= $key ?>" <?php if (isset($row[$key]) && $row[$key] == 1) echo 'checked'; ?> data-bootstrap-switch data-off-color="<?= isset($input['color'][1]) ? $input['color'][1] : 'primary' ?>" data-on-color="<?= isset($input['color'][0]) ? $input['color'][0] : 'success' ?> " data-off-text="<?= isset($input['label'][1]) ? $input['label'][1] : 'OFF' ?>" data-on-text="<?= isset($input['label'][0]) ? $input['label'][0] : 'ON' ?>">
            </div>
        <?php
            break;

        case 'combobox':
        ?>
            <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">
                <?php if (isset($input['label'])) echo ' <label class="' . (isset($input['label-class']) ? $input['label-class'] : "") . '">' . $input['label'] . '</label>'; ?>
                <?php if (isset($input['input-div-class'])) : ?>
                    <div class="<?= $input['input-div-class'] ?>">
                    <?php endif; ?>

                    <div class="<?= isset($input['selected-color']) ? 'select2-' . $input['selected-color'] : 'select2-info' ?>">
                        <select class="<?= isset($input['class']) ? $input['class'] : 'form-control' ?>" id="<?= isset($input['id']) ? $input['id'] : $key ?>" name="<?= $key ?>" <?php if (isset($input['required'])) echo 'required'; ?>>
                            <option value=""><?= isset($input['placeholder']) ? $input['placeholder'] : 'Choose an option' ?></option>
                            <?php foreach ($input['items'] as $item) { ?>
                                <option value="<?= $item['value'] ?>" <?php if (isset($row[$key]) && $row[$key] == $item['value']) echo 'selected';
                                                                        elseif (isset($input['value']) && $input['value'] == $item['value']) echo 'selected'; ?>><?= $item['label'] ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <?php if (isset($input['input-div-class'])) : ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php
            break;

        case 'multy-select':
        ?>
            <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">
                <?php if (isset($input['label'])) echo ' <label class="' . (isset($input['label-class']) ? $input['label-class'] : "") . '">' . $input['label'] . '</label>'; ?>
                <?php if (isset($input['input-div-class'])) : ?>
                    <div class="<?= $input['input-div-class'] ?>">
                    <?php endif; ?>

                    <div class="<?= isset($input['selected-color']) ? 'select2-' . $input['selected-color'] : 'select2-info' ?>">
                        <select id="<?= isset($input['id']) ? $input['id'] : $key ?>" name="<?= $key ?>[]" class="select2 modal-class" multiple="multiple" data-placeholder="<?= isset($input['placeholder']) ? $input['placeholder'] : '' ?>" data-dropdown-css-class="<?= isset($input['dropdown-color']) ? 'select2-' . $input['dropdown-color'] : 'select2-info' ?>" <?php if (isset($input['required'])) echo 'required'; ?> style="width: 100%;">
                            <?php foreach ($input['items'] as $item) { ?>
                                <option value="<?= $item['value'] ?>" <?php if (isset($row[$key]) && $row[$key] == $item['value']) echo 'selected';
                                                                        elseif (isset($input['value']) && $input['value'] == $item['value']) echo 'selected'; ?>><?= $item['label'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <?php if (isset($input['input-div-class'])) : ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php
            break;

        case 'image':
        ?>
            <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">
                <input type="file" class="<?= isset($input['class']) ? $input['class'] : '' ?>" id="<?= isset($input['id']) ? $input['id'] : $key ?>" name="<?= $key ?>" accept="image/*" <?php if (isset($input['required'])) echo 'required'; ?>>
            </div>
        <?php
            break;

        case 'checkbox':
        ?>
            <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12' ?>">
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input class="<?= isset($input['class']) ? $input['class'] : 'custom-control-input' ?>" type="checkbox" id="<?= isset($input['id']) ? $input['id'] : $key ?>" name="<?= $key ?>" <?php if (isset($input['required'])) echo 'required'; ?> <?php if ((isset($row[$key]) && $row[$key] == 1) || (isset($input['checked']) && $input['checked'] === true)) echo 'checked'; ?>>
                        <label class="custom-control-label" for="<?= isset($input['id']) ? $input['id'] : $key ?>"><?= $input['label'] ?></label>
                    </div>
                </div>
            </div>
        <?php
            break;

        case 'country':
        ?>
            <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">
                <?php if (isset($input['label'])) echo ' <label class="' . (isset($input['label-class']) ? $input['label-class'] : "") . '">' . $input['label'] . '</label>'; ?>
                <?php if (isset($input['input-div-class'])) : ?>
                    <div class="<?= $input['input-div-class'] ?>">
                    <?php endif; ?>

                    <div class="niceCountryInputSelector <?= isset($input['class']) ? $input['class'] : 'form-control' ?>" data-selectedcountry="<?= isset($row[$key]) ? $row[$key] : (isset($input['value']) ? $input['value'] : 'GB') ?>" data-showspecial="false" data-showflags="true" data-i18nall="All selected" data-i18nnofilter="No selection" data-i18nfilter="Filter" data-onchangecallback="<?= isset($input['onChangeCallback']) ? $input['onChangeCallback'] : 'onChangeCallback' ?>"></div>
                    <input type="hidden" id="<?= isset($input['id']) ? $input['id'] : $key ?>" name="<?= $key ?>" value="<?= isset($row[$key]) ? $row[$key] : (isset($input['value']) ? $input['value'] : '') ?>">
                    <?php if (isset($input['input-div-class'])) : ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php
            break;

        case 'textarea':
        ?>
            <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">
                <?php if (isset($input['label'])) echo ' <label class="' . (isset($input['label-class']) ? $input['label-class'] : "") . '">' . $input['label'] . '</label>'; ?>
                <?php if (isset($input['input-div-class'])) : ?>
                    <div class="<?= $input['input-div-class'] ?>">
                    <?php endif; ?>

                    <textarea class="<?= $input['class'] ?>" id="<?= isset($input['id']) ? $input['id'] : $key ?>" name="<?= $key ?>" <?php if (isset($input['required'])) echo 'required'; ?> <?php if (isset($input['pattern'])) echo 'pattern="' . $input['pattern'] . '"'; ?> rows="<?= isset($input['rows']) ? $input['rows'] : '5' ?>"><?= isset($row[$key]) ? $row[$key] : (isset($input['value']) ? $input['value'] : '') ?></textarea>
                    <?php if (isset($input['input-div-class'])) : ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php
            break;

        case 'date':
        ?>
            <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">
                <?php if (isset($input['label'])) echo ' <label class="' . (isset($input['label-class']) ? $input['label-class'] : "") . '">' . $input['label'] . '</label>'; ?>

                <?php if (isset($input['input-div-class'])) : ?>
                    <div class="<?= $input['input-div-class'] ?>">
                    <?php endif; ?>
                    <?php if (isset($input['button'])) : ?>
                        <div class="input-group">
                        <?php endif; ?>

                        <input type="date" class="<?= isset($input['class']) ? $input['class'] : 'form-control' ?>" id="<?= isset($input['id']) ? $input['id'] : $key ?>" name="<?= $key ?>" <?php if (isset($input['required'])) echo 'required'; ?> <?php if (isset($input['pattern'])) echo 'pattern="' . $input['pattern'] . '"'; ?> value="<?= isset($row[$key]) ? date('Y-m-d', strtotime($row[$key])) : (isset($input['value']) ? $input['value'] : '') ?>">

                        <?php if (isset($input['button'])) : ?>
                            <span class="input-group-append">
                                <button id="<?= $input['button']['id'] ?>" type="button" class="<?= $input['button']['class'] ?>"><?= $input['button']['text'] ?></button>
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($input['input-div-class'])) : ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php
            break;

        case 'datetime':
        ?>
            <div class="<?= isset($input['div_class']) ? $input['div_class'] : 'col-lg-12 col-md-12 form-group' ?>">
                <?php if (isset($input['label'])) echo ' <label class="' . (isset($input['label-class']) ? $input['label-class'] : "") . '">' . $input['label'] . '</label>'; ?>
                <?php if (isset($input['input-div-class'])) : ?>
                    <div class="<?= $input['input-div-class'] ?>">
                    <?php endif; ?>

                    <input type="datetime-local" class="<?= isset($input['class']) ? $input['class'] : 'form-control' ?>" id="<?= isset($input['id']) ? $input['id'] : $key ?>" name="<?= $key ?>" <?php if (isset($input['required'])) echo 'required'; ?> <?php if (isset($input['pattern'])) echo 'pattern="' . $input['pattern'] . '"'; ?> value="<?= isset($row[$key]) ? date('Y-m-d\TH:i', strtotime($row[$key])) : (isset($input['value']) ? $input['value'] : '') ?>">
                    <?php if (isset($input['input-div-class'])) : ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php
            break;

        default:
            break;
    }

    $output = ob_get_clean();
    return $output;
}

function renderFormInput($field_key, $field, $value = '', $return = false) {
    $type = $field['type'] ?? 'text';
    $label = $field['label'] ?? '';
    $placeholder = $field['placeholder'] ?? $label;
    $class = $field['class'] ?? 'form-control';
    $div_class = $field['div_class'] ?? 'form-group row';
    $label_class = $field['label_class'] ?? 'col-sm-2 col-form-label';
    $input_div_class = $field['input_div_class'] ?? 'col-sm-10';
    $required = isset($field['required']) && $field['required'] ? 'required' : '';
    $attributes = $field['attributes'] ?? [];
    $validation = $field['validation'] ?? [];
    
    if ($return) {
        ob_start();
    }
    
    echo "<div class=\"{$div_class}\">";
    
    if ($type !== 'hidden') {
        echo "<label for=\"{$field_key}\" class=\"{$label_class}\">{$label}</label>";
    }
    
    if ($type !== 'hidden') {
        echo "<div class=\"{$input_div_class}\">";
    }
    
    $attr_str = '';
    foreach ($attributes as $attr_key => $attr_value) {
        $attr_str .= " {$attr_key}=\"{$attr_value}\"";
    }
    
    $validation_str = '';
    if (isset($validation['onkeyup'])) {
        $validation_str .= " onkeyup=\"{$validation['onkeyup']}\"";
    }
    
    $input_id = isset($validation['id']) ? $validation['id'] : $field_key;
    $validation_str .= " id=\"{$input_id}\"";
    
    switch ($type) {
        case 'hidden':
            echo "<input type=\"hidden\" name=\"{$field_key}\" value=\"" . htmlspecialchars($value) . "\">";
            break;
            
        case 'select':
            echo "<select name=\"{$field_key}\" class=\"{$class}\" {$required} {$validation_str} {$attr_str}>";
            if (isset($field['options']) && is_array($field['options'])) {
                foreach ($field['options'] as $option_value => $option_label) {
                    $selected = $value == $option_value ? 'selected' : '';
                    echo "<option value=\"" . htmlspecialchars($option_value) . "\" {$selected}>" . htmlspecialchars($option_label) . "</option>";
                }
            }
            echo "</select>";
            break;
            
        case 'textarea':
            echo "<textarea name=\"{$field_key}\" class=\"{$class}\" placeholder=\"{$placeholder}\" {$required} {$validation_str} {$attr_str}>" . htmlspecialchars($value) . "</textarea>";
            break;
            
        case 'switch':
            $checked = (!empty($value) || (isset($field['checked']) && $field['checked'])) ? 'checked' : '';
            $offColor = $field['color'][1] ?? 'primary';
            $onColor = $field['color'][0] ?? 'success';
            $offText = $field['label'][1] ?? 'OFF';
            $onText = $field['label'][0] ?? 'ON';
            echo "<input type=\"checkbox\" name=\"{$field_key}\" {$validation_str} {$checked} data-bootstrap-switch " .
                 "data-off-color=\"{$offColor}\" data-on-color=\"{$onColor}\" " .
                 "data-off-text=\"{$offText}\" data-on-text=\"{$onText}\" {$attr_str}>";
            break;
            
        case 'checkbox':
            $checked = (!empty($value) || (isset($field['checked']) && $field['checked'])) ? 'checked' : '';
            echo "<div class=\"custom-control custom-checkbox\">";
            echo "<input type=\"checkbox\" class=\"custom-control-input\" name=\"{$field_key}\" {$validation_str} {$required} {$checked} {$attr_str}>";
            echo "<label class=\"custom-control-label\" for=\"{$input_id}\">{$label}</label>";
            echo "</div>";
            break;
            
        case 'date':
            $date_value = !empty($value) ? date('Y-m-d', strtotime($value)) : '';
            echo "<input type=\"date\" name=\"{$field_key}\" class=\"{$class}\" placeholder=\"{$placeholder}\" " .
                 "value=\"{$date_value}\" {$required} {$validation_str} {$attr_str}>";
            break;
            
        case 'datetime':
            $datetime_value = !empty($value) ? date('Y-m-d\TH:i', strtotime($value)) : '';
            echo "<input type=\"datetime-local\" name=\"{$field_key}\" class=\"{$class}\" placeholder=\"{$placeholder}\" " .
                 "value=\"{$datetime_value}\" {$required} {$validation_str} {$attr_str}>";
            break;
            
        default:
            echo "<input type=\"{$type}\" name=\"{$field_key}\" class=\"{$class}\" placeholder=\"{$placeholder}\" " .
                 "value=\"" . htmlspecialchars($value) . "\" {$required} {$validation_str} {$attr_str}>";
            break;
    }
    
    if (isset($validation['messages']) && is_array($validation['messages'])) {
        if (isset($validation['messages']['error'])) {
            echo "<p id=\"{$field_key}_error\" style=\"color: red; display:none;\">{$validation['messages']['error']}</p>";
        }
        if (isset($validation['messages']['success'])) {
            echo "<p id=\"{$field_key}_ok\" style=\"color: green; display:none;\">{$validation['messages']['success']}</p>";
        }
        if (isset($validation['messages']['empty'])) {
            echo "<p id=\"{$field_key}_emp\" style=\"color: red; display:none;\">{$validation['messages']['empty']}</p>";
        }
    }
    
    if ($type !== 'hidden') {
        echo "</div>";
    }
    
    echo "</div>";
    
    if ($return) {
        return ob_get_clean();
    }
}

function renderUserInput($field_key, $field, $value = '') {
    return renderFormInput($field_key, $field, $value);
}

function renderadminInput($field_key, $field, $value = '') {
    return renderFormInput($field_key, $field, $value);
}

function safe_echo($array, $key, $default = '') {
    return isset($array[$key]) && !empty($array[$key]) ? htmlspecialchars($array[$key]) : $default;
}

function items_from_model($model,$filed='f1')
{
    $list= $model->get_all()['error'] === null ? $model->get_all()['data'] : null;

    if (!$list) {
        return [];
    }

    $model_items = [];
    if ($list) {
        foreach ($list as $item) {
            $model_items[] = [
                'value' => strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $item['id'])),
                'label' => ($item[$filed])
            ];
        }
    }

    return $model_items;
}


function items_from_model_val($model,$filed='f1')
{
    $list= $model->get_all()['error'] === null ? $model->get_all()['data'] : null;

    if (!$list) {
        return [];
    }

    $model_items = [];
    if ($list) {
        foreach ($list as $item) {
            $model_items[] = [
                'value' => strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $item[$filed])),
                'label' => ($item[$filed])
            ];
        }
    }

    return $model_items;
}



function handleRedirection($result, $successRedirect, $errorRedirect = null, $successMessage = null, $errorMessage = null)
{

    // Check if 'error', 'status', and 'code' fields exist in $result
    $hasError = isset($result['error']);
    $hasStatus = isset($result['status']);
    $hasCode = isset($result['code']);
    $hasMessage = isset($result['message']);




    // Handle success case
    if (($hasStatus && $result['status'] > 0) || ($hasCode && $result['code'] == 200) || ($hasMessage && $result['message'])) {



        // If there is an error but it's not 'No data found', store it in the session
        if ($hasError && $result['error'] != null && $result['error'] != 'No data found') {
            $_SESSION['error'] = $result['error'];
        }

        // Set the success message in the session, using either the provided one or a default
        $_SESSION['message'] = array(
            'title' => $successMessage['title'] ?? 'Success',
            'text' => $successMessage['message'] ?? ($hasMessage ? $result['message'] : 'Operation completed successfully.'),
            'icon' => 'success'
        );


        // Redirect to success URL
        header('Location: ' . $successRedirect);
    }
    // Handle error case
    else {
        // If there is an error, set it in the session
        if ($hasError && $result['error'] && $result['error'] != 'No data found') {
            $_SESSION['error'] = $errorMessage ?? $result['error'];
        }

        // Redirect to error URL or fallback to success URL if no errorRedirect is provided
        $redirectUrl = $errorRedirect ?? $successRedirect;
        header('Location: ' . $redirectUrl);
    }

    exit;
}
