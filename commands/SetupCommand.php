<?php
require __DIR__ . '/../vendor/autoload.php';

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
        // Get the addon name from the input argument
        $addonName = $input->getArgument('addonName');
        $currentDir = getcwd();  // Current working directory

        // Path to the new `addonName.php` file
        $addonFilePath = $currentDir . DIRECTORY_SEPARATOR . $addonName . '.php';

        // Path to the stub file (template)
        $stubFile = __DIR__ . '/addon.stub';
        if (!file_exists($stubFile)) {
            $output->writeln("<error>Stub file not found at: $stubFile</error>");
            return Command::FAILURE;
        }

        // Read stub content
        $addonFileContent = file_get_contents($stubFile);

        // Replace placeholders in the stub with the actual addon name
        $addonFileContent = str_replace('$addonName', $addonName, $addonFileContent);

        // Create the addonName.php file
        if (file_put_contents($addonFilePath, $addonFileContent)) {
            $output->writeln("<info>Created addon file: $addonFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $addonFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }

        // Now create the Application.php without namespace
        $applicationFilePath = $currentDir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Application.php';
        $applicationStub = __DIR__ . '/application.stub';

        if (!file_exists($applicationStub)) {
            $output->writeln("<error>Stub file not found at: $applicationStub</error>");
            return Command::FAILURE;
        }

        // Read application stub content
        $applicationFileContent = file_get_contents($applicationStub);

        // Replace placeholder `$addonName` in the Application.php
        $applicationFileContent = str_replace('$addonName', $addonName, $applicationFileContent);

        // Create the Application.php file
        if (file_put_contents($applicationFilePath, $applicationFileContent)) {
            $output->writeln("<info>Created Application file: $applicationFilePath</info>");
        } else {
            $output->writeln("<error>Failed to create $applicationFilePath. Please check permissions.</error>");
            return Command::FAILURE;
        }

        // Rename the folder to the addon name after files have been created
        $newFolderPath = dirname($currentDir) . DIRECTORY_SEPARATOR . $addonName;

        if (rename($currentDir, $newFolderPath)) {
            $output->writeln("<info>Folder renamed to: $addonName</info>");
        } else {
            $output->writeln("<error>Failed to rename folder. Please check permissions.</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
