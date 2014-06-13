<?php

$SKILLS = array('Overall','Attack','Defence','Strength','Hitpoints',
'Ranged','Prayer','Magic','Cooking','Woodcutting',
'Fletching','Fishing','Firemaking','Crafting','Smithing',
'Mining','Herblore','Agility','Thieving','Slayer',
'Farming','Runecrafting','Hunter','Construction');

$SKILL_COUNT = count($SKILLS);

function skill_name($i) {
    global $SKILLS;
    if($i == 99) return 'EHP';
    return $SKILLS[$i];
}

function level_from_xp($xp) {
    $points = 0;
    $lvlXP = 0;
    for ($lvl = 1; $lvl < 127; $lvl++) {
        $points += floor($lvl + 300.0 * pow(2.0, $lvl / 7.0));
        $lvlXP = floor($points / 4);

        if($lvlXP > $xp) {
            return $lvl;
        }
    }
    return 126;
}

function combat_level($attack, $defence, $strength, $hitpoints, $ranged, $prayer, $magic) {
    $lel = max(max($attack + $strength, (int)(1.5 * $magic)), (int)(1.5 * $ranged));
    return (int)(($defence + $hitpoints + ((int)($prayer / 2.0)) + 1.3 * $lel) / 4.0);
}

?>