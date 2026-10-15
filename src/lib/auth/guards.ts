import "server-only";
import { cache } from "react";
import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { prisma } from "@/lib/db";
import { SESSION_COOKIE, verifySession } from "./session";
import { can, type Permission } from "./rbac";

/**
 * Carrega o usuário da sessão SEMPRE a partir do banco: se o usuário for
 * desativado, excluído ou tiver o papel alterado, o efeito é imediato.
 */
export const getCurrentUser = cache(async () => {
  const session = await verifySession(cookies().get(SESSION_COOKIE)?.value);
  if (!session) return null;
  const user = await prisma.user.findUnique({
    where: { id: session.uid },
    select: { id: true, name: true, email: true, role: true, active: true, mustChangePassword: true },
  });
  if (!user || !user.active) return null;
  return user;
});

export type CurrentUser = NonNullable<Awaited<ReturnType<typeof getCurrentUser>>>;

/** Exige login. Usuários com troca de senha pendente são enviados à tela de troca. */
export async function requireUser(opts: { allowMustChange?: boolean } = {}): Promise<CurrentUser> {
  const user = await getCurrentUser();
  if (!user) redirect("/admin/login");
  if (user.mustChangePassword && !opts.allowMustChange) redirect("/admin/alterar-senha");
  return user;
}

/** Exige login + permissão. Sem permissão: página 403 (ou erro em Server Actions). */
export async function requirePermission(permission: Permission): Promise<CurrentUser> {
  const user = await requireUser();
  if (!can(user.role, permission)) redirect("/admin/acesso-negado");
  return user;
}
