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

        $courseUrl = $courseUrlGenerator->generate($guid, $user, $lmsCourseId);

        $this->assertSame($expectedUrl, $courseUrl);
    }

    public function testItThrowsUserOrgDefinedIdMissingExceptionWhenTheGivenUserDoesNotHaveOrgDefinedId(): void
    {
        $guid = new Guid(bin2hex(random_bytes(10)));
        $user = $this->getUser(null);
        $lmsCourseId = random_int(1, 10000);

        $courseUrlGenerator = new CourseUrlGenerator(
            'https://petersonstest.brightspace.com',
            '/d2l/lp/auth/login/ssoLogin.d2l',
        );

        $this->expectExceptionObject(new UserOrgDefinedIdMissingException($user));

        $courseUrlGenerator->generate($guid, $user, $lmsCourseId);
    }

    private function getUser(?string $orgDefinedId): UserData
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
