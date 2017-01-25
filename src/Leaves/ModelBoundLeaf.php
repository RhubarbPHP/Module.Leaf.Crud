<?php

/*
 *	Copyright 2015 RhubarbPHP
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Rhubarb\Leaf\Crud\Leaves;

use Rhubarb\Crown\String\StringTools;
use Rhubarb\Leaf\Controls\Common\Checkbox\Checkbox;
use Rhubarb\Leaf\Controls\Common\DateTime\Date;
use Rhubarb\Leaf\Controls\Common\SelectionControls\DropDown\DropDown;
use Rhubarb\Leaf\Controls\Common\Text\NumericTextBox;
use Rhubarb\Leaf\Controls\Common\Text\PasswordTextBox;
use Rhubarb\Leaf\Controls\Common\Text\TextArea;
use Rhubarb\Leaf\Controls\Common\Text\TextBox;
use Rhubarb\Leaf\Leaves\Leaf;
use Rhubarb\Stem\Collections\Collection;
use Rhubarb\Stem\Models\Model;
use Rhubarb\Stem\Repositories\MySql\Schema\Columns\MySqlEnumColumn;
use Rhubarb\Stem\Schema\Columns\BooleanColumn;
use Rhubarb\Stem\Schema\Columns\Column;
use Rhubarb\Stem\Schema\Columns\DateColumn;
use Rhubarb\Stem\Schema\Columns\DateTimeColumn;
use Rhubarb\Stem\Schema\Columns\DecimalColumn;
use Rhubarb\Stem\Schema\Columns\IntegerColumn;
use Rhubarb\Stem\Schema\Columns\LongStringColumn;
use Rhubarb\Stem\Schema\Columns\MoneyColumn;
use Rhubarb\Stem\Schema\Columns\StringColumn;
use Rhubarb\Stem\Schema\Columns\TimeColumn;
use Rhubarb\Stem\Schema\SolutionSchema;

abstract class ModelBoundLeaf extends Leaf
{
    /**
     * @var ModelBoundModel
     */
    protected $model;
    private $incomingRestModel;

    private $hasRestModelOrCollection = false;

    public function __construct($modelOrCollection = null)
    {
        $this->incomingRestModel = $modelOrCollection;

        parent::__construct("");
    }

    public function setRestModel(Model $restModel)
    {
        $this->model->restModel = $restModel;
        $this->hasRestModelOrCollection = true;
        $this->initialiseView();
    }

    public function setRestCollection(Collection $restCollection)
    {
        $this->model->restCollection = $restCollection;
        $this->hasRestModelOrCollection = true;
        $this->initialiseView();
    }


    /**
     * Provides an opportunity for extending classes to modify the model in some way when they themselves are not
     * directly responsible for the model creation.
     */
    protected function onModelCreated()
    {
        parent::onModelCreated();

        if ($this->incomingRestModel instanceof Model){
            $this->setRestModel($this->incomingRestModel);
        } elseif ($this->incomingRestModel instanceof Collection){
            $this->setRestCollection($this->incomingRestModel);
        }

        $this->model->createSubLeafFromNameEvent->attachHandler(function($leafName){

            if (!$this->hasRestModelOrCollection) {
                return null;
            }

            $restModel = $this->model->restModel;

            if ($restModel) {
                $class = $restModel->getModelName();
                $schema = $restModel->getSchema();
            } else {
                $restCollection = $this->model->restCollection;

                $class = $restCollection->getModelClassName();
                $schema = $restCollection->getModelSchema();
            }

            $leaf = $this->createLeafForLeafName($leafName);

            if ($leaf){
                return $leaf;
            }

            // See if the model has a relationship with this name.
            $relationships = SolutionSchema::getAllOneToOneRelationshipsForModelBySourceColumnName($class);

            $columnRelationships = false;

            if (isset($relationships[$leafName])) {
                $columnRelationships = $relationships[$leafName];
            } else {
                if ($leafName == $schema->uniqueIdentifierColumnName) {
                    if (isset($relationships[""])) {
                        $columnRelationships = $relationships[""];
                    }
                }
            }

            if ($columnRelationships) {
                $relationship = $relationships[$leafName];

                $collection = $relationship->getCollection();

                $dropDown = new DropDown($leafName);
                $dropDown->setSelectionItems(
                    [
                        ["", "Please Select"],
                        $collection
                    ]
                );

                $dropDown->setLabel(StringTools::wordifyStringByUpperCase($relationship->getNavigationPropertyName()));

                return $dropDown;
            }

            $columns = $schema->getColumns();

            if (!isset($columns[$leafName])) {
                return null;
            }

            $column = $columns[$leafName];

            return $this->createLeafFromColumn($column, $leafName);
        });
    }

    protected function createLeafForLeafName($leafName)
    {
        return null;
    }

    protected function createLeafFromColumn(Column $column, $leafName)
    {
        // Checkbox
        if ($column instanceof BooleanColumn) {
            return new Checkbox($leafName);
        }

        // Date
        if ($column instanceof DateColumn || $column instanceof DateTimeColumn) {
            return new Date($leafName);
        }

        // Time
        if ($column instanceof TimeColumn) {
            //$textBox = new \Rhubarb\Leaf\Presenters\Controls\DateTime\Time($leafName);
            //return $textBox;
        }

        // Drop Downs
        if ($column instanceof MySqlEnumColumn) {
            $dropDown = new DropDown($leafName);
            $dropDown->setSelectionItems(
                [
                    ["", "Please Select"],
                    $column
                ]
            );

            return $dropDown;
        }

        // TextArea
        if ($column instanceof LongStringColumn) {
            $textArea = new TextArea($leafName, 5, 40);

            return $textArea;
        }

        // TextBoxes
        if ($column instanceof StringColumn) {
            if (stripos($leafName, "password") !== false) {
                return new PasswordTextBox($leafName);
            }

            $textBox = new TextBox($leafName);
            $textBox->setMaxLength($column->maximumLength);

            return $textBox;
        }

        // Decimal
        if ($column instanceof DecimalColumn || $column instanceof MoneyColumn) {
            $textBox = new NumericTextBox($leafName);

            return $textBox;
        }

        // Int
        if ($column instanceof IntegerColumn) {
            $textBox = new TextBox($leafName);
            //$textBox->setSize(5);

            return $textBox;
        }

        return null;
    }
}
