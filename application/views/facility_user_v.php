<?php
	foreach($facilities as $facility){
		
	}

?>
<style type="text/css">
	.actions_panel {
		width: 200px;
		margin-top: 5px;
	}
	.hovered td {
		background-color: #E5E5E5 !important;
	}
	a{
		text-decoration: none;
	}
	.enable_user{
		color:green;
		font-weight:bold;
	}
	.disable_user{
		color:red;
		font-weight:bold;
	}
	.edit_user{
		color:blue;
		font-weight:bold;
	}
	.passmessage {

		display: none;
		background: #00CC33;
		color: black;
		text-align: center;
		height: 20px;
		padding:5px;
		font: bold 1px;
		border-radius: 8px;
		width: 30%;
		margin-left: 30%;
		margin-right: 10%;
		font-size: 16px;
		font-weight: bold;
	}
	.errormessage {

		display: none;
		background: #FF0000;
		color: black;
		text-align: center;
		height: 20px;
		padding:5px;
		font: bold 1px;
		border-radius: 8px;
		width: 30%;
		margin-left: 30%;
		margin-right: 10%;
		font-size: 16px;
		font-weight: bold;
	}
	#facility_form(
	    width: 300px;
		height:150px;
		margin-top: 5px;
		border:1px solid #DDD;
		padding:20px;
		margin-left:500px;
		margin-right:200px;
	)
	.submit-button .Save{
		display:none;
	}
	


</style>

<script type="text/javascript">
$(document).ready(function() {
$("#facility_type").attr("value","<?php echo @$facility['facilitytype'];?>");
$("#district").attr("value","<?php echo @$facility['district'];?>");
$("#central_site").attr("value","<?php echo @$facility['parent'];?>");
		
//count to check which message to display
 var count='<?php echo @$this -> session -> userdata['message_counter']?>';
 var message='<?php echo @$this -> session -> userdata['message']?>';	
	
	if(count == 1) {
	$(".passmessage").slideDown('slow', function() {

	});
	$(".passmessage").append(message);

	var fade_out = function() {
	$(".passmessage").fadeOut().empty();
	}
	setTimeout(fade_out, 5000);
     <?php 
     $this -> session -> set_userdata('message_counter', "0");
     $this -> session -> set_userdata('message', " ");
     ?>

	}
	if(count == 2) {
	$(".errormessage").slideDown('slow', function() {

	});
	$(".errormessage").append(message);

	var fade_out = function() {
	$(".errormessage").fadeOut().empty();
	}
	setTimeout(fade_out, 5000);
     <?php 
     $this -> session -> set_userdata('message_counter', "0");
     $this -> session -> set_userdata('message', " ");
     ?>

	}
		
	});

	

