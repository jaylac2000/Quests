<?php

namespace jaylac2000\Quests;

use pocketmine\event\{
	Listener,
	block\BlockPlaceEvent,
	block\BlockBreakEvent,
	player\PlayerLoginEvent,
	player\PlayerMoveEvent,
	player\PlayerDeathEvent,
	entity\EntityDamageByEntityEvent
};
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\math\Vector3;

class EventListener implements Listener 
{

	private $plugin;

	public function __construct(Main $plugin)
	{
		$this->plugin = $plugin;
	}

	public function onPlayerLogin(PlayerLoginEvent $ev)
	{
		$player = $ev->getPlayer();

		QuestManager::registerUser($player->getName());
	}

	public function onBreakBlock(BlockBreakEvent $ev)
	{
		$quests = $this->plugin->getQuests();
		$block = $ev->getBlock();
		$player = $ev->getPlayer();
		
		if($ev->isCancelled()) return;
		
		foreach ($quests as $name => $value) {
			if($value["type"] === "breakblock" && QuestManager::isCurrent($player->getName(), $name)){
				if(strpos($value["block"], ":") === false){
					$b = Block::get($value["block"], 0);
				} else {
					$arr = explode(":", $value["block"]);
					$b = Block::get($arr[0], $arr[1]);
				}
				if($block->getId() === $b->getId() && $block->getDamage() === $b->getDamage()) QuestManager::incrementProgress($player, $name);
			}
		}
	}

	public function onPlaceBlock(BlockPlaceEvent $ev)
	{
		$quests = $this->plugin->getQuests();
		$block = $ev->getBlock();
		$player = $ev->getPlayer();
		
		if($ev->isCancelled()) return;
		
		foreach ($quests as $name => $value) {
			if($value["type"] === "placeblock" && QuestManager::isCurrent($player->getName(), $name)){
				if(strpos($value["block"], ":") === false){
					$b = Block::get($value["block"], 0);
				} else {
					$arr = explode(":", $value["block"]);
					$b = Block::get($arr[0], $arr[1]);
				}
				if($block->getId() === $b->getId() && $block->getDamage() === $b->getDamage()) QuestManager::incrementProgress($player, $name);
			}
		}
	}

	public function onDeath(PlayerDeathEvent $ev)
	{
		$player = $ev->getPlayer();
		$quests = $this->plugin->getQuests();

		$cause = $player->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent){
			$damager = $cause->getDamager();
			if($damager instanceof Player){
				foreach ($quests as $name => $value){
					if($value["type"] === "kills" && QuestManager::isCurrent($damager->getName(), $name)){
						QuestManager::incrementProgress($damager, $name);
					}
				}
			}
		}
	}

	public function onMove(PlayerMoveEvent $ev)
	{
		$quests = $this->plugin->getQuests();
		$player = $ev->getPlayer();
		$from = $ev->getFrom();
		$to = $ev->getTo();
		
		if($ev->isCancelled()) return;
		
		if($from->getX() !== $to->getX() && $from->getZ() !== $to->getZ()){
			foreach ($quests as $name => $value) {
				if($value["type"] === "move" && QuestManager::isCurrent($player->getName(), $name)){
					QuestManager::incrementProgress($player, $name);
				}
			}
		}
	}
}