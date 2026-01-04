<?php
/**
 * Vidio Livestreaming Grabber
 * Split per Channel & League
 */

function slug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

$url = "https://api.vidio.com/sections/3787-pertandingan-hari-ini";

/* =========================
   HEADERS (WAJIB LENGKAP)
   ========================= */
$headers = [
    "Accept: application/json",
    "Content-Type: application/vnd.api+json",
    "X-Api-App-Info: js/tv.vidio.com",
    "X-Api-Key: " . getenv('VIDIO_API_KEY'),
    "X-Api-Platform: tv-react",
    "X-Secure-Level: 2",
    "X-User-Email: " . getenv('VIDIO_USER_EMAIL'),
    "X-User-Token: " . getenv('VIDIO_USER_TOKEN'),
    "X-View-Profile: personal",
    "X-Visitor-Id: " . getenv('VIDIO_VISITOR_ID'),
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    fwrite(STDERR, curl_error($ch));
    exit(1);
}
curl_close($ch);

$data = json_decode($response, true);

/* =========================
   DEBUG AWAL
   ========================= */
$includedCount = count($data['included'] ?? []);
echo "Included count: {$includedCount}\n";

$channels = [];
$leagues  = [];

/* =========================
   PARSING DATA
   ========================= */
foreach ($data['included'] ?? [] as $item) {

    if (
        $item['type'] !== 'content' ||
        ($item['attributes']['content_type'] ?? '') !== 'livestreaming_schedule'
    ) {
        continue;
    }

    $attr = $item['attributes'];

    $entry = [
        "schedule_id" => $attr['content_id'],
        "title" => $attr['title'],
        "channel" => $attr['alt_title'] ?? $attr['livestreaming_title'] ?? "Unknown",
        "cover_url" => $attr['cover_url'] ?? null,
        "livestreaming_id" => $item['links']['self']['meta']['livestreaming_id'] ?? null,
        "start_time" => $attr['start_time'],
        "end_time" => $attr['end_time'],
        "web_url" => $attr['web_url'] ?? null
    ];

    /* ===== SPLIT CHANNEL ===== */
    $channelSlug = slug($entry['channel']);
    $channels[$channelSlug][] = $entry;

    /* ===== SPLIT LIGA ===== */
    // ambil bagian awal judul (umumnya nama liga/event)
    $leagueName = explode('-', $entry['title'])[0];
    $leagueSlug = slug($leagueName);
    $leagues[$leagueSlug][] = $entry;
}

/* =========================
   SIMPAN FILE
   ========================= */
@mkdir("data/channels", 0777, true);
@mkdir("data/leagues", 0777, true);

foreach ($channels as $slug => $items) {
    file_put_contents(
        "data/channels/{$slug}.json",
        json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
}

foreach ($leagues as $slug => $items) {
    file_put_contents(
        "data/leagues/{$slug}.json",
        json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
}

echo "Generated " . count($channels) . " channels & " . count($leagues) . " leagues\n";
