<?php 
if($table){
?>
<a href="#dialog_<?php echo $table;?>" role="button" id="<?php echo $table;?>" class="btn add" data-toggle="modal"><i class="icon-plus icon-black"></i>New<?php echo "  " . $label;?></a>
<?php }echo $dyn_table;?>
<!--Dialog for Counties-->
<div id="dialog_counties" title="Add County" class="modal hide fade cyan" tabindex="-1" role="dialog" aria-labelledby="AddCounty" aria-hidden="true">
	   <?php
		$attributes = array('class' => 'input_form');
		echo form_open('admin_management/save/'.$table, $attributes);
		echo validation_errors('<p class="error">', '</p>');
		?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
			×
		</button>
		<h3 id="NewDrug">Add County</h3>
	</div>
	<div class="modal-body">
		<div class="max-row">
				<label>County Name</label>
				<input type="text" class="input-large" name="name" required="required"/>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">
			Cancel
		</button>
		<input type="submit" value="Save" class="btn btn-primary " />
	</div>
	<?php echo form_close(); ?>
</div>
<!--Dialog for Satellites-->
<div id="dialog_facilities" title="Add Satellite" class="modal hide fade cyan" tabindex="-1" role="dialog" aria-labelledby="AddCounty" aria-hidden="true">
    <?php
		$attributes = array('class' => 'input_form');
		echo form_open('admin_management/save/'.$table, $attributes);
		echo validation_errors('<p class="error">', '</p>');
	?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
			×
		</button>
		<h3 id="NewDrug">Add Satellite</h3>
	</div>
	<div class="modal-body">
		<div class="max-row">
				<label>Facility Name</label>
				<select name="facility" id="satellite" class="input-xlarge">
				</select>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">
			Cancel
		</button>
		<input type="submit" value="Save" class="btn btn-primary " />
	</div>
	<?php echo form_close(); ?>
</div>
<!--Dialog for Districts-->
<div id="dialog_district" title="Add District" class="modal hide fade cyan" tabindex="-1" role="dialog" aria-labelledby="AddDistrict" aria-hidden="true">
	<?php
		$attributes = array('class' => 'input_form');
		echo form_open('admin_management/save/'.$table, $attributes);
		echo validation_errors('<p class="error">', '</p>');
	?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
			×
		</button>
		<h3 id="NewDrug">Add District</h3>
	</div>
	<div class="modal-body">
		<div class="max-row">
				<label>District Name</label>
				<input type="text" class="input-large" name="name" required="required"/>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">
			Cancel
		</button>
		<input type="submit" value="Save" class="btn btn-primary " />
	</div>
	<?php echo form_close(); ?>
</div>
<!--Dialog for Menus-->
<div id="dialog_menu" title="Add Menu" class="modal hide fade cyan" tabindex="-1" role="dialog" aria-labelledby="AddDistrict" aria-hidden="true">
	<?php
		$attributes = array('class' => 'input_form');
		echo form_open('admin_management/save/'.$table, $attributes);
		echo validation_errors('<p class="error">', '</p>');
	?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
			×
		</button>
		<h3 id="NewDrug">Add Menu</h3>
	</div>
	<div class="modal-body">
		<div class="max-row">
				<label>Menu Name</label>
				<input type="text" class="input-large" name="name" required="required"/>
		</div>
		<div class="max-row">
				<label>Menu URL</label>
				<input type="text" class="input-large" name="url" required="required"/>
		</div>
		<div class="max-row">
				<label>Menu Description</label>
				<textarea cols="40" rows="5" name="description"></textarea>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">
			Cancel
		</button>
		<input type="submit" value="Save" class="btn btn-primary " />
	</div>
	<?php echo form_close(); ?>
</div>

<!--Dialog for Users-->

