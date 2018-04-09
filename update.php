<?php
$name = "PayBox payment method";

$element = "paybox";
$version = "1.0.0";

$addon = JTable::getInstance('addon', 'jshop');
$addon->loadAlias($element);
$addon->set('name',$name);
$addon->set('version',$version);
$addon->set('uninstall','/components/com_jshopping/addons/'.$element.'/uninstall.php');
$cache = addslashes('{"creationDate":"10.10.2015","author":"PayBox","authorEmail":"info@paybox.money","authorUrl":"https://paybox.money","version":"'.$version.'"}');
$addon->store();
