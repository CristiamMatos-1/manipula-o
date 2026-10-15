import bcrypt from "bcrypt";
import { z } from "zod";

const ROUNDS = 12;

export const hashPassword = (plain: string) => bcrypt.hash(plain, ROUNDS);
export const verifyPassword = (plain: string, hash: string) => bcrypt.compare(plain, hash);

const WEAK = new Set(["admin", "password", "senha", "12345678", "drogacare"]);

/** Política de senha: mínimo 10 caracteres, maiúscula, minúscula e número. */
export const passwordSchema = z
  .string()
  .min(10, "A senha deve ter pelo menos 10 caracteres.")
  .max(128, "A senha deve ter no máximo 128 caracteres.")
  .regex(/[a-z]/, "Inclua ao menos uma letra minúscula.")
  .regex(/[A-Z]/, "Inclua ao menos uma letra maiúscula.")
  .regex(/[0-9]/, "Inclua ao menos um número.")
  .refine((v) => !WEAK.has(v.toLowerCase()), "Escolha uma senha menos previsível.");
