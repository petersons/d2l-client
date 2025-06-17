<?php

declare(strict_types=1);

namespace Petersons\D2L;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Petersons\D2L\Contracts\ClientInterface;
use Petersons\D2L\DTO\BrightspaceDataSet\BrightspaceDataSetReportInfo;
use Petersons\D2L\DTO\BrightspaceDataSet\DataSetReportInfo;
use Petersons\D2L\DTO\BrightspaceDataSet\PagedBrightspaceDataSetReportInfo;
use Petersons\D2L\DTO\ContentCompletions\ContentTopicCompletionUpdate;
use Petersons\D2L\DTO\ContentObject\Module;
use Petersons\D2L\DTO\ContentObject\Structure;
use Petersons\D2L\DTO\ContentObject\Topic;
use Petersons\D2L\DTO\DataExport\CreateExportJobData;
use Petersons\D2L\DTO\DataExport\DataSetData;
use Petersons\D2L\DTO\DataExport\DataSetFilter;
use Petersons\D2L\DTO\DataExport\ExportJobData;
use Petersons\D2L\DTO\Enrollment\CreateEnrollment;
use Petersons\D2L\DTO\Enrollment\CreateSectionEnrollment;
use Petersons\D2L\DTO\Enrollment\Enrollment;
use Petersons\D2L\DTO\Enrollment\OrganizationUnitUser;
use Petersons\D2L\DTO\Enrollment\RoleInfo;
use Petersons\D2L\DTO\Grade\GradeObject;
use Petersons\D2L\DTO\Grade\GradeObjectCategory;
use Petersons\D2L\DTO\Grade\GradeObjectCategoryData;
use Petersons\D2L\DTO\Grade\IncomingGradeValue;
use Petersons\D2L\DTO\Guid;
use Petersons\D2L\DTO\Organization\OrganizationInfo;
use Petersons\D2L\DTO\Organization\OrganizationUnitTypeInfo;
use Petersons\D2L\DTO\Organization\OrgUnit;
use Petersons\D2L\DTO\Organization\OrgUnitProperties;
use Petersons\D2L\DTO\Quiz\AttemptsAllowed;
use Petersons\D2L\DTO\Quiz\Description;
use Petersons\D2L\DTO\Quiz\FillInTheBlank;
use Petersons\D2L\DTO\Quiz\FillInTheBlankAnswer;
use Petersons\D2L\DTO\Quiz\FillInTheBlankBlank;
use Petersons\D2L\DTO\Quiz\FillInTheBlankText;
use Petersons\D2L\DTO\Quiz\Footer;
use Petersons\D2L\DTO\Quiz\Header;
use Petersons\D2L\DTO\Quiz\Instructions;
use Petersons\D2L\DTO\Quiz\LateSubmissionInfo;
use Petersons\D2L\DTO\Quiz\Likert;
use Petersons\D2L\DTO\Quiz\LikertStatement;
use Petersons\D2L\DTO\Quiz\LongAnswer;
use Petersons\D2L\DTO\Quiz\MultipleChoiceAnswer;
use Petersons\D2L\DTO\Quiz\MultipleChoiceAnswers;
use Petersons\D2L\DTO\Quiz\MultipleShortAnswer;
use Petersons\D2L\DTO\Quiz\MultipleShortAnswers;
use Petersons\D2L\DTO\Quiz\MultiSelectAnswer;
use Petersons\D2L\DTO\Quiz\MultiSelectAnswers;
use Petersons\D2L\DTO\Quiz\Quiz;
use Petersons\D2L\DTO\Quiz\QuizListPage;
use Petersons\D2L\DTO\Quiz\QuizQuestion;
use Petersons\D2L\DTO\Quiz\QuizQuestionListPage;
use Petersons\D2L\DTO\Quiz\QuizQuestionType;
use Petersons\D2L\DTO\Quiz\RestrictIPAddressRange;
use Petersons\D2L\DTO\Quiz\ShortAnswerBlank;
use Petersons\D2L\DTO\Quiz\ShortAnswerBlankAnswer;
use Petersons\D2L\DTO\Quiz\ShortAnswers;
use Petersons\D2L\DTO\Quiz\SubmissionTimeLimit;
use Petersons\D2L\DTO\Quiz\TrueFalse;
use Petersons\D2L\DTO\RichText;
use Petersons\D2L\DTO\Section\Section;
use Petersons\D2L\DTO\User\CreateUser;
use Petersons\D2L\DTO\User\UpdateUser;
use Petersons\D2L\DTO\User\User;
use Petersons\D2L\DTO\User\UserData;
use Petersons\D2L\Enum\ContentObject\ActivityType;
use Petersons\D2L\Enum\ContentObject\TopicType;
use Petersons\D2L\Enum\ContentObject\Type;
use Petersons\D2L\Enum\DataExport\ExportFilterType;
use Petersons\D2L\Enum\DataExport\ExportJobStatus;
use Petersons\D2L\Enum\Quiz\EnumerationType;
use Petersons\D2L\Enum\Quiz\EvaluationType;
use Petersons\D2L\Enum\Quiz\LateSubmissionOption;
use Petersons\D2L\Enum\Quiz\OverallGradeCalculationOption;
use Petersons\D2L\Enum\Quiz\QuestionGradingRule;
use Petersons\D2L\Enum\Quiz\QuestionScaleOption;
use Petersons\D2L\Enum\Quiz\QuestionStyle;
use Petersons\D2L\Exceptions\ApiException;
use SimpleXMLElement;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class SymfonyHttpClient implements ClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private AuthenticatedUriFactory $authenticatedUriFactory,
        private string $orgId,
        private string $installationCode,
        private string $pKey,
        private string $apiLpVersion,
        private string $apiLeVersion,
    ) {}

    public function getUserById(int $userId): UserData
    {
        $method = 'GET';
        $path = sprintf('/d2l/api/lp/%s/users/%d', $this->apiLpVersion, $userId);

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        return $this->getUserDtoFromUserArrayResponse($decodedResponse);
    }

    public function getUserByOrgDefinedId(string $orgDefinedId): UserData
    {
        $method = 'GET';
        $path = sprintf('/d2l/api/lp/%s/users/', $this->apiLpVersion);

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => array_merge(
                    $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
                    [
                        'orgDefinedId' => $orgDefinedId,
                    ],
                ),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        return $this->getSingleUserDtoFromMultipleUserArrayResponse($decodedResponse);
    }

    public function getUserByEmail(string $email): UserData
    {
        $method = 'GET';
        $path = sprintf('/d2l/api/lp/%s/users/', $this->apiLpVersion);

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => array_merge(
                    $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
                    [
                        'externalEmail' => $email,
                    ],
                ),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        return $this->getSingleUserDtoFromMultipleUserArrayResponse($decodedResponse);
    }

    public function createUser(CreateUser $createUser): UserData
    {
        $method = 'POST';
        $path = sprintf('/d2l/api/lp/%s/users/', $this->apiLpVersion);

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
                'json' => $createUser->toArray(),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        return $this->getUserDtoFromUserArrayResponse($decodedResponse);
    }

    public function updateUser(UpdateUser $updateUser): UserData
    {
        $method = 'PUT';
        $path = sprintf('/d2l/api/lp/%s/users/%d', $this->apiLpVersion, $updateUser->getLmsUserId());

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
                'json' => $updateUser->toArray(),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        return $this->getUserDtoFromUserArrayResponse($decodedResponse);
    }

    public function enrollUser(CreateEnrollment $createEnrollment): Enrollment
    {
        $method = 'POST';
        $path = sprintf('/d2l/api/lp/%s/enrollments/', $this->apiLpVersion);

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
                'json' => $createEnrollment->toArray(),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        return new Enrollment(
            $decodedResponse['OrgUnitId'],
            $decodedResponse['UserId'],
            $decodedResponse['RoleId'],
            $decodedResponse['IsCascading'],
        );
    }

    public function enrollUserInASection(CreateSectionEnrollment $createSectionEnrollment): void
    {
        $method = 'POST';
        $path = sprintf(
            '/d2l/api/lp/%s/%d/sections/%d/enrollments/',
            $this->apiLpVersion,
            $createSectionEnrollment->getOrgUnitId(),
            $createSectionEnrollment->getSectionId(),
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
                'json' => $createSectionEnrollment->toArray(),
            ],
        );

        try {
            $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }
    }

    public function getBrightspaceDataExportList(): Collection
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/lp/%s/dataExport/bds/list',
            $this->apiLpVersion,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        $brightspaceDataExportList = new Collection();

        foreach ($decodedResponse as $brightspaceDataExport) {
            $brightspaceDataExportList->add(
                new DataSetReportInfo(
                    $brightspaceDataExport['PluginId'],
                    $brightspaceDataExport['Name'],
                    $brightspaceDataExport['Description'],
                    (null !== $brightspaceDataExport['CreatedDate']) ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $brightspaceDataExport['CreatedDate']) : null,
                    $brightspaceDataExport['DownloadLink'],
                    $brightspaceDataExport['DownloadSize'],
                ),
            );
        }

        return $brightspaceDataExportList;
    }

    public function getBrightspaceDataExportItems(int $page = 1, int $pageSize = 100): PagedBrightspaceDataSetReportInfo
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/lp/%s/dataExport/bds',
            $this->apiLpVersion,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => array_merge(
                    $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
                    [
                        'page' => $page,
                        'pageSize' => $pageSize,
                    ],
                ),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        $brightspaceDataSetsCollection = new Collection();

        $brightspaceDataSets = $decodedResponse['BrightspaceDataSets'];

        foreach ($brightspaceDataSets as $brightspaceDataSet) {
            $brightspaceDataSetsCollection->add(
                new BrightspaceDataSetReportInfo(
                    $brightspaceDataSet['PluginId'],
                    $brightspaceDataSet['Name'],
                    $brightspaceDataSet['Description'],
                    $brightspaceDataSet['FullDataSet'],
                    (null !== $brightspaceDataSet['CreatedDate']) ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $brightspaceDataSet['CreatedDate']) : null,
                    $brightspaceDataSet['DownloadLink'],
                    $brightspaceDataSet['DownloadSize'],
                    $brightspaceDataSet['Version'],
                    $this->buildPreviousDataSets($brightspaceDataSet['PreviousDataSets']),
                    (null !== $brightspaceDataSet['QueuedForProcessingDate']) ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $brightspaceDataSet['QueuedForProcessingDate']) : null,
                ),
            );
        }

        return new PagedBrightspaceDataSetReportInfo(
            $brightspaceDataSetsCollection,
            $decodedResponse['NextPageUrl'],
            $decodedResponse['PrevPageUrl'],
        );
    }

    public function findBrightspaceDataExportItemByName(string $name): BrightspaceDataSetReportInfo|null
    {
        $pagedBrightspaceDataSetReportInfo = $this->getBrightspaceDataExportItems(1, 1000);

        return $pagedBrightspaceDataSetReportInfo->getBrightspaceDataSets()->first(function (BrightspaceDataSetReportInfo $item) use ($name): bool {
            return $item->getName() === $name;
        });
    }

    public function getQuizById(int $orgUnitId, int $quizId): Quiz
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/le/%s/%d/quizzes/%d',
            $this->apiLeVersion,
            $orgUnitId,
            $quizId,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        return $this->getQuizDtoFromQuizArrayResponse($decodedResponse);
    }

    public function quizzesList(int $orgUnitId, string|null $bookmark = null): QuizListPage
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/le/%s/%d/quizzes/',
            $this->apiLeVersion,
            $orgUnitId,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => array_merge(
                    $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
                    [
                        'bookmark' => $bookmark ?? '',
                    ],
                ),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        return new QuizListPage($decodedResponse['Next'], Collection::make($decodedResponse['Objects'] ?? [])->map(function (array $item): Quiz {
            return $this->getQuizDtoFromQuizArrayResponse($item);
        }));
    }

    public function getQuizzesForAnOrganizationUnit(int $orgUnitId): Collection
    {
        $result = new Collection();
        $bookmark = null;

        while (true) {
            try {
                $quizListPage = $this->quizzesList($orgUnitId, $bookmark);
            } catch (ApiException $exception) {
                break;
            }

            $result = $result->merge($quizListPage->getObjects());

            if (null === $quizListPage->getNextUrl()) {
                break;
            } else {
                $parts = parse_url($quizListPage->getNextUrl());
                parse_str($parts['query'], $query);
                $bookmark = $query['bookmark'];
            }
        }

        return $result;
    }

    public function quizQuestionsList(int $orgUnitId, int $quizId, string|null $bookmark = null): QuizQuestionListPage
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/le/%s/%d/quizzes/%d/questions/',
            $this->apiLeVersion,
            $orgUnitId,
            $quizId,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => array_merge(
                    $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
                    [
                        'bookmark' => $bookmark ?? '',
                    ],
                ),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        return new QuizQuestionListPage($decodedResponse['Next'], Collection::make($decodedResponse['Objects'] ?? [])->map(function (array $item): QuizQuestion {
            $quizQuestionType = QuizQuestionType::make($item['QuestionTypeId']);

            $questionInfo = match ($quizQuestionType->type()) {
                QuizQuestionType::MULTIPLE_CHOICE => $this->createMultipleChoiceQuestionInfo($item['QuestionInfo']),
                QuizQuestionType::TRUE_FALSE => $this->createTrueFalseQuestionInfo($item['QuestionInfo']),
                QuizQuestionType::FILL_IN_THE_BLANK => $this->createFillInTheBlankQuestionInfo($item['QuestionInfo']),
                QuizQuestionType::MULTI_SELECT => $this->createMultiSelectAnswerQuestionInfo($item['QuestionInfo']),
                QuizQuestionType::LONG_ANSWER => $this->createLongAnswerQuestionInfo($item['QuestionInfo']),
                QuizQuestionType::SHORT_ANSWER => $this->createShortAnswerQuestionInfo($item['QuestionInfo']),
                QuizQuestionType::LIKERT => $this->createLikertQuestionInfo($item['QuestionInfo']),
                QuizQuestionType::MULTI_SHORT_ANSWER => $this->createMultipleShortAnswerQuestionInfo($item['QuestionInfo']),
                default => null,
            };

            return new QuizQuestion(
                $item['QuestionId'],
                $quizQuestionType,
                $item['Name'],
                new RichText($item['QuestionText']['Text'], $item['QuestionText']['Html']),
                $item['Points'],
                $item['Difficulty'],
                $item['Bonus'],
                $item['Mandatory'],
                $item['Hint'] ? new RichText($item['Hint']['Text'], $item['Hint']['Html']) : null,
                $item['Feedback'] ? new RichText($item['Feedback']['Text'], $item['Feedback']['Html']) : null,
                CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $item['LastModified']),
                $item['LastModifiedBy'],
                $item['SectionId'],
                $item['QuestionTemplateId'],
                $item['QuestionTemplateVersionId'],
                $questionInfo,
            );
        }));
    }

    public function getQuizQuestionsForAQuiz(int $orgUnitId, int $quizId): Collection
    {
        $result = new Collection();
        $bookmark = null;

        while (true) {
            try {
                $quizQuestionListPage = $this->quizQuestionsList($orgUnitId, $quizId, $bookmark);
            } catch (ApiException $exception) {
                break;
            }

            $result = $result->merge($quizQuestionListPage->getObjects());

            if (null === $quizQuestionListPage->getNextUrl()) {
                break;
            } else {
                $parts = parse_url($quizQuestionListPage->getNextUrl());
                parse_str($parts['query'], $query);
                $bookmark = $query['bookmark'];
            }
        }

        return $result;
    }

    public function getEnrolledUsersForAnOrganizationUnit(int $orgUnitId): Collection
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/lp/%s/enrollments/orgUnits/%d/users/',
            $this->apiLpVersion,
            $orgUnitId,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        $collection = new Collection();

        foreach ($decodedResponse['Items'] as $item) {
            $user = new User(
                $item['User']['Identifier'],
                $item['User']['DisplayName'],
                $item['User']['EmailAddress'],
                $item['User']['OrgDefinedId'],
                $item['User']['ProfileBadgeUrl'],
                $item['User']['ProfileIdentifier'],
            );

            $roleInfo = new RoleInfo($item['Role']['Id'], $item['Role']['Name'], $item['Role']['Code']);
            $collection->add(new OrganizationUnitUser($user, $roleInfo));
        }

        return $collection;
    }

    public function updateGradeValueForUser(IncomingGradeValue $incomingGradeValue, int $orgUnitId, int $gradeObjectId, int $userId, string $bearerToken): void
    {
        $method = 'PUT';
        $path = sprintf('/d2l/api/le/%s/%d/grades/%d/values/%d', $this->apiLeVersion, $orgUnitId, $gradeObjectId, $userId);

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
                'json' => $incomingGradeValue->toArray(),
                'auth_bearer' => $bearerToken,
            ],
        );

        try {
            $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }
    }

    public function getOrganizationInfo(): OrganizationInfo
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/lp/%s/organization/info',
            $this->apiLpVersion,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        return new OrganizationInfo($decodedResponse['Identifier'], $decodedResponse['Name'], $decodedResponse['TimeZone']);
    }

    public function getOrganizationStructure(): Collection
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/lp/%s/orgstructure/',
            $this->apiLpVersion,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        $collection = new Collection();

        foreach ($decodedResponse['Items'] as $item) {
            $organizationUnitTypeInfo = new OrganizationUnitTypeInfo((int) $item['Type']['Id'], $item['Type']['Code'], $item['Type']['Name']);

            $collection->add(new OrgUnitProperties($item['Identifier'], $item['Name'], $organizationUnitTypeInfo, $item['Path'], $item['Code']));
        }

        return $collection;
    }

    public function getDescendentUnitsForAnOrganizationUnit(int $orgUnitId): Collection
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/lp/%s/orgstructure/%d/descendants/paged/',
            $this->apiLpVersion,
            $orgUnitId,
        );

        $collection = new Collection();

        $bookmark = null;

        while (true) {
            $response = $this->httpClient->request(
                $method,
                $path,
                [
                    'query' => array_merge(
                        $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
                        [
                            'bookmark' => $bookmark ?? '',
                        ],
                    ),
                ],
            );

            try {
                $body = $response->getContent();
            } catch (ExceptionInterface $exception) {
                throw ApiException::fromSymfonyHttpException($exception);
            }

            $decodedResponse = json_decode($body, true);

            foreach ($decodedResponse['Items'] as $item) {
                $organizationUnitTypeInfo = new OrganizationUnitTypeInfo((int) $item['Type']['Id'], $item['Type']['Code'], $item['Type']['Name']);

                $collection->add(new OrgUnit($item['Identifier'], $item['Name'], $organizationUnitTypeInfo, $item['Code']));
            }

            $bookmark = $decodedResponse['PagingInfo']['Bookmark'];

            if (false === $decodedResponse['PagingInfo']['HasMoreItems']) {
                break;
            }
        }

        return $collection;
    }

    public function getGradeCategoriesForAnOrganizationUnit(int $orgUnitId): Collection
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/le/%s/%d/grades/categories/',
            $this->apiLeVersion,
            $orgUnitId,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        $collection = new Collection();

        foreach ($decodedResponse as $item) {
            $grades = Collection::make($item['Grades'])->map(function (array $grade): GradeObject {
                return new GradeObject($grade['Name']);
            });

            $collection->add(
                new GradeObjectCategory(
                    $item['Id'],
                    $grades,
                    new GradeObjectCategoryData(
                        $item['Name'],
                        $item['ShortName'],
                        $item['CanExceedMax'],
                        $item['ExcludeFromFinalGrade'],
                        null !== $item['StartDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $item['StartDate']) : null,
                        null !== $item['EndDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $item['EndDate']) : null,
                        $item['Weight'],
                        $item['MaxPoints'],
                        $item['AutoPoints'],
                        $item['WeightDistributionType'],
                        $item['NumberOfHighestToDrop'],
                        $item['NumberOfLowestToDrop'],
                    ),
                ),
            );
        }

        return $collection;
    }

    public function getDataExportList(): Collection
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/lp/%s/dataExport/list',
            $this->apiLpVersion,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        $collection = new Collection();

        foreach ($decodedResponse as $item) {
            $filters = Collection::make($item['Filters'])->map(function (array $filter): DataSetFilter {
                return new DataSetFilter($filter['Name'], ExportFilterType::make($filter['Type']), $filter['Description'], $filter['DefaultValue']);
            });

            $collection->add(new DataSetData($item['DataSetId'], $item['Name'], $item['Description'], $item['Category'], $filters));
        }

        return $collection;
    }

    public function createDataExport(CreateExportJobData $createExportJobData): ExportJobData
    {
        $method = 'POST';
        $path = sprintf(
            '/d2l/api/lp/%s/dataExport/create',
            $this->apiLpVersion,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
                'json' => $createExportJobData->toArray(),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        return new ExportJobData(
            $decodedResponse['ExportJobId'],
            $decodedResponse['DataSetId'],
            $decodedResponse['Name'],
            CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $decodedResponse['SubmitDate']),
            new ExportJobStatus($decodedResponse['Status']),
            $decodedResponse['Category'],
        );
    }

    public function getRootModulesForAnOrganizationUnit(int $orgUnitId): Collection
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/le/%s/%d/content/root/',
            $this->apiLeVersion,
            $orgUnitId,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        $collection = new Collection();

        foreach ($decodedResponse as $item) {
            $collection->add($this->parseModuleItem($item));
        }

        return $collection;
    }

    public function getModuleStructureForAnOrganizationUnit(int $orgUnitId, int $moduleId): Collection
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/le/%s/%d/content/modules/%d/structure/',
            $this->apiLeVersion,
            $orgUnitId,
            $moduleId,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        $collection = new Collection();

        foreach ($decodedResponse as $item) {
            if (Type::make($item['Type'])->isModule()) {
                $collection->add($this->parseModuleItem($item));
            } else {
                $collection->add($this->parseTopicItem($item));
            }
        }

        return $collection;
    }

    public function updateContentTopicCompletion(ContentTopicCompletionUpdate $updateContentTopicCompletion, int $orgUnitId, int $topicId, int $userId): void
    {
        $method = 'PUT';
        $path = sprintf('/d2l/api/le/%s/%d/content/topics/%d/completions/users/%d', $this->apiLeVersion, $orgUnitId, $topicId, $userId);

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
                'json' => $updateContentTopicCompletion->toArray(),
            ],
        );

        try {
            $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }
    }

    public function getSectionsForOrganizationUnit(int $orgUnitId): Collection
    {
        $method = 'GET';
        $path = sprintf(
            '/d2l/api/lp/%s/%d/sections/',
            $this->apiLpVersion,
            $orgUnitId,
        );

        $response = $this->httpClient->request(
            $method,
            $path,
            [
                'query' => $this->authenticatedUriFactory->getQueryParametersAsArray($method, $path),
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        $decodedResponse = json_decode($body, true);

        $collection = new Collection();

        foreach ($decodedResponse as $item) {
            $collection->add($this->parseSectionItem($item));
        }

        return $collection;
    }

    public function generateExpiringGuid(string $orgDefinedId): Guid
    {
        $response = $this->httpClient->request(
            'POST',
            '/d2l/guids/D2L.Guid.2.asmx/GenerateExpiringGuid',
            [
                'body' => [
                    'guidType'    => 'SSO',
                    'orgId'       => $this->orgId,
                    'installCode' => $this->installationCode,
                    'TTL'         => 90,
                    'data'        => $orgDefinedId,
                    'key'         => $this->pKey,
                ],
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        set_error_handler(function (
            int $errorLevel,
            string $errorMessage,
            string|null $errorFilename = null,
            int|null $errorLine = null,
            array|null $errorContext = null,
        ) {
            throw new \Exception($errorMessage, $errorLevel);
        });

        try {
            $xml = new SimpleXMLElement($body);
        } catch (Throwable $e) {
            throw new ApiException(sprintf('Invalid response - "%s" given', $body), $e->getCode(), $e);
        } finally {
            restore_error_handler();
        }

        try {
            $data = json_decode(json_encode($xml), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new ApiException(sprintf('Invalid response - "%s" given', $body), $e->getCode(), $e);
        }

        if (isset($data[0]) && is_string($data[0])) {
            return new Guid($data[0]);
        }

        throw new ApiException(sprintf('Invalid API response received. The response was "%s"', $body));
    }

    public function validateGuid(Guid $guid, string $orgDefinedId): bool
    {
        $response = $this->httpClient->request(
            'POST',
            '/d2l/guids/D2L.Guid.2.asmx/ValidateGuid',
            [
                'body' => [
                    'guid' => $guid->getValue(),
                    'guidType'    => 'SSO',
                    'orgId'       => $this->orgId,
                    'installCode' => $this->installationCode,
                    'TTL'         => 90,
                    'data'        => $orgDefinedId,
                    'key'         => $this->pKey,
                ],
            ],
        );

        try {
            $body = $response->getContent();
        } catch (ExceptionInterface $exception) {
            throw ApiException::fromSymfonyHttpException($exception);
        }

        set_error_handler(function (
            int $errorLevel,
            string $errorMessage,
            string|null $errorFilename = null,
            int|null $errorLine = null,
            array|null $errorContext = null,
        ) {
            throw new \Exception($errorMessage, $errorLevel);
        });

        try {
            $xml = new SimpleXMLElement($body);
        } catch (Throwable $e) {
            throw new ApiException(sprintf('Invalid response - "%s" given', $body), $e->getCode(), $e);
        } finally {
            restore_error_handler();
        }

        try {
            $data = json_decode(json_encode($xml), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new ApiException(sprintf('Invalid response - "%s" given', $body), $e->getCode(), $e);
        }

        if (isset($data[0]) && ClientInterface::GUID_OK === $data[0]) {
            return true;
        }

        return false;
    }

    private function getSingleUserDtoFromMultipleUserArrayResponse(array $decodedResponse): UserData
    {
        return $this->getUserDtoFromUserArrayResponse($decodedResponse[0]);
    }

    private function getUserDtoFromUserArrayResponse(array $decodedResponse): UserData
    {
        return new UserData(
            $decodedResponse['OrgId'],
            $decodedResponse['UserId'],
            $decodedResponse['FirstName'],
            $decodedResponse['MiddleName'],
            $decodedResponse['LastName'],
            $decodedResponse['UserName'],
            $decodedResponse['ExternalEmail'],
            $decodedResponse['OrgDefinedId'],
            $decodedResponse['UniqueIdentifier'],
            $decodedResponse['Activation']['IsActive'],
            null !== $decodedResponse['LastAccessedDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $decodedResponse['LastAccessedDate']) : null,
        );
    }

    /**
     * @return Collection<BrightspaceDataSetReportInfo>|null
     */
    private function buildPreviousDataSets(array|null $previousDataSets = null): Collection|null
    {
        if (null === $previousDataSets) {
            return null;
        }

        $previousDatasets = [];

        foreach ($previousDataSets as $previousDataSet) {
            $previousDatasets[] = new BrightspaceDataSetReportInfo(
                $previousDataSet['PluginId'],
                $previousDataSet['Name'],
                $previousDataSet['Description'],
                $previousDataSet['FullDataSet'],
                (null !== $previousDataSet['CreatedDate']) ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $previousDataSet['CreatedDate']) : null,
                $previousDataSet['DownloadLink'],
                $previousDataSet['DownloadSize'],
                $previousDataSet['Version'],
                //make a recursive call for deeply nested elements, if any
                $this->buildPreviousDataSets($previousDataSet['PreviousDataSets']),
                (null !== $previousDataSet['QueuedForProcessingDate']) ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $previousDataSet['QueuedForProcessingDate']) : null,
            );
        }

        return new Collection($previousDatasets);
    }

    private function parseModuleItem(array $item): Module
    {
        $structure = new Collection();

        foreach ($item['Structure'] as $structureItem) {
            $structure->add(
                new Structure(
                    $structureItem['Id'],
                    $structureItem['Title'],
                    $structureItem['ShortTitle'],
                    Type::make($structureItem['Type']),
                    null !== $structureItem['LastModifiedDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $structureItem['LastModifiedDate']) : null,
                ),
            );
        }

        return new Module(
            $item['Id'],
            $structure,
            null !== $item['ModuleStartDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $item['ModuleStartDate']) : null,
            null !== $item['ModuleEndDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $item['ModuleEndDate']) : null,
            null !== $item['ModuleDueDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $item['ModuleDueDate']) : null,
            $item['IsHidden'],
            $item['IsLocked'],
            $item['Title'],
            $item['ShortTitle'],
            null !== $item['Description'] ? new RichText($item['Description']['Text'], $item['Description']['Html']) : null,
            $item['ParentModuleId'],
            null !== $item['LastModifiedDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $item['LastModifiedDate']) : null,
        );
    }

    private function parseTopicItem(array $item): Topic
    {
        return new Topic(
            $item['Id'],
            TopicType::make($item['TopicType']),
            $item['Url'],
            null !== $item['StartDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $item['StartDate']) : null,
            null !== $item['EndDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $item['EndDate']) : null,
            null !== $item['DueDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $item['DueDate']) : null,
            $item['IsHidden'],
            $item['IsLocked'],
            $item['OpenAsExternalResource'],
            $item['Title'],
            $item['ShortTitle'],
            null !== $item['Description'] ? new RichText($item['Description']['Text'], $item['Description']['Html']) : null,
            $item['ParentModuleId'],
            $item['ActivityId'],
            $item['IsExempt'],
            $item['ToolId'],
            $item['ToolItemId'],
            ActivityType::make($item['ActivityType']),
            $item['GradeItemId'],
            null !== $item['LastModifiedDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $item['LastModifiedDate']) : null,
        );
    }

    private function getQuizDtoFromQuizArrayResponse(array $decodedResponse): Quiz
    {
        $restrictIPAddressRangeCollection = new Collection();

        foreach ($decodedResponse['RestrictIPAddressRange'] as $restrictIPAddressRangeItem) {
            $restrictIPAddressRangeCollection->add(new RestrictIPAddressRange($restrictIPAddressRangeItem['IPRangeStart'], $restrictIPAddressRangeItem['IPRangeEnd']));
        }

        return new Quiz(
            $decodedResponse['QuizId'],
            $decodedResponse['Name'],
            $decodedResponse['IsActive'],
            $decodedResponse['SortOrder'],
            $decodedResponse['AutoExportToGrades'],
            $decodedResponse['GradeItemId'],
            $decodedResponse['IsAutoSetGraded'],
            new Instructions(new RichText($decodedResponse['Instructions']['Text']['Text'], $decodedResponse['Instructions']['Text']['Html']), $decodedResponse['Instructions']['IsDisplayed']),
            new Description(new RichText($decodedResponse['Description']['Text']['Text'], $decodedResponse['Description']['Text']['Html']), $decodedResponse['Description']['IsDisplayed']),
            null !== $decodedResponse['StartDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $decodedResponse['StartDate']) : null,
            null !== $decodedResponse['EndDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $decodedResponse['EndDate']) : null,
            null !== $decodedResponse['DueDate'] ? CarbonImmutable::createFromFormat(ClientInterface::D2L_DATETIME_FORMAT, $decodedResponse['DueDate']) : null,
            $decodedResponse['DisplayInCalendar'],
            new AttemptsAllowed($decodedResponse['AttemptsAllowed']['IsUnlimited'], $decodedResponse['AttemptsAllowed']['NumberOfAttemptsAllowed']),
            new LateSubmissionInfo(LateSubmissionOption::make($decodedResponse['LateSubmissionInfo']['LateSubmissionOption']), $decodedResponse['LateSubmissionInfo']['LateLimitMinutes']),
            new SubmissionTimeLimit($decodedResponse['SubmissionTimeLimit']['IsEnforced'], $decodedResponse['SubmissionTimeLimit']['ShowClock'], $decodedResponse['SubmissionTimeLimit']['TimeLimitValue']),
            $decodedResponse['SubmissionGracePeriod'],
            $decodedResponse['Password'],
            new Header(new RichText($decodedResponse['Header']['Text']['Text'], $decodedResponse['Header']['Text']['Html']), $decodedResponse['Header']['IsDisplayed']),
            new Footer(new RichText($decodedResponse['Footer']['Text']['Text'], $decodedResponse['Footer']['Text']['Html']), $decodedResponse['Footer']['IsDisplayed']),
            $decodedResponse['AllowHints'],
            $decodedResponse['DisableRightClick'],
            $decodedResponse['DisablePagerAndAlerts'],
            $decodedResponse['NotificationEmail'],
            OverallGradeCalculationOption::make($decodedResponse['CalcTypeId']),
            $restrictIPAddressRangeCollection,
            $decodedResponse['CategoryId'],
            $decodedResponse['PreventMovingBackwards'],
            $decodedResponse['Shuffle'],
            $decodedResponse['ActivityId'],
            $decodedResponse['AllowOnlyUsersWithSpecialAccess'],
            $decodedResponse['IsRetakeIncorrectOnly'],
        );
    }

    private function createMultipleChoiceQuestionInfo(array $questionInfo): MultipleChoiceAnswers
    {
        $answers = new Collection();

        foreach ($questionInfo['Answers'] as $answer) {
            $answers->add(
                new MultipleChoiceAnswer(
                    $answer['PartId'],
                    new RichText($answer['Answer']['Text'], $answer['Answer']['Html']),
                    new RichText($answer['AnswerFeedback']['Text'], $answer['AnswerFeedback']['Html']),
                    $answer['Weight'],
                ),
            );
        }

        return new MultipleChoiceAnswers(
            $answers,
            $questionInfo['Randomize'],
            EnumerationType::make($questionInfo['Enumeration']),
        );
    }

    private function createTrueFalseQuestionInfo(array $questionInfo): TrueFalse
    {
        return new TrueFalse(
            $questionInfo['TruePartId'],
            $questionInfo['TrueWeight'],
            new RichText($questionInfo['TrueFeedback']['Text'], $questionInfo['TrueFeedback']['Html']),
            $questionInfo['FalsePartId'],
            $questionInfo['FalseWeight'],
            new RichText($questionInfo['FalseFeedback']['Text'], $questionInfo['FalseFeedback']['Html']),
            EnumerationType::make($questionInfo['Enumeration']),
        );
    }

    private function createFillInTheBlankQuestionInfo(array $questionInfo): FillInTheBlank
    {
        $texts = new Collection();

        foreach ($questionInfo['Texts'] as $text) {
            $texts->add(
                new FillInTheBlankText(
                    new RichText($text['Text']['Text'], $text['Text']['Html']),
                ),
            );
        }

        $blanks = new Collection();

        foreach ($questionInfo['Blanks'] as $blank) {
            $answers = new Collection();

            foreach ($blank['Answers'] as $answer) {
                $answers->add(
                    new FillInTheBlankAnswer(
                        $answer['TextAnswer'],
                        $answer['Weight'],
                        EvaluationType::make($answer['EvaluationType']),
                    ),
                );
            }

            $blanks->add(
                new FillInTheBlankBlank(
                    $blank['PartId'],
                    $blank['Size'],
                    $answers,
                ),
            );
        }

        return new FillInTheBlank(
            $texts,
            $blanks,
        );
    }

    private function createMultiSelectAnswerQuestionInfo(array $questionInfo): MultiSelectAnswers
    {
        $answers = new Collection();

        foreach ($questionInfo['Answers'] as $answer) {
            $answers->add(
                new MultiSelectAnswer(
                    $answer['PartId'],
                    new RichText($answer['Answer']['Text'], $answer['Answer']['Html']),
                    new RichText($answer['AnswerFeedback']['Text'], $answer['AnswerFeedback']['Html']),
                    $answer['IsCorrect'],
                ),
            );
        }

        return new MultiSelectAnswers(
            $answers,
            $questionInfo['Randomize'],
            EnumerationType::make($questionInfo['Enumeration']),
            QuestionStyle::make($questionInfo['Style']),
            QuestionGradingRule::make($questionInfo['GradingType']),
        );
    }

    private function createLongAnswerQuestionInfo(array $questionInfo): LongAnswer
    {
        return new LongAnswer(
            $questionInfo['PartId'],
            $questionInfo['EnableStudentEditor'],
            new RichText($questionInfo['InitialText']['Text'], $questionInfo['InitialText']['Html']),
            new RichText($questionInfo['AnswerKey']['Text'], $questionInfo['AnswerKey']['Html']),
            $questionInfo['EnableAttachments'],
        );
    }

    private function createShortAnswerQuestionInfo(array $questionInfo): ShortAnswers
    {
        $blanks = new Collection();

        foreach ($questionInfo['Blanks'] as $blank) {
            $shortAnswerBlankAnswers = new Collection();

            foreach ($blank['Answers'] as $answer) {
                $shortAnswerBlankAnswers->add(
                    new ShortAnswerBlankAnswer(
                        $answer['Text'],
                        $answer['Weight'],
                    ),
                );
            }

            $blanks->add(
                new ShortAnswerBlank(
                    $blank['PartId'],
                    $shortAnswerBlankAnswers,
                    EvaluationType::make($blank['EvaluationType']),
                ),
            );
        }

        return new ShortAnswers(
            $blanks,
            QuestionGradingRule::make($questionInfo['GradingType']),
        );
    }

    private function createLikertQuestionInfo(array $questionInfo): Likert
    {
        $statements = new Collection();

        foreach ($questionInfo['Statements'] as $statement) {
            $statements->add(
                new LikertStatement(
                    $statement['PartId'],
                    new RichText($statement['Statement']['Text'], $statement['Statement']['Html']),
                ),
            );
        }

        return new Likert(
            QuestionScaleOption::make($questionInfo['Scale']),
            $questionInfo['NaOption'],
            $statements,
        );
    }

    private function createMultipleShortAnswerQuestionInfo(array $questionInfo): MultipleShortAnswers
    {
        $partIds = Collection::make($questionInfo['PartIds']);

        $answers = new Collection();

        foreach ($questionInfo['Answers'] as $answer) {
            $answers->add(
                new MultipleShortAnswer(
                    $answer['AnswerText'],
                    $answer['Weight'],
                    EvaluationType::make($answer['EvaluationType']),
                ),
            );
        }

        return new MultipleShortAnswers(
            $partIds,
            $questionInfo['Boxes'],
            $questionInfo['Rows'],
            $questionInfo['Columns'],
            $answers,
        );
    }

    private function parseSectionItem(array $item): Section
    {
        return new Section(
            $item['SectionId'],
            $item['Name'],
            $item['Code'],
            new RichText($item['Description']['Text'], $item['Description']['Html']),
            $item['Enrollments'],
        );
    }
}
