<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\ExportService;
use App\Services\PhotoStorageService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ExportService $exportService,
        private readonly PhotoStorageService $photoStorage,
    ) {}

    public function index(Request $request)
    {
        return UserResource::collection(
            $this->userService->paginate((int) $request->query('per_page', 15))
        );
    }

    public function store(StoreUserRequest $request): UserResource
    {
        $data = $request->safe()->except('photo');
        $user = $this->userService->create($data, $request->file('photo'));

        return new UserResource($user);
    }

    public function show(string $user): UserResource
    {
        return new UserResource($this->userService->find($user));
    }

    public function update(UpdateUserRequest $request, string $user): UserResource
    {
        $model = $this->userService->find($user);
        $data = $request->safe()->except('photo');

        return new UserResource(
            $this->userService->update($model, $data, $request->file('photo'))
        );
    }

    public function destroy(string $user): JsonResponse
    {
        $this->userService->delete($this->userService->find($user));

        return response()->json(['message' => 'Usuario eliminado.']);
    }

    public function photo(string $user): Response
    {
        $model = $this->userService->find($user);

        if ($model->photo_file_id === null) {
            abort(404);
        }

        return $this->photoStorage->stream($model->photo_file_id);
    }

    public function export(string $format): Response
    {
        return $this->exportService->users($format);
    }
}
