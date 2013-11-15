<?php
error_reporting(0);
class auto_management extends MY_Controller {
	function __construct() {
		parent::__construct();
		ini_set("max_execution_time", "100000");
		$this -> load -> library('PHPExcel');
		$this -> load -> helper('url');
		date_default_timezone_set('Africa/Nairobi');
	}

	public function index() {
		$message = 0;
		$today = (int)date('Ymd');
		$stmt1 = "select last_index from migration_log where source='auto_update'";
		$stmt2 = "update migration_log set last_index='$today' where source='auto_update'";
		$q = $this -> db -> query($stmt1);
		$rs = $q -> result_array();
		$last_index = (int)$rs[0]['last_index'];
		if ($today != $last_index) {
			$message = $this -> auto_update();
			$message .= $this -> auto_sms($this -> session -> userdata('facility_name'));
			$this -> db -> query($stmt2);
		}
		echo $message;
	}

	public function auto_sms($facility_name = "Liverpool VCT") {

		/* Find out if today is on a weekend */
		$weekDay = date('w');
		if ($weekDay == 6) {
			$tommorrow = date('Y-m-d', strtotime('+2 day'));
		} else {
			$tommorrow = date('Y-m-d', strtotime('+1 day'));
		}

		$phone_minlength = '8';
		$phone = "";
		$phone_list = "";
		$first_part = "";
		$kenyacode = "254";
		$arrDelimiters = array("/", ",", "+");

		$message = "You have an Appointment on " . date('l dS-M-Y', strtotime($tommorrow)) . " at $facility_name";

		/*Get All Patient Who Consented Yes That have an appointment Tommorow */
		$sql = "SELECT p.phone,p.patient_number_ccc,p.nextappointment,temp.patient,temp.appointment,temp.machine_code as status,temp.id
					FROM patient p
					LEFT JOIN 
					(SELECT pa.id,pa.patient, pa.appointment, pa.machine_code
					FROM patient_appointment pa
					WHERE pa.appointment =  '$tommorrow'
					GROUP BY pa.patient) as temp ON temp.patient=p.patient_number_ccc
					WHERE p.sms_consent =  '1'
					AND p.nextappointment =temp.appointment
					AND char_length(p.phone)>$phone_minlength
					AND temp.machine_code !='s'
					GROUP BY p.patient_number_ccc";

		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$alert = "Patients notified (<b>" . $this -> db -> affected_rows() . "</b>)";

		if ($results) {
			foreach ($results as $result) {
				$phone = $result['phone'];
				$newphone = substr($phone, -$phone_minlength);
				$first_part = str_replace($newphone, "", $phone);

				if (strlen($first_part) < 7) {
					if ($first_part === '07') {
						$phone = "+" . $kenyacode . substr($phone, 1);
						$phone_list .= $phone;
					} else if ($first_part == '7') {
						$phone = "0" . $phone;
						$phone = "+" . $kenyacode . substr($phone, 1);
						$phone_list .= $phone;
					} else if ($first_part == '+' . $kenyacode . '07') {
						$phone = str_replace($kenyacode . '07', $kenyacode . '7', $phone);
						$phone_list .= $phone;
					}
				} else {
					/*If Phone Does not meet requirements*/

					$phone = str_replace($arrDelimiters, "-|-", $phone);
					$phones = explode("-|-", $phone);

					foreach ($phones as $phone) {
						$newphone = substr($phone, -$phone_minlength);
						$first_part = str_replace($newphone, "", $phone);
						if (strlen($first_part) < 7) {
							if ($first_part === '07') {
								$phone = "+" . $kenyacode . substr($phone, 1);
								$phone_list .= $phone;
								break;
							} else if ($first_part == '7') {
								$phone = "0" . $phone;
								$phone = "+" . $kenyacode . substr($phone, 1);
								$phone_list .= $phone;
								break;
							} else if ($first_part == '+' . $kenyacode . '07') {
								$phone = str_replace($kenyacode . '07', $kenyacode . '7', $phone);
								$phone_list .= $phone;
								break;
							}
						}
					}
				}
				$stmt = "update patient_appointment set machine_code='s' where id='" . $result['id'] . "'";
				$q = $this -> db -> query($stmt);
			}
			$phone_list = substr($phone_list, 1);
		}
		$phone_list = explode("+", $phone_list);
		$message = urlencode($message);
		foreach ($phone_list as $phone) {
			file("http://41.57.109.242:13000/cgi-bin/sendsms?username=clinton&password=ch41sms&to=$phone&text=$message");
		}
		return $alert;
	}

