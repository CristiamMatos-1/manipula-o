"use server";

import { redirect } from "next/navigation";
import { z } from "zod";
import { prisma } from "@/lib/db";
import { audit } from "@/lib/audit";
import { env } from "@/lib/env";
import { sendMail, passwordResetEmail } from "@/lib/mail";
import { createSession, destroySession } from "@/lib/auth/session";
import { getCurrentUser, requireUser } from "@/lib/auth/guards";
import { hashPassword, passwordSchema, verifyPassword } from "@/lib/auth/password";
import { RESET_TTL_MINUTES, createResetToken, hashToken, verifyResetToken } from "@/lib/auth/reset-token";
import { type FormState, str, zodFieldErrors } from "@/lib/form";

const MAX_ATTEMPTS = 5;
const LOCK_MINUTES = 15;
// Hash fictício para igualar o tempo de resposta quando o e-mail não existe.
const DUMMY_HASH = "$2b$12$6AnkHDCsCaFcxwlEVP7x2uOR0o6YEwJKu0zHGa3ZSM9oCVmYmdEAG";

const loginSchema = z.object({
  email: z.string().email("Informe um e-mail válido.").toLowerCase(),
  password: z.string().min(1, "Informe a senha."),
});

export async function loginAction(_prev: FormState, fd: FormData): Promise<FormState> {
  const parsed = loginSchema.safeParse({ email: str(fd, "email"), password: String(fd.get("password") ?? "") });
  if (!parsed.success) return { fieldErrors: zodFieldErrors(parsed.error) };
  const { email, password } = parsed.data;

  const genericError = "E-mail ou senha incorretos.";
  const user = await prisma.user.findUnique({ where: { email } });

  if (!user) {
    await verifyPassword(password, DUMMY_HASH);
    await audit({ userEmail: email, action: "LOGIN_FAILED", entity: "Auth", details: "E-mail desconhecido" });
    return { error: genericError };
  }

  if (user.lockedUntil && user.lockedUntil > new Date()) {
    await audit({ userId: user.id, userEmail: email, action: "LOGIN_BLOCKED", entity: "Auth", details: "Conta temporariamente bloqueada" });
    return { error: `Muitas tentativas. Tente novamente em alguns minutos.` };
  }

  const ok = await verifyPassword(password, user.passwordHash);
  if (!ok || !user.active) {
    const attempts = user.failedLoginAttempts + 1;
    const lock = attempts >= MAX_ATTEMPTS;
    await prisma.user.update({
      where: { id: user.id },
      data: {
        failedLoginAttempts: lock ? 0 : attempts,
        lockedUntil: lock ? new Date(Date.now() + LOCK_MINUTES * 60_000) : null,
      },
    });
    await audit({
      userId: user.id,
      userEmail: email,
      action: lock ? "LOGIN_LOCKED" : "LOGIN_FAILED",
      entity: "Auth",
      details: !user.active ? "Usuário inativo" : `Tentativa ${attempts}/${MAX_ATTEMPTS}`,
    });
    return { error: genericError };
  }

  await prisma.user.update({
    where: { id: user.id },
    data: { failedLoginAttempts: 0, lockedUntil: null, lastLoginAt: new Date() },
  });
  await createSession({ uid: user.id, role: user.role, mcp: user.mustChangePassword });
  await audit({ userId: user.id, userEmail: user.email, action: "LOGIN", entity: "Auth" });

  redirect(user.mustChangePassword ? "/admin/alterar-senha" : "/admin");
}

export async function logoutAction() {
  const user = await getCurrentUser();
  if (user) await audit({ userId: user.id, userEmail: user.email, action: "LOGOUT", entity: "Auth" });
  destroySession();
  redirect("/admin/login");
}

const forgotSchema = z.object({ email: z.string().email("Informe um e-mail válido.").toLowerCase() });

