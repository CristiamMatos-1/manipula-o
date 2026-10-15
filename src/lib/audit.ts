import { headers } from "next/headers";
import { prisma } from "@/lib/db";

export type AuditInput = {
  userId?: string | null;
  userEmail?: string | null;
  action: string;
  entity: string;
  entityId?: string | null;
  details?: string | null;
};

export function getClientIp(): string | null {
  const h = headers();
  const fwd = h.get("x-forwarded-for");
  return (fwd ? fwd.split(",")[0].trim() : h.get("x-real-ip")) || null;
}

/** Registra uma ação na trilha de auditoria. Nunca grave senhas ou tokens em `details`. */
export async function audit(input: AuditInput) {
  try {
    await prisma.auditLog.create({
      data: {
        userId: input.userId ?? null,
        userEmail: input.userEmail ?? null,
        action: input.action,
        entity: input.entity,
        entityId: input.entityId ?? null,
        details: input.details?.slice(0, 500) ?? null,
        ip: getClientIp(),
      },
    });
  } catch (err) {
    console.error("Falha ao gravar log de auditoria", err);
  }
}
