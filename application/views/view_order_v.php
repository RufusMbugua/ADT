<script type="text/javascript">
	$(document).ready(function() {
		//$("#regimens_table tbody tr").hide('slow');
		$('#accordion_collapse').click(function() {
			if($(this).val() == "-") {
				$("#regimens_table tbody tr").hide('slow');
				$('#accordion_collapse').val("+");
			} else {
				$("#regimens_table tbody tr").show('slow');
				$('#accordion_collapse').val("-");
			}

		});
	});

</script>

<div class="full-content" style="background:#9CF">
	<div>
		<ul class="breadcrumb">
			<li>
				<a href="<?php echo site_url().'order_management' ?>">Orders</a><span class="divider">/</span>
			</li>
			<li class="active" id="actual_page">
				Details for Order No <?php echo $order_no;?>
			</li>
		</ul>
	</div>
	<div class="facility_info">
		<table class="table table-bordered table-striped" style="margin:auto">
			<tbody>
				<tr>
					<th>Order No</th>
					<td><span class="_green"><?php
						$order_types = array(0=>"Central Order",1=>"Aggregated Order",2=>"Satellite Order"); 
						echo $order_no."(".@$order_types[$order_details->Code].")";?></span></td>
					<th width="160px">Facility code:</th>
					<td><span class="_green"><?php echo $order_details -> Facility_Object -> facilitycode;?></span></td>
				</tr>
				<tr>
					<th width="140px">Facility Name:</th>
					<td><span class="_green"><?php echo $order_details -> Facility_Object -> name;?></span></td>
					<th>Facility Type:</th>
					<td><span class="_green"><?php echo $order_details -> Facility_Object -> Type -> Name;?></span></td>
				</tr>
				<tr>
					<th>District/County:</th>
					<td><span class="_green"><?php echo $order_details -> Facility_Object -> Parent_District -> Name;?>/<?php echo $order_details -> Facility_Object -> County -> county;?></span></td>
					<th>Reporting Period:</th>
					<td colspan="3" ><span class="green"><b><?php echo date("M-Y", strtotime($order_details -> Period_Begin));?></b></span></td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php
	$type = $order_details -> Code;
	$unit = "In Units";
	if ($type == "1") {
		$unit = "In Packs";
	}
	$header_text = '<thead>
<tr>
<!-- label row -->
<th class="col_drug" rowspan="3">Drug Name</th>
<th class="number" rowspan="3">Pack Size</th> <!-- pack size -->

<th class="number">Beginning Balance</th>
<th class="number">Qty Received</th>

<!-- dispensed_units -->
<th class="col_dispensed_units">Total Qty Dispensed</th>
<th class="col_losses_units">Losses (Damages, Expiries, Missing)</th>
<!-- dispensed_packs -->

<th class="col_adjustments">Adjustments (Borrowed from or Issued out to Other Facilities)</th>
<th class="number">End of Month Physical Count</th>
<th class="number" colspan="2">Quantity to Expire in less than 6 months</th>

<!-- aggr_consumed/on_hand -->
<th class="number">Qty required for RESUPPLY</th>
</tr>
<tr>
<!-- unit row -->
<th>' . $unit . '</th> <!-- balance -->
<th>' . $unit . '</th> <!-- received -->

<!-- dispensed_units -->
<th class="col_dispensed_units">' . $unit . '</th>
<th class="col_dispensed_units">' . $unit . '</th>
<!-- dispensed_packs -->

<th>' . $unit . '</th> <!-- adjustments -->
<th>' . $unit . '</th> <!-- count -->
<th>'.$unit.'</th> <!-- expire -->
<th>mm-yy</th> <!-- expire -->

<!-- aggr_consumed/on_hand -->

<th>' . $unit . '</th> <!-- resupply -->
</tr>
<tr>
<!-- letter row -->
<th>A</th> <!-- balance -->
<th>B</th> <!-- received -->
<th>C</th> <!-- dispensed_units/packs -->
<th>D</th> <!-- losses -->
<th>E</th> <!-- adjustments -->
<th>F</th> <!-- count -->
<th>G</th> <!-- expire -->
<th>H</th> <!-- expire -->
<th>I</th> <!-- count -->
<!-- aggr_consumed/on_hand -->

