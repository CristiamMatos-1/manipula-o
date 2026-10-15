"use server";

import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import { z } from "zod";
import { prisma } from "@/lib/db";
import { audit } from "@/lib/audit";
import { requirePermission } from "@/lib/auth/guards";
import { hashPassword, passwordSchema } from "@/lib/auth/password";
import { ROLES, ROLE_LABELS, type Role } from "@/lib/auth/rbac";
import { type FormState, str, zodFieldErrors } from "@/lib/form";

const base = {
  name: z.string().min(2, "Informe o nome.").max(100),
  email: z.string().email("E-mail inválido.").toLowerCase(),
  role: z.enum(ROLES as [Role, ...Role[]], { message: "Perfil inválido." }),
};

const createSchema = z.object({ ...base, password: passwordSchema });
const updateSchema = z.object({ ...base, password: z.union([passwordSchema, z.literal("")]) });

/** Garante que sempre reste ao menos um SUPERADMIN ativo. */
async function otherActiveSuperadmins(excludeId: string) {
  return prisma.user.count({ where: { role: "SUPERADMIN", active: true, id: { not: excludeId } } });
}

export async function saveUserAction(id: string | null, _prev: FormState, fd: FormData): Promise<FormState> {
  const actor = await requirePermission("users:manage");
  const active = fd.get("active") === "on";
  const mustChange = fd.get("mustChangePassword") === "on";

  const parsed = (id ? updateSchema : createSchema).safeParse({
    name: str(fd, "name"),
    email: str(fd, "email"),
    role: str(fd, "role"),
    password: String(fd.get("password") ?? ""),
  });
  if (!parsed.success) return { fieldErrors: zodFieldErrors(parsed.error) };
  const d = parsed.data;

  const clash = await prisma.user.findUnique({ where: { email: d.email } });
  if (clash && clash.id !== id) return { fieldErrors: { email: "Já existe um usuário com este e-mail." } };

  if (!id) {
    const created = await prisma.user.create({
      data: { name: d.name, email: d.email, role: d.role, passwordHash: await hashPassword(d.password), mustChangePassword: true, active: true },
    });
    await audit({ userId: actor.id, userEmail: actor.email, action: "USER_CREATE", entity: "User", entityId: created.id, details: `${created.email} (${ROLE_LABELS[created.role]})` });
    revalidatePath("/admin/usuarios");
    redirect("/admin/usuarios?ok=criado");
  }

  const target = await prisma.user.findUnique({ where: { id } });
  if (!target) return { error: "Usuário não encontrado." };

  const losesSuper = target.role === "SUPERADMIN" && (d.role !== "SUPERADMIN" || !active);
  if (losesSuper && (await otherActiveSuperadmins(id)) === 0) {
    return { error: "Não é possível: este é o único Super Administrador ativo do sistema." };
  }
  if (id === actor.id && !active) return { error: "Você não pode desativar a própria conta." };

  const changes: string[] = [];
  if (target.name !== d.name) changes.push("nome");
  if (target.email !== d.email) changes.push(`e-mail (${target.email} -> ${d.email})`);
  if (target.role !== d.role) changes.push(`perfil (${ROLE_LABELS[target.role]} -> ${ROLE_LABELS[d.role]})`);
  if (target.active !== active) changes.push(active ? "reativado" : "desativado");
  if (d.password) changes.push("senha redefinida por administrador");

  await prisma.user.update({
    where: { id },
    data: {
      name: d.name,
      email: d.email,
      role: d.role,
      active,
      mustChangePassword: d.password ? true : mustChange,
      ...(d.password ? { passwordHash: await hashPassword(d.password), resetToken: null, resetTokenExpiry: null } : {}),
      ...(active ? {} : { resetToken: null, resetTokenExpiry: null }),
    },
  });
  await audit({ userId: actor.id, userEmail: actor.email, action: "USER_UPDATE", entity: "User", entityId: id, details: `${d.email}: ${changes.join("; ") || "sem alterações"}` });
  revalidatePath("/admin/usuarios");
  return { success: "Usuário atualizado." };
}

export async function deleteUserAction(id: string) {
  const actor = await requirePermission("users:manage");
  if (id === actor.id) redirect("/admin/usuarios?erro=self");
  const target = await prisma.user.findUnique({ where: { id } });
  if (!target) redirect("/admin/usuarios");
  if (target.role === "SUPERADMIN" && (await otherActiveSuperadmins(id)) === 0) redirect("/admin/usuarios?erro=last");

  await prisma.user.delete({ where: { id } });
  await audit({ userId: actor.id, userEmail: actor.email, action: "USER_DELETE", entity: "User", entityId: id, details: `${target.email} (${ROLE_LABELS[target.role]})` });
  revalidatePath("/admin/usuarios");
  redirect("/admin/usuarios?ok=excluido");
}
