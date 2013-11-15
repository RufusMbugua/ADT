<?php
class Patient_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this -> load -> database();
		$this -> load -> library('PHPExcel');
		ini_set("max_execution_time", "100000");
		ini_set('memory_limit', '512M');
		$this -> load -> database();
	}

	public function index() {
		$data['content_view'] = "patient_listing_v";
		$this -> base_params($data);
		//$this -> listing();
	}

	public function details() {
		$data['content_view'] = "patient_details_v";
		$data['hide_side_menu'] = 1;
		$this -> base_params($data);
	}

	public function addpatient_show() {
		$data = array();
		$data['districts'] = District::getPOB();
		$data['genders'] = Gender::getAll();
		$data['statuses'] = Patient_Status::getStatus();
		$data['sources'] = Patient_Source::getSources();
		$data['supporters'] = Supporter::getAllActive();
		$data['service_types'] = Regimen_Service_Type::getHydratedAll();
		$data['facilities'] = Facilities::getAll();
		$data['family_planning'] = Family_Planning::getAll();
		$data['other_illnesses'] = Other_Illnesses::getAll();
		$data['hide_side_menu'] = '1';
		$data['content_view'] = "add_patient_v";
		$this -> base_params($data);
	}

	public function checkpatient_no($patient_no) {
		//Variables
		$facility_code = $this -> session -> userdata('facility');
		$sql = "select * from patient where facility_code='$facility_code' and patient_number_ccc='$patient_no'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			echo json_decode("1");
		} else {
			echo json_decode("0");
		}

	}

	public function listing() {
		$access_level = $this -> session -> userdata('user_indicator');
		$facility_code = $this -> session -> userdata('facility');
		$link = "";
		//Testing, don't judge
		$data = array();
		/* Array of database columns which should be read and sent back to DataTables. Use a space where
		 * you want to insert a non-database field (for example a counter or static image)
		 */
		$aColumns = array('Patient_Number_CCC', 'First_Name', 'Last_Name', 'Other_Name', 'Phone', 'Date_Enrolled', 'NextAppointment', 'Regimen_Desc', 'Name');

		$iDisplayStart = $this -> input -> get_post('iDisplayStart', true);
		$iDisplayLength = $this -> input -> get_post('iDisplayLength', true);
		$iSortCol_0 = $this -> input -> get_post('iSortCol_0', true);
		$iSortingCols = $this -> input -> get_post('iSortingCols', true);
		$sSearch = $this -> input -> get_post('sSearch', true);
		$sEcho = $this -> input -> get_post('sEcho', true);

		// Paging
		if (isset($iDisplayStart) && $iDisplayLength != '-1') {
			$this -> db -> limit($this -> db -> escape_str($iDisplayLength), $this -> db -> escape_str($iDisplayStart));
		}

		// Ordering
		if (isset($iSortCol_0)) {
			for ($i = 0; $i < intval($iSortingCols); $i++) {
				$iSortCol = $this -> input -> get_post('iSortCol_' . $i, true);
				$bSortable = $this -> input -> get_post('bSortable_' . intval($iSortCol), true);
				$sSortDir = $this -> input -> get_post('sSortDir_' . $i, true);

				if ($bSortable == 'true') {
					$this -> db -> order_by($aColumns[intval($this -> db -> escape_str($iSortCol))], $this -> db -> escape_str($sSortDir));
				}
			}
		}

		/*
		 * Filtering
		 * NOTE this does not match the built-in DataTables filtering which does it
		 * word by word on any field. It's possible to do here, but concerned about efficiency
		 * on very large tables, and MySQL's regex functionality is very limited
		 */
		if (isset($sSearch) && !empty($sSearch)) {
			for ($i = 0; $i < count($aColumns); $i++) {
				$bSearchable = $this -> input -> get_post('bSearchable_' . $i, true);

				// Individual column filtering
				if (isset($bSearchable) && $bSearchable == 'true') {
					$this -> db -> or_like($aColumns[$i], $this -> db -> escape_like_str($sSearch));
				}
			}
		}

		// Select Data
		$this -> db -> select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $aColumns)), false);

		$this -> db -> select("p.id,p.Patient_Number_CCC,p.First_Name,p.Last_Name,p.Other_Name,p.Phone,p.Physical,p.Date_Enrolled,p.NextAppointment,r.Regimen_Desc,s.Name,p.Active");
		$this -> db -> from("patient p");
		$this -> db -> where("p.Facility_Code", $facility_code);
		$this -> db -> join("regimen r", "r.id=p.Current_Regimen");
		$this -> db -> join("patient_status s", "s.id=p.current_status");

		$rResult = $this -> db -> get();

		// Data set length after filtering
		$this -> db -> select('FOUND_ROWS() AS found_rows');
		$iFilteredTotal = $this -> db -> get() -> row() -> found_rows;

		// Total data set length
		$this -> db -> select("p.*");
		$this -> db -> from("patient p");
		$this -> db -> where("p.Facility_Code", $facility_code);
		$this -> db -> join("regimen r", "r.id=p.Current_Regimen");
		$this -> db -> join("patient_status s", "s.id=p.current_status");
		$tot_patients = $this -> db -> get();
		$iTotal = count($tot_patients -> result_array());

		// Output
		$output = array('sEcho' => intval($sEcho), 'iTotalRecords' => $iTotal, 'iTotalDisplayRecords' => $iFilteredTotal, 'aaData' => array());

		foreach ($rResult->result_array() as $aRow) {
			$row = array();
			$col = 0;
			$name = "";
			$id = "";
			foreach ($aColumns as $col) {
				if ($col == "First_Name" or $col == "Last_Name" or $col == "Other_Name") {
					if ($col == "First_Name") {
						$name = $aRow[$col] . " ";
						$name = strtoupper($name);
						continue;
					} else {
						if ($col == "Last_Name") {
							$name .= $aRow[$col] . " ";
							$name = strtoupper($name);
							continue;
						} else if ($col == "Other_Name") {
							$name .= $aRow[$col];
							$name = strtoupper($name);
							$name = "<span style='white-space:nowrap;'>" . $name . "</span>";
						}

					}
				} else if ($col == "Date_Enrolled") {
					$name = date('d-M-Y', strtotime($aRow[$col]));
				} else if ($col == "NextAppointment") {
					if ($aRow[$col]) {
						$name = date('d-M-Y', strtotime($aRow[$col]));
					} else {
						$name = "N/A";
					}
				}
				//Check if phone No does not exist
				else if ($col == "Phone") {
					if ($aRow[$col] == "") {
						$name = str_replace(" ", "", $aRow['Physical']);
					} else {
						$name = str_replace(" ", "", $aRow['Phone']);
					}
				} else if ($col == "Regimen_Desc") {
					$name = "<b style='white-space:nowrap;'>" . $aRow[$col] . "</b>";
				} else if ($col == "Name") {
					$name = "<b>" . $aRow[$col] . "</b>";
				} else {
					$name = $aRow[$col];
					$name = strtoupper($name);
				}

				$row[] = $name;
			}
			$id = $aRow['id'];
			$link = "";
			if ($access_level == "facility_administrator") {
				if ($aRow['Active'] == 1) {
					$link = '| <a href="' . base_url() . 'patient_management/disable/' . $id . '" class="red">Disable</a>';

				} else {
					$link = '| <a href="' . base_url() . 'patient_management/enable/' . $id . '" class="green">Enable</a>';
				}
			}

			$row[] = '<a href="' . base_url() . 'patient_management/viewDetails/' . $id . '">Detail</a> | <a href="' . base_url() . 'patient_management/edit/' . $id . '">Edit</a> ' . $link;
			$output['aaData'][] = $row;
		}
		echo json_encode($output);
	}

	public function extract_illness($illness_list = "") {
		$illness_array = explode(",", $illness_list);
		$new_array=array();
		foreach ($illness_array as $index => $illness) {
			if ($illness == null) {
				unset($illness_array[$index]);
			}else{
				$illness=str_replace("\n", "", $illness);
				$new_array[]=trim($illness);
			}
		}
		return json_encode($new_array);
	}

	public function viewDetails($record_no) {
		$this -> session -> set_userdata('record_no', $record_no);
		$patient = "";
		$facility = "";
		$sql = "select * from patient where id='$record_no'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$results[0]['other_illnesses'] = $this -> extract_illness($results[0]['other_illnesses']);
			$data['results'] = $results;
			$patient = $results[0]['patient_number_ccc'];
			$facility = $this -> session -> userdata("facility");
		}
		//Patient History
		$sql = "SELECT pv.dispensing_date, v.name AS visit, u.Name AS unit, pv.dose, pv.duration, pv.indication, pv.id AS record, d.drug, pv.quantity, pv.current_weight, pv.current_height, r1.regimen_desc as last_regimen, r.regimen_desc, pv.batch_number, pv.pill_count, pv.adherence, pv.user, pv.regimen_change_reason FROM patient_visit pv LEFT JOIN drugcode d ON pv.drug_id = d.id LEFT JOIN drug_unit u ON d.unit = u.id LEFT JOIN regimen r ON pv.regimen = r.id LEFT JOIN regimen r1 ON pv.last_regimen = r1.id LEFT JOIN visit_purpose v ON pv.visit_purpose = v.id WHERE pv.patient_id =  '$patient' AND pv.facility =  '$facility' AND pv.active='1' ORDER BY dispensing_date DESC";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$data['history_logs'] = $results;
		} else {
			$data['history_logs'] = "";
		}

		$data['districts'] = District::getPOB();
		$data['genders'] = Gender::getAll();
		$data['statuses'] = Patient_Status::getStatus();
		$data['sources'] = Patient_Source::getSources();
		$data['supporters'] = Supporter::getAllActive();
		$data['service_types'] = Regimen_Service_Type::getHydratedAll();
		$data['facilities'] = Facilities::getAll();
		$data['family_planning'] = Family_Planning::getAll();
		$data['other_illnesses'] = Other_Illnesses::getAll();
		$data['regimens'] = Regimen::getRegimens();

		$data['content_view'] = 'patient_details_v';
		//Hide side menus
		$data['hide_side_menu'] = '1';
		$this -> base_params($data);
	}

	public function edit($record_no) {
		$sql = "select * from patient where id='$record_no'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$results[0]['other_illnesses'] = $this -> extract_illness($results[0]['other_illnesses']);
			$data['results'] = $results;
		}
		$data['record_no'] = $record_no;
		$data['districts'] = District::getPOB();
		$data['genders'] = Gender::getAll();
		$data['statuses'] = Patient_Status::getStatus();
		$data['sources'] = Patient_Source::getSources();
		$data['supporters'] = Supporter::getAllActive();
		$data['service_types'] = Regimen_Service_Type::getHydratedAll();
		$data['facilities'] = Facilities::getAll();
		$data['family_planning'] = Family_Planning::getAll();
		$data['other_illnesses'] = Other_Illnesses::getAll();
		$data['regimens'] = Regimen::getRegimens();
		$data['content_view'] = 'edit_patients_v';
		//Hide side menus
		$data['hide_side_menu'] = '1';
		$this -> base_params($data);

	}

	public function save() {
		$family_planning = "";
		$other_illness_listing = "";
		$patient = "";

		$family_planning = $this -> input -> post('family_planning_holder', TRUE);
		if ($family_planning == null) {
			$family_planning = "";
		}
		$other_illness_listing = $this -> input -> post('other_illnesses_holder', TRUE);
		if ($other_illness_listing == null) {
			$other_illness_listing = "";
		}
		$other_chronic = $this -> input -> post('other_chronic', TRUE);
		if ($other_chronic != "") {
			if ($other_illness_listing) {
				$other_illness_listing = $other_illness_listing . "," . $other_chronic;
			} else {
				$other_illness_listing = $other_chronic;
			}
		}

		//Patient Information & Demographics
		$new_patient = new Patient();
		$new_patient -> Medical_Record_Number = $this -> input -> post('medical_record_number', TRUE);
		$new_patient -> Patient_Number_CCC = $this -> input -> post('patient_number', TRUE);
		$new_patient -> First_Name = $this -> input -> post('first_name', TRUE);
		$new_patient -> Last_Name = $this -> input -> post('last_name', TRUE);
		$new_patient -> Other_Name = $this -> input -> post('other_name', TRUE);
		$new_patient -> Dob = $this -> input -> post('dob', TRUE);
		$new_patient -> Pob = $this -> input -> post('pob', TRUE);
		$new_patient -> Gender = $this -> input -> post('gender', TRUE);
		$new_patient -> Pregnant = $this -> input -> post('pregnant', TRUE);
		$new_patient -> Start_Weight = $this -> input -> post('weight', TRUE);
		$new_patient -> Start_Height = $this -> input -> post('height', TRUE);
		$new_patient -> Start_Bsa = $this -> input -> post('surface_area', TRUE);
		$new_patient -> Weight = $this -> input -> post('weight', TRUE);
		$new_patient -> Height = $this -> input -> post('height', TRUE);
		$new_patient -> Sa = $this -> input -> post('surface_area', TRUE);
		$new_patient -> Phone = $this -> input -> post('phone', TRUE);
		$new_patient -> SMS_Consent = $this -> input -> post('sms_consent', TRUE);
		$new_patient -> Physical = $this -> input -> post('physical', TRUE);
		$new_patient -> Alternate = $this -> input -> post('alternate', TRUE);

		//Patient History
		$new_patient -> Partner_Status = $this -> input -> post('partner_status', TRUE);
		$new_patient -> Disclosure = $this -> input -> post('disclosure', TRUE);
		$new_patient -> Fplan = $family_planning;
		$new_patient -> Other_Illnesses = $other_illness_listing;
		$new_patient -> Other_Drugs = $this -> input -> post('other_drugs', TRUE);
		$new_patient -> Adr = $this -> input -> post('other_allergies_listing', TRUE);
		$new_patient -> Support_Group = $this -> input -> post('support_group_listing', TRUE);
		$new_patient -> Smoke = $this -> input -> post('smoke', TRUE);
		$new_patient -> Alcohol = $this -> input -> post('alcohol', TRUE);
		$new_patient -> Tb = $this -> input -> post('tb', TRUE);
		$new_patient -> Tbphase = $this -> input -> post('tbphase', TRUE);
		$new_patient -> Startphase = $this -> input -> post('fromphase', TRUE);
		$new_patient -> Endphase = $this -> input -> post('tophase', TRUE);

		//Program Information
		$new_patient -> Date_Enrolled = $this -> input -> post('enrolled', TRUE);
		$new_patient -> Current_Status = $this -> input -> post('current_status', TRUE);
		//$new_patient -> Status_Change_Date = $this -> input -> post('status_started', TRUE);
		$new_patient -> Source = $this -> input -> post('source', TRUE);
		$new_patient -> Transfer_From = $this -> input -> post('transfer_source', TRUE);
		$new_patient -> Supported_By = $this -> input -> post('support', TRUE);
		$new_patient -> Facility_Code = $this -> session -> userdata('facility');
		$new_patient -> Service = $this -> input -> post('service', TRUE);
		$new_patient -> Start_Regimen = $this -> input -> post('regimen', TRUE);
		$new_patient -> Current_Regimen = $this -> input -> post('regimen', TRUE);
		$new_patient -> Start_Regimen_Date = $this -> input -> post('service_started', TRUE);
		$new_patient -> save();

		$patient = $this -> input -> post('patient_number', TRUE);
		$direction = $this -> input -> post('direction', TRUE);

		if ($direction == 0) {
			$this -> session -> set_userdata('msg_success', 'Patient: ' . $this -> input -> post('first_name', TRUE) . " " . $this -> input -> post('last_name', TRUE) . ' was Saved');
			redirect("patient_management");
		} else if ($direction == 1) {
			redirect("dispensement_management/dispense/$patient");
		}
	}

	public function update($record_id) {
		$family_planning = "";
		$other_illness_listing = "";
		$prev_appointment = "";
		$facility = "";
		$patient = "";

		//Check if appointment exists
		$prev_appointment = $this -> input -> post('prev_appointment_date', TRUE);
		$appointment = $this -> input -> post('next_appointment_date', TRUE);
		$facility = $this -> session -> userdata('facility');
		$patient = $this -> input -> post('patient_number', TRUE);
		if ($appointment) {
			$sql = "select * from patient_appointment where patient='$patient' and appointment='$prev_appointment' and facility='$facility'";
			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			if ($results) {
				$record_no = $results[0]['id'];
				//If exisiting appointment(Update new Record)
				$sql = "update patient_appointment set appointment='$appointment',patient='$patient',facility='$facility' where id='$record_no'";
			} else {
				//If no appointment(Insert new record)
				$sql = "insert patient_appointment(patient,appointment,facility)VALUES('$patient','$appointment','$facility')";
			}
			$this -> db -> query($sql);
		}

		$family_planning = $this -> input -> post('family_planning_holder', TRUE);
		if ($family_planning == null) {
			$family_planning = "";
		}
		$other_illness_listing = $this -> input -> post('other_illnesses_holder', TRUE);
		if ($other_illness_listing == null) {
			$other_illness_listing = "";
		}
		$other_chronic = $this -> input -> post('other_chronic', TRUE);
		if ($other_chronic != "") {
			if ($other_illness_listing) {
				$other_illness_listing = $other_illness_listing . "," . $other_chronic;
			} else {
				$other_illness_listing = $other_chronic;
			}
		}

		$other_drugs = $this -> input -> post('other_drugs', TRUE);
		if (!$other_drugs) {
			$other_drugs = "";
		}
		$other_allergies = $this -> input -> post('other_allergies_listing', TRUE);
		if (!$other_allergies) {
			$other_allergies = "";
		}

		$data = array('Medical_Record_Number' => $this -> input -> post('medical_record_number', TRUE), 'Patient_Number_CCC' => $this -> input -> post('patient_number', TRUE), 'First_Name' => $this -> input -> post('first_name', TRUE), 'Last_Name' => $this -> input -> post('last_name', TRUE), 'Other_Name' => $this -> input -> post('other_name', TRUE), 'Dob' => $this -> input -> post('dob', TRUE), 'Pob' => $this -> input -> post('pob', TRUE), 'Gender' => $this -> input -> post('gender', TRUE), 'Pregnant' => $this -> input -> post('pregnant', TRUE), 'Start_Weight' => $this -> input -> post('start_weight', TRUE), 'Start_Height' => $this -> input -> post('start_height', TRUE), 'Start_Bsa' => $this -> input -> post('start_bsa', TRUE), 'Weight' => $this -> input -> post('current_weight', TRUE), 'Height' => $this -> input -> post('current_height', TRUE), 'Sa' => $this -> input -> post('current_bsa', TRUE), 'Phone' => $this -> input -> post('phone', TRUE), 'SMS_Consent' => $this -> input -> post('sms_consent', TRUE), 'Physical' => $this -> input -> post('physical', TRUE), 'Alternate' => $this -> input -> post('alternate', TRUE), 'Partner_Status' => $this -> input -> post('partner_status', TRUE), 'Disclosure' => $this -> input -> post('disclosure', TRUE), 'Fplan' => $family_planning, 'Other_Illnesses' => $other_illness_listing, 'Other_Drugs' => $other_drugs, 'Adr' => $other_allergies, 'Smoke' => $this -> input -> post('smoke', TRUE), 'Alcohol' => $this -> input -> post('alcohol', TRUE), 'Tb' => $this -> input -> post('tb', TRUE), 'Tbphase' => $this -> input -> post('tbphase', TRUE), 'Startphase' => $this -> input -> post('fromphase', TRUE), 'Endphase' => $this -> input -> post('tophase', TRUE), 'Date_Enrolled' => $this -> input -> post('enrolled', TRUE), 'Current_Status' => $this -> input -> post('current_status', TRUE), 'Status_Change_Date' => $this -> input -> post('status_started', TRUE), 'Source' => $this -> input -> post('source', TRUE), 'Transfer_From' => $this -> input -> post('transfer_source', TRUE), 'Supported_By' => $this -> input -> post('support', TRUE), 'Facility_Code' => $this -> session -> userdata('facility'), 'Service' => $this -> input -> post('service', TRUE), 'Start_Regimen' => $this -> input -> post('regimen', TRUE), 'Start_Regimen_Date' => $this -> input -> post('service_started', TRUE), 'Current_Regimen' => $this -> input -> post('current_regimen', TRUE), 'Nextappointment' => $this -> input -> post('next_appointment_date', TRUE));
		$this -> db -> where('id', $record_id);
		$this -> db -> update('patient', $data);

		//Set session for notications
		$this -> session -> set_userdata('msg_save_transaction', 'success');
		$this -> session -> set_userdata('user_updated', $this -> input -> post('first_name'));

		redirect("patient_management/viewDetails/$record_id");
	}

	public function base_params($data) {
		$data['title'] = "webADT | Patients";
		$data['banner_text'] = "Facility Patients";
		$data['link'] = "patients";
		$this -> load -> view('template', $data);
	}

	public function create_timestamps() {
		$visits = Patient_Visit::getAll();
		foreach ($visits as $visit) {
			$current_date = $visit -> Dispensing_Date;
			$changed_date = strtotime($current_date);
			$visit -> Dispensing_Date_Timestamp = $changed_date;
			$visit -> save();
		}
	}

	public function regimen_breakdown() {
		$selected_facility = $this -> input -> post('facility');
		if (isset($selected_facility)) {
			$facility = $this -> input -> post('facility');
		}
		$data = array();
		$data['current'] = "patient_management";
		$data['title'] = "webADT | Patient Regimen Breakdown";
		$data['content_view'] = "patient_regimen_breakdown_v";
		$data['banner_text'] = "Patient Regimen Breakdown";
		$data['facilities'] = Reporting_Facility::getAll();
		//Get the regimen data
		$data['optimal_regimens'] = Regimen::getOptimalityRegimens("1");
		$data['sub_optimal_regimens'] = Regimen::getOptimalityRegimens("2");
		$months = 12;
		$months_previous = 11;
		$regimen_data = array();
		for ($current_month = 1; $current_month <= $months; $current_month++) {
			$start_date = date("Y-m-01", strtotime("-$months_previous months"));
			$end_date = date("Y-m-t", strtotime("-$months_previous months"));
			//echo $start_date." to ".$end_date."</br>";
			if ($facility) {
				$get_month_statistics_sql = "SELECT regimen,count(patient_id) as patient_numbers,sum(months_of_stock) as months_of_stock FROM (select  distinct patient_id,months_of_stock,regimen,dispensing_date from `patient_visit` where facility = '" . $facility . "' and  dispensing_date between str_to_date('" . $start_date . "','%Y-%m-%d') and str_to_date('" . $end_date . "','%Y-%m-%d')) patient_visits group by regimen";
			} else {
				$get_month_statistics_sql = "SELECT regimen,count(patient_id) as patient_numbers,sum(months_of_stock) as months_of_stock FROM (select  distinct patient_id,months_of_stock,regimen,dispensing_date from `patient_visit` where dispensing_date between str_to_date('" . $start_date . "','%Y-%m-%d') and str_to_date('" . $end_date . "','%Y-%m-%d')) patient_visits group by regimen";
			}
			$month_statistics_query = $this -> db -> query($get_month_statistics_sql);
			foreach ($month_statistics_query->result_array() as $month_data) {
				$regimen_data[$month_data['regimen']][$start_date] = array("patient_numbers" => $month_data['patient_numbers'], "mos" => $month_data['months_of_stock']);
			}
			//echo $get_month_statistics_sql . "<br>";
			$months_previous--;
		}
		$data['regimen_data'] = $regimen_data;
		$this -> load -> view("platform_template", $data);
	}

	public function create_appointment_timestamps() {
		/*$appointments = Patient_Appointment::getAll();
		 foreach($appointments as $appointment){
		 $app_date = $appointment->Appointment;
		 $changed_date = strtotime($app_date);
		 //echo $app_date." currently becomes ".$changed_date." which was initially ".date("m/d/Y",$changed_date)."<br>";
		 $appointment->Appointment = $changed_date;
		 $appointment->save();
		 }*/
	}

	public function export() {
		$facility_code = $this -> session -> userdata('facility');
		$sql = "SELECT medical_record_number,patient_number_ccc,first_name,last_name,other_name,dob,pob,IF(gender=1,'MALE','FEMALE')as gender,IF(pregnant=1,'YES','NO')as pregnant,weight as Current_Weight,height as Current_height,sa as Current_BSA,p.phone,physical as Physical_Address,alternate as Alternate_Address,other_illnesses,other_drugs,adr as Drug_Allergies,IF(tb=1,'YES','NO')as TB,IF(smoke=1,'YES','NO')as smoke,IF(alcohol=1,'YES','NO')as alcohol,date_enrolled,ps.name as Patient_source,s.Name as supported_by,timestamp,facility_code,rst.name as Service,r1.regimen_desc as Start_Regimen,start_regimen_date,pst.Name as Current_status,migration_id,machine_code,IF(sms_consent=1,'YES','NO') as SMS_Consent,fplan as Family_Planning,tbphase,startphase,endphase,IF(partner_status=1,'Concordant',IF(partner_status=2,'Discordant','')) as partner_status,status_change_date,IF(partner_type=1,'YES','NO') as Disclosure,support_group,r.regimen_desc as Current_Regimen,nextappointment,start_height,start_weight,start_bsa,IF(p.transfer_from !='',f.name,'N/A') as Transfer_From,DATEDIFF(nextappointment,CURDATE()) AS Days_to_NextAppointment
FROM patient p
left join regimen r on r.id=p.current_regimen
left join regimen r1 on r1.id=p.start_regimen
left join patient_source ps on ps.id=p.source
left join supporter s on s.id=p.supported_by
left join regimen_service_type rst on rst.id=p.service
left join patient_status pst on pst.id=p.current_status
left join facilities f on f.facilitycode=p.transfer_from
WHERE facility_code='$facility_code'
ORDER BY p.patient_number_ccc ASC";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();

		$objPHPExcel = new PHPExcel();
		$objPHPExcel -> setActiveSheetIndex(0);
		$i = 1;

		$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $i, "medical_record_number");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $i, "patient_number_ccc");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $i, "first_name");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $i, "last_name");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('E' . $i, "other_name");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('F' . $i, "dob");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('G' . $i, "pob");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('H' . $i, "gender");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('I' . $i, "pregnant");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('J' . $i, "Current_Weight");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('K' . $i, "Current_height");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('L' . $i, "Current_BSA");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('M' . $i, "phone");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('N' . $i, "Physical_Address");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('O' . $i, "Alternate_Address");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('P' . $i, "other_illnesses");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('Q' . $i, "other_drugs");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('R' . $i, "Drug_Allergies");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('S' . $i, "TB");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('T' . $i, "smoke");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('U' . $i, "alcohol");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('V' . $i, "date_enrolled");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('W' . $i, "Patient_source");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('X' . $i, "supported_by");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('Y' . $i, "timestamp");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('Z' . $i, "facility_code");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AA' . $i, "pob");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AB' . $i, "Service");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AC' . $i, "Start_Regimen");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AD' . $i, "start_regimen_date");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AE' . $i, "Current_status");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AF' . $i, "migration_id");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AG' . $i, "machine_code");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AH' . $i, "SMS_Consent");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AI' . $i, "Family_Planning");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AJ' . $i, "tbphase");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AK' . $i, "startphase");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AL' . $i, "endphase");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AM' . $i, "partner_status");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AN' . $i, "status_change_date");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AO' . $i, "Disclosure");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AP' . $i, "support_group");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AQ' . $i, "Current_Regimen");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AR' . $i, "nextappointment");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AS' . $i, "start_height");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AT' . $i, "start_weight");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AU' . $i, "start_bsa");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AV' . $i, "Transfer_From");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('AW' . $i, "Days_To_NextAppointment");

		foreach ($results as $result) {
			$i++;
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $i, $result["medical_record_number"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $i, $result["patient_number_ccc"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $i, $result["first_name"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $i, $result["last_name"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('E' . $i, $result["other_name"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('F' . $i, $result["dob"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('G' . $i, $result["pob"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('H' . $i, $result["gender"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('I' . $i, $result["pregnant"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('J' . $i, $result["Current_Weight"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('K' . $i, $result["Current_height"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('L' . $i, $result["Current_BSA"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('M' . $i, $result["phone"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('N' . $i, $result["Physical_Address"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('O' . $i, $result["Alternate_Address"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('P' . $i, $result["other_illnesses"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('Q' . $i, $result["other_drugs"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('R' . $i, $result["Drug_Allergies"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('S' . $i, $result["TB"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('T' . $i, $result["smoke"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('U' . $i, $result["alcohol"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('V' . $i, $result["date_enrolled"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('W' . $i, $result["Patient_source"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('X' . $i, $result["supported_by"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('Y' . $i, $result["timestamp"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('Z' . $i, $result["facility_code"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AA' . $i, $result["pob"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AB' . $i, $result["Service"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AC' . $i, $result["Start_Regimen"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AD' . $i, $result["start_regimen_date"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AE' . $i, $result["Current_status"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AF' . $i, $result["migration_id"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AG' . $i, $result["machine_code"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AH' . $i, $result["SMS_Consent"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AI' . $i, $result["Family_Planning"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AJ' . $i, $result["tbphase"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AK' . $i, $result["startphase"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AL' . $i, $result["endphase"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AM' . $i, $result["partner_status"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AN' . $i, $result["status_change_date"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AO' . $i, $result["Disclosure"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AP' . $i, $result["support_group"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AQ' . $i, $result["Current_Regimen"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AR' . $i, $result["nextappointment"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AS' . $i, $result["start_height"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AT' . $i, $result["start_weight"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AU' . $i, $result["start_bsa"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AV' . $i, $result["Transfer_From"]);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AW' . $i, $result["Days_to_NextAppointment"]);

		}

		if (ob_get_contents())
			ob_end_clean();
		$filename = "Patient Master For " . $facility_code . ".csv";
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename=' . $filename);

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');

		$objWriter -> save('php://output');

		$objPHPExcel -> disconnectWorksheets();
		unset($objPHPExcel);

	}

	public function enable($id) {
		$sql = "update patient set active='1' where id='$id'";
		$this -> db -> query($sql);
		$get_user = "select first_name FROM patient WHERE id='$id' LIMIT 1";
		$user_sql = $this -> db -> query($get_user);
		$user_array = $user_sql -> result_array();
		$first_name = "";
		foreach ($user_array as $value) {
			$first_name = $value['first_name'];
		}
		//Set session for notications
		$this -> session -> set_userdata('msg_save_transaction', 'success');
		$this -> session -> set_userdata('user_enabled', $first_name);
		redirect("patient_management");
	}

	public function disable($id) {
		$sql = "update patient set active='0' where id='$id'";
		$this -> db -> query($sql);
		$get_user = "select first_name FROM patient WHERE id='$id' LIMIT 1";
		$user_sql = $this -> db -> query($get_user);
		$user_array = $user_sql -> result_array();
		$first_name = "";
		foreach ($user_array as $value) {
			$first_name = $value['first_name'];
		}
		//Set session for notications
		$this -> session -> set_userdata('msg_save_transaction', 'success');
		$this -> session -> set_userdata('user_disabled', $first_name);
		redirect("patient_management");
	}

	public function getAppointments($appointment = "") {
		$results = "";
		$sql = "select count(distinct(patient)) as total_appointments,weekend_max,weekday_max from patient_appointment pa,facilities f  where pa.appointment = '$appointment' and f.facilitycode=pa.facility";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		echo json_encode($results);
	}

	public function getSixMonthsDispensing($patient_no) {
		$facility = $this -> session -> userdata("facility");
		$dyn_table = "";
		$sql = "select * from patient_visit pv left join drugcode d on d.id=pv.drug_id left join dose ds on ds.Name=pv.dose where patient_id = '$patient_no' and datediff(curdate(),dispensing_date)<=180 and datediff(curdate(),dispensing_date)>0 and pv.facility='$facility' and pv.active='1' order by pv.dispensing_date desc";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			foreach ($results as $result) {
				if ($result['pill_count'] == "") {
					$result['pill_count'] = "-";
				}
				if ($result['missed_pills'] == "") {
					$result['missed_pills'] = "-";
				}
				//Calculate Adherence for Missed Pills
				if ($result['frequency'] == 1) {
					if ($result['missed_pills'] <= 0) {
						$self_reporting = "100%";
					} else if ($result['missed_pills'] < 2 && $result['missed_pills'] > 0) {
						$self_reporting = "≥95%";
					} else if ($result['missed_pills'] >= 2 && $result['missed_pills'] <= 4) {
						$self_reporting = "84-94%";
					} else if ($result['missed_pills'] >= 5) {
						$self_reporting = "<85%";
					}
				} else if ($result['frequency'] == 2) {
					if ($result['missed_pills'] <= 0) {
						$self_reporting = "100%";
					} else if ($result['missed_pills'] <= 3 && $result['missed_pills'] > 0) {
						$self_reporting = "≥95%";
					} else if ($result['missed_pills'] >= 4 && $result['missed_pills'] <= 8) {
						$self_reporting = "84-94%";
					} else if ($result['missed_pills'] >= 9) {
						$self_reporting = "<85%";
					}
				} else {
					$self_reporting = "-";
				}

				//Calculate Adherence for Pill Count
				$pill_count = ($result['pill_count'] - $result['months_of_stock']);
				if ($result['frequency'] == 1) {
					if ($pill_count <= 0) {
						$pill_count_reporting = "100%";
					} else if ($pill_count < 2 && $pill_count > 0) {
						$pill_count_reporting = "≥95%";
					} else if ($pill_count >= 2 && $pill_count <= 4) {
						$pill_count_reporting = "84-94%";
					} else if ($pill_count >= 5) {
						$pill_count_reporting = "<85%";
					}
				} else if ($result['frequency'] == 2) {
					if ($pill_count <= 0) {
						$pill_count_reporting = "100%";
					} else if ($pill_count <= 3 && $pill_count > 0) {
						$pill_count_reporting = "≥95%";
					} else if ($pill_count >= 4 && $pill_count <= 8) {
						$pill_count_reporting = "84-94%";
					} else if ($pill_count >= 9) {
						$pill_count_reporting = "<85%";
					}
				} else {
					$pill_count_reporting = "-";
				}

				if ($result['adherence'] == " ") {
					$result['adherence'] = "-";
				}

				$dyn_table .= "<tbody><tr><td>" . date('d-M-Y', strtotime($result['dispensing_date'])) . "</td><td>" . $result['drug'] . "</td><td align='center'>" . $result['quantity'] . "</td><td align='center'>" . ($result['pill_count'] - $result['months_of_stock']) . "</td><td align='center'>" . $result['missed_pills'] . "</td><td align='center'>" . $pill_count_reporting . "</td><td align='center'>" . $self_reporting . "</td><td align='center'>" . $result['adherence'] . "</td></tr></tbody>";
			}
		}
		echo $dyn_table;
	}

	public function getRegimenChange($patient_no) {
		$dyn_table = "";
		$facility = $this -> session -> userdata("facility");
		$sql = "select dispensing_date, r1.regimen_desc as current_regimen, r2.regimen_desc as previous_regimen, if(rc.name is null,pv.regimen_change_reason,rc.name) as reason from patient_visit pv left join regimen r1 on pv.regimen = r1.id left join regimen r2 on pv.last_regimen = r2.id left join regimen_change_purpose rc on pv.regimen_change_reason = rc.id where pv.patient_id = '$patient_no' and pv.facility = '$facility' and pv.regimen != pv.last_regimen group by dispensing_date,pv.regimen order by pv.dispensing_date desc";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			foreach ($results as $result) {
				if ($result['current_regimen'] == "") {
					$result['current_regimen'] = "-";
				}
				if ($result['previous_regimen'] == "") {
					$result['previous_regimen'] = "-";
				}
				if ($result['reason'] == "") {
					$result['reason'] = "-";
				} elseif ($result['reason'] == "undefined") {
					$result['reason'] = "-";
				} elseif ($result['reason'] == "null") {
					$result['reason'] = "-";
				}
				if ($result['current_regimen'] == "-") {
					$dyn_table .= "<tbody><tr><td>" . date('d-M-Y', strtotime($result['dispensing_date'])) . "</td><td>" . $result['current_regimen'] . "</td><td align='center'>" . $result['previous_regimen'] . "</td><td align='center'>" . $result['reason'] . "</td></tr></tbody>";
				}
			}
		}
		echo $dyn_table;
	}

	public function getAppointmentHistory($patient_no) {
		$dyn_table = "";
		$status = "";
		$facility = $this -> session -> userdata("facility");
		$sql = "SELECT pa.appointment,IF(pa.appointment=pv.dispensing_date,'Visited',DATEDIFF(pa.appointment,curdate()))as Days_To FROM(SELECT patient,appointment FROM patient_appointment pa WHERE patient='$patient_no' AND facility='$facility') as pa,(SELECT patient_id,dispensing_date FROM patient_visit WHERE patient_id='$patient_no' AND facility='$facility') as pv GROUP BY pa.appointment ORDER BY pa.appointment desc";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			foreach ($results as $result) {

				if ($result['Days_To'] > 0) {
					$status = "<td align='center'>" . $result['Days_To'] . " Days To</td>";
				} else if ($result['Days_To'] < 0) {
					$mysql = "select dispensing_date,DATEDIFF(dispensing_date,'" . @$result['appointment'] . "')as days from patient_visit where patient_id='$patient_no' and dispensing_date>'" . @$result['appointment'] . "' and facility='$facility' ORDER BY dispensing_date asc LIMIT 1";
					$myquery = $this -> db -> query($mysql);
					$myresults = $myquery -> result_array();
					$result['dispensing_date'] = date('Y-m-d');
					if ($myresults) {
						$result['dispensing_date'] = $myresults[0]['dispensing_date'];
						$result['Days_To'] = $myresults[0]['days'];
					}
					$result['Days_To'] = str_replace("-", "", $result['Days_To']);
					$status = "<td align='center'> Late By " . $result['Days_To'] . " Days (" . date('d-M-Y', strtotime($result['dispensing_date'])) . ")</td>";
				} else {
					$status = "<td align='center' class='green'>" . $result['Days_To'] . "</td>";
				}

				$dyn_table .= "<tbody><tr><td>" . date('d-M-Y', strtotime($result['appointment'])) . "</td>$status</tr></tbody>";
			}
		}
		echo $dyn_table;
	}

}
?>