<?php
/**
 * Genere includes/legacy-qr-redirects.php depuis un CSV.
 *
 * Usage minimal:
 * php scripts/build-legacy-qr-redirects.php --input="/chemin/fichier.csv"
 *
 * Usage complet:
 * php scripts/build-legacy-qr-redirects.php \
 *   --input="/chemin/fichier.csv" \
 *   --output="includes/legacy-qr-redirects.php" \
 *   --old-column="old_url" \
 *   --new-column="new_url" \
 *   --delimiter=";"
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
	fwrite(STDERR, "Ce script doit etre execute en CLI.\n");
	exit(1);
}

$options = getopt('', array(
	'input:',
	'output::',
	'old-column::',
	'new-column::',
	'delimiter::',
));

$input = isset($options['input']) ? (string) $options['input'] : '';
if ('' === $input || !is_readable($input)) {
	fwrite(STDERR, "CSV introuvable ou non lisible. Utilise --input=\"/chemin/fichier.csv\".\n");
	exit(1);
}

$output = isset($options['output']) && is_string($options['output']) && '' !== $options['output']
	? $options['output']
	: 'includes/legacy-qr-redirects.php';

$oldColumn = isset($options['old-column']) && is_string($options['old-column']) && '' !== $options['old-column']
	? strtolower(trim($options['old-column']))
	: 'old_url';

$newColumn = isset($options['new-column']) && is_string($options['new-column']) && '' !== $options['new-column']
	? strtolower(trim($options['new-column']))
	: 'new_url';

$delimiterOption = isset($options['delimiter']) && is_string($options['delimiter']) ? $options['delimiter'] : ',';
$delimiter = '' !== $delimiterOption ? $delimiterOption[0] : ',';
$enclosure = '"';
$escape = '\\';

$handle = fopen($input, 'r');
if (false === $handle) {
	fwrite(STDERR, "Impossible d'ouvrir le fichier CSV.\n");
	exit(1);
}

$headers = fgetcsv($handle, 0, $delimiter, $enclosure, $escape);
if (!is_array($headers) || empty($headers)) {
	fclose($handle);
	fwrite(STDERR, "CSV vide ou en-tete invalide.\n");
	exit(1);
}

$normalizedHeaders = array();
foreach ($headers as $index => $header) {
	$normalizedHeaders[(int) $index] = strtolower(trim((string) $header));
}

$oldIndex = array_search($oldColumn, $normalizedHeaders, true);
$newIndex = array_search($newColumn, $normalizedHeaders, true);
if (false === $oldIndex || false === $newIndex) {
	fclose($handle);
	fwrite(
		STDERR,
		sprintf(
			"Colonnes introuvables. Colonnes detectees: %s\n",
			implode(', ', $normalizedHeaders)
		)
	);
	exit(1);
}

$map = array();
$errors = array();
$lineNumber = 1;

while (($row = fgetcsv($handle, 0, $delimiter, $enclosure, $escape)) !== false) {
	$lineNumber++;
	$oldRaw = isset($row[(int) $oldIndex]) ? trim((string) $row[(int) $oldIndex]) : '';
	$newRaw = isset($row[(int) $newIndex]) ? trim((string) $row[(int) $newIndex]) : '';

	if ('' === $oldRaw && '' === $newRaw) {
		continue;
	}

	if ('' === $oldRaw || '' === $newRaw) {
		$errors[] = sprintf('Ligne %d: ancienne ou nouvelle URL manquante.', $lineNumber);
		continue;
	}

	if (isset($map[$oldRaw]) && $map[$oldRaw] !== $newRaw) {
		$errors[] = sprintf('Ligne %d: doublon avec destination differente pour "%s".', $lineNumber, $oldRaw);
		continue;
	}

	$map[$oldRaw] = $newRaw;
}

fclose($handle);

ksort($map);

$php = "<?php\n";
$php .= "/**\n";
$php .= " * Mapping des redirections QR legacy.\n";
$php .= " * Genere automatiquement via scripts/build-legacy-qr-redirects.php.\n";
$php .= " */\n\n";
$php .= "return array(\n";

foreach ($map as $legacy => $target) {
	$php .= "\t" . var_export($legacy, true) . ' => ' . var_export($target, true) . ",\n";
}

$php .= ");\n";

$written = file_put_contents($output, $php);
if (false === $written) {
	fwrite(STDERR, "Impossible d'ecrire le fichier de sortie: {$output}\n");
	exit(1);
}

fwrite(STDOUT, sprintf("OK: %d redirections ecrites dans %s\n", count($map), $output));
if (!empty($errors)) {
	fwrite(STDOUT, sprintf("Attention: %d ligne(s) ignoree(s)\n", count($errors)));
	foreach ($errors as $error) {
		fwrite(STDOUT, ' - ' . $error . "\n");
	}
}
