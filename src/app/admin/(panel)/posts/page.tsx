import { ContentListPage } from "@/components/admin/ContentAdmin";

export const metadata = { title: "Artigos" };

export default function Page() {
  return <ContentListPage kind="post" />;
}
