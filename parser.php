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
        }

        $pairs[] = array($first, $last);
    }

    return $pairs;
}

echo count(time_pairs(86400)) . "\n";
