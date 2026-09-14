<?php

class Base {
    protected $db;
    protected $table;
    public $row;
    protected $timestamps = true;

    public function __construct($id = null) {
        $this->db = Database::getInstance();
        if ($id) {
            $this->row = $this->find_by_id($id);
        } else {
            $this->row = [];
        }
    }

    public function __get($name) {
        if (is_object($this->row) && isset($this->row->$name)) {
            return $this->row->$name;
        } elseif (is_array($this->row) && isset($this->row[$name])) {
            return $this->row[$name];
        }
        return null;
    }

    public function __isset($name) {
        if (is_object($this->row)) {
            return isset($this->row->$name);
        } elseif (is_array($this->row)) {
            return isset($this->row[$name]);
        }
        return false;
    }

    public function insert(array $data) {
        if ($this->timestamps) {
            $data['created_date'] = date('Y-m-d H:i:s');
        }
        return $this->db->insert($this->table, $data);
    }

    public function update($id, array $data) {
        if ($this->timestamps) {
            $data['updated_date'] = date('Y-m-d H:i:s');
        }
        return $this->db->update($this->table, $data, "id = $id");
    }

    public function find_by_id($id){
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function all(){
        return $this->db->fetchAll("SELECT * FROM {$this->table}");
    }

    public function whereFirst($col, $val){
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE $col = ? LIMIT 1", [$val]);
    }

    public function delete($id){
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }

    public function update_or_insert(array $where, array $data){
        $col = array_key_first($where);
        $val = $where[$col];

        if($ex = $this->whereFirst($col, $val)){
            return $this->update($ex->id, $data);
        }
        return $this->insert($data);
    }

    /**
     * Render form elements with enhanced validation support
     */
    public function renderFormElements($form_config)
    {
        if (empty($form_config['inputs'])) {
            return;
        }

        $row = [];
        if (!empty($this->row)) {
            $row = is_object($this->row) ? (array) $this->row : $this->row;
        }

        foreach ($form_config['inputs'] as $key => $input) {
            if (!empty($input['skip'])) {
                continue;
            }
            
            $this->renderField($key, $input, $row);
        }
    }

    /**
     * Render individual field with proper validation attributes
     */
    private function renderField($key, $input, $row)
    {
        $divClass = $input['div_class'] ?? 'col-lg-12 col-md-12 form-group';
        $label = isset($input['label']) ? '<label for="' . $key . '">' . htmlspecialchars($input['label']) . '</label>' : '';
        $requiredStar = !empty($input['required']) ? '<span class="text-danger">*</span>' : '';
        $value = htmlspecialchars($row[$key] ?? $input['value'] ?? '');
        $class = htmlspecialchars($input['class'] ?? 'form-control');
        $required = !empty($input['required']) ? 'required' : '';
        $readonly = !empty($input['readonly']) ? 'readonly' : '';
        $disabled = !empty($input['disabled']) ? 'disabled' : '';

        // Build validation attributes
        $validationAttrs = $this->buildValidationAttributes($input);
        
        // Validation message
        $validation_message_html = '';
        if (!empty($input['validation_message'])) {
            $validation_message_html = '<div class="invalid-feedback">' . 
                htmlspecialchars($input['validation_message']) . 
                '</div>';
        }

        switch ($input['type']) {
            case 'text':
                $placeholder = !empty($input['placeholder']) ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label $requiredStar
                    <input type="text" class="$class" id="$key" name="$key" value="$value" 
                           $required $validationAttrs $placeholder $readonly $disabled>
                    $validation_message_html
                </div>
HTML;
                break;

            case 'email':
                $placeholder = !empty($input['placeholder']) ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label $requiredStar
                    <input type="email" class="$class" id="$key" name="$key" value="$value" 
                           $required $validationAttrs $placeholder $readonly $disabled>
                    $validation_message_html
                </div>
HTML;
                break;

            case 'password':
                $placeholder = !empty($input['placeholder']) ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label $requiredStar
                    <input type="password" class="$class" id="$key" name="$key" value="" 
                           $required $validationAttrs $placeholder $readonly $disabled autocomplete="new-password">
                    $validation_message_html
                </div>
HTML;
                break;

            case 'number':
                $placeholder = !empty($input['placeholder']) ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
                $min = !empty($input['min']) ? 'min="' . htmlspecialchars($input['min']) . '"' : 'min="0"';
                $max = !empty($input['max']) ? 'max="' . htmlspecialchars($input['max']) . '"' : '';
                $step = !empty($input['step']) ? 'step="' . htmlspecialchars($input['step']) . '"' : 'step="1"';
                echo <<<HTML
                <div class="$divClass">
                    $label $requiredStar
                    <input type="number" class="$class" id="$key" name="$key" value="$value"
                           $required $validationAttrs $placeholder $min $max $step $readonly $disabled>
                    $validation_message_html
                </div>
HTML;
                break;

            case 'tel':
                $placeholder = !empty($input['placeholder']) ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label $requiredStar
                    <input type="tel" class="$class" id="$key" name="$key" value="$value" 
                           $required $validationAttrs $placeholder $readonly $disabled>
                    $validation_message_html
                </div>
HTML;
                break;

            case 'url':
                $placeholder = !empty($input['placeholder']) ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label $requiredStar
                    <input type="url" class="$class" id="$key" name="$key" value="$value" 
                           $required $validationAttrs $placeholder $readonly $disabled>
                    $validation_message_html
                </div>
HTML;
                break;

            case 'date':
                $min = !empty($input['min']) ? 'min="' . htmlspecialchars($input['min']) . '"' : '';
                $max = !empty($input['max']) ? 'max="' . htmlspecialchars($input['max']) . '"' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label $requiredStar
                    <input type="date" class="$class" id="$key" name="$key" value="$value" 
                           $required $validationAttrs $min $max $readonly $disabled>
                    $validation_message_html
                </div>
HTML;
                break;

            case 'time':
                $min = !empty($input['min']) ? 'min="' . htmlspecialchars($input['min']) . '"' : '';
                $max = !empty($input['max']) ? 'max="' . htmlspecialchars($input['max']) . '"' : '';
                $step = !empty($input['step']) ? 'step="' . htmlspecialchars($input['step']) . '"' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label $requiredStar
                    <input type="time" class="$class" id="$key" name="$key" value="$value" 
                           $required $validationAttrs $min $max $step $readonly $disabled>
                    $validation_message_html
                </div>
HTML;
                break;

            case 'datetime-local':
                $min = !empty($input['min']) ? 'min="' . htmlspecialchars($input['min']) . '"' : '';
                $max = !empty($input['max']) ? 'max="' . htmlspecialchars($input['max']) . '"' : '';
                echo <<<HTML
                <div class="$divClass">
                    $label $requiredStar
                    <input type="datetime-local" class="$class" id="$key" name="$key" value="$value" 
                           $required $validationAttrs $min $max $readonly $disabled>
                    $validation_message_html
                </div>
HTML;
                break;

            case 'textarea':
                $placeholder = !empty($input['placeholder']) ? 'placeholder="' . htmlspecialchars($input['placeholder']) . '"' : '';
                $rows = $input['rows'] ?? '5';
                echo <<<HTML
                <div class="$divClass">
                    $label $requiredStar
                    <textarea class="$class" id="$key" name="$key" rows="$rows" 
                              $required $validationAttrs $placeholder $readonly $disabled>$value</textarea>
                    $validation_message_html
                </div>
HTML;
                break;

            case 'file':
                $accept = !empty($input['accept']) ? 'accept="' . htmlspecialchars($input['accept']) . '"' : '';
                $previewStyle = $value === '' ? 'display: none; max-height: 100px;' : 'max-height: 100px;';
                echo <<<HTML
                <div class="$divClass">
                    $label
                    <input type="file" class="$class" id="$key" name="$key" $accept $required $disabled>
                    <img id="{$key}-preview" class="img-fluid mt-2" src="$value" alt="" style="$previewStyle">
                    $validation_message_html
                </div>
HTML;
                break;

            case 'select':
            case 'dropdown':
                $items = $input['items'] ?? [];
                $multiple = !empty($input['multiple']) ? 'multiple' : '';
                $items_html = '<option value="">Select</option>';
                
                foreach ($items as $item) {
                    $itemValue = htmlspecialchars($item['value'] ?? '');
                    $itemLabel = htmlspecialchars($item['label'] ?? $itemValue);
                    $selected = ($value == $itemValue) ? 'selected' : '';
                    $items_html .= "<option value='$itemValue' $selected>$itemLabel</option>";
                }
                
                echo <<<HTML
                <div class="$divClass">
                    $label $requiredStar
                    <select class="$class" id="$key" name="$key" $required $disabled $multiple>
                        $items_html
                    </select>
                    $validation_message_html
                </div>
HTML;
                break;

            case 'checkbox':
                $checked = ((isset($row[$key]) && $row[$key] == 1) || 
                           (isset($input['checked']) && $input['checked'])) ? 'checked' : '';
                echo <<<HTML
                <div class="$divClass">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="$key" name="$key" 
                               value="1" $checked $required>
                        <label class="custom-control-label" for="$key">
                            {$input['label']} $requiredStar
                        </label>
                    </div>
                    $validation_message_html
                </div>
HTML;
                break;

            case 'hidden':
                echo '<input type="hidden" id="' . $key . '" name="' . $key . '" value="' . $value . '">';
                break;

            default:
                // Handle any other input types
                break;
        }
    }

    /**
     * Build validation attributes from input config
     */
    private function buildValidationAttributes($input): string
    {
        $attrs = [];

        if (!empty($input['pattern'])) {
            $attrs[] = 'pattern="' . htmlspecialchars($input['pattern']) . '"';
        }

        if (!empty($input['minlength'])) {
            $attrs[] = 'minlength="' . htmlspecialchars($input['minlength']) . '"';
        }

        if (!empty($input['maxlength'])) {
            $attrs[] = 'maxlength="' . htmlspecialchars($input['maxlength']) . '"';
        }

        if (!empty($input['min'])) {
            $attrs[] = 'min="' . htmlspecialchars($input['min']) . '"';
        }

        if (!empty($input['max'])) {
            $attrs[] = 'max="' . htmlspecialchars($input['max']) . '"';
        }

        if (!empty($input['step'])) {
            $attrs[] = 'step="' . htmlspecialchars($input['step']) . '"';
        }

        if (!empty($input['title'])) {
            $attrs[] = 'title="' . htmlspecialchars($input['title']) . '"';
        }

        return implode(' ', $attrs);
    }
}