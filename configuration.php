<?php


/** @var \Icinga\Application\Modules\Module $this */
$cssDirectory = $this->getCssDir();
$cssFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
    $cssDirectory,
    RecursiveDirectoryIterator::CURRENT_AS_PATHNAME | RecursiveDirectoryIterator::SKIP_DOTS
));
foreach ($cssFiles as $path) {
    $this->provideCssFile(ltrim(substr($path, strlen($cssDirectory)), DIRECTORY_SEPARATOR));
}

