<?php
include("stats.php");


$serialized = trim(file_get_contents("foot"));
$updates = unserialize($serialized);

function time_pairs($gap) {
    global $updates;
    $pairs = array();
    $lower_bound = 0;
    $update_count = count($updates);

    for($i = 0; $i < $update_count; $i++) {
        $first_index = $i;
        $first       = $updates[$first_index];

        for($j = $lower_bound; $j < $update_count; $j++) {
            $u = $updates[$j];
            if ($u->time - $first->time > $gap)
                break;
            $lower_bound = $j;
        }

        $last_index = $lower_bound;

        $last = $updates[$last_index];
        $pairs[] = array($first, $last, $last_index);
    }

    return $pairs;
}


function record($pairs, $skill) {
    global $updates;
    $record_xp = 0;
    $record_pair = null;

    foreach($pairs as $pair) {
        $xp_difference = $pair[1]->xp[$skill] - $pair[0]->xp[$skill];
        if ($xp_difference > $record_xp) {
            $record_pair = $pair;
            $record_xp   = $xp_difference;
        }
    }

    $timespan_end = $record_pair[2];
    $actual_end   = $record_pair[2];

    while($updates[$actual_end - 1]->xp[$skill] == $updates[$timespan_end]->xp[$skill])
        $actual_end--;

    $record = array("xp" => $record_xp, "time" => $updates[$actual_end]->time);
    return $record;
}

function ehp_record($pairs) {
    $record_ehp = 0;
    $record_pair = null;

    foreach($pairs as $pair) {
        $ehp_difference = $pair[1]->ehp - $pair[0]->ehp;
        if ($ehp_difference > $record_ehp) {
            $record_pair = $pair;
            $record_ehp   = $ehp_difference;
        }
    }

    $record = array("ehp" => $record_ehp, "time" => $record_pair[1]->time);
    return $record;
}

for($n = 0; $n < 10; $n++) {
    $times = array(1, 7, 31);
    $pairs_cached = array();

    foreach($times as $time) {
        $pairs_cached[$time] = time_pairs($time * 86400);
    }

    foreach($times as $time) {
        $pairs = $pairs_cached[$time];

        for($i = 0; $i < $SKILL_COUNT; $i++) {
            $record = record($pairs, $i);
            $skill  = skill_name($i);
//        echo "$skill $time day record xp " .  $record["xp"] . "\n";
        }
        $record = ehp_record($pairs);
//    echo "EHP $time day record: " . $record["ehp"] . "\n";
    }
}