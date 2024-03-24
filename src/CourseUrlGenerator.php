<?php

declare(strict_types=1);

namespace Petersons\D2L;

use Petersons\D2L\DTO\Guid;
use Petersons\D2L\DTO\User\UserData;
use Petersons\D2L\Exceptions\UserOrgDefinedIdMissingException;

final class CourseUrlGenerator
{
    public function __construct(
        private string $d2lHost,
        private string $d2lGuidLoginUri
    ) {}

    /**
     * @throws UserOrgDefinedIdMissingException
     */
    public function generateCourseUrl(Guid $guid, UserData $user, int $lmsCourseId): string
    {
        $this->checkIfUserHasOrgDefinedIdDefined($user);

        $queryString = http_build_query(
            [
                'guid' => $guid->getValue(),
                'orgId' => $lmsCourseId, // the course ID to which the user is sent
                'orgDefinedId' => $user->getOrgDefinedId(),
            ]
        );

        return sprintf('%s%s?%s', $this->d2lHost, $this->d2lGuidLoginUri, $queryString);
    }

    /**
     * @throws UserOrgDefinedIdMissingException
     */
    public function generateCourseGradesUrl(Guid $guid, UserData $user, int $lmsCourseId): string
    {
        $this->checkIfUserHasOrgDefinedIdDefined($user);

        $queryString = http_build_query(
            [
                'guid' => $guid->getValue(),
                'orgId' => $lmsCourseId,
                'orgDefinedId' => $user->getOrgDefinedId(),
                'target' => sprintf('%s/d2l/lms/grades/my_grades/main.d2l?ou=%d', $this->d2lHost, $lmsCourseId),
            ]
        );

        return sprintf('%s%s?%s', $this->d2lHost, $this->d2lGuidLoginUri, $queryString);
    }

    /**
     * @throws UserOrgDefinedIdMissingException
     */
    private function checkIfUserHasOrgDefinedIdDefined(UserData $user): void
    {
        if (null === $user->getOrgDefinedId()) {
            throw new UserOrgDefinedIdMissingException($user);
        }
    }
}
