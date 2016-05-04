<?php

namespace Rhubarb\Leaf\Crud\Leaves;

use Rhubarb\Crown\String\StringTools;
use Rhubarb\Leaf\Controls\Common\Buttons\Button;
use Rhubarb\Leaf\Views\View;
use Rhubarb\Stem\Schema\SolutionSchema;

class CrudView extends View
{
    /**
     * @var CrudModel
     */
    protected $model;

    /**
     * The place where extending classes should create and register new Views
     */
    protected function createSubLeaves()
    {
        parent::createSubLeaves();

        $this->registerSubLeaf(new Button("Save", "Save", function(){
            $this->model->savePressedEvent->raise();
        }));

        $this->registerSubLeaf(new Button("Delete", "Delete", function(){
            $this->model->deletePressedEvent->raise();
        }));

        $this->registerSubLeaf(new Button("Cancel", "Cancel", function(){
            $this->model->cancelPressedEvent->raise();
        }));
    }

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

    protected function getBindingValue($propertyName, $index = null)
    {
        if ($index !== null ){
            if (isset($this->model->restModel->$propertyName[$index])){
                return $this->model->restModel->$propertyName[$index];
            } else {
                return null;
            }
        } else {
            return isset($this->model->restModel->$propertyName) ? $this->model->restModel->$propertyName : null;
        }
    }

    protected function setBindingValue($propertyName, $propertyValue, $index = null)
    {
        if ($index !== null){
            if (!isset($this->model->restModel->$propertyName) || !is_array($this->model->restModel->$propertyName)){
                $this->model->restModel->$propertyName = [];
            }

            $this->model->restModel->$propertyName[$index] = $propertyValue;
        } else {
            $this->model->restModel->$propertyName = $propertyValue;
        }
    }
}