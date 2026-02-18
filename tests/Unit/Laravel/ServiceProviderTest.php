<?php

declare(strict_types=1);

namespace Tests\Unit\Laravel;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Petersons\D2L\AuthenticatedUriFactory;
use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\CourseUrlGenerator;
use Petersons\D2L\Laravel\ServiceProvider;
use PHPUnit\Framework\TestCase;

final class ServiceProviderTest extends TestCase
{
    public function testItRegistersTheHttpClient(): void
    {
        $container = new Container();
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->exactly(2))->method('get')->with('d2l')->willReturn([
            'host' => 'https://petersonstest.brightspace.com',
            'app_id' => 'baz',
            'app_key' => 'qux',
            'lms_user_id' => 'foo',
            'lms_user_key' => 'bar',
            'org_id' => 'quux',
            'installation_code' => 'quuz',
            'p_key' => 'corge',
            'api_lp_version' => '1.30',
            'api_le_version' => '1.53',
            'guid_login_uri' => '/d2l/lp/auth/login/ssoLogin.d2l',
        ]);

        $container->instance(Repository::class, $repository);

        $serviceProvider = new ServiceProvider($container);
        $serviceProvider->register();

        $httpClient = $container->make(ClientInterface::class);
        $this->assertInstanceOf(ClientInterface::class, $httpClient);
    }

    public function testItRegistersTheAuthenticatedUriFactory(): void
    {
        $container = new Container();
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())->method('get')->with('d2l')->willReturn([
            'host' => 'https://petersonstest.brightspace.com',
            'app_id' => 'baz',
            'app_key' => 'qux',
            'lms_user_id' => 'foo',
            'lms_user_key' => 'bar',
            'org_id' => 'quux',
            'installation_code' => 'quuz',
            'p_key' => 'corge',
            'api_lp_version' => '1.30',
            'api_le_version' => '1.53',
            'guid_login_uri' => '/d2l/lp/auth/login/ssoLogin.d2l',
        ]);

        $container->instance(Repository::class, $repository);

        $serviceProvider = new ServiceProvider($container);
        $serviceProvider->register();

        $authenticatedUriFactory = $container->make(AuthenticatedUriFactory::class);
        $this->assertInstanceOf(AuthenticatedUriFactory::class, $authenticatedUriFactory);
    }

    public function testItRegistersTheCourseUrlGenerator(): void
    {
        $container = new Container();
        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())->method('get')->with('d2l')->willReturn([
            'host' => 'https://petersonstest.brightspace.com',
            'app_id' => 'baz',
            'app_key' => 'qux',
            'lms_user_id' => 'foo',
            'lms_user_key' => 'bar',
            'org_id' => 'quux',
            'installation_code' => 'quuz',
            'p_key' => 'corge',
            'api_lp_version' => '1.30',
            'api_le_version' => '1.53',
            'guid_login_uri' => '/d2l/lp/auth/login/ssoLogin.d2l',
        ]);

        $container->instance(Repository::class, $repository);

        $serviceProvider = new ServiceProvider($container);
        $serviceProvider->register();

        $courseUrlGenerator = $container->make(CourseUrlGenerator::class);
        $this->assertInstanceOf(CourseUrlGenerator::class, $courseUrlGenerator);
    }

    public function testItPublishesTheConfig(): void
    {
        $app = $this->createMock(Application::class);
        $app->expects($this->once())->method('runningInConsole')->willReturn(true);
        $app->expects($this->once())->method('configPath')->with('d2l.php')->willReturn($path = '/foo/d2l.php');

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->boot();

        $this->assertSame([
            ServiceProvider::class => [
                realpath(__DIR__ . '/../../../src/Laravel/config/d2l.php') => $path,
            ],
        ], ServiceProvider::$publishes);

        $this->assertSame([
            'd2l-config' => [
                realpath(__DIR__ . '/../../../src/Laravel/config/d2l.php') => $path,
            ],
        ], ServiceProvider::$publishGroups);
    }

    public function testItProvidesTheNeededServices(): void
    {
        $serviceProvider = new ServiceProvider(new Container());
        $this->assertSame(
            [
                ClientInterface::class,
                AuthenticatedUriFactory::class,
                CourseUrlGenerator::class,
            ],
            $serviceProvider->provides(),
        );
    }
}
