<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Thomas Allmer <at@delusionworld.com>
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
 * This ViewHelper checks if the given user has the category
 *
 * = Examples =
 *
 * <code title="Show only if of type Tx_Assets_Domain_Model_Youtube">
 * <f:if condition="{r:userHasCategory(user: frontendUser, category: category)}">
 *   show only user has the category
 * </f:if>
 * </code>
 * <output>
 * show only if condition is met
 * </output>
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Tx_RegisterBase_ViewHelpers_UserHasCategoryViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Check if the object has the category
	 *
	 * @param object $user The Object to check
	 * @param Tx_RegisterBase_Domain_Model_Category $category
	 * @return boolean
	 * @author Thomas Allmer <at@delusionworld.com>
	 */
	public function render($user, Tx_RegisterBase_Domain_Model_Category $category) {
		if ($user->hasCategory($category)) {
			return TRUE;
		}
		return FALSE;

	}
}
