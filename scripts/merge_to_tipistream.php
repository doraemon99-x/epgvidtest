<?php
/**
 * Merge master M3U8 into existing HTML playlist
 */

$sourceM3U = "data/playlists/all.m3u8";
$targetHTML = "data/playlists/tipistream.html";

if (!file_exists($sourceM3U) || !file_exists($targetHTML)) {
    fwrite(STDERR, "Source or target file not found\n");
    exit(1);
}

$m3u = trim(file_get_contents($sourceM3U));
$html = file_get_contents($targetHTML);

/* =========================
   CONVERT M3U -> HTML
   ========================= */
$lines = explode("\n", $m3u);
$output = "<pre>\n";

foreach ($lines as $line) {
    $output .= htmlspecialchars($line) . "\n";
}

$output .= "</pre>";

/* =========================
   REPLACE BETWEEN MARKERS
   ========================= */
$pattern = '/<!-- START AUTO VIDIO -->(.*?)<!-- END AUTO VIDIO -->/s';

$replacement = <<<HTML
<!-- START AUTO VIDIO -->
{$output}
<!-- END AUTO VIDIO -->
HTML;

if (!preg_match($pattern, $html)) {
    fwrite(STDERR, "Marker not found in HTML\n");
    exit(1);
}

$html = preg_replace($pattern, $replacement, $html);

file_put_contents($targetHTML, $html);

echo "Merged master playlist into tipistream.html\n";
