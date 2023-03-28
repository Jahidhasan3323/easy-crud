<?php

namespace EasyCrud\console\commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;

class MakeCrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:easy-crud
                            {moduleName}
                            {--route=routes/web.php}
                            {--request}
                            {--api}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'perform crud operation using easy-crud package';

    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected Filesystem $files;

    protected string $type;

    protected string $modelBasePath = 'App/Models/';
    protected string $controllerBasePath = 'App/Http/Controllers/';
    public string $moduleName;
    private string $routePath;
    private string $controllerPath;
    private string $route;
    private string $moduleClassName;
    private string $storeRequestClass;
    private string $updateRequestClass;
    private bool|null $api;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }


    /**
     * @return void
     */
    #[NoReturn] public function handle(): void
    {
        $this->moduleName = $this->argument('moduleName');
        $this->api = $this->option('api');
        $this->moduleClassName = $this->className($this->moduleName);
        $this->routePath = base_path($this->option('route'));
        $this->route = Str::kebab($this->moduleName);
        if ($this->option('request')){
            $this->storeRequestClass = $this->getRequestClassName('Store');
            $this->updateRequestClass = $this->getRequestClassName('Update');
            $this->makeRequest();
        } else{
            $this->storeRequestClass = 'Request';
            $this->updateRequestClass = 'Request';
        }
//        dd($this->storeRequestClass ? str_replace("/", "\\", 'Illuminate/Http/Request/').'Store'.str_replace("/", "\\", $this->moduleName).'Request' : 'Request');
        $this->makeMigrations();
        $this->makeFiles('model');
        $this->makeFiles('controller');
        $this->makeRoute();
        $this->warn('please create blade files for list,form, details etc and add  file name in controller');
    }


    /**
     * @return string|void
     */
    public function getSourceFilePath()
    {
        if ($this->type === 'model'){
            return base_path($this->modelBasePath) . Str::ucfirst($this->moduleName) . '.php';
        }elseif ($this->type === 'controller'){
            return base_path($this->controllerBasePath) . Str::ucfirst($this->moduleName) . 'Controller.php';
        }
    }

    /**
     * @param $argument
     * @return string
     */
    public function className($argument): string
    {
        $argumentArray = explode('/', $argument);
        $moduleName = end($argumentArray);
        return Str::ucfirst($moduleName);
    }

    /**
     * @param $type
     * @return void
     */
    public function makeFiles($type): void
    {
        $this->type = $type;
        file_get_contents($this->getStubPath());
        $filePath = $this->getSourceFilePath();
        $contents = $this->getSourceFile();
        $this->makeDirectory(dirname($filePath));
        if (!$this->files->exists($filePath)) {
            $this->files->put($filePath, $contents);
            $this->info("File : {$filePath} created");
        } else {
            $this->error("File : {$filePath} already exits");
        }
    }

    /**
     * @return array|bool|string
     */
    public function getSourceFile(): bool|array|string
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }

    /**
     * @return string
     */
    public function getStubPath(): string
    {
        $controllerPath = $this->api  ? '/../../stubs/controller.api.stub' : '/../../stubs/controller.stub';
        return match ($this->type) {
            'model' => __DIR__ . '/../../stubs/model.stub',
            'controller' => __DIR__ . $controllerPath,
            default => '',
        };
    }

    /**
     * @param $path
     * @return mixed
     */
    protected function makeDirectory($path): mixed
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

    /**
     * @param $stub
     * @param array $stubVariables
     * @return string|array|bool
     */
    public function getStubContents($stub, array $stubVariables = []): string|array|bool
    {
        $contents = file_get_contents($stub);
        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('{{ ' . $search . ' }}', $replace, $contents);
        }

        return $contents;

    }

    /**
     * @return array
     */
    public function getStubVariables(): array
    {
        $variables = [];
        $moduleNameArray = explode('/', $this->moduleName);
        $moduleName = end($moduleNameArray);
        $subPath='';
        if (count($moduleNameArray) > 0 ){
            array_pop($moduleNameArray);
            $subPath = implode('\\', $moduleNameArray);
        }
        $this->controllerPath = $this->getNameSpace($this->controllerBasePath, $subPath);
        return match ($this->type) {
            'model' => [
                'namespace' => $this->getNameSpace($this->modelBasePath, $subPath),
                'class' => $this->moduleClassName,
            ],
            'controller' => [
                'namespace' => $this->controllerPath,
                'class' => $this->moduleClassName."Controller",
                'namespacedModel' => str_replace("/", "\\", $this->modelBasePath).str_replace("/", "\\", $this->moduleName),
                'rootNamespace' => "App\\",
                'namespacedRequests' => "Illuminate\Http\Request",
                'model' => ucfirst($moduleName),
                'modelVariable' => lcfirst($moduleName),
                'namespacedStoreRequests' => $this->getCustomRequestNameSpace('Store', $subPath),
                'namespacedUpdateRequests' => $this->getCustomRequestNameSpace('Update', $subPath),
                'storeRequest' => $this->storeRequestClass != 'Request' ? "Store".$this->moduleClassName."Request" : 'Request',
                'updateRequest' => $this->updateRequestClass != 'Request' ? "Update".$this->moduleClassName."Request" : 'Request',
                'redirectUrl' => "'{$this->route}'",
            ],
            default => $variables,
        };

    }

    /**
     * @return void
     */
    private function makeMigrations(): void
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->moduleName)));
        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
            '--fullpath' => true,
        ]);
    }

    /**
     * @return void
     */
    private function makeRoute(): void
    {
        $controllerPath = "{$this->controllerPath}\\{$this->moduleClassName}Controller::class";
        $contents = "Route::resource('{$this->route}', {$controllerPath});\n";
        $this->files->append($this->routePath, $contents);
        $this->info("{$this->route}this is the route for this feature");
    }

    /**
     * @return void
     */
    private function makeRequest(): void
    {
        $this->call('make:request', [
            'name' => $this->storeRequestClass,
        ]);

        $this->call('make:request', [
            'name' => $this->updateRequestClass,
        ]);
    }

    /**
     * @param string $path
     * @param string $subPath
     * @return string
     */
    public function getNameSpace(string $path, string $subPath): string
    {
        return str_replace("/", "\\", substr($path, 0, -1)) . ($subPath ? '\\' : '') . $subPath;
    }


    /**
     * @param $type
     * @param $subPath
     * @return string
     */
    public function getCustomRequestNameSpace($type, $subPath): string
    {
        $varName = strtolower($type)."RequestClass";
        return $this->$varName != "Request" ? "use App\Http\Requests\\".$subPath.($subPath ? '\\' : '').$type.$this->moduleClassName."Request;" : '';
    }

    /**
     * @param $type
     * @return string
     */
    public function getRequestClassName($type): string
    {
        $moduleNameArray = explode('/', $this->moduleName);
        $moduleName = end($moduleNameArray);
        $subPath='';
        if (count($moduleNameArray) > 0 ){
            array_pop($moduleNameArray);
            $subPath = implode('/', $moduleNameArray);
        }
        return $subPath.'/'.$type.ucfirst($moduleName).'Request';
    }
}
