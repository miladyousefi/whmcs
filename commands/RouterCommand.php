<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RouterCommand extends Command
{
    protected static $defaultName = 'make:router';

    protected function configure()
    {
        $this
            ->setDescription('Generate a Router.php file based on a stub file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Debugging output
        $output->writeln("<info>Running RouterCommand...</info>");

        // Get the addon name from the parent directory
        $addonName  = basename(getcwd());

        $currentDir = getcwd();
        $routerFilePath = $currentDir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Router.php';  // Ensure correct path

        // Load and process the stub file
        $stubPath = __DIR__ . '/../router.stub';  // Path to the stub file
        if (!file_exists($stubPath)) {
            $output->writeln("<error>Router stub file not found: $stubPath</error>");
            return Command::FAILURE;
        }

        // Read the stub and replace placeholders
        $stubContent = file_get_contents($stubPath);
        $routerStub = str_replace(
            ['{{addonName}}'],
            [$addonName],
            $stubContent
        );

        // Write the router file
        if (file_put_contents($routerFilePath, $routerStub)) {
            $output->writeln("<info>Created Router.php file: $routerFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create Router.php file. Please check permissions.</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
