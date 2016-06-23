<?php

use Imagine\Image\Box;
use Imagine\Image\Point;

class Theme_Area_Background extends Theme_Area_File {
	protected $replace_content_only = false;
	public function validate() {
		//Validate that the image uploaded is infact an image
		$types = [
			'image/jpeg',
			'image/png',
			'image/gif'
		];

		if (!in_array($this->file_type($this->area->file->uri), $types)) {
			return "File uploaded is not an image";
		}

		//Also validate that the image is bigger than the requested size

		$c = \Wa72\HtmlPageDom\HtmlPageCrawler::create($this->area->html);
		
		$size = $c->getAttribute('mvc-size') ? $c->getAttribute('mvc-size') : '1x1';
		list($width, $height) = explode('x', $size);

		$imagin = new Imagine\Gd\Imagine();
		$image = $imagin->open($this->area->file->uri);
		
		if($image->getSize()->getHeight() < $height || $image->getSize()->getWidth() < $width) {
			return "Image is not big enough for saving. Please make sure the image is atleast $size";
		}
		
	}
	
	function save() {
		//Update html value to match new uploaded file
		
		$c = \Wa72\HtmlPageDom\HtmlPageCrawler::create($this->area->html);
		if($this->area->file != null) {
			$c->setAttribute('style', 'background-image:url(\''.$this->area->file->uri.'\');');
		}
		
		$this->area->html = $c->saveHTML();
		R::store($this->area);
		
		return true;
	}

}
