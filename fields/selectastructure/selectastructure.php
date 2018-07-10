<?php

/*
*  Credit where it's due, most of this code is Sonja's:
*  https://forum.getkirby.com/t/fetch-query-from-parent-page/1290/6
*/

class SelectAStructureField extends BaseField {

	public function __construct() {
		$this->type    = 'selectastructure';
		$this->icon    = 'chevron-down';
		$this->label   = 'category';
		$this->options = array();

	}

	public function options() {
		return FieldOptions::build($this);
	}

	public function option($value, $text, $selected = false) {
		return new Brick('option', $this->i18n($text), array(
		'value'    => $value,
		'selected' => $selected
	));
	}

	public function input() {
		$select = new Brick('select');
		$select->addClass('selectbox');
		$select->attr(array(
			'name'         => $this->name(),
			'id'           => $this->id(),
			'required'     => $this->required(),
			'autocomplete' => $this->autocomplete(),
			'autofocus'    => $this->autofocus(),
			'readonly'     => $this->readonly(),
			'disabled'     => $this->disabled(),
		));

		$default = $this->default();

		if(!$this->required()) {
			$select->append($this->option('', '', $this->value() == ''));
		}

		if($this->readonly()) {
			$select->attr('tabindex', '-1');
		}

		// First, let's pull the page and field from the blueprint.
		if($this->structurepage() == '/') {
		    $structurepage = site();
		} else if(!$this->structurepage()) {
			$structurepage = $this->page();
		}
		else {
		    $structurepage = page($this->structurepage());
		}
		// Below lines need serious refractoring
		$structurefield = $this->structurefield();
		$structurefield = $structurepage->$structurefield();
		$optionid = $this->optionid();
		$optionkey = str::split($this->optionkey());
		$optiondivider = $this->optiondivider() != null ? $this->optiondivider() : ', ';

		// If the strucure field exists, toStrucure() it.
		if($this->page($structurepage)->field($structurefield)) {
			$structure = $structurefield->toStructure();
		}

		// Build the list of options.
		foreach($structure as $entry)  {
			$string = '';
			foreach($optionkey as $key) {
				if($entry->$key() != '') {
					$string = $string . $optiondivider . $entry->$key();
				}
			}
			if($entry->$optionid() != '') {
				$optionidvalue = $entry->$optionid();
			}
			$this->options[$optionidvalue->value()] = str::substr($string, str::length($optiondivider)); // remove the unecessary divider in the beginning
		}

		// Add the options to the select field.
		foreach($this->options() as $value => $text) {
			$select->append($this->option($value, $text, $this->value() == $value));  // $this->value() == $value instead of $this->value() == $text
		}

		$inner = new Brick('div');
		$inner->addClass('selectbox-wrapper');
		$inner->append($select);

		$wrapper = new Brick('div');
		$wrapper->addClass('input input-with-selectbox');
		$wrapper->append($inner);

		if($this->readonly()) {
			$wrapper->addClass('input-is-readonly');
		} else {
			$wrapper->attr('data-focus', 'true');
		}

		return $wrapper;

	}

}
