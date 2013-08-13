<?php
	$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('register_base');
	return array(
		'MailChimp' => $extensionPath . 'Resources/Private/Php/mailchimp-api/MailChimp.class.php',
	);
?>