<?php
class Dispensement_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this -> load -> database();
	}

	public function index() {
		//$this -> listing();
	}

	public function dispense($patient_no) {
		$data = array();
		$facility_code = $this -> session -> userdata('facility');
		$dispensing_date = "";
		$data['last_regimens'] = "";
		$data['visits'] = "";
		$data['appointments'] = "";
		$dispensing_date = date('Y-m-d');

		$sql = "select * from patient where patient_number_ccc='$patient_no' and facility_code='$facility_code'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$data['results'] = $results;
		}
		$sql = "SELECT r.id, r.regimen_desc,r.regimen_code, dispensing_date, pv.current_weight, pv.current_height FROM patient_visit pv, regimen r WHERE pv.patient_id =  '$patient_no' AND pv.regimen = r.id ORDER BY pv.dispensing_date DESC LIMIT 1";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$data['last_regimens'] = $results[0];
			$dispensing_date = $results[0]['dispensing_date'];
		}

		$sql = "select d.drug,pv.quantity,pv.months_of_stock as mos,pv.drug_id,pv.dispensing_date,ds.value,ds.frequency from patient_visit pv,drugcode d left outer join dose ds on ds.Name=d.dose where pv.patient_id = '$patient_no' and pv.dispensing_date = '$dispensing_date' and pv.drug_id = d.id order by pv.id desc";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$data['prev_visit'] = "";
		if ($results) {
			$data['visits'] = $results;
			$data['prev_visit'] = json_encode($results);
		}

		$sql = "SELECT appointment FROM patient_appointment pa WHERE pa.patient = '$patient_no' AND pa.facility =  '$facility_code' ORDER BY appointment DESC LIMIT 1";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$data['appointments'] = $results[0];
		}

		$data['facility'] = $facility_code;
		$data['user'] = $this -> session -> userdata('full_name');
		$data['regimens'] = Regimen::getRegimens();
		$data['non_adherence_reasons'] = Non_Adherence_Reasons::getAllHydrated();
		$data['regimen_changes'] = Regimen_Change_Purpose::getAllHydrated();
		$data['purposes'] = Visit_Purpose::getAll();
		$data['content_view'] = "dispense_v";
		$data['hide_side_menu'] = 1;
		$this -> base_params($data);
	}
	
	//Get list of drugs for a specific regimen
	public function getDrugsRegimens() {
		$regimen_id = $this -> input -> post('selected_regimen');
		$sql = "SELECT DISTINCT(d.id),d.drug FROM drugcode d LEFT JOIN regimen_drug rd ON d.id=rd.drugcode LEFT JOIN drug_stock_balance dsb ON d.id=dsb.drug_id LEFT JOIN regimen r ON r.id = rd.regimen  WHERE dsb.balance>0 AND dsb.expiry_date>CURDATE() AND (rd.regimen='" . $regimen_id . "' OR r.regimen_code =  'OI') and d.enabled='1' ORDER BY d.drug asc";
		$get_drugs_sql = $this -> db -> query($sql);
		$get_drugs_array = $get_drugs_sql -> result_array();
		echo json_encode($get_drugs_array);

	}

	public function getBrands() {
		$drug_id = $this -> input -> post("selected_drug");
		$get_drugs_sql = $this -> db -> query("SELECT DISTINCT id,brand FROM brand WHERE drug_id='" . $drug_id . "' AND brand!=''");
		$get_drugs_array = $get_drugs_sql -> result_array();
		echo json_encode($get_drugs_array);
	}

	public function getDoses() {
		$get_doses_sql = $this -> db -> query("SELECT id,Name,value,frequency FROM dose");
		$get_doses_array = $get_doses_sql -> result_array();
		echo json_encode($get_doses_array);
	}

	public function getIndications() {
		$get_indication_sql = $this -> db -> query("SELECT id,Name,Indication FROM opportunistic_infection where active='1'");
		$get_indication_array = $get_indication_sql -> result_array();
		echo json_encode($get_indication_array);
	}

	public function edit($record_no) {
		$facility_code = $this -> session -> userdata('facility');
		$sql = "select * from patient_visit pv,patient p where pv.id='$record_no' and pv.patient_id=p.patient_number_ccc and facility='$facility_code'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$data['results'] = $results;
			//Get expriry date the batch
			foreach ($results as $value) {
				$batch_number = $value['batch_number'];
				$drug_ig = $value['drug_id'];
				$expiry_sql = $this -> db -> query("select expiry_date FROM drug_stock_balance WHERE batch_number='$batch_number' AND drug_id='$drug_ig' AND stock_type='2' AND facility_code='$facility_code' AND balance>0 LIMIT 1");
				$expiry_array = $expiry_sql -> result_array();
				$expiry_date = "";
				$data['expiries'] = $expiry_array;
				foreach ($expiry_array as $row) {
					$expiry_date = $row['expiry_date'];
					$data['original_expiry_date'] = $expiry_date;
				}
			}

		} else {
			$data['results'] = "";
		}
		$data['purposes'] = Visit_Purpose::getAll();
		$data['record'] = $record_no;
		$data['regimens'] = Regimen::getRegimens();
		$data['non_adherence_reasons'] = Non_Adherence_Reasons::getAllHydrated();
		$data['regimen_changes'] = Regimen_Change_Purpose::getAllHydrated();
		$data['doses'] = Dose::getAllActive();
		$data['indications'] = Opportunistic_Infection::getAllHydrated();
		$data['content_view'] = 'edit_dispensing_v';
		$data['hide_side_menu'] = 1;
		$this -> base_params($data);
	}

	public function save() {
		$period = date("M-Y");
		$record_no = $this -> session -> userdata('record_no');
		$next_appointment_date = $this -> input -> post("next_appointment_date");
		$last_appointment_date = $this -> input -> post("last_appointment_date");
		$last_appointment_date = date('Y-m-d', strtotime($last_appointment_date));
		$dispensing_date = $this -> input -> post("dispensing_date");
		$dispensing_date_timestamp = date('U', strtotime($dispensing_date));
		$facility = $this -> session -> userdata("facility");
		$patient = $this -> input -> post("patient");
		$height = $this -> input -> post("height");
		$current_regimen = $this -> input -> post("current_regimen");
		$drugs = $this -> input -> post("drug");
		$unit = $this -> input -> post("unit");
		$batch = $this -> input -> post("batch");
		$expiry = $this -> input -> post("expiry");
		$dose = $this -> input -> post("dose");
		$duration = $this -> input -> post("duration");
		$quantity = $this -> input -> post("qty_disp");
		$qty_available = $this -> input -> post("soh");
		$brand = $this -> input -> post("brand");
		$soh = $this -> input -> post("soh");
		$indication = $this -> input -> post("indication");
		$mos = $this -> input -> post("next_pill_count");
		$pill_count = $this -> input -> post("pill_count");
		$comment = $this -> input -> post("comment");
		$missed_pill = $this -> input -> post("missed_pills");
		$purpose = $this -> input -> post("purpose");
		$weight = $this -> input -> post("weight");
		$regimen_change_reason = $this -> input -> post("regimen_change_reason");
		$non_adherence_reasons = $this -> input -> post("non_adherence_reasons");
		$timestamp = date('U');
		$period = date("Y-m-01");
		$user = $this -> session -> userdata("username");
		$adherence = $this -> input -> post("adherence");
		//Get transaction type
		$transaction_type = transaction_type::getTransactionType("dispense", "0");
		$transaction_type = $transaction_type -> id;
		/*
		 * Update Appointment Info
		 */
		if ($last_appointment_date) {
			if ($last_appointment_date > $dispensing_date) {
				$sql = "update patient_appointment set appointment='$next_appointment_date',machine_code='1' where patient='$patient' and appointment='$last_appointment_date';";
			} else {
				$sql = "insert into patient_appointment (patient,appointment,facility) values ('$patient','$next_appointment_date','$facility');";
			}
		} else {
			$sql = "insert into patient_appointment (patient,appointment,facility) values ('$patient','$next_appointment_date','$facility');";
		}

		/*
		 * Update patient Info
		 */

		$sql .= "update patient SET height='$height',current_regimen='$current_regimen',nextappointment='$next_appointment_date' where patient_number_ccc ='$patient' and facility_code='$facility';";

		/*
		 * Update Visit and Drug Info
		 */

		for ($i = 0; $i < sizeof($drugs); $i++) {
			$remaining_balance = $soh[$i] - $quantity[$i];
			if ($pill_count[$i] == '') {
				$pill_count[$i] = $mos[$i];
			}
			$sql .= "insert into patient_visit (patient_id, visit_purpose, current_height, current_weight, regimen, regimen_change_reason, drug_id, batch_number, brand, indication, pill_count, comment, `timestamp`, user, facility, dose, dispensing_date, dispensing_date_timestamp,quantity,duration,adherence,missed_pills,non_adherence_reason,months_of_stock) VALUES ('$patient','$purpose', '$height', '$weight', '$current_regimen', '$regimen_change_reason', '$drugs[$i]', '$batch[$i]', '$brand[$i]', '$indication[$i]', '$pill_count[$i]','$comment[$i]', '$timestamp', '$user','$facility', '$dose[$i]','$dispensing_date', '$dispensing_date_timestamp','$quantity[$i]','$duration[$i]','$adherence','$missed_pill[$i]','$non_adherence_reasons','$mos[$i]');";
			$sql .= "insert into drug_stock_movement (drug, transaction_date, batch_number, transaction_type,source,destination,expiry_date,quantity, quantity_out,balance, facility,`timestamp`) VALUES ('$drugs[$i]','$dispensing_date','$batch[$i]','$transaction_type','$facility','$facility','$expiry[$i]',0,'$quantity[$i]',$remaining_balance,'$facility','$dispensing_date_timestamp');";
			$sql .= "update drug_stock_balance SET balance=balance - '$quantity[$i]' WHERE drug_id='$drugs[$i]' AND batch_number='$batch[$i]' AND expiry_date='$expiry[$i]' AND stock_type='2' AND facility_code='$facility';";
			$sql .= "INSERT INTO drug_cons_balance(drug_id,stock_type,period,facility,amount) VALUES('$drugs[$i]','2','$period','$facility','$quantity[$i]') ON DUPLICATE KEY UPDATE amount=amount+'$quantity[$i]';";

		}
		$queries = explode(";", $sql);
		$count = count($queries);
		$c = 0;
		foreach ($queries as $query) {
			$c++;
			if (strlen($query) > 0) {
				$this -> db -> query($query);
			}

		}
		$this -> session -> set_userdata('msg_save_transaction', 'success');
		$this -> session -> set_userdata('dispense_updated', 'Drugs dispensed to Patient No:' . $patient);
		redirect("patient_management/viewDetails/$record_no");
	}

	public function save_edit() {
		$timestamp = "";
		$patient = "";
		$facility = "";
		$user = "";
		$record_no = "";
		$soh = $this -> input -> post("soh");
		//Get transaction type
		$transaction_type = transaction_type::getTransactionType("dispense", "0");
		$transaction_type = $transaction_type -> id;
		$transaction_type1 = transaction_type::getTransactionType("returns", "1");
		$transaction_type1 = $transaction_type1 -> id;
		$original_qty = @$_POST["qty_hidden"];
		$facility = $this -> session -> userdata("facility");
		$user = $this -> session -> userdata("full_name");
		$timestamp = date('Y-m-d H:i:s');
		$patient = @$_POST['patient'];
		$expiry_date = @$_POST['expiry'];
		//If record is to be deleted
		if (@$_POST['delete_trigger'] == 1) {
			$sql = "update patient_visit set active='0' WHERE id='" . @$_POST["dispensing_id"] . "';";
			$this -> db -> query($sql);
			$bal = $soh + @$_POST["qty_disp"];
			
			//Update drug_stock_balance
			$sql = "UPDATE drug_stock_balance SET balance=balance+" . @$_POST["qty_disp"] . " WHERE drug_id='" . @$_POST["original_drug"] . "' AND batch_number='" . @$_POST["batch"] . "' AND expiry_date='" . @$_POST["original_expiry_date"] . "' AND stock_type='2' AND facility_code='$facility'";
			$this -> db -> query($sql);
			
			//Insert in drug stock movement
			//Get balance after update
			$sql="SELECT balance FROM drug_stock_balance WHERE drug_id='" . @$_POST["original_drug"] . "' AND batch_number='" . @$_POST["batch"] . "' AND expiry_date='" . @$_POST["original_expiry_date"] . "' AND stock_type='2' AND facility_code='$facility'";
			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			$actual_balance=$results[0]['balance'];
			$sql = "INSERT INTO drug_stock_movement (drug, transaction_date, batch_number, transaction_type,source,destination,expiry_date, quantity, balance, facility, machine_code,timestamp) SELECT '" . @$_POST["original_drug"] . "','" . @$_POST["original_dispensing_date"] . "', '" . @$_POST["batch"] . "','$transaction_type1','$facility','$facility','$expiry_date','" . @$_POST["qty_disp"] . "','" . @$actual_balance . "','$facility','0','$timestamp' from drug_stock_movement WHERE batch_number= '" . @$_POST["batch"] . "' AND drug='" . @$_POST["original_drug"] . "' LIMIT 1;";
			$this -> db -> query($sql);
			
			//Update drug consumption
			$period=date('Y-m-01');
			$sql="UPDATE drug_cons_balance SET amount=amount-".$original_qty." WHERE drug_id='" . @$_POST["original_drug"] . "' AND stock_type='2' AND period='$period' AND facility='$facility'";
			$this -> db -> query($sql);
			
			$this -> session -> set_userdata('dispense_deleted', 'success');
		} 
		else {//If record is edited 
			
			$period=date('Y-m-01');
			$sql = "UPDATE patient_visit SET dispensing_date = '" . @$_POST["dispensing_date"] . "', visit_purpose = '" . @$_POST["purpose"] . "', current_weight='" . @$_POST["weight"] . "', current_height='" . @$_POST["height"] . "', regimen='" . @$_POST["current_regimen"] . "', drug_id='" . @$_POST["drug"] . "', batch_number='" . @$_POST["batch"] . "', dose='" . @$_POST["dose"] . "', duration='" . @$_POST["duration"] . "', quantity='" . @$_POST["qty_disp"] . "', brand='" . @$_POST["brand"] . "', indication='" . @$_POST["indication"] . "', pill_count='" . @$_POST["pill_count"] . "', missed_pills='" . @$_POST["missed_pills"] . "', comment='" . @$_POST["comment"] . "',non_adherence_reason='" . @$_POST["non_adherence_reasons"] . "',adherence='" . @$_POST["adherence"] . "' WHERE id='" . @$_POST["dispensing_id"] . "';";
			$this -> db -> query($sql);
			if (@$_POST["batch"] != @$_POST["batch_hidden"] || @$_POST["qty_disp"] != @$_POST["qty_hidden"]) {
				//Update drug_stock_balance
				//Balance=balance+(previous_qty_disp-actual_qty_dispense)
				$bal = $soh;
				//New qty dispensed=old qty - actual qty dispensed
				$new_qty_dispensed = $_POST["qty_hidden"] - $_POST["qty_disp"];
				//If new quantity dispensed is less than qty previously dispensed
				if ($new_qty_dispensed > 0) {
					$bal = $soh + $new_qty_dispensed;
					$sql = "UPDATE drug_stock_balance SET balance=balance+" . @$new_qty_dispensed . " WHERE drug_id='" . @$_POST["original_drug"] . "' AND batch_number='" . @$_POST["batch"] . "' AND expiry_date='" . @$_POST["original_expiry_date"] . "' AND stock_type='2' AND facility_code='$facility'";
					$this -> db -> query($sql);
					
					//Update drug consumption
					$sql="UPDATE drug_cons_balance SET amount=amount-".$new_qty_dispensed." WHERE drug_id='" . @$_POST["original_drug"] . "' AND stock_type='2' AND period='$period' AND facility='$facility'";
					$this -> db -> query($sql);
					
				} else if ($new_qty_dispensed < 0) {
					$bal = $soh - $new_qty_dispensed;
					$new_qty_dispensed = abs($new_qty_dispensed);
					$sql = "UPDATE drug_stock_balance SET balance=balance-" . @$new_qty_dispensed . " WHERE drug_id='" . @$_POST["original_drug"] . "' AND batch_number='" . @$_POST["batch"] . "' AND expiry_date='" . @$_POST["original_expiry_date"] . "' AND stock_type='2' AND facility_code='$facility'";
					$this -> db -> query($sql);
					
					//Update drug consumption
					$sql="UPDATE drug_cons_balance SET amount=amount+".$new_qty_dispensed." WHERE drug_id='" . @$_POST["original_drug"] . "' AND stock_type='2' AND period='$period' AND facility='$facility'";
					$this -> db -> query($sql);
				}
				//Balance after returns
				$bal1 = $soh + $original_qty;
				//Returns transaction
				$sql = "INSERT INTO drug_stock_movement (drug, transaction_date, batch_number, transaction_type,source,destination,expiry_date, quantity,balance, facility, machine_code,timestamp) SELECT '" . @$_POST["original_drug"] . "','" . @$_POST["original_dispensing_date"] . "', '" . @$_POST["batch_hidden"] . "','$transaction_type1','$facility','$facility',expiry_date,'" . @$_POST["qty_hidden"] . "','$bal1','$facility','0','$timestamp' from drug_stock_movement WHERE batch_number= '" . @$_POST["batch_hidden"] . "' AND drug='" . @$_POST["original_drug"] . "' LIMIT 1;";
				$this -> db -> query($sql);
				//Dispense transaction
				$sql = "INSERT INTO drug_stock_movement (drug, transaction_date, batch_number, transaction_type,source,destination,expiry_date, quantity_out,balance, facility, machine_code,timestamp) SELECT '" . @$_POST["drug"] . "','" . @$_POST["original_dispensing_date"] . "', '" . @$_POST["batch"] . "','$transaction_type','$facility','$facility',expiry_date,'" . @$_POST["qty_disp"] . "','$bal','$facility','0','$timestamp' from drug_stock_movement WHERE batch_number= '" . @$_POST["batch"] . "' AND drug='" . @$_POST["drug"] . "' LIMIT 1;";
				$this -> db -> query($sql);

			}
			$this -> session -> set_userdata('dispense_updated', 'success');
		}
		$sql = "select * from patient where patient_number_ccc='$patient' and facility_code='$facility'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$record_no = $results[0]['id'];
		$this -> session -> set_userdata('msg_save_transaction', 'success');
		redirect("patient_management/viewDetails/$record_no");
	}

	public function base_params($data) {
		$data['title'] = "webADT | Drug Dispensements";
		$data['banner_text'] = "Facility Dispensements";
		$data['link'] = "dispensements";
		$this -> load -> view('template', $data);
	}

}
?>