<?php namespace janareit\laravel5generators;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use janareit\laravel5generators\FormDumpers\FieldsDumper;
use janareit\laravel5generators\FormDumpers\TableDumper;
use janareit\laravel5generators\FormGenerator;
use janareit\laravel5generators\Scaffolders\ControllerScaffolder;

class ScaffoldGenerator
{

    /**
     * The illuminate command instance.
     *
     * @var \Illuminate\Console\Command
     */
    protected $console;

    /**
     * The laravel instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $laravel;

    /**
     * The array of view names being created.
     *
     * @var array
     */
    protected $views = ['index', 'edit', 'show', 'create', 'form'];

    /**
     * Indicates the migration has been migrated.
     *
     * @var boolean
     */
    protected $migrated = false;

    /**
     * The constructor.
     *
     * @param Command $console
     */
    public function __construct(Command $console)
    {
        $this->console = $console;
        $this->laravel = $console->getLaravel();
    }

    /**
     * Get entity name.
     *
     * @return string
     */
    public function getEntity()
    {
        return strtolower(str_singular($this->console->argument('entity')));
    }

    /**
     * Get entities name.
     *
     * @return string
     */
    public function getEntities()
    {
        return str_plural($this->getEntity());
    }

    /**
     * Get controller name.
     *
     * @return string
     */
    public function getControllerName()
    {
        $controller = Str::studly($this->getEntities()).'Controller';

        if ($this->console->option('prefix')) {
            $controller = Str::studly($this->getPrefix('/')).$controller;
        }

        return str_replace('/', '\\', $controller);
    }

    /**
     * Confirm a question with the user.
     *
     * @param  string $message
     * @return string
     */
    public function confirm($message)
    {
        if ($this->console->option('no-question')) {
            return true;
        }
        
        return $this->console->confirm($message);
    }

    /**
     * Generate model.
     *
     * @return void
     */
    public function generateModel()
    {
        if (! $this->confirm('Do you want to create a model?')) {
            return;
        }

        $this->console->call('generate:model', [
            'name' => 'Repositories/'.$this->console->option('prefix').'/'.$this->getEntity(),
            '--fillable' => $this->console->option('fields'),
            '--force' => $this->console->option('force')
        ]);
    }

    /**
     * Generate seed.
     *
     * @return void
     */
    public function generateSeed()
    {
        if (! $this->confirm('Do you want to create a database seeder class?')) {
            return;
        }

        $this->console->call('generate:seed', [
            'name' => $this->getEntities(),
            '--force' => $this->console->option('force')
        ]);
    }

    /**
     * Generate migration.
     *
     * @return void
     */
    public function generateMigration()
    {
        if (! $this->confirm('Do you want to create a migration?')) {
            return;
        }

        $this->console->call('generate:migration', [
            'name' => "create_{$this->getEntities()}_table",
            '--fields' => $this->console->option('fields'),
            '--force' => $this->console->option('force')
        ]);
    }

    /**
     * Generate controller.
     *
     * @return void
     */
    public function generateController()
    {
        if (! $this->confirm('Do you want to generate a controller?')) {
            return;
        }

        $this->console->call('generate:controller', [
            'name' => $this->getControllerName(),
            '--force' => $this->console->option('force'),
            '--scaffold' => true
        ]);
    }

    /**
     * Get view layout.
     *
     * @return string
     */
    public function getViewLayout()
    {
        return (null !== $this->console->option('extends') ) ? $this->console->option('extends') : 'layouts/master';
    }

    /**
     * Generate a view layout.
     *
     * @return void
     */
    public function generateViewLayout()
    {
        if ($this->confirm('Do you want to create master view?')) {
            $this->console->call('generate:view', [
                'name' => $this->getViewLayout(),
                '--master' => true,
                '--force' => $this->console->option('force')
            ]);
        }
    }

    /**
     * Get controller scaffolder instance.
     *
     * @return string
     */
    public function getControllerScaffolder()
    {
        return new ControllerScaffolder($this->getEntity(), $this->getPrefix());
    }

    /**
     * Get form generator instance.
     *
     * @return string
     */
    public function getFormGenerator()
    {
        return new FormGenerator($this->getEntities(), $this->console->option('fields'));
    }

    /**
     * Get table dumper.
     *
     * @return mixed
     */
    public function getTableDumper()
    {
        if ($this->migrated) {
            return new TableDumper($this->getEntities());
        }

        return new FieldsDumper($this->console->option('fields'));
    }

