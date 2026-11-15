<?php

namespace App\Http\Controllers;

use App\Models\FeatureAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function download(FeatureAttachment $attachment): StreamedResponse
    {
        $attachment->loadMissing('feature.project');
        $this->authorize('view', $attachment->feature->project);

        abort_unless(Storage::disk('local')->exists($attachment->path), 404, 'File tidak ditemukan.');

        return Storage::disk('local')->download($attachment->path, $attachment->nama_file);
    }
}
