<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\CarbonImmutable;
use Petersons\D2L\AuthenticatedUriFactory;
use Petersons\D2L\DTO\ContentCompletions\ContentTopicCompletionUpdate;
use Petersons\D2L\DTO\ContentObject\ContentObject;
use Petersons\D2L\DTO\ContentObject\Module;
use Petersons\D2L\DTO\ContentObject\Topic;
use Petersons\D2L\DTO\DataExport\CreateExportJobData;
use Petersons\D2L\DTO\DataExport\ExportJobFilter;
use Petersons\D2L\DTO\Enrollment\CreateEnrollment;
use Petersons\D2L\DTO\Enrollment\CreateSectionEnrollment;
use Petersons\D2L\DTO\Grade\IncomingGradeValue;
use Petersons\D2L\DTO\Guid;
use Petersons\D2L\DTO\Quiz\FillInTheBlank;
use Petersons\D2L\DTO\Quiz\LongAnswer;
use Petersons\D2L\DTO\Quiz\MultipleChoiceAnswers;
use Petersons\D2L\DTO\Quiz\ShortAnswers;
use Petersons\D2L\DTO\Quiz\TrueFalse;
use Petersons\D2L\DTO\RichTextInput;
use Petersons\D2L\DTO\Section\Section;
use Petersons\D2L\DTO\User\CreateUser;
use Petersons\D2L\DTO\User\UpdateUser;
use Petersons\D2L\Enum\RichTextInputType;
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

    public function testFetchingUserById(): void
    {
        $this->freezeTime();

        $userJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'user_fetch_by_id_response.json');
        $callback = function (string $method, string $url, array $options) use ($userJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/3163?x_a=baz&x_b=foo&x_c=FpMfkzXcBy3gqB2smJhHzyQv6m8JlMVURMpFbtn5j0U&x_d=kZdNU7pg3RQR7GQ319kNTDCMJfybaa5KtjbqziiR9SM&x_t=1615390200' === $url) {
                return new MockResponse($userJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $user = $client->getUserById(3163);

        $this->assertSame(6606, $user->getOrgId());
        $this->assertSame(3163, $user->getUserId());
        $this->assertSame('Nicholas', $user->getFirstName());
        $this->assertNull($user->getMiddleName());
        $this->assertSame('Test', $user->getLastName());
        $this->assertSame('Nicholas.Test.2.1356', $user->getUsername());
        $this->assertSame('petersons_508833_0@email.fake', $user->getExternalEmail());
        $this->assertSame('2.508833', $user->getOrgDefinedId());
        $this->assertSame('Nicholas.Holland.2.1356', $user->getUniqueIdentifier());
        $this->assertTrue($user->isActive());
        $this->assertSame(
            CarbonImmutable::createFromFormat("Y-m-d\TH:i:s.v\Z", '2020-07-22T03:05:09.700Z')->toAtomString(),
            $user->getLastAccessedAt()->toAtomString()
        );
    }

    public function testFetchingUserByIdWhenD2LReturnsNotFoundResponse(): void
    {
        $this->freezeTime();

        $callback = function (string $method, string $url, array $options): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/3163?x_a=baz&x_b=foo&x_c=FpMfkzXcBy3gqB2smJhHzyQv6m8JlMVURMpFbtn5j0U&x_d=kZdNU7pg3RQR7GQ319kNTDCMJfybaa5KtjbqziiR9SM&x_t=1615390200' === $url) {
                return new MockResponse('', ['http_code' => 404]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 404 returned for "https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/3163?x_a=baz&x_b=foo&x_c=FpMfkzXcBy3gqB2smJhHzyQv6m8JlMVURMpFbtn5j0U&x_d=kZdNU7pg3RQR7GQ319kNTDCMJfybaa5KtjbqziiR9SM&x_t=1615390200".',
                404
            )
        );

        $client->getUserById(3163);
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
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&externalEmail=petersons_1296_0@email.fake' === $url) {
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
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&externalEmail=petersons_1296_0@email.fake' === $url) {
                return new MockResponse('', ['http_code' => 403]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 403 returned for "https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&externalEmail=petersons_1296_0@email.fake".',
                403
            )
        );

        $client->getUserByEmail('petersons_1296_0@email.fake');
    }

    public function testFetchingUserByEmailWhenD2LReturnsNotFoundResponse(): void
    {
        $this->freezeTime();

        $callback = function (string $method, string $url, array $options): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&externalEmail=petersons_1296_0@email.fake' === $url) {
                return new MockResponse('', ['http_code' => 404]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 404 returned for "https://petersonstest.brightspace.com/d2l/api/lp/1.30/users/?x_a=baz&x_b=foo&x_c=FGDZzyI8CiSOWa-c6hr_rcwj4fY58CFqzuvWiapEyQY&x_d=OjWXyQhHjYt2qgfJEDFezAIZfYpy9jyb3Jlzknywe7o&x_t=1615390200&externalEmail=petersons_1296_0@email.fake".',
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

    public function testGetQuizById(): void
    {
        $this->freezeTime();

        $quizJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'quiz_fetch_by_id_response.json');
        $callback = function (string $method, string $url, array $options) use ($quizJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/514893/quizzes/46673?x_a=baz&x_b=foo&x_c=zDeDx7bLby5mVCJ7AovrXKqvZq-pVtEysBW3exY-gzk&x_d=JuXn5lEot0_ON-AupXnHK8K5l2agmJxyv_P0l7TIxkY&x_t=1615390200' === $url) {
                return new MockResponse($quizJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $quiz = $client->getQuizById(514893, 46673);

        $this->assertSame(46673, $quiz->getId());
        $this->assertSame('Module 4 Prefixes Quiz', $quiz->getName());
        $this->assertTrue($quiz->isActive());
        $this->assertSame(5, $quiz->getSortOrder());
        $this->assertTrue($quiz->getAutoExportToGrades());
        $this->assertSame(50354, $quiz->getGradeItemId());
        $this->assertTrue($quiz->isAutoSetGraded());
        $this->assertSame('', $quiz->getInstructions()->getText()->getText());
        $this->assertSame('', $quiz->getInstructions()->getText()->getHtml());
        $this->assertFalse($quiz->getInstructions()->isDisplayed());
        $this->assertSame('', $quiz->getDescription()->getText()->getText());
        $this->assertSame('', $quiz->getDescription()->getText()->getHtml());
        $this->assertFalse($quiz->getDescription()->isDisplayed());
        $this->assertSame('2021-10-14 14:00:00', $quiz->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2021-11-04 22:00:00', $quiz->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2021-10-15 22:00:00', $quiz->getDueDate()->format('Y-m-d H:i:s'));
        $this->assertFalse($quiz->displayInCalendar());
        $this->assertTrue($quiz->getAttemptsAllowed()->isUnlimited());
        $this->assertNull($quiz->getAttemptsAllowed()->getNumberOfAttemptsAllowed());
        $this->assertSame(0, $quiz->getLateSubmissionInfo()->getLateSubmissionOption()->getOption());
        $this->assertNull($quiz->getLateSubmissionInfo()->getLateLimitMinutes());
        $this->assertFalse($quiz->getSubmissionTimeLimit()->isEnforced());
        $this->assertFalse($quiz->getSubmissionTimeLimit()->isShowClock());
        $this->assertSame(120, $quiz->getSubmissionTimeLimit()->getTimeLimitValue());
        $this->assertSame(5, $quiz->getSubmissionGracePeriod());
        $this->assertNull($quiz->getPassword());
        $this->assertSame('', $quiz->getHeader()->getText()->getText());
        $this->assertSame('', $quiz->getHeader()->getText()->getHtml());
        $this->assertFalse($quiz->getHeader()->isDisplayed());
        $this->assertSame('', $quiz->getFooter()->getText()->getText());
        $this->assertSame('', $quiz->getFooter()->getText()->getHtml());
        $this->assertFalse($quiz->getFooter()->isDisplayed());
        $this->assertFalse($quiz->allowHints());
        $this->assertFalse($quiz->disableRightClick());
        $this->assertFalse($quiz->disablePagerAndAlerts());
        $this->assertNull($quiz->getNotificationEmail());
        $this->assertSame(1, $quiz->getCalcTypeId()->getOption());
        $this->assertTrue($quiz->getRestrictIPAddressRange()->isEmpty());
        $this->assertNull($quiz->getCategoryId());
        $this->assertFalse($quiz->preventMovingBackwards());
        $this->assertFalse($quiz->shuffle());
        $this->assertSame(
            'https://ids.brightspace.com/activities/quiz/34907245-882D-4965-B3D6-0708A1D560F9-77531',
            $quiz->getActivityId()
        );
        $this->assertFalse($quiz->allowOnlyUsersWithSpecialAccess());
        $this->assertFalse($quiz->isRetakeIncorrectOnly());
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
        $this->assertSame(2, $quizzes[0]->getSortOrder());
        $this->assertTrue($quizzes[0]->getAutoExportToGrades());
        $this->assertSame(44372, $quizzes[0]->getGradeItemId());
        $this->assertTrue($quizzes[0]->isAutoSetGraded());
        $this->assertSame('', $quizzes[0]->getInstructions()->getText()->getText());
        $this->assertSame('', $quizzes[0]->getInstructions()->getText()->getHtml());
        $this->assertFalse($quizzes[0]->getInstructions()->isDisplayed());
        $this->assertStringContainsString("\r\nDiagnostic", $quizzes[0]->getDescription()->getText()->getText());
        $this->assertStringContainsString('<hr style="width: 100%', $quizzes[0]->getDescription()->getText()->getHtml());
        $this->assertTrue($quizzes[0]->getDescription()->isDisplayed());
        $this->assertNull($quizzes[0]->getStartDate());
        $this->assertNull($quizzes[0]->getEndDate());
        $this->assertNull($quizzes[0]->getDueDate());
        $this->assertFalse($quizzes[0]->displayInCalendar());
        $this->assertFalse($quizzes[0]->getAttemptsAllowed()->isUnlimited());
        $this->assertSame(1, $quizzes[0]->getAttemptsAllowed()->getNumberOfAttemptsAllowed());
        $this->assertSame(2, $quizzes[0]->getLateSubmissionInfo()->getLateSubmissionOption()->getOption());
        $this->assertSame(1, $quizzes[0]->getLateSubmissionInfo()->getLateLimitMinutes());
        $this->assertTrue($quizzes[0]->getSubmissionTimeLimit()->isEnforced());
        $this->assertTrue($quizzes[0]->getSubmissionTimeLimit()->isShowClock());
        $this->assertSame(36, $quizzes[0]->getSubmissionTimeLimit()->getTimeLimitValue());
        $this->assertSame(1, $quizzes[0]->getSubmissionGracePeriod());
        $this->assertNull($quizzes[0]->getPassword());
        $this->assertSame('', $quizzes[0]->getHeader()->getText()->getText());
        $this->assertSame('', $quizzes[0]->getHeader()->getText()->getHtml());
        $this->assertTrue($quizzes[0]->getHeader()->isDisplayed());
        $this->assertSame('', $quizzes[0]->getFooter()->getText()->getText());
        $this->assertSame('', $quizzes[0]->getFooter()->getText()->getHtml());
        $this->assertTrue($quizzes[0]->getFooter()->isDisplayed());
        $this->assertFalse($quizzes[0]->allowHints());
        $this->assertFalse($quizzes[0]->disableRightClick());
        $this->assertTrue($quizzes[0]->disablePagerAndAlerts());
        $this->assertNull($quizzes[0]->getNotificationEmail());
        $this->assertSame(4, $quizzes[0]->getCalcTypeId()->getOption());
        $this->assertTrue($quizzes[0]->getRestrictIPAddressRange()->isEmpty());
        $this->assertSame(376, $quizzes[0]->getCategoryId());
        $this->assertFalse($quizzes[0]->preventMovingBackwards());
        $this->assertFalse($quizzes[0]->shuffle());
        $this->assertSame(
            'https://ids.brightspace.com/activities/quiz/34907245-882D-4965-B3D6-0708A1D560F9-14513',
            $quizzes[0]->getActivityId()
        );
        $this->assertFalse($quizzes[0]->allowOnlyUsersWithSpecialAccess());
        $this->assertFalse($quizzes[0]->isRetakeIncorrectOnly());

        $this->assertSame(41576, $quizzes[1]->getId());
        $this->assertSame('Diagnostic Test - Word Knowledge', $quizzes[1]->getName());
        $this->assertTrue($quizzes[1]->isActive());
        $this->assertSame(3, $quizzes[1]->getSortOrder());
        $this->assertTrue($quizzes[1]->getAutoExportToGrades());
        $this->assertSame(44375, $quizzes[1]->getGradeItemId());
        $this->assertTrue($quizzes[1]->isAutoSetGraded());
        $this->assertSame('', $quizzes[1]->getInstructions()->getText()->getText());
        $this->assertSame('', $quizzes[1]->getInstructions()->getText()->getHtml());
        $this->assertFalse($quizzes[1]->getInstructions()->isDisplayed());
        $this->assertStringContainsString("\r\nDiagnostic", $quizzes[1]->getDescription()->getText()->getText());
        $this->assertStringContainsString('<hr style="width: 100%', $quizzes[1]->getDescription()->getText()->getHtml());
        $this->assertTrue($quizzes[1]->getDescription()->isDisplayed());
        $this->assertNull($quizzes[1]->getStartDate());
        $this->assertNull($quizzes[1]->getEndDate());
        $this->assertNull($quizzes[1]->getDueDate());
        $this->assertFalse($quizzes[1]->displayInCalendar());
        $this->assertFalse($quizzes[1]->getAttemptsAllowed()->isUnlimited());
        $this->assertSame(1, $quizzes[1]->getAttemptsAllowed()->getNumberOfAttemptsAllowed());
        $this->assertSame(2, $quizzes[1]->getLateSubmissionInfo()->getLateSubmissionOption()->getOption());
        $this->assertSame(1, $quizzes[1]->getLateSubmissionInfo()->getLateLimitMinutes());
        $this->assertTrue($quizzes[1]->getSubmissionTimeLimit()->isEnforced());
        $this->assertTrue($quizzes[1]->getSubmissionTimeLimit()->isShowClock());
        $this->assertSame(11, $quizzes[1]->getSubmissionTimeLimit()->getTimeLimitValue());
        $this->assertSame(1, $quizzes[1]->getSubmissionGracePeriod());
        $this->assertNull($quizzes[1]->getPassword());
        $this->assertSame('', $quizzes[1]->getHeader()->getText()->getText());
        $this->assertSame('', $quizzes[1]->getHeader()->getText()->getHtml());
        $this->assertTrue($quizzes[1]->getHeader()->isDisplayed());
        $this->assertSame('', $quizzes[1]->getFooter()->getText()->getText());
        $this->assertSame('', $quizzes[1]->getFooter()->getText()->getHtml());
        $this->assertTrue($quizzes[1]->getFooter()->isDisplayed());
        $this->assertFalse($quizzes[1]->allowHints());
        $this->assertFalse($quizzes[1]->disableRightClick());
        $this->assertTrue($quizzes[1]->disablePagerAndAlerts());
        $this->assertNull($quizzes[1]->getNotificationEmail());
        $this->assertSame(4, $quizzes[1]->getCalcTypeId()->getOption());
        $this->assertTrue($quizzes[1]->getRestrictIPAddressRange()->isEmpty());
        $this->assertSame(376, $quizzes[1]->getCategoryId());
        $this->assertFalse($quizzes[1]->preventMovingBackwards());
        $this->assertFalse($quizzes[1]->shuffle());
        $this->assertSame(
            'https://ids.brightspace.com/activities/quiz/34907245-882D-4965-B3D6-0708A1D560F9-14514',
            $quizzes[1]->getActivityId()
        );
        $this->assertFalse($quizzes[1]->allowOnlyUsersWithSpecialAccess());
        $this->assertFalse($quizzes[1]->isRetakeIncorrectOnly());
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
        $this->assertSame(2, $quizzes[0]->getSortOrder());
        $this->assertTrue($quizzes[0]->getAutoExportToGrades());
        $this->assertSame(44372, $quizzes[0]->getGradeItemId());
        $this->assertTrue($quizzes[0]->isAutoSetGraded());
        $this->assertSame('', $quizzes[0]->getInstructions()->getText()->getText());
        $this->assertSame('', $quizzes[0]->getInstructions()->getText()->getHtml());
        $this->assertFalse($quizzes[0]->getInstructions()->isDisplayed());
        $this->assertStringContainsString("\r\nDiagnostic", $quizzes[0]->getDescription()->getText()->getText());
        $this->assertStringContainsString('<hr style="width: 100%', $quizzes[0]->getDescription()->getText()->getHtml());
        $this->assertTrue($quizzes[0]->getDescription()->isDisplayed());
        $this->assertNull($quizzes[0]->getStartDate());
        $this->assertNull($quizzes[0]->getEndDate());
        $this->assertNull($quizzes[0]->getDueDate());
        $this->assertFalse($quizzes[0]->displayInCalendar());
        $this->assertFalse($quizzes[0]->getAttemptsAllowed()->isUnlimited());
        $this->assertSame(1, $quizzes[0]->getAttemptsAllowed()->getNumberOfAttemptsAllowed());
        $this->assertSame(2, $quizzes[0]->getLateSubmissionInfo()->getLateSubmissionOption()->getOption());
        $this->assertSame(1, $quizzes[0]->getLateSubmissionInfo()->getLateLimitMinutes());
        $this->assertTrue($quizzes[0]->getSubmissionTimeLimit()->isEnforced());
        $this->assertTrue($quizzes[0]->getSubmissionTimeLimit()->isShowClock());
        $this->assertSame(36, $quizzes[0]->getSubmissionTimeLimit()->getTimeLimitValue());
        $this->assertSame(1, $quizzes[0]->getSubmissionGracePeriod());
        $this->assertNull($quizzes[0]->getPassword());
        $this->assertSame('', $quizzes[0]->getHeader()->getText()->getText());
        $this->assertSame('', $quizzes[0]->getHeader()->getText()->getHtml());
        $this->assertTrue($quizzes[0]->getHeader()->isDisplayed());
        $this->assertSame('', $quizzes[0]->getFooter()->getText()->getText());
        $this->assertSame('', $quizzes[0]->getFooter()->getText()->getHtml());
        $this->assertTrue($quizzes[0]->getFooter()->isDisplayed());
        $this->assertFalse($quizzes[0]->allowHints());
        $this->assertFalse($quizzes[0]->disableRightClick());
        $this->assertTrue($quizzes[0]->disablePagerAndAlerts());
        $this->assertNull($quizzes[0]->getNotificationEmail());
        $this->assertSame(4, $quizzes[0]->getCalcTypeId()->getOption());
        $this->assertTrue($quizzes[0]->getRestrictIPAddressRange()->isEmpty());
        $this->assertSame(376, $quizzes[0]->getCategoryId());
        $this->assertFalse($quizzes[0]->preventMovingBackwards());
        $this->assertFalse($quizzes[0]->shuffle());
        $this->assertSame(
            'https://ids.brightspace.com/activities/quiz/34907245-882D-4965-B3D6-0708A1D560F9-14513',
            $quizzes[0]->getActivityId()
        );
        $this->assertFalse($quizzes[0]->allowOnlyUsersWithSpecialAccess());
        $this->assertFalse($quizzes[0]->isRetakeIncorrectOnly());

        $this->assertSame(41576, $quizzes[1]->getId());
        $this->assertSame('Diagnostic Test - Word Knowledge', $quizzes[1]->getName());
        $this->assertTrue($quizzes[1]->isActive());
        $this->assertSame(3, $quizzes[1]->getSortOrder());
        $this->assertTrue($quizzes[1]->getAutoExportToGrades());
        $this->assertSame(44375, $quizzes[1]->getGradeItemId());
        $this->assertTrue($quizzes[1]->isAutoSetGraded());
        $this->assertSame('', $quizzes[1]->getInstructions()->getText()->getText());
        $this->assertSame('', $quizzes[1]->getInstructions()->getText()->getHtml());
        $this->assertFalse($quizzes[1]->getInstructions()->isDisplayed());
        $this->assertStringContainsString("\r\nDiagnostic", $quizzes[1]->getDescription()->getText()->getText());
        $this->assertStringContainsString('<hr style="width: 100%', $quizzes[1]->getDescription()->getText()->getHtml());
        $this->assertTrue($quizzes[1]->getDescription()->isDisplayed());
        $this->assertNull($quizzes[1]->getStartDate());
        $this->assertNull($quizzes[1]->getEndDate());
        $this->assertNull($quizzes[1]->getDueDate());
        $this->assertFalse($quizzes[1]->displayInCalendar());
        $this->assertFalse($quizzes[1]->getAttemptsAllowed()->isUnlimited());
        $this->assertSame(1, $quizzes[1]->getAttemptsAllowed()->getNumberOfAttemptsAllowed());
        $this->assertSame(2, $quizzes[1]->getLateSubmissionInfo()->getLateSubmissionOption()->getOption());
        $this->assertSame(1, $quizzes[1]->getLateSubmissionInfo()->getLateLimitMinutes());
        $this->assertTrue($quizzes[1]->getSubmissionTimeLimit()->isEnforced());
        $this->assertTrue($quizzes[1]->getSubmissionTimeLimit()->isShowClock());
        $this->assertSame(11, $quizzes[1]->getSubmissionTimeLimit()->getTimeLimitValue());
        $this->assertSame(1, $quizzes[1]->getSubmissionGracePeriod());
        $this->assertNull($quizzes[1]->getPassword());
        $this->assertSame('', $quizzes[1]->getHeader()->getText()->getText());
        $this->assertSame('', $quizzes[1]->getHeader()->getText()->getHtml());
        $this->assertTrue($quizzes[1]->getHeader()->isDisplayed());
        $this->assertSame('', $quizzes[1]->getFooter()->getText()->getText());
        $this->assertSame('', $quizzes[1]->getFooter()->getText()->getHtml());
        $this->assertTrue($quizzes[1]->getFooter()->isDisplayed());
        $this->assertFalse($quizzes[1]->allowHints());
        $this->assertFalse($quizzes[1]->disableRightClick());
        $this->assertTrue($quizzes[1]->disablePagerAndAlerts());
        $this->assertNull($quizzes[1]->getNotificationEmail());
        $this->assertSame(4, $quizzes[1]->getCalcTypeId()->getOption());
        $this->assertTrue($quizzes[1]->getRestrictIPAddressRange()->isEmpty());
        $this->assertSame(376, $quizzes[1]->getCategoryId());
        $this->assertFalse($quizzes[1]->preventMovingBackwards());
        $this->assertFalse($quizzes[1]->shuffle());
        $this->assertSame(
            'https://ids.brightspace.com/activities/quiz/34907245-882D-4965-B3D6-0708A1D560F9-14514',
            $quizzes[1]->getActivityId()
        );
        $this->assertFalse($quizzes[1]->allowOnlyUsersWithSpecialAccess());
        $this->assertFalse($quizzes[1]->isRetakeIncorrectOnly());
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
                return new MockResponse(json_encode([
                    'Next' => null,
                    'Objects' => [
                        [
                            'QuizId' => 123,
                            'Name' => 'foo',
                            'AutoExportToGrades' => true,
                            'IsActive' => false,
                            'GradeItemId' => null,
                            'IsAutoSetGraded' => true,
                            'Instructions' => [
                                'Text' => [
                                    'Text' => '',
                                    'Html' => '',
                                ],
                                'IsDisplayed' => false,
                            ],
                            'Description' => [
                                'Text' => [
                                    'Text' => '',
                                    'Html' => '',
                                ],
                                'IsDisplayed' => false,
                            ],
                            'Header' => [
                                'Text' => [
                                    'Text' => '',
                                    'Html' => '',
                                ],
                                'IsDisplayed' => false,
                            ],
                            'Footer' => [
                                'Text' => [
                                    'Text' => '',
                                    'Html' => '',
                                ],
                                'IsDisplayed' => false,
                            ],
                            'StartDate' => '2021-10-14T14:00:00.000Z',
                            'EndDate' => '2021-11-04T22:00:00.000Z',
                            'DueDate' => '2021-10-15T22:00:00.000Z',
                            'DisplayInCalendar' => false,
                            'SortOrder' => 5,
                            'SubmissionTimeLimit' => [
                                'IsEnforced' => false,
                                'ShowClock' => false,
                                'TimeLimitValue' => 120,
                            ],
                            'SubmissionGracePeriod' => 5,
                            'LateSubmissionInfo' => [
                                'LateSubmissionOption' => 0,
                                'LateLimitMinutes' => null,
                            ],
                            'AttemptsAllowed' => [
                                'IsUnlimited' => true,
                                'NumberOfAttemptsAllowed' => null,
                            ],
                            'Password' => null,
                            'AllowHints' => false,
                            'DisableRightClick' => false,
                            'DisablePagerAndAlerts' => false,
                            'RestrictIPAddressRange' => [
                            ],
                            'NotificationEmail' => null,
                            'CalcTypeId' => 1,
                            'CategoryId' => null,
                            'PreventMovingBackwards' => false,
                            'Shuffle' => false,
                            'ActivityId' => 'https://ids.brightspace.com/activities/quiz/34907245-882D-4965-B3D6-0708A1D560F9-77531',
                            'AllowOnlyUsersWithSpecialAccess' => false,
                            'IsRetakeIncorrectOnly' => false,
                        ]
                    ]
                ]));
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
        $this->assertSame(44372, $quizzes[0]->getGradeItemId());

        $this->assertSame(41576, $quizzes[1]->getId());
        $this->assertSame('Diagnostic Test - Word Knowledge', $quizzes[1]->getName());
        $this->assertTrue($quizzes[1]->isActive());
        $this->assertSame(44375, $quizzes[1]->getGradeItemId());

        $this->assertSame(123, $quizzes[2]->getId());
        $this->assertSame('foo', $quizzes[2]->getName());
        $this->assertFalse($quizzes[2]->isActive());
        $this->assertNull($quizzes[2]->getGradeItemId());
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

        /** @var MultipleChoiceAnswers $questionInfo */
        $questionInfo = $questions[0]->getQuestionInfo();
        $this->assertInstanceOf(MultipleChoiceAnswers::class, $questionInfo);

        $answers = $questionInfo->getAnswers();
        $this->assertCount(4, $answers);

        $this->assertSame(307387, $answers[0]->getPartId());
        $this->assertSame('47 shares', $answers[0]->getAnswer()->getText());
        $this->assertSame('47 shares', $answers[0]->getAnswer()->getHtml());
        $this->assertSame('', $answers[0]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[0]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[0]->getWeight());

        $this->assertSame(307388, $answers[1]->getPartId());
        $this->assertSame('53 shares', $answers[1]->getAnswer()->getText());
        $this->assertSame('53 shares', $answers[1]->getAnswer()->getHtml());
        $this->assertSame('', $answers[1]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[1]->getAnswerFeedback()->getHtml());
        $this->assertSame(100.0, $answers[1]->getWeight());

        $this->assertSame(307389, $answers[2]->getPartId());
        $this->assertSame('56 shares', $answers[2]->getAnswer()->getText());
        $this->assertSame('56 shares', $answers[2]->getAnswer()->getHtml());
        $this->assertSame('', $answers[2]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[2]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[2]->getWeight());

        $this->assertSame(307390, $answers[3]->getPartId());
        $this->assertSame('62 shares', $answers[3]->getAnswer()->getText());
        $this->assertSame('62 shares', $answers[3]->getAnswer()->getHtml());
        $this->assertSame('', $answers[3]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[3]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[3]->getWeight());

        $this->assertFalse($questionInfo->isRandomize());
        $this->assertSame(4, $questionInfo->getEnumeration()->type());

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

        /** @var MultipleChoiceAnswers $questionInfo */
        $questionInfo = $questions[1]->getQuestionInfo();
        $this->assertInstanceOf(MultipleChoiceAnswers::class, $questionInfo);

        $answers = $questionInfo->getAnswers();
        $this->assertCount(4, $answers);

        $this->assertSame(473242, $answers[0]->getPartId());
        $this->assertSame('$90.10', $answers[0]->getAnswer()->getText());
        $this->assertSame('$90.10', $answers[0]->getAnswer()->getHtml());
        $this->assertSame('', $answers[0]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[0]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[0]->getWeight());

        $this->assertSame(473243, $answers[1]->getPartId());
        $this->assertSame('$100.10', $answers[1]->getAnswer()->getText());
        $this->assertSame('$100.10', $answers[1]->getAnswer()->getHtml());
        $this->assertSame('', $answers[1]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[1]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[1]->getWeight());

        $this->assertSame(473244, $answers[2]->getPartId());
        $this->assertSame('$126.10', $answers[2]->getAnswer()->getText());
        $this->assertSame('$126.10', $answers[2]->getAnswer()->getHtml());
        $this->assertSame('', $answers[2]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[2]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[2]->getWeight());

        $this->assertSame(473245, $answers[3]->getPartId());
        $this->assertSame('$148.10', $answers[3]->getAnswer()->getText());
        $this->assertSame('$148.10', $answers[3]->getAnswer()->getHtml());
        $this->assertSame('', $answers[3]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[3]->getAnswerFeedback()->getHtml());
        $this->assertSame(100.0, $answers[3]->getWeight());

        $this->assertFalse($questionInfo->isRandomize());
        $this->assertSame(4, $questionInfo->getEnumeration()->type());
    }

    public function testQuizQuestionsListWithLongAnswerType(): void
    {
        $this->freezeTime();

        $quizQuestionsListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'quiz_questions_list_with_long_answer_type.json');
        $callback = function (string $method, string $url, array $options) use ($quizQuestionsListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/12396/quizzes/40412/questions/?x_a=baz&x_b=foo&x_c=i8cvgucGOeXc4ecjBVbhj-1Hbui7j9dQ7XU3YN0MnRY&x_d=3g2mBt2EZ7we-3wIUEoCivMVo0_6el4DmcB_ilp2qjs&x_t=1615390200&bookmark=' === $url) {
                return new MockResponse($quizQuestionsListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $quizQuestionListPage = $client->quizQuestionsList(12396, 40412);

        $this->assertNull($quizQuestionListPage->getNextUrl());

        $questions = $quizQuestionListPage->getObjects();

        $this->assertCount(1, $questions);

        $this->assertSame(3005481, $questions[0]->getId());
        $this->assertSame(7, $questions[0]->getType()->type());
        $this->assertSame('CLEP-CollegeComposition-PT1-S2-Q1', $questions[0]->getName());
        $this->assertStringContainsString(
            'Directions: Write an essay',
            $questions[0]->getText()->getText()
        );
        $this->assertStringContainsString(
            'Write an essay in which you discuss',
            $questions[0]->getText()->getHtml()
        );
        $this->assertSame(1.0, $questions[0]->getPoints());
        $this->assertSame(1, $questions[0]->getDifficulty());
        $this->assertFalse($questions[0]->isBonus());
        $this->assertFalse($questions[0]->isMandatory());
        $this->assertNull($questions[0]->getHint());
        $this->assertStringContainsString(
            'Sample Essay A: This essay is scored a 6',
            $questions[0]->getFeedback()->getText()
        );
        $this->assertStringContainsString(
            '<p><strong>Sample Essay A:</strong> This essay is scored a 6',
            $questions[0]->getFeedback()->getHtml()
        );
        $this->assertSame('2021-03-30T22:42:46+00:00', $questions[0]->getLastModifiedAt()->toAtomString());
        $this->assertNull($questions[0]->getLastModifiedBy());
        $this->assertSame(0, $questions[0]->getSectionId());
        $this->assertSame(143538, $questions[0]->getTemplateId());
        $this->assertSame(185743, $questions[0]->getTemplateVersionId());

        /** @var LongAnswer $questionInfo */
        $questionInfo = $questions[0]->getQuestionInfo();
        $this->assertInstanceOf(LongAnswer::class, $questionInfo);

        $this->assertSame(785986, $questionInfo->getPartId());
        $this->assertFalse($questionInfo->studentEditorEnabled());
        $this->assertSame('', $questionInfo->getInitialText()->getText());
        $this->assertSame('', $questionInfo->getInitialText()->getHtml());
        $this->assertSame('', $questionInfo->getAnswerKey()->getText());
        $this->assertSame('', $questionInfo->getAnswerKey()->getHtml());
        $this->assertFalse($questionInfo->attachmentsEnabled());
    }

    public function testQuizQuestionsListWithShortAnswerType(): void
    {
        $this->freezeTime();

        $quizQuestionsListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'quiz_questions_list_with_short_answer_type.json');
        $callback = function (string $method, string $url, array $options) use ($quizQuestionsListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/12408/quizzes/16532/questions/?x_a=baz&x_b=foo&x_c=lohTujGj5AEQNxtIk317SG3wzXCiewwn0oigBQSrnNA&x_d=Djf5WWfF8DXyplxpB00OLCGnE43FfHtc4kj2wHZariI&x_t=1615390200&bookmark=' === $url) {
                return new MockResponse($quizQuestionsListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $quizQuestionListPage = $client->quizQuestionsList(12408, 16532);

        $this->assertSame(
            'https://learn.petersons.com/d2l/api/le/1.60/12408/quizzes/16532/questions/?bookmark=1492746',
            $quizQuestionListPage->getNextUrl()
        );

        $questions = $quizQuestionListPage->getObjects();

        $this->assertCount(1, $questions);

        $this->assertSame(1492746, $questions[0]->getId());
        $this->assertSame(8, $questions[0]->getType()->type());
        $this->assertSame('CLEP-PT1-S2-Q8', $questions[0]->getName());
        $this->assertStringContainsString(
            'A car starts at point A and drives 30 miles due east',
            $questions[0]->getText()->getText()
        );
        $this->assertStringContainsString(
            '<p>A car starts at point A and drives 30 miles due east',
            $questions[0]->getText()->getHtml()
        );
        $this->assertSame(1.0, $questions[0]->getPoints());
        $this->assertSame(1, $questions[0]->getDifficulty());
        $this->assertFalse($questions[0]->isBonus());
        $this->assertFalse($questions[0]->isMandatory());
        $this->assertNull($questions[0]->getHint());
        $this->assertStringContainsString(
            'The correct answer is 34.6.',
            $questions[0]->getFeedback()->getText()
        );
        $this->assertStringContainsString(
            '<p><strong>The correct answer is 34.6.',
            $questions[0]->getFeedback()->getHtml()
        );
        $this->assertSame('2021-06-07T18:11:28+00:00', $questions[0]->getLastModifiedAt()->toAtomString());
        $this->assertSame(4966, $questions[0]->getLastModifiedBy());
        $this->assertSame(0, $questions[0]->getSectionId());
        $this->assertSame(93628, $questions[0]->getTemplateId());
        $this->assertSame(207910, $questions[0]->getTemplateVersionId());

        /** @var ShortAnswers $questionInfo */
        $questionInfo = $questions[0]->getQuestionInfo();
        $this->assertInstanceOf(ShortAnswers::class, $questionInfo);

        $blanks = $questionInfo->getBlanks();
        $this->assertCount(1, $blanks);

        $this->assertSame(879853, $blanks[0]->getPartId());

        $answers = $blanks[0]->getAnswers();

        $this->assertSame('34.6', $answers[0]->getText());
        $this->assertSame(100.0, $answers[0]->getWeight());

        $this->assertSame(0, $blanks[0]->getEvaluationType()->type());
        $this->assertSame(0, $questionInfo->getGradingType()->rule());
    }

    public function testQuizQuestionsListWithFillInTheBlankType(): void
    {
        $this->freezeTime();

        $quizQuestionsListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'quiz_questions_list_with_fill_in_the_blank_type.json');
        $callback = function (string $method, string $url, array $options) use ($quizQuestionsListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/14859/quizzes/11097/questions/?x_a=baz&x_b=foo&x_c=0aOYqkpQ0E_q4GJwvgAb2h9irnklfftT08JeMDqIoNw&x_d=_MoB5gVMTg2gr7HWZ-STsF4TU-Nn_4MpR1zUPiVoBaM&x_t=1615390200&bookmark=' === $url) {
                return new MockResponse($quizQuestionsListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $quizQuestionListPage = $client->quizQuestionsList(14859, 11097);

        $this->assertNull($quizQuestionListPage->getNextUrl());

        $questions = $quizQuestionListPage->getObjects();

        $this->assertCount(1, $questions);

        $this->assertSame(980317, $questions[0]->getId());
        $this->assertSame(3, $questions[0]->getType()->type());
        $this->assertSame('GRE PT1_S1_Q18', $questions[0]->getName());
        $this->assertStringContainsString(
            'Directions: For the following question, enter your answer in the box.',
            $questions[0]->getText()->getText()
        );
        $this->assertStringContainsString(
            '<p><strong>Directions:</strong>&#160;<em>For the following question, enter your answer in the box</em>.</p>',
            $questions[0]->getText()->getHtml()
        );
        $this->assertSame(1.0, $questions[0]->getPoints());
        $this->assertSame(1, $questions[0]->getDifficulty());
        $this->assertFalse($questions[0]->isBonus());
        $this->assertFalse($questions[0]->isMandatory());
        $this->assertNull($questions[0]->getHint());
        $this->assertStringContainsString(
            'The correct answer is 79.',
            $questions[0]->getFeedback()->getText()
        );
        $this->assertStringContainsString(
            '<p><strong>The correct answer is 79.</strong>',
            $questions[0]->getFeedback()->getHtml()
        );
        $this->assertSame('2021-10-12T19:53:46+00:00', $questions[0]->getLastModifiedAt()->toAtomString());
        $this->assertSame(72488, $questions[0]->getLastModifiedBy());
        $this->assertSame(0, $questions[0]->getSectionId());
        $this->assertSame(109334, $questions[0]->getTemplateId());
        $this->assertSame(225035, $questions[0]->getTemplateVersionId());

        /** @var FillInTheBlank $questionInfo */
        $questionInfo = $questions[0]->getQuestionInfo();
        $this->assertInstanceOf(FillInTheBlank::class, $questionInfo);

        $texts = $questionInfo->getTexts();

        $this->assertCount(1, $texts);

        $this->assertStringContainsString(
            'Directions: For the following question, enter your answer in the box.',
            $texts[0]->getText()->getText()
        );

        $this->assertStringContainsString(
            '<p><strong>Directions:</strong>',
            $texts[0]->getText()->getHtml()
        );

        $blanks = $questionInfo->getBlanks();
        $this->assertCount(1, $blanks);

        $this->assertSame(952730, $blanks[0]->getPartId());
        $this->assertSame(30, $blanks[0]->getSize());

        $answers = $blanks[0]->getAnswers();

        $this->assertCount(1, $answers);

        $this->assertSame('79', $answers[0]->getTextAnswer());
        $this->assertSame(100.0, $answers[0]->getWeight());
        $this->assertSame(0, $answers[0]->getEvaluationType()->type());
    }

    public function testQuizQuestionsListWithTrueFalseType(): void
    {
        $this->freezeTime();

        $quizQuestionsListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'quiz_questions_list_with_true_false_type.json');
        $callback = function (string $method, string $url, array $options) use ($quizQuestionsListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/12479/quizzes/17315/questions/?x_a=baz&x_b=foo&x_c=zc_3gAV5K3bTq-Xt4t3kyx3ynByACl60sirCUQ9IFO0&x_d=48AZdbF-ma6d2PSKOjn1pDUYzhBqdLzcaqMtEghT34Y&x_t=1615390200&bookmark=' === $url) {
                return new MockResponse($quizQuestionsListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $quizQuestionListPage = $client->quizQuestionsList(12479, 17315);

        $this->assertSame(
            'https://learn.petersons.com/d2l/api/le/1.60/12479/quizzes/17315/questions/?bookmark=3447017',
            $quizQuestionListPage->getNextUrl()
        );

        $questions = $quizQuestionListPage->getObjects();

        $this->assertCount(1, $questions);

        $this->assertSame(3447012, $questions[0]->getId());
        $this->assertSame(2, $questions[0]->getType()->type());
        $this->assertSame('GMAT-PT1-S2-Q12 ', $questions[0]->getName());
        $this->assertStringContainsString(
            'The following question is based on this passage:',
            $questions[0]->getText()->getText()
        );
        $this->assertStringContainsString(
            '<div class="d-none">
<div style="display: none;">
<p>The following question is',
            $questions[0]->getText()->getHtml()
        );
        $this->assertSame(1.0, $questions[0]->getPoints());
        $this->assertSame(1, $questions[0]->getDifficulty());
        $this->assertFalse($questions[0]->isBonus());
        $this->assertFalse($questions[0]->isMandatory());
        $this->assertNull($questions[0]->getHint());
        $this->assertStringContainsString(
            'The correct answer is True. Sort the table by',
            $questions[0]->getFeedback()->getText()
        );
        $this->assertStringContainsString(
            '<p><strong>The correct answer is True.</strong> Sort the table by',
            $questions[0]->getFeedback()->getHtml()
        );
        $this->assertSame('2021-11-11T17:10:08+00:00', $questions[0]->getLastModifiedAt()->toAtomString());
        $this->assertSame(190, $questions[0]->getLastModifiedBy());
        $this->assertSame(1536134, $questions[0]->getSectionId());
        $this->assertSame(167179, $questions[0]->getTemplateId());
        $this->assertSame(229020, $questions[0]->getTemplateVersionId());

        /** @var TrueFalse $questionInfo */
        $questionInfo = $questions[0]->getQuestionInfo();
        $this->assertInstanceOf(TrueFalse::class, $questionInfo);

        $this->assertSame(969854, $questionInfo->getTruePartId());
        $this->assertSame(100.0, $questionInfo->getTrueWeight());
        $this->assertSame('', $questionInfo->getTrueFeedback()->getText());
        $this->assertSame('', $questionInfo->getTrueFeedback()->getHtml());
        $this->assertSame(969855, $questionInfo->getFalsePartId());
        $this->assertSame(0.0, $questionInfo->getFalseWeight());
        $this->assertSame('', $questionInfo->getFalseFeedback()->getText());
        $this->assertSame('', $questionInfo->getFalseFeedback()->getHtml());
        $this->assertSame(6, $questionInfo->getEnumeration()->type());
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

        /** @var MultipleChoiceAnswers $questionInfo */
        $questionInfo = $questions[0]->getQuestionInfo();
        $this->assertInstanceOf(MultipleChoiceAnswers::class, $questionInfo);

        $answers = $questionInfo->getAnswers();
        $this->assertCount(4, $answers);

        $this->assertSame(307387, $answers[0]->getPartId());
        $this->assertSame('47 shares', $answers[0]->getAnswer()->getText());
        $this->assertSame('47 shares', $answers[0]->getAnswer()->getHtml());
        $this->assertSame('', $answers[0]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[0]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[0]->getWeight());

        $this->assertSame(307388, $answers[1]->getPartId());
        $this->assertSame('53 shares', $answers[1]->getAnswer()->getText());
        $this->assertSame('53 shares', $answers[1]->getAnswer()->getHtml());
        $this->assertSame('', $answers[1]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[1]->getAnswerFeedback()->getHtml());
        $this->assertSame(100.0, $answers[1]->getWeight());

        $this->assertSame(307389, $answers[2]->getPartId());
        $this->assertSame('56 shares', $answers[2]->getAnswer()->getText());
        $this->assertSame('56 shares', $answers[2]->getAnswer()->getHtml());
        $this->assertSame('', $answers[2]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[2]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[2]->getWeight());

        $this->assertSame(307390, $answers[3]->getPartId());
        $this->assertSame('62 shares', $answers[3]->getAnswer()->getText());
        $this->assertSame('62 shares', $answers[3]->getAnswer()->getHtml());
        $this->assertSame('', $answers[3]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[3]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[3]->getWeight());

        $this->assertFalse($questionInfo->isRandomize());
        $this->assertSame(4, $questionInfo->getEnumeration()->type());

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

        /** @var MultipleChoiceAnswers $questionInfo */
        $questionInfo = $questions[1]->getQuestionInfo();
        $this->assertInstanceOf(MultipleChoiceAnswers::class, $questionInfo);

        $answers = $questionInfo->getAnswers();
        $this->assertCount(4, $answers);

        $this->assertSame(473242, $answers[0]->getPartId());
        $this->assertSame('$90.10', $answers[0]->getAnswer()->getText());
        $this->assertSame('$90.10', $answers[0]->getAnswer()->getHtml());
        $this->assertSame('', $answers[0]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[0]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[0]->getWeight());

        $this->assertSame(473243, $answers[1]->getPartId());
        $this->assertSame('$100.10', $answers[1]->getAnswer()->getText());
        $this->assertSame('$100.10', $answers[1]->getAnswer()->getHtml());
        $this->assertSame('', $answers[1]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[1]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[1]->getWeight());

        $this->assertSame(473244, $answers[2]->getPartId());
        $this->assertSame('$126.10', $answers[2]->getAnswer()->getText());
        $this->assertSame('$126.10', $answers[2]->getAnswer()->getHtml());
        $this->assertSame('', $answers[2]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[2]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[2]->getWeight());

        $this->assertSame(473245, $answers[3]->getPartId());
        $this->assertSame('$148.10', $answers[3]->getAnswer()->getText());
        $this->assertSame('$148.10', $answers[3]->getAnswer()->getHtml());
        $this->assertSame('', $answers[3]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[3]->getAnswerFeedback()->getHtml());
        $this->assertSame(100.0, $answers[3]->getWeight());

        $this->assertFalse($questionInfo->isRandomize());
        $this->assertSame(4, $questionInfo->getEnumeration()->type());
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

        /** @var MultipleChoiceAnswers $questionInfo */
        $questionInfo = $questions[0]->getQuestionInfo();
        $this->assertInstanceOf(MultipleChoiceAnswers::class, $questionInfo);

        $answers = $questionInfo->getAnswers();
        $this->assertCount(4, $answers);

        $this->assertSame(307387, $answers[0]->getPartId());
        $this->assertSame('47 shares', $answers[0]->getAnswer()->getText());
        $this->assertSame('47 shares', $answers[0]->getAnswer()->getHtml());
        $this->assertSame('', $answers[0]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[0]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[0]->getWeight());

        $this->assertSame(307388, $answers[1]->getPartId());
        $this->assertSame('53 shares', $answers[1]->getAnswer()->getText());
        $this->assertSame('53 shares', $answers[1]->getAnswer()->getHtml());
        $this->assertSame('', $answers[1]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[1]->getAnswerFeedback()->getHtml());
        $this->assertSame(100.0, $answers[1]->getWeight());

        $this->assertSame(307389, $answers[2]->getPartId());
        $this->assertSame('56 shares', $answers[2]->getAnswer()->getText());
        $this->assertSame('56 shares', $answers[2]->getAnswer()->getHtml());
        $this->assertSame('', $answers[2]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[2]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[2]->getWeight());

        $this->assertSame(307390, $answers[3]->getPartId());
        $this->assertSame('62 shares', $answers[3]->getAnswer()->getText());
        $this->assertSame('62 shares', $answers[3]->getAnswer()->getHtml());
        $this->assertSame('', $answers[3]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[3]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[3]->getWeight());

        $this->assertFalse($questionInfo->isRandomize());
        $this->assertSame(4, $questionInfo->getEnumeration()->type());

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

        /** @var MultipleChoiceAnswers $questionInfo */
        $questionInfo = $questions[1]->getQuestionInfo();
        $this->assertInstanceOf(MultipleChoiceAnswers::class, $questionInfo);

        $answers = $questionInfo->getAnswers();
        $this->assertCount(4, $answers);

        $this->assertSame(473242, $answers[0]->getPartId());
        $this->assertSame('$90.10', $answers[0]->getAnswer()->getText());
        $this->assertSame('$90.10', $answers[0]->getAnswer()->getHtml());
        $this->assertSame('', $answers[0]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[0]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[0]->getWeight());

        $this->assertSame(473243, $answers[1]->getPartId());
        $this->assertSame('$100.10', $answers[1]->getAnswer()->getText());
        $this->assertSame('$100.10', $answers[1]->getAnswer()->getHtml());
        $this->assertSame('', $answers[1]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[1]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[1]->getWeight());

        $this->assertSame(473244, $answers[2]->getPartId());
        $this->assertSame('$126.10', $answers[2]->getAnswer()->getText());
        $this->assertSame('$126.10', $answers[2]->getAnswer()->getHtml());
        $this->assertSame('', $answers[2]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[2]->getAnswerFeedback()->getHtml());
        $this->assertSame(0.0, $answers[2]->getWeight());

        $this->assertSame(473245, $answers[3]->getPartId());
        $this->assertSame('$148.10', $answers[3]->getAnswer()->getText());
        $this->assertSame('$148.10', $answers[3]->getAnswer()->getHtml());
        $this->assertSame('', $answers[3]->getAnswerFeedback()->getText());
        $this->assertSame('', $answers[3]->getAnswerFeedback()->getHtml());
        $this->assertSame(100.0, $answers[3]->getWeight());

        $this->assertFalse($questionInfo->isRandomize());
        $this->assertSame(4, $questionInfo->getEnumeration()->type());
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

    public function testUpdateGradeValueForUserDoesNotThrowExceptionOnSuccessfulUpdate(): void
    {
        $this->freezeTime();

        $incomingGradeValue = IncomingGradeValue::numeric(
            new RichTextInput('', RichTextInputType::make('Text')),
            new RichTextInput('', RichTextInputType::make('Text')),
            3.0,
        );

        $callback = function (string $method, string $url, array $options) use ($incomingGradeValue): MockResponse {
            if (
                'PUT' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/api/le/1.53/1/grades/2/values/3?x_a=baz&x_b=foo&x_c=fAoL6WIxt7dDRDG1k7J_mVK5v4LCarnsgchZwO0rxUs&x_d=D5fh-y5F7zgnOeJU8rE0KNLpizKZPqLnPSWN5aOgrBU&x_t=1615390200' === $url
                &&
                $options['body'] === json_encode($incomingGradeValue->toArray(), \JSON_PRESERVE_ZERO_FRACTION)
                &&
                $options['normalized_headers']['authorization'][0] === 'Authorization: Bearer foo'
            ) {
                return new MockResponse('');
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $client->updateGradeValueForUser($incomingGradeValue, 1, 2, 3, 'foo');

        $this->addToAssertionCount(1);
    }

    public function testUpdateGradeValueForUserThrowsExceptionOnNonSuccessfulUpdate(): void
    {
        $this->freezeTime();

        $incomingGradeValue = IncomingGradeValue::numeric(
            new RichTextInput('', RichTextInputType::make('Text')),
            new RichTextInput('', RichTextInputType::make('Text')),
            3.0,
        );

        $callback = function (string $method, string $url, array $options) use ($incomingGradeValue): MockResponse {
            if (
                'PUT' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/api/le/1.53/1/grades/2/values/3?x_a=baz&x_b=foo&x_c=fAoL6WIxt7dDRDG1k7J_mVK5v4LCarnsgchZwO0rxUs&x_d=D5fh-y5F7zgnOeJU8rE0KNLpizKZPqLnPSWN5aOgrBU&x_t=1615390200' === $url
                &&
                $options['body'] === json_encode($incomingGradeValue->toArray(), \JSON_PRESERVE_ZERO_FRACTION)
                &&
                $options['normalized_headers']['authorization'][0] === 'Authorization: Bearer bar'
            ) {
                return new MockResponse('', ['http_code' => 403]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 403 returned for "https://petersonstest.brightspace.com/d2l/api/le/1.53/1/grades/2/values/3?x_a=baz&x_b=foo&x_c=fAoL6WIxt7dDRDG1k7J_mVK5v4LCarnsgchZwO0rxUs&x_d=D5fh-y5F7zgnOeJU8rE0KNLpizKZPqLnPSWN5aOgrBU&x_t=1615390200".',
                403
            )
        );

        $client->updateGradeValueForUser($incomingGradeValue, 1, 2, 3, 'bar');
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

    public function testGetRootModulesForAnOrganizationUnit(): void
    {
        $this->freezeTime();

        $rootModulesListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'root_modules_list.json');
        $callback = function (string $method, string $url, array $options) use ($rootModulesListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/513982/content/root/?x_a=baz&x_b=foo&x_c=sd87uCDudUE851NdLXKGaBjMKdKDel70YRrPZnITszQ&x_d=5c20ppHATy57XledQl1-STfyAAz7gGCtEj8IUC7SJ3U&x_t=1615390200' === $url) {
                return new MockResponse($rootModulesListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        /** @var Module[] $rootModules */
        $rootModules = $client->getRootModulesForAnOrganizationUnit(513982);

        $this->assertCount(2, $rootModules);

        $this->assertInstanceOf(Module::class, $rootModules[0]);
        $this->assertSame(861, $rootModules[0]->getId());
        $this->assertSame('Getting Started', $rootModules[0]->getTitle());
        $this->assertSame('', $rootModules[0]->getShortTitle());
        $structure = $rootModules[0]->getStructure();
        $this->assertCount(1, $structure);
        $this->assertSame(862, $structure[0]->getId());
        $this->assertSame('Getting Started', $structure[0]->getTitle());
        $this->assertSame('', $structure[0]->getShortTitle());
        $this->assertSame(1, $structure[0]->getType()->getType());
        $this->assertSame('2020-07-29 18:09:33', $structure[0]->getLastModifiedDate()->format('Y-m-d H:i:s'));
        $this->assertNull($rootModules[0]->getStartDate());
        $this->assertNull($rootModules[0]->getEndDate());
        $this->assertNull($rootModules[0]->getDueDate());
        $this->assertFalse($rootModules[0]->isHidden());
        $this->assertFalse($rootModules[0]->isLocked());
        $this->assertSame('', $rootModules[0]->getDescription()->getText());
        $this->assertSame('', $rootModules[0]->getDescription()->getHtml());
        $this->assertNull($rootModules[0]->getParentModuleId());
        $this->assertSame('2017-11-04 17:45:48', $rootModules[0]->getLastModifiedDate()->format('Y-m-d H:i:s'));

        $this->assertInstanceOf(Module::class, $rootModules[1]);
        $this->assertSame(863, $rootModules[1]->getId());
        $this->assertSame('Learner Essentials', $rootModules[1]->getTitle());
        $this->assertSame('', $rootModules[1]->getShortTitle());
        $structure = $rootModules[1]->getStructure();
        $this->assertCount(2, $structure);
        $this->assertSame(864, $structure[0]->getId());
        $this->assertSame('Quick Tips for Getting Started', $structure[0]->getTitle());
        $this->assertSame('', $structure[0]->getShortTitle());
        $this->assertSame(1, $structure[0]->getType()->getType());
        $this->assertSame('2017-11-04 17:45:50', $structure[0]->getLastModifiedDate()->format('Y-m-d H:i:s'));
        $this->assertSame(865, $structure[1]->getId());
        $this->assertSame('Using ePortfolio', $structure[1]->getTitle());
        $this->assertSame('', $structure[1]->getShortTitle());
        $this->assertSame(1, $structure[1]->getType()->getType());
        $this->assertSame('2017-11-04 17:45:50', $structure[1]->getLastModifiedDate()->format('Y-m-d H:i:s'));
        $this->assertNull($rootModules[1]->getStartDate());
        $this->assertNull($rootModules[1]->getEndDate());
        $this->assertNull($rootModules[1]->getDueDate());
        $this->assertFalse($rootModules[1]->isHidden());
        $this->assertFalse($rootModules[1]->isLocked());
        $this->assertStringContainsString('This module contains video playlists', $rootModules[1]->getDescription()->getText());
        $this->assertStringContainsString('<p>This module contains video playlists', $rootModules[1]->getDescription()->getHtml());
        $this->assertNull($rootModules[1]->getParentModuleId());
        $this->assertSame('2020-07-29 17:58:22', $rootModules[1]->getLastModifiedDate()->format('Y-m-d H:i:s'));
    }

    public function testGetRootModulesForAnOrganizationUnitWhenD2LReturnsForbiddenResponse(): void
    {
        $this->freezeTime();

        $callback = function (string $method, string $url, array $options): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/513982/content/root/?x_a=baz&x_b=foo&x_c=sd87uCDudUE851NdLXKGaBjMKdKDel70YRrPZnITszQ&x_d=5c20ppHATy57XledQl1-STfyAAz7gGCtEj8IUC7SJ3U&x_t=1615390200' === $url) {
                return new MockResponse('', ['http_code' => 403]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 403 returned for "https://petersonstest.brightspace.com/d2l/api/le/1.53/513982/content/root/?x_a=baz&x_b=foo&x_c=sd87uCDudUE851NdLXKGaBjMKdKDel70YRrPZnITszQ&x_d=5c20ppHATy57XledQl1-STfyAAz7gGCtEj8IUC7SJ3U&x_t=1615390200".',
                403
            )
        );

        $client->getRootModulesForAnOrganizationUnit(513982);
    }

    public function testGetModuleStructureForAnOrganizationUnitWithModuleInResponse(): void
    {
        $this->freezeTime();

        $moduleStructureListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'module_structure_list_with_module_record.json');
        $callback = function (string $method, string $url, array $options) use ($moduleStructureListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/515376/content/modules/321584/structure/?x_a=baz&x_b=foo&x_c=7qh2mzaXA2gumirmcPPV08yConZ5ixNi-C2ea8tLpz0&x_d=PpMymRZOYVgV7nkyS9EXlwH3i1NQ_Vbgcuiu8rDIkIA&x_t=1615390200' === $url) {
                return new MockResponse($moduleStructureListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        /** @var ContentObject[] $contentObjects */
        $contentObjects = $client->getModuleStructureForAnOrganizationUnit(515376, 321584);

        $this->assertCount(1, $contentObjects);

        $this->assertInstanceOf(Module::class, $contentObjects[0]);
        $this->assertSame(321606, $contentObjects[0]->getId());
        $this->assertSame('Suffix Elements 1 - 20', $contentObjects[0]->getTitle());
        $this->assertSame('', $contentObjects[0]->getShortTitle());
        $structure = $contentObjects[0]->getStructure();
        $this->assertCount(2, $structure);
        $this->assertSame(321666, $structure[0]->getId());
        $this->assertSame('Suffix Elements 1-5', $structure[0]->getTitle());
        $this->assertSame('', $structure[0]->getShortTitle());
        $this->assertSame(1, $structure[0]->getType()->getType());
        $this->assertSame('2021-12-23 15:46:22', $structure[0]->getLastModifiedDate()->format('Y-m-d H:i:s'));
        $this->assertSame(321667, $structure[1]->getId());
        $this->assertSame('Suffix Elements 6-10', $structure[1]->getTitle());
        $this->assertSame('', $structure[1]->getShortTitle());
        $this->assertSame(1, $structure[1]->getType()->getType());
        $this->assertSame('2021-12-23 15:46:22', $structure[1]->getLastModifiedDate()->format('Y-m-d H:i:s'));
        $this->assertNull($contentObjects[0]->getStartDate());
        $this->assertNull($contentObjects[0]->getEndDate());
        $this->assertNull($contentObjects[0]->getDueDate());
        $this->assertFalse($contentObjects[0]->isHidden());
        $this->assertFalse($contentObjects[0]->isLocked());
        $this->assertSame("\n\n\n\n\n\n\n", $contentObjects[0]->getDescription()->getText());
        $this->assertSame("\n\n\n\n\n\n\n", $contentObjects[0]->getDescription()->getHtml());
        $this->assertSame(321584, $contentObjects[0]->getParentModuleId());
        $this->assertSame('2020-08-03 19:12:47', $contentObjects[0]->getLastModifiedDate()->format('Y-m-d H:i:s'));
    }

    public function testGetModuleStructureForAnOrganizationUnitWithTopicsInResponse(): void
    {
        $this->freezeTime();

        $moduleStructureListJsonResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'module_structure_list_with_topic_records.json');
        $callback = function (string $method, string $url, array $options) use ($moduleStructureListJsonResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/515376/content/modules/321584/structure/?x_a=baz&x_b=foo&x_c=7qh2mzaXA2gumirmcPPV08yConZ5ixNi-C2ea8tLpz0&x_d=PpMymRZOYVgV7nkyS9EXlwH3i1NQ_Vbgcuiu8rDIkIA&x_t=1615390200' === $url) {
                return new MockResponse($moduleStructureListJsonResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        /** @var Topic[] $contentObjects */
        $contentObjects = $client->getModuleStructureForAnOrganizationUnit(515376, 321584);

        $this->assertCount(3, $contentObjects);

        $this->assertInstanceOf(Topic::class, $contentObjects[0]);
        $this->assertSame(321655, $contentObjects[0]->getId());
        $this->assertSame(3, $contentObjects[0]->getTopicType()->getType());
        $this->assertSame('Introduction to the Dean Vaughn Total Retention System®', $contentObjects[0]->getTitle());
        $this->assertSame('', $contentObjects[0]->getShortTitle());
        $this->assertSame('https://learn.petersons.com/d2l/lor/viewer/view.d2l?ou=515376&loIdentId=200', $contentObjects[0]->getUrl());
        $this->assertNull($contentObjects[0]->getStartDate());
        $this->assertNull($contentObjects[0]->getEndDate());
        $this->assertNull($contentObjects[0]->getDueDate());
        $this->assertFalse($contentObjects[0]->isHidden());
        $this->assertFalse($contentObjects[0]->isLocked());
        $this->assertFalse($contentObjects[0]->isExempt());
        $this->assertFalse($contentObjects[0]->getOpenAsExternalResource());
        $this->assertSame('', $contentObjects[0]->getDescription()->getText());
        $this->assertSame('', $contentObjects[0]->getDescription()->getHtml());
        $this->assertSame(321600, $contentObjects[0]->getParentModuleId());
        $this->assertNull($contentObjects[0]->getActivityId());
        $this->assertSame(2, $contentObjects[0]->getActivityType()->getType());
        $this->assertNull($contentObjects[0]->getToolId());
        $this->assertNull($contentObjects[0]->getToolItemId());
        $this->assertNull($contentObjects[0]->getGradeItemId());
        $this->assertSame('2021-12-23 15:46:21', $contentObjects[0]->getLastModifiedDate()->format('Y-m-d H:i:s'));

        $this->assertInstanceOf(Topic::class, $contentObjects[1]);
        $this->assertSame(321656, $contentObjects[1]->getId());
        $this->assertSame(3, $contentObjects[1]->getTopicType()->getType());
        $this->assertSame('The Dean Vaughn Total Retention System™- Part 1', $contentObjects[1]->getTitle());
        $this->assertSame('', $contentObjects[1]->getShortTitle());
        $this->assertSame('https://learn.petersons.com/d2l/lor/viewer/view.d2l?ou=515376&loIdentId=201', $contentObjects[1]->getUrl());
        $this->assertNull($contentObjects[1]->getStartDate());
        $this->assertNull($contentObjects[1]->getEndDate());
        $this->assertNull($contentObjects[1]->getDueDate());
        $this->assertFalse($contentObjects[1]->isHidden());
        $this->assertFalse($contentObjects[1]->isLocked());
        $this->assertFalse($contentObjects[1]->isExempt());
        $this->assertFalse($contentObjects[1]->getOpenAsExternalResource());
        $this->assertSame('', $contentObjects[1]->getDescription()->getText());
        $this->assertSame('', $contentObjects[1]->getDescription()->getHtml());
        $this->assertSame(321600, $contentObjects[1]->getParentModuleId());
        $this->assertNull($contentObjects[1]->getActivityId());
        $this->assertSame(2, $contentObjects[1]->getActivityType()->getType());
        $this->assertNull($contentObjects[1]->getToolId());
        $this->assertNull($contentObjects[1]->getToolItemId());
        $this->assertNull($contentObjects[1]->getGradeItemId());
        $this->assertSame('2021-12-23 15:46:21', $contentObjects[1]->getLastModifiedDate()->format('Y-m-d H:i:s'));

        $this->assertInstanceOf(Topic::class, $contentObjects[2]);
        $this->assertSame(321657, $contentObjects[2]->getId());
        $this->assertSame(3, $contentObjects[2]->getTopicType()->getType());
        $this->assertSame('The Dean Vaughn Total Retention System™- Part 2', $contentObjects[2]->getTitle());
        $this->assertSame('', $contentObjects[2]->getShortTitle());
        $this->assertSame('https://learn.petersons.com/d2l/lor/viewer/view.d2l?ou=515376&loIdentId=202', $contentObjects[2]->getUrl());
        $this->assertNull($contentObjects[2]->getStartDate());
        $this->assertNull($contentObjects[2]->getEndDate());
        $this->assertNull($contentObjects[2]->getDueDate());
        $this->assertFalse($contentObjects[2]->isHidden());
        $this->assertFalse($contentObjects[2]->isLocked());
        $this->assertFalse($contentObjects[2]->isExempt());
        $this->assertFalse($contentObjects[2]->getOpenAsExternalResource());
        $this->assertSame('', $contentObjects[2]->getDescription()->getText());
        $this->assertSame('', $contentObjects[2]->getDescription()->getHtml());
        $this->assertSame(321600, $contentObjects[2]->getParentModuleId());
        $this->assertNull($contentObjects[2]->getActivityId());
        $this->assertSame(2, $contentObjects[2]->getActivityType()->getType());
        $this->assertNull($contentObjects[2]->getToolId());
        $this->assertNull($contentObjects[2]->getToolItemId());
        $this->assertNull($contentObjects[2]->getGradeItemId());
        $this->assertSame('2021-12-23 15:46:21', $contentObjects[2]->getLastModifiedDate()->format('Y-m-d H:i:s'));
    }

    public function testGetModuleStructureForAnOrganizationUnitWhenD2LReturnsForbiddenResponse(): void
    {
        $this->freezeTime();

        $callback = function (string $method, string $url, array $options): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/le/1.53/515376/content/modules/321584/structure/?x_a=baz&x_b=foo&x_c=7qh2mzaXA2gumirmcPPV08yConZ5ixNi-C2ea8tLpz0&x_d=PpMymRZOYVgV7nkyS9EXlwH3i1NQ_Vbgcuiu8rDIkIA&x_t=1615390200' === $url) {
                return new MockResponse('', ['http_code' => 403]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 403 returned for "https://petersonstest.brightspace.com/d2l/api/le/1.53/515376/content/modules/321584/structure/?x_a=baz&x_b=foo&x_c=7qh2mzaXA2gumirmcPPV08yConZ5ixNi-C2ea8tLpz0&x_d=PpMymRZOYVgV7nkyS9EXlwH3i1NQ_Vbgcuiu8rDIkIA&x_t=1615390200".',
                403
            )
        );

        $client->getModuleStructureForAnOrganizationUnit(515376, 321584);
    }

    public function testUpdateContentTopicCompletionDoesNotThrowExceptionOnSuccessfulUpdate(): void
    {
        $this->freezeTime();

        $time = CarbonImmutable::now();

        $contentTopicCompletionUpdate = new ContentTopicCompletionUpdate($time);

        $callback = function (string $method, string $url, array $options) use ($contentTopicCompletionUpdate): MockResponse {
            if (
                'PUT' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/api/le/1.53/1/content/topics/2/completions/users/3?x_a=baz&x_b=foo&x_c=uT_agY5cX1AHy84RNtsesb1ht4r8jgEhEcyTybCuxTU&x_d=m0DtnYriG3dKoeaBQbVbSx-jFuw5vKnuhwgsEWEyRO0&x_t=1615390200' === $url
                && $options['body'] === json_encode($contentTopicCompletionUpdate->toArray())
            ) {
                return new MockResponse('');
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $client->updateContentTopicCompletion($contentTopicCompletionUpdate, 1, 2, 3);

        $this->addToAssertionCount(1);
    }

    public function testUpdateContentTopicCompletionThrowsExceptionOnNonSuccessfulUpdate(): void
    {
        $this->freezeTime();

        $time = CarbonImmutable::now();

        $contentTopicCompletionUpdate = new ContentTopicCompletionUpdate($time);

        $callback = function (string $method, string $url, array $options) use ($contentTopicCompletionUpdate): MockResponse {
            if (
                'PUT' === $method
                &&
                'https://petersonstest.brightspace.com/d2l/api/le/1.53/1/content/topics/2/completions/users/3?x_a=baz&x_b=foo&x_c=uT_agY5cX1AHy84RNtsesb1ht4r8jgEhEcyTybCuxTU&x_d=m0DtnYriG3dKoeaBQbVbSx-jFuw5vKnuhwgsEWEyRO0&x_t=1615390200' === $url
                && $options['body'] === json_encode($contentTopicCompletionUpdate->toArray())
            ) {
                return new MockResponse('', ['http_code' => 403]);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        $this->expectExceptionObject(
            new ApiException(
                'HTTP 403 returned for "https://petersonstest.brightspace.com/d2l/api/le/1.53/1/content/topics/2/completions/users/3?x_a=baz&x_b=foo&x_c=uT_agY5cX1AHy84RNtsesb1ht4r8jgEhEcyTybCuxTU&x_d=m0DtnYriG3dKoeaBQbVbSx-jFuw5vKnuhwgsEWEyRO0&x_t=1615390200".',
                403
            )
        );

        $client->updateContentTopicCompletion($contentTopicCompletionUpdate, 1, 2, 3);
    }

    public function testGetSectionsForOrganizationUnit(): void
    {
        $this->freezeTime();

        $coursesSectionResponse = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'courses_section_response.json');
        $callback = function (string $method, string $url, array $options) use ($coursesSectionResponse): MockResponse {
            if ('GET' === $method && 'https://petersonstest.brightspace.com/d2l/api/lp/1.30/501470/sections/?x_a=baz&x_b=foo&x_c=DAnAWfmb-87cZ7EHLqBqBf4mrX3GFbtUplW5oz6YcK4&x_d=HSqx-2FOzQ08dIKhqVrgiD-2V7jmXaIZ14eq25Sd1GU&x_t=1615390200' === $url) {
                return new MockResponse($coursesSectionResponse);
            }

            $this->fail('This should not have happened.');
        };

        $mockClient = new MockHttpClient($callback);

        $client = $this->getClient($mockClient);

        /** @var Section[] $sectionsForOrganizationUnit */
        $sectionsForOrganizationUnit = $client->getSectionsForOrganizationUnit(501470);

        $this->assertCount(2, $sectionsForOrganizationUnit);

        $this->assertInstanceOf(Section::class, $sectionsForOrganizationUnit[0]);
        $this->assertSame(501471, $sectionsForOrganizationUnit[0]->getSectionId());
        $this->assertSame('Section 1', $sectionsForOrganizationUnit[0]->getName());
        $this->assertSame('sec1', $sectionsForOrganizationUnit[0]->getCode());
        $this->assertSame('', $sectionsForOrganizationUnit[0]->getDescription()->getHtml());
        $this->assertSame('', $sectionsForOrganizationUnit[0]->getDescription()->getText());
        $this->assertCount(3, $sectionsForOrganizationUnit[0]->getEnrollments());
        $this->assertSame([13, 42, 333], $sectionsForOrganizationUnit[0]->getEnrollments());

        $this->assertInstanceOf(Section::class, $sectionsForOrganizationUnit[1]);
        $this->assertSame(501472, $sectionsForOrganizationUnit[1]->getSectionId());
        $this->assertSame('Archive', $sectionsForOrganizationUnit[1]->getName());
        $this->assertSame('sec2', $sectionsForOrganizationUnit[1]->getCode());
        $this->assertSame('', $sectionsForOrganizationUnit[1]->getDescription()->getHtml());
        $this->assertSame('', $sectionsForOrganizationUnit[1]->getDescription()->getText());
        $this->assertCount(0, $sectionsForOrganizationUnit[1]->getEnrollments());
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
