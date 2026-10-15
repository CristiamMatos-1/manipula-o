"use server";

import { revalidatePath } from "next/cache";
import { z } from "zod";
import { prisma } from "@/lib/db";
import { audit } from "@/lib/audit";
import { requirePermission } from "@/lib/auth/guards";
import { type FormState, str, zodFieldErrors } from "@/lib/form";
import { parseVideoUrl } from "@/lib/embed";
import { safeImageUrl, safeUrl } from "@/lib/utils";

const text = (max: number) => z.string().max(max);
const url = (label: string) =>
  z.string().max(500).refine((v) => v === "" || safeUrl(v) !== null, `${label}: informe uma URL http(s) válida.`);

const schema = z.object({
  name: text(100).min(2, "Informe o nome."),
  tagline: text(120),
  foundedYear: z.coerce.number().int().min(1800, "Ano inválido.").max(new Date().getFullYear(), "Ano no futuro."),
  pharmacistName: text(120),
  pharmacistCrf: text(40),
  pharmacistPhotoUrl: z.string().max(500).refine((v) => v === "" || safeImageUrl(v) !== null, "URL de foto inválida."),
  phone: text(40),
  whatsapp: z.string().max(20).transform((v) => v.replace(/\D/g, "")).refine((v) => v === "" || v.length >= 12, "Use DDI + DDD + número, ex.: 5511999999999."),
  email: z.string().max(120).refine((v) => v === "" || z.string().email().safeParse(v).success, "E-mail inválido."),
  address: text(250),
  mapsEmbedUrl: url("Mapa"),
  heroVideoUrl: url("Vídeo").refine((v) => v === "" || parseVideoUrl(v) !== null, "Use um link do YouTube ou um arquivo .mp4/.webm."),
  youtubeUrl: url("YouTube"),
  instagramUrl: url("Instagram"),
  facebookUrl: url("Facebook"),
});

export async function saveCompanyAction(_prev: FormState, fd: FormData): Promise<FormState> {
  const user = await requirePermission("company:edit");
  const raw = Object.fromEntries(
    Object.keys(schema.shape).map((k) => [k, str(fd, k)]),
  );
  const parsed = schema.safeParse(raw);
  if (!parsed.success) return { fieldErrors: zodFieldErrors(parsed.error) };

  const before = await prisma.companyInfo.findUnique({ where: { id: 1 } });
  await prisma.companyInfo.upsert({ where: { id: 1 }, update: parsed.data, create: { id: 1, ...parsed.data } });

  const changed = before
    ? (Object.keys(parsed.data) as Array<keyof typeof parsed.data>).filter((k) => String(before[k]) !== String(parsed.data[k]))
    : [];
  await audit({
    userId: user.id,
    userEmail: user.email,
    action: "COMPANY_UPDATE",
    entity: "CompanyInfo",
    entityId: "1",
    details: changed.length ? `Campos alterados: ${changed.join(", ")}` : "Sem alterações",
  });
  revalidatePath("/", "layout");
  return { success: "Dados da empresa salvos e publicados no site." };
}
