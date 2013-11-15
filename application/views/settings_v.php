<style>
    .setting_table{
    	font-size:12px;
    	letter-spacing:0px;
    }
	.dataTables_length{
		width:50%;
	}
	.dataTables_info{
		width:44%;
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
	.edit_user{
		color:blue;
		font-weight:bold;
	}
	#edit_form, #client_form,#entry_form{
		background-color:#CCFFFF;
	}
	.hero-unit{
		margin-bottom:0px;
	}
</style>
<script>
	
	$(document).ready(function() {
		setTimeout(function(){
			$(".message").fadeOut("2000");
		},6000);
		//What happens when editing/updating/disabling/enabling
		<?php
		//Check if the session for a page load already exists
		if($this->session->userdata('link_id') and $this->session->userdata('linkSub')){
		?>
		$(".settings_title").fadeIn("1000");
		$(".settings").css("display","none");
		$("#loadingDiv").css("display","block");
		link_id='#'+'<?php echo $this->session->userdata('link_id')?>';
		linkSub='<?php echo $this->session->userdata('linkSub')?>';
		linkIdUrl=link_id.substr(link_id.indexOf('#')+1,(link_id.indexOf('_li')-1));
		linkTitle='<?php echo @$this->session->userdata('linkTitle')?>';
		//Change the page title value
		$("#actual_page").html(linkTitle);
		$(".settings").load('<?php echo base_url();?>'+linkSub+'/'+linkIdUrl,function(){
			$("input[type='text']").attr("required","required");
			$("#loadingDiv").css("display","none");
			$(".settings").css("display","block");
				if( linkSub=="brandname_management"){
						$('#brand_name_table').dataTable({
		    			"sScrollY": "200px",
		    			"bLengthChange": false,
		                "bPaginate": false,
		                "bJQueryUI": true,
		            	"bDestroy":true})
		    	        .rowGrouping({
		                    bExpandableGrouping: true,
		                    bExpandSingleGroup: false,
		                    iExpandGroupOffset: -1,
		                    asExpandedGroups: [""],
		                    
		                });
			        GridRowCount();
					function GridRowCount() {
				        $('span.rowCount-grid').remove();
				        $('input.expandedOrCollapsedGroup').remove();
				
				        $('.dataTables_wrapper').find('[id|=group-id]').each(function () {
				            var rowCount = $(this).nextUntil('[id|=group-id]').length;
				            $(this).find('td').append($('<span />', { 'class': 'rowCount-grid' }).append($('<b />', { 'text': '('+rowCount+')' })));
				        });
				
				        $('.dataTables_wrapper').find('.dataTables_filter').append($('<input />', { 'type': 'button', 'class': 'expandedOrCollapsedGroup collapsed', 'value': 'Expanded All Group' }));
				
				        $('.expandedOrCollapsedGroup').live('click', function () {
				            if ($(this).hasClass('collapsed')) {
				                $(this).addClass('expanded').removeClass('collapsed').val('Collapse All Group').parents('.dataTables_wrapper').find('.collapsed-group').trigger('click');
				            }
				            else {
				                $(this).addClass('collapsed').removeClass('expanded').val('Expanded All Group').parents('.dataTables_wrapper').find('.expanded-group').trigger('click');
				            }
			        	});
			        };
				}
				else{
					oTable = $('.setting_table').dataTable({
						"sScrollY" : "250px",
						"bJQueryUI" : true,
						"sPaginationType" : "full_numbers",
						"bDestroy":true
					});
				}
				
		});
		<?php
		$this->session->unset_userdata('link_id');
		$this->session->unset_userdata('linkSub');
		}
		?>
		//What happens when editing/updating/disabling/enabling -- end
		
		$('.dropdown-toggle').click(function() {
			$('.setting_menus').dropdown();
		});
		
		//so which link was clicked?
			  $('.setting_menus li').on('click',function(){
			  	//Synchronizes stock balance
			  	if($(this).find('a').attr('class')=="stock_balance_synch"){
			  		//Starts synchronization
			  		$('#drug_stock_balance_synch').modal('show');
			  		drug_cons_synch();
			  		
			  	}
			  	//Synchronizes drug stock movment remaining balance
			  	else if($(this).find('a').attr('class')=="stock_movement_balance_synch"){
					$('#confirmbox').modal('show');
						//No clicked
						$('#confirmFalse').click(function(){
							
						});
						//Yes Clicked
						$('#confirmTrue').click(function(){
							synch_drug_movement_balance("1");
							$("#drug_stock__movement_balance_synch").modal("show");
							
						});
			  		
			  	}
			  	else{
			  		$(".settings_title").fadeIn("1000");
				  	$(".settings").css("display","none");
				  	$("#loadingDiv").css("display","block");
				  	var linkDomain=" ";
					link_id='#'+$(this).find('a').attr('id');
					linkSub=$(this).find('a').attr('class');
					linkIdUrl=link_id.substr(link_id.indexOf('#')+1,(link_id.indexOf('_li')-1));
					//Get actual page title
					linkTitle=$(this).find('a').attr('title');
					//Change the page title value
					$("#actual_page").html(linkTitle);
					$(".settings").load('<?php echo base_url();?>'+linkSub+'/'+linkIdUrl,function(){
						$("input[type='text']").attr("required","required");
						$("#loadingDiv").css("display","none");
						$(".settings").css("display","block");
								oTable = $('.setting_table').dataTable({
									"sScrollY" : "250px",
									"bJQueryUI" : true,
									"sPaginationType" : "full_numbers",
									"bDestroy":true
								});
					
					});
			  	}

				
				})/*end of which link was clicked*/
	
		
		
		
	
	}); 
	
	
	
	
	
