import type { ReactNode } from "react";
import Link from "next/link";
import type { LucideIcon } from "lucide-react";
import { cn } from "@/lib/utils";

export function PageHeader({ title, description, actions }: { title: string; description?: string; actions?: ReactNode }) {
  return (
    <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 className="text-2xl font-extrabold text-brand-navy sm:text-3xl">{title}</h1>
        {description && <p className="mt-1 text-sm text-brand-navy/70">{description}</p>}
      </div>
      {actions && <div className="flex flex-wrap gap-2">{actions}</div>}
    </div>
  );
}

const TONES = {
  blue: "bg-brand-blue/10 text-brand-blue",
  green: "bg-brand-green/25 text-brand-forest",
  sky: "bg-brand-sky/15 text-brand-sky",
  navy: "bg-brand-navy/10 text-brand-navy",
} as const;

export function StatCard({
  label,
  value,
  icon: Icon,
  tone = "blue",
  href,
  hint,
}: {
  label: string;
  value: number | string;
  icon: LucideIcon;
  tone?: keyof typeof TONES;
  href?: string;
  hint?: string;
}) {
  const body = (
    <div className={cn("card flex items-center gap-4", href && "transition hover:-translate-y-0.5 hover:shadow-lg")}>
      <div className={cn("flex h-12 w-12 shrink-0 items-center justify-center rounded-xl", TONES[tone])}>
        <Icon className="h-6 w-6" aria-hidden />
      </div>
      <div className="min-w-0">
        <p className="text-3xl font-extrabold leading-none text-brand-navy">{value}</p>
        <p className="mt-1 truncate text-sm font-semibold text-brand-navy/70">{label}</p>
        {hint && <p className="truncate text-xs text-brand-navy/50">{hint}</p>}
      </div>
    </div>
  );
  return href ? <Link href={href}>{body}</Link> : body;
}

export function StatusBadge({ status }: { status: "DRAFT" | "PUBLISHED" }) {
  return status === "PUBLISHED" ? (
    <span className="badge bg-brand-green/30 text-brand-forest">Publicado</span>
  ) : (
    <span className="badge bg-amber-100 text-amber-800">Rascunho</span>
  );
}
