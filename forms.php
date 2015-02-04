<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/formslib.php');

class buttons extends moodleform{
	function definition() {
		
		$mform =& $this->_form;
		//<a href="../../message/edit.php?id='.$USER->id.'">
		$buttonarray=array();
		$buttonarray[]=$mform->createElement('static', 'description1', '',get_string('notificationsettingstext', 'local_facebook'));
		$buttonarray[]=$mform->createElement('button','link',get_string('notificationsettings', 'local_facebook'),array("onClick"=>"window.location.href='../../message/edit.php'"));
		$mform->addGroup($buttonarray);
		
		
		$buttonarray=array();
		$buttonarray[]=$mform->createElement('static', 'description2', '',get_string('cancelnotifications', 'local_facebook'));
		$buttonarray[]=$mform->createElement('submit','disconnect',get_string('disconnectaccount', 'local_facebook'));
		$mform->addGroup($buttonarray);
		
	}
}
class connect extends moodleform{
	function definition() {

		$mform =& $this->_form;
		$instance = $this->_customdata;
		$duplicate=$instance['duplicate'];
	
		if($duplicate==false){
		
		$mform->addElement('static', 'description1', '',get_string('connectquestion', 'local_facebook'));
		$mform->addElement('submit','connect',get_string('connectbutton', 'local_facebook'));
		
		}
		//si la cuenta ya se encuentra enlazada y activa no lo deja enlazar
		else{
			$mform->addElement('static', 'description1', '',get_string('accused', 'local_facebook',array("style="=>"color:red")));
			$mform->addElement('button','link',get_string('back', 'local_facebook'),array("onClick"=>"window.location.href='../../'"));
			}
	
	
	
	
	}
}







