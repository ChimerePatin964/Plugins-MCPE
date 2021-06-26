<?php

namespace Spells_V2\Sorts\TaskSort;

use Spells_V2\Main;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as T;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;

class TaskHellHarmor extends Task{

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
		if($config->get('T_HellHarmor') != 0) {
			$config->set('T_HellHarmor', $config->get('T_HellHarmor') - 1);
			$config->save();
		} else {
			$config->set('HellHarmor', 0);
			$config->save();
			$player->sendMessage("§e(!) Le sort HellHarmor est a present terminé.");
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}