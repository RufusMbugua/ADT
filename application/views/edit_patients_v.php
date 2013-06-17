<<<<<<< HEAD
<?php
foreach($results as $result){
	
}
?>
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
					changeYear : true
			});
			
			$("#medical_record_number").val("<?php echo $result['medical_record_number'];?>");
			$("#patient_number").val("<?php echo $result['patient_number_ccc'];?>");
			$("#last_name").val("<?php echo $result['last_name'];?>");
			$("#first_name").val("<?php echo $result['first_name'];?>");
			$("#other_name").val("<?php echo $result['other_name'];?>");
			$("#dob").val("<?php echo $result['dob'];?>");
			$("#pob").val("<?php echo $result['pob'];?>");
			$("#gender").val("<?php echo $result['gender'];?>");
			
			//Display Gender Tab
			if($("#gender").val()==2){
				$("#pregnant_view").show();
			}
			
			
			$('#start_age').val(getStartAge("<?php echo $result['dob'];?>","<?php echo $result['date_enrolled'];?>"));
			$('#age').val(getAge("<?php echo $result['dob'];?>"));
	        $('#start_weight').val("<?php echo $result['start_weight'];?>");
	        $('#start_height').val("<?php echo $result['start_height'];?>");
	        $('#start_bsa').val("<?php echo $result['start_bsa'];?>");
	        $('#current_weight').val("<?php echo $result['weight'];?>");
	        $('#current_height').val("<?php echo $result['height'];?>");
	        $('#current_bsa').val("<?php echo $result['sa'];?>");
	        $('#physical').val("<?php echo $result['physical'];?>");
	        $('#alternate').val("<?php echo $result['alternate'];?>");
	        
	        $('#partner_status').val("<?php echo $result['partner_status'];?>");
	        $('#disclosure').val("<?php echo $result['disclosure'];?>");
	        
	        //Function to configure multiselect in family planning and other chronic illnesses
			$("#family_planning").multiselect().multiselectfilter();
			$("#other_illnesses").multiselect().multiselectfilter();
			
			//Select Family Planning Methods Selected
			var family_planning="<?php echo $result['fplan'];?>";
			
				if(family_planning != null || family_planning != " ") {
					var fplan = family_planning.split(',');
					for(var i = 0; i < fplan.length; i++) {
						$("select#family_planning").multiselect("widget").find(":checkbox[value='" + fplan[i] + "']").each(function() {
	                       $(this).click();
	                    });
					}
				}
				
			//To Disable Textareas
			$("textarea[name='other_chronic']").not(this).attr("disabled", "true");
			$("textarea[name='other_drugs']").not(this).attr("disabled", "true");
			$("textarea[name='other_allergies_listing']").not(this).attr("disabled", "true");
			$("textarea[name='support_group_listing']").not(this).attr("disabled", "true");
			
				
			//Select Other Illnesses Methods Selected
			var other_illnesses='<?php echo $result['other_illnesses'];?>';
			
			if (other_illnesses.indexOf(',') == -1) {
              other_illnesses=other_illnesses+",";
            }else{
              other_illnesses=other_illnesses;
            }
			var other_sickness="";
				if(other_illnesses != null || other_illnesses != " ") {
					var other_ill = other_illnesses.split(',');
					for(var i = 0; i < other_ill.length; i++) {
						$("select#other_illnesses").multiselect("widget").find(":checkbox[value='" + other_ill[i] + "']").each(function() {
	                       $(this).click();
	                    });
	                   if(other_ill[i].charAt(0) !="-"){
	                   	other_sickness+=","+other_ill[i];
	                   }
					}
					$("#other_chronic").val(other_sickness.substring(1));
				}

			if($("#other_chronic").val()){
				$("input[name='other_other']").not(this).attr("checked", "true");
			    $("textarea[name='other_chronic']").not(this).removeAttr("disabled");		
			}

            $("#other_drugs").val("<?php echo $result['other_drugs']?>");

            if($("#other_drugs").val()){
				$("input[name='other_drugs_box']").not(this).attr("checked", "true");
			    $("textarea[name='other_drugs']").not(this).removeAttr("disabled");		
			}
			
			//To Check Disclosure
			var disclosure="<?php echo $result['disclosure'];?>";
			if(disclosure==1){
			$("#disclosure_yes").attr("checked", "true");	
			}else if(disclosure==0){
			$("#disclosure_no").attr("checked", "true");	
			}
			
			$("#other_allergies_listing").val("<?php echo $result['adr']?>");

            if($("#other_allergies_listing").val()){
				$("input[name='other_allergies']").not(this).attr("checked", "true");
			    $("textarea[name='other_allergies_listing']").not(this).removeAttr("disabled");		
			}
			
			 $("#support_group_listing").val("<?php echo $result['support_group']?>");

            if($("#support_group_listing").val()){
				$("input[name='support_group']").not(this).attr("checked", "true");
			    $("textarea[name='support_group_listing']").not(this).removeAttr("disabled");		
			}
			
			$('#smoke').val("<?php echo $result['smoke'];?>");
			$('#alcohol').val("<?php echo $result['alcohol'];?>");	
			
			$("#tb").val("<?php echo $result['tb']; ?>");
			
			if($("#tb").val()==1){
				$("#tbphase_view").show();
				$("#tbphase").val("<?php echo $result['tbphase']; ?>");
				$("#fromphase").val("<?php echo $result['startphase']; ?>");
				$("#tophase").val("<?php echo $result['endphase']; ?>");
				
				 if($("#tbphase").val() ==3) {
		   	     	$("#fromphase_view").hide();
				    $("#tophase_view").show();
				 } 
				 else if($("#tbphase").val()==0){
				 	$("#fromphase_view").hide();
				 	$("#tophase_view").hide();
				 }else {
					$("#fromphase_view").show();
				    $("#tophase_view").show();
					$("#transfer_source").attr("value",'');
			     }
			}

				

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
				 } 
				 else if(tbpase==0){
				 	$("#fromphase_view").hide();
				 	$("#tophase_view").hide();
				 }else {
					$("#fromphase_view").show();
				    $("#tophase_view").show();
					$("#transfer_source").attr("value",'');
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
			
			//Attach date picker for date of enrollment
			$("#enrolled").datepicker({
					yearRange : "-30:+0",
					maxDate : "0D",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true
			});
			$("#enrolled").val("<?php echo $result['date_enrolled'] ?>");
			$("#current_status").val("<?php echo $result['current_status'] ?>");
			$("#status_started").val("<?php echo $result['status_change_date'] ?>");
			$("#source").val("<?php echo $result['source'] ?>");
			$("#support").val("<?php echo $result['supported_by'] ?>");
			
			$("#service").val("<?php echo $result['service'] ?>");
			$("#service_started").val("<?php echo $result['start_regimen_date'] ?>");
			
			$("#regimen").val("<?php echo $result['start_regimen'] ?>");
			$("#current_regimen").val("<?php echo $result['current_regimen'] ?>");
			
			//Attach date picker for date of status change
			$("#status_started").datepicker({
					yearRange : "-30:+0",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					maxDate : "0D",
					changeYear : true
			});
			
			//Attach date picker for date of start regimen 
			$("#service_started").datepicker({
					yearRange : "-30:+0",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true,
					maxDate : "0D"
			});
			
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
		   
		   $("#next_appointment_date").datepicker({
	         yearRange : "-30:+0",
	         dateFormat : $.datepicker.ATOM,
	         changeMonth : true,
	         changeYear : true
	       });
	       
	       $("#next_appointment_date").val("<?php echo $result['nextappointment'];?>");
	       $("#prev_appointment_date").val("<?php echo $result['nextappointment'];?>");
	       
	       var appointment=$("#next_appointment_date").val();
	       var days = getDays(appointment);
	       if(days>=0){
	       $('#days_to_next').attr("value", days);
	       }
	       
	       $("#next_appointment_date").change(function(){
	       	    var appointment=$(this).val();
	       	    var days = getDays(appointment);
	       	    $('#days_to_next').attr("value", days);
	       });
	       
	       $("#days_to_next").change(function() {
	           var days = $("#days_to_next").attr("value");
	           var base_date = new Date();
	           var appointment_date = $("#next_appointment_date");
	           var today = new Date(base_date.getFullYear(), base_date.getMonth(), base_date.getDate());
	           var today_timestamp = today.getTime();
	           var appointment_timestamp = (1000 * 60 * 60 * 24 * days) + today_timestamp;
	           appointment_date.datepicker("setDate", new Date(appointment_timestamp));
	       });
	       
	       
	       //Function to display tranfer From	
	       if($("#source").val()==3){
	       	$("#patient_source_listing").show();
	       }       
	       $("#transfer_source").val("<?php echo $result['transfer_from']; ?>");
				
		});
			function getMSQ() {
			  var weight = $('#current_weight').attr('value');
			  var height = $('#current_height').attr('value');
			  var MSQ = Math.sqrt((parseInt(weight) * parseInt(height)) / 3600);
			  $('#current_bsa').attr('value', MSQ);
			}
		
			function getStartMSQ() {
			  var weight = $('#start_weight').attr('value');
			  var height = $('#start_height').attr('value');
			  var MSQ = Math.sqrt((parseInt(weight) * parseInt(height)) / 3600);
			  $('#start_bsa').attr('value', MSQ);
			}
		
			function getDays(dateString) {
		        var base_date = new Date();
		        var today = new Date(base_date.getFullYear(), base_date.getMonth(), base_date.getDate());
		        var today_timestamp = today.getTime();
		        var one_day = 1000 * 60 * 60 * 24;
		        var appointment_timestamp = new Date(Date.parse(dateString, "YYYY/MM/dd")).getTime();
		        var difference = appointment_timestamp - today_timestamp;
		        var days_difference = Math.ceil(difference / one_day);
		        return (days_difference-1);
		    }

		
		
			function getStartAge(dateString, baseDate) {
	            var today = new Date(baseDate);
	            var birthDate = new Date(dateString);
	            var age = today.getFullYear() - birthDate.getFullYear();
	            var m = today.getMonth() - birthDate.getMonth();
	                if(m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
	                 age--;
	                }
	                if(isNaN(age)) {
	                 return "N/A";
	                }
	                return age;
	        }
	        
	        function getAge(dateString) {
	           var today = new Date();
	           var birthDate = new Date(dateString);
	           var age = today.getFullYear() - birthDate.getFullYear();
	           var m = today.getMonth() - birthDate.getMonth();
	              if(m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
	                age--;
	              }
	              if(isNaN(age)) {
	                return "N/A";
	              }
	              return age;
	        }
	        
	        //Function to validate required fields
	        function processData(form) {
	          var form_selector = "#" + form;
	          var validated = $(form_selector).validationEngine('validate');
	            if(!validated) {
                   return false;
	            }else{
	            	return true;
	            }
	       }
		</script>
	</head>
	<body>
