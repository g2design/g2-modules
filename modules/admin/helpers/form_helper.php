<?php
class Form_helper {
	public function contact(){
		$form = new G2_TwigView('forms/contact');
		$form = new G2_FormMagic($form->get_render());
		
		if($form->is_posted() && $form->validate()){
			$result = Package_Submissions::getInstance()->add('Contact Form',$form->data());
			return $form->thank_you('Thank you for your enquiry. We will get back to you shortly');
		}
		return $form->parse();
	}
	
	public function job_application(){
		$form = new G2_TwigView('forms/job-application');
		$form = new G2_FormMagic($form->get_render());
		
		if($form->is_posted() && $form->validate()){
			$result = Package_Submissions::getInstance()->add('Job Application',$form->data());
			return $form->thank_you('Thank you for your enquiry. We will get back to you shortly');
		}
		
		return $form->parse();
	}
}