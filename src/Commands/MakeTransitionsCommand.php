<?php

namespace TautId\Shipping\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeTransitionsCommand extends Command
{
    public $signature = 'taut-shipping:make-transitions';

    public $description = 'Create shipping transition files in App/Transitions/Shipping directory';

    protected array $transitions = [
        'ToCanceled',
        'ToDelivered',
        'ToDelivering',
        'ToDraft',
        'ToFailed',
        'ToLost',
        'ToRequested',
        'ToReturned'
    ];

    public function handle()
    {
        $path = 'app/Transitions/Shipping';
        $fullPath = base_path($path);

        // Create directory if it doesn't exist
        if (! File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
            $this->info("Created directory: {$path}");
        }

        foreach ($this->transitions as $transition) {
            $this->createTransitionFile($transition, $fullPath, $path);
        }

        $this->info('Shipping transitions created successfully!');
    }

    protected function createTransitionFile(string $className, string $fullPath, string $relativePath): void
    {
        $filePath = $fullPath.'/'.$className.'.php';

        if (File::exists($filePath)) {
            $this->warn("File already exists: {$relativePath}/{$className}.php");

            return;
        }

        $namespace = $this->getNamespaceFromPath($relativePath);
        $stub = $this->getStub($className, $namespace);

        File::put($filePath, $stub);
        $this->info("Created: {$relativePath}/{$className}.php");
    }

    protected function getNamespaceFromPath(string $path): string
    {
        // Convert path like "app/Transitions/Shipping" to "App\Transitions\Shipping"
        return str_replace(['/', 'app'], ['\\', 'App'], $path);
    }

    protected function getStub(string $className, string $namespace): string
    {
        return "<?php

namespace {$namespace};

use TautId\Shipping\Abstracts\ShippingTransitionAbstract;
use TautId\Shipping\Models\Shipping;

class {$className} extends ShippingTransitionAbstract
{
    public function handle(Shipping \$record): void
    {
        //
    }
}
";
    }
}
