<?php declare(strict_types=1);

namespace JTL\Installation;

use Cocur\Slugify\Slugify;
use Faker\Factory as Fake;
use Faker\Generator;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Installation\Faker\de_DE\Commerce;
use JTL\Installation\Faker\ImageProvider;
use stdClass;

/**
 * Class DemoDataInstaller
 * @package JTL\Installation
 */
class DemoDataInstaller
{
    /**
     * number of categories to create.
     */
    public const NUM_CATEGORIES = 10;

    /**
     * number of articles to create.
     */
    public const NUM_ARTICLES = 50;

    /**
     * number of manufacturers to create.
     */
    public const NUM_MANUFACTURERS = 10;

    /**
     * number of customers to create.
     */
    public const NUM_CUSTOMERS = 100;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var Slugify
     */
    private $slugify;

    /**
     * @var \NiceDB
     */
    private $pdo;

    /**
     * @var array
     */
    private static $defaultConfig = [
        'manufacturers' => self::NUM_MANUFACTURERS,
        'categories'    => self::NUM_CATEGORIES,
        'articles'      => self::NUM_ARTICLES,
        'customers'     => self::NUM_CUSTOMERS,
    ];

    /**
     * DemoDataInstaller constructor.
     * @param DbInterface $DB
     * @param array       $config
     */
    public function __construct(DbInterface $DB, array $config = [])
    {
        $this->pdo    = $DB;
        $this->config = \array_merge(static::$defaultConfig, $config);
        $this->faker  = Fake::create('de_DE');
        $this->faker->addProvider(new Commerce($this->faker));
        $this->faker->addProvider(new ImageProvider($this->faker));

        $this->slugify = new Slugify([
            'lowercase' => false,
            'rulesets'  => ['default', 'german'],
        ]);
    }

    protected function execute(): void
    {
        $config = [
            'manufacturers' => \max(0, (int)$this->config['manufacturers']),
            'categories'    => \max(0, (int)$this->config['categories']),
            'articles'      => \max(0, (int)$this->config['articles']),
            'customers'     => \max(0, (int)$this->config['customers']),
        ];
        $steps  = count(\array_filter($config));
        $step   = 1;

        $this->updateRatingsAvg()->updateGlobals();
    }

    /**
     * @param null $callback
     * @return $this
     */
    public function run($callback = null): self
    {
        $this->cleanup()
             ->addCompanyData()
             ->createManufacturers($callback)
             ->createCategories($callback)
             ->createProducts($callback)
             ->updateRatingsAvg()
             ->setConfig()
             ->updateGlobals();

        return $this;
    }

