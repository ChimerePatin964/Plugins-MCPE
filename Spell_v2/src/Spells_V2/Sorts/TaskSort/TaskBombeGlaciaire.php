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
use pocketmine\level\particle\SnowballPoofParticle;
use pocketmine\level\Explosion;
use pocketmine\level\Position;

class TaskBombeGlaciaire extends Task{

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
		if($config->get('C_BombeGlaciaire') > 0){
			$config->set('C_BombeGlaciaire', $config->get('C_BombeGlaciaire') - 1);
			$config->save();	
			$level = $block->getLevel();
			$x = $block->getX();
			$y = $block->getY() + 1;
			$z = $block->getZ();
			$particule = 5;
			while ($particule > 0) {
				$level->addParticle(new SnowballPoofParticle(new Vector3($x, $y, $z)));
				$particule = $particule - 1;
			}
		} else {
			$player->sendMessage("§e(!) Votre bombe a bien explosé.");
			foreach ($block->getLevel()->getEntities() as $entity) {
				if ($entity instanceof Living) {
					if ($block->distance($entity) <= 6){
						$ev = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_CONTACT, 8);
						$entity->attack($ev);
						$entity->addEffect(new EffectInstance(Effect::getEffect(2), 20 * 20, 1, false));
					}
				}
			}
            $explosion = new Explosion(new Position($block->getX(), $block->getY(), $block->getZ(), $block->getLevel()), 1.1,$block);
            $explosion->explodeA();
            $explosion->explodeB();
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}