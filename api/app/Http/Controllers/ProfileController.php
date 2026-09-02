<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\StoreProfileRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Services\ExportService;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
        private readonly ExportService $exportService,
    ) {}

    public function index(Request $request)
    {
        return ProfileResource::collection(
            $this->profileService->paginate((int) $request->query('per_page', 15))
        );
    }

    public function store(StoreProfileRequest $request): ProfileResource
    {
        return new ProfileResource($this->profileService->create($request->validated()));
    }

    public function show(string $profile): ProfileResource
    {
        return new ProfileResource($this->profileService->find($profile));
    }

    public function update(UpdateProfileRequest $request, string $profile): ProfileResource
    {
        $model = $this->profileService->find($profile);

        return new ProfileResource(
            $this->profileService->update($model, $request->validated())
        );
    }

    public function destroy(string $profile): JsonResponse
    {
        $this->profileService->delete($this->profileService->find($profile));

        return response()->json(['message' => 'Perfil eliminado.']);
    }

    public function export(string $format): Response
    {
        return $this->exportService->profiles($format);
    }
}
