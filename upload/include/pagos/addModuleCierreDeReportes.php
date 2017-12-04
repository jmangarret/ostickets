<?php
include_once ('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;

// Module Create
$MODULENAME = 'Cierres';
$TABLENAME = 'vtiger_cierres';
$moduleInstance = new Vtiger_Module(); 
$moduleInstance->name = $MODULENAME;     
$moduleInstance->parent= 'Satelites';
$moduleInstance->save();			  

// Schema Setup
$moduleInstance->initTables(); 		 
$menuInstance = Vtiger_Menu::getInstance('Satelites');  
$menuInstance->addModule($moduleInstance);			

/////////////////////////INICIO BLOQUE///////////////////////////
$blockInstance = new Vtiger_Block();					
$blockInstance->label = 'LBL_CIERRE_INFORMATION';		
$moduleInstance->addBlock($blockInstance);				

$blockInstance2 = new Vtiger_Block();					
$blockInstance2->label = 'LBL_CUSTOM_INFORMATION';		
$moduleInstance->addBlock($blockInstance2);				

/////////////////////////INICIO CAMPOS///////////////////////////
$fieldInstance1  = new Vtiger_Field();                          
$fieldInstance1->name = 'accountid';                                       
$fieldInstance1->label = 'Cuenta/Satelite';                                      
$fieldInstance1->table = $TABLENAME;                      
$fieldInstance1->uitype = 10;                                            
$fieldInstance1->column = 'accountid';                                     
$fieldInstance1->columntype = 'INT(11)';           
$fieldInstance1->typeofdata = 'I~M';                            
$blockInstance->addField($fieldInstance1); 

$fieldInstance1->setRelatedModules(Array('Accounts'));

$fieldInstance2  = new Vtiger_Field();                          
$fieldInstance2->name = 'nombre';                                     
$fieldInstance2->label = 'Nombre del Reporte';                            
$fieldInstance2->table = $TABLENAME;                      
$fieldInstance2->uitype = 1;                                            
$fieldInstance2->column = 'nombre';                           
$fieldInstance2->columntype = 'VARCHAR(255)';           
$fieldInstance2->typeofdata = 'V~M';                            
$blockInstance->addField($fieldInstance2);   

$fieldInstance3  = new Vtiger_Field();
$fieldInstance3->name = 'desde';
$fieldInstance3->label = 'Desde';
$fieldInstance3->table = $TABLENAME;
$fieldInstance3->uitype = 5;
$fieldInstance3->column = 'desde';
$fieldInstance3->columntype = 'DATE';
$fieldInstance3->typeofdata = 'D~M';
$blockInstance->addField($fieldInstance3);

$fieldInstance4  = new Vtiger_Field();
$fieldInstance4->name = 'hasta';
$fieldInstance4->label = 'Hasta';
$fieldInstance4->table = $TABLENAME;
$fieldInstance4->uitype = 5;
$fieldInstance4->column = 'hasta';
$fieldInstance4->columntype = 'DATE';
$fieldInstance4->typeofdata = 'D~M';
$blockInstance->addField($fieldInstance4);

$fieldInstance5  = new Vtiger_Field();
$fieldInstance5->name = 'saldobs';
$fieldInstance5->label = 'Saldo VEF';
$fieldInstance5->table = $TABLENAME;
$fieldInstance5->uitype = 71;
$fieldInstance5->column = 'saldobs';
$fieldInstance5->columntype = 'DECIMAL(25,2)';
$fieldInstance5->typeofdata = 'N~M';
$blockInstance->addField($fieldInstance5);

$fieldInstance6  = new Vtiger_Field();
$fieldInstance6->name = 'saldodol';
$fieldInstance6->label = 'Saldo USD';
$fieldInstance6->table = $TABLENAME;
$fieldInstance6->uitype = 71;
$fieldInstance6->column = 'saldodol';
$fieldInstance6->columntype = 'DECIMAL(25,2)';
$fieldInstance6->typeofdata = 'N~M';
$blockInstance->addField($fieldInstance6);

$fieldInstance7  = new Vtiger_Field();
$fieldInstance7->name = 'status';
$fieldInstance7->label = 'Status';
$fieldInstance7->table = $TABLENAME;
$fieldInstance7->uitype = 16;
$fieldInstance7->column = 'status';
$fieldInstance7->columntype = 'VARCHAR(25)';
$fieldInstance7->typeofdata = 'V~M';
$blockInstance->addField($fieldInstance7);

$moduleInstance->setEntityIdentifier($fieldInstance2);

/////////////////////////INICIO CAMPOS OBLIGATORIOS ///////////////////////////
$mfield1 = new Vtiger_Field();
$mfield1->name = 'assigned_user_id';
$mfield1->label = 'Assigned To';
$mfield1->table = 'vtiger_crmentity';
$mfield1->column = 'smownerid';
$mfield1->uitype = 53;
$mfield1->typeofdata = 'V~M';
$blockInstance2->addField($mfield1);

$mfield2 = new Vtiger_Field();
$mfield2->name = 'createdTime';
$mfield2->label= 'Created Time';
$mfield2->table = 'vtiger_crmentity';
$mfield2->column = 'createdtime';
$mfield2->uitype = 70;
$mfield2->typeofdata = 'T~O';
$mfield2->displaytype= 2;
$blockInstance2->addField($mfield2);

$mfield3 = new Vtiger_Field();
$mfield3->name = 'modifiedTime';
$mfield3->label= 'Modified Time';
$mfield3->table = 'vtiger_crmentity';
$mfield3->column = 'modifiedtime';
$mfield3->uitype = 70;
$mfield3->typeofdata = 'T~O';
$mfield3->displaytype= 2;
$blockInstance2->addField($mfield3);

/////////////////////////INICIO FILTRO OBLIGATORIO ///////////////////////////
$filter1 = new Vtiger_Filter();
$filter1->name = 'All';
$filter1->isdefault = true;
$moduleInstance->addFilter($filter1);

//campos que se agregaran al filtro
$filter1->addField($fieldInstance1)
        ->addField($fieldInstance2, 1)
        ->addField($fieldInstance3, 2)
        ->addField($fieldInstance4, 3)
        ->addField($fieldInstance5, 4)
        ->addField($fieldInstance6, 5)
        ->addField($mfield1, 6)
        ->addField($mfield2, 7)
        ->addField($mfield3, 8); 

//// Sharing Access Setup
$moduleInstance->setDefaultSharing();

//// Webservice Setup
$moduleInstance->initWebservice();

$moduleInstance = Vtiger_Module::getInstance('Cierres');
$moduleInstance->setRelatedList(Vtiger_Module::getInstance('Boletos'), 'Boletos',Array('ADD','SELECT'),'get_related_list');
$moduleInstance->setRelatedList(Vtiger_Module::getInstance('RegistroDePagos'), 'Pagos',Array('ADD','SELECT'),'get_related_list');

echo "MODULE OK!\n";

?>