<?php
class Facility_Order extends Doctrine_Record {

	public function setTableDefinition() {
		/*
		 * For the Statuses
		 * 0 - Pending
		 * 1 - Approved
		 * 2 - Declined
		 * 3 - Dispatched
		 *
		 * For the codes
		 * 0 - Central facility order
		 * 1 - Aggregated facility order
		 * 2 - Satellite facility order
		 */

		$this -> hasColumn('Status', 'varchar', 10);
		$this -> hasColumn('Created', 'varchar', 32);
		$this -> hasColumn('Updated', 'varchar', 32);
		$this -> hasColumn('Code', 'varchar', 10);
		$this -> hasColumn('Period_Begin', 'varchar', 10);
		$this -> hasColumn('Period_End', 'varchar', 10);
		$this -> hasColumn('Comments', 'text');
		$this -> hasColumn('Reports_Expected', 'varchar', 10);
		$this -> hasColumn('Reports_Actual', 'varchar', 10);
		$this -> hasColumn('Services', 'varchar', 10);
		$this -> hasColumn('Sponsors', 'varchar', 10);
		$this -> hasColumn('Delivery_Note', 'varchar', 10);
		$this -> hasColumn('Order_Id', 'varchar', 10);
		$this -> hasColumn('Facility_Id', 'varchar', 10);
		$this -> hasColumn('Picking_List_Id', 'varchar', 10);
		$this -> hasColumn('Central_Facility', 'varchar', 10);
		$this -> hasColumn('Unique_Id', 'varchar','150');
		$this -> hasColumn('Is_Uploaded', 'int','5');
	}//end setTableDefinition

	public function setUp() {
		$this -> setTableName('facility_order');
		$this -> hasOne('Facilities as Facility_Object', array('local' => 'Facility_Id', 'foreign' => 'facilitycode'));
		$this -> hasMany('Cdrr_Item as Commodity_Objects', array('local' => 'id', 'foreign' => 'Cdrr_Id'));
	}//end setUp

	public static function getTotalNumber($status) {
		$query = Doctrine_Query::create() -> select("COUNT(*) as Total_Orders") -> from("Facility_Order") -> where("Status = '$status' and Code = '1'");
		$count = $query -> execute();
		return $count[0] -> Total_Orders;
	}

	public function getPagedOrders($offset, $items, $status) {
		$query = Doctrine_Query::create() -> select("*") -> from("Facility_Order") -> orderBy("abs(id) desc") -> where("Status = '$status' and Code = '1'") -> offset($offset) -> limit($items);
		$orders = $query -> execute();
		return $orders;
	}

	public static function getTotalFacilityNumber($status, $facility) {
		$query = Doctrine_Query::create() -> select("COUNT(*) as Total_Orders") -> from("Facility_Order") -> where("Status = '$status' and Central_Facility = '$facility'");
		$count = $query -> execute();
		return $count[0] -> Total_Orders;
	}

	public function getPagedFacilityOrders($offset, $items, $status, $facility) {
		$query = Doctrine_Query::create() -> select("*") -> from("Facility_Order") -> orderBy("abs(id) desc") -> where("Status = '$status' and (Facility_Id = '$facility' or Central_Facility = '$facility')")-> offset($offset);
		$orders = $query -> execute();
		return $orders;
	}

	public static function getOrder($order) {
		$query = Doctrine_Query::create() -> select("*") -> from("Facility_Order") -> where("id = '$order'");
		$order_object = $query -> execute();
		return $order_object[0];
	}

	public static function getSatelliteOrders($period_start, $period_end, $central_facility, $status) {
		$query = Doctrine_Query::create() -> select("*") -> from("Facility_Order") -> where("Central_Facility = '$central_facility' and Status = '$status' and Period_Begin = '$period_start' and Period_End = '$period_end'");
		$orders = $query -> execute();
		return $orders;
	}
	
	public function getPickingList($status='3', $facility) {
		$query = Doctrine_Query::create() -> select("*") -> from("Facility_Order") ->  where("Status = '$status' and Central_Facility = '$facility'");
		$orders = $query -> execute();
		return $orders;
	}

}//end class
?>