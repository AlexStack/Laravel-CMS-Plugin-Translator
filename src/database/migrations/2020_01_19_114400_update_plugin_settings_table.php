<?php

use AlexStack\LaravelCms\Models\LaravelCmsSetting;
use Illuminate\Database\Migrations\Migration;

class UpdatePluginSettingsTable extends Migration
{
    private $config;
    private $table_name;

    public function __construct()
    {
        $this->config     = include base_path('config/laravel-cms.php');
        $this->table_name = $this->config['table_name']['settings'];
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        $setting_data = [
            'category'        => 'plugin',
            'param_name'      => 'page-tab-translator',
            'input_attribute' => '{"rows":10,"required":"required"}',
            'enabled'         => 1,
            'sort_value'      => 20,
            'abstract'        => 'Automatically translate content via Google AI or Baidu AI. <a href="https://github.com/AlexStack/Laravel-CMS-Plugin-Translator#where-can-i-get-the-app_id--app_key" target="_blank"><i class="fas fa-link mr-1"></i>Tutorial</a>',
            'param_value'     => '{
    "api_provider": "Google_Free",
    "app_id": "google_free_001",
    "app_key": "google_free_001",                
    "plugin_name": "Content Translator",
    "blade_file": "translator",
    "tab_name": "<i class=\'fas fa-language mr-1\'></i>__(translator)",
    "php_class": "App\\\\LaravelCms\\\\Plugins\\\\Translator\\\\Controllers\\\\TranslatorController"
}',
        ];
        LaravelCmsSetting::UpdateOrCreate(
            ['category'=>'plugin', 'param_name' => 'page-tab-translator'],
            $setting_data
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        LaravelCmsSetting::where('param_name', 'page-tab-translator')->where('category', 'plugin')->delete();
    }
}
