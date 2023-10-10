<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    //
    public function storeNewPost(Request $request)
    {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost = Post::create($incomingFields);
        // return redirect('/post/' . $newPost->id);
        return redirect("/post/{$newPost->id}")->with('success', 'New post successfully created!');
    }

    public function showCreateForm()
    {
        // if (!auth()->check()) {
        //     return redirect('/');
        // }

        return view('create-post');
    }

    public function viewSinglePost(Post $post)
    {
        $post['body'] = strip_tags(Str::markdown($post->body), '<p><ul><li><em><strong><h3><br>');
        return view('single-post', ['post' => $post]);
    }

    public function delete(Post $post)
    {
        // if (auth()->user()->cannot('delete', $post)) {
        //     return redirect('/profile/' . auth()->user()->username)->with('failure', 'You are not authorized to delete this post.');
        // }
        $post->delete();
        return redirect('/profile/' . auth()->user()->username)->with('success', 'Post successfully deleted.');
    }

    public function showEditForm(Post $post)
    {
        return view('edit-post', ['post' => $post]);
    }

    public function actuallyUpdate(Post $post, Request $request)
    {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);
        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        $post->update($incomingFields);
        return back()->with('success', 'Post successfully updated.');
    }
}
