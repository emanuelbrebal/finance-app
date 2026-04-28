<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreCategoryRequest;
use App\Http\Requests\V1\UpdateCategoryRequest;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;
use App\Services\DefaultCategoriesService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = $request->user()->categories();

        if ($kind = $request->query('kind')) {
            abort_unless(in_array($kind, ['income', 'expense'], true), 422);
            $query->ofKind($kind);
        }

        $archived = $request->boolean('archived');
        $query = $archived ? $query->archived() : $query->active();

        return CategoryResource::collection($query->orderBy('kind')->orderBy('name')->get());
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $request->user()->categories()->create($request->validated());

        return CategoryResource::make($category)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Category $category)
    {
        $this->authorize('view', $category);

        return CategoryResource::make($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorize('update', $category);

        $category->update($request->validated());

        return CategoryResource::make($category);
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        $category->update(['archived_at' => now()]);

        return response()->noContent();
    }

    public function seed(Request $request, DefaultCategoriesService $service)
    {
        $created = $service->seedFor($request->user());

        return response()->json([
            'data' => [
                'created' => $created,
            ],
        ]);
    }
}
