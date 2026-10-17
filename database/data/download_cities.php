<?php

/**
 * Download cities.json from GitHub
 * Run: php database/data/download_cities.php
 */

ini_set('memory_limit', '512M');
set_time_limit(300);

$outputFile = __DIR__ . '/cities.json';
// jsDelivr CDN serves GitHub files reliably even if they're large
$url = 'https://cdn.jsdelivr.net/gh/dr5hn/countries-states-cities-database@master/json/cities.json';

echo "Downloading cities.json from GitHub...\n";
echo "This may take a few minutes for the ~80MB file.\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 300);
curl_setopt($ch, CURLOPT_BUFFERSIZE, 128000);
curl_setopt($ch, CURLOPT_NOPROGRESS, false);
curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($resource, $downloadSize, $downloaded) {
    if ($downloadSize > 0) {
        $percent = round(($downloaded / $downloadSize) * 100, 1);
        $mb = round($downloaded / 1048576, 1);
        echo "\r  Progress: {$percent}% ({$mb} MB)      ";
    }
});

$data = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "\n\n";

if ($httpCode === 200 && $data && strlen($data) > 1000) {
    file_put_contents($outputFile, $data);
    $sizeMB = round(strlen($data) / 1048576, 1);
    echo "SUCCESS: Downloaded {$sizeMB} MB to {$outputFile}\n";
} else {
    echo "FAILED: HTTP {$httpCode}, Error: {$error}\n";
    echo "Please manually download from:\n";
    echo "  https://github.com/dr5hn/countries-states-cities-database/blob/master/json/cities.json\n";
    echo "  and place it in: {$outputFile}\n";
}
