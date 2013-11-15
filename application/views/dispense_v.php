<?php 
foreach($results as $result){
	
}
?>

<!DOCTYPE html>
<html lang="en" >
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style>
			table#drugs_table input{
				font-size:0.8em;
				height:25px;
				width:100%;
			}
			table#drugs_table select{
				font-size:0.8em;
				height:25px;
			}

		</style>
		<script type="text/javascript">
			$(document).ready(function() {
				<?php $this -> session -> set_userdata('record_no', $result['id']);?>
				$("#patient").val("<?php echo $result['patient_number_ccc'];?>");
				var first_name="<?php echo strtoupper($result['first_name']); ?>";
				var other_name="<?php echo strtoupper($result['other_name']); ?>";
				var last_name="<?php echo strtoupper($result['last_name']); ?>";
				$("#patient_details").val(first_name+" "+other_name+" "+last_name);
				$("#height").val("<?php echo $result['height']; ?>");
				<?php
				if($last_regimens){
				?>
				$("#last_regimen_disp").val("<?php echo $last_regimens['regimen_code']." | ".$last_regimens['regimen_desc'];?>");
				$("#last_regimen").val("<?php echo $last_regimens['id'];?>");
				<?php
				}
				?>
				var last_visit_date ="<?php echo @$last_regimens['dispensing_date']; ?>";
				if(last_visit_date){
					var last_visit_date ="<?php echo date('d-M-Y',strtotime(@$last_regimens['dispensing_date'])); ?>";
				    $("#last_visit_date").attr("value", last_visit_date);
				}
				
				
				
				//Get Prev Appointment
				<?php
				if($appointments){
				?>
				var today = new Date();
				var appointment_date = $.datepicker.parseDate('yy-mm-dd',"<?php echo $appointments['appointment'];?>");
				var timeDiff = today.getTime() - appointment_date.getTime();
				var diffDays = Math.floor(timeDiff / (1000 * 3600 * 24));
				if(diffDays > 0) {
					var html = "<span style='color:#ED5D3B;'>Late by <b>" + diffDays + "</b> days.</span>";
				} else {
					var html = "<span style='color:#009905'>Not Due for <b>" + Math.abs(diffDays) + "</b> days.</span>";
				}

				$("#days_late").append(html);
				$("#days_count").attr("value", diffDays);
				$("#last_appointment_date").attr("value","<?php echo @$appointments['appointment']; ?>");
				$("#last_appointment_date").attr("value","<?php echo date('d-M-Y',strtotime(@$appointments['appointment'])); ?>");
				<?php
				}
				?>
				
				
		    //Attach date picker for date of dispensing
	        $("#dispensing_date").datepicker({
					yearRange : "-120:+0",
					maxDate : "0D",
					dateFormat : $.datepicker.ATOM,
					changeMonth : true,
					changeYear : true
			});
			$("#dispensing_date").datepicker();
            $("#dispensing_date").datepicker("setDate", new Date());
            
           
			
			//Add listener to check purpose
			$("#purpose").change(function() {
					$("#adherence").attr("value", " ");
					$("#adherence").removeAttr("readonly");
					var selected_value = $(this).val();
					var day_percentage = 0;
					if(selected_value == 2 || selected_value == 5) {
						var days_count = $("#days_count").val();
						if(days_count <= 0) {
							day_percentage = "100%";
						} else if(days_count > 0 && days_count <= 2) {
							day_percentage = ">=95%";
						} else if(days_count > 2 && days_count < 14) {
							day_percentage = "84-94%";
						} else if(days_count >= 14) {
							day_percentage = "<85%";
						}
						$("#adherence").attr("value", day_percentage);
						$("#adherence").attr("readonly", "readonly");
					}

			});
			
			//Add datepicker for the next appointment date
				$("#next_appointment_date").datepicker({
					changeMonth : true,
					changeYear : true,
					dateFormat : $.datepicker.ATOM,
					onSelect : function(dateText, inst) {
						var base_date = new Date();
						var today = new Date(base_date.getFullYear(), base_date.getMonth(), base_date.getDate());
						var today_timestamp = today.getTime();
						var one_day = 1000 * 60 * 60 * 24;
						var appointment_timestamp = $("#next_appointment_date").datepicker("getDate").getTime();
						var difference = appointment_timestamp - today_timestamp;
						var days_difference = difference / one_day;
						$("#days_to_next").attr("value", days_difference);
						retrieveAppointedPatients();
					}
				});
			
			//Add listener to the 'days_to_next' field so that the date picker can reflect the correct number of days!
			$("#days_to_next").change(function() {
					var days = $("#days_to_next").attr("value");
					var base_date = new Date();
					var appointment_date = $("#next_appointment_date");
					var today = new Date(base_date.getFullYear(), base_date.getMonth(), base_date.getDate());
					var today_timestamp = today.getTime();
					var appointment_timestamp = (1000 * 60 * 60 * 24 * days) + today_timestamp;
					appointment_date.datepicker("setDate", new Date(appointment_timestamp));
					retrieveAppointedPatients();
					
					//Loop through Table to calculate pill counts for all rows
					$.each($(".drug"), function(i, v) {
					    var row=$(this);
                        var qty_disp=row.closest("tr").find(".qty_disp").val();
					    var dose_val=row.closest("tr").find(".dose option:selected").attr("dose_val");
					    var dose_freq=row.closest("tr").find(".dose option:selected").attr("dose_freq");
					    /*
					    var pill_count=getPillCount(dose_val,dose_freq,qty_disp);
					    var current_pill_count=row.closest("tr").find(".pill_count").val();
					    row.closest("tr").find(".next_pill_count").val(parseFloat(pill_count)+parseFloat(current_pill_count));
					    */
				    });
			 });
			 
			 //Dynamically change the list of drugs once a current regimen is selected
			$("#current_regimen").change(function() {
			   var selected_regimen=$(this).val();
			   var _url="<?php echo base_url().'dispensement_management/getDrugsRegimens'; ?>";
			   //Get drugs
			   var request=$.ajax({
			     url: _url,
			     type: 'post',
			     data: {"selected_regimen":selected_regimen},
			     dataType: "json"
			    });
			    request.done(function(data){
			    	$(".drug option").remove();
			    	$(".drug").append($("<option value='0'>-Select Drug-</option>"));
			    	$.each(data,function(key,value){
			    		$(".drug").append($("<option value='"+value.id+"'>"+value.drug+"</option>"));
			    	});
			    });
			    request.fail(function(jqXHR, textStatus) {
				  alert( "Could not retrieve drug details : " + textStatus );
				});
			   
			
			   var regimen = $("#current_regimen option:selected").attr("value");
			   var last_regimen = $("#last_regimen").attr("value");
			   if(last_regimen != 0) {
						if(regimen != last_regimen) {
							$("#regimen_change_reason_container").show();
						} else {
							$("#regimen_change_reason_container").hide();
							$("#regimen_change_reason").val("");
						}
				}else{
					  $("#regimen_change_reason_container").hide();
					  $("#regimen_change_reason").val("");
				}	
			});
			
			//Drug selection change
			$(".drug").change(function(){
				var row=$(this);
				resetFields(row);
				row.closest("tr").find(".batch option").remove();
				row.closest("tr").find(".batch").append($("<option value='0'>Loading ...</option>"));
				var row=$(this);
				var selected_drug=$(this).val();
				<?php 
				if($prev_visit){
				?>
				var prev_visit_arr=<?php echo $prev_visit; ?>;
				   //Loop through prev_dispensing table and chack with current drug selected if a match is found populate pill count
		           $.each(prev_visit_arr, function(i, v) {
						var prev_drug_id=v['drug_id'];
						var prev_drug_qty=v['mos'];
						var prev_qty=v['quantity'];
						var prev_date=v['dispensing_date'];
						var prev_value=v['value'];
						var prev_frequency=v['frequency'];
						
						
					if(selected_drug==prev_drug_id){
						var base_date = new Date();
						var today = new Date(base_date.getFullYear(), base_date.getMonth(), base_date.getDate());
						var today_timestamp = today.getTime();
						var one_day = 1000 * 60 * 60 * 24;
						var appointment_timestamp = Date.parse(prev_date);
						var difference = today_timestamp-appointment_timestamp;
						var days_difference = difference / one_day;
						days_difference=days_difference.toFixed(0);
						prev_drug_qty=getActualPillCount(days_difference,prev_value,prev_frequency,prev_qty);
						row.closest("tr").find(".pill_count").val(prev_drug_qty);
						}
				   });
				<?php 
				}
				?>

           



				
				
				var stock_type="2";
				var dose="";
				//Get batches that have not yet expired and have stock balance
				var _url="<?php echo base_url().'inventory_management/getBacthes'; ?>";
				
				var request=$.ajax({
			     url: _url,
			     type: 'post',
			     data: {"selected_drug":selected_drug,"stock_type":stock_type},
			     dataType: "json"
			    });
			    request.done(function(data){
			    	
			    	var url_dose="<?php echo base_url().'dispensement_management/getDoses'; ?>";
					//Get doses
					var request_dose=$.ajax({
				     url: url_dose,
				     type: 'post',
				     dataType: "json"
				    });
				    request_dose.done(function(data){
				    	row.closest("tr").find(".dose option").remove();
				    	row.closest("tr").find(".dose").append("<option value='0'>None</option> ");
				    	$.each(data,function(key,value){
				    		row.closest("tr").find(".dose").append("<option value='"+value.Name+"'  dose_val='"+value.value+"' dose_freq='"+value.frequency+"' >"+value.Name+"</option> ");
				   		});
				   		row.closest("tr").find(".dose").val(dose);
				   		//$(".dose").val(dose);
				    	
				    });

			    	row.closest("tr").find(".batch option").remove();
			    	$(".batch").append($("<option value='0'>Select</option>"));
			    	$.each(data,function(key,value){
			    		row.closest("tr").find(".unit").val(value.Name);
			    		row.closest("tr").find(".batch").append("<option value='"+value.batch_number+"'>"+value.batch_number+"</option> ");
			    		row.closest("tr").find(".dose").val(value.dose);
			    		row.closest("tr").find(".duration").val(value.duration);
			    		row.closest("tr").find(".qty_disp").val(value.quantity);
			    		dose=value.dose;
			    	});
			    	var new_url="<?php echo base_url().'dispensement_management/getBrands'; ?>";
			    	

			    	
			    	var request_brand=$.ajax({
				     url: new_url,
				     type: 'post',
				     data: {"selected_drug":selected_drug},
				     dataType: "json"
				    });
				    
				    request_brand.done(function(data){
				    	row.closest("tr").find(".brand option").remove();
				    	row.closest("tr").find(".brand").append("<option value='0'>None</option> ");
				    	$.each(data,function(key,value){
				    		//alert(value.drug);
				    		row.closest("tr").find(".brand").append("<option value='"+value.id+"'>"+value.brand+"</option> ");
				   		});
				    	
				    });
				    request_brand.fail(function(jqXHR, textStatus) {
					  alert( "Could not retrieve the list of brands : " + textStatus );
					});
				    
				    //Get indications(opportunistic infections)
				    var url_indication="<?php echo base_url().'dispensement_management/getIndications'; ?>";
				    var request_dose=$.ajax({
				     url: url_indication,
				     type: 'post',
				     dataType: "json"
				    });
				    request_dose.done(function(data){
				    	row.closest("tr").find(".indication option").remove();
				    	row.closest("tr").find(".indication").append("<option value='0'>None</option> ");
				    	$.each(data,function(key,value){
				    		row.closest("tr").find(".indication").append("<option value='"+value.Indication+"'>"+value.Indication+" | "+value.Name+"</option> ");
				   		});
				   		//$(".dose").val(dose);
				    	
				    });
					
			    });
			    request.fail(function(jqXHR, textStatus) {
				  alert( "Could not retrieve the list of batches : " + textStatus );
				});
			});
			
			//Batch change
			$(".batch").change(function(){
				if($(this).prop("selectedIndex")>1){
			   		alert("THIS IS NOT THE FIRST EXPIRING BATCH");
			   	}
				//resetFields($(this));
				var row=$(this);
				
				//Get batch details(balance,expiry date)
				if($(this).val()!=0){
					var batch_selected=$(this).val();
					var stock_type="2";
					var selected_drug=row.closest("tr").find(".drug").val();
					var _url="<?php echo base_url().'inventory_management/getBacthDetails'; ?>";
					var request=$.ajax({
				     url: _url,
				     type: 'post',
				     data: {"selected_drug":selected_drug,"stock_type":stock_type,"batch_selected":batch_selected},
				     dataType: "json"
				    });
				    
				    request.done(function(data){
				    	$.each(data,function(key,value){
				    		row.closest("tr").find(".expiry").val(value.expiry_date);
				    		row.closest("tr").find(".soh ").val(value.balance);
				    		
				    	});
				    });
				    request.fail(function(jqXHR, textStatus) {
					  alert( "Could not retrieve batch details : " + textStatus );
					});			
				}
			});
			
			$(".qty_disp").keyup(function() {
				var row=$(this);
				var selected_value = $(this).attr("value");
				var stock_at_hand = row.closest("tr").find(".soh ").attr("value");
				var stock_validity = stock_at_hand - selected_value;
				if(stock_validity < 0) {
					alert("Quantity Cannot Be larger Than Stock at Hand");
					row.closest("tr").find(".qty_disp").css("background-color","red");
					row.closest("tr").find(".qty_disp").addClass("input_error");
					
				}
				else{
					row.closest("tr").find(".qty_disp").css("background-color","white");
					row.closest("tr").find(".qty_disp").removeClass("input_error");
				}	

			});
			
			$(".next_pill").change(function(){
				    var row=$(this);
					var qty_disp=row.closest("tr").find(".qty_disp").val();
					var dose_val=row.closest("tr").find(".dose option:selected").attr("dose_val");
					var dose_freq=row.closest("tr").find(".dose option:selected").attr("dose_freq");
					/*
					var pill_count=getPillCount(dose_val,dose_freq,qty_disp);
					var current_pill_count=row.closest("tr").find(".pill_count").val();
					row.closest("tr").find(".next_pill_count").val(parseFloat(pill_count)+parseFloat(current_pill_count));
					*/
			})
						
			$(".add").click(function() {
				var last_row=$('#drugs_table tr:last');
				var drug_selected=last_row.find(".drug").val();
				var quantity_entered=last_row.find(".qty_disp").val();
				if(last_row.find(".qty_disp").hasClass("input_error")){
					alert("Error !Quantity dispensed is greater than qty available!");
				}
				
				else if(drug_selected==0 ){
					alert("You have not selected a drug!");
				}
				else if(quantity_entered=="" || quantity_entered==0){
					alert("You have not entered any quantity!");
				}
				else{
					var cloned_object = $('#drugs_table tr:last').clone(true);
					var drug_row = cloned_object.attr("drug_row");
					var next_drug_row = parseInt(drug_row) + 1;
					var row_element = cloned_object;

					//Second thing, retrieve the respective containers in the row where the drug is
					row_element.find(".unit").attr("value", "");
					row_element.find(".batch").empty();
					//Fixing the expiry date in dispensing
					var expiry_id = "expiry_date_" + next_drug_row;
					var expiry_date = row_element.find(".expiry").attr("value", "");
					expiry_date.attr("id", expiry_id);
					var expiry_selector = "#" + expiry_id;

					$(expiry_selector).datepicker({
						defaultDate : new Date(),
						changeYear : true,
						changeMonth : true
					});

					row_element.find(".dose").attr("value", "");
					row_element.find(".duration").attr("value", "");
					row_element.find(".qty_disp").attr("value", "");
					row_element.find(".brand").attr("value", "");
					row_element.find(".soh").attr("value", "");
					row_element.find(".indication").attr("value", "");
					row_element.find(".pill_count").attr("value", "");
					row_element.find(".next_pill_count").attr("value", "");
					row_element.find(".pill_count").removeAttr("disabled");
					row_element.find(".comment").attr("value", "");
					row_element.find(".missed_pills").attr("value", "");
					row_element.find(".missed_pills").removeAttr("disabled");
					row_element.find(".remove").show();
					cloned_object.attr("drug_row", next_drug_row);
					cloned_object.insertAfter('#drugs_table tr:last');
					return false;
				}
				
			});
			$(".remove").click(function() {
				$(this).closest('tr').remove();
			});
			/*
			$(".confirm").click(function(){
				var test_confirm=confirm("Are You Sure?");
				if(test_confirm){
					alert("Marete")
					return true;
				}else{
					return false;
				}
			});*/
			
		 function resetFields(row){
			row.closest("tr").find(".qty_disp").val("");
			row.closest("tr").find(".soh").val("");
			//row.closest("tr").find(".indication").val("");
			row.closest("tr").find(".duration").val("");
			row.closest("tr").find(".expiry").val("");
			row.closest("tr").find(".pill_count").val("");
			row.closest("tr").find(".missed_pills").val("");
		}
		
		 function retrieveAppointedPatients(){
          	$("#scheduled_patients").html("");
			$('#scheduled_patients').hide();
                //Function to Check Patient Number exists
			    var base_url="<?php echo base_url();?>";
				var appointment=$("#next_appointment_date").val();
				var link=base_url+"patient_management/getAppointments/"+appointment;
				$.ajax({
				    url: link,
				    type: 'POST',
				    dataType: 'json',
				    success: function(data) {		        
				       var all_appointments_link = "<a class='link' target='_blank' href='<?php echo base_url().'report_management/getScheduledPatients/';?>" + appointment + "/" + appointment + "' style='font-weight:bold;color:red;'>View appointments</a>";
					   var html = "Patients Scheduled on Date: <b>" + data[0].total_appointments + "</b> Patients" + all_appointments_link;
					   var new_date = new Date(appointment);
					   var formatted_date_day = new_date.getDay();
					   var days_of_week = ["Sunday", "Monday", "Tuseday", "Wednesday", "Thursday", "Friday", "Saturday"];
					      if(formatted_date_day == 6 || formatted_date_day == 0) {
						     alert("It will be on " + days_of_week[formatted_date_day] + " During the Weekend");
						   if(parseInt(data[0].total_appointments)  > parseInt(data[0].weekend_max)) {
							 alert("Maximum Appointments for Weekend Reached");
						   }
					      } else {
						if(parseInt(data[0].total_appointments) > parseInt(data[0].weekday_max)) {
							alert("Maximum Appointments for Weekday Reached");
						}

					}

					$("#scheduled_patients").append(html);
					$('#scheduled_patients').show();
				   }
				});
           }
           
           $("#btn_submit").click(function(event){
       			processData('dispense_form');
       			
       		});
           
       });
       		
       
               //Function to validate required fields
		    function processData(form) {
		          var form_selector = "#" + form;
		          var validated = $(form_selector).validationEngine('validate');
		            if(!validated) {
		            	return false;
		            }else{
		            	saveData();
		            }
		     }
		     
		     //Function to post data to the server
		     function saveData(){
		     	$("#btn_submit").attr("disabled","disabled");
		     	var facility="<?php echo $facility ?>";
		     	var timestamp = new Date().getTime();
		     	var user="<?php echo $user;?>";
		     	var last_row=$('#drugs_table tr:last');
		     	
				if(last_row.find(".qty_disp").hasClass("input_error")){
					alert("The quantity of the last commodity being dispensed is greater that the quantity available!");
					return;
				}
				
				var rowCount = $('#drugs_table tr').length;
				//Check if details were entered before submiting
				if(rowCount==2){
					var drug_selected=last_row.find(".drug").val();
					var quantity_entered=last_row.find(".qty_disp").val();
					if(drug_selected==0 ){
						alert("You have not selected a drug!");
						return;
					}
					else if(quantity_entered=="" || quantity_entered==0){
						alert("You have not entered the quantity being dispensed for the last commodity entered!");
						return;
					}
				}
				//Before going any further, first calculate the number of drugs being recorded
				var drugs_count = 0;
				$.each($(".drug"), function(i, v) {
					if($(this).attr("value")) {
						drugs_count++;
					}
				});
				
				//If no drugs were selected, exit
				if(drugs_count == 0) {
					return;
				}
				
				//Retrieve all form input elements and their values
				var dump = retrieveFormValues();
				//Call this function to do a special retrieve function for elements with several values
				var drugs = retrieveFormValues_Array('drug');
				var batches = retrieveFormValues_Array('batch');
				var doses = retrieveFormValues_Array('dose');
				var brands = retrieveFormValues_Array('brand');
				var expiry = retrieveFormValues_Array('expiry');
				var indications = retrieveFormValues_Array('indication');
				var pill_counts = retrieveFormValues_Array('pill_count');
				var comments = retrieveFormValues_Array('comment');
				var missed_pills = retrieveFormValues_Array('missed_pills');
				var quantities = retrieveFormValues_Array('qty_disp');
				var durations = retrieveFormValues_Array('duration');
				var next_appointment_sql = "";
				var drug_consumption = " ";
				var balance_sql="";
				var transaction_type = 5;
				var dispensing_date_timestamp = Date.parse(dump["dispensing_date"]);
				//Check if there is a date indicated for the next appointment. If there is, schedule it!
					if($("#next_appointment_date").attr("value").length > 1) {
						var last_date = dump["last_appointment_date"];
						var todays_date = dump["dispensing_date"];
						var last_String = last_date.toString();
						var today_string = todays_date.toString();
						//If there is a last appointment date
						if(last_String) {
							//Check if last appointment date is greater than today(Came later than appointment date)
							if(last_String > today_string) {
								next_appointment_sql = "update patient_appointment set appointment=DATE(STR_TO_DATE('"+dump["next_appointment_date"]+"','%Y-%m-%d')) where patient='" + dump["patient"] + "' and appointment=DATE(STR_TO_DATE('"+last_date+"','%Y-%m-%d'));";
							}
							//check if last apppointment date is equal to today(Came on correct appointment date) or //Check if last appointment date is less than today(Came earlier than appointment date)
							else {
								next_appointment_sql = "insert into patient_appointment (patient,appointment,facility) values ('" + dump["patient"] + "',DATE(STR_TO_DATE('"+dump["next_appointment_date"]+"','%Y-%m-%d')),'" + facility + "');";
	
							}
						}
						//If no appointment date
						else {
							next_appointment_sql = "insert into patient_appointment (patient,appointment,facility) values ('" + dump["patient"] + "',DATE(STR_TO_DATE('"+dump["next_appointment_date"]+"','%Y-%m-%d')),'" + facility + "');";
	
						}
					}
					var sql = next_appointment_sql;
					sql += "UPDATE patient SET height='" + dump["height"] + "',current_regimen='" + dump["current_regimen"] + "',nextappointment=DATE(STR_TO_DATE('"+dump["next_appointment_date"]+"','%Y-%m-%d')) where patient_number_ccc ='" + dump["patient"] + "';";
					
					//After getting the number of drugs issued, create a unique entry (sql statement) for each in the database in this loop
					for(var i = 0; i < drugs_count; i++) {
						sql += "INSERT INTO patient_visit (patient_id, visit_purpose, current_height, current_weight, regimen, regimen_change_reason, drug_id, batch_number, brand, indication, pill_count, comment, timestamp, user, facility, dose, dispensing_date, dispensing_date_timestamp,quantity,duration,adherence,missed_pills,non_adherence_reason) VALUES ('" + dump["patient"] + "', '" + dump["purpose"] + "', '" + dump["height"] + "', '" + dump["weight"] + "', '" + dump["current_regimen"] + "', '" + dump["regimen_change_reason"] + "', '" + drugs[i] + "', '" + batches[i] + "', '" + brands[i] + "', '" + indications[i] + "', '" + pill_counts[i] + "', '" + comments[i] + "', '" + timestamp + "', '" + user + "', '" + facility + "', '" + doses[i] + "', DATE(STR_TO_DATE('"+dump["dispensing_date"]+"','%Y-%m-%d')), '" + dispensing_date_timestamp + "','" + quantities[i] + "','" + durations[i] + "','" + dump["adherence"] + "','" + missed_pills[i] + "','" + dump["non_adherence_reasons"] + "');";
						drug_consumption = "INSERT INTO drug_stock_movement (drug, transaction_date, batch_number, transaction_type,source,destination,expiry_date,quantity, quantity_out, facility,timestamp) VALUES ('" + drugs[i] + "', DATE(STR_TO_DATE('"+dump["dispensing_date"]+"','%Y-%m-%d')), '" + batches[i] + "', '" + transaction_type + "','"+facility+"','"+facility+"',DATE(STR_TO_DATE('"+expiry[i]+"','%Y-%m-%d')),0,'" + quantities[i] + "','" + facility + "','" + timestamp + "');";
						sql += drug_consumption;
						balance_sql="UPDATE drug_stock_balance SET balance=balance - "+quantities[i]+" WHERE drug_id='"+drugs[i]+"' AND batch_number='"+batches[i]+"' AND expiry_date=DATE(STR_TO_DATE('"+expiry[i]+"','%Y-%m-%d')) AND stock_type='2' AND facility_code='"+facility+"';";
						sql += balance_sql;
						if((i+1)==drugs_count){
							//console.log(sql);
							$("#sql").val(sql);
							$("#dispense_form").submit();
						}
						
		     		}
		     }
		     function retrieveFormValues() {
				//This function loops the whole form and saves all the input, select, e.t.c. elements with their corresponding values in a javascript array for processing
				var dump = Array;
				$.each($("input, select, textarea"), function(i, v) {
					var theTag = v.tagName;
					var theElement = $(v);
					var theValue = theElement.val();
					if(theElement.attr('type') == "radio") {
						var text = 'input:radio[name=' + theElement.attr('name') + ']:checked';
						dump[theElement.attr("name")] = $(text).attr("value");
					} else {
						dump[theElement.attr("name")] = theElement.attr("value");
					}
				});
				return dump;
			}
			
			function retrieveFormValues_Array(name) {
				var dump = new Array();
				var counter = 0;
				$.each($("input[name=" + name + "], select[name=" + name + "], select[name=" + name + "]"), function(i, v) {
					var theTag = v.tagName;
					var theElement = $(v);
					var theValue = theElement.val();
					dump[counter] = theElement.attr("value");
					counter++;
				});
				return dump;
			}
			
			function getPillCount(dose_qty,dose_frequency,total_actual_drugs){
				var days_issued=$("#days_to_next").val();
				var error_message="";
				    if(!days_issued){
				       error_message+="Days to Next Appointment not Selected \r\n";
					}
					if(!dose_qty){
						error_message+="Dose has no Value \r\n";
					}
					if(!dose_frequency){
						error_message+="Dose has no Frequency \r\n";
					}
					if(!total_actual_drugs){
						error_message+="No Quantity to Dispense Selected \r\n";
					}					
				    if(error_message){
					   // alert(error_message);
				    }else{
						var drugs_per_day=(dose_qty*dose_frequency);
				        var total_expected_drugs=(drugs_per_day*days_issued);
				        var pill_count=(total_actual_drugs-total_expected_drugs);
				        return pill_count;
					}
			}
			
			function getActualPillCount(days_issued,dose_qty,dose_frequency,total_actual_drugs){
				var error_message="";
					if(!dose_qty){
						error_message+="Dose has no Value \r\n";
					}
					if(!dose_frequency){
						error_message+="Dose has no Frequency \r\n";
					}
					if(!total_actual_drugs){
						error_message+="No Quantity to Dispense Selected \r\n";
					}					
				    if(error_message){
					   //alert(error_message);
				    }else{
						var drugs_per_day=(dose_qty*dose_frequency);
				        var total_expected_drugs=(drugs_per_day*days_issued);
				        var pill_count=(total_actual_drugs-total_expected_drugs);			       
				        return pill_count;
					}
			}

		</script>

	</head>
	<body>
		<div class="full-content" style="background: #f9f">
			<div id="sub_title" >
				<a href="<?php  echo base_url().'patient_management ' ?>">Patient Listing </a> <i class=" icon-chevron-right"></i><a href="<?php  echo base_url().'patient_management/viewDetails/'.$result['id'] ?>"><?php echo strtoupper($result['first_name'].' '.$result['other_name'].' '.$result['last_name']) ?></a> <i class=" icon-chevron-right"></i><strong>Dispensing details</strong>
				<hr size="1">
			</div>
			<h3>Dispense Drugs</h3>
			<form id="dispense_form" class="dispense_form" method="post"  action="<?php echo base_url().'dispensement_management/save';?>"  >
				<textarea name="sql" id="sql" style="display:none;"></textarea>
				<input type="hidden" id="hidden_stock" name="hidden_stock"/>
				<input type="hidden" id="days_count" name="days_count"/>
				<div class="column-2">
					<fieldset>
						<legend>
							Dispensing Information
						</legend>

						<div class="max-row">
							<div class="mid-row">
								<label>Patient Number CCC</label>
								<input readonly="" id="patient" name="patient" class="validate[required]"/>
							</div>
							<div class="mid-row">
								<label>Patient Name</label>
								<input readonly="" id="patient_details"  class="validate[required]" />
							</div>
						</div>

						<div class="max-row">
							<div class="mid-row">
								<label><span class='astericks'>*</span>Dispensing Date</label>

								<input  type="text"name="dispensing_date" id="dispensing_date" class="validate[required]">
							</div>
							<div class="mid-row">
								<label><span class='astericks'>*</span>Purpose of Visit</label>

								<select  type="text"name="purpose" id="purpose" class="validate[required]" style='width:100%;'>
									<option value="">--Select One--</option>
									<?php 
									foreach($purposes as $purpose){
										echo "<option value='".$purpose['id']."'>".$purpose['Name']."</option>";
									}
									?>
								</select>
								</label>
							</div>
						</div>
						<div class="max-row">
							<div class="mid-row">
								<label>Current Height(cm)</label>

								<input  type="text"name="height" id="height" class="validate[required]">
							</div>
							<div class="mid-row">
								<label><span class='astericks'>*</span>Current Weight(kg)</label>

								<input  type="text"name="weight" id="weight" class="validate[required]" >
							</div>
						</div>
						<div class="max-row">
							<div class="mid-row">
								<label><span class='astericks'>*</span>Days to Next Appointment</label>
								<input  type="text" name="days_to_next" id="days_to_next" class="validate[required]">
							</div>
							<div class="mid-row">
								<label><span class='astericks'>*</span>Date of Next Appointment</label>

								<input  type="text" name="next_appointment_date" id="next_appointment_date" class="validate[required]" >
							</div>
						</div>
                            
                            
                       <div class="max-row">
                       	<br/>
						<span id="scheduled_patients" style="display:none;background:#9CF;padding:5px;">
							
						</span>

						</div>
						<div class="max-row">
							<div class="mid-row">
								<label id="scheduled_patients" class="message information close" style="display:none"></label><label>Last Regimen Dispensed</label>
								<input type="text"name="last_regimen_disp" value="none" id="last_regimen_disp" readonly="">
								<input type="hidden" name="last_regimen" value="0" id="last_regimen" value="0">
							</div>

							<div class="mid-row">
								<label><span class='astericks'>*</span>Current Regimen</label>
								<select type="text"name="current_regimen" id="current_regimen"  class="validate[required]" style='width:100%;' >
									<option value="">-Select One--</option>
										<?php 
									       foreach($regimens as $regimen){
										     echo "<option value='".$regimen['id']."'>".$regimen['Regimen_Code']." | ".$regimen['Regimen_Desc']."</option>";
									       }
									     ?>
								</select>
							</div>
						</div>
						<div class="max-row">
							<div class="mid-row">
								<div style="display:none" id="regimen_change_reason_container">
									<label>Regimen Change Reason</label>
									<select type="text"name="regimen_change_reason" id="regimen_change_reason" >
										<option value="">--Select One--</option>
										 <?php
										   foreach($regimen_changes as $changes){
										   	echo "<option value='".$changes['id']."'>".$changes['Name']."</option>";
										   }
										  ?>
									</select>
								</div>
							</div>
						</div>
						<div class="max-row">
							<div class="mid-row">
								<label>Appointment Adherence (%)</label>
								<input type="text" name="adherence" id="adherence"/>
							</div>
							<div class="mid-row">
								<label> Poor/Fair Adherence Reasons </label>
								<select type="text"name="non_adherence_reasons" id="non_adherence_reasons"  style='width:100%;'>
									<option value="">-Select One--</option>
										<?php 
									       foreach($non_adherence_reasons as $reasons){
										     echo "<option value='".$reasons['id']."'>".$reasons['Name']."</option>";
									       }
									     ?>
								</select>
							</div>
						</div>

					</fieldset>
				</div>
				<div class="column-2">
					<fieldset>
						<legend>
							Previous Patient Information
						</legend>
						<div class="max-row">
							<div class="mid-row">
								<label> Appointment Date</label>
								<input readonly="" id="last_appointment_date" name="last_appointment_date"/>
							</div>
						</div>
						<div class="max-row">
							<div class="mid-row">
								<label>Previous Visit Date</label>
								<input readonly="" id="last_visit_date" name="last_visit_date"/>
							</div>
						</div>
                        <div class="max-row">
						<table class="data-table prev_dispense" id="last_visit_data" style="float:left;">
							<thead>
							<th>Drug Dispensed</th>
							<th>Quantity Dispensed</th>
							<!--<th>Pill Count<span class="green">(Expected)</span></th>-->
							</thead>
							<tbody>
								<?php 
								if($visits){
								foreach($visits as $visit){
									echo "<tr><td>".$visit['drug']."</td><td style='text-align:center'>".$visit['quantity']."</td></tr>";
								 //<td class='exp_pill' drug_id='".$visit['drug_id']."' drug_val='".$visit['mos']."'>".$visit['mos']."</td>
								}
								}
								?>
							</tbody>
						</table>
						</div>
					</fieldset>
				</div>

				<div class="content-rowy" style="height:auto;">
					<table border="0" class="data-table" id="drugs_table" style="">
						<thead>
							<th class="subsection-title" colspan="15">Select Drugs</th>
							<tr style="font-size:0.8em">
							<th>Drug</th>
							<th>Unit</th>
							<th >Batch No.&nbsp;</th>
							<th>Expiry&nbsp;Date</th>
							<th>Dose</th>
							<th>Duration</th>
							<th>Qty. disp</th>
							<th>Brand Name</th>
							<th>Stock on Hand</th>
							<th>Indication</th>
							<th><b>Expected</b><br/>Pill Count</th>
							<th><b>Actual</b><br/> Pill Count</th>
							<th>Comment</th>
							<th>Missed Pills</th>
							<th style="">Action</th>
							</tr>
							<tr drug_row="0">
							<td><select name="drug[]" class="drug input-large"  style=" "></select></td>
							<td>
							<input type="text" name="unit[]" class="unit input-small" style="" readonly="" />
							</td>
							<td><select name="batch[]" class="batch input-small next_pill" style=""></select></td>
							<td>
							<input type="text" name="expiry[]" name="expiry" class="expiry input-small" id="expiry_date" readonly="" size="15"/>
							</td>
							<td>
							<select name="dose[]" class="dose input-small next_pill"></select>
							</td>
							<td>
							<input type="text" name="duration[]" class="duration input-small" />
							</td>
							<td>
							<input type="text" name="qty_disp[]" class="qty_disp input-small next_pill" />
							</td>
							<td><select name="brand[]" class="brand input-small"></select></td>
							<td>
							<input type="text" name="soh[]" class="soh input-small" readonly="readonly"/>
							</td>
							<td>
							<select name="indication[]" class="indication input-small" style="">
							<option value="0">None</option>
							</select></td>
							<td>
							<input type="text" name="pill_count[]" class="pill_count input-small" readonly="readonly" />
							</td>
							<td>
							<input type="text" name="next_pill_count[]" class="next_pill_count input-small"  />
							</td>
							<td>
							<input type="text" name="comment[]" class="comment input-small" />
							</td>
							<td>
							<input type="text" name="missed_pills[]" class="missed_pills input-small" />
							</td>
							<td>
							<a class="add btn-small">Add</a>|<a style="display: none" class="remove btn-small">Remove</a>
							</td>
							</tr>
						</thead>
					
					</table>
				
				</div>
				<div id="submit_section">
					<input type="reset" class="btn confirm" id="reset" value="Reset Fields" />
					<input form="dispense_form" id="btn_submit" class="btn confirm actual" id="submit" type="button" value="Dispense Drugs"/>
				</div>
			</form>

		</div>

	</body>
</html>