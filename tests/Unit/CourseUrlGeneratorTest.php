<?php

declare(strict_types=1);

namespace Tests\Unit;

use Petersons\D2L\CourseUrlGenerator;
use Petersons\D2L\DTO\Guid;
use Petersons\D2L\DTO\User\UserData;
use Petersons\D2L\Exceptions\UserOrgDefinedIdMissingException;
use PHPUnit\Framework\TestCase;

final class CourseUrlGeneratorTest extends TestCase
{
    public function testCourseUrlGeneration(): void
    {
        $guid = new Guid(bin2hex(random_bytes(10)));
        $user = $this->getUser($orgDefinedId = bin2hex(random_bytes(10)));
        $lmsCourseId = random_int(1, 10000);

        $courseUrlGenerator = new CourseUrlGenerator(
            $d2lHost = 'https://petersonstest.brightspace.com',
            $d2lGuidLoginUri = '/d2l/lp/auth/login/ssoLogin.d2l',
        );

        $expectedUrl = sprintf(
            '%s%s?guid=%s&orgId=%d&orgDefinedId=%s',
            $d2lHost,
            $d2lGuidLoginUri,
            $guid->getValue(),
            $lmsCourseId,
            $orgDefinedId
        );

        $courseUrl = $courseUrlGenerator->generateCourseUrl($guid, $user, $lmsCourseId);

        $this->assertSame($expectedUrl, $courseUrl);
    }

    public function testCourseUrlGenerationThrowsUserOrgDefinedIdMissingExceptionWhenTheGivenUserDoesNotHaveOrgDefinedId(): void
    {
        $guid = new Guid(bin2hex(random_bytes(10)));
        $user = $this->getUser(null);
        $lmsCourseId = random_int(1, 10000);

        $courseUrlGenerator = new CourseUrlGenerator(
            'https://petersonstest.brightspace.com',
            '/d2l/lp/auth/login/ssoLogin.d2l',
        );

        $this->expectExceptionObject(new UserOrgDefinedIdMissingException($user));

        $courseUrlGenerator->generateCourseUrl($guid, $user, $lmsCourseId);
    }

    public function testCourseGradesUrlGeneration(): void
    {
        $guid = new Guid(bin2hex(random_bytes(10)));
        $user = $this->getUser($orgDefinedId = bin2hex(random_bytes(10)));
        $lmsCourseId = random_int(1, 10000);

        $courseUrlGenerator = new CourseUrlGenerator(
            $d2lHost = 'https://petersonstest.brightspace.com',
            $d2lGuidLoginUri = '/d2l/lp/auth/login/ssoLogin.d2l',
        );

        $expectedUrl = sprintf(
            '%s%s?guid=%s&orgId=%d&orgDefinedId=%s&target=%s',
            $d2lHost,
            $d2lGuidLoginUri,
            $guid->getValue(),
            $lmsCourseId,
            $orgDefinedId,
            urlencode(sprintf('%s/d2l/lms/grades/my_grades/main.d2l?ou=%d', $d2lHost, $lmsCourseId)),
        );

        $courseGradesUrl = $courseUrlGenerator->generateCourseGradesUrl($guid, $user, $lmsCourseId);

        $this->assertSame($expectedUrl, $courseGradesUrl);
    }

    public function testCourseGradesUrlGenerationThrowsUserOrgDefinedIdMissingExceptionWhenTheGivenUserDoesNotHaveOrgDefinedId(): void
    {
        $guid = new Guid(bin2hex(random_bytes(10)));
        $user = $this->getUser(null);
        $lmsCourseId = random_int(1, 10000);

        $courseUrlGenerator = new CourseUrlGenerator(
            'https://petersonstest.brightspace.com',
            '/d2l/lp/auth/login/ssoLogin.d2l',
        );

        $this->expectExceptionObject(new UserOrgDefinedIdMissingException($user));

        $courseUrlGenerator->generateCourseGradesUrl($guid, $user, $lmsCourseId);
    }

    private function getUser(string|null $orgDefinedId): UserData
    {
        return new UserData(
            555,
            55,
            '',
            null,
            '',
            '',
            '',
            $orgDefinedId,
            '',
            true,
            null
        );
    }
}
