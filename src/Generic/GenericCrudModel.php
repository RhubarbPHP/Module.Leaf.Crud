<?php

namespace Rhubarb\Leaf\Crud\Generic;

use Rhubarb\Leaf\Crud\Leaves\CrudModel;

class GenericCrudModel extends CrudModel
{
    public $errors = [];

    /** @var boolean */
    public $displayActionButtons;
}
