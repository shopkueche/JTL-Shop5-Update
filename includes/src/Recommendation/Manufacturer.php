<?php declare(strict_types=1);

namespace JTL\Recommendation;

use stdClass;

/**
 * Class Manufacturer
 * @package JTL\Recommendation
 */
class Manufacturer
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $profileURL;

    /**
     * Manufacturer constructor.
     * @param stdClass $manufacturer
     */
    public function __construct(stdClass $manufacturer)
    {
        $this->setName($manufacturer->company_name);
        $this->setProfileURL($manufacturer->profile_url);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getProfileURL(): string
    {
        return $this->profileURL;
    }

    /**
     * @param string $profileURL
     */
    public function setProfileURL(string $profileURL): void
    {
        $this->profileURL = $profileURL;
    }
}
