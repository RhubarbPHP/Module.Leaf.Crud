<?php

namespace Rhubarb\Leaf\Crud;

use Rhubarb\Leaf\Crud\Custard\CreateCrudLeafCommand;
use Rhubarb\Crown\Module;

class CrudModule extends Module
{
	public function getCustardCommands()
	{
		return [
			new CreateCrudLeafCommand()
		];
	}

}