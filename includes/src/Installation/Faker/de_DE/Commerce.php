<?php declare(strict_types=1);

namespace JTL\Installation\Faker\de_DE;

/**
 * Class Commerce
 * @package JTL\Installation\Faker\de_DE
 */
class Commerce extends \JTL\Installation\Faker\Commerce
{
    /**
     * @var array
     */
    protected static $department = [
        'Antiquitäten & Kunst', 'Auto & Motorrad: Fahrzeuge', 'Auto & Motorrad: Teile', 'Baby',
        'Beauty & Gesundheit', 'Briefmarken', 'Business & Industrie', 'Bücher', 'Büro & Schreibwaren',
        'Computer, Tablets & Netzwerk', 'Feinschmecker', 'Filme & DVDs', 'Foto & Camcorder',
        'Garten & Terrasse', 'Handys & Kommunikation', 'Haushaltsgeräte', 'Haustierbedarf', 'Heimwerker',
        'Immobilien', 'Kleidung & Accessoires', 'Modellbau', 'Musik', 'Musikinstrumente', 'Möbel & Wohnen',
        'Münzen', 'PC- & Videospiele', 'Reisen', 'Sammeln & Seltenes', 'Spielzeug', 'Sport', 'Tickets',
        'TV, Video & Audio', 'Uhren & Schmuck', 'Verschiedenes',
    ];

    /**
     * @var array
     */
    protected static $adjective = [
        'Klein', 'Ergonomisch', 'Rustikal', 'Intelligent', 'Herrlich', 'Unglaublich', 'Fantastisch',
        'Praktisch', 'Geschmeidig', 'Enorm', 'Mittelmäßig', 'Leicht', 'Aerodynamisch', 'Langlebig',
    ];

    /**
     * @var array
     */
    protected static $material = [
        'Stahl', 'Beton', 'Kunststoff', 'Baumwolle', 'Granit', 'Gummi', 'Leder', 'Seide',
        'Wolle', 'Leinen', 'Marmor', 'Eisen', 'Bronze', 'Kupfer', 'Aluminium', 'Papier',
    ];

    /**
     * @var array
     */
    protected static $product = [
        'Stuhl', 'Auto', 'Computer', 'Handschuhe', 'Hose', 'Hemd', 'Tabelle', 'Schuhe', 'Hut',
        'Platte', 'Messer', 'Flasche', 'Mantel',
        'Lampe', 'Tastatur', 'Tasche', 'Bank', 'Uhr', 'Portemonnaie',
    ];

    /**
     * maskulin = 0, feminin = 1, neutral = 2
     *
     * @var array
     */
    protected static $article = [0, 2, 0, 0, 1, 2, 1, 1, 0, 1, 2, 1, 0, 1, 1, 1, 1, 1, 2];

    /**
     * @var array
     */
    protected static $suffix = [0 => 'er', 1 => 'e', 2 => 'es'];

    /**
     * @return string
     */
    public function productName(): string
    {
        $product = static::randomElement(static::$product);
        $suffix  = $this->adjectiveSuffix($product) ?: '';

        return static::randomElement(static::$adjective)
            . $suffix . ' ' . static::randomElement(static::$material) . '-' . $product;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function adjectiveSuffix($name)
    {
        $key = \array_search($name, static::$product, true);
        if (!\array_key_exists($key, static::$article)) {
            return null;
        }
        $article = static::$article[$key];

        return static::$suffix[$article];
    }
}
