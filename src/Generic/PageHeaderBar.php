<?php

namespace Rhubarb\Leaf\Crud\Generic;

use Rhubarb\Crown\Settings\HtmlPageSettings;

trait PageHeaderBar
{
    protected function getHeaderBarClasses()
    {
        return ['o-flex', 'o-flex--align-middle', 'u-border-bottom', 'u-pad', 'u-marg-bottom'];
    }

    /**
     * @return string
     */
    final protected function getHeaderBarHTML()
    {
        $classes = implode(' ', $this->getHeaderBarClasses());
        return <<<HTML
            <div class="{$classes}">
                <div class="c-breadcrumb">
                    {$this->generateBreadcrumbDOM()}
                </div>
            </div>
HTML;
    }

    /**
     * @return string
     */
    private function generateBreadcrumbDOM()
    {
        $this->setTitle();
        $htmlPageSettings = HtmlPageSettings::singleton();
        $breadcrumbs = array_merge(
            ['Home' => '/'],
            $this->getBreadcrumbItems(),
            [$htmlPageSettings->pageTitle => null]
        );
        $dom = '';
        foreach ($breadcrumbs as $text => $url) {
            $text = htmlentities($text);
            $dom .=
                $url !== null
                    ? "<a href='{$url}' class='c-breadcrumb__item'>{$text}</a><span class='c-icon c-icon--chevron-right u-micro u-text'></span>"
                    : "<span class='c-breadcrumb__item-current'>{$text}</span>";
        }

        return $dom;
    }

    /**
     * @return array Dictionary of title => url for all pages BETWEEN "home" and this page
     */
    abstract protected function getBreadcrumbItems();

    /**
     * @return string
     */
    private function generateTopButtonsDOM()
    {
        return implode('  ', $this->getTopButtons());
    }

    /**
     * @return array button strings
     */
    protected function getTopButtons()
    {
        return [];
    }

    /**
     * Should configure the HtmlPageSettings::pageTitle property
     */
    abstract protected function setTitle();
}
