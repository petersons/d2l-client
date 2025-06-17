<?php

declare(strict_types=1);

namespace Petersons\D2L\Laravel;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Petersons\D2L\AuthenticatedUriFactory;
use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\CourseUrlGenerator;
use Petersons\D2L\SymfonyHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\ScopingHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ServiceProvider extends LaravelServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->bind(ClientInterface::class, SymfonyHttpClient::class);

        $this->app->bind(HttpClientInterface::class, static function (): HttpClientInterface {
            return HttpClient::create();
        });

        $this->app->bind(SymfonyHttpClient::class, static function (Container $container): SymfonyHttpClient {
            /** @var Repository $config */
            $config = $container->get(Repository::class);
            $d2lConfig = $config->get('d2l');

            return new SymfonyHttpClient(
                ScopingHttpClient::forBaseUri(
                    $container->make(HttpClientInterface::class),
                    $d2lConfig['host'],
                ),
                $container->make(AuthenticatedUriFactory::class),
                $d2lConfig['org_id'],
                $d2lConfig['installation_code'],
                $d2lConfig['p_key'],
                $d2lConfig['api_lp_version'],
                $d2lConfig['api_le_version'],
            );
        });

        $this->app->bind(AuthenticatedUriFactory::class, static function (Container $container): AuthenticatedUriFactory {
            /** @var Repository $config */
            $config = $container->get(Repository::class);
            $d2lConfig = $config->get('d2l');

            return new AuthenticatedUriFactory(
                $d2lConfig['host'],
                $d2lConfig['app_id'],
                $d2lConfig['app_key'],
                $d2lConfig['lms_user_id'],
                $d2lConfig['lms_user_key'],
            );
        });

        $this->app->bind(CourseUrlGenerator::class, static function (Container $container): CourseUrlGenerator {
            /** @var Repository $config */
            $config = $container->get(Repository::class);
            $d2lConfig = $config->get('d2l');

            return new CourseUrlGenerator($d2lConfig['host'], $d2lConfig['guid_login_uri']);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/d2l.php' => $this->app->configPath('d2l.php'),
            ], 'd2l-config');
        }
    }

    public function provides(): array
    {
        return [ClientInterface::class, AuthenticatedUriFactory::class, CourseUrlGenerator::class];
    }
}