</script>

<div class="center-content">
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
	<div class="navbar">
		<div class="navbar-inner">
			<ul class="nav">
				<li class="dropdown">
					<a class="dropdown-toggle" role="button" data-toggle="dropdown" id="dLabel" href="#">Regimens<b class="caret"></b></a>
					<ul class="dropdown-menu setting_menus" role="menu" aria-labelledby="dLabel">
						<li>
							<a href="#" class="regimen_management" title="Regimen Management" id="index">View Regimens</a>
						</li>
						<li>
							<a href="#" class="regimen_drug_management" title="Regimen Drug Management" id="index">Regimen Drugs</a>
						</li>
						<li>
							<a href="#" class="regimenchange_management" title="Regimen Change Reason Management" id="index">Regimen change reasons</a>
						</li>
					</ul>
				</li>
				<li class="divider-vertical"></li>
				<li class="dropdown">
					<a class="dropdown-toggle" role="button" data-toggle="dropdown" id="dLabel" href="#">Drugs<b class="caret"></b></a>
					<ul class="dropdown-menu setting_menus" role="menu" aria-labelledby="dLabel">
						<li>
							<a href="#" class="drugcode_management" title="Drug Code Management">Drug Codes</a>
						</li>
						<li>
							<a href="#" class="dose_management" title="Drug Dose Management">Drug Doses</a>
						</li>
						<li>
							<a href="#" class="indication_management" title="Drug Indication Management">Drug Indications</a>
						</li>
						<li>
							<a href="#" class="drugsource_management" title="Drug Source Management">Drug Sources</a>
						</li>
						<li>
							<a href="#" class="drugdestination_management" title="Drug Destination Management">Drug Destinations</a>
						</li>
					</ul>
				</li>
				<li class="divider-vertical"></li>
				<li class="dropdown">
					<a class="dropdown-toggle" role="button" data-toggle="dropdown" id="dLabel" href="#">Others<b class="caret"></b></a>
					<ul class="dropdown-menu setting_menus" role="menu" aria-labelledby="dLabel">
						<li>
							<a href="#" class="genericname_management" title="Generic Name Management" >Generic Names</a>
						</li>
						<li>
							<a href="#" class="brandname_management" title="Brand Name Management"> Brand Names</a>
						</li>
						<li>
							<a href="#" class="nonadherence_management" title="Non Adherence Reason Management">Non Adherence reasons</a>
						</li>
						<li>
							<a href="#" class="visit_management" title="Visit Purpose Management">Visit Purpose</a>
						</li>
					</ul>
				</li>
				<li class="divider-vertical"></li>
				<li class="dropdown">
					<a class="dropdown-toggle" role="button" data-toggle="dropdown" id="dLabel" href="#">Facility<b class="caret"></b></a>
					<ul class="dropdown-menu setting_menus" role="menu" aria-labelledby="dLabel">
						<li>
							<a href="#" class="facility_management" title="Facility Details Management">Facility details</a>
						</li>
						<li>
							<a href="#" class="client_management" title="Patient Source Management">Patient Sources</a>
						</li>
						<li>
							<a href="#" class="client_support" title="Facility Supporters Management">Facility Supporters</a>
						</li>
						<li>
							<a href="#drug_stock_balance_synch" data-toggle="modal" class="stock_balance_synch" title="Stock balance synchronization">Stock synchronization</a>
						</li>
						<li>
							<a href="#" class="user_management" title="Users Management">Users</a>
						</li>
					</ul>
				</li>

			</ul>
		</div>
		
	</div>
	<div class="f_left settings_title" style="display:none">
		<ul class="breadcrumb">
		  <li><a href="<?php echo site_url().'settings_management' ?>">Settings</a> <span class="divider">/</span></li>
		  <li class="active" id="actual_page"></li>
		</ul>
	</div>
	<div class="settings well"></div>
	<div id="loadingDiv" style="display: none"><img style="width: 30px" src="<?php echo asset_url().'images/loading_spin.gif' ?>"></div>
</div>
</div>