	public function auto_update() {

		$days_to_lost_followup = 90;
		$days_to_pep_end = 30;
		$days_in_year = date("z", mktime(0, 0, 0, 12, 31, date('Y'))) + 1;
		$adult_age = 12;
		$active = 'active';
		$lost = 'lost';
		$pep = 'pep';
		$pmtct = 'pmtct';
		$two_year_days = $days_in_year * 2;
		$adult_days = $days_in_year * $adult_age;
		$message = "";

		//Get Patient Status id's
		$status_array = array($active, $lost, $pep, $pmtct);
		foreach ($status_array as $status) {
			$s = "SELECT id,name FROM patient_status ps WHERE ps.name LIKE '%$status%'";
			$q = $this -> db -> query($s);
			$rs = $q -> result_array();
			$state[$status] = $rs[0]['id'];
		}

		/*Change Last Appointment to Next Appointment*/
		$sql['Change Last Appointment to Next Appointment'] = "(SELECT patient_number_ccc,nextappointment,temp.appointment,temp.patient
					FROM patient p
					LEFT JOIN 
					(SELECT MAX(pa.appointment)as appointment,pa.patient
					FROM patient_appointment pa
					GROUP BY pa.patient) as temp ON p.patient_number_ccc =temp.patient
					WHERE p.nextappointment !=temp.patient
					AND DATEDIFF(temp.appointment,p.nextappointment)>0
					GROUP BY p.patient_number_ccc) as p1
					SET p.nextappointment=p1.appointment";

