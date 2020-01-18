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
            'abstract'        => 'Translate content by Baidu AI or Google AI',
            'param_value'     => '{
    "blade_file": "translator",
    "tab_name": "<i class=\'fas fa-language mr-1\'></i>__(translator)",
    "php_class": "App\\\\LaravelCms\\\\Plugins\\\\Translator\\\\Controllers\\\\TranslatorController",
    "api_provider": "baidu",
    "app_id": "",
    "app_key": "",
    "append_source_content": "yes"

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