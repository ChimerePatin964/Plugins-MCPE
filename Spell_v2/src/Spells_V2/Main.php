<?php

//    ______ _     _                        ______           _      ____   __   __    
//   / _____) |   (_)                      (_____ \     _   (_)    / __ \ / /  / /    
//  | /     | | _  _ ____   ____  ____ ____ _____) )___| |_  _ ___( (__) ) /_ / /____ 
//  | |     | || \| |    \ / _  )/ ___) _  )  ____/ _  |  _)| |  _ \__  / __ \___   _)
//  | \_____| | | | | | | ( (/ /| |  ( (/ /| |   ( ( | | |__| | | | |/ ( (__) )  | |  
//   \______)_| |_|_|_|_|_|\____)_|   \____)_|    \_||_|\___)_|_| |_/_/ \____/   |_| 

namespace Spells_V2;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\FurnaceRecipe;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\entity\Living;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as TF;
use Spells_V2\Sorts\Feu;
use Spells_V2\Sorts\Air;
use Spells_V2\Sorts\Eau;
use Spells_V2\Sorts\Terre;

class Main extends PluginBase implements Listener
{	

	private static $instance;
	
	public static function getInstance() {
		return self::$instance;
	}
	
    public function onEnable() : void{
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder()."players/");
        $this->getLogger()->info("Plugin Spells_V2 activé! Fait par L'ombre de Fer !");
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
		self::$instance = $this;
	}

    public function onJoin(PlayerJoinEvent $event)
    {
        if(!file_exists($this->getDataFolder()."players/".strtolower($event->getPlayer()->getName()).".yml")){

            $config = new Config($this->getDataFolder()."players/".strtolower($event->getPlayer()->getName()).".yml", Config::YAML);

			$config->set('Clan',0);

			$config->set('Xp-general',1);
			
            $config->set('miner-xp',0);
            $config->set('chasseur-xp',0);
            $config->set('farmer-xp',0);

            $config->set('chasseur-lvl',1);
            $config->set('miner-lvl', 1);
            $config->set('farmer-lvl',1);
			
			$config->set('mana',20);
			$config->set('ManaMax',20);
			
			$config->set('med',0);
			
            $config->save();

        }
		$this->getScheduler()->scheduleRepeatingTask(new EnergieTask($this, $event->getPlayer()), 20);
		$this->getScheduler()->scheduleTask(new Cherker($this, $event->getPlayer()));
    }
	
	public static $config;

// Gestion des commandes

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        switch(strtolower($command->getName())) {
			case "sortinfo":
				$this->Info($sender);						
				break;	
			case "job":
				$this->JobUI($sender);						
				break;	
			case "sort":
				self::$config = new Config($this->getDataFolder()."players/".strtolower($sender->getName()).".yml", Config::YAML);
				$player = $sender;
				$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
				if ($config->get('Clan') == 0) {
					$player->sendMessage("§c(!)Vous devez choisir un attribut pour pouvoir lancer des sorts. En cas de beug lors de la selection des attributs deconnectez vous puis reconnectez vous. Si le probléme persiste contactez un administratuer.");
				}				
				if ($config->get('Clan') == 1) {
					$this->FeuUI($player);
				}
				if ($config->get('Clan') == 2) {
					$this->EauUI($player);
				}
				if ($config->get('Clan') == 3) {
					$this->AirUI($player);
				}
				if ($config->get('Clan') == 4) {
					$this->TerreUI($player);
				}				
		}
		return true;
	}

	public function onPlayerMove(PlayerMoveEvent $event): void{
		$player = $event->getPlayer();
		$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
		if($config->get('med') == 1){
			$config->set('med',0);
			$config->save();
			$player->sendMessage("§c(!)Meditation annulé car vous avez bougée.");
		}
	}

