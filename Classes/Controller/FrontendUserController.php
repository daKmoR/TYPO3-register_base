<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Thomas Allmer <thomas.allmer@webteam.at>, WEBTEAM GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 *
 *
 * @package register_base
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_RegisterBase_Controller_FrontendUserController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_RegisterBase_Domain_Repository_FrontendUserRepository
	 */
	protected $frontendUserRepository;

	/**
	 * @param Tx_RegisterBase_Domain_Repository_FrontendUserRepository $frontendUserRepository
	 * @return void
	 */
	public function injectFrontendUserRepository(Tx_RegisterBase_Domain_Repository_FrontendUserRepository $frontendUserRepository) {
		$this->frontendUserRepository = $frontendUserRepository;
	}

	/**
	 * @var Tx_Extbase_Domain_Repository_FrontendUserGroupRepository
	 */
	protected $frontendUserGroupRepository;

	/**
	 * @param Tx_Extbase_Domain_Repository_FrontendUserGroupRepository $frontendUserGroupRepository
	 * @return void
	 */
	public function injectFrontendUserGroupRepository(Tx_Extbase_Domain_Repository_FrontendUserGroupRepository $frontendUserGroupRepository) {
		$this->frontendUserGroupRepository = $frontendUserGroupRepository;
	}

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @var Tx_Extbase_Security_Cryptography_HashService
	 */
	protected $hashService;

	/**
	 * @param Tx_Extbase_Security_Cryptography_HashService $hashService
	 * @return void
	 */
	public function injectHashService(Tx_Extbase_Security_Cryptography_HashService $hashService) {
		$this->hashService = $hashService;
	}

	/**
	 * @param Tx_RegisterBase_Domain_Model_FrontendUser $newFrontendUser
	 * @dontvalidate $newFrontendUser
	 * @return void
	 */
	public function newAction(Tx_RegisterBase_Domain_Model_FrontendUser $newFrontendUser = NULL) {
		$this->view->assign('newFrontendUser', $newFrontendUser);
	}

	/**
	 * @param Tx_RegisterBase_Domain_Model_FrontendUser $newFrontendUser
	 * @return void
	 */
	public function createAction(Tx_RegisterBase_Domain_Model_FrontendUser $newFrontendUser) {
		$newFrontendUser->disable();
		$newFrontendUser->setName();
		$newFrontendUser->setUsername();

		//$defaultFrontEndUserGroup = $this->frontendUserGroupRepository->findAll();

		if ($newFrontendUser->getPassword() === '') {
			$newFrontendUser->setPassword(t3lib_div::generateRandomBytes(40));
		}
		$mailHash = $this->hashService->generateHash($newFrontendUser->getPassword());
		$newFrontendUser->setMailHash($mailHash);

		$this->frontendUserRepository->add($newFrontendUser);

		$this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();
		$this->sendActivationEmail($newFrontendUser);
	}

	/**
	 * @param string $authCode
	 */
	public function editViaHashAction($authCode) {
		$frontendUser = $this->frontendUserRepository->findByMailHash($authCode);
		if ($frontendUser instanceof Tx_RegisterBase_Domain_Model_FrontendUser) {
			$this->forward('edit', NULL, NULL, array('frontendUser' => $frontendUser));
		}
	}

	/**
	 * @param Tx_RegisterBase_Domain_Model_FrontendUser $frontendUser
	 * @return void
	 */
	public function editAction(Tx_RegisterBase_Domain_Model_FrontendUser $frontendUser) {
		$this->view->assign('frontendUser', $frontendUser);
	}

	/**
	 * @param Tx_RegisterBase_Domain_Model_FrontendUser $frontendUser
	 * @return void
	 */
	public function updateAction(Tx_RegisterBase_Domain_Model_FrontendUser $frontendUser) {
		$frontendUser->setName();
		$frontendUser->setUsername();

		$this->frontendUserRepository->update($frontendUser);
		$this->flashMessageContainer->add('Your FrontendUser was updated.');
		$this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();
		$this->forward('edit', NULL, NULL, array('frontendUser' => $frontendUser));
	}

	/**
	 * @param Tx_RegisterBase_Domain_Model_FrontendUser $frontendUser
	 * @return void
	 */
	public function deleteAction(Tx_RegisterBase_Domain_Model_FrontendUser $frontendUser) {
		$this->frontendUserRepository->remove($frontendUser);
	}

	/**
	 *
	 * @param string $authCode
	 * @return void
	 */
	public function confirmAction($authCode) {
		$frontendUser = $this->frontendUserRepository->findByMailHash($authCode);

		if ($frontendUser instanceof Tx_RegisterBase_Domain_Model_FrontendUser) {
			$this->view->assign('frontendUser', $frontendUser);
			if ($frontendUser->isEnabled()) {
				$this->view->assign('userAlreadyActive', TRUE);
			} else {
				$frontendUser->enable();
				//$frontendUser = $this->changeUsergroupPostActivation($frontendUser);
				//$this->sendEmailsPostConfirm($frontendUser);

				$this->view->assign('userActivated', TRUE);
			}
		} else {
			$this->view->assign('userNotFound', TRUE);
		}
	}

	/**
	 * @param $name
	 * @return object
	 */
	public function getEmailView($name) {
		$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$templateRootPath = t3lib_div::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);
		$emailView = $this->objectManager->get('Tx_Fluid_View_StandaloneView');
		$emailView->setTemplatePathAndFilename($templateRootPath . 'Email/' . $name . '.html');
		$emailView->assign('templateRootPath', $templateRootPath);
		return $emailView;
	}

	/**
	 * @param Tx_RegisterBase_Domain_Model_FrontendUser $frontendUser
	 */
	public function sendActivationEmail(Tx_RegisterBase_Domain_Model_FrontendUser $frontendUser) {
		$emailView = $this->getEmailView('Activation');
		$emailView->assign('frontendUser', $frontendUser);
		echo $emailView->render();

		//$this->sendEmail($frontendUser, $emailView->render(););
	}

	/**
	 * @param Tx_RegisterBase_Domain_Model_FrontendUser $frontendUser
	 */
	public function sendConfirmationEmail(Tx_RegisterBase_Domain_Model_FrontendUser $frontendUser) {
		$emailView = $this->getEmailView('Confirmation');
		$emailView->assign('frontendUser', $frontendUser);
		echo $emailView->render();

		//$this->sendEmail($frontendUser, $emailView->render(););
	}

	/**
	 * sends all mails configured in the setting sendEmail
	 *
	 * @param $frontendUser
	 * @param $body string
	 * @return void
	 */
	public function sendEmail($frontendUser, $body) {
		$mail = t3lib_div::makeInstance('t3lib_mail_Message');
		$mail->setFrom(array($this->settings['fromEmail'] => $this->settings['fromName']));
		$mail->setTo(array($frontendUser->getEmail() => $frontendUser->getName()));
		$mail->setSubject('Pls Confirm your registration');
		$mail->setBody($body, 'text/html');
		$mail->send();
	}

}
