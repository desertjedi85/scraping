<?php

$cr = curl_init('https://www.gsaadvantage.gov/advantage/main/home.do');
curl_setopt($cr, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, false);    
$user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36';
curl_setopt($cr, CURLOPT_USERAGENT, $user_agent);
curl_setopt($cr, CURLOPT_COOKIEJAR, 'cookie.txt');   
$whoCares = curl_exec($cr); 
curl_close($cr); 

$url = 'https://www.gsaadvantage.gov/advantage/catalog/product_detail.do?gsin=11000011835164';
$cr = curl_init($url);
curl_setopt($cr, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, false);
$user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36';
curl_setopt($cr, CURLOPT_USERAGENT, $user_agent);
curl_setopt($cr, CURLOPT_COOKIEFILE, 'cookie.txt'); 
$gsa = curl_exec($cr);
curl_close($cr);

// echo $gsa;
$dom = new DOMDocument();
@$dom->loadHTML($gsa);

$classname="black8pt";
$finder = new DomXPath($dom);
$spaner = $finder->query("//*[contains(@class, '$classname')]");

$i = 0;
$n = 0;
$matchArray = array();
foreach ($spaner as $element) {
    // echo $element->textContent;
    $textContent = $element->textContent;
    // print_r($);
    // echo $textContent . "<br><br>";
    if (preg_match("/(.*)/",$textContent,$match)) {
        $matchArray[] = $match[0];
        if (preg_match("/Mfr Part No/",$textContent)) {
            $n = $i + 1;
        }
        // print_r($match);
    } else {
        echo "No match found<br>";
    }
    $i++;
}

// print_r($matchArray);

if ($n > 0) {
    $split = preg_split("/=/",$url);
    $gsin = $split[1];
    $partNo = $matchArray[$n];
    updateTable($gsin,$partNo);
    // echo "GSIN No: " . $gsin . "<br>";
    // echo "Part No: " . $matchArray[$n] . "<br>" ;
} else {
    echo "No part numbers found<br>";
}

function updateTable ($gsin,$partNo ) {
    // list($db,$user,$pass,$host) = getConfigData();
    // $ip = $_SERVER["REMOTE_ADDR"];
//    $mysqli = new \mysqli($host, $user, $pass, $db);
    $mysqli = new \mysqli('localhost','root','root','gsaData');
    if ($mysqli->connect_errno) {
        echo "Errno: " . $mysqli->connect_errno . "\n";
        echo "Error: " . $mysqli->connect_error . "\n";
        // exit();
    }

    $stmt = $mysqli->prepare("INSERT INTO gsinToPartNo (gsin,partNo) VALUES (?,?)");
    $stmt->bind_param('ss', $gsin,$partNo);
    if ($stmt === FALSE) {
        die($mysqli->error);
    }
    $stmt->execute();
    echo "GSIN: " . $gsin . " added to database<br>";

    // echo "URL Added Successfully.";
}


// if (preg_match("/Mfr Part No\.:(.*)?Contractor Part No\../",$dom,$match)) {
//     echo "Part No: " . $match[0][0];
// }
// print_r($dom);
// foreach($dom->getElementsByClass('strong') as $link) {
//     # Show the <a href>
//     // echo $link->getAttribute('href');
//     echo $link->textContent;
//     echo "<br />";
// }

// if (preg_match_all("/(\$\d*)/",$gsa,$match)) {
//     echo "Price " . $match[0][0];
// }

// if (preg_match("/\$(\d{1,6}\.\d{2})/",$gsa,$match)) {
//     echo "Price " . $match[1][0];
// }

// echo $gsa;

