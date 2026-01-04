<?php
/**
 * Fetch source data from Indonesia hosting
 */

$url = "https://today.tipiku.biz.id/epgvid.php";

$data = file_get_contents($url);

if ($data === false) {
    fwrite(STDERR, "Failed to fetch source\n");
    exit(1);
}

// Deteksi XML atau JSON
if (str_starts_with(trim($data), '<')) {
    file_put_contents('data/source.xml', $data);
    echo "Fetched XML source\n";
} else {
    file_put_contents('data/source.json', $data);
    echo "Fetched JSON source\n";
}
