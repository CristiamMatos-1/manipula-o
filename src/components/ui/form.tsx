"use client";

import { useFormStatus } from "react-dom";
import type { ButtonHTMLAttributes, InputHTMLAttributes, ReactNode, TextareaHTMLAttributes } from "react";
import { Loader2 } from "lucide-react";
import { cn } from "@/lib/utils";
import type { FormState } from "@/lib/form";

export function SubmitButton({
  children,
  className = "btn-primary",
  pendingText = "Aguarde...",
  ...props
}: ButtonHTMLAttributes<HTMLButtonElement> & { pendingText?: string }) {
  const { pending } = useFormStatus();
  return (
    <button type="submit" className={className} disabled={pending || props.disabled} {...props}>
      {pending && <Loader2 className="h-4 w-4 animate-spin" aria-hidden />}
      {pending ? pendingText : children}
    </button>
  );
}

export function ConfirmButton({
  message,
  children,
  className = "btn-danger",
  ...props
}: ButtonHTMLAttributes<HTMLButtonElement> & { message: string }) {
  return (
    <button
      type="submit"
      className={className}
      onClick={(e) => {
        if (!window.confirm(message)) e.preventDefault();
      }}
      {...props}
    >
      {children}
    </button>
  );
}

export function FormMessage({ state }: { state: FormState }) {
  if (state.error)
    return (
      <p role="alert" className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700">
        {state.error}
      </p>
    );
  if (state.success)
    return (
      <p role="status" className="rounded-lg border border-brand-green bg-brand-mint/50 px-3 py-2 text-sm font-medium text-brand-forest">
        {state.success}
      </p>
    );
  return null;
}

type FieldBase = { label: string; name: string; error?: string; hint?: ReactNode; className?: string };

export function Field({ label, name, error, hint, className, ...props }: FieldBase & InputHTMLAttributes<HTMLInputElement>) {
  return (
    <div className={className}>
      <label htmlFor={name} className="label">
        {label}
      </label>
      <input id={name} name={name} className={cn("input", error && "border-red-500")} aria-invalid={!!error} {...props} />
      {hint && !error && <p className="mt-1 text-xs text-brand-navy/60">{hint}</p>}
      {error && (
        <p role="alert" className="mt-1 text-xs font-medium text-red-600">
          {error}
        </p>
      )}
    </div>
  );
}

export function TextArea({ label, name, error, hint, className, ...props }: FieldBase & TextareaHTMLAttributes<HTMLTextAreaElement>) {
  return (
    <div className={className}>
      <label htmlFor={name} className="label">
        {label}
      </label>
      <textarea id={name} name={name} className={cn("input", error && "border-red-500")} aria-invalid={!!error} {...props} />
      {hint && !error && <p className="mt-1 text-xs text-brand-navy/60">{hint}</p>}
      {error && (
        <p role="alert" className="mt-1 text-xs font-medium text-red-600">
          {error}
        </p>
      )}
    </div>
  );
}
