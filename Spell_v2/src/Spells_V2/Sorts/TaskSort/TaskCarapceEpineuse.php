<?php

namespace Spells_V2\Sorts\TaskSort;

use Spells_V2\Main;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as T;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;

class TaskCarapceEpineuse extends Task{

	private $plugin;
	private $player;
	
	public function __construct(Main $plugin, $player)
	{
		$this->plugin = $plugin;
		$this->player = $player;
	}

	public function onRun(int $currentTick)
	{
		$player = $this->player;
		$config = new Config($this->plugin->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
		// Cooldown
		if($config->get('T_CarapceEpineuse') != 0) {
			$config->set('T_CarapceEpineuse', $config->get('T_CarapceEpineuse') - 1);
			$config->save();
		} else {
			$config->set('CarapceEpineuse', 0);
			$config->save();
			$player->sendMessage("§e(!) Le sort Carapce Epineuse est a present terminé.");
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());
		}		
		// Sort
		if($config->get('T_CarapceEpineuse') > 0) {
			foreach ($player->getLevel()->getNearbyEntities($player->getBoundingBox()->expandedCopy(1, 0, 1), $player) as $entity) {
				if ($entity instanceof Living) {
					$ev = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_CONTACT, 1);
					$entity->attack($ev);
				}
            }
		}
	}
}