<?php declare(strict_types=1);

namespace JTL\Template;

use SimpleXMLElement;
use stdClass;

/**
 * Class XMLReader
 * @package JTL\Template
 */
class XMLReader
{
    /**
     * @param string $dirName
     * @param bool   $isAdmin
     * @return SimpleXMLElement|null
     */
    public function getXML(string $dirName, bool $isAdmin = false): ?SimpleXMLElement
    {
        $dirName = \basename($dirName);
        $xmlFile = $isAdmin === false
            ? \PFAD_ROOT . \PFAD_TEMPLATES . $dirName . \DIRECTORY_SEPARATOR . \TEMPLATE_XML
            : \PFAD_ROOT . \PFAD_ADMIN . \PFAD_TEMPLATES . $dirName . \DIRECTORY_SEPARATOR . \TEMPLATE_XML;
        if (!\file_exists($xmlFile)) {
            return null;
        }
        if (\defined('LIBXML_NOWARNING')) {
            // try to suppress warning if opening fails
            $xml = \simplexml_load_file($xmlFile, 'SimpleXMLElement', \LIBXML_NOWARNING);
        } else {
            $xml = \simplexml_load_file($xmlFile);
        }
        if ($xml === false) {
            $xml = \simplexml_load_string(\file_get_contents($xmlFile));
        }

        if (\is_a($xml, SimpleXMLElement::class)) {
            $xml->dir = $dirName;
        } else {
            $xml = null;
        }

        return $xml;
    }

    /**
     * @param string      $targetDir - the current template's dir name
     * @param string|null $parent
     * @return array
     */
    public function getConfigXML(string $targetDir, ?string $parent = null): array
    {
        $sections = [];
        $ignored  = []; // list of settings that are overridden by child
        foreach (\array_filter([$targetDir, $parent]) as $dir) {
            $xml = $this->getXML($dir);
            if ($xml === null || !isset($xml->Settings, $xml->Settings->Section)) {
                continue;
            }
            $sections = $this->parseSections($xml->Settings->Section, $sections, $ignored, $dir);
        }

        return $sections;
    }

    /**
     * @param SimpleXMLElement $xmlSections
     * @param array            $sections
     * @param string           $dir
     * @param array            $ignored
     * @return stdClass[]
     */
    protected function parseSections(SimpleXMLElement $xmlSections, array &$sections, array &$ignored, string $dir): array
    {
        foreach ($xmlSections as $xmlSection) {
            $section   = null;
            $sectionID = (string)$xmlSection->attributes()->Key;
            $exists    = false;
            foreach ($sections as $_section) {
                if ($_section->key === $sectionID) {
                    $exists  = true;
                    $section = $_section;
                    break;
                }
            }
            if (!$exists) {
                $section           = new stdClass();
                $section->name     = (string)$xmlSection->attributes()->Name;
                $section->key      = $sectionID;
                $section->settings = [];
            }
            $this->parseSettings($xmlSection->Setting, $ignored, $section, $dir);
            if (!$exists) {
                $sections[] = $section;
            }
        }
        foreach ($sections as $section) {
            foreach ($section->settings as $setting) {
                $setting->section   = $section->key;
                $setting->elementID = $section->key . '_' . $setting->key;
            }
        }

        return $sections;
    }

