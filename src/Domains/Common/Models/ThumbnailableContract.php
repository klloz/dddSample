<?php

namespace Domains\Common\Models;

use Domains\Common\Models\Storage\FileContract;

interface ThumbnailableContract
{
    /**
     * @return FileContract
     */
    public function file(): FileContract;

    /**
     * @return array
     */
    public function thumbnails(): array;
}
