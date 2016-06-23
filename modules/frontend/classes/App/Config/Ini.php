<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class App_Config_Ini extends Zend_Config_Ini {

	/**
	 * Load the INI file from disk using parse_ini_file(). Use a private error
	 * handler to convert any loading errors into a Zend_Config_Exception
	 *
	 * @param string $filename
	 * @throws Zend_Config_Exception
	 * @return array
	 */
	protected function _parseIniFile($filename) {
		set_error_handler(array($this, '_loadFileErrorHandler'));
		if (substr($filename, -4) == '.ini') {
			$iniArray = parse_ini_file($filename, true);
		} else {
			$iniArray = parse_ini_string($filename, true);
		}
		restore_error_handler();

		// Check if there was a error while loading file
		if ($this->_loadFileErrorStr !== null) {
			/**
			 * @see Zend_Config_Exception
			 */
			require_once 'Zend/Config/Exception.php';
			throw new Zend_Config_Exception($this->_loadFileErrorStr);
		}

		return $iniArray;
	}

}
