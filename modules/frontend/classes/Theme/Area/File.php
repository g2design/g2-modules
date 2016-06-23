<?php

class Theme_Area_File extends Theme_Area_Default {

	protected $replace_content_only = false;
	protected $value = null, $file_stored = false;

	function get_form_field() {
		return "{$this->get_label()} <input name=\"{$this->area->id}\" type=\"file\">";
	}

	/**
	 * 
	 * @param type $value
	 * 
	 */
	public function set_value($value) {
		$this->value = $value;
		if(!$this->file_stored) {
			$this->store_file();
			$this->file_stored = true;
		}
	}
	
	function store_file(){
		$value = $this->value;
		//Save the file inside the database
		$file = R::dispense('file');
		$filename = $value[G2_FormMagic::FILE_NAME];

		$filename = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $filename);
		// Remove any runs of periods (thanks falstro!)
		$filename = preg_replace("([\.]{2,})", '', $filename);
		//Move the file to an upload directory;
		$directory = "uploads/";

		//check if the current file exist. If it does update the name
		$count = 1;
		$start_looking = $filename;
		while (file_exists($directory . $start_looking)) {
			$start_looking = str_replace('.'.Mvc_Functions::get_extension($filename), '', $filename)."_$count".'.'.Mvc_Functions::get_extension($filename);
			$count++;
		}
		
		$filename = $start_looking;
		$full_uri = $directory.$start_looking;
		
		if(!is_dir(dirname($full_uri))) mkdir (dirname ($full_uri),0777, true);
		if(!file_exists($value[G2_FormMagic::FILE_URI])) {
			return;
		}
		move_uploaded_file($value[G2_FormMagic::FILE_URI], $full_uri);
		
		//Save the file to database
		$file->name = $filename;
		$file->uri = $full_uri;
		R::store($file);
		
		$this->area->file = $file;
	}

	function save() {
		//Update html value to match new uploaded file
		
		$c = \Wa72\HtmlPageDom\HtmlPageCrawler::create($this->area->html);
		if($this->area->file != null) {
			$c->setAttribute('href', $this->area->file->uri);
		}
		
		$this->area->html = $c->saveHTML();
		parent::save();
		return true;
	}
	
	function file_type($file_location) {
		$mime = finfo_open(FILEINFO_MIME);

		if ($mime === FALSE) {
			throw new Exception('Unable to open finfo');
		}
		$filetype = finfo_file($mime, $file_location);
		finfo_close($mime);
		if ($filetype === FALSE) {
			throw new Exception('Unable to recognise filetype');
		}

		list($mime, $junk) = explode(';', $filetype);
		return $mime;
	}

}
