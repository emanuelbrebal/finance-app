<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ConfirmImportRequest;
use App\Http\Requests\V1\StoreImportRequest;
use App\Http\Resources\V1\ImportBatchResource;
use App\Models\ImportBatch;
use App\Services\ImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ImportController extends Controller
{
    public function __construct(private readonly ImportService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $batches = ImportBatch::where('user_id', $request->user()->id)
            ->with('account')
            ->latest()
            ->paginate(25);

        return ImportBatchResource::collection($batches);
    }

    public function store(StoreImportRequest $request): JsonResponse
    {
        $batch = $this->service->upload(
            file: $request->file('file'),
            accountId: $request->integer('account_id'),
            userId: $request->user()->id,
            importerHint: $request->input('importer'),
            mapping: $request->input('mapping'),
        );

        return response()->json([
            'data' => new ImportBatchResource($batch),
            'preview_url' => "/api/v1/imports/{$batch->id}/preview",
        ], 202);
    }

    public function show(Request $request, ImportBatch $import): ImportBatchResource
    {
        $this->authorizeOwner($request, $import);

        return new ImportBatchResource($import->load('account'));
    }

    public function preview(Request $request, ImportBatch $import): JsonResponse
    {
        $this->authorizeOwner($request, $import);

        if (!$import->isPreviewReady()) {
            return response()->json([
                'status' => $import->status,
                'message' => $import->status === 'failed'
                    ? $import->error_message
                    : 'Import is still being processed.',
            ], $import->status === 'failed' ? 422 : 202);
        }

        return response()->json([
            'data' => new ImportBatchResource($import->load('account')),
            'rows' => $import->preview_payload,
        ]);
    }

    public function confirm(ConfirmImportRequest $request, ImportBatch $import): JsonResponse
    {
        $this->authorizeOwner($request, $import);

        $result = $this->service->confirm($import, $request->input('overrides', []));

        return response()->json([
            'data' => new ImportBatchResource($import->fresh()->load('account')),
            'imported' => $result['imported'],
            'skipped' => $result['skipped'],
        ]);
    }

    public function revert(Request $request, ImportBatch $import): JsonResponse
    {
        $this->authorizeOwner($request, $import);

        $this->service->revert($import);

        return response()->json([
            'data' => new ImportBatchResource($import->fresh()->load('account')),
        ]);
    }

    private function authorizeOwner(Request $request, ImportBatch $import): void
    {
        abort_if($import->user_id !== $request->user()->id, 403);
    }
}
