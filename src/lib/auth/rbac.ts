export type Role = "SUPERADMIN" | "EDITOR_CONTEUDO" | "VISUALIZADOR";

export const ROLES: Role[] = ["SUPERADMIN", "EDITOR_CONTEUDO", "VISUALIZADOR"];

export const ROLE_LABELS: Record<Role, string> = {
  SUPERADMIN: "Super Administrador",
  EDITOR_CONTEUDO: "Editor de Conteúdo",
  VISUALIZADOR: "Visualizador",
};

/**
 * Matriz de permissões (fonte única da verdade).
 * Toda Server Action e toda página do painel validam uma destas permissões
 * no servidor - esconder o menu na interface NÃO é considerado segurança.
 */
export const PERMISSIONS = {
  "dashboard:view": ["SUPERADMIN", "EDITOR_CONTEUDO", "VISUALIZADOR"],
  "content:view": ["SUPERADMIN", "EDITOR_CONTEUDO", "VISUALIZADOR"],
  "content:write": ["SUPERADMIN", "EDITOR_CONTEUDO"],
  "content:publish": ["SUPERADMIN", "EDITOR_CONTEUDO"],
  "content:delete": ["SUPERADMIN", "EDITOR_CONTEUDO"],
  "company:view": ["SUPERADMIN", "EDITOR_CONTEUDO", "VISUALIZADOR"],
  "company:edit": ["SUPERADMIN", "EDITOR_CONTEUDO"],
  "users:manage": ["SUPERADMIN"],
  "audit:view": ["SUPERADMIN"],
} as const satisfies Record<string, readonly Role[]>;

export type Permission = keyof typeof PERMISSIONS;

export function can(role: Role | undefined | null, permission: Permission): boolean {
  if (!role) return false;
  return (PERMISSIONS[permission] as readonly Role[]).includes(role);
}

export function isRole(value: unknown): value is Role {
  return typeof value === "string" && (ROLES as string[]).includes(value);
}
