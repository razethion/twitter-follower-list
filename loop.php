<?php

$ACCOUNT_ID  = ""; //Enter the ID of the twitter account with the followers you want to list
$URL = "https://api.twitter.com/2/users/" . $ACCOUNT_ID . "/followers"; //The api call
$URL_ARGS = "?max_results=1000"; //This can't be over 1000
$BEARER = ""; //Enter your api bearer token here
$myfile = fopen("outputfile.txt", "a"); //You can change where the txt file outputs here


echo "Searching account ID: " . $ACCOUNT_ID . "\r\n";

$counter = 0;
$token = 0;
$NEW_URL = "";
$FULL_URL = $URL . $URL_ARGS;
echo "\r\n";

do {
    //Checks if this is first run, if not it appends the pagination token to the api call
    if($counter > 0) {
        $NEW_URL = $FULL_URL . "&pagination_token=" .$token;
    } else {
        $NEW_URL = $FULL_URL;
    }

    $curl = curl_init($NEW_URL);
    curl_setopt($curl, CURLOPT_URL, $NEW_URL);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "Authorization: Bearer " . $BEARER,
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    curl_close($curl);

    //decode the json data and response
    $json_a = json_decode($resp, true);
    $counter++;
    if(isset($json_a["meta"]["next_token"])) {
        $token = $json_a["meta"]["next_token"];
    }

    //Loop through each username and list it
    foreach ($json_a['data'] AS $d){
        $DATA = $d['username'] . "\r\n";
        echo $DATA;
        fwrite($myfile, $DATA);
    }

    //Pause every minute (to avoid api rate limiting) unless it's the last page
    if(isset($json_a["meta"]["next_token"])) {
        echo "pausing";
        sleep(61);
    }

} while(isset($json_a["meta"]["next_token"]));
fclose($myfile);