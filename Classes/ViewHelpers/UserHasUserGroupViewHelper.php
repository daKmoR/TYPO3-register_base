<?php
namespace TYPO3\RegisterBase\ViewHelpers;

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
 * This ViewHelper checks if a user has a certain frontendUserGroup
 *
 * = Examples =
 *
 * <code title="Show only if of user has the frontendUserGroup">
 * <f:if condition="{r:userHasUserGroup(user: frontendUser, frontendUserGroup: frontendUserGroup)}">
 *   show only user has the frontendUserGroup
 * </f:if>
 * </code>
 * <output>
 * show only if condition is met
 * </output>
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class UserHasUserGroupViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Check if the object has the frontendUserGroup
	 *
	 * @param object $user The Object to check
	 * @param \TYPO3\RegisterBase\Domain\Model\FrontendUserGroup $frontendUserGroup
	 * @return boolean
	 */
	public function render($user, \TYPO3\RegisterBase\Domain\Model\FrontendUserGroup $frontendUserGroup) {
		if ($user->hasFrontendUserGroup($frontendUserGroup)) {
			return TRUE;
		}
		return FALSE;
	}

}