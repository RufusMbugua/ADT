<?php
class Order_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this -> load -> library('pagination');
		date_default_timezone_set('Africa/Nairobi');
		$this -> load -> library('PHPExcel');
	}

	public function index() {
		$this -> submitted_orders();
	}

	public function export($order) {
		$inputFileType = 'Excel2007';
		$inputFileName = $_SERVER['DOCUMENT_ROOT'] . '/ADT/assets/template.xlsx';
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		$objPHPExcel = $objReader -> load($inputFileName);
		$order_types = array(0 => "Central Order", 1 => "Aggregated Order", 2 => "Satellite Order");
		$original_order = $order;
		$dir = "Export";
		$i = 9;
		$j = $i - 1;
		$last_counter = 0;
		$overall_total = 0;
		$made_by = "";
		$access_level = "";

		/*Delete all files in export folder*/
		if (is_dir($dir)) {
			$files = scandir($dir);
			foreach ($files as $object) {
				if ($object != "." && $object != "..") {
					unlink($dir . "/" . $object);
				}
			}
		} else {
			mkdir($dir);
		}

		/*Resources needed*/
		$order_details = Facility_Order::getOrder($order);
		$order = $order_details -> Unique_Id;
		$cdrrs = Cdrr_Item::getOrderItems($order);
		$maps = Maps_Item::getOrderItems($order);
		$comments = Order_Comment::getOrderComments($order);

		/*Set Order Details*/
		$objPHPExcel -> getActiveSheet() -> SetCellValue('G2', $original_order . "(" . @$order_types[$order_details -> Code] . ")");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('G3', $order_details -> Facility_Object -> name);
		$objPHPExcel -> getActiveSheet() -> SetCellValue('G4', $order_details -> Facility_Object -> Parent_District -> Name . "/" . $order_details -> Facility_Object -> County -> county);
		$objPHPExcel -> getActiveSheet() -> SetCellValue('K2', $order_details -> Facility_Object -> facilitycode);
		$objPHPExcel -> getActiveSheet() -> SetCellValue('K3', $order_details -> Facility_Object -> Type -> Name);
		$objPHPExcel -> getActiveSheet() -> SetCellValue('K4', date("M-Y", strtotime($order_details -> Period_Begin)));

		/*Set Cddr Commodities*/
		$type = $order_details -> Code;
		$unit = "In Units";
		if ($type == "1") {
			$unit = "In Packs";
		}

		$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $j, $unit);
		$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $j, $unit);
		$objPHPExcel -> getActiveSheet() -> SetCellValue('E' . $j, $unit);
		$objPHPExcel -> getActiveSheet() -> SetCellValue('F' . $j, $unit);
		$objPHPExcel -> getActiveSheet() -> SetCellValue('G' . $j, $unit);
		$objPHPExcel -> getActiveSheet() -> SetCellValue('H' . $j, $unit);
		$objPHPExcel -> getActiveSheet() -> SetCellValue('I' . $j, $unit);
		$objPHPExcel -> getActiveSheet() -> SetCellValue('K' . $j, $unit);

		foreach ($cdrrs as $cdrr) {
			$i++;
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $i, $cdrr -> Drugcode_Object -> Drug);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $i, $cdrr -> Drugcode_Object -> Pack_Size);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $i, number_format((double)$cdrr -> Balance));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $i, number_format((double)$cdrr -> Received));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('E' . $i, number_format((double)$cdrr -> Dispensed_Units));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('F' . $i, number_format((double)$cdrr -> Losses));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('G' . $i, number_format((double)$cdrr -> Adjustments));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('H' . $i, number_format((double)$cdrr -> Count));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('I' . $i, number_format((double)$cdrr -> Aggr_Consumed));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('J' . $i, $cdrr -> Aggr_On_Hand);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('K' . $i, number_format((double)$cdrr -> Resupply));

		}
		$last_counter = $i;
		$i = 9;
		/*Set Maps Patients*/
		foreach ($maps as $map) {
			$i++;
			$objPHPExcel -> getActiveSheet() -> SetCellValue('M' . $i, $map -> Regimen_Id);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('N' . $i, number_format((double)$map -> Total));
			$overall_total += $map -> Total;
		}
		$i++;
		$objPHPExcel -> getActiveSheet() -> SetCellValue('M' . $i, "Overall Total");
		$objPHPExcel -> getActiveSheet() -> SetCellValue('N' . $i, number_format((double)$overall_total));

		$i = $last_counter + 3;
		/*Set Comments*/
		foreach ($comments as $comment) {
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $i, "Comments");
			$i++;
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $i, $comment -> Comment);
			$i = $i + 3;
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $i, "Last Update");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $i, "Made By");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $i, "Access Level");
			$i++;
			if ($comment -> User_Object -> Name) {
				$made_by = $comment -> User_Object -> Name;
			} else {
				$made_by = $comment -> User;
			}
			if ($comment -> User_Object -> Access -> Level_Name) {
				$access_level = $comment -> User_Object -> Access -> Level_Name;
			} else {
				$access_level = "Facility Administrator";
			}
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $i, date('l d-M-Y h:i:s a', $comment -> Timestamp));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $i, $made_by);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $i, $access_level);
			$i = $i + 3;
		}

		/*Generate CSV File*/
		ob_start();
		$facility_name = Facilities::getFacilityName($order_details -> Facility_Object -> facilitycode);
		$facility_name .= "(" . date('d-M-Y h:i:s a') . ")";
		$filename = $dir . "/F-Maps Order_no " . $original_order . ".xlsx";
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter -> save($filename);
		$objPHPExcel -> disconnectWorksheets();
		unset($objPHPExcel);
		if (file_exists($filename)) {
			redirect($filename);
		}
	}

	public function view_order($order) {
		//First retrieve the order and its particulars from the database
		$data = array();
		$data['order_no'] = $order;
		$data['order_details'] = Facility_Order::getOrder($order);
		$order = $data['order_details'] -> Unique_Id;
		$data['order_details_page'] = 'view_order';
		$data['commodities'] = Cdrr_Item::getOrderItems($order);
		$data['regimens'] = Maps_Item::getOrderItems($order);
		$data['comments'] = Order_Comment::getOrderComments($order);
		$data['content_view'] = "view_order_v";
		$data['hide_side_menu'] = 1;
		$data['banner_text'] = "Order Particulars";
		//get all submitted orders that have not been rationalized (fresh orders)
		$this -> base_params($data);
	}

	public function edit_order($order) {
		//First retrieve the order and its particulars from the database
		$data = array();
		$data['order_details_page'] = 'edit_order';
		$data['order_no'] = $order;
		$data['order_details'] = Facility_Order::getOrder($order);
		$order = $data['order_details'] -> Unique_Id;
		$data['hide_side_menu'] = 1;
		$this -> load -> database();
		//Get all drugs, ordered or not
		$sql = "select d.drug,d.id as did,d.pack_size,c.* from drugcode d left join cdrr_item c on d.drug = c.drug_id and c.cdrr_id = '$order' where d.supplied = '1' order by d.id";
		$query = $this -> db -> query($sql);
		$data['commodities'] = $query -> result_array();
		//Get all regimens; ordered or not
		$regimen_sql = "select r.regimen_desc,r.id as rid,m.* from regimen r left join maps_item m on CONCAT(r.regimen_code,concat(' | ',r.regimen_desc)) = m.regimen_id and m.maps_id = '$order' order by r.id";
		$regimen_query = $this -> db -> query($regimen_sql);
		//$data['commodities'] = $query -> result_array();
		//var_dump($data['commodities']);
		$data['regimen_totals'] = $regimen_query -> result_array();
		//print_r($data['regimen_totals']['44']);
		//die();
		$data['comments'] = Order_Comment::getOrderComments($order);
		$order_type = $data['order_details']['Code'];
		$data['order_type'] = $order_type;

		//If order is for central
		$data['content_view'] = "edit_order_v";
		//$data['content_view'] = "edit_order_v";
		$data['regimen_categories'] = Regimen_Category::getAll();
		$data['banner_text'] = "Order Particulars";
		//get all submitted orders that have not been rationalized (fresh orders)
		$this -> base_params($data);
	}

	public function submitted_orders_2() {
		if ($status == 0) {
			$data['page_title'] = "Pending Orders";
			$data['days_pending'] = "Approval";
		} elseif ($status == 1) {
			$data['page_title'] = "Approved Orders";
			$data['days_pending'] = "Dispatched";
		} elseif ($status == 2) {
			$data['page_title'] = "Declined Orders";
			$data['days_pending'] = "Resubmission";
		} elseif ($status == 3) {
			$data['page_title'] = "Dispatched Orders";
			$data['days_pending'] = "Delivery";
		}

		$facility = $this -> session -> userdata('facility');
		$items_per_page = 10;
		$number_of_orders = Facility_Order::getTotalFacilityNumber($status, $facility);
		$orders = Facility_Order::getPagedFacilityOrders($offset, $items_per_page, $status, $facility);
		$data = array();
		/* Array of database columns which should be read and sent back to DataTables. Use a space where
		 * you want to insert a non-database field (for example a counter or static image)
		 */
		$aColumns = array('drug', 'unit', 'pack_size');

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
		$this -> db -> select("dc.id,SUM(dsb.balance) as stock_level,g.Name as generic_name,s.Name as supported_by,d.Name as dose");
		$today = date('Y-m-d');
		$this -> db -> from("drugcode dc");
		$this -> db -> where('dc.enabled', '1');
		$this -> db -> where('dsb.facility_code', $facility_code);
		$this -> db -> where('dsb.expiry_date > ', $today);
		$this -> db -> where('dsb.stock_type ', '2');
		$this -> db -> join("generic_name g", "g.id=dc.generic_name");
		$this -> db -> join("drug_stock_balance dsb", "dsb.drug_id=dc.id");
		$this -> db -> join("supporter s", "s.id=dc.supported_by");
		$this -> db -> join("dose d", "d.id=dc.dose");
		$this -> db -> group_by("dsb.drug_id");

		$rResult = $this -> db -> get();

		// Data set length after filtering
		$this -> db -> select('FOUND_ROWS() AS found_rows');
		$iFilteredTotal = $this -> db -> get() -> row() -> found_rows;

		// Total data set length
		$this -> db -> select("dsb.*");
		$where = "dc.enabled='1' AND dsb.facility='$facility_code' AND dsb.expiry_date > CURDATE() AND dsb.stock_type='1'";
		$this -> db -> from("drugcode dc");
		$this -> db -> where('dc.enabled', '1');
		$this -> db -> where('dsb.facility_code', $facility_code);
		$this -> db -> where('dsb.expiry_date > ', $today);
		$this -> db -> where('dsb.stock_type ', '2');
		$this -> db -> join("generic_name g", "g.id=dc.generic_name");
		$this -> db -> join("drug_stock_balance dsb", "dsb.drug_id=dc.id");
		$this -> db -> join("supporter s", "s.id=dc.supported_by");
		$this -> db -> join("dose d", "d.id=dc.dose");
		$this -> db -> group_by("dsb.drug_id");
		$tot_drugs = $this -> db -> get();
		$iTotal = count($tot_drugs -> result_array());

		// Output
		$output = array('sEcho' => intval($sEcho), 'iTotalRecords' => $iTotal, 'iTotalDisplayRecords' => $iFilteredTotal, 'aaData' => array());

		foreach ($rResult->result_array() as $aRow) {
			$row = array();
			$x = 0;
			foreach ($aColumns as $col) {
				$x++;
				$row[] = strtoupper($aRow[$col]);
				//Append Generic name
				if ($x == 1) {
					$row[] = strtoupper($aRow['generic_name']);
					$row[] = '<b style="color:green">' . number_format($aRow['stock_level']) . '</b>';
				} else if ($x == 3) {
					$row[] = $aRow['supported_by'];
					$row[] = $aRow['dose'];
					$id = $aRow['id'];
					$row[] = "<a href='" . base_url() . "inventory_management/view_bin_card/" . $id . "/2'>View Bin Card</a>";
				}

			}
			$output['aaData'][] = $row;
		}
		echo json_encode($output);
	}

	public function submitted_orders($status = 0, $offset = 0) {

		if ($status == 0) {
			$data['page_title'] = "Pending Orders";
			$data['days_pending'] = "Approval";
		} elseif ($status == 1) {
			$data['page_title'] = "Approved Orders";
			$data['days_pending'] = "Dispatched";
		} elseif ($status == 2) {
			$data['page_title'] = "Declined Orders";
			$data['days_pending'] = "Resubmission";
		} elseif ($status == 3) {
			$data['page_title'] = "Dispatched Orders";
			$data['days_pending'] = "Delivery";
		} elseif ($status == 4) {
			$data['page_title'] = "Delivered Orders";
			$data['days_pending'] = "Since Delivery";
		}

		$facility = $this -> session -> userdata('facility');
		$items_per_page = 10;
		$number_of_orders = Facility_Order::getTotalFacilityNumber($status, $facility);
		$orders = Facility_Order::getPagedFacilityOrders($offset, $items_per_page, $status, $facility);

		if ($number_of_orders > $items_per_page) {
			$config['base_url'] = base_url() . "order_management/submitted_orders/" . $status . "/";
			$config['total_rows'] = $number_of_orders;
			$config['per_page'] = $items_per_page;
			$config['uri_segment'] = 4;
			$config['num_links'] = 5;
			$this -> pagination -> initialize($config);
			$data['pagination'] = $this -> pagination -> create_links();
		}
		$data['facilities'] = Facilities::getSatellites($facility);
		$data['orders'] = $orders;
		$data['quick_link'] = $status;
		$data['content_view'] = "view_facility_orders_v";
		$data['banner_text'] = "Submitted Orders";
		//$data['styles'] = array("pagination.css");
		//get all submitted orders that have not been rationalized (fresh orders)

		$this -> base_params($data);
	}

	//Function used to check if order belongs to an aggregated order
	public function order_aggregate($order) {
		$check_order = Aggregated_Order::getAggregatedOrder($order);
		echo json_encode($check_order);
	}

	public function delete_order($order, $aggregated_order = "") {
		$this -> session -> set_userdata('msg_error', 'Order No:' . $order . ' was Deleted');
		$order = Facility_Order::getOrder($order);
		$order_number = $order -> Unique_Id;
		$order -> delete();
		$old_commodities = Cdrr_Item::getOrderItems($order_number);
		$old_regimens = Maps_Item::getOrderItems($order_number);
		$old_comments = Order_Comment::getOrderComments($order_number);
		foreach ($old_commodities as $old_commodity) {
			$old_commodity -> delete();
		}
		foreach ($old_regimens as $old_regimen) {
			$old_regimen -> delete();
		}
		foreach ($old_comments as $old_comment) {
			$old_comment -> delete();
		}
		if ($aggregated_order != "") {
			$order = Facility_Order::getOrder($aggregated_order);
			$order -> delete();
			$agg_order = Aggregated_Order::getOrder($aggregated_order);
			$agg_order -> delete();
		}
		redirect("order_management/submitted_orders");
	}

	public function new_order($facility = "0") {
		if ($facility == "0") {
			$data['content_view'] = "facility_selection_v";
			$data['banner_text'] = "Select Facility";
			$this -> base_params($data);
		}
		$data = array();
		$data['content_view'] = "new_order_v";
		$data['banner_text'] = "New Order";
		$supplier['supplied_by'] = Facilities::getSupplier($this -> session -> userdata("facility"));
		$data['commodities'] = Drugcode::getAllObjects($supplier['supplied_by']);
		$data['regimens'] = Regimen::getAllObjects($this -> session -> userdata('facility'));
		$this -> base_params($data);
	}

	public function new_central_order($type_order = 1) {
		$data = array();

		$facility = $this -> session -> userdata('facility_id');
		$facility_code = $this -> session -> userdata('facility');

		//If order is an aggregated one
		if ($type_order == 1) {

			$data['page_title'] = 'New Aggregated Facility Report';
			if ($this -> input -> post("btn_period_select_proceed")) {
				$data['page_title_1'] = 'Satelitte Orders';
				$reporting_period = $this -> input -> post("reporting_period");
				$reporting_period = date('Y-m', strtotime($reporting_period));
				$start_date = $this -> input -> post("start_date");
				$end_date = $this -> input -> post("end_date");
				$start_date = $reporting_period . "-" . $start_date;
				$end_date = $reporting_period . "-" . $end_date;
				if ($start_date == null || $end_date == null) {
					$data['facility_object'] = Facilities::getFacility($facility);
					$data['content_view'] = "central_facility_selection_v";
					$data['banner_text'] = "Select Reporting Period";
					$this -> base_params($data);
					return;
				} else {

					//$start_date=date('m-Y',strtotime($start_date));
					$data['period_start_date'] = $start_date;
					$data['period_end_date'] = $end_date;
					//Retrieve and display all the orders that were made for that period and are still pending
					$data['satellite_orders'] = Facility_Order::getSatelliteOrders($start_date, $end_date, $facility_code, '0');
					$data['content_view'] = "satellite_orders_v";
					$data['banner_text'] = "Select Satellite Orders";
					$this -> base_params($data);
					return;
				}
			} else {

				$data['facility_object'] = Facilities::getFacility($facility);
				$data['content_view'] = "central_facility_selection_v";
				$data['banner_text'] = "Select Reporting Period";
				$this -> base_params($data);
				return;
			}

		}
		//If order is a central one
		else {

			$data['page_title'] = 'New Central Facility Report';
			$data['order_details_page'] = "new_central_order";
			$data['content_view'] = "new_central_order_v";
			//$data['scripts'] = array("offline_database.js");
			if ($type_order == 0) {
				$data['banner_text'] = "New Central Order";
			} else {
				$data['banner_text'] = "New Satellite Order";
			}
			$supplier['supplied_by'] = Facilities::getSupplier($this -> session -> userdata("facility"));
			$data['commodities'] = Drugcode::getAllObjects($supplier['supplied_by']);
			//$data['regimens'] = Regimen::getAllObjects($facility_id);
			$data['regimen_categories'] = Regimen_Category::getAll();
			$data['facility_object'] = Facilities::getCodeFacility($facility_code);
			$data['hide_side_menu'] = 0;
			$data['page_title'] = "New Central Order";
			$this -> base_params($data);
			return;
		}

	}

	public function new_satellite_order() {

		$central_facility = $this -> session -> userdata('facility');
		$facility_id = $this -> input -> post("satellite_facility");
		$parent = Facilities::getParent($central_facility);

		//Satellite order
		if ($parent -> parent == $central_facility) {

			if ($facility_id < 1) {

				$data['content_view'] = "facility_selection_v";
				$data['banner_text'] = "Select Satelitte Facility";
				$data['facilities'] = Facilities::getSatellites($central_facility);
				//echo json_encode($data);
				//die();

			} else {
				$data = array();
				$data['order_details_page'] = "new_satellite_order";
				$data['content_view'] = "new_order_v";
				//$data['scripts'] = array("offline_database.js");
				$data['banner_text'] = "New Satellite Order";
				$supplier['supplied_by'] = Facilities::getSupplier($this -> session -> userdata("facility"));
				$data['commodities'] = Drugcode::getAllObjects($supplier['supplied_by']);
				//$data['regimens'] = Regimen::getAllObjects($facility_id);
				$data['regimen_categories'] = Regimen_Category::getAll();
				$data['facility_object'] = Facilities::getCodeFacility($facility_id);
				$data['hide_side_menu'] = 0;
			}
			$data['page_title'] = "Satellite Order Details";
			$this -> base_params($data);
			return;
		} else {

			$data = array();
			$data['order_details_page'] = "new_satellite_order";
			$facility_id = $this -> session -> userdata('facility');
			$data['content_view'] = "new_order_v";
			//$data['scripts'] = array("offline_database.js");
			$data['banner_text'] = "New Satellite Order";
			$supplier['supplied_by'] = Facilities::getSupplier($this -> session -> userdata("facility"));
			$data['commodities'] = Drugcode::getAllObjects($supplier['supplied_by']);
			//$data['regimens'] = Regimen::getAllObjects($facility_id);
			$data['regimen_categories'] = Regimen_Category::getAll();
			$data['facility_object'] = Facilities::getCodeFacility($facility_id);
			$data['hide_side_menu'] = 0;
			$this -> base_params($data);
			return;

		}

	}

	public function base_params($data) {
		$central_facility = $this -> session -> userdata('facility');
		$parent = Facilities::getParent($central_facility);
		$data['parent'] = $parent;
		$data['central_facility'] = $central_facility;
		//$data['page_title'] = "Order details";

		$data['_type'] = 'order_facility';
		$data['title'] = "Commodity Orders";
		$data['link'] = "order_management";
		$this -> load -> view('template', $data);
	}

	public function save() {
		$facility = $this -> input -> post('facility_id');
		$central_facility = $this -> input -> post('central_facility');
		$user_id = $this -> session -> userdata('full_name');
		$updated_on = date("U");

		$reporting_period = $this -> input -> post("reporting_period");
		$aggregated_orders = $this -> input -> post("aggregated_order");

		$services = $this -> input -> post('services');
		$sponsors = $this -> input -> post('sponsors');
		$opening_balances = $this -> input -> post('opening_balance');
		$quantities_received = $this -> input -> post('quantity_received');
		$quantities_dispensed = $this -> input -> post('quantity_dispensed');
		$losses = $this -> input -> post('losses');
		$adjustments = $this -> input -> post('adjustments');
		$physical_count = $this -> input -> post('physical_count');
		$expiry_quantity = $this -> input -> post('expire_qty');
		$expiry_date = $this -> input -> post('expire_period');
		$out_of_stock = $this -> input -> post('out_of_stock');
		$resupply = $this -> input -> post('resupply');
		$commodities = $this -> input -> post('commodity');
		$regimens = $this -> input -> post('patient_regimens');
		$patient_numbers = $this -> input -> post('patient_numbers');
		$mos = $this -> input -> post('mos');
		$comments = $this -> input -> post('comments');
		$order_number = $this -> input -> post('order_number');
		$order_type = $this -> input -> post('order_type');
		//boolean to tell if we are editing the order
		$is_editing = false;
		if ($order_number != null) {
			$is_editing = true;
			$reporting_period = date('Y-m', strtotime($reporting_period));
			$start_date = $this -> input -> post("start_date");
			$end_date = $this -> input -> post("end_date");
			$period_start = $start_date;
			$period_end = $end_date;
		} else {
			$reporting_period = date('Y-m', strtotime($reporting_period));
			$start_date = $this -> input -> post("start_date");
			$end_date = $this -> input -> post("end_date");
			$period_start = $reporting_period . "-" . $start_date;
			$period_end = $reporting_period . "-" . $end_date;
		}
		$commodity_counter = 0;
		$regimen_counter = 0;
		$last_id = 0;
		$initial_order_number = 0;
		$this -> session -> set_userdata('msg_success', 'Order was Added');
		//Save the cdrr
		if ($is_editing) {
			//Retrieve the order being edited
			$order_object = Facility_Order::getOrder($order_number);
			$this -> session -> set_userdata('msg_success', 'Order was Updated');
			//Delete all items for that order
			$initial_order_number = $order_number;
			$order_number = md5($order_number . $facility);
			$old_commodities = Cdrr_Item::getOrderItems($order_number);
			$old_regimens = Maps_Item::getOrderItems($order_number);
			$old_comments = Order_Comment::getOrderComments($order_number);
			foreach ($old_commodities as $old_commodity) {
				$old_commodity -> delete();
			}
			foreach ($old_regimens as $old_regimen) {
				$old_regimen -> delete();
			}
			foreach ($old_comments as $old_comment) {
				$old_comment -> delete();
			}
		} else {
			$order_object = new Facility_Order();
			$order_object -> Created = $updated_on;
		}

		//status = 0 i.e. prepared
		$order_object -> Status = 0;
		$order_object -> Updated = $updated_on;
		//code = 0 i.e. fcdrr
		$order_object -> Code = $order_type;
		$order_object -> Period_Begin = $period_start;
		$order_object -> Period_End = $period_end;
		//Only for dcdrrs
		/*$order_object->Reports_Expected = 0;
		 $order_object->Reports_Actual = 0;*/
		$order_object -> Services = $services;
		$order_object -> Sponsors = $sponsors;
		$order_object -> Facility_Id = $facility;
		$order_object -> Central_Facility = $central_facility;
		if ($order_number == null) {
			$sql = "select max(id)as last from facility_order";
			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			$last_id = $results[0]['last'];
			$last_id++;
		} else {
			$last_id = $initial_order_number;
		}
		$unique_id = md5($last_id . $facility);
		$order_object -> Unique_Id = $unique_id;
		$order_object -> save();
		$order_id = $order_object -> id;
		if ($aggregated_orders) {
			foreach ($aggregated_orders as $aggregated_order) {
				$aggregated = new Aggregated_Order();
				$aggregated -> aggregated_order_id = $order_id;
				$aggregated -> child_order_id = $aggregated_order;
				$aggregated -> save();
			}

		}
		//Now save the comment that has been made
		if (strlen($comments) > 0) {
			$order_comment = new Order_Comment();
			$order_comment -> Order_Number = $unique_id;
			$order_comment -> Timestamp = date('U');
			$order_comment -> User = $user_id;
			$order_comment -> Comment = $comments;
			if ($order_number == null) {
				$sql = "select max(id)as last from order_comment";
				$query = $this -> db -> query($sql);
				$results = $query -> result_array();
				$last_id = $results[0]['last'];
				$last_id++;
			} else {
				$last_id = $initial_order_number;
			}
			$order_comment -> Unique_Id = md5($last_id . $facility);
			$order_comment -> save();

		}

		//Now save the cdrr items
		$commodity_counter = 0;

		if ($commodities != null) {
			if ($order_number == null) {
				$sql = "select max(id)as last from cdrr_item";
				$query = $this -> db -> query($sql);
				$results = $query -> result_array();
				$last_id = $results[0]['last'];
			} else {
				$last_id = $initial_order_number;
			}
			foreach ($commodities as $commodity) {
				//First check if any quantitites are required for resupply to avoid empty entries
				if ($resupply[$commodity_counter] > 0) {
					$cdrr_item = new Cdrr_Item();
					$cdrr_item -> Balance = $opening_balances[$commodity_counter];
					$cdrr_item -> Received = $quantities_received[$commodity_counter];
					$cdrr_item -> Dispensed_Units = $quantities_dispensed[$commodity_counter];
					//For fcdrr, packs are not used.
					//$cdrr_item->Dispensed_Packs = $opening_balances[$commodity_counter];
					$cdrr_item -> Losses = $losses[$commodity_counter];
					$cdrr_item -> Adjustments = $adjustments[$commodity_counter];
					$cdrr_item -> Count = $physical_count[$commodity_counter];
					$cdrr_item -> Resupply = $resupply[$commodity_counter];
					$cdrr_item -> Newresupply = $resupply[$commodity_counter];
					//The following not required for fcdrrs
					$cdrr_item->Aggr_Consumed = $expiry_quantity[$commodity_counter];
					$cdrr_item->Aggr_On_Hand = $expiry_date[$commodity_counter];
					// $cdrr_item->Publish = $opening_balances[$commodity_counter];*/
					$cdrr_item -> Cdrr_Id = $unique_id;
					$cdrr_item -> Drug_Id = $commodities[$commodity_counter];
					$sql = "select max(id)as last from cdrr_item";
					$query = $this -> db -> query($sql);
					$results = $query -> result_array();
					if ($order_number == null) {
						$last_id++;
					}
					$cdrr_item -> Unique_Id = md5($last_id . $facility);
					$cdrr_item -> save();
					//echo $cdrr_item -> id . "<br>";
				}
				$commodity_counter++;
			}
		}
		//Save the maps details
		$maps_id = $order_object -> id;
		if ($regimens != null) {
			if ($order_number == null) {
				$sql = "select max(id)as last from maps_item";
				$query = $this -> db -> query($sql);
				$results = $query -> result_array();
				$last_id = $results[0]['last'];
			} else {
				$last_id = $initial_order_number;
			}
			foreach ($regimens as $regimen) {
				//Check if any patient numbers have been reported for this regimen
				if ($patient_numbers[$regimen_counter] > 0) {
					echo $regimens[$regimen_counter] . "-" . $patient_numbers[$regimen_counter] . "<br/>";
					$maps_item = new Maps_Item();
					$maps_item -> Total = $patient_numbers[$regimen_counter];
					$maps_item -> Regimen_Id = $regimens[$regimen_counter];
					$maps_item -> Maps_Id = $unique_id;
					if ($order_number == null) {
						$last_id++;
					}
					$maps_item -> Unique_Id = md5($last_id . $facility);
					$maps_item -> save();
					//echo $maps_item -> id . "<br>";
				}
				$regimen_counter++;
			}
		}
		//var_dump($this -> input -> post("patient_numbers"));
		redirect("order_management/submitted_orders");

		/*$cdrr_sql = "INSERT INTO cdrr (status,created,updated,code,period_begin,period_end,comments,services,sponsors,delivery_note,facility_id)VALUES('prepared','$created_on','$updated_on','F-CDRR_units','$period_start','$period_end','','$services','$sponsors','','108'); select last_insert_id() as cdrr_id;";
		 echo $cdrr_sql;
		 $cdrr_item_sql = "";
		 //save the cdrr items
		 $commodity_counter = 0;
		 foreach ($commodities as $commodity) {
		 if ($resupply[$commodity_counter] > 0) {
		 //create the sql
		 $cdrr_id = "1";
		 $cdrr_item_sql .= "INSERT INTO cdrr_item (balance,received,dispensed_units,dispensed_packs,losses,adjustments,count,expiry_quant,expiry_date,out_of_stock,resupply,aggr_consumed,aggr_on_hand,publish,cdrr_id,drug_id)VALUES('$opening_balances[$commodity_counter]','$quantities_received[$commodity_counter]','$quantities_dispensed[$commodity_counter]','','$losses[$commodity_counter]','$adjustments[$commodity_counter]','$physical_count[$commodity_counter]','$expiry_quantity[$commodity_counter]','$expiry_date[$commodity_counter]','$out_of_stock[$commodity_counter]','$resupply[$commodity_counter]','','','0','$cdrr_id','$commodities[$commodity_counter]');";
		 }
		 $commodity_counter++;
		 }
		 echo $cdrr_item_sql;
		 //Make database connection
		 /*$connection = ssh2_connect('demo.kenyapharma.org', 22);
		 ssh2_auth_password($connection, 'ubuntu', 'Nb!23Q2([58L61D');
		 $tunnel = ssh2_tunnel($connection, 'demo.kenyapharma.org', 3306);
		 $db = mysqli_connect('demo.kenyapharma.org', 'demo', 'Ms#=T9F1@56446N', 'kenyapharma_demo', 3306) or die('Fail: ' . mysql_error());
		 */
		//Connection2
		/*$connection = ssh2_connect('demo.kenyapharma.org', '22');
		 if (ssh2_auth_password($connection, 'ubuntu', 'Nb!23Q2([58L61D')) { echo "Authentication Successful!\n";
		 } else { die('Authentication Failed...');
		 }
		 $command = 'echo "' . $cdrr_sql . '" | mysql -udemo -pMs#=T9F1@56446N kenyapharma_demo2';
		 //echo $command;
		 $stream = ssh2_exec($connection, $command);
		 stream_set_blocking($stream, true);
		 $cdrr_id = '';
		 while ($line = fgets($stream)) { flush();
		 if ($line + 0) {
		 $cdrr_id = $line;
		 }
		 }
		 echo "CDRR id ".$cdrr_id." sent to Kenya Pharma";

		 //get the inserted cdrr id
		 $cdrr_item_sql = "";
		 //save the cdrr items
		 foreach ($commodities as $commodity) {
		 if ($resupply[$commodity_counter] > 0) {
		 //create the sql
		 $cdrr_item_sql .= "INSERT INTO cdrr_item (balance,received,dispensed_units,dispensed_packs,losses,adjustments,count,expiry_quant,expiry_date,out_of_stock,resupply,aggr_consumed,aggr_on_hand,publish,cdrr_id,drug_id)VALUES('$opening_balances[$commodity_counter]','$quantities_received[$commodity_counter]','$quantities_dispensed[$commodity_counter]','','$losses[$commodity_counter]','$adjustments[$commodity_counter]','$physical_count[$commodity_counter]','$expiry_quantity[$commodity_counter]','$expiry_date[$commodity_counter]','$out_of_stock[$commodity_counter]','$resupply[$commodity_counter]','','','0','$cdrr_id','$commodities[$commodity_counter]');";
		 }
		 $commodity_counter++;
		 }
		 $command = 'echo "' . $cdrr_item_sql . '" | mysql -udemo -pMs#=T9F1@56446N kenyapharma_demo2';
		 ssh2_exec($connection, $command);

		 //save the maps
		 $maps_sql = "insert into maps (status,created,updated,code,period_begin,period_end,services,sponsors,facility_id) values ('prepared','$created_on','$updated_on','F-MAPS','$period_start','$period_end','$services','$sponsors','108'); select last_insert_id() as maps_id;";
		 //get the maps id
		 $command = 'echo "' . $maps_sql . '" | mysql -udemo -pMs#=T9F1@56446N kenyapharma_demo2';
		 //echo $command;
		 $stream = ssh2_exec($connection, $command);
		 stream_set_blocking($stream, true);
		 $maps_id = '';
		 while ($line = fgets($stream)) { flush();
		 if ($line + 0) {
		 $maps_id = $line;
		 }
		 }
		 echo "MAPS id ".$maps_id." sent to Kenya Pharma";
		 $maps_item_sql = "";
		 //save the maps items
		 foreach ($regimens as $regimen) {
		 if ($patient_numbers[$regimen_counter] > 0) {
		 $maps_item_sql .= "INSERT INTO maps_item(total,regimen_id,maps_id)VALUES('$patient_numbers[$regimen_counter]','$regimens[$regimen_counter]','$maps_id');";
		 }
		 $regimen_counter++;
		 }
		 $command = 'echo "' . $maps_item_sql . '" | mysql -udemo -pMs#=T9F1@56446N kenyapharma_demo2';
		 ssh2_exec($connection, $command);
		 */
	}

	public function combine_orders() {
		$facility_id = $this -> session -> userdata('facility');
		$this -> load -> database();
		$orders = $this -> input -> post("order");

		if (!$orders) {
			redirect("order_management/new_central_order");
			die();
		}
		$start_date = $this -> input -> post("start_date");
		$end_date = $this -> input -> post("end_date");

		$cdrr_totals = array();
		$maps_totals = array();
		$cdrr_portion = "cdrr_id = ";
		$maps_portion = "maps_id = ";
		$order_nos = "";
		$aggregated_order_ids = "";
		//Now save the orders in with the selected/new picking list
		$counter = 1;
		foreach ($orders as $order) {
			if ($counter != 1) {
				$order_nos .= ",";
			}
			$order_nos .= $order;
			$aggregated_order_ids .= "<input type='hidden' name='aggregated_order[]' value='" . $order . "' >";
			$results = Facility_Order::getOrder($order);
			$order = $results -> Unique_Id;
			//append the order id to the order string
			if (isset($orders[$counter])) {
				$cdrr_portion .= "'" . $order . "' or cdrr_id = ";
				$maps_portion .= "'" . $order . "' or maps_id = ";
			} else {
				$cdrr_portion .= "'" . $order . "' ";
				$maps_portion .= "'" . $order . "' ";
			}
			$counter++;
		}

		//generate the queries to retrieve the aggregated values
		$sql_cdrr = "select drug_id,sum(balance) as balance,sum(received) as received,sum(dispensed_units) as dispensed_units,sum(dispensed_packs) as dispensed_packs,sum(losses) as losses,sum(adjustments) as adjustments,sum(count) as count, sum(resupply) as resupply,sum(aggr_consumed) as aggr_consumed,aggr_on_hand from cdrr_item c where ($cdrr_portion) group by drug_id";
		$sql_maps = "select regimen_id,sum(total) as total from maps_item where $maps_portion group by regimen_id";
		$cdrr_results = $this -> db -> query($sql_cdrr) -> result_array();
		$maps_results = $this -> db -> query($sql_maps) -> result_array();
		//put the results in their respective arrays
		foreach ($cdrr_results as $cdrr_item) {
			$cdrr_totals[$cdrr_item['drug_id']] = $cdrr_item;
		}
		foreach ($maps_results as $maps_item) {
			$maps_totals[$maps_item['regimen_id']] = $maps_item;

		}

		$data['aggregated_order_ids'] = $aggregated_order_ids;
		$data['order_nos'] = $order_nos;
		$data['page_title'] = 'New Aggregated Facility Report';
		$data['page_title_1'] = "Aggregated Order Details";
		$data['order_details_page'] = "aggregated_orders";
		$data['facility_object'] = Facilities::getCodeFacility($facility_id);
		$data['cdrr_totals'] = $cdrr_totals;
		$data['maps_totals'] = $maps_totals;
		$data['start_date'] = $start_date;
		$data['end_date'] = $end_date;
		$data['content_view'] = "new_aggregated_order_v";
		$data['banner_text'] = "Aggregated Order";
		$data['hide_side_menu'] = 0;
		$data['commodities'] = Drugcode::getAllObjects($data['facility_object']['supplied_by']);
		$data['regimen_categories'] = Regimen_Category::getAll();
		$this -> base_params($data);
	}

	public function getPeriodDrugBalance($drug, $from, $to) {
		$sql = "select case when 1=1 then '$drug' end as drug,stock_in.*,sum(p.quantity) as total_dispensed from (select sum(ds.quantity) as total_received from drug_stock_movement ds left join transaction_type t on ds.transaction_type = t.id where drug = '$drug' and t.effect = '1' and transaction_date between '$from' and '$to') stock_in left join patient_visit p on p.drug_id = '$drug' and dispensing_date between '$from' and '$to'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		echo json_encode($results);
	}

	public function getPeriodDrugs($drug_id, $start_date, $end_date) {
		$prev_start = date("Y-m-d", strtotime("-1 month", strtotime($start_date)));
		$prev_end = date("Y-m-d", strtotime("-1 month", strtotime($end_date)));
		$period = 180;
		$sql = "SELECT main_temp.drug_id, main_temp.beginning_balance, sec_temp.stocks_qty, sec_temp.early_expiry 
		        FROM(SELECT $drug_id as drug_id,SUM(dst.balance) AS beginning_balance,temp_expire.stocks_qty,temp_expire.early_expiry 
		             FROM drug_stock_movement dst,(SELECT drug, batch_number, MAX( transaction_date ) AS trans_date 
		                                           FROM  `drug_stock_movement` 
		                                           WHERE transaction_date 
		                                           BETWEEN  '$prev_start' AND  '$prev_end' 
		                                           AND drug ='$drug_id'  
		                                           GROUP BY batch_number) AS temp,(SELECT d.id, SUM( dsb.balance ) AS stocks_qty, dsb.expiry_date AS early_expiry 
		                                                                           FROM drugcode d 
		                                                                           LEFT JOIN drug_unit u ON d.unit = u.id 
		                                                                           LEFT JOIN drug_stock_balance dsb ON d.id = dsb.drug_id 
		                                                                           WHERE DATEDIFF( dsb.expiry_date, CURDATE( ) ) <=  '$period' 
		                                                                           AND DATEDIFF( dsb.expiry_date, CURDATE( ) ) >=0 
		                                                                           AND d.enabled =1 
		                                                                           AND dsb.balance >0 
		                                                                           AND d.id =  '$drug_id' 
		                                                                           GROUP BY d.drug ORDER BY dsb.expiry_date ASC) as temp_expire 
		                                                                           WHERE dst.drug = temp.drug 
		                                                                           AND dst.drug = temp_expire.id 
		                                                                           AND dst.batch_number = temp.batch_number 
		                                                                           AND dst.transaction_date = temp.trans_date) AS main_temp 
		         LEFT JOIN (SELECT d.id, SUM( dsb.balance ) AS stocks_qty, dsb.expiry_date AS early_expiry 
		                    FROM drugcode d 
		                    LEFT JOIN drug_unit u ON d.unit = u.id 
		                    LEFT JOIN drug_stock_balance dsb ON d.id = dsb.drug_id 
		                    WHERE DATEDIFF( dsb.expiry_date, CURDATE( ) ) <=  '$period' 
		                    AND DATEDIFF( dsb.expiry_date, CURDATE( ) ) >=0 
		                    AND d.enabled =1 
		                    AND dsb.balance >0 
		                    AND d.id =  '$drug_id' 
		                    GROUP BY d.drug 
		                    ORDER BY dsb.expiry_date ASC) AS sec_temp ON sec_temp.id = main_temp.drug_id";

		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$row = array();
		$row["drug"] = (int)$drug_id;
		if ($results) {
			if ($results[0]['beginning_balance'] != null) {
				$row["beginning_balance"] = (int)$results[0]['beginning_balance'];
				$row["stock_to_expire"] = (int)$results[0]['stocks_qty'];
				$row["early_expiry"] =  (int)$results[0]['early_expiry'];
			} else {
				$row["beginning_balance"] = 0;
				if((int)$results[0]['stocks_qty']>0){
					$row["early_expiry"] = date('M-Y',strtotime($results[0]['early_expiry']));
				}else{
					$row["early_expiry"] = "-";
				}
				$row["stock_to_expire"] = (int)$results[0]['stocks_qty'];
				
			}
		} else {
			$row["beginning_balance"] = 0;
			$row["stock_to_expire"] = 0;
			$row["early_expiry"] = "-";
		}
		$start_date = date('Y-m-d', strtotime($start_date));
		$end_date = date('Y-m-d', strtotime($end_date));
		$sql = "SELECT trans.name, trans.id, trans.effect, dsm.in_total, dsm.out_total FROM (SELECT id, name, effect FROM transaction_type WHERE name LIKE  '%received%' OR name LIKE  '%adjustment%' OR name LIKE  '%return%' OR name LIKE  '%dispense%' OR name LIKE  '%issue%' OR name LIKE  '%loss%' OR name LIKE  '%ajustment%' OR name LIKE  '%physical%count%' OR name LIKE  '%starting%stock%') AS trans LEFT JOIN (SELECT transaction_type, SUM( quantity ) AS in_total, SUM( quantity_out ) AS out_total FROM drug_stock_movement WHERE transaction_date BETWEEN  '$start_date' AND  '$end_date' AND drug =  '$drug_id'  GROUP BY transaction_type) AS dsm ON trans.id = dsm.transaction_type GROUP BY trans.name";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			foreach ($results as $result) {
				$effect = $result['effect'];
				$trans_name = str_replace(array(" ", "(-)", "(+)", "/"), array("_", "_", "plus", "_"), $result['name']);
				if ($effect == 1) {
					if ($result['in_total'] != null) {
						$total = (int)$result['in_total'];
					} else {
						$total = 0;
					}
				} else {
					if ($result['out_total'] != null) {
						$total = (int)$result['out_total'];
					} else {
						$total = 0;
					}
				}
				$row[$trans_name] = $total;
			}
		}
		echo json_encode($row);
	}

	public function getPeriodRegimenPatients($from, $to) {
		$facility_code = $this -> session -> userdata("facility");
		$sql = "SELECT count(*) as patients, r.regimen_desc,r.regimen_code,p.current_regimen as regimen FROM patient p,regimen r WHERE p.date_enrolled<='$to' AND p.current_status=1 AND r.id=p.current_regimen AND p.facility_code='$facility_code' AND p.current_regimen !=0 AND p.current_regimen !='' AND p.current_status !='' AND p.current_status !=0 GROUP BY p.current_regimen ORDER BY r.regimen_code ASC";
		//$sql = "SELECT regimen, COUNT( DISTINCT patient_id ) AS patients FROM patient_visit WHERE dispensing_date BETWEEN  '$from' AND  '$to' GROUP BY regimen";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		echo json_encode($results);
	}

}
?>