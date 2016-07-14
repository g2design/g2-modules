<?php
class Theme_Area_Link extends Theme_Area_Default {
	public function enable_edit() {
		$this->c()->setAttribute('data-area', $this->area->getID());
		$this->c()->setAttribute('data-type', $this->area->type);
		$this->c()->addClass('link-editor');
	}
}