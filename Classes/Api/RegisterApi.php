<?php
namespace TYPO3\RegisterBase\Api;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Thomas Allmer <thomas.allmer@moodley.at>, moodley brand identity
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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 *
 * @package register_base
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class RegisterApi {

	/**
	 * @var \TYPO3\RegisterBase\Domain\Repository\FrontendUserRepository
	 * @inject
	 */
	protected $frontendUserRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
	 * @inject
	 */
	protected $hashService;

	/**
	 * @var \MailChimp
	 * @inject
	 */
	protected $mailChimp;

	/**
	 * @var string
	 */
	protected $mailChimpApiKey;

	/**
	 * @var string
	 */
	protected $mailChimpListId;

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var \TYPO3\CMS\Core\Mail\MailMessage
	 * @inject
	 */
	protected $mailMessage;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator
	 * @inject
	 */
	protected $emailAddressValidator;

	/**
	 * @var \TYPO3\RegisterBase\Domain\Validator\EmailAddressAvailableValidator
	 * @inject
	 */
	protected $emailAddressAvailableValidator;

	/**
	 * @var \TYPO3\RegisterBase\Domain\Validator\UsernameAvailableValidator
	 * @inject
	 */
	protected $usernameAvailableValidator;

	/**
	 * @var \TYPO3\RegisterBase\Domain\Validator\GroupNeededValidator
	 * @inject
	 */
	protected $groupNeededValidator;

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $user
	 * @return boolean
	 */
	public function register(\TYPO3\RegisterBase\Domain\Model\FrontendUser $user) {
		$settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$this->settings = $settings['plugin.']['tx_registerbase.']['settings.'];

		if (
			$this->emailAddressValidator->isValid($user->getEmail()) &&
			$this->emailAddressAvailableValidator->isValid($user) &&
			$this->usernameAvailableValidator->isValid($user) &&
			$this->groupNeededValidator->isValid($user)
		) {
			$user->disable();
			$user->setName();

			$user->setNewsletterHtmlFormat(TRUE);

			if ($user->getUsername() === '') {
				$user->setUsername();
				$this->settings['usernameGenerated'] = TRUE;
			}
			if ($user->getPassword() === '') {
				$tmpPassword = $this->hashService->generateHmac(\TYPO3\CMS\Core\Utility\GeneralUtility::generateRandomBytes(40));
				$tmpPassword = substr($tmpPassword, 0, 8);
				$user->setPassword($tmpPassword);
				$this->settings['passwordGenerated'] = TRUE;
			}
			$mailHash = $this->hashService->generateHmac($user->getUsername() . $user->getPassword());
			$user->setMailHash($mailHash);

			$this->frontendUserRepository->add($user);

			$this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager')->persistAll();
			$this->sendEmailsFor($user, 'Activation');

			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @param $name
	 * @return object
	 */
	public function getEmailView($name) {
		// removes last "." in name if found
		$name = rtrim($name, '.');

		$settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$templateRootPath = $settings['plugin.']['tx_registerbase.']['view.']['templateRootPath'];
		$templateRootPath = GeneralUtility::getFileAbsFileName($templateRootPath);
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
		$for .= '.';
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

/***************************************************************************************************
 * Mail Chimp Functions
 **************************************************************************************************/

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $user
	 */
	public function mailChimpSubscribe(\TYPO3\RegisterBase\Domain\Model\FrontendUser $user) {
		$result = $this->mailChimp->call('lists/subscribe', array(
			'id' => $this->getMailChimpListId(),
			'email'             => array('email' => $user->getEmail()),
			'merge_vars'        => array(
				'FNAME' => $user->getFirstName(),
				'LNAME' => $user->getLastName(),
				'groupings' => array(
					array(
						'name' => 'Newsletters',
						'groups' => $user->getMailChimpGroupsArray()
					),
				),
			),
			'double_optin'      => false,
			'update_existing'   => true,
			'replace_interests' => true,
			'send_welcome'      => false,
		));
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUser $user
	 */
	public function mailChimpUnsubscribe(\TYPO3\RegisterBase\Domain\Model\FrontendUser $user) {
		$result = $this->mailChimp->call('lists/unsubscribe', array(
			'id' => $this->getMailChimpListId(),
			'email'             => array('email' => $user->getEmail()),
			'send_goodbye'      => false,
			'update_existing'   => true,
			'replace_interests' => true,
			'send_welcome'      => false,
		));
	}

	/**
	 * @param $email
	 */
	public function unsubscribeByEmail($email) {
		$user = $this->frontendUserRepository->findByEmail($email)->getFirst();
		if ($user) {
			$user->setNewsletters(FALSE);
			$this->persistenceManager->persistAll();
		}
	}

	/**
	 * @param $data array
	 */
	public function updateFromMailChimp($data) {
		$user = $this->frontendUserRepository->findByEmail($data['email'])->getFirst();
		if ($user) {
			if ($user->getFirstName() !== $data['merges']['FNAME']) {
				$user->setFirstName($data['merges']['FNAME']);
			}
			if ($user->getLastName() !== $data['merges']['LNAME']) {
				$user->setLastName($data['merges']['LNAME']);
			}
			if ($user->getMailChimpGroups() !== $data['merges']['INTERESTS']) {
				$user->setMailChimpGroups($data['merges']['INTERESTS']);
			}
			$this->persistenceManager->persistAll();
		}
	}

	/**
	 * @param $oldEmail string
	 * @param $newEmail string
	 */
	public function updateEmail($oldEmail, $newEmail) {
		$user = $this->frontendUserRepository->findByEmail($oldEmail)->getFirst();
		if ($user) {
			$user->setEmail($newEmail);
			$this->persistenceManager->persistAll();
		}
	}

	/**
	 * @param string $mailChimpApiKey
	 */
	public function setMailChimpApiKey($mailChimpApiKey) {
		$this->mailChimpApiKey = $mailChimpApiKey;
		$this->mailChimp->setApiKey($this->mailChimpApiKey);
	}

	/**
	 * @return string
	 */
	public function getMailChimpApiKey() {
		return $this->mailChimpApiKey;
	}

	/**
	 * @param string $mailChimpListId
	 */
	public function setMailChimpListId($mailChimpListId) {
		$this->mailChimpListId = $mailChimpListId;
	}

	/**
	 * @return string
	 */
	public function getMailChimpListId() {
		return $this->mailChimpListId;
	}

}