<?php

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Traits\Common\AppendsTrait;
use App\Http\Controllers\Web\Admin\Panel\Library\Traits\Models\Crud;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[ScopedBy([ActiveScope::class])]
class NativeSponsor extends BaseModel
{
    use Crud, AppendsTrait;

    protected $table = 'native_sponsors';
    
    protected $guarded = ['id'];
    
    protected $fillable = [
        'title',
        'link',
        'carousel_image_path',
        'sidebar_image_path',
        'card_image_path',
        'frequency',
        'category_id',
        'expires_at',
        'active',
    ];
    
    protected $casts = [
        'expires_at' => 'date',
        'active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    
    private function processUploadedImage($value, $attribute_name)
    {
        $destination_path = 'sponsors';
        $disk = \App\Helpers\Common\Files\Storage\StorageDisk::getDisk();

        // Se o valor for uma string base64, converte para UploadedFile
        if (is_string($value) && str_starts_with($value, 'data:image')) {
            $value = \App\Helpers\Common\Files\Upload::fromBase64($value);
        }

        // Se o valor for vazio/nulo
        if (empty($value)) {
            if (!empty($this->{$attribute_name}) && $disk->exists($this->{$attribute_name})) {
                $disk->delete($this->{$attribute_name});
            }
            $this->attributes[$attribute_name] = null;
            return;
        }

        // Se for um arquivo de upload novo, vamos deletar o arquivo anterior
        if ($value instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            if (!empty($this->{$attribute_name}) && $disk->exists($this->{$attribute_name})) {
                $disk->delete($this->{$attribute_name});
            }
        }

        // Usa o helper Upload::image que já lida com UploadedFile e strings
        $uploadedPath = \App\Helpers\Common\Files\Upload::image($value, $destination_path);
        
        if (!empty($uploadedPath)) {
            $this->attributes[$attribute_name] = $uploadedPath;
            
            // Gera as miniaturas do patrocinador
            $isWebpFormatEnabled = (config('settings.optimization.webp_format') == '1');
            \App\Jobs\GenerateImageThumbJob::dispatchSync($uploadedPath, false, 'picture-lg');
            if ($isWebpFormatEnabled) {
                \App\Jobs\GenerateImageThumbJob::dispatchSync($uploadedPath, false, 'picture-lg', true);
            }
        } else {
            $this->attributes[$attribute_name] = null;
        }
    }

    public function setCarouselImagePathAttribute($value)
    {
        $this->processUploadedImage($value, 'carousel_image_path');
    }

    public function setSidebarImagePathAttribute($value)
    {
        $this->processUploadedImage($value, 'sidebar_image_path');
    }

    public function setCardImagePathAttribute($value)
    {
        $this->processUploadedImage($value, 'card_image_path');
    }
    
    // Add image URL appended accessors for API
    protected $appends = ['carousel_image_url', 'sidebar_image_url', 'card_image_url'];
    
    private function getImageUrlForPath($filePath)
    {
        if (empty($filePath)) {
            return null;
        }
        $isWebpFormatEnabled = (config('settings.optimization.webp_format') == '1');
        return thumbParam($filePath)->setOption('picture-lg', $isWebpFormatEnabled)->url();
    }

    public function getCarouselImageUrlAttribute()
    {
        return $this->getImageUrlForPath($this->carousel_image_path);
    }

    public function getSidebarImageUrlAttribute()
    {
        return $this->getImageUrlForPath($this->sidebar_image_path);
    }

    public function getCardImageUrlAttribute()
    {
        return $this->getImageUrlForPath($this->card_image_path);
    }

    public function getCarouselImageHtml()
    {
        $url = $this->carousel_image_url;
        if (empty($url)) {
            return '<span class="badge bg-secondary">-</span>';
        }
        return '<img src="' . $url . '" class="img-rounded" style="width:auto; max-height:45px; border: 1px solid #dee2e6; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
    }

    public function getSidebarImageHtml()
    {
        $url = $this->sidebar_image_url;
        if (empty($url)) {
            return '<span class="badge bg-secondary">-</span>';
        }
        return '<img src="' . $url . '" class="img-rounded" style="width:auto; max-height:45px; border: 1px solid #dee2e6; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
    }

    public function getCardImageHtml()
    {
        $url = $this->card_image_url;
        if (empty($url)) {
            return '<span class="badge bg-secondary">-</span>';
        }
        return '<img src="' . $url . '" class="img-rounded" style="width:auto; max-height:45px; border: 1px solid #dee2e6; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
    }
}
