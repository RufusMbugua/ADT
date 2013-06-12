<!DOCTYPE html>
<html lang="en" >
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Patients Missing Appointments in Period</title>
		<link href="../CSS/style.css" type="text/css" rel="stylesheet"/>
		<link rel="SHORTCUT ICON" href="../Images/favicon.ico">
		<link href="../Scripts/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet" media="screen"/>
		<link href="../CSS/offline_css.css" type="text/css" rel="stylesheet"/>
		<link href="../CSS/jquery-ui.css" type="text/css" rel="stylesheet"/>
		<link href="TableFilter/filtergrid.css" type="text/css" rel="stylesheet"/>
		<script type="text/javascript" src="../Scripts/jquery.js"></script>
		<script type="text/javascript" src="../Scripts/jquery-ui.js"></script>
		<script type="text/javascript" src="../Scripts/offline_database.js"></script>
		<script type="text/javascript" src="../Scripts/offlineData.js"></script>
		<script type="text/javascript" src="TableFilter/tablefilter_all.js"></script>
		<script type="text/javascript" src="../Scripts/menus.js"></script>
		
	</head>
	<body>
		<style>
			
			select.flt {
				font-size: 14px;
			}
			h5 {
				margin: 10px;
			}
			hr{
				margin:0 auto;
				
			}
			
			.report_title {
				color:rgb(34, 86, 253);
				letter-spacing: 1px;
			}
			h2{
				margin:0.5em;
			}
			h4{
				font-size:18px;
			}
			
			.odd {
				background-color: rgb(226, 232, 255);
			}
			
			 select.pgSlc{
				height:30px;
				width:60px;
			}
		</style>
		<!-- Menus -->
		<div class="navbar ">
		  <div class="navbar-inner">
		    <div class="container">
		 
		      <!-- Everything you want hidden at 940px or less, place within here -->
		      <div class="nav-collapse collapse" id="acdmenu">
		      	<a class="brand" href="../reports.html"/><img src="../Images/home-icon.png"> | </a>
		        <ul class="nav">
				  <li class="dropdown">
				    <a href="#" class="dropdown-toggle" data-toggle="dropdown" id="standard_report">
				     Standard Reports
				    <b class="caret"></b>
				    </a>
				    <ul class="dropdown-menu" id="standard_report_sub">
				      <li><a href="#" id="patients_enrolled_in_period" class="report donor_date_range_report active_report">Number of Patients Enrolled in Period</a></li>
				      <li><a href="#" id="patients_enrolled_in_art" class="report donor_date_range_report active_report">Number of Patients Started on ART in the Period</a></li>
				      <li><a href="#" id="graph_patients_enrolled_in_year" class="report active_report annual_report">Graph of Number of Patients Enrolled Per Month in a Given Year</a></li>
				      <li><a href="#" id="cumulative_patients" class="report active_report single_date_report">Cumulative Number of Patients to Date</a></li>
				      <li><a href="#" id="patients_on_ART_active" class="report active_report single_date_report">Number of Active Patients Receiving ART (by Regimen)</a></li>
				    </ul>
				  </li>
				  <li class="dropdown divider-vertical" id="visiting_patient">
				    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
				     Visiting Patients
				    <b class="caret"></b>
				    </a>
				     <ul class="dropdown-menu" id="visiting_patient_sub">
				      <li><a href="#" id="patients_scheduled_to_visit" class="report active_report date_range_report">List of Patients Scheduled to Visit</a></li>
				      <li><a href="#" id="patients_started_on_date" class="report active_report date_range_report">List of Patients Started (on a Particular Date)</a></li>
				      <li><a href="#" id="patients_visitied_for_refill" class="report active_report date_range_report">List of Patients Visited for Refill</a></li>
				      <li><a href="#" id="patients_missing_appointments" class="report active_report date_range_report">Patients Missing Appointments</a></li>
				      <li><a href="#" id="patients_adherence" class="report active_report date_range_report">Patients Adherence Report</a></li>
				    </ul>
				  </li>
				  <li class="dropdown divider-vertical">
				    <a href="#" class="dropdown-toggle" data-toggle="dropdown" id="early_warning">
				     Early Warning Indicators
				    <b class="caret"></b>
				    </a>
				    <ul class="dropdown-menu " id="early_warning_sub">
				      <li><a href="#" id="patients_who_changed_regimen" class="report active_report date_range_report">Active Patients who Have Changed Regimens</a></li>
				      <li><a href="#" id="patients_starting" class="report active_report date_range_report">List of Patients Starting (By Regimen)</a></li>
				      <li><a href="#" id="early_warning_indicators" class="report active_report date_range_report">HIV Early Warning Indicators</a></li>
				      <li><a href="#" id="service_statistics" class="report active_report single_date_report">Service Statistics (By Regimen)</a></li>
				     
				    </ul>
				  </li>
				  <li class="dropdown divider-vertical">
				    <a href="#" class="dropdown-toggle" data-toggle="dropdown" id="drug_inventory">
				     Drug Inventory
				    <b class="caret"></b>
				    </a>
				    <ul class="dropdown-menu" id="drug_inventory_sub">
				      <li><a href="#" id="drug_consumption" class="report active_report annual_report">Drug Consumption Report</a></li>
				      <li><a href="#" id="drug_stock_on_hand" class="report active_report no_filter">Drug Stock on Hand Report</a></li>
				      <li><a href="#" id="commodity_summary" class="report active_report date_range_report">Facility Summary Commodity Report</a></li>
				      <li><a href="#" id="expiring_drugs" class="report active_report no_filter">Short Dated Stocks &lt;6 Months to Expiry</a></li>
				      <li><a href="#" id="expired_drugs" class="report active_report no_filter">List of Expired Drugs</a></li>
				    </ul>
				  </li>
				</ul>
		        
		      </div>
		 
		    </div>
		  </div>
		</div>
		<!-- Menus end -->
		
		<h2 id="facility_name" style="text-align: center"></h2>
		<h4 style="text-align: center">Listing of Patients Who Did Not Report In The Period 
		<br><br>
		Between <input type="text" id="start_date" value='<?php echo $from; ?>'> and <input type="text" id="end_date" value="<?php echo $to; ?>"></h4>
		<hr size="1" style="width:80%">
		<table align='center'  width='20%' style="font-size:16px; margin-bottom: 20px">
			<tr>
				<td colspan="2"><h5 class="report_title" style="text-align:center;font-size:14px;">Number of patients: <span id="total_count"><?php echo $all_count;?></span></h5></td>
			</tr>
		</table>
		
		<div id="appointment_list">
		<?php echo $dyn_table;?>
		</div>
		
		<!-- Pop up Window -->
		<input type="hidden" id="selected_report" />
		<div id="date_range_report" title="Select Date Range">
			<label> <strong class="label">From: </strong>
				<input type="text"name="date_range_from" id="date_range_from">
			</label>
			<label> <strong class="label">To: </strong>
				<input type="text"name="date_range_to" id="date_range_to">
			</label>
			<button id="generate_date_range_report" class="action_button" style="height:30px; font-size: 13px; width: 200px;">
				Generate Report
			</button>
		</div>
		<div id="donor_date_range_report" title="Select Date Range and Donor">
			<label> <strong class="label">Select Donor: </strong>
				<select name="donor" id="donor">
					<option value="0">All Donors</option><option value="1">GOK</option><option value="2">PEPFAR</option>
				</select> </label>
			<label> <strong class="label">From: </strong>
				<input type="text"name="donor_date_range_from" id="donor_date_range_from">
			</label>
			<label> <strong class="label">To: </strong>
				<input type="text"name="donor_date_range_to" id="donor_date_range_to">
			</label>
			<button id="donor_generate_date_range_report" class="action_button" style="height:30px; font-size: 13px; width: 200px;">
				Generate Report
			</button>
		</div>
		<div id="single_date">
			<label> <strong class="label">Select Date </strong>
				<input type="text"name="filter_date" id="single_date_filter">
			</label>
			<button id="generate_single_date_report" class="action_button" style="height:30px; font-size: 13px; width: 200px;">
				Generate Report
			</button>
		</div>
		<div id="year">
			<label> <strong class="label">Report Year: </strong>
				<input type="text"name="filter_year" id="single_year_filter">
			</label>
			<button id="generate_single_year_report" class="action_button" style="height:30px; font-size: 13px; width: 200px;">
				Generate Report
			</button>
		</div>
		<div id="no_filter">
			<button id="generate_no_filter_report" class="action_button" style="height:30px; font-size: 13px; width: 200px;">
				Generate Report
			</button>
		</div>
		<!-- Pop up Window end-->
	</body>
</html>