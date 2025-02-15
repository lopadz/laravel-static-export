<?php

namespace Antlur\Export;

use Antlur\Export\Routing\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class Export
{
    public function __construct(public Router $router)
    {
    }

    public function export()
    {
        $this->initOutputDirectory();

        $paths = $this->router->exportPaths();

        foreach ($paths as $path) {
            $html = $this->render($path);

            $this->save($path, $html);
        }
    }

    public function render(string $url): string
    {
        $request = Request::create($url);

        $kernel = app(config('static-export.kernal_namespace'));
        $response = $kernel->handle($request);

        return $response->getContent();
    }

    public function save(string $path, string $html)
    {
        $filename = ($path === '/')
            ? 'index.html'
            : $path.'/index.html';

        $filepath = config('static-export.output_path').'/'.$filename;

        File::ensureDirectoryExists(dirname($filepath));

        File::put($filepath, $html);
    }

    private function initOutputDirectory()
    {
        $outputPath = config('static-export.output_path');

        if (config('static-export.clear_before_export')) {
            File::deleteDirectory($outputPath);
        }

        File::ensureDirectoryExists($outputPath);

        // Copy public directory except index.php
        File::copyDirectory(public_path(), $outputPath);
        File::delete($outputPath.'/index.php');

        // Copy storage directory
        // File::copyDirectory(storage_path('app/public'), $outputPath . '/storage');
    }
}
