import { z } from 'zod'

export const CATEGORY_KINDS = ['income', 'expense'] as const
export type CategoryKind = (typeof CATEGORY_KINDS)[number]

export const CATEGORY_KIND_LABELS: Record<CategoryKind, string> = {
  income: 'entrada',
  expense: 'saída',
}

const hexColor = z.string().regex(/^#[0-9A-Fa-f]{6}$/, 'cor hex inválida (#RRGGBB)')

export const CreateCategorySchema = z.object({
  name: z.string().min(1, 'obrigatório').max(60),
  kind: z.enum(CATEGORY_KINDS, { message: 'tipo inválido' }),
  color: hexColor,
  icon: z.string().min(1, 'obrigatório').max(40),
  is_essential: z.boolean().optional(),
  monthly_budget: z
    .string()
    .regex(/^\d+(\.\d{1,2})?$/, 'use formato 1234.56')
    .optional()
    .or(z.literal('')),
})

export type CreateCategoryInput = z.infer<typeof CreateCategorySchema>

export const UpdateCategorySchema = CreateCategorySchema.partial()
export type UpdateCategoryInput = z.infer<typeof UpdateCategorySchema>
