<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Post;
use Intervention\Image\Facades\Image as Image;

use Validator;
use App\Models\File;
use App\Http\Resources\Post as PostResource;

class PostController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $post = Post::all();
        
        return $this->sendResponse(PostResource::collection($post), 'Post retrieved successfully.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        $input = $request->all();
        $userDetails = $request->user();
        
        $validator = Validator::make($input, [            
            'title' => 'required',
            'slug' => 'required',
            'category' => 'required',
            'description' => 'required'            
        ]);

        /* Compress and Upload image */
        $image = $request->file('featured_image');

        $input['featured_image'] = time().'.'.$image->extension();

        $filePath = public_path('/posts_thumbnails');

        $img = Image::make($image->path());
        $img->resize(110, 110, function ($const) {
            $const->aspectRatio();
        })->save($filePath.'/'.$input['featured_image']);

        $filePath = public_path('/posts');
        $image->move($filePath, $input['featured_image']);
        /* Image processing task end here */
        
        $input['user_id'] = $userDetails->id; 
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $post = Post::create($input);
        
        return $this->sendResponse(new PostResource($post), 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
        
        $input = $request->all();
        
        $validator = Validator::make($input, [            
            'slug' => 'required'           
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $result = Post::where('slug','=',$request->slug)->get();
       
        return $this->sendResponse($result, 'Post search result.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        
        $input = $request->all();
        
        $validator = Validator::make($input, [            
            'id' => 'required'           
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }


        $result = Post::where('id', $request->id)->update(['deleted_at' => 0]); 
        //$post = Post::where('id', $request->id)->withTrashed()->first();

        //$result = $post->forceDelete();

        return $this->sendResponse($result, 'Post deleted successfully.');
    }
}
