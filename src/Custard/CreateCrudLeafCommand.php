<?php

namespace Rhubarb\Leaf\Crud\Custard;

use Rhubarb\Leaf\CustardCommands\CreateLeafCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCrudLeafCommand extends CreateLeafCommand
{
	protected function configure()
	{
		$this->setName("leaf:create-crud-leaf")
			->addArgument("name", InputOption::VALUE_OPTIONAL, "The name of the Model you want to CRUD")
			->addOption('viewbridge');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument("name");
		$generateViewbridge = $input->getOption('viewbridge') != null;

		if (sizeof($name) == 0) {
			$name = $this->askQuestion("Enter the name of the Model you want to CRUD", "", true);
		} else {
			$name = $name[0];
		}

		$leaves = [
			$name . "Collection",
			$name . "Item"
		];

		$viewBridgeMethods = $generateViewbridge ? $this->getViewBridgeMethods($name) : "";
		$viewBridgeUseStatement = $generateViewbridge ? <<<END
use Rhubarb\Leaf\Leaves\LeafDeploymentPackage;

END
			: "";

		$namespace = $this->getNamespaceFromPath();
		$namespaceStatement = "";

		if ($namespace) {
			$namespaceStatement = "
namespace {$namespace};
";
		}
		foreach ($leaves as $leaf) {

			file_put_contents($leaf . ".php", <<<END
<?php
$namespaceStatement
use Rhubarb\Leaf\Crud\Leaves\CrudLeaf;
use Rhubarb\Leaf\Crud\Leaves\CrudModel;

class {$leaf} extends CrudLeaf
{
    /**
     * @var CrudModel
     */
    protected \$model;

    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected function getViewClass()
    {
        return {$leaf}View::class;
    }
}
END
			);
			file_put_contents($leaf . "View.php", <<<END
<?php
$namespaceStatement
use Rhubarb\Leaf\Crud\Leaves\CrudModel;
use Rhubarb\Leaf\Crud\Leaves\CrudView;
{$viewBridgeUseStatement}
class {$leaf}View extends CrudView
{
    /**
     * @var CrudModel
     */
    protected \$model;

    protected function createSubLeaves()
    {
        parent::createSubLeaves();
    }

    protected function printViewContent()
    {
        // Print your HTML here.
    }
{$viewBridgeMethods}
}
END
			);

			if ($generateViewbridge) {
				file_put_contents(
					$leaf . 'ViewBridge.js',
					$this->generateViewBridgeContent($leaf)
				);
			}
		}
	}
}