// Systéme de métier

    public function getNetworkId(Entity $entity): int
    {
        return get_class($entity)::NETWORK_ID;
    }
    public function Chasseur(EntityDeathEvent $event){
        $entity = $event->getEntity();
        $malikoum = $event->getEntity()->getLastDamageCause();
        if($malikoum instanceof EntityDamageByEntityEvent) {
            $name = $event->getEntity()->getLastDamageCause()->getDamager();
			$player = $name;
            $config = new Config($this->getDataFolder()."players/".strtolower($name->getName()).".yml", Config::YAML);

            if ($event->getEntity() instanceof Entity) {
				
				if($player instanceof Player) {
					
					if ($config->get('chasseur-lvl') <= 20) {

						if ($config->get('chasseur-xp') < 500 * $config->get('chasseur-lvl')) {

							switch ($this->getNetworkId($entity)) {
								case 13:
									$rand = mt_rand(10,20);
									$config->set('chasseur-xp', $config->get('chasseur-xp') + $rand);
									$config->save();
									$name->sendPopup("§2+ " . $rand . " xp");
									break;
								case 11:
									$rand = mt_rand(20,30);
									$config->set('chasseur-xp', $config->get('chasseur-xp') + $rand);
									$config->save();
									$name->sendPopup("§2+ " . $rand . " xp");
									break;
								case 10:
									$rand = mt_rand(10,20);
									$config->set('chasseur-xp', $config->get('chasseur-xp') + $rand);
									$config->save();
									$name->sendPopup("§2+ " . $rand . " xp");
								case 12:
									$rand = mt_rand(10,20);
									$config->set('chasseur-xp', $config->get('chasseur-xp') + $rand);
									$config->save();
									$name->sendPopup("§2+ " . $rand . " xp");
								case 32:
									$rand = mt_rand(15,25);
									$config->set('chasseur-xp', $config->get('chasseur-xp') + $rand);
									$config->save();
									$name->sendPopup("§2+ " . $rand . " xp");
								case 33:
									$rand = mt_rand(15,20);
									$config->set('chasseur-xp', $config->get('chasseur-xp') + $rand);
									$config->save();
									$name->sendPopup("§2+ " . $rand . " xp");
								case 34:
									$rand = mt_rand(10,15);
									$config->set('chasseur-xp', $config->get('chasseur-xp') + $rand);
									$config->save();
									$name->sendPopup("§2+ " . $rand . " xp");
								case 35:
									$rand = mt_rand(15,25);
									$config->set('chasseur-xp', $config->get('chasseur-xp') + $rand);
									$config->save();
									$name->sendPopup("§2+ " . $rand . " xp");
									break;
							}
                        }

                    } else {

                        $config->set('chasseur-xp', 0);
                        $config->set('chasseur-lvl', $config->get('chasseur-lvl') + 1);
						$config->set('Xp-general', $config->get('Xp-general') + 1);
                        Server::GetInstance()->broadcastMessage($name->getName() . "§b est maintenant niveau §e" . $config->get('chasseur-lvl') . " §b dans le metier de chasseur.");
                        $config->save();
						$this->GainLevel($player);

                    }
                }
            }
    	}
    }

    public function onMiner(BlockBreakEvent $event)
    {
        $config = new Config($this->getDataFolder()."players/".strtolower($event->getPlayer()->getName()).".yml", Config::YAML);
        $player = $event->getPlayer();
        $name = $player->getName();
        $block = $event->getBlock();

        if($config->get('miner-lvl') <= 20) {

            if ($config->get('miner-xp') < 500 * $config->get('miner-lvl')) {
            	                	$name = $event->getPlayer();

                switch ($block->getID()) {

                    case 16:
                        $config->set('miner-xp', $config->get('miner-xp') + 1);
                       $config->save();
                       $name->sendPopup("§2 + 1 xp");

                	     break;

                  

                    case 15:
                    	$rand = mt_rand(3,5);
                        $config->set('miner-xp', $config->get('miner-xp') + $rand);
                        $config->save();
                        $name->sendPopup("§2 + " . $rand . " xp");

                  	  break;

                   

                    case 56:
                        $rand = mt_rand(10,20);
                        $config->set('miner-xp', $config->get('miner-xp') + $rand);
                        $config->save();
                                                $name->sendPopup("§2 + " . $rand . " xp");

                    	break;
                    case 129:
                     $rand = mt_rand(20,30);
                        $config->set('miner-xp', $config->get('miner-xp') + $rand);
                        $config->save();
                                                $name->sendPopup("§2 + " . $rand . " xp");

                        break;
                    case 153:
                     $rand = mt_rand(10,20);
                        $config->set('miner-xp', $config->get('miner-xp') + $rand);
                        $config->save();
                                                $name->sendPopup("§2 + " . $rand . " xp");

                        break;


                }

            } else {

                $config->set('miner-xp', 0);
                $config->set('miner-lvl', $config->get('miner-lvl') + 1);
                $config->set('Xp-general', $config->get('Xp-general') + 1);
                Server::GetInstance()->broadcastMessage($name . "§b est maintenant niveau §e" . $config->get('miner-lvl') . " §b dans le metier de mineur.");
                $config->save();
				$this->GainLevel($player);

            }

        }

        if($config->get('farmer-lvl') <= 20) {

            if ($config->get('farmer-xp') < 500 * $config->get('farmer-lvl')) {
            	                	$name = $event->getPlayer();

                switch ($block->getID()) {

                    case 86:
                    	$rand = mt_rand(5,10);
                        $config->set('farmer-xp', $config->get('farmer-xp') + $rand);
                        $config->save();
                        $name->sendPopup("§2 + " . $rand . " xp");

                	     break;
                    case 103:
                    	$rand = mt_rand(5,10);
                        $config->set('farmer-xp', $config->get('farmer-xp') + $rand);
                        $config->save();
                        $name->sendPopup("§2 + " . $rand . " xp");

                  	  break;                   

                    case 141:
                        $rand = mt_rand(6,12);
                        $config->set('farmer-xp', $config->get('farmer-xp') + $rand);
                        $config->save();
                        $name->sendPopup("§2 + " . $rand . " xp");

                    	break;

                    case 142:
                        $rand = mt_rand(6,12);
                        $config->set('farmer-xp', $config->get('farmer-xp') + $rand);
                        $config->save();
                        $name->sendPopup("§2 + " . $rand . " xp");

                    	break;

                    case 59:
                        $rand = mt_rand(6,12);
                        $config->set('farmer-xp', $config->get('farmer-xp') + $rand);
                        $config->save();
                        $name->sendPopup("§2 + " . $rand . " xp");

                    	break;

                    case 244:
                     $rand = mt_rand(5,10);
                        $config->set('farmer-xp', $config->get('farmer-xp') + $rand);
                        $config->save();
                        $name->sendPopup("§2 + " . $rand . " xp");

                        break;               

                }

            } else {

                $config->set('farmer-xp', 0);
                $config->set('farmer-lvl', $config->get('farmer-lvl') + 1);
				$config->set('Xp-general', $config->get('Xp-general') + 1);
                Server::GetInstance()->broadcastMessage($name . "§b est maintenant niveau §e" . $config->get('farmer-lvl') . " §b dans le metier de farmer.");
                $config->save();
				$this->GainLevel($player);

            }

        }

    }
	
