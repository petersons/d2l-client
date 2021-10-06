<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\CarbonImmutable;
use Petersons\D2L\AuthenticatedUriFactory;
use Petersons\D2L\DTO\DataExport\CreateExportJobData;
use Petersons\D2L\DTO\DataExport\ExportJobFilter;
use Petersons\D2L\DTO\Enrollment\CreateEnrollment;
use Petersons\D2L\DTO\Enrollment\CreateSectionEnrollment;
use Petersons\D2L\DTO\Guid;
use Petersons\D2L\DTO\User\CreateUser;
use Petersons\D2L\DTO\User\UpdateUser;
use Petersons\D2L\Exceptions\ApiException;
use Petersons\D2L\SymfonyHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpClient\ScopingHttpClient;

final class SymfonyHttpClientTest extends TestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function testFetchingUserByOrgDefinedId(): void
    {
        $this->freezeTime();

        $userJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'user_show_response.json');
        $callback = function (string $method, string $url, array $options) use ($userJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&orgDefinedId=2.1296' === $url) {
                return new MockResponse($userJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $user = $client->getUserByOrgDefinedId('2.1296');

        $this->assertSame(6606, $user->getOrgId());
        $this->assertSame(3163, $user->getUserId());
        $this->assertSame('Roy', $user->getFirstName());
        $this->assertNull($user->getMiddleName());
        $this->assertSame('Burnap', $user->getLastName());
        $this->assertSame('Roy.Burnap.2.1456', $user->getUsername());
        $this->assertSame('petersons_1296_0@email.fake', $user->getExternalEmail());
        $this->assertSame('2.1296', $user->getOrgDefinedId());
        $this->assertSame('Roy.Burnap.2.1456', $user->getUniqueIdentifier());
        $this->assertTrue($user->isActive());
        $this->assertSame(
            CarbonImmutable::createFromFormat("Y-m-d\TH:i:s.v\Z", '2020-10-09T20:07:47.017Z')->toAtomString(),
            $user->getLastAccessedAt()->toAtomString()
        );
    }

    public function testFetchingUserByOrgDefinedIdWhenD2LReturnsForbiddenResponse(): void
    {
        $this->freezeTime();

        $callback = function (string $method, string $url, array $options): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&orgDefinedId=2.1296' === $url) {
                return new MockResponse('', ['http_code' => 403]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 403 returned for "https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&orgDefinedId=2.1296".',
                403
            )
        );

        $client->getUserByOrgDefinedId('2.1296');
    }

    public function testFetchingUserByOrgDefinedIdWhenD2LReturnsNotFoundResponse(): void
    {
        $this->freezeTime();

        $callback = function (string $method, string $url, array $options): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&orgDefinedId=2.1296' === $url) {
                return new MockResponse('', ['http_code' => 404]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 404 returned for "https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&orgDefinedId=2.1296".',
                404
            )
        );

        $client->getUserByOrgDefinedId('2.1296');
    }

    public function testFetchingUserByEmail(): void
    {
        $this->freezeTime();

        $userJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'user_show_response.json');
        $callback = function (string $method, string $url, array $options) use ($userJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&externalEmail=petersons_1296_0%40email.fake' === $url) {
                return new MockResponse($userJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $user = $client->getUserByEmail('petersons_1296_0@email.fake');

        $this->assertSame(6606, $user->getOrgId());
        $this->assertSame(3163, $user->getUserId());
        $this->assertSame('Roy', $user->getFirstName());
        $this->assertNull($user->getMiddleName());
        $this->assertSame('Burnap', $user->getLastName());
        $this->assertSame('Roy.Burnap.2.1456', $user->getUsername());
        $this->assertSame('petersons_1296_0@email.fake', $user->getExternalEmail());
        $this->assertSame('2.1296', $user->getOrgDefinedId());
        $this->assertSame('Roy.Burnap.2.1456', $user->getUniqueIdentifier());
        $this->assertTrue($user->isActive());
        $this->assertSame(
            CarbonImmutable::createFromFormat("Y-m-d\TH:i:s.v\Z", '2020-10-09T20:07:47.017Z')->toAtomString(),
            $user->getLastAccessedAt()->toAtomString()
        );
    }

    public function testFetchingUserByEmailWhenD2LReturnsForbiddenResponse(): void
    {
        $this->freezeTime();

        $callback = function (string $method, string $url, array $options): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&externalEmail=petersons_1296_0%40email.fake' === $url) {
                return new MockResponse('', ['http_code' => 403]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 403 returned for "https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&externalEmail=petersons_1296_0%40email.fake".',
                403
            )
        );

        $client->getUserByEmail('petersons_1296_0@email.fake');
    }

    public function testFetchingUserByEmailWhenD2LReturnsNotFoundResponse(): void
    {
        $this->freezeTime();

        $callback = function (string $method, string $url, array $options): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&externalEmail=petersons_1296_0%40email.fake' === $url) {
                return new MockResponse('', ['http_code' => 404]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 404 returned for "https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&externalEmail=petersons_1296_0%40email.fake".',
                404
            )
        );

        $client->getUserByEmail('petersons_1296_0@email.fake');
    }

    public function testCreatingUser(): void
    {
        $this->freezeTime();

        $createUser = new CreateUser(
            '2.1296999',
            'test',
            null,
            'test',
            'test1296999@gmail.com',
            'test 2.1296999',
            110,
            true,
            false
        );

        $userJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'user_create_response.json');
        $callback = function (string $method, string $url, array $options) use ($userJsonResponse, $createUser): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FVYO1pXtlMB6EMp6RyD8YLEWcFDfdmP8Hqica0asezc&x_d=9Qj7gcU5BwW5-n-ppJNqidxzeKFd1a3fmzhGP5zJedA&x_t=1615390200' === $url
                && $options['body'] === json_encode($createUser->toArray())
            ) {
                return new MockResponse($userJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $user = $client->createUser($createUser);

        $this->assertSame(6606, $user->getOrgId());
        $this->assertSame(3163, $user->getUserId());
        $this->assertSame('Roy', $user->getFirstName());
        $this->assertNull($user->getMiddleName());
        $this->assertSame('Burnap', $user->getLastName());
        $this->assertSame('Roy.Burnap.2.1456', $user->getUsername());
        $this->assertSame('petersons_1296_0@email.fake', $user->getExternalEmail());
        $this->assertSame('2.1296', $user->getOrgDefinedId());
        $this->assertSame('Roy.Burnap.2.1456', $user->getUniqueIdentifier());
        $this->assertTrue($user->isActive());
        $this->assertSame(
            CarbonImmutable::createFromFormat("Y-m-d\TH:i:s.v\Z", '2020-10-09T20:07:47.017Z')->toAtomString(),
            $user->getLastAccessedAt()->toAtomString()
        );
    }

    public function testCreatingUserWhenD2LReturnsForbiddenResponse(): void
    {
        $this->freezeTime();

        $createUser = new CreateUser(
            '2.1296999',
            'test',
            null,
            'test',
            'test1296999@gmail.com',
            'test 2.1296999',
            110,
            true,
            false
        );

        $callback = function (string $method, string $url, array $options) use ($createUser): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FVYO1pXtlMB6EMp6RyD8YLEWcFDfdmP8Hqica0asezc&x_d=9Qj7gcU5BwW5-n-ppJNqidxzeKFd1a3fmzhGP5zJedA&x_t=1615390200' === $url
                && $options['body'] === json_encode($createUser->toArray())
            ) {
                return new MockResponse('', ['http_code' => 403]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 403 returned for "https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FVYO1pXtlMB6EMp6RyD8YLEWcFDfdmP8Hqica0asezc&x_d=9Qj7gcU5BwW5-n-ppJNqidxzeKFd1a3fmzhGP5zJedA&x_t=1615390200".',
                403
            )
        );

        $client->createUser($createUser);
    }

    public function testUpdatingUser(): void
    {
        $this->freezeTime();

        $updateUser = new UpdateUser(
            4,
            '2.1296999',
            'test',
            null,
            'test',
            'test1296999@gmail.com',
            'test 2.1296999',
            true
        );

        $userJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'user_create_response.json');
        $callback = function (string $method, string $url, array $options) use ($userJsonResponse, $updateUser): MockResponse {
            if (
                'PUT' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/4?x_a=baz&x_b=foo&x_c=5Ex7iiPQRH3b4P_WtNuX7YPpI8__O4kWme8QmEaWApI&x_d=MKhR5KPF5YpER423SPeLWPVEY8dAsPP6V-j54hc-nhw&x_t=1615390200' === $url
                && $options['body'] === json_encode($updateUser->toArray())
            ) {
                return new MockResponse($userJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $user = $client->updateUser($updateUser);

        $this->assertSame(6606, $user->getOrgId());
        $this->assertSame(3163, $user->getUserId());
        $this->assertSame('Roy', $user->getFirstName());
        $this->assertNull($user->getMiddleName());
        $this->assertSame('Burnap', $user->getLastName());
        $this->assertSame('Roy.Burnap.2.1456', $user->getUsername());
        $this->assertSame('petersons_1296_0@email.fake', $user->getExternalEmail());
        $this->assertSame('2.1296', $user->getOrgDefinedId());
        $this->assertSame('Roy.Burnap.2.1456', $user->getUniqueIdentifier());
        $this->assertTrue($user->isActive());
        $this->assertSame(
            CarbonImmutable::createFromFormat("Y-m-d\TH:i:s.v\Z", '2020-10-09T20:07:47.017Z')->toAtomString(),
            $user->getLastAccessedAt()->toAtomString()
        );
    }

    public function testUpdatingUserWhenD2LReturnsForbiddenResponse(): void
    {
        $this->freezeTime();

        $updateUser = new UpdateUser(
            55,
            '2.1296999',
            'test',
            null,
            'test',
            'test1296999@gmail.com',
            'test 2.1296999',
            true
        );

        $callback = function (string $method, string $url, array $options) use ($updateUser): MockResponse {
            if (
                'PUT' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/55?x_a=baz&x_b=foo&x_c=7zhY2erfZt2IfdIV6pApfUgu9FdpuSBSHjmT9qAYeco&x_d=JFkmnn4gC96D5cJCQ3_f5bv4QJmB5XE7LtsRumFTu9w&x_t=1615390200' === $url
                && $options['body'] === json_encode($updateUser->toArray())
            ) {
                return new MockResponse('', ['http_code' => 403]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 403 returned for "https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/55?x_a=baz&x_b=foo&x_c=7zhY2erfZt2IfdIV6pApfUgu9FdpuSBSHjmT9qAYeco&x_d=JFkmnn4gC96D5cJCQ3_f5bv4QJmB5XE7LtsRumFTu9w&x_t=1615390200".',
                403
            )
        );

        $client->updateUser($updateUser);
    }

    public function testEnrollingTheUserInACourse(): void
    {
        $this->freezeTime();

        $createEnrollment = new CreateEnrollment(
            12388,
            3163,
            110
        );

        $userJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'enrollment_create_response.json');
        $callback = function (string $method, string $url, array $options) use ($createEnrollment, $userJsonResponse): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/api/lp/1.30/enrollments/?x_a=baz&x_b=foo&x_c=hIsndeo06aj1dSvhHN52io-9h3PkfGGRP5nwgD1KM4Q&x_d=g9ZoLJh27ZwBexHFmDRZyQgYr4GPyjEE-GQO9b8GbFk&x_t=1615390200' === $url
                &&
                $options['body'] === json_encode($createEnrollment->toArray())
            ) {
                return new MockResponse($userJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $enrollment = $client->enrollUser($createEnrollment);

        $this->assertSame(12388, $enrollment->getOrgUnitId());
        $this->assertSame(3163, $enrollment->getUserId());
        $this->assertSame(110, $enrollment->getRoleId());
        $this->assertFalse($enrollment->isCascading());
    }

    public function testUserEnrollmentWhenD2LReturnsForbiddenResponse(): void
    {
        $this->freezeTime();

        $createEnrollment = new CreateEnrollment(
            12388,
            3163,
            110
        );

        $callback = function (string $method, string $url, array $options) use ($createEnrollment): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/api/lp/1.30/enrollments/?x_a=baz&x_b=foo&x_c=hIsndeo06aj1dSvhHN52io-9h3PkfGGRP5nwgD1KM4Q&x_d=g9ZoLJh27ZwBexHFmDRZyQgYr4GPyjEE-GQO9b8GbFk&x_t=1615390200' === $url
                && $options['body'] === json_encode($createEnrollment->toArray())
            ) {
                return new MockResponse('', ['http_code' => 403]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 403 returned for "https://petersonstest.brightspace.com/d2l/api/lp/1.30/enrollments/?x_a=baz&x_b=foo&x_c=hIsndeo06aj1dSvhHN52io-9h3PkfGGRP5nwgD1KM4Q&x_d=g9ZoLJh27ZwBexHFmDRZyQgYr4GPyjEE-GQO9b8GbFk&x_t=1615390200".',
                403
            )
        );

        $client->enrollUser($createEnrollment);
    }

    public function testEnrollingTheUserInASection(): void
    {
        $this->freezeTime();

        $createSectionEnrollment = new CreateSectionEnrollment(
            12388,
            3163,
            0
        );

        $userJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'enrollment_create_response.json');
        $callback = function (string $method, string $url, array $options) use ($createSectionEnrollment, $userJsonResponse): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/api/lp/1.30/12388/sections/0/enrollments/?x_a=baz&x_b=foo&x_c=-HqVi_1dCey5Is_0t4inJdkZxcwnaO7x4XTEyzAXvik&x_d=6HzRkHoUuf6DtvrwaKVqahGo-X92O4VvvZbDRR_WpeQ&x_t=1615390200' === $url
                &&
                $options['body'] === json_encode($createSectionEnrollment->toArray())
            ) {
                return new MockResponse($userJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $client->enrollUserInASection($createSectionEnrollment);

        $this->assertTrue(true);
    }

    public function testEnrollingTheUserInASectionWhenD2LReturnsForbiddenResponse(): void
    {
        $this->freezeTime();

        $createSectionEnrollment = new CreateSectionEnrollment(
            12388,
            3163,
            0
        );

        $callback = function (string $method, string $url, array $options) use ($createSectionEnrollment): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/api/lp/1.30/12388/sections/0/enrollments/?x_a=baz&x_b=foo&x_c=-HqVi_1dCey5Is_0t4inJdkZxcwnaO7x4XTEyzAXvik&x_d=6HzRkHoUuf6DtvrwaKVqahGo-X92O4VvvZbDRR_WpeQ&x_t=1615390200' === $url
                && $options['body'] === json_encode($createSectionEnrollment->toArray())
            ) {
                return new MockResponse('', ['http_code' => 403]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 403 returned for "https://petersonstest.brightspace.com/d2l/api/lp/1.30/12388/sections/0/enrollments/?x_a=baz&x_b=foo&x_c=-HqVi_1dCey5Is_0t4inJdkZxcwnaO7x4XTEyzAXvik&x_d=6HzRkHoUuf6DtvrwaKVqahGo-X92O4VvvZbDRR_WpeQ&x_t=1615390200".',
                403
            )
        );

        $client->enrollUserInASection($createSectionEnrollment);
    }

    public function testGenerateExpiringGuidWhenTheResponseIsValidXml(): void
    {
        $this->freezeTime();

        $guidValue = bin2hex(random_bytes(10));

        $callback = function (string $method, string $url, array $options) use ($guidValue): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/guids/D2L.Guid.2.asmx/GenerateExpiringGuid' === $url
                &&
                $options['body'] === 'guidType=SSO&orgId=quux&installCode=quuz&TTL=90&data=2.1296&key=corge'
            ) {
                return new MockResponse(sprintf('<?xml version="1.0" encoding="utf-8"?>
<string xmlns="http://desire2learn.com/">%s</string>', $guidValue));
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $guid = $client->generateExpiringGuid('2.1296');

        $this->assertSame($guidValue, $guid->getValue());
    }

    public function testGenerateExpiringGuidWhenTheResponseIsNotXml(): void
    {
        $this->freezeTime();

        $callback = function (string $method, string $url, array $options): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/guids/D2L.Guid.2.asmx/GenerateExpiringGuid' === $url
                &&
                $options['body'] === 'guidType=SSO&orgId=quux&installCode=quuz&TTL=90&data=2.1296&key=corge'
            ) {
                return new MockResponse('foobar');
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'Invalid response - "foobar" given',
                0
            )
        );

        $client->generateExpiringGuid('2.1296');
    }

    public function testGenerateExpiringGuidWhenTheResponseIsNotTheExpectedXml(): void
    {
        $this->freezeTime();

        $callback = function (string $method, string $url, array $options): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/guids/D2L.Guid.2.asmx/GenerateExpiringGuid' === $url
                &&
                $options['body'] === 'guidType=SSO&orgId=quux&installCode=quuz&TTL=90&data=2.1296&key=corge'
            ) {
                return new MockResponse('<?xml version="1.0" encoding="utf-8"?>
<root>
  <foo>bar</foo>
</root>');
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'Invalid API response received. The response was "<?xml version="1.0" encoding="utf-8"?>
<root>
  <foo>bar</foo>
</root>"',
                0
            )
        );

        $client->generateExpiringGuid('2.1296');
    }

    public function testGuidValidationWhenTheResponseIsThatTheGuidIsOk(): void
    {
        $this->freezeTime();

        $guidValue = bin2hex(random_bytes(10));

        $callback = function (string $method, string $url, array $options) use ($guidValue): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/guids/D2L.Guid.2.asmx/ValidateGuid' === $url
                &&
                $options['body'] === sprintf('guid=%s&guidType=SSO&orgId=quux&installCode=quuz&TTL=90&data=2.1296&key=corge', $guidValue)
            ) {
                return new MockResponse('<?xml version="1.0" encoding="utf-8"?>
<ValidationReply xmlns="http://desire2learn.com/">OK</ValidationReply>');
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $isGuidValid = $client->validateGuid(new Guid($guidValue), '2.1296');

        $this->assertTrue($isGuidValid);
    }

    public function testGuidValidationWhenTheResponseIsThatTheGuidIsExpired(): void
    {
        $this->freezeTime();

        $guidValue = bin2hex(random_bytes(10));

        $callback = function (string $method, string $url, array $options) use ($guidValue): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/guids/D2L.Guid.2.asmx/ValidateGuid' === $url
                &&
                $options['body'] === sprintf('guid=%s&guidType=SSO&orgId=quux&installCode=quuz&TTL=90&data=2.1296&key=corge', $guidValue)
            ) {
                return new MockResponse('<?xml version="1.0" encoding="utf-8"?>
<ValidationReply xmlns="http://desire2learn.com/">EXPIRED</ValidationReply>');
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $isGuidValid = $client->validateGuid(new Guid($guidValue), '2.1296');

        $this->assertFalse($isGuidValid);
    }

    public function testGuidValidationWhenTheResponseIsThatTheGuidIsInvalid(): void
    {
        $this->freezeTime();

        $guidValue = bin2hex(random_bytes(10));

        $callback = function (string $method, string $url, array $options) use ($guidValue): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/guids/D2L.Guid.2.asmx/ValidateGuid' === $url
                &&
                $options['body'] === sprintf('guid=%s&guidType=SSO&orgId=quux&installCode=quuz&TTL=90&data=2.1296&key=corge', $guidValue)
            ) {
                return new MockResponse('<?xml version="1.0" encoding="utf-8"?>
<ValidationReply xmlns="http://desire2learn.com/">INVALID_GUID</ValidationReply>');
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $isGuidValid = $client->validateGuid(new Guid($guidValue), '2.1296');

        $this->assertFalse($isGuidValid);
    }

    public function testGuidValidationWhenTheResponseIsThatTheGuidStatusIsError(): void
    {
        $this->freezeTime();

        $guidValue = bin2hex(random_bytes(10));

        $callback = function (string $method, string $url, array $options) use ($guidValue): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/guids/D2L.Guid.2.asmx/ValidateGuid' === $url
                &&
                $options['body'] === sprintf('guid=%s&guidType=SSO&orgId=quux&installCode=quuz&TTL=90&data=2.1296&key=corge', $guidValue)
            ) {
                return new MockResponse('<?xml version="1.0" encoding="utf-8"?>
<ValidationReply xmlns="http://desire2learn.com/">ERROR</ValidationReply>');
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $isGuidValid = $client->validateGuid(new Guid($guidValue), '2.1296');

        $this->assertFalse($isGuidValid);
    }

    public function testGuidValidationWhenTheResponseIsThatTheGuidStatusIsUnknownVersion(): void
    {
        $this->freezeTime();

        $guidValue = bin2hex(random_bytes(10));

        $callback = function (string $method, string $url, array $options) use ($guidValue): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/guids/D2L.Guid.2.asmx/ValidateGuid' === $url
                &&
                $options['body'] === sprintf('guid=%s&guidType=SSO&orgId=quux&installCode=quuz&TTL=90&data=2.1296&key=corge', $guidValue)
            ) {
                return new MockResponse('<?xml version="1.0" encoding="utf-8"?>
<ValidationReply xmlns="http://desire2learn.com/">UNKNOWN_VERSION</ValidationReply>');
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $isGuidValid = $client->validateGuid(new Guid($guidValue), '2.1296');

        $this->assertFalse($isGuidValid);
    }

    public function testGuidValidationWhenTheResponseIsThatTheGuidStatusIsInvalidData(): void
    {
        $this->freezeTime();

        $guidValue = bin2hex(random_bytes(10));

        $callback = function (string $method, string $url, array $options) use ($guidValue): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/guids/D2L.Guid.2.asmx/ValidateGuid' === $url
                &&
                $options['body'] === sprintf('guid=%s&guidType=SSO&orgId=quux&installCode=quuz&TTL=90&data=2.1296&key=corge', $guidValue)
            ) {
                return new MockResponse('<?xml version="1.0" encoding="utf-8"?>
<ValidationReply xmlns="http://desire2learn.com/">INVALID_DATA</ValidationReply>');
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $isGuidValid = $client->validateGuid(new Guid($guidValue), '2.1296');

        $this->assertFalse($isGuidValid);
    }

    public function testGuidValidationWhenTheResponseIsThatTheGuidStatusIsNoDbConnection(): void
    {
        $this->freezeTime();

        $guidValue = bin2hex(random_bytes(10));

        $callback = function (string $method, string $url, array $options) use ($guidValue): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/guids/D2L.Guid.2.asmx/ValidateGuid' === $url
                &&
                $options['body'] === sprintf('guid=%s&guidType=SSO&orgId=quux&installCode=quuz&TTL=90&data=2.1296&key=corge', $guidValue)
            ) {
                return new MockResponse('<?xml version="1.0" encoding="utf-8"?>
<ValidationReply xmlns="http://desire2learn.com/">NO_DB_CONNECTION</ValidationReply>');
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $isGuidValid = $client->validateGuid(new Guid($guidValue), '2.1296');

        $this->assertFalse($isGuidValid);
    }

    public function testGuidValidationWhenTheResponseIsNotXml(): void
    {
        $this->freezeTime();

        $guidValue = bin2hex(random_bytes(10));

        $callback = function (string $method, string $url, array $options) use ($guidValue): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/guids/D2L.Guid.2.asmx/ValidateGuid' === $url
                &&
                $options['body'] === sprintf('guid=%s&guidType=SSO&orgId=quux&installCode=quuz&TTL=90&data=2.1296&key=corge', $guidValue)
            ) {
                return new MockResponse('foobar');
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'Invalid response - "foobar" given',
                0
            )
        );

        $client->validateGuid(new Guid($guidValue), '2.1296');
    }

    public function testGetBrightspaceDataExportList(): void
    {
        $this->freezeTime();

        $brightspaceDataExportListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'brightspace_data_export_list.json');
        $callback = function (string $method, string $url, array $options) use ($brightspaceDataExportListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/dataExport/bds/list?x_a=baz&x_b=foo&x_c=R8GMLAGxo3gvzUiRXg72QXY9feXFqwBf-0f8MKnplVM&x_d=xZLSsODHjfaEqRBmRyh-ZcELNzmmdJ4MOEvpD6nh7rg&x_t=1615390200' === $url) {
                return new MockResponse($brightspaceDataExportListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $brightspaceDataExportList = $client->getBrightspaceDataExportList();

        $this->assertCount(2, $brightspaceDataExportList);

        $this->assertSame('1fa8ff9c-8702-46fc-a863-18ca6c2cc4d1', $brightspaceDataExportList[0]->getPluginId());
        $this->assertSame('Grade Objects Log', $brightspaceDataExportList[0]->getName());
        $this->assertSame('The grade objects log data set is a log of all changes to grades for each applicable user in the organization.', $brightspaceDataExportList[0]->getDescription());
        $this->assertSame('2021-05-18T19:50:19+00:00', $brightspaceDataExportList[0]->getCreatedAt()->toAtomString());
        $this->assertSame('https://learn.petersons.com/d2l/api/lp/1.30/dataexport/bds/download/1fa8ff9c-8702-46fc-a863-18ca6c2cc4d1', $brightspaceDataExportList[0]->getDownloadLink());
        $this->assertSame(28709805.0, $brightspaceDataExportList[0]->getDownloadSize());

        $this->assertSame('df537dc9-8358-4c28-9ab9-ddb8d364a9fc', $brightspaceDataExportList[1]->getPluginId());
        $this->assertSame('Rubric Object Criteria', $brightspaceDataExportList[1]->getName());
        $this->assertSame('The rubric object criteria data set returns the basic details for all rubric object criteria.', $brightspaceDataExportList[1]->getDescription());
        $this->assertSame('2021-05-18T19:50:19+00:00', $brightspaceDataExportList[1]->getCreatedAt()->toAtomString());
        $this->assertSame('https://learn.petersons.com/d2l/api/lp/1.30/dataexport/bds/download/df537dc9-8358-4c28-9ab9-ddb8d364a9fc', $brightspaceDataExportList[1]->getDownloadLink());
        $this->assertSame(288.0, $brightspaceDataExportList[1]->getDownloadSize());
    }

    public function testGetBrightspaceDataExportItems(): void
    {
        $this->freezeTime();

        $brightspaceDataExportItemsJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'brightspace_data_export_items.json');
        $callback = function (string $method, string $url, array $options) use ($brightspaceDataExportItemsJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/dataExport/bds?x_a=baz&x_b=foo&x_c=D4R-m0hAkQLk_oAT696FNgE3cOb8VhSBy_AD3_3HmXk&x_d=E4BVesjXL0tVYK-gS4Pf7bKu1nct6zXLhobwsALaZX4&x_t=1615390200&page=1&pageSize=1000' === $url) {
                return new MockResponse($brightspaceDataExportItemsJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $brightspaceDataExportItems = $client->getBrightspaceDataExportItems(1, 1000);

        $brightspaceDataSets = $brightspaceDataExportItems->getBrightspaceDataSets();

        $this->assertSame(
            'https://learn.petersons.com/d2l/api/lp/1.30/dataexport/bds?page=2&pageSize=2',
            $brightspaceDataExportItems->getNextPageUrl()
        );
        $this->assertNull($brightspaceDataExportItems->getPrevPageUrl());
        $this->assertCount(2, $brightspaceDataSets);

        $this->assertSame('df537dc9-8358-4c28-9ab9-ddb8d364a9fc', $brightspaceDataSets[0]->getPluginId());
        $this->assertSame('Rubric Object Criteria', $brightspaceDataSets[0]->getName());
        $this->assertSame('The rubric object criteria data set returns the basic details for all rubric object criteria.', $brightspaceDataSets[0]->getDescription());
        $this->assertTrue($brightspaceDataSets[0]->isFullDataset());
        $this->assertSame('2021-05-18T19:50:19+00:00', $brightspaceDataSets[0]->getCreatedDate()->toAtomString());
        $this->assertSame('https://learn.petersons.com/d2l/api/lp/1.30/dataexport/bds/df537dc9-8358-4c28-9ab9-ddb8d364a9fc/1621367362', $brightspaceDataSets[0]->getDownloadLink());
        $this->assertSame(288.0, $brightspaceDataSets[0]->getDownloadSize());
        $this->assertSame('6.6', $brightspaceDataSets[0]->getVersion());
        $this->assertSame('2021-05-18T19:49:22+00:00', $brightspaceDataSets[0]->getQueuedForProcessingDate()->toAtomString());
        $this->assertCount(1, $brightspaceDataSets[0]->getPreviousDataSets());

        $this->assertSame('df537dc9-8358-4c28-9ab9-ddb8d364a9fc', $brightspaceDataSets[0]->getPreviousDataSets()[0]->getPluginId());
        $this->assertSame('Rubric Object Criteria', $brightspaceDataSets[0]->getPreviousDataSets()[0]->getName());
        $this->assertSame('The rubric object criteria data set returns the basic details for all rubric object criteria.', $brightspaceDataSets[0]->getPreviousDataSets()[0]->getDescription());
        $this->assertTrue($brightspaceDataSets[0]->getPreviousDataSets()[0]->isFullDataset());
        $this->assertSame('2021-05-11T19:49:46+00:00', $brightspaceDataSets[0]->getPreviousDataSets()[0]->getCreatedDate()->toAtomString());
        $this->assertSame('https://learn.petersons.com/d2l/api/lp/1.30/dataexport/bds/df537dc9-8358-4c28-9ab9-ddb8d364a9fc/1620762542', $brightspaceDataSets[0]->getPreviousDataSets()[0]->getDownloadLink());
        $this->assertSame(288.0, $brightspaceDataSets[0]->getPreviousDataSets()[0]->getDownloadSize());
        $this->assertSame('6.6', $brightspaceDataSets[0]->getPreviousDataSets()[0]->getVersion());
        $this->assertSame('2021-05-11T19:49:02+00:00', $brightspaceDataSets[0]->getPreviousDataSets()[0]->getQueuedForProcessingDate()->toAtomString());
        $this->assertNull($brightspaceDataSets[0]->getPreviousDataSets()[0]->getPreviousDataSets());

        $this->assertSame('1fa8ff9c-8702-46fc-a863-18ca6c2cc4d1', $brightspaceDataSets[1]->getPluginId());
        $this->assertSame('Grade Objects Log', $brightspaceDataSets[1]->getName());
        $this->assertSame('The grade objects log data set is a log of all changes to grades for each applicable user in the organization.', $brightspaceDataSets[1]->getDescription());
        $this->assertTrue($brightspaceDataSets[1]->isFullDataset());
        $this->assertSame('2021-05-18T19:50:19+00:00', $brightspaceDataSets[1]->getCreatedDate()->toAtomString());
        $this->assertSame('https://learn.petersons.com/d2l/api/lp/1.30/dataexport/bds/1fa8ff9c-8702-46fc-a863-18ca6c2cc4d1/1621367362', $brightspaceDataSets[1]->getDownloadLink());
        $this->assertSame(28709805.0, $brightspaceDataSets[1]->getDownloadSize());
        $this->assertSame('6.6', $brightspaceDataSets[1]->getVersion());
        $this->assertSame('2021-05-18T19:49:22+00:00', $brightspaceDataSets[1]->getQueuedForProcessingDate()->toAtomString());
        $this->assertCount(1, $brightspaceDataSets[1]->getPreviousDataSets());

        $this->assertSame('1fa8ff9c-8702-46fc-a863-18ca6c2cc4d1', $brightspaceDataSets[1]->getPreviousDataSets()[0]->getPluginId());
        $this->assertSame('Grade Objects Log', $brightspaceDataSets[1]->getPreviousDataSets()[0]->getName());
        $this->assertSame('The grade objects log data set is a log of all changes to grades for each applicable user in the organization.', $brightspaceDataSets[1]->getPreviousDataSets()[0]->getDescription());
        $this->assertTrue($brightspaceDataSets[1]->getPreviousDataSets()[0]->isFullDataset());
        $this->assertSame('2021-05-11T19:49:45+00:00', $brightspaceDataSets[1]->getPreviousDataSets()[0]->getCreatedDate()->toAtomString());
        $this->assertSame('https://learn.petersons.com/d2l/api/lp/1.30/dataexport/bds/1fa8ff9c-8702-46fc-a863-18ca6c2cc4d1/1620762542', $brightspaceDataSets[1]->getPreviousDataSets()[0]->getDownloadLink());
        $this->assertSame(27068473.0, $brightspaceDataSets[1]->getPreviousDataSets()[0]->getDownloadSize());
        $this->assertSame('6.6', $brightspaceDataSets[1]->getPreviousDataSets()[0]->getVersion());
        $this->assertSame('2021-05-11T19:49:02+00:00', $brightspaceDataSets[1]->getPreviousDataSets()[0]->getQueuedForProcessingDate()->toAtomString());
        $this->assertNull($brightspaceDataSets[1]->getPreviousDataSets()[0]->getPreviousDataSets());
    }

    public function testFindBrightspaceDataExportItemByNameWhenAnItemWithTheSearchedNameExists(): void
    {
        $this->freezeTime();

        $brightspaceDataExportItemsJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'brightspace_data_export_items.json');
        $callback = function (string $method, string $url, array $options) use ($brightspaceDataExportItemsJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/dataExport/bds?x_a=baz&x_b=foo&x_c=D4R-m0hAkQLk_oAT696FNgE3cOb8VhSBy_AD3_3HmXk&x_d=E4BVesjXL0tVYK-gS4Pf7bKu1nct6zXLhobwsALaZX4&x_t=1615390200&page=1&pageSize=1000' === $url) {
                return new MockResponse($brightspaceDataExportItemsJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $brightspaceDataSetReportInfo = $client->findBrightspaceDataExportItemByName('Rubric Object Criteria');

        $this->assertSame('df537dc9-8358-4c28-9ab9-ddb8d364a9fc', $brightspaceDataSetReportInfo->getPluginId());
        $this->assertSame('Rubric Object Criteria', $brightspaceDataSetReportInfo->getName());
        $this->assertSame('The rubric object criteria data set returns the basic details for all rubric object criteria.', $brightspaceDataSetReportInfo->getDescription());
        $this->assertTrue($brightspaceDataSetReportInfo->isFullDataset());
        $this->assertSame('2021-05-18T19:50:19+00:00', $brightspaceDataSetReportInfo->getCreatedDate()->toAtomString());
        $this->assertSame('https://learn.petersons.com/d2l/api/lp/1.30/dataexport/bds/df537dc9-8358-4c28-9ab9-ddb8d364a9fc/1621367362', $brightspaceDataSetReportInfo->getDownloadLink());
        $this->assertSame(288.0, $brightspaceDataSetReportInfo->getDownloadSize());
        $this->assertSame('6.6', $brightspaceDataSetReportInfo->getVersion());
        $this->assertSame('2021-05-18T19:49:22+00:00', $brightspaceDataSetReportInfo->getQueuedForProcessingDate()->toAtomString());
        $this->assertCount(1, $brightspaceDataSetReportInfo->getPreviousDataSets());

        $this->assertSame('df537dc9-8358-4c28-9ab9-ddb8d364a9fc', $brightspaceDataSetReportInfo->getPreviousDataSets()[0]->getPluginId());
        $this->assertSame('Rubric Object Criteria', $brightspaceDataSetReportInfo->getPreviousDataSets()[0]->getName());
        $this->assertSame('The rubric object criteria data set returns the basic details for all rubric object criteria.', $brightspaceDataSetReportInfo->getPreviousDataSets()[0]->getDescription());
        $this->assertTrue($brightspaceDataSetReportInfo->getPreviousDataSets()[0]->isFullDataset());
        $this->assertSame('2021-05-11T19:49:46+00:00', $brightspaceDataSetReportInfo->getPreviousDataSets()[0]->getCreatedDate()->toAtomString());
        $this->assertSame('https://learn.petersons.com/d2l/api/lp/1.30/dataexport/bds/df537dc9-8358-4c28-9ab9-ddb8d364a9fc/1620762542', $brightspaceDataSetReportInfo->getPreviousDataSets()[0]->getDownloadLink());
        $this->assertSame(288.0, $brightspaceDataSetReportInfo->getPreviousDataSets()[0]->getDownloadSize());
        $this->assertSame('6.6', $brightspaceDataSetReportInfo->getPreviousDataSets()[0]->getVersion());
        $this->assertSame('2021-05-11T19:49:02+00:00', $brightspaceDataSetReportInfo->getPreviousDataSets()[0]->getQueuedForProcessingDate()->toAtomString());
        $this->assertNull($brightspaceDataSetReportInfo->getPreviousDataSets()[0]->getPreviousDataSets());
    }

    public function testFindBrightspaceDataExportItemByNameWhenAnItemWithTheSearchedNameDoesNotExist(): void
    {
        $this->freezeTime();

        $brightspaceDataExportItemsJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'brightspace_data_export_items.json');
        $callback = function (string $method, string $url, array $options) use ($brightspaceDataExportItemsJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/dataExport/bds?x_a=baz&x_b=foo&x_c=D4R-m0hAkQLk_oAT696FNgE3cOb8VhSBy_AD3_3HmXk&x_d=E4BVesjXL0tVYK-gS4Pf7bKu1nct6zXLhobwsALaZX4&x_t=1615390200&page=1&pageSize=1000' === $url) {
                return new MockResponse($brightspaceDataExportItemsJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->assertNull($client->findBrightspaceDataExportItemByName('Rubric Object Criteria 1'));
    }

    public function testQuizzesListWithoutBookmark(): void
    {
        $this->freezeTime();

        $quizzesListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'quizzes_list.json');
        $callback = function (string $method, string $url, array $options) use ($quizzesListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/513982/quizzes/?x_a=baz&x_b=foo&x_c=UtDgaa6gsOZvsTtjqMFdV91XFGy-DjoCVcn1ZSYOjVY&x_d=z8jqC7KwlcEPSuv247ZiwTeiEd97tYXSUr9JUc9fibk&x_t=1615390200&bookmark=' === $url) {
                return new MockResponse($quizzesListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $quizListPage = $client->quizzesList(513982);

        $this->assertSame(
            'https://learn.petersons.com/d2l/api/le/1.53/513982/quizzes/?bookmark=41594_21',
            $quizListPage->getNextUrl()
        );

        $quizzes = $quizListPage->getObjects();

        $this->assertCount(2, $quizzes);

        $this->assertSame(41575, $quizzes[0]->getId());
        $this->assertSame('Diagnostic Test - Arithmetic Reasoning', $quizzes[0]->getName());
        $this->assertTrue($quizzes[0]->isActive());

        $this->assertSame(41576, $quizzes[1]->getId());
        $this->assertSame('Diagnostic Test - Word Knowledge', $quizzes[1]->getName());
        $this->assertTrue($quizzes[1]->isActive());
    }

    public function testQuizzesListWithBookmark(): void
    {
        $this->freezeTime();

        $quizzesListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'quizzes_list.json');
        $callback = function (string $method, string $url, array $options) use ($quizzesListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/513982/quizzes/?x_a=baz&x_b=foo&x_c=UtDgaa6gsOZvsTtjqMFdV91XFGy-DjoCVcn1ZSYOjVY&x_d=z8jqC7KwlcEPSuv247ZiwTeiEd97tYXSUr9JUc9fibk&x_t=1615390200&bookmark=41594_21' === $url) {
                return new MockResponse($quizzesListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $quizListPage = $client->quizzesList(513982, '41594_21');

        $this->assertSame(
            'https://learn.petersons.com/d2l/api/le/1.53/513982/quizzes/?bookmark=41594_21',
            $quizListPage->getNextUrl()
        );

        $quizzes = $quizListPage->getObjects();

        $this->assertCount(2, $quizzes);

        $this->assertSame(41575, $quizzes[0]->getId());
        $this->assertSame('Diagnostic Test - Arithmetic Reasoning', $quizzes[0]->getName());
        $this->assertTrue($quizzes[0]->isActive());

        $this->assertSame(41576, $quizzes[1]->getId());
        $this->assertSame('Diagnostic Test - Word Knowledge', $quizzes[1]->getName());
        $this->assertTrue($quizzes[1]->isActive());
    }

    public function testGetQuizzesForAnOrganizationUnit(): void
    {
        $this->freezeTime();

        $quizzesListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'quizzes_list.json');
        $callback = function (string $method, string $url, array $options) use ($quizzesListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/513982/quizzes/?x_a=baz&x_b=foo&x_c=UtDgaa6gsOZvsTtjqMFdV91XFGy-DjoCVcn1ZSYOjVY&x_d=z8jqC7KwlcEPSuv247ZiwTeiEd97tYXSUr9JUc9fibk&x_t=1615390200&bookmark=' === $url) {
                return new MockResponse($quizzesListJsonResponse);
            }

            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/513982/quizzes/?x_a=baz&x_b=foo&x_c=UtDgaa6gsOZvsTtjqMFdV91XFGy-DjoCVcn1ZSYOjVY&x_d=z8jqC7KwlcEPSuv247ZiwTeiEd97tYXSUr9JUc9fibk&x_t=1615390200&bookmark=41594_21' === $url) {
                return new MockResponse(json_encode(['Next' => null, 'Objects' => [['QuizId' => 123, 'Name' => 'foo', 'IsActive' => false]]]));
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $quizzes = $client->getQuizzesForAnOrganizationUnit(513982);

        $this->assertCount(3, $quizzes);

        $this->assertSame(41575, $quizzes[0]->getId());
        $this->assertSame('Diagnostic Test - Arithmetic Reasoning', $quizzes[0]->getName());
        $this->assertTrue($quizzes[0]->isActive());

        $this->assertSame(41576, $quizzes[1]->getId());
        $this->assertSame('Diagnostic Test - Word Knowledge', $quizzes[1]->getName());
        $this->assertTrue($quizzes[1]->isActive());

        $this->assertSame(123, $quizzes[2]->getId());
        $this->assertSame('foo', $quizzes[2]->getName());
        $this->assertFalse($quizzes[2]->isActive());
    }

    public function testQuizQuestionsListWithoutBookmark(): void
    {
        $this->freezeTime();

        $quizQuestionsListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'quiz_questions_list.json');
        $callback = function (string $method, string $url, array $options) use ($quizQuestionsListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/513982/quizzes/41575/questions/?x_a=baz&x_b=foo&x_c=f8ys7g-H2Q9dUZ2NT60GPfmREu9isbCwFNO1Toums_8&x_d=5pz9l1tbVuKMS_74bNtUpXpeHF58Mf2MI2kvG7hY_T8&x_t=1615390200&bookmark=' === $url) {
                return new MockResponse($quizQuestionsListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $quizQuestionListPage = $client->quizQuestionsList(513982, 41575);

        $this->assertSame(
            'https://learn.petersons.com/d2l/api/le/1.53/513982/quizzes/41575/questions/?bookmark=3077018',
            $quizQuestionListPage->getNextUrl()
        );

        $questions = $quizQuestionListPage->getObjects();

        $this->assertCount(2, $questions);

        $this->assertSame(3076999, $questions[0]->getId());
        $this->assertSame(1, $questions[0]->getType()->type());
        $this->assertSame('ASVAB-D1-AR-Q1', $questions[0]->getName());
        $this->assertSame(
            'A man owns 50 shares of stock worth $30 each. The corporation declared a dividend of 6% payable in stock. How many shares did he then own?',
            $questions[0]->getText()->getText()
        );
        $this->assertSame(
            'A man owns 50 shares of stock worth $30 each. The corporation declared a dividend of 6% payable in stock. How many shares did he then own?',
            $questions[0]->getText()->getHtml()
        );
        $this->assertSame(1.0, $questions[0]->getPoints());
        $this->assertSame(1, $questions[0]->getDifficulty());
        $this->assertFalse($questions[0]->isBonus());
        $this->assertFalse($questions[0]->isMandatory());
        $this->assertNull($questions[0]->getHint());
        $this->assertSame(
            'The correct answer is B. 50 shares x $30 = $1500. 6% payable stock => it means 6% of the total value of 50 shares => 1500 x 6% = 90. With $90, the man can buy 3 more shares, so the total he owns then are 53 shares.',
            $questions[0]->getFeedback()->getText()
        );
        $this->assertSame(
            '<p><strong>The correct answer is B. </strong>50 shares x $30 = $1500. 6% payable stock =&gt; it means 6% of the total value of 50 shares =&gt; 1500 x 6% = 90. With $90, the man can buy 3 more shares, so the total he owns then are 53 shares.</p>',
            $questions[0]->getFeedback()->getHtml()
        );
        $this->assertSame('2021-04-12T22:09:47+00:00', $questions[0]->getLastModifiedAt()->toAtomString());
        $this->assertNull($questions[0]->getLastModifiedBy());
        $this->assertSame(0, $questions[0]->getSectionId());
        $this->assertSame(71207, $questions[0]->getTemplateId());
        $this->assertSame(71218, $questions[0]->getTemplateVersionId());

        $this->assertSame(3077000, $questions[1]->getId());
        $this->assertSame(1, $questions[1]->getType()->type());
        $this->assertSame('ASVAB-D1-AR-Q21', $questions[1]->getName());
        $this->assertSame(
            'A man takes out a $5,000 life insurance policy at a yearly rate of $29.62 per $1,000. What is the yearly premium?',
            $questions[1]->getText()->getText()
        );
        $this->assertSame(
            'A man takes out a $5,000 life insurance policy at a yearly rate of $29.62 per $1,000. What is the yearly premium?',
            $questions[1]->getText()->getHtml()
        );
        $this->assertSame(1.0, $questions[1]->getPoints());
        $this->assertSame(1, $questions[1]->getDifficulty());
        $this->assertFalse($questions[1]->isBonus());
        $this->assertFalse($questions[1]->isMandatory());
        $this->assertNull($questions[1]->getHint());
        $this->assertSame(
            'The correct answer is D. $29.62 × 5 = $148.10 ',
            $questions[1]->getFeedback()->getText()
        );
        $this->assertSame(
            '<p><strong>The correct answer is D. $29.62 &#215; 5 = $148.10&#160;</strong></p>',
            $questions[1]->getFeedback()->getHtml()
        );
        $this->assertSame('2021-04-12T22:09:47+00:00', $questions[1]->getLastModifiedAt()->toAtomString());
        $this->assertNull($questions[1]->getLastModifiedBy());
        $this->assertSame(0, $questions[1]->getSectionId());
        $this->assertSame(71227, $questions[1]->getTemplateId());
        $this->assertSame(110378, $questions[1]->getTemplateVersionId());
    }

    public function testQuizQuestionsListWithBookmark(): void
    {
        $this->freezeTime();

        $quizQuestionsListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'quiz_questions_list.json');
        $callback = function (string $method, string $url, array $options) use ($quizQuestionsListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/513982/quizzes/41575/questions/?x_a=baz&x_b=foo&x_c=f8ys7g-H2Q9dUZ2NT60GPfmREu9isbCwFNO1Toums_8&x_d=5pz9l1tbVuKMS_74bNtUpXpeHF58Mf2MI2kvG7hY_T8&x_t=1615390200&bookmark=3077018' === $url) {
                return new MockResponse($quizQuestionsListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $quizQuestionListPage = $client->quizQuestionsList(513982, 41575, '3077018');

        $this->assertSame(
            'https://learn.petersons.com/d2l/api/le/1.53/513982/quizzes/41575/questions/?bookmark=3077018',
            $quizQuestionListPage->getNextUrl()
        );

        $questions = $quizQuestionListPage->getObjects();

        $this->assertCount(2, $questions);

        $this->assertSame(3076999, $questions[0]->getId());
        $this->assertSame(1, $questions[0]->getType()->type());
        $this->assertSame('ASVAB-D1-AR-Q1', $questions[0]->getName());
        $this->assertSame(
            'A man owns 50 shares of stock worth $30 each. The corporation declared a dividend of 6% payable in stock. How many shares did he then own?',
            $questions[0]->getText()->getText()
        );
        $this->assertSame(
            'A man owns 50 shares of stock worth $30 each. The corporation declared a dividend of 6% payable in stock. How many shares did he then own?',
            $questions[0]->getText()->getHtml()
        );
        $this->assertSame(1.0, $questions[0]->getPoints());
        $this->assertSame(1, $questions[0]->getDifficulty());
        $this->assertFalse($questions[0]->isBonus());
        $this->assertFalse($questions[0]->isMandatory());
        $this->assertNull($questions[0]->getHint());
        $this->assertSame(
            'The correct answer is B. 50 shares x $30 = $1500. 6% payable stock => it means 6% of the total value of 50 shares => 1500 x 6% = 90. With $90, the man can buy 3 more shares, so the total he owns then are 53 shares.',
            $questions[0]->getFeedback()->getText()
        );
        $this->assertSame(
            '<p><strong>The correct answer is B. </strong>50 shares x $30 = $1500. 6% payable stock =&gt; it means 6% of the total value of 50 shares =&gt; 1500 x 6% = 90. With $90, the man can buy 3 more shares, so the total he owns then are 53 shares.</p>',
            $questions[0]->getFeedback()->getHtml()
        );
        $this->assertSame('2021-04-12T22:09:47+00:00', $questions[0]->getLastModifiedAt()->toAtomString());
        $this->assertNull($questions[0]->getLastModifiedBy());
        $this->assertSame(0, $questions[0]->getSectionId());
        $this->assertSame(71207, $questions[0]->getTemplateId());
        $this->assertSame(71218, $questions[0]->getTemplateVersionId());

        $this->assertSame(3077000, $questions[1]->getId());
        $this->assertSame(1, $questions[1]->getType()->type());
        $this->assertSame('ASVAB-D1-AR-Q21', $questions[1]->getName());
        $this->assertSame(
            'A man takes out a $5,000 life insurance policy at a yearly rate of $29.62 per $1,000. What is the yearly premium?',
            $questions[1]->getText()->getText()
        );
        $this->assertSame(
            'A man takes out a $5,000 life insurance policy at a yearly rate of $29.62 per $1,000. What is the yearly premium?',
            $questions[1]->getText()->getHtml()
        );
        $this->assertSame(1.0, $questions[1]->getPoints());
        $this->assertSame(1, $questions[1]->getDifficulty());
        $this->assertFalse($questions[1]->isBonus());
        $this->assertFalse($questions[1]->isMandatory());
        $this->assertNull($questions[1]->getHint());
        $this->assertSame(
            'The correct answer is D. $29.62 × 5 = $148.10 ',
            $questions[1]->getFeedback()->getText()
        );
        $this->assertSame(
            '<p><strong>The correct answer is D. $29.62 &#215; 5 = $148.10&#160;</strong></p>',
            $questions[1]->getFeedback()->getHtml()
        );
        $this->assertSame('2021-04-12T22:09:47+00:00', $questions[1]->getLastModifiedAt()->toAtomString());
        $this->assertNull($questions[1]->getLastModifiedBy());
        $this->assertSame(0, $questions[1]->getSectionId());
        $this->assertSame(71227, $questions[1]->getTemplateId());
        $this->assertSame(110378, $questions[1]->getTemplateVersionId());
    }

    public function testGetQuizQuestionsForAQuiz(): void
    {
        $this->freezeTime();

        $quizQuestionsListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'quiz_questions_list.json');
        $onlyOneQuizQuestionOnTheListResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'only_one_quiz_question_on_the_list.json');
        $callback = function (string $method, string $url, array $options) use ($quizQuestionsListJsonResponse, $onlyOneQuizQuestionOnTheListResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/513982/quizzes/41575/questions/?x_a=baz&x_b=foo&x_c=f8ys7g-H2Q9dUZ2NT60GPfmREu9isbCwFNO1Toums_8&x_d=5pz9l1tbVuKMS_74bNtUpXpeHF58Mf2MI2kvG7hY_T8&x_t=1615390200&bookmark=' === $url) {
                return new MockResponse($quizQuestionsListJsonResponse);
            }

            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/513982/quizzes/41575/questions/?x_a=baz&x_b=foo&x_c=f8ys7g-H2Q9dUZ2NT60GPfmREu9isbCwFNO1Toums_8&x_d=5pz9l1tbVuKMS_74bNtUpXpeHF58Mf2MI2kvG7hY_T8&x_t=1615390200&bookmark=3077018' === $url) {
                return new MockResponse($onlyOneQuizQuestionOnTheListResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $questions = $client->getQuizQuestionsForAQuiz(513982, 41575);

        $this->assertCount(3, $questions);

        $this->assertSame(3076999, $questions[0]->getId());
        $this->assertSame(1, $questions[0]->getType()->type());
        $this->assertSame('ASVAB-D1-AR-Q1', $questions[0]->getName());
        $this->assertSame(
            'A man owns 50 shares of stock worth $30 each. The corporation declared a dividend of 6% payable in stock. How many shares did he then own?',
            $questions[0]->getText()->getText()
        );
        $this->assertSame(
            'A man owns 50 shares of stock worth $30 each. The corporation declared a dividend of 6% payable in stock. How many shares did he then own?',
            $questions[0]->getText()->getHtml()
        );
        $this->assertSame(1.0, $questions[0]->getPoints());
        $this->assertSame(1, $questions[0]->getDifficulty());
        $this->assertFalse($questions[0]->isBonus());
        $this->assertFalse($questions[0]->isMandatory());
        $this->assertNull($questions[0]->getHint());
        $this->assertSame(
            'The correct answer is B. 50 shares x $30 = $1500. 6% payable stock => it means 6% of the total value of 50 shares => 1500 x 6% = 90. With $90, the man can buy 3 more shares, so the total he owns then are 53 shares.',
            $questions[0]->getFeedback()->getText()
        );
        $this->assertSame(
            '<p><strong>The correct answer is B. </strong>50 shares x $30 = $1500. 6% payable stock =&gt; it means 6% of the total value of 50 shares =&gt; 1500 x 6% = 90. With $90, the man can buy 3 more shares, so the total he owns then are 53 shares.</p>',
            $questions[0]->getFeedback()->getHtml()
        );
        $this->assertSame('2021-04-12T22:09:47+00:00', $questions[0]->getLastModifiedAt()->toAtomString());
        $this->assertNull($questions[0]->getLastModifiedBy());
        $this->assertSame(0, $questions[0]->getSectionId());
        $this->assertSame(71207, $questions[0]->getTemplateId());
        $this->assertSame(71218, $questions[0]->getTemplateVersionId());

        $this->assertSame(3077000, $questions[1]->getId());
        $this->assertSame(1, $questions[1]->getType()->type());
        $this->assertSame('ASVAB-D1-AR-Q21', $questions[1]->getName());
        $this->assertSame(
            'A man takes out a $5,000 life insurance policy at a yearly rate of $29.62 per $1,000. What is the yearly premium?',
            $questions[1]->getText()->getText()
        );
        $this->assertSame(
            'A man takes out a $5,000 life insurance policy at a yearly rate of $29.62 per $1,000. What is the yearly premium?',
            $questions[1]->getText()->getHtml()
        );
        $this->assertSame(1.0, $questions[1]->getPoints());
        $this->assertSame(1, $questions[1]->getDifficulty());
        $this->assertFalse($questions[1]->isBonus());
        $this->assertFalse($questions[1]->isMandatory());
        $this->assertNull($questions[1]->getHint());
        $this->assertSame(
            'The correct answer is D. $29.62 × 5 = $148.10 ',
            $questions[1]->getFeedback()->getText()
        );
        $this->assertSame(
            '<p><strong>The correct answer is D. $29.62 &#215; 5 = $148.10&#160;</strong></p>',
            $questions[1]->getFeedback()->getHtml()
        );
        $this->assertSame('2021-04-12T22:09:47+00:00', $questions[1]->getLastModifiedAt()->toAtomString());
        $this->assertNull($questions[1]->getLastModifiedBy());
        $this->assertSame(0, $questions[1]->getSectionId());
        $this->assertSame(71227, $questions[1]->getTemplateId());
        $this->assertSame(110378, $questions[1]->getTemplateVersionId());

        $this->assertSame(3076999, $questions[2]->getId());
        $this->assertSame(1, $questions[2]->getType()->type());
        $this->assertSame('ASVAB-D1-AR-Q1', $questions[2]->getName());
        $this->assertSame(
            'A man owns 50 shares of stock worth $30 each. The corporation declared a dividend of 6% payable in stock. How many shares did he then own?',
            $questions[2]->getText()->getText()
        );
        $this->assertSame(
            'A man owns 50 shares of stock worth $30 each. The corporation declared a dividend of 6% payable in stock. How many shares did he then own?',
            $questions[2]->getText()->getHtml()
        );
        $this->assertSame(1.0, $questions[2]->getPoints());
        $this->assertSame(1, $questions[2]->getDifficulty());
        $this->assertFalse($questions[2]->isBonus());
        $this->assertFalse($questions[2]->isMandatory());
        $this->assertNull($questions[2]->getHint());
        $this->assertSame(
            'The correct answer is B. 50 shares x $30 = $1500. 6% payable stock => it means 6% of the total value of 50 shares => 1500 x 6% = 90. With $90, the man can buy 3 more shares, so the total he owns then are 53 shares.',
            $questions[2]->getFeedback()->getText()
        );
        $this->assertSame(
            '<p><strong>The correct answer is B. </strong>50 shares x $30 = $1500. 6% payable stock =&gt; it means 6% of the total value of 50 shares =&gt; 1500 x 6% = 90. With $90, the man can buy 3 more shares, so the total he owns then are 53 shares.</p>',
            $questions[2]->getFeedback()->getHtml()
        );
        $this->assertSame('2021-04-12T22:09:47+00:00', $questions[2]->getLastModifiedAt()->toAtomString());
        $this->assertNull($questions[2]->getLastModifiedBy());
        $this->assertSame(0, $questions[2]->getSectionId());
        $this->assertSame(71207, $questions[2]->getTemplateId());
        $this->assertSame(71218, $questions[2]->getTemplateVersionId());
    }

    public function testGetEnrolledUsersForAnOrganizationUnit(): void
    {
        $this->freezeTime();

        $quizzesListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'enrolled_users_in_org_unit_list.json');
        $callback = function (string $method, string $url, array $options) use ($quizzesListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/enrollments/orgUnits/513982/users/?x_a=baz&x_b=foo&x_c=yYq9JWRbdT05hqqEOVRZkzLmfsVM_kV61mDfZNZ26MY&x_d=NHki9J6k_RWI2B6cxkzswJJIqWzq9VI1Q_f1Yrgdchs&x_t=1615390200' === $url) {
                return new MockResponse($quizzesListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $enrolledUsers = $client->getEnrolledUsersForAnOrganizationUnit(513982);

        $this->assertCount(2, $enrolledUsers);

        $this->assertSame('177', $enrolledUsers[0]->getUser()->getIdentifier());
        $this->assertSame('Jason Varcoe', $enrolledUsers[0]->getUser()->getDisplayName());
        $this->assertSame('jason.varcoe@petersons.com', $enrolledUsers[0]->getUser()->getEmailAddress());
        $this->assertSame('', $enrolledUsers[0]->getUser()->getOrgDefinedId());
        $this->assertNull($enrolledUsers[0]->getUser()->getProfileBadgeUrl());
        $this->assertSame('iC0BbxoH6e', $enrolledUsers[0]->getUser()->getProfileIdentifier());

        $this->assertSame(105, $enrolledUsers[0]->getRoleInfo()->getId());
        $this->assertNull($enrolledUsers[0]->getRoleInfo()->getCode());
        $this->assertSame('Super Admin (C)', $enrolledUsers[0]->getRoleInfo()->getName());

        $this->assertSame('85497', $enrolledUsers[1]->getUser()->getIdentifier());
        $this->assertSame('jennifer lagemann', $enrolledUsers[1]->getUser()->getDisplayName());
        $this->assertSame('jennifer.lagemann@petersons.com', $enrolledUsers[1]->getUser()->getEmailAddress());
        $this->assertNull($enrolledUsers[1]->getUser()->getOrgDefinedId());
        $this->assertNull($enrolledUsers[1]->getUser()->getProfileBadgeUrl());
        $this->assertSame('tv3s6NpAIW', $enrolledUsers[1]->getUser()->getProfileIdentifier());

        $this->assertSame(106, $enrolledUsers[1]->getRoleInfo()->getId());
        $this->assertNull($enrolledUsers[1]->getRoleInfo()->getCode());
        $this->assertSame('Administrator (C)', $enrolledUsers[1]->getRoleInfo()->getName());
    }

    public function testGetOrganizationInfo(): void
    {
        $this->freezeTime();

        $organizationInfoJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'organization_info.json');
        $callback = function (string $method, string $url, array $options) use ($organizationInfoJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/organization/info?x_a=baz&x_b=foo&x_c=L3ABx3PSBWp2-V27BV6QYn7G2x_QUMfd4BVjvYwgdfA&x_d=MBWOUehtCxZgZvfHgRGgVVhBDin-NxHzgSPJbXi7Xb4&x_t=1615390200' === $url) {
                return new MockResponse($organizationInfoJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $organizationInfo = $client->getOrganizationInfo();

        $this->assertSame('6606', $organizationInfo->getIdentifier());
        $this->assertSame('Petersons LLC', $organizationInfo->getName());
        $this->assertSame('America/Denver', $organizationInfo->getTimeZone());
    }

    public function testGetOrganizationStructure(): void
    {
        $this->freezeTime();

        $organizationStructureJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'organization_structure.json');
        $callback = function (string $method, string $url, array $options) use ($organizationStructureJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/orgstructure/?x_a=baz&x_b=foo&x_c=qHJ8PfUoGiQ5sd2PRQEyEDAMnRexnw7d9flGGut1tx4&x_d=fpVk4xKbau8yYwKe-G3-YQqgGFnmvd--qeW6xtXkAHM&x_t=1615390200' === $url) {
                return new MockResponse($organizationStructureJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $organizationUnits = $client->getOrganizationStructure();

        $this->assertCount(2, $organizationUnits);

        $this->assertSame('6606', $organizationUnits[0]->getIdentifier());
        $this->assertSame('Petersons LLC', $organizationUnits[0]->getName());
        $this->assertSame(1, $organizationUnits[0]->getOrganizationUnitTypeInfo()->getId());
        $this->assertSame('Organization', $organizationUnits[0]->getOrganizationUnitTypeInfo()->getCode());
        $this->assertSame('Organization', $organizationUnits[0]->getOrganizationUnitTypeInfo()->getName());
        $this->assertSame('/content/', $organizationUnits[0]->getPath());
        $this->assertSame('PETERSONSLLC', $organizationUnits[0]->getCode());

        $this->assertSame('6645', $organizationUnits[1]->getIdentifier());
        $this->assertSame('Latest Content Experience Sample ', $organizationUnits[1]->getName());
        $this->assertSame(3, $organizationUnits[1]->getOrganizationUnitTypeInfo()->getId());
        $this->assertSame('Course Offering', $organizationUnits[1]->getOrganizationUnitTypeInfo()->getCode());
        $this->assertSame('Course Offering', $organizationUnits[1]->getOrganizationUnitTypeInfo()->getName());
        $this->assertSame('/content/enforced/6645-Lessons_sb/', $organizationUnits[1]->getPath());
        $this->assertSame('Lessons_sb_d2l-Sample', $organizationUnits[1]->getCode());
    }

    public function testGetDescendentUnitsForAnOrganizationUnit(): void
    {
        $this->freezeTime();

        $descendentUnitsForAnOrganizationUnitJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'organization_structure_descendants_paged.json');
        $callback = function (string $method, string $url, array $options) use ($descendentUnitsForAnOrganizationUnitJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/orgstructure/6606/descendants/paged/?x_a=baz&x_b=foo&x_c=1TjQQ3L9scCBYbCs7L5w0QGo6N3AbjOA48tdOx_BKb4&x_d=1BCza4s48xpXSCJaj-U0x1_K7v_aoaPzEF-xMPbUsFA&x_t=1615390200&bookmark=' === $url) {
                return new MockResponse($descendentUnitsForAnOrganizationUnitJsonResponse);
            }

            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/orgstructure/6606/descendants/paged/?x_a=baz&x_b=foo&x_c=1TjQQ3L9scCBYbCs7L5w0QGo6N3AbjOA48tdOx_BKb4&x_d=1BCza4s48xpXSCJaj-U0x1_K7v_aoaPzEF-xMPbUsFA&x_t=1615390200&bookmark=6606_8069' === $url) {
                return new MockResponse(json_encode(['PagingInfo' => ['Bookmark' => null, 'HasMoreItems' => false], 'Items' => []]));
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $organizationUnits = $client->getDescendentUnitsForAnOrganizationUnit(6606);

        $this->assertCount(2, $organizationUnits);

        $this->assertSame('6614', $organizationUnits[0]->getIdentifier());
        $this->assertSame('Brightspace Bulk tools container', $organizationUnits[0]->getName());
        $this->assertSame('ct_bulktools_d2l', $organizationUnits[0]->getCode());
        $this->assertSame(2, $organizationUnits[0]->getOrganizationUnitTypeInfo()->getId());
        $this->assertSame('Course Template', $organizationUnits[0]->getOrganizationUnitTypeInfo()->getCode());
        $this->assertSame('Course Template', $organizationUnits[0]->getOrganizationUnitTypeInfo()->getName());

        $this->assertSame('8069', $organizationUnits[1]->getIdentifier());
        $this->assertSame('CLEP Introductory Sociology Exam Prep', $organizationUnits[1]->getName());
        $this->assertSame('Master_539', $organizationUnits[1]->getCode());
        $this->assertSame(3, $organizationUnits[1]->getOrganizationUnitTypeInfo()->getId());
        $this->assertSame('Course Offering', $organizationUnits[1]->getOrganizationUnitTypeInfo()->getCode());
        $this->assertSame('Course Offering', $organizationUnits[1]->getOrganizationUnitTypeInfo()->getName());
    }

    public function testGetGradeCategoriesForAnOrganizationUnit(): void
    {
        $this->freezeTime();

        $gradeCategoriesForOrgUnitListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'grade_categories_for_org_unit_list.json');
        $callback = function (string $method, string $url, array $options) use ($gradeCategoriesForOrgUnitListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/513982/grades/categories/?x_a=baz&x_b=foo&x_c=mAXgTxsicUGs7EzejqaoZiyS077d9NQZI1Q6MHgztfM&x_d=5HutUQpPxO6aNNEbMO1-DHbJNbKUk4WLhiwjYQI72k4&x_t=1615390200' === $url) {
                return new MockResponse($gradeCategoriesForOrgUnitListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $gradeCategories = $client->getGradeCategoriesForAnOrganizationUnit(513982);

        $this->assertCount(2, $gradeCategories);

        $this->assertSame(44371, $gradeCategories[0]->getId());
        $this->assertSame('Diagnostic Test', $gradeCategories[0]->getGradeObjectCategoryData()->getName());
        $this->assertSame('', $gradeCategories[0]->getGradeObjectCategoryData()->getShortName());
        $this->assertFalse($gradeCategories[0]->getGradeObjectCategoryData()->canExceedMax());
        $this->assertTrue($gradeCategories[0]->getGradeObjectCategoryData()->excludeFromFinalGrade());
        $this->assertNull($gradeCategories[0]->getGradeObjectCategoryData()->getStartDate());
        $this->assertNull($gradeCategories[0]->getGradeObjectCategoryData()->getEndDate());
        $this->assertNull($gradeCategories[0]->getGradeObjectCategoryData()->getWeight());
        $this->assertSame(10.0, $gradeCategories[0]->getGradeObjectCategoryData()->getMaxPoints());
        $this->assertFalse($gradeCategories[0]->getGradeObjectCategoryData()->autoPoints());
        $this->assertNull($gradeCategories[0]->getGradeObjectCategoryData()->getWeightDistributionType());
        $this->assertSame(0, $gradeCategories[0]->getGradeObjectCategoryData()->getNumberOfHighestToDrop());
        $this->assertSame(0, $gradeCategories[0]->getGradeObjectCategoryData()->getNumberOfLowestToDrop());
        $this->assertCount(2, $gradeCategories[0]->getGrades());
        $this->assertSame('Diagnostic Test - Arithmetic Reasoning', $gradeCategories[0]->getGrades()[0]->getName());
        $this->assertSame('Diagnostic Test - Mathematics Knowledge', $gradeCategories[0]->getGrades()[1]->getName());

        $this->assertSame(44376, $gradeCategories[1]->getId());
        $this->assertSame('Post Assessment', $gradeCategories[1]->getGradeObjectCategoryData()->getName());
        $this->assertSame('', $gradeCategories[1]->getGradeObjectCategoryData()->getShortName());
        $this->assertFalse($gradeCategories[1]->getGradeObjectCategoryData()->canExceedMax());
        $this->assertTrue($gradeCategories[1]->getGradeObjectCategoryData()->excludeFromFinalGrade());
        $this->assertNull($gradeCategories[1]->getGradeObjectCategoryData()->getStartDate());
        $this->assertNull($gradeCategories[1]->getGradeObjectCategoryData()->getEndDate());
        $this->assertNull($gradeCategories[1]->getGradeObjectCategoryData()->getWeight());
        $this->assertSame(10.0, $gradeCategories[1]->getGradeObjectCategoryData()->getMaxPoints());
        $this->assertFalse($gradeCategories[1]->getGradeObjectCategoryData()->autoPoints());
        $this->assertNull($gradeCategories[1]->getGradeObjectCategoryData()->getWeightDistributionType());
        $this->assertSame(0, $gradeCategories[1]->getGradeObjectCategoryData()->getNumberOfHighestToDrop());
        $this->assertSame(0, $gradeCategories[1]->getGradeObjectCategoryData()->getNumberOfLowestToDrop());
        $this->assertCount(2, $gradeCategories[1]->getGrades());
        $this->assertSame('Post Assessment - Arithmetic Reasoning', $gradeCategories[1]->getGrades()[0]->getName());
        $this->assertSame('Post Assessment - Mathematics Knowledge', $gradeCategories[1]->getGrades()[1]->getName());
    }

    public function testGetDataExportList(): void
    {
        $this->freezeTime();

        $dataExportListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'data_export_list.json');
        $callback = function (string $method, string $url, array $options) use ($dataExportListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/dataExport/list?x_a=baz&x_b=foo&x_c=lIsaaFf2Ayh3WUld9tjRbfzwa4V85cyFXog5rITZh-Q&x_d=pJVJwBrntyMMwXBUoXt66juDo77-XfmBF4m84yzF7ks&x_t=1615390200' === $url) {
                return new MockResponse($dataExportListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $dataExportList = $client->getDataExportList();

        $this->assertCount(2, $dataExportList);

        $this->assertSame('ff842aae-84d0-4e39-9db4-3ae51d36bb0e', $dataExportList[0]->getId());
        $this->assertSame('Final Grades', $dataExportList[0]->getName());
        $this->assertSame('The Final Grades dataset returns final grades for all learners in course offerings.', $dataExportList[0]->getDescription());
        $this->assertSame('AdvancedDataSets', $dataExportList[0]->getCategory());
        $this->assertCount(2, $dataExportList[0]->getFilters());
        $this->assertSame('startDate', $dataExportList[0]->getFilters()[0]->getName());
        $this->assertSame('1', (string) $dataExportList[0]->getFilters()[0]->getType());
        $this->assertNull($dataExportList[0]->getFilters()[0]->getDescription());
        $this->assertNull($dataExportList[0]->getFilters()[0]->getDefaultValue());
        $this->assertSame('endDate', $dataExportList[0]->getFilters()[1]->getName());
        $this->assertSame('1', (string) $dataExportList[0]->getFilters()[1]->getType());
        $this->assertNull($dataExportList[0]->getFilters()[1]->getDescription());
        $this->assertNull($dataExportList[0]->getFilters()[1]->getDefaultValue());

        $this->assertSame('c1bf7603-669f-4bef-8cf4-651b914c4678', $dataExportList[1]->getId());
        $this->assertSame('Enrollments and Withdrawals', $dataExportList[1]->getName());
        $this->assertSame('An Enrollments and Withdrawals dataset consisting of Org Unit, User and Role attributes, along with enrollment status for a given date range.', $dataExportList[1]->getDescription());
        $this->assertSame('AdvancedDataSets', $dataExportList[1]->getCategory());
        $this->assertCount(2, $dataExportList[1]->getFilters());
        $this->assertSame('startDate', $dataExportList[1]->getFilters()[0]->getName());
        $this->assertSame('1', (string) $dataExportList[1]->getFilters()[0]->getType());
        $this->assertNull($dataExportList[0]->getFilters()[1]->getDescription());
        $this->assertNull($dataExportList[0]->getFilters()[1]->getDefaultValue());
        $this->assertSame('endDate', $dataExportList[1]->getFilters()[1]->getName());
        $this->assertSame('1', (string) $dataExportList[1]->getFilters()[1]->getType());
        $this->assertNull($dataExportList[1]->getFilters()[1]->getDescription());
        $this->assertNull($dataExportList[1]->getFilters()[1]->getDefaultValue());
    }

    public function testCreateDataExport(): void
    {
        $this->freezeTime();

        $createDataExportJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'create_data_export.json');
        $callback = function (string $method, string $url, array $options) use ($createDataExportJsonResponse): MockResponse {
            if (
                'POST' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/api/lp/1.30/dataExport/create?x_a=baz&x_b=foo&x_c=kP3oy0jc1zruozBCtz7lUXz1kk-qRXhP_rlfN4Rep-k&x_d=jaw-ZQt4VfSz8Ao2cBgG7B5UCn_nJZOzSfLBqEclrkM&x_t=1615390200' === $url
                &&
                '{"DataSetId":"ff842aae-84d0-4e39-9db4-3ae51d36bb0e","Filters":[{"Name":"startDate","Value":"2016-01-28T19:39:19.000Z"},{"Name":"endDate","Value":"2016-01-29T19:39:19.000Z"}]}' === $options['body']
            ) {
                return new MockResponse($createDataExportJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $exportJobData = $client->createDataExport(
            new CreateExportJobData(
                'ff842aae-84d0-4e39-9db4-3ae51d36bb0e',
                [
                    ExportJobFilter::startDate(CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2016-01-28 19:39:19')),
                    ExportJobFilter::endDate(CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2016-01-29 19:39:19')),
                ]
            )
        );

        $this->assertSame('a35e9829-9788-43ed-962e-c8c8f361f76f', $exportJobData->getExportJobId());
        $this->assertSame('ff842aae-84d0-4e39-9db4-3ae51d36bb0e', $exportJobData->getDataSetId());
        $this->assertSame('Final Grades', $exportJobData->getName());
        $this->assertSame('2021-05-24T16:30:42+00:00', $exportJobData->getSubmitDate()->toAtomString());
        $this->assertSame(0, $exportJobData->getStatus()->getStatus());
        $this->assertSame('AdvancedDataSets', $exportJobData->getCategory());
    }

    private function getClient(MockHttpClient $mockHttpClient): SymfonyHttpClient
    {
        return new SymfonyHttpClient(
            ScopingHttpClient::forBaseUri(
                $mockHttpClient,
                'https://petersonstest.brightspace.com',
            ),
            new AuthenticatedUriFactory(
                'https://petersonstest.brightspace.com',
                'baz',
                'qux',
                'foo',
                'bar',
            ),
            'quux',
            'quuz',
            'corge',
            '1.30',
            '1.53',
        );
    }

    private function freezeTime(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2021-03-10 15:30:00'));
    }
}
