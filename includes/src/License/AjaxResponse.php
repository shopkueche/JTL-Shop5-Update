<?php declare(strict_types=1);

namespace JTL\License;

use JsonSerializable;

/**
 * Class AjaxResponse
 * @package JTL\License
 */
class AjaxResponse implements JsonSerializable
{
    /**
     * @var string
     */
    public $html = '';

    /**
     * @var string
     */
    public $notification = '';

    /**
     * @var string
     */
    public $id = '';

    /**
     * @var string
     */
    public $status = 'OK';

    /**
     * @var string|null
     */
    public $redirect;

    /**
     * @var string
     */
    public $action = '';

    /**
     * @var string
     */
    public $error = '';

    /**
     * @var mixed
     */
    public $additional;

    /**
     * @var array
     */
    public $replaceWith = [];

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'error'        => $this->error,
            'status'       => $this->status,
            'action'       => $this->action,
            'id'           => $this->id,
            'notification' => \trim($this->notification),
            'html'         => \trim($this->html),
            'replaceWith'  => $this->replaceWith,
            'redirect'     => $this->redirect
        ];
    }
}
