
<script type="text/javascript">
	
	$(document).ready( function () {
		$('#store_table').dataTable( {
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "inventory_management/main_store_stock",
	        "bJQueryUI": true,
	        "sPaginationType": "full_numbers",
	       "aoColumnDefs": [
      		{ "bSearchable": false, "aTargets": [ 2,3,4 ] }
    		]  
		} );
		$('#pharmacy_table').dataTable( {
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "inventory_management/pharmacy_store_stock",
	        "bJQueryUI": true,
	        "sPaginationType": "full_numbers"
		} );
		$("#store_btn").click(function(){
			$("#pharmacy_btn").removeClass();
			$(this).addClass("active");
			$("#pharmacy_table").hide();
			$("#pharmacy_table_wrapper").hide();
			$("#store_table").show();
			$("#store_table_wrapper").show();
			
		});
		$("#pharmacy_btn").click(function(){
			$("#store_btn").removeClass();
			$(this).addClass("active");
			$("#store_table").hide();
			$("#store_table_wrapper").hide();
			$("#pharmacy_table").show();
			$("#pharmacy_table_wrapper").show();
			
		});
	} );

</script>
<?php
if($this->session->userdata("inventory_go_back")){
	
	if($this->session->userdata("inventory_go_back")=="store_table"){
		?>
		<script type="text/javascript">
			$(document).ready(function(){
				$("#pharmacy_btn").removeClass();
				$(this).addClass("active");
				$("#pharmacy_table").hide();
				$("#pharmacy_table_wrapper").hide();
				$("#store_table").show();
				$("#store_table_wrapper").show();
				
			});
		</script>
		
		<?php
	}
	else if($this->session->userdata("inventory_go_back")=="pharmacy_table"){
		?>
		<script type="text/javascript">
				$(document).ready(function(){
				$("#store_btn").removeClass();
				$("#pharmacy_btn").addClass("active");
				$("#store_table").hide();
				$("#store_table_wrapper").css("display","none");
				$("#pharmacy_table").show();
				$("#pharmacy_table_wrapper").show();
				
			});
		</script>
		
		<?php
	}
	
}
else{
	?>
	<script type="text/javascript">
	$(document).ready( function () {
		$("#pharmacy_btn").removeClass();
		$(this).addClass("active");
		$("#pharmacy_table").hide();
		$("#pharmacy_table_wrapper").hide();
		$("#store_table").show();
		$("#store_table_wrapper").show();
		
	});
	</script>
	<?php
	
}
?>

<?php
$this->session->unset_userdata("inventory_go_back");
$access_level = $this -> session -> userdata('user_indicator');
$user_is_administrator = false;
$user_is_nascop = false;
$user_is_pharmacist = false;
$user_is_facilityadmin = false;

if ($access_level == "system_administrator") {
	$user_is_administrator = true;
}
if ($access_level == "pharmacist") {
	$user_is_pharmacist = true;

}
if ($access_level == "nascop_staff") {
	$user_is_nascop = true;
}
if ($access_level == "facility_administrator") {
	$user_is_facilityadmin = true;
}
?>

<?php 
//COunt number of patients
?>
<div class="main-content">
	
	<div class="center-content">
		
		<div>
				<?php if($this->session->userdata("msg_save_transaction")){
					?>
					
					<script type="text/javascript">
						setTimeout(function(){
							$(".message").fadeOut("2000");
						},6000)
					</script>
					<?php
					if($this->session->userdata("msg_save_transaction")=="success"){
						?>
						<p class=""><span class="message success">Your data were successfully saved !</span></p>
						<?php
					}
					else{
						?>
						<p class=""><span class="message error">Your data were not saved ! Try again or contact your system administrator.</span></p>
						<?php
					}
					$this->session->unset_userdata('msg_save_transaction');
				}
				?>
		</div>
		
		<ul class="nav nav-tabs">  
			<li id="store_btn" class="active"><a  href="#">Store Inventory</a> </li>   
			<li id="pharmacy_btn"><a  href="#">Pharmacy Inventory</a></li>   
		</ul> 
		
		<?php
		 // echo $store_table;
		 // echo $pharmacy_table;
		?>
		<table id="store_table" class="listing_table" border="1" >
			<thead>
				<tr>
					<th style="width:30%">Commodity</th><th>Generic Name</th><th>QTY/SOH</th><th>Unit</th><th>Pack Size</th><th>Supplier</th><th>Dose</th><th style="width:10%">Action</th>
				</tr>
			</thead>
			<tbody>
				
			</tbody>
		</table>
		
		<table id="pharmacy_table" class="listing_table" border="1" >
			<thead>
				<tr>
					<th style="width:30%">Commodity</th><th>Generic Name</th><th>QTY/SOH</th><th>Unit</th><th>Pack Size</th><th>Supplier</th><th>Dose</th><th style="width:10%">Action</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
	
</div>
