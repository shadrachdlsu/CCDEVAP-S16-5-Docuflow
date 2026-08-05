<?php
declare(strict_types=1);

function docuflow_document_file_url(?string $storedPath): string
{
    $path = trim(str_replace('\\', '/', (string) $storedPath));

    if ($path === '') {
        return '';
    }

    $urlPath = parse_url($path, PHP_URL_PATH);

    if (!is_string($urlPath) || $urlPath === '') {
        return '';
    }

    $segments = array_values(array_filter(
        explode('/', trim(rawurldecode($urlPath), '/')),
        static fn (string $segment): bool => $segment !== ''
    ));
    $storageIndex = null;

    foreach ($segments as $index => $segment) {
        if (in_array(strtolower($segment), ['uploads', 'pdfs'], true)) {
            $storageIndex = $index;
        }
    }

    if ($storageIndex === null) {
        return '';
    }

    $fileSegments = array_slice($segments, $storageIndex);

    if (
        count($fileSegments) < 2
        || in_array('.', $fileSegments, true)
        || in_array('..', $fileSegments, true)
    ) {
        return '';
    }

    $fileUrl = implode('/', array_map('rawurlencode', $fileSegments));
    $projectUrl = docuflow_project_url_path();

    return $projectUrl === null
        ? '../' . $fileUrl
        : $projectUrl . '/' . $fileUrl;
}

function docuflow_project_url_path(): ?string
{
    $documentRootSetting = trim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));

    if ($documentRootSetting === '') {
        return null;
    }

    $documentRoot = realpath($documentRootSetting);
    $projectRoot = realpath(dirname(__DIR__));

    if ($documentRoot === false || $projectRoot === false) {
        return null;
    }

    $normalizedDocumentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
    $normalizedProjectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');
    $documentRootLength = strlen($normalizedDocumentRoot);
    $isInsideDocumentRoot = strncasecmp(
        $normalizedProjectRoot,
        $normalizedDocumentRoot,
        $documentRootLength
    ) === 0;

    if (
        !$isInsideDocumentRoot
        || (
            strlen($normalizedProjectRoot) > $documentRootLength
            && $normalizedProjectRoot[$documentRootLength] !== '/'
        )
    ) {
        return null;
    }

    $relativeProjectPath = trim(
        substr($normalizedProjectRoot, $documentRootLength),
        '/'
    );

    if ($relativeProjectPath === '') {
        return '';
    }

    return '/' . implode(
        '/',
        array_map('rawurlencode', explode('/', $relativeProjectPath))
    );
}
