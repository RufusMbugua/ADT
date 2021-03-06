<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Home_Controller extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function index() {

		$this -> platform_home();
	}

	public function platform_home() {
		//Check if the user is already logged in and if so, take him to their home page. Else, display the platform home page.
		$user_id = $this -> session -> userdata('user_id');
		if (strlen($user_id) > 0) {
			redirect("home_controller/home");
		}
		$data = array();
		$data['current'] = "home_controller";
		$data['title'] = "webADT | System Dashboard";
		$data['banner_text'] = "System Dashboard";
		$data['content_view'] = "platform_home_v";
		$this -> load -> view("template_platform", $data);
	}

	public function home() {
	
		$rights = User_Right::getRights($this -> session -> userdata('access_level'));
		$menu_data = array();
		$menus = array();
		$counter = 0;
		foreach ($rights as $right) {
			$menu_data['menus'][$right -> Menu] = $right -> Access_Type;
			$menus['menu_items'][$counter]['url'] = $right -> Menu_Item -> Menu_Url;
			$menus['menu_items'][$counter]['text'] = $right -> Menu_Item -> Menu_Text;
			$menus['menu_items'][$counter]['offline'] = $right -> Menu_Item -> Offline;
			$counter++;
		}
		$this -> session -> set_userdata($menu_data);
		$this -> session -> set_userdata($menus);

		//Check if the user is a pharmacist. If so, update his/her local envirinment with current values
		if ($this -> session -> userdata('user_indicator') == "pharmacist") {
			$facility_code = $this -> session -> userdata('facility');
			//Retrieve the Totals of the records in the master database that have clones in the clients!
			$today = date('m/d/Y');
			$timestamp = strtotime($today);
			$data['scheduled_patients'] = Patient_Appointment::getAllScheduled($timestamp);
		}
		
		$data['title'] = "webADT | System Home";
		$data['content_view'] = "home_v";
		$data['banner_text'] = "Home";
		$data['link'] = "home";

		//Get mac address
		//$get_mac = "getmac";
		//exec($get_mac, $output, $ret);
		//$value = explode('\\', $output[3]);
		//$data['mac'] = $value[0];
		$data['user'] = $this -> session -> userdata['full_name'];
		$this -> load -> view("template", $data);

	}

	public function synchronize_patients() {
		$data['regimens'] = Regimen::getAll();
		$data['supporters'] = Supporter::getAll();
		$data['service_types'] = Regimen_Service_Type::getAll();
		$data['sources'] = Patient_Source::getAll();
		$data['drugs'] = Drugcode::getAll();
		$data['regimen_change_purpose'] = Regimen_Change_Purpose::getAll();
		$data['visit_purpose'] = Visit_Purpose::getAll();
		$data['opportunistic_infections'] = Opportunistic_Infection::getAll();
		$data['regimen_drugs'] = Regimen_Drug::getAll();
	}

	public function getNotified() {
		//Notify for patients
		// set current date
		$notice = array();
		$date = date('y-m-d');
		// parse about any English textual datetime description into a Unix timestamp
		$ts = strtotime($date);
		// find the year (ISO-8601 year number) and the current week
		$year = date('o', $ts);
		$week = date('W', $ts);
		$facility_code = $this -> session -> userdata('facility');
		$this -> load -> database();
		/*
		 $f1=1;
		 $l1=6;

		 $fts = strtotime($year . 'W' . $week . $f1);
		 $lts = strtotime($year . 'W' . $week . $l1);
		 $first_date=date("Y-m-d ", $fts);
		 $last_date=date("Y-m-d ", $lts);
		 */
		// print week for the current date
		for ($i = 1; $i <= 6; $i++) {
			// timestamp from ISO week date format
			$ts = strtotime($year . 'W' . $week . $i);
			$string_date = date("l", $ts);
			$number_date = date("Y-m-d ", $ts);

			$appointment_query = $this -> db -> query("SELECT COUNT(distinct(patient)) as Total from patient_appointment where appointment='$number_date' and facility='$facility_code'");
			$visit_query = $this -> db -> query("SELECT COUNT(distinct(patient_id)) as Total from patient_visit where dispensing_date='$number_date' and visit_purpose='2'and facility='$facility_code'");
			$appointments_on_date = $appointment_query -> result_array();
			$visits_on_date = $visit_query -> result_array();
			$notice['Days'][$i - 1] = $string_date;
			$notice['Appointments'][$i - 1] = $appointments_on_date[0]['Total'];
			$notice['Visits'][$i - 1] = $visits_on_date[0]['Total'];
			$notice['Percentage'][$i - 1] = round((@$visits_on_date[0]['Total'] / @$appointments_on_date[0]['Total']) * 100, 2) . "%";

		}
		echo json_encode($notice);

	}

}
