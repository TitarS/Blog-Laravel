<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use Sluggable;

    const IS_DRAFT = 0;
    const IS_PUBLIC = 1;

    protected $fillable = ['title', 'content'];

    public function category() {
        return $this->hasOne(Category::class);
    }

    public function author() {
        return $this->hasOne(User::class);
    }

    public function tags() {
        return $this->belongsToMany(
            Tag::class,
            'post_tags',
            'post_id',
            'tag_ig'
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
        Storage::delete('uploads/'. $this->image);
        $this->delete();
    }

    public function uploadImage($image) {
        if($image == NULL) {
            return;
        }

        Storage::delete('uploads/'. $this->image);
        //$fileName = str_random(10) . '.' . $image->getClientsOriginExtension();
        $fileName = str_random(10) . '.' . $image->extension();
        $image->saveAs('uploads', $fileName);
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
        return 'upload/' . $this->image;
    }

}
