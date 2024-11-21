<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
    protected static $defaultName = 'make:addon';

    protected function configure()
    {
        $this
            ->setDescription('Generate a new WHMCS addon module.')
            ->addArgument('addonName', InputArgument::REQUIRED, 'The name of the addon');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addonName = $input->getArgument('addonName');
        $currentDir = getcwd(); // Current working directory

        // Ensure the src directory exists
        $srcDir = $currentDir . DIRECTORY_SEPARATOR . 'src';
        if (!is_dir($srcDir)) {
            if (!mkdir($srcDir, 0777, true)) {
                $output->writeln("<error>Failed to create src directory. Please check permissions.</error>");
                return Command::FAILURE;
            }
            $output->writeln("<info>Created src directory: $srcDir</info>");
        }

        // Path to the new addon file
        $addonFilePath = $currentDir . DIRECTORY_SEPARATOR . $addonName . '.php';

        // Path to the stub file (template)
        $stubFile = __DIR__ . '/addon.stub';
        if (!file_exists($stubFile)) {
            $output->writeln("<error>Stub file not found at: $stubFile</error>");
            return Command::FAILURE;
        }

        // Read stub content and replace placeholders
        $addonFileContent = file_get_contents($stubFile);
        $addonFileContent = str_replace('$addonName', $addonName, $addonFileContent);

        // Create the addon file
        if (file_put_contents($addonFilePath, $addonFileContent)) {
            $output->writeln("<info>Created addon file: $addonFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $addonFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }

        // Now create the Application.php file
        $applicationFilePath = $srcDir . DIRECTORY_SEPARATOR . 'Application.php';
        $applicationStub = __DIR__ . '/application.stub';

        if (!file_exists($applicationStub)) {
            $output->writeln("<error>Stub file not found at: $applicationStub</error>");
            return Command::FAILURE;
        }

        $applicationFileContent = file_get_contents($applicationStub);
        $applicationFileContent = str_replace('$addonName', $addonName, $applicationFileContent);

        if (file_put_contents($applicationFilePath, $applicationFileContent)) {
            $output->writeln("<info>Created Application file: $applicationFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $applicationFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}


