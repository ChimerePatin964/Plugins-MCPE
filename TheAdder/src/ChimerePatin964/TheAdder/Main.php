<?php

//    ______ _     _                        ______           _      ____   __   __    
//   / _____) |   (_)                      (_____ \     _   (_)    / __ \ / /  / /    
//  | /     | | _  _ ____   ____  ____ ____ _____) )___| |_  _ ___( (__) ) /_ / /____ 
//  | |     | || \| |    \ / _  )/ ___) _  )  ____/ _  |  _)| |  _ \__  / __ \___   _)
//  | \_____| | | | | | | ( (/ /| |  ( (/ /| |   ( ( | | |__| | | | |/ ( (__) )  | |  
//   \______)_| |_|_|_|_|_|\____)_|   \____)_|    \_||_|\___)_|_| |_/_/ \____/   |_| 
 

namespace ChimerePatin964\TheAdder;

use pocketmine\block\BlockFactory;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use ChimerePatin964\TheAdder\block\SlimeBlock;
use ChimerePatin964\TheAdder\block\Beacon;
use ChimerePatin964\TheAdder\block\JukeBox;
use ChimerePatin964\TheAdder\item\TurtleHelmet;

class Main extends PluginBase implements Listener{
	
	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
		if($settup->get("enabled") !== false){
			$this->settup = $settup->getAll();
		    $this->getLogger()->info("§aPlugin TheAdder chargé avec succès !");
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
			$this->Register();
		}else{
			$this->getLogger()->info("Eteind a partir de la config");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
	}

    public function Register(){	
        BlockFactory::registerBlock(new SlimeBlock(), true);
		BlockFactory::registerBlock(new Beacon(), true);
		BlockFactory::registerBlock(new JukeBox(), true);
		ItemFactory::registerItem(new TurtleHelmet(), true);
        Item::initCreativeItems();
	}

}
