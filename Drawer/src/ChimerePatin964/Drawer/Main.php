<?php
 
//    ______ _     _                        ______           _      ____   __   __    
//   / _____) |   (_)                      (_____ \     _   (_)    / __ \ / /  / /    
//  | /     | | _  _ ____   ____  ____ ____ _____) )___| |_  _ ___( (__) ) /_ / /____ 
//  | |     | || \| |    \ / _  )/ ___) _  )  ____/ _  |  _)| |  _ \__  / __ \___   _)
//  | \_____| | | | | | | ( (/ /| |  ( (/ /| |   ( ( | | |__| | | | |/ ( (__) )  | |  
//   \______)_| |_|_|_|_|_|\____)_|   \____)_|    \_||_|\___)_|_| |_/_/ \____/   |_| 
 

namespace ChimerePatin964\Drawer;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\items;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\item\ItemFactory;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener{

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
        @mkdir($this->getDataFolder()."drawers/");
		$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
		if($settup->get("enabled") !== false){
			$this->settup = $settup->getAll();
		    $this->getLogger()->info("§aPlugin Drawer chargé avec succès !");
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		}else{
			$this->getLogger()->info("Eteind a partir de la config");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
	}
	public function onPlace(BlockPlaceEvent $event){
		$block = $event->getBlock();
		$Id_block = $block->getID() . ":" . $block->getDamage();
		$loc = $block->getX().":".$block->getY().":".$block->getZ().":".$block->getLevel()->getName();
		$World = $block->getLevel()->getName();
		$player = $event->getPlayer();
		$name = $player->getName();
		$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
		$item = $settup->get('Block');

		if ($player->isSneaking()) {
			if ($settup->get('Shift') == true) {
				$target = $event->getBlockAgainst()->getId() . ':' . $event->getBlockAgainst()->getDamage();
				if ($target == $item) {
					$event->setCancelled();
					return;
				}
			}
		}		
		if ($Id_block === $item) {
			if(!file_exists($this->getDataFolder()."drawers/".strtolower($loc).".yml")){
				$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
			
				$config->set('First-Player',"$name");
			
				$config->set('World',"$World");
				$config->set('X',$block->getX());
				$config->set('Y',$block->getY());
				$config->set('Z',$block->getZ());
				$config->set('Count',0);
				$config->set('Id','0:0');
			
				$config->save();
			}
		}
	}

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        switch(strtolower($command->getName())) {
			case "id":
				$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
				$actif = $settup->get("ID");
				if($actif !== false){
					$sender->sendMessage("§aID: §b{$sender->getInventory()->getItemInHand()->getId()}:{$sender->getInventory()->getItemInHand()->getDamage()}");
				} else {
					$sender->sendMessage(TF::RED . $settup->get("(!)Cette commande a été desactivée a partir de la config"));
				}	
		}
		return true;
	}
		
	public function onTouch(PlayerInteractEvent $event){
		if($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			return;
		}
		$block = $event->getBlock();
		$Id_block = $block->getID() . ":" . $block->getDamage();
		$player = $event->getPlayer();
		$loc = $block->getX().":".$block->getY().":".$block->getZ().":".$block->getLevel()->getName();
		$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
		$item = $settup->get('Block');

		if ($Id_block == $item){
			if(file_exists($this->getDataFolder()."drawers/".strtolower($loc).".yml")){
				if (!$player->isSneaking()) {
					$this->Drawer($player, $block, $Id_block, $loc);
				} else {
					if ($settup->get('Shift') == true) {
						$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
						$hand = $player->getInventory()->getItemInHand()->getId() . ":" . $player->getInventory()->getItemInHand()->getDamage();
						$Id = $player->getInventory()->getItemInHand()->getId();
						$Meta = $player->getInventory()->getItemInHand()->getDamage();
						if ($hand !== '0:0') {
							$count = $player->getInventory()->getItemInHand()->getCount();
							if ($config->get('Count') <= 0) {
								if ($hand !== $settup->get('Item')) {
									if (($config->get('Count') + $count) <= $settup->get('Taille')) {
										$player->getInventory()->removeItem(Item::get($Id, $Meta, $count));
										$config->set('Count',$count);
										$config->set('Id',$hand);
										$config->save();
										$name_item = ItemFactory::fromString($hand)->getName();
										$player->sendMessage("§aVous avez bien ajouté " . $count . ' ' . $name_item);
									} else {
										$player->sendMessage("§cVous ne pouvez pas mettre autant d'items dans ce drawer");
									}
								} else {
									$player->sendMessage("§cCet item ne peut pas etre placé dans les drawers.");
								}
							} else {
								if ($hand == $config->get('Id')) {
									if (($config->get('Count') + $count) <= $settup->get('Taille')) {
										$player->getInventory()->removeItem(Item::get($Id, $Meta, $count));
										$config->set('Count',$config->get('Count') + $count);
										$config->save();
										$name_item = ItemFactory::fromString($hand)->getName();
										$player->sendMessage("§aVous avez bien ajouté " . $count . ' ' . $name_item);
									} else {
										$player->sendMessage("§cVous ne pouvez pas mettre autant d'items dans ce drawer");
									}
								} else {
									$player->sendMessage("§cVous ne pouvez pas stocker deux items/blocs différents dans le drawer");
								}
							}
						}
					}
				}
			} else {
				$player->sendMessage("§c(!)Ce Drawer a été corrompu veuillez le détruire puis le replacer.");
				return;
			}
		}
	}
	
	public function onBreak(BlockBreakEvent $event){
		$block = $event->getBlock();
		$Id_block = $block->getID() . ":" . $block->getDamage();
		$loc = $block->getX().":".$block->getY().":".$block->getZ().":".$block->getLevel()->getName();
		$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
		$item = $settup->get('Block');
		
		if ($Id_block == $item){
			if(file_exists($this->getDataFolder()."drawers/".strtolower($loc).".yml")){
				$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
				$item = ItemFactory::fromString($config->get('Id'));
				$item_id = $item->getID();
				$meta = $item->getDamage();
				$count = $config->get('Count');
				while ($count > 64) {
					$config->set('Count',$count - 64);
					$config->save();
					$block->getLevel()->dropItem($block, Item::get($item_id, $meta, 64));
					$count = $config->get('Count');
				}
				if ($count <= 64) {
					$block->getLevel()->dropItem($block, Item::get($item_id, $meta, $count));
				}
				unlink($this->getDataFolder()."drawers/".strtolower($loc).".yml");
			}
		}
	}
	
	public function onExplode(EntityExplodeEvent $event){
        $entity = $event->getEntity();
        $center = $entity->getLevel()->getBlock($entity);
        $listBlock = [];
        for($i = 0; $i <= (3.3*2); $i++) {
			$listBlock[] = $center->getSide($i);
        }
		foreach ($listBlock as $block) {
			$Id_block = $block->getID() . ":" . $block->getDamage();
			$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
			$item = $settup->get('Block');
		
			if ($Id_block == $item){
				$loc = $block->getX().":".$block->getY().":".$block->getZ().":".$block->getLevel()->getName();
				if(file_exists($this->getDataFolder()."drawers/".strtolower($loc).".yml")){
					$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
					$item = ItemFactory::fromString($config->get('Id'));
					$item_id = $item->getID();
					$meta = $item->getDamage();
					$count = $config->get('Count');
					while ($count > 64) {
						$config->set('Count',$count - 64);
						$config->save();
						$block->getLevel()->dropItem($block, Item::get($item_id, $meta, 64));
						$count = $config->get('Count');
					}
					if ($count <= 64) {
						$block->getLevel()->dropItem($block, Item::get($item_id, $meta, $count));
					}
					unlink($this->getDataFolder()."drawers/".strtolower($loc).".yml");
				}
			}
		}
	}
	
    public function Drawer(Player $player, $block, $Id_block, $loc)
	{
		$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
		$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
		$item = ItemFactory::fromString($config->get('Id'));
		$name = $item->getName();
        $form = new SimpleForm(function (Player $player, $data) use ($block, $Id_block, $loc) {
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						$this->AjoutDrawer($player, $loc);						
							break;	
						case 2:
						$this->RetireDrawer($player, $loc);						
							break;							
					}
				}
		});
        $form->setTitle("§eDrawer");
		$content = "";
		$content .= "Info du drawer : \n\n";
		if ($config->get('Count') <= 0) {
			$content .= "§eCe drawer est vide vous pouvez le remplire en cliquant sur |AJOUTER DES ITEMS| \n";
		} else {
			$content .= "Blocs : " . $config->get('Id') . " (" . $name . ")" . "\n";
			$content .= "Nombres : " . $config->get('Count') . "/" . $settup->get('Taille') . "\n\n";
		}
		$form->setContent("$content");
		$form->addButton("§e|QUITTER LE MENU|");
		$form->addButton("§a|AJOUTER DES ITEMS|");
		$form->addButton("§c|RETIRER DES ITEMS|");
		$form->sendToPlayer($player);
    }

    public function AjoutDrawer(Player $player, $loc)
	{
		$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
		$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
		$item = ItemFactory::fromString($config->get('Id'));
		$name = $item->getName();
		$ID = $item->getId();
		$Meta = $item->getDamage();
        $form = new CustomForm(function (Player $player, $data) use ($loc) {
			$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
			$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
			if ($config->get('Count') < $settup->get('Taille')) {
				if ($data !== null) {
					$test = $data[1] . ':' . $data[2];
					if ((is_numeric($data[1])) && (is_numeric($data[2]))) {
						if ($config->get('Count') <= 0) {
							if ($test !== $settup->get('Item')) {
								if ($player->getInventory()->contains(Item::get((int)$data[1], (int)$data[2], $data[3])) === true) {
									if (($config->get('Count') + $data[3]) <= $settup->get('Taille')) {
										$player->getInventory()->removeItem(Item::get((int)$data[1], (int)$data[2], $data[3]));
										$config->set('Count',$data[3]);
										$config->set('Id',$test);
										$config->save();
									} else {
										$player->sendMessage("§cVous ne pouvez pas mettre autant d'items dans ce drawer");
									}
								} else {
									$player->sendMessage("§cVous ne possédez pas autant d'items dans votre inventaire");
								}
							} else {
								$player->sendMessage("§cCet item ne peut pas etre placé dans les drawers.");
							}
						} else {
							if ($test == $config->get('Id')) {
								if ($player->getInventory()->contains(Item::get((int)$data[1], (int)$data[2], $data[3])) === true) {
									if (($config->get('Count') + $data[3]) <= $settup->get('Taille')) {
										$player->getInventory()->removeItem(Item::get((int)$data[1], (int)$data[2], $data[3]));
										$config->set('Count',$config->get('Count') + $data[3]);
										$config->save();
									} else {
										$player->sendMessage("§cVous ne pouvez pas mettre autant d'items dans ce drawer");
									}
								} else {
									$player->sendMessage("§cVous ne possédez pas autant d'items dans votre inventaire");
								}
							} else {
								$player->sendMessage("§cVous ne pouvez pas stocker deux items/blocs différents dans le drawer");
							}
						}
					} else {
						$player->sendMessage("§eVous devez rentrer l'id du block/item que vous voulez stocker (faites /id)");
					}
				}
			}
		});			
        $form->setTitle("§aAjouter des items");
		$content = "";
		$content .= "Info du drawer:\n\n";
		if ($config->get('Count') >= $settup->get('Taille')) {
			$content .= "§eCe drawer est plein il est donc impossible d'ajouter des items. \n";
			$content .= "Pour retirer des items réouvrez le drawer et clickez sur |RETIRER DES ITEMS|. \n\n";
			$form->addLabel("$content");
		} else {
			$content .= "Blocs : " . $config->get('Id') . " (" . $name . ")" . "\n";
			$content .= "Nombres : " . $config->get('Count') . "/" . $settup->get('Taille') . "\n\n";
			$form->addLabel("$content");
			$form->addInput("Marquez l'id du bloc que vous voulez ajouter (/id)", "ID de l'item ou du bloc");
			$form->addInput("Marquez la Meta du bloc que vous voulez ajouter (/id)", "Meta de l'item ou du bloc");
			$form->addSlider("Combien", 1, 64);
		}
		$form->sendToPlayer($player);
    }

    public function RetireDrawer(Player $player, $loc)
	{
		$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
		$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
		$item = ItemFactory::fromString($config->get('Id'));
		$ID = $item->getId();
		$Meta = $item->getDamage();
		$name = $item->getName();
		$max = $config->get('Count');
        $form = new CustomForm(function (Player $player, $data) use ($loc) {
			$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
			$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
			if ($config->get('Count') > 0) {
				if ($data !== null) {
					$item = ItemFactory::fromString($config->get('Id'));
					$ID = $item->getId();
					$Meta = $item->getDamage();
					if ($data[1] <= $config->get('Count')) {
						if ($player->getInventory()->canAddItem(Item::get($ID, $Meta, $data[1]))) {
							$player->getInventory()->addItem(Item::get($ID, $Meta, $data[1]));
							$result = $config->get('Count') - $data[1];
							$config->set('Count',$result);
							$config->save();
							if ($result == 0) {
								$config->set('Id','0:0');
								$config->save();
							}
						} else {
							$player->sendMessage("§cVous n'avez pas la place de retirer autant d'items");
						}
					} else {
						$player->sendMessage("§eEvitez d'etre deux a retirer des items en meme temps !");
					}
				}
			}	
		});			
        $form->setTitle("§cRetirer des items");
		$content = "";
		$content .= "Info du drawer:\n\n";
		if ($config->get('Count') <= 0) {
			$content .= "§eCe drawer est vide il est donc impossible de retirer des items. \n";
			$content .= "Pour ajouter des items réouvrez le drawer et clickez sur |AJOUTER DES ITEMS|. \n\n";
			$form->addLabel("$content");
		} else {
			$content .= "Blocs : " . $config->get('Id') . " (" . $name . ")" . "\n";
			$content .= "Nombres : " . $config->get('Count') . "/" . $settup->get('Taille') . "\n\n";
			$content .= "Veuillez selectionner combien d'item vous voulez retirer";
			$form->addLabel("$content");
			$form->addSlider("Combien", 1, $max);
		}
		$form->sendToPlayer($player);
    }	
}