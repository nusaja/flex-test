<?php

require_once('model.php');

$mewsData = getData();

echo "There are " . count($mewsData) . " rooms in Mews API.\n";

$apiMismatches = searchAndUpdateRooms($mewsData);

findMismatchesInDatabase();

saveMismatches($apiMismatches, "api");