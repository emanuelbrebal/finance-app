import { z } from 'zod'

export const updateProfileSchema = z
  .object({
    name: z.string().min(1, 'nome obrigatório').max(255).optional(),
    email: z.string().email('e-mail inválido').max(255).optional(),
    password: z.string().min(8, 'mínimo 8 caracteres').optional(),
    password_confirmation: z.string().optional(),
    target_net_worth: z
      .string()
      .regex(/^\d+(\.\d{1,2})?$/, 'valor inválido')
      .optional(),
    target_date: z.string().optional(),
    estimated_monthly_income: z
      .string()
      .regex(/^\d+(\.\d{1,2})?$/, 'valor inválido')
      .optional(),
  })
  .refine(
    (data) => {
      if (data.password) {
        return data.password === data.password_confirmation
      }
      return true
    },
    { message: 'senhas não conferem', path: ['password_confirmation'] },
  )

export type UpdateProfileInput = z.infer<typeof updateProfileSchema>
