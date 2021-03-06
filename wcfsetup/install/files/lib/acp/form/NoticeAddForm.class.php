<?php
namespace wcf\acp\form;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\notice\NoticeAction;
use wcf\data\notice\NoticeEditor;
use wcf\form\AbstractForm;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the form to create a new notice.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class NoticeAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.notice.add';
	
	/**
	 * grouped notice condition object types
	 * @var	array
	 */
	public $groupedConditionObjectTypes = array();
	
	/**
	 * 1 if the notice is disabled
	 * @var	integer
	 */
	public $isDisabled = 0;
	
	/**
	 * 1 if the notice is dismissible
	 * @var	integer
	 */
	public $isDismissible = 0;
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.notice.canManageNotice');
	
	/**
	 * name of the notice
	 * @var	string
	 */
	public $noticeName = '';
	
	/**
	 * 1 if html is used in the notice text
	 * @var	integer
	 */
	public $noticeUseHtml = 0;
	
	/**
	 * order used to the show the notices
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'isDisabled' => $this->isDisabled,
			'isDismissible' => $this->isDismissible,
			'groupedConditionObjectTypes' => $this->groupedConditionObjectTypes,
			'noticeName' => $this->noticeName,
			'noticeUseHtml' => $this->noticeUseHtml,
			'showOrder' => $this->showOrder
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.condition.notice');
		foreach ($objectTypes as $objectType) {
			if (!$objectType->conditionobject) continue;
			
			if (!isset($this->groupedConditionObjectTypes[$objectType->conditionobject])) {
				$this->groupedConditionObjectTypes[$objectType->conditionobject] = array();
			}
			
			if ($objectType->conditiongroup) {
				if (!isset($this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->conditiongroup])) {
					$this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->conditiongroup] = array();
				}
				
				$this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
			}
			else {
				$this->groupedConditionObjectTypes[$objectType->conditionobject][$objectType->objectTypeID] = $objectType;
			}
		}
		
		parent::readData();
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (isset($_POST['isDisabled'])) $this->isDisabled = 1;
		if (isset($_POST['isDismissible'])) $this->isDismissible = 1;
		if (isset($_POST['noticeName'])) $this->noticeName = StringUtil::trim($_POST['noticeName']);
		if (isset($_POST['noticeUseHtml'])) $this->noticeUseHtml = 1;
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		
		foreach ($this->groupedConditionObjectTypes as $groupedObjectTypes) {
			foreach ($groupedObjectTypes as $objectTypes) {
				if (is_array($objectTypes)) {
					foreach ($objectTypes as $objectType) {
						$objectType->getProcessor()->readFormParameters();
					}
				}
				else {
					$objectTypes->getProcessor()->readFormParameters();
				}
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('notice');
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new NoticeAction(array(), 'create', array(
			'data' => array_merge($this->additionalFields, array(
				'isDisabled' => $this->isDisabled,
				'isDismissible' => $this->isDismissible,
				'notice' => I18nHandler::getInstance()->isPlainValue('notice') ? I18nHandler::getInstance()->getValue('notice') : '',
				'noticeName' => $this->noticeName,
				'noticeUseHtml' => $this->noticeUseHtml,
				'showOrder' => $this->showOrder
			))
		));
		$returnValues = $this->objectAction->executeAction();
		
		if (!I18nHandler::getInstance()->isPlainValue('notice')) {
			I18nHandler::getInstance()->save('notice', 'wcf.notice.notice.notice'.$returnValues['returnValues']->noticeID, 'wcf.notice', 1);
			
			// update notice name
			$noticeEditor = new NoticeEditor($returnValues['returnValues']);
			$noticeEditor->update(array(
				'notice' => 'wcf.notice.notice.notice'.$returnValues['returnValues']->noticeID
			));
		}
		
		// transform conditions array into one-dimensional array
		$conditions = array();
		foreach ($this->groupedConditionObjectTypes as $groupedObjectTypes) {
			foreach ($groupedObjectTypes as $objectTypes) {
				if (is_array($objectTypes)) {
					$conditions = array_merge($conditions, $objectTypes);
				}
				else {
					$conditions[] = $objectTypes;
				}
			}
		}
		
		ConditionHandler::getInstance()->createConditions($returnValues['returnValues']->noticeID, $conditions);
		
		$this->saved();
		
		// reset values
		$this->isDisabled = 0;
		$this->isDismissible = 0;
		$this->noticeName = '';
		$this->noticeUseHtml = 0;
		$this->showOrder = 0;
		I18nHandler::getInstance()->reset();
		
		foreach ($conditions as $condition) {
			$condition->getProcessor()->reset();
		}
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->noticeName)) {
			throw new UserInputException('noticeName');
		}
		
		if (!I18nHandler::getInstance()->validateValue('notice')) {
			if (I18nHandler::getInstance()->isPlainValue('notice')) {
				throw new UserInputException('notice');
			}
			else {
				throw new UserInputException('notice', 'multilingual');
			}
		}
		
		foreach ($this->groupedConditionObjectTypes as $groupedObjectTypes) {
			foreach ($groupedObjectTypes as $objectTypes) {
				if (is_array($objectTypes)) {
					foreach ($objectTypes as $objectType) {
						$objectType->getProcessor()->validate();
					}
				}
				else {
					$objectTypes->getProcessor()->validate();
				}
			}
		}
	}
}
