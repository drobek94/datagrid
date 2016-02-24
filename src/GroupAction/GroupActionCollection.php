<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\GroupAction;

use Nette;
use Nette\Application\UI\Form;

class GroupActionCollection extends Nette\Object
{

	const ID_ATTRIBUTE_PREFIX = 'group_action_item_';


	/**
	 * @var GroupAction[]
	 */
	protected $group_actions = [];


	/**
	 * Get assambled form
	 * @param Nette\Forms\Container $container
	 * @return void
	 */
	public function addToFormContainer($container)
	{
		$form = $container->lookup('Nette\Application\UI\Form');
		$translator = $form->getTranslator();

		/**
		 * First foreach for filling "main" select
		 */
		foreach ($this->group_actions as $id => $action) {
			$main_options[$id] = $action->getTitle();
		}

		$container->addSelect('group_action', '', $main_options)
			->setPrompt($translator->translate('ublaboo_datagrid.choose'));

		/**
		 * Second for creating select for each "sub"-action
		 */
		foreach ($this->group_actions as $id => $action) {
			if ($action->hasOptions()) {
				$container->addSelect($id, '', $action->getOptions())
					->setAttribute('id', static::ID_ATTRIBUTE_PREFIX.$id);
			}
		}

		foreach ($this->group_actions as $id => $action) {
			$container['group_action']->addCondition(Form::EQUAL, $id)
				->toggle(static::ID_ATTRIBUTE_PREFIX.$id);
		}

		$container['group_action']->addCondition(Form::FILLED)
			->toggle('group_action_submit');

		$container->addSubmit('submit', $translator->translate('ublaboo_datagrid.execute'))
			->setAttribute('id', 'group_action_submit');

		$form->onSubmit[] = [$this, 'submitted'];
	}


	/**
	 * Pass "sub"-form submission forward to custom submit function
	 * @param  Form   $form
	 * @return void
	 */
	public function submitted(Form $form)
	{
		if (!isset($form['group_action']['submit']) || !$form['group_action']['submit']->isSubmittedBy()) {
			return;
		}

		$values = $form->getValues();
		$values = $values['group_action'];

		if ($values->group_action === 0 || is_null($values->group_action)) {
			return;
		}

		/**
		 * @todo Define items IDs
		 */
		$http_ids = $form->getHttpData(Form::DATA_LINE|Form::DATA_KEYS, 'group_action_item[]');
		$ids = array_keys($http_ids);

		$id = $values->group_action;
		$this->group_actions[$id]->onSelect($ids, isset($values->{$id}) ? $values->{$id} : NULL);

		$form['group_action']['group_action']->setValue(NULL);
	}


	/**
	 * Add one group action to collection of actions
	 * @param string $title
	 * @param array $options
	 */
	public function addGroupAction($title, $options)
	{
		$id = ($s = sizeof($this->group_actions)) ? ($s + 1) : 1;

		return $this->group_actions[$id] = new GroupAction($title, $options);
	}

}
