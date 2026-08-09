export interface CatalogItem {
    id: number;
    name: string;
    product_count?: number;
}

export interface CatalogLabelItem extends CatalogItem {
    color: string | null;
}

export type CatalogItemKind = 'category' | 'brand' | 'label';