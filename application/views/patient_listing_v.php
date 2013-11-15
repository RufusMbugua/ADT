
<script type="text/javascript">
	
	$(document).ready( function () {
		var oTable =$('.listing_table').dataTable({
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "patient_management/listing",
	        "bJQueryUI": true,
	        "sPaginationType": "full_numbers",
            "aoColumnDefs": [ { "bSearchable": true, "aTargets": [ 0 ,1] }, { "bSearchable": false, "aTargets": [ "_all" ] } ]});
		 setTimeout(function(){
			$(".message").fadeOut("2000");
		},6000);
         oTable.fnSort([[3,'desc']]);
	});

</script>
<?php
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
		
		<div>
			<?php if($this->session->userdata("msg_save_transaction")){
				?>
				
				<script type="text/javascript">
					setTimeout(function(){
						$(".info").fadeOut("2000");
					},6000)
				</script>
				<?php
				if($this->session->userdata("msg_save_transaction")=="success"){
					?>
					<div class="message success">Your data were successfully saved !</div>
					<?php
				}
				else{
					?>
					<div class="message error">Your data were not saved ! Try again or contact your system administrator.</div>
					<?php
				}
				$this->session->unset_userdata('msg_save_transaction');
			}
			?>
		</div>
		
		<table class="listing_table" id="patient_listing" border="1" style="">
			<thead>
				<tr>
					<th style="width:60px">CCC No</th><th>Patient Name</th><th>Contact</th><th style="width: 100px">Date Enrolled</th><th style="width: 100px">Next Appointment</th><th>Current Regimen</th><th style="width:150px">Status</th><th style="width:20%">Action</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
	
</div>
