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
	 * @validate Tx_RegisterBase_Domain_Validator_UniqueEmailValidator
	 */
	protected $email;

	/**
	 * @var boolean
	 */
	protected $disable;

	/**
	 * General Terms and Conditions
	 * @var boolean
	 * @validate NotEmpty
	 */
	protected $gtc;

	/**
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_RegisterBase_Domain_Model_Category>
	 */
	protected $categories;

	/**
	 * @var string
	 */
	protected $mailHash;

	/**
	 * Should the user get newsletters?
	 *
	 * @var boolean
	 */
	protected $newsletter;

	/**
	 * If the user gets newsletters let them be in the html format
	 *
	 * @var boolean
	 */
	protected $newsletterHtmlFormat;

	/**
	 * Constructs a new Front-End User
	 */
	public function __construct($username = '', $password = '') {
		parent::__construct($username, $password);
		$this->categories = new Tx_Extbase_Persistence_ObjectStorage();
	}

	/**
	 * @param boolean $gtc
	 */
	public function setGtc($gtc) {
		$this->gtc = $gtc;
	}

	/**
	 * @return boolean
	 */
	public function getGtc() {
		return $this->gtc;
	}

	/**
	 * @param string $name
	 */
	public function setName($name = NULL) {
		if ($name === NULL) {
			parent::setName($this->getFirstName() . ' ' . $this->getLastName());
			if ($this->getName() === ' ') {
				parent::setName($this->getEmail());
			}
		} else {
			parent::setName($name);
		}
	}

	/**
	 * @param string $username
	 */
	public function setUsername($username = NULL) {
		if ($username === NULL) {
			parent::setUsername(strtolower($this->getFirstName()) . '.' . strtolower($this->getLastName()));
			if ($this->getUsername() === '.') {
				parent::setUsername($this->getEmail());
			}
		} else {
			parent::setUsername($username);
		}
	}

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

	/**
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_RegisterBase_Domain_Model_Category> $categories
	 */
	public function setCategories($categories) {
		$this->categories = $categories;
	}

	/**
	 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_RegisterBase_Domain_Model_Category>
	 */
	public function getCategories() {
		return $this->categories;
	}

	/**
	 * @param Tx_RegisterBase_Domain_Model_Category $category
	 */
	public function addCategory(Tx_RegisterBase_Domain_Model_Category $category) {
		$this->categories->attach($category);
	}

	/**
	 * @param Tx_RegisterBase_Domain_Model_Category $category
	 */
	public function removeCategory(Tx_RegisterBase_Domain_Model_Category $category) {
		$this->categories->detach($category);
	}

	/**
	 * @param Tx_RegisterBase_Domain_Model_Category $category
	 * @return boolean
	 */
	public function hasCategory(Tx_RegisterBase_Domain_Model_Category $category) {
		return $this->getCategories()->contains($category);
	}

	/**
	 * @param boolean $newsletter
	 */
	public function setNewsletter($newsletter) {
		$this->newsletter = $newsletter;
	}

	/**
	 * @return boolean
	 */
	public function getNewsletter() {
		return $this->newsletter;
	}

	/**
	 * @param boolean $newsletterHtmlFormat
	 */
	public function setNewsletterHtmlFormat($newsletterHtmlFormat) {
		$this->newsletterHtmlFormat = $newsletterHtmlFormat;
	}

	/**
	 * @return boolean
	 */
	public function getNewsletterHtmlFormat() {
		return $this->newsletterHtmlFormat;
	}

}