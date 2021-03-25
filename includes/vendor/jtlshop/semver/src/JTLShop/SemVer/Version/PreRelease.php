<?php

namespace JTLShop\SemVer\Version;

/**
 * Class PreRelease
 * @package JTLShop\SemVer\Version
 */
class PreRelease extends Versionable
{
    /**
     * "Greek" name
     *
     * @var string
     */
    private $greek;

    /**
     * Release number
     *
     * @var int
     */
    private $releaseNumber = 0;

    /**
     * Set the "greek" name of the pre-release status
     *
     * @param string $greek
     * @return PreRelease
     */
    public function setGreek($greek)
    {
        $this->greek = $greek;

        return $this;
    }

    /**
     * Set the release number
     *
     * @param int $releaseNumber
     * @return PreRelease
     */
    public function setReleaseNumber($releaseNumber)
    {
        $this->releaseNumber = $releaseNumber;

        return $this;
    }

    /**
     * Get the "greek" name of the pre-release status
     *
     * @return string
     */
    public function getGreek()
    {
        return $this->greek;
    }

    /**
     * Get the release number
     *
     * @return int
     */
    public function getReleaseNumber()
    {
        return $this->releaseNumber;
    }

    /**
     * Get string representation
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->getMajor() > 0 || $this->getMinor() > 0 || $this->getPatch() > 0) {
            return parent::__toString();
        }

        $preReleaseStr = $this->getGreek();

        if ($this->getReleaseNumber() > 0) {
            $preReleaseStr .= '.' . $this->getReleaseNumber();
        }

        return $preReleaseStr;
    }
}
