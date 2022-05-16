<?php

declare(strict_types=1);

namespace Petersons\D2L\Contracts;

use Illuminate\Support\Collection;
use Petersons\D2L\DTO\BrightspaceDataSet\BrightspaceDataSetReportInfo;
use Petersons\D2L\DTO\BrightspaceDataSet\DataSetReportInfo;
use Petersons\D2L\DTO\BrightspaceDataSet\PagedBrightspaceDataSetReportInfo;
use Petersons\D2L\DTO\ContentCompletions\ContentTopicCompletionUpdate;
use Petersons\D2L\DTO\ContentObject\ContentObject;
use Petersons\D2L\DTO\ContentObject\Module;
use Petersons\D2L\DTO\DataExport\CreateExportJobData;
use Petersons\D2L\DTO\DataExport\DataSetData;
use Petersons\D2L\DTO\DataExport\ExportJobData;
use Petersons\D2L\DTO\Enrollment\CreateEnrollment;
use Petersons\D2L\DTO\Enrollment\CreateSectionEnrollment;
use Petersons\D2L\DTO\Enrollment\Enrollment;
use Petersons\D2L\DTO\Enrollment\OrganizationUnitUser;
use Petersons\D2L\DTO\Grade\GradeObjectCategory;
use Petersons\D2L\DTO\Grade\IncomingGradeValue;
use Petersons\D2L\DTO\Guid;
use Petersons\D2L\DTO\Organization\OrganizationInfo;
use Petersons\D2L\DTO\Organization\OrgUnit;
use Petersons\D2L\DTO\Organization\OrgUnitProperties;
use Petersons\D2L\DTO\Quiz\Quiz;
use Petersons\D2L\DTO\Quiz\QuizListPage;
use Petersons\D2L\DTO\Quiz\QuizQuestion;
use Petersons\D2L\DTO\Quiz\QuizQuestionListPage;
use Petersons\D2L\DTO\User\CreateUser;
use Petersons\D2L\DTO\User\UpdateUser;
use Petersons\D2L\DTO\User\UserData;
use Petersons\D2L\Exceptions\ApiException;

interface ClientInterface
{
    public const D2L_DATETIME_FORMAT = "Y-m-d\TH:i:s.v\Z";

    public const GUID_OK = 'OK';
    public const GUID_EXPIRED = 'EXPIRED';
    public const GUID_INVALID = 'INVALID_GUID';
    public const GUID_ERROR = 'ERROR';
    public const GUID_UNKNOWN_VERSION = 'UNKNOWN_VERSION';
    public const GUID_INVALID_DATA = 'INVALID_DATA';
    public const GUID_NO_DB_CONNECTION = 'NO_DB_CONNECTION';

    /**
     * @link https://docs.valence.desire2learn.com/res/user.html#get--d2l-api-lp-(version)-users-(userId)
     * @throws ApiException
     */
    public function getUserById(int $userId): UserData;

    /**
     * @link https://docs.valence.desire2learn.com/res/user.html#get--d2l-api-lp-(version)-users-
     * @throws ApiException
     */
    public function getUserByOrgDefinedId(string $orgDefinedId): UserData;

    /**
     * @link https://docs.valence.desire2learn.com/res/user.html#get--d2l-api-lp-(version)-users-
     * @throws ApiException
     */
    public function getUserByEmail(string $email): UserData;

    /**
     * @link https://docs.valence.desire2learn.com/res/user.html#post--d2l-api-lp-(version)-users-
     * @throws ApiException
     */
    public function createUser(CreateUser $createUser): UserData;

    /**
     * @link https://docs.valence.desire2learn.com/res/user.html#put--d2l-api-lp-(version)-users-(userId)
     * @throws ApiException
     */
    public function updateUser(UpdateUser $updateUser): UserData;

    /**
     * @link https://docs.valence.desire2learn.com/res/enroll.html#post--d2l-api-lp-(version)-enrollments-
     * @throws ApiException
     */
    public function enrollUser(CreateEnrollment $createEnrollment): Enrollment;

    /**
     * @link https://docs.valence.desire2learn.com/res/enroll.html#post--d2l-api-lp-(version)-(orgUnitId)-sections-(sectionId)-enrollments-
     * @throws ApiException
     */
    public function enrollUserInASection(CreateSectionEnrollment $createSectionEnrollment): void;

    /**
     * @link https://petersons.brightspace.com/d2l/guids/D2L.Guid.2.asmx?op=GenerateExpiringGuid
     * @throws ApiException
     */
    public function generateExpiringGuid(string $orgDefinedId): Guid;

    /**
     * @link https://petersons.brightspace.com/d2l/guids/D2L.Guid.2.asmx?op=ValidateGuid
     * @throws ApiException
     */
    public function validateGuid(Guid $guid, string $orgDefinedId): bool;

    /**
     * @link https://docs.valence.desire2learn.com/res/dataExport.html#get--d2l-api-lp-(version)-dataExport-bds
     * @throws ApiException
     * @return Collection|DataSetReportInfo[]
     */
    public function getBrightspaceDataExportList(): Collection;

    /**
     * @link https://docs.valence.desire2learn.com/res/dataExport.html#get--d2l-api-lp-(version)-dataExport-bds
     * @throws ApiException
     */
    public function getBrightspaceDataExportItems(int $page = 1, int $pageSize = 100): PagedBrightspaceDataSetReportInfo;

