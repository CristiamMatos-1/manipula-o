import { ContentEditPage } from "@/components/admin/ContentAdmin";

export const metadata = { title: "Editar" };

export default function Page({ params, searchParams }: { params: { id: string }; searchParams: { saved?: string } }) {
  return <ContentEditPage kind="post" id={params.id} saved={searchParams.saved} />;
}
