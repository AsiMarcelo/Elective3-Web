<?php


foreach (['/GeminiFuncs/GeminiServices.php', '/includes/GeminiFuncs/GeminiServices.php', '/includes/GeminiServices.php'] as $path) {
    if (is_file(__DIR__ . $path)) {
        require_once __DIR__ . $path;
        break;
    }
}
if (!class_exists('GeminiServices')) {
    exit("GeminiServices.php not found. Edit the paths at the top of this file.\n");
}

try {
    $gemini = new GeminiServices();

    if (isset($argv[1])) {
        $result = $gemini->processApplicant((int) $argv[1]);
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "\n";
    } else {
        print_r($gemini->checkDatabase());
    }
} catch (Throwable $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
    if ($e->getPrevious()) {
        echo 'Cause: ', $e->getPrevious()->getMessage(), "\n";
    }
}