<?php
declare(strict_types=1);

// Every translatable source string must have an entry in each shipped translation.
$root = dirname(__DIR__);
$sources = [
	'js/topbar.js' => "~\\btr\\('((?:[^'\\\\]|\\\\.)*)'~",
	'lib/Service/PunchService.php' => "~\\\$this->t\\('((?:[^'\\\\]|\\\\.)*)'~",
	'lib/Service/ClientSource.php' => "~translate\\(\\\$l10n, '((?:[^'\\\\]|\\\\.)*)'~",
	'lib/Controller/PunchController.php' => "~->l10n->t\\('((?:[^'\\\\]|\\\\.)*)'~",
	'templates/main.php' => "~\\\$l->t\\('((?:[^'\\\\]|\\\\.)*)'~",
];
$strings = [];
foreach ($sources as $file => $pattern) {
	preg_match_all($pattern, (string)file_get_contents("$root/$file"), $m);
	if ($m[1] === []) { fwrite(STDERR, "FAIL: no strings found in $file\n"); exit(1); }
	foreach ($m[1] as $s) { $strings[stripslashes($s)] = $file; }
}
$failed = false;
foreach (glob("$root/l10n/*.json") as $json) {
	$translations = json_decode((string)file_get_contents($json), true, 512, JSON_THROW_ON_ERROR)['translations'];
	foreach ($strings as $s => $file) {
		if (!array_key_exists($s, $translations)) { fwrite(STDERR, 'FAIL: ' . basename($json) . " missing \"$s\" ($file)\n"); $failed = true; }
	}
	$js = (string)file_get_contents(substr($json, 0, -4) . 'js');
	if (!str_contains($js, 'OC.L10N.register') || substr_count($js, '": "') !== count($translations)) {
		fwrite(STDERR, 'FAIL: ' . basename($json, '.json') . ".js out of sync with .json\n"); $failed = true;
	}
}
if ($failed) { exit(1); }
echo 'PASS: ' . count($strings) . " translatable strings covered in every l10n file.\n";
