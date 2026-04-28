import { z } from 'zod'

export const ACCOUNT_TYPES = ['checking', 'savings', 'credit_card', 'cash', 'investment'] as const
export type AccountType = (typeof ACCOUNT_TYPES)[number]

export const ACCOUNT_TYPE_LABELS: Record<AccountType, string> = {
  checking: 'conta corrente',
  savings: 'poupança',
  credit_card: 'cartão de crédito',
  cash: 'dinheiro',
  investment: 'investimento',
}

const hexColor = z
  .string()
  .regex(/^#[0-9A-Fa-f]{6}$/, 'cor hex inválida (#RRGGBB)')

export const CreateAccountSchema = z.object({
  name: z.string().min(1, 'obrigatório').max(100),
  type: z.enum(ACCOUNT_TYPES, { error: 'tipo inválido' }),
  initial_balance: z.union([
    z.literal(''),
    z.string().regex(/^\d+(\.\d{1,2})?$/, 'use formato 1234.56'),
  ]).optional(),
  color: z.union([z.literal(''), hexColor]).optional(),
  icon: z.union([z.literal(''), z.string().max(40)]).optional(),
})

export type CreateAccountInput = z.infer<typeof CreateAccountSchema>

export const UpdateAccountSchema = CreateAccountSchema.partial()
export type UpdateAccountInput = z.infer<typeof UpdateAccountSchema>
