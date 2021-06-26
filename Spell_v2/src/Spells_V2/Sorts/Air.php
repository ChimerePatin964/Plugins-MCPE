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

class Air implements Listener{
	
//Fuite Invisible
	public static function FuiteInvisibleUI (Player $player){
        $config = Main::$config;
        $form = new SimpleForm(function (Player $player, $data){
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						Air::FuiteInvisible($player);						
							break;								
					}
				}
			});
			
        $form->setTitle("§a Fuite Invisible");
		$content = "";
		$content .= "Info du sort Fuite Invisible:\n\n";
		$content .= "Cout en mana : 60\n";
		$content .= "Durée : 2 min\n";
		$content .= "Utilisable par :  Mage d'Air \n";
		$content .= "Débloquage : NV 5\n\n";
		$content .= "Description : Ce sort vous offre la vitesse du vent et vous rend invisible pendant 10 sec lorsque vous prenez un coup.\n";
		$form->setContent("$content");
		$form->addButton("VOTRE MANA : " . $config->get('mana') . "/" . $config->get('ManaMax'));
		$form->addButton("§a|UTILISER|");
		$form->addButton("§c|ANNULER|");
		$form->sendToPlayer($player);
    }
	public static function FuiteInvisible (Player $player){		
        $config = Main::$config;
		if($config->get('Xp-general') >= 5){
			if($config->get('mana') >= 60){
				if($player instanceof Player){
					$player->sendMessage("§a(!)Sort Fuite Invisible executé. Temps restant: 2min");
					$config->set('mana', $config->get('mana') - 60);
					$config->set('FuiteInvisible', 1);
					$config->set('T_FuiteInvisible', 120);
					$config->save();
					$player->addEffect(new EffectInstance(Effect::getEffect(1), 120 * 20, 0, false));
					Main::getInstance()->getScheduler()->scheduleRepeatingTask(new TaskSort\TaskFuiteInvisible(Main::getInstance(), $player), 20);
				}
			} else {
				$player->sendMessage("§c(!)Vous n'avez pas assez de mana pour executer ce sort.");
			}
		} else {
			$player->sendMessage("§c(!)Votre niveau générale est trop faible pour executer ce sort.");
		}
	}	
//Nuage Empoisonné
	public static function NuageEmpoisonneUI (Player $player){
        $config = Main::$config;
        $form = new SimpleForm(function (Player $player, $data){
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						$config = Main::$config;
						if($config->get('T_NuageEmpoisonne') >= 1){
							$player->sendMessage("§c(!) Vous avez deja une posé un nuage empoisonné.");	
						} else {
							Air::NuageEmpoisonne($player);	
						}						
						break;								
					}
				}
			});
			
        $form->setTitle("§a Nuage Empoisonné");
		$content = "";
		$content .= "Info du sort Nuage Empoisonné:\n\n";
		$content .= "Cout en mana : 150\n";
		$content .= "Durée : 1 min\n";
		$content .= "Utilisable par :  Mage d'Air \n";
		$content .= "Débloquage : NV 8\n\n";
		$content .= "Description : Ce sort va creer un nuage empoisonné la ou vous voulez.\n";
		$form->setContent("$content");
		$form->addButton("VOTRE MANA : " . $config->get('mana') . "/" . $config->get('ManaMax'));
		$form->addButton("§a|UTILISER|");
		$form->addButton("§c|ANNULER|");
		$form->sendToPlayer($player);
    }
	public static function NuageEmpoisonne (Player $player){		
        $config = Main::$config;
		if($config->get('Xp-general') >= 8){
			if($config->get('mana') >= 150){
				if($player instanceof Player){
					$player->sendMessage("§a(!)Sort Nuage Empoisonné executé. Veuillez clicker sur un block pour poser le nuage.");
					$config->set('mana', $config->get('mana') - 150);
					$config->set('P_NuageEmpoisonne', 1);
					$config->save();
				}
			} else {
				$player->sendMessage("§c(!)Vous n'avez pas assez de mana pour executer ce sort.");
			}
		} else {
			$player->sendMessage("§c(!)Votre niveau générale est trop faible pour executer ce sort.");
		}
	}	
	
}