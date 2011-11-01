<?php
class Patient_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function index() {
		$this -> listing();
	}

	public function listing() {
		$data = array();
		$data['content_view'] = "patient_listing_v";  
		$this -> base_params($data);
	}

	public function save() {
		$this->load->database();
		$sql = $this->input->post("sql");
		$this->db->query($sql);

	}

	public function base_params($data) { 
		$data['title'] = "Patients"; 
		$data['banner_text'] = "Facility Patients";
		$data['link'] = "patients";
		$this -> load -> view('template', $data);
	}

}
?>