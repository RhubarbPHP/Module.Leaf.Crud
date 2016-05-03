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

namespace Rhubarb\Patterns\Mvp\Crud;

use Rhubarb\Crown\Settings\HtmlPageSettings;
use Rhubarb\Crown\String\StringTools;
use Rhubarb\Crown\UrlHandlers\UrlHandler;
use Rhubarb\Leaf\Presenters\Controls\Buttons\Button;
use Rhubarb\Leaf\UrlHandlers\MvpRestHandler;
use Rhubarb\Leaf\Views\View;
use Rhubarb\Patterns\Mvp\Controls\Buttons\PrimaryButton;
use Rhubarb\Patterns\Mvp\Controls\Buttons\WarningButton;
use Rhubarb\Stem\Collections\Collection;
use Rhubarb\Stem\Models\Model;
use Rhubarb\Stem\Schema\SolutionSchema;

class CrudView extends View
{
    public $newModel = true;

    protected function createSubLeaves()
    {
        $this->registerSubLeaf(
            $save = new PrimaryButton("Save", "Save", function () {
                $this->raiseEvent("SavePressed");
            }),
            new Button("Cancel", "Cancel", function () {
                $this->raiseEvent("CancelPressed");
            }),
            $delete = new WarningButton("Delete", "Delete", function () {
                $this->raiseEvent("DeletePressed");
            }),
            new Button("Add", "Add", function () {
                $this->raiseEvent("AddPressed");
            })
        );

        $validator = $this->raiseEvent("GetDefaultClientSideValidator");

        if ($validator) {
            $save->setValidator($validator);
        }

        $delete->setConfirmMessage("Are you sure you want to delete this item?");

        parent::createPresenters();
    }

    /**
     * Override this to provide a name for the real world entity being manipulated.
     * @return string
     */
    protected function getEntityName()
    {
        $restModel = $this->raiseEvent("GetRestModel");

        if ($restModel instanceof Model) {
            return StringTools::wordifyStringByUpperCase(
                SolutionSchema::getModelNameFromClass(get_class($restModel))
            );
        }

        if ($restModel instanceof Collection) {
            return StringTools::wordifyStringByUpperCase(
                SolutionSchema::getModelNameFromClass($restModel->getModelClassName())
            );
        }

        return "";
    }

    protected function onBeforePrintViewContent()
    {
        parent::onBeforePrintViewContent();

        $restModel = $this->raiseEvent("GetRestModel");

        if (is_object($restModel)) {
            $pageSettings = HtmlPageSettings::singleton();

            if ($restModel instanceof Model) {
                if ($restModel->isNewRecord()) {
                    $pageSettings->pageTitle = "Add a " . $this->getEntityName();
                } else {
                    $urlHandler = UrlHandler::getExecutingUrlHandler();

                    if ($urlHandler instanceof MvpRestHandler) {
                        $action = $this->describeUrlAction($urlHandler->getUrlAction());
                    } else {
                        $action = "Editing ";
                    }

                    $label = $restModel->getLabel();
                    if ($label) {
                        $label = ': ' . $label;
                    }

                    $pageSettings->pageTitle = $action . ' ' . $this->getEntityName() . $label;
                }
            } elseif ($restModel instanceof Collection) {
                $pageSettings->pageTitle = StringTools::pluralise($this->getEntityName(), 2);
            }
        }
    }

    protected function describeUrlAction($action)
    {
        if (is_numeric($action)) {
            $action = '';
        } else {
            if (empty($action) || $action == 'edit') {
                $action = 'Editing ';
            } else {
                $action .= ' ';
            }
        }

        return $action;
    }
}
