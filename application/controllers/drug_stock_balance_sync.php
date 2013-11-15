<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Drug_stock_balance_sync extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this->load->database();
		ini_set("max_execution_time", "1000000");
	}
	public function index() {
		//$this->synch_balance();
		$data['banner_text']="Syncronization";
		$data['content_view'] = "sync_drug_balance_v";	
		$data['title'] = "Web ADT";	
		$this->load->view("template",$data);
	}
	
	public function getDrugs(){
		if($this->input->post("check_if_malicious_posted")){
			$getTot_Drugs=$this->db->query("select d.id,drug from drugcode d where d.Enabled=1");
			$drugs=$getTot_Drugs->result_array();
			$data['drugs']=$drugs;
			$data['count']=count($drugs);
			echo json_encode($data);
		}
		
	}
	
	public function synch_balance($stock_type="2"){
		$stock_type=$this->input->post("stock_type");
		$drug_id=$this->input->post("drug_id");
		$not_saved=0;
		$facility_code = $this -> session -> userdata('facility');
		$stock_param="";
		//Store
		if($stock_type=='1'){
			$stock_param=" AND (source='".$facility_code."' OR destination='".$facility_code."') AND source!=destination ";
		}
		//Pharmacy
		else if($stock_type=='2'){
			$stock_param=" AND (source=destination) AND(source='".$facility_code."') ";
		}
		
			$count_it=0;
			$stock_status=0;
			//Get all the batches
			$get_batches_sql="SELECT d.batch_number AS batch,expiry_date FROM drug_stock_movement d WHERE d.drug =  '" .$drug_id. "' AND facility='" .$facility_code. "' ".$stock_param." GROUP BY d.batch_number";
			$bacthes=$this -> db -> query($get_batches_sql);
			$batch_results=$bacthes -> result_array();
			foreach ($batch_results as $key => $batch_row) {
				//echo $count_it."<br>";
				//Query to check if batch has had a physical count
				$batch_no = $batch_row['batch'];
				$expiry_date=$batch_row['expiry_date'];
				//Get the latest physical count
				$initial_stock_sql = "SELECT d.quantity AS Initial_stock, d.transaction_date AS transaction_date, '" .$batch_no. "' AS batch,t.name as trans_name FROM drug_stock_movement d LEFT JOIN transaction_type t ON t.id=d.transaction_type WHERE d.drug =  '" .$drug_id. "' AND (t.name LIKE '%physical count%' OR t.name LIKE '%stock count%') AND facility='" .$facility_code. "' ".$stock_param." AND d.batch_number =  '" .$batch_no. "' ORDER BY d.id DESC LIMIT 1";
				//Old query
				//$initial_stock_sql = "SELECT SUM( d.quantity ) AS Initial_stock, d.transaction_date AS transaction_date, '" .$batch_no. "' AS batch,t.name as trans_name FROM drug_stock_movement d LEFT JOIN transaction_type t ON t.id=d.transaction_type WHERE d.drug =  '" .$drug_id. "' AND (t.name LIKE '%physical count%' OR t.name LIKE '%stock count%') AND facility='" .$facility_code. "' ".$stock_param." AND d.batch_number =  '" .$batch_no. "'";
				$bacthes_initial_stock=$this -> db -> query($initial_stock_sql);
				$batch_initial_stock=$bacthes_initial_stock -> result_array();
				$x=count($batch_initial_stock);
				foreach ($batch_initial_stock as $key => $value2) {
					//If initial stock is not null
					if($value2['Initial_stock']!=null){
						//Get the balance for that batch
						//Old query
						//$batch_stock_balance_sql = "SELECT (SUM( ds.quantity ) - SUM( ds.quantity_out )) AS stock_levels, ds.batch_number FROM drug_stock_movement ds WHERE ds.transaction_date BETWEEN  '" .$value2['transaction_date']. "' AND curdate() AND facility='" .$facility_code. "' ".$stock_param." AND ds.drug ='" .$drug_id. "'  AND ds.batch_number ='" .$value2['batch']. "'";
						$batch_stock_balance_sql = "SELECT ds.quantity AS stock_levels, ds.batch_number FROM drug_stock_movement ds WHERE ds.transaction_date BETWEEN  '" .$value2['transaction_date']. "' AND curdate() AND facility='" .$facility_code. "' ".$stock_param." AND ds.drug ='" .$drug_id. "'  AND ds.batch_number ='" .$value2['batch']. "' ORDER BY ds.id DESC LIMIT 1";
						$bacthes_balance=$this -> db -> query($batch_stock_balance_sql);
						$batch_balance_array=$bacthes_balance -> result_array();
						foreach ($batch_balance_array as $key => $value3) {
							//Save balance in drug_stock_balance table
							if($value3['stock_levels']>0){
								$batch_balance_save=$value3['stock_levels'];
							}
							else{
								$batch_balance_save=0;
							}
							$batch_number_save=$batch_no;
							$drug_id_save=$drug_id;
							$expiry_date_save=$expiry_date;
							$insert_balance_sql="INSERT INTO drug_stock_balance(drug_id,batch_number,stock_type,expiry_date,facility_code,balance) VALUES('".$drug_id_save."','".$batch_number_save."','".$stock_type."','".$expiry_date_save."','".$facility_code."','".$batch_balance_save."') ON DUPLICATE KEY UPDATE balance='".$batch_balance_save."'";
							$q=$this -> db -> query($insert_balance_sql);
							if(!$q){
								$not_saved++;
							}
						}
					}
					else{
						//Get the balance for that batch
						$batch_stock_balance_sql = "SELECT (SUM( ds.quantity ) - SUM( ds.quantity_out ) ) AS stock_levels, ds.batch_number FROM drug_stock_movement ds WHERE ds.drug =  '" .$drug_id. "' AND ds.expiry_date > curdate() AND facility='" .$facility_code. "' ".$stock_param." AND ds.batch_number='" .$value2['batch']. "'";
						$bacthes_balance=$this -> db -> query($batch_stock_balance_sql);
						$batch_balance_array=$bacthes_balance -> result_array();
						foreach ($batch_balance_array as $key => $value3) {
							//Store balance in drug_stock_balance table
							$batch_balance_save=$value3['stock_levels'];
							if($value3['stock_levels']>0){
								$batch_balance_save=$value3['stock_levels'];
							}
							else{
								$batch_balance_save=0;
							}
							$batch_number_save=$batch_no;
							$drug_id_save=$drug_id;
							$expiry_date_save=$expiry_date;
							$insert_balance_sql="INSERT INTO drug_stock_balance(drug_id,batch_number,stock_type,expiry_date,facility_code,balance) VALUES('".$drug_id_save."','".$batch_number_save."','".$stock_type."','".$expiry_date_save."','".$facility_code."','".$batch_balance_save."') ON DUPLICATE KEY UPDATE balance='".$batch_balance_save."'";
							$q=$this -> db -> query($insert_balance_sql);
							if(!$q){
								$not_saved++;
							}
						}
					}
				}
			}

	}
	
	//Synchronizes drug stock moment balance
	public function drug_stock_movement_balance(){
		$stock_type=$this->input->post("stock_type");
		$drug_id=$this->input->post("drug_id");
		$facility_code = $this -> session -> userdata('facility');
		$stock_param="";
		//Store
		if($stock_type=='1'){
			$stock_param=" AND (source='".$facility_code."' OR destination='".$facility_code."') AND source!=destination ";
		}
		//Pharmacy
		else if($stock_type=='2'){
			$stock_param=" AND (source=destination) AND(source='".$facility_code."') ";
		}
		
		$stock_status=0;
		//Get all the batches
		$get_batches_sql="SELECT DISTINCT d.batch_number AS batch,expiry_date FROM drug_stock_movement d WHERE d.drug =  '" .$drug_id. "' AND facility='" .$facility_code. "' ".$stock_param." GROUP BY d.batch_number";
		$batches=$this -> db -> query($get_batches_sql);
		$batch_results=$batches -> result_array();
		foreach ($batch_results as $key => $batch_row) {
			$batch_number=$batch_row['batch'];
			//get drug balances
			$get_balances_sql="SELECT dsm.ID,dsm.TRANSACTION_DATE,dsm.TRANSACTION_TYPE,dsm.QUANTITY,dsm.QUANTITY_OUT,t.name as transaction_name,IF((t.name LIKE '%physical count%' OR t.name LIKE '%starting stock%') ,@BALANCE:=@BALANCE-@BALANCE+QUANTITY,@BALANCE:=@BALANCE+QUANTITY-QUANTITY_OUT) as balance FROM drug_stock_movement dsm LEFT JOIN transaction_type t ON t.id=dsm.TRANSACTION_TYPE ,(SELECT @BALANCE:=0) as DUMMY WHERE drug='" .$drug_id. "' AND batch_number='".$batch_number."' AND facility='" .$facility_code. "' ".$stock_param." ORDER BY ID ASC";
			$balances=$this -> db -> query($get_balances_sql);
			$balance_results=$balances -> result_array();
			//Loop through the array to get the actual values
			foreach ($balance_results as $key => $balance) {
				$bal=$balance['balance'];
				if($bal<0){
					$bal=0;
				}
				//Update the balance column 
				$update_balance_sql="UPDATE drug_stock_movement SET balance='".$bal."' WHERE id=".$balance['ID'];
				$balances=$this -> db -> query($update_balance_sql);
				//$balance_results=$balances -> result_array();
				
			}
		}
		
	}
	
	//Get drug details for drug consumpition balance table
	public function get_drug_details_cons(){
		$check=$this->input->post('check_if_malicious_posted');
		if($check!=""){
			$get_consumption=$this->db->query("SELECT drug_id,'2',DATE_FORMAT(dispensing_date,'%Y-%m-01') as period, SUM( quantity ) AS total FROM  `patient_visit`  GROUP BY drug_id,period ORDER BY  `patient_visit`.`drug_id`");
			$get_cons_array=$get_consumption->result_array();
			$data['drugs']=$get_cons_array;
			$data['count']=count($get_cons_array);
			echo json_encode($data);
		}
		
	}
	
	//Drug consumption balance
	public function drug_consumption(){
		$facility_code = $this -> session -> userdata('facility');
		$drug_id=$this->input->post('drug_id');
		$period=$this->input->post('period');
		$total=$this->input->post('total');
		$get_consumption=$this->db->query("INSERT INTO drug_cons_balance(drug_id,stock_type,period,facility,amount) VALUES('$drug_id','2','$period','$facility_code','$total') ON DUPLICATE KEY UPDATE amount='$total',facility='$facility_code'");
		
	}

	//Update balances in drug_stock_balance
	public function drug_stock_balance_update(){
		//SELECT QUANTITY ,QUANTITY_OUT,@BALANCE:=@BALANCE+QUANTITY-QUANTITY_OUT FROM adt.drug_stock_movement ,(SELECT @BALANCE:=0) as DUMMY WHERE transaction_type!='11' AND drug='155' AND batch_number='NVSA12022-' AND source='13050' AND source=destination ORDER BY ID ASC; 
		//SELECT TRANSACTION_TYPE,QUANTITY ,QUANTITY_OUT,IF(TRANSACTION_TYPE='11',@BALANCE:=@BALANCE-@BALANCE+QUANTITY,@BALANCE:=@BALANCE+QUANTITY-QUANTITY_OUT) FROM adt.drug_stock_movement ,(SELECT @BALANCE:=0) as DUMMY WHERE drug='155' AND batch_number='NVSA12022-' AND source='13050' AND source=destination ORDER BY ID ASC; 
		
	}

}
?>