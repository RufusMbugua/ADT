<script type="text/javascript">
			$(document).ready(function() {
				$("#actual_page").text("<?php echo $actual_page; ?>");
				var base_url="<?php echo base_url(); ?>";
				$("#table_list").multiselect().multiselectfilter();
				var table_array =['tblARVDrugStockMain', 'tblCurrentStatus', 'tblDose', 'tblDrugsInRegimen', 'tblGenericName', 'tblIndication', 'tblReasonforChange', 'tblRegimen', 'tblRegimenCategory', 'tblSecurity', 'tblARTPatientMasterInformation', 'tblStockTransactionType', 'tblTypeOfService', 'tblVisitTransaction', 'tblSourceOfClient', 'tblARTPatientTransactions', 'tblARVDrugStockTransactions'];
				var target_array =['drugcode', 'patient_status', 'dose', 'regimen_drug', 'generic_name', 'opportunistic_infection', 'regimen_change_purpose', 'regimen', 'regimen_category', 'users', 'patient','transaction_type', 'regimen_service_type', 'visit_purpose', 'patient_source','patient_visit', 'drug_stock_movement'];
				
				$("#dbname").change(function(){
					var dbname=$(this).val();
					var link=base_url+"migration_management/checkDB/"+dbname
					$.ajax({
						url : link,
						type : 'POST', 
						success : function(data) {
							      if(data==1){
							      	alert("Database Cannot be Migrated")
							      }
								 }		 
				    });			
				});
				
				$("#migrate").click(function(){
					var dbname = $("#dbname").val();
				if(dbname) {
					var selected_table=$("select#table_list").val();
					var checked_value=$("input[name='tablename']:checked").val();
					if(checked_value==0){
						//Selected All
						$.each(table_array,function(i){
								var table_name=table_array[i];
								var offset=0;
								var index =table_array.indexOf(table_name);
								var target_name=target_array[index];
								//Check Migration Log							
								checklog(dbname,table_name,target_name);
                         });
				}else{
						//Custom Select
						if(selected_table){
							selected_table=selected_table.toString();
							var array = selected_table.split(",");
							$.each(array,function(i){
								var table_name=array[i];
								var index =table_array.indexOf(table_name);
								var target_name=target_array[index];
								//Check Migration Log
								checklog(dbname,table_name,target_name);
                            });
						}						  
				  }
				}else{
					alert("No Database Selected");
					return false;
				}
					
				});
				
				$("#tablecustom").click(function(){
					$("#table_select").show();
				});
				$("#tablename").click(function(){
					$("#table_select").hide();
				});
				
				function simpleMigration(dbname,table_name,target_name,offset){
				   var link = base_url + "migration_management/simplemigrate/"+dbname+"/"+table_name+"/"+target_name+"/"+offset;
					$.ajax({
						url : link,
						type : 'POST',
						success : function(data) {
							$("#output").append(data);
						}
					});
				}
				function advancedMigration(dbname,table_name,target_name,count,offset){
				   var link = base_url + "migration_management/countRecords/"+dbname+"/"+table_name;
				   var percentage=0;
				   var viewpercentage="";
				   if(table_name=="tblARTPatientTransactions"){
				   	viewpercentage="ppercentage";
				   }else if(table_name=="tblARVDrugStockTransactions"){
				   	viewpercentage="dpercentage";
				   }
				   $.ajax({
						url : link,
						type : 'POST', 
						success : function(data) {
							      var total_records=parseFloat(data);
							      percentage=((count/total_records)*100).toFixed(1);
							       $("#output").append("Data From <b>"+table_name+"</b> Migrated to <b>"+target_name+"</b> table (<span id='"+viewpercentage+"'>"+percentage+"% </span>)<br/>");
							           //Check while Counter is not equal to total
							           if(count<=total_records){
							           	recursive(table_name,target_name,offset,total_records); 
							           }
								 }		 
				    });
				}

				function recursive(dbname,table_name,target_name,count,total_records) {
					var link = base_url + "migration_management/advancedmigrate/"+dbname+"/"+ table_name + "/" + target_name + "/" + count;
						$.ajax({
						  url : link,
						  type : 'POST',
						  success : function(data) {
						  	var selected_data=data.toString();
							var selected_array = selected_data.split(",");
							var count=parseFloat(selected_array[0]);
							var offset=parseFloat(selected_array[1]);
						    percentage = ((count / total_records) * 100).toFixed(1);
						    if(table_name=="tblARTPatientTransactions"){
				   	           var percent="ppercentage";
				   	           if(count<total_records){
								recursive(dbname,table_name,target_name,offset,total_records); 
							   }
				            }else if(table_name=="tblARVDrugStockTransactions"){
				   	           var percent="dpercentage";
				   	           if(count<total_records){
								recursive(dbname,table_name,target_name,offset,total_records); 
							  }
				            }
				            $("#"+percent).text(percentage + '%');
							 
						   }
						});
				}

				function checklog(dbname,table_name,target_name){
					var link = base_url + "migration_management/checklog/" + target_name;
						$.ajax({
						  url : link,
						  type : 'POST',
						  success : function(data) {					    
						     if(table_name=='tblARTPatientTransactions' || table_name=='tblARVDrugStockTransactions'){
						     	    var selected_data=data.toString();
							        var selected_array = selected_data.split(",");
							        var count=parseFloat(selected_array[0]);
							        var offset=parseFloat(selected_array[1]);
                                	//If patient and drug transactions(Advanced Migration)
                                	advancedMigration(dbname,table_name,target_name,count,offset);
                                }else{
                                	var offset=parseFloat(data)
                                	//Other tables(Simple Migration)
                                	simpleMigration(dbname,table_name,target_name,offset);
                               }
						  }
						});
				}

		  });
		</script>
		<style type="text/css">
			#table_view {
				width:40%;
			}
			#output{
				width:600px;
				height:200px;
				background:#FFF;
				overflow:scroll;
				font-family:Verdana;
			}
			#table_select{
				display:none;
				padding:5px;
				width:400px;
			}
			#table_list{
				width:250px;
				zoom:85%;
			}
		</style>

		<div class="full-content" id="table_view" style="background:#9CF;">
	    <div>
		<ul class="breadcrumb">
		  <li><a href="<?php echo site_url().'home_controller/home' ?>"><i class="icon-home"></i><strong>Home</strong></a> 
		  	<span class="divider">/</span></li>
		  <li class="active" id="actual_page"></li>
		</ul>
	     </div>
			<fieldset>
				   <legend class="leg">
						<p>
							webADT Migration Configuration
						</p>
					</legend>
					  <p>
					 <div style="padding:10px;display:inline-block;">
					 	<div class="max-row"><h3>Database Name</h3></div>	
					 	<div class="max-row">
					 	<select id="dbname" name="dbname" style="width:400px;">
					 		<option value="">-Select database-</option>
							<?php 
							 foreach($db_tables as $db_table){
							 	echo "<option value='".$db_table['SCHEMA_NAME']."'> ". $db_table['SCHEMA_NAME']." </option>";
							 }
							?>
						</select>
					 	</div>	
						<div class="max-row"><h3>Type of Migration</h3></div>	
						<div class="max-row">			
						Full
						<input type="radio" name="tablename" id="tablename" checked="checked" value="0"/>
						Custom
						<input type="radio" name="tablename" id="tablecustom" value="1"/>
						<div id="table_select">
						<label>Tables</label>
						<select id="table_list" multiple="multiple" style="width:400px;">
							<?php 
							 foreach($tables as $col=>$table){
							 	echo "<option value='".$table."'> ". $col." </option>";
							 }
							?>
						</select>
						</div>
						</div>
						<div class="button-bar">
						<div class="btn-group">
							<input type="submit" value="Migrate"  id='migrate'" class="btn"/>
						</div>
						</div>
						<div class="max-row"><hr/></div>
						<div class="max-row">
						<div id="output">
					         Migration Progress... 
				        </div>
				        </div>
					</div>
			</fieldset>
		</div>