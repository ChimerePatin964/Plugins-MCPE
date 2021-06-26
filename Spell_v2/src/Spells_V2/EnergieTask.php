<?php

namespace Spells_V2;

use Spells_V2\Main;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as T;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;

class EnergieTask extends Task{

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
		if($player->isOnline()){
			$config = new Config($this->plugin->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
			$mana = $config->get('mana');
			$ManaMax = $config->get('ManaMax');			
			
			// Regen Mana
			$med = $config->get('med');
			if (($med == 0) && ($mana < $ManaMax)){
				$config->set('mana',$mana + 1);
				$config->save();
			}
			if (($med == 1) && ($mana+3 < $ManaMax)) {
				$config->set('mana',$mana + 3);
				$config->save();
			}
			if (($med == 1) && ($mana+3 >= $ManaMax)) {
				$config->set('mana',$ManaMax);
				$config->set('med',0);
				$config->save();
				$player->sendMessage("§e(!) Meditation arreté car votre mana est plein.");
			}
			$player->sendPopup("§b MANA : " . $mana . "/" . $ManaMax);
		} else {
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}