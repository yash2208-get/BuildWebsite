<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Website;
use App\Services\Website\PublishService;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class WebsiteExportController extends Controller
{
    public function __invoke(Website $website, PublishService $publisher): StreamedResponse
    {
        $this->authorize('export', $website);

        $files = $publisher->exportBundle($website);
        $name = Str::slug($website->name).'-export-'.now()->format('Ymd-His').'.zip';
        $tmp = tempnam(sys_get_temp_dir(), 'export');

        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE | ZipArchive::CREATE);

        foreach ($files as $filename => $contents) {
            $zip->addFromString($filename, $contents);
        }

        $zip->close();

        ActivityLog::record('exported', "Website \"{$website->name}\" was exported", $website);

        return response()->streamDownload(function () use ($tmp) {
            readfile($tmp);
            @unlink($tmp);
        }, $name, ['Content-Type' => 'application/zip']);
    }
}
