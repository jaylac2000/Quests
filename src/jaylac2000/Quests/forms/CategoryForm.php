<?php

namespace jaylac2000\Quests\forms;

use jaylac2000\Quests\{
	Main,
	QuestManager
};

use jojoe77777\FormAPI\SimpleForm;

class CategoryForm extends SimpleForm
{

	public function __construct(?callable $callable){
		parent::__construct($callable);
		$categories = QuestManager::getCategoriesName();
		foreach ($categories as $category) {
			$this->addButton($category, 0);
		}
		if(!is_null(Main::getInstance()->getConfig()->get("category-select-title"))){
			$this->setTitle(Main::getInstance()->getConfig()->get("category-select-title"));
		}
		if(!is_null(Main::getInstance()->getConfig()->get("category-select-content"))){
			$this->setContent(Main::getInstance()->getConfig()->get("category-select-content"));
		}
	}
}