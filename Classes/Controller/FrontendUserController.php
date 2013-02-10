<?php
namespace TYPO3\RegisterBase\Controller;

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
class FrontendUserController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \TYPO3\RegisterBase\Domain\Repository\FrontendUserRepository
	 * @inject
	 */
	protected $frontendUserRepository;

	/**
	 * @var \TYPO3\RegisterBase\Domain\Repository\CategoryRepository
	 * @inject
	 */
	protected $categoryRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository
	 * @inject
	 */
	protected $frontendUserGroupRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
	 * @inject
	 */
	protected $hashService;

	/**
	 *
	 */
	public function initializeAction() {
		if (array_key_exists($this->arguments, 'newFrontendUser')) {
			$mappingConfiguration = $this->arguments['newFrontendUser']->getPropertyMappingConfiguration();
			for ($i = 0; $i < 100; $i++) {
				$mappingConfiguration->allowCreationForSubProperty('categories.' . $i);
				$mappingConfiguration->allowModificationForSubProperty('categories.' . $i);
			}
		}
		if (array_key_exists($this->arguments, 'frontendUser')) {
			$mappingConfiguration = $this->arguments['frontendUser']->getPropertyMappingConfiguration();
			for ($i = 0; $i < 100; $i++) {
				$mappingConfiguration->allowCreationForSubProperty('categories.' . $i);
				$mappingConfiguration->allowModificationForSubProperty('categories.' . $i);
			}
		}
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $newFrontendUser
	 * @dontvalidate $newFrontendUser
	 * @return void
	 */
	public function newAction(\TYPO3\RegisterBase\Domain\Model\FrontendUser $newFrontendUser = NULL) {
		$this->view->assign('newFrontendUser', $newFrontendUser);

		$userGroups = $this->frontendUserGroupRepository->findAll();
		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($userGroups);
		die();
//		$this->categoryRepository->setDefaultOrderings(array('sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
//		$categories = $this->categoryRepository->findAll();
//		$this->view->assign('categories', $categories);
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $newFrontendUser
	 * @return void
	 */
	public function createAction(\TYPO3\RegisterBase\Domain\Model\FrontendUser $newFrontendUser) {
		$newFrontendUser->disable();
		$newFrontendUser->setName();
		$newFrontendUser->setUsername();
		$newFrontendUser->setNewsletter(TRUE);
		$newFrontendUser->setNewsletterHtmlFormat(TRUE);

		if ($newFrontendUser->getPassword() === '') {
			$tmpPassword = $this->hashService->generateHmac(\TYPO3\CMS\Core\Utility\GeneralUtility::generateRandomBytes(40));
			$newFrontendUser->setPassword($tmpPassword);
		}
		$mailHash = $this->hashService->generateHmac($newFrontendUser->getPassword());

		$newFrontendUser->setMailHash($mailHash);

		$this->frontendUserRepository->add($newFrontendUser);

		$this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager')->persistAll();
		$this->sendActivationEmail($newFrontendUser);
	}

	/**
	 * @param string $authCode
	 */
	public function editViaHashAction($authCode) {
		$frontendUser = $this->frontendUserRepository->findByMailHash($authCode);
		if ($frontendUser instanceof \TYPO3\RegisterBase\Domain\Model\FrontendUser) {
			$this->forward('edit', NULL, NULL, array('frontendUser' => $frontendUser));
		}
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser
	 * @param string $updateMessage
	 * @return void
	 */
	public function editAction(\TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser, $updateMessage = '') {
		$this->categoryRepository->setDefaultOrderings(array('sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
		$categories = $this->categoryRepository->findAll();
		$this->view->assign('categories', $categories);
		$this->view->assign('frontendUser', $frontendUser);
		$this->view->assign('updateMessage', $updateMessage);
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser
	 * @return void
	 */
	public function updateAction(\TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser) {
		$frontendUser->setName();
		$frontendUser->setUsername();

		$this->frontendUserRepository->update($frontendUser);
		$this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();
		$this->forward('edit', NULL, NULL, array('frontendUser' => $frontendUser, 'updateMessage' => 'Die Benutzerdaten wurden geändert.'));
	}

	/**
	 * @param string $authCode
	 */
	public function deleteViaHashAction($authCode) {
		$frontendUser = $this->frontendUserRepository->findByMailHash($authCode);
		if ($frontendUser instanceof \TYPO3\RegisterBase\Domain\Model\FrontendUser) {
			$this->forward('delete', NULL, NULL, array('frontendUser' => $frontendUser));
		}
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser
	 * @return void
	 */
	public function deleteAction(\TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser) {
		$this->frontendUserRepository->remove($frontendUser);
	}

	/**
	 *
	 * @param string $authCode
	 * @return void
	 */
	public function confirmAction($authCode) {
		$frontendUser = $this->frontendUserRepository->findByMailHash($authCode);

		if ($frontendUser instanceof \TYPO3\RegisterBase\Domain\Model\FrontendUser) {
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
		$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);
		$emailView = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$emailView->setTemplatePathAndFilename($templateRootPath . 'Email/' . $name . '.html');
		$emailView->assign('templateRootPath', $templateRootPath);
		return $emailView;
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser
	 */
	public function sendActivationEmail(\TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser) {
		$emailView = $this->getEmailView('Activation');
		$emailView->assign('frontendUser', $frontendUser);
		$this->sendEmail($frontendUser, $emailView->render(), 'Registrierung bestätigen');
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser
	 */
	public function sendConfirmationEmail(\TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser) {
		$emailView = $this->getEmailView('Confirmation');
		$emailView->assign('frontendUser', $frontendUser);
		$this->sendEmail($frontendUser, $emailView->render(), 'Registrierung erfolgreich');
	}

	/**
	 * sends all mails configured in the setting sendEmail
	 *
	 * @param $frontendUser
	 * @param $body string
	 * @param $subject
	 * @return void
	 */
	public function sendEmail($frontendUser, $body, $subject) {
		$this->settings['fromEmail'] = 'newsletter@medianet.at';
		$this->settings['fromName'] = 'medianet';

		//$mail = t3lib_div::makeInstance('t3lib_mail_Message');
		$mail = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');
		$mail->setFrom(array($this->settings['fromEmail'] => $this->settings['fromName']));
		$mail->setTo(array($frontendUser->getEmail() => $frontendUser->getName()));
		$mail->setSubject($subject);
		$mail->setBody($body, 'text/html');
		$mail->send();
	}

}
