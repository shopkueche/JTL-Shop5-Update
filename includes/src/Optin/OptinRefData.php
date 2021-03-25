<?php declare(strict_types=1);

namespace JTL\Optin;

use JTL\GeneralDataProtection\IpAnonymizer;

/**
 * Class OptinRefData
 * @package JTL\Optin
 */
class OptinRefData implements \Serializable
{
    /**
     * @var string
     */
    private $optinClass;

    /**
     * @var int
     */
    private $languageID;

    /**
     * @var int
     */
    private $customerID;

    /**
     * @var string
     */
    private $salutation = '';

    /**
     * @var string
     */
    private $firstName = '';

    /**
     * @var string
     */
    private $lastName = '';

    /**
     * @var string
     */
    private $email = '';

    /**
     * @var string
     */
    private $realIP = '';

    /**
     * @var int
     */
    private $productID;

    /**
     * @return string
     */
    public function serialize(): string
    {
        return \serialize([
            $this->optinClass,
            $this->languageID,
            $this->customerID,
            $this->salutation,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->realIP,
            $this->productID
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        [
            $this->optinClass,
            $this->languageID,
            $this->customerID,
            $this->salutation,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->realIP,
            $this->productID
        ] = \unserialize($serialized, ['OptinRefData']);
    }

    /**
     * @param string $optinClass
     * @return OptinRefData
     */
    public function setOptinClass(string $optinClass): self
    {
        $this->optinClass = $optinClass;

        return $this;
    }

    /**
     * @param int $languageID
     * @return OptinRefData
     */
    public function setLanguageID(int $languageID): self
    {
        $this->languageID = $languageID;

        return $this;
    }

    /**
     * @param int $customerID
     * @return OptinRefData
     */
    public function setCustomerID(int $customerID): self
    {
        $this->customerID = $customerID;

        return $this;
    }

    /**
     * @param string $salutation
     * @return OptinRefData
     */
    public function setSalutation(string $salutation): self
    {
        $this->salutation = $salutation;

        return $this;
    }

    /**
     * @param string $firstName
     * @return OptinRefData
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param string $lastName
     * @return OptinRefData
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @param string $email
     * @return OptinRefData
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string $realIP
     * @return OptinRefData
     */
    public function setRealIP(string $realIP): self
    {
        $this->realIP = $realIP;

        return $this;
    }

    /**
     * @param int $productId
     * @return OptinRefData
     */
    public function setProductId(int $productId): self
    {
        $this->productID = $productId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOptinClass(): string
    {
        return $this->optinClass;
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @return int
     */
    public function getCustomerID(): int
    {
        return $this->customerID;
    }

    /**
     * @return string
     */
    public function getSalutation(): string
    {
        return $this->salutation;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getRealIP(): string
    {
        return $this->realIP;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productID;
    }

    /**
     * @return $this
     */
    public function anonymized(): self
    {
        $this->setEmail('anonym');
        $this->setRealIP((new IpAnonymizer($this->getRealIP()))->anonymize());
        $this->setFirstName('anonym');
        $this->setLastName('anonym');

        return $this;
    }

    /**
     * @return false|mixed|string
     */
    public function __toString()
    {
        return $this->serialize();
    }
}
