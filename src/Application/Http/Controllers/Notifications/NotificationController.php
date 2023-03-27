<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Responses\Notifications\DisplayNotificationResponse;
use App\Http\Responses\Notifications\NonDisplayedNotificationsCountResponse;
use App\Http\Responses\Notifications\NotificationListResponse;
use App\Http\Responses\Notifications\ReadNotificationResponse;
use Domains\Accounts\Models\Company\CompanyAccount;
use Domains\Accounts\Models\Company\CompanyAccountId;
use Domains\Accounts\Models\Company\UserCompanyAccount;
use Domains\Accounts\Models\User\User;
use Domains\Notifications\Models\List\NotificationList;
use Domains\Notifications\Models\List\NotificationListFilters;
use Domains\Notifications\Repositories\NotificationRepositoryContract;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;
use Ramsey\Uuid\Uuid;

class NotificationController extends Controller
{
    public function __construct(private NotificationRepositoryContract $notificationsRepository)
    {
    }

    /**
     * List notifications
     *
     * @param Request $request
     * @return NotificationListResponse
     * @throws ValidationException
     * @throws \Exception
     *
     * @OA\Get (
     *     tags={"Notification"},
     *     path="/api/notifications",
     *     @OA\Header(
     *       header="Authorization",
     *       required=true,
     *       @OA\Schema(
     *           type="string"
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="company_account_id",
     *       in="query",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *       name="date_from",
     *       in="query",
     *       required=false,
     *       @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *       name="page",
     *       in="query",
     *       required=false,
     *       @OA\Schema(type="integer")
     *     ),
     *      @OA\Parameter(
     *        name="limit",
     *        in="query",
     *        required=false,
     *        @OA\Schema(type="integer")
     *     ),
     *      @OA\Parameter(
     *        name="exclude_displayed",
     *        in="query",
     *        required=false,
     *        @OA\Schema(
     *          type="boolean",
     *          default="false"
     *        )
     *     ),
     *      @OA\Parameter(
     *        name="exclude_read",
     *        in="query",
     *        required=false,
     *        @OA\Schema(
     *          type="boolean",
     *          default="false"
     *        )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Notifications list",
     *         @OA\JsonContent(ref="#/components/schemas/NotificationList")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication failed."
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Error: Action forbidden. Logged user doesn't have permission to perform requested action."
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Model required to perform action not found."
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function list(Request $request): NotificationListResponse
    {
        $this->validate($request, [
            'company_account_id' => ['required', 'string'],
            'date_from' => ['int'],
            'page' => ['int'],
            'limit' => ['int'],
            'exclude_displayed' => ['prohibited_unless:exclude_read,null', 'boolean'],
            'exclude_read' => ['prohibited_unless:exclude_displayed,null', 'boolean'],
        ]);

        $filters = new NotificationListFilters();

        $filters
            ->setDateFrom($request->get('date_from'))
            ->setPage($request->get('page', 1))
            ->setPerPage($request->get('limit', 10))
            ->setExcludeDisplayed((bool)$request->get('exclude_displayed'))
            ->setExcludeRead((bool)$request->get('exclude_read'));

        $userCompanyAccount = $this->getUserCompanyAccount($request);
        $list = NotificationList::userCompanyAccount(
            $this->notificationsRepository,
            $userCompanyAccount,
            $filters
        );

        return new NotificationListResponse($list);
    }

    /**
     * Number of non-displayed notifications
     *
     * @param Request $request
     * @return NonDisplayedNotificationsCountResponse
     * @throws ValidationException
     * @throws \Exception
     *
     * @OA\Get (
     *     tags={"Notification"},
     *     path="/api/notifications/non-displayed",
     *     @OA\Header(
     *       header="Authorization",
     *       required=true,
     *       @OA\Schema(
     *           type="string"
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="company_account_id",
     *       in="query",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *       name="date_from",
     *       in="query",
     *       required=false,
     *       @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Number of non-displayed notifications",
     *         @OA\JsonContent(ref="#/components/schemas/NonDisplayedCount")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication failed."
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Error: Action forbidden. Logged user doesn't have permission to perform requested action."
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Model required to perform action not found."
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function nonDisplayedCount(Request $request): NonDisplayedNotificationsCountResponse
    {
        $this->validate($request, [
            'company_account_id' => ['required', 'string'],
            'date_from' => ['int'],
        ]);

        $userCompanyAccount = $this->getUserCompanyAccount($request);
        $dateFrom = $request->get('date_from');
        if (!$dateFrom) {
            $dateFrom = (new \DateTimeImmutable(date('Y-m-d 00:00:00')))->getTimestamp() - (30*24*60*60);
        }
        $dateFrom = new \DateTimeImmutable('@' . $dateFrom);

        $nonDisplayedCount = $this->notificationsRepository->countNonDisplayed($userCompanyAccount, $dateFrom);

        return new NonDisplayedNotificationsCountResponse($nonDisplayedCount);
    }

    /**
     * Mark notifications as displayed
     *
     * @param Request $request
     * @return DisplayNotificationResponse
     * @throws ValidationException
     *
     * @OA\Put (
     *     tags={"Notification"},
     *     path="/api/notifications/display",
     *     @OA\Header(
     *       header="Authorization",
     *       required=true,
     *       @OA\Schema(
     *           type="string"
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="company_account_id",
     *       in="query",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *       name="ids",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *         type="array",
     *         @OA\Items(
     *           type="string",
     *         )
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="older_than",
     *       in="query",
     *       required=false,
     *       @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication failed."
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Error: Action forbidden. Logged user doesn't have permission to perform requested action."
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Model required to perform action not found."
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function display(Request $request): DisplayNotificationResponse
    {
        $this->validate($request, [
            'company_account_id' => ['required', 'string'],
            'ids' => ['prohibited_unless:older_than,null', 'required_without:older_than', 'array'],
            'older_than' => ['prohibited_unless:ids,null', 'required_without:ids', 'int'],
        ]);

        $ids = $request->get('ids');
        $olderThanTimestamp = $request->get('older_than');
        $userCompanyAccount = $this->getUserCompanyAccount($request);
        $notifications = [];

        if ($ids) {
            $filteredIds = array_filter($ids, static fn(string $id) => Uuid::isValid($id));
            $notifications = $this->notificationsRepository->findNonDisplayedByIds($userCompanyAccount, $filteredIds);
        }

        if ($olderThanTimestamp) {
            $olderThan = new \DateTimeImmutable('@' . $olderThanTimestamp);
            $notifications = $this->notificationsRepository->findNonDisplayedOlderThan($userCompanyAccount, $olderThan);
        }

        foreach ($notifications as $notification) {
            $notification->display($this->notificationsRepository);
        }

        if (!empty($notifications)) {
            $this->notificationsRepository->flush();
        }

        return new DisplayNotificationResponse();
    }

    /**
     * Mark notifications as read
     *
     * @param Request $request
     * @return ReadNotificationResponse
     * @throws ValidationException
     * @throws \Exception
     *
     * @OA\Put (
     *     tags={"Notification"},
     *     path="/api/notifications/read",
     *     @OA\Header(
     *       header="Authorization",
     *       required=true,
     *       @OA\Schema(
     *           type="string"
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="company_account_id",
     *       in="query",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *       name="ids",
     *       in="query",
     *       required=false,
     *       @OA\Schema(
     *         type="array",
     *         @OA\Items(
     *           type="string",
     *         )
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="older_than",
     *       in="query",
     *       required=false,
     *       @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Authentication failed."
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Error: Action forbidden. Logged user doesn't have permission to perform requested action."
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Model required to perform action not found."
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function read(Request $request): ReadNotificationResponse
    {
        $this->validate($request, [
            'company_account_id' => ['required', 'string'],
            'older_than' => ['prohibited_unless:ids,null', 'required_without:ids', 'int'],
            'ids' => ['prohibited_unless:older_than,null', 'required_without:older_than', 'array'],
        ]);

        $ids = $request->get('ids');
        $olderThanTimestamp = $request->get('older_than');
        $userCompanyAccount = $this->getUserCompanyAccount($request);
        $notifications = [];

        if ($ids) {
            $filteredIds = array_filter($ids, static fn(string $id) => Uuid::isValid($id));
            $notifications = $this->notificationsRepository->findUnreadByIds($userCompanyAccount, $filteredIds);
        }

        if ($olderThanTimestamp) {
            $olderThan = new \DateTimeImmutable('@' . $olderThanTimestamp);
            $notifications = $this->notificationsRepository->findUnreadOlderThan($userCompanyAccount, $olderThan);
        }

        foreach ($notifications as $notification) {
            $notification->read($this->notificationsRepository);
        }

        if (!empty($notifications)) {
            $this->notificationsRepository->flush();
        }

        return new ReadNotificationResponse();
    }

    /**
     * @param Request $request
     * @return UserCompanyAccount
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    private function getUserCompanyAccount(Request $request): UserCompanyAccount
    {
        $this->validate($request, [
            'company_account_id' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $companyAccountId = $request->get('company_account_id');
        $userCompanyAccount = $user->userCompanyAccount(new CompanyAccountId($companyAccountId));
        $this->requireModel($userCompanyAccount, CompanyAccount::class, [$companyAccountId]);

        return $userCompanyAccount;
    }
}
