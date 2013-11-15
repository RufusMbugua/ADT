<style>
	.dataTables_wrapper{
		width: 100%;
	}
	#DataTables_Table_0{
		font-size:15px;
	}
</style>
<script type="text/javascript">
		var url = "";
	$(function() {
	$("#confirm_delete").dialog( {
	height: 150,
	width: 450,
	modal: true,
	autoOpen: false,
	buttons: {
	"Delete Order": function() {
	delete_record();
	},
	Cancel: function() {
	$( this ).dialog( "close" );
	}
	}

	} );
	
	$("#confirm_delete_aggregated").dialog( {
	height: 180,
	width: 900,
	modal: true,
	autoOpen: false,
	buttons: {
	"Delete Order": function() {
	delete_record();
	},
	Cancel: function() {
	$( this ).dialog( "close" );
	}
	}

	} );

	$(".delete").live('click',function(){ 
		var x=0;
		var agg_ord_id="";
		var order_id=$(this).attr("order");
		$.ajax({
		  url:"<?php echo base_url().'order_management/order_aggregate/'?>" +$(this).attr("order"),
		  dataType: "json",
		  type: 'GET',
		  statusCode: {
		    404: function() {
		      alert("The page you are requesting was not found !");
		    }
		  }
		}).done(function (data) {
			for (var key in data){
      			if (data.hasOwnProperty(key)){
      				x=1;
      				agg_ord_id=data[key].aggregated_order_id;	
      			}
      		}
      		
      		if(x==0){
      			url = "<?php echo base_url().'order_management/delete_order/'?>" +order_id;
      			$("#confirm_delete").dialog('open');
      		}
      		else{
      			<?php if($parent->parent!=$central_facility){?> alert("This order is linked to an aggregated order. You do not have enough privileges to delete it !");  
      			<?php }
				else{
				?>	
				url = "<?php echo base_url().'order_management/delete_order/'?>" +order_id+"/"+agg_ord_id;
      			$("#confirm_delete_aggregated").dialog('open');
				<?php } ?>
      			
      		}
			
		});
		
	});
		setTimeout(function(){
			$(".message").fadeOut("2000");
		},6000);
		
	    /*Auto-Sync Orders to NASCOP when internet is present*/
		var online = navigator.onLine;
		if(online==true){
		    syncOrders();
		}
		
		
	});
	function delete_record(){
	window.location = url;
	}
	
	
</script>
<div class="center-content">
	<div>
		<ul class="breadcrumb">
		  <li><a href="<?php echo site_url().'order_management' ?>">Orders</a> <span class="divider">/</span></li>
		 
		  	<?php
		  	if(isset($page_title)){
		  		?>
		  		 <li class="active" id="actual_page"><?php echo $page_title;?> </li>
		  		<?php
		  	}
		  	?>
		 
		</ul>
	</div>
	<div>
	<?php
  	if($this->session->userdata("msg_success")){
  		?>
  		<span class="message success"><?php echo $this->session->userdata("msg_success")  ?></span>
  	<?php
  	$this->session->unset_userdata("msg_success");
	}
  		
  	elseif($this->session->userdata("msg_error")){
  		?>
  		<span class="message error"><?php echo $this->session->userdata("msg_error")  ?></span>
  	<?php
  	$this->session->unset_userdata("msg_error");
  	}
	?>
	</div>
	<?php
	if($parent->parent!=$central_facility){
		$facilities=array();
		$facilities[]=array("facilitycode"=>$this->session->userdata("facility"),"name"=>$this->session->userdata("facility_name"));
	$this->load->view('satellite_orders_sub_menu');	
	}else{
	$this->load->view('orders_sub_menu');
	}
	?>
