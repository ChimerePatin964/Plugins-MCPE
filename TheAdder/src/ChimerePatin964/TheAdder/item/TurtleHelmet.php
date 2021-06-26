<?php

namespace ChimerePatin964\TheAdder\item;


use pocketmine\item\Armor;

class TurtleHelmet extends Armor
{

    const TURTLE_HELMET = 469;

    public function __construct(int $meta = 0){
        parent::__construct(self::TURTLE_HELMET, $meta, "Casque de tortue");
    }

    public function getDefensePoints() : int{
        return 2;
    }

    public function getMaxDurability() : int{
        return 305;
    }

}