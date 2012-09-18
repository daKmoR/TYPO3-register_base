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
class Tx_RegisterBase_Domain_Model_FrontendUser extends Tx_Extbase_Domain_Model_FrontendUser {

	/**
	 * @var string
	 * @validate NotEmpty
	 * @validate EmailAddress
	 */
	protected $email;

	/**
	 * @var boolean
	 */
	protected $disable;

	/**
	 * @var string
	 */
	protected $mailHash;

	/**
	 * @param string $mailHash
	 */
	public function setMailHash($mailHash) {
		$this->mailHash = $mailHash;
	}

	/**
	 * @return string
	 */
	public function getMailHash() {
		return $this->mailHash;
	}

	/**
	 * @param boolean $disable
	 */
	public function setDisable($disable) {
		$this->disable = $disable;
	}

	/**
	 * @return boolean
	 */
	public function getDisable() {
		return $this->disable;
	}

	/**
	 * Disable the FrontendUser
	 */
	public function disable() {
		$this->setDisable(TRUE);
	}

	/**
	 * Enable the FrontendUser
	 */
	public function enable() {
		$this->setDisable(FALSE);
	}

	/**
	 * @return bool
	 */
	public function isEnabled() {
		return $this->getDisable() === TRUE ? FALSE : TRUE;
	}

}