<table id="orderlist" class="dataTables" border="1">
	<thead>
		<tr>
			<th width="80px">Order No</th>
			<th >Facility Name</th>
			<th>Type of Order</th>
			<th>Reporting Period</th>

			<th>
				<?php
				if (isset($parent)) {
					if($parent -> parent != $central_facility){
					?>
					Days since submission
					<?php
					}else{
					?>
					Days pending <p style="<?php if($days_pending=="Approval"){ ?> color:rgb(255, 167, 11);  <?php } elseif ($days_pending=="Dispatched") { ?> color:green; <?php } elseif ($days_pending=="Resubmission") { ?> color:red; <?php } elseif ($days_pending=="Delivery") { ?> color:rgb(1, 167, 146); <?php } ?>">(<?php echo $days_pending ?>)</p></th>	
					<?php 
					} 
				} ?>
				
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$order_types = array(0=>"Central",1=>"Aggregated",2=>"Satellite");
		foreach($orders as $order){
			$period_begin=$order->Period_Begin;
			$period_end=$order->Period_End;
			$startTimeStamp = $order->Updated;
			$endTimeStamp = strtotime("now");
			
			$timeDiff = abs($endTimeStamp - $startTimeStamp);
			$numberDays = $timeDiff/86400;  // 86400 seconds in one day
			// and you might want to convert to integer
			$numberDays = intval($numberDays);
			?>
			<tr>
				<td><?php echo $order->id;?></td>
				<td><?php echo $order->Facility_Object->name;?></td>
				<td><b><?php echo @$order_types[$order->Code];?></b></td>
				<td><?php echo date('M-Y',strtotime($period_begin));?></td>
				
				<td align="center"><?php echo $numberDays; ?> Day(s)</td>
				<td style="text-align: center">
					 <a  href="<?php echo base_url()."order_management/export/".$order->id;?>" ><i class="icon-download"></i>Export</a>
					 | 
					 <a  href="<?php echo base_url()."order_management/view_order/".$order->id;?>" >View</a>
					<?php if(($quick_link != 1 && $quick_link != 3) ||$quick_link == 2 ){?>
					 | <a href="<?php echo base_url()."order_management/edit_order/".$order->id;?>"> Edit</a> 
					<?php }?>
					<?php if($quick_link == 0){?>
					 | <a class="delete" order="<?php echo $order->id;?>">Delete</a></div></td>
					<?php }?>
					
			</tr>
		<?php }
		?>
	</tbody>
</table>
</div>
<div title="Confirm Delete!" id="confirm_delete" style="width: 300px; height: 150px; margin: 5px auto 5px auto;">
	Are you sure you want to delete this order?
</div>

<div title="Confirm Delete Aggregated Order!" id="confirm_delete_aggregated">
	<span style="color:orange" >This order is linked to an aggregated order. Deleting it would delete the relevant aggregated order.</span>
	
</div>

<!-- Modal to select a satellite facility -->
<div id="select_satellite" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form id="fmFillOrderForm" action="<?php echo base_url().'order_management/new_satellite_order'?>" method="post" style="margin:0 auto;">
		<div class="modal-header">
		    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		    <h3 id="myModalLabel">Satellite facilities</h3>
		</div>
		<div class="modal-body">
			<table  cellpadding="5">
				<tr>
					<td><span id="msg_fill_order" class='message error'></span></td>
				</tr>
				<tr>
					<td colspan='2'>
						<select id="satellite_facility" name="satellite_facility" style="width:250px;height:35px;">
								<option value="0">--Select Facility--</option>
							<?php 
							foreach($facilities as $facility){?>
								<option value="<?php echo $facility['facilitycode'];?>"><?php echo $facility['name'];?></option>
							<?php }?>
						</select> 
					</td>
				</tr>	
			</table>
			
			
		</div>
		<div class="modal-footer">
		    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		    <input type="button" class="btn btn-primary" name="proceed" id="proceed" value="Fill Order Form">
		    <a href="#"><input type="button" class="btn btn-primary" id="upload_excel_btn" value="Upload Excel"></a>
		</div>
		
	</form>
	<div id="excel_upload" style="text-align:center;display: none">
		<form name="frm" method="post" enctype="multipart/form-data" id="frm" action="<?php echo base_url()."fcdrr_management/data_upload"?>">
		<p>
			<input type="file"  name="file" size="30"  required="required" />
			<input name="btn_save" class="btn" type="submit"  value="Save"  style="width:80px; height:30px;"/>
		</p>		
		</form>	
	</div>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#msg_fill_order").css("display","none");
			$("#upload_excel_btn").click(function(){
				$("#excel_upload").toggle();
			});
			//Validate before submitting
			$("#proceed").click(function(){
				if($("#satellite_facility").val()==0){
					$("#msg_fill_order").fadeIn("slow");
					$("#msg_fill_order").html("Please select a facility !");
				}
				//If everything is ok,submit the form
				else{
					$("#msg_fill_order").fadeOut("slow");
					$("#msg_fill_order").html("");
					$("#fmFillOrderForm").submit();
				}
			});
			
		 var oTable = $('#orderlist').dataTable();
         oTable.fnSort( [ [0,'desc'] ] );
		});
	</script>
</div>
<!-- Modal to select a satellite facility end -->
