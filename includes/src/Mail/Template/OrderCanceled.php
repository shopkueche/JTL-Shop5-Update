<?php declare(strict_types=1);

namespace JTL\Mail\Template;

/**
 * Class OrderCanceled
 * @package JTL\Mail\Template
 */
class OrderCanceled extends OrderCleared
{
    protected $id = \MAILTEMPLATE_BESTELLUNG_STORNO;
}
