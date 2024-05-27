<?php

class PathBuilder
{
    private $pathUrl;

    /**
     * Creates a new path
     *
     */
    public function __construct()
    {
        $this->pathUrl = '';
    }
    
    /**
     * Add a path
     *
     * @param string $path
     * @return PathBuilder
     */
    public function append($path)
    {
        if ($this->pathUrl !== '') {
            $this->pathUrl = "{$this->pathUrl}/{$path}";
        } else {
            $this->pathUrl = $path;
        }
        return $this;
    }

    /**
     * Add a path
     *
     * @param string $path
     * @return PathBuilder
     */
    public function collection($path)
    {
        if ($this->pathUrl !== '') {
            $this->pathUrl = "{$this->pathUrl}/{$path}";
        } else {
            $this->pathUrl = $path;
        }
        return $this;
    }

    /**
     * Add a path
     *
     * @param string $path
     * @return PathBuilder
     */
    public function document($path)
    {
        if ($this->pathUrl !== '') {
            $this->pathUrl = "{$this->pathUrl}/{$path}";
        } else {
            $this->pathUrl = $path;
        }
        return $this;
    }

    /**
     * Get the complete path
     *
     * @return string
     */
    public function complete()
    {
        return $this->pathUrl;
    }
}