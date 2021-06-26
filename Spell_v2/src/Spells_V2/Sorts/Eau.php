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

class Eau implements Listener{
	
//Frappe Acide
	public static function FrappeAcideUI (Player $player){
        $config = Main::$config;
        $form = new SimpleForm(function (Player $player, $data){
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						Eau::FrappeAcide($player);						
							break;								
					}
				}
			});
			
        $form->setTitle("§9 Frappe Acide");
		$content = "";
		$content .= "Info du sort Frappe Acide:\n\n";
		$content .= "Cout en mana : 60\n";
		$content .= "Durée : 2 min \n";
		$content .= "Utilisable par :  Mage d'Eau \n";
		$content .= "Débloquage : NV 5\n\n";
		$content .= "Description : Ce sort vous offre 2 coeur de plus ainsi que la possibilité de récupérer 1/4 des degats que vous infligez au corp a corp.\n";
		$form->setContent("$content");
		$form->addButton("VOTRE MANA : " . $config->get('mana') . "/" . $config->get('ManaMax'));
		$form->addButton("§a|UTILISER|");
		$form->addButton("§c|ANNULER|");
		$form->sendToPlayer($player);
    }
	public static function FrappeAcide (Player $player){		
        $config = Main::$config;
		if($config->get('Xp-general') >= 5){
			if($config->get('mana') >= 60){
				if($player instanceof Player){
					$player->sendMessage("§a(!)Sort Frappe Acide executé. Temps restant: 2min");
					$config->set('mana', $config->get('mana') - 60);
					$config->set('FrappeAcide', 1);
					$config->set('T_FrappeAcide', 120);
					$config->save();
					$player->addEffect(new EffectInstance(Effect::getEffect(21), 120 * 20, 0, false));
					Main::getInstance()->getScheduler()->scheduleRepeatingTask(new TaskSort\TaskFrappeAcide(Main::getInstance(), $player), 20);
				}
			} else {
				$player->sendMessage("§c(!)Vous n'avez pas assez de mana pour executer ce sort.");
			}
		} else {
			$player->sendMessage("§c(!)Votre niveau générale est trop faible pour executer ce sort.");
		}
	}	
//Bombe Glaciaire
	public static function BombeGlaciaireUI (Player $player){
        $config = Main::$config;
        $form = new SimpleForm(function (Player $player, $data){
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						$config = Main::$config;
						if($config->get('C_BombeGlaciaire') >= 1){
							$player->sendMessage("§c(!) Vous avez deja une posé une bombe glaciaire.");	
						} else {
							Eau::BombeGlaciaire($player);	
						}							
						break;								
					}
				}
			});
			
        $form->setTitle("§9 Bombe Glaciaire");
		$content = "";
		$content .= "Info du sort Bombe Glaciaire:\n\n";
		$content .= "Cout en mana : 150\n";
		$content .= "Utilisable par :  Mage d'Eau \n";
		$content .= "Débloquage : NV 8\n\n";
		$content .= "Description : Ce sort va poser une bombe qui explosera au bout de 20 sec. Lorsqu'elle explosera les entitées autour seront ralentis et prendront des degats.\n";
		$form->setContent("$content");
		$form->addButton("VOTRE MANA : " . $config->get('mana') . "/" . $config->get('ManaMax'));
		$form->addButton("§a|UTILISER|");
		$form->addButton("§c|ANNULER|");
		$form->sendToPlayer($player);
    }
	public static function BombeGlaciaire (Player $player){		
        $config = Main::$config;
		if($config->get('Xp-general') >= 8){
			if($config->get('mana') >= 150){
				if($player instanceof Player){
					$player->sendMessage("§a(!)Sort Bombe Glaciaire executé. Veuillez clicker sur un block pour poser la bombe.");
					$config->set('mana', $config->get('mana') - 150);
					$config->set('P_BombeGlaciaire', 1);
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