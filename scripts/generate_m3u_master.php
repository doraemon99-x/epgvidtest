<?php
/**
 * Generate MASTER M3U8 playlist (all.m3u8)
 * Source: data/channels/*.json
 */

function clean($text) {
    return trim(str_replace(["\n", "\r"], '', $text));
}

$channelDir = "data/channels";
$outputDir  = "data/playlists";
$outputFile = "{$outputDir}/all.m3u8";

@mkdir($outputDir, 0777, true);

$files = glob($channelDir . "/*.json");

$m3u = "#EXTM3U\n";

foreach ($files as $file) {

    $channelName = ucwords(str_replace('-', ' ', basename($file, '.json')));
    $items = json_decode(file_get_contents($file), true);

    if (empty($items)) {
        continue;
    }

    foreach ($items as $item) {

        if (empty($item['livestreaming_id'])) {
            continue;
        }

        $title = clean($item['title']);
        $logo  = $item['cover_url'] ?? '';
        $lid   = $item['livestreaming_id'];

        $group = "BRI Super League";

        $m3u .= <<<M3U
#EXTINF:-1 tvg-logo="{$logo}" group-title="{$group}",{$title}
#KODIPROP:inputstream=inputstream.adaptive
#KODIPROP:inputstream.adaptive.license_type=com.widevine.alpha
#KODIPROP:inputstream.adaptive.license_key=https://tipiku.biz.id/pally/{$lid}
#KODIPROP:inputstream.adaptive.manifest_type=mpd
#EXTVLCOPT:http-referrer=https://tv.vidio.com/
#EXTVLCOPT:http-user-agent=Mozilla/5.0 (Linux; Android 11; Smart TV Build/AR2101; wv)
https://tipiku.biz.id/dash/{$lid}

M3U;
    }
}

file_put_contents($outputFile, $m3u);

echo "Master playlist generated: data/playlists/all.m3u8\n";
