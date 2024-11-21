<?php

require __DIR__ . '/../vendor/autoload.php';

use WHMCS\Database\Capsule;

class Application
{
    /**
     * Initial configuration for the module
     *
     * @return string[]
     */
    public static function config(): array
    {
        return [
            'name' => 'kyc',
            'description' => 'Description for kyc',
            'author' => 'YourCompany',
            'version' => '1.0.0',
            'enabled' => true,  // Set to false to disable the module
        ];
    }

    /**
     * Activate module
     *
     * @return string[]
     */
    public static function activate(): array
    {
        // Activation logic here
        return [
            'status' => 'success',
            'description' => 'kyc module activated successfully',
        ];
    }

    /**
     * Deactivate module
     *
     * @return string[]
     */
    public static function deactivate(): array
    {
        // Deactivation logic here
        return [
            'status' => 'success',
            'description' => 'kyc module deactivated successfully',
        ];
    }

    /**
     * Example of how to add a custom API (optional)
     */
    public static function createCustomApi()
    {
        // Custom API creation logic
    }
}
