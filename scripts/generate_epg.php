<?php
/**
 * Generate EPG XML (XMLTV) from data/channels/*.json
 */

function xmlEscape($string) {
    return htmlspecialchars($string, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function toXmltvTime($iso) {
    $dt = new DateTime($iso, new DateTimeZone('UTC'));
    return $dt->format('YmdHis') . ' +0000';
}

$channelDir = "data/channels";
$outputDir  = "data/epg";
$outputFile = "{$outputDir}/epg.xml";

@mkdir($outputDir, 0777, true);

$files = glob($channelDir . "/*.json");

$channels = [];
$programs = [];

/* =========================
   COLLECT DATA
   ========================= */
foreach ($files as $file) {

    $items = json_decode(file_get_contents($file), true);

    foreach ($items ?? [] as $item) {

        if (empty($item['livestreaming_id'])) {
            continue;
        }

        $id = "vidio-" . $item['livestreaming_id'];

        $channels[$id] = [
            "id" => $id,
            "name" => $item['title'],
            "icon" => $item['cover_url'] ?? null
        ];

        $programs[] = [
            "channel" => $id,
            "start" => toXmltvTime($item['start_time']),
            "stop" => toXmltvTime($item['end_time']),
            "title" => $item['title'],
            "desc" => "Live Event"
        ];
    }
}

/* =========================
   BUILD XML
   ========================= */
$xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= "<tv generator-info-name=\"Vidio Auto EPG\">\n";

/* ===== CHANNELS ===== */
foreach ($channels as $ch) {
    $xml .= "  <channel id=\"{$ch['id']}\">\n";
    $xml .= "    <display-name>" . xmlEscape($ch['name']) . "</display-name>\n";

    if ($ch['icon']) {
        $xml .= "    <icon src=\"" . xmlEscape($ch['icon']) . "\" />\n";
    }

    $xml .= "  </channel>\n";
}

/* ===== PROGRAMMES ===== */
foreach ($programs as $p) {
    $xml .= "  <programme start=\"{$p['start']}\" stop=\"{$p['stop']}\" channel=\"{$p['channel']}\">\n";
    $xml .= "    <title>" . xmlEscape($p['title']) . "</title>\n";
    $xml .= "    <desc>" . xmlEscape($p['desc']) . "</desc>\n";
    $xml .= "  </programme>\n";
}

$xml .= "</tv>\n";

file_put_contents($outputFile, $xml);

echo "EPG generated: data/epg/epg.xml\n";
