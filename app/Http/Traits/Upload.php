<?php

namespace App\Http\Traits;

use Intervention\Image\Facades\Image;

trait Upload
{
    public function makeDirectory($path)
    {
        if (is_dir($path)) {
            // Re-assert permissions even if the directory already existed
            // (e.g. it was copied in at build time with different
            // ownership than the www-data user php-fpm runs as).
            @chmod($path, 0775);
            return true;
        }

        $made = @mkdir($path, 0775, true);
        if ($made) {
            @chmod($path, 0775);
        }
        return $made;
    }

    public function removeFile($path)
    {
        return file_exists($path) && is_file($path) ? @unlink($path) : false;
    }

    /**
     * Config values in config/location.php are relative paths (e.g.
     * "assets/uploads/content/"). Resolving them against whatever the
     * current working directory happens to be is fragile -- PHP-FPM's
     * cwd for a web request follows the front controller's directory,
     * not necessarily the Laravel project root. Anchor to base_path()
     * so uploads always land in the same place regardless of that, and
     * normalize the trailing slash so we never end up with a stray
     * double slash in the stored filename.
     */
    protected function resolveUploadPath($location)
    {
        $location = rtrim($location, '/');

        if (!str_starts_with($location, '/')) {
            $location = base_path($location);
        }

        return $location;
    }

    public function uploadImage($file, $location, $size = null, $old = null, $thumb = null, $filename = null)
    {
        $location = $this->resolveUploadPath($location);

        $path = $this->makeDirectory($location);

        if (!$path) {
            throw new \Exception("Upload directory could not be created or is not writable: {$location}");
        }

        if (!empty($old)) {
            $this->removeFile($location . '/' . $old);
            $this->removeFile($location . '/thumb_' . $old);
        }

        if ($filename == null) {
            $filename = uniqid() . time() . '.' . $file->getClientOriginalExtension();
        }

        $image = Image::make($file);


        if (!empty($size)) {
            $size = explode('x', strtolower($size));
            $image->resize($size[0], $size[1]);
        }
        $image->save($location . '/' . $filename);

        if (!empty($thumb)) {
            $thumb = explode('x', $thumb);
            Image::make($file)->resize($thumb[0], $thumb[1])->save($location . '/thumb_' . $filename);
        }

        return $filename;
    }


}
