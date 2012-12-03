<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Form',
	array(
		'FrontendUser' => 'new, create, edit, editViaHash, update, delete, deleteViaHash, confirm',

	),
	// non-cacheable actions
	array(
		'FrontendUser' => 'create, update, delete, confirm',

	)
);

?>