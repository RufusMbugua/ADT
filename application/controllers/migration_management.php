<?php
class Migration_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$data = array();
		$this -> load -> database();
		$this -> load -> library('encrypt');
	}

	public function index() {
		$this -> migration_interface();
	}

	public function migration_interface() {
		$data['content_view'] = "migration_v";
		$data['banner_text'] = "Data Migration";
		$tables = array('Drug Table' => 'tblARVDrugStockMain', 'Patient Status Table' => 'tblCurrentStatus', 'Dose Table' => 'tblDose', 'Drug in Regimen Table' => 'tblDrugsInRegimen', 'Generic Name Table' => 'tblGenericName', 'Indication Table' => 'tblIndication', 'Regimen Change Reason Table' => 'tblReasonforChange', 'Regimen Table' => 'tblRegimen', 'Regimen Category Table' => 'tblRegimenCategory', 'Users Table' => 'tblSecurity', 'Patient Table' => 'tblARTPatientMasterInformation', 'Transaction Type Table' => 'tblStockTransactionType', 'Type of Service Table' => 'tblTypeOfService', 'Type of Visit Table' => 'tblVisitTransaction', 'Patient Source Table' => 'tblSourceOfClient', 'Patient Transactions Table' => 'tblARTPatientTransactions', 'Drug Transactions Table' => 'tblARVDrugStockTransactions');
		$sql = "SELECT * FROM information_schema.schemata where DEFAULT_COLLATION_NAME NOT LIKE '%phpmyadmin%' AND DEFAULT_COLLATION_NAME NOT LIKE '%information_schema%' AND DEFAULT_COLLATION_NAME NOT LIKE '%performance_schema%'";
		$query = $this -> db -> query($sql);
		$data['db_tables'] = $query -> result_array();
		$data['tables'] = $tables;
		$data['hide_side_menu'] = 1;
		$data['actual_page'] = 'webADT Migration';
		$this -> base_params($data);
	}

	public function simplemigrate($dbname, $tablename, $targetname, $offset = 0) {
		$facility = $this -> session -> userdata('facility');
		$message = "";
		$appendsql = "LIMIT $offset,18446744073709551615";
		$sql = "";
		if ($targetname == 'drugcode') {
			$sql = "INSERT IGNORE INTO drugcode(drug,pack_size,unit,generic_name,safety_quantity,comment,supported_by,dose,duration,quantity,tb_drug,drug_in_use,supplied)SELECT arvdrugsid,packsizes,unit,genericname,saftystock,comment,supportedby,stddose,stdduration,stdqty,IF(tbdrug=0,'F','T')as tbdrug,IF(inuse=0,'F','T') as inuse,'1' from $dbname.tblarvdrugstockmain $appendsql;";
			$this -> db -> query($sql);
			$sql = "update drugcode dc,drug_unit du SET dc.unit=du.id WHERE dc.unit=du.Name";
		} else if ($targetname == 'patient_status') {
			$sql .= "INSERT IGNORE INTO patient_status(id,Name,Active)SELECT currentstatusid,currentstatus,'1' FROM $dbname.tblcurrentstatus WHERE currentstatus is not null $appendsql;";
		} else if ($targetname == 'dose') {
			$sql .= "INSERT IGNORE INTO dose(Name,value,frequency,Active)SELECT dose,value,frequency ,'1' FROM $dbname.tbldose $appendsql;";
		} else if ($targetname == 'generic_name') {
			$sql .= "INSERT IGNORE INTO generic_name(id,name,active)SELECT genid,genericname,'1' FROM $dbname.tblgenericname WHERE genericname is not null $appendsql;";
		} else if ($targetname == 'opportunistic_infection') {
			$sql .= "INSERT IGNORE INTO opportunistic_infection(name,indication,active)SELECT indicationname,indicationcode,'1'  FROM $dbname.tblindication $appendsql;";
		} else if ($targetname == 'regimen_change_purpose') {
			$sql .= "INSERT IGNORE INTO regimen_change_purpose(id,name,active)SELECT  reasonforchangeid,reasonforchange,'1'  FROM $dbname.tblreasonforchange  WHERE reasonforchange is not null $appendsql;";
		} else if ($targetname == 'regimen_category') {
			$sql .= "INSERT IGNORE INTO regimen_category(id,Name,Active)SELECT categoryid,categoryname,'1' FROM $dbname.tblregimencategory $appendsql;";
		} else if ($targetname == 'regimen_service_type') {
			$sql .= "INSERT IGNORE INTO regimen_service_type(id,name,active)SELECT typeofserviceid,typeofservice,'1' FROM $dbname.tbltypeofservice $appendsql;";
		} else if ($targetname == 'regimen') {
			$sql .= "INSERT IGNORE INTO regimen(regimen_code,regimen_desc,line,remarks,category,type_of_service,enabled)SELECT regimencode,regimen,line,remarks,category,typeoservice,IF(`show`=0,'0','1') as active FROM $dbname.tblregimen $appendsql;";
			$this -> db -> query($sql);
			$sql = "UPDATE `regimen` SET `enabled`='0' WHERE `regimen_desc`='';";
		} else if ($targetname == 'regimen_drug') {
			$sql .= "INSERT IGNORE INTO regimen_drug(regimen,drugcode,active)SELECT regimencode,combinations,'1' FROM $dbname.tbldrugsinregimen WHERE combinations is not null $appendsql;";
			$this -> db -> query($sql);
			$sql = "UPDATE regimen_drug rd,regimen r,drugcode d SET rd.regimen=r.id,rd.drugcode=d.id WHERE rd.regimen=r.regimen_code AND rd.drugcode=d.drug;";
		} else if ($targetname == 'users') {
			$key = $this -> encrypt -> get_key();
			$today = date('Y-m-d H:i:s');
			$sql .= "INSERT IGNORE INTO users(id,Name,Username,Password,Access_Level,Facility_Code,Active,Created_By,Time_Created)VALUES('1','System Admin','admin',md5(concat('$key','admin')),'1','$facility','1','1','$today')";
			$this -> db -> query($sql);
			$sql = "INSERT IGNORE INTO users(Name,Username,Password,Access_Level,Facility_Code,Active,Created_By,Time_Created)SELECT name,userid,md5(concat('$key',password)) as password,IF(UCASE(authoritylevel)='USER','2','3')  as authoritylevel,'$facility','1','1','$today' FROM $dbname.tblsecurity $appendsql;";
		} else if ($targetname == 'patient_source') {
			$sql .= "INSERT IGNORE INTO patient_source(id,name,active)SELECT sourceid,sourceofclient,'1' FROM $dbname.tblsourceofclient WHERE sourceofclient is not null $appendsql;";
		} else if ($targetname == 'transaction_type') {
			$sql = "INSERT IGNORE INTO transaction_type(id,name,`desc`,active)SELECT transactiontype,transactiondescription,reporttitle,'0' FROM $dbname.tblstocktransactiontype $appendsql;";
			$this -> db -> query($sql);
			$sql = "update `transaction_type` set effect='1' WHERE name like '%Starting%' or name like '%+%' or name like '%Forward%' or name like '%Received%'";
		} else if ($targetname == 'visit_purpose') {
			$sql .= "INSERT IGNORE INTO visit_purpose(id,name,active) SELECT transactioncode,visittranname,'1'  FROM $dbname.tblvisittransaction $appendsql;";
		} else if ($targetname == 'patient') {
			$sql = "INSERT IGNORE INTO patient_appointment(`patient`,`appointment`,`facility`)select artid,STR_TO_DATE(dateofnextappointment,'%Y-%m-%d'),'$facility' FROM $dbname.tblartpatientmasterinformation $appendsql;";
			$this -> db -> query($sql);
			$sql = "select count(*) as total from $tablename";
			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			$last_index = $results[0]['total'];
			$this -> updatelog("patient_appointment", $last_index);
			$sql = "update `$dbname.tblartpatientmasterinformation` SET dateofbirth=DATE_SUB(datetherapystarted, INTERVAL ncurrentage YEAR ) WHERE dateofbirth is null;";
			$query = $this -> db -> query($sql);
			$sql = "update `$dbname.tblartpatientmasterinformation`,regimen r SET currentregimen=r.id WHERE currentregimen=r.regimen_code;";
			$query = $this -> db -> query($sql);
			$sql = "update `$dbname.tblartpatientmasterinformation`,regimen r SET regimenstarted=r.id WHERE regimenstarted=r.regimen_code;";
			$query = $this -> db -> query($sql);
			$sql = "INSERT IGNORE INTO patient(`patient_number_ccc`,`first_name`,`last_name`,`gender`,`pregnant`,`date_enrolled`,`start_weight`,`supported_by`,`other_illnesses`,`adr`,`other_drugs`,`service`,`nextappointment`,`current_status`,`current_regimen`,`start_regimen`,`physical`,`weight`,`start_bsa`,`sa`,`start_height`,`height`,`source`,`tb`,`start_regimen_date`,`status_change_date`,`other_name`,`dob`,`pob`,`phone`,`alternate`,`smoke`,`alcohol`,`transfer_from`,`facility_code`)select artid,firstname,surname,IF(UCASE(sex)='MALE','1','2'),IF(pregnant=0,'0','1'),STR_TO_DATE(datetherapystarted, '%Y-%m-%d'),weightonstart,clientsupportedby,otherdeaseconditions,adrorsideeffects,otherdrugs,typeofservice,STR_TO_DATE(dateofnextappointment, '%Y-%m-%d'),currentstatus,currentregimen,regimenstarted,address,currentweight, startbsa,currentbsa,startheight,currentheight,sourceofclient,IF(tb=0,'0','1'),STR_TO_DATE(datestartedonart, '%Y-%m-%d'),STR_TO_DATE(datechangedstatus, '%Y-%m-%d'),lastname,STR_TO_DATE(dateofbirth,'%Y-%m-%d'),placeofbirth, patientcellphone,alternatecontact,IF(patientsmoke=0,'0','1'),IF(patientdrinkalcohol=0,'0','1'),transferfrom,'$facility' FROM $dbname.tblartpatientmasterinformation $appendsql;";

		}
		$this -> db -> query($sql);
		$message = "<br/>Data From <b>$tablename</b> Migrated to <b>$targetname</b> table";
		$sql = "select count(*) as total from $dbname.$tablename";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$last_index = $results[0]['total'];
		$this -> updatelog($targetname, $last_index);
		echo $message;
	}

	public function advancedmigrate($dbname, $tablename, $targetname, $offset = 0, $limit = 10000) {
		$facility = $this -> session -> userdata('facility');
		$message = "";
		$sql = "";
		if ($tablename == 'tblARTPatientTransactions') {
			$appendsql = "WHERE patienttranno >=$offset LIMIT $limit";
			$mainsql = "SELECT tp.artid,STR_TO_DATE(tp.dateofvisit, '%Y-%m-%d'),d.id,tp.brandname,tp.transactioncode,tp.arvqty,tp.dose,tp.duration,r.id,r1.id,tp.comment,tp.operator,tp.indication,tp.weight,tp.pillcount,tp.pillcount,tp.adherence,tp.reasonsforchange,tp.batchno,'$facility' FROM $dbname.tblartpatienttransactions tp LEFT JOIN drugcode d ON tp.drugname=d.drug LEFT JOIN regimen r ON tp.regimen=r.regimen_code LEFT JOIN regimen r1 ON tp.lastregimen=r1.regimen_code $appendsql;";
			$minsql = "SELECT temp.patienttranno as max FROM (SELECT patienttranno FROM $dbname.tblartpatienttransactions $appendsql) as temp ORDER BY temp.patienttranno desc LIMIT 1";
			$query = $this -> db -> query($minsql);
			$results = $query -> result_array();
			$last_index = $results[0]['max'];
			$thesql = "SELECT patienttranno FROM $dbname.tblartpatienttransactions WHERE patienttranno <='$last_index';";
			$sql .= "INSERT INTO patient_visit(patient_id,dispensing_date,drug_id,brand,visit_purpose,quantity,dose,duration,regimen,last_regimen,comment,user,indication,current_weight,pill_count,months_of_stock,adherence,regimen_change_reason,batch_number,facility)$mainsql";
		} else if ($tablename == 'tblARVDrugStockTransactions') {
			$appendsql = "WHERE stocktranno >=$offset LIMIT $limit";
			$mainsql = "SELECT d.id,STR_TO_DATE(trandate, '%Y-%m-%d'),reforderno,batchno,transactiontype,'$facility','$facility',STR_TO_DATE(expirydate, '%Y-%m-%d'),npacks,unitcost,IF(t.effect='0',qty,'0'),IF(t.effect='1',qty,'0'),amount,remarks,operator,'$facility' FROM $dbname.tblarvdrugstocktransactions td LEFT JOIN drugcode d ON td.arvdrugsid=d.drug LEFT JOIN transaction_type t ON td.transactiontype=t.id $appendsql;";
			$minsql = "SELECT temp.stocktranno as max FROM (SELECT stocktranno FROM $dbname.tblarvdrugstocktransactions $appendsql) as temp ORDER BY temp.stocktranno desc LIMIT 1";
			$query = $this -> db -> query($minsql);
			$results = $query -> result_array();
			$last_index = $results[0]['max'];
			$thesql = "SELECT stocktranno FROM $dbname.tblarvdrugstocktransactions WHERE stocktranno <='$last_index';";
			$sql .= "INSERT INTO drug_stock_movement(drug,transaction_date,order_number,batch_number,transaction_type,source,destination,expiry_date,packs,unit_cost,quantity,quantity_out,amount,remarks,operator,facility)$mainsql";
		}
		$this -> db -> query($sql);
		$query = $this -> db -> query($thesql);
		$answer = $query -> num_rows();
		$this -> updatelog($targetname, $last_index, $answer);
		//Main Store Received
		$sql = "UPDATE drug_stock_movement dsm LEFT JOIN transaction_type t ON t.id=dsm.transaction_type SET source_destination=(select id from drug_source des where des.name LIKE '%kenya pharma%' OR des.name LIKE '%kemsa%' limit 1) WHERE t.name LIKE '%received%' AND dsm.source!=dsm.destination";
		$this -> db -> query($sql);
		//Pharmacy Received
		$sql = "UPDATE drug_stock_movement dsm LEFT JOIN transaction_type t ON t.id=dsm.transaction_type SET source_destination=(select id from drug_source des where des.name LIKE '%store%' limit 1) WHERE t.name LIKE '%received%' AND dsm.source=dsm.destination and dsm.source!=''";
		$this -> db -> query($sql);
		//Pharmacy issued(Outpatient)
		$sql = "UPDATE drug_stock_movement dsm LEFT JOIN transaction_type t ON t.id=dsm.transaction_type SET source_destination=(select id from drug_destination des where des.name LIKE '%pharmacy%' limit 1) WHERE t.name LIKE '%issued%' AND dsm.source=dsm.destination and dsm.source!=''";
		$this -> db -> query($sql);
		//Main Store Issued
		$sql = "UPDATE drug_stock_movement dsm LEFT JOIN transaction_type t ON t.id=dsm.transaction_type SET source_destination=(select id from drug_destination des where des.name LIKE '%pharmacy%' limit 1) WHERE t.name LIKE '%issued%' AND dsm.source!=dsm.destination";
		$this -> db -> query($sql);
		echo $answer . "," . $last_index;
	}

	public function countRecords($dbname, $tablename) {
		$sql = "Select count(*) as total from $dbname.$tablename";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		echo $results[0]['total'];

	}

	public function checklog($tablename) {
		$last_index = 0;
		$sql = "select * from migration_log where source='$tablename'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($tablename == "patient_visit" || $tablename == "drug_stock_movement") {
			$last_index = $results[0]['count'] . "," . $results[0]['last_index'];
		} else {
			$last_index = $results[0]['last_index'];
		}

		echo $last_index;
	}

	public function updatelog($tablename, $last_index, $count = '0') {
		$sql = "select * from migration_log where source='$tablename'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($tablename == "patient_visit" || $tablename == "drug_stock_movement") {
			if ($results) {
				//update
				$sql = "update migration_log set last_index='$last_index',count='$count' where source='$tablename'";
			} else {
				//insert
				$sql = "insert into migration_log(source,last_index,count)VALUES('$tablename','$last_index','$count')";
			}
		} else {
			if ($results) {
				//update
				$sql = "update migration_log set last_index='$last_index' where source='$tablename'";
			} else {
				//insert
				$sql = "insert into migration_log(source,last_index)VALUES('$tablename','$last_index')";
			}
		}
		$query = $this -> db -> query($sql);
	}

	public function checkDB($dbname) {
		$sql = "show tables from $dbname like '%tblarvdrugstockmain%';";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$temp = 0;
		} else {
			$temp = 1;
		}
		echo $temp;
	}

	public function base_params($data) {
		$data['title'] = "webADT | Data Migration";
		$data['_type'] = 'migration';
		$data['link'] = "migration_management";
		$this -> load -> view('template', $data);
	}

}
?>