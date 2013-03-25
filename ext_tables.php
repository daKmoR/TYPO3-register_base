<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Form',
	'Register: Register Form'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Edit',
	'Register: Edit Form'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Register using extbase');

// allows to use the following marker in the newsletter ###USER_mailhash###
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['direct_mail']['addRecipFields'] = 'mailhash';

// fe_users modified
$tempCols = array(
	'gtc' => array(
		'exclude'	=> 0,
		'label'		=> 'AGB',
		'config'	=> array(
			'type'	=> 'check',
		)
	)
);

// \TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('fe_users');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempCols);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'gtc');

$tempCols = array(
	'show_in_frontend' => array(
		'exclude' => 0,
		'label' => 'Show in Frontend',
		'config' => array(
			'type' => 'check',
		),
	),
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_groups', $tempCols);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_groups', 'show_in_frontend');

// category
//$TCA['sys_dmail_category']['interface'] = array(
//	'showRecordFieldList' => 'hidden,category,description'
//);
//$TCA['sys_dmail_category']['types'] = array(
//	'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource,hidden;;1;;1-1-1, category, description')
//);

//$TCA['sys_dmail_category']['columns']['description'] = array(
//	'exclude' => 0,
//	'label' => 'Beschreibung',
//	'config' => array(
//		'type' => 'input',
//		'size' => '30',
//	)
//);