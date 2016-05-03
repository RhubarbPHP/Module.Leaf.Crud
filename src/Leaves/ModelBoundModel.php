<?php

namespace Rhubarb\Leaf\Crud\Leaves;

use Rhubarb\Leaf\Leaves\LeafModel;
use Rhubarb\Stem\Collections\Collection;
use Rhubarb\Stem\Models\Model;

class ModelBoundModel extends LeafModel
{
    /**
     * @var Model
     */
    public $restModel;

    /**
     * @var Collection
     */
    public $restCollection;
}