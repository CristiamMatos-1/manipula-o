"use client";

import { useFormState } from "react-dom";
import { resetPasswordAction } from "@/app/admin/actions/auth";
import { Field, FormMessage, SubmitButton } from "@/components/ui/form";
import { initialFormState } from "@/lib/form";

export function ResetForm({ token }: { token: string }) {
  const [state, action] = useFormState(resetPasswordAction, initialFormState);
  return (
    <form action={action} className="space-y-4" noValidate>
      <input type="hidden" name="token" value={token} />
      <FormMessage state={state} />
      <Field label="Nova senha" name="password" type="password" autoComplete="new-password" required error={state.fieldErrors?.password} />
      <Field label="Confirmar nova senha" name="confirm" type="password" autoComplete="new-password" required error={state.fieldErrors?.confirm} />
      <SubmitButton className="btn-primary w-full" pendingText="Salvando...">
        Redefinir senha
      </SubmitButton>
    </form>
  );
}
