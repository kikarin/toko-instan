<?php

namespace App\DTO\Store;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreCmsDTO
{
    public function __construct(
        public string $theme,
        public array $themeColors,
        public array $showcase
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'theme' => 'required|string|in:teal,sky,navy,sand,forest,custom',
            'theme_colors.primary' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'theme_colors.secondary' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'theme_colors.accent' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'theme_colors.strong' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'showcase.hero.title' => 'nullable|string|max:255',
            'showcase.hero.subtitle' => 'nullable|string|max:500',
            'showcase.hero.cta_label' => 'nullable|string|max:100',
            'showcase.hero.image' => 'nullable|url',
            'showcase.about.title' => 'nullable|string|max:255',
            'showcase.about.text' => 'nullable|string|max:2000',
            'showcase.contact.show' => 'nullable|boolean',
            'showcase.featured_product_ids' => 'nullable|array',
            'showcase.featured_product_ids.*' => 'integer',
            'showcase.testimonials' => 'nullable|array',
            'showcase.testimonials.*.name' => 'nullable|string|max:255',
            'showcase.testimonials.*.role' => 'nullable|string|max:255',
            'showcase.testimonials.*.text' => 'nullable|string|max:2000',
            'showcase.testimonials.*.rating' => 'nullable|integer|between:1,5',
        ])->validate();

        return new self(
            theme: $validated['theme'],
            themeColors: $validated['theme_colors'],
            showcase: $validated['showcase'] ?? []
        );
    }
}
