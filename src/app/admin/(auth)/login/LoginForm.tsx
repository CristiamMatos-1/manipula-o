"use client";

import Link from "next/link";
import { useFormState } from "react-dom";
import { loginAction } from "@/app/admin/actions/auth";
import { Field, FormMessage, SubmitButton } from "@/components/ui/form";
import { initialFormState } from "@/lib/form";

export function LoginForm() {
  const [state, action] = useFormState(loginAction, initialFormState);
  return (
    <form action={action} className="space-y-4" noValidate>
      <FormMessage state={state} />
      <Field label="E-mail" name="email" type="email" autoComplete="username" required error={state.fieldErrors?.email} />
      <Field label="Senha" name="password" type="password" autoComplete="current-password" required error={state.fieldErrors?.password} />
      <SubmitButton className="btn-primary w-full" pendingText="Entrando...">
        Entrar
      </SubmitButton>
      <p className="text-center text-sm">
        <Link href="/admin/esqueci-senha" className="font-semibold text-brand-blue hover:underline">
          Esqueci minha senha
        </Link>
      </p>
    </form>
  );
}
