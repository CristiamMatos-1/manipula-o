"use client";

import { useFormState } from "react-dom";
import { saveUserAction } from "@/app/admin/actions/users";
import { Field, FormMessage, SubmitButton } from "@/components/ui/form";
import { initialFormState } from "@/lib/form";
import { ROLES, ROLE_LABELS, type Role } from "@/lib/auth/rbac";

const ROLE_HELP: Record<Role, string> = {
  SUPERADMIN: "Acesso total: usuários, conteúdo, empresa e auditoria.",
  EDITOR_CONTEUDO: "Cria, edita, publica e exclui artigos e páginas; edita dados da empresa. Não gerencia usuários.",
  VISUALIZADOR: "Somente leitura: consulta o painel sem alterar nada.",
};

export type UserFormItem = { id: string; name: string; email: string; role: Role; active: boolean; mustChangePassword: boolean };

export function UserForm({ user, isSelf }: { user?: UserFormItem; isSelf?: boolean }) {
  const [state, action] = useFormState(saveUserAction.bind(null, user?.id ?? null), initialFormState);
  const e = state.fieldErrors ?? {};
  return (
    <form action={action} className="card max-w-2xl space-y-4" noValidate>
      <FormMessage state={state} />
      <Field label="Nome completo" name="name" required defaultValue={user?.name} error={e.name} />
      <Field label="E-mail (login)" name="email" type="email" required defaultValue={user?.email} error={e.email} />
      <div>
        <label htmlFor="role" className="label">Perfil de acesso</label>
        <select id="role" name="role" className="input" defaultValue={user?.role ?? "VISUALIZADOR"}>
          {ROLES.map((r) => (
            <option key={r} value={r}>{ROLE_LABELS[r]}</option>
          ))}
        </select>
        {e.role && <p className="mt-1 text-xs font-medium text-red-600">{e.role}</p>}
        <ul className="mt-2 space-y-1 text-xs text-brand-navy/60">
          {ROLES.map((r) => (
            <li key={r}><strong>{ROLE_LABELS[r]}:</strong> {ROLE_HELP[r]}</li>
          ))}
        </ul>
      </div>
      <Field
        label={user ? "Nova senha (opcional)" : "Senha provisória"}
        name="password"
        type="password"
        autoComplete="new-password"
        required={!user}
        error={e.password}
        hint={user ? "Deixe em branco para manter a atual. Ao redefinir, o usuário será obrigado a trocá-la no próximo login." : "Mínimo de 10 caracteres, com maiúscula, minúscula e número. O usuário deverá trocá-la no 1º login."}
      />
      {user && (
        <div className="space-y-2">
          <label className="flex min-h-[44px] items-center gap-3 text-sm font-semibold">
            <input type="checkbox" name="active" defaultChecked={user.active} disabled={isSelf} className="h-5 w-5 accent-brand-blue" />
            Usuário ativo (pode acessar o painel)
            {isSelf && <input type="hidden" name="active" value="on" />}
          </label>
          <label className="flex min-h-[44px] items-center gap-3 text-sm font-semibold">
            <input type="checkbox" name="mustChangePassword" defaultChecked={user.mustChangePassword} className="h-5 w-5 accent-brand-blue" />
            Exigir troca de senha no próximo login
          </label>
        </div>
      )}
      <div className="flex flex-col gap-2 sm:flex-row">
        <SubmitButton className="btn-primary" pendingText="Salvando...">
          {user ? "Salvar alterações" : "Criar usuário"}
        </SubmitButton>
      </div>
    </form>
  );
}
