<?php
namespace TYPO3\RegisterBase\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Thomas Allmer <d4kmor@gmail.com>
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
class FrontendUserRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository {

	/**
	 * @param $mailHash
	 * @return \TYPO3\RegisterBase\Domain\Model\FrontendUser
	 */
	public function findByMailHash($mailHash) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectEnableFields(FALSE);

		$user = $query
			->matching(
				$query->equals('mailhash', $mailHash)
			)
			->setLimit(1)
			->execute()
			->getFirst();

		return $user;
	}

	/**
	 * @param $email
	 * @return \TYPO3\RegisterBase\Domain\Model\FrontendUser
	 */
	public function findByEmail($email) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);

		$user = $query
			->matching(
				$query->equals('email', $email)
			)
			->setLimit(1)
			->execute()
			->getFirst();

		return $user;
	}

}