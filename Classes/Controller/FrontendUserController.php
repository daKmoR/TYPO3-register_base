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
	 * @var \TYPO3\RegisterBase\Api\RegisterApi
	 * @inject
	 */
	protected $registerApi;

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
	 * @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
	 * @inject
	 */
	protected $countryRepository;

	/**
	 * @var array
	 */
	protected $embedCache;

	/**
	 *
	 */
	public function initializeAction() {
		$this->registerApi->setMailChimpApiKey($this->settings['mailChimpApiKey']);
		$this->registerApi->setMailChimpListId($this->settings['mailChimpListId']);
	}

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
			$newFrontendUser->setGtc(TRUE);
		}

		$countries = $this->countryRepository->findAll();
		$frontendUserGroups = $this->frontendUserGroupRepository->findByShowInFrontend(1);
		if ($this->settings['mailChimpGroups'] !== '') {
			$mailChimpGroups = array_map('trim', explode(',', $this->settings['mailChimpGroups']));
			$this->view->assign('mailChimpGroups', $mailChimpGroups);
		}

		$this->view->assign('countries', $countries);
		$this->view->assign('newFrontendUser', $newFrontendUser);
		$this->view->assign('frontendUserGroups', $frontendUserGroups);

//		$this->categoryRepository->setDefaultOrderings(array('sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
//		$categories = $this->categoryRepository->findAll();
//		$this->view->assign('categories', $categories);
	}

	/**
	 * if no newsletter is selected we have to init an empty array
	 */
	public function initializeCreateAction() {
		$data = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_registerbase_form');
		$newFrontendUser = $data['newFrontendUser'];
		if ($newFrontendUser && $newFrontendUser['mailChimpGroupsArray'] === '') {
			$newFrontendUser['mailChimpGroupsArray'] = array();
			$this->request->setArgument('newFrontendUser', $newFrontendUser);
		}
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $newFrontendUser
	 * @validate $newFrontendUser \TYPO3\RegisterBase\Domain\Validator\EmailAddressAvailableValidator
	 * @validate $newFrontendUser \TYPO3\RegisterBase\Domain\Validator\UsernameAvailableValidator
	 * @validate $newFrontendUser \TYPO3\RegisterBase\Domain\Validator\GroupNeededValidator
	 * @return void
	 */
	public function createAction(\TYPO3\RegisterBase\Domain\Model\FrontendUser $newFrontendUser) {
		$this->registerApi->register($newFrontendUser);
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
		$countries = $this->countryRepository->findAll();
		if ($this->settings['mailChimpGroups'] !== '') {
			$mailChimpGroups = array_map('trim', explode(',', $this->settings['mailChimpGroups']));
			$this->view->assign('mailChimpGroups', $mailChimpGroups);
		}

		$this->view->assign('countries', $countries);
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
	 * @validate $frontendUser \TYPO3\RegisterBase\Domain\Validator\EmailAddressAvailableValidator
	 * @validate $frontendUser \TYPO3\RegisterBase\Domain\Validator\UsernameAvailableValidator
	 * @validate $frontendUser \TYPO3\RegisterBase\Domain\Validator\GroupNeededValidator
	 * @return void
	 */
	public function updateAction(\TYPO3\RegisterBase\Domain\Model\FrontendUser $frontendUser) {
		$this->registerApi->update($frontendUser);
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
		$this->registerApi->delete($frontendUser);
	}

	/**
	 * usage: ?tx_registerbase_form%5Baction%5D=webHookMailChimp&tx_registerbase_form%5Bcontroller%5D=FrontendUser&mailChimpWebHookKey=TYPO3
	 *
	 * mailChimpWebHookKey can be defined via ts plugin.tx_registerbase.settings.mailChimpWebHookKey
	 */
	public function webHookMailChimpAction() {
		$key = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('mailChimpWebHookKey');
		if ($key !== $this->settings['mailChimpWebHookKey']) {
			die('Error: Security key did not match. Did you set plugin.tx_registerbase.settings.mailChimpWebHookKey');
		}

		$type = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('type');
		$data = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('data');

		switch ($type) {
			case 'unsubscribe':
				$this->registerApi->unsubscribeByEmail($data['email']);
				break;
			case 'profile':
				$this->registerApi->updateFromMailChimp($data);
				break;
			case 'upemail':
				$this->registerApi->updateEmail($data['old_email'], $data['new_email']);
				break;
			case 'cleaned':
				//ToDo Reason will be one of "hard" (for hard bounces) or "abuse"
				$this->registerApi->unsubscribeByEmail($data['email']);
				break;
			default:
				die('Error: no valid MailChimp Action defined');
		}
		die('webHookMailChimp DONE');
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
				if ($frontendUser->getNewsletters()) {
					$this->registerApi->mailChimpSubscribe($frontendUser);
				}
				//$frontendUser = $this->changeUsergroupPostActivation($frontendUser);
				//$this->sendEmailsPostConfirm($frontendUser);

				$this->view->assign('userActivated', TRUE);
			}
		} else {
			$this->view->assign('userNotFound', TRUE);
		}
	}

}