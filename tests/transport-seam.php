<?php declare(strict_types=1);

$source = file_get_contents(dirname(__DIR__) . '/GrokImagine.module.php');
$checks = [
    'release version is 1.8.9' => str_contains($source, "'version' => 189"),
    'payload builder is hookable' => str_contains($source, 'public function ___buildGenerationPayload('),
    'transport is hookable' => str_contains($source, 'public function ___sendGenerationRequest('),
    'request handler uses payload seam' => str_contains($source, '$this->buildGenerationPayload($prompt, $aspect, $index)'),
    'request handler uses transport seam' => str_contains($source, '$this->sendGenerationRequest($payload, $apiKey)'),
];

$failed = false;
foreach ($checks as $label => $passed) {
    echo ($passed ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    if (!$passed) $failed = true;
}
exit($failed ? 1 : 0);
