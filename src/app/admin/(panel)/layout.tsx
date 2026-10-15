import type { ReactNode } from "react";
import { requireUser } from "@/lib/auth/guards";
import { can, ROLE_LABELS } from "@/lib/auth/rbac";
import { AdminShell, type NavItem } from "@/components/admin/AdminShell";

export const dynamic = "force-dynamic";

export default async function PanelLayout({ children }: { children: ReactNode }) {
  const user = await requireUser();

  const nav: NavItem[] = [
    { href: "/admin", label: "Dashboard", icon: "dashboard" },
    { href: "/admin/posts", label: "Artigos", icon: "posts" },
    { href: "/admin/paginas", label: "Páginas", icon: "pages" },
    { href: "/admin/empresa", label: "Dados da empresa", icon: "company" },
    ...(can(user.role, "users:manage") ? [{ href: "/admin/usuarios", label: "Usuários", icon: "users" } as NavItem] : []),
    ...(can(user.role, "audit:view") ? [{ href: "/admin/auditoria", label: "Auditoria", icon: "audit" } as NavItem] : []),
  ];

  return (
    <AdminShell nav={nav} user={{ name: user.name, email: user.email, roleLabel: ROLE_LABELS[user.role] }}>
      {children}
    </AdminShell>
  );
}
