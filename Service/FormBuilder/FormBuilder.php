<?php
/**
 * FormBuilder.php
 *
 * PHP Version 5.4
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */

namespace c24\Service\FormBuilder;

/**
 * Class HTML_Form
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 *
 */
class FormBuilder
{

    private $tag;
    private $xhtml;
    protected $errors;

    public function __construct($errors = array(), $xhtml = false)
    {
        $this->xhtml = $xhtml;
        $this->errors = $errors;
    }

    public function startForm($action = '#', $method = 'post', $id = '', $attr_ar = array())
    {
        $str = "<form action=\"$action\" method=\"$method\"";
        if (!empty($id)) {
            $str .= " id=\"$id\"";
        }
        $str .= $attr_ar? $this->addAttributes($attr_ar) . '>': '>';
        return $str;
    }

    private function addAttributes($attr_ar, $field_name = null)
    {
        if ($this->getErrors($field_name)) {
            if (!isset($attr_ar['class'])) {
                $attr_ar['class'] = 'error';
            } else {
                $attr_ar['class'].= ' error';
            }
        }

        $str = '';
        // check minimized (boolean) attributes
        $min_atts = array('checked', 'disabled', 'readonly', 'multiple',
                'required', 'autofocus', 'novalidate', 'formnovalidate'); // html5
        foreach ($attr_ar as $key=>$val) {
            if (in_array($key, $min_atts)) {
                if (!empty($val)) {
                    $str .= $this->xhtml? " $key=\"$key\"": " $key";
                }
            } else {
                $str .= " $key=\"$val\"";
            }
        }
        return $str;
    }

    public function addInput($type, $name, $value, $attr_ar = array())
    {
        $str = "<input type=\"$type\" name=\"$name\" value=\"$value\"";
        if ($attr_ar) {
            $str .= $this->addAttributes($attr_ar, $name);
        }
        $str .= $this->xhtml? ' />': '>';
        return $str;
    }

    public function addTextarea($name, $rows = 4, $cols = 30, $value = '', $attr_ar = array())
    {
        $str = "<textarea name=\"$name\" rows=\"$rows\" cols=\"$cols\"";
        if ($attr_ar) {
            $str .= $this->addAttributes($attr_ar, $name);
        }
        $str .= ">$value</textarea>";
        return $str;
    }

    // for attribute refers to id of associated form element
    public function addLabelFor($forID, $text, $attr_ar = array())
    {
        $str = "<label for=\"$forID\"";
        if ($attr_ar) {
            $str .= $this->addAttributes($attr_ar, $name);
        }
        $str .= ">$text</label>";
        return $str;
    }

    // from parallel arrays for option values and text
    public function addSelectListArrays($name, $val_list, $txt_list, $selected_value = null,
            $header = null, $attr_ar = array())
    {
        $option_list = array_combine($val_list, $txt_list);
        $str = $this->addSelectList($name, $option_list, true, $selected_value, $header, $attr_ar);
        return $str;
    }

    // option values and text come from one array (can be assoc)
    // $bVal false if text serves as value (no value attr)
    public function addSelectList($name, $option_list, $bVal = true, $selected_value = null,
            $header = null, $attr_ar = array())
    {
        $str = "<select name=\"$name\"";
        if ($attr_ar) {
            $str .= $this->addAttributes($attr_ar, $name);
        }
        $str .= ">\n";
        if (isset($header)) {
            $str .= "  <option value=\"\">$header</option>\n";
        }
        foreach ($option_list as $val => $text) {
            $str .= $bVal? "  <option value=\"$val\"": "  <option";
            if (isset($selected_value) && ($selected_value === $val || $selected_value === $text)) {
                $str .= $this->xhtml? ' selected="selected"': ' selected';
            }
            $str .= ">$text</option>\n";
        }
        $str .= "</select>";
        return $str;
    }

    public function endForm()
    {
        return "</form>";
    }

    public function startTag($tag, $attr_ar = array())
    {
        $this->tag = $tag;
        $str = "<$tag";
        if ($attr_ar) {
            $str .= $this->addAttributes($attr_ar, $name);
        }
        $str .= '>';
        return $str;
    }

    public function endTag($tag = '')
    {
        $str = $tag? "</$tag>": "</$this->tag>";
        $this->tag = '';
        return $str;
    }

    public function addEmptyTag($tag, $attr_ar = array())
    {
        $str = "<$tag";
        if ($attr_ar) {
            $str .= $this->addAttributes($attr_ar);
        }
        $str .= $this->xhtml? ' />': '>';
        return $str;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    protected function getErrors($name = null)
    {
        if ($name) {
            if (!empty($this->errors[$name])) {
                return $this->errors[$name];
            }
            return false;
        } elseif (!empty($this->errors)) {
            return $this->errors;
        }
        return false;
    }

    public function addErrors($name)
    {
        $str = '';
        $errors = $this->getErrors($name);
        if ($errors) {
            foreach ($errors as $v) {
                $str.='<span class="error_message error">'.$v['message'].'</span>';
            }
        }
        return $str;
    }
}