// UI Métiers
	
    public function JobUI(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data){
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						$this->minerUI($player);						
							break;				
						case 2:
						$this->chasseurUI($player);						
							break;
						case 3:
						$this->farmerUI($player);					
							break;
						
					}
				}
			});			

        $form->setTitle("§4Jobs");
        $form->setContent("Bienvenue a toi combattant !");
        $form->addButton("Choisie un métier: ");
		$form->addButton("Mineur");
		$form->addButton("Chasseur");
		$form->addButton("Farmer");
        $form->addButton(TF::RED . ">> Retour");
		$form->sendToPlayer($player);
    }
	
	
    public function minerUI(Player $player)
    {
        $config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
        $form = new SimpleForm(function (Player $player, $data){});
        $form->setTitle("§4Mineur");
        $form->setContent("Mineur information:");
        $form->addButton("Niveau(x):" . $config->get('miner-lvl'));
        $form->addButton("XP: " . $config->get('miner-xp') . "/" . 500 * $config->get('miner-lvl'));
		$form->addButton(500 * $config->get('miner-lvl') - $config->get('miner-xp') . "xp restant pour le prochain niveau");
        $form->addButton(TF::RED . ">> Retour");
		$form->sendToPlayer($player);
    }
    public function chasseurUI(Player $player)
    {
        $config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
        $form = new SimpleForm(function (Player $player, $data){});
        $form->setTitle("§4Chasseur");
        $form->setContent("Chasseur information:");
        $form->addButton("Niveau(x):" . $config->get('chasseur-lvl'));
        $form->addButton("XP: " . $config->get('chasseur-xp') . "/" . 500 * $config->get('chasseur-lvl'));
		$form->addButton(500 * $config->get('chasseur-lvl') - $config->get('chasseur-xp') . "xp restant pour le prochain niveau");
        $form->addButton(TF::RED . ">> Retour");
		$form->sendToPlayer($player);
    }
    public function farmerUI(Player $player)
    {
        $config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
        $form = new SimpleForm(function (Player $player, $data){});
        $form->setTitle("§4Farmer");
        $form->setContent("Farmer information:");
        $form->addButton("Niveau(x):" . $config->get('farmer-lvl'));
        $form->addButton("XP: " . $config->get('farmer-xp') . "/" . 500 * $config->get('farmer-lvl'));
		$form->addButton(500 * $config->get('farmer-lvl') - $config->get('farmer-xp') . "xp restant pour le prochain niveau");
        $form->addButton(TF::RED . ">> Retour");
		$form->sendToPlayer($player);
    }	
	
	
