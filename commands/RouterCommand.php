<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RouterCommand extends Command
{
    protected static $defaultName = 'make:model';

    protected function configure()
    {
        $this
            ->setDescription('Generate a model and ensure its corresponding table exists in the database.');
        }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get the addon name from the parent directory
        $addonName  = basename(getcwd());

        $currentDir = getcwd();
        $modelFilePath = $currentDir . DIRECTORY_SEPARATOR.'Router.php';

        // Load and process the stub file
        $stubPath = __DIR__ . '/router.stub';  // Path to the stub file
        if (!file_exists($stubPath)) {
            $output->writeln("<error>Model stub file not found: $stubPath</error>");
            return Command::FAILURE;
        }

        // Read the stub and replace placeholders
        $stubContent = file_get_contents($stubPath);
        $modelStub = str_replace(
            ['{{addonName}}'],
            [$addonName],
            $stubContent
        );

        // Write the model file
        if (file_put_contents($modelFilePath, $modelStub)) {
            $output->writeln("<info>Created model: $modelFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create model file. Please check permissions.</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
