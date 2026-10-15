"use client";

import { useFormState } from "react-dom";
import { changePasswordAction, logoutAction } from "@/app/admin/actions/auth";
import { Field, FormMessage, SubmitButton } from "@/components/ui/form";
import { initialFormState } from "@/lib/form";

export function ChangePasswordForm({ forced }: { forced: boolean }) {
  const [state, action] = useFormState(changePasswordAction, initialFormState);
  return (
    <>
      <form action={action} className="space-y-4" noValidate>
        <FormMessage state={state} />
        <Field label="Senha atual" name="current" type="password" autoComplete="current-password" required error={state.fieldErrors?.current} />
        <Field
          label="Nova senha"
          name="password"
          type="password"
          autoComplete="new-password"
          required
          hint="Mínimo de 10 caracteres, com maiúscula, minúscula e número."
          error={state.fieldErrors?.password}
        />
        <Field label="Confirmar nova senha" name="confirm" type="password" autoComplete="new-password" required error={state.fieldErrors?.confirm} />
        <SubmitButton className="btn-primary w-full" pendingText="Salvando...">
          Salvar nova senha
        </SubmitButton>
      </form>
      {forced && (
        <form action={logoutAction} className="mt-4 text-center">
          <button type="submit" className="text-sm font-semibold text-brand-blue hover:underline">
            Sair
          </button>
        </form>
      )}
    </>
  );
}
