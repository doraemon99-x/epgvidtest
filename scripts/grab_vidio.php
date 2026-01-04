<?php

$url = "https://api.vidio.com/sections/3787-pertandingan-hari-ini";

$headers = [
    "Accept: application/json",
    "Content-Type: application/vnd.api+json",
    "X-Api-App-Info: js/tv.vidio.com",
    "X-Api-Key: " . getenv('VIDIO_API_KEY'),
    "X-Api-Platform: tv-react",
    "X-Secure-Level: 2"
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
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

$result = [];

if (!empty($data['included'])) {
    foreach ($data['included'] as $item) {

        // Filter livestreaming_schedule
        if (
            $item['type'] === 'content' &&
            ($item['attributes']['content_type'] ?? '') === 'livestreaming_schedule'
        ) {
            $result[] = [
                "schedule_id" => $item['attributes']['content_id'],
                "title" => $item['attributes']['title'] ?? null,
                "cover_url" => $item['attributes']['cover_url'] ?? null,
                "livestreaming_id" => $item['links']['self']['meta']['livestreaming_id'] ?? null,
                "start_time" => $item['attributes']['start_time'] ?? null,
                "end_time" => $item['attributes']['end_time'] ?? null
            ];
        }
    }
}

// Simpan ke file
file_put_contents(
    "result.json",
    json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

// Tampilkan ke log
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
