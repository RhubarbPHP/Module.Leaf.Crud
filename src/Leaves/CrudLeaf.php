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

    protected function createdModel()
    {
        $model = new CrudModel();
        $model->savePressedEvent->attachHandler(
            function () {
                $this->save();
            }
        );

        $model->cancelPressedEvent->attachHandler(
            function () {
                $this->cancel();
            }
        );

        $model->deletePressedEvent->attachHandler(
            function () {
                $this->delete();
            }
        );
        return $model;
    }
}