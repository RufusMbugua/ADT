<style>
	#inner_wrapper{
		top:120px;
	}
	.main-content{
		margin:0 auto;	
	}
	#sql{
		display:none;
	}
	.full-content{
		display:table;
	}
	table th{
		text-align:left;
	}
	#drugs_table {
		background: #FFF;
		font-size: 12px;
	}
	#drugs_table input, #drugs_table select{
		font-size:12px;
		height:24px;
		width: 4.2em;
	}
	#transaction_type_details{
		padding:1%;
		display:table-cell;
		width:15%;
	}
	#transaction_type_details th{
		text-align:left;
	}
	#drug_details{
		padding:1%;
		width:82%;
		display:table-cell;
	}
	#drugs_table{
		width: 100%;
		
	}
	#drugs_table th{
		
		text-align:center;
	}
	#sub_title{
		margin-bottom:15px;
		font-size:16px;
	}
	#submit_section{
		text-align:right;
		padding-right:10px;	
	}
</style>

<script type="text/javascript">
	$(document).ready(function(){
		$("#btn_submit").attr("disabled","disabled");
		var today = new Date();
		var today_date = ("0" + today.getDate()).slice(-2)
		var today_year = today.getFullYear();
		var today_month = today.getMonth();
		
		var month=new Array();
		month[0]="Jan";
		month[1]="Feb";
		month[2]="Mar";
		month[3]="Apr";
		month[4]="May";
		month[5]="Jun";
		month[6]="Jul";
		month[7]="Aug";
		month[8]="Sep";
		month[9]="Oct";
		month[10]="Nov";
		month[11]="Dec"; 
		var today_full_date =today_date+ "-"+month[today_month] + "-" + today_year ;
		$("#transaction_date").attr("value", today_full_date);
		
		$(".t_source").css("display","none");
		$(".t_destination").css("display","none");
		$(".t_picking_list").css("display","none");
		$("#drug_details").css("pointer-events","none");
		
		//Transaction type change
		$("#select_transtype").change(function(){
			
			
			//If transaction type not selected
			if($("#select_transtype").attr("value")==0){
				$("#drug_details").css("pointer-events","none");
				$(".t_source").css("display","none");
				$(".t_destination").css("display","none");
				$("#btn_submit").attr("disabled","disabled");
			}
			
			
			else{
				$("#btn_submit").removeAttr('disabled');
				$("#drug_details").css("pointer-events","auto");
				//Coming in
				var trans_type=$("#select_transtype option:selected").text().toLowerCase().replace(/ /g,'');
				var trans_effect=$("#select_transtype option:selected").attr('label');
				if(trans_type.indexOf('received') != -1 || trans_type.indexOf('balanceforward')!= -1 || (trans_type.indexOf('returns')!= -1 && trans_effect==1) || (trans_type.indexOf('adjustment')!= -1 && trans_effect==1) || trans_type.indexOf('startingstock')!= -1 || trans_type.indexOf('physicalcount')!= -1 ){
					//Whether to show source or not
					if(trans_type.indexOf('receivedfrom')!= -1 || (trans_type.indexOf('returns')!= -1 && trans_effect==1)){
						$(".t_destination").css("display","none");
						$(".t_source").css("display","block");
					}
					else{
						$(".t_destination").css("display","none");
						$(".t_source").css("display","none");
					}
					
					//Renitialize drugs table 
					reinitializeDrugs(stock_type,trans_type);
					
					
					$("#select_drug ").html("<option value='0'>Loading drugs ...</option> ");
					
					var _url="<?php echo base_url().'inventory_management/getAllDrugs'; ?>";
					//Get drugs that have a balance
					var request=$.ajax({
				     url: _url,
				     type: 'post',
				     dataType: "json"
				    });
				    request.done(function(data){
				    	$("#select_drug option").remove();
				    	$("#select_drug ").append("<option value='0'>Select commodity </option> ");
				    	$.each(data,function(key,value){
				    		//alert(value.drug);
				    		$("#select_drug ").append("<option value='"+value.id+"'>"+value.drug+"</option> ");
				    		
				    	});
				    })
				    
				    request.fail(function(jqXHR, textStatus) {
					  alert( "Could not retrieve the list of drugs : " + textStatus );
					});
				}
				
				//Going out
				else {
					$("#select_drug ").html("<option value='0'>Loading drugs ...</option> ");
					var stock_type=<?php echo  $stock_type ?>;
					var _url="<?php echo base_url().'inventory_management/getStockDrugs'; ?>";
					//In case of dispensed to patients,adjustments(-),returns,losses,expiries, hide destination
					if(trans_type.indexOf('dispensed')!= -1 || (trans_type.indexOf('adjustment')!= -1 && trans_effect==0) ||  trans_type.indexOf('loss')!= -1 || trans_type.indexOf('expir') != -1){
						$(".t_destination").css("display","none");
						$(".t_source").css("display","none");
					}
					else{
						$(".t_destination").css("display","block");
						$(".t_source").css("display","none");
					}
					
					//Renitialize drugs table 
					reinitializeDrugs(stock_type,trans_type);
					
					//Get drugs that have a balance
					var request=$.ajax({
				     url: _url,
				     type: 'post',
				     data: {"stock_type":stock_type},
				     dataType: "json"
				    });
				    request.done(function(data){
				    	$("#select_drug option").remove();
				    	$("#select_drug ").append("<option value='0'>Select commodity </option> ");
				    	$.each(data,function(key,value){
				    		//alert(value.drug);
				    		$("#select_drug ").append("<option value='"+value.id+"'>"+value.drug+"</option> ");
				    		
				    	});
				    })
				    
				    request.fail(function(jqXHR, textStatus) {
					  alert( "Could not retrieve the list of drugs : " + textStatus );
					});
				   
				    
				    
				}
			}
		})
		
		//Source change
		$("#select_source").change(function(){
			var stock_type=<?php echo  $stock_type ?>;
			var trans_type=$("#select_transtype option:selected").text().toLowerCase().replace(/ /g,'');
			var selected_source=$("#select_source option:selected").text().toLowerCase().replace(/ /g,'');
			var supplier_name='<?php echo $supplier_name ?>';
			var pipeline_name=supplier_name.toLowerCase().replace(/ /g,'');
			
			//Check if transaction if pharmacy and source is Main Store
			if(stock_type==2 && (selected_source.indexOf('mainstore')!= -1 || selected_source.indexOf('store')!= -1)){
			
				//If transaction if receive from Main Store to Pharmacy, get available drugs in the Main Store
				$("#select_drug ").html("<option value='0'>Loading drugs ...</option> ");
				var _url="<?php echo base_url().'inventory_management/getStockDrugs'; ?>";
				//Get drugs that have a balance
				var request=$.ajax({
			     url: _url,
			     type: 'post',
			     data: {"stock_type":1},
			     dataType: "json"
			    });
			    request.done(function(data){
			    	$("#select_drug option").remove();
			    	$("#select_drug ").append("<option value='0'>Select commodity </option> ");
			    	$.each(data,function(key,value){
			    		//alert(value.drug);
			    		$("#select_drug ").append("<option value='"+value.id+"'>"+value.drug+"</option> ");
			    		
			    	});
			    })
			    
			    request.fail(function(jqXHR, textStatus) {
				  alert( "Could not retrieve the list of drugs : " + textStatus );
				});
			}
			
			//If stock type is main store, transaction type is receive from and source is Pipeline, get orders dispatched
			else if(stock_type==1 && trans_type.indexOf('received') != -1 &&  selected_source.indexOf(pipeline_name) != -1){
				$(".t_picking_list").css("display","block");
			}
			else{
				$(".t_picking_list").css("display","none");
			}
			
		})
		
		//Picking list changed
		$("#picking_list_name").change(function(){
			var rowCount = $('#drugs_table tr').length;
			//Check if details were entered before submiting
			if(rowCount==2){
			
			}
			var link="<?php echo base_url().'inventory_management/getOrderDetails' ?>";
			//Get list of orders
			var order_id=$("#picking_list_name").val();
			$.ajax({
				url : link,
				type : 'POST',
				dataType : 'json',
				data: {"order_id":order_id},
				success : function(data) {
					var data_count=data.length;
					var x=1;
					var last_row=$('#drugs_table tr:last');
					$.each(data, function(i, jsondata) {
						var drug_id=data[i]['id'];
						var resupply=data[i]['resupply'];
						var pack_size=data[i]['pack_size'];
						var drug_selected=last_row.find(".drug").val();
						var cloned_object = $('#drugs_table tr:last').clone(true);
						var drug_row = cloned_object.attr("drug_row");
						var next_drug_row = parseInt(drug_row) + 1;
						cloned_object.attr("drug_row", next_drug_row);
						cloned_object.find(".remove").show();
						var packs = cloned_object.find(".pack");
						var expiry_id = "expiry_date_" + next_drug_row;
						cloned_object.find(".drug").attr('value',drug_id);
						cloned_object.find(".pack").attr('value',resupply);
						cloned_object.find(".pack_size").attr('value',pack_size);
						var expiry_selector = "#" + expiry_id;
						$(expiry_selector).datepicker({
							defaultDate : new Date(),
							changeYear : true,
							changeMonth : true
						});
						
						//Validity check
						if(!isNaN(pack_size) && pack_size.length > 0 && !isNaN(resupply) && resupply.length > 0) {
							var qty=resupply * pack_size;
							cloned_object.find(".quantity ").attr('value',qty);
						}
						cloned_object.insertAfter('#drugs_table tr:last');
						refreshDatePickers();
						if(x==data_count){
							$('#drugs_table tbody tr:first').remove();
						}
						x++;
						
					});
	
				}
			});
			
			
		});
		
		//Drug change
		$("#select_drug").change(function(){
			var trans_type=$("#select_transtype option:selected").text().toLowerCase().replace(/ /g,'');
			var trans_effect=$("#select_transtype option:selected").attr('label');
			var stock_type=<?php echo  $stock_type ?>;
			//Get source selected
			var selected_source=$("#select_source option:selected").text().toLowerCase().replace(/ /g,'');
			resetFields($(this));
			var row=$(this);
			
			//Receiving from Main Store To Pharmacy
			if(trans_type.indexOf('received') != -1 && stock_type==2 && (selected_source.indexOf('mainstore')!= -1 || selected_source.indexOf('store')!= -1)){
				$(this).closest("tr").find("#batch_1").css("display","none");
				$(this).closest("tr").find("#batchselect_1").css("display","block");
				$(this).closest("tr").find("#batchselect_1 ").html("<option value='0'>Loading batches ...</option> ");
				var selected_drug=$(this).val();
				
				//Get batches that have not yet expired and have stock balance
				var _url="<?php echo base_url().'inventory_management/getBacthes'; ?>";
				
				var request=$.ajax({
			     url: _url,
			     type: 'post',
			     data: {"selected_drug":selected_drug,"stock_type":1},
			     dataType: "json"
			    });
			    request.done(function(data){
			    	row.closest("tr").find(".batchselect option").remove();
			    	row.closest("tr").find(".batchselect ").append("<option value='0'>Select batch </option> ");
			    	$.each(data,function(key,value){
			    		row.closest("tr").find("#unit").val(value.Name);
			    		row.closest("tr").find("#pack_size").val(value.pack_size);
			    		//alert(value.drug);
			    		row.closest("tr").find(".batchselect").append("<option value='"+value.batch_number+"'>"+value.batch_number+"</option> ");
			    		
			    	});
			    });
			    request.fail(function(jqXHR, textStatus) {
				  alert( "Could not retrieve the list of batches : " + textStatus );
				});
			}
			
			//Receiving
			else if(trans_type.indexOf('received') != -1 || trans_type.indexOf('balanceforward')!= -1 || (trans_type.indexOf('returns')!= -1 && trans_effect==1) || (trans_type.indexOf('ajustment')!= -1 && trans_effect==1) || trans_type.indexOf('startingstock')!= -1 || trans_type.indexOf('physicalcount')!= -1 ){
				row.closest("tr").find("#batch_1").css("display","block");
				row.closest("tr").find("#batchselect_1").css("display","none");
				
				var selected_drug=$(this).val();
				var _url="<?php echo base_url().'inventory_management/getDrugDetails'; ?>";
				var request=$.ajax({
			     url: _url,
			     type: 'post',
			     data: {"selected_drug":selected_drug},
			     dataType: "json"
			    });
			    request.done(function(data){
			    	$.each(data,function(key,value){
			    		row.closest("tr").find("#unit").val(value.Name);
			    		row.closest("tr").find("#pack_size").val(value.pack_size);
			    		
			    	});
			    });
			    request.fail(function(jqXHR, textStatus) {
				  alert( "Could not retrieve drug details : " + textStatus );
				});
				
			}
			//If issuing
			else{
				$(this).closest("tr").find("#batch_1").css("display","none");
				$(this).closest("tr").find("#batchselect_1").css("display","block");
				$(this).closest("tr").find("#batchselect_1 ").html("<option value='0'>Loading batches ...</option> ");
				var selected_drug=$(this).val();
				var stock_type=<?php echo  $stock_type ?>;
				
				//Get batches that have not yet expired and have stock balance
				var _url="<?php echo base_url().'inventory_management/getBacthes'; ?>";
				
				var request=$.ajax({
			     url: _url,
			     type: 'post',
			     data: {"selected_drug":selected_drug,"stock_type":stock_type},
			     dataType: "json"
			    });
			    request.done(function(data){
			    	row.closest("tr").find(".batchselect option").remove();
			    	row.closest("tr").find(".batchselect ").append("<option value='0'>Select batch </option> ");
			    	$.each(data,function(key,value){
			    		row.closest("tr").find("#unit").val(value.Name);
			    		row.closest("tr").find("#pack_size").val(value.pack_size);
			    		//alert(value.drug);
			    		row.closest("tr").find(".batchselect").append("<option value='"+value.batch_number+"'>"+value.batch_number+"</option> ");
			    		
			    	});
			    });
			    request.fail(function(jqXHR, textStatus) {
				  alert( "Could not retrieve the list of batches : " + textStatus );
				});
				
			}
			
		});
		
	
		//Batch change
		$(".batchselect").change(function(){
			var trans_type=$("#select_transtype option:selected").text().toLowerCase().replace(/ /g,'');
			var trans_effect=$("#select_transtype option:selected").attr('label');
			var stock_type=<?php echo  $stock_type ?>;
			//If transaction type if received from
			if(trans_type.indexOf('received') != -1){
				var selected_source=$("#select_source option:selected").text().toLowerCase().replace(/ /g,'');
				//If transaction if from Main Store to Pharmacy, get remaining balance from store
				
				if(stock_type==2 && (selected_source.indexOf('mainstore')!= -1 || selected_source.indexOf('store')!= -1)){
					stock_type=1;
				}
			}
			
			resetFields($(this));
			var row=$(this);
			
			//Get batch details(balance,expiry date)
			if($(this).val()!=0){
				var batch_selected=$(this).val();
				var selected_drug=row.closest("tr").find("#select_drug").val();
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
			    		row.closest("tr").find(".quantity_available ").val(value.balance);
			    		
			    	});
			    });
			    request.fail(function(jqXHR, textStatus) {
				  alert( "Could not retrieve batch details : " + textStatus );
				});
				
			}
		});
		
		//Add datepicker for the transaction date
		$("#transaction_date").datepicker({
			defaultDate : new Date(),
			dateFormat : 'dd-M-yy',
			changeYear : true,
			changeMonth : true
		});
		//Add datepicker for the expiry date
		$("#expiry_date").datepicker({
			defaultDate : new Date(),
			dateFormat : $.datepicker.ATOM,
			changeYear : true,
			changeMonth : true
		});
		//Check if number of packs has changed and automatically calculate the total
		$(".pack").keyup(function() {
			updateCommodityQuantity($(this));
			
		});
		
		//Calculate the total cost automatically
		$("#unit_cost").keyup(function() {
			updateTotalCost($(this));
		});
		
		$(".pack").change(function() {
			updateCommodityQuantity($(this));
		});
		
		$(".quantity").change(function() {
			updateCommodityQuantityUnit($(this));
		});
		
		$("#unit_cost").change(function() {
			updateTotalCost($(this));
		});
		$(".remove").click(function() {
			$(this).closest('tr').remove();
		});
		
		$(".add").click(function() {
			var last_row=$('#drugs_table tr:last');
			var drug_selected=last_row.find(".drug").val();
			var quantity_entered=last_row.find(".quantity").val();
			if(last_row.find(".quantity").hasClass("stock_add_form_input_error")){
				alert("Error !Quantity issued is greater than qty available!");
			}
			
			else if(drug_selected==0 ){
				alert("You have not selected a drug!");
			}
			else if(quantity_entered=="" || quantity_entered==0){
				alert("Please Specify the Quantity of the Drug");
			}
			else{
				var cloned_object = $('#drugs_table tr:last').clone(true);
				var drug_row = cloned_object.attr("drug_row");
				var next_drug_row = parseInt(drug_row) + 1;
				cloned_object.attr("drug_row", next_drug_row);
				var batch_id = "batch_" + next_drug_row;
				var batchselect_id = "batchselect_" + next_drug_row;
				var quantity_id = "quantity_" + next_drug_row;
				var expiry_id = "expiry_date_" + next_drug_row;
				var batch = cloned_object.find(".batch");
				var batchselect = cloned_object.find(".batchselect");
				batchselect.empty();
				var packs = cloned_object.find(".pack");
				var unit = cloned_object.find(".unit");
				var pack_size = cloned_object.find(".pack_size");
				var quantity = cloned_object.find(".quantity");
				var quantity_available = cloned_object.find(".quantity_available");
				var expiry_date = cloned_object.find(".expiry");
				var unit_cost = cloned_object.find(".unit_cost");
				var total_amount = cloned_object.find(".amount");
				var comment = cloned_object.find(".comment");
				cloned_object.find(".remove").show();
				batch.attr("id", batch_id);
				batchselect.attr("id", batchselect_id);
				quantity.attr("id", quantity_id);
				expiry_date.attr("id", expiry_id);
				batch.attr("value", "");
				batchselect.attr("value", "");
				quantity.attr("value", "");
				expiry_date.attr("value", "");
				packs.attr("value", "");
				pack_size.attr("value", "");
				unit.attr("value", "");
				quantity_available.attr("value", "");
				unit_cost.attr("value","");
				total_amount.attr("value","");
				comment.attr("value","");
				var expiry_selector = "#" + expiry_id;
		
				$(expiry_selector).datepicker({
					defaultDate : new Date(),
					changeYear : true,
					changeMonth : true
				});
				cloned_object.insertAfter('#drugs_table tr:last');
				refreshDatePickers();
			}
			
	
			return false;
		});
		
		//Save transaction details
		$("#btn_submit").click(function(){
			//Check if select source is visible
			if($("#select_source").is(":visible")){
				if($("#select_source").val()==0){
					alert("Please select a source !");
					return;
				}
			}
			else if($("#select_destination").is(":visible")){
				if($("#select_destination").val()==0){
					alert("Please select a destination !");
					return;
				}
			}
			
			var trans_type=$("#select_transtype option:selected").text().toLowerCase().replace(/ /g,'');
			var trans_effect=$("#select_transtype option:selected").attr('label');
			var selected_source=$("#select_source option:selected").text().toLowerCase().replace(/ /g,'');
			var stock_type=<?php echo  $stock_type ?>;
			var last_row=$('#drugs_table tr:last');
			if(last_row.find(".quantity").hasClass("stock_add_form_input_error")){
				alert("There is a commodity that has a quantity greater than the quantity available!");
				return;
			}
			
			var rowCount = $('#drugs_table tr').length;
			//Check if details were entered before submiting
			if(rowCount==2){
				var drug_selected=last_row.find(".drug").val();
				var quantity_entered=last_row.find(".quantity").val();
				if(drug_selected==0 ){
					alert("You have not selected a drug!");
					return;
				}
				else if(quantity_entered=="" || quantity_entered==0){
					alert("You have not entered any quantity!");
					return;
				}
				
			}
			
			
			
			var facility=<?php echo $facility ?>;
			var user=<?php echo $user_id ?>;
			
			//Before going any further, first calculate the number of drugs being recorded
			var drugs_count = 0;
			var batch_type=0;
			//Check if transaction is coming in,validate batch number first
			if(trans_type.indexOf('received') != -1 || trans_type.indexOf('balance')!= -1 || (trans_type.indexOf('returns')!= -1 && trans_effect==1) || (trans_type.indexOf('ajustment')!= -1 && trans_effect==1) || trans_type.indexOf('startingstock')!= -1 || trans_type.indexOf('physicalcount')!= -1 ) {
				//If transction is from Main Store to Pharmacy,get batch selected
				if(trans_type.indexOf('received') != -1 && (selected_source.indexOf('mainstore')!= -1 || selected_source.indexOf('store')!= -1) && stock_type=='2'){
					batch_type=1;
				}
				else{
					batch_type=0;
					
				}
			}
			else{
				batch_type=1;
			}
			var c=0;
			$.each($(".drug"), function(i, v) {
				//Check if batch number was entered
				if(batch_type==0){
					if($(this).closest("tr").find(".batch").attr("value")=="" || $(this).closest("tr").find(".expiry ").attr("value")==""){
						c=1;
						alert("Please make sure you have entered a batch number and selected an expiry date for all drugs!");
						return false;
					}
				}
				//Check if batch number was selected
				else if(batch_type==1){
					if($(this).closest("tr").find(".batch").is(":visible") && $(this).closest("tr").find(".batch").attr("value")==0){
						c=1;
						alert("Please make sure you have selected a batch number and selected an expiry date for all the drugs!");
						return false;
					}
					else if($(this).closest("tr").find(".batch_select").is(":visible") && $(this).closest("tr").find(".batch_select").attr("value")==0){
						c=1;
						alert("Please make sure you have selected a batch number and selected an expiry date for all the drugs!");
						return false;
					}
				}
				
				
				
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
			if(trans_type.indexOf('received') != -1 || trans_type.indexOf('balance')!= -1 || (trans_type.indexOf('returns')!= -1 && trans_effect==1) || (trans_type.indexOf('ajustment')!= -1 && trans_effect==1) || trans_type.indexOf('startingstock')!= -1 || trans_type.indexOf('physicalcount')!= -1 ) {
				//If transction is from Main Store to Pharmacy,get batch selected
				if(trans_type.indexOf('received') != -1 && (selected_source.indexOf('mainstore')!= -1 || selected_source.indexOf('store')!= -1) && stock_type=='2'){
					var batches = retrieveFormValues_Array('batchselect');
				}
				else{
					var batches = retrieveFormValues_Array('batch');
					
				}
				
			} 
			else {
				var batches = retrieveFormValues_Array('batchselect');
			}
			var transaction_type=dump["transaction_type"];
			var expiries = retrieveFormValues_Array('expiry');
			var quantities = retrieveFormValues_Array('quantity');
			var packs = retrieveFormValues_Array('pack');
			var unit_costs = retrieveFormValues_Array('unit_cost');
			var comments = retrieveFormValues_Array('comment');
			var amounts = retrieveFormValues_Array('amount');
			var available_quantity=retrieveFormValues_Array('available_quantity');
			var balance=0;
			
			//If transaction is from store
			var stock_type=<?php echo $stock_type; ?>;
			if(stock_type=='1'){
				//Stockin coming in
				if(trans_type.indexOf('received') != -1 || trans_type.indexOf('balanceforward')!= -1 || (trans_type.indexOf('returns')!= -1 && trans_effect==1) || (trans_type.indexOf('adjustment')!= -1 && trans_effect==1) || trans_type.indexOf('startingstock')!= -1 || trans_type.indexOf('physicalcount')!= -1) {
					var quantity_choice = "quantity";
					var quantity_out_choice = "quantity_out";
				} else {
					var quantity_choice = "quantity_out";
					var quantity_out_choice = "quantity";
				}
			}
			//If transaction is from pharmacy
			else if(stock_type=='2'){
				//If transaction is received from
				if(trans_type.indexOf('received') != -1 || trans_type.indexOf('balanceforward')!= -1 || (trans_type.indexOf('returns')!= -1 && trans_effect==1) || (trans_type.indexOf('adjustment')!= -1 && trans_effect==1) || trans_type.indexOf('startingstock')!= -1 || trans_type.indexOf('physicalcount')!= -1) {
					var quantity_choice = "quantity";
					var quantity_out_choice = "quantity_out";
				} else {
					var quantity_choice = "quantity_out";
					var quantity_out_choice = "quantity";
				}
			}
			
			//After getting the number of drugs being recorded, create a unique entry (sql statement) for each in the database in this loop
			var sql_queries = "";
			var source="";
			var destination="";
			var remaining_drugs=drugs_count;
			for(var i = 0; i < drugs_count; i++) {
				//Check if batch number was entered or selected for all drugs
				if(c==1){
					return false;
				}
				var _url="<?php echo base_url().'inventory_management/save'; ?>";
				var get_qty_choice=quantity_choice;
				var get_qty_out_choice=quantity_out_choice;
				var get_source=dump["source"];
				var get_destination=dump["destination"];
				var get_transaction_date=dump["transaction_date"];
				var ref_number=dump["reference_number"];
				var get_transaction_type=dump["transaction_type"];
				var get_drug_id=drugs[i];
				var get_batch=batches[i];
				var get_expiry=expiries[i];
				var get_packs=packs[i];
				var get_qty=quantities[i];
				var get_available_qty=available_quantity[i];
				var get_unit_cost=unit_costs[i];
				var get_amount=amounts[i];
				var get_comment=comments[i];
				var get_stock_type=stock_type;
				var get_user=user;
				$("#btn_submit").attr("disabled","disabled");
				var request=$.ajax({
			     url: _url,
			     type: 'post',
			     data: {"remaining_drugs":i,"quantity_choice":get_qty_choice,"quantity_out_choice":get_qty_out_choice,"source_name":selected_source,"source":get_source,"destination":get_destination,"transaction_date":get_transaction_date,"reference_number":ref_number,"trans_type":trans_type,"trans_effect":trans_effect,"transaction_type":get_transaction_type,"drug_id":get_drug_id,"batch":get_batch,"expiry":get_expiry,"packs":get_packs,"quantity":get_qty,"available_qty":get_available_qty,"unit_cost":get_unit_cost,"amount":get_amount,"comment":get_comment,"stock_type":get_stock_type},
			     dataType: "json"
			    });
			    request.always(function(data){
					remaining_drugs-=1;
					if(remaining_drugs==0){
						//Update status for order, from dispatched to delivered
						var order_id=$("#picking_list_name").val();
						var _url="<?php echo base_url().'inventory_management/set_order_status'; ?>";
						var request=$.ajax({
							url: _url,
						    type: 'post',
						    data: {"order_id":order_id,"status":"4"},
						    dataType: "json"
						});
						request.always(function(data){
							//Set session after completing transactions
							var _url="<?php echo base_url().'inventory_management/set_transaction_session'; ?>";
							var request=$.ajax({
								url: _url,
								type: 'post',
								data: {"remaining_drugs":remaining_drugs},
								dataType: "json"
							});
							request.always(function(data){
								window.location.replace('<?php echo base_url().'inventory_management'?>');
							});
						});
						
					}
			    });
			  
			};
			
			
		})
		
	});
	
	//Reinitialize drugs table
	function  reinitializeDrugs(stock_type,trans_type){
		//------------Whether show select order from picking list or not
		if(stock_type==1 && trans_type.indexOf('received') != -1 &&  selected_source.indexOf(pipeline_name) != -1){
			$(".t_picking_list").css("display","block");
		}
		else{
			//Before reinitialize table, check if picking list combo box is visible
			if($(".t_picking_list").is(":visible") && $("#picking_list_name").val()!=0){
				//Clone drug table row
				var cloned_object = $('#drugs_table tr:last').clone(true);
				$('#drugs_table tbody tr').remove();
				$('#drugs_table tbody ').append(cloned_object);
				//Reset the list of drugs
				
				//Reset all the fields
				var row=$('#drugs_table tbody tr:first');
				resetFields(row);
			}
			if($(".remove").is(":visible")){
				row.closest("tr").find(".remove").remove();
			}
			
			$(".t_picking_list").css("display","none");
			$("#select_source").val("0");
			$("#picking_list_name").val("0");
			
		}
		//------------Whether show select order from picking list or not end
	}
	
	function resetFields(row){
		//row.closest("tr").find(".pack_size").val("");
		row.closest("tr").find(".pack").val("");
		row.closest("tr").find(".quantity").val("");
		row.closest("tr").find(".expiry").val("");
		row.closest("tr").find(".quantity_available").val("");
		row.closest("tr").find(".unit_cost").val("");
		row.closest("tr").find("#total_amount").val("");
	}
	function updateCommodityQuantity(pack_object) {
		var trans_type=$("#select_transtype option:selected").text().toLowerCase().replace(/ /g,'');
		var trans_effect=$("#select_transtype option:selected").attr('label');
		var packs = pack_object.attr("value");
		var pack_size = pack_object.closest("tr").find(".pack_size").attr("value");
		var quantity_holder = pack_object.closest("tr").find(".quantity");
		var available_quantity=pack_object.closest("tr").find(".quantity_available").val();
		available_quantity=parseInt(available_quantity);
		
		if(!isNaN(pack_size) && pack_size.length > 0 && !isNaN(packs) && packs.length > 0) {
			var qty=packs * pack_size;
			//If stock is going out, check that qty issued to be <= to qty available
			
			//Transaction coming in
			if(trans_type.indexOf('received') != -1 || trans_type.indexOf('balanceforward')!= -1 || (trans_type.indexOf('returns')!= -1 && trans_effect==1) || (trans_type.indexOf('adjustment')!= -1 && trans_effect==1) || trans_type.indexOf('startingstock')!= -1 || trans_type.indexOf('physicalcount')!= -1 || $("#select_transtype").attr("value") == 0) {
				quantity_holder.css("background-color","#FFF");
				quantity_holder.attr("value",qty );
				
			} 
			//Transaction going out
			else {
				if(available_quantity>=qty){
					quantity_holder.css("background-color","#FFF");
					quantity_holder.attr("value",qty );
					quantity_holder.removeClass("stock_add_form_input_error");
				}
				else{
					quantity_holder.attr("value",qty );
					alert("Error !Quantity issued is greater than qty available!");
					quantity_holder.addClass("stock_add_form_input_error");
					quantity_holder.css("background-color","rgb(255, 92, 52)");
				}
			}
			
			
			
		}
	}
	
	function updateCommodityQuantityUnit(pack_object) {
		var trans_type=$("#select_transtype option:selected").text().toLowerCase().replace(/ /g,'');
		var trans_effect=$("#select_transtype option:selected").attr('label');
		var packs = pack_object.attr("value");
		var pack_size = pack_object.closest("tr").find(".pack_size").attr("value");
		var quantity_holder = pack_object.closest("tr").find(".pack");
		var available_quantity=pack_object.closest("tr").find(".quantity_available").val();
		available_quantity=parseInt(available_quantity);
		
		if(!isNaN(pack_size) && pack_size.length > 0 && !isNaN(packs) && packs.length > 0) {
			var qty=packs/pack_size;
			qty=qty.toFixed(0);
			//If stock is going out, check that qty issued to be <= to qty available
			
			//Transaction coming in
			if(trans_type.indexOf('received') != -1 || trans_type.indexOf('balanceforward')!= -1 || (trans_type.indexOf('returns')!= -1 && trans_effect==1) || (trans_type.indexOf('adjustment')!= -1 && trans_effect==1) || trans_type.indexOf('startingstock')!= -1 || trans_type.indexOf('physicalcount')!= -1 || $("#select_transtype").attr("value") == 0) {
				quantity_holder.css("background-color","#FFF");
				quantity_holder.attr("value",qty );
				
			} 
			//Transaction going out
			else {
				if(available_quantity>=qty){
					quantity_holder.css("background-color","#FFF");
					quantity_holder.attr("value",qty );
					quantity_holder.removeClass("stock_add_form_input_error");
				}
				else{
					quantity_holder.attr("value",qty );
					alert("Error !Quantity issued is greater than qty available!");
					quantity_holder.addClass("stock_add_form_input_error");
					quantity_holder.css("background-color","rgb(255, 92, 52)");
				}
			}
			
			
			
		}
	}
	
	function updateTotalCost(unit_cost_object) {
		var unit_cost = unit_cost_object.attr("value");
		var quantity_holder = unit_cost_object.closest("tr").find(".pack").attr("value");
		var total_cost=unit_cost_object.closest("tr").find(".amount");
		if(!isNaN(unit_cost) && unit_cost.length > 0 && !isNaN(quantity_holder) && quantity_holder.length > 0) {
			total_cost.attr("value", unit_cost * quantity_holder);
		}
		else{
			total_cost.attr("value",0);
		}
	}
	
	function refreshDatePickers() {
		var counter = 0;
		$('.expiry').each(function() {
			var new_id = "date_" + counter;
			$(this).attr("id", new_id);
			$(this).datepicker("destroy");
			$(this).not('.hasDatePicker').datepicker({
				defaultDate : new Date(),
				dateFormat : $.datepicker.ATOM,
				changeYear : true,
				changeMonth : true
			});
			counter++;

		});
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
	
	
	
</script>

<div class="main-content">
	
		<div>
			<span id="msg_server"></span>
		</div>
		<div class="full-content" id="stock_div" style="background:#9CF">
		<form id="stock_form" method="post" action="<?php echo base_url().'inventory_management/save' ?>" >

			<textarea name="sql" id="sql" style="display: none"></textarea>
			
			<div id="sub_title" >
				<a href="<?php  echo base_url().'inventory_management ' ?>">Inventory</a> <i class=" icon-chevron-right"></i>  <?php echo $store ?> 
				<hr size="1">
			</div>
			<div id="transaction_type_details">
				<h3>Transaction details</h3>
				<table>
					<tr><th>Transaction Date</th></tr>
					<tr><td><input type="text" name="transaction_date" id="transaction_date" class="input-large" /></td></tr>
					<tr><th>Transaction Type</th></tr>
					<tr><td><select name="transaction_type" id="select_transtype" class="input-large">
								<option label="" value="0" selected="">-- Select Type --</option>
								<?php
								foreach ($transaction_types as $transaction_type) {
									//If transaction is a pharmacy transaction,
									if($stock_type==2){
										//Hide issued to when transaction is from pharmacy
										$trans_name=str_replace(" ","",strtolower($transaction_type['Name']));
										$findme="issued";
										$pos = strpos($trans_name, $findme);
										//Check if transaction type is not issued to
										if($pos===false){
									?>
									<option label="<?php echo $transaction_type['Effect'] ?>"  value="<?php echo $transaction_type['id'] ?>"><?php echo $transaction_type['Name'] ?></option>
									<?php
										}
									}
									else{
										////If transaction is a store transaction,hide dispensed to
										$trans_name=str_replace(" ","",strtolower($transaction_type['Name']));
										$findme="dispense";
										$pos = strpos($trans_name, $findme);
										if($pos===false){
										?>
										<option label="<?php echo $transaction_type['Effect'] ?>" value="<?php echo $transaction_type['id'] ?>"><?php echo $transaction_type['Name'] ?></option>
										<?php
										}
									}
								}
								?>
								
							</select>
						</td>
					</tr>
					<tr><th>Ref. /Order No</th></tr>
					<tr><td><input type="text" name="reference_number" id="reference_number" class="input-large" /></td></tr>
					<tr class="t_source"><th>Source</th></tr>
					<tr class="t_source"><td>
						<select name="source" id="select_source" class="input-large">
							<option value="0">--Select Source --</option>
							<?php
							foreach ($drug_sources as $drug_source) {
								$drug_s=str_replace(" ","",strtolower($drug_source['Name']));
								$findme1="store";
								$findme2="main";
								$pos1 = strpos($drug_s, $findme1);
								$pos2 = strpos($drug_s, $findme2);
								$pipeline_name=str_replace(" ","",strtolower($supplier_name));
								$pos3 = strpos($drug_s, $pipeline_name);
								//If stock type is main store, don't display main store as source
								if($stock_type==1 && ($pos1==true || $pos2==true)){
									continue;
								}
								//If transaction type is pharmacy, don't display pipeline
								else if ($stock_type==2 && ($pos3==true || $pos3===0)){
									continue;
								}
							?>
							<option value="<?php echo $drug_source['id'] ?>"><?php echo $drug_source['Name'] ?></option>
							<?php
							}
							?>
						</select>
						</td>
					</tr>
					<tr class="t_destination"><th>Destination</th></tr>
					<tr class="t_destination"><td>
							<select name="destination" id="select_destination" class="input-large">
								<option value="0">--Select Destination --</option>
								<?php
								//Add satelittes if transaction from main store
								
									foreach ($satelittes as $satelitte) {
										?>
										<option value="<?php echo $satelitte['facilitycode'] ?>"><?php echo $satelitte['name'] ?></option>
									<?php
									}
								
								
								foreach ($drug_destinations as $drug_destination) {
									$drug_d=str_replace(" ","",strtolower($drug_destination['Name']));
									$findme1="outpatient";
									$pos1 = strpos($drug_d, $findme1);
									//Not picking outpatient pharmacy if stock type is pharmacy
									if($stock_type==2 && $pos1===0){
										continue;
									}
									//Outpatient pharmacy
									else if($pos1===0){
									?>
									<option value="<?php echo $facility ?>"><?php echo $drug_destination['Name'] ?></option>
									<?php
									}
									else{
									?>
									<option value="<?php echo $drug_destination['id'] ?>"><?php echo $drug_destination['Name'] ?></option>
									<?php	
									}
								?>
								
								<?php
								}
								?>
							</select>
						</td>
					</tr>
					
					<!-- Select from orders dispacthed -->
					<tr class="t_picking_list"><th>Select Order </th></tr>
					<tr class="t_picking_list">
						<td>
						<select id="picking_list_name" name="picking_list_name" class="input-large" >
							<option value="0">-- Select One --</option>
							<?php
							foreach($picking_lists as $picking_list){
							?>
							<option value="<?php echo $picking_list['id'] ?>" ><?php echo "Order no: ".$picking_list['id']."(".date('M-Y',strtotime($picking_list['Period_Begin'])).")"; ?></option>
							<?php
							}
							?>
						</select>
						</td>
					</tr>
					
				</table>
			</div>
			<div id="drug_details">
				<h3>Drug details</h3>
				<table border="0" class="table table-bordered" id="drugs_table">
					<thead>
						<tr>
							<th>Drug</th>
							<th>Unit</th>
							<th>Pack Size</th>
							<th>Batch No.</th>
							<th>Expiry&nbsp;Date</th>
							<th>Packs</th>
							<th>Qty</th>
							<th>Available Qty</th>
							<th>Pack Cost</th>
							<th>Total</th>
							<th>Comment</th>
							<th style="width:350px">Action</th>
						</tr>
					</thead>
					<tbody >
						<tr drug_row="1">
							<td>
							<select id="select_drug" name="drug" class="drug"  style="min-width:150px;max-width:250px;">
								<option value="0"> -- Select Commodity --</option>
							</select></td>
							<td>
							<input type="text" id="unit" name="unit" class="unit small_text input-small" readonly="" />
							</td>
							<td>
							<input type="text"  id="pack_size" name="pack_size" class="pack_size small_text input-small" readonly="" />
							</td>
							<td><select name="batchselect" class="batchselect" id="batchselect_1" style="display:none;width:90px;"></select>
							<input type="text" name="batch" class="batch  validate[required] input-small"   id="batch_1" style="width:90px;"/>
							</td>
							<td>
							<input type="text" name="expiry" class="expiry medium_text" id="expiry_date" style="width:78px;font-size: 11px" />
							</td>
							<td>
							<input type="text" name="pack" class="pack small_text validate[required] input-small" id="packs_1"  />
							</td>
							<td>
							<input type="text" name="quantity" id="quantity_1" class="quantity small_text input-small"  />
							</td>
							<td>
							<input type="text" id="available_quantity" name="available_quantity" class="quantity_available medium_text input-small" readonly="" />
							</td>
							<td>
							<input type="text" name="unit_cost" id="unit_cost" class="unit_cost small_text input-small" />
							</td>
							<td>
							<input type="text" name="amount" id="total_amount" class="amount input-small input-small" readonly="" />
							</td>
							<td >
							<input type="text" name="comment" class="comment " style="width:150px"/>
							</td>
							<td style="text-align: center;font-size: 10px" >
								<a href="#" class="add" >Add</a>
								<span class="remove" style="display:none;"> |<a href="#" >Remove</a></span>
							</td>
							
						</tr>
					</tbody>
					
					
				</table>
			</div>
		
		
	
		<div id="submit_section">
			<input type="reset" class="btn" id="reset" value="Reset Fields" />
			<input type="button" class="btn" id="btn_submit" value="Submit" />
		</div>
	</form>
	</div>
</div>