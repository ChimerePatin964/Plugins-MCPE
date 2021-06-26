<?php
 
//    ______ _     _                        ______           _      ____   __   __    
//   / _____) |   (_)                      (_____ \     _   (_)    / __ \ / /  / /    
//  | /     | | _  _ ____   ____  ____ ____ _____) )___| |_  _ ___( (__) ) /_ / /____ 
//  | |     | || \| |    \ / _  )/ ___) _  )  ____/ _  |  _)| |  _ \__  / __ \___   _)
//  | \_____| | | | | | | ( (/ /| |  ( (/ /| |   ( ( | | |__| | | | |/ ( (__) )  | |  
//   \______)_| |_|_|_|_|_|\____)_|   \____)_|    \_||_|\___)_|_| |_/_/ \____/   |_| 
 

namespace ChimerePatin964\CustomDrawer;

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
use pocketmine\math\Vector3;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener{

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
        @mkdir($this->getDataFolder()."drawers/");
		$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
		if($settup->get("enabled") !== false){
			$this->settup = $settup->getAll();
		    $this->getLogger()->info("§aPlugin CustomDrawer chargé avec succès !");
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
		$sub = $block->getLevel()->getBlock($block->floor()->subtract(0, 1));
		$ID = $sub->getID() . ":" . $sub->getDamage();
		$block_name = $sub->getName();
		$player = $event->getPlayer();
		$name = $player->getName();
		
		if ($Id_block === "216:0") {
			if(!file_exists($this->getDataFolder()."drawers/".strtolower($loc).".yml")){
				$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
			
				$config->set('beneath',"$ID");
				$config->set('name',"$block_name");
				$config->set('First-Player',"$name");
			
				$config->set('World',"$World");
				$config->set('X',$block->getX());
				$config->set('Y',$block->getY());
				$config->set('Z',$block->getZ());
				$config->set('Count',0);
				$config->set('Id','0:0');
				$config->set('Compact','0');
			
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
		$sub = $block->getLevel()->getBlock($block->floor()->subtract(0, 1));
		$ID = $sub->getID() . ":" . $sub->getDamage();
		$Hand = $player->getInventory()->getItemInHand()->getId() . ":" . $player->getInventory()->getItemInHand()->getDamage();

		if ($Id_block == "216:0"){
			if(file_exists($this->getDataFolder()."drawers/".strtolower($loc).".yml")){
				$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
				$compact = $config->get('Compact');
				if ($compact == '0') {
					if ($player->isSneaking()) {
						if ($Hand == "467:0") {
							$player->sendMessage("§a(!)Ce drawer a été compacté il est pret pour le deplacement.");
							$config->set('Compact','1');
							$player->getInventory()->removeItem(Item::get('467', '0', '1'));
							$block->getLevel()->addParticle(new DestroyBlockParticle(new Vector3($block->x, $block->y+1, $block->z), Block::get(Block::REDSTONE_BLOCK)));
						}
					} else {
						if ($ID === "0:0") {
							$player->sendMessage("§c(!)Vous devez placer le CustomDrawer sur un block pour pouvoir l'utiliser.");
							return;
						} else {
							if ($ID !== $config->get('beneath')) {
								$player->sendMessage("§c(!)Vous avez modifier le block sous le CustomDrawer, veuillez supprimer le CustomDrawer puis le replacer sur un nouveau block."); 
								return;
							} else {
								$this->Drawer($player, $block, $Id_block, $loc, $sub, $ID);
							}
						}
					}
				} else {
					$player->sendMessage("§a(!)Ce CustomDrawer a été compacté vous pouvez donc le transporter sans probléme.");
					return;
				}
			} else {
				$player->sendMessage("§e(!)Ce CustomDrawer a été corrompu veuillez le détruire puis le replacer.");
				return;
			}
		}
	}
	
	public function onBreak(BlockBreakEvent $event){
		$block = $event->getBlock();
		$Id_block = $block->getID() . ":" . $block->getDamage();
		$loc = $block->getX().":".$block->getY().":".$block->getZ().":".$block->getLevel()->getName();
		$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
		$item = ItemFactory::fromString($config->get('Id'));
		$item_id = $item->getID();
		$meta = $item->getDamage();
		$count = $config->get('Count');
		if ($Id_block == "216:0"){
			if(file_exists($this->getDataFolder()."drawers/".strtolower($loc).".yml")){
				$block->getLevel()->dropItem($block, Item::get($item_id, $meta, $count));
				unlink($this->getDataFolder()."drawers/".strtolower($loc).".yml");
			}
		}
	}
	
    public function Drawer(Player $player, $block, $Id_block, $loc, $sub, $ID)
	{
		$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
		$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
		$item = ItemFactory::fromString($config->get('Id'));
		$name = $item->getName();
        $form = new SimpleForm(function (Player $player, $data){
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						$this->AjoutDrawer($player, $config);						
							break;	
						case 2:
						$this->RetireDrawer($player, $config);						
							break;							
					}
				}
			});
        $form->setTitle("§eDrawer");
		$content = "";
		$content .= "Info du drawer : \n\n";
		if ($config->get('Count') !== 0) {
			if ($settup->get($ID) !== 0) {
				$content .= "Blocs : " . $config->get('Id') . " (" . $name . ")" . "\n";
				$content .= "Nombres : " . $config->get('Count') . "/" . $settup->get($ID) . "\n\n";
			} else {
				$content .= "§cLe bloc sous le drawer est incompatible avec celui-ci \n";
			}
		} else {
			$content .= "§eCe drawer est vide vous pouvez le remplire en cliquant sur |AJOUTER DES ITEMS| \n";
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
        $form = new CustomForm(function (Player $player, $data){});			
        $form->setTitle("§aAjouter des itesm");
		$content = "";
		$content .= "Info du drawer:\n\n";
		if ($config->get('Count') >= $settup->get($ID)) {
			$content .= "§eCe drawer est plein il est donc impossible d'ajouter des items. \n";
			$content .= "Pour retirer des items réouvrez le drawer et clickez sur |RETIRER DES ITEMS|. \n\n";
		} else {
			$content .= "Blocs : " . $config->get('Id') . " (" . $name . ")" . "\n";
			$content .= "Nombres : " . $config->get('Count') . "/" . $settup->get($ID) . "\n\n";
		}
		$form->addLabel("$content");
		$form->addInput("Marquez l'id du bloc que vous voulez ajouter (/id)", "ID de l'item ou du bloc");
		$form->addSlider("Combien", 1, 64);
		$form->sendToPlayer($player);
    }

    public function RetireDrawer(Player $player, $loc)
	{
		$config = new Config($this->getDataFolder()."drawers/".strtolower($loc).".yml", Config::YAML);
		$settup = new Config($this->getDataFolder(). "config.yml", Config::YAML);
		$item = ItemFactory::fromString($config->get('Id'));
		$name = $item->getName();
        $form = new CustomForm(function (Player $player, $data){});			
        $form->setTitle("§cRetirer des itesm");
		$content = "";
		$content .= "Info du drawer:\n\n";
		if ($config->get('Count') !== 0) {
			$content .= "Blocs : " . $config->get('Id') . " (" . $name . ")" . "\n";
			$content .= "Nombres : " . $config->get('Count') . "/" . $settup->get($ID) . "\n\n";
		} else {
			$content .= "§eCe drawer est vide il est donc impossible de retirer des items. \n";
			$content .= "Pour ajouter des items réouvrez le drawer et clickez sur |AJOUTER DES ITEMS|. \n\n";
		}
		$form->addLabel("$content");
		$form->addInput("Marquez l'id du bloc que vous voulez retirer (/id)", "ID de l'item ou du bloc");
		$form->addSlider("Combien", 1, 64);
		$form->sendToPlayer($player);
    }	
}