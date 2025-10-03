<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Tweak extends Model
{
    use HasFactory;

    protected $fillable = [
        'package',
        'name',
        'version',
        'description',
        'author',
        'maintainer',
        'section',
        'architecture',
        'depends',
        'homepage',
        'icon_url',
        'header_url',
        'sileo_depiction',
        'installed_size',
        'deb_file_path',
        'extracted_path',
        'icon_path',
        'data_files',
        'control_data',
    ];

    protected $casts = [
        'data_files' => 'array',
        'control_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the full URL for the .deb file
     */
    public function getDebFileUrlAttribute(): string
    {
        return Storage::url($this->deb_file_path);
    }

    /**
     * Get the full URL for the icon
     */
    public function getIconUrlFullAttribute(): ?string
    {
        if ($this->icon_path) {
            return Storage::url($this->icon_path);
        }

        return $this->icon_url;
    }

    /**
     * Get the full URL for the header
     */
    public function getHeaderUrlFullAttribute(): ?string
    {
        return $this->header_url;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->deb_file_path) {
            return 'N/A';
        }

        $bytes = Storage::disk('public')->size($this->deb_file_path);

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    /**
     * Scope to filter by section
     */
    public function scopeSection($query, $section)
    {
        return $query->where('section', $section);
    }

    /**
     * Scope to search tweaks
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('package', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('author', 'like', "%{$search}%");
        });
    }

    public function changeLogs()
    {
        return $this->hasMany(Changelog::class);
    }

}
