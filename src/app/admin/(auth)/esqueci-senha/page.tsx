"use client";

import Link from "next/link";
import { useFormState } from "react-dom";
import { forgotPasswordAction } from "@/app/admin/actions/auth";
import { AuthShell } from "@/components/admin/AuthShell";
import { Field, FormMessage, SubmitButton } from "@/components/ui/form";
import { initialFormState } from "@/lib/form";

export default function ForgotPasswordPage() {
  const [state, action] = useFormState(forgotPasswordAction, initialFormState);
  return (
    <AuthShell title="Esqueci minha senha" subtitle="Informe seu e-mail e enviaremos um link seguro para criar uma nova senha.">
      <form action={action} className="space-y-4" noValidate>
        <FormMessage state={state} />
        <Field label="E-mail" name="email" type="email" autoComplete="email" required error={state.fieldErrors?.email} />
        <SubmitButton className="btn-primary w-full" pendingText="Enviando...">
          Enviar link de redefinição
        </SubmitButton>
        <p className="text-center text-sm">
          <Link href="/admin/login" className="font-semibold text-brand-blue hover:underline">
            Voltar ao login
          </Link>
        </p>
      </form>
    </AuthShell>
  );
}
