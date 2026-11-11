<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    protected $guard ='admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'image',
        'cover_image',
        'name',
        'designation',
        'bio',
        'website',
        'twitter',
        'linkedin',
        'facebook',
        'instagram',
        'email',
        'password',
        'status',
    ];

    /**
     * Get all news articles written by this author.
     */
    public function news()
    {
        return $this->hasMany(\App\Models\News::class, 'author_id');
    }

    /**
     * Get published and approved articles by this author.
     */
    public function publishedNews()
    {
        return $this->hasMany(\App\Models\News::class, 'author_id')
            ->where('status', 1)
            ->where('is_approved', 1)
            ->orderBy('created_at', 'DESC');
    }

    /**
     * Total views across all published articles.
     */
    public function totalViews(): int
    {
        return (int) $this->news()->where('status', 1)->where('is_approved', 1)->sum('views');
    }

    /**
     * Count of published articles.
     */
    public function publishedArticlesCount(): int
    {
        return (int) $this->news()->where('status', 1)->where('is_approved', 1)->count();
    }

    /**
     * Get author profile public URL.
     */
    public function profileUrl(): string
    {
        $slug = \Illuminate\Support\Str::slug($this->name);
        return route('author.profile', ['id' => $this->id, 'slug' => $slug]);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}