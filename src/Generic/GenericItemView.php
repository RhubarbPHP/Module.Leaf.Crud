<?php

namespace Rhubarb\Leaf\Crud\Generic;

use Rhubarb\Crown\Settings\HtmlPageSettings;
use Rhubarb\Crown\String\StringTools;
use Rhubarb\Leaf\Crud\Leaves\CrudView;

abstract class GenericItemView extends CrudView
{
    use URLRestrictedAccess, PageHeaderBar;

    /** @var GenericCrudModel */
    protected $model;

    /** @var string */
    protected $deleteConfirmMessage = 'Are you sure you want to delete this?';

    protected function createSubLeaves()
    {
        parent::createSubLeaves();

        $this->leaves['Save']->addCssClassNames('c-button');

        $this->leaves['Cancel']->addCssClassNames('c-button', 'c-button--light');
        $this->leaves['Delete']->addCssClassNames('c-button', 'c-button--text');
        $this->leaves['Delete']->setConfirmMessage($this->deleteConfirmMessage);

        foreach ($this->getInputs() as $input) {
            $this->registerSubLeaf($input);
        }
    }

    protected function printViewContent()
    {
        $htmlPageSettings = HtmlPageSettings::singleton();

        print <<<HTML
        <main class="c-main">
HTML;
        if($this->model->showHeader) {
            $this->getHeaderBarHTML();
        }
        print <<<HTML
            <div
} class="u-pad o-wrap">
                <h1 class="u-epsilon c-heading">{$htmlPageSettings->pageTitle}</h1>
                
HTML;

        $this->printInputs();

        if($this->model->showButtonBar)
        {
            $this->printButtonBar();
        }

        print <<<HTML
            </div>
        </main>
HTML;
    }

    protected function printButtonBar()
    {
        $delete = $this->model->restModel->isNewRecord() ? '' : $this->leaves['Delete'];
        print <<<HTML
        <div class="u-pad-top-bottom o-flex">
                    <div class="o-flex__item">{$this->leaves['Save']}{$this->leaves['Cancel']}</div>
                    <div>{$delete}</div>
                </div>
HTML;
    }

    abstract protected function getInputs();

    protected function printInputs()
    {
        foreach ($this->getInputs() as $input) {
            $input = $this->leaves[$input];
            $input->addHtmlAttribute('class', 'c-input');

            if (isset($this->model->errors) && isset($this->model->errors[$input->getName()])) {
                $error = '<div class="c-alert c-alert--error">
                            <div class="o-flex">
                                <span class="c-icon c-icon--error-circle"></span>
                                    <div class="o-flex__item">
                                        ' . $this->model->errors[$input->getName()] . '
                                    </div>
                            </div>
                          </div>';

                unset($this->model->errors[$input->getName()]);
            } else {
                $error = '';
            }

            print <<<HTML
            <div class="u-marg-bottom">
                    <label for="{$input->getName()}" class="c-label">{$input->getLabel()}:</label>
                    {$input}
                {$error}
            </div>
HTML;
        }

        if (isset($this->model->errors) && sizeof($this->model->errors) > 0) {
            $error = implode(' ', $this->model->errors);
            print <<<HTML
            <div class="c-alert c-alert--error">
                <div class="o-flex">
                    <span class="c-icon c-icon--error-circle"></span>
                        <div class="o-flex__item">
                            {$error}
                        </div>
                </div>
            </div>
HTML;
        }
    }

    /**
     * @return string
     */
    protected function getModelDisplayName()
    {
        if ($this->model->restModel !== null) {
            return StringTools::wordifyStringByUpperCase($this->model->restModel->getModelName());
        }

        return '';
    }

    /**
     * @return array Dictionary of title => url for all pages BETWEEN "home" and this page
     */
    protected function getBreadcrumbItems()
    {
        return [$this->getModelDisplayName() . 's' => '../'];
    }

    /**
     * Should configure the HtmlPageSettings::pageTitle property
     */
    protected function setTitle()
    {
        $settings = HtmlPageSettings::singleton();
        $settings->pageTitle = $this->model->restModel->isNewRecord()
            ? 'New ' . $this->getModelDisplayName()
            : $this->model->restModel->getLabel();
    }

    protected function getActionButtons()
    {
        $buttons = [
            'Save',
            'Cancel'
        ];
        if (!$this->model->restModel->isNewRecord()) {
            $buttons[] = 'Delete';
        }
        return $buttons;
    }

    protected function printActionButtons()
    {
        if ($this->model->displayActionButtons) {
            $buttons = $this->getActionButtons();
            if (sizeof($buttons) > 0) {
                print <<<HTML
            <div class="u-pad-top-bottom o-flex">
                    <div class="o-flex__item">
HTML;
                foreach ($buttons as $actionButton) {
                    print $this->leaves[$actionButton];
                }
                print <<<HTML
                </div>
            </div>
HTML;
            }
        }
    }
}
