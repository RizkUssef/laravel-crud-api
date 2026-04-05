<?php

namespace Rizkussef\LaravelCrudApi\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeApiCrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-crud:make {name : The name of the entity} {--no-migration : Do not create a migration file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all files needed for API CRUD (Model, Resource, Requests, Controller, Service, Migration)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        
        $this->info("Generating CRUD files for {$name}...");

        // 1. Model & Migration
        $modelOptions = ['name' => $name];
        if (!$this->option('no-migration')) {
            $modelOptions['-m'] = true;
        }
        $this->call('make:model', $modelOptions);

        // 2. Resource
        $this->call('make:resource', ['name' => "{$name}Resource"]);

        // 3. Form Requests
        $this->call('make:request', ['name' => "{$name}Request"]);
        $this->call('make:request', ['name' => "{$name}UpdateRequest"]);
        
        // 4. Controller
        $this->generateController($name);

        // 5. Service
        $this->generateService($name);

        $this->info("CRUD API for {$name} generated successfully.");
        return Command::SUCCESS;
    }

    protected function generateController($name)
    {
        $controllerNamespace = $this->laravel->getNamespace() . 'App/Http/Controllers';
        $controllerPath = app_path("Http/Controllers/{$name}Controller.php");

        $stub = File::get(__DIR__ . '/../Stubs/controller.stub');
        $stub = str_replace(
            ['{{ rootNamespace }}', '{{ class }}'],
            [$this->laravel->getNamespace(), $name],
            $stub
        );

        File::ensureDirectoryExists(app_path('Http/Controllers'));
        
        if (!File::exists($controllerPath)) {
            File::put($controllerPath, $stub);
            $this->components->info(sprintf('Controller [%s] created successfully.', "app/Http/Controllers/{$name}Controller.php"));
        } else {
            $this->components->error("Controller [Http/Controllers/{$name}Controller.php] already exists.");
        }
    }

    protected function generateService($name)
    {
        $serviceNamespace = $this->laravel->getNamespace() . 'App/Services';
        $servicePath = app_path("Services/{$name}Service.php");

        $stub = File::get(__DIR__ . '/../Stubs/service.stub');
        $stub = str_replace(
            ['{{ rootNamespace }}', '{{ class }}'],
            [$this->laravel->getNamespace(), $name],
            $stub
        );

        File::ensureDirectoryExists(app_path('Services'));
        
        if (!File::exists($servicePath)) {
            File::put($servicePath, $stub);
            $this->components->info(sprintf('Service [%s] created successfully.', "app/Services/{$name}Service.php"));
        } else {
            $this->components->error("Service [app/Services/{$name}Service.php] already exists.");
        }
    }
}
