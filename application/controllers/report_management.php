<?php
class report_management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this -> load -> database();
	}

	public function index() {
		$this -> listing();
	}

	public function listing($data = "") {
		$data['content_view'] = "report_v";
		$this -> base_params($data);
	}

	public function patient_enrolled($supported_by = 0, $from = "2013-03-01", $to = "2013-03-31") {
		//Variables
		$facility_code = $this -> session -> userdata("facility");
		//art
		$adult_male_art_outpatient = 0;
		$adult_male_art_inpatient = 0;
		$adult_male_art_transferin = 0;
		$adult_male_art_casualty = 0;
		$adult_male_art_transit = 0;
		$adult_male_art_htc = 0;
		$adult_male_art_other = 0;

		$child_male_art_outpatient = 0;
		$child_male_art_inpatient = 0;
		$child_male_art_transferin = 0;
		$child_male_art_casualty = 0;
		$child_male_art_transit = 0;
		$child_male_art_htc = 0;
		$child_male_art_other = 0;

		$adult_female_art_outpatient = 0;
		$adult_female_art_inpatient = 0;
		$adult_female_art_transferin = 0;
		$adult_female_art_casualty = 0;
		$adult_female_art_transit = 0;
		$adult_female_art_htc = 0;
		$adult_female_art_other = 0;

		$child_female_art_outpatient = 0;
		$child_female_art_inpatient = 0;
		$child_female_art_transferin = 0;
		$child_female_art_casualty = 0;
		$child_female_art_transit = 0;
		$child_female_art_htc = 0;
		$child_female_art_other = 0;

		//PEP
		$adult_male_pep_outpatient = 0;
		$adult_male_pep_inpatient = 0;
		$adult_male_pep_transferin = 0;
		$adult_male_pep_casualty = 0;
		$adult_male_pep_transit = 0;
		$adult_male_pep_htc = 0;
		$adult_male_pep_other = 0;

		$child_male_pep_outpatient = 0;
		$child_male_pep_inpatient = 0;
		$child_male_pep_transferin = 0;
		$child_male_pep_casualty = 0;
		$child_male_pep_transit = 0;
		$child_male_pep_htc = 0;
		$child_male_pep_other = 0;

		$adult_female_pep_outpatient = 0;
		$adult_female_pep_inpatient = 0;
		$adult_female_pep_transferin = 0;
		$adult_female_pep_casualty = 0;
		$adult_female_pep_transit = 0;
		$adult_female_pep_htc = 0;
		$adult_female_pep_other = 0;

		$child_female_pep_outpatient = 0;
		$child_female_pep_inpatient = 0;
		$child_female_pep_transferin = 0;
		$child_female_pep_casualty = 0;
		$child_female_pep_transit = 0;
		$child_female_pep_htc = 0;
		$child_female_pep_other = 0;

		//PMTCT
		$adult_male_pmtct_outpatient = 0;
		$adult_male_pmtct_inpatient = 0;
		$adult_male_pmtct_transferin = 0;
		$adult_male_pmtct_casualty = 0;
		$adult_male_pmtct_transit = 0;
		$adult_male_pmtct_htc = 0;
		$adult_male_pmtct_other = 0;

		$child_male_pmtct_outpatient = 0;
		$child_male_pmtct_inpatient = 0;
		$child_male_pmtct_transferin = 0;
		$child_male_pmtct_casualty = 0;
		$child_male_pmtct_transit = 0;
		$child_male_pmtct_htc = 0;
		$child_male_pmtct_other = 0;

		$adult_female_pmtct_outpatient = 0;
		$adult_female_pmtct_inpatient = 0;
		$adult_female_pmtct_transferin = 0;
		$adult_female_pmtct_casualty = 0;
		$adult_female_pmtct_transit = 0;
		$adult_female_pmtct_htc = 0;
		$adult_female_pmtct_other = 0;

		$child_female_pmtct_outpatient = 0;
		$child_female_pmtct_inpatient = 0;
		$child_female_pmtct_transferin = 0;
		$child_female_pmtct_casualty = 0;
		$child_female_pmtct_transit = 0;
		$child_female_pmtct_htc = 0;
		$child_female_pmtct_other = 0;

		//OI
		$adult_male_oi_outpatient = 0;
		$adult_male_oi_inpatient = 0;
		$adult_male_oi_transferin = 0;
		$adult_male_oi_casualty = 0;
		$adult_male_oi_transit = 0;
		$adult_male_oi_htc = 0;
		$adult_male_oi_other = 0;

		$child_male_oi_outpatient = 0;
		$child_male_oi_inpatient = 0;
		$child_male_oi_transferin = 0;
		$child_male_oi_casualty = 0;
		$child_male_oi_transit = 0;
		$child_male_oi_htc = 0;
		$child_male_oi_other = 0;

		$adult_female_oi_outpatient = 0;
		$adult_female_oi_inpatient = 0;
		$adult_female_oi_transferin = 0;
		$adult_female_oi_casualty = 0;
		$adult_female_oi_transit = 0;
		$adult_female_oi_htc = 0;
		$adult_female_oi_other = 0;

		$child_female_oi_outpatient = 0;
		$child_female_oi_inpatient = 0;
		$child_female_oi_transferin = 0;
		$child_female_oi_casualty = 0;
		$child_female_oi_transit = 0;
		$child_female_oi_htc = 0;
		$child_female_oi_other = 0;

		if ($supported_by == 0) {
			$supported_query = "AND(supported_by=1 OR supported_by=2) AND facility_code='$facility_code'";
		}
		if ($supported_by == 1) {
			$supported_query = "AND supported_by=1 AND facility_code='$facility_code'";
		}
		if ($supported_by == 2) {
			$supported_query = "AND supported_by=2 AND facility_code='$facility_code'";
		}
		$sql = "select count(*) as total, service, gender,r.name,source,ROUND((DATEDIFF(CURDATE(),dob)/360)) as age from patient p left join regimen_service_type r on p.service = r.id where date_enrolled between '$from' and '$to' $supported_query and r.active=1 group by service,gender,source,age";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();

		if ($results) {
			//Loop through array
			foreach ($results as $result) {
				if ($result['age'] >= 15) {
					//Check if adult
					if ($result['gender'] == 1) {
						//Check if male adult
						if ($result['service'] == 1) {
							//Check if ART
							if ($result['source'] == 1) {
								//Check if Outpatient
								$adult_male_art_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$adult_male_art_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$adult_male_art_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$adult_male_art_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$adult_male_art_transit;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$adult_male_art_htc++;
							} else {
								//Check if other
								$adult_male_art_other++;
							}

						} else if ($result['service'] == 2) {
							//Check if PEP
							if ($result['source'] == 1) {
								//Check if Outpatient
								$adult_male_pep_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$adult_male_pep_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$adult_male_pep_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$adult_male_pep_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$adult_male_pep_tarnsit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$adult_male_pep_htc++;
							} else {
								//Check if other
								$adult_male_pep_other++;
							}
						} else if ($result['service'] == 3) {
							//Check if PMTCT
							if ($result['source'] == 1) {
								//Check if Outpatient
								$adult_male_pmtct_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$adult_male_pmtct_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$adult_male_pmtct_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$adult_male_pmtct_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$adult_male_pmtct_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$adult_male_pmtct_htc++;
							} else {
								//Check if other
								$adult_male_pmtct_other++;
							}
						} else if ($result['service'] == 5) {
							//Check if OI
							if ($result['source'] == 1) {
								//Check if Outpatient
								$adult_male_oi_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$adult_male_oi_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$adult_male_oi_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$adult_male_oi_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$adult_male_oi_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$adult_male_oi_htc++;
							} else {
								//Check if other
								$adult_male_oi_other++;
							}
						}
					} else if ($result['gender'] == 2) {
						//Check if female adult
						if ($result['service'] == 1) {
							//Check if ART
							if ($result['source'] == 1) {
								//Check if Outpatient
								$adult_female_art_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$adult_female_art_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$adult_female_art_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$adult_female_art_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$adult_female_art_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$adult_female_art_htc++;
							} else {
								//Check if other
								$adult_female_art_other++;
							}
						} else if ($result['service'] == 2) {
							//Check if PEP
							if ($result['source'] == 1) {
								//Check if Outpatient
								$adult_female_pep_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$adult_female_pep_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$adult_female_pep_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$adult_female_pep_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$adult_female_pep_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$adult_female_pep_htc++;
							} else {
								//Check if other
								$adult_female_pep_other++;
							}
						} else if ($result['service'] == 3) {
							//Check if PMTCT
							if ($result['source'] == 1) {
								//Check if Outpatient
								$adult_female_pmtct_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$adult_female_pmtct_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$adult_female_pmtct_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$adult_female_pmtct_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$adult_female_pmtct_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$adult_female_pmtct_htc++;
							} else {
								//Check if other
								$adult_female_pmtct_other++;
							}
						} else if ($result['service'] == 5) {
							//Check if OI
							if ($result['source'] == 1) {
								//Check if Outpatient
								$adult_female_oi_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$adult_female_oi_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$adult_female_oi_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$adult_female_oi_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$adult_female_oi_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$adult_female_oi_htc++;
							} else {
								//Check if other
								$adult_female_oi_other++;
							}
						}
					}
				} else if ($result['age'] < 15) {
					//Check if child
					if ($result['gender'] == 1) {
						//Check if male child
						if ($result['service'] == 1) {
							//Check if ART
							if ($result['source'] == 1) {
								//Check if Outpatient
								$child_male_art_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$child_male_art_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$child_male_art_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$child_male_art_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$child_male_art_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$child_male_art_htc++;
							} else {
								//Check if other
								$child_male_art_other++;
							}
						} else if ($result['service'] == 2) {
							//Check if PEP
							if ($result['source'] == 1) {
								//Check if Outpatient
								$child_male_pep_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$child_male_pep_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$child_male_pep_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$child_male_pep_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$child_male_pep_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$child_male_pep_htc++;
							} else {
								//Check if other
								$child_male_pep_other++;
							}
						} else if ($result['service'] == 3) {
							//Check if PMTCT
							if ($result['source'] == 1) {
								//Check if Outpatient
								$child_male_pmtct_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$child_male_pmtct_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$child_male_pmtct_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$child_male_pmtct_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$child_male_pmtct_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$child_male_pmtct_htc++;
							} else {
								//Check if other
								$child_male_pmtct_other++;
							}
						} else if ($result['service'] == 5) {
							//Check if OI
							if ($result['source'] == 1) {
								//Check if Outpatient
								$child_male_oi_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$child_male_oi_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$child_male_oi_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$child_male_oi_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$child_male_oi_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$child_male_oi_htc++;
							} else {
								//Check if other
								$child_male_oi_other++;
							}
						}
					} else if ($result['gender'] == 2) {
						//Check if female child
						if ($result['service'] == 1) {
							//Check if ART
							if ($result['source'] == 1) {
								//Check if Outpatient
								$child_female_art_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$child_female_art_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$child_female_art_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$child_female_art_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$child_female_art_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$child_female_art_htc++;
							} else {
								//Check if other
								$child_female_art_other++;
							}
						} else if ($result['service'] == 2) {
							//Check if PEP
							if ($result['source'] == 1) {
								//Check if Outpatient
								$child_female_pep_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$child_female_pep_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$child_female_pep_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$child_female_pep_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$child_female_pep_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$child_female_pep_htc++;
							} else {
								//Check if other
								$child_female_pep_other++;
							}
						} else if ($result['service'] == 3) {
							//Check if PMTCT
							if ($result['source'] == 1) {
								//Check if Outpatient
								$child_female_pmtct_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$child_female_pmtct_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$child_female_pmtct_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$child_female_pmtct_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$child_female_pmtct_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$child_female_pmtct_htc++;
							} else {
								//Check if other
								$child_female_pmtct_other++;
							}
						} else if ($result['service'] == 5) {
							//Check if OI
							if ($result['source'] == 1) {
								//Check if Outpatient
								$child_female_oi_outpatient++;
							} else if ($result['source'] == 2) {
								//Check if inpatient
								$child_female_oi_inpatient++;
							} else if ($result['source'] == 3) {
								//Check if Transfer in
								$child_female_oi_transferin++;
							} else if ($result['source'] == 4) {
								//Check if Casualty
								$child_female_oi_casualty++;
							} else if ($result['source'] == 5) {
								//Check if Transit
								$child_female_oi_transit++;
							} else if ($result['source'] == 6) {
								//Check if HTC
								$child_female_oi_htc++;
							} else {
								//Check if other
								$child_female_oi_other++;
							}
						}
					}
				}

			}
		}

		//Push to array
		$data = array();
		$data['adult_male_art_outpatient'] = $adult_male_art_outpatient;
		$data['adult_male_art_inpatient'] = $adult_male_art_inpatient;
		$data['adult_male_art_transferin'] = $adult_male_art_transferin;
		$data['adult_male_art_casualty'] = $adult_male_art_casualty;
		$data['adult_male_art_transit'] = $adult_male_art_transit;
		$data['adult_male_art_htc'] = $adult_male_art_htc;
		$data['adult_male_art_other'] = $adult_male_art_other;

		$data['child_male_art_outpatient'] = $child_male_art_outpatient;
		$data['child_male_art_inpatient'] = $child_male_art_inpatient;
		$data['child_male_art_transferin'] = $child_male_art_transferin;
		$data['child_male_art_casualty'] = $child_male_art_casualty;
		$data['child_male_art_transit'] = $child_male_art_transit;
		$data['child_male_art_htc'] = $child_male_art_htc;
		$data['child_male_art_other'] = $child_male_art_other;

		$data['adult_female_art_outpatient'] = $adult_female_art_outpatient;
		$data['adult_female_art_inpatient'] = $adult_female_art_inpatient;
		$data['adult_female_art_transferin'] = $adult_female_art_transferin;
		$data['adult_female_art_casualty'] = $adult_female_art_casualty;
		$data['adult_female_art_transit'] = $adult_female_art_transit;
		$data['adult_female_art_htc'] = $adult_female_art_htc;
		$data['adult_female_art_other'] = $adult_female_art_other;

		$data['child_female_art_outpatient'] = $child_female_art_outpatient;
		$data['child_female_art_inpatient'] = $child_female_art_inpatient;
		$data['child_female_art_transferin'] = $child_female_art_transferin;
		$data['child_female_art_casualty'] = $child_female_art_casualty;
		$data['child_female_art_transit'] = $child_female_art_transit;
		$data['child_female_art_htc'] = $child_female_art_htc;
		$data['child_female_art_other'] = $child_female_art_other;

		$data['adult_male_pep_outpatient'] = $adult_male_pep_outpatient;
		$data['adult_male_pep_inpatient'] = $adult_male_pep_inpatient;
		$data['adult_male_pep_transferin'] = $adult_male_pep_transferin;
		$data['adult_male_pep_casualty'] = $adult_male_pep_casualty;
		$data['adult_male_pep_transit'] = $adult_male_pep_transit;
		$data['adult_male_pep_htc'] = $adult_male_pep_htc;
		$data['adult_male_pep_other'] = $adult_male_pep_other;

		$data['child_male_pep_outpatient'] = $child_male_pep_outpatient;
		$data['child_male_pep_inpatient'] = $child_male_pep_inpatient;
		$data['child_male_pep_transferin'] = $child_male_pep_transferin;
		$data['child_male_pep_casualty'] = $child_male_pep_casualty;
		$data['child_male_pep_transit'] = $child_male_pep_transit;
		$data['child_male_pep_htc'] = $child_male_pep_htc;
		$data['child_male_pep_other'] = $child_male_pep_other;

		$data['adult_female_pep_outpatient'] = $adult_female_pep_outpatient;
		$data['adult_female_pep_inpatient'] = $adult_female_pep_inpatient;
		$data['adult_female_pep_transferin'] = $adult_female_pep_transferin;
		$data['adult_female_pep_casualty'] = $adult_female_pep_casualty;
		$data['adult_female_pep_transit'] = $adult_female_pep_transit;
		$data['adult_female_pep_htc'] = $adult_female_pep_htc;
		$data['adult_female_pep_other'] = $adult_female_pep_other;

		$data['child_female_pep_outpatient'] = $child_female_pep_outpatient;
		$data['child_female_pep_inpatient'] = $child_female_pep_inpatient;
		$data['child_female_pep_transferin'] = $child_female_pep_transferin;
		$data['child_female_pep_casualty'] = $child_female_pep_casualty;
		$data['child_female_pep_transit'] = $child_female_pep_transit;
		$data['child_female_pep_htc'] = $child_female_pep_htc;
		$data['child_female_pep_other'] = $child_female_pep_other;

		$data['adult_male_pmtct_outpatient'] = $adult_male_pmtct_outpatient;
		$data['adult_male_pmtct_inpatient'] = $adult_male_pmtct_inpatient;
		$data['adult_male_pmtct_transferin'] = $adult_male_pmtct_transferin;
		$data['adult_male_pmtct_casualty'] = $adult_male_pmtct_casualty;
		$data['adult_male_pmtct_transit'] = $adult_male_pmtct_transit;
		$data['adult_male_pmtct_htc'] = $adult_male_pmtct_htc;
		$data['adult_male_pmtct_other'] = $adult_male_pmtct_other;

		$data['child_male_pmtct_outpatient'] = $child_male_pmtct_outpatient;
		$data['child_male_pmtct_inpatient'] = $child_male_pmtct_inpatient;
		$data['child_male_pmtct_transferin'] = $child_male_pmtct_transferin;
		$data['child_male_pmtct_casualty'] = $child_male_pmtct_casualty;
		$data['child_male_pmtct_transit'] = $child_male_pmtct_transit;
		$data['child_male_pmtct_htc'] = $child_male_pmtct_htc;
		$data['child_male_pmtct_other'] = $child_male_pmtct_other;

		$data['adult_female_pmtct_outpatient'] = $adult_female_pmtct_outpatient;
		$data['adult_female_pmtct_inpatient'] = $adult_female_pmtct_inpatient;
		$data['adult_female_pmtct_transferin'] = $adult_female_pmtct_transferin;
		$data['adult_female_pmtct_casualty'] = $adult_female_pmtct_casualty;
		$data['adult_female_pmtct_transit'] = $adult_female_pmtct_transit;
		$data['adult_female_pmtct_htc'] = $adult_female_pmtct_htc;
		$data['adult_female_pmtct_other'] = $adult_female_pmtct_other;

		$data['child_female_pmtct_outpatient'] = $child_female_pmtct_outpatient;
		$data['child_female_pmtct_inpatient'] = $child_female_pmtct_inpatient;
		$data['child_female_pmtct_transferin'] = $child_female_pmtct_transferin;
		$data['child_female_pmtct_casualty'] = $child_female_pmtct_casualty;
		$data['child_female_pmtct_transit'] = $child_female_pmtct_transit;
		$data['child_female_pmtct_htc'] = $child_female_pmtct_htc;
		$data['child_female_pmtct_other'] = $child_female_pmtct_other;

		$data['adult_male_oi_outpatient'] = $adult_male_oi_outpatient;
		$data['adult_male_oi_inpatient'] = $adult_male_oi_inpatient;
		$data['adult_male_oi_transferin'] = $adult_male_oi_transferin;
		$data['adult_male_oi_casualty'] = $adult_male_oi_casualty;
		$data['adult_male_oi_transit'] = $adult_male_oi_transit;
		$data['adult_male_oi_htc'] = $adult_male_oi_htc;
		$data['adult_male_oi_other'] = $adult_male_oi_other;

		$data['child_male_oi_outpatient'] = $child_male_oi_outpatient;
		$data['child_male_oi_inpatient'] = $child_male_oi_inpatient;
		$data['child_male_oi_transferin'] = $child_male_oi_transferin;
		$data['child_male_oi_casualty'] = $child_male_oi_casualty;
		$data['child_male_oi_transit'] = $child_male_oi_transit;
		$data['child_male_oi_htc'] = $child_male_oi_htc;
		$data['child_male_oi_other'] = $child_male_oi_other;

		$data['adult_female_oi_outpatient'] = $adult_female_oi_outpatient;
		$data['adult_female_oi_inpatient'] = $adult_female_oi_inpatient;
		$data['adult_female_oi_transferin'] = $adult_female_oi_transferin;
		$data['adult_female_oi_casualty'] = $adult_female_oi_casualty;
		$data['adult_female_oi_transit'] = $adult_female_oi_transit;
		$data['adult_female_oi_htc'] = $adult_female_oi_htc;
		$data['adult_female_oi_other'] = $adult_female_oi_other;

		$data['child_female_oi_outpatient'] = $child_female_oi_outpatient;
		$data['child_female_oi_inpatient'] = $child_female_oi_inpatient;
		$data['child_female_oi_transferin'] = $child_female_oi_transferin;
		$data['child_female_oi_casualty'] = $child_female_oi_casualty;
		$data['child_female_oi_transit'] = $child_female_oi_transit;
		$data['child_female_oi_htc'] = $child_female_oi_htc;
		$data['child_female_oi_other'] = $child_female_oi_other;

		//Totals for Service Lines(Adult Male)
		$data['total_adult_male_art'] = $adult_male_art_outpatient + $adult_male_art_inpatient + $adult_male_art_transferin + $adult_male_art_casualty + $adult_male_art_transit + $adult_male_art_htc + $adult_male_art_other;
		$data['total_adult_male_pep'] = $adult_male_pep_outpatient + $adult_male_pep_inpatient + $adult_male_pep_transferin + $adult_male_pep_casualty + $adult_male_pep_transit + $adult_male_pep_htc + $adult_male_pep_other;
		$data['total_adult_male_pmtct'] = $adult_male_pmtct_outpatient + $adult_male_pmtct_inpatient + $adult_male_pmtct_transferin + $adult_male_pmtct_casualty + $adult_male_pmtct_transit + $adult_male_pmtct_htc + $adult_male_pmtct_other;
		$data['total_adult_male_oi'] = $adult_male_oi_outpatient + $adult_male_oi_inpatient + $adult_male_oi_transferin + $adult_male_oi_casualty + $adult_male_oi_transit + $adult_male_oi_htc + $adult_male_oi_other;
		$data['overall_line_adult_male'] = $data['total_adult_male_art'] + $data['total_adult_male_pep'] + $data['total_adult_male_oi'];

		//Totals for sources(Adult Male)
		$data['total_adult_male_outpatient'] = $adult_male_art_outpatient + $adult_male_pep_outpatient + $adult_male_pmtct_outpatient + $adult_male_oi_outpatient;
		$data['total_adult_male_inpatient'] = $adult_male_art_inpatient + $adult_male_pep_inpatient + $adult_male_pmtct_inpatient + $adult_male_oi_inpatient;
		$data['total_adult_male_transferin'] = $adult_male_art_transferin + $adult_male_pep_transferin + $adult_male_pmtct_transferin + $adult_male_oi_transferin;
		$data['total_adult_male_casualty'] = $adult_male_art_casualty + $adult_male_pep_casualty + $adult_male_pmtct_casualty + $adult_male_oi_casualty;
		$data['total_adult_male_transit'] = $adult_male_art_transit + $adult_male_pep_transit + $adult_male_pmtct_transit + $adult_male_oi_transit;
		$data['total_adult_male_htc'] = $adult_male_art_htc + $adult_male_pep_htc + $adult_male_pmtct_htc + $adult_male_oi_htc;
		$data['total_adult_male_other'] = $adult_male_art_other + $adult_male_pep_other + $adult_male_pmtct_other + $adult_male_oi_other;

		//Totals for Service Lines(Adult Female)
		$data['total_adult_female_art'] = $adult_female_art_outpatient + $adult_female_art_inpatient + $adult_female_art_transferin + $adult_female_art_casualty + $adult_female_art_transit + $adult_female_art_htc + $adult_female_art_other;
		$data['total_adult_female_pep'] = $adult_female_pep_outpatient + $adult_female_pep_inpatient + $adult_female_pep_transferin + $adult_female_pep_casualty + $adult_female_pep_transit + $adult_female_pep_htc + $adult_female_pep_other;
		$data['total_adult_female_pmtct'] = $adult_female_pmtct_outpatient + $adult_female_pmtct_inpatient + $adult_female_pmtct_transferin + $adult_female_pmtct_casualty + $adult_female_pmtct_transit + $adult_female_pmtct_htc + $adult_female_pmtct_other;
		$data['total_adult_female_oi'] = $adult_female_oi_outpatient + $adult_female_oi_inpatient + $adult_female_oi_transferin + $adult_female_oi_casualty + $adult_female_oi_transit + $adult_female_oi_htc + $adult_female_oi_other;
		$data['overall_line_adult_female'] = $data['total_adult_female_art'] + $data['total_adult_female_pep'] + $data['total_adult_female_pmtct'] + $data['total_adult_female_oi'];

		//Totals for sources(Adult Female)
		$data['total_adult_female_outpatient'] = $adult_female_art_outpatient + $adult_female_pep_outpatient + $adult_female_pmtct_outpatient + $adult_female_oi_outpatient;
		$data['total_adult_female_inpatient'] = $adult_female_art_inpatient + $adult_female_pep_inpatient + $adult_female_pmtct_inpatient + $adult_female_oi_inpatient;
		$data['total_adult_female_transferin'] = $adult_female_art_transferin + $adult_female_pep_transferin + $adult_female_pmtct_transferin + $adult_female_oi_transferin;
		$data['total_adult_female_casualty'] = $adult_female_art_casualty + $adult_female_pep_casualty + $adult_female_pmtct_casualty + $adult_female_oi_casualty;
		$data['total_adult_female_transit'] = $adult_female_art_transit + $adult_female_pep_transit + $adult_female_pmtct_transit + $adult_female_oi_transit;
		$data['total_adult_female_htc'] = $adult_female_art_htc + $adult_female_pep_htc + $adult_female_pmtct_htc + $adult_female_oi_htc;
		$data['total_adult_female_other'] = $adult_female_art_other + $adult_female_pep_other + $adult_female_pmtct_other + $adult_female_oi_other;

		//Totals for Service Lines(Child Male)
		$data['total_child_male_art'] = $child_male_art_outpatient + $child_male_art_inpatient + $child_male_art_transferin + $child_male_art_casualty + $child_male_art_transit + $child_male_art_htc + $child_male_art_other;
		$data['total_child_male_pep'] = $child_male_pep_outpatient + $child_male_pep_inpatient + $child_male_pep_transferin + $child_male_pep_casualty + $child_male_pep_transit + $child_male_pep_htc + $child_male_pep_other;
		$data['total_child_male_pmtct'] = $child_male_pmtct_outpatient + $child_male_pmtct_inpatient + $child_male_pmtct_transferin + $child_male_pmtct_casualty + $child_male_pmtct_transit + $child_male_pmtct_htc + $child_male_pmtct_other;
		$data['total_child_male_oi'] = $child_male_oi_outpatient + $child_male_oi_inpatient + $child_male_oi_transferin + $child_male_oi_casualty + $child_male_oi_transit + $child_male_oi_htc + $child_male_oi_other;
		$data['overall_line_child_male'] = $data['total_child_male_art'] + $data['total_child_male_pep'] + $data['total_child_male_pmtct'] + $data['total_child_male_oi'];

		//Totals for sources(Child Male)
		$data['total_child_male_outpatient'] = $child_male_art_outpatient + $child_male_pep_outpatient + $child_male_pmtct_outpatient + $child_male_oi_outpatient;
		$data['total_child_male_inpatient'] = $child_male_art_inpatient + $child_male_pep_inpatient + $child_male_pmtct_inpatient + $child_male_oi_inpatient;
		$data['total_child_male_transferin'] = $child_male_art_transferin + $child_male_pep_transferin + $child_male_pmtct_transferin + $child_male_oi_transferin;
		$data['total_child_male_casualty'] = $child_male_art_casualty + $child_male_pep_casualty + $child_male_pmtct_casualty + $child_male_oi_casualty;
		$data['total_child_male_transit'] = $child_male_art_transit + $child_male_pep_transit + $child_male_pmtct_transit + $child_male_oi_transit;
		$data['total_child_male_htc'] = $child_male_art_htc + $child_male_pep_htc + $child_male_pmtct_htc + $child_male_oi_htc;
		$data['total_child_male_other'] = $child_male_art_other + $child_male_pep_other + $child_male_pmtct_other + $child_male_oi_other;

		//Totals for Service Lines(Child Female)
		$data['total_child_female_art'] = $child_female_art_outpatient + $child_female_art_inpatient + $child_female_art_transferin + $child_female_art_casualty + $child_female_art_transit + $child_female_art_htc + $child_female_art_other;
		$data['total_child_female_pep'] = $child_female_pep_outpatient + $child_female_pep_inpatient + $child_female_pep_transferin + $child_female_pep_casualty + $child_female_pep_transit + $child_female_pep_htc + $child_female_pep_other;
		$data['total_child_female_pmtct'] = $child_female_pmtct_outpatient + $child_female_pmtct_inpatient + $child_female_pmtct_transferin + $child_female_pmtct_casualty + $child_female_pmtct_transit + $child_female_pmtct_htc + $child_female_pmtct_other;
		$data['total_child_female_oi'] = $child_female_oi_outpatient + $child_female_oi_inpatient + $child_female_oi_transferin + $child_female_oi_casualty + $child_female_oi_transit + $child_female_oi_htc + $child_female_oi_other;
		$data['overall_line_child_female'] = $data['total_child_female_art'] + $data['total_child_female_pep'] + $data['total_child_female_pmtct'] + $data['total_child_female_oi'];

		//Totals for sources(Child Female)
		$data['total_child_female_outpatient'] = $child_female_art_outpatient + $child_female_pep_outpatient + $child_female_pmtct_outpatient + $child_female_oi_outpatient;
		$data['total_child_female_inpatient'] = $child_female_art_inpatient + $child_female_pep_inpatient + $child_female_pmtct_inpatient + $child_female_oi_inpatient;
		$data['total_child_female_transferin'] = $child_female_art_transferin + $child_female_pep_transferin + $child_female_pmtct_transferin + $child_female_oi_transferin;
		$data['total_child_female_casualty'] = $child_female_art_casualty + $child_female_pep_casualty + $child_female_pmtct_casualty + $child_female_oi_casualty;
		$data['total_child_female_transit'] = $child_female_art_transit + $child_female_pep_transit + $child_female_pmtct_transit + $child_female_oi_transit;
		$data['total_child_female_htc'] = $child_female_art_htc + $child_female_pep_htc + $child_female_pmtct_htc + $child_female_oi_htc;
		$data['total_child_female_other'] = $child_female_art_other + $child_female_pep_other + $child_female_pmtct_other + $child_female_oi_other;

		//Overall Total
		$data['overall_total'] = $data['overall_line_adult_female'] + $data['overall_line_adult_male'] + $data['overall_line_child_female'] + $data['overall_line_child_male'];
		$data['from'] = date('d-M-Y', strtotime($from));
		$data['to'] = date('d-M-Y', strtotime($to));
		$this -> load -> view('reports/no_of_patients_enrolled_v', $data);
	}

	public function patient_active_byregimen($from = "2013-06-06") {
		//Variables
		$facility_code = $this -> session -> userdata("facility");
		$regimen_totals = array();
		$data = array();
		$row_string = "";
		$overall_adult_male = 0;
		$overall_adult_female = 0;
		$overall_child_male = 0;
		$overall_child_female = 0;

		//Get Total of all patients
		$sql = "SELECT count(*) as total, r.regimen_desc,p.current_regimen FROM patient p,regimen r WHERE p.current_status=1 AND r.id=p.current_regimen AND p.facility_code='$facility_code' AND p.current_regimen !=0 AND p.current_regimen !='' AND p.current_status !='' AND p.current_status !=0";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$patient_total = $results[0]['total'];

		//Get Totals for each regimen
		$sql = "SELECT count(*) as total, r.regimen_desc,r.regimen_code,p.current_regimen FROM patient p,regimen r WHERE p.current_status=1 AND r.id=p.current_regimen AND p.facility_code='$facility_code' AND p.current_regimen !=0 AND p.current_regimen !='' AND p.current_status !='' AND p.current_status !=0 GROUP BY p.current_regimen ORDER BY r.regimen_code ASC";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$row_string .= "<table id='patient_listing' border='1' cellpadding='5'>
			<tr>
				<th rowspan='3'>Regimen</th>
				<th colspan='2'>Total</th>
				<th colspan='4'> Adult</th>
				<th colspan='4'> Children </th>
			</tr>
			<tr>
				<th rowspan='2'>No.</th>
				<th rowspan='2'>%</th>
				<th colspan='2'>Male</th>
				<th colspan='2'>Female</th>
				<th colspan='2'>Male</th>
				<th colspan='2'>Female</th>
			</tr>
			<tr>
				<th>No.</th>
				<th>%</th>
				<th>No.</th>
				<th>%</th><th>No.</th>
				<th>%</th><th>No.</th>
				<th>%</th>
			</tr>";
			foreach ($results as $result) {
				$regimen_totals[$result['current_regimen']] = $result['total'];
				$current_regimen = $result['current_regimen'];
				$regimen_name = $result['regimen_desc'];
				$regimen_code = $result['regimen_code'];
				$regimen_total = $result['total'];
				$regimen_total_percentage = number_format(($regimen_total / $patient_total) * 100, 1);
				$row_string .= "<tr><td><b>$regimen_code</b> | $regimen_name</td><td>$regimen_total</td><td>$regimen_total_percentage</td>";
				//SQL for Adult Male Regimens
				$sql = "SELECT count(*) as total_adult_male, r.regimen_desc,p.current_regimen as regimen_id FROM patient p,regimen r WHERE p.current_status=1 AND r.id=p.current_regimen AND p.facility_code='$facility_code' AND p.gender=1 AND p.current_regimen='$current_regimen' AND round(datediff('$from',p.dob)/360)>=15 GROUP BY p.current_regimen";
				$query = $this -> db -> query($sql);
				$results = $query -> result_array();
				if ($results) {
					foreach ($results as $result) {
						$total_adult_male = $result['total_adult_male'];
						$overall_adult_male += $total_adult_male;
						$total_adult_male_percentage = number_format(($total_adult_male / $regimen_total) * 100, 1);
						if ($result['regimen_id'] != null) {
							$row_string .= "<td>$total_adult_male</td><td>$total_adult_male_percentage</td>";
						}
					}
				} else {
					$row_string .= "<td>-</td><td>-</td>";
				}
				//SQL for Adult Female Regimens
				$sql = "SELECT count(*) as total_adult_female, r.regimen_desc,p.current_regimen as regimen_id FROM patient p,regimen r WHERE p.current_status=1 AND r.id=p.current_regimen AND p.facility_code='$facility_code' AND p.gender=2 AND p.current_regimen='$current_regimen' AND round(datediff('$from',p.dob)/360)>=15 GROUP BY p.current_regimen";
				$query = $this -> db -> query($sql);
				$results = $query -> result_array();
				if ($results) {
					foreach ($results as $result) {
						$total_adult_female = $result['total_adult_female'];
						$overall_adult_female += $total_adult_female;
						$total_adult_female_percentage = number_format(($total_adult_female / $regimen_total) * 100, 1);
						if ($result['regimen_id'] != null) {
							$row_string .= "<td>$total_adult_female</td><td>$total_adult_female_percentage</td>";
						}
					}
				} else {
					$row_string .= "<td>-</td><td>-</td>";
				}
				//SQL for Child Male Regimens
				$sql = "SELECT count(*) as total_child_male, r.regimen_desc,p.current_regimen as regimen_id FROM patient p,regimen r WHERE p.current_status=1 AND r.id=p.current_regimen AND p.facility_code='$facility_code' AND p.gender=1 AND p.current_regimen='$current_regimen' AND round(datediff('$from',p.dob)/360)<15 GROUP BY p.current_regimen";
				$query = $this -> db -> query($sql);
				$results = $query -> result_array();
				if ($results) {
					foreach ($results as $result) {
						$total_child_male = $result['total_child_male'];
						$overall_child_male += $total_child_male;
						$total_child_male_percentage = number_format(($total_child_male / $regimen_total) * 100, 1);
						if ($result['regimen_id'] != null) {
							$row_string .= "<td>$total_child_male</td><td>$total_child_male_percentage</td>";
						}
					}
				} else {
					$row_string .= "<td>-</td><td>-</td>";
				}
				//SQL for Child Female Regimens
				$sql = "SELECT count(*) as total_child_female, r.regimen_desc,p.current_regimen as regimen_id FROM patient p,regimen r WHERE p.current_status=1 AND r.id=p.current_regimen AND p.facility_code='$facility_code' AND p.gender=2 AND p.current_regimen='$current_regimen' AND round(datediff('$from',p.dob)/360)<15 GROUP BY p.current_regimen";
				$query = $this -> db -> query($sql);
				$results = $query -> result_array();
				if ($results) {
					foreach ($results as $result) {
						$total_child_female = $result['total_child_female'];
						$overall_child_female += $total_child_female;
						$total_child_female_percentage = number_format(($total_child_female / $regimen_total) * 100, 1);
						if ($result['regimen_id'] != null) {
							$row_string .= "<td>$total_child_female</td><td>$total_child_female_percentage</td>";
						}
					}
				} else {
					$row_string .= "<td>-</td><td>-</td>";
				}
				$row_string .= "</tr>";
			}
			$row_string .= "<tr class='tfoot'><td><b>Totals:</b></td><td><b>$patient_total</b></td><td><b>100</b></td><td><b>$overall_adult_male</b></td><td><b>" . number_format(($overall_adult_male / $patient_total) * 100, 1) . "</b></td><td><b>$overall_adult_female</b></td><td><b>" . number_format(($overall_adult_female / $patient_total) * 100, 1) . "</b></td><td><b>$overall_child_male</b></td><td><b>" . number_format(($overall_child_male / $patient_total) * 100, 1) . "</b></td><td><b>$overall_child_female</b></td><td><b>" . number_format(($overall_child_female / $patient_total) * 100, 1) . "</b></td></tr>";
			$row_string .= "</table>";
			$data['from'] = date('d-M-Y', strtotime($from));
			$data['dyn_table'] = $row_string;
			$this -> load -> view('reports/no_of_patients_receiving_art_byregimen_v', $data);
		}
	}

	public function cumulative_patients($from = "2013-06-06") {
		//Variables
		$facility_code = $this -> session -> userdata("facility");
		$status_totals = array();

		//Get Total Count of all patients
		$sql = "select count(*) as total from patient where(date_enrolled <= '$from' or date_enrolled='') and current_status is not null and facility_code='$facility_code'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$patient_total = $results[0]['total'];

		//Get Totals for each Status
		$sql = "select count(p.id) as total,current_status,ps.name from patient p,patient_status ps where(date_enrolled <= '$from' or date_enrolled='') and facility_code='$facility_code' and ps.id = current_status group by p.current_status";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			foreach ($results as $result) {
				$status_totals[$result['current_status']] = $result['total'];
				$current_status = $result['current_status'];
				//SQL for Adult Male Status
				$sql = "SELECT count(*) as total_adult_male, ps.Name,ps.id as current_status,r.name AS Service FROM patient p,patient_status ps,regimen_service_type r WHERE  p.current_status=ps.id AND p.service=r.id AND p.facility_code='$facility_code' AND p.gender=1  AND round(datediff('$from',p.dob)/360)>=15 GROUP BY p.current_status,service";
				$query = $this -> db -> query($sql);
				$results = $query -> result_array();
				if ($results) {
					foreach ($results as $result) {
						$status_name = $result['Name'];
						$status = $result['current_status'];
						$service = $result['service'];
						//Loop Through all service lines for Adult Male
						for ($i = 0; $i < 3; $i++) {
							//IF ART
							if ($service == "ART") {

							}
							//IF PEP
							if ($service == "PEP") {

							}
							//IF OI
							if ($service == "OI Only") {

							} else {

							}
						}
					}

				}
			}
		}
	}

	public function base_params($data) {
		$data['title'] = "Reports";
		$data['banner_text'] = "Facility Reports";
		$this -> load -> view('template', $data);
	}

}
