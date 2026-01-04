<?php
/**
 * Generate M3U8 playlist from data/channels/*.json
 */

function slug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

$channelDir = "data/channels";
$outputDir  = "data/playlists";

@mkdir($outputDir, 0777, true);

$files = glob($channelDir . "/*.json");

foreach ($files as $file) {

    $channelSlug = basename($file, ".json");
    $items = json_decode(file_get_contents($file), true);

    if (empty($items)) {
        continue;
    }

    $m3u = "#EXTM3U\n";

    foreach ($items as $item) {

        if (empty($item['livestreaming_id'])) {
            continue;
        }

        $title = $item['title'];
        $logo  = $item['cover_url'];
        $lid   = $item['livestreaming_id'];

        // group-title dari channel
        $group = "#1. " . ucfirst(str_replace('-', ' ', $channelSlug));

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

    file_put_contents("{$outputDir}/{$channelSlug}.m3u8", $m3u);
}

echo "M3U8 playlists generated\n";
