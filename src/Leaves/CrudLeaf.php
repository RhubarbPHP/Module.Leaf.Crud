<?php

namespace Rhubarb\Leaf\Crud\Leaves;

use Rhubarb\Crown\Exceptions\ForceResponseException;
use Rhubarb\Crown\Response\RedirectResponse;

abstract class CrudLeaf extends ModelBoundLeaf
{
    /**
     * @var CrudModel
     */
    protected $model;

    protected function redirectAfterSave()
    {
        $this->redirectAfterCancel();
    }

    protected function redirectAfterCancel()
    {
        throw new ForceResponseException(new RedirectResponse("../"));
    }

    protected function saveRestModel()
    {
        $this->model->restModel->save();

        return $this->model->restModel;
    }

    protected final function save()
    {
        $this->saveRestModel();
        $this->redirectAfterSave();
    }

    protected function cancel()
    {
        $this->redirectAfterCancel();
    }

    protected function delete()
    {
        $this->model->restModel->delete();
        $this->redirectAfterSave();
    }

    protected function createModel()
    {
        return new CrudModel();

    }

    protected function onModelCreated()
    {
        parent::onModelCreated();

        $this->model->savePressedEvent->attachHandler(
            function () {
                $this->save();
            }
        );

        $this->model->cancelPressedEvent->attachHandler(
            function () {
                $this->cancel();
            }
        );

        $this->model->deletePressedEvent->attachHandler(
            function () {
                $this->delete();
            }
        );
    }
}