<?php

namespace Spells_V2\Sorts\TaskSort;

use Spells_V2\Main;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\Explosion;
use pocketmine\level\Position;

class TaskNuageEmpoisonne extends Task{

	private $plugin;
	private $player;
	private $block;
	
	public function __construct(Main $plugin, $player, $block)
	{
		$this->plugin = $plugin;
		$this->player = $player;
		$this->block = $block;
	}

	public function onRun(int $currentTick)
	{
		$player = $this->player;
		$block = $this->block;
		$config = new Config($this->plugin->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
		if($config->get('T_NuageEmpoisonne') > 0){
			$config->set('T_NuageEmpoisonne', $config->get('T_NuageEmpoisonne') - 1);
			$config->save();	
			$level = $block->getLevel();
			$x = $block->getX();
			$y = $block->getY();
			$z = $block->getZ();
			$particule = 10;
			while ($particule > 0) {
				$level->addParticle(new DustParticle(new Vector3($x + mt_rand(-3, 3), $y + mt_rand(-2, 4), $z + mt_rand(-3, 3)), 111, 197, 61));
				$particule = $particule - 1;
			}
			foreach ($block->getLevel()->getEntities() as $entity) {
				if ($entity instanceof Living) {
					if ($block->distance($entity) <= 3){
						$entity->addEffect(new EffectInstance(Effect::getEffect(2), 3 * 20, 2, false));
						$entity->addEffect(new EffectInstance(Effect::getEffect(25), 3 * 20, 3, false));
						$entity->addEffect(new EffectInstance(Effect::getEffect(15), 3 * 20, 0, false));
					}
				}
			}
		} else {
			$player->sendMessage("§e(!) Votre nuage s'est dissipé.");
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}