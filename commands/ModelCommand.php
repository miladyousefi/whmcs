<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModelCommand extends Command
{
    protected static $defaultName = 'make:model';

    protected function configure()
    {
        $this
            ->setDescription('Generate a model and synchronize it with the database table.')
            ->addArgument('modelName', InputArgument::REQUIRED, 'The name of the model')
            ->addArgument('tableName', InputArgument::OPTIONAL, 'The name of the database table (default: pluralized model name)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $modelName = $input->getArgument('modelName');
        $tableName = $input->getArgument('tableName') ?? strtolower($modelName) . 's'; // Default pluralization

        $currentDir = getcwd();
        $modelDir = $currentDir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Models';

        // Ensure the models directory exists
        if (!is_dir($modelDir)) {
            if (!mkdir($modelDir, 0777, true)) {
                $output->writeln("<error>Failed to create models directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
        }

        // Path to the model file
        $modelFilePath = $modelDir . DIRECTORY_SEPARATOR . $modelName . '.php';

        // Model stub content
        $modelStub = <<<PHP
<?php

namespace WHMCS\Module\Addon\Models;

use Illuminate\Database\Eloquent\Model;

class $modelName extends Model
{
    protected \$table = '$tableName';
    protected \$fillable = []; // Add your fillable fields
}
PHP;

        // Write the model file
        if (file_put_contents($modelFilePath, $modelStub)) {
            $output->writeln("<info>Created model: $modelFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create model file. Please check permissions.</error>");
            return Command::FAILURE;
        }

        // Test database connection and synchronize table
        try {
            $this->setupDatabase();
            if (!Capsule::schema()->hasTable($tableName)) {
                $output->writeln("<error>Table '$tableName' does not exist. Please create the table first.</error>");
            } else {
                $output->writeln("<info>Model synchronized with table: $tableName</info>");
            }
        } catch (\Exception $e) {
            $output->writeln("<error>Failed to connect to the database: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function setupDatabase()
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'    => getenv('DB_CONNECTION'),
            'host'      => getenv('DB_HOST'),
            'database'  => getenv('DB_DATABASE'),
            'username'  => getenv('DB_USERNAME'),
            'password'  => getenv('DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
