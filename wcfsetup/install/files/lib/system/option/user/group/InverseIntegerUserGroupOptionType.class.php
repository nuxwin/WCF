<?php
namespace wcf\system\option\user\group;
use wcf\system\option\IntegerOptionType;

/**
 * User group option type implementation for integer input fields.
 * 
 * The merge of option values returns the lowest value.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category	Community Framework
 */
class InverseIntegerUserGroupOptionType extends IntegerOptionType implements IUserGroupOptionType {
	/**
	 * @see	\wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge($defaultValue, $groupValue) {
		if ($defaultValue < $groupValue) {
			return null;
		}
		
		return $groupValue;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::compare()
	 */
	public function compare($value1, $value2) {
		if ($value1 == $value2) {
			return 0;
		}
		
		return ($value1 < $value2) ? 1 : -1;
	}
}
