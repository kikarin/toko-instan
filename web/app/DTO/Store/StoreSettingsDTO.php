<?php

namespace App\DTO\Store;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class StoreSettingsDTO
{
    public function __construct(
        public array $validatedData,
        public ?UploadedFile $bannerFile = null,
        public array $bannerFiles = [],
        public ?UploadedFile $logoFile = null
    ) {}

    public static function fromRequest(Request $request, int $storeId): self
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:stores,slug,'.$storeId,
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|string|max:500',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'avatar_hue' => 'nullable|integer|between:0,360',
            'banner_url' => 'nullable|string|max:500',
            'banner_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'banner_files' => 'nullable|array|max:5',
            'banner_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'existing_banners' => 'nullable|array',
            'existing_banners.*' => 'string|max:500',
            'highlights' => 'nullable|array|max:3',
            'highlights.*' => 'string|max:50',
            'hero_config' => 'nullable|array',
            'hero_config.about_text' => 'nullable|string|max:255',
            'hero_config.widget_title' => 'nullable|string|max:50',
            'hero_config.widget_subtitle' => 'nullable|string|max:100',
            'hero_config.widget_description' => 'nullable|string|max:150',
            'hero_config.fake_buyer_count' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'origin_city' => 'nullable|string|max:120',
            'origin_postal_code' => 'nullable|string|max:10',
            'instagram' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'headline' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'npwp' => 'nullable|string|max:30',
            'nik' => 'nullable|string|max:30',
            'is_pkp' => 'nullable|boolean',
            'tax_name' => 'nullable|string|max:255',
            'tax_address' => 'nullable|string',
        ])->validate();

        return new self(
            validatedData: $validated,
            bannerFile: $request->file('banner_file'),
            bannerFiles: $request->file('banner_files') ?? [],
            logoFile: $request->file('logo_file')
        );
    }
}
