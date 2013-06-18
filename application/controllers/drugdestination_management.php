<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Drugdestination_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		
	}

	public function index() {
		$this -> listing();
	}

	public function listing() {
		$access_level = $this -> session -> userdata('user_indicator');
		$sources = Drug_Destination::getThemAll($access_level);
		$tmpl = array ( 'table_open'  => '<table class="setting_table">'  );
		$this -> table ->set_template($tmpl);
		$this -> table -> set_heading('Id', 'Name','Options');

		foreach ($sources as $source) {
			$links="";
			if($source->Active==1){
				$links = anchor('drugdestination_management/edit/' .$source->id, 'Edit',array('class' => 'edit_user','id'=>$source->id,'name'=>$source->Name));
				$links.=" | ";
			}
			if($access_level=="system_administrator"){
				if($source->Active==1){
				$links .= anchor('drugdestination_management/disable/' .$source->id, 'Disable',array('class' => 'disable_user'));	
				}else{
				$links .= anchor('drugdestination_management/enable/' .$source->id, 'Enable',array('class' => 'enable_user'));	
				}
			}
			$this -> table -> add_row($source->id, $source->Name,$links);
		}

		$data['sources'] = $this -> table -> generate();
		$data['title'] = "Drug Destinations";
		$data['banner_text'] = "Drug Destinations";
		$data['link'] = "drugdestinations";
		$actions = array(0 => array('Edit', 'edit'), 1 => array('Disable', 'disable'));
		$data['actions'] = $actions;
		$data['settings_view'] = "drugdestination_v";
		$this -> base_params($data);
	}

	public function save() {
		$creator_id = $this -> session -> userdata('user_id');
		$source = $this -> session -> userdata('facility');

		$source = new Drug_Destination();
		$source -> Name = $this -> input -> post('source_name');
		$source -> Active = "1";
		$source -> save();
		
		$this -> session -> set_userdata('message_counter','1');
		$this -> session -> set_userdata('message',$this -> input -> post('source_name').' was Added');
		redirect('drugdestination_management');
	}

	public function edit($source_id) {
		$data['title'] = "Edit Drug Destinations";
		$data['settings_view'] = "editdrugdestinations_v";
		$data['banner_text'] = "Edit Drug Destinations";
		$data['link'] = "drugdestination";
		$data['sources'] = Drug_Destination::getSource($source_id);
		$this -> base_params($data);
	}

	public function update() {
		$source_id = $this -> input -> post('source_id');
		$source_name = $this -> input -> post('source_name');
		

		$this -> load -> database();
		$query = $this -> db -> query("UPDATE drug_destination SET Name='$source_name' WHERE id='$source_id'");
		$this -> session -> set_userdata('message_counter','1');
		$this -> session -> set_userdata('message',$this -> input -> post('source_name').' was Updated');
		redirect('drugdestination_management');
	}

	public function enable($source_id) {
		$this -> load -> database();
		$query = $this -> db -> query("UPDATE drug_destination SET Active='1'WHERE id='$source_id'");
		$results=Drug_Destination::getSource($source_id);
		$this -> session -> set_userdata('message_counter','1');
		$this -> session -> set_userdata('message',$results->Name.' was enabled');
		redirect('drugdestination_management');
	}

	public function disable($source_id) {
		$this -> load -> database();
		$query = $this -> db -> query("UPDATE drug_destination SET Active='0'WHERE id='$source_id'");
		$results=Drug_Destination::getSource($source_id);
		$this -> session -> set_userdata('message_counter','2');
		$this -> session -> set_userdata('message',$results->Name.' was disabled');
		redirect('drugdestination_management');
	}

	public function base_params($data) {
		$data['content_view'] = "settings_v";
		$data['quick_link'] = "drug_destination";
		$this -> load -> view("template", $data);
	}

	

}