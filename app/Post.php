<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use Sluggable;

    const IS_DRAFT = 0;
    const IS_PUBLIC = 1;

    protected $fillable = ['title', 'content', 'date', 'description'];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function author() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function tags() {
        return $this->belongsToMany(
            Tag::class,
            'post_tags',
            'post_id',
            'tag_id'
        );
    }

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public static function add($fields) {
        $post = new static;
        $post->fill($fields);
        $post->user_id = 1;
        $post->save();

        return $post;
    }

    public function edit($fields) {
        $this->fill($fields);
        $this->save();
    }

    public function remove() {
        $this->removeImage();
        $this->delete();
    }

    public function removeImage() {
        if($this->image != null) {
            Storage::delete('uploads/'. $this->image);
        }
    }

    public function uploadImage($image) {
        if($image == NULL) {
            return;
        }
        $this->removeImage();
        //$fileName = str_random(10) . '.' . $image->getClientOriginalExtension();
        $fileName = str_random(10) . '.' . $image->extension();
        $image->storeAs('uploads', $fileName);
        $this->image = $fileName;
        $this->save();
    }

    public function setCategory($id){
        if($id == NULL) {
            return;
        }
        $this->category_id = $id;
        $this->save();
    }

    public function setTags($ids) {
        if($ids == NULL) {
            return;
        }

        $this->tags()->sync($ids);
    }

    public function setDraft() {
        $this->status = Post::IS_DRAFT;
        $this->save();
    }

    public function setPublic() {
        $this->status = Post::IS_PUBLIC;
        $this->save();
    }

    public function toggleStatus($value) {
        if($value == NULL) {
            return $this->setDraft();
        }
        return $this->setPublic();
    }

    public function setFeatured() {
        $this->is_featured = 1;
        $this->save();
    }

    public function setStandart() {
        $this->is_featured = 0;
        $this->save();
    }

    public function toggleFeatured($value) {
        if($value == null) {
            return $this->setStandart();
        }

        return $this->setFeatured();
    }

    public function getImage() {
        if($this->image == NULL) {
            return '/img/no-image.png';
        }
        return '/uploads/' . $this->image;
    }

    public function setDateAttribute($value) {
        $date = Carbon::createFromFormat('d/m/y', $value)->format('Y-m-d');
        $this->attributes['date'] = $date;
    }

    public function getCategoryTitle() {
        return ($this->category != NULL)
            ? $this->category->title
            : 'Нет категории!';
    }

    public  function getTagsTitles() {
        return (!$this->tags->isEmpty())
            ? implode(', ', $this->tags->pluck('title')->all())
            : 'Нет тегов';
    }

    public function getDateAttribute($value) {
        $date = Carbon::createFromFormat('Y-m-d', $value)->format('d/m/y');
        return $date;
    }

    public function getCategoryID() {
        return $this->category != NULL ? $this->category->id : null;
    }

    public function getDate() {
        return Carbon::createFromFormat('d/m/y', $this->date)->format('F d, Y');
    }

    public function hasPrevious(){
        return self::where('id', '<', $this->id)->max('id');
    }

    public function getPrevious() {
        $postID = $this->hasPrevious(); //ID

        return self::find($postID);
    }

    public function hasNext() {
        return self::where('id', '>', $this->id)->min('id');
    }

    public function getNext() {
        $postID = $this->hasNext();

        return self::find($postID);
    }

    public function related() {
            return self::all()->except($this->id);
    }

    public function hasCategory() {
        return $this->category != null ? true : false;
    }

    public function getComments() {
        return $this->comments()->where('status', 1)->get();
    }
}
