
		<style>
			#patient_listing {
				margin: 0 auto;
				font-size: 14px;
				letter-spacing: 0.5px;
				width: 100%;
				border-collapse:collapse;
			}
		
			#patient_listing td  {
				text-align: justify;
			}
			
		    .report_title {
				color:rgb(34, 86, 253);
				letter-spacing: 1px;
			}
			.tfoot{
				background-color:#DDD;
				font-weight: bold;
				font-size:16px;
			}
			th{
				background-color: #CFF;
				text-transform: uppercase;
				font-weight: bold;
				font-size:16px;
			}
			
		</style>
	
		<h4 style="text-align: center">Cumulative Number of Patients by Current Status as of <span  id="date_of_appointment"><?php echo $from;?></span></h4>
		<hr size="1" style="width:80%">
          <?php echo $dyn_table;?>
		
		<!-- Pop up Window -->
		<div class="result"></div>
		<!-- Pop up Window end-->
	