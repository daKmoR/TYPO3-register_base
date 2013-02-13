<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'TYPO3.' . $_EXTKEY,
	'Form',
	array(
		'FrontendUser' => 'new, create, edit, editViaHash, update, delete, deleteViaHash, confirm',
	),
	// non-cacheable actions
	array(
		'FrontendUser' => 'create, update, delete, confirm',
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'TYPO3.' . $_EXTKEY,
	'Edit',
	array(
		'FrontendUser' => 'editLoggedInFrontendUser, edit, editViaHash, update, delete, deleteViaHash, confirm',
	),
	// non-cacheable actions
	array(
		'FrontendUser' => 'update, delete, confirm',
	)
);


?>