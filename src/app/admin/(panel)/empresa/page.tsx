import { requirePermission } from "@/lib/auth/guards";
import { can } from "@/lib/auth/rbac";
import { getCompanyInfo } from "@/lib/company";
import { PageHeader } from "@/components/admin/ui";
import { CompanyForm } from "@/components/admin/CompanyForm";

export const metadata = { title: "Dados da empresa" };

export default async function CompanyPage() {
  const user = await requirePermission("company:view");
  const company = await getCompanyInfo();
  return (
    <>
      <PageHeader title="Dados da empresa" description="Informações globais exibidas no site: fundação, farmacêutico responsável, contato, vídeo e redes sociais." />
      <CompanyForm company={company} canEdit={can(user.role, "company:edit")} />
    </>
  );
}
