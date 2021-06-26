<?php

namespace Spells_V2;

use Spells_V2\Main;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as T;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;

class Cherker extends Task{
	
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
			
			// Classe Feu
			if ($config->get('Clan') == 1) {
				$config->set('HellHarmor',0);
				$config->set('T_HellHarmor',0);
				$config->save();
			}
			// Classe Eau
			if ($config->get('Clan') == 2) {
				$config->set('FrappeAcide',0);
				$config->set('T_FrappeAcide',0);
				$config->set('P_BombeGlaciaire',0);
				$config->set('C_BombeGlaciaire',0);
				$config->save();
			}
			// Classe Air
			if ($config->get('Clan') == 3) {
				$config->set('FuiteInvisible',0);
				$config->set('T_FuiteInvisible',0);
				$config->set('P_NuageEmpoisonne',0);
				$config->set('T_NuageEmpoisonne',0);
				$config->save();
			}
			// Classe Terre
			if ($config->get('Clan') == 4) {
				$config->set('CarapceEpineuse',0);
				$config->set('T_CarapceEpineuse',0);
				$config->save();
			}
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());
		} else {
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());
		}
	}
	
}