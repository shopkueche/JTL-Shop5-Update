<?php
/**
 * add_nfehlerhaft_texportformat
 *
 * @author mh
 * @created Wed, 03 Apr 2019 11:55:19 +0200
 */

use JTL\DB\ReturnType;
use JTL\Exportformat;
use JTL\Mail\Hydrator\TestHydrator;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Validator\SyntaxChecker;
use JTL\Shopsetting;
use JTL\Smarty\MailSmarty;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190403115519
 */
class Migration_20190403115519 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Add nFehlerhaft to texportformat, tpluginemailvorlage';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $id = $this->getDB()->query(
            "SELECT kEmailvorlage 
                FROM temailvorlage 
                WHERE cModulId = 'core_jtl_rma_submitted'",
            ReturnType::SINGLE_OBJECT
        );
        if (isset($id->kEmailvorlage)) {
            $this->getDB()->delete('temailvorlage', 'kEmailvorlage', $id->kEmailvorlage);
            $this->getDB()->delete('temailvorlagesprache', 'kEmailvorlage', $id->kEmailvorlage);
            $this->getDB()->delete('temailvorlagespracheoriginal', 'kEmailvorlage', $id->kEmailvorlage);
        }
        $revs = $this->getDB()->selectAll('trevisions', 'type', 'mail');
        foreach ($revs as $rev) {
            $update  = false;
            $content = json_decode($rev->content);
            if (isset($content->references)) {
                foreach ((array)$content->references as $ref) {
                    if (isset($ref->cDateiname)) {
                        $update         = true;
                        $ref->cPDFNames = $ref->cDateiname;
                        unset($ref->cDateiname);
                    }
                }
            }
            if ($update === true) {
                $rev->content             = json_encode($content);
                $rev->reference_secondary = $rev->reference_secondary ?? '_DBNULL_';
                $this->getDB()->update('trevisions', 'id', $rev->id, $rev);
            }
        }
        $this->execute("UPDATE temailvorlagesprache SET cBetreff = '' WHERE kEmailvorlage > 0 AND cBetreff IS NULL");
        $this->execute("UPDATE temailvorlagespracheoriginal 
            SET cBetreff = '' WHERE kEmailvorlage > 0 AND cBetreff IS NULL"
        );
        $this->execute('DELETE FROM texportformat WHERE nSpecial = 1 AND kPlugin = 0');
        $this->execute('ALTER TABLE texportformat ADD COLUMN nFehlerhaft TINYINT(1) DEFAULT 0');
        $this->execute('ALTER TABLE tpluginemailvorlage ADD COLUMN nFehlerhaft TINYINT(1) DEFAULT 0');
        $this->execute('ALTER TABLE temailvorlagesprache 
            CHANGE COLUMN `cDateiname` `cPDFNames` VARCHAR(255) NULL DEFAULT NULL');
        $this->execute('ALTER TABLE temailvorlagespracheoriginal 
            CHANGE COLUMN `cDateiname` `cPDFNames` VARCHAR(255) NULL DEFAULT NULL');
        $this->execute('ALTER TABLE tpluginemailvorlagesprache 
            CHANGE COLUMN `cDateiname` `cPDFNames` VARCHAR(255) NULL DEFAULT NULL');
        $this->execute('ALTER TABLE tpluginemailvorlagespracheoriginal
            CHANGE COLUMN `cDateiname` `cPDFNames` VARCHAR(255) NULL DEFAULT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE texportformat DROP COLUMN nFehlerhaft');
        $this->execute('ALTER TABLE tpluginemailvorlage DROP COLUMN nFehlerhaft');
        $this->execute('ALTER TABLE temailvorlagesprache 
            CHANGE COLUMN `cPDFNames` `cDateiname` VARCHAR(255) NULL DEFAULT NULL');
        $this->execute('ALTER TABLE temailvorlagespracheoriginal 
            CHANGE COLUMN `cPDFNames` `cDateiname` VARCHAR(255) NULL DEFAULT NULL');
        $this->execute('ALTER TABLE tpluginemailvorlagesprache 
            CHANGE COLUMN `cPDFNames` `cDateiname` VARCHAR(255) NULL DEFAULT NULL');
        $this->execute('ALTER TABLE tpluginemailvorlagespracheoriginal 
            CHANGE COLUMN `cPDFNames` `cDateiname` VARCHAR(255) NULL DEFAULT NULL');
    }
}
