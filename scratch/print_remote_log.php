<?php
$data = json_decode(file_get_contents('C:\Users\afan\.gemini\antigravity-ide\brain\b400aa9a-d6a2-46f4-a9f7-b4610419f002/.system_generated/steps/76/output.txt'), true);
$content = $data['content'] ?? '';
$lines = explode("\n", $content);
$tail = array_slice($lines, -40);
echo implode("\n", $tail);
