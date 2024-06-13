<?php

declare(strict_types=1);

foreach (glob(__DIR__ . '/*') as $path) {
    if (! is_dir($path)) {
        continue;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS),
            static fn (SplFileInfo $file): bool => $file->getBasename()[0] !== '.',
        ),
        RecursiveIteratorIterator::LEAVES_ONLY | RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($files as $filepath => $info) {
        if (! $info->isFile()) {
            continue;
        }

        if ($info->getFilename() !== 'composer.json') {
            continue;
        }

        if (str_contains(dirname($info->getPath()), '/vendor/')) {
            continue;
        }

        echo 'Processing ' . $info->getPath() . "...\n";

        $descriptorspec = [STDIN, STDOUT, STDERR];
        $proc = proc_open(trim('composer install ' . getenv('COMPOSER_FLAGS') ?: ''), $descriptorspec, $pipes, $info->getPath());
        for ($running = true; $running;) {
            $status = proc_get_status($proc);
            $running = $status['running'];
            $exitcode = $status['exitcode'];
        }

        if ($exitcode !== 0) {
            return;
        }
    }
}
