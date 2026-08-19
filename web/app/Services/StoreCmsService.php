<?php

namespace App\Services;

use App\DTO\Store\StoreCmsDTO;
use App\Models\Store;
use App\Repositories\StoreRepository;

class StoreCmsService
{
    public function __construct(protected StoreRepository $storeRepository) {}

    /**
     * Available themes with their color tokens and fonts.
     *
     * @return array<string, array{label: string, colors: array{primary: string, secondary: string, accent: string, strong: string}, font: string}>
     */
    public function themes(): array
    {
        return [
            'teal' => [
                'label' => 'Modern',
                'colors' => ['primary' => '#3F9AAE', 'secondary' => '#79C9C5', 'accent' => '#FFE2AF', 'strong' => '#F96E5B'],
                'font' => 'Outfit',
                'variant' => 'modern',
            ],
            'modern' => [
                'label' => 'Modern',
                'colors' => ['primary' => '#3F9AAE', 'secondary' => '#79C9C5', 'accent' => '#FFE2AF', 'strong' => '#F96E5B'],
                'font' => 'Outfit',
                'variant' => 'modern',
            ],
            'fashion' => [
                'label' => 'Fashion',
                'colors' => ['primary' => '#1C1917', 'secondary' => '#E7E5E4', 'accent' => '#C4A574', 'strong' => '#9F1239'],
                'font' => 'Playfair Display',
                'variant' => 'fashion',
            ],
            'food' => [
                'label' => 'Food',
                'colors' => ['primary' => '#C2410C', 'secondary' => '#FFEDD5', 'accent' => '#65A30D', 'strong' => '#B45309'],
                'font' => 'Nunito',
                'variant' => 'food',
            ],
            'sky' => [
                'label' => 'Langit',
                'colors' => ['primary' => '#5EABD6', 'secondary' => '#FEFBC7', 'accent' => '#FFB4B4', 'strong' => '#E14434'],
                'font' => 'Outfit',
                'variant' => 'modern',
            ],
            'navy' => [
                'label' => 'Navy',
                'colors' => ['primary' => '#384B70', 'secondary' => '#507687', 'accent' => '#FCFAEE', 'strong' => '#B8001F'],
                'font' => 'Merriweather',
                'variant' => 'fashion',
            ],
            'sand' => [
                'label' => 'Sand',
                'colors' => ['primary' => '#8CB9BD', 'secondary' => '#FEFBF6', 'accent' => '#ECB159', 'strong' => '#B67352'],
                'font' => 'Merriweather',
                'variant' => 'fashion',
            ],
            'forest' => [
                'label' => 'Hutan',
                'colors' => ['primary' => '#638C6D', 'secondary' => '#E7FBB4', 'accent' => '#DF6D2D', 'strong' => '#C84C05'],
                'font' => 'Nunito',
                'variant' => 'food',
            ],
        ];
    }

    /**
     * Default showcase content for a store landing page.
     *
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return [
            'hero' => [
                'title' => 'Temukan Produk Terbaik di Toko Ini',
                'subtitle' => 'Produk berkualitas dengan harga terbaik, dikirim cepat dan aman.',
                'cta_label' => 'Lihat Produk',
                'image' => null,
            ],
            'banners' => [],
            'featured_product_ids' => [],
            'about' => [
                'title' => 'Tentang Toko Ini',
                'text' => 'Toko terpercaya di Toko Instan yang menyediakan produk berkualitas dengan harga terjangkau.',
            ],
            'testimonials' => [],
            'contact' => [
                'show' => true,
            ],
        ];
    }

    /**
     * Merge stored showcase with defaults so frontend always has all keys.
     *
     * @param  array<string, mixed>|null  $showcase
     * @return array<string, mixed>
     */
    public function normalize(?array $showcase): array
    {
        $defaults = $this->defaults();

        if (! $showcase) {
            return $defaults;
        }

        return array_replace_recursive($defaults, $showcase);
    }

    /**
     * Resolve active theme config for a store, falling back to defaults.
     *
     * @return array{key: string, label: string, colors: array{primary: string, secondary: string, accent: string, strong: string}, font: string}
     */
    public function resolve(Store $store): array
    {
        $themes = $this->themes();
        $key = $store->theme ?: 'teal';

        if (! isset($themes[$key]) && $key !== 'custom') {
            $key = 'teal';
        }

        $config = $themes[$key] ?? $themes['teal'];
        $colors = $config['colors'];

        $custom = $store->theme_colors;
        if (is_array($custom)) {
            foreach (['primary', 'secondary', 'accent', 'strong'] as $token) {
                if (isset($custom[$token])) {
                    $colors[$token] = $custom[$token];
                }
            }
        }

        return [
            'key' => $key,
            'label' => $key === 'custom' ? 'Custom' : $config['label'],
            'colors' => $colors,
            'font' => $config['font'],
            'variant' => $config['variant'] ?? 'modern',
        ];
    }

    public function updateCms(Store $store, StoreCmsDTO $dto): void
    {
        $showcase = $this->normalize($store->showcase);
        $rawShowcase = $dto->showcase;

        foreach ($rawShowcase as $section => $values) {
            $showcase[$section] = array_replace($showcase[$section] ?? [], $values);
        }

        $this->storeRepository->update($store, [
            'theme' => $dto->theme,
            'theme_colors' => $dto->themeColors,
            'showcase' => $showcase,
        ]);
    }
}
