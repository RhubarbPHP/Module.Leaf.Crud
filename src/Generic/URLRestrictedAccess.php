<?php

namespace Rhubarb\Leaf\Crud\Generic;

use Rhubarb\Crown\Exceptions\ForceResponseException;
use Rhubarb\Crown\LoginProviders\LoginProvider;
use Rhubarb\Crown\Response\RedirectResponse;
use Rhubarb\Crown\UrlHandlers\UrlHandler;

trait URLRestrictedAccess
{
    protected function beforeRender()
    {
        if (!$this->checkUserHasPermission()) {
            $this->handleAccessDenied();
        }

        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        parent::beforeRender();
    }

    /**
     * @return mixed
     */
    protected function checkUserHasPermission()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return LoginProvider::getProvider()->getLoggedInUser()->can($this->getPermissionBasePath());
    }

    /**
     * @var string
     */
    private $basePath;

    /**
     * @return string
     */
    protected function getPermissionBasePath()
    {
        if ($this->basePath === null) {
            $handler = UrlHandler::getExecutingUrlHandler();
            $this->basePath = self::createPagePermissionPath($handler->getUrl());
        }

        return $this->basePath;
    }

    protected function handleAccessDenied()
    {
        throw new ForceResponseException(new RedirectResponse('/'));
    }


    /**
     * @param array ...$pathComponents
     * @return string
     */
    public static function createPagePermissionPath(...$pathComponents)
    {
        return implode(
            '/',
            array_map(
                function ($value) {
                    return trim($value, '/');
                },
                $pathComponents
            )
        );
    }
}
