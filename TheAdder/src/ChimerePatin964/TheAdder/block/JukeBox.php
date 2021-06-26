<?php

namespace ChimerePatin964\TheAdder\block;

use pocketmine\block\BlockFactory;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\block\Solid;

class JukeBox extends Solid {

	protected $id = self::JUKEBOX;

	public function __construct(int $meta = 0){
		parent::__construct(self::JUKEBOX, $meta);
	}

	public function getName(): string{
		return "Jukebox";
	}

	public function getHardness(): float{
		return 2;
	}

}