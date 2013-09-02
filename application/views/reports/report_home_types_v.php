<style>
	select{
		width:auto;
	}
</style>
<div id="standard_report_sub">
	<table >
		<!-- Standard reports -->
		<tr id="standard_report_row" class="reports_types">
			<td><label >Select Report </label></td>
			<td>
			<select id="standard_report_select" class="input-large select_report">
				<option  value="0" class="none">-- Select a Report  --</option>
				<option class="donor_date_range_report" value="patient_enrolled">Number of Patients Enrolled in Period</option>
				<option class="donor_date_range_report" value="getStartedonART">Number of Patients Started on ART in the Period</option>
				<option class="annual_report" value="graph_patients_enrolled_in_year">Graph of Number of Patients Enrolled Per Month in a Given Year</option>
				<option class="single_date_report" value="cumulative_patients">Cumulative Number of Patients to Date</option>
				<option class="single_date_report" value="service_statistics">Number of Active Patients Receiving ART (by Regimen)</option>
				<option class="single_date_report" value="getFamilyPlanning">Family Planning Summary</option>
				<option class="date_range_report" value="getIndications">Patient Indications Summary</option>
				<option class="date_range_report" value="getTBPatients">TB Stages Summary</option>
				<option class="single_date_report" value="getChronic">Chronic Illnesses Summary</option>
				<option class="single_date_report" value="getADR">Patient Allergy Summary</option>
			</select></td>
		</tr>
		<!-- Visiting patients reports -->
		<tr id="visiting_patient_report_row" class="reports_types">
			<td><label >Select Report </label></td>
			<td>
			<select id="visiting_patient_report_select" class="input-large select_report">
				<option value="0" class="none">-- Select a Report  --</option>
				<option class="date_range_report" value="getScheduledPatients">List of Patients Scheduled to Visit</option>
				<option class="date_range_report" value="getPatientsStartedonDate">List of Patients Started (on a Particular Date)</option>
				<option class="date_range_report" value="getPatientsforRefill">List of Patients Visited For Refill</option>
				<option class="date_range_report" value="getPatientMissingAppointments">Patients Missing Appointments</option>
				<option class="date_range_report" value="patients_adherence">Patients Adherence Report</option>
				<option class="date_range_report" value="patients_disclosure">Patients Status &amp; Disclosure</option>
			</select></td>
		</tr>
		<!-- Early warning reports -->
		<tr id="early_warning_report_row" class="reports_types">
			<td><label >Select Report </label></td>
			<td>
			<select id="early_warning_report_select" class="input-large select_report">
				<option value="0" class="none">-- Select a Report  --</option>
				<option class="date_range_report" value="patients_who_changed_regimen">Active Patients who Have Changed Regimens</option>
				<option class="date_range_report" value="patients_starting">List of Patients Starting (By Regimen)</option>
				<option class="date_range_report" value="early_warning_indicators">HIV Early Warning Indicators</option>
				<!--<option class="single_date_report" value="service_statistics">Service Statistics (By Regimen)</option>-->
				<option class="single_date_report" value="getBMI">Patient BMI Summary</option>
			</select></td>
		</tr>
		<!-- Drug inventory reports -->
		<tr id="drug_inventory_report_row" class="reports_types">
			<td><label >Select Report </label></td>
			<td>
			<select id="drug_inventory_report__select" class="input-large select_report">
				<option value="0" class="none">-- Select a Report  --</option>
				<option id="drug_consumption" class="annual_report" value="stock_report/drug_consumption">Drug Consumption Report</option>
				<option id="drug_stock_on_hand" class="no_filter" value="stock_report/drug_stock_on_hand">Drug Stock on Hand Report</option>
				<option id="commodity_summary" class="date_range_report" value="stock_report/commodity_summary">Facility Summary Commodity Report</option>
				<option id="expiring_drugs" class="no_filter" value="expiring_drugs">Short Dated Stocks &lt;6 Months to Expiry</option>
				<option id="expired_drugs" class="no_filter" value="expired_drugs">List of Expired Drugs</option>
				<option id="getFacilityConsumption" class="date_range_report" value="getFacilityConsumption">Stock Consumption</option>
				<option id="getDailyConsumption" class="date_range_report" value="getDailyConsumption">Daily Drug Consumption</option>
				<option id="getDrugsIssued" class="date_range_report" value="getDrugsIssued">Drugs Issued To</option>
				<option id="getDrugsReceived" class="date_range_report" value="getDrugsReceived">Drugs Received From</option>
			</select></td>
		</tr>
		<tr>
			<!-- Select report range donors -->
			<table id="donor_date_range_report" class="select_types">
				<tr>
					<td><label >Select Donor : </label></td>
					<td>
					<select id="donor" class="input-medium">
						<option value="0">--All Donor--</option>
						<option value="1">GOK</option>
						<option value="2">PEPFAR</option>
					</select></td>
				</tr>
				<tr>
					<td><label>From : </label></td>
					<td>
					<input type="text" name="donor_date_range_from" id="donor_date_range_from" class="input-medium donor_input_dates_from">
					</td>
					<td><label >To : </label></td>
					<td>
					<input type="text" name="donor_date_range_to" id="donor_date_range_to" class="input-medium donor_input_dates_to">
					</td>
				</tr>
				<tr>
					<td>
					<input type="button" id="donor_generate_date_range_report" class="btn generate_btn" value="Generate Report">
					</td>
				</tr>
			</table>
			<!-- Report year -->
			<table id="year" class="select_types">
				<tr>
					<td><label>Select Year : </label></td>
					<td>
					<input type="text" name="filter_year" id="single_year_filter" class="input-medium input_year" />
					</td>
					<td>
					<input type="button" id="generate_single_year_report" class="btn generate_btn" value="Generate Report">
					</td>
				</tr>
			</table>
			<!-- Report single date -->
			<table id="single_date" class="select_types">
				<tr>
					<td><label>Select Date : </label></td>
					<td>
					<input type="text" name="filter_date" id="single_date_filter" class="input-medium input_dates" />
					</td>
					<td>
					<input type="button" id="generate_single_date_report" class="btn generate_btn" value="Generate Report">
					</td>
				</tr>
			</table>
			<!-- Report date range -->
			<table id="date_range_report" class="select_types">
				<tr>
					<td class="show_report_type"><label>Select Report Type :</label></td>
					<td class="show_report_type">
					<select name="commodity_summary_report_type" id="commodity_summary_report_type" class="report_type input-large">
						<option value="0">-- Select Report Type --</option>
						<option value="1">Main Store</option>
						<option value="2">Pharmacy</option>
					</select></td>
					<td><label >From: </label></td>
					<td>
					<input type="text" name="date_range_from" id="date_range_from" class="input-medium input_dates_from">
					</td>
					<td><label >To: </label></td>
					<td>
					<input type="text" name="date_range_to" id="date_range_to" class="input-medium input_dates_to">
					</td>
					<td>
					<input type="button" id="generate_date_range_report" class="btn generate_btn" value="Generate Report">
					</td>
				</tr>
			</table>
			<!-- Reports no filter -->
			<table id="no_filter" class="select_types">
				<tr  >
					<td class="show_report_type"><label>Select Report Type :</label></td>
					<td class="show_report_type">
					<select name="commodity_summary_report_type_1" id="commodity_summary_report_type_1" class="report_type input-large">
						<option value="0">-- Select Report Type --</option>
						<option value="1">Main Store</option>
						<option value="2">Pharmacy</option>
					</select></td>
					<td>
					<input type="button" id="generate_no_filter_report" class="btn generate_btn" value="Generate Report">
					</td>
				</tr>
			</table>
		</tr>
	</table>
</div>
