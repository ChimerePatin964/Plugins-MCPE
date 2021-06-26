<?php

namespace Spells_V2\Sorts;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\utils\TextFormat as TF;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\event\Listener;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use Spells_V2\Main;

class Terre implements Listener{
	
//Carapce Epineuse
	public static function CarapceEpineuseUI (Player $player){
        $config = Main::$config;
        $form = new SimpleForm(function (Player $player, $data){
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						Terre::CarapceEpineuse($player);						
							break;								
					}
				}
			});
			
        $form->setTitle("§6 Carapace Epineuse");
		$content = "";
		$content .= "Info du sort Carapace Epineuse:\n\n";
		$content .= "Cout en mana : 60\n";
		$content .= "Durée : 2 min \n";
		$content .= "Utilisable par :  Mage de Terre \n";
		$content .= "Débloquage : NV 5\n\n";
		$content .= "Description : Ce sort vous rend plus resistant et blesse toute les perssones s'aprochant trop de vous.\n";
		$form->setContent("$content");
		$form->addButton("VOTRE MANA : " . $config->get('mana') . "/" . $config->get('ManaMax'));
		$form->addButton("§a|UTILISER|");
		$form->addButton("§c|ANNULER|");
		$form->sendToPlayer($player);
    }
	public static function CarapceEpineuse (Player $player){		
        $config = Main::$config;
		if($config->get('Xp-general') >= 5){
			if($config->get('mana') >= 60){
				if($player instanceof Player){
					$player->sendMessage("§a(!)Sort Carapce Epineuse executé. Temps restant: 2min");
					$config->set('mana', $config->get('mana') - 60);
					$config->set('CarapceEpineuse', 1);
					$config->set('T_CarapceEpineuse', 120);
					$config->save();
					$player->addEffect(new EffectInstance(Effect::getEffect(11), 120 * 20, 0, false));
					Main::getInstance()->getScheduler()->scheduleRepeatingTask(new TaskSort\TaskCarapceEpineuse(Main::getInstance(), $player), 20);
				}
			} else {
				$player->sendMessage("§c(!)Vous n'avez pas assez de mana pour executer ce sort.");
			}
		} else {
			$player->sendMessage("§c(!)Votre niveau générale est trop faible pour executer ce sort.");
		}
	}
//Sable Mouvant
	public static function SableMouvantUI (Player $player){
        $config = Main::$config;
        $form = new SimpleForm(function (Player $player, $data){
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						Terre::SableMouvant($player);						
							break;								
					}
				}
			});
			
        $form->setTitle("§6 Sable Mouvant");
		$content = "";
		$content .= "Info du sort Sable Mouvant:\n\n";
		$content .= "Cout en mana : 150\n";
		$content .= "Utilisable par :  Mage de Terre \n";
		$content .= "Débloquage : NV 8\n\n";
		$content .= "Description : Ce sort va enfoncer dans le sol toute les entités autour de vous si elles se trouve sur une matiére minérale.\n";
		$form->setContent("$content");
		$form->addButton("VOTRE MANA : " . $config->get('mana') . "/" . $config->get('ManaMax'));
		$form->addButton("§a|UTILISER|");
		$form->addButton("§c|ANNULER|");
		$form->sendToPlayer($player);
    }
	public static function SableMouvant (Player $player){		
        $config = Main::$config;
		if($config->get('Xp-general') >= 8){
			if($config->get('mana') >= 150){
				if($player instanceof Player){
					$player->sendMessage("§a(!)Sort Sable Mouvant executé.");
					$config->set('mana', $config->get('mana') - 150);
					$config->save();
					foreach ($player->getLevel()->getNearbyEntities($player->getBoundingBox()->expandedCopy(6, 6, 6), $player) as $entity) {
						if ($entity instanceof Living) {
							$number = 2;
							while ($number > 0){
								$block = $entity->getLevel()->getBlock($entity->floor()->subtract(0, 1));
								$id = $block->getID();
								if (($id == 3) or ($id == 4) or ($id == 1) or ($id == 12) or ($id == 13) or ($id == 2) or ($id == 110)){
									$level = $entity->getLevel();
									$x = $entity->getX();
									$y = $entity->getY();
									$z = $entity->getZ();
									$entity->teleport(new Position($x, $y-1, $z, $level));
								}
							$number = $number - 1;
							}
						}
					}
				}
			} else {
				$player->sendMessage("§c(!)Vous n'avez pas assez de mana pour executer ce sort.");
			}
		} else {
			$player->sendMessage("§c(!)Votre niveau générale est trop faible pour executer ce sort.");
		}
	}	
	
}