<div id="dialog_users" title="New User" class="modal hide fade cyan" tabindex="-1" role="dialog" aria-labelledby="label" aria-hidden="true">
		
			<?php
			$attributes = array('class' => 'input_form','id'=>'fm_user');
			echo form_open('admin_management/save/'.$table, $attributes);
			echo validation_errors('<p class="error">', '</p>');
			?>
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="NewDrug">User details</h3>
			</div>
			<div class="modal-body">
			<div class="msg error" id="msg_error">Fields with <i class="icon-star icon-black"></i> are compulsory</div>
			<br>
			<table style="margin:0 auto" class="table-striped" width="100%">
				<tr><td><strong class="label">Usertype</strong> </td>
					<td>
						<span class="add-on"><i class=" icon-chevron-down icon-black"></i></span>
						<select class="input-xlarge" id="access_level" name="access_level">
							<option selected="selected" value="3">Facility Administrator</option>
						</select>
					</td>
					<td></td>
				</tr>
				
				<tr><td><strong class="label">Full Name</strong></td>
					<td>
						<div >
							<span class="add-on"><i class="icon-user icon-black"></i></span>
							<input type="text" class="input-xlarge" id="fullname" name="fullname" required="" >
							<span class="add-on"><i class="icon-star icon-black"></i></span>
						</div>
					</td><td class="_red"></td></tr>
				<tr><td><strong class="label">Username</strong></td>
					<td><div>
							<span class="add-on"><i class="icon-user icon-black"></i></span>
							<input type="text" name="username" id="username" class="input-xlarge" required=""> 
							<span class="add-on"><i class="icon-star icon-black"></i></span>
						</div>
					</td><td class="_red"></td></tr>
				<tr ><td><strong class="label">Phone number</strong></td>
					<td>
						<div >
							<span class="add-on"><i class="icon-calendar icon-black"></i> </span>
							<input type="text" name="phone" id="phone" class="input-xlarge" placeholder="e.g. +254721111111">
							<span class="add-on"><i class="icon-star icon-black"></i></span>
						</div>
					</td><td></td></tr>
				<tr><td><strong class="label">Email address</strong></td>
					<td>
						<div >
							<span class="add-on"><i class=" icon-envelope icon-black"></i></span>
							<input type="email" name="email" id="email" class="input-xlarge" placeholder="e.g. youremail@example.com">
						</div></td><td class="_red" id="invalid_email">
					</td></tr>
				<tr><td><strong class="label">Facility</strong></td>
					<td>
						<span class="add-on"><i class=" icon-chevron-down icon-black"></i></span>
						<select name="facility" id="facility" class="input-xlarge">
							
						</select>
					</td>
					<td></td>
				</tr>
			</table>
			</div>
			<div class="modal-footer">
			   <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
			   <input type="submit" value="Save" class="btn btn-primary " />
			</div>
			</form>
			<?php echo form_close(); ?>
		</div>

<!--Dialog For User Rights-->
<!--Dialog for Satellites-->
<div id="dialog_user_right" title="Add User Right" class="modal hide fade cyan" tabindex="-1" role="dialog" aria-labelledby="AddCounty" aria-hidden="true">
	<?php
		$attributes = array('class' => 'input_form','id'=>'fm_user');
		echo form_open('admin_management/save/'.$table, $attributes);
		echo validation_errors('<p class="error">', '</p>');
	 ?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
			×
		</button>
		<h3 id="NewDrug">Add User Right</h3>
	</div>
	<div class="modal-body">
		<div class="max-row">
				<label>Access Level</label>
				<select class="input-large" name="access_level" id="access_levels">

				</select>
		</div>
		<div class="max-row">
				<label>Menu List</label>
				<select class="input-large" name="menus" id="menus">

				</select>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">
			Cancel
		</button>
		<input type="submit" value="Save" class="btn btn-primary " />
	</div>
	<?php echo form_close(); ?>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		var base_url="<?php echo base_url(); ?>";
		//Adding Facilities
		$("#facilities").click(function(){
		    var link=base_url+"facility_management/getFacilityList";
				$.ajax({
				    url: link,
				    type: 'POST',
				    dataType: "json",
				    success: function(data) {	
				    	$("#satellite").append($("<option></option>").attr("value",'').text('--Select One--'));
				    	$.each(data, function(i, jsondata){
				    		$("#satellite").append($("<option></option>").attr("value",jsondata.facilitycode).text(jsondata.name));
				    	});
				    }
				});
		});
		
		//Adding Users
		$("#users").click(function(){
		    var link=base_url+"facility_management/getCurrent";
				$.ajax({
				    url: link,
				    type: 'POST',
				    dataType: "json",
				    success: function(data) {	
				    	$.each(data, function(i, jsondata){
				    		$("#facility").append($("<option selected='selected'></option>").attr("value",jsondata.facilitycode).text(jsondata.name));
				    	});
				    }
				});
		});
		
		//Adding User Rights
	    $("#user_right").click(function(){
		    var link1=base_url+"settings_management/getAccessLevels";
				$.ajax({
				    url: link1,
				    type: 'POST',
				    dataType: "json",
				    success: function(data) {	
				    	$.each(data, function(i, jsondata){
				    		$("#access_levels").append($("<option></option>").attr("value",jsondata.Id).text(jsondata.Access));
				    	});
				    }
				});
				
			 var link2=base_url+"settings_management/getMenus";
				$.ajax({
				    url: link2,
				    type: 'POST',
				    dataType: "json",
				    success: function(data) {	
				    	$("#menus").append($("<option></option>").attr("value",'').text('--Select One--'));
				    	$.each(data, function(i, jsondata){
				    		$("#menus").append($("<option></option>").attr("value",jsondata.id).text(jsondata.Menu_Text));
				    	});
				    }
				});	
		});
	});
</script>

