<?php

require_once __DIR__ . '/bootstrap/AutoLoader.php';

$loader = AutoLoader::register('src');

$loader->addClassMap(array(
	'AllInOne' => 'sdk/PHP/AioSDK/sdk/AllPay.Payment.Integration',
	'AllpayLogistics' => 'sdk/PHP/AllPayLogisticSDK/sdk/AllPay.Logistics.Integration',
));

$loader->setUserFunction(function($class, $loader) {
	$classNamespace = $loader->getClassNamespace($class);
	$classBasename = $loader->getClassBasename($class);

	if ($classNamespace != 'AllPay') {
		return false;
	}

	$facadeName = array($classNamespace, 'Facades', $classBasename);
	$path = $loader->parsePath(array_merge(array('src'), $facadeName));

	if ( ! $loader->isFileExists($path)) {
		return false;
	}

	$loader->addClassAlias(join($facadeName, '\\'), $class);
	return $path;
});
