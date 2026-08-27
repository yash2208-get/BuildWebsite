<?php

declare(strict_types=1);

namespace App\Enums;

enum AiGenerationType: string
{
    case Website = 'website';
    case LandingPage = 'landing';
    case Content = 'content';
    case Blog = 'blog';
    case Palette = 'palette';
    case ImageSuggestion = 'image';
    case DesignSuggestion = 'design';

    public function label(): string
    {
        return match ($this) {
            self::Website => 'AI Website',
            self::LandingPage => 'Landing Page',
            self::Content => 'Content',
            self::Blog => 'Blog Article',
            self::Palette => 'Colour Palette',
            self::ImageSuggestion => 'Image Suggestions',
            self::DesignSuggestion => 'Design Suggestions',
        };
    }

    public function credits(): int
    {
        return match ($this) {
            self::Website => 5,
            self::LandingPage => 3,
            self::Blog => 2,
            default => 1,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Website => 'sparkles',
            self::LandingPage => 'rocket',
            self::Content => 'pencil',
            self::Blog => 'book',
            self::Palette => 'palette',
            self::ImageSuggestion => 'image',
            self::DesignSuggestion => 'wand',
        };
    }
}
