<?php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File;

if (!function_exists('uploadBase64Image')){
    function uploadBase64Image($image, $directory) {
        $base64File = $image;

        $fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64File));

        $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
        file_put_contents($tmpFilePath, $fileData);

        $tmpFile = new File($tmpFilePath);

        $file = new UploadedFile(
            $tmpFile->getPathname(),
            $tmpFile->getFilename(),
            $tmpFile->getMimeType(),
            0,
            true // Mark it as test, since the file isn't from real HTTP POST.
        );

        return $file->store($directory, 'public');


    }
}
