<?php

namespace BluehouseGroup\SingleRecord;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Versioned\RecursivePublishable;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;

class SingleRecordModelAdmin extends LeftAndMain
{
		/**
		 * @var string
		 */
		private static $url_segment = 'setting';

		/**
		 * @var string
		 */
		private static $url_rule = '/$Action/$ID/$OtherID';

		/**
		 * @var int
		 */
		private static $menu_priority = -1;

		/**
		 * @var string
		 */
		private static $menu_title = null;

		/**
		 * @var string
		 */
		private static $menu_icon_class = 'font-icon-cog';

		/**
		 * @var string
		 */
		protected $tree_class;

		/**
		 * Initialises the {@link SingleRecordDataObject} controller.
		 */
		public function init()
		{
				parent::init();
				if (class_exists(SiteTree::class)) {
						Requirements::javascript('silverstripe/cms: client/dist/js/bundle.js');
				}
		}

		/**
		 * @param null $id Not used.
		 * @param null $fields Not used.
		 *
		 * @return Form
		 */
		public function getEditForm($id = null, $fields = null)
		{
				$class_name = $this->tree_class;
				$class_object = new $class_name();
				$SingleRecordDataObject = $class_object->current_single_record();
				$fields = $SingleRecordDataObject->getCMSFields();

				// Tell the CMS what URL the preview should show
				$home = Director::absoluteBaseURL();
				$fields->push(new HiddenField('PreviewURL', 'Preview URL', $home));

				// Added in-line to the form, but plucked into different view by LeftAndMain.Preview.js upon load
				/** @skipUpgrade */
				$fields->push($navField = new LiteralField('SilverStripeNavigator', $this->getSilverStripeNavigator()));
				$navField->setAllowHTML(true);

				// Retrieve validator, if one has been setup (e.g. via data extensions).
				if ($SingleRecordDataObject->hasMethod("getCMSValidator")) {
						$validator = $SingleRecordDataObject->getCMSValidator();
				} else {
						$validator = null;
				}

				$actions = $SingleRecordDataObject->getCMSActions();
				$negotiator = $this->getResponseNegotiator();
				/** @var Form $form */
				$form = Form::create(
						$this,
						'EditForm',
						$fields,
						$actions,
						$validator
				)->setHTMLID('Form_EditForm');
				$form->setValidationResponseCallback(function (ValidationResult $errors) use ($negotiator, $form) {
						$request = $this->getRequest();
						if ($request->isAjax() && $negotiator) {
								$result = $form->forTemplate();
								return $negotiator->respond($request, array(
										'CurrentForm' => function () use ($result) {
												return $result;
										}
								));
						}
				});
				$form->addExtraClass('flexbox-area-grow fill-height cms-content cms-edit-form');
				$form->setAttribute('data-pjax-fragment', 'CurrentForm');

				if ($form->Fields()->hasTabSet()) {
						$form->Fields()->findOrMakeTab('Root')->setTemplate('SilverStripe\\Forms\\CMSTabSet');
				}
				$form->setHTMLID('Form_EditForm');
				$form->loadDataFrom($SingleRecordDataObject);
				$form->setTemplate($this->getTemplatesWithSuffix('_EditForm'));

				// Use <button> to allow full jQuery UI styling
				$actions = $actions->dataFields();
				if ($actions) {
						/** @var FormAction $action */
						foreach ($actions as $action) {
								$action->setUseButtonTag(true);
						}
				}

				$this->extend('updateEditForm', $form);

				return $form;
		}

		/**
		 * Save the current sites {@link SiteConfig} into the database.
		 *
		 * @param array $data
		 * @param Form $form
		 * @return String
		 */
		public function save_singlerecorddataobject($data, $form)
		{
				$data = $form->getData();
				$SingleRecordDataObject = DataObject::get_by_id($this->tree_class, $data['ID']);
				$form->saveInto($SingleRecordDataObject);
				$SingleRecordDataObject->write();
				if ($SingleRecordDataObject->hasExtension(RecursivePublishable::class)) {
						$SingleRecordDataObject->publishRecursive();
				}
				$this->response->addHeader('X-Status', rawurlencode(_t(LeftAndMain::class . '.SAVEDUP', 'Saved.')));
				return $form->forTemplate();
		}


		public function Breadcrumbs($unlinked = false)
		{
				return new ArrayList(array(
						new ArrayData(array(
								'Title' => static::menu_title(),
								'Link' => $this->Link()
						))
				));
		}
}
