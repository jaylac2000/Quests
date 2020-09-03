<?php

namespace jaylac2000\Quests\commands;

use pocketmine\{
	Player,
	command\Command,
	command\CommandSender
};

use jaylac2000\Quests\{
	Main,
	QuestManager,
	forms\CategoryForm,
   forms\QuestForm,
   forms\QuestInfoForm
};

class QuestCommand extends Command
{

	private $plugin;
	private $category;

	public function __construct(Main $plugin, string $name){
		parent::__construct($name, "Ouvrir le menu des quêtes");
		$this->plugin = $plugin;
		$this->setDescription("Ouvrir le menu des quêtes");
	}

	public function execute(CommandSender $sender, string $label, array $args)
	{
		if(!$sender instanceof Player) return;

		$form = new CategoryForm(function (Player $player, int $data = null){
			if(is_null($data)) return;
			$this->category[$player->getName()] = $data;

			$form = new QuestForm(function(Player $player, int $data = null){
				if(is_null($data)) return;
				$this->quest[$player->getName()] = QuestManager::getQuestNameById($data, $this->category[$player->getName()]);
				if(QuestManager::isCompleted($player->getName(), $this->quest[$player->getName()])) return $player->sendMessage($this->plugin->getConfig()->get("quest-already-finished"));

				$form = new QuestInfoForm(function(Player $player, bool $data = null){
					if(is_null($data)) return;
					if($data) QuestManager::updateQuest($player, $this->category[$player->getName()], $this->quest[$player->getName()]);
				}, $player, $this->category[$player->getName()], $data);
				$player->sendForm($form);
			}, $player, $data);
			$player->sendForm($form);
		});
		$sender->sendForm($form);
	}
}