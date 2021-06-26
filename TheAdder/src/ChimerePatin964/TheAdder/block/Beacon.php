<?php

namespace ChimerePatin964\TheAdder\block;

use pocketmine\block\BlockFactory;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\block\Transparent;
use pocketmine\block\Air;

class Beacon extends Transparent {

	protected $id = self::BEACON;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function canBeActivated(): bool{
		return true;
	}

	public function getName(): string{
		return "Beacon";
	}

	public function getLightLevel(): int{
		return 15;
	}

	public function getBlastResistance(): float{
		return 15;
	}

	public function getHardness(): float{
		return 3;
	}

}