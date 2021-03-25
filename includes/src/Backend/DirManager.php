<?php

namespace JTL\Backend;

/**
 * Class DirManager
 * @package Backend
 */
class DirManager
{
    /**
     * @var string
     */
    public $filename;

    /**
     * @var bool
     */
    public $isdir;

    /**
     * @var string
     */
    public $path;

    /**
     * Userfunc (callback function) must have 1 parameter (array)
     *
     * @param string        $path
     * @param callable|null $userfunc
     * @param array|null    $parameters
     * @return $this
     */
    public function getData(string $path, callable $userfunc = null, array $parameters = null): self
    {
        $islinux = true;
        if (\mb_strpos($path, '\\') !== false) {
            $islinux = false;
        }
        if ($islinux) {
            if (\mb_strpos(\mb_substr($path, \mb_strlen($path) - 1, 1), '/') === false) {
                $path .= '/';
            }
        } elseif (\mb_strpos(\mb_substr($path, \mb_strlen($path) - 1, 1), '\\') === false) {
            $path .= '\\';
        }
        if (\is_dir($path)) {
            $this->path = $path;
            $dirhandle  = @\opendir($path);
            if ($dirhandle) {
                while (($file = \readdir($dirhandle)) !== false) {
                    if ($file !== '.' && $file !== '..' && $file !== '.svn' && $file !== '.git') {
                        $this->filename = $file;
                        // Go 1 level deeper
                        if (\is_dir($path . $file)) {
                            $this->isdir = true;
                            $this->getData($path . $file, $userfunc, $parameters);
                        }
                        // Last level dir?
                        $options = [
                            'filename' => $file,
                            'path'     => $path,
                            'isdir'    => false
                        ];
                        if (\is_dir($path . $file)) {
                            $options['isdir'] = true;
                        }
                        if ($parameters !== null && \is_array($parameters)) {
                            $options = \array_merge($options, $parameters);
                        }
                        if ($userfunc !== null) {
                            $userfunc($options);
                        }
                    }
                }

                @\closedir($dirhandle);
            }
        }

        return $this;
    }
}
