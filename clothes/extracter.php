<?php


function fetchPage($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function getCategories($country)
{
    $categories = [];
    $data = fetchPage("https://www.habbo.${country}/gamedata/external_flash_texts/hashyhash");
    $lines = explode("\n", $data);
    foreach ($lines as $line) {
        if (!strstr($line, "=")) {
            continue;
        }
        list($key, $value) = explode("=", $line);
        $k = explode(".", $key);
        if (sizeof($k) >= 2 && $k[0] == "avatareditor" && $k[1] == "category" && strlen($k[2]) == 2) {
            $categories[$k[2]] = $value;
        }
    }

    return $categories;
}

function getClothingIDs()
{
    $clothing = [];
    $data = fetchPage("https://www.habbo.com/gamedata/figuredata/hashyhash");
    $xml = new SimpleXMLElement($data);

    foreach ($xml->sets[0] as $settype) {
        $cat = (string)$settype->attributes()['type'];
        $clothing[$cat] = [];
        foreach ($settype as $item) {
            $clothing[$cat][(string)$item->attributes()['id']] = "";
        }
    }

    return $clothing;
}

function getClothingNames($country)
{
    $clothing = [];
    $data = fetchPage("https://www.habbo.${country}/gamedata/furnidata_xml/hashyhash");
    $xml = nonStrictXMLParsing($data);

    foreach ($xml->roomitemtypes[0] as $furni) {
        $slug = (string)$furni->attributes()['classname'];
        if (!strstr($slug, "clothing_")) {
            continue;
        }

        $ids = [];
        $id = (string)$furni->customparams;
        if (strstr($id, ",")) {
            foreach (explode(",", $id) as $i) {
                $ids[] = trim($i);
            }
        } else {
            $ids[] = $id;
        }
        foreach ($ids as $id) {
            $clothing[$id] = [
                'slug' => $slug,
                'name' => (string)$furni->name,
                'description' => (string)$furni->description,
            ];
        }

    }

    return $clothing;
}

function nonStrictXMLParsing($xmlData)
{
    libxml_use_internal_errors(true);
    $dom = new DOMDocument("1.0", "UTF-8");
    $dom->strictErrorChecking = false;
    $dom->validateOnParse = false;
    $dom->recover = true;
    $dom->loadXML($xmlData);
    $xml = simplexml_import_dom($dom);

    libxml_clear_errors();
    libxml_use_internal_errors(false);
    return $xml;
}

/* Main */
echo "-- Starting script\n";
$data = [
    'com' => [
        'categories' => [],
        'clothing' => [],
    ],
    'com.br' => [
        'categories' => [],
        'clothing' => [],
    ],
    'com.tr' => [
        'categories' => [],
        'clothing' => [],
    ],
    'de' => [
        'categories' => [],
        'clothing' => [],
    ],
    'es' => [
        'categories' => [],
        'clothing' => [],
    ],
    'fi' => [
        'categories' => [],
        'clothing' => [],
    ],
    'fr' => [
        'categories' => [],
        'clothing' => [],
    ],
    'nl' => [
        'categories' => [],
        'clothing' => [],
    ],
    'it' => [
        'categories' => [],
        'clothing' => [],
    ]
];

$clothingIDs = getClothingIDs();

foreach ($data as $country => $d) {
    echo "Fetching categories for ${country}\n";
    $data[$country]['categories'] = getCategories($country);


    echo "Fetching clothing for ${country}\n";
    $clothingNames = getClothingNames($country);

    foreach ($clothingIDs as $cat => $ids) {
        krsort($ids);
        foreach ($ids as $id => $v) {
            if (key_exists($id, $clothingNames)) {
                $clothing = $clothingNames[$id];
            } else {
                $clothing = null;
            }
            $data[$country]['clothing'][$cat][$id] = $clothing;
        }
    }
    echo "-- Fetching done\n";
}

file_put_contents("extracted.json", json_encode($data, JSON_PRETTY_PRINT));

echo "\nDone!";