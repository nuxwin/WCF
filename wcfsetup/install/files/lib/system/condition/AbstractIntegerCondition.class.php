<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Abstract implementation of a condition for an integer value.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
abstract class AbstractIntegerCondition extends AbstractSingleFieldCondition {
	/**
	 * property value has to be greater than the given value
	 * @var	integer
	 */
	protected $greaterThan = null;
	
	/**
	 * identifier used for the input fields
	 * @var	string
	 */
	protected $identifier = '';
	
	/**
	 * property value has to be less than the given value
	 * @var	integer
	 */
	protected $lessThan = null;
	
	/**
	 * maximum value the property can have
	 * @var	integer
	 */
	protected $maxValue = null;
	
	/**
	 * minimum value the property can have
	 * @var	integer
	 */
	protected $minValue = null;
	
	/**
	 * name of the integer user property
	 * @var	string
	 */
	protected $propertyName = '';
	
	/**
	 * @see	\wcf\system\condition\ICondition::getData()
	 */
	public function getData() {
		$data = array();
		
		if ($this->lessThan !== null) {
			$data['lessThan'] = $this->lessThan;
		}
		if ($this->greaterThan !== null) {
			$data['greaterThan'] = $this->greaterThan;
		}
		
		if (!empty($data)) {
			return $data;
		}
		
		return null;
	}
	
	/**
	 * @see	\wcf\system\condition\AbstractMultipleFieldsCondition::getData()
	 */
	protected function getErrorMessageElement() {
		if ($this->errorMessage) {
			$errorMessage = '';
			switch ($this->errorMessage) {
				case 'wcf.condition.greaterThan.error.maxValue':
				case 'wcf.condition.lessThan.error.maxValue':
					$errorMessage = WCF::getLanguage()->getDynamicVariable($this->errorMessage, array(
						'maxValue' => $this->maxValue
					));
				break;
				
				case 'wcf.condition.greaterThan.error.minValue':
				case 'wcf.condition.lessThan.error.minValue':
					$errorMessage = WCF::getLanguage()->getDynamicVariable($this->errorMessage, array(
						'minValue' => $this->minValue
					));
				break;
				
				default:
					$errorMessage = WCF::getLanguage()->get($this->errorMessage);
				break;
			}
			
			return '<small class="innerError">'.$errorMessage.'</small>';
		}
		
		return '';
	}
	
	/**
	 * @see	\wcf\system\condition\AbstractSingleFieldCondition::getFieldElement()
	 */
	public function getFieldElement() {
		$greaterThanPlaceHolder = WCF::getLanguage()->get('wcf.condition.greaterThan');
		$lessThanPlaceHolder = WCF::getLanguage()->get('wcf.condition.lessThan');
		
		return <<<HTML
<input type="number" name="greaterThan_{$this->getIdentifier()}" value="{$this->greaterThan}" placeholder="{$greaterThanPlaceHolder}"{$this->getMinMaxAttributes('greaterThan')} />
<input type="number" name="lessThan_{$this->getIdentifier()}" value="{$this->lessThan}" placeholder="{$lessThanPlaceHolder}"{$this->getMinMaxAttributes('lessThan')} />
HTML;
	}
	
	/**
	 * Returns the identifier used for the input fields.
	 * 
	 * @return	string
	 */
	protected function getIdentifier() {
		return $this->identifier;
	}
	
	/**
	 * Returns the maximum value the property can have or null if there is no
	 * such maximum.
	 * 
	 * @return	integer
	 */
	protected function getMaxValue() {
		if ($this->getDecoratedObject()->maxvalue !== null) {
			return $this->getDecoratedObject()->maxvalue;
		}
		
		if ($this->maxValue !== null) {
			return $this->maxValue;
		}
		
		return null;
	}
	
	/**
	 * Returns the min and max attributes for the input elements.
	 * 
	 * @param	string		$type
	 * @return	string
	 */
	protected function getMinMaxAttributes($type) {
		$attributes = '';
		if ($this->getMinValue() !== null) {
			$attributes .= ' min="'.($this->getMinValue() + ($type == 'lessThan' ? 1 : 0)).'"';
		}
		if ($this->getMaxValue() !== null) {
			$attributes .= ' max="'.($this->getMaxValue() - ($type == 'lessThan' ? 1 : 0)).'"';
		}
		
		return $attributes;
	}
	
	/**
	 * Returns the minimum value the property can have or null if there is no
	 * such minimum.
	 * 
	 * @return	integer
	 */
	protected function getMinValue() {
		if ($this->getDecoratedObject()->minvalue !== null) {
			return $this->getDecoratedObject()->minvalue;
		}
		
		if ($this->minValue !== null) {
			return $this->minValue;
		}
		
		return null;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST['lessThan_'.$this->getIdentifier()]) && strlen($_POST['lessThan_'.$this->getIdentifier()])) $this->lessThan = intval($_POST['lessThan_'.$this->getIdentifier()]);
		if (isset($_POST['greaterThan_'.$this->getIdentifier()]) && strlen($_POST['greaterThan_'.$this->getIdentifier()])) $this->greaterThan = intval($_POST['greaterThan_'.$this->getIdentifier()]);
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::reset()
	 */
	public function reset() {
		$this->lessThan = null;
		$this->greaterThan = null;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::setData()
	 */
	public function setData(Condition $condition) {
		$this->lessThan = $condition->lessThan;
		$this->greaterThan = $condition->greaterThan;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::validate()
	 */
	public function validate() {
		if ($this->lessThan !== null) {
			if ($this->getMinValue() !== null && $this->lessThan <= $this->getMinValue()) {
				$this->errorMessage = 'wcf.condition.lessThan.error.minValue';
				
				throw new UserInputException('lessThan', 'minValue');
			}
			else if ($this->getMaxValue() !== null && $this->lessThan > $this->getMaxValue()) {
				$this->errorMessages['lessThan'] = 'wcf.condition.lessThan.error.maxValue';
				
				throw new UserInputException('lessThan', 'maxValue');
			}
		}
		if ($this->greaterThan !== null) {
			if ($this->getMinValue() !== null && $this->greaterThan < $this->getMinValue()) {
				$this->errorMessages['greaterThan'] = 'wcf.condition.greaterThan.error.minValue';
				
				throw new UserInputException('greaterThan', 'minValue');
			}
			else if ($this->getMaxValue() !== null && $this->greaterThan >= $this->getMaxValue()) {
				$this->errorMessages['greaterThan'] = 'wcf.condition.greaterThan.error.maxValue';
				
				throw new UserInputException('greaterThan', 'maxValue');
			}
		}
		
		if ($this->lessThan !== null && $this->greaterThan !== null && $this->greaterThan + 1 >= $this->lessThan) {
			$this->errorMessage = 'wcf.condition.greaterThan.error.lessThan';
			
			throw new UserInputException('greaterThan', 'lessThan');
		}
	}
}
