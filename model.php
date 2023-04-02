<?php

function getData() : array {

    require_once('config.php');

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.mews-demo.com//api/connector/v1/resources/getAll',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => '{
        "ClientToken": "'.CONFIG['ClientToken'].'",
        "AccessToken": "'.CONFIG['AccessToken'].'",
        "Client": "Sample Client 1.0.0",
        "Extent": {
            "Resources": true,
            "ResourceCategories": false,
            "ResourceCategoryAssignments": false,
            "ResourceCategoryImageAssignments": false,
            "ResourceFeatures": false,
            "ResourceFeatureAssignments": false,
            "Inactive": false
        }
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    ));


    $response = curl_exec($curl);

    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);


    if(curl_error($curl)) {
        echo 'Error: ' . curl_error($curl);
        die;
    } else if ($httpcode >= 400) {
        echo 'Error: ' . $httpcode . ' ' . $response;
        die;
    } 

    curl_close($curl);

    //////////////////////////////////////////////////////////


    $data = json_decode($response, true);

    $idName = [];

    foreach ($data['Resources'] as $value) {
        $idName[$value['Id']] = $value['Name'];
    }

    echo "Retrieved Mews API data.\n";

    return $idName;

}

function searchAndUpdateRooms($mewsData) {

    $valuesMewsData = '"' . implode('","', array_values($mewsData)) . '"';

    // Create a PDO object and connect to the SQLite database
    $pdo = new PDO('sqlite:database.sqlite');

    // Prepare a SELECT statement with a WHERE clause that checks for matching values
    $stmt = $pdo->prepare('SELECT * FROM rooms WHERE room_title IN (' . $valuesMewsData .  ')');

    $stmt->execute();

    // Fetch the results and do something with them
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results === false) {
        $error = $stmt->errorInfo();
        echo "Error: " . $error[2];
        die;
    } 

    echo "Found the database room titles that match with Mews API room titles.\n";

    foreach ($results as $row) {

        // 1. Get room_title from $row['room_title']

        $title = $row['room_title'];

        // 2. Find room_title in $mewsData
        // 3. Get room_id from this room_title

        $id = array_search($title, $mewsData);

        // 4. Update table with room_id

        $stmt = $pdo->prepare('UPDATE rooms SET room_id = ? WHERE room_title = ?');

        // Bind the value to the placeholder

        $stmt->bindParam(1, $id);
        $stmt->bindParam(2, $title);

        // Execute the statement
        $stmt->execute();

        // Unset element to be left only with mismatches

        unset($mewsData[$id]); 
        
    }

    echo "Updated IDs of room titles that matched. There are " . count($mewsData) . " room title mismatches in Mews API.\n";

    return $mewsData;

}

function findMismatchesInDatabase() {

    $pdo = new PDO('sqlite:database.sqlite');

    $stmt = $pdo->prepare('SELECT room_title FROM rooms WHERE room_id IS NULL');

    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "There are " . count($results) . " room title mismatches in the database.\n";

    saveMismatches($results, "database");

}

function saveMismatches($mismatches, $mismatchType) {

    $fp = fopen('mismatch_' . $mismatchType . '.csv', 'w');
  
    foreach ($mismatches as $mismatch) {

        if (is_string($mismatch)) {
            fputcsv($fp, [$mismatch]);
        } else {
        fputcsv($fp, $mismatch);
        }
    }

    echo "Saved room title mismatches in .csv\n";

}