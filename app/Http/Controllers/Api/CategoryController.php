<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;


class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $categories = Category::orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Listado de categorías',
            'data' => CategoryResource::collection($categories)
        ]);
    }

    // public function store(CategoryRequest $request)
    // {
    //     $category = Category::create($request->validated());

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Categoría creada exitosamente',
    //         'data' => new CategoryResource($category)
    //     ], 201);
    // }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'data' => $category
        ], 201);
    }

    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detalle de la categoría',
            'data' => new CategoryResource($category)
        ]);
    }

    // public function update(CategoryRequest $request, Category $category)
    // {
    //     $category->update($request->validated());

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Categoría actualizada exitosamente',
    //         'data' => new CategoryResource($category)
    //     ]);
    // }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente',
            'data' => $category
        ], 200);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente'
        ], 200);
    }
}