// UI Info	
    public function Info(Player $player)
    {
        $config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
        $form = new SimpleForm(function (Player $player, $data){
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						$this->InfoJob($player);						
							break;
						case 2:
						$this->InfoSort($player);						
							break;							
						
					}
				}
			});
			
        $form->setTitle("§4Info");
		$form->addButton("Choisie une option:");
		$form->addButton("Info Jobs");
		$form->addButton("Info Sorts");
        $form->addButton(TF::RED . ">> Retour");
		$form->sendToPlayer($player);
    }
	
    public function InfoJob(Player $player)
    {
        $config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
        $form = new SimpleForm(function (Player $player, $data){});
        $form->setTitle("§4Info Jobs");
		$content = "";
		$content .= "Salut a toi travailleur et bienvenue dans l'info des jobs :\n\n";
		$content .= "Les métiers sont essentiel au devellopement de tes sorts, en effet en passant un niveau dans un métier cela vas aussi te faire passer un niveu général (nécessaire pour les sorts)\n";
		$content .= "Pour l'instant il y a 3 métiers disponible :\n";
		$content .= "-> le métier de mineur : qui s'augemente en trouvant de nouveaux minerais dans la nature. De plus , plus un minerais est rare plus il va rapporter d'xp \n";
		$content .= "-> le métier de chasseur : qui s'augemente en tuant des animaux ou des mobs . De plus les mobs dangereux rapportent plus d'xp que les animaux \n";
		$content .= "-> le métier de farmer : qui s'augemente en cultivant des plantes . De plus les graines rapportent plus d'xp que les pastéques et melons \n\n";
		$content .= "Pour finir il faut savoir que chaque métier peut etre augementer au niveaux 20 maximum et que la difficultés pour les augementer est exponentiel";
        $form->setContent("$content");
        $form->addButton(TF::RED . ">> Retour");
		$form->sendToPlayer($player);
    }
	
    public function InfoSort(Player $player)
    {
        $config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
        $form = new SimpleForm(function (Player $player, $data){});
        $form->setTitle("§4Info Sorts");
		$content = "";
		$content .= "Salut a toi combattant et bienvenue dans l'info des sorts :\n\n";
		$content .= "Les sorts sont la base du serveur et ils sont particuliérement utiles et puissant néamoins il existe 3 contraintes pour les utiliser :\n";
		$content .= "-> La premiére est votre niveaux géneral celui-ci s'augemente via les métiers (voir l'info job) et permet de débloquer les différents sorts . De plus, plus vous aurez un niveaux général élevé plus vous aurez d'énergie magique. \n";
		$content .= "-> La seconde est la bar d'énergie, cette bar représente votre quantité d'énergie magique .Cette énergie permet de lancer des sorts (chaque sort coute de l'énergie. Pour récupérer votre mana il vous suffit d'attendre ou de méditer pour que cela soit plus rapide. \n";
		$content .= "-> Le cooldown ce dernier facteur n'est pas présent sur tout les sorts mais empéche l'utilisation abusive de certain sorts. \n\n";
        $form->setContent("$content");
        $form->addButton(TF::RED . ">> Retour");
		$form->sendToPlayer($player);
    }
	
    public function GainLevel(Player $player)
    {
		$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
		$config->set('ManaMax',20*$config->get('Xp-general'));
		$config->save();
    }
	