</tr>
</thead>';
	?>
	<div id="commodity-table">
		<table id="commoditylist" class="table table-bordered table_order_details dataTables">
			<?php echo $header_text;?>
			<tbody>
				<?php
$counter = 0;
foreach($commodities as $commodity){
$counter++;
if($counter == 10){
//echo $header_text;
$counter = 0;
}
				?>
				<tr class="ordered_drugs" drug_id="<?php echo $commodity -> Drugcode_Object -> id;?>">
					<td class="col_drug"><?php echo $commodity -> Drugcode_Object -> Drug;
					if ($commodity -> Drugcode_Object -> Drug == "") {echo $commodity -> Drug_Id;
					}
					?></td>
					<td class="number"><?php echo $commodity -> Drugcode_Object -> Pack_Size;?></td>
					<td class="number calc_count"><?php echo number_format((double)$commodity -> Balance);?></td>
					<td class="number calc_count"><?php echo number_format((double)$commodity -> Received);?></td>
					<!-- dispensed_units-->
					<td class="number col_dispensed_units calc_dispensed_packs  calc_resupply calc_count"><?php echo number_format((double)$commodity -> Dispensed_Units);?></td>
					<td class="number calc_count"><?php echo number_format((double)$commodity -> Losses);?></td>
					<td class="number calc_count"><?php echo number_format((double)$commodity -> Adjustments);?></td>
					<td class="number calc_resupply col_count"><?php echo number_format((double)$commodity -> Count);?></td>
					<td class="number calc_expire_qty col_exqty"><?php echo number_format((double)$commodity -> Aggr_Consumed);?></td>
					<td class="number calc_expire_period col_experiod"><?php echo $commodity -> Aggr_On_Hand;?></td>
					<!-- aggregate -->
					<td class="number col_resupply"><?php echo number_format((double)$commodity -> Resupply);?></td>
				</tr>
				<?php }?>
			</tbody>
		</table>
		<br />
		<hr size="1">
		<?php foreach($comments as $comment){
		?>
		<div>
			<span class="label" style="vertical-align: bottom">Comments </span>
			<textarea style="width:98%" rows="3" name="comments" readonly="readonly"><?php echo $comment->Comment ?></textarea>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Last Update</th><th>Made By</th><th>Access Level</th>
					</tr>
				</thead>
				<tbody>
					<tr>
				<td><span class="green"><?php echo date('l d-M-Y h:i:s a', $comment -> Timestamp);?></span></td><td><span class="green"><?php if($comment -> User_Object -> Name){echo $comment -> User_Object -> Name;}else{echo $comment->User;}?></span></td><td><span class="green"><?php  if($comment -> User_Object -> Access -> Level_Name){echo $comment -> User_Object -> Access -> Level_Name;}else{ echo "Facility Administrator";}?></span></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php }?>
	</div>
	<table class=" table table-bordered regimen-table big-table research" id="regimens_table">
		<thead>
			<tr>
				<th class="col_drug"> Regimen </th>
				<th>
				<input type="button" id="accordion_collapse" value="-"/>
				</span> Patients<span></th>
			</tr>
		</thead>
		<tbody>
			<?php
$counter = 1;
$overall_patients=0;
foreach($regimens as $regimen){
			?>
			<tr>
				<td regimen_id="<?php echo $regimen -> id;?>" class="regimen_desc col_drug"><?php echo "<b>" . $regimen -> Regimen_Id. "</b>";?></td>
				<td regimen_id="<?php echo $regimen -> id;?>" class="regimen_numbers"><?php $overall_patients+=(double)$regimen -> Total;echo number_format((double)$regimen -> Total);?></td>
			</tr>
			<?php
			}
			?>
		</tbody>
		<tfoot>
			<tr><td>Patient Total</td><td><?php echo  number_format($overall_patients); ?></td></tr>
		</tfoot>
	</table>
</div>
