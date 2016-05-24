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

    public function getBoundValue($propertyName, $index = null)
    {
        if ($this->restModel){
            $columns = $this->restModel->getSchema()->getColumns();
            if (!isset($columns[$propertyName])){
                return parent::getBoundValue($propertyName, $index);
            }
        } else {
            return parent::getBoundValue($propertyName, $index);
        }

        if ($index !== null ){
            if (isset($this->restModel->$propertyName[$index])){
                return $this->restModel->$propertyName[$index];
            } else {
                return null;
            }
        } else {
            return isset($this->restModel->$propertyName) ? $this->restModel->$propertyName : null;
        }
    }

    public function setBoundValue($propertyName, $value, $index = null)
    {
        if ($this->restModel){
            $columns = $this->restModel->getSchema()->getColumns();
            if (!isset($columns[$propertyName])){
                parent::setBoundValue($propertyName, $value, $index);
                return;
            }
        } else {
            parent::setBoundValue($propertyName, $value, $index);
            return;
        }

        if ($index !== null){
            if (!isset($this->restModel->$propertyName) || !is_array($this->restModel->$propertyName)){
                $this->restModel->$propertyName = [];
            }

            $this->restModel->$propertyName[$index] = $value;
        } else {
            $this->restModel->$propertyName = $value;
        }
    }


}