		/*Change Active to Lost_to_follow_up*/
		$sql['Change Active to Lost_to_follow_up'] = "(SELECT patient_number_ccc,nextappointment,DATEDIFF(CURDATE(),nextappointment) as days
				   FROM patient p
				   LEFT JOIN patient_status ps ON ps.id=p.current_status
				   WHERE ps.Name LIKE '%$active%'
				   AND (DATEDIFF(CURDATE(),nextappointment )) >=$days_to_lost_followup) as p1
				   SET p.current_status = '$state[$lost]'";

		/*Change Lost_to_follow_up to Active */
		$sql['Change Lost_to_follow_up to Active'] = "(SELECT patient_number_ccc,nextappointment,DATEDIFF(CURDATE(),nextappointment) as days
				   FROM patient p
				   LEFT JOIN patient_status ps ON ps.id=p.current_status
				   WHERE ps.Name LIKE '%$lost%'
				   AND (DATEDIFF(CURDATE(),nextappointment )) <$days_to_lost_followup) as p1
				   SET p.current_status = '$state[$active]' ";

		/*Change Active to PEP End*/
		$sql['Change Active to PEP End'] = "(SELECT patient_number_ccc,rst.name as Service,ps.Name as Status,DATEDIFF(CURDATE(),date_enrolled) as days_enrolled
				   FROM patient p
				   LEFT JOIN regimen_service_type rst ON rst.id=p.service
				   LEFT JOIN patient_status ps ON ps.id=p.current_status
				   WHERE (DATEDIFF(CURDATE(),date_enrolled))>=$days_to_pep_end 
				   AND rst.name LIKE '%$pep%' 
				   AND ps.Name NOT LIKE '%$pep%') as p1
				   SET p.current_status = '$state[$pep]' ";

		/*Change PEP End to Active*/
		$sql['Change PEP End to Active'] = "(SELECT patient_number_ccc,rst.name as Service,ps.Name as Status,DATEDIFF(CURDATE(),date_enrolled) as days_enrolled
				   FROM patient p
				   LEFT JOIN regimen_service_type rst ON rst.id=p.service
				   LEFT JOIN patient_status ps ON ps.id=p.current_status
				   WHERE (DATEDIFF(CURDATE(),date_enrolled))<$days_to_pep_end 
				   AND rst.name LIKE '%$pep%' 
				   AND ps.Name NOT LIKE '%$active%') as p1
				   SET p.current_status = '$state[$active]' ";

		/*Change Active to PMTCT End(children)*/
		$sql['Change Active to PMTCT End(children)'] = "(SELECT patient_number_ccc,rst.name AS Service,ps.Name AS Status,DATEDIFF(CURDATE(),dob) AS days
				   FROM patient p
				   LEFT JOIN regimen_service_type rst ON rst.id = p.service
				   LEFT JOIN patient_status ps ON ps.id = p.current_status
				   WHERE (DATEDIFF(CURDATE(),dob )) >=$two_year_days
				   AND (DATEDIFF(CURDATE(),dob)) <$adult_days
				   AND rst.name LIKE  '%$pmtct%'
				   AND ps.Name NOT LIKE  '%$pmtct%') as p1
				   SET p.current_status = '$state[$pmtct]'";

		/*Change PMTCT End to Active(Adults)*/
		$sql['Change PMTCT End to Active(Adults)'] = "(SELECT patient_number_ccc,rst.name AS Service,ps.Name AS Status,DATEDIFF(CURDATE(),dob) AS days
				   FROM patient p
				   LEFT JOIN regimen_service_type rst ON rst.id = p.service
				   LEFT JOIN patient_status ps ON ps.id = p.current_status 
				   WHERE (DATEDIFF(CURDATE(),dob)) >=$two_year_days 
				   AND (DATEDIFF(CURDATE(),dob)) >=$adult_days 
				   AND rst.name LIKE '%$pmtct%'
				   AND ps.Name LIKE '%$pmtct%') as p1
				   SET p.current_status = '$state[$active]'";

		foreach ($sql as $i => $q) {
			$stmt1 = "UPDATE patient p,";
			$stmt2 = " WHERE p.patient_number_ccc=p1.patient_number_ccc;";
			$stmt1 .= $q;
			$stmt1 .= $stmt2;
			$q = $this -> db -> query($stmt1);
			if ($this -> db -> affected_rows() > 0) {
				$message .= $i . "(<b>" . $this -> db -> affected_rows() . "</b>) rows affected<br/>";
			}
		}
		return $message;
	}

	public function base_params($data) {
		$data['title'] = "webADT | Errors";
		$data['banner_text'] = "System Errors";
		$data['link'] = "patients";
		$this -> load -> view('template', $data);
	}

	public function error_generator() {
		$array_text = '';
		$array_text = $this -> input -> post("array_text", true);
		$error_list = $this -> error_correction();
		$id_list = "";
		$access_level = $this -> session -> userdata('user_indicator');

		foreach ($error_list[$array_text] as $error_array) {
			$id_list .= "'" . $error_array['id'] . "',";

		}
		$id_list = substr($id_list, 0, -1);

		$stmt = "SELECT p.id,p.patient_number_ccc,p.first_name,p.other_name,p.last_name,p.phone,p.date_enrolled,p.nextappointment,r.regimen_desc,ps.Name,ps.Active
		         FROM patient p 
		         LEFT JOIN regimen r ON r.id=p.current_regimen
		         LEFT JOIN patient_status ps ON ps.id=p.current_status
		         WHERE p.id IN($id_list)
		         AND p.active='1'
		         GROUP BY p.patient_number_ccc";
		$q = $this -> db -> query($stmt);
		$rs = $q -> result_array();

		$dyn_table = '<table class="dataTables" id="patient_listing" border="1" >';
		$dyn_table .= '<thead><tr><th style="width:60px">CCC No</th><th>Patient Name</th><th>Contact</th><th style="width: 100px">Date Enrolled</th><th style="width: 100px">Next Appointment</th><th>Current Regimen</th><th style="width:150px">Status</th><th style="width:20%">Action</th></tr></thead><tbody>';
		foreach ($rs as $r) {
			$patient_name = strtoupper(trim($r['first_name'] . " " . $r['other_name'] . " " . $r['last_name']));
			$id = $r['id'];
			$link = "";
			$link = '<a href="' . base_url() . 'patient_management/viewDetails/' . $id . '">Detail</a> | <a href="' . base_url() . 'patient_management/edit/' . $id . '">Edit</a> ' . $link;
			if ($access_level == "facility_administrator") {
				if ($r['Active'] == 1) {
					$link .= '| <a href="' . base_url() . 'patient_management/disable/' . $id . '" class="red">Disable</a>';

				} else {
					$link .= '| <a href="' . base_url() . 'patient_management/enable/' . $id . '" class="green">Enable</a>';
				}
			}
			$appointment = "";
			$date_enrolled = "";
			$appointment = $r['nextappointment'];
			if ($appointment) {
				$appointment = date('d-M-Y', strtotime($r['nextappointment']));
			}
			$date_enrolled = $r['date_enrolled'];
			if ($date_enrolled) {
				$date_enrolled = date('d-M-Y', strtotime($r['date_enrolled']));
			}

			$dyn_table .= "<tr><td>" . strtoupper($r['patient_number_ccc']) . "</td><td>" . $patient_name . "</td><td>" . $r['phone'] . "</td><td>" . $date_enrolled . "</td><td>" . $appointment . "</td><td><b>" . strtoupper($r['regimen_desc']) . "</b></td><td><b>" . $r['Name'] . "</b></td><td>" . $link . "</td></tr>";
		}
		$dyn_table .= "</tbody></table>";
		echo $dyn_table;
	}

	public function error_fix() {
		$data['errors'] = $this -> error_correction();

		foreach ($data['errors'] as $error => $error_array) {
              $data['first_error']=$error;
			  break;
		}
		$data['content_view'] = "error_listing_v";
		$this -> base_params($data);
	}

	public function error_correction() {
		$overall_total = 0;
		$error_array = array();

		/*Patients without Gender*/
		$sql['Patients without Gender'] = "SELECT p.patient_number_ccc,p.gender,p.id
										   FROM patient p 
										   LEFT JOIN gender g on g.id=p.gender
										   WHERE (p.gender=' ' 
										   OR p.gender='' 
										   OR p.gender='null' 
										   OR p.gender is null)
										   AND p.active='1'
										   GROUP BY p.patient_number_ccc;";

		/*Patients without DOB*/
		$sql['Patients without DOB'] = "SELECT p.patient_number_ccc,p.dob,p.id
										FROM patient p 
										WHERE (p.dob=' ' 
										OR p.dob='' 
										OR p.dob='null' 
										OR p.dob is null)
										AND p.active='1'
										GROUP BY p.patient_number_ccc;";

		/*Patients without Appointment*/
		$sql['Patients without Appointment'] = "SELECT p.patient_number_ccc, p.nextappointment, ps.Name AS current_status,p.id
											   FROM patient p
											   LEFT JOIN patient_status ps ON ps.id = p.current_status
											   WHERE(p.nextappointment = ' '
											   OR p.nextappointment =  ''
											   OR p.nextappointment =  'null'
											   OR p.nextappointment IS NULL)
											   AND p.active = '1'
											   AND ps.Name LIKE '%active%'
											   AND p.active='1'
											   GROUP BY p.patient_number_ccc;";
		/*Patients without Current Regimen*/
		$sql['Patients without Current Regimen'] = "SELECT p.patient_number_ccc,p.current_regimen,CONCAT_WS(' | ',r.regimen_code,r.regimen_desc) as regimen,p.id
												FROM patient p 
												LEFT JOIN regimen r ON r.id=p.current_regimen
												WHERE (p.current_regimen=' '
												OR p.current_regimen=''
												OR p.current_regimen is null
												OR p.current_regimen='null')
												AND p.active='1'
												GROUP BY p.patient_number_ccc;";
		/*Patients without Start Regimen*/
		$sql['Patients without Start Regimen'] = "SELECT p.patient_number_ccc, p.start_regimen, CONCAT_WS(  ' | ', r.regimen_code, r.regimen_desc ) AS regimen,p.id
												FROM patient p
												LEFT JOIN regimen r ON r.id = p.start_regimen
												WHERE (p.start_regimen =  ' '
												OR p.start_regimen =  ''
												OR p.start_regimen IS NULL 
												OR p.start_regimen =  'null')
												AND p.active='1'
												GROUP BY p.patient_number_ccc;";
		/*Patients without Current Status*/
		$sql['Patients without Current Status'] = "SELECT p.patient_number_ccc,p.current_status,ps.Name as status,p.id
												FROM patient p
												LEFT JOIN patient_status ps ON ps.id=p.current_status
												WHERE(p.current_status=' '
												OR p.current_status=''
												OR p.current_status is null
												OR p.current_status='null')
												AND p.active='1'
												GROUP BY p.patient_number_ccc;";

		/*Patients without Service Line*/
		$sql['Patients without Current Status'] = "SELECT p.patient_number_ccc,p.service,rst.name as status,p.id
												FROM patient p
												LEFT JOIN regimen_service_type rst ON rst.id=p.service
												WHERE(p.service=' '
												OR p.service=''
												OR p.service is null
												OR p.service='null')
												AND p.active='1'
												GROUP BY p.patient_number_ccc;";

		/*Duplicate Patient Numbers*/
		$sql['Duplicate Patient Numbers'] = "SELECT p.patient_number_ccc,count(p.patient_number_ccc) as total,p.id
											FROM patient p
											WHERE p.active='1'
											GROUP by p.patient_number_ccc
											HAVING(total >1);";

		/*Patients without Enrollment date*/
		$sql['Patients without Enrollment date'] = "SELECT p.patient_number_ccc,p.id,p.date_enrolled
												FROM patient p
												WHERE char_length(p.date_enrolled)<10
												AND p.active='1'
												GROUP BY p.patient_number_ccc;";

		/*Patients without Status Change date*/
		$sql['Patients without Status Change date'] = "SELECT p.patient_number_ccc,p.id,p.status_change_date
												FROM patient p
												WHERE char_length(p.status_change_date)<10
												AND p.active='1'
												GROUP BY p.patient_number_ccc;";

		/*Patients without Start Regimen date*/
		$sql['Patients without Start Regimen date'] = "SELECT p.patient_number_ccc,p.id,p.start_regimen_date
												FROM patient p
												WHERE char_length(p.start_regimen_date)<10
												AND p.active='1'
												GROUP BY p.patient_number_ccc;";

		/*Patients With Incorrect Current Regimens*/
		$sql['Patients with Incorrect Current Regimens'] = "SELECT p.id,p.patient_number_ccc, p.first_name, p.last_name, p.service, p.current_regimen, r.regimen_desc, rst1.Name AS FIRST,rst2.Name AS SECOND 
														FROM patient p
														LEFT JOIN regimen r ON r.id = p.current_regimen
														LEFT JOIN regimen_service_type rst1 ON rst1.id = p.service
														LEFT JOIN regimen_service_type rst2 ON rst2.id = r.type_of_service
														WHERE rst1.id != rst2.id
														GROUP BY p.patient_number_ccc;";

		foreach ($sql as $i => $q) {
			$q = $this -> db -> query($q);
			if ($this -> db -> affected_rows() > 0) {
				$overall_total += $this -> db -> affected_rows();
				$rs = $q -> result_array();
				$error_array[$i . "(" . $this -> db -> affected_rows() . ")"] = $rs;
			}
		}
		return $error_array;
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
AND p.active='1'
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

		//if (ob_get_contents())
		//ob_end_clean();
		ob_start();
		$facility_name = Facilities::getFacilityName($facility_code);
		$facility_name .= "(" . date('d-M-Y h:i:s a') . ")";
		$filename = "Patient List From " . $facility_name . ".csv";
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename=' . $filename);

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
		//ob_end_clean();
		$objWriter -> save('php://output');

		$objPHPExcel -> disconnectWorksheets();
		unset($objPHPExcel);
	}

	public function password_notification($user_id) {

		$days_before_pwdchange = 30;
		$notification_start = 10;

		$stmt = "SELECT $days_before_pwdchange-DATEDIFF(CURDATE(),u.Time_Created) as days_to_go
		         FROM users u
		         WHERE id='$user_id'";
		$q = $this -> db -> query($stmt);
		$rs = $q -> result_array();
		$days_before_pwdchange = $rs[0]['days_to_go'];
		if ($days_before_pwdchange > $notification_start) {
			$days_before_pwdchange = "";
		} else {
			echo "<a><i class='icon-th'></i>Days to Password expiry <div class='badge badge-important'>" . $days_before_pwdchange . "</div></a>";
		}

	}

}
?>	