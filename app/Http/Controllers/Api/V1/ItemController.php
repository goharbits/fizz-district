<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Item;
use App\Models\Category;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\CustomHelper;


class ItemController extends BaseController
{
    // Get all items with their category or subcategory, and additional details
    public function getAll(Request $request)
    {
        // need to set this code :category to categories
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $items = Item::with(['category.parentCategory.itemGroup', 'variants.subVariants'])
            ->paginate($perPage);

        // Initialize an empty array to store the transformed items
        $transformedItems = [];

        // Transform items to include the additional data
        foreach ($items as $item) {
            // dd($item->categories->parent_category_id);
            $attachedWithCategory = is_null($item->category->parent_category_id);

            $transformedItem = [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'image'=>   asset('items/' . @$item->image),
                'attached_with_category' => $attachedWithCategory,
                'category' => $attachedWithCategory ? $item->category : null,
                'sub_category' => !$attachedWithCategory ? $item->category : null,
                'item_group' => $item->category->parentCategory->itemGroup ?? $item->category->itemGroup,
                'variants' => []
            ];


            // Transform variants
            foreach ($item->variants as $variant) {
                $transformedVariant = [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sub_variants' => []
                ];

                // Transform sub-variants
                foreach ($variant->subVariants as $subVariant) {
                    $transformedVariant['sub_variants'][] = [
                        'id' => $subVariant->id,
                        'name' => $subVariant->name,
                        'price' => $subVariant->price,
                    ];
                }

                $transformedItem['variants'][] = $transformedVariant;
            }

            $transformedItems[] = $transformedItem;
        }

        // Replace the items in the paginator with the transformed items
        $items->setCollection(collect($transformedItems));

        return ResponseHelper::formatResponse(true, $items, 'Items retrieved successfully.');
    }


     public function getSpecificRecord($id)
    {
        $categoryName = '';
        // Fetch the item with related data
        // categories.parentCategory.itemGroup
        $item = Item::with(['categories',
                            'variants.subVariants',
                            'itemDescriptions'
                            ])
                ->find($id);

        if (!$item) {
            return ResponseHelper::formatResponse(false,null, 'Item not found.');
        }

        $isFav =  CustomHelper::checkFavoriteItem($item->id);

        $latte_ids = config('app.latte_ids');

        if ($item && $item->categories) {
            foreach ($item->categories as $category) {
                if (in_array($category->id,$latte_ids)) {
                    $categoryName = $category->name; // Assign the category name
                    break; // Exit the loop once a match is found
                }
            }
        }

        $transformedItem = [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'image' => asset('items/' . @$item->image),
                'isFav' => $isFav,
                'banner_image' => asset('banners/' . @$item->banner_image),
                'short_description' => $item->short_description,
                'long_description' => $item->long_description,
                'nutrition_html' => $item->nutrition_html,
                'ingredients_html' => $item->ingredients_html,
                'category_name' => $categoryName,
                // Sorting and mapping the variants based on order_no
                'variants' => $item->variants
                    ->filter(function ($variant) {
                        // Remove variants with the name "Featured"
                        return stripos($variant->name, 'Featured') === false;
                    })
                    ->sortBy(function ($variant) {
                            return is_numeric($variant->order_no) ? intval($variant->order_no) : 999; // Default to 999 if not set
                // Sort by order_no, default to 999 if not set
                    })
                    ->map(function ($variant) {
                        return [
                            'id' => $variant->id,
                            'name' => $variant->name,
                            'order_no'=> $variant->order_no,
                            'minRequired'=> $variant->minRequired,
                            'maxAllowed'=> $variant->maxAllowed,
                            'is_boxed' => $variant->is_boxed,
                            'sub_variants' => $variant->subVariants->map(function ($subVariant) {
                                return [
                                    'id' => $subVariant->id,
                                    'image' => $subVariant->image,
                                    'name' => $subVariant->name,
                                    'price' => $subVariant->price,
                                    'description' => $subVariant->description
                                ];
                            }),
                        ];
                    })
                    ->values(), // Reindex after sorting

                // Mapping item descriptions
                'descriptions' => $item->itemDescriptions->map(function ($description) {
                    $image = asset('descriptions_icons/' . @$description->icon);
                    return [
                        'id' => $description->id,
                        'description' => $description->description,
                        'icon' => $image,
                    ];
                }),
            ];

        return ResponseHelper::formatResponse(true, $transformedItem, 'Item retrieved successfully.');
    }



