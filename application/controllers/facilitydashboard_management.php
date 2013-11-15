<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Facilitydashboard_Management extends MY_Controller {

	var $drug_array = array();
	var $drug_count = 0;
	var $counter = 0;

	function __construct() {
		parent::__construct();
		$this -> load -> database();
	}

	public function error_correction() {
		$overall_total = 0;
		$error_array = array();
		$temp = "";

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
			}
		}
		if ($overall_total > 0) {
			$temp_link = $order_link = site_url('auto_management/error_fix');
			$temp = "<li><a href='" . $temp_link . "'><i class='icon-th'></i>Errors <div class='badge badge-important'>" . $overall_total . "</div></a><li>";
		}

		return $temp;
	}

	public function password_notification($user_id) {

		$days_before_pwdchange = 30;
		$notification_start = 10;
		$temp = "";

		$stmt = "SELECT $days_before_pwdchange-DATEDIFF(CURDATE(),u.Time_Created) as days_to_go
		         FROM users u
		         WHERE id='$user_id'";
		$q = $this -> db -> query($stmt);
		$rs = $q -> result_array();
		$days_before_pwdchange = $rs[0]['days_to_go'];
		if ($days_before_pwdchange > $notification_start) {
			$days_before_pwdchange = "";
			$temp = $days_before_pwdchange;
		} else {
			$temp = "<li><a><i class='icon-th'></i>Days to Password expiry <div class='badge badge-important'>" . $days_before_pwdchange . "</div></a><li>";
		}
		return $temp;
	}

	public function order_notification() {
		$facility_code = $this -> session -> userdata("facility");
		$sql = "SELECT status ,COUNT(*) AS total FROM `facility_order` WHERE facility_id ='$facility_code' AND code='1' GROUP BY STATUS";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$status = "";
		$status_total = 0;
		$total = 0;
		$order_link = "";
		$dyn_table = "";
		if ($results) {
			foreach ($results as $result) {
				if ($result['status'] != '') {
					if ($result['status'] == 0) {
						$status = "Pending Orders";
						$order_link = site_url('order_management/submitted_orders/0');
					} else if ($result['status'] == 1) {
						$status = "Approved Orders";
						$order_link = site_url('order_management/submitted_orders/1');
					} else if ($result['status'] == 2) {
						$status = "Declined Orders";
						$order_link = site_url('order_management/submitted_orders/2');
					} else if ($result['status'] == 3) {
						$status = "Dispatched Orders";
						$order_link = site_url('order_management/submitted_orders/3');
					}
					$status_total = $result['total'];
					$total += $status_total;
					$dyn_table .= "<li><a id='inactive_users' href='$order_link'><i class='icon-th'></i>$status <div class='badge badge-important'>$status_total</div></a></li>";
				}
			}
		} else {
			$dyn_table .= "<li>No Data Available</li>";
		}
		$access_level = $this -> session -> userdata('user_indicator');
		if ($access_level == "facility_administrator") {
			$dyn_table .= $this -> inactive_users();
		}
		$dyn_table .= $this -> error_correction();
		$dyn_table .= $this -> password_notification($this -> session -> userdata("user_id"));
		echo $dyn_table;
	}

	public function inactive_users() {
		$facility_code = $this -> session -> userdata("facility");
		$sql = "select count(*) as total from users where Facility_Code='$facility_code' and Active='0' and access_level='2'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$total = 0;
		$temp = "";
		$order_link = site_url('settings_management');
		if ($results) {
			foreach ($results as $result) {
				$total = $result['total'];
			}
		}
		$temp = "<li class='divider'></li><li><a href='$order_link'><i class='icon-th'></i>Deactivated Users <div class='badge badge-important'>$total</div></a></li>";
		return $temp;
	}

	public function stock_notification($stock_type = "2") {
		$facility_code = $this -> session -> userdata("facility");
		//Main Store
		if ($stock_type == '1') {
			$stock_param = "AND source !=destination";
		}
		//Pharmacy
		else if ($stock_type == '2') {
			$stock_param = "AND source =destination";
		}
		$sql = "SELECT d.drug as drug_name,du.Name as drug_unit,temp1.qty as stock_level,temp2.minimum_consumption FROM (SELECT drug_id, SUM( balance ) AS qty FROM drug_stock_balance WHERE expiry_date > CURDATE() AND stock_type =  '$stock_type' AND balance >=0 GROUP BY drug_id) as temp1 LEFT JOIN (SELECT drug, SUM( quantity_out ) AS total_consumption, SUM( quantity_out ) * 0.5 AS minimum_consumption FROM drug_stock_movement WHERE DATEDIFF( CURDATE() , transaction_date ) <=90 $stock_param GROUP BY drug) as temp2 ON temp1.drug_id=temp2.drug LEFT JOIN drugcode d ON d.id=temp1.drug_id LEFT JOIN drug_unit du ON du.id=d.unit WHERE temp1.qty<temp2.minimum_consumption";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$tmpl = array('table_open' => '<table id="stock_level" class="table table-striped table-condensed">');
		$this -> table -> set_template($tmpl);
		$this -> table -> set_heading('No', 'Drug', 'Unit', 'Qty (Units)', 'Threshold Qty (Units)', 'Priority');
		$x = 1;
		foreach ($results as $drugs) {
			if ($drugs['minimum_consumption'] == 0 and $drugs['stock_level'] == 0) {
				$priority = 100;
			} else {
				$priority = ($drugs['stock_level'] / $drugs['minimum_consumption']) * 100;
			}
			//Check for priority
			if ($priority >= 50) {
				$priority_level = "<span class='low_priority'><b>LOW</b></span>";
			} else {
				$priority_level = "<span class='high_priority'><b>HIGH</b></span>";
			}

			$this -> table -> add_row($x, $drugs['drug_name'], $drugs['drug_unit'], $drugs['stock_level'], $drugs['minimum_consumption'], $priority_level);
			$x++;
		}
		$drug_display = $this -> table -> generate();
		echo $drug_display;
	}

	public function stock_info($stock_type = "2") {
		$drugs_array = array();
		$counter = 0;

		//Get the facility_code
		$facility_code = $this -> session -> userdata("facility");
		$strDATA = "";
		$strcat = "";
		$strLEVEL = "";
		$strXML = "<chart caption='Drugs Below Safety Stock' useroundedges='1' >";
		//Store
		if ($stock_type == '1') {
			$stock_param = " AND (source='" . $facility_code . "' OR destination='" . $facility_code . "') AND source!=destination ";
		}
		//Pharmacy
		else if ($stock_type == '2') {
			$stock_param = " AND (source=destination) AND(source='" . $facility_code . "') ";
		}
		//Create table to store data
		$tmpl = array('table_open' => '<table id="stock_level" class="table table-striped table-condensed">');
		$this -> table -> set_template($tmpl);
		$this -> table -> set_heading('No', 'Drug', 'Unit', 'Qty (Units)', 'Threshold Qty (Units)', 'Priority');
		$data = "";
		$x = 1;
		$priority = "";
		foreach ($drugs_array as $drugs) {
			if ($drugs['minimum_consumption'] == 0 and $drugs['stock_level'] == 0) {
				$priority = 100;
			} else {
				$priority = ($drugs['stock_level'] / $drugs['minimum_consumption']) * 100;
			}
			//Check for priority
			if ($priority >= 50) {
				$priority_level = "<span class='low_priority'><b>LOW</b></span>";
			} else {
				$priority_level = "<span class='high_priority'><b>HIGH</b></span>";
			}

			$this -> table -> add_row($x, $drugs['drug_name'], $drugs['drug_unit'], $drugs['stock_level'], $drugs['minimum_consumption'], $priority_level);
			$x++;
		}
		$drug_display = $this -> table -> generate();
		echo $drug_display;

	}

	public function showChart() {
		$this -> load -> view("drug_below_safety_v");
	}

	public function getExpiringDrugs($period = 30, $stock_type = 1) {
		$expiryArray = array();
		$stockArray = array();
		$resultArraySize = 0;
		$count = 0;
		$facility_code = $this -> session -> userdata('facility');
		//$drugs_sql = "SELECT s.id AS id,s.drug AS Drug_Id,d.drug AS Drug_Name,d.pack_size AS pack_size, u.name AS Unit, s.batch_number AS Batch,s.expiry_date AS Date_Expired,DATEDIFF(s.expiry_date,CURDATE()) AS Days_Since_Expiry FROM drugcode d LEFT JOIN drug_unit u ON d.unit = u.id LEFT JOIN drug_stock_movement s ON d.id = s.drug LEFT JOIN transaction_type t ON t.id=s.transaction_type WHERE t.effect=1 AND DATEDIFF(s.expiry_date,CURDATE()) <='$period' AND DATEDIFF(s.expiry_date,CURDATE())>=0 AND d.enabled=1 AND s.facility ='" . $facility_code . "' GROUP BY Batch ORDER BY Days_Since_Expiry asc";
		$drugs_sql = "SELECT d.drug as drug_name,d.pack_size,u.name as drug_unit,dsb.batch_number as batch,dsb.balance as stocks_display,dsb.expiry_date,DATEDIFF(dsb.expiry_date,CURDATE()) as expired_days_display FROM drugcode d LEFT JOIN drug_unit u ON d.unit=u.id LEFT JOIN drug_stock_balance dsb ON d.id=dsb.drug_id WHERE DATEDIFF(dsb.expiry_date,CURDATE()) <='$period' AND DATEDIFF(dsb.expiry_date,CURDATE())>=0 AND d.enabled=1 AND dsb.facility_code ='" . $facility_code . "' AND dsb.stock_type='" . $stock_type . "' AND dsb.balance>0 ORDER BY expired_days_display asc";
		$drugs = $this -> db -> query($drugs_sql);
		$results = $drugs -> result_array();
		$d = 0;
		$drugs_array = $results;

		$nameArray = array();
		$dataArray = array();
		foreach ($drugs_array as $drug) {
			$nameArray[] = $drug['drug_name'] . '(' . $drug['batch'] . ')';
			$expiryArray[] = (int)$drug['expired_days_display'];
			$stockArray[] = (int)$drug['stocks_display'];
			$resultArraySize++;
		}
		$resultArray = array( array('name' => 'Expiry', 'data' => $expiryArray), array('name' => 'Stock', 'data' => $stockArray));
		
		$resultArray = json_encode($resultArray);
		$categories = $nameArray;
		$categories = json_encode($categories);
		//Load Data Variables
		$data['resultArraySize'] = $resultArraySize;
		$data['container'] = 'chart_expiry';
		$data['chartType'] = 'bar';
		$data['title'] = 'Chart';
		$data['chartTitle'] = 'Expiring Drugs';
		$data['categories'] = $categories;
		$data['yAxix'] = 'Drugs';
		$data['resultArray'] = $resultArray;
		$this -> load -> view('chart_v', $data);

	}

	public function getBatchInfo($drug, $batch, $drug_unit, $drug_name, $expiry_date, $expired_days, $drug_id, $pack_size, $stock_type, $facility_code) {
		$stock_status = 0;
		$stock_param = "";

		//Store
		if ($stock_type == '1') {
			$stock_param = " AND (source='" . $facility_code . "' OR destination='" . $facility_code . "') AND source!=destination ";
		}
		//Pharmacy
		else if ($stock_type == '2') {
			$stock_param = " AND (source=destination) AND(source='" . $facility_code . "') ";
		}
		$initial_stock_sql = "SELECT SUM( d.quantity ) AS Initial_stock, d.transaction_date AS transaction_date, '" . $batch . "' AS batch FROM drug_stock_movement d WHERE d.drug =  '" . $drug . "' AND facility='" . $facility_code . "' " . $stock_param . " AND transaction_type =  '11' AND d.batch_number =  '" . $batch . "'";
		$batches = $this -> db -> query($initial_stock_sql);
		$batch_results = $batches -> result_array();
		foreach ($batch_results as $batch_result => $value) {
			$initial_stock = $value['Initial_stock'];
			//Check if initial stock is present meaning physical count done
			if ($initial_stock != null) {
				$batch_stock_sql = "SELECT (SUM( ds.quantity ) - SUM( ds.quantity_out )) AS stock_levels, ds.batch_number FROM drug_stock_movement ds WHERE ds.transaction_date BETWEEN  '" . $value['transaction_date'] . "' AND curdate() AND facility='" . $facility_code . "' " . $stock_param . " AND ds.drug ='" . $drug . "'  AND ds.batch_number ='" . $value['batch'] . "'";
				$second_row = $this -> db -> query($batch_stock_sql);
				$second_rows = $second_row -> result_array();

				foreach ($second_rows as $second_row => $value) {
					if ($value['stock_levels'] > 0) {
						$batch_balance = $value['stock_levels'];
						$ed = substr($expired_days, 0, 1);
						if ($ed == "-") {
							$expired_days = $expired_days;
						}

						$batch_stock = $batch_balance / $pack_size;
						$expired_days_display = number_format($expired_days);
						$stocks_display = ceil(number_format($batch_stock, 1));

						$this -> drug_array[$this -> counter]['drug_name'] = $drug_name;
						$this -> drug_array[$this -> counter]['batch'] = $batch;
						$this -> drug_array[$this -> counter]['stocks_display'] = $stocks_display;
						$this -> drug_array[$this -> counter]['expired_days_display'] = $expired_days_display;
						$this -> counter++;
					}
				}

			} else {

				$batch_stock_sql = "SELECT (SUM( ds.quantity ) - SUM( ds.quantity_out ) ) AS stock_levels, ds.batch_number FROM drug_stock_movement ds WHERE ds.drug =  '" . $drug . "' AND facility='" . $facility_code . "' " . $stock_param . " AND ds.expiry_date > curdate() AND ds.batch_number='" . $value['batch'] . "'";
				$second_row = $this -> db -> query($batch_stock_sql);
				$second_rows = $second_row -> result_array();

				foreach ($second_rows as $second_row => $value) {

					if ($value['stock_levels'] > 0) {
						$batch_balance = $value['stock_levels'];
						$ed = substr($expired_days, 0, 1);
						if ($ed == "-") {

							$expired_days = $expired_days;
						}
						@$batch_stock = $batch_balance / @$pack_size;
						$expired_days_display = number_format($expired_days);

						$stocks_display = number_format($batch_stock, 1);

						$this -> drug_array[$this -> counter]['drug_name'] = $drug_name;
						$this -> drug_array[$this -> counter]['batch'] = $batch;
						$this -> drug_array[$this -> counter]['stocks_display'] = $stocks_display;
						$this -> drug_array[$this -> counter]['expired_days_display'] = $expired_days_display;
						$this -> counter++;
					}
				}
			}

		}
	}

	//Get patients enrolled
	public function getPatientEnrolled($startdate = "", $enddate = "") {
		$startdate = date('Y-m-d', strtotime($startdate));
		$enddate = date('Y-m-d', strtotime($enddate));
		$first_date = $startdate;
		$last_date = $enddate;
		$maleAdult = array();
		$femaleAdult = array();
		$maleChild = array();
		$femaleChild = array();
		$facility_code = $this -> session -> userdata('facility');
		$timestamp = time();
		$edate = date('Y-m-d', $timestamp);
		$dates = array();
		$x = 6;
		$y = 0;
		$resultArraySize = 0;
		$days_in_year = date("z", mktime(0, 0, 0, 12, 31, date('Y'))) + 1;
		$adult_age = 15;
		$patients_array = array();

		//If no parameters are passed, get enrolled patients for the past 7 days
		if ($startdate == "" || $enddate == "") {
			for ($i = 0; $i < $x; $i++) {
				if (date("D", $timestamp) != "Sun") {
					$sdate = date('Y-m-d', $timestamp);
					//Store the days in an array
					$dates[$y] = $sdate;
					$y++;
				}
				//If sunday is included, add one more day
				else {$x = 8;
				}
				$timestamp += 24 * 3600;
			}
			$start_date = $sdate;
			$end_date = $edate;
		} else {
			$startdate = strtotime($startdate);
			for ($i = 0; $i < $x; $i++) {
				if (date("D", $startdate) != "Sun") {
					$sdate = date('Y-m-d', $startdate);
					//Store the days in an array

					$dates[$y] = $sdate;
					$y++;
				}
				//If sunday is included, add one more day
				else {$x = 8;
				}
				$startdate += 24 * 3600;
			}
			$start_date = $startdate;
			$end_date = $enddate;
		}

		/*Loop through all dates in range and get summary of patients enrollment i those days */
		foreach ($dates as $date) {

			$stmt = "SELECT p.date_enrolled, g.name AS gender, ROUND(DATEDIFF(CURDATE(),p.dob)/$days_in_year) AS age,COUNT(*) AS total
					FROM patient p
					LEFT JOIN gender g ON p.gender = g.id
					WHERE p.date_enrolled ='$date'
					GROUP BY g.name, ROUND(DATEDIFF(CURDATE(),p.dob)/$days_in_year)>$adult_age";
			$q = $this -> db -> query($stmt);
			$rs = $q -> result_array();

			/*Loop through selected days result set*/
			$total_male_adult = 0;
			$total_female_adult = 0;
			$total_male_child = 0;
			$total_female_child = 0;

			if ($rs) {
				foreach ($rs as $r) {
					/*Check if Adult Male*/
					if (strtolower($r['gender']) == "male" && $r['age'] >= $adult_age) {
						$total_male_adult = $r['total'];
					}
					/*Check if Adult Female*/
					if (strtolower($r['gender']) == "female" && $r['age'] >= $adult_age) {
						$total_female_adult = $r['total'];
					}
					/*Check if Child Male*/
					if (strtolower($r['gender']) == "male" && $r['age'] < $adult_age) {
						$total_male_child = $r['total'];
					}
					/*Check if Child Female*/
					if (strtolower($r['gender']) == "female" && $r['age'] < $adult_age) {
						$total_female_child = $r['total'];
					}
				}
			}

			/*Place Values into an Array*/

			$patients_array[$date] = array("Adult Male" => $total_male_adult, "Adult Female" => $total_female_adult, "Child Male" => $total_male_child, "Child Female" => $total_female_child);

		}

		$resultArraySize = 6;
		$categories = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
		foreach ($patients_array as $key => $value) {
			$maleAdult[] = (int)$value['Adult Male'];
			$femaleAdult[] = (int)$value['Adult Female'];
			$maleChild[] = (int)$value['Child Male'];
			$femaleChild[] = (int)$value['Child Female'];
		}
		$resultArray = array( array('name' => 'Male Adult', 'data' => $maleAdult), array('name' => 'Female Adult', 'data' => $femaleAdult), array('name' => 'Male Child', 'data' => $maleChild), array('name' => 'Female Child', 'data' => $femaleChild));
		$resultArray = json_encode($resultArray);
		$categories = json_encode($categories);

		$data['resultArraySize'] = $resultArraySize;
		$data['container'] = "chart_enrollment";
		$data['chartType'] = 'bar';
		$data['chartTitle'] = 'Patients Enrollment';
		$data['yAxix'] = 'Patients';
		$data['categories'] = $categories;
		$data['resultArray'] = $resultArray;
		$this -> load -> view('chart_stacked_v', $data);

	}

	//Get patients expected for appointment
	public function getExpectedPatients($startdate = "", $enddate = "") {
		$startdate = date('Y-m-d', strtotime($startdate));
		$enddate = date('Y-m-d', strtotime($enddate));
		$first_date = $startdate;
		$last_date = $enddate;
		$facility_code = $this -> session -> userdata('facility');
		$timestamp = time();
		$edate = date('Y-m-d', $timestamp);
		$dates = array();
		$x = 6;
		$y = 0;
		$missed = array();
		$visited = array();

		//If no parameters are passed, get enrolled patients for the past 7 days
		if ($startdate == "" || $enddate == "") {
			for ($i = 0; $i < $x; $i++) {
				if (date("D", $timestamp) != "Sun") {
					$sdate = date('Y-m-d', $timestamp);
					//Store the days in an array
					$dates[$y] = $sdate;
					$y++;
				}
				//If sunday is included, add one more day
				else {$x = 8;
				}
				$timestamp += 24 * 3600;
			}
			$start_date = $sdate;
			$end_date = $edate;
		} else {
			$startdate = strtotime($startdate);
			for ($i = 0; $i < $x; $i++) {
				if (date("D", $startdate) != "Sun") {
					$sdate = date('Y-m-d', $startdate);
					//Store the days in an array

					$dates[$y] = $sdate;
					$y++;
				}
				//If sunday is included, add one more day
				else {$x = 8;
				}
				$startdate += 24 * 3600;
			}
			$start_date = $startdate;
			$end_date = $enddate;
		}
		//Get Data for total_expected and total_visited in selected period
		$start_date = $first_date;
		$end_date = $last_date;
		$sql = "select temp1.appointment,temp1.total_expected,temp2.total_visited from (select pa.appointment,count(distinct pa.patient) as total_expected from patient_appointment pa where pa.appointment between '$start_date' and '$end_date' group by pa.appointment) as temp1 left join (SELECT dispensing_date, COUNT( DISTINCT patient_id ) AS total_visited FROM patient_visit WHERE dispensing_date BETWEEN  '$start_date' AND  '$end_date' GROUP BY dispensing_date) as temp2 on temp1.appointment=temp2.dispensing_date";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();

		$outer_array = array();
		foreach ($results as $result) {
			$outer_array[$result['appointment']]['expected'] = $result['total_expected'];
			$outer_array[$result['appointment']]['visited'] = $result['total_visited'];
		}
		$keys = array_keys($outer_array);
		//Loop through dates and check if they are in the result array
		foreach ($dates as $date) {
			$index = array_search($date, $keys);
			if ($index >= 0) {
				$visited[] =@(int)$outer_array[$keys[$index]]['visited'];
				$missed[] = @(int)$outer_array[$keys[$index]]['expected'];
			} else {
				$visited[] = 0;
				$missed[] = 0;
			}
		}
		$categories = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
		$resultArray = array( array('name' => 'Visited', 'data' => $visited), array('name' => 'Expected', 'data' => $missed));
		$resultArray = json_encode($resultArray);
		$categories = json_encode($categories);
		$data['resultArraySize'] = 6;
		$data['container'] = "chart_appointments";
		$data['chartType'] = 'bar';
		$data['chartTitle'] = 'Patients Expected';
		$data['yAxix'] = 'Patients';
		$data['categories'] = $categories;
		$data['resultArray'] = $resultArray;
		$this -> load -> view('chart_v', $data);

	}

	function age_from_dob($dob) {
		list($y, $m, $d) = explode('-', $dob);
		if (($m = (date('m') - $m)) < 0) {
			$y++;
		} elseif ($m == 0 && date('d') - $d < 0) {
			$y++;
		}
		return date('Y') - $y;

	}

	public function base_params($data) {
		$data['content_view'] = "settings_v";
		$data['quick_link'] = "client_sources";
		$this -> load -> view("template", $data);
	}

	/**
	 * Highchart Test Charts
	 */
	//Get patients enrolled
	public function getPatHC($startdate = "", $enddate = "") {
		$facility_code = $this -> session -> userdata('facility');
		$timestamp = time();
		$edate = date('Y-m-d', $timestamp);
		$dates = array();
		$x = 7;
		$y = 0;

		//If no parameters are passed, get enrolled patients for the past 7 days
		if ($startdate == "" || $enddate == "") {
			for ($i = 0; $i < $x; $i++) {
				if (date("D", $timestamp) != "Sun") {
					$sdate = date('Y-m-d', $timestamp);
					//Store the days in an array
					$dates[$y] = $sdate;
					$y++;
				}
				//If sunday is included, add one more day
				else {$x = 8;
				}
				$timestamp -= 24 * 3600;
			}
			$start_date = $sdate;
			$end_date = $edate;
		} else {
			$start_date = $startdate;
			$end_date = $enddate;
		}
		$get_patient_sql = "SELECT p.gender, dob , date_enrolled FROM patient p WHERE p.date_enrolled
							BETWEEN  '" . $start_date . "' AND  '" . $end_date . "' AND p.facility_code='" . $facility_code . "' ORDER BY p.date_enrolled  ";
		$res = $this -> db -> query($get_patient_sql);
		$x = 0;
		$y = 0;
		$count_patient_date = 0;
		$date_enrolled = "";
		$counter = 0;
		$total_male_adult = 0;
		$total_female_adult = 0;
		$total_male_child = 0;
		$total_female_child = 0;
		$patients_array = array();

		$results = $res -> result_array();

		$adult = array();
		$child = array();
		//Split Gender
		foreach ($results as $key => $value) {
			$age = $value['dob'];
			$age = $this -> age_from_dob($age);
			if ($value['gender'] == 1) {
				if ($age > 15) {
					$total_male_adult += 1;
				} elseif ($age < 15) {
					$total_male_child += 1;
				}

			} elseif ($value['gender'] == 2) {
				if ($age > 15) {
					$total_female_adult += 1;
				} elseif ($age < 15) {
					$total_female_child += 1;
				}
			}

		}
		/*
		 //Loop through the array to get totals for each category
		 foreach ($results as $key => $value) {
		 $count_patient_date++;
		 if ($x == 0) {
		 $x = 1;
		 $date_enrolled = $value['date_enrolled'];
		 }
		 //If enrollement date changes
		 if ($value['date_enrolled'] != $date_enrolled) {
		 $count_patient_date = 1;
		 $y = 0;
		 $total_male_adult = 0;
		 $total_female_adult = 0;
		 $total_male_child = 0;
		 $total_female_child = 0;
		 $counter++;
		 $patients_array[$counter]['date_enrolled'] = $value['date_enrolled'];
		 $patients_array[$counter]['total_day'] = $count_patient_date;
		 $date_enrolled = $value['date_enrolled'];

		 } else if ($value['date_enrolled'] == $date_enrolled) {

		 if ($y != 1) {
		 //Initialise totals
		 $patients_array[$counter]['date_enrolled'] = $value['date_enrolled'];
		 $patients_array[$counter]['total_male_adult'] = 0;
		 $patients_array[$counter]['total_female_adult'] = 0;
		 $patients_array[$counter]['total_male_child'] = 0;
		 $patients_array[$counter]['total_female_child'] = 0;
		 }
		 $patients_array[$counter]['total_day'] = $count_patient_date;
		 $y = 1;

		 }

		 $birthDate = $value['dob'];
		 //get age from date or birthdate
		 $age = $this -> age_from_dob($birthDate);
		 //If patient is male, check if he is an adult or child
		 if ($value['gender'] == 1) {
		 //Check if adult
		 if ($age >= 15) {
		 $total_male_adult++;
		 $patients_array[$counter]['total_male_adult'] = $total_male_adult;
		 $patients_array[$counter]['total_male_child'] = $total_male_child;
		 $patients_array[$counter]['total_female_adult'] = $total_female_adult;
		 $patients_array[$counter]['total_female_child'] = $total_female_child;
		 } else {
		 $total_male_child++;
		 $patients_array[$counter]['total_male_adult'] = $total_male_adult;
		 $patients_array[$counter]['total_male_child'] = $total_male_child;
		 $patients_array[$counter]['total_female_adult'] = $total_female_adult;
		 $patients_array[$counter]['total_female_child'] = $total_female_child;
		 }
		 }
		 //If patient is female, check if he is an adult or child
		 else if ($value['gender'] == 2) {
		 //Check if adult
		 if ($age >= 15) {
		 $total_female_adult++;
		 $patients_array[$counter]['total_male_adult'] = $total_male_adult;
		 $patients_array[$counter]['total_male_child'] = $total_male_child;
		 $patients_array[$counter]['total_female_adult'] = $total_female_adult;
		 $patients_array[$counter]['total_female_child'] = $total_female_child;
		 } else {
		 $total_female_child++;
		 $patients_array[$counter]['total_male_adult'] = $total_male_adult;
		 $patients_array[$counter]['total_male_child'] = $total_male_child;
		 $patients_array[$counter]['total_female_adult'] = $total_female_adult;
		 $patients_array[$counter]['total_female_child'] = $total_female_child;
		 }
		 }

		 }

		 $strXML = "<chart useroundedges='1' bgcolor='ffffff' showborder='0' yAxisName='Enrollments' showvalues='1' showsum='1' areaOverColumns='0' showPercentValues='1' baseFont='Arial' baseFontSize='9' palette='2' rotateNames='1' animation='1'  labelDisplay='Rotate' slantLabels='1' exportEnabled='1' exportHandler='" . base_url() . "Scripts/FusionCharts/ExportHandlers/PHP/FCExporter.php' exportAtClient='0' exportAction='download'>";

		 $stradultmale = "<dataset seriesName='Adult Male' showValues= '0' >";
		 $stradultfemale = "<dataset seriesName='Adult Female' showValues= '0' >";
		 $strchildmale = "<dataset seriesName='Child Male' showValues= '0' >";
		 $strchildfemale = "<dataset seriesName='Child Female' showValues= '0' >";
		 $strCAT = "<categories>";
		 foreach ($patients_array as $patients) {

		 $strCAT .= "<category label='" . date('D M d,Y', strtotime($patients['date_enrolled'])) . "'/>";

		 $stradultmale .= "<set value='" . $patients['total_male_adult'] . "' />";
		 $stradultfemale .= "<set value='" . $patients['total_female_adult'] . "' />";
		 $strchildmale .= "<set value='" . $patients['total_male_child'] . "' />";
		 $strchildfemale .= "<set value='" . $patients['total_female_child'] . "' />";
		 }
		 $strCAT .= "</categories>";
		 $stradultmale .= "</dataset>";
		 $stradultfemale .= "</dataset>";
		 $strchildmale .= "</dataset>";
		 $strchildfemale .= "</dataset>";
		 $strXML .= $strCAT . $stradultmale . $stradultfemale . $strchildmale . $strchildfemale;

		 header('Content-type: text/xml');
		 echo $strXML .= "</chart>";*/

	}

}
