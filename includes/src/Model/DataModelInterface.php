<?php declare(strict_types=1);

namespace JTL\Model;

use Exception;
use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use stdClass;

/**
 * Interface DataModelInterface
 * @package JTL\Model
 */
interface DataModelInterface
{
    public const NONE                = 0;
    public const ON_NOTEXISTS_CREATE = 0x001;
    public const ON_NOTEXISTS_NEW    = 0x002;
    public const ON_EXISTS_UPDATE    = 0x004;
    public const ON_NOTEXISTS_FAIL   = 0x008;
    public const ON_INSERT_IGNORE    = 0x0A0;

    public const ERR_NOT_FOUND      = 0x101;
    public const ERR_DUPLICATE      = 0x102;
    public const ERR_INVALID_PARAM  = 0x104;
    public const ERR_NO_PRIMARY_KEY = 0x108;
    public const ERR_DATABASE       = 0x1A0;

    /**
     * fill and load from database or create and store if item does not exist
     *
     * @param array $attributes
     * @param int   $option
     * @return $this
     * @throws Exception
     */
    public function init(array $attributes, $option = self::NONE): self;

    /**
     * Create model in database and return created instance
     *
     * @param array|object $attributes - the base attributes to create this model as an array or simple object
     * @param DbInterface  $db
     * @param int          $option - can be NONE or ON_EXISTS_UPDATE
     *      - NONE: throws Exception with ERR_DUPLICATE if model already exists
     *      - ON_EXISTS_UPDATE: update if model already exists
     *
     * @return static
     * @throws Exception - throws Exception with ERR_DUPLICATE if model already exists and ON_EXISTS_UPDATE is not
     *     specified
     *
     */
    public static function create($attributes, DbInterface $db, $option = self::NONE);

    /**
     * Load model from database and return new instance
     *
     * @param array|object $attributes - the base attributes to load this model as an array or simple object
     *      - Should be at least the primary key
     * @param DbInterface  $db
     * @param int          $option - can be NONE, ON_NOTEXISTS_CREATE or ON_NOTEXISTS_NEW
     *      - NONE: throws Exception with ERR_NOT_FOUND if model doesnt exists
     *      - ON_NOTEXISTS_CREATE: creates model in database and return created instance if model doesnt exists
     *      - ON_NOTEXISTS_NEW: instantiate an empty new model if model doesnt exists
     *
     * @return static
     * @throws Exception - throws Exception with ERR_NOT_FOUND if model doesnt exists and option NONE is specified
     *
     */
    public static function load($attributes, DbInterface $db, $option = self::ON_NOTEXISTS_NEW);

    /**
     * @param array       $attributes
     * @param DbInterface $db
     * @param int         $option
     * @return static
     * @throws Exception
     * @see DataModelInterface::load()
     */
    public static function loadByAttributes($attributes, DbInterface $db, $option = self::ON_NOTEXISTS_NEW);

    /**
     * @param DbInterface  $db
     * @param string|array $key
     * @param mixed        $value
     * @return Collection
     * @throws Exception
     */
    public static function loadAll(DbInterface $db, $key, $value): Collection;

    /**
     * Fill the data model with values from attributes and return itself.
     * Simple creation of a model instance without database operation
     *
     * @param array|object $attributes - the base attributes to fill this model as an array or simple object
     * @return static
     */
    public function fill($attributes): self;

    /**
     * Save the model to database and return true if successful - false otherwise
     *
     * @param array|null $partial - if specified, save only this partiell attributes
     * @return bool
     */
    public function save(array $partial = null): bool;

    /**
     * Delete the model from database and return true if successful deleted or no model where found - false otherwise
     *
     * @return bool
     */
    public function delete(): bool;

    /**
     * Reload the model from database and return model itself
     *
     * @return static
     */
    public function reload();

    /**
     * Get the mapped name for given real attribute name
     *
     * @param string $attribName
     * @return string
     */
    public function getMapping($attribName): string;

    /**
     * Get the value of the primary key of this model
     *
     * @return int
     */
    public function getKey(): int;

    /**
     * Set the value of the primary key of this model and return model itself
     *
     * @param int $value - new value for primary key
     * @return static
     */
    public function setKey($value);

    /**
     * Get the name of the primary key of this model
     * @param bool $realName
     * @return string
     * @throws Exception - throws an ERR_NO_PRIMARY_KEY if no primary key exists
     * @see DataModelInterface::getKeyName()
     */
    public function getKeyName(bool $realName = false): string;

    /**
     * Get the names of all keys of this model
     * @param bool $realName
     * @return array
     * @throws Exception - throws an ERR_NO_PRIMARY_KEY if no primary key exists
     */
    public function getAllKeyNames(bool $realName = false): array;

    /**
     * Set the name of the primary key of this model
     *
     * @param string $keyName - new name for primary key
     * @throws Exception - throws an ERR_INVALID_PARAM if keyName is not a property of this model
     */
    public function setKeyName($keyName): void;

    /**
     * Get the value for an attribute
     *
     * @param string     $attribName - name of the attribute
     * @param null|mixed $default - default value if specified attribute not currently set
     *
     * @return mixed
     * @throws Exception - throws an ERR_INVALID_PARAM if attribName is not a property of this model
     */
    public function getAttribValue($attribName, $default = null);

    /**
     * Set the value for an attribute and return model itself
     *
     * @param string $attribName - name of the attribute
     * @param mixed  $value - new value for the attribute
     *
     * @return static
     * @throws Exception - throws an ERR_INVALID_PARAM if attribName is not a property of this model
     */
    public function setAttribValue($attribName, $value);

    /**
     * Get all attribute definitions of this model as an assoziative array of {@link DataAttribute}(s) (attribute name
     * as key)
     * @return DataAttribute[]
     * @see DataAttribute
     */
    public function getAttributes(): array;

    /**
     * Get model data as json encoded string
     *
     * @param int  $options - bitmask of JSON_ constants, @see json_encode
     * @param bool $iterated
     * @return string|bool
     */
    public function rawJSON(int $options = 0, bool $iterated = false);

    /**
     * Get model data as an assoziative array
     *
     * @param bool $iterated
     * @return array
     */
    public function rawArray(bool $iterated = false): array;

    /**
     * Get model data as a stdClass instance
     *
     * @param bool $iterated = false
     * @return stdClass
     */
    public function rawObject(bool $iterated = false): stdClass;

    /**
     * @param bool $noPrimary
     * @return stdClass
     */
    public function getSqlObject(bool $noPrimary = false): stdClass;

    /**
     * Clone the model into a new, non-existing instance
     *
     * @param array|null $except - array with property names which will not be transferred
     * @return static
     */
    public function replicate(array $except = null);

    /**
     *
     */
    public function wasLoaded(): void;

    /**
     * @return bool
     */
    public function getWasLoaded(): bool;

    /**
     * @param bool $loaded
     */
    public function setWasLoaded(bool $loaded): void;

    /**
     * Get the name of the corresponding database table
     *
     * @return string
     */
    public function getTableName(): string;

    /**
     * @return DbInterface|null
     */
    public function getDB(): ?DbInterface;

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void;
}