    /**
     * Generate views.
     *
     * @return void
     */
    public function generateViews()
    {
        $this->generateViewLayout();

        if (! $this->confirm('Do you want to create view resources?')) {
            return;
        }

        foreach ($this->views as $view) {
            $this->generateView($view);
        }
    }

    /**
     * Generate a scaffold view.
     *
     * @param  string $view
     * @return void
     */
    public function generateView($view)
    {
        $generator = new ViewGenerator([
            'name' => $this->getPrefix('/').$this->getEntities().'/'.$view,
            'extends' => str_replace('/', '.', $this->getViewLayout()),
            'template' => __DIR__.'/Stubs/scaffold/views/'.$view.'.stub',
            'force' => $this->console->option('force')
        ]);

        $generator->appendReplacement(array_merge($this->getControllerScaffolder()->toArray(), [
            'lower_plural_entity' => strtolower($this->getEntities()),
            'studly_singular_entity' => Str::studly($this->getEntity()),
            'form' => $this->getFormGenerator()->render(),
            'table_heading' => $this->getTableDumper()->toHeading(),
            'table_body' => $this->getTableDumper()->toBody($this->getEntity()),
            'show_body' => $this->getTableDumper()->toRows($this->getEntity())
        ]));

        $generator->run();

        $this->console->info("View created successfully.");
    }

    /**
     * Append new route.
     *
     * @return void
     */
    public function appendRoute()
    {
        if (! $this->confirm('Do you want to append new route?')) {
            return;
        }

        $contents = $this->laravel['files']->get($path = app_path('Http/routes.php'));
        $contents .= PHP_EOL."Route::resource('{$this->getRouteName()}', '{$this->getControllerName()}');";

        $this->laravel['files']->put($path, $contents);

        $this->console->info("Route appended successfully.");
    }

    /**
     * Get route name.
     *
     * @return string
     */
    public function getRouteName()
    {
        $route = $this->getEntities();

        if ($this->console->option('prefix')) {
            $route = strtolower($this->getPrefix('/')).$route;
        }

        return $route;
    }

    /**
     * Get prefix name.
     *
     * @param  string|null $suffix
     * @return string|null
     */
    public function getPrefix($suffix = null)
    {
        $prefix = $this->console->option('prefix');

        return $prefix ? $prefix.$suffix : null;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function runMigration()
    {
        if ($this->confirm('Do you want to run all migration now?')) {
            $this->migrated = true;
            
            $this->console->call('migrate', [
                '--force' => $this->console->option('force')
            ]);
        }
    }

    /**
     * Generate request classes.
     *
     * @return void
     */
    public function generateRequest()
    {
        if (! $this->confirm('Do you want to create form request classes?')) {
            return;
        }

        foreach (['Create', 'Update'] as $request) {
            $name = $this->getPrefix('/').$this->getEntities().'/'. $request.Str::studly($this->getEntity()).'Request';

            $this->console->call('generate:request', [
                'name' => $name,
                '--scaffold' => true,
                '--auth' => true,
                '--rules' => $this->console->option('fields'),
                '--force' => $this->console->option('force')
            ]);
        }
    }

    /**
     * Append new route.
     *
     * @return void
     */
    public function appendRouteServiceProvider()
    {
        if (! $this->confirm('Do you want to append new route-service provider?')) {
            return;
        }

        $contents = $this->laravel['files']->get($path = app_path('Providers/RouteServiceProvider.php'));

        $contents = str_replace('//scaffolded routes will appear here [do not remove]',
            '//scaffolded routes will appear here [do not remove]'.PHP_EOL.PHP_EOL
           .'        $router->bind(\''. $this->getEntities() .'\', function($id) {'.PHP_EOL
           .'            return '. Str::studly($this->getEntity()) .'::findOrFail($id);'.PHP_EOL
           .'        });', $contents);

        $this->laravel['files']->put($path, $contents);

        $this->console->info("RouteServiceProvider appended successfully.");
    }

    /**
     * Run the generator.
     *
     * @return void
     */
    public function run()
    {
        $this->generateModel();
        $this->generateMigration();
        $this->generateSeed();
        $this->generateRequest();
        $this->generateController();
        $this->runMigration();
        $this->generateViews();
        $this->appendRoute();
        $this->appendRouteServiceProvider();
    }
}