</script>
	<div class="container-fluid">
	  <div class="row-fluid row">
		 <!-- Side bar menus -->
	    <?php echo $this->load->view('settings_side_bar_menus_v.php'); ?>
	    <!-- SIde bar menus end -->

	    <div class="span9 span-fixed-sidebar" >

	    	<div id="action_panel_parent" style="display:none">
				<div class="actions_panel" style="visibility:hidden" >
					<?php
			//Loop through all the actions passed on to this file
			foreach($actions as $action){
					?>
					<a class="link" link="<?php echo $this->router->class."/".$action[1]."/"?>"><?php echo $action[0]
					?></a>
					<?php }?>
				</div>
			</div>

	      	<div class="hero-unit" style="padding:10px;background: rgb(184, 255, 184);">
				<div class="passmessage"></div>
			    <div class="errormessage"></div>
				<?php echo validation_errors('<p class="error">', '</p>');?>
				


	    		<div id="facility_form" title="Facility Information">
	    			
		      		<?php
						$attributes = array('class' => 'input_form');
						echo form_open('facility_management/update', $attributes);
						echo validation_errors('<p class="error">', '</p>');
					?>
						<fieldset>
	    					<h3>Facility Details</h3>
							<table class="facility_basic_info" style="width:70%;">
								<tr><td><label for="facility_code"><strong class="label">Organization Code/MFL No</strong></label></td>
									<td>
										<input type="hidden" name="facility_id" id="facility_id" class="input" value="<?php echo @$facility['id'];?>" >
										<input type="hidden" name="facility_cod" id="facility_cod" class="input" value="<?php echo @$facility['facilitycode'];?>" >
										<span name="facility_code" id="facility_code"  class="input-large uneditable-input" ><?php echo @$facility['facilitycode'];?></span>
										
									</td>
								</tr>
								<tr><td><strong class="label">Name of Organization / System</strong></td>
									<td><input type="text" name="facility_name" id="facility_name" class="input-xlarge" style="color:green" value="<?php echo @$facility['name'];?>" >
									</td>
								</tr>
								<tr><td><strong class="label">Adult Age</strong></td>
									<td><input type="text" name="adult_age" id="adult_age" class="input-small" value="<?php echo @$facility['adult_age'];?>">
									</td>
								</tr>
								<tr><td><strong class="label">Maximum Patients Per Day</strong></td>
									<td><input type="text" name="weekday_max" id="weekday_max" class="input-small" value="<?php echo @$facility['weekday_max'];?>"></td>
								</tr>
								<tr><td><strong class="label">Maximum Patients Per Week</strong></td>
									<td><input type="text" name="weekend_max" id="weekend_max" class="input-small" value="<?php echo @$facility['weekend_max'];?>"></td>
								</tr>
								<tr><td><strong class="label">Facility Type</strong></td>
									<td><select class="input-xlarge" id="facility_type" name="facility_type">
											<?php foreach($facility_types as $facility_type){?>
											<option value="<?php echo $facility_type['id'];?>"><?php echo @$facility_type['Name'];?></option>
											<?php }?>
										</select>
									</td>
								</tr>
								<tr><td><strong class="label">District</strong></td>
									<td><select class="input-xlarge" id="district" name="district">
											<?php foreach($districts as $district){?>
											<option value="<?php echo $district['id'];?>"><?php echo $district['name'];?></option>
											<?php }?>
										</select>
									</td>
								</tr>

							   <tr><td><strong class="label">County</strong></td>
									<td><select class="input-xlarge" id="county" name="county">
											<?php foreach($counties as $county){?>
											<option value="<?php echo $county['id'];?>"><?php echo $county['county'];?></option>
											<?php }?>
										</select>
									</td>
								</tr>
								
								 <tr><td><strong class="label">Central Site</strong></td>
									<td><select class="input-xlarge" id="central_site" name="central_site">
											<?php foreach($sites as $site){?>
											<option value="<?php echo $site['facilitycode'];?>"><?php echo $site['name'];?></option>
											<?php }?>
										</select>
									</td>
								</tr>
							<?php $supported_by=$facility['supported_by']; ?>

							</table>
							<hr size="2" style="border-top: 1px solid #000;">
							<div class="span3">
								<fieldset>
									<legend style="color:red">Client Supported By</legend>
									
									  <?php foreach ($supporter as $support) {
									  	?>
									  	<label class="radio">
										  	<input type="radio" name="supported_by" value="<?php echo $support->id?>" id="<?php echo $support->id?>" <?php if($supported_by==$support->id){?> checked="checked"<?php } ?>>
										    <?php echo $support->Name ?> Sponsorship
										  </label> 
									  	<?php
									  }	
									  ?>

								</fieldset>
							</div>

							<div class="span4">
								<fieldset>
									<legend style="color:red">Services offered at the facility</legend>
									<label class="checkbox">
									  <input type="checkbox" id="art_service" name="art_service" <?php if(@$facility['service_art']==1){?> checked <?php } ?>>
									 ART
									</label>
									<label class="checkbox">
									  <input type="checkbox" id="pmtct_service" name="pmtct_service" <?php if(@$facility['service_pmtct']==1){?> checked <?php } ?>>
									 PMTCT
									</label>
									<label class="checkbox">
									  <input type="checkbox" id="pep_service" name="pep_service" <?php if(@$facility['service_pep']==1){?> checked <?php } ?>>
									 PEP
									</label>
								</fieldset>
							</div>
							<div class="span2">
								<fieldset>
									<legend style="color:red">Client Supplied By</legend>
									<label class="radio">
									  	<input type="radio" name="supplied_by" value="1" id="supply_1" <?php if(@$facility['supplied_by']==1){?> checked="checked"<?php } ?>>
									     KEMSA
									</label>
									<label class="radio">
									  	<input type="radio" name="supplied_by" value="2" id="supply_2" <?php if(@$facility['supplied_by']==2){?> checked="checked"<?php } ?>>
									     Kenya Pharma
									</label> 
								</fieldset>
							</div>

							<div class="span3" style="padding-top: 5em;padding-left:8em">
								<input type="submit" class="btn btn-primary" value="Save" style="padding-left: 2em; padding-right: 2em;">
							</div>

							
						</fieldset>
					</form>
				</div>
	    		

			    
			</div>
			<div id="loading" style="text-align:center;display:none"><img width="120px" src="<?php echo site_url().'/Images/loading.gif' ?>"></div> 
			    
	    </div><!--/span-->
	  </div><!--/row-->
	</div><!--/.fluid-container-->
	
</div>