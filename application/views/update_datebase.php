<?php
foreach($regimens as $regimen){?>
Populate("insert into regimen values(<?php echo $regimen->id;?>,'<?php echo $regimen->Regimen_Code;?>','<?php echo $regimen->Regimen_Desc;?>','<?php echo $regimen->Category;?>','<?php echo $regimen->Line;?>','<?php echo $regimen->Type_Of_Service;?>','<?php echo $regimen->Remarks;?>','<?php echo $regimen->Enabled;?>')");<?php }
foreach($supporters as $supporter){
?>
Populate("insert into supporter values(<?php echo $supporter->id;?>,'<?php echo $supporter->Name;?>')");<?php }
foreach($service_types as $service_type){
?>
Populate("insert into regimen_service_type values(<?php echo $service_type->id;?>,'<?php echo $service_type->Name;?>')");<?php }
foreach($sources as $source){
?>
Populate("insert into patient_source values(<?php echo $source->id;?>,'<?php echo $source->Name;?>')");<?php }
foreach($drugs as $drug){
?>
Populate("insert into drugcode values(<?php echo $drug->id;?>,'<?php echo $drug->Drug;?>','<?php echo $drug->Drug_Unit->Name;?>','<?php echo $drug->Pack_Size;?>','<?php echo $drug->Safety_Quantity;?>','<?php echo $drug->Generic->Name;?>','<?php echo $drug->Supporter->Name;?>','<?php echo $drug->Drug_Dose->Name;?>','<?php echo $drug->Duration;?>','<?php echo $drug->Quantity;?>')");<?php }
foreach($regimen_change_purpose as $change_purpose){
?>
Populate("insert into regimen_change_purpose values(<?php echo $change_purpose->id;?>,'<?php echo $change_purpose->Name;?>')");<?php }
foreach($visit_purpose as $v_purpose){
?>
Populate("insert into visit_purpose values(<?php echo $v_purpose->id;?>,'<?php echo $v_purpose->Name;?>')");<?php }
foreach($opportunistic_infections as $oi){
?>
Populate("insert into opportunistic_infections values(<?php echo $oi->id;?>,'<?php echo $oi->Name;?>')");<?php }
foreach($regimen_drugs as $regimen_drug){
?>
Populate("insert into regimen_drug values(<?php echo $regimen_drug->id;?>,'<?php echo $regimen_drug->Regimen;?>','<?php echo $regimen_drug->Drugcode;?>')");<?php } 
foreach($scheduled_patients as $scheduled_patient){
?>
Populate("insert into scheduled_patients values(<?php echo $scheduled_patient->id;?>,'<?php echo $scheduled_patient->Patient_Object->First_Name." ".$scheduled_patient->Patient_Object->Other_Name." ".$scheduled_patient->Patient_Object->Last_Name;?>','<?php echo $scheduled_patient->id;?>','<?php echo $scheduled_patient->Current_Regimen;?>')");<?php } 
 

?>