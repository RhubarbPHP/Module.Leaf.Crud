<?php

namespace Rhubarb\Leaf\Crud\Leaves;

use Rhubarb\Crown\Events\Event;

class CrudModel extends ModelBoundModel
{
    /**
     * @var Event
     */
    public $savePressedEvent;

    /**
     * @var Event
     */
    public $cancelPressedEvent;

    /**
     * @var Event
     */
    public $deletePressedEvent;

    public function __construct()
    {
        parent::__construct();

        $this->savePressedEvent = new Event();
        $this->cancelPressedEvent = new Event();
        $this->deletePressedEvent = new Event();
    }

}