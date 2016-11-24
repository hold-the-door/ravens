<?php

namespace Hodor\Config;

interface LoaderInterface
{
    /**
     * @param  string $file_path
     * @return \Hodor\JobQueue\Config
     */
    public function loadFromFile($file_path);
}
