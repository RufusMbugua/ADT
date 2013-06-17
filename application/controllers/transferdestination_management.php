<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Transferdestination_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		
	}

	public function index() {
		$this -> listing();
	}

	public function listing() {
		$sources = Transfer_Destination::getThemAll();
		$tmpl = array ( 'table_open'  => '<table class="setting_table">'  );
		$this -> table ->set_template($tmpl);
		$this -> table -> set_heading('Id', 'Name','Options');

		foreach ($sources as $source) {
			$links = anchor('transferdestination_management/edit/' .$source->id, 'Edit',array('class' => 'edit_user'));
			$links.=" | ";
			if($source->Active==1){
			$links .= anchor('transferdestination_management/disable/' .$source->id, 'Disable',array('class' => 'disable_user'));	
			}else{
			$links .= anchor('transferdestination_management/enable/' .$source->id, 'Enable',array('class' => 'enable_user'));	
			}
			$this -> table -> add_row($source->id, $source->Name,$links);
		}

		$data['sources'] = $this -> table -> generate();
		$data['title'] = "Transfer Destinations";
		$data['banner_text'] = "Transfer Destinations";
		$data['link'] = "transferdestinations";
		$actions = array(0 => array('Edit', 'edit'), 1 => array('Disable', 'disable'));
		$data['actions'] = $actions;
		$data['settings_view'] = "transferdestination_v";
		$this -> base_params($data);
	}

	public function save() {
		$creator_id = $this -> session -> userdata('user_id');
		$source = $this -> session -> userdata('facility');

		$source = new Transfer_Destination();
		$source -> Name = $this -> input -> post('source_name');
		$source -> Active = "1";
		$source -> save();
		
		$this -> session -> set_userdata('message_counter','1');
		$this -> session -> set_userdata('message',$this -> input -> post('source_name').' was Added');
		redirect('transferdestination_management');
	}

	public function edit($source_id) {
		$data['title'] = "Edit Transfer Destinations";
		$data['settings_view'] = "edittransferdestinations_v";
		$data['banner_text'] = "Edit Transfer Destinations";
		$data['link'] = "tranferdestination";
		$data['sources'] = Transfer_Destination::getSource($source_id);
		$this -> base_params($data);
	}

	public function update() {
		$source_id = $this -> input -> post('source_id');
		$source_name = $this -> input -> post('source_name');
		

		$this -> load -> database();
		$query = $this -> db -> query("UPDATE transfer_destination SET Name='$source_name' WHERE id='$source_id'");
		$this -> session -> set_userdata('message_counter','1');
		$this -> session -> set_userdata('message',$this -> input -> post('source_name').' was Updated');
		redirect('transferdestination_management');
	}

	public function enable($source_id) {
		$this -> load -> database();
		$query = $this -> db -> query("UPDATE transfer_destination SET Active='1'WHERE id='$source_id'");
		$results=Transfer_Destination::getSource($source_id);
		$this -> session -> set_userdata('message_counter','1');
		$this -> session -> set_userdata('message',$results->Name.' was enabled');
		redirect('transferdestination_management');
	}

	public function disable($source_id) {
		$this -> load -> database();
		$query = $this -> db -> query("UPDATE transfer_destination SET Active='0'WHERE id='$source_id'");
		$results=Transfer_Destination::getSource($source_id);
		$this -> session -> set_userdata('message_counter','2');
		$this -> session -> set_userdata('message',$results->Name.' was disabled');
		redirect('transferdestination_management');
	}

	public function base_params($data) {
		$data['content_view'] = "settings_v";
		$data['quick_link'] = "transfer_destination";
		$this -> load -> view("template", $data);
	}

	

}
