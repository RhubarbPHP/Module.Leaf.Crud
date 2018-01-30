<?php

namespace Rhubarb\Leaf\Crud\Generic;

use Rhubarb\Crown\Settings\HtmlPageSettings;
use Rhubarb\Crown\String\StringTools;
use Rhubarb\Leaf\Crud\Leaves\CrudView;
use Rhubarb\Leaf\SearchPanel\Leaves\SearchPanel;
use Rhubarb\Leaf\Table\Leaves\Table;
use Rhubarb\Leaf\Tabs\Leaves\Tabs;
use Rhubarb\Stem\Schema\SolutionSchema;

abstract class GenericCollectionView extends CrudView
{
    use URLRestrictedAccess, PageHeaderBar;

    /**
     * @var SearchPanel
     */
    protected $search;

    /**
     * @var Tabs
     */
    protected $tabs;

    protected function createSubLeaves()
    {
        $this->registerSubLeaf(
            $table = new Table($this->getTableCollection(), 20)
        );
        $table->setExportColumns($this->getExportColumns());
        $table->columns = $this->getDisplayColumns();
        $table->columns[''] = $this->getActionsColumnContents();

        $table->addCssClassNames('c-table');

        $this->search = $this->createSearch();
        if ($this->search !== null) {
            $this->registerSubLeaf($this->search);
            $table->bindEventsWith($this->search);
        }

        $this->tabs = $this->createTabs();
        if ($this->tabs !== null) {
            $this->registerSubLeaf($this->tabs);
            $table->bindEventsWith($this->tabs);

            if ($this->search) {
                $this->search->bindEventsWith($this->tabs);
            }
        }

        parent::createSubLeaves();
    }

    /**
     * @return \Rhubarb\Stem\Collections\Collection
     */
    protected function getTableCollection()
    {
        $class = $this->model->restCollection->getModelClassName();
        $data = $class::all();

        if (isset(SolutionSchema::getModelSchema($class)->getColumns()['Order'])) {
            $data->addSort("Order");
        }
        return $data;
    }

    /**
     * @return string
     */
    protected function getModelDisplayName()
    {
        if ($this->model->restCollection !== null) {
            return StringTools::wordifyStringByUpperCase(StringTools::getShortClassNameFromNamespace(
                $this->model->restCollection->getModelClassName()
            ));
        }

        return '';
    }

    protected function printViewContent()
    {
        $htmlPageSettings = HtmlPageSettings::singleton();

        print <<<HTML
        <main class="c-main">
            <div>
                {$this->getHeaderBarHTML()} 
                <div class="o-wrap">
                    <div class="u-pos-relative u-pad">
                        <h1 class="u-epsilon c-heading">{$htmlPageSettings->pageTitle}</h1>
                            <div class="o-flex c-search"> 
HTML;
        if (isset($this->search)) {
            print $this->search;
        }

        if (isset($this->tabs)) {
            print $this->tabs;
        }
        print <<<HTML
                            <span class="u-marg-left">{$this->generateTopButtonsDOM()}</span>
                            </div>
                            {$this->leaves['Table']}
                        </div>
                    </div>
                </div>
            </main>
HTML;
    }

    abstract public function getDisplayColumns();

    public function getExportColumns()
    {
    }

    /**
     * @return null
     */
    protected function createSearch()
    {
        return null;
    }

    /**
     * @return null
     */
    protected function createTabs()
    {
        return null;
    }

    /**
     * @return array
     */
    protected function getBreadcrumbItems()
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getTopButtons()
    {
        return [
            $this->makePrimaryTopButton('New ' . $this->getModelDisplayName(), 'add/'),
        ];
    }

    /**
     * @param string $label
     * @param string $url
     * @return string Button DOM
     */
    protected function makePrimaryTopButton($label, $url)
    {
        return "<a href='{$url}'>{$label}</a>";
    }

    /**
     * Should configure the HtmlPageSettings::pageTitle property
     */
    protected function setTitle()
    {
        $htmlPageSettings = HtmlPageSettings::singleton();
        $htmlPageSettings->pageTitle = $this->getModelDisplayName() . 's';
    }

    /**
     * @return string
     */
    protected function getActionsColumnContents()
    {
        $idColumn = $this->model->restCollection->getModelSchema()->uniqueIdentifierColumnName;
        return '<a href="{' . $idColumn . '}/">Edit</a>';
    }
}
