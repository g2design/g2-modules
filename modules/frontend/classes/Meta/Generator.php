<?php

class Meta_Generator {

	var $title, $description;

	/**
	 *
	 * @var Zend_Config_Ini 
	 */
	var $config;

	/**
	 * 
	 * @param Zend_Config_Ini $config
	 */
	public function __construct($config) {

		if (!$config->meta) {
			throw new Exception('Config does not contain a meta group');
		}
		$this->config = $config;
	}

	function get_title($page) {
		// Title Will Consist of the main title if it exists
		$main = $this->config->meta->title->main;
		$seperator = $this->config->meta->title->seperator ? $this->config->meta->title->seperator : ' | ';
		$title = $this->convert_title($page);

		return "$title $seperator $main  ";
	}

	function get_clean_title($page) {
		// Title Will Consist of the main title if it exists
		$main = $this->config->meta->title->main;
		$seperator = $this->config->meta->title->seperator ? $this->config->meta->title->seperator : ' | ';
		$title = $this->convert_title($page, true);


		return "$title";
	}

	private function convert_title($page, $leaveout_path = false) {
		//Overwrite titles will be store in the meta.title.change.name.subname config area
		$page = str_replace('-', '_', $page);
		$page_config = str_replace('/', '->', $page);
		debug("Trying: " . "\$title =  \$this->config->meta->title->change->$page_config;");
		@eval("\$title =  \$this->config->meta->title->change->$page_config;");
		if (@$title) {
			if (!is_string($title)) {
				return ($title->index);
			}
			return $title;
		} else {
//			Auto Create title
			$title = '';
			$page_arr = explode('/', $page);
			
			if ($leaveout_path) {
				return ucfirst(str_replace(array('_', '-'), ' ', reset(array_reverse($page_arr))));
				
				
			}
			
			
			$each_title = [];
			foreach ($page_arr as $key => $page) {
				
				$each_title[] = $page;
				$page_arr[$key] = $this->convert_title(implode('/', $each_title), true);
			}
			$seperator = $this->config->meta->title->seperator ? $this->config->meta->title->seperator : ' | ';
			$title = implode($seperator, array_reverse($page_arr));
			return $title;
		}
	}

	function get_description($page) {
		$default = $this->config->meta->description->index;
		if ($this->convert_description($page)) {
			return $this->convert_description($page);
		} else
			return $default;
	}

	private function convert_description($page) {
		//Overwrite titles will be store in the meta.title.change.name.subname config area
		$page = str_replace('-', '_', $page);
		$page_config = str_replace('/', '->', filter_var($page, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		debug("Trying: " . "\$description =  \$this->config->meta->description->change->$page_config;");
		@eval("\$description =  \$this->config->meta->description->change->$page_config;");

		if (@$description) {
			if (!is_string($description)) {
				return ($description->index);
			}
			return @$description;
		} else
			return false;
	}

	public function get_extra($page) {
		$page_config = str_replace('/', '->', filter_var($page, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		$metas = [];
		if (isset($this->config->meta->add)) {

			foreach ($this->config->meta->add as $key => $config_cont) {
				@eval("\$value =  \$config_cont->$page_config;");


				if (empty($value)) {
					$metas[$key] = $config_cont->index;
				} else {
					$metas[$key] = $value;
				}
			}
		}
		$meta_str = '';
		foreach ($metas as $key => $value) {
			$meta_str .= "<meta name=\"$key\" content=\"$value\" />";
		}

		return $meta_str;
	}

}