<div class="full-content">
=======
<div class="full-content" style="background:#FF9">
>>>>>>> 25b9b2ddbc2c54bccba48d3a32d66045e9267d69
	<h3>Patient Registration
	<div style="float:right;margin:5px 40px 0 0;">
		(Fields Marked with <b><span class='astericks'>*</span></b> Asterisks are required)
	</div></h3>

	<form id="edit_patient_form" method="post"  action="<?php $record=$result['id']; echo base_url() . 'patient_management/update/'.$record; ?>" onsubmit="return processData('edit_patient_form')" >
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
						foreach ($districts as $district) {
							echo "<option value='" . $district['id'] . "'>" . $district['Name'] . "</option>";
						}
						?>
					</select>
				</div>
			</div>

			<div class="max-row">
				<div class="mid-row">
					<label><span class='astericks'>*</span>Gender</label>
					<select name="gender" id="gender" class="validate[required]">
						<option value=" ">--Select--</option>
						<?php
						foreach ($genders as $gender) {
							echo "<option value='" . $gender['id'] . "'>" . $gender['name'] . "</option>";
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
					<label >Start Age(Years)</label>
					<input type="text" id="start_age" disabled="disabled"/>
				</div>
				<div class="mid-row">
					<label >Current Age(Years)</label>
					<input type="text" id="age" disabled="disabled"/>
				</div>
			</div>
			<div class="max-row">
				<div class="mid-row">
					<label >Start Weight (KG)</label>
					<input type="text"name="start_weight" id="start_weight">
				</div>
				<div class="mid-row">
					<label>Current Weight (KG) </label>
					<input type="text"name="current_weight" id="current_weight">
				</div>
			</div>
			<div class="max-row">
				<div class="mid-row">
					<label > Start Height (CM)</label>
					<input type="text"name="start_height" id="start_height" onblur="getStartMSQ()">
				</div>
				<div class="mid-row">
					<label > Current Height (CM)</label>
					<input  type="text"name="current_height" id="current_height" onblur="getMSQ()">
				</div>
			</div>
			<div class="max-row">
				<div class="mid-row">
					<label > Start Body Surface Area (MSQ)</label>
					<input type="text" name="start_bsa" id="start_bsa" value="" >
				</div>
				<div class="mid-row">
					<label > Current Body Surface Area (MSQ)</label>
					<input type="text" name="current_bsa" id="current_bsa" value="" >
				</div>
			</div>
			<div class="max-row">
				<div class="mid-row">

				</div>
				<div class="mid-row"></div>
			</div>
			<div class="max-row">
				<div class="mid-row">

				</div>
				<div class="mid-row"></div>
			</div>

			<div class="max-row">
				<label> Patient's Physical Contact(s)</label>
				<textarea name="physical" id="physical" value=""></textarea>
			</div>
			<div class="max-row">
				<label> Patient's Alternate Contact(s)</label>
				<input type="text" name="alternate" id="alternate" value="">
			</div>

	</div>

	<div class="column" id="colmnTwo">
		<fieldset>
			<legend>
				Program History
			</legend>
			<div class="max-row">
				<label  id="tstatus"> Partner Status</label>
				<select name="partner_status" id="partner_status" >
					<option value="0" selected="selected">No Partner</option>
					<option value="1" > Concordant</option>
					<option value="2" > Discordant</option>
				</select>

			</div>
			<div class="max-row">
				<div class="mid-row">
					<label id="dcs" >Disclosure</label>
					<input  type="radio"  name="disclosure" value="1" id="disclosure_yes">
					Yes
					<input  type="radio"  name="disclosure" value="0" id="disclosure_no">
					No
				</div>
			</div>
			<div class="max-row">
				<label>Family Planning Method</label>
				<select name="family_planning" id="family_planning" multiple="multiple" style="width:200px;"  >
					<?php
					foreach ($family_planning as $fplan) {
						echo "<option value='" . $fplan['indicator'] . "'>" . $fplan['name'] . "</option>";
					}
					?>
				</select>

			</div>
			<div class="max-row">
				<label>Does Patient have other Chronic illnesses</label>
				<select name="other_illnesses" id="other_illnesses"  multiple="multiple"  style="width:200px;" >
					<?php
					foreach ($other_illnesses as $other_illness) {
						echo "<option value='" . $other_illness['indicator'] . "'>" . $other_illness['name'] . "</option>";
					}
					?>
				</select>
			</div>
			<div class="max-row">
				<label>If <b>Other Illnesses</b>
					<br/>
					Click Here
					<input type="checkbox" name="other_other" id="other_other" value="">
					<br/>
					List Them Below (Use Commas to separate) </label>
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
				<label>Does Patient belong to any support group?</label>
				<label>Yes
					<input type="checkbox" name="support_group" id="support_group" value="">
				</label>

				<div class="list">
					List Them
				</div>
				<textarea class="list_area" name="support_group_listing" id="support_group_listing"></textarea>
			</div>
			<div class="max-row">
				<div class="mid-row">
					<label > Does Patient Smoke?</label>
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
			<div class="max-row">
				<div class="mid-row">
				<label> Date of Next Appointment</label>
				<input type="text" name="next_appointment_date" id="next_appointment_date" />
				<input type="hidden" name="prev_appointment_date" id="prev_appointment_date" />
				</div>
				<div class="mid-row">
				<label> Days to Next Appointment</label>
				<input  type="text"name="days_to_next" id="days_to_next">
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
					foreach ($statuses as $status) {
						echo "<option value='" . $status['id'] . "'>" . $status['Name'] . "</option>";
					}
					?>
				</select>
			</div>
			<div class="max-row">
				<label class="status_started" ><span class='astericks'>*</span>Date of Status Change</label>
				<input type="text" name="status_started" id="status_started" value="" class="validate[required]">
			</div>
			<div class="max-row">
				<label><span class='astericks'>*</span>Source of Patient</label>
				<select name="source" id="source" class="validate[required]">
					<option value="">--Select--</option>
					<?php
					foreach ($sources as $source) {
						echo "<option value='" . $source['id'] . "'>" . $source['Name'] . "</option>";
					}
					?>
				</select>
			</div>
			<div id="patient_source_listing" class="max-row" style="display:none;">
				<label> Transfer From</label>
				<select name="transfer_source" id="transfer_source" >
					<option value="">--Select--</option>
					<?php
					foreach ($facilities as $facility) {
						echo "<option value='" . $facility['facilitycode'] . "'>" . $facility['name'] . "</option>";
					}
					?>
				</select>
			</div>
			<div class="max-row">
				<label><span class='astericks'>*</span>Patient Supported by</label>
				<select name="support" id="support" class="validate[required]">
					<option value="">--Select--</option>
					<?php
					foreach ($supporters as $supporter) {
						echo "<option value='" . $supporter['id'] . "'>" . $supporter['Name'] . "</option>";
					}
					?>
				</select>
			</div>
			<div class="max-row">
				<label><span class='astericks'>*</span>Type of Service</label>
				<select name="service" id="service" class="validate[required]">
					<option value="">--Select--</option>
					<?php
					foreach ($service_types as $service_type) {
						echo "<option value='" . $service_type['id'] . "'>" . $service_type['Name'] . "</option>";
					}
					?>
				</select> </label>
				</select>
			</div>
			<div class="max-row">
				<label id="start_of_regimen"><span class='astericks'>*</span>Start Regimen </label>
				<select name="regimen" id="regimen" class="validate[required] start_regimen" >
					<option value=" ">--Select One--</option>
					<?php
					foreach ($regimens as $regimen) {
						echo "<option value='" . $regimen['id'] . "'>".$regimen['Regimen_Code'] ." | " . $regimen['Regimen_Desc'] . "</option>";
					}
					?>
				</select>
			</div>
			<div class="max-row">
				<label id="date_service_started"><span class='astericks'>*</span>Start Regimen Date</label>
				<input type="text" name="service_started" id="service_started" value="" class="validate[required]">
			</div>
			<div class="max-row">
				<label style="color:red;font-weight:bold;">Current Regimen</label>
				<select type="text"name="current_regimen" id="current_regimen" class="validate[required]">
					<option value="">--Select--</option>
					<?php
					foreach ($regimens as $regimen) {
						echo "<option value='" . $regimen['id'] . "'>".$regimen['Regimen_Code'] ." | " . $regimen['Regimen_Desc'] . "</option>";
					}
					?>
				</select>
			</div>
		</fieldset>
	</div>
	<div class="button-bar">
			<input form="edit_patient_form" type="submit" class="btn" value="Edit" name="save"/>

	</div>

</form>
</div>
</body>
</html>
