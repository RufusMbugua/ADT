<div id="wrapperd">
			
	<div id="patient_enrolled_content" class="full-content">
		<?php $this->load->view("reports/reports_top_menus_v") ?>
		<h4 style="text-align: center">Active Patients Started By Regimen as of <span  id="date_of_appointment"><?php echo $from;?></span></h4>
		<hr size="1" style="width:80%">
		<?php echo  $dyn_table;?>
	</div>
</div>	
		