import { z } from 'zod'

export const RegisterSchema = z
  .object({
    name: z.string().min(2, 'mínimo 2 caracteres').max(120),
    email: z.string().email('email inválido').max(180),
    password: z.string().min(8, 'mínimo 8 caracteres'),
    password_confirmation: z.string(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'as senhas não conferem',
    path: ['password_confirmation'],
  })

export type RegisterInput = z.infer<typeof RegisterSchema>

export const LoginSchema = z.object({
  email: z.string().email('email inválido'),
  password: z.string().min(1, 'obrigatório'),
})

export type LoginInput = z.infer<typeof LoginSchema>
