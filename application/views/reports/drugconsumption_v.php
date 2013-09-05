<script type="text/javascript">
	
	$(document).ready( function () {
		
		var stock_type=<?php echo $stock_type; ?>;
		var base_url='<?php echo $base_url ?>';
		var _url=<?php echo "'".$base_url."report_management/drug_consumption/".$stock_type."'"; ?>;
		$('#drug_table').dataTable( {
			"oTableTools": {
				"sSwfPath": base_url+"assets/scripts/datatable/copy_csv_xls_pdf.swf",
				"aButtons": [ "copy", "print","xls","pdf" ]
			},
			"sDom" : '<"H"Tfr>t<"F"ip>',
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": _url,
	        "bJQueryUI": true,
	        "aoColumnDefs": [
          	{'bSortable': false, 'aTargets': [ 1,2,3,4,5,6,7,8,9,10,11,12,13] }
    		],
	        "sPaginationType": "full_numbers"
		});
		
	});

</script>
<div id="wrapperd">
	<div id="drug_consumption" class="full-content">
		<?php $this->load->view("reports/reports_top_menus_v") ?>
		<h4 style="text-align: center;">Listing of Drug Consumption Report for <span class="_date" id="_year"><?php echo @$year ?></span> </h4>
		<hr size="1" style="width:80%">
		
		<table id="drug_table" class="table table-bordered table-striped listing_table " style="font-size:0.8em">
			<thead>
				<tr>
					<th style="min-width: 300px">Drug</th><th>Unit</th><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th><th>Jul</th><th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>	