<?php


function fetchPage($url): bool|string
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

function getCategories($country): array
{
    $categories = [];
    $data = fetchPage("https://www.habbo.{$country}/gamedata/external_flash_texts/hashyhash");
    $lines = explode("\n", $data);
    foreach ($lines as $line) {
        if (!str_contains($line, "=")) {
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

/**
 * @throws Exception
 */
function getClothingIDs(): array
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
    $data = fetchPage("https://www.habbo.{$country}/gamedata/furnidata_xml/hashyhash");
    $xml = nonStrictXMLParsing($data);

    foreach ($xml->roomitemtypes[0] as $furni) {
        $slug = (string)$furni->attributes()['classname'];
        if (!str_contains($slug, "clothing_") && $slug != "test_nft_clothing" && $slug != "test_nft_clothing2") {
            continue;
        }

        $ids = [];
        $id = (string)$furni->customparams;
        if (str_contains($id, ",")) {
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
                'buyout' => (int)$furni->buyout,
            ];
        }

    }

    return $clothing;
}

function nonStrictXMLParsing($xmlData): SimpleXMLElement|null
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
echo "- Starting script\n";
$countries = ['com', 'com.br', 'com.tr', 'de', 'es', 'fi', 'fr', 'nl', 'it'];

try {
    $clothingIDs = getClothingIDs();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
foreach ($countries as $country) {
    echo "- Extracting: .{$country}\n";
    echo "-- Fetching categories for {$country}\n";
    $data['categories'] = getCategories($country);


    echo "-- Fetching clothing for {$country}\n";
    $clothingNames = getClothingNames($country);

    foreach ($clothingIDs as $cat => $ids) {
        krsort($ids);
        foreach ($ids as $id => $v) {
            if (key_exists($id, $clothingNames)) {
                $clothing = $clothingNames[$id];
            } else {
                $clothing = null;
            }
            $data['clothing'][$cat][$id] = $clothing;
        }
    }
    echo "--- Fetching done\n";
    file_put_contents("{$country}.extracted.json", json_encode($data, JSON_PRETTY_PRINT));
    echo "---- Writing to file complete\n\n";
}

echo "\nDone!";