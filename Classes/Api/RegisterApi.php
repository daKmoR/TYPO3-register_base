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
	 * @param $email
	 */
	public function unsubscribeByEmail($email) {
		$user = $this->frontendUserRepository->findByEmail($email)->getFirst();
		$user->setNewsletters(FALSE);
		$this->persistenceManager->persistAll();
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
		$user->setEmail($newEmail);
		$this->persistenceManager->persistAll();
	}

}