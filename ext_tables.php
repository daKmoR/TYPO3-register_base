<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Form',
	'Register Form'
);

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Register using extbase');

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

t3lib_div::loadTCA('fe_users');
t3lib_extMgm::addTCAcolumns('fe_users',$tempCols);
$TCA['fe_users']['feInterface']['fe_admin_fieldList'].=',gtc';
t3lib_extMgm::addToAllTCATypes('fe_users','gtc');

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