    /**
     * @link https://docs.valence.desire2learn.com/res/dataExport.html#get--d2l-api-lp-(version)-dataExport-bds
     * @throws ApiException
     */
    public function findBrightspaceDataExportItemByName(string $name): ?BrightspaceDataSetReportInfo;

    /**
     * @link https://docs.valence.desire2learn.com/res/quiz.html#get--d2l-api-le-(version)-(orgUnitId)-quizzes-(quizId)
     * @throws ApiException
     */
    public function getQuizById(int $orgUnitId, int $quizId): Quiz;

    /**
     * @link https://docs.valence.desire2learn.com/res/quiz.html#get--d2l-api-le-(version)-(orgUnitId)-quizzes-
     * @throws ApiException
     */
    public function quizzesList(int $orgUnitId, ?string $bookmark = null): QuizListPage;

    /**
     * @link https://docs.valence.desire2learn.com/res/quiz.html#get--d2l-api-le-(version)-(orgUnitId)-quizzes-
     * @return Collection|Quiz[]
     */
    public function getQuizzesForAnOrganizationUnit(int $orgUnitId): Collection;

    /**
     * @link https://docs.valence.desire2learn.com/res/quiz.html#get--d2l-api-le-(version)-(orgUnitId)-quizzes-(quizId)-questions-
     * @throws ApiException
     */
    public function quizQuestionsList(int $orgUnitId, int $quizId, ?string $bookmark = null): QuizQuestionListPage;

    /**
     * @link https://docs.valence.desire2learn.com/res/quiz.html#get--d2l-api-le-(version)-(orgUnitId)-quizzes-(quizId)-questions-
     * @return Collection|QuizQuestion[]
     */
    public function getQuizQuestionsForAQuiz(int $orgUnitId, int $quizId): Collection;

    /**
     * @link https://docs.valence.desire2learn.com/res/quiz.html#get--d2l-api-le-(version)-(orgUnitId)-quizzes-(quizId)-questions-
     * @throws ApiException
     * @return Collection|OrganizationUnitUser[]
     */
    public function getEnrolledUsersForAnOrganizationUnit(int $orgUnitId): Collection;

    /**
     * @link https://docs.valence.desire2learn.com/res/grade.html#put--d2l-api-le-(version)-(orgUnitId)-grades-(gradeObjectId)-values-(userId)
     * @throws ApiException
     */
    public function updateGradeValueForUser(IncomingGradeValue $incomingGradeValue, int $orgUnitId, int $gradeObjectId, int $userId, string $bearerToken): void;

    /**
     * @link https://docs.valence.desire2learn.com/res/orgunit.html#get--d2l-api-lp-(version)-organization-info
     * @throws ApiException
     */
    public function getOrganizationInfo(): OrganizationInfo;

    /**
     * @link https://docs.valence.desire2learn.com/res/orgunit.html#get--d2l-api-lp-(version)-orgstructure-
     * @throws ApiException
     * @return Collection|OrgUnitProperties[]
     */
    public function getOrganizationStructure(): Collection;

    /**
     * @link https://docs.valence.desire2learn.com/res/orgunit.html#get--d2l-api-lp-(version)-orgstructure-(orgUnitId)-descendants-paged-
     * @throws ApiException
     * @return Collection|OrgUnit[]
     */
    public function getDescendentUnitsForAnOrganizationUnit(int $orgUnitId): Collection;

    /**
     * @link https://docs.valence.desire2learn.com/res/grade.html#get--d2l-api-le-(version)-(orgUnitId)-grades-categories-
     * @throws ApiException
     * @return Collection|GradeObjectCategory[]
     */
    public function getGradeCategoriesForAnOrganizationUnit(int $orgUnitId): Collection;

    /**
     * @link https://docs.valence.desire2learn.com/res/dataExport.html#get--d2l-api-lp-(version)-dataExport-list
     * @throws ApiException
     * @return Collection|DataSetData[]
     */
    public function getDataExportList(): Collection;

    /**
     * @link https://docs.valence.desire2learn.com/res/dataExport.html#post--d2l-api-lp-(version)-dataExport-create
     * @throws ApiException
     */
    public function createDataExport(CreateExportJobData $createExportJobData): ExportJobData;

    /**
     * @link https://docs.valence.desire2learn.com/res/content.html#get--d2l-api-le-(version)-(orgUnitId)-content-root-
     * @throws ApiException
     * @return Collection|Module[]
     */
    public function getRootModulesForAnOrganizationUnit(int $orgUnitId): Collection;

    /**
     * @link https://docs.valence.desire2learn.com/res/content.html#get--d2l-api-le-(version)-(orgUnitId)-content-modules-(moduleId)-structure-
     * @throws ApiException
     * @return Collection|ContentObject[]
     */
    public function getModuleStructureForAnOrganizationUnit(int $orgUnitId, int $moduleId): Collection;

    /**
     * @link https://docs.valence.desire2learn.com/res/content.html#put--d2l-api-le-(version)-(orgUnitId)-content-topics-(topicId)-completions-users-(userId)
     * @throws ApiException
     */
    public function updateContentTopicCompletion(ContentTopicCompletionUpdate $updateContentTopicCompletion, int $orgUnitId, int $topicId, int $userId): void;

    /**
     * Retrieve all the sections for a provided org unit.
     * @link https://docs.valence.desire2learn.com/res/enroll.html#get--d2l-api-lp-(version)-(orgUnitId)-sections-
     * @throws ApiException
     * @return Collection&ContentObject[]
     */
    public function getSectionsForOrganizationUnit(int $orgUnitId): Collection;
}