    public function getItemsByAllCategory()
    {
        // Get all categories
        // $categories = Category::orderBy('order_no', 'asc')->get();

        $categories = Category::with(['itemGroup', 'subCategories'])
                        ->orderBy('order_no','asc')
                        ->whereNull('parent_category_id')
                        ->get();

        if ($categories->isEmpty()) {
            return ResponseHelper::formatResponse(false, [], 'No categories found.');
        }

        $userId = null;

        if(Auth::guard('api')->check()){
        // Get the current authenticated user ID
            $userId = Auth::guard('api')->user()->id;
        }

        // Initialize the result structure
        $result = [
            'categories' => $categories->map(function ($category) use ($userId) {
                $categoryData = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'order_no' => $category->order_no,
                    'has_subcategories' => (boolean) $category->has_subcategories,
                    'items' => null, // Default to null if no items
                    'sub_categories' => null // Default to null if no sub-categories
                ];

                // Check if the category has sub-categories
                if ($category->has_subcategories) {
                    // Get all sub-categories of this category
                    $subCategories = Category::where('parent_category_id', $category->id)->with(['items'])->get();

                    if (!$subCategories->isEmpty()) {
                        $categoryData['sub_categories'] = $subCategories->map(function ($subCategory) use ($userId) {
                            $items = $subCategory->items;

                            // If no items in sub-category, set it to null
                            $itemsData = $items->isEmpty() ? null : $items->map(function ($item) use ($userId) {
                                $isFav = DB::table('favorites')
                                    ->where('user_id', $userId)
                                    ->where('item_id', $item->id)
                                    ->exists();

                                return [
                                    'id' => $item->id,
                                    'name' => $item->name,
                                    'price' => $item->price,
                                    'isFav' => $isFav,
                                    'image' => $item->image ? asset('items/' . $item->image) : null,
                                    'variants' => $item->variants->map(function ($variant) {
                                        return [
                                            'id' => $variant->id,
                                            'name' => $variant->name,
                                            'sub_variants' => $variant->subVariants->map(function ($subVariant) {
                                                return [
                                                    'id' => $subVariant->id,
                                                    'name' => $subVariant->name,
                                                    'image' => $subVariant->image,
                                                    'price' => $subVariant->price,
                                                    'description' => $subVariant->description,
                                                ];
                                            }),
                                        ];
                                    }),
                                ];
                            });

                            return [
                                'sub_category' => $subCategory->name,
                                'items' => $itemsData // Pass the items or null
                            ];
                        });
                    }
                } else {
                    // If no sub-categories, return items directly associated with this category
                    // $items = Item::with('variants.subVariants')
                    //     ->where('category_id', $category->id)
                    //     ->get(); // Fetch items for this category

                        $items = Item::with('variants.subVariants')
                                ->whereHas('categories', function ($query) use ($category) {
                                    $query->where('categories.id', $category->id); // Specify the table name
                                })->get();
                    // If no items, keep items as null; otherwise, map the items
                    if (!$items->isEmpty()) {
                        $categoryData['items'] = $items->map(function ($item) use ($userId) {
                            $isFav = DB::table('favorites')
                                ->where('user_id', $userId)
                                ->where('item_id', $item->id)
                                ->exists();

                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'price' => $item->price,
                                'isFav' => $isFav,
                                'image' => $item->image ? asset('items/' . @$item->image) : null,
                                'variants' => $item->variants->map(function ($variant) {
                                    return [
                                        'id' => $variant->id,
                                        'name' => $variant->name,
                                        'sub_variants' => $variant->subVariants->map(function ($subVariant) {
                                            return [
                                                'id' => $subVariant->id,
                                                'name' => $subVariant->name,
                                                'image' => $subVariant->image ,
                                                'price' => $subVariant->price,
                                                'description' => $subVariant->description,
                                            ];
                                        }),
                                    ];
                                }),
                            ];
                        });
                    }
                }

                return $categoryData;
            })->toArray() // Convert the collection to an array to avoid numerical indexing
        ];

        return ResponseHelper::formatResponse(true, $result, 'Data retrieved successfully.');
    }


    public function getItemsByCategoryId($categoryId)
    {
        // Ensure the given ID is a valid category (it can be either a top-level category or a sub-category)
        $category = Category::find($categoryId);

        if (!$category) {
            return ResponseHelper::formatResponse(false, [], 'The provided ID does not correspond to a valid category.');
        }

        $userId = null;

        if(Auth::guard('api')->check()){
        // Get the current authenticated user ID
            $userId = Auth::guard('api')->user()->id; // Assuming you are using Laravel's built-in auth system
        }

        // Initialize the response structure
        $result = [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'has_subcategories' => (boolean) $category->has_subcategories,
            ],
        ];

        // Check if the category has sub-categories
        if ($category->has_subcategories) {
            // Get all sub-categories of this category

            $subCategories = Category::with(['items.variants.subVariants'])
                ->where('parent_category_id', $categoryId)
                ->get();

            if ($subCategories->isEmpty()) {
                return ResponseHelper::formatResponse(false, [], 'No sub-categories or items found for this category.');
            }

            $result['category']['sub_categories'] = $subCategories->map(function ($subCategory) use ($userId) {
                return [
                    'sub_category' => $subCategory->name,
                    'items' => $subCategory->items->map(function ($item) use ($userId) {
                        // Check if the item is marked as favorite
                        $isFav = DB::table('favorites')
                            ->where('user_id', $userId)
                            ->where('item_id', $item->id)
                            ->exists();

                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'price' => $item->price,
                            'isFav' => $isFav, // Add 'isFav' as true or false
                            'image'=> asset('items/' . @$item->image)
                        ];
                    }),
                ];
            });
        } else {
            // If no sub-categories, return items directly associated with this category
            // $items = Item::with('variants.subVariants')
            //     ->where('category_id', $categoryId)
            //     ->paginate(10); // Paginate with 10 items per page

            $items = Item::with('variants.subVariants')
                    ->whereHas('categories', function ($query) use ($categoryId) {
                        $query->where('categories.id', $categoryId); // Specify the table name
                    })
                    ->paginate(10);

            if ($items->isEmpty()) {
                return ResponseHelper::formatResponse(false, [], 'No items found for this category.');
            }

            $result['category']['items'] = $items->map(function ($item) use ($userId) {
                // Check if the item is marked as favorite
                $isFav = DB::table('favorites')
                    ->where('user_id', $userId)
                    ->where('item_id', $item->id)
                    ->exists();

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'isFav' => $isFav, // Add 'isFav' as true or false
                    'image'=> asset('items/' . @$item->image)

                ];
            });
        }

        return ResponseHelper::formatResponse(true, $result, 'Data retrieved successfully.');
    }



    public function getFeaturedItems(){

            $featur_items = Variant::where('name', 'Featured')
                            ->with(['items'])
                            ->first();

            if ($featur_items && $featur_items->items) {
                $featur_items->items->each(function ($item) {
                   $item->isFav = CustomHelper::checkFavoriteItem($item->id); // Assuming checkFavoriteItem() returns a boolean
                   $item->image = asset('items/' . @$item->image);
                   $item->banner_image = asset('banners/' . @$item->banner_image);
                });
            }

        return ResponseHelper::formatResponse(true, $featur_items, 'Featured items');

    }
