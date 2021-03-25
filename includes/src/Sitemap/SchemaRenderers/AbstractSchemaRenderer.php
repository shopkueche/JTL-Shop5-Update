<?php declare(strict_types=1);

namespace JTL\Sitemap\SchemaRenderers;

/**
 * Class AbstractSchemaRenderer
 * @package JTL\Sitemap\SchemaRenderers
 */
abstract class AbstractSchemaRenderer implements SchemaRendererInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getXmlHeader(): string
    {
        return $this->xmlHeader;
    }

    /**
     * @param string $xmlHeader
     */
    public function setXmlHeader(string $xmlHeader): void
    {
        $this->xmlHeader = $xmlHeader;
    }
}
