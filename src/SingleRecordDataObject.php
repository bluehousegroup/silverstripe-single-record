<?php

namespace BluehouseGroup\SingleRecord;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Security;
use SilverStripe\View\TemplateGlobalProvider;
use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\ORM\DataExtension;

/**
 * SingleRecordDataObject
 */
class SingleRecordDataObject extends DataExtension
{
	public $single_record = false;

	/**
	 * Get the actions that are sent to the CMS.
	 *
	 * In your extensions: updateEditFormActions($actions)
	 *
	 * @return FieldList
	 */
	public function updateCMSActions(FieldList $actions)
	{
		if ($this->owner->single_record) {
			$actions->push(FormAction::create(
					'save_singlerecorddataobject',
					_t(CMSMain::class . '.SAVE', 'Save')
				)->addExtraClass('btn-primary font-icon-save')
			);
		}
	}

	/**
	 * Get the current sites SingleRecordDataObject, and creates a new one through
	 * {@link make_single_record_data_object()} if none is found.
	 *
	 * @return SingleRecordDataObject
	 */
	public static function current_single_record_data_object($class_name)
	{
		$SingleRecordDataObject = DataObject::get_one($class_name);
		if ($SingleRecordDataObject) {
			return $SingleRecordDataObject;
		}

		return self::make_single_record_data_object($class_name);
	}

	/**
	 * Create SingleRecordDataObject with defaults from language file.
	 *
	 * @return SingleRecordDataObject
	 */
	public static function make_single_record_data_object($class_name)
	{
		$config = $class_name::create();
		$config->write();

		return $config;

		return false;
	}

	/**
	 * Add $SingleRecordDataObject to all SSViewers
	 */
	public static function get_template_global_variables($class_name)
	{
		return [
			$class_name => 'current_single_record_data_object',
		];

		return [];
	}

	public function current_single_record() {
		return self::current_single_record_data_object($this->owner->ClassName);
	}
}
