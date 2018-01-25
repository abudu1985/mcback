<?php
/**
 * Created by PhpStorm.
 * User: Anna
 * Date: 10.12.2017
 * Time: 22:01
 */

namespace App\Http\Controllers;
use App\Category;
use JWTAuth;
use Illuminate\Http\Request;
class CategoryController extends Controller
{
    public function postCategory(Request $request)
    {
        $user = JWTAuth::parseToken()->toUser();
        $category = new Category();
        $category->name = $request->input('name');
        $category->capacity = $request->input('capacity');
        $category->user_id = $user->id;
        $category->save();
        return response()->json(['category' => $category], 201);
    }
    public function getCategories()
    {
        $user = JWTAuth::parseToken()->toUser();
        $categories = Category::where('user_id', $user->id)->get();
        return response()->json($categories, 200);
    }
    public function putCategory(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $category->name = $request->input('name');
        $category->capacity = $request->input('capacity');
        $category->save();
        return response()->json(['categories' => $category], 200);
    }

    public function getCategoryById($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        } else {
            return response()->json( $category, 200);
        }
    }
//    public function deleteQuote($id)
//    {
//        $quote = Quote::find($id);
//        $quote->delete();
//        return response()->json(['message' => 'Quote deleted'], 200);
//    }
}