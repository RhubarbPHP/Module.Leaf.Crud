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

namespace Rhubarb\Leaf\Crud\UrlHandlers;

use Rhubarb\Crown\Request\Request;
use Rhubarb\Crown\Response\HtmlResponse;
use Rhubarb\RestApi\Exceptions\RestImplementationException;
use Rhubarb\Stem\UrlHandlers\ModelCollectionHandler;

/**
 * A rest handler that handles HTML requests by passing control to Leaf leaves.
 */
class LeafRestUrlHandler extends ModelCollectionHandler
{
    protected $collectionPresenterClassName;
    protected $itemPresenterClassName;
    protected $additionalLeafClassNameMap = [];
    protected $urlAction = "";

    /**
     * @param string $modelName The full namespaced class name of the model providing data for the requests
     * @param string $collectionPresenterClassName The full namespaced class name of the presenter representing the model collection
     * @param string $itemPresenterClassName The full namespaced class name of the presenter representing an individual item
     * @param array $additionalPresenterClassNameMap An optional associative array mapping 'actions' to other presenters.
     * @param array $children
     */
    public function __construct(
        $modelName,
        $collectionPresenterClassName,
        $itemPresenterClassName,
        $additionalPresenterClassNameMap = [],
        $children = []
    ) {
        parent::__construct($modelName, $children);

        $this->collectionPresenterClassName = $collectionPresenterClassName;
        $this->itemPresenterClassName = $itemPresenterClassName;
        $this->additionalLeafClassNameMap = $additionalPresenterClassNameMap;
    }

    /**
     * @return string
     */
    public function getUrlAction()
    {
        return $this->urlAction;
    }

    protected function getSupportedMimeTypes()
    {
        $mime = parent::getSupportedMimeTypes();

        $mime["application/core"] = "mvp";

        return $mime;
    }

    private function checkForPotentialAction($actionName)
    {
        if (isset($this->additionalLeafClassNameMap[$actionName])) {
            return true;
        }

        $potentialClassName = $this->namespaceBase . "\\" . $this->leafClassStub .
            $this->makeActionClassFriendly($actionName);

        return class_exists($potentialClassName);
    }

    private function makeActionClassFriendly($action)
    {
        return str_replace(" ", "", ucwords(strtolower(str_replace("-", " ", $action))));
    }

    /**
     * Should be implemented to return a true or false as to whether this handler supports the given request.
     *
     * Normally this involves testing the request URI.
     *
     * @param Request $request
     * @param string $currentUrlFragment
     * @return bool
     */
    protected function getMatchingUrlFragment(Request $request, $currentUrlFragment = "")
    {
        $uri = $currentUrlFragment;

        $parentResponse = parent::getMatchingUrlFragment($request, $currentUrlFragment);

        if (preg_match('|^' . $this->url . '([0-9]+)/([a-zA-Z0-9\-]+)|', $uri, $matches)) {
            if ($this->checkForPotentialAction($matches[2])) {
                $this->urlAction = $matches[2];
                $this->isCollection = false;

                return $matches[0];
            }
        }

        if (preg_match("|^" . $this->url . "([^/]+)/|", $uri, $match)) {
            if (is_numeric($match[1]) || isset($this->additionalLeafClassNameMap[$match[1]])) {
                $this->urlAction = $match[1];
                $this->isCollection = false;

                return $match[0];
            }
        }

        return $parentResponse;
    }

    protected function getLeafClassName()
    {
        $leafClass = false;

        if ($this->urlAction != "") {
            if (isset($this->additionalLeafClassNameMap[$this->urlAction])) {
                $leafClass = $this->additionalLeafClassNameMap[$this->urlAction];
            } else {
                if (is_numeric($this->urlAction)) {
                    $this->isCollection = false;
                }
            }
        }

        if ($leafClass === false) {
            if ($this->isCollection()) {
                $leafClass = $this->collectionPresenterClassName;
            } else {
                $leafClass = $this->itemPresenterClassName;
            }
        }

        return $leafClass;
    }

    /**
     * Return the response if appropriate or false if no response could be generated.
     *
     * @param mixed $request
     * @return bool
     */
    protected function generateResponseForRequest($request = null)
    {
        $leafClass = $this->getLeafClassName();

        if ($this->isCollection()) {
            $leaf = new $leafClass($this->getModelCollection());
        } else {
            $leaf = new $leafClass($this->getModelObject());
        }

        $response = $leaf->generateResponse($request);

        if (is_string($response)) {
            $htmlResponse = new HtmlResponse($leaf);
            $htmlResponse->setContent($response);
            $response = $htmlResponse;
        }

        return $response;
    }
}
