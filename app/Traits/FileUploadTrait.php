<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FileUploadTrait
{
    public function handleFileUpload(Request $request, string $inputName, ?string $oldPath = null, string $directory = 'uploads'): ?string
    {
        if (! $request->hasFile($inputName)) {
            return null;
        }

        $file = $request->file($inputName);

        if (! $file->isValid()) {
            return null;
        }

        if (! empty($oldPath)) {
            $this->deleteFile($oldPath);
        }

        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(30) . '.' . $extension;

        $targetDir = public_path($directory);
        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0777, true, true);
        }

        $file->move($targetDir, $filename);

        return $directory . '/' . $filename;
    }

    public function deleteFile(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        $cleanPath = ltrim($path, '/\\');

        if (File::exists(public_path($cleanPath))) {
            @File::delete(public_path($cleanPath));
        }

        if (Storage::disk('public')->exists($cleanPath)) {
            Storage::disk('public')->delete($cleanPath);
        }
    }
}
