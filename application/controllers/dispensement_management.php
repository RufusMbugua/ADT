<?php
class Dispensement_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function index() {
		$this -> listing();
	}

	public function listing() {
		$data = array();
		$data['content_view'] = "dispensement_listing_v";  
		$this -> base_params($data);
	}

	public function save() {
		$this->load->database();
		$sql = $this->input->post("sql");
		$this->db->query($sql);

	}

	public function base_params($data) { 
		$data['title'] = "Drug Dispensements"; 
		$data['banner_text'] = "Facility Dispensements";
		$data['link'] = "dispensements";
		$this -> load -> view('template', $data);
	}

}
?>