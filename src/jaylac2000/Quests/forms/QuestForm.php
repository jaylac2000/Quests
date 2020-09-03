<?php

namespace jaylac2000\Quests\forms;

use jaylac2000\Quests\{
	Main,
	QuestManager
};

use pocketmine\Player;

use jojoe77777\FormAPI\SimpleForm;

class QuestForm extends SimpleForm
{

	public function __construct(?callable $callable, Player $player, int $category){
		parent::__construct($callable);
		$categoryName = QuestManager::getCategory($category);
		$categories = Main::getInstance()->getCategories();
		foreach ($categories as $cat => $quests) {
			foreach (array_values($quests) as $id => $questName) {
			    if($cat === $categoryName){
			        $questName = QuestManager::getQuestNameById($id, $category);
					$questData = QuestManager::getQuestInfoById($category, $questName);

			        if(QuestManager::isCurrent($player->getName(), $questName)) { 
			        	$statut = Main::getInstance()->getConfig()->get("quest-inprogress");
			        } elseif(QuestManager::isCompleted($player->getName(), $questName)){
			        	$statut = Main::getInstance()->getConfig()->get("quest-finished"); 
			        } else {
			        	$statut = Main::getInstance()->getConfig()->get("quest-opened");
			        }

			        	$this->addButton($questData["name"] . "\n" . $statut, 0);
			    }
			}
		}
		if(!is_null(Main::getInstance()->getConfig()->get("category-select-title"))){
			$this->setTitle(Main::getInstance()->getConfig()->get("category-select-title"));
		}
		if(!is_null(Main::getInstance()->getConfig()->get("quest-select-content"))){
			$this->setContent(Main::getInstance()->getConfig()->get("quest-select-content"));
		}
	}
}