<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Indication_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		
	}

	public function index() {
		$this -> listing();
	}

	public function listing() {
		$access_level = $this -> session -> userdata('user_indicator');
		$infections = Opportunistic_Infection::getThemAll($access_level);
		$tmpl = array ( 'table_open'  => '<table class="setting_table">'  );
		$this -> table ->set_template($tmpl);
		$this -> table -> set_heading('Id', 'Name','Options');

		foreach ($infections as $infection) {
			$links="";
			
			if($infection->Active==1){
				$links = anchor('indication_management/edit/' .$infection->id, 'Edit',array('class' => 'edit_user','id'=>$infection->id,'name'=>$infection->Name));
				$links.=" | ";
			}
			if($access_level=="system_administrator"){
				
				if($infection->Active==1){
				$links .= anchor('indication_management/disable/' .$infection->id, 'Disable',array('class' => 'disable_user'));	
				}else{
				$links .= anchor('indication_management/enable/' .$infection->id, 'Enable',array('class' => 'enable_user'));	
				}
			}
			$this -> table -> add_row($infection->id, $infection->Name,$links);
		}

		$data['indications'] = $this -> table -> generate();
		$data['title'] = "Drug Indications";
		$data['settings_view'] = "indications_v";
		$data['banner_text'] = "Drug Indications";
		$data['link'] = "indications";
		$actions = array(0 => array('Edit', 'edit'), 1 => array('Disable', 'disable'));
		$data['actions'] = $actions;
		$this -> base_params($data);
	}

	public function save() {
		$creator_id = $this -> session -> userdata('user_id');
		$source = $this -> session -> userdata('facility');

		$indication = new Opportunistic_Infection();
		$indication -> Name = $this -> input -> post('indication_name');
		$indication -> Active = "1";
		$indication -> save();
		
		$this -> session -> set_userdata('message_counter','1');
		$this -> session -> set_userdata('message',$this -> input -> post('indication_name').' was Added');
		redirect('indication_management');
	}

	public function edit($indication_id) {
		$data['title'] = "Edit Drug Indications";
		$data['settings_view'] = "editindications_v";
		$data['banner_text'] = "Edit Drug Indications";
		$data['link'] = "indications";
		$data['indications'] = Opportunistic_Infection::getIndication($indication_id);
		$this -> base_params($data);
	}

	public function update() {
		$indication_id = $this -> input -> post('indication_id');
		$indication_name = $this -> input -> post('indication_name');
		

		$this -> load -> database();
		$query = $this -> db -> query("UPDATE opportunistic_infection SET Name='$indication_name' WHERE id='$indication_id'");
		$this -> session -> set_userdata('message_counter','1');
		$this -> session -> set_userdata('message',$this -> input -> post('indication_name').' was Updated');
		redirect('indication_management');
	}

	public function enable($indication_id) {
		$this -> load -> database();
		$query = $this -> db -> query("UPDATE opportunistic_infection SET Active='1'WHERE id='$indication_id'");
		$results=Opportunistic_Infection::getIndication($indication_id);
		$this -> session -> set_userdata('message_counter','1');
		$this -> session -> set_userdata('message',$results->Name.' was enabled');
		redirect('indication_management');
	}

	public function disable($indication_id) {
		$this -> load -> database();
		$query = $this -> db -> query("UPDATE opportunistic_infection SET Active='0'WHERE id='$indication_id'");
		$results=Opportunistic_Infection::getIndication($indication_id);
		$this -> session -> set_userdata('message_counter','2');
		$this -> session -> set_userdata('message',$results->Name.' was disabled');
		redirect('indication_management');
	}

	public function base_params($data) {
		$data['quick_link'] = "indications";
		$data['content_view'] = "settings_v";
		$this -> load -> view('template_admin', $data);
	}

	

}
