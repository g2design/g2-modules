<?php
class Form_helper {
	public function contact(){
		$form = new G2_TwigView('forms/contact');
		$form = new G2_FormMagic($form->get_render());
		
		if($form->is_posted() && $form->validate()){
			
		}
		return $form->parse();
	}
	
	public function job_application(){
		$contact_form = new G2_TwigView('forms/job-application');
		$contact_form = new G2_FormMagic($contact_form->get_render());
		
		return $contact_form->parse();
	}
}