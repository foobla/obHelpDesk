<?php 
defined('JPATH_PLATFORM') or die;
jimport('joomla.form.formfield');


class obHelpDeskFieldsHelper {
	
	public static function printField( $field) {
		if($field->required) {
			$classrequired = 'required';
			$attrequired = 'required="required"';
		} else {
			$classrequired  = '';
			$attrequired = '';
		}
		$html = '';
		switch ($field->type) {
			case 'text':
				$html = '<input type="text" name="jform[field]['.$field->id.']"  value="'.$field->default_value.'" class="inputbox '.$classrequired.'" '.$attrequired.' />';
				break;
			case 'radio':
				$html = self::radioField($field->id, $field->values, $field->default_value, $field->breakline, $classrequired, $attrequired);
				break;
			case 'checkbox':
			case 'checkboxes':
				$html = self::checkboxesField($field->id, $field->values, $field->default_value, $field->breakline, $classrequired, $attrequired);
				break;
			case 'list':
				$html = self::listField($field->id, $field->values, $field->default_value, $field->size, $field->multiple, $classrequired, $attrequired) ;
				break;
			case 'textarea':
				$html = self::textareaField($field->id, $field->default_value, $field->rows, $field->cols, $field->editor, $classrequired, $attrequired);
				break;
			case 'calendar':
				$params = JComponentHelper::getParams('com_obhelpdesk');
				$format = $params->get('cutomfield_date_format','%Y-%m-%d');
				$html = self::calendarField($field, $format);
				break;
			case 'datetime':
				$params = JComponentHelper::getParams('com_obhelpdesk');
				$format = $params->get('cutomfield_datetime_format', '%Y-%m-%d %H:%M:%S');
				$html = self::calendarField($field, $format);
				break;
			default:
				;
			break;
		}

		// add help-block
		$html .= '<p class="help-block">'.$field->helptext.'</p>';

		return $html;
	}
	
	public static function calendarField($field, $format) {
		if($field->required) $required = true; 
		else $required = false;
		
		if($field->required) $classrequired = 'required'; 
		else $classrequired = '';
		
		return JHtml::calendar($field->default_value, 'jform[field]['.$field->id.']', 'obhelpdesk_'.$field->id, $format, array('required' => $required, 'class' => $classrequired));
	}
	
	
	public static function radioField($id, $values, $default_value, $dir = 1, $classrequired, $attrequired) {
		$arr = explode("\n", $values);
		$html = '<fieldset id="jform_obhelpdesk_'.$id.'_"'.$attrequired.' class="'.$classrequired.' radio">';
		if(count($arr)) {
			$i = 0;
			foreach($arr as $f) {
				$ex = explode(':', $f);
				if(count($ex) == 1) $ex[1] = $ex[0];
				$checked = '';
				if(trim($ex[0]) == trim($default_value)) $checked = 'checked="checked"';
				if($dir ==  0) {
					$html .= '<label for="jform_obhelpdesk_'.$id.'" class="radio inline">'. '<input type="radio" aria-invalid="false" id="jform_obhelpdesk_'.$id.'_'.$i.'" name="jform[field]['.$id.']" value="'.trim($ex[0]).'" '.$checked.' /> '.trim($ex[1]). '</label>';
				}else {
					$html .= '<label for="jform_obhelpdesk_'.$id.'">'. '<input type="radio" aria-invalid="false" id="jform_obhelpdesk_'.$id.'_'.$i.'" name="jform[field]['.$id.'][]" value="'.trim($ex[0]).'" '.$checked.' /> '.trim($ex[1]). '</label>';
				}
				$i ++;
			}
		}
		$html .= '</fieldset>';
		return $html;
	}
	
	public static function checkboxesField($id, $values, $default_value, $dir = 1, $classrequired, $attrequired) {
		$arr = explode("\n", $values);
		$defaults = explode('|', $default_value);
		$trim_de = array();
		foreach ($defaults as $default) $trim_de[] = trim($default); 
		$html = '<fieldset id="jform_obhelpdesk_'.$id.'_"'.$attrequired.' class="'.$classrequired.' checkboxes">';
		if(count($arr)) {
			$i = 0;
			foreach($arr as $f) {
				$ex = explode(':', $f);
				if(count($ex) == 1) $ex[1] = $ex[0];
				$checked = '';
				if(in_array(trim($ex[0]), $trim_de)) $checked = 'checked="checked"';
				if($dir == 0) {
					$html .= '<label for="jform_obhelpdesk_'.$id.'" class="checkbox inline">'. '<input type="checkbox" aria-invalid="false" id="jform_obhelpdesk_'.$id.'_'.$i.'" name="jform[field]['.$id.'][]" value="'.trim($ex[0]).'" '.$checked.' /> '.trim($ex[1]). '</label>';
				} else {
					$html .= '<label for="jform_obhelpdesk_'.$id.'">'. '<input type="checkbox" aria-invalid="false" id="jform_obhelpdesk_'.$id.'_'.$i.'" name="jform[field]['.$id.'][]" value="'.trim($ex[0]).'" '.$checked.' /> '.trim($ex[1]). '</label>';
				}
				$i++;
			}
			
		}
		$html .= '</fieldset>';
		return $html;
	}
	
	public static function listField($id, $values, $default_value, $size, $multiple, $classrequired, $attrequired) {
		$arr = explode("\n", $values);
		$trim_de = array();
		if($multiple) {
			$html = '<select name="jform[field]['.$id.'][]" size="'.$size.'" multiple="true" class="'.$classrequired.'" '.$attrequired.' >';
			$defaults = explode('|', $default_value);
			foreach ($defaults as $default) $trim_de[] = trim($default); 
		}
		else {
			$html = '<select name="jform[field]['.$id.']" class="'.$classrequired.'" '.$attrequired.'>';
			$trim_de = array(trim($default_value));
		}
		if(count($arr)) {
			foreach($arr as $f) {
				$ex = explode(':', $f);
				if(count($ex) == 1) $ex[1] = $ex[0];
				$selected = '';
				
				if(in_array(trim($ex[0]), $trim_de))  $selected = 'selected="selected"';
				$html .= '<option value="'.trim($ex[0]).'" '.$selected.' /> '.trim($ex[1]). '</option>';
			}
			
		}
		$html .= '</select>';
		return $html;
	}
	
	public static function textareaField($id, $default_value, $rows, $cols, $editor = 0, $classrequired, $attrequired) {
		if($editor) {
			return '<textarea name="jform[field]['.$id.']" rows="'.$rows.'" class="form-control '.$classrequired.'" '.$attrequired.' >'.trim($default_value).'</textarea>';
		}
		
		return '<textarea name="jform[field]['.$id.']" rows="'.$rows.'" class="form-control '.$classrequired.'" '.$attrequired.' >'.trim($default_value).'</textarea>';
	}
	
	//public function saveOneField($field)
}