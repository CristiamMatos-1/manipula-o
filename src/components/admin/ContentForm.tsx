"use client";

import { useFormState } from "react-dom";
import { saveContentAction, type ContentKind } from "@/app/admin/actions/content";
import { Field, FormMessage, SubmitButton, TextArea } from "@/components/ui/form";
import { initialFormState } from "@/lib/form";

export type ContentFormItem = {
  id: string;
  title: string;
  slug: string;
  excerpt: string | null;
  content: string;
  coverUrl?: string | null;
  status: "DRAFT" | "PUBLISHED";
  seoTitle: string | null;
  seoDescription: string | null;
  seoKeywords: string | null;
  ogImageUrl: string | null;
};

export function ContentForm({
  kind,
  item,
  canWrite,
  canPublish,
}: {
  kind: ContentKind;
  item?: ContentFormItem;
  canWrite: boolean;
  canPublish: boolean;
}) {
  const [state, action] = useFormState(saveContentAction.bind(null, kind, item?.id ?? null), initialFormState);
  const e = state.fieldErrors ?? {};
  const published = item?.status === "PUBLISHED";

  return (
    <form action={action} className="space-y-6" noValidate>
      <FormMessage state={state} />
      <fieldset disabled={!canWrite} className="space-y-6">
        <div className="card space-y-4">
          <Field label="Título" name="title" required defaultValue={item?.title} error={e.title} maxLength={150} />
          <Field
            label="Endereço (slug)"
            name="slug"
            defaultValue={item?.slug}
            error={e.slug}
            maxLength={80}
            hint="Deixe em branco para gerar automaticamente a partir do título. Ex.: cuidados-com-a-pele"
          />
          <TextArea label="Resumo" name="excerpt" rows={2} defaultValue={item?.excerpt ?? ""} error={e.excerpt} maxLength={300} hint="Aparece nas listagens e ao compartilhar." />
          {kind === "post" && (
            <Field label="Imagem de capa (URL)" name="coverUrl" defaultValue={item?.coverUrl ?? ""} error={e.coverUrl} placeholder="https://..." />
          )}
          <TextArea
            label="Conteúdo"
            name="content"
            rows={14}
            required
            defaultValue={item?.content}
            error={e.content}
            hint="Formatação: ## Subtítulo · - item de lista · **negrito** · [texto](https://link). Separe parágrafos com uma linha em branco."
            className="font-mono"
          />
        </div>

        <details className="card" open={!!(item?.seoTitle || item?.seoDescription)}>
          <summary className="cursor-pointer text-base font-bold">Configurações de SEO (Google e redes sociais)</summary>
          <div className="mt-4 space-y-4">
            <Field label="Título SEO" name="seoTitle" maxLength={70} defaultValue={item?.seoTitle ?? ""} error={e.seoTitle} hint="Até 70 caracteres. Se vazio, usa o título." />
            <TextArea label="Descrição SEO" name="seoDescription" rows={2} maxLength={160} defaultValue={item?.seoDescription ?? ""} error={e.seoDescription} hint="Até 160 caracteres." />
            <Field label="Palavras-chave" name="seoKeywords" maxLength={200} defaultValue={item?.seoKeywords ?? ""} error={e.seoKeywords} hint="Separadas por vírgula." />
            <Field label="Imagem de compartilhamento (URL)" name="ogImageUrl" defaultValue={item?.ogImageUrl ?? ""} error={e.ogImageUrl} placeholder="https://..." />
          </div>
        </details>

        {canWrite && (
          <div className="sticky bottom-0 -mx-4 flex flex-col gap-2 border-t border-brand-navy/10 bg-white/95 px-4 py-3 backdrop-blur sm:static sm:mx-0 sm:flex-row sm:border-0 sm:bg-transparent sm:p-0">
            {published ? (
              <>
                {canPublish && (
                  <SubmitButton name="intent" value="publish" className="btn-success" pendingText="Salvando...">
                    Salvar alterações (continua publicado)
                  </SubmitButton>
                )}
                <SubmitButton name="intent" value="draft" className="btn-secondary" pendingText="Salvando...">
                  Despublicar e salvar como rascunho
                </SubmitButton>
              </>
            ) : (
              <>
                <SubmitButton name="intent" value="draft" className="btn-secondary" pendingText="Salvando...">
                  Salvar rascunho
                </SubmitButton>
                {canPublish && (
                  <SubmitButton name="intent" value="publish" className="btn-success" pendingText="Publicando...">
                    Publicar
                  </SubmitButton>
                )}
              </>
            )}
          </div>
        )}
      </fieldset>
      {!canWrite && <p className="text-sm text-brand-navy/60">Seu perfil é somente leitura: não é possível editar este conteúdo.</p>}
    </form>
  );
}
