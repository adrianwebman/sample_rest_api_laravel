<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Gets the post through slug filter
     *
     * @param  string $slug
     * @return mixed
     */
    protected function getPost($slug)
    {
        $postData = Post::where('slug', $slug)->first();
        if (!$postData) {
            return false;
        }

        return $postData;
    }

    /**
     * Gets the post by id
     *
     * @param  string $id
     * @return mixed
     */
    protected function getComment($id)
    {
        $commentData = Comment::find($id);
        if (!$commentData) {
            return false;
        }

        return $commentData;
    }

    /**
     * Displays the common message
     *
     * @param  mixed $message
     * @return \Illuminate\Http\Response
     */
    protected function displayMessage($message, $status = 422, $messageLabel = 'message')
    {
        return response()->json([
            $messageLabel => $message,
        ], $status);
    }
}
