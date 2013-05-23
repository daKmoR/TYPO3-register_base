<?php
namespace TYPO3\RegisterBase\Domain\Model;

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
class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser {

	/**
	 * @var string
	 * @validate notEmpty
	 * @validate EmailAddress
	 */
	protected $email;

	/**
	 * @var boolean
	 */
	protected $disable;

	/**
	 * @var string
	 * @validate notEmpty
	 */
	protected $username;

	/**
	 * @var string
	 * @validate notEmpty
	 */
	protected $password;

	/**
	 * General Terms and Conditions
	 *
	 * @var boolean
	 * @validate RegularExpression(regularExpression=/1/)
	 */
	protected $gtc = FALSE;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<Tx_RegisterBase_Domain_Model_Category>
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
	protected $newsletters;

	/**
	 * If the user gets newsletters let them be in the html format
	 *
	 * @var boolean
	 */
	protected $newsletterHtmlFormat;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\RegisterBase\Domain\Model\FrontendUserGroup>
	 */
	protected $usergroup;

	/**
	 * @var string
	 */
	protected $mailChimpGroups;

	/**
	 * @var array
	 */
	protected $mailChimpGroupsArray;

	/**
	 * @var string
	 * @validate notEmpty
	 */
	protected $country;

	/**
	 * Constructs a new Front-End User
	 */
	public function __construct($username = '', $password = '') {
		parent::__construct($username, $password);
		$this->categories = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

	/**
	 * @param string $mailChimpGroups
	 */
	public function setMailChimpGroups($mailChimpGroups) {
		$this->mailChimpGroups = $mailChimpGroups;
	}

	/**
	 * @return string
	 */
	public function getMailChimpGroups() {
		return $this->mailChimpGroups;
	}

	/**
	 * @return array
	 */
	public function getMailChimpGroupsArray() {
		return array_map('trim', explode(',', $this->getMailChimpGroups()));
	}

	/**
	 * @param array $mailChimpGroupsArray
	 */
	public function setMailChimpGroupsArray($mailChimpGroupsArray) {
		$array = implode(',', $mailChimpGroupsArray);
		$this->setMailChimpGroups($array);
		$this->mailChimpGroupsArray = $array;
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
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\RegisterBase\Domain\Model\Category> $categories
	 */
	public function setCategories($categories) {
		$this->categories = $categories;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\RegisterBase\Domain\Model\Category>
	 */
	public function getCategories() {
		return $this->categories;
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\Category $category
	 */
	public function addCategory(\TYPO3\RegisterBase\Domain\Model\Category $category) {
		$this->categories->attach($category);
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\Category $category
	 */
	public function removeCategory(\TYPO3\RegisterBase\Domain\Model\Category $category) {
		$this->categories->detach($category);
	}

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\Category $category
	 * @return boolean
	 */
	public function hasCategory(\TYPO3\RegisterBase\Domain\Model\Category $category) {
		return $this->getCategories()->contains($category);
	}

	/**
	 * @param boolean $newsletter
	 */
	public function setNewsletters($newsletter) {
		$this->newsletters = $newsletter;
	}

	/**
	 * @return boolean
	 */
	public function getNewsletters() {
		return $this->newsletters;
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

	/**
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUserGroup $frontendUserGroup
	 * @return boolean
	 */
	public function hasFrontendUserGroup(\TYPO3\RegisterBase\Domain\Model\FrontendUserGroup $frontendUserGroup) {
		return $this->getUsergroup()->contains($frontendUserGroup);
	}

}