<?php

namespace WebChefs\PuppeteerToPdf;

use Exception;
use InvalidArgumentException;

class TemporaryDirectory
{
    /** @var string */
    protected $location;

    /** @var string */
    protected $name;

    /** @var bool */
    protected $forceCreate = false;

    public function __construct($location = '')
    {
        $this->location = $this->sanitizePath($location);
    }

    /** @return $this */
    public function create()
    {
        if (empty($this->location)) {
            $this->location = $this->getSystemTemporaryDirectory();
        }

        if (empty($this->name)) {
            $this->name = str_replace([' ', '.'], '', microtime());
        }

        if ($this->forceCreate && file_exists($this->getFullPath())) {
            $this->deleteDirectory($this->getFullPath());
        }

        if (file_exists($this->getFullPath())) {
            throw new InvalidArgumentException("Path `{$this->getFullPath()}` already exists.");
        }

        if (! file_exists($this->getFullPath())) {
            mkdir($this->getFullPath(), 0777, true);
        }

        return $this;
    }

    /** @return $this */
    public function force()
    {
        $this->forceCreate = true;

        return $this;
    }

    /**
     *  @param string $name
     *
     *  @return $this
     */
    public function name($name)
    {
        $this->name = $this->sanitizeName($name);

        return $this;
    }

    /**
     *  @param string $location
     *
     *  @return $this
     */
    public function location($location)
    {
        $this->location = $this->sanitizePath($location);

        return $this;
    }

    public function path($pathOrFilename = '')
    {
        if (empty($pathOrFilename)) {
            return $this->getFullPath();
        }

        $path = $this->getFullPath().DIRECTORY_SEPARATOR.trim($pathOrFilename, '/');

        $directoryPath = $this->removeFilenameFromPath($path);

        if (! file_exists($directoryPath)) {
            mkdir($directoryPath, 0777, true);
        }

        return $path;
    }

    /** @return $this */
    public function makeEmpty()
    {
        $this->deleteDirectory($this->getFullPath());
        mkdir($this->getFullPath());

        return $this;
    }

    public function delete()
    {
        $this->deleteDirectory($this->getFullPath());
    }

    protected function getFullPath()
    {
        return $this->location.($this->name ? DIRECTORY_SEPARATOR.$this->name : '');
    }

    protected function isValidDirectoryName(string $directoryName)
    {
        return strpbrk($directoryName, '\\/?%*:|"<>') === false;
    }

    protected function getSystemTemporaryDirectory()
    {
        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
    }

    protected function sanitizePath($path)
    {
        $path = rtrim($path);

        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    protected function sanitizeName($name)
    {
        if (! $this->isValidDirectoryName($name)) {
            throw new Exception("The directory name `$name` contains invalid characters.");
        }

        return trim($name);
    }

    protected function removeFilenameFromPath($path)
    {
        if (! $this->isFilePath($path)) {
            return $path;
        }

        return substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR));
    }

    protected function isFilePath($path)
    {
        return strpos($path, '.') !== false;
    }

    protected function deleteDirectory($path)
    {
        if (! file_exists($path)) {
            return true;
        }

        if (! is_dir($path)) {
            return unlink($path);
        }

        foreach (scandir($path) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (! $this->deleteDirectory($path.DIRECTORY_SEPARATOR.$item)) {
                return false;
            }
        }

        return rmdir($path);
    }
}