<?php

namespace App\Http\Controllers;

use App\Photo;
use App\Like;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Image;

class PhotoController extends Controller
{
  /**
  * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
  */
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    return response(
      Photo::select('photos.location as p_location', 'photos.description as p_description', 'photos.created_at as p_created_at', 'photos.id as p_id', 'photos.*', 'users.*', 'categories.name as c_name')
      ->join('users','photos.user_id','=','users.id')->join('categories','categories.id','=','photos.category_id')->orderBy('photos.id', 'desc')->get()
    );
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
    $filename = $request->file('photofile')->store('');
    $image = Image::make($request->file('photofile'));
    $width = $image->width();
    if ($width > 1920) {
      $image->resize(1920, null, function ($constraint) {
        $constraint->aspectRatio();
      })->save('storage/s'.$filename);
    } else {
      $image->save('storage/s'.$filename);
    }
    if ($exif = exif_read_data($request->file('photofile'))) {
      $camera = (isset($exif['Model'])) ? $exif['Model'] : NULL;
      $lens = (isset($exif['LensModel'])) ? $exif['LensModel'] : NULL;
      $lens = (isset($exif['Lens'])) ? $exif['Lens'] : NULL;
      $focal_length = (isset($exif['FocalLength'])) ? $exif['FocalLength'] : NULL;
      $speed = (isset($exif['ExposureTime'])) ? $exif['ExposureTime'] : NULL;
      $iris = (isset($exif['FNumber'])) ? $exif['FNumber'] : NULL;
      $iso = (isset($exif['ISOSpeedRatings'])) ? $exif['ISOSpeedRatings'] : NULL;
    }

    DB::table('photos')->insert(
      [
        'user_id' => User::firstOrNew(['screen_name' => $request->input('screen_name')])->id,
        'category_id' => $request->input('category'),
        'title' => $request->input('title'),
        'path' => $filename,
        'location' => $request->input('location'),
        'tags' => $request->input('tags'),
        'description' => $request->input('description'),
        'camera' => $camera,
        'lens' => $lens,
        'focal_length' => $focal_length,
        'speed' => $speed,
        'iris' => $iris,
        'iso' => $iso,
        'updated_at' => date('Y/m/d H:i:s'),
        'created_at' => date('Y/m/d H:i:s')
      ]
    );
  }

  /**
  * Display the specified resource.
  *
  * @param  \App\Photo  $photo
  * @return \Illuminate\Http\Response
  */
  public function show(Photo $photo)
  {
    //
  }

  /**
  * Show the form for editing the specified resource.
  *
  * @param  \App\Photo  $photo
  * @return \Illuminate\Http\Response
  */
  public function edit(Photo $photo)
  {
    //
  }

  /**
  * Update the specified resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  \App\Photo  $photo
  * @return \Illuminate\Http\Response
  */
  public function update(Request $request, Photo $photo)
  {
    //
  }

  /**
  * Remove the specified resource from storage.
  *
  * @param  \App\Photo  $photo
  * @return \Illuminate\Http\Response
  */
  public function destroy(Photo $photo)
  {
    //
  }

  /**
   * 写真1枚の情報をゲット
   * @param  int $id 写真のid
   * @return [type]     [description]
   */
  public function get($id)
  {
    return response(
      Photo::select('photos.location as p_location', 'photos.description as p_description', 'photos.created_at as p_created_at', 'photos.id as p_id', 'photos.*', 'users.*', 'categories.name as c_name')
      ->join('users','photos.user_id','=','users.id')->join('categories','categories.id','=','photos.category_id')->where('photos.id', $id)->get()
    );
  }

  /**
   * いいね/アンいいねのトグル
   */
  public function toggleLike(Request $request)
  {
    $screen_name = $request->input('screen_name');
    $photo_id = $request->input('photo_id');
    $user = User::firstOrNew(['screen_name' => $screen_name]);
    $photo = Photo::firstOrNew(['id' => $photo_id]);

    if(Like::select()->where(['user_id' => $user->id, 'photo_id' => $photo_id])->exists()) {
      $like = Like::firstOrNew(['user_id' => $user->id, 'photo_id' => $photo_id]);
      $like->delete();
      $photo->likes -= 1;
      $photo->save();
    }
    else {
      Like::insert(['user_id' => $user->id, 'photo_id' => $photo_id, 'updated_at' => date('Y/m/d H:i:s'), 'created_at' => date('Y/m/d H:i:s')]);
      $photo->likes += 1;
      $photo->save();
    }
  }

  public function checkLike($screen_name, $photo_id)
  {
    $user = User::firstOrNew(['screen_name' => $screen_name]);
    if(Like::select()->where(['user_id' => $user->id, 'photo_id' => $photo_id])->exists()) {
      return 'true';
    }
    else {
      return 'false';
    }
  }
}
