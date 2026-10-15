import { cache } from "react";
import { prisma } from "@/lib/db";

/** CompanyInfo é um singleton (id = 1); é criado com os padrões se não existir. */
export const getCompanyInfo = cache(async () =>
  prisma.companyInfo.upsert({ where: { id: 1 }, update: {}, create: { id: 1 } }),
);

export const yearsInBusiness = (foundedYear: number) => Math.max(0, new Date().getFullYear() - foundedYear);
