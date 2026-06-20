<?php
namespace App\Providers;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // @active('segment') → 'active' if current URL contains segment
        Blade::directive('active', function ($expression) {
            return "<?php echo (str_contains(request()->path(), {$expression})) ? 'active' : ''; ?>";
        });
    }
}
