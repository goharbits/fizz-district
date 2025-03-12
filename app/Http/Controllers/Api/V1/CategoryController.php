<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidatorHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\V1\BaseController;

class CategoryController extends BaseController
{
    // Get all categories with their item group and subcategories, including pagination
    public function getAll(Request $request)
    {
        $perPage = $request->input('per_page', 100); // Default to 10 items per page
        $categories = Category::with(['itemGroup', 'subCategories'])
                        ->orderBy('order_no','asc')
                        ->whereNull('parent_category_id')
                        ->paginate($perPage);

        return ResponseHelper::formatResponse(true, $categories, 'Categories retrieved successfully.');
    }

    // Get specific category by ID, including its item group and subcategories
    public function getSpecificRecord($id)
    {
        $category = Category::with(['itemGroup', 'subCategories'])
        ->whereNull('parent_category_id')
        ->find($id);

        if (!$category) {
            return ResponseHelper::formatResponse(false, [], 'Category not found.');
        }

        return ResponseHelper::formatResponse(true, $category, 'Category retrieved successfully.');
    }

    // Add a new category with validation
    public function add(Request $request)
    {
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'item_group_id' => 'required|exists:item_groups,id',
            'has_subcategories' => 'required|boolean',
        ];

        // Validate request
        $validation = ValidatorHelper::validate($request->all(), $rules);

        if (!$validation['success']) {
            return ResponseHelper::formatResponse(false, [], $validation['errors']);
        }

        DB::beginTransaction();
        try{
            $response = $this->cloverService->createCategoryOnClover($request);
            // Create the category

            if (!$response->getData()->success) {
                DB::rollBack();
                return ResponseHelper::formatResponse(false, [], $response->getData()->msg);
            }

            $cloverCatId = $response->getData()->data->id;

            $category = Category::create([
                'name' => $request->input('name'),
                'item_group_id' => $request->input('item_group_id'),
                'clover_category_id'=> $cloverCatId,
                'has_subcategories' => $request->input('has_subcategories'),
            ]);
            DB::commit();
            return ResponseHelper::formatResponse(true, $category, 'Category created successfully.');

        } catch (\Exception $e) {
                // Rollback the transaction in case of error
                DB::rollBack();
                return ResponseHelper::formatResponse(false, [], $e->getMessage());
        }
    }

    // Get all subcategories for a specific category
    public function getSubCategories($categoryId)
    {
        $subCategories = Category::where('parent_category_id', $categoryId)->get();

        if ($subCategories->isEmpty()) {
            return ResponseHelper::formatResponse(false, [], 'No subcategories found for this category.');
        }

        return ResponseHelper::formatResponse(true, $subCategories, 'Subcategories retrieved successfully.');
    }


    // Get all subcategories with pagination
    public function getAllSubCategories(Request $request)
    {
        $perPage = $request->input('per_page', 100); // Default to 10 items per page
        $subCategories = Category::with('parentCategory')
                            ->whereNotNull('parent_category_id')
                            ->paginate($perPage);

        return ResponseHelper::formatResponse(true, $subCategories, 'Subcategories retrieved successfully.');
    }

    // Get specific subcategory by ID
    public function getSpecificSubCategory($id)
    {
        $subCategory = Category::with('parentCategory')->whereNotNull('parent_category_id')->find($id);

        if (!$subCategory) {
            return ResponseHelper::formatResponse(false, [], 'Subcategory not found.');
        }

        return ResponseHelper::formatResponse(true, $subCategory, 'Subcategory retrieved successfully.');
    }

    // Add a new subcategory with validation
    public function addSubCategory(Request $request)
    {
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'parent_category_id' => 'required|exists:categories,id',
        ];

        // Validate request
        $validation = ValidatorHelper::validate($request->all(), $rules);

        if (!$validation['success']) {
            return ResponseHelper::formatResponse(false, [], $validation['errors']);
        }

        // Get the parent category to retrieve the item_group_id
        $parentCategory = Category::find($request->input('parent_category_id'));

        // Create the subcategory with `has_subcategories` set to false
        $subCategory = Category::create([
            'name' => $request->input('name'),
            'item_group_id' => $parentCategory->item_group_id, // Set to the same item_group_id as the parent category
            'parent_category_id' => $request->input('parent_category_id'),
            'has_subcategories' => false, // Automatically set to false for subcategories
        ]);

        return ResponseHelper::formatResponse(true, $subCategory, 'Subcategory created successfully.');
    }

    // Get all categories by item group ID
    public function getCategoriesByItemGroupId($itemGroupId, Request $request)
    {
        // Retrieve categories associated with the item group ID
        $categories = Category::where('item_group_id', $itemGroupId)
                              ->orderBy('order_no','asc')
                              ->whereNull('parent_category_id') // Only top-level categories
                              ->get();

        if ($categories->isEmpty()) {
            return ResponseHelper::formatResponse(false, [], 'No categories found for this item group.');
        }

        return ResponseHelper::formatResponse(true, $categories, 'Categories retrieved successfully.');
    }

     public function attachItem(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return ResponseHelper::formatResponse(false, [], 'Category not found.');
        }

        $itemIds = $request->input('item_ids', []);

        if (empty($itemIds)) {
            return ResponseHelper::formatResponse(false, [], 'No items provided.');
        }

        $alreadyAttached = [];
        $newlyAttached = [];

        foreach ($itemIds as $itemId) {
            if ($category->items()->where('item_id', $itemId)->exists()) {
                $alreadyAttached[] = $itemId;
            } else {
                $category->items()->attach($itemId);
                $newlyAttached[] = $itemId;
            }

        }

        $message = 'Item attached successfully.';
        if (!empty($alreadyAttached)) {
            $message .= ' Some items were already attached: ' . implode(', ', $alreadyAttached);
        }

        return ResponseHelper::formatResponse(true, ['newly_attached' => $newlyAttached], $message);
    }

    public function detachItem(Request $request, $id)
    {
       $category = Category::find($id);

        if (!$category) {
            return ResponseHelper::formatResponse(false, [], 'Category not found.');
        }

        $itemIds = $request->input('item_ids', []);

        if (empty($itemIds)) {
            return ResponseHelper::formatResponse(false, [], 'No items provided.');
        }

        $category->variants()->detach($itemIds);

        return ResponseHelper::formatResponse(true, [], 'items detached successfully.');
    }
}
