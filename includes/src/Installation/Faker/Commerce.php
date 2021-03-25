<?php declare(strict_types=1);

namespace JTL\Installation\Faker;

use Faker\Provider\Base as FakerBase;

/**
 * Class Commerce
 * @package JTL\Installation\Faker
 */
class Commerce extends FakerBase
{
    /**
     * @var array
     */
    protected static $department = [
        'Books', 'Movies', 'Music', 'Games', 'Electronics', 'Computers', 'Home', 'Garden', 'Tools',
        'Grocery', 'Health', 'Beauty', 'Toys', 'Kids', 'Baby', 'Clothing', 'Shoes', 'Jewelery',
        'Sports', 'Outdoors', 'Automotive', 'Industrial',
    ];

    /**
     * @var array
     */
    protected static $adjective = [
        'Small', 'Ergonomic', 'Rustic', 'Intelligent', 'Gorgeous', 'Incredible', 'Fantastic',
        'Practical', 'Sleek', 'Awesome', 'Enormous', 'Mediocre', 'Synergistic', 'Heavy Duty',
        'Lightweight', 'Aerodynamic', 'Durable',
    ];

    protected static $material = [
        'Steel', 'Wooden', 'Concrete', 'Plastic', 'Cotton', 'Granite', 'Rubber', 'Leather',
        'Silk', 'Wool', 'Linen', 'Marble', 'Iron', 'Bronze', 'Copper', 'Aluminum', 'Paper',
    ];

    /**
     * @var array
     */
    protected static $product = [
        'Chair', 'Car', 'Computer', 'Gloves', 'Pants', 'Shirt', 'Table', 'Shoes', 'Hat', 'Plate', 'Knife',
        'Bottle', 'Coat', 'Lamp', 'Keyboard', 'Bag', 'Bench', 'Clock', 'Watch', 'Wallet',
    ];

    /**
     * @return string
     */
    public function productName(): string
    {
        return static::randomElement(static::$adjective)
            . ' ' . static::randomElement(static::$material)
            . ' ' . static::randomElement(static::$product);
    }

    /**
     * @return string
     */
    public function department(): string
    {
        return static::randomElement(static::$department);
    }

    /**
     * @return string
     */
    public function material(): string
    {
        return static::randomElement(static::$material);
    }
}