public function getFoodItems()
{
    $userId = null;
    // Ensure the given ID is a valid category (it can be either a top-level category or a sub-category)
    $categories = Category::whereIn('name', ['Snacks & Sweets', 'Breakfast Bites'])->pluck('id');

    if ($categories->isEmpty()) {
        return ResponseHelper::formatResponse(false, [], 'Categories not found.');
    }

    // $items = Item::whereIn('category_id', $categories)
    //             ->with(['category.parentCategory.itemGroup',
    //                         'variants.subVariants'
    //                     ])
    //             ->get();

    // $items = Item::with('variants.subVariants')
    //                 ->whereHas('categories', function ($query) use ($category) {
    //                     $query->where('categories.id', $category->id); // Specify the table name
    //                 })->get();
            $items = Item::with([
                            'categories.parentCategory.itemGroup', // Eager load the parentCategory and itemGroup via categories
                            'variants.subVariants'                // Eager load variants with subVariants
                    ])
                    ->whereHas('categories', function ($query) use ($categories) {
                        $query->whereIn('categories.id', $categories); // Specify the table name and filter categories
                    })
                    ->get();
             // Paginate with 10 items per page

    if ($items->isEmpty()) {
        return ResponseHelper::formatResponse(false, [], 'No items found for this category.');
    }

    if(Auth::guard('api')->check())
    {
       // Get the current authenticated user ID
        $userId = Auth::guard('api')->user()->id;
    }

    // Initialize an empty array to store the transformed items
        $transformedItems = [];

        // Transform items to include the additional data
        foreach ($items as $item) {

             $isFav = DB::table('favorites')
                        ->where('user_id', $userId)
                        ->where('item_id', $item->id)
                        ->exists();

            $transformedItem = [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'image' => asset('items/' . @$item->image),
                'variants' => [],
                'isFav' => $isFav,

            ];

            // Transform variants
            foreach ($item->variants as $variant) {
                $transformedVariant = [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sub_variants' => []
                ];

                // Transform sub-variants
                foreach ($variant->subVariants as $subVariant) {
                    $transformedVariant['sub_variants'][] = [
                        'id' => $subVariant->id,
                        'name' => $subVariant->name,
                        'price' => $subVariant->price,
                    ];
                }

                $transformedItem['variants'][] = $transformedVariant;
            }

            $transformedItems[] = $transformedItem;
        }

        // $items->setCollection(collect($transformedItems));

    return ResponseHelper::formatResponse(true, $transformedItems, 'Data retrieved successfully.');

}

    // Get all items by subcategory ID (ensure it's a subcategory)
    public function getItemsBySubCategoryId($subCategoryId)
    {
        // Ensure the given ID is a subcategory (i.e., it has a parent_category_id)
        $subCategory = Category::where('id', $subCategoryId)->whereNotNull('parent_category_id')->first();

        if (!$subCategory) {
            return ResponseHelper::formatResponse(false, [], 'The provided ID does not correspond to a valid subcategory.');
        }

        // Fetch items by subcategory ID
        $items = Item::with('category')
            ->where('category_id', $subCategoryId)
            ->paginate(10); // Paginate with 10 items per page

        if ($items->isEmpty()) {
            return ResponseHelper::formatResponse(false, [], 'No items found for this subcategory.');
        }

        return ResponseHelper::formatResponse(true, $items, 'Items retrieved successfully.');
    }


    public function addItemInModifier(Request $request){
        $cloverRequest = $this->cloverService->addItemInModifier($request->all());
        if (!$cloverRequest->getData()->success) {
            return ResponseHelper::formatResponse(false, [], 'Failed to create item in Clover.');
        }
        return ResponseHelper::formatResponse(true, $cloverRequest,'Item added with modifier group.');


    }

    public function addItemWithCategory(Request $request)
    {
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    $category = Category::find($value);
                    if ($category && $category->parent_category_id !== null) {
                        $fail('The selected category is not a category.');
                    }
                },
            ],
            'image' => 'nullable|string',
            'short_description' => 'nullable|string|max:255',
            'long_description' => 'nullable|string',
        ];

        // Validate request
        $validation = ValidatorHelper::validate($request->all(), $rules);

        if (!$validation['success']) {
            return ResponseHelper::formatResponse(false, [], $validation['errors']);
        }

        // Create the Clover item
        $cloverRequest = $this->cloverService->createCloverItem($request->input('name'), ($request->input('price') * 100));

        if (!$cloverRequest->getData()->success) {
            return ResponseHelper::formatResponse(false, [], 'Failed to create item in Clover.');
        }

        $cloverItemId = $cloverRequest->getData()->data->id;
        // Create the item
        $item = Item::create([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'category_id' => $request->input('category_id') ?? null,
            'clover_id' => $cloverItemId, // Add Clover ID
            'image' => $request->input('image'),
            'short_description' => $request->input('short_description'),
            'long_description' => $request->input('long_description'),
        ]);
        return ResponseHelper::formatResponse(true, $item, 'Item created successfully with category.');
    }

    public function addItemWithSubCategory(Request $request)
    {
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'sub_category_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    $subCategory = Category::find($value);
                    if ($subCategory && $subCategory->parent_category_id === null) {
                        $fail('The selected category is not a subcategory.');
                    }
                },
            ],
            'image' => 'nullable|string',
            'short_description' => 'nullable|string|max:255',
            'long_description' => 'nullable|string',
        ];

        // Validate request
        $validation = ValidatorHelper::validate($request->all(), $rules);

        if (!$validation['success']) {
            return ResponseHelper::formatResponse(false, [], $validation['errors']);
        }

        // Create the Clover item
        $cloverRequest = $this->cloverService->createCloverItem($request->input('name'), ($request->input('price') * 100));

        if (!$cloverRequest->getData()->success) {
            return ResponseHelper::formatResponse(false, [], 'Failed to create item in Clover.');
        }

        $cloverItemId = $cloverRequest->getData()->data->id;

        // Create the item in the database
        $item = Item::create([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'category_id' => $request->input('sub_category_id') ?? null, // Store subcategory ID in category_id column
            'clover_id' => $cloverItemId, // Add Clover ID
            'image' => $request->input('image'),
            'short_description' => $request->input('short_description'),
            'long_description' => $request->input('long_description'),
        ]);

        return ResponseHelper::formatResponse(true, $item, 'Item created successfully with subcategory.');
    }

    public function attachVariants(Request $request, $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return ResponseHelper::formatResponse(false, [], 'Item not found.');
        }

        $variantIds = $request->input('variant_ids', []);

        if (empty($variantIds)) {
            return ResponseHelper::formatResponse(false, [], 'No variants provided.');
        }

        $alreadyAttached = [];
        $newlyAttached = [];

        foreach ($variantIds as $variantId) {
            if ($item->variants()->where('variant_id', $variantId)->exists()) {
                $alreadyAttached[] = $variantId;
            } else {
                $item->variants()->attach($variantId);
                $newlyAttached[] = $variantId;
            }
        }

        $message = 'Variants attached successfully.';
        if (!empty($alreadyAttached)) {
            $message .= ' Some variants were already attached: ' . implode(', ', $alreadyAttached);
        }

        return ResponseHelper::formatResponse(true, ['newly_attached' => $newlyAttached], $message);
    }

    public function detachVariants(Request $request, $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return ResponseHelper::formatResponse(false, [], 'Item not found.');
        }

        $variantIds = $request->input('variant_ids', []);

        if (empty($variantIds)) {
            return ResponseHelper::formatResponse(false, [], 'No variants provided.');
        }

        $item->variants()->detach($variantIds);

        return ResponseHelper::formatResponse(true, [], 'Variants detached successfully.');
    }

    public function updateNutrition(Request $request, $itemId)
    {
        $rules = [
            'nutrition' => 'required|string',
        ];

        $validation = ValidatorHelper::validate($request->all(), $rules);

        if (!$validation['success']) {
            return ResponseHelper::formatResponse(false, [], $validation['errors']);
        }

        $item = Item::find($itemId);

        if (!$item) {
            return ResponseHelper::formatResponse(false, [], 'Item not found.');
        }

        $item->nutrition_html = $request->input('nutrition');
        $item->save();

        return ResponseHelper::formatResponse(true, $item, 'Nutrition updated successfully.');
    }

    public function updateIngredients(Request $request, $itemId)
    {
        $rules = [
            'ingredients' => 'required|string',
        ];

        $validation = ValidatorHelper::validate($request->all(), $rules);

        if (!$validation['success']) {
            return ResponseHelper::formatResponse(false, [], $validation['errors']);
        }

        $item = Item::find($itemId);

        if (!$item) {
            return ResponseHelper::formatResponse(false, [], 'Item not found.');
        }

        $item->ingredients_html = $request->input('ingredients');
        $item->save();

        return ResponseHelper::formatResponse(true, $item, 'Ingredients updated successfully.');
    }

    public function verifyToken(){
        return ResponseHelper::formatResponse(true,'Token Verified');
    }

    public function searchItems(Request $request)
    {
        // Validate the input, it must be a string (for name) or numeric (for price)
        $rules = [
            'input' => 'required|string|max:255'
        ];

        $validation = ValidatorHelper::validate($request->all(), $rules);

        if (!$validation['success']) {
            return ResponseHelper::formatResponse(false, [], $validation['errors']);
        }

        $input = $request->input('input');

        // Check if the input is numeric (for price) or a string (for name)
        $items = Item::where('name', 'like', '%' . $input . '%') // Check if name matches
            ->orWhere(function ($query) use ($input) {
                if (is_numeric($input)) {
                    $query->where('price', $input); // Check if price matches if the input is numeric
                }
            })
            ->get();

        if ($items->isEmpty()) {
            return ResponseHelper::formatResponse(false, [], 'No items found.');
        }

        return ResponseHelper::formatResponse(true, $items, 'Items retrieved successfully.');
    }
}
