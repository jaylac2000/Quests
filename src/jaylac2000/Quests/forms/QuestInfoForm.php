<?php

namespace jaylac2000\Quests\forms;

use jaylac2000\Quests\{
	Main,
	QuestManager
};

use pocketmine\Player;

use jojoe77777\FormAPI\ModalForm;

class QuestInfoForm extends ModalForm
{

	public function __construct(?callable $callable, Player $player, int $categoryId, int $questId){
		parent::__construct($callable);

		$questName = QuestManager::getQuestNameById($questId, $categoryId);
		$questData = QuestManager::getQuestInfoById($categoryId, $questName);

		$this->setTitle($questData["name"]);
		$this->setContent($questData["description"]);

		if(QuestManager::isCurrent($player->getName(), $questName)){
			$this->setButton1(Main::getInstance()->getConfig()->get("button-info-pause"));
		} else {
			$this->setButton1(Main::getInstance()->getConfig()->get("button-info-start"));
		}
		if(!is_null(Main::getInstance()->getConfig()->get("button-leave"))){
			$this->setButton2(Main::getInstance()->getConfig()->get("button-leave"));
		}
	}
}