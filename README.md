Silverstripe Single Record
=====================
A module for managing a data object that has only a single record (similar to site config).

## Features
* Manage a data object with a single record through a model admin similar to site config.

## Requirements
* SilverStripe 4+

### Install with Composer  
	composer require bluehousegroup/silverstripe-single-record

## Example Usage

	<?php //MySetting.php

	use ...

	class MySetting extends DataObject
	{
		private static $db = [
			"MyField" => "Varchar(255)",
		];

		private static $table_name = 'MySetting';

		public $single_record = true;

		public function getCMSFields()
		{
			$fields = new FieldList(
				new TabSet(
					"Root",
					$tabMySettings = new Tab(
						'My Settings',
						$myField = new TextField("MyField", _t(self::class . '.MYFIELD', "My Field"))
					)
				),
				new HiddenField('ID')
			);

			$tabMySettings->setTitle(_t(self::class . '.TABMYSETTINGS', "My Settings"));
			$this->extend('updateCMSFields', $fields);

			return $fields;
		}
	}
#
	<?php //MySettingAdmin.php

	use ...
	use BluehouseGroup\SingleRecord\SingleRecordModelAdmin;

	class MySettingAdmin extends SingleRecordModelAdmin
	{
		private static $url_segment = 'my-settings';

		private static $menu_title = 'My Settings';

		protected $tree_class = 'MySetting';
	}

