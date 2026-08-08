import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Badge } from "./Badge.vue"

export const badgeVariants = cva(
  "inline-flex items-center justify-center rounded-full border px-2 py-0.5 text-xs font-medium w-fit whitespace-nowrap shrink-0 [&>svg]:size-3 gap-1 [&>svg]:pointer-events-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-3 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive transition-[color,box-shadow] overflow-hidden",
  {
    variants: {
      variant: {
        default:
          "border-transparent bg-primary text-primary-foreground [a&]:hover:bg-primary/90",
        secondary:
          "border-transparent bg-secondary text-secondary-foreground [a&]:hover:bg-secondary/90",
        destructive:
         "border-transparent bg-destructive text-white [a&]:hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60",
        outline:
          "text-foreground [a&]:hover:bg-accent [a&]:hover:text-accent-foreground",
        amber: "bg-(--brand) text-white",
        amberSolid: "bg-(--brand) text-white",
        violet: "bg-(--brand-accent) text-(--brand-strong) border border-(--brand-accent)",
        violetSolid: "bg-[var(--brand-strong)] text-white",
        teal: "bg-[var(--brand)] text-white border border-[var(--brand)]",
        sky: "bg-[#3b82f61a] text-[#3b82f6] border border-[#3b82f630]",
        green: "bg-[#22a15a1a] text-[#22a15a] border border-[#22a15a30]",
        rose: "bg-[#e0405a1a] text-[#e0405a] border border-[#e0405a30]",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  },
)
export type BadgeVariants = VariantProps<typeof badgeVariants>
