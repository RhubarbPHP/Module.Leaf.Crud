<?php

namespace Rhubarb\Leaf\Crud\Generic;

use Rhubarb\Leaf\Crud\Leaves\CrudLeaf;
use Rhubarb\Stem\Exceptions\ModelConsistencyValidationException;

/**
 * @package Rhubarb\Leaf\Crud\Generic
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class GenericCrudLeaf extends CrudLeaf
{
    /** @var GenericCrudModel */
    protected $model;

    protected function redirectAfterSave()
    {
        if (count($this->model->errors) === 0) {
            parent::redirectAfterSave();
        }
    }

    protected function saveRestModel()
    {
        try {
            parent::saveRestModel();
            $this->model->errors = [];
        } catch (ModelConsistencyValidationException $ex) {
            $this->model->errors = $ex->getErrors();
        }
    }

    /**
     * @return GenericCrudModel
     */
    protected function createModel()
    {
        return new GenericCrudModel();
    }
}
