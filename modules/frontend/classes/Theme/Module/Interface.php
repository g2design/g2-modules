<?php

interface Theme_Module_Interface {
	function __construct($id, $page_id = false);
	function render();
}