// UI Clans
    public function Clan(PlayerJoinEvent $event)
    {
		$player = $event->getPlayer();
		$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);		
		if ($config->get('Clan') <= 0) {
			$this->ClanUI($player);
		}
    }
    public function ClanUI(Player $player)
    {
		$form = new SimpleForm(function (Player $player, $data){
        $result = $data;
        if($result == null){
			$this->ClanUI($player);	
			return true;
        } else {
            switch($result){
				case 1:
				$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
				$config->set('Clan',1);	
				$config->save();
				$player->sendMessage("§6(!)Felicitaion vous avez bien choisie la classe de Mage de Feu");
					break;
				case 2:
				$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
				$config->set('Clan',2);	
				$config->save();
				$player->sendMessage("§6(!)Felicitaion vous avez bien choisie la classe de Mage d'Eau");				
					break;		
				case 3:
				$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
				$config->set('Clan',3);	
				$config->save();
				$player->sendMessage("§6(!)Felicitaion vous avez bien choisie la classe de Mage d'Air");
					break;
				case 4:
				$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
				$config->set('Clan',4);	
				$config->save();	
				$player->sendMessage("§6(!)Felicitaion vous avez bien choisie la classe de Mage de Terre");
					break;							
						
				}
			}				
			});
			$form->setTitle("§4Choix de l'attribut");
			$content = "";
			$content .= "Salut a toi combattant et bienvenue dans le menu de choix de ton clan.\n\n";
			$content .= "Il existe sur ce serveur 4 differentes attributs. Choisis bien car une fois que tu as choisis ton attribut tu ne peux plus revenir en arriére. \n";			
			$form->setContent("$content");
			$form->addButton("Fais ton choix :");
			$form->addButton("§c Mage de Feu");
			$form->addButton("§9 Mage d'Eau");
			$form->addButton("§a Mage d'Air");
			$form->addButton("§6 Mage de Terre");
			$form->sendToPlayer($player);
	}
	
	
	// SortsUI
	
    public function FeuUI(Player $player)
    {
		$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
		$form = new SimpleForm(function (Player $player, $data){
        $result = $data;
        if($result == null){
        } else {
            switch($result){
				case 1:
				$this->MeditationUI($player);
					break;								
				case 2:
				$this->Heal($player);
					break;	
				case 3:
				Feu::HellHarmorUI($player);
					break;	
				case 4:
				Feu::ExplosionRageUI($player);
					break;					
				}
			}				
			});
			$form->setTitle("§c Sorts de Feu");
			$form->addButton("§l Level General : " . $config->get('Xp-general'));
			$form->addButton("Meditation");
			$form->addButton("Heal");
			$form->addButton("HellArmor");
			$form->addButton("Explosion de Rage");
			$form->sendToPlayer($player);
	}
    public function EauUI(Player $player)
    {
		$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
		$form = new SimpleForm(function (Player $player, $data){
        $result = $data;
        if($result == null){
        } else {
            switch($result){
				case 1:
				$this->MeditationUI($player);
					break;								
				case 2:
				$this->Heal($player);
					break;	
				case 3:
				Eau::FrappeAcideUI($player);
					break;	
				case 4:
				Eau::BombeGlaciaireUI($player);
					break;						
				}
			}				
			});
			$form->setTitle("§9 Sorts d'Eau");
			$form->addButton("§l Level General : " . $config->get('Xp-general'));
			$form->addButton("Meditation");
			$form->addButton("Heal");
			$form->addButton("Frappe Acide");
			$form->addButton("Bombe Glaciaire");
			$form->sendToPlayer($player);
	}
    public function AirUI(Player $player)
    {
		$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
		$form = new SimpleForm(function (Player $player, $data){
        $result = $data;
        if($result == null){
        } else {
            switch($result){
				case 1:
				$this->MeditationUI($player);
					break;								
				case 2:
				$this->Heal($player);
					break;	
				case 3:
				Air::FuiteInvisibleUI($player);
					break;	
				case 4:
				Air::NuageEmpoisonneUI($player);
					break;					
				}
			}				
			});
			$form->setTitle("§a Sorts d'Air");
			$form->addButton("§l Level General : " . $config->get('Xp-general'));
			$form->addButton("Meditation");
			$form->addButton("Heal");
			$form->addButton("Fuite Invisible");
			$form->addButton("Nuage Empoisonné");
			$form->sendToPlayer($player);
	}
    public function TerreUI(Player $player)
    {
		$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
		$form = new SimpleForm(function (Player $player, $data){
        $result = $data;
        if($result == null){
        } else {
            switch($result){
				case 1:
				$this->MeditationUI($player);
					break;								
				case 2:
				$this->Heal($player);
					break;
				case 3:
				Terre::CarapceEpineuseUI($player);
					break;		
				case 4:
				Terre::SableMouvantUI($player);
					break;					
				}
			}				
			});
			$form->setTitle("§6 Sorts de Terre");
			$form->addButton("§l Level General : " . $config->get('Xp-general'));
			$form->addButton("Meditation");
			$form->addButton("Heal");
			$form->addButton("Carapce Epineuse");
			$form->addButton("Sable Mouvant");
			$form->sendToPlayer($player);
	}
	
	// Sorts
	
    public function MeditationUI(Player $player)
    {
        $config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
        $form = new SimpleForm(function (Player $player, $data){
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
						$mana = $config->get('mana');
						$ManaMax = $config->get('ManaMax');	
						if($mana < $ManaMax) {
							$player->sendMessage("§a (!) La meditation vient d'etre lancé.");
							$config->set('med',1);
							$config->save();
							break;	
						} else {
							$player->sendMessage("§c (!) Meditation Impossible car votre mana est plein.");
						}
					}
				}
			});
			
        $form->setTitle("Meditation");
		$content = "";
		$content .= "Info du sort de Meditation:\n\n";
		$content .= "Cout en mana : 0\n";
		$content .= "Utilisable par : Toute les classes \n";
		$content .= "Débloquage : NV 1\n\n";
		$content .= "Description : Il s'agit de la capacité de base des mages leur permettant de regagner leur mana 3 fois plus vite. Neanmoins ils doivent rester immobiles lors de son utilisation.\n";
		$form->setContent("$content");
		$form->addButton("VOTRE MANA : " . $config->get('mana') . "/" . $config->get('ManaMax'));
		$form->addButton("§a|UTILISER|");
		$form->addButton("§c|ANNULER|");
		$form->sendToPlayer($player);
    }
	
    public function Heal(Player $player)
    {
        $config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
        $form = new SimpleForm(function (Player $player, $data){
                $result = $data;
                if($result == null){
                } else {
                    switch($result){
						case 1:
						$this->UseHeal($player);						
							break;								
					}
				}
			});
			
        $form->setTitle("§2 Heal");
		$content = "";
		$content .= "Info du sort de Heal:\n\n";
		$content .= "Cout en mana : 20\n";
		$content .= "Utilisable par : Toute les classes \n";
		$content .= "Débloquage : NV 3\n\n";
		$content .= "Description : Ce sort est le plus basique du serveur il va vous soigner un peu de votre vie et vous rendre toute votre nouriture.\n";
		$form->setContent("$content");
		$form->addButton("VOTRE MANA : " . $config->get('mana') . "/" . $config->get('ManaMax'));
		$form->addButton("§a|UTILISER|");
		$form->addButton("§c|ANNULER|");
		$form->sendToPlayer($player);
    }
    public function UseHeal(Player $player)
    {
        $config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
		if($config->get('Xp-general') >= 3){
			if($config->get('mana') >= 20){
				if($player instanceof Player){
					$player->sendMessage("§a(!)Sort Heal executé.");
					$player->setFood(20);
					$player->setHealth($player->getHealth() + 5);
					$config->set('mana', $config->get('mana') - 20);
					$config->save();
				}
			} else {
				$player->sendMessage("§c(!)Vous n'avez pas assez de mana pour executer ce sort.");
			}
		} else {
			$player->sendMessage("§c(!)Votre niveau générale est trop faible pour executer ce sort.");
		}
	}

