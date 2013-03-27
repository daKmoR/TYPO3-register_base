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
	 * @var \TYPO3\RegisterBase\Domain\Repository\FrontendUserGroupRepository
	 * @inject
	 */
	protected $frontendUserGroupRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
	 * @inject
	 */
	protected $hashService;

	/**
	 * @var \TYPO3\CMS\Core\Mail\MailMessage
	 * @inject
	 */
	protected $mailMessage;

	/**
	 * @var array
	 */
	protected $embedCache;

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $newFrontendUser
	 * @dontvalidate $newFrontendUser
	 * @return void
	 */
	public function newAction(\TYPO3\RegisterBase\Domain\Model\FrontendUser $newFrontendUser = NULL) {
		if ($newFrontendUser === NULL) {
			$newFrontendUser = $this->objectManager->create('TYPO3\RegisterBase\Domain\Model\FrontendUser');
		}

		$form = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_registerbase_form');
		if (array_key_exists('gtc', $form['newFrontendUser']) && $form['newFrontendUser']['gtc'] === '1') {
			$newFrontendUser->setGtc(true);
		}

		$this->view->assign('newFrontendUser', $newFrontendUser);

		$frontendUserGroups = $this->frontendUserGroupRepository->findByShowInFrontend(1);
		$this->view->assign('frontendUserGroups', $frontendUserGroups);

//		$this->categoryRepository->setDefaultOrderings(array('sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
//		$categories = $this->categoryRepository->findAll();
//		$this->view->assign('categories', $categories);
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $newFrontendUser
	 * @validate $newFrontendUser \TYPO3\RegisterBase\Domain\Validator\FrontendUserCreateValidator
	 * @return void
	 */
	public function createAction(\TYPO3\RegisterBase\Domain\Model\FrontendUser $newFrontendUser) {
		$newFrontendUser->disable();
		$newFrontendUser->setName();

		$newFrontendUser->setNewsletter(TRUE);
		$newFrontendUser->setNewsletterHtmlFormat(TRUE);

		if ($newFrontendUser->getUsername() === '') {
			$newFrontendUser->setUsername();
		}
		if ($newFrontendUser->getPassword() === '') {
			$tmpPassword = $this->hashService->generateHmac(\TYPO3\CMS\Core\Utility\GeneralUtility::generateRandomBytes(40));
			$newFrontendUser->setPassword($tmpPassword);
		}
		$mailHash = $this->hashService->generateHmac($newFrontendUser->getUsername() . $newFrontendUser->getPassword());

		$newFrontendUser->setMailHash($mailHash);

		$this->frontendUserRepository->add($newFrontendUser);

		$this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager')->persistAll();
		$this->sendEmailsFor($newFrontendUser, 'Activation');
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
//		$this->categoryRepository->setDefaultOrderings(array('sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
//		$categories = $this->categoryRepository->findAll();
//		$this->view->assign('categories', $categories);

		$frontendUserGroups = $this->frontendUserGroupRepository->findByShowInFrontend(1);

		$this->view->assign('frontendUser', $frontendUser);
		$this->view->assign('frontendUserGroups', $frontendUserGroups);

		$this->view->assign('updateMessage', $updateMessage);
	}

	/**
	 * @return void
	 */
	public function editLoggedInFrontendUserAction() {
		$frontendUser = $GLOBALS['TSFE']->loginUser > 0 ? $this->frontendUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']) : NULL;
		$this->redirect('edit', NULL, NULL, array('frontendUser' => $frontendUser));
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser
	 * @validate $frontendUser \TYPO3\RegisterBase\Domain\Validator\FrontendUserCreateValidator
	 * @return void
	 */
	public function updateAction(\TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser) {
		$frontendUser->setName();

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
		$emailView->assign('settings', $this->settings);
		return $emailView;
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser
	 * @param $for
	 */
	public function sendEmailsFor(\TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser, $for) {
		if (is_array($this->settings[$for])) {
			foreach($this->settings[$for] as $template => $mailSettings) {
				$emailView = $this->getEmailView($template);
				$emailView->assign('frontendUser', $frontendUser);
				$body = $emailView->render();

				foreach(array('fromEmail', 'fromName', 'toEmail', 'toName') as $property) {
					if (strpos($mailSettings[$property], 'Function:') !== FALSE) {
						$function = substr($mailSettings[$property], 9);
						$mailSettings[$property] = $frontendUser->$function();
					}
				}

				$this->mailMessage->setFrom(array($mailSettings['fromEmail'] => $mailSettings['fromName']));
				$this->mailMessage->setTo(array($mailSettings['toEmail'] => $mailSettings['toName']));
				$this->mailMessage->setSubject(sprintf($mailSettings['subject'], $frontendUser->getName(), $frontendUser->getEmail()));

				$body = preg_replace_callback('/(<img [^>]*src=["|\'])([^"|\']+)/i', array(&$this, 'imageEmbed'), $body);
				$this->mailMessage->setBody($body, 'text/html');
				$this->mailMessage->send();
			}
		}
	}

	/**
	 * @param $match
	 * @return string
	 */
	private function imageEmbed($match) {
		if ($this->embedCache === NULL) {
			$this->embedCache = array();
		}
		$key = $match[2];
		if (array_key_exists($key, $this->embedCache)) {
			return $match[1] . $this->embedCache[$key];
		}
		$this->embedCache[$key] = $this->mailMessage->embed(\Swift_Image::fromPath($match[2]));

		return $match[1] . $this->embedCache[$key];
	}

}