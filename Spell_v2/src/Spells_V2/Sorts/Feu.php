<?php

namespace Spells_V2\Sorts;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\utils\TextFormat as TF;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use Spells_V2\Main;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class Feu implements Listener{
	
// HellHarmor
	public static function HellHarmorUI (Player $player){
        $config = Main::$config;
        $form = new SimpleForm(function (Player $player, $data){
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						Feu::HellHarmor($player);						
							break;								
					}
				}
			});
			
        $form->setTitle("§c HellHarmor");
		$content = "";
		$content .= "Info du sort HellHarmor:\n\n";
		$content .= "Cout en mana : 60\n";
		$content .= "Durée : 2 min\n";
		$content .= "Utilisable par :  Mage de Feu \n";
		$content .= "Débloquage : NV 5\n\n";
		$content .= "Description : Ce sort va vous recouvrir de feu brulant ceux qui vous tapent et ceux que vous tapez a main nue. Vous beneficiez aussi d'une resistance au feu.\n";
		$form->setContent("$content");
		$form->addButton("VOTRE MANA : " . $config->get('mana') . "/" . $config->get('ManaMax'));
		$form->addButton("§a|UTILISER|");
		$form->addButton("§c|ANNULER|");
		$form->sendToPlayer($player);
    }
	public static function HellHarmor (Player $player){
        $config = Main::$config;
		if($config->get('Xp-general') >= 5){
			if($config->get('mana') >= 60){
				if($player instanceof Player){
					$player->sendMessage("§a(!)Sort HellHarmor executé. Temps restant: 2min");
					$config->set('mana', $config->get('mana') - 60);
					$config->set('HellHarmor', 1);
					$config->set('T_HellHarmor', 120);
					$config->save();
					$player->addEffect(new EffectInstance(Effect::getEffect(12), 120 * 20, 0, false));
					Main::getInstance()->getScheduler()->scheduleRepeatingTask(new TaskSort\TaskHellHarmor(Main::getInstance(), $player), 20);
				}
			} else {
				$player->sendMessage("§c(!)Vous n'avez pas assez de mana pour executer ce sort.");
			}
		} else {
			$player->sendMessage("§c(!)Votre niveau générale est trop faible pour executer ce sort.");
		}
	}
// Explosion de Rage
	public static function ExplosionRageUI (Player $player){
        $config = Main::$config;
        $form = new SimpleForm(function (Player $player, $data){
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						Feu::ExplosionRage($player);						
							break;								
					}
				}
			});
			
        $form->setTitle("§c Explosion de Rage");
		$content = "";
		$content .= "Info du sort Explosion de Rage:\n\n";
		$content .= "Cout en mana : 150\n";
		$content .= "Utilisable par :  Mage de Feu \n";
		$content .= "Débloquage : NV 8\n\n";
		$content .= "Description : Ce sort va creer une explosion autour de vous. Cette explosion ne vous blessera pas. Vous receverez aussi un boost de force.\n";
		$form->setContent("$content");
		$form->addButton("VOTRE MANA : " . $config->get('mana') . "/" . $config->get('ManaMax'));
		$form->addButton("§a|UTILISER|");
		$form->addButton("§c|ANNULER|");
		$form->sendToPlayer($player);
    }
	public static function ExplosionRage (Player $player){
        $config = Main::$config;
		if($config->get('Xp-general') >= 8){
			if($config->get('mana') >= 150){
				if($player instanceof Player){
					$player->sendMessage("§a(!)Sort Explosion de Rage executé.");
					$config->set('mana', $config->get('mana') - 150);
					$config->save();
					$player->setGamemode(1);
					$explosion = new Explosion(new Position($player->getX(), $player->getY(), $player->getZ(), $player->getLevel()), 3.3,$player);
					$explosion->explodeA();
					$explosion->explodeB();
					$player->setGamemode(0);
					$player->addEffect(new EffectInstance(Effect::getEffect(5), 20 * 20, 1, false));
				}
			} else {
				$player->sendMessage("§c(!)Vous n'avez pas assez de mana pour executer ce sort.");
			}
		} else {
			$player->sendMessage("§c(!)Votre niveau générale est trop faible pour executer ce sort.");
		}
	}
}