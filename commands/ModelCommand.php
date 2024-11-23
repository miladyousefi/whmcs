<?php

namespace WHMCS\Module\Addon;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Database\Schema\Blueprint;

class ModelCommand extends Command
{
    protected static $defaultName = 'make:model';

    protected function configure()
    {
        $this
            ->setDescription('Generate a model and ensure its corresponding table exists in the database.')
            ->addArgument('modelName', InputArgument::REQUIRED, 'The name of the model');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addonName = basename(dirname(getcwd()));  // Extract addon name from the parent directory
        $modelName = $input->getArgument('modelName');
        $tableName = strtolower($modelName); // Automatically set tableName to lowercase modelName

        $currentDir = getcwd();
        $modelDir = $currentDir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Addon' . DIRECTORY_SEPARATOR . $addonName . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Models';

        // Ensure the models directory exists
        if (!is_dir($modelDir)) {
            if (!mkdir($modelDir, 0777, true)) {
                $output->writeln("<error>Failed to create models directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
        }

        // Path to the model file
        $modelFilePath = $modelDir . DIRECTORY_SEPARATOR . $modelName . '.php';

        // Load and process the stub file
        $stubPath = __DIR__ . '/model.stub';  // Path to the stub file
        if (!file_exists($stubPath)) {
            $output->writeln("<error>Model stub file not found: $stubPath</error>");
            return Command::FAILURE;
        }

        $stubContent = file_get_contents($stubPath);
        $modelStub = str_replace(
            ['{{addonName}}', '{{modelName}}', '{{tableName}}'],
            [$addonName, $modelName, $tableName],
            $stubContent
        );

        // Write the model file
        if (file_put_contents($modelFilePath, $modelStub)) {
            $output->writeln("<info>Created model: $modelFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create model file. Please check permissions.</error>");
            return Command::FAILURE;
        }

        // Set up the database and check if the table exists
        try {
            $this->setupDatabase();

            // Check if the table exists, if not, create it
            if (!Capsule::schema()->hasTable($tableName)) {
                $output->writeln("<info>Table '$tableName' does not exist. Creating table...</info>");
                Capsule::schema()->create($tableName, function (Blueprint $table) {
                    $table->increments('id');  // Default column for ID
                    $table->timestamps();  // Timestamps for created_at and updated_at
                });
                $output->writeln("<info>Table '$tableName' created successfully.</info>");
            } else {
                $output->writeln("<info>Table '$tableName' already exists.</info>");
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
