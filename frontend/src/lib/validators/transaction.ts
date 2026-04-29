import { z } from 'zod'

export const DIRECTIONS = ['in', 'out'] as const
export type Direction = (typeof DIRECTIONS)[number]

export const DIRECTION_LABELS: Record<Direction, string> = {
  in: 'entrada',
  out: 'saída',
}

export const CreateTransactionSchema = z.object({
  account_id: z.number({ error: 'conta obrigatória' }).int().positive(),
  category_id: z.number().int().positive().nullable().optional(),
  occurred_on: z.string().regex(/^\d{4}-\d{2}-\d{2}$/, 'use formato AAAA-MM-DD'),
  description: z.string().min(1, 'obrigatório').max(255),
  amount: z
    .string()
    .regex(/^\d+(\.\d{1,2})?$/, 'use formato 1234.56')
    .refine((v) => parseFloat(v) > 0, 'deve ser maior que zero'),
  direction: z.enum(DIRECTIONS, { error: 'inválido' }),
  notes: z.union([z.literal(''), z.string().max(5000)]).optional(),
  tags: z.array(z.string().max(50)).optional(),
  out_of_scope: z.boolean().optional(),
})

export type CreateTransactionInput = z.infer<typeof CreateTransactionSchema>

export const UpdateTransactionSchema = CreateTransactionSchema.partial()
export type UpdateTransactionInput = z.infer<typeof UpdateTransactionSchema>
