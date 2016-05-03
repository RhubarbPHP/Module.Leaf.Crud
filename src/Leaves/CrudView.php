<?php

namespace Rhubarb\Leaf\Crud\Leaves;

use Rhubarb\Crown\String\StringTools;
use Rhubarb\Leaf\Views\View;
use Rhubarb\Stem\Schema\SolutionSchema;

class CrudView extends View
{
    /**
     * @var CrudModel
     */
    protected $model;

    protected function getTitle()
    {
        if ($this->model->restModel !== null) {
            if ($this->model->restModel->isNewRecord()) {
                return "Adding a new " . strtolower(
                    StringTools::wordifyStringByUpperCase(
                        SolutionSchema::getModelNameFromClass(get_class($this->model->restModel))
                    )
                ) . " entry";
            } else {
                return ucfirst(
                    strtolower(
                        StringTools::wordifyStringByUpperCase(
                            SolutionSchema::getModelNameFromClass(get_class($this->model->restModel))
                        )
                    ) . " '" . $this->model->restModel->getLabel() . "'"
                );
            }
        } else {
            if ($this->model->restCollection !== null) {
                return StringTools::wordifyStringByUpperCase(
                    StringTools::makePlural(
                        SolutionSchema::getModelNameFromClass($this->model->restCollection->getModelClassName())
                    )
                );
            } else {
                return "Untitled";
            }
        }
    }
}