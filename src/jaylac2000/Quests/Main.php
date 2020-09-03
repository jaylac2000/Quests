<?php

namespace jaylac2000\Quests;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use jaylac2000\Quests\commands\QuestCommand;

class Main extends PluginBase
{

	private $config;
	private $categories = [];
	private $quests;
	public static $instance;

	public function onLoad() : void
	{
		self::$instance = $this;
	}

	public function onEnable() : void
	{
		@mkdir($this->getDataFolder());
		if(!file_exists($this->getDataFolder()."config.yml")) $this->saveResource("config.yml");
		if(!file_exists($this->getDataFolder()."data.db")) $this->saveResource("data.db");
		$this->config = new Config($this->getDataFolder()."config.yml", Config::YAML);
		$this->initQuests();
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getServer()->getCommandMap()->register("quest", new QuestCommand($this, "quest"));
	}

	public function initQuests()
	{
		$config = $this->config;
		$quests;

		foreach ($config->get("categories") as $category) {
			foreach ($category as $key => $value) {
				$this->quests[$key] = $value;
			}
		}
		foreach ($config->getNested("categories") as $category => $value){
			$quests[$category] = array();
			foreach (array_keys($value) as $tadaronne) {
				array_push($quests[$category], $tadaronne);
			}
		}
		$this->categories = $quests;
		QuestManager::initDb();
	}

	public static function getInstance()
	{
		return self::$instance;
	}

	public function getCategories()
	{
		return $this->categories;
	}

	public function getQuests()
	{
		return $this->quests;
	}

	public function getConfig() : Config
	{
		return $this->config;
	}
}