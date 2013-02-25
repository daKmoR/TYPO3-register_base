<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'TYPO3.' . $_EXTKEY,
	'register: Form',
	array(
		'FrontendUser' => 'new, create, edit, editViaHash, editLoggedInFrontendUser, update, delete, deleteViaHash, confirm',
	),
	// non-cacheable actions
	array(
		'FrontendUser' => 'new, create, edit, editViaHash, editLoggedInFrontendUser, update, delete, deleteViaHash, confirm',
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'TYPO3.' . $_EXTKEY,
	'register: Edit logged in FrontendUser',
	array(
		'FrontendUser' => 'editLoggedInFrontendUser, new, create, edit, editViaHash, update, delete, deleteViaHash, confirm',
	),
	// non-cacheable actions
	array(
		'FrontendUser' => 'new, create, edit, editViaHash, editLoggedInFrontendUser, update, delete, deleteViaHash, confirm',
	)
);

?>