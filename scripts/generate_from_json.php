<?php

$data = json_decode(file_get_contents('data/source.json'), true);

@mkdir('data/playlists', 0777, true);
@mkdir('data/epg', 0777, true);

$m3u = "#EXTM3U\n";
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tv>\n";

foreach ($data as $item) {

    $id = "vidio-" . $item['livestreaming_id'];

    // PLAYLIST
    $m3u .= <<<M3U
#EXTINF:-1 tvg-id="{$id}" tvg-logo="{$item['cover_url']}" group-title="BRI Super League",{$item['title']}
#KODIPROP:inputstream=inputstream.adaptive
#KODIPROP:inputstream.adaptive.license_type=com.widevine.alpha
#KODIPROP:inputstream.adaptive.license_key=https://tipiku.biz.id/pally/{$item['livestreaming_id']}
#KODIPROP:inputstream.adaptive.manifest_type=mpd
#EXTVLCOPT:http-referrer=https://tv.vidio.com/
#EXTVLCOPT:http-user-agent=Mozilla/5.0 (Linux; Android 11; Smart TV Build/AR2101; wv)
https://tipiku.biz.id/dash/{$item['livestreaming_id']}

M3U;

    // EPG
    $start = (new DateTime($item['start_time']))->format('YmdHis') . ' +0000';
    $stop  = (new DateTime($item['end_time']))->format('YmdHis') . ' +0000';

    $xml .= <<<XML
<channel id="{$id}">
  <display-name>{$item['title']}</display-name>
</channel>
<programme start="{$start}" stop="{$stop}" channel="{$id}">
  <title>{$item['title']}</title>
</programme>

XML;
}

$xml .= "</tv>";

file_put_contents('data/playlists/all.m3u8', $m3u);
file_put_contents('data/epg/epg.xml', $xml);

echo "Playlist & EPG generated\n";
