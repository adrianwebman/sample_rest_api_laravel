<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Add slug, user_id to the request data
            $requestData = array_merge($request->all(), [
                'slug' => $this->generateSlug($request),
                'user_id' => auth()->user()->id,
            ]);

            // Create the validator
            $validator = Validator::make($requestData, [
                'title' => 'required',
                'content' => 'required',
                'slug' => Rule::unique('posts', 'slug'),
            ]);

            // Catch the specific errors if there is any
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Create the post
            $post = Post::create($requestData);

            // Return the response
            return response()->json($post, 201);
        } catch (\Throwable $th) {
            return $this->displayMessage('The given data was invalid!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $post)
    {
        $postData = $this->getPost($post);
        if (!$postData) {
            return $this->displayMessage('The post you are trying to show does not exist.', 404);
        }

        return response()->json([
            'data' => $postData,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $post)
    {
        try {

            // Count the requests
            if (count($request->all()) < 1) {
                return $this->displayMessage('Pass at least one field value in the request.');
            }

            // Check if post exists
            $postData = $this->getPost($post);
            if (!$postData) {
                return $this->displayMessage('The post you are trying to update does not exist.', 404);
            }

            // Prepare the request data
            $requestData = [
                'title' => $request->title,
                'content' => $request->content,
            ];

            // Update the record
            $data = tap(DB::table('posts')->where('slug', $post)->where('user_id', auth()->user()->id))
                ->update($requestData)
                ->first();

            return response()->json([
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return $this->displayMessage('The given data was invalid!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy($post)
    {
        try {
            // Check if post exists
            $postData = $this->getPost($post);
            if (!$postData) {
                return $this->displayMessage('The post you are trying to delete does not exist.', 404);
            }

            // Delete the record
            Post::where('slug', $post)->delete();

            return $this->displayMessage('The record was deleted successfully', 200, 'status');
        } catch (\Throwable $th) {
            return $this->displayMessage('The given data was invalid!');
        }
    }

    /**
     * If slug is not empty parse it to the slug.
     * If not, parse the title to become the slug.
     *
     * @param  mixed $request
     * @return void
     */
    private function generateSlug($request)
    {
        return !empty($request->slug) ? Str::slug($request->slug) : Str::slug($request->title);
    }
}
