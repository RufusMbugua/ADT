<?php
class Patient extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('Medical_Record_Number', 'varchar', 10);
		$this -> hasColumn('Patient_Number_CCC', 'varchar', 10);
		$this -> hasColumn('First_Name', 'varchar', 50);
		$this -> hasColumn('Last_Name', 'varchar', 50);
		$this -> hasColumn('Other_Name', 'varchar', 50);
		$this -> hasColumn('Dob', 'varchar', 32);
		$this -> hasColumn('Pob', 'varchar', 100);
		$this -> hasColumn('Gender', 'varchar', 2);
		$this -> hasColumn('Pregnant', 'varchar', 2);
		$this -> hasColumn('Weight', 'varchar', 5);
		$this -> hasColumn('Height', 'varchar', 5);
		$this -> hasColumn('Sa', 'varchar', 5);
		$this -> hasColumn('Phone', 'varchar', 30);
		$this -> hasColumn('Physical', 'varchar', 100);
		$this -> hasColumn('Alternate', 'varchar', 50);
		$this -> hasColumn('Other_Illnesses', 'text');
		$this -> hasColumn('Other_Drugs', 'text');
		$this -> hasColumn('Adr', 'text');
		$this -> hasColumn('Tb', 'varchar', 2);
		$this -> hasColumn('Smoke', 'varchar', 2);
		$this -> hasColumn('Alcohol', 'varchar', 2);
		$this -> hasColumn('Date_Enrolled', 'varchar', 32);
		$this -> hasColumn('Source', 'varchar', 2);
		$this -> hasColumn('Supported_By', 'varchar', 2);
		$this -> hasColumn('Timestamp', 'varchar', 32);
		$this -> hasColumn('Facility_Code', 'varchar', 10);
		$this -> hasColumn('Service', 'varchar', 5);
		$this -> hasColumn('Start_Regimen', 'varchar', 5);
		
	}

	public function setUp() {
		$this -> setTableName('patient');
	}
 

}