// Utilisation de sorts
    public function onEntityDamage(EntityDamageEvent $event){  
		if ($event instanceof EntityDamageByEntityEvent) {
            $player = $event->getEntity();
            $damager = $event->getDamager();
			$damage = $event->getFinalDamage();
			
			if(($damager instanceof Living) && ($player instanceof Player)){
				$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
				if($config->get('HellHarmor') == 1){
					$damager->setOnFire(15);
				}
				if($config->get('FuiteInvisible') == 1){
					$player->addEffect(new EffectInstance(Effect::getEffect(14), 10 * 20, 0, false));
				}			
			}
			if($damager instanceof Player){
				$config = new Config($this->getDataFolder()."players/".strtolower($damager->getName()).".yml", Config::YAML);
				$item = $damager->getInventory()->getItemInHand();
				if($config->get('HellHarmor') == 1){
					if($item->getId() == 0){
						$player->setOnFire(7);
					}
				}
				if($config->get('FrappeAcide') == 1){
					if($item->getId() != 261){
						if(floor($damager->getHealth() + (0.25*$damage)) > $damager->getMaxHealth()) {
							$damager->setHealth($damager->getMaxHealth());
						} else {
							$damager->setHealth(floor($damager->getHealth() + (0.25*$damage)));
						}
					}
				}
			}
		}
	}
	public function onTouch(PlayerInteractEvent $event){
		if($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			return;
		}
		$player = $event->getPlayer();
		$config = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml", Config::YAML);
		if($config->get('P_BombeGlaciaire') == 1){
			$block = $event->getBlock();
			$player->sendMessage("§a(!) La bombe a été posée, elle explosera dans 20 sec.");
			$config->set('P_BombeGlaciaire', 0);
			$config->set('C_BombeGlaciaire', 20);
			$config->save();
			$this->getScheduler()->scheduleRepeatingTask(new Sorts\TaskSort\TaskBombeGlaciaire($this, $player, $block), 20);
		}
		if($config->get('P_NuageEmpoisonne') == 1){
			$block = $event->getBlock();
			$player->sendMessage("§a(!) Le nuage vient d'etre posé, il se terminera dans 1 min.");
			$config->set('P_NuageEmpoisonne', 0);
			$config->set('T_NuageEmpoisonne', 60);
			$config->save();
			$this->getScheduler()->scheduleRepeatingTask(new Sorts\TaskSort\TaskNuageEmpoisonne($this, $player, $block), 20);
		}
	}
	
}