    /**
     * @return $this
     */
    public function setConfig(): self
    {
        $this->pdo->query(
            "UPDATE `teinstellungen`
                SET `cWert`='Y'
                WHERE `kEinstellungenSektion`='107'
                AND cName = 'bewertung_anzeigen';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `teinstellungen`
                SET `cWert`='10'
                WHERE `kEinstellungenSektion`='2'
                AND cName = 'startseite_bestseller_anzahl';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `teinstellungen`
                SET `cWert`='10'
                WHERE `kEinstellungenSektion`='2'
                AND cName = 'startseite_neuimsortiment_anzahl';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `teinstellungen`
                SET `cWert`='10'
                WHERE `kEinstellungenSektion`='2'
                AND cName = 'startseite_sonderangebote_anzahl';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `teinstellungen`
                SET `cWert`='10'
                WHERE `kEinstellungenSektion`='2'
                AND cName = 'startseite_topangebote_anzahl';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen`
                SET `cWert`='Y'
                WHERE `cTemplate`='NOVA'
                AND `cSektion`='megamenu'
                AND `cName`='show_pages';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen`
                SET `cWert`='Y'
                WHERE `cTemplate`='NOVA'
                AND `cSektion`='megamenu'
                AND `cName`='show_manufacturers';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen`
                SET `cWert`='Y'
                WHERE `cTemplate`='NOVA'
                AND `cSektion`='footer'
                AND `cName`='newsletter_footer';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen`
                SET `cWert`='Y'
                WHERE `cTemplate`='NOVA'
                AND `cSektion`='footer'
                AND `cName`='socialmedia_footer';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen`
                SET `cWert`='https://www.facebook.com/JTLSoftware/'
                WHERE `cTemplate`='NOVA'
                AND `cSektion`='footer'
                AND `cName`='facebook';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen`
                SET `cWert`='https://twitter.com/JTLSoftware'
                WHERE `cTemplate`='NOVA'
                AND `cSektion`='footer'
                AND `cName`='twitter';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen`
                SET `cWert`='https://www.youtube.com/user/JTLSoftwareGmbH'
                WHERE `cTemplate`='NOVA'
                AND `cSektion`='footer'
                AND `cName`='youtube';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `ttemplateeinstellungen`
                SET `cWert`='https://www.xing.com/companies/jtl-softwaregmbh'
                WHERE `cTemplate`='NOVA'
                AND `cSektion`='footer'
                AND `cName`='xing';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `tlinksprache`
                SET `cTitle`='Startseite!', `cContent`='" . $this->faker->text(500) . "'
                WHERE `kLink`='3'
                AND `cISOSprache`='ger';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "UPDATE `tlinksprache`
                SET `cTitle`='Home!', `cContent`='" . $this->faker->text(500) . "'
                WHERE `kLink`=3
                AND `cISOSprache`='eng';",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `teinheit` (`kEinheit`, `kSprache`, `cName`)
                VALUES (1,1,'kg'),(1,2,'kg'),(2,1,'ml'),(2,2,'ml'),(3,1,'Stk'),(3,2,'Piece');",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlink` (`kLink`,`kVaterLink`,`kPlugin`,`cName`,`nLinkart`,`cNoFollow`,`cKundengruppen`,
            `cSichtbarNachLogin`,`cDruckButton`,`nSort`,`bSSL`,`bIsFluid`,`cIdentifier`)
                VALUES (100,0,0,'NurEndkunden',1,'N','1;','N','N',0,0,0,'');",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlink` (`kLink`,`kVaterLink`,`kPlugin`,`cName`,`nLinkart`,`cNoFollow`,
          `cKundengruppen`,`cSichtbarNachLogin`,`cDruckButton`,`nSort`,`bSSL`,`bIsFluid`,`cIdentifier`)
                VALUES (101,0,0,'NurHaendler',1,'N','2;','N','N',0,0,0,'');",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlink` (`kLink`,`kVaterLink`,`kPlugin`,`cName`,`nLinkart`,`cNoFollow`,
            `cKundengruppen`,`cSichtbarNachLogin`,`cDruckButton`,`nSort`,`bSSL`,`bIsFluid`,`cIdentifier`)
                VALUES (102,0,9,0,'Beispiel',1,'N',NULL,'N','N',0,0,0,'');",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlink` (`kLink`,`kVaterLink`,`kPlugin`,`cName`,`nLinkart`,`cNoFollow`,
            `cKundengruppen`,`cSichtbarNachLogin`,`cDruckButton`,`nSort`,`bSSL`,`bIsFluid`,`cIdentifier`)
                VALUES (103,102,0,'Kindseite1',1,'N',NULL,'N','N',0,0,0,'');",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlink` (`kLink`,`kVaterLink`,`kPlugin`,`cName`,`nLinkart`,`cNoFollow`,
            `cKundengruppen`,`cSichtbarNachLogin`,`cDruckButton`,`nSort`,`bSSL`,`bIsFluid`,`cIdentifier`)
                VALUES (104,102,0,'Kindseite2',1,'N',NULL,'N','N',0,0,0,'');",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            'INSERT INTO `tlinkgroupassociations` (`linkID`,`linkGroupID`)
                VALUES (100, 9), (101, 9), (102, 9), (103, 9), (104, 9);',
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,
            `cMetaTitle`,`cMetaKeywords`,`cMetaDescription`)
                VALUES (100,'customers-only','eng','Customers only','Customers only','" .
            $this->faker->text(500) . "','','','');",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,
            `cMetaTitle`,`cMetaKeywords`,`cMetaDescription`)
                VALUES (100,'nur-kunden','ger','Nur Endkunden','Nur Endkunden','" .
            $this->faker->text(500) . "','','','');",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,
                `cMetaTitle`,`cMetaKeywords`,`cMetaDescription`)
                VALUES (101,'retailers-only','eng','Retailers only','Retailers only','" .
            $this->faker->text(500) . "','','','');",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,
            `cMetaTitle`,`cMetaKeywords`,`cMetaDescription`)
                VALUES (101,'nur-haendler','ger','Nur Haendler','Nur Haendler','" .
            $this->faker->text(500) . "','','','');",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,
            `cMetaTitle`,`cMetaKeywords`,`cMetaDescription`)
                VALUES (102,'beispiel-seite','ger','Beispielseite','Beispielseite','" .
            $this->faker->text(500) . "','','','');",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,
            `cMetaTitle`,`cMetaKeywords`,`cMetaDescription`)
                VALUES (103,'kindseite-eins','ger','Kindseite1','Kindseite1','" .
            $this->faker->text(500) . "','','','');",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tlinksprache` (`kLink`,`cSeo`,`cISOSprache`,`cName`,`cTitle`,`cContent`,
            `cMetaTitle`,`cMetaKeywords`,`cMetaDescription`)
                VALUES (104,'kindseite-zwei','ger','Kindseite2','Kindseite2','" .
            $this->faker->text(500) . "','','','');",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('nur-endkunden', 'kLink', 100, 3);",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('customers-only', 'kLink', 100, 2);",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('nur-haendler', 'kLink', 101, 3);",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('retailers-only', 'kLink', 101, 2);",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('beispiel-seite', 'kLink', 102, 3);",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('kindseite-eins', 'kLink', 103, 3);",
            ReturnType::DEFAULT
        );
        $this->pdo->query(
            "INSERT INTO `tseo` (`cSeo`,`cKey`,`kKey`,`kSprache`) VALUES ('kindseite-zwei', 'kLink', 104, 3);",
            ReturnType::DEFAULT
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function cleanup(): self
    {
        $this->pdo->query(
            'TRUNCATE TABLE tkategorie; TRUNCATE TABLE tartikel; TRUNCATE TABLE tartikelpict; ' .
            'TRUNCATE TABLE tkategorieartikel; TRUNCATE TABLE tbewertung; TRUNCATE TABLE tartikelext; ' .
            'TRUNCATE TABLE tkategoriepict; TRUNCATE TABLE thersteller; ' .
            'TRUNCATE TABLE tpreis; TRUNCATE TABLE tpreisdetail; TRUNCATE TABLE teinheit; TRUNCATE TABLE tkunde;',
            ReturnType::DEFAULT
        );
        $this->pdo->query('DELETE FROM tlink WHERE kLink > 99;', ReturnType::DEFAULT);
        $this->pdo->query('DELETE FROM tlinksprache WHERE kLink > 99;', ReturnType::DEFAULT);
        $this->pdo->query("DELETE FROM tseo WHERE cKey = 'kLink' AND kKey > 99;", ReturnType::DEFAULT);
        $this->pdo->query(
            "DELETE FROM tseo WHERE cKey = 'kArtikel' OR cKey = 'kKategorie' OR cKey = 'kHersteller'",
            ReturnType::DEFAULT
        );

        return $this;
    }

    /**
     * @return DemoDataInstaller
     */
    public function addCompanyData(): self
    {
        $ins                = new stdClass();
        $ins->cName         = 'Beispiel GmbH';
        $ins->cUnternehmer  = 'Max Mustermann';
        $ins->cStrasse      = 'Zufallsstraße';
        $ins->cHausnummer   = 42;
        $ins->cPLZ          = '12345';
        $ins->cOrt          = 'Beispielshausen';
        $ins->cLand         = 'Deutschland';
        $ins->cTel          = '01234 123456789';
        $ins->cFax          = '01234 123456788';
        $ins->cEMail        = 'info@example.com';
        $ins->cWWW          = 'www.example.com';
        $ins->cKontoinhaber = 'Beispiel GmbH';
        $ins->cBLZ          = '1112250000';
        $ins->cKontoNr      = '1337133713';
        $ins->cBank         = 'Sparkasse Entenhausen';
        $ins->cIBAN         = 'DE257864472';
        $ins->cBIC          = 'FOOOBAR';
        $this->pdo->insert('tfirma', $ins);

        return $this;
    }

    /**
     * @return int
     */
    public function updateGlobals(): int
    {
        return $this->pdo->query('UPDATE tglobals SET dLetzteAenderung = now()', ReturnType::AFFECTED_ROWS);
    }

    /**
     * @return $this
     */
    public function updateRatingsAvg(): self
    {
        $this->pdo->query('TRUNCATE TABLE tartikelext', ReturnType::DEFAULT);
        $this->pdo->query(
            'INSERT INTO tartikelext(kArtikel, fDurchschnittsBewertung)
                SELECT kArtikel, AVG(nSterne) FROM tbewertung GROUP BY kArtikel',
            ReturnType::DEFAULT
        );

        return $this;
    }

    /**
     * @param null $callback
     * @return $this
     */
    public function createManufacturers($callback = null): self
    {
        $maxPk      = (int)$this->pdo->query(
            'SELECT max(kHersteller) AS maxPk FROM thersteller',
            ReturnType::SINGLE_OBJECT
        )->maxPk;
        $limit      = $this->config['manufacturers'];
        $name_index = 0;

        for ($i = 1; $i <= $limit; ++$i) {
            try {
                $_name = $this->faker->unique()->company;
                $res   = $this->pdo->query(
                    'SELECT kHersteller FROM thersteller WHERE cName = "' . $_name . '"',
                    ReturnType::ARRAY_OF_OBJECTS
                );
                if (\is_array($res) && count($res) > 0) {
                    throw new \OverflowException();
                }
            } catch (\OverflowException $e) {
                $_name = $this->faker->unique(true)->company . '_' . ++$name_index;
            }

            $_manufacturer              = new stdClass();
            $_manufacturer->kHersteller = $maxPk + $i;
            $_manufacturer->cName       = $_name;
            $_manufacturer->cSeo        = $this->slug($_name);
            $_manufacturer->cHomepage   = $this->faker->unique()->url;
            $_manufacturer->nSortNr     = 0;
            $_manufacturer->cBildpfad   = $this->createManufacturerImage($_manufacturer->kHersteller, $_name);
            $res                        = $this->pdo->insert('thersteller', $_manufacturer);
            if ($res > 0) {
                $seoItem       = new stdClass();
                $seoItem->cKey = 'kHersteller';
                $seoItem->cSeo = $_manufacturer->cSeo;

                $seo_index = 0;
                while (($data = $this->pdo->select('tseo', 'cKey', $seoItem->cKey, 'cSeo', $seoItem->cSeo)) !== false
                    && \is_array($data)
                    && count($data) > 0
                ) {
                    $seoItem->cSeo = $_manufacturer->cSeo . '_' . ++$seo_index;
                }

                $seoItem->kKey     = $_manufacturer->kHersteller;
                $seoItem->kSprache = 1;
                $this->pdo->insert('tseo', $seoItem);

                $seoItem->cSeo    .= '-en';
                $seoItem->kSprache = 2;
                $this->pdo->insert('tseo', $seoItem);
            }

            $this->callback($callback, $i, $limit, $res > 0, $_name);
        }

        return $this;
    }

    /**
     * @param null $callback
     * @return $this
     */
    public function createCategories($callback = null): self
    {
        $maxPk      = (int)$this->pdo->query(
            'SELECT max(kKategorie) AS maxPk FROM tkategorie',
            ReturnType::SINGLE_OBJECT
        )->maxPk;
        $limit      = $this->config['categories'];
        $name_index = 0;
        for ($i = 1; $i <= $limit; ++$i) {
            try {
                $_name = $this->faker->unique()->department;
                $res   = $this->pdo->query(
                    'SELECT kKategorie FROM tkategorie WHERE cName = "' . $_name . '"',
                    ReturnType::ARRAY_OF_OBJECTS
                );
                if (\is_array($res) && count($res) > 0) {
                    throw new \OverflowException();
                }
            } catch (\OverflowException $e) {
                $_name = $this->faker->unique(true)->department . '_' . ++$name_index;
            }
            $_category                        = new stdClass();
            $_category->kKategorie            = $maxPk + $i;
            $_category->cName                 = $_name;
            $_category->cSeo                  = $this->slug($_name);
            $_category->cBeschreibung         = $this->faker->text(200);
            $_category->kOberKategorie        = \rand(0, $_category->kKategorie - 1);
            $_category->nSort                 = 0;
            $_category->dLetzteAktualisierung = 'now()';
            $_category->lft                   = 0;
            $_category->rght                  = 0;
            $res                              = $this->pdo->insert('tkategorie', $_category);
            if ($res > 0) {
                $_seoEntry       = new stdClass();
                $_seoEntry->cKey = 'kKategorie';
                $_seoEntry->cSeo = $_category->cSeo;

                $seo_index = 0;
                while (($data = $this->pdo->select('tseo', 'cKey', $_seoEntry->cKey, 'cSeo', $_seoEntry->cSeo)) !== false
                    && \is_array($data)
                    && count($data) > 0
                ) {
                    $_seoEntry->cSeo = $_category->cSeo . '_' . ++$seo_index;
                }

                $_seoEntry->kKey     = $_category->kKategorie;
                $_seoEntry->kSprache = 1;
                $this->pdo->insert('tseo', $_seoEntry);

                $_seoEntry->cSeo    .= '-en';
                $_seoEntry->kSprache = 2;
                $this->pdo->insert('tseo', $_seoEntry);

                $this->createCategoryImage($_category->kKategorie, $_name);
            }

            $this->callback($callback, $i, $limit, $res > 0, $_name);
        }
        $this->rebuildCategoryTree(0, 1);

        return $this;
    }

    /**
     * @param null $callback
     * @return $this
     */
    public function createProducts($callback = null): self
    {
        $maxPk             = (int)$this->pdo->query(
            'SELECT max(kArtikel) AS maxPk FROM tartikel',
            ReturnType::SINGLE_OBJECT
        )->maxPk;
        $manufacturesCount = (int)$this->pdo->query(
            'SELECT count(kHersteller) AS mCount FROM thersteller',
            ReturnType::SINGLE_OBJECT
        )->mCount;
        $categoryCount     = (int)$this->pdo->query(
            'SELECT count(kKategorie) AS mCount FROM tkategorie',
            ReturnType::SINGLE_OBJECT
        )->mCount;

        if ($categoryCount === 0) {
            return $this;
        }

        $unitCount = (int)$this->pdo->query(
            'SELECT max(groupCount) AS unitCount
                FROM (
                    SELECT count(*) AS groupCount
                    FROM teinheit
                    GROUP BY kSprache
                ) x',
            ReturnType::SINGLE_OBJECT
        )->unitCount;

        $limit      = $this->config['articles'];
        $name_index = 0;
        $_taxRate   = 19.00;

        for ($i = 1; $i <= $limit; ++$i) {
            try {
                $_name = $this->faker->unique()->productName;
                $res   = $this->pdo->query(
                    'SELECT kArtikel FROM tartikel WHERE cName = "' . $_name . '"',
                    ReturnType::ARRAY_OF_OBJECTS
                );
                if (\is_array($res) && count($res) > 0) {
                    throw new \OverflowException();
                }
            } catch (\OverflowException $e) {
                $_name = $this->faker->unique(true)->productName . '_' . ++$name_index;
            }

            $price                             = \rand(1, 2999);
            $product                           = new stdClass();
            $product->kArtikel                 = $maxPk + $i;
            $product->kHersteller              = \rand(0, $manufacturesCount);
            $product->kLieferstatus            = 0;
            $product->kSteuerklasse            = 1;
            $product->kEinheit                 = (\rand(0, 10) === 10) && $unitCount > 0 ? \rand(1, $unitCount) : 0;
            $product->kVersandklasse           = 1;
            $product->kEigenschaftKombi        = 0;
            $product->kVaterArtikel            = 0;
            $product->kStueckliste             = 0;
            $product->kWarengruppe             = 0;
            $product->kVPEEinheit              = 0;
            $product->kMassEinheit             = 0;
            $product->kGrundpreisEinheit       = 0;
            $product->cName                    = $_name;
            $product->cSeo                     = $this->slug($_name);
            $product->cArtNr                   = $this->faker->ean8();
            $product->cBeschreibung            = $this->faker->text(300);
            $product->cAnmerkung               = '';
            $product->fLagerbestand            = (float)\rand(0, 1000);
            $product->fStandardpreisNetto      = $price / 19.00;
            $product->fMwSt                    = $_taxRate;
            $product->fMindestbestellmenge     = (5 < \rand(0, 10)) ? \rand(0, 5) : 0;
            $product->fLieferantenlagerbestand = 0;
            $product->fLieferzeit              = 0;
            $product->cBarcode                 = $this->faker->ean13;
            $product->cTopArtikel              = (\rand(0, 10) === 10) ? 'Y' : 'N';
            $product->fGewicht                 = (float)\rand(0, 10);
            $product->fArtikelgewicht          = $product->fGewicht;
            $product->fMassMenge               = 0; //@todo?
            $product->fGrundpreisMenge         = 0;
            $product->fBreite                  = 0;
            $product->fHoehe                   = 0;
            $product->fLaenge                  = 0;
            $product->cNeu                     = (\rand(0, 10) === 10) ? 'Y' : 'N';
            $product->cKurzBeschreibung        = $this->faker->text(50);
            $product->fUVP                     = (\rand(0, 10) === 10) ? ($price / 2) : 0;
            $product->cLagerBeachten           = (\rand(0, 10) === 10) ? 'Y' : 'N';
            $product->cLagerKleinerNull        = $product->cLagerBeachten;
            $product->cLagerVariation          = 'N';
            $product->cTeilbar                 = 'N';
            $product->fPackeinheit             = (\rand(0, 10) === 10) ? \rand(1, 12) : 1;
            $product->fAbnahmeintervall        = 0;
            $product->fZulauf                  = 0;
            $product->cVPE                     = 'N';
            $product->fVPEWert                 = 0;
            $product->nSort                    = 0;
            $product->dErscheinungsdatum       = 'now()';
            $product->dErstellt                = 'now()';
            $product->dLetzteAktualisierung    = 'now()';
            $productID                         = $this->pdo->insert('tartikel', $product); //@todo!
            if ($productID > 0) {
                $_maxImages = $this->faker->numberBetween(1, 3);
                for ($k = 0; $k < $_maxImages; ++$k) {
                    $this->createProductImage($product->kArtikel, $_name, $k + 1);
                }
                $_numRatings = $this->faker->numberBetween(0, 6);
                for ($j = 0; $j < $_numRatings; ++$j) {
                    $this->createRating($product->kArtikel);
                }

                $productCategory                    = new stdClass();
                $productCategory->kKategorieArtikel = $product->kArtikel;
                $productCategory->kArtikel          = $product->kArtikel;
                $productCategory->kKategorie        = \rand(1, $categoryCount);
                $this->pdo->insert('tkategorieartikel', $productCategory);

                $seoItem       = new stdClass();
                $seoItem->cKey = 'kArtikel';
                $seoItem->cSeo = $product->cSeo;

                $seo_index = 0;
                while (($data = $this->pdo->select(
                    'tseo',
                    'cKey',
                    $seoItem->cKey,
                    'cSeo',
                    $seoItem->cSeo
                )) !== false
                    && \is_array($data)
                    && count($data) > 0
                ) {
                    $seoItem->cSeo = $product->cSeo . '_' . ++$seo_index;
                }

                $seoItem->kKey     = $product->kArtikel;
                $seoItem->kSprache = 1;
                $this->pdo->insert('tseo', $seoItem);

                $seoItem->cSeo    .= '-en';
                $seoItem->kSprache = 2;
                $this->pdo->insert('tseo', $seoItem);

                $_price2                = new stdClass();
                $_price2->kArtikel      = $product->kArtikel;
                $_price2->kKundengruppe = 1;
                $idxKg1                 = $this->pdo->insert('tpreis', $_price2);
                if ($idxKg1 > 0) {
                    $_price3            = new stdClass();
                    $_price3->kPreis    = $idxKg1;
                    $_price3->nAnzahlAb = 0;
                    $_price3->fVKNetto  = $price / 19.00;
                    $this->pdo->insert('tpreisdetail', $_price3);
                }

                $_price2->kKundengruppe = 2;
                $idxKg2                 = $this->pdo->insert('tpreis', $_price2);
                if ($idxKg2 > 0) {
                    $_price3            = new stdClass();
                    $_price3->kPreis    = $idxKg2;
                    $_price3->nAnzahlAb = 0;
                    $_price3->fVKNetto  = $price / 19.00;
                    $this->pdo->insert('tpreisdetail', $_price3);
                }
            }

            $this->callback($callback, $i, $limit, $productID > 0, $_name);
        }

        return $this;
    }

    /**
     * @param null $callback
     * @return $this
     */
    public function createCustomers($callback = null): self
    {
        $limit  = $this->config['customers'];
        $fake   = $this->faker;
        $pdo    = $this->pdo;
        $secret = \BLOWFISH_KEY;
        $xtea   = new \XTEA($secret);

        for ($i = 1; $i <= $limit; ++$i) {
            if (\rand(0, 1) === 0) {
                $firstName = $fake->firstNameMale;
                $gender    = 'm';
            } else {
                $firstName = $fake->firstNameFemale;
                $gender    = 'w';
            }
            $lastName      = $fake->lastName;
            $streetName    = $fake->streetName;
            $houseNr       = \rand(1, 200);
            $cityName      = $fake->city;
            $postcode      = $fake->postcode;
            $email         = $fake->email;
            $dateofbirth   = $fake->date('Y-m-d', '1998-12-31');
            $password      = \password_hash('pass', \PASSWORD_DEFAULT);
            $streetNameEnc = $xtea->encrypt($streetName);
            $lastNameEnc   = $xtea->encrypt($lastName);
            $lastName      = $fake->lastName;

            $insertObj = (object)[
                'kKundengruppe'      => 1,
                'kSprache'           => 1,
                'cKundenNr'          => '',
                'cPasswort'          => $password,
                'cAnrede'            => $gender,
                'cTitel'             => '',
                'cVorname'           => $firstName,
                'cNachname'          => $lastNameEnc,
                'cFirma'             => '',
                'cZusatz'            => '',
                'cStrasse'           => $streetNameEnc,
                'cHausnummer'        => $houseNr,
                'cAdressZusatz'      => '',
                'cPLZ'               => $postcode,
                'cOrt'               => $cityName,
                'cBundesland'        => '',
                'cLand'              => 'DE',
                'cTel'               => '',
                'cMobil'             => '',
                'cFax'               => '',
                'cMail'              => $email,
                'cUSTID'             => '',
                'cWWW'               => '',
                'cSperre'            => 'N',
                'fGuthaben'          => 0.0,
                'cNewsletter'        => '',
                'dGeburtstag'        => $dateofbirth,
                'fRabatt'            => 0.0,
                'dErstellt'          => 'now()',
                'dVeraendert'        => 'now()',
                'cAktiv'             => 'Y',
                'cAbgeholt'          => 'N',
                'nRegistriert'       => 1,
                'nLoginversuche'     => 0,
                'cResetPasswordHash' => '',
            ];

            $res = $pdo->insert('tkunde', $insertObj);
            $this->callback($callback, $i, $limit, $res > 0, $firstName . ' ' . $lastName);
        }

        return $this;
    }

    /**
     * @param string      $path
     * @param null|string $text
     * @param int         $width
     * @param int         $height
     * @return bool
     */
    private function createImage(string $path, string $text = null, int $width = 500, int $height = 500): bool
    {
        $font     = $this->getFontFile();
        $filepath = $this->faker->imageFile(null, $width, $height, 'jpg', true, $text, null, null, $font);

        return $filepath !== null && \rename($filepath, $path);
    }

    /**
     * @param int    $manufacturerID
     * @param string $text
     * @return string
     */
    private function createManufacturerImage(int $manufacturerID, string $text): string
    {
        if ($manufacturerID > 0) {
            $file        = $this->slug($text) . '.jpg';
            $pathNormal  = \PFAD_ROOT . 'bilder/hersteller/normal/' . $file;
            $pathSmall   = \PFAD_ROOT . 'bilder/hersteller/klein/' . $file;
            $pathStorage = \PFAD_ROOT . 'media/image/storage/manufacturers/' . $file;

            return ($this->createImage($pathNormal, $text) === true
                && $this->createImage($pathSmall, $text, 100, 100) === true
                && $this->createImage($pathStorage, $text, 800, 800) === true)
                ? $file
                : '';
        }

        return '';
    }

    /**
     * @param int    $productID
     * @param string $text
     * @param int    $imageNumber
     */
    private function createProductImage(int $productID, string $text, int $imageNumber): void
    {
        $maxPk = (int)$this->pdo->query(
            'SELECT max(kArtikelPict) AS maxPk FROM tartikelpict',
            ReturnType::SINGLE_OBJECT
        )->maxPk;

        if ($productID > 0) {
            $file = '1024_1024_' . \md5($text . $productID . $imageNumber) . '.jpg';
            $path = \PFAD_ROOT . 'media/image/storage/' . $file;

            if ($this->createImage($path, $text, 1024, 1024) === true) {
                $_image                   = new stdClass();
                $_image->cPfad            = $file;
                $_image->kBild            = $this->pdo->insert('tbild', $_image);
                $_image->kArtikelPict     = $maxPk + 1;
                $_image->kMainArtikelBild = 0;
                $_image->kArtikel         = $productID;
                $_image->nNr              = $imageNumber;
                $this->pdo->insert('tartikelpict', $_image);
            }
        }
    }

    /**
     * @param int    $categoryID
     * @param string $text
     */
    private function createCategoryImage(int $categoryID, string $text): void
    {
        if ($categoryID > 0) {
            $file = $this->slug($text) . '.jpg';
            $path = \PFAD_ROOT . 'bilder/kategorien/' . $file;
            if ($this->createImage($path, $text, 200, 200) === true) {
                $pathStorage = \PFAD_ROOT . 'media/image/storage/categories/' . $file;
                $this->createImage($pathStorage, $text, 800, 800);
                $image             = new stdClass();
                $image->kKategorie = $categoryID;
                $image->cPfad      = $file;
                $this->pdo->insert('tkategoriepict', $image);
            }
        }
    }

    /**
     * @param int $productID
     * @return bool
     */
    private function createRating(int $productID): bool
    {
        if ($productID > 0) {
            $rating                  = new stdClass();
            $rating->kArtikel        = $productID;
            $rating->kKunde          = 0;
            $rating->kSprache        = 1; //@todo: rand(0, 1)?
            $rating->cName           = $this->faker->name;
            $rating->cTitel          = \addcslashes($this->faker->realText(75), '\'"');
            $rating->cText           = $this->faker->text(100);
            $rating->nHilfreich      = \rand(0, 10);
            $rating->nNichtHilfreich = \rand(0, 10);
            $rating->nSterne         = \rand(1, 5);
            $rating->nAktiv          = 1;
            $rating->dDatum          = 'now()';

            return $this->pdo->insert('tbewertung', $rating) > 0;
        }

        return false;
    }

    /**
     * update lft/rght values for categories in the nested set model.
     *
     * @param int $parentId
     * @param int $left
     * @param int $level
     * @return int
     */
    private function rebuildCategoryTree(int $parentId, int $left, int $level = 0): int
    {
        // the right value of this node is the left value + 1
        $right = $left + 1;
        // get all children of this node
        $result = $this->pdo->query(
            'SELECT kKategorie FROM tkategorie WHERE kOberKategorie = ' . $parentId . ' ORDER BY nSort, cName',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($result as $_res) {
            $right = $this->rebuildCategoryTree((int)$_res->kKategorie, $right, $level + 1);
        }
        // we've got the left value, and now that we've processed the children of this node we also know the right value
        $this->pdo->query(
            'UPDATE tkategorie SET lft = ' . $left . ', rght = ' . $right . ', nLevel = ' . $level . '
                WHERE kKategorie = ' . $parentId,
            ReturnType::DEFAULT
        );

        // return the right value of this node + 1
        return $right + 1;
    }

    /**
     * @param string $text
     * @return string
     */
    private function slug($text): string
    {
        return $this->slugify->slugify($text);
    }

    /**
     *
     */
    private function callback(): void
    {
        $arguments = \func_get_args();
        $cb        = \array_shift($arguments);

        if ($cb !== null && \is_callable($cb)) {
            \call_user_func_array($cb, $arguments);
        }
    }

    /**
     * @return string
     */
    private function getFontFile(): string
    {
        return \PFAD_ROOT . 'install/OpenSans-Regular.ttf';
    }
}
