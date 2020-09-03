<?php

namespace jaylac2000\Quests;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\command\ConsoleCommandSender;

class QuestManager
{

	public static function getQuestInfoById(int $id, string $name)
	{
		$cat = array_values(Main::getInstance()->getCategories());
		$quests = Main::getInstance()->getQuests();

		foreach($cat[$id] as $questName){
			if($name === $questName) return $quests[$questName]; 
		}
	}

	public static function getQuestInfo(string $name)
	{
		$quests = Main::getInstance()->getQuests();

		return $quests[$name];
	}

	public static function getQuestNameById(int $questId, int $categoryId)
	{
		$quests = Main::getInstance()->getQuests();
		$category = array_values(Main::getInstance()->getCategories());

		foreach ($category[$categoryId] as $key => $name) {
			if($key === $questId) return $name;
		}
	}

	public static function getCategoriesName()
	{
		$categories = array_keys(Main::getInstance()->getCategories());
		$return = [];
		foreach ($categories as $name) {
			array_push($return, $name);
		}
		return $return;
	}
	
	public static function getCategory(int $id){
	    $categories = array_keys(Main::getInstance()->getCategories());
	    return $categories[$id];
	}

	public static function getUserData(string $player)
	{
		$db = self::getDb();

		$result = $db->query("SELECT * FROM users WHERE name='$player'");
		$array = $result->fetchArray(SQLITE3_ASSOC);
		return $array;
	}

	public static function getUserQuestData(string $player, string $quest)
	{
		$db = self::getDb();

		$result = $db->query("SELECT * FROM $quest WHERE user='$player'");
		$array = $result->fetchArray(SQLITE3_ASSOC);
		return $array;
	}

	public static function numRows(\SQLite3Result $res){
		$num = 0;
		$res->reset();
		while ($res->fetchArray()) $num++;
		return $num;
	}
	
	public static function setCompleted(string $player, string $quest){
	    $db = self::getDb();
	    $db->exec("UPDATE $quest SET progress='FINISHED' WHERE user='$player'");
	}
	
	public static function isCompleted(string $player, string $quest){
	    $db = self::getDb();
	    $res = $db->query("SELECT * FROM $quest WHERE user='$player'");
	    $array = $res->fetchArray();

	    if($array["progress"] === "FINISHED"){
	        return true;
	    }
	    else
	    {
	        return false;
	    }
	}

	public static function incrementProgress(Player $player, string $quest){
		$db = self::getDb();
		$name = $player->getName();
		$usrData = self::getUserData($player->getName());
		$questData = self::getQuestInfo($quest);
		$n = self::getProgress($player->getName(), $quest) + 1;
		if($n >= $questData["number"]){
			self::setCompleted($player->getName(), $quest);
			self::resetQuest($player->getName());
			$str = str_replace("{QUEST}", $questData["name"], Main::getInstance()->getConfig()->get("finished-quest-message"));
			$player->sendMessage($str);
			foreach ($questData["rewards"] as $command) {
				$cmd = str_replace("{PLAYER}", $name, $command);
				Main::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), $cmd);
			}
		} else {
			$db->exec("UPDATE $quest SET progress = progress + 1 WHERE user='$name'");
		}
	}

	public static function updateQuest(Player $player, int $category, string $quest){
		$usrData = self::getUserData($player->getName());
		$questData = self::getQuestInfoById($category, $quest);

		if($usrData["current"] === $quest){
			self::resetQuest($player->getName());
			$str = str_replace("{QUEST}", $questData["name"], Main::getInstance()->getConfig()->get("paused-quest"));
			$player->sendMessage($str);
		} else 
		{
			self::setCurrent($player->getName(), $quest);
			$str = str_replace("{QUEST}", $questData["name"], Main::getInstance()->getConfig()->get("started-quest"));
			$player->sendMessage($str);
		}
	}

	public static function registerUser(string $name){
		$db = self::getDb();
		$quests = Main::getInstance()->getQuests();
		$queries = [];

		foreach ($quests as $questName => $value) {
			$result = $db->query("SELECT * FROM $questName WHERE user ='$name'");
			if(self::numRows($result) <= 0){
				$db->exec("INSERT INTO $questName(user, progress) VALUES ('$name', 0)");
				Main::getInstance()->getLogger()->info("Register " . $name . " in quest " . $questName);
			}
		}

		$result = $db->query("SELECT * FROM users WHERE name='$name'");
		if(self::numRows($result) <= 0){
			$db->exec(("INSERT INTO users(name, current) VALUES ('$name', NULL)"));
			Main::getInstance()->getLogger()->info("Register " . $name);
		}
	}

	public static function getProgress(string $player, string $quest)
	{
		$db = self::getDb();

		$res = $db->query("SELECT * FROM $quest WHERE user='$player'");
		$arr = $res->fetchArray();
		return $arr["progress"];
	}

	public static function setCurrent(string $player, string $quest)
	{
		$db = self::getDb();

		$db->exec("UPDATE users SET current='$quest' WHERE name='$player'");
	}

	public static function resetQuest(string $player)
	{
		$db = self::getDb();

		$db->exec("UPDATE users SET current=NULL WHERE name='$player'");
	}

	public static function isCurrent(string $player, string $name)
	{
		$data = self::getUserData($player);
		if($data["current"] === $name){
			return true;
		} else {
			return false;
		}
	}

	public static function initDb()
	{
		$db = self::getDb();
		$quests = Main::getInstance()->getQuests();
		$queries = [
			"CREATE TABLE IF NOT EXISTS users(name VARCHAR(255), current TEXT)"
		];

		foreach ($quests as $name => $value) {
			array_push($queries, "CREATE TABLE IF NOT EXISTS " . $name . "(user VARCHAR(255), progress INT)");
		}

		foreach ($queries as $query) {
			$db->exec($query);
		}
	}

	public static function getDb()
	{
		$db = new \SQLite3(Main::getInstance()->getDataFolder() . "data.db");
		return $db;
	}
}