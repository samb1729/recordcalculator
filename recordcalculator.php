<?php
include("stats.php");


$serialized = trim(file_get_contents("foot"));
$updates = unserialize($serialized);

function time_triples($gap) {
    global $updates;
    $triples = array();
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
        $triples[] = array($first, $last, $last_index);
    }

    return $triples;
}


function record($triples, $skill) {
    global $updates;
    $record_xp = 0;
    $record_triple = null;

    foreach($triples as $triple) {
        $xp_difference = $triple[1]->xp[$skill] - $triple[0]->xp[$skill];
        if ($xp_difference > $record_xp) {
            $record_triple = $triple;
            $record_xp   = $xp_difference;
        }
    }

    $timespan_end = $record_triple[2];
    $actual_end   = $record_triple[2];

    while($updates[$actual_end - 1]->xp[$skill] == $updates[$timespan_end]->xp[$skill])
        $actual_end--;

    $record = array("xp" => $record_xp, "time" => $updates[$actual_end]->time);
    return $record;
}

function ehp_record($triples) {
    global $updates;
    $record_ehp = 0;
    $record_triple = null;

    foreach($triples as $triple) {
        $ehp_difference = $triple[1]->ehp - $triple[0]->ehp;
        if ($ehp_difference > $record_ehp) {
            $record_triple = $triple;
            $record_ehp   = $ehp_difference;
        }
    }

    $timespan_end = $record_triple[2];
    $actual_end   = $record_triple[2];
    while($updates[$actual_end - 1]->ehp == $updates[$timespan_end]->ehp)
        $actual_end--;

    $record = array("ehp" => $record_ehp, "time" => $record_triple[1]->time);
    return $record;
}

for($n = 0; $n < 10; $n++) {
    $times = array(1, 7, 31);
    $triples_cached = array();

    foreach($times as $time) {
        $triples_cached[$time] = time_triples($time * 86400);
    }

    foreach($times as $time) {
        $triples = $triples_cached[$time];

        for($i = 0; $i < $SKILL_COUNT; $i++) {
            $record = record($triples, $i);
            $skill  = skill_name($i);
//        echo "$skill $time day record xp " .  $record["xp"] . "\n";
        }
        $record = ehp_record($triples);
//    echo "EHP $time day record: " . $record["ehp"] . "\n";
    }
}
