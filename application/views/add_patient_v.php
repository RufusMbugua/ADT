<!DOCTYPE html>
<html lang="en">
	<head>
		<script type="text/javascript">
		$(document).ready(function(){

			//Function to Check Patient Numner exists
			var base_url="<?php echo base_url();?>";

		    $("#patient_number").change(function(){
				var patient_no=$("#patient_number").val();
				var link=base_url+"patient_management/checkpatient_no/"+patient_no;
				$.ajax({
				    url: link,
				    type: 'POST',
				    success: function(data) {
				        if(data==1){
				          alert("Patient Number Matches an existing record");
				        }
				    }
				});
	        });
	        
	        //Attach date picker for date of birth
	        $("#dob").datepicker({
					yearRange : "-120:+0",
					maxDate : "0D",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true,
			});

					
			//Function to calculate age in years and months
			$("#dob").change(function() {
					var dob = $(this).val();
					dob = new Date(dob);
					var today = new Date();
					var age_in_years = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
					$("#age_in_years").attr("value", age_in_years);
					//calculate age in months
					var yearDiff = today.getFullYear() - dob.getFullYear();
					var y1 = today.getFullYear();
					var y2 = dob.getFullYear();
					var age_in_months = (today.getMonth() + y1 * 12) - (dob.getMonth() + y2 * 12);
					$("#age_in_months").attr("value", age_in_months);

			});
			
			//Function to check if female is pregnant
			$("#gender").change(function() {
					var selected_value = $(this).attr("value");
					//if female, display the prengancy selector
					if(selected_value == 2) {
						//If female show pregnant container
						$('#pregnant_view').slideDown('slow', function() {

						});
					} else {
						//If male do not show pregnant container
						$('#pregnant_view').slideUp('slow', function() {

						});
					}
			});
			
			//Attach date picker for date of enrollment
			$("#enrolled").datepicker({
					yearRange : "-30:+0",
					maxDate : "0D",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true
			});
			
			$("#enrolled").datepicker('setDate', new Date());
			
			
			//Attach date picker for date of start regimen 
			$("#service_started").datepicker({
					yearRange : "-30:+0",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true,
					maxDate : "0D"
			});
			
			$("#service_started").datepicker('setDate', new Date());
			
			//Function to display transfer from list if patient source is(transfer in)
				$("#source").change(function() {
					var selected_value = $(this).val();
					if(selected_value == 3) {
						$("#patient_source_listing").show();
					} else {
						$("#patient_source_listing").hide();
						$("#transfer_source").attr("value",'');
					}
				});
				
		   //Function to display Regimens in this line
		   $("#service").change(function() {
		   	$("#regimen option").remove();
		   	  var service_line = $(this).val();
		   	  var link=base_url+"regimen_management/getRegimenLine/"+service_line;
				$.ajax({
				    url: link,
				    type: 'POST',
				    dataType: "json",
				    success: function(data) {	
				    	$("#regimen").append($("<option></option>").attr("value",'').text('--Select One--'));
				    	$.each(data, function(i, jsondata){
				    		$("#regimen").append($("<option></option>").attr("value",jsondata.id).text(jsondata.Regimen_Code+" | "+jsondata.Regimen_Desc));
				    	});
				    }
				});
		   });
		   
		   //Function to display tb phases
		   $(".tb").change(function() {
		   	    var tb = $(this).val();
		   	     if(tb == 1) {
				    $("#tbphase_view").show();
				 } 
				 else {
					$("#tbphase_view").hide();
					$("#fromphase_view").hide();
				 	$("#tophase_view").hide();
					$("#tbphase").attr("value",'0');
					$("#fromphase").attr("value",'');
		   	        $("#tophase").attr("value",'');
			     }
		   });
		   
		   //Function to display tbphase dates
		   $(".tbphase").change(function() {
		   	    var tbpase = $(this).val();
		   	    $("#fromphase").attr("value",'');
		   	    $("#tophase").attr("value",'');
		   	     if(tbpase ==3) {
		   	     	$("#fromphase_view").hide();
				    $("#tophase_view").show();
				    $("#tb").val(0);
				 } 
				 else if(tbpase==0){
				 	$("#fromphase_view").hide();
				 	$("#tophase_view").hide();
				 }else {
					$("#fromphase_view").show();
				    $("#tophase_view").show();
			     }
		   });
		   
		   //Function to display datepicker for tb fromphase
		   $("#fromphase").datepicker({
					maxDate : "0D",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true
			});
			
			//Function to display datepicker for tb tophase
			$("#tophase").datepicker({
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true
			});
			
			
			//Function to calculate date ranges for tb stages
			$("#fromphase").change(function(){
				  var from_date=$(this).val();
				  var new_date=new Date(from_date);
				  var to_date=new Date();
				  var tbphase=$(".tbphase").val();
				  if(tbphase==1){
				  	//Intensive
				  	 var numberOfDaysToAdd=56;
				  }else if(tbphase==2){
				  	//Continuation
				  	 var numberOfDaysToAdd=112;
				  }
				  to_date.setDate(new_date.getDate() + numberOfDaysToAdd);
				  $("#tophase").datepicker('setDate', new Date(to_date));
			});
			
			//Function to configure multiselect in family planning and other chronic illnesses
			$("#family_planning").multiselect().multiselectfilter();
			$("#other_illnesses").multiselect().multiselectfilter();
			
			//To Disable Textareas
			$("textarea[name='other_chronic']").not(this).attr("disabled", "true");
			$("textarea[name='other_drugs']").not(this).attr("disabled", "true");
			$("textarea[name='other_allergies_listing']").not(this).attr("disabled", "true");
			$("textarea[name='support_group_listing']").not(this).attr("disabled", "true");

			
			
			//Function to enable textareas for other chronic illnesses
			$("#other_other").change(function() {
					var other = $(this).is(":checked");
					if(other){
						$("textarea[name='other_chronic']").not(this).removeAttr("disabled");
					}else{
						$("textarea[name='other_chronic']").not(this).attr("disabled", "true");
					}
			});
			
			//Function to enable textareas for other allergies
			$("#other_drugs_box").change(function() {
					var other = $(this).is(":checked");
					if(other){
						$("textarea[name='other_drugs']").not(this).removeAttr("disabled");
					}else{
						$("textarea[name='other_drugs']").not(this).attr("disabled", "true");
					}
			});
			
			//Function to enable textareas for other allergies
			$("#other_allergies").change(function() {
					var other = $(this).is(":checked");
					if(other){
						$("textarea[name='other_allergies_listing']").not(this).removeAttr("disabled");
					}else{
						$("textarea[name='other_allergies_listing']").not(this).attr("disabled", "true");
					}
			});
			
			//Function to enable textareas for support group
			$("#support_group").change(function() {
					var other = $(this).is(":checked");
					if(other){
						$("textarea[name='support_group_listing']").not(this).removeAttr("disabled");
					}else{
						$("textarea[name='support_group_listing']").not(this).attr("disabled", "true");
					}
			});
			
			$("input[name='save']").click(function(){
				var direction=$(this).attr("direction");
				$("#direction").val(direction);
			});

	   });
	   //Function to calculate BSA
		function getMSQ() {
		   var weight = $('#weight').attr('value');
		   var height = $('#height').attr('value');
		   var MSQ = Math.sqrt((parseInt(weight) * parseInt(height)) / 3600);
		   $('#surface_area').attr('value', MSQ);
	    }
	    
	    //Function to validate required fields
	    function processData(form){  
	          var form_selector = "#" + form;
	          var validated = $(form_selector).validationEngine('validate');
	            var family_planning = $("select#family_planning").multiselect("getChecked").map(function() {
					return this.value;
				}).get();
				var other_illnesses = $("select#other_illnesses").multiselect("getChecked").map(function() {
					return this.value;
				}).get();
				$("#family_planning_holder").val(family_planning);
				$("#other_illnesses_holder").val(other_illnesses);
	            if(!validated) {
                   return false;
	            }else{
	            	$(".btn").attr("disabled","disabled");
	            	//return true;
	            }
	     }
		</script>

	</head>

	<body>
		<div class="full-content" style="background:#80f26d">
			<div id="sub_title" >
				<a href="<?php  echo base_url().'patient_management ' ?>">Patient Listing </a> <i class=" icon-chevron-right"></i> <strong>Add Patients</strong>
				<hr size="1">
			</div>
			<h3>Patient Registration
			<div style="float:right;margin:5px 40px 0 0;">
				(Fields Marked with <b><span class='astericks'>*</span></b> Asterisks are required)
			</div></h3>

			<form id="add_patient_form" method="post"  action="<?php echo base_url().'patient_management/save';?>" onsubmit="return processData('add_patient_form')" >
				<div class="column" id="columnOne">
					<fieldset>
						<legend>
							Patient Information &amp; Demographics
						</legend>
						<div class="max-row">
							<div class="mid-row">
								<label> Medical Record No.</label>
								<input type="text" name="medical_record_number" id="medical_record_number" value="">
							</div>
							<div class="mid-row">
								<label> <span class='astericks'>*</span>Patient Number CCC </label>
								<input type="text"name="patient_number" id="patient_number" class="validate[required]">
							</div>
						</div>
						<div class="max-row">
							<label><span class='astericks'>*</span>Last Name</label>
							<input  type="text"name="last_name" id="last_name" class="validate[required]">
						</div>
						<div class="max-row">
							<div class="mid-row">
								<label><span class='astericks'>*</span>First Name</label>
								<input type="text"name="first_name" id="first_name" class="validate[required]">
							</div>

							<div class="mid-row">
								<label>Other Name</label>
								<input type="text"name="other_name" id="other_name">
							</div>
						</div>
						<div class="max-row">
							<div class="mid-row">
								<label><span class='astericks'>*</span>Date of Birth</label>
								<input type="text"name="dob" id="dob" class="validate[required]">
							</div>
							<div class="mid-row">
								<label> Place of Birth </label>
								<select name="pob" id="pob">
									<option value=" ">--Select--</option>
									<?php
									foreach($districts as $district){
										echo "<option value='".$district['id']."'>".$district['Name']."</option>";
									}
									?>
								</select>
							</div>
						</div>
						<div class="max-row">
							<div class="mid-row">
							<label>Age(Years)</label>
							<input type="text" id="age_in_years" name="age_in_years" disabled="disabled"/>
							</div>
							<div class="mid-row">
							<label>Age(Months)</label>
							<input type="text" id="age_in_months" disabled="disabled"/>
							</div>
						</div>
						<div class="max-row">
							<div class="mid-row">
								<label><span class='astericks'>*</span>Gender</label>
								<select name="gender" id="gender" class="validate[required]">
									<option value=" ">--Select--</option>
									<?php
									foreach($genders as $gender){
										echo "<option value='".$gender['id']."'>".$gender['name']."</option>";
									}
									?>
								</select>
							</div>
							<div id="pregnant_view" class="mid-row" style="display:none;">
								<label id="pregnant_container"> Pregnant?</label>
								<select name="pregnant" id="pregnant">
									<option value="0">No</option><option value="1">Yes</option>
								</select>
							</div>
						</div>
						<div class="max-row">
							<div class="mid-row">
								<label><span class='astericks'>*</span>Weight (KG)</label>
								<input type="text"name="weight" id="weight" class="validate[required]" onblur="getMSQ()">
							</div>
							<div class="mid-row">
								<label ><span class='astericks'>*</span>Height (CM)</label>
								<input  type="text"name="height" id="height" class="validate[required]" onblur="getMSQ()">
							</div>
						</div>
						<div class="max-row">
							<label><span class='astericks'>*</span> Body Surface Area (MSQ)</label>
							<input type="text" name="surface_area" id="surface_area" value="" readonly="readonly" class="validate[required]">

						</div>
						<div class="max-row">
							<div class="mid-row">
								<label> Patient's Phone Contact(s)</label>
								<input  type="text"  name="phone" id="phone" value="" class="phone" placeholder="e.g 0722123456">
							</div>
							<div class="mid-row">
								<label > Receive SMS Reminders</label>
								<input  type="radio"  name="sms_consent" value="1">
								Yes
								<input  type="radio"  name="sms_consent" value="0" checked="checked">
								No
							</div>

						</div>
						<div class="max-row">
							<label> Patient's Physical Contact(s)</label>
							<textarea name="physical" id="physical" value=""></textarea>
						</div>
						<div class="max-row">
							<label> Patient's Alternate Contact(s)</label>
							<input type="text" name="alternate" id="alternate" class="phone" value="" placeholder="e.g 0722123456">
						</div>
						
					   <div class="max-row">
							<label>Does Patient belong to any support group?</label>
							<label>Yes
								<input type="checkbox" name="support_group" id="support_group" value="">
							</label>

							<div class="list">
								List Them
							</div>
							<textarea class="list_area" name="support_group_listing" id="support_group_listing"></textarea>
						</div>

				</div>

				<div class="column" id="colmnTwo">
					<fieldset>
						<legend>
							Program History
						</legend>
						<div class="max-row">
							<label  id="tstatus"> Partner Status</label>
							<select name="partner_status" id="partner_status">
								<option value="0" selected="selected">No Partner</option>
								<option value="1" > Concordant</option>
								<option value="2" > Discordant</option>
							</select>

						</div>
						<div class="max-row">
							<div class="mid-row">
								<label id="dcs" >Disclosure</label>
								<input  type="radio"  name="disclosure" value="1">
								Yes
								<input  type="radio"  name="disclosure" value="0">
								No
							</div>
						</div>
						<div class="max-row">
							<label>Family Planning Method</label>
							<input type="hidden" id="family_planning_holder" name="family_planning_holder" />
							<select name="family_planning" id="family_planning" multiple="multiple" style="width:400px;" >
								<?php
								    foreach($family_planning as $fplan){
										echo "<option value='".$fplan['indicator']."'>"." ".$fplan['name']."</option>";
									}
								?>
							</select>

						</div>
						<div class="max-row">
							<label>Does Patient have other Chronic illnesses</label>
							<input type="hidden" id="other_illnesses_holder" name="other_illnesses_holder" />
							<select name="other_illnesses" id="other_illnesses"  multiple="multiple" style="width:400px;">
								<?php
								    foreach($other_illnesses as $other_illness){
										echo "<option value='".$other_illness['indicator']."'>"." ".$other_illness['name']."</option>";
									}
								?>	
							</select>
						</div>
						<div class="max-row">
							If <b>Other Illnesses</b> 
								<br/>Click Here <input type="checkbox" name="other_other" id="other_other" value=""> 
								<br/>List Them Below (Use Commas to separate) 
							
							<textarea  name="other_chronic" id="other_chronic"></textarea>
						</div>
						<div class="max-row">
							<label> List Other Drugs Patient is Taking </label>
							<label>Yes
								<input type="checkbox" name="other_drugs_box" id="other_drugs_box" value="">
							</label>

							<label>List Them</label>
							<textarea name="other_drugs" id="other_drugs"></textarea>
						</div>
						<div class="max-row">
							<label>Does Patient have any Drugs Allergies/ADR</label>
 
							<label>Yes
								<input type="checkbox" name="other_allergies" id="other_allergies" value="">
							</label>

							<label>List Them</label>
							<textarea class="list_area" name="other_allergies_listing" id="other_allergies_listing"></textarea>
						</div>
						<div class="max-row">
						   <div class="mid-row">
							<label > Does Patient <br/>Smoke?</label>
							<select name="smoke" id="smoke">
								<option value="0" selected="selected">No</option>
								<option value="1">Yes</option>
							</select>
						   </div>
						   <div class="mid-row">
							<label> Does Patient Drink Alcohol?</label>
							<select name="alcohol" id="alcohol">
								<option value="0" selected="selected">No</option>
								<option value="1">Yes</option>
							</select>
						   </div>
						</div>
						
						<div class="max-row">
							<div class="mid-row">
								<label> Does Patient Have TB?</label>
								<select name="tb" id="tb" class="tb">
									<option value="0" selected="selected">No</option>
									<option value="1">Yes</option>
								</select>
							</div>
							<div class="mid-row" id="tbphase_view" style="display:none;">
								<label id="tbstats"> TB Phase</label>
								<select name="tbphase" id="tbphase" class="tbphase">
									<option value="0" selected="selected">--Select One--</option>
									<option value="1">Intensive</option>
									<option value="2">Continuation</option>
									<option value="3">Completed</option>
								</select>
							</div>
						</div>
						<div class="max-row">
							<div class="mid-row" id="fromphase_view" style="display:none;">
								<label id="ttphase">Start of Phase</label>
								<input type="text" name="fromphase" id="fromphase" value=""/>
							</div>
							<div class="mid-row" id="tophase_view" style="display:none;">
								<label id="endp">End of Phase</label>
								<input type="text" name="tophase" id="tophase" value=""/>
							</div>
						</div>
					</fieldset>
				</div>
				<div class="column" id="columnThree">
					<fieldset>
						<legend>
							Patient Information
						</legend>
						<div class="max-row">
							<label><span class='astericks'>*</span>Date Patient Enrolled</label>
							<input type="text" name="enrolled" id="enrolled" value="" class="validate[required]">
						</div>
						<div class="max-row">
							<label><span class='astericks'>*</span>Current Status</label>
							<select name="current_status" id="current_status" class="validate[required]">
							<option value="">--Select--</option>
								<?php
								    foreach($statuses as $status){
								        if(strtolower($status['Name'])=="active"){
											echo "<option selected='selected' value='".$status['id']."'>".$status['Name']."</option>";											
										}else{
											echo "<option value='".$status['id']."'>".$status['Name']."</option>";
										}
									}
								?>	
							</select>
						</div>

						<div class="max-row">
							<label><span class='astericks'>*</span>Source of Patient</label>
							<select name="source" id="source" class="validate[required]">
								<option value="">--Select--</option>
								<?php
								    foreach($sources as $source){
								    	echo "<option value='".$source['id']."'>".$source['Name']."</option>";	
									}
								?>	
							</select>
						</div>
						<div id="patient_source_listing" class="max-row" style="display:none;">
							<label> Transfer From</label>
							<select name="transfer_source" id="transfer_source" style="width:200px;">
							<option value="">--Select--</option>
								<?php
								    foreach($facilities as $facility){
										echo "<option value='".$facility['facilitycode']."'>".$facility['name']."</option>";
									}
								?>		
							</select>
						</div>
						<div class="max-row">
							<label><span class='astericks'>*</span>Patient Supported by</label>
							<select name="support" id="support" class="validate[required]">
								<option value="">--Select--</option>
								<?php
								    foreach($supporters as $supporter){
										echo "<option value='".$supporter['id']."'>".$supporter['Name']."</option>";
									}
								?>	
							</select>
						</div>
						<div class="max-row">
							<label><span class='astericks'>*</span>Type of Service</label>
							<select name="service" id="service" class="validate[required]">
								<option value="">--Select--</option>
								<?php
								    foreach($service_types as $service_type){
										echo "<option value='".$service_type['id']."'>".$service_type['Name']."</option>";
									}
								?>	
							</select> </label>
							</select>
						</div>
						<div class="max-row">
							<label id="start_of_regimen"><span class='astericks'>*</span>Start Regimen </label>
							<select name="regimen" id="regimen" class="validate[required] start_regimen" >
								<option value=" ">--Select One--</option>
								
							</select>

						</div>
						<div class="max-row">
							<label id="date_service_started"><span class='astericks'>*</span>Start Regimen Date</label>
							<input type="text" name="service_started" id="service_started" value="" class="validate[required]">
						</div>
					</fieldset>
				</div>
				<div class="button-bar">
					<div class="btn-group">
						<input type="hidden" name="direction" id="direction" />
						<input form="add_patient_form" type="submit" class="btn actual" direction="0" value="Save" name="save"/>
						<input form="add_patient_form" type="submit" class="btn actual" direction="1" value="Dispense" name="save"/>
						<input type="reset"  class="btn btn-danger" value="Reset"/>
					</div>
					
				</div>

			</form>
		</div>
	</body>
</html>