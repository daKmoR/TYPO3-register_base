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

$TCA['fe_users']['interface'] = array(
	'showRecordFieldList' => 'gtc,username,password,usergroup,lockToDomain,name,first_name,middle_name,last_name,title,company,address,zip,city,country,email,www,telephone,fax,disable,starttime,endtime,lastlogin',
);
$TCA['fe_users']['columns']['gtc'] = array(
	'exclude'	=> 0,
	'label'		=> 'AGB',
	'config'	=> array(
		'type'	=> 'check',
	)
);
$TCA['fe_users']['types'] = array(
		'0' => array('showitem' => '
			disable,gtc,username;;;;1-1-1, password, usergroup, lastlogin;;;;1-1-1,
			--div--;LLL:EXT:cms/locallang_tca.xml:fe_users.tabs.personelData, company;;1;;1-1-1, name;;2;;2-2-2, address, zip, city, country, telephone, fax, email, www, image;;;;2-2-2,
			--div--;LLL:EXT:cms/locallang_tca.xml:fe_users.tabs.options, lockToDomain;;;;1-1-1, TSconfig;;;;2-2-2,
			--div--;LLL:EXT:cms/locallang_tca.xml:fe_users.tabs.access, starttime, endtime,
			--div--;LLL:EXT:cms/locallang_tca.xml:fe_users.tabs.extended
		')
);