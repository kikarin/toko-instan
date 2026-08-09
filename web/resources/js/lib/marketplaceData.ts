import {
    Flame,
    Footprints,
    Shirt,
    ShoppingBag,
    Star,
    Watch,
} from 'lucide-vue-next';

export const CATEGORY_ICON_META: Record<
    string,
    { icon: typeof ShoppingBag; color: string }
> = {
    Sneakers: { icon: Footprints, color: 'bg-rose-100 text-rose-600' },
    Running: { icon: Flame, color: 'bg-blue-100 text-blue-600' },
    Apparel: { icon: Shirt, color: 'bg-(--brand-soft) text-(--brand-secondary)' },
    Basketball: { icon: Star, color: 'bg-amber-100 text-amber-600' },
    Accessories: { icon: Watch, color: 'bg-emerald-100 text-emerald-600' },
};

export const FALLBACK_CATEGORY_META = {
    icon: ShoppingBag,
    color: 'bg-black/10 text-black',
};
