<?php ?>

<!DOCTYPE html >
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" manifest="/ADT/offline.appcache">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $title;?></title>
<link href="<?php echo base_url().'CSS/style.css'?>" type="text/css" rel="stylesheet"/>
<link rel="SHORTCUT ICON" href="<?php echo base_url().'Images/favicon.ico';?>">

<?php if(isset($script_urls)){
foreach ($script_urls as $script_url){
echo "<script src=\"".$script_url."\" type=\"text/javascript\"></script>";
}
}?>

<?php if(isset($scripts)){
foreach ($scripts as $script){
echo "<script src=\"".base_url()."Scripts/".$script."\" type=\"text/javascript\"></script>";
}
}?>


 
<?php if(isset($styles)){
foreach ($styles as $style){
echo "<link href=\"".base_url()."CSS/".$style."\" type=\"text/css\" rel=\"stylesheet\"></link>";
}
}?> 
<!-- Bootstrap -->
<link href="<?php echo base_url().'Scripts/bootstrap/css/bootstrap.min.css'?>" rel="stylesheet" media="screen">
<link href="<?php echo base_url().'Scripts/bootstrap/css/bootstrap-responsive.min.css'?>" rel="stylesheet" media="screen">
<script type="text/javascript" src="<?php echo base_url().'Scripts/bootstrap/js/jquery_bootstrap.js'?>"></script>
<style type="text/css">
#signup_form{ 
background-color:whiteSmoke; 
border: 1px solid #E5E5E5;
padding: 20px 25px 15px;
width:700px;
margin:0 auto;
}


#signup_form label{
display: block;
margin: 0 auto 1.5em auto;
width:300px;
 
}
.label {
font-weight: bold;
margin: 0 0 .5em;
display: block;
-webkit-user-select: none;
-moz-user-select: none;
user-select: none;
}
.remember-label {
font-weight: normal;
color: #666;
line-height: 0;
padding: 0 0 0 .4em;
-webkit-user-select: none;
-moz-user-select: none;
user-select: none;
}
#system_title{ 
	position: absolute;
	top: 50px;
	left: 110px;
	text-shadow: 0 1px rgba(0, 0, 0, 0.1);
	letter-spacing: 1px;
}
#main_wrapper{
margin-top:100px;	
width: auto;	
border:0px;
}

#contact_email,#contact_phone{
	height:29px;
}
</style>
</head>

<body>
<div id="wrapper">
	<div id="top-panel" style="margin:0px;">

		<div class="logo">
<a class="logo" href="<?php echo base_url();?>" ></a> 

</div>
<div id="system_title">
<span style="display: block; font-weight: bold; font-size: 14px; margin:2px;">Ministry of Health</span>
 <span style="display: block; font-size: 12px;">ARV Drugs Supply Chain Management Tool</span> 
</div>
 
</div>

<div id="inner_wrapper"> 


<div id="main_wrapper"> 

<div id="signup_form">
	 <div class="short_title" >
<h1 class="banner_text" >Reset Password</h1>
<hr size="1">
</div>
<?php
if(isset($error)){
	echo $error;		
}
else if(isset($message)){
	echo $message;
}

?>

</div>

</div>  

  <!--End Wrapper div--></div>
    <div id="bottom_ribbon" style="top:150px; width:90%;">
        <div id="footer">
 <?php $this->load->view("footer_v");?>
    </div>
    </div>
</body>
</html>
