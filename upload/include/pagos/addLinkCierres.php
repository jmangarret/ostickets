<?php
ini_set('display_errors', true);
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
include_once('vtlib/Vtiger/Module.php');
//CREA UN LINK RELACIONADO CON EL MODULO INDICADO
$moduleInstance = Vtiger_Module::getInstance('Cierres2');
$moduleInstance->setRelatedList(Vtiger_Module::getInstance('Boletos'), 'Boletos',Array('ADD','SELECT'),'get_related_list');
$moduleInstance->setRelatedList(Vtiger_Module::getInstance('RegistroDePagos'), 'Pagos',Array('ADD','SELECT'),'get_related_list');
?>
