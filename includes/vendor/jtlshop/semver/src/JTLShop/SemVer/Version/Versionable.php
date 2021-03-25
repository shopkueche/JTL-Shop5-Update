<?php

namespace JTLShop\SemVer\Version;

/**
 * Class Versionable
 * @package JTLShop\SemVer\Version
 */
abstract class Versionable
{
    /**
     * Prefix of version
     *
     * @var int
     */
    private $prefix;

    /**
     * Major version
     *
     * @var int
     */
    private $major = 0;

    /**
     * Minor version
     *
     * @var int
     */
    private $minor = 0;

    /**
     * Patch version
     *
     * @var int
     */
    private $patch = 0;

    /**
     * @var bool
     */
    private $selectLastPatch = false;

    /**
     * Get the major of the Version
     *
     * @return int
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the major of the Version
     *
     * @param int $prefix
     * @return Versionable
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get the major of the Version
     *
     * @return int
     */
    public function getMajor()
    {
        return $this->major;
    }

    /**
     * Set the major of the Version
     *
     * @param int $major
     * @return Versionable
     */
    public function setMajor($major)
    {
        $this->major = $major;

        return $this;
    }

    /**
     * Get the minor of the Version
     *
     * @return int
     */
    public function getMinor()
    {
        return $this->minor;
    }

    /**
     * Set the minor of the Version
     *
     * @param int $minor
     * @return Versionable
     */
    public function setMinor($minor)
    {
        $this->minor = $minor;

        return $this;
    }

    /**
     * Get the patch of the version
     *
     * @return int
     */
    public function getPatch()
    {
        return $this->patch;
    }

    /**
     * Set the patch of the version
     *
     * @param int $patch
     * @return Versionable
     */
    public function setPatch($patch)
    {
        $this->patch = $patch;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSelectLastPatch()
    {
        return $this->selectLastPatch;
    }

    /**
     * @param bool $selectLastPatch
     * @return Versionable
     */
    public function setSelectLastPatch($selectLastPatch)
    {
        $this->selectLastPatch = $selectLastPatch;

        return $this;
    }

    /**
     * To strings
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->getMajor() === 4 || $this->getMajor() === 3) {
            $format = '%s%d.%02d.%d';
        } else {
            $format = '%s%d.%d.%d';
        }

        return \sprintf(
            $format,
            $this->getPrefix(),
            $this->getMajor(),
            $this->getMinor(),
            $this->getPatch()
        );
    }
}