    /**
     * @param SimpleXMLElement $settings
     * @param array            $ignored
     * @param stdClass         $section
     * @param string           $dir
     */
    protected function parseSettings(SimpleXMLElement $settings, array &$ignored, stdClass $section, string $dir): void
    {
        foreach ($settings as $XMLSetting) {
            $attributes             = $XMLSetting->attributes();
            $key                    = (string)$attributes->Key;
            $setting                = new stdClass();
            $setting->rawAttributes = [];
            $settingExists          = false;
            if (\in_array($key, $ignored, true)) {
                continue;
            }
            foreach ($attributes as $_k => $_attr) {
                $setting->rawAttributes[$_k] = (string)$_attr;
            }
            if ((string)$attributes->override === 'true') {
                $ignored[] = $key;
            }
            $setting->name         = (string)$attributes->Description;
            $setting->key          = $key;
            $setting->cType        = (string)$attributes->Type;
            $setting->value        = (string)$attributes->Value;
            $setting->isEditable   = (string)$attributes->Editable;
            $setting->cPlaceholder = (string)$attributes->Placeholder;
            // negative values for the 'toggle'-attributes of textarea(resizable), check-boxes and radio-buttons
            $toggleValues = ['0', 'no', 'none', 'off', 'false'];
            if ($setting->cType === 'float') {
                $setting->cType = 'text';
            }
            // special handling for textarea-type settings
            if ($setting->cType === 'textarea') {
                // inject the tag-attributes of the TextAreaValue in our oSetting
                $setting->textareaAttributes = [];
                // get the SimpleXMLElement-array
                $attr = $XMLSetting->TextAreaValue->attributes();
                // we insert our default "no resizable"
                $setting->textareaAttributes['Resizable'] = 'none';
                foreach ($attr as $_key => $_val) {
                    $_val                               = (string)$_val; // cast the value(!)
                    $setting->textareaAttributes[$_key] = $_val;
                    // multiple values of 'disable resizing' are allowed,
                    // but only vertical is ok, if 'resizable' is required
                    if ((string)$_key === 'Resizable') {
                        \in_array($_val, $toggleValues, true)
                            ? $setting->textareaAttributes[$_key] = 'none'
                            : $setting->textareaAttributes[$_key] = 'vertical';
                        // only vertical, because horizontal breaks the layout
                    } else {
                        $setting->textareaAttributes[$_key] = $_val;
                    }
                }
                // get the tag content of "TextAreaValue"; trim leading and trailing spaces
                $textLines = \mb_split("\n", (string)$XMLSetting->TextAreaValue);
                \array_walk($textLines, '\trim');
                $setting->cTextAreaValue = \implode("\n", $textLines);
            }
            foreach ($section->settings as $_setting) {
                if ($_setting->key === $setting->key) {
                    $settingExists = true;
                    $setting       = $_setting;
                    break;
                }
            }
            if (\is_string($setting->isEditable)) {
                $setting->isEditable = \mb_strlen($setting->isEditable) === 0
                    ? true
                    : (bool)(int)$setting->isEditable;
            }
            if (isset($XMLSetting->Option)) {
                $setting->options = [];
                foreach ($XMLSetting->Option as $XMLOption) {
                    $opt        = new stdClass();
                    $opt->name  = (string)$XMLOption;
                    $opt->value = (string)$XMLOption->attributes()->Value;
                    $opt->dir   = $dir; // add current folder to option - useful for theme previews
                    if ((string)$XMLOption === '' && (string)$XMLOption->attributes()->Name !== '') {
                        // overwrite the name (which defaults to the tag content),
                        // if it's empty, with the Option-attribute "Name", if we got that
                        $opt->name = (string)$XMLOption->attributes()->Name;
                    }
                    $setting->options[] = $opt;
                }
            }
            if (isset($XMLSetting->Optgroup)) {
                $setting->optGroups = [];
                foreach ($XMLSetting->Optgroup as $XMLOptgroup) {
                    $optgroup         = new stdClass();
                    $optgroup->name   = (string)$XMLOptgroup->attributes()->label;
                    $optgroup->values = [];
                    foreach ($XMLOptgroup->Option as $XMLOptgroupOption) {
                        $optgroupValues        = new stdClass();
                        $optgroupValues->name  = (string)$XMLOptgroupOption;
                        $optgroupValues->value = (string)$XMLOptgroupOption->attributes()->Value;
                        $optgroup->values[]    = $optgroupValues;
                    }
                    $setting->optGroups[] = $optgroup;
                }
            }
            if (!$settingExists) {
                $section->settings[] = $setting;
            }
        }
    }
}
