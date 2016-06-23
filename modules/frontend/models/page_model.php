<?php

class Page_Model extends Mvc_Model {
	/*
	 * Form function
	 */

	function form(RedBeanPHP\OODBBean $page) {
		//Check if object is loaded
		//Load areas connected to this page
		$areas = $page->ownArea;

		//Load form html
		$view = new G2_TwigView('forms/page/page');

		//Create processors
		$areasP = [];
		$data = [];
		foreach ($areas as $area) {
			$areaP = new Theme_Area_Processor($area);
			
			$areasP[$area->id] = $areaP;
			$data[$area->id] = $areaP->render();
		}
		$view->areas = $areasP;
		$view->page = $page;

		$form = new G2_FormMagic($view->get_render());
		if (!$form->is_posted()) {
			$form->set_data($data);
		}
		
		if($form->is_posted()) {
			//Field Validate the content
			$data = $form->data();
			foreach ($data as $area_id => $value) {
				
				$areasP[$area_id]->set_value($value);
				$message = $areasP[$area_id]->validate();
				if( $message !== true ) {
					$form->invalidate($area_id, $message);
				}
			}
			
			foreach($form->get_uploaded_files()  as $key => $file) {
				$areasP[$key]->set_value($file);
				
				$message = $areasP[$key]->validate();
				if( $message !== true ) {
					$form->invalidate($area_id, $message);
				}
			}
		}

		if ($form->is_posted() && $form->validate()) {
			$data = $form->data();
			foreach ($data as $area_id => $value) {
				
				$areasP[$area_id]->set_value($value);
				
				$areasP[$area_id]->save();
			}
			
			foreach($form->get_uploaded_files()  as $key => $file) {
				$areasP[$key]->set_value($file);
				
				$areasP[$key]->save();
			}

			return true;
		}

		echo $form->parse();
	}

}
