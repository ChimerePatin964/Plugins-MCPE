<?php

namespace ChimerePatin964\TheAdder\block;

use pocketmine\block\BlockFactory;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\block\Solid;

class SlimeBlock extends Solid {

	protected $id = Block::SLIME_BLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName(): string{
		return "Slime Block";
	}

	public function getHardness(): float{
		return 0;
	}

	public function hasEntityCollision(): bool{
		return true;
	}

}