<?php

namespace Jtl\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class JtlResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->response['id'] ?: null;
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->response['name'] ?: null;
    }

    /**
     * Get given name
     *
     * @return string|null
     */
    public function getGivenName()
    {
        return $this->response['given_name'] ?: null;
    }

    /**
     * Get family name
     *
     * @return string|null
     */
    public function getFamilyName()
    {
        return $this->response['family_name'] ?: null;
    }

    /**
     * Get email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['email'] ?: null;
    }

    /**
     * Get user gender
     *
     * @return string|null
     */
    public function getGender()
    {
        return $this->response['gender'] ?: null;
    }

    /**
     * Get user birthdate
     *
     * @return string|null
     */
    public function getBirthdate()
    {
        return $this->response['birthdate'] ?: null;
    }

    /**
     * Get user phone number
     *
     * @return string|null
     */
    public function getPhoneNumber()
    {
        return $this->response['phone_number'] ?: null;
    }

    /**
     * Get user address
     *
     * @return array
     */
    public function getAddress()
    {
        return $this->response['address'] ?: [];
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
