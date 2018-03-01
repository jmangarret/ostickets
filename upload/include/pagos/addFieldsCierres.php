<?php
$Vtiger_Utils_Log = true;
ini_set('display_errors', true);
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once('vtlib/Vtiger/Module.php');
include_once('vtlib/Vtiger/Utils.php');
include_once('vtlib/Vtiger/Menu.php');
require_once('vtlib/Vtiger/Block.php');
require_once('vtlib/Vtiger/Field.php');
$Vtiger_Utils_Log = true;
// Define instances
$boletos = Vtiger_Module::getInstance('Boletos');
// Nouvelle instance pour le nouveau bloc
$block = Vtiger_Block::getInstance('LBL_BLOCK_BOLETOS', $boletos);
// Add field
$fieldInstance = new Vtiger_Field();
$fieldInstance->name = 'cierresid'; //Usually matches column name
$fieldInstance->table = 'vtiger_boletos';
$fieldInstance->column = 'cierresid'; //Must be lower case
$fieldInstance->label = 'Cierre de Reporte'; //Upper case preceeded by LBL_
$fieldInstance->columntype = 'INT(11)'; //
$fieldInstance->uitype = 10; //Related module
$fieldInstance->typeofdata = 'N~M'; //V=Varchar?, M=Mandatory, O=Optional
$block->addField($fieldInstance);

$fieldInstance->setRelatedModules(Array('CierreDeReportes'));

$fieldInstance2 = new Vtiger_Field();
$fieldInstance2->name = 'status_satelite'; //Usually matches column name
$fieldInstance2->table = 'vtiger_boletos';
$fieldInstance2->column = 'status_satelite'; //Must be lower case
$fieldInstance2->label = 'Status Satelite'; //Upper case preceeded by LBL_
$fieldInstance2->columntype = 'VARCHAR(100)'; //
$fieldInstance2->uitype = 2; //text no obligatorio
$fieldInstance2->typeofdata = 'V~M'; //V=Varchar?, M=Mandatory, O=Optional
$fieldInstance2->defaultvalue = 'Pendiente'; 
$block->addField($fieldInstance2);

$block->save($boletos);
$boletos->initWebservice();

/****** CAMPOS PAGOS *****/
/****** CAMPOS PAGOS *****/
/****** CAMPOS PAGOS *****/

$pagos = Vtiger_Module::getInstance('RegistroDePagos');
// Nouvelle instance pour le nouveau bloc
$block2 = Vtiger_Block::getInstance('LBL_BLOCK_RESUMEN', $pagos);
// Add field
$fieldInstance = new Vtiger_Field();
$fieldInstance->name = 'cierresid'; //Usually matches column name
$fieldInstance->table = 'vtiger_registrodepagos';
$fieldInstance->column = 'cierresid'; //Must be lower case
$fieldInstance->label = 'Cierre de Reporte'; //Upper case preceeded by LBL_
$fieldInstance->columntype = 'INT(11)'; //
$fieldInstance->uitype = 10; //Related module
$fieldInstance->typeofdata = 'N~M'; //V=Varchar?, M=Mandatory, O=Optional
$block2->addField($fieldInstance);

$fieldInstance->setRelatedModules(Array('CierreDeReportes'));

$fieldInstance2 = new Vtiger_Field();
$fieldInstance2->name = 'status_satelite'; //Usually matches column name
$fieldInstance2->table = 'vtiger_registrodepagos';
$fieldInstance2->column = 'status_satelite'; //Must be lower case
$fieldInstance2->label = 'Status Satelite'; //Upper case preceeded by LBL_
$fieldInstance2->columntype = 'VARCHAR(100)'; //
$fieldInstance2->uitype = 2; //text no obligatorio
$fieldInstance2->typeofdata = 'V~M'; //V=Varchar?, M=Mandatory, O=Optional
$fieldInstance2->defaultvalue = 'Pendiente'; 
$block2->addField($fieldInstance2);

$block2->save($pagos);
$pagos->initWebservice();
?>