export async function forgotPasswordAction(_prev: FormState, fd: FormData): Promise<FormState> {
  const parsed = forgotSchema.safeParse({ email: str(fd, "email") });
  if (!parsed.success) return { fieldErrors: zodFieldErrors(parsed.error) };

  const user = await prisma.user.findUnique({ where: { email: parsed.data.email } });
  if (user && user.active) {
    const { token, tokenHash, expiresAt } = createResetToken(user.id);
    await prisma.user.update({ where: { id: user.id }, data: { resetToken: tokenHash, resetTokenExpiry: expiresAt } });
    const link = `${env.appUrl}/admin/redefinir-senha?token=${encodeURIComponent(token)}`;
    const mail = passwordResetEmail(user.name, link, RESET_TTL_MINUTES);
    try {
      await sendMail({ to: user.email, ...mail });
    } catch (err) {
      console.error("Falha ao enviar e-mail de recuperação", err);
    }
    await audit({ userId: user.id, userEmail: user.email, action: "PASSWORD_RESET_REQUESTED", entity: "Auth" });
  }

  // Resposta idêntica exista o e-mail ou não (evita enumeração de usuários).
  return {
    success:
      "Se o e-mail estiver cadastrado, enviaremos um link de redefinição válido por " +
      `${RESET_TTL_MINUTES} minutos. Verifique também a caixa de spam.`,
  };
}

const resetSchema = z
  .object({ token: z.string().min(10), password: passwordSchema, confirm: z.string() })
  .refine((v) => v.password === v.confirm, { path: ["confirm"], message: "As senhas não conferem." });

export async function resetPasswordAction(_prev: FormState, fd: FormData): Promise<FormState> {
  const parsed = resetSchema.safeParse({
    token: str(fd, "token"),
    password: String(fd.get("password") ?? ""),
    confirm: String(fd.get("confirm") ?? ""),
  });
  if (!parsed.success) return { fieldErrors: zodFieldErrors(parsed.error) };

  const invalid = { error: "Link inválido ou expirado. Solicite uma nova redefinição." };
  const decoded = verifyResetToken(parsed.data.token);
  if (!decoded) return invalid;

  const user = await prisma.user.findUnique({ where: { id: decoded.userId } });
  if (
    !user ||
    !user.active ||
    !user.resetToken ||
    !user.resetTokenExpiry ||
    user.resetTokenExpiry < new Date() ||
    user.resetToken !== hashToken(parsed.data.token)
  ) {
    return invalid;
  }

  await prisma.user.update({
    where: { id: user.id },
    data: {
      passwordHash: await hashPassword(parsed.data.password),
      resetToken: null,
      resetTokenExpiry: null,
      mustChangePassword: false,
      failedLoginAttempts: 0,
      lockedUntil: null,
    },
  });
  await audit({ userId: user.id, userEmail: user.email, action: "PASSWORD_RESET_COMPLETED", entity: "Auth" });
  destroySession();
  redirect("/admin/login?reset=1");
}

const changeSchema = z
  .object({ current: z.string().min(1, "Informe a senha atual."), password: passwordSchema, confirm: z.string() })
  .refine((v) => v.password === v.confirm, { path: ["confirm"], message: "As senhas não conferem." })
  .refine((v) => v.password !== v.current, { path: ["password"], message: "A nova senha deve ser diferente da atual." });

export async function changePasswordAction(_prev: FormState, fd: FormData): Promise<FormState> {
  const current = await requireUser({ allowMustChange: true });
  const parsed = changeSchema.safeParse({
    current: String(fd.get("current") ?? ""),
    password: String(fd.get("password") ?? ""),
    confirm: String(fd.get("confirm") ?? ""),
  });
  if (!parsed.success) return { fieldErrors: zodFieldErrors(parsed.error) };

  const user = await prisma.user.findUniqueOrThrow({ where: { id: current.id } });
  if (!(await verifyPassword(parsed.data.current, user.passwordHash))) {
    return { fieldErrors: { current: "Senha atual incorreta." } };
  }

  await prisma.user.update({
    where: { id: user.id },
    data: { passwordHash: await hashPassword(parsed.data.password), mustChangePassword: false, resetToken: null, resetTokenExpiry: null },
  });
  // Reemite a sessão sem a flag de troca obrigatória.
  await createSession({ uid: user.id, role: user.role, mcp: false });
  await audit({
    userId: user.id,
    userEmail: user.email,
    action: user.mustChangePassword ? "PASSWORD_CHANGED_FORCED" : "PASSWORD_CHANGED",
    entity: "User",
    entityId: user.id,
  });
  redirect("/admin");
}
