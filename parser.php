<?php
include("stats.php");


$serialized = trim(file_get_contents("raw_stats"));
$updates = unserialize($serialized);

function time_pairs($gap) {
    global $updates;
    $pairs = array();
    $lower_bound = 0;

    foreach($updates as $update) {
        $first = $update;
        $last  = $updates[$lower_bound];

        for($i = $lower_bound; $i < count($updates); $i++) {
            $u = $updates[$i];
            if ($u->time - $first->time > $gap)
                break;
            $last = $u;
            $lower_bound = $i;
        }

        $pairs[] = array($first, $last);
    }

    return $pairs;
}

function record($gap, $skill) {
    $pairs = time_pairs($gap);

    $record_xp = 0;
    $record_pair = null;

    foreach($pairs as $pair) {
        $xp_difference = $pair[1]->xp[$skill] - $pair[0]->xp[$skill];
        if ($xp_difference > $record_xp) {
            $record_pair = $pair;
            $record_xp   = $xp_difference;
        }
    }

    $record = array("xp" => $record_xp, "time" => $record_pair[1]->time);
    return $record;
}

echo count(time_pairs(86400)) . "\n";
$month_record = record(86400 * 31, 0);
echo "Month record xp: " . $month_record["xp"] . "\n";