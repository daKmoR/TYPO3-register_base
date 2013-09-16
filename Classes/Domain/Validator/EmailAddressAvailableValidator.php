<?php
namespace TYPO3\RegisterBase\Domain\Validator;

/***************************************************************
*  Copyright notice
*
*  (c) 2012 Thomas Allmer <d4kmor@gmail.com>
*  All rights reserved
*
*  This class is a backport of the corresponding class of FLOW3.
*  All credits go to the v5 team.
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
 * Validator for email to be unique
 *
 * @package Extbase
 * @subpackage Validation\Validator
 * @version $Id$
 */
class EmailAddressAvailableValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {

	/**
	 * @var \TYPO3\RegisterBase\Domain\Repository\FrontendUserRepository
	 * @inject
	 */
	protected $frontendUserRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator
	 * @inject
	 */
	protected $emailAddressValidator;

	/**
	 * Checks if the new frontendUser email is available
	 *
	 * @param mixed $frontendUser The value that should be validated
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 */
	public function isValid($frontendUser) {
		$result = TRUE;

		if (!$this->emailAddressValidator->isValid($frontendUser->getEmail())) {
			// do not add this Error for invalid e-mail addresses
			return TRUE;
		}

		$foundFrontendUser = $this->frontendUserRepository->findOneByEmail($frontendUser->getEmail());
		if ($foundFrontendUser && $foundFrontendUser->getUid() !== $frontendUser->getUid()) {
			if ($this->result) {
				$this->result->forProperty('email')->addError(
					new \TYPO3\CMS\Extbase\Error\Error('E-Mail is already taken.', 1360850585)
				);
			}
			$result = FALSE;
		}

		return $result;
	}

}