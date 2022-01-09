<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  string  $post
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $post)
    {

        try {
            // Get post to check if it exists
            $postData = $this->getPost($post);

            // Proceed if post data exists.
            if ($postData) {

                // Add additional data to the request data
                $requestData = array_merge($request->all(), [
                    'commentable_type' => 'App\\Model\\Post',
                    'commentable_id' => $postData->id,
                    'creator_id' => auth()->user()->id,
                ]);

                // Create the validator
                $validator = Validator::make($requestData, [
                    'body' => 'required',
                ]);

                // Catch the specific errors if there is any
                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'The given data was invalid.',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                // Create the comment
                $post = Comment::create($requestData);

                // Return the response
                return response()->json($post, 201);
            } else {
                return $this->displayMessage('The post you are trying to comment does not exist.');
            }

        } catch (\Throwable $th) {
            return $this->displayMessage('The given data was invalid!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $post
     * @return \Illuminate\Http\Response
     */
    public function show($post)
    {
        $postComments = Post::where('slug', $post)->first()->comments()->paginate();
        if (!$postComments) {
            return $this->displayMessage('There are no comments as of the moment.', 404);
        }

        return response()->json($postComments, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $post,
     * @param  int  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $post, $comment)
    {
        try {

            // Get post to check if it exists
            $postData = $this->getPost($post);

            // Proceed if post data exists.
            if ($postData) {
                // Count the requests
                if (count($request->all()) < 1) {
                    return $this->displayMessage('Pass at least one field value in the request.');
                }

                // Check if the comment exists
                $commentData = $this->getComment($comment);
                if (!$commentData) {
                    return $this->displayMessage('The comment you are trying to update does not exist.');
                }

                // Update the record
                $data = tap(DB::table('comments')->where('id', $comment))
                    ->update($request->all())
                    ->first();

                return response()->json($data, 200);
            } else {
                return $this->displayMessage('The post you are trying to comment does not exist.', 404);
            }

        } catch (\Throwable $th) {
            return $this->displayMessage('The given data was invalid!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $post,
     * @param  int  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy($post, $comment)
    {
        try {

            $postData = $this->getPost($post);
            if ($postData) {
                // Check if the comment exists
                $commentData = $this->getComment($comment);
                if (!$commentData) {
                    return $this->displayMessage('The comment you are trying to update does not exist.');
                }

                // Delete the record
                Comment::where('id', $comment)->delete();

                return $this->displayMessage('The record was deleted successfully', 200, 'status');
            } else {
                return $this->displayMessage('The post you are trying to delete does not exist.');
            }
        } catch (\Throwable $th) {
            return $this->displayMessage('The given data was invalid!');
